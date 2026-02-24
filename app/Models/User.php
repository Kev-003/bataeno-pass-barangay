<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use App\Notifications\DocumentRequestReceived;
use Spatie\Permission\Traits\HasRoles;



class User extends Authenticatable implements FilamentUser, HasName
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'family_id',

        'first_name',
        'middle_name',
        'last_name',
        'suffix',

        'date_of_birth',
        'place_of_birth',
        'gender',
        'civil_status',

        'blood_type',
        'occupation',
        'registered_at',

        'email',
        'email_verified_at',
        'password',
        'remember_token',

        'municity_name',
        'barangay_name',
        'municity_code',
        'barangay_code',
        'profile_photos',
        'digital_signature',


    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function transactions()
    {
        return $this->hasMany(DocumentTransaction::class, 'requester_id');
    }

    public function householdMemberProfiles()
    {
        return $this->hasMany(HouseholdMemberProfile::class, 'user_id');
    }

    public function activeTerm()
    {
        return $this->hasOne(BarangayTerm::class, 'user_id')
            ->where(function ($query) {
                $query->whereNull('ended_at')
                    ->orWhere('ended_at', '>=', now());
            });
    }

    public function barangayTerms()
    {
        return $this->hasMany(BarangayTerm::class, 'user_id');
    }

    public function scopeOfficialsForBarangay($query, $barangayCode)
    {
        return $query->where(function ($q) use ($barangayCode) {
            // 1. Check if they have an active term in this specific barangay
            $q->whereHas('activeTerm', function ($sub) use ($barangayCode) {
                $sub->where('barangay_code', $barangayCode);
            })
                // 2. OR if they are a Super Admin (Global access)
                ->orWhereIn('email', [
                    'kevern920@gmail.com',
                    'admin@bataan.gov.ph',
                ]);
        });
    }

    public function isOfficial()
    {
        return $this->hasAnyRole(['Secretary', 'Kagawad', 'Captain'])
            || $this->isAdmin()
            || $this->email === 'russelsantos142@gmail.com';
    }

    public function isAdmin()
    {
        return $this->hasAnyRole(['Admin', 'Super Admin'])
            || in_array($this->email, [
                'kevern920@gmail.com',
                'admin@bataan.gov.ph',
            ]);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    /**
     * Filament display name
     */
    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user's full name (accessor)
     */
    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: $this->email;
    }

    public function getActiveBarangayIds(): array
    {
        // 1. Get IDs from Household memberships
        $householdBarangayIds = $this->householdMemberProfiles()
            ->whereNull('ended_at')
            ->with('household.house')
            ->orderByRaw("CASE 
            WHEN membership_type = 'primary' THEN 1 
            WHEN membership_type = 'transient' THEN 2 
            WHEN membership_type = 'associate' THEN 3 
            ELSE 4 END ASC")
            ->get()
            ->map(fn($profile) => $profile->household?->house?->barangay_id)
            ->filter();

        // 2. Get ID from Official Term
        $termBarangayId = $this->activeTerm?->barangay_code;

        // 3. Merge, unique, and reset keys
        return collect([$termBarangayId])
            ->concat($householdBarangayIds)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get active Barangay Codes for the user.
     */
    public function getActiveBarangayCodes(): array
    {
        // 1. From households
        $householdBarangayCodes = $this->householdMemberProfiles()
            ->whereNull('ended_at')
            ->with('household.house')
            ->get()
            ->map(fn($profile) => $profile->household?->house?->barangay_code ? \App\Models\Barangay::normalizeCode($profile->household->house->barangay_code) : null)
            ->filter();

        // 2. From resident property
        $residentCode = $this->barangay_code ? \App\Models\Barangay::normalizeCode($this->barangay_code) : null;

        // 3. From Official Term
        $termCode = $this->activeTerm && $this->activeTerm->barangay ? \App\Models\Barangay::normalizeCode($this->activeTerm->barangay->barangay_code) : null;

        // 4. From active Delegations (Where this user is the delegate)
        $delegatedCodes = \App\Models\Delegation::whereHas('delegateTerm', function ($query) {
            $query->where('user_id', $this->id);
        })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get()
            ->map(fn($d) => $d->granterTerm?->barangay?->barangay_code ? \App\Models\Barangay::normalizeCode($d->granterTerm->barangay->barangay_code) : null)
            ->filter();

        return collect([$termCode, $residentCode])
            ->concat($householdBarangayCodes)
            ->concat($delegatedCodes)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Returns the primary active Barangay ID.
     */
    public function getActiveBarangayId()
    {
        // Priority 1: Official Seat
        if ($this->activeTerm) {
            return $this->activeTerm->barangay_code;
        }

        // Priority 2: Primary Residence
        return $this->getActiveBarangayIds()[0] ?? null;
    }

    /**
     * Returns the primary active Barangay Code.
     */
    public function getActiveBarangayCode()
    {
        // Priority 1: Official Seat
        if ($this->activeTerm && $this->activeTerm->barangay) {
            return \App\Models\Barangay::normalizeCode($this->activeTerm->barangay->barangay_code);
        }

        // Priority 2: Resident record
        if ($this->barangay_code) {
            return \App\Models\Barangay::normalizeCode($this->barangay_code);
        }

        // Priority 3: Primary Residence
        return $this->getActiveBarangayCodes()[0] ?? null;
    }

    public function hasAnyValidID(): bool
    {
        if (!$this->egov_data || !is_array($this->egov_data)) {
            return false;
        }

        // List of ID keys we want to check for
        $idTypes = ['passport', 'umid', 'drivers_license', 'philhealth'];

        foreach ($idTypes as $type) {
            if (isset($this->egov_data[$type]['expiry_date'])) {
                try {
                    $expiry = \Carbon\Carbon::parse($this->egov_data[$type]['expiry_date']);
                    if ($expiry->isFuture()) {
                        return true; // Found one!
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return false;
    }

}
