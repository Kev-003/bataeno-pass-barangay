<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Family;

class UserObserver
{
    /**
     * Handle the User "saved" event.
     */
    public function saved(User $user): void
    {
        // 1. If parents are newly assigned, ensure a nuclear family unit exists.
        if ($user->father_id && $user->mother_id) {
            $this->ensureNuclearFamily($user);
        }

        // 2. Trigger family cleanup based on current user state
        $this->cleanupUserFamilies($user);
    }

    /**
     * Handle the User "deleted" event (soft-delete).
     */
    public function deleted(User $user): void
    {
        $this->cleanupUserFamilies($user);
    }

    /**
     * Comprehensive cleanup logic based on user's conditions.
     */
    private function cleanupUserFamilies(User $user): void
    {
        // Check families where this user was a parent
        $parentedFamilies = Family::where('father_id', $user->id)
            ->orWhere('mother_id', $user->id)
            ->get();

        // Check the family they actually currently belong to
        $currentFamily = $user->family_id ? Family::find($user->family_id) : null;

        // Process these collections
        foreach ($parentedFamilies as $family) {
            $this->processFamilyDissolution($family);
        }

        if ($currentFamily) {
            $this->processFamilyDissolution($currentFamily);
        }

        // Also check any family that might now be empty globally (periodic or triggered)
        // To be safe and efficient, we only delete empty families that were orphaned by this user.
        if ($user->wasChanged('family_id')) {
            $oldFamilyId = $user->getOriginal('family_id');
            if ($oldFamilyId) {
                $oldFamily = Family::find($oldFamilyId);
                if ($oldFamily)
                    $this->processFamilyDissolution($oldFamily);
            }
        }
    }

    /**
     * Check and process family dissolution based on specific conditions.
     */
    private function processFamilyDissolution(Family $family): void
    {
        // Load the relationship properly to avoid trashed() issues on generic objects
        $family->load(['father', 'mother']);

        // Get living members (those actually assigned to this family ID)
        $livingMembers = User::where('family_id', $family->id)->get();

        // 1. Condition 3: A family with no members must be deleted.
        if ($livingMembers->isEmpty()) {
            $family->delete();
            return;
        }

        // 2. Condition 1 & 1.2: Both parents are dead
        $fatherIsDead = !$family->father_id || ($family->father && $family->father->trashed());
        $motherIsDead = !$family->mother_id || ($family->mother && $family->mother->trashed());
        $bothParentsDead = $fatherIsDead && $motherIsDead;

        if ($bothParentsDead) {
            // Condition 1.1: All biological children already married/left into another family unit
            $allChildrenLeft = !User::where('family_id', $family->id)
                ->where(function ($q) use ($family) {
                    $q->where('father_id', $family->father_id)
                        ->orWhere('mother_id', $family->mother_id);
                })
                ->exists();

            if ($allChildrenLeft) {
                // Dissolve the family. 
                // Any stray members (unlikely) should be unlinked before deletion.
                User::where('family_id', $family->id)->update(['family_id' => null]);
                $family->delete();
                return;
            }
        }

        // 3. Special Case: Lone surviving parent (Condition 2)
        if ($livingMembers->count() === 1) {
            $loneMember = $livingMembers->first();
            $isParent = $loneMember->id === $family->father_id || $loneMember->id === $family->mother_id;

            if ($isParent) {
                // They are allowed to be alone (Condition 2.1). 
            }
        }
    }

    /**
     * Ensure a nuclear family unit exists for the user and their parents.
     */
    private function ensureNuclearFamily(User $child): void
    {
        $fatherId = $child->father_id;
        $motherId = $child->mother_id;

        // Try to find an existing family unit for this specific parent combination
        $family = Family::where('father_id', $fatherId)
            ->where('mother_id', $motherId)
            ->first();

        if (!$family) {
            $father = User::withTrashed()->find($fatherId);
            $familyName = $father ? $father->last_name . " Family" : "New Family";

            $family = Family::create([
                'family_name' => $familyName,
                'father_id' => $fatherId,
                'mother_id' => $motherId,
                'barangay_id' => $child->barangay_id ?? ($father ? $father->barangay_id : null),
            ]);
        }

        // Assign the child to this nuclear family if they aren't married yet
        if ($child->civil_status !== 'Married' && $child->family_id !== $family->id) {
            $child->family_id = $family->id;
            $child->saveQuietly();
        }

        // Ensure parents belong to this family unit (Condition 2.2 support)
        $parents = User::withTrashed()->whereIn('id', [$fatherId, $motherId])->get();
        foreach ($parents as $parent) {
            if ($parent->family_id !== $family->id) {
                $parent->family_id = $family->id;
                $parent->saveQuietly();
            }
        }
    }
}
