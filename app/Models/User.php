<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'family_id',
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

    public function householdMemberProfiles()
    {
        return $this->hasMany(HouseholdMemberProfile::class, 'user_id');
    }

    public function activeTerm()
    {
        return $this->hasOne(BarangayTerm::class, 'user_id')
            ->where('ended_at', '>=', now());
    }

    public function isOfficial()
    {
        return $this->activeTerm()->exists();
    }

    public function getActiveBarangayIds(): array
    {
        // 1. Get IDs from Household memberships (Crawl: Profile -> Household -> House)
        $householdBarangayIds = $this->householdMemberProfiles()
            ->whereNull('ended_at')
            ->with('household.house') // Eager load to avoid N+1 and handle the "crawl"
            ->orderByRaw("CASE 
            WHEN membership_type = 'primary' THEN 1 
            WHEN membership_type = 'transient' THEN 2 
            WHEN membership_type = 'associate' THEN 3 
            ELSE 4 END ASC")
            ->get()
            ->map(fn($profile) => $profile->household?->house?->barangay_id)
            ->filter();

        // 2. Get ID from Official Term
        $termBarangayId = $this->activeTerm?->barangay_id;

        // 3. Merge, unique, and reset keys
        return collect([$termBarangayId])
            ->concat($householdBarangayIds)
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

        // Priority 2: Primary Residence (First active household found)
        return $this->getActiveBarangayIds()[0] ?? null;
    }
}
