<?php

namespace App\Livewire;

use App\Models\HouseholdMemberProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class HouseholdProfiles extends Component
{
    public $showRequestModal = false;
    public $showAddMemberModal = false;
    public $selected_household_id;
    public $search_user_query = '';
    public $target_user_id;

    public $municipality_id;
    public $barangay_id;

    public $request_type = 'new'; // 'new' or 'join'
    public $household_id;

    public function openAddMemberModal($householdId)
    {
        $this->selected_household_id = $householdId;
        $this->showAddMemberModal = true;
        $this->search_user_query = '';
        $this->target_user_id = null;
    }

    public function selectUser($userId)
    {
        $this->target_user_id = $userId;
    }

    public function inviteMember()
    {
        $this->validate([
            'target_user_id' => 'required|exists:users,id',
            'selected_household_id' => 'required|exists:households,id',
        ]);

        // Security check: Ensure current user is the head of the target household
        $household = \App\Models\Household::find($this->selected_household_id);
        $isHead = \App\Models\HouseholdMemberProfile::where('user_id', \Auth::id())
            ->where('household_id', $this->selected_household_id)
            ->where(function ($q) use ($household) {
                $q->where('role', 'Head');
                if ($household && $household->household_head_id) {
                    $q->orWhere('id', $household->household_head_id);
                }
            })
            ->exists();

        if (!$isHead) {
            abort(403, 'Unauthorized action. Only the household head can add members.');
        }

        $household = \App\Models\Household::with('house')->find($this->selected_household_id);

        \App\Models\ResidencyRequest::create([
            'user_id' => $this->target_user_id,
            'barangay_id' => $household->house->barangay_id,
            'household_id' => $household->id,
            'housing_unit' => $household->house->housing_unit,
            'street' => $household->house->street,
            'subdivision' => $household->house->subdivision,
            'role' => 'Member', // Non-head default
            'membership_type' => 'Resident',
            'ownership' => $household->ownership,
            'status' => 'Pending',
        ]);

        $this->showAddMemberModal = false;
        $this->reset(['target_user_id', 'search_user_query', 'selected_household_id']);
        session()->flash('success', 'Residency request for the new member has been submitted for official verification.');
    }

    public $housing_unit;
    public $street;
    public $subdivision;
    public $role = 'Member';
    public $membership_type = 'Resident';
    public $ownership = 'Owned';

    public function updatedMunicipalityId()
    {
        $this->barangay_id = null;
        $this->household_id = null;
    }

    public function updatedBarangayId()
    {
        $this->household_id = null;
    }

    public function submitRequest()
    {
        $rules = [
            'barangay_id' => 'required|exists:barangays,id',
            'role' => 'required|string',
            'ownership' => 'required|string',
        ];

        if ($this->request_type === 'new') {
            $rules['street'] = 'required|string|max:255';
            // Assuming subdivision and housing_unit are also required for 'new' based on original code,
            // but the instruction only specified 'street' for validation.
            // Adding them here to maintain similar validation logic for 'new' requests.
            $rules['subdivision'] = 'required|string|max:255';
            $rules['housing_unit'] = 'nullable|string|max:255'; // Original didn't validate housing_unit, making it nullable here.
        } else {
            $rules['household_id'] = 'required|exists:households,id';
        }

        $this->validate($rules);

        $data = [
            'user_id' => Auth::id(),
            'barangay_id' => $this->barangay_id,
            'household_id' => $this->request_type === 'join' ? $this->household_id : null,
            'role' => $this->role,
            'membership_type' => $this->membership_type,
            'ownership' => $this->ownership,
            'status' => 'Pending',
        ];

        if ($this->request_type === 'new') {
            $data['housing_unit'] = $this->housing_unit;
            $data['street'] = $this->street;
            $data['subdivision'] = $this->subdivision;
        } else {
            // Inherit address from household
            $h = \App\Models\Household::with('house')->find($this->household_id);
            $data['housing_unit'] = $h->house->housing_unit;
            $data['street'] = $h->house->street;
            $data['subdivision'] = $h->house->subdivision;
        }

        \App\Models\ResidencyRequest::create($data);

        $this->reset(['barangay_id', 'household_id', 'housing_unit', 'street', 'subdivision', 'role', 'membership_type', 'ownership', 'showRequestModal', 'request_type']);
        session()->flash('success', 'Your residency request has been submitted and is pending verification.');
    }

    public function switchPresence($profileId)
    {
        $user = Auth::user();

        // 1. Set all other profiles for this user to 'Absent' (or similar status)
        HouseholdMemberProfile::where('user_id', $user->id)
            ->where('id', '!=', $profileId)
            ->update(['presence_status' => 'Absent']);

        // 2. Set the target profile to 'Present'
        $activeProfile = HouseholdMemberProfile::findOrFail($profileId);
        $activeProfile->update(['presence_status' => 'Present']);

        // 3. Update user's primary barangay_id based on this household
// household_id -> houses -> barangay_id
        $barangayId = $activeProfile->household->house->barangay_id ?? null;

        if ($barangayId) {
            $user->update(['barangay_id' => $barangayId]);
        }

        session()->flash('success', 'Presence status updated and residency synced.');
    }

    public function render()
    {
        $profiles = HouseholdMemberProfile::where('user_id', Auth::id())
            ->with(['household.house.linkedBarangay'])
            ->get()
            ->sortByDesc(fn($p) => $p->presence_status === 'Present');

        $pendingRequests = \App\Models\ResidencyRequest::where('user_id', Auth::id())
            ->where('status', '!=', 'Approved')
            ->with('barangay')
            ->orderByDesc('created_at')
            ->get();

        $municipalities = \App\Models\Municipality::orderBy('name')->get();

        $barangays = \App\Models\Barangay::when($this->municipality_id, function ($query) {
            $query->where('municity_code', $this->municipality_id);
        })
            ->orderBy('name')
            ->get();

        $households = [];
        if ($this->barangay_id && $this->request_type === 'join') {
            $households = \App\Models\Household::whereHas('house', function ($q) {
                $q->where('barangay_id', $this->barangay_id);
            })
                ->with('house', 'headOfHousehold.user')
                ->get();
        }

        $searchResults = [];
        if (strlen($this->search_user_query) >= 3) {
            $searchResults = \App\Models\User::where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search_user_query . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search_user_query . '%');
            })
                ->where('id', '!=', Auth::id())
                ->whereDoesntHave('householdMemberProfiles', function ($q) {
                    $q->where('household_id', $this->selected_household_id);
                })
                ->limit(5)
                ->get();
        }

        return view('livewire.household-profiles', [
            'profiles' => $profiles,
            'pendingRequests' => $pendingRequests,
            'barangays' => $barangays,
            'municipalities' => $municipalities,
            'households' => $households,
            'searchResults' => $searchResults,
        ]);
    }
}