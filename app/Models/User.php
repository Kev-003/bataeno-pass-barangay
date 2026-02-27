<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Notifications\DocumentRequestReceived;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasName, HasTenants
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

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

        'mother_id',
        'father_id',

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

        'municity_id',
        'barangay_id',
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

    // Upward Relationship: Who is my father?
    public function father()
    {
        return $this->belongsTo(User::class, 'father_id')->withTrashed();
    }

    // Upward Relationship: Who is my mother?
    public function mother()
    {
        return $this->belongsTo(User::class, 'mother_id')->withTrashed();
    }

    // Downward Relationship: Who are my children?
    public function children()
    {
        return $this->hasMany(User::class, 'father_id')
            ->withTrashed()
            ->union(
                User::withTrashed()->where('mother_id', $this->id)
            );
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
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
                $sub->whereHas('barangay', function ($bSub) use ($barangayCode) {
                    $bSub->where('barangay_code', $barangayCode);
                });
            });
        });
    }

    public function isOfficial(): bool
    {
        return $this->hasAnyRole(['Secretary', 'Kagawad', 'Captain'])
            || $this->activeTerm()->exists()
            || $this->isAdmin();
    }

    public function isAdmin()
    {
        return $this->hasAnyRole(['Admin', 'Super Admin'])
            || in_array($this->email, [
                'kevern920@gmail.com', //DEVS
                'admin@bataan.gov.ph',
                'russelsantos142@gmail.com' //DEVS

            ]);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->isAdmin();
        }

        if ($panel->getId() === 'official') {
            return $this->isOfficial();
        }

        return false;
    }

    public function getTenants(Panel $panel): array|Collection
    {
        // For the official panel, only return the specific barangay they hold office in
        if ($panel->getId() === 'official') {
            $termBarangayId = $this->activeTerm?->barangay_id;

            if ($termBarangayId) {
                return Barangay::where('id', $termBarangayId)->get();
            }
        }

        // For other panels, return all barangays they are associated with
        $ids = $this->getActiveBarangayIds();

        return Barangay::whereIn('id', $ids)->get();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if (!$tenant instanceof Barangay) {
            return false;
        }

        // Check if the tenant ID is in the user's active IDs
        return in_array(
            $tenant->id,
            $this->getActiveBarangayIds()
        );
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
            ->where('presence_status', 'Present')
            ->with('household.house')
            ->get()
            ->map(fn($profile) => $profile->household?->house?->barangay_id)
            ->filter();

        // 2. Get ID from resident property
        $residentId = $this->barangay_id;

        // 3. Get ID from Official Term
        $termId = $this->activeTerm?->barangay_id;

        // 4. From active Delegations
        $delegatedIds = \App\Models\Delegation::whereHas('delegateTerm', function ($query) {
            $query->where('user_id', $this->id);
        })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get()
            ->map(fn($d) => $d->granterTerm?->barangay_id)
            ->filter();

        // 5. Merge, unique, and reset keys
        return collect([$termId, $residentId])
            ->concat($householdBarangayIds)
            ->concat($delegatedIds)
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
            return $this->activeTerm->barangay_id;
        }

        // Priority 2: Primary Residence
        return $this->getActiveBarangayIds()[0] ?? null;
    }

    /**
     * Returns the primary active Barangay Code.
     */
    public function getActiveBarangayCode()
    {
        $id = $this->getActiveBarangayId();
        if (!$id)
            return null;

        return Barangay::where('id', $id)->value('barangay_code');
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
