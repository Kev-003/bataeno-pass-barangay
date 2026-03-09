<?php

namespace App\Models;

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
use Illuminate\Broadcasting\InteractsWithSockets;

class User extends Authenticatable implements FilamentUser, HasName, HasTenants
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, InteractsWithSockets;

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
        'contact_number',
        'municity_id',
        'barangay_id',
        'profile_photos',
        'digital_signature',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'registered_at' => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function father()
    {
        return $this->belongsTo(User::class, 'father_id')->withTrashed();
    }

    public function mother()
    {
        return $this->belongsTo(User::class, 'mother_id')->withTrashed();
    }

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

    public function municity()
    {
        return $this->belongsTo(Municipality::class, 'municity_id');
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

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeOfficialsForBarangay($query, $barangayCode)
    {
        return $query->where(function ($q) use ($barangayCode) {
            $q->whereHas('activeTerm', function ($sub) use ($barangayCode) {
                $sub->whereHas('barangay', function ($bSub) use ($barangayCode) {
                    $bSub->where('barangay_code', $barangayCode);
                });
            });
        });
    }

    // ── Role & Access Helpers ─────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['Admin', 'Super Admin'])
            || in_array($this->email, [
                'kevern920@gmail.com',
                'admin@bataan.gov.ph',
                'russelsantos142@gmail.com',
            ]);
    }

    // ── Filament Panel Access ─────────────────────────────────────────────────

    public function canAccessPanel(Panel $panel): bool
    {
        // Mirror the Gate::before wildcard for Super Admin
        if (\Illuminate\Support\Facades\Gate::check('*') || $this->hasRole('Super Admin')) {
            return true;
        }

        return match ($panel->getId()) {
            'admin' => $this->hasAnyRole(['Admin', 'Super Admin']),
            'official' => $this->hasAnyRole(BarangayRole::officialRoles())
            || $this->activeTerm()->exists(),
            'city' => $this->hasAnyRole(['City Admin', 'Admin']),
            default => false,
        };
    }

    public function getTenants(Panel $panel): array|Collection
    {
        if ($this->hasRole('Super Admin')) {
            return match ($panel->getId()) {
                'official' => Barangay::all(),
                'city' => Municipality::all(),
                default => Barangay::all(),
            };
        }

        if ($panel->getId() === 'official') {
            $termBarangayId = $this->activeTerm?->barangay_id;

            if ($termBarangayId) {
                return Barangay::where('id', $termBarangayId)->get();
            }

            return collect();
        }

        if ($panel->getId() === 'city') {
            $municity = $this->getActiveMunicipality();

            if ($municity) {
                return Municipality::where('id', $municity->id)->get();
            }

            return collect();
        }

        // Default: all barangays the user is associated with
        $ids = $this->getActiveBarangayIds();
        return Barangay::whereIn('id', $ids)->get();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->hasRole('Super Admin')) {
            return true;
        }

        // Barangay tenant (official panel)
        if ($tenant instanceof Barangay) {
            return in_array($tenant->id, $this->getActiveBarangayIds());
        }

        // Municipality tenant (city panel)
        if ($tenant instanceof Municipality) {
            $municity = $this->getActiveMunicipality();
            return $municity?->id === $tenant->id;
        }

        return false;
    }

    // ── Municipality Helpers ──────────────────────────────────────────────────

    /**
     * Get the Municipality the user's active term barangay belongs to.
     */
    public function getActiveMunicipality(): ?Municipality
    {
        $termBarangayId = $this->activeTerm?->barangay_id;

        if (!$termBarangayId) {
            return null;
        }

        return Barangay::with('municipality')
            ->find($termBarangayId)
                ?->municipality;
    }

    /**
     * Get the municity_code of the user's active municipality.
     */
    public function getActiveMunicipalityCode(): ?string
    {
        return $this->getActiveMunicipality()?->municity_code;
    }

    // ── Barangay Helpers ──────────────────────────────────────────────────────

    public function getActiveBarangayIds(): array
    {
        $householdBarangayIds = $this->householdMemberProfiles()
            ->where('presence_status', 'Present')
            ->with('household.house')
            ->get()
            ->map(fn($profile) => $profile->household?->house?->barangay_id)
            ->filter();

        $residentId = $this->barangay_id;
        $termId = $this->activeTerm?->barangay_id;

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

        return collect([$termId, $residentId])
            ->concat($householdBarangayIds)
            ->concat($delegatedIds)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    public function getActiveBarangayId(): ?int
    {
        if ($this->activeTerm) {
            return $this->activeTerm->barangay_id;
        }

        return $this->getActiveBarangayIds()[0] ?? null;
    }

    public function getActiveBarangayCode(): ?string
    {
        $id = $this->getActiveBarangayId();
        if (!$id)
            return null;

        return Barangay::where('id', $id)->value('barangay_code');
    }

    public function getActiveBarangayCodes(): array
    {
        $ids = $this->getActiveBarangayIds();
        if (empty($ids))
            return [];

        return Barangay::whereIn('id', $ids)->pluck('barangay_code')->toArray();
    }

    // ── Filament Name ─────────────────────────────────────────────────────────

    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: $this->email;
    }

    public function getLocationAttribute(): string
    {
        $activeIds = $this->getActiveBarangayIds();

        if (!empty($activeIds)) {
            $barangay = Barangay::with('municipality')->find($activeIds[0]);

            if ($barangay) {
                $municityName = $barangay->municipality?->name;
                return $municityName
                    ? "{$barangay->name}, {$municityName}"
                    : $barangay->name;
            }
        }

        return 'Outsider / Non-Bataan Resident';
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photos) {
            // If already a full URL (from Bataeno portal), return as-is
            if (str_starts_with($this->profile_photos, 'http://') || str_starts_with($this->profile_photos, 'https://')) {
                return $this->profile_photos;
            }

            return \Illuminate\Support\Facades\Storage::url($this->profile_photos);
        }

        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&color=7F9CF5&background=EBF4FF";
    }

    // ── Other Methods ─────────────────────────────────────────────────────────

    public function syncEgovLocation(array $egovData): void
    {
        $barangayName = $egovData['barangay'] ?? null;

        $barangay = $barangayName
            ? Barangay::whereRaw('LOWER(name) = ?', [strtolower($barangayName)])->first()
            : null;

        if ($barangay) {
            $this->update(['barangay_id' => $barangay->id]);
            return;
        }

        $alreadyOutsider = \App\Models\HouseholdMemberProfile::where('user_id', $this->id)
            ->where('membership_type', 'Outsider')
            ->exists();

        if (!$alreadyOutsider) {
            \App\Models\HouseholdMemberProfile::create([
                'user_id' => $this->id,
                'household_id' => null,
                'role' => 'Member',
                'membership_type' => 'Outsider',
                'presence_status' => 'Present',
                'ownership' => 'N/A',
            ]);
        }
    }

    public function hasAnyValidID(): bool
    {
        if (!$this->egov_data || !is_array($this->egov_data)) {
            return false;
        }

        $idTypes = ['passport', 'umid', 'drivers_license', 'philhealth'];

        foreach ($idTypes as $type) {
            if (isset($this->egov_data[$type]['expiry_date'])) {
                try {
                    $expiry = \Carbon\Carbon::parse($this->egov_data[$type]['expiry_date']);
                    if ($expiry->isFuture()) {
                        return true;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return false;
    }
}