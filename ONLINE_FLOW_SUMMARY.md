# Online Document Request Flow - Test Summary

## Overview

Successfully implemented and tested the online self-service document request flow where a resident submits their own document request through the web portal and a barangay official approves it.

## Flow Steps

### 1. **Setup Phase**

- **Barangay**: Test barangay created
- **Resident**: Citizen with active household membership (establishes barangay jurisdiction)
    - Lives in a house within the barangay
    - Has household member profile (primary membership)
    - Presence status: "Present"
- **Captain**: Barangay official with signing authority
- **Document Type**: Barangay Clearance with requirements (Valid ID)

### 2. **Request Creation (Online/Self-Service)**

**Actor**: Resident (submitting for themselves)

**Endpoint**: `POST /api/barangay/{barangay_id}/documents/request`

**Payload**:

```json
{
    "document_type_id": 1,
    "request_origin": "web"
    // Note: No requester_id needed - system uses auth()->id()
}
```

**Controller Logic** (`DocumentController::request`):

- Validates that document type exists and has requirements configured
- Automatically uses authenticated user as requester (`auth()->id()`)
- Derives barangay context from user's active household membership
- Creates transaction with status "pending"
- Returns list of requirements to fulfill

**Key Difference from Walk-In**:

- `requester_id` is **NOT** provided (defaults to authenticated user)
- Resident must be authenticated
- System automatically determines barangay from user's household profile

**Result**: Transaction created successfully (HTTP 201)

**Response**:

```json
{
    "message": "Request submitted successfully.",
    "transaction_id": 123,
    "requirements": [
        {
            "id": 1,
            "requirement_name": "Valid ID",
            "data_type": "file",
            "description": "National ID"
        }
    ]
}
```

### 3. **Requirement Fulfillment** (Not yet implemented in test)

**Actor**: Resident

**Expected Flow**:

- Upload required documents (Valid ID, etc.)
- System creates `TransactionRequirement` records
- Files stored with verification status

### 4. **Document Signing**

**Actor**: Captain (or delegated official)

**Endpoint**: `PATCH /api/barangay/{barangay_id}/documents/{transaction_id}/sign`

**Authorization Checks** (`GovernanceService::canSign`):

1. ✅ Transaction exists
2. ✅ Official belongs to same barangay as transaction
3. ✅ Official has active term (not expired)
4. ✅ Official is Captain OR has delegation for this document type

**Controller Logic** (`DocumentController::sign`):

- Uses database transaction with row-level locking (`lockForUpdate()`)
- Prevents concurrent modifications
- Checks if document already issued (idempotency)
- Validates signing authority via `GovernanceService`
- Updates transaction:
    - `status`: "issued"
    - `approver_id`: Captain's term ID
    - `issued_at`: Current timestamp
    - `signing_capacity`: "Captain"
    - `checksum`: Generated hash for verification

**Result**: Document signed and issued (HTTP 200)

### 5. **Final State**

- Transaction status: "issued"
- Requester: Resident (self)
- Approver: Captain
- Document ready for download/pickup

## Key Features Demonstrated

### 1. **Self-Service Portal**

- Residents can request documents themselves
- No need to visit barangay office
- Authenticated session required
- Automatic barangay detection from household membership

### 2. **Jurisdiction Detection**

The system automatically determines the resident's barangay through the "crawl":

```
User → HouseholdMemberProfile → Household → House → Barangay
```

**User Model Method** (`getActiveBarangayId()`):

```php
// Priority 1: Official Seat (if user is an official)
if ($this->activeTerm) {
    return $this->activeTerm->barangay_id;
}

// Priority 2: Primary Residence
return $this->getActiveBarangayIds()[0] ?? null;
```

### 3. **Security & Validation**

#### Pre-Request Validation:

- ✅ Document type must exist
- ✅ Document type must have requirements configured
- ✅ User must be authenticated

#### Pre-Signing Validation:

- ✅ Transaction must exist
- ✅ Transaction must belong to official's barangay (jurisdictional check)
- ✅ Official must have active term
- ✅ Official must be Captain OR have delegation
- ✅ Document must not already be issued

#### Concurrency Protection:

- Database row locking prevents race conditions
- Multiple officials cannot sign simultaneously
- Prevents double-signing

### 4. **Audit Trail**

```
requester_id: 123 (Resident - self)
approver_id: 456 (Captain's Term)
request_origin: "web"
status: "issued"
issued_at: "2026-02-05 07:35:00"
checksum: "a1b2c3d4..."
barangay_id: 1
```

## Test Assertions (DocumentFlowTest)

✅ Request created with HTTP 201
✅ Transaction ID returned
✅ Status is "pending"
✅ Requester ID matches resident
✅ Captain can sign successfully (HTTP 200)
✅ Status changed to "issued"
✅ Approver ID matches captain's term
✅ Checksum generated

## Governance Service Tests

### Test: Captain Has Automatic Authority

```php
test_captain_has_automatic_authority()
```

- Captain can sign any document type without delegation
- No need for explicit permission grants
- Position-based authority

### Test: Delegated Official Can Sign

```php
test_delegated_official_can_sign()
```

- Secretary receives delegation from Captain
- Delegation is document-type specific
- Delegation has expiration date
- Secretary can sign while delegation is active

### Test: Expired Delegation Cannot Sign

```php
test_expired_delegation_cannot_sign()
```

- Delegation with `expires_at` in the past is rejected
- System checks delegation validity on every sign attempt
- Prevents use of outdated permissions

### Test: Captain Term Expired

```php
test_captain_term_expired()
```

- Even Captains cannot sign if their term has ended
- `ended_at` field is checked
- Ensures only current officials can sign

### Test: Cross-Barangay Signing Prevented

```php
test_official_signing_for_different_barangay()
```

- Captain of Barangay A cannot sign for Barangay B
- Jurisdictional boundary enforcement
- Critical security feature

## Comparison: Online vs Walk-In

| Aspect                 | Online Request            | Walk-In Request               |
| ---------------------- | ------------------------- | ----------------------------- |
| **Requester**          | Self (authenticated user) | Specified by official         |
| **requester_id**       | Auto (`auth()->id()`)     | Provided in payload           |
| **Authentication**     | Resident logs in          | Official logs in              |
| **Barangay Detection** | From resident's household | From official's term          |
| **Use Case**           | Convenience, 24/7 access  | Immediate service, assistance |
| **Actor**              | Resident → Captain        | Resident → Official → Captain |

## Files Involved

1. `app/Http/Controllers/DocumentController.php` - Request and sign endpoints
2. `app/Services/GovernanceService.php` - Authorization logic
3. `app/Models/User.php` - Barangay detection via household crawl
4. `tests/Feature/DocumentFlowTest.php` - End-to-end online flow test
5. `tests/Feature/GovernanceServiceTest.php` - Authorization unit tests

## Security Highlights

### 1. **Middleware Protection**

```php
Route::middleware(['auth:sanctum', EnsureUserBelongsToBarangay::class])
```

- Ensures user is authenticated
- Validates user belongs to the barangay in the URL
- Prevents cross-barangay access

### 2. **Row-Level Locking**

```php
DocumentTransaction::where('barangay_id', $barangay_id)
    ->lockForUpdate()
    ->findOrFail($id);
```

- Prevents concurrent modifications
- Ensures data consistency
- Avoids race conditions

### 3. **State Guards**

```php
if ($transaction->status === 'issued') {
    abort(403, "Document is already issued.");
}
```

- Prevents re-signing
- Maintains document integrity
- Clear error messages

### 4. **Checksum Generation**

```php
'checksum' => bin2hex(random_bytes(16))
```

- Unique identifier for each issued document
- Enables verification
- Prevents forgery

## Next Steps (Potential Enhancements)

- [ ] Implement requirement upload endpoint
- [ ] Add requirement verification workflow
- [ ] Implement payment/fee collection
- [ ] Add email/SMS notifications
- [ ] Generate PDF certificates with QR codes
- [ ] Add document download endpoint
- [ ] Implement document verification API (public)
- [ ] Add analytics dashboard for officials
- [ ] Implement document expiration tracking
- [ ] Add renewal workflow for expired documents
