# Household Profiles & Residency Management Implementation

## Overview

The Household Profiles system allows residents to manage their place of residence, switch between multiple households (e.g., for students or transient workers), and enables Household Heads to invite new members. Official verification is maintained through a Request-Approval workflow handled by Barangay Officials.

---

## 1. Data Architecture

### Core Models & Relationships

- **House**: Physical structure representing an address (Street, Barangay, Municipality).
- **Household**: An economic unit within a House. One House can contain multiple Households.
- **HouseholdMemberProfile**: A pivot-like model linking a `User` to a `Household`.
    - Fields: `role` (Head, Spouse, Member), `presence_status` (Present, Absent, Away), `membership_type`.
- **ResidencyRequest**: Temporary storage for residency applications pending Official approval.

### Database Schema Highlights

- `households.household_head_id`: References the `HouseholdMemberProfile` ID of the primary contact.
- `residency_requests.household_id`: Nullable. If present, indicates a request to join an **existing** household. If null, indicates a request for a **new** household/house.

---

## 2. Resident Experience (Livewire)

### Profile Management (`HouseholdProfiles.php`)

The resident dashboard allows users to:

1. **View Active Residences**: Displays all houses the user is associated with.
2. **Switch Presence**: A one-click action to toggle which household is currently "Active". This automatically updates the user's `barangay_id` and primary location context.
3. **Register New Residence**: A multi-step form to declare a new address.
4. **Join Existing Residence**: A searchable dropdown to find established households within a selected Barangay.

### Household Head Actions

Users designated as "Head" (checked via `role === 'Head'` or `household_head_id`) see a special **"Add Member"** button:

- **Search**: Debounced searching for existing residents by name.
- **Invite**: Submits a `ResidencyRequest` on behalf of the invited user. The address and ownership details are automatically inherited from the Head's household.

---

## 3. Official Workflow (Filament)

### Review & Approval (`ResidencyRequestResource.php`)

Officials review incoming requests. Upon approval:

1. **Existing Household**: If `household_id` is provided, the user is linked to that specific household.
2. **New Household**: If `household_id` is null, the system automatically creates a new `House` and `Household` record.
3. **Head Designation**: If the approved role is "Head", the `households.household_head_id` is updated to point to the new member.
4. **Resident Sync**: The user's primary `barangay_id` is synced to the new location.

---

## 4. Security & Logic

- **Role Scoping**: Only Household Heads can trigger the invitation logic.
- **Presence Mutex**: Users can have multiple profiles, but only **one** can be marked as "Present" at a time to ensure data integrity for census and document requests.
- **Address Inheritance**: Invitation requests copy address data directly from the Head's house to prevent entry errors.

---

## 5. Future Considerations

- **Automatic Leaving**: Notifying the previous household when a member becomes "Present" in a new one.
- **Dependent Management**: Allowing Heads to update the economic status or profile details of their dependents.
- **Document Scoping**: Restricting certain document requests (like Barangay Clearance) to only be available if the user is "Present" in that specific Barangay.
