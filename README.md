<p align="center"><img src="public/bataeno_pass_logo.svg" width="200" alt="Bataeño Pass Logo"></p>
<h1 align="center" class="text-8xl text-white">
                Bataeño Pass
            </h1>

# Barangay-Level Implementation of Bataeño Pass

## Final Project Documentation

**Authors:**

- Kevern Joebert C. Angeles
- Russel Matthew F. Santos

**Organization:** Provincial Government of Bataan — PITO

**Internship Period:** February 2, 2026 – March 10, 2026

**Technology Officers:**

- Mr. Paolo Nuestro
- Mr. Nixon Somoza
- Mr. Bryan Gonzales

**ERD Reference:** https://dbdiagram.io/d/ERDBrgy-697aaeb0bd82f5fce2f3aac1

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [System Architecture](#2-system-architecture)
3. [Feature Specifications](#3-feature-specifications)
4. [User Story Mapping](#4-user-story-mapping)
5. [Process Implementation & Objectives](#5-process-implementation--objectives)
6. [Constraints](#6-constraints)
7. [Data Dictionary](#7-data-dictionary)
8. [Key Design Decisions](#8-key-design-decisions)
9. [Development Changelog](#9-development-changelog)
10. [Impact & Next Steps](#10-impact--next-steps)

---

## 1. Project Overview

### Scope

The **Barangay Module** implements the **Bataeño Pass** at the barangay level across Bataan. It serves as a centralized platform where residents securely submit personal information and required documents (such as barangay indigency certificates or valid IDs), which are reviewed and approved by authorized barangay officials. Once verified, the Bataeño Pass allows residents to easily access public services, while enabling barangay administrations to efficiently manage resident records, improve security, and ensure that benefits and services are granted only to qualified constituents.

### Non-Functional Requirements

The system shall ensure the **security and confidentiality** of user data by implementing proper access control and encryption. It shall comply with the **Data Privacy Act of 2012 (RA 10173)** to protect personal information. The system shall provide **reliable and efficient performance**, with minimal response time during normal operation. It shall be **available at all times**, except during scheduled maintenance. The system shall be **user-friendly and compatible** with common devices and web browsers to accommodate both residents and barangay officials.

### Technology Stack

| Layer                 | Technology                             |
| --------------------- | -------------------------------------- |
| Backend Framework     | Laravel (PHP)                          |
| Frontend/UI           | Laravel Livewire, Filament Admin       |
| Real-Time             | Laravel Reverb (WebSockets)            |
| PDF Generation        | Spatie Browsershot + Puppeteer         |
| Authentication        | eGovPH / Bataeño Pass SSO (OAuth)      |
| Role & Permissions    | Spatie Laravel Permission              |
| Queue/Jobs            | Laravel Queue (database driver)        |
| Debugging             | Laravel Telescope                      |
| Lineage Visualization | D3.js (SVG-based, Alpine.js component) |
| Physical Card Support | NFC (PhilID QR & NFC tap)              |
| Local Dev             | OrbStack                               |

---

## 2. System Architecture

### High-Level Architecture Overview

The Barangay Module is a **vertical extension** on the existing Bataeño Pass ecosystem. Core identity services — basic information and demographic data — are leveraged in isolated logic for:

- Barangay-level document processing
- Household relationships
- Barangay official term management

### Component Interactions

#### Existing Bataeño Pass Core

- **Role:** Handles authentication (Registration via eGovPH SSO)
- **Integration:** The Barangay Module trusts the core authentication and does not use a separate login for residents.

#### Barangay Module (New)

- **Role:** Manages logic for household relationships, certificate and form requests, and barangay official terms.

### Infrastructure and Access Control

The Barangay Module uses two interfaces to support distinct groups of users: **Residents** and **Officials**.

| Role               | Permissions                                                                                    | Access                                          |
| ------------------ | ---------------------------------------------------------------------------------------------- | ----------------------------------------------- |
| Residents          | Request documents, view my documents, view my profile                                          | Resident portal (Livewire)                      |
| Barangay Officials | Read/write barangay data; read/write barangay-level resident data; read-only for higher levels | Official Filament panel with elevated RBAC      |
| City Admin         | Read-only, municipality-scoped view across all barangays                                       | City Admin Filament panel at /city-admin        |
| Super Admin        | Global access to all panels and tenants                                                        | All panels, bypasses Gate::before tenancy guard |

### Multi-Tenancy Design

The system is built on a multi-tenant architecture using `barangay_code` (then migrated to `barangay_id`) as the tenant slug. The Official Panel is scoped per barangay, while the City Admin Panel is scoped per municipality (`municity_code`).

### Document Storage (User-Centric)

To ensure residents maintain access to their historical documents even after moving barangays, file storage uses a user-centric hierarchy:

```
storage/app/generated/{user_id}/{barangay_code}/{doc_slug}/{transaction_id}_signed.pdf
```

**Access control levels:**

- **Residents** — Can download any document where they are the `requester_id`.
- **Officials** — Can download documents issued by their own barangay.
- **Admins** — Have global download permissions for audit and support.

### Real-Time Notifications

Laravel Reverb provides WebSocket broadcasting over private channels. The `barangay.{barangayCode}.requests` channel authorizes officials when a new document request is created. Broadcasting channel authorization is cached via `Cache::remember()` with a 15-minute TTL to prevent repeated database hits on every WebSocket reconnect.

### Queue Jobs for Document Processing

Document request creation and approval are offloaded to asynchronous Laravel queue jobs using the database driver:

- **ProcessDocumentRequest** — Handles official notifications, resident confirmation, and `DocumentRequestCreated` broadcast. Dispatched via `->afterCommit()`.
- **ProcessDocumentApproval** — Wraps `DocumentApprovalService::generateAndSign()` including Browsershot PDF generation, file storage, transaction status update, and `DocumentIssuedNotification`. Configured with a 120-second timeout and 3 retries with 10-second backoff. On failure, reverts transaction status back to `pending` so the official can retry.

---

## 3. Feature Specifications

### Feature 1: Resident Certificate/Form Request

**Summary:** Enables residents to digitally apply for necessary barangay documents (e.g., Certificate of Indigency, Residency, or First-Time Jobseeker) directly through the mobile app or web portal.

**Input:** Document type selection, purpose of request, and any required supporting file uploads (e.g., photo of a bill for residency proof, any existing valid ID).

**Process:** System verifies the resident's status and confirms they are linked to the correct barangay. It checks if a similar active document already exists to prevent duplicate requests.

**Output:** A new record in the `document_transactions` table with a `Pending` status.

**UI/UX Flow:** Dashboard → "Request Document" button → Form selection → Success confirmation with a "Tracking ID."

**Edge Cases:**

- What if the user isn't verified? The system prompts the user to complete their eGovPH registration first.
- What if the purpose is "Other"? A mandatory text field appears for the user to specify their reason.

---

### Feature 2: Official-Led Resident Registration

**Summary:** Allows barangay officials to assist residents who may have difficulty with technology in registering for the Bataeño Pass, ensuring inclusive coverage.

**Target Users:** Barangay Secretary, Barangay Enrollment Staff.

**Input:** Resident's demographic data, contact information, and eGovPH credentials or National ID details.

**Process:** The official initiates a sync with the eGovPH platform to verify identity. Walk-in registration supports PhilID QR scanning and NFC card tapping, allowing officials to pull resident data without manual entry. A "Double-Guard" duplicate detection blocks duplicate registrations via both UI (disabling the Create button) and server-side checks on `uuid` and `email`.

**Output:** A newly created or updated resident profile linked to the official's barangay.

**UI/UX Flow:** Official Portal → "Register Resident" → Scan/Enter ID → Data Review → Confirm Registration.

**Edge Cases:**

- What if the resident already exists? The system displays a "Record Found" message and offers to update existing residency details.

---

### Feature 3: Document Issuance (In-App & Walk-In)

**Summary:** Provides a dashboard for officials to review digital requests or manually input data for walk-in residents to issue signed, QR-coded documents.

**Target Users:** Barangay Captain, Barangay Secretary.

**Input:** For in-app: Approval/Rejection toggle. For walk-in: Resident lookup (via name, NFC tap, or PhilID QR) and document selection.

**Process:** The system performs a **"Triple Match" check** (Auth, Authority, Scope) to ensure the official is currently active in the Officials Directory for that specific barangay. Before generating the PDF, the system performs a **Hierarchy Check:**

1. Is the official the Barangay Captain? (If yes, Proceed as `Native`).
2. If no, is the user a Secretary with an Active Delegation for this `document_type`? (If yes, Proceed as `Acting`).
3. If neither, **Deny Access**.

**Output:** Generated PDF with a digital signature and QR code; status updated to `Active` in `document_transactions`.

**Document Validity (Default V_days):**

| Document Type      | Default Validity | Logic/Requirement                                   |
| ------------------ | ---------------- | --------------------------------------------------- |
| Barangay Clearance | 180 days         | Checks for "No Pending Cases" flag in local records |
| Residency          | 180 days         | Verified against the Household Registry             |
| Indigency          | 90 days          | Requires "Low Income" attribute in the User Cache   |
| Good Moral         | 180 days         | Standard conduct certification                      |
| Business Clearance | 365 days         | Linked to City/Municipal permit cycles              |
| No Objection       | 30–90 days       | Project-specific                                    |

**Edge Cases:**

- What if the official's term has expired? The "Issue" button is disabled, and the system prompts the administrator to update the Officials Directory.
- What happens if a Captain's term ends while a delegation is active? The system automatically voids all active delegations associated with that Captain's `term_id`.

---

### Feature 4: Leadership Terms History Tracking

**Summary:** Manages the frequent changes in barangay positions by maintaining a historical record of all officials and their periods of authority.

**Target Users:** System Administrators, Barangay Captains.

**Input:** User ID, Position Title (e.g., Treasurer), Start Date, and End Date (when applicable).

**Process:** When a new official is added, the system checks if the previous official in that role needs an `end_date` populated. A `barangay_id` cannot have two active "Barangay Captain" terms simultaneously.

**Output:** An audit-ready directory of past and present barangay leadership.

**Database Table:** `barangay_terms` — tracks `start_date`, `end_date`, `barangay_id`, and `position_type`.

**Edge Cases:**

- What if an official is suspended? The system allows for an "Active" term to be marked as `Revoked` or `Suspended` to immediately cut off system access.

---

### Feature 5: Household Clustering & Head Designation

**Summary:** Groups residents based on communal resource sharing (commensality) within physical structures and manages dynamic leadership and residency status across the province.

**Target Users:** Residents, Barangay Officials.

**Key Design Points:**

- **Dynamic Head Model:** Replaces the static `head_user_id` pointer with a role-based system in the `household_member_profile` table.
- **Jurisdictional Sync:** Households inherit their `barangay_id` automatically from the associated `house` record to prevent jurisdictional fraud.
- **Exclusivity Enforcement:** A Partial Unique Index ensures a user holds only one Primary residency status at a time within Bataan.
- **Presence Mutex:** A user may have multiple profiles, but only one can be marked `Present` (Primary) at a time.

**Resident Dashboard (Livewire):**

- View active residences
- Switch presence (one-click to toggle active household, automatically syncing `barangay_id`)
- Register new residence (multi-step form)
- Join existing residence (searchable dropdown)
- Household Head can add members via debounced resident search and ResidencyRequest invitations

**Official Approval Workflow:**
Upon approving a ResidencyRequest, the system automatically:

- Links the user to an existing Household if a `household_id` is provided.
- Creates a new House and Household record if `household_id` is null.
- Updates `households.household_head_id` when the approved role is Head.
- Syncs the user's primary `barangay_id` to their new location.

**Edge Cases:**

- What if a family head dies or moves? An **Atomic Succession** is triggered. The system identifies the `Head` role is being terminated and prompts for a successor to prevent an "Inactive" household status.
- What if a resident stays in a boarding house? The resident is added to the boarding house with a `presence_status` of `Secondary`. Their family home remains their `Primary` record for subsidies, but the Secondary status allows them to request local clearances in the new barangay.
- What if a household is registered in the wrong barangay? The system blocks the entry if the household's physical `structure_id` does not belong to the official's `barangay_id`.
- What if a user tries to be "Primary" in two places? The database rejects the `INSERT` via the `idx_unique_active_primary_resident` constraint, forcing a relocation workflow.

---

### Feature 6: Family Lineage Tracking

**Summary:** Tracks nuclear family relationships and automates family lifecycle management.

**Automated Family Lifecycle (UserObserver):**

- **Creation:** When a resident is assigned both a Father and a Mother, the system automatically creates (or finds) a dedicated family unit for that parent pair.
- **Migration:** Residents are automatically moved into their nuclear family unit when they originate or join one.
- **Dissolution — Empty:** A family record is automatically deleted when it has 0 members.
- **Dissolution — Resolved:** A family record is automatically deleted when both core parents are deceased and all children have married or moved into their own family units.
- **Lone Parent Policy:** A lone surviving parent may remain in their family record.

**Lineage Tree System (D3.js):**

- SVG-based D3.js implementation integrated as an Alpine.js component.
- Complex family JSON passed via hidden script tags to avoid attribute quoting issues.
- Interactive features: mouse-wheel zoom, click-and-drag panning, smooth curved links.
- Visual improvements: highlighted Current User indicator, gender-coded node gradients, strikethrough styling for deceased ancestors.

---

### Feature 7: City Admin Panel

**Summary:** Read-only, municipality-scoped panel for city/municipal-level administrators to view data across all barangays within their jurisdiction.

**Resources (all read-only, all municipality-scoped):**

- BarangaysResource — Barangay list with active captain status badge per barangay.
- OfficialsResource — Barangay terms across all municipality barangays, filterable by active/past terms, barangay, and position.
- ResidentResource — Resident list with barangay column and lineage tree modal.
- FamilyResource — Family list with barangay column and family members modal.
- HouseResource — House list with barangay column and inhabitants modal.
- HouseholdResource — Household list with barangay column and full infolist view modal.

**Panel Scope:** `/city-admin`, scoped to Municipality tenancy using `municity_code` as the tenant slug.

**Performance Optimizations:**

- Eager loading on OfficialsResource (user, barangay, position) to eliminate N+1 queries.
- Municipality barangay ID lookups cached via `Cache::remember()` with a 30-minute TTL.
- Database notifications polling disabled to reduce unnecessary broadcasting auth requests.

---

## 4. User Story Mapping

### Identity and Access

**Goal:** Securely access the platform with verified credentials and role-appropriate access.

- As a **resident**, I want to log in using my eGovPH credentials so that I don't have to create a new account.
- As a **barangay official**, I want my access to be automatically revoked when my term ends so that former officials cannot continue accessing the system.
- As a **system admin**, I want to assign officials to specific barangays so that they only see data relevant to their jurisdiction.

### Document Requests

**Goal:** Enable residents to digitally request barangay documents without needing to visit the barangay hall.

- As a **resident**, I want to request a Certificate of Residency online so that I don't have to travel to the barangay hall during office hours.
- As a **resident**, I want to receive a notification when my document is ready so that I know when to pick it up or download it.
- As a **barangay official**, I want to see a queue of pending document requests so that I can process them efficiently.

### Walk-In Processing

**Goal:** Support residents who are unable to use the digital portal by allowing officials to process requests on their behalf.

- As a **barangay official**, I want to tap a resident's NFC card to pull their profile so that walk-in registration is faster and more accurate.
- As a **barangay official**, I want to issue documents for walk-in residents immediately so that they don't have to wait for the asynchronous queue.

### Household Management

**Goal:** Accurately track where residents live and who leads each household.

- As a **resident**, I want to join an existing household so that my official address is reflected correctly for government services.
- As a **household head**, I want to invite another resident to join my household so that my family is correctly registered.
- As a **barangay official**, I want to see all households in my barangay and their members so that I can manage the residency records.

### Official Term & Delegation

**Goal:** Ensure that document signing authority is always current and legally auditable.

- As a **barangay captain**, I want to delegate signing authority to my secretary when I am unavailable so that barangay services continue uninterrupted.
- As a **barangay captain**, I want delegations to automatically expire when my own term ends so that no unauthorized signing occurs during leadership transitions.

---

## 5. Process Implementation & Objectives

### Objective 1: Multi-Document Issuance Workflow

**Expiry Calculation Formula:**

```
E = I + V_days
```

Where E is Expiry Date, I is Issuance Date, and V_days is the validity constant in days defined per document type.

**Online Request Flow:**

1. **Initiation:** The resident logs in via eGovPH and selects a document from the Barangay Services menu.
2. **Attribute Matching:** System checks the `users` table for eligibility flags (e.g., "Low Income" tag for Indigency).
3. **Queue Placement:** The request is timestamped and placed in the Pending Queue for that specific barangay.
4. **Official Action:** An authorized official (Captain or delegated Secretary) reviews the request and chooses to Approve or Reject.
5. **Generation & Signing:** Upon approval, the system fetches the official's name/title and calculates the expiration date.
6. **Notification:** The resident receives a notification and can view the digitally signed document.

**Walk-In Request Flow:**

1. **Identity Verification:** The resident presents their eGovPH QR or physical National ID. The official scans the ID using the Barangay Module to pull the resident's profile.
2. **Manual Input:** The official selects the document type and enters the purpose on behalf of the resident.
3. **Eligibility Check:** The system automatically runs background checks.
4. **Instant Approval:** Since the official is encoding it, the "Pending" state is skipped. The official clicks "Issue & Print."
5. **Simultaneous Output:** Digital record is pushed to the resident's Digital Vault; a PDF is generated and printed for the resident.

**SQL Implementation: Document Generation**

```sql
INSERT INTO document_transactions (
    transaction_id, requestor_id, approver_id,
    document_type, request_origin, status,
    issued_at, expiry_date
) VALUES (
    gen_random_uuid(), :resident_pcn_hash, :official_term_id,
    :doc_type, :origin,
    CASE WHEN :origin = 'Walk_In' THEN 'Active' ELSE 'Pending' END,
    CASE WHEN :origin = 'Walk_In' THEN CURRENT_TIMESTAMP ELSE NULL END,
    CASE
        WHEN :origin = 'Walk_In'
        THEN CURRENT_TIMESTAMP + (SELECT validity_interval FROM doc_configs WHERE type = :doc_type)
        ELSE NULL
    END
);
```

**Document Hash Generation:**

To prevent forgery, the system generates a unique hash for every issued document. If a third party scans the QR code, the system re-calculates the hash. If the hashes don't match, the document is flagged as **Invalid/Tampered**.

---

### Objective 2: Household Grouping & Head Designation

**Household Head Succession Flow:**

1. **Trigger:** An official receives a report that a Household Head has died, moved out, or resigned.
2. **Verification:** The system checks the eGovPH User Cache for a "Deceased" flag or a "Relocation" request.
3. **Successor Selection:** The official views the active `household_member_profile` list for that `household_id`.
4. **Nomination:** The official selects a qualified member (e.g., spouse or eldest adult child) to assume leadership.
5. **Atomic Update:** A single database transaction:
    - Closes the previous head's profile record (sets `end_date`).
    - Promotes the successor (sets `role = 'Head'`, `start_date = NOW()`).
    - Generates an audit trail record.
    - Prompts the official to review and update `role` or `relation_to_head` for all remaining active members.

**SQL: Enforcing One Primary Home**

```sql
-- Prevents "double-dipping" in provincial subsidies
CREATE UNIQUE INDEX idx_unique_active_primary_resident
ON household_member_profile (user_id)
WHERE presence_status = 'Primary' AND end_date IS NULL;
```

**SQL: Enforcing One Active Head**

```sql
-- Ensures each household has only one active 'Head' role
CREATE UNIQUE INDEX idx_single_active_household_head
ON household_member_profile (household_id)
WHERE role = 'Head' AND end_date IS NULL;
```

**SQL: Jurisdictional Validation**

```sql
-- Check if the house's barangay matches the official's jurisdiction
SELECT hs.barangay_id
FROM house hs
WHERE hs.house_id = :input_structure_id
  AND hs.barangay_id = :official_barangay_id;
-- Result must return 1 row; otherwise, access is denied.
```

---

### Objective 3: Dynamic Barangay Positions (Term Management)

**Delegation Flow:**

1. **Setup:** The Captain goes on leave and uses the app to "Delegate Authority" to the Secretary for N days.
2. **Request:** A resident walks in for a Barangay Clearance.
3. **Authentication:** The Secretary logs in. The system identifies them as "Secretary" in the `barangay_terms` table.
4. **Authority Check:** The system looks for an active record in the `delegations` table matching the Secretary's `term_id` and the document type.
5. **Issuance:** The document is generated. The signature block automatically reads: _"Hon. [Secretary Name], Acting for the Barangay Captain."_

**SQL: Authorization Middleware**

```sql
-- Check if User has direct OR delegated authority
SELECT 1
FROM barangay_terms t
LEFT JOIN delegations d ON t.term_id = d.delegate_term_id
WHERE t.user_id = :current_user_id
  AND t.barangay_id = :resident_barangay_id
  AND t.end_date IS NULL -- Official is currently in office
  AND (
    t.position = 'Barangay Captain' -- Direct Authority
    OR (
      d.document_type = :requested_doc_type -- Delegated Authority
      AND d.expires_at > CURRENT_TIMESTAMP
    )
  );
```

**Document Rendering — Signing Capacity:**

- If `Native`: Displays _"Hon. [Captain Name], Barangay Captain."_
- If `Acting`: Displays _"Hon. [Secretary Name], Barangay Secretary,"_ with a sub-caption: _"By Authority of the Barangay Captain"_ or _"Acting for the Barangay Captain."_

---

## 6. Constraints

These represent the technical and business "guardrails" that the system must operate within to maintain data integrity and legal compliance.

### Primary Residency Constraint

A resident can be linked to multiple households (e.g., family home and boarding house), but only **one** record can be marked as `presence_status = 'Primary'` at any time. This prevents "double-dipping" for social services but necessitates a formal "Transfer Process" for residents moving between households.

### Household Head Succession Constraint

A new Household Head _must_ be an existing member of that household before being promoted. A member cannot be "moved out" (given an `end_date`) if they are the current `Head` unless a successor is designated or the household is marked `Inactive`.

### Single Active Household Head Constraint

A household must have exactly one member assigned the `role = 'Head'` where the `end_date` is NULL. Enforced via the `idx_single_active_household_head` partial unique index.

### Household Jurisdictional Locking

A household's location is derived strictly from its `structure_id`. A household cannot be registered in a barangay that does not match the physical `barangay_id` of the house.

### Official Scoping Constraint

An official's authorization is strictly scoped to the `barangay_id` defined in their active record in the Officials Directory. The system will automatically reject any attempt by an official to view, edit, or sign documents for a resident whose `barangay_id` does not match their own.

### Official Succession Constraint

A `barangay_id` cannot have two active "Barangay Captain" terms simultaneously. Adding a new Captain must trigger an `end_date` for the predecessor.

### Document Generation Constraint

- **Duplicate Request Block:** A resident cannot request a new Certificate of Residency if they already have one with a `status = 'Active'`.
- **Zero-Status Visibility:** If a resident is removed from a household, all their "Household-linked" active documents (like Residency) are automatically updated to `status = 'Revoked'`.

### Substitution Constraint

- **Scope Limitation:** A delegate cannot further delegate their authority to a third party (No Sub-delegation).
- **Hierarchy Constraint:** A Secretary cannot be granted "Acting" status for a barangay they are not currently active in.
- **Temporal Override:** If the Captain's own term is marked with an `end_date`, all delegations granted by that Captain are automatically revoked by the system.
- **Delegation Type Constraint:** A delegation can be **Granular** — an official might be allowed to sign "Certificates of Residency" but NOT "Barangay Business Clearances."

---

## 7. Data Dictionary

The system contains **38 tables** organized into **8 functional groups**. For the full column-level data dictionary, refer to the standalone Data Dictionary document. The table inventory is summarized below.

### Database Summary

| Table Name                        | Group                        | Columns | Model                          |
| --------------------------------- | ---------------------------- | ------- | ------------------------------ |
| municipalities                    | Core Location                | 7       | Municipality                   |
| barangays                         | Core Location                | 8       | Barangay                       |
| users                             | Users & Authentication       | 29      | User                           |
| password_reset_tokens             | Users & Authentication       | 3       | (Laravel built-in)             |
| personal_access_tokens            | Users & Authentication       | 10      | (Laravel Sanctum)              |
| permissions                       | Roles & Permissions (Spatie) | 5       | BarangayRole / Spatie          |
| roles                             | Roles & Permissions (Spatie) | 5       | BarangayRole / Spatie          |
| model_has_roles                   | Roles & Permissions (Spatie) | 3       | (Spatie pivot)                 |
| model_has_permissions             | Roles & Permissions (Spatie) | 3       | (Spatie pivot)                 |
| role_has_permissions              | Roles & Permissions (Spatie) | 2       | (Spatie pivot)                 |
| barangay_terms                    | Barangay Governance          | 8       | BarangayTerm                   |
| delegations                       | Barangay Governance          | 6       | Delegation                     |
| houses                            | Housing & Households         | 7       | House                          |
| households                        | Housing & Households         | 9       | Household                      |
| household_member_profiles         | Housing & Households         | 12      | HouseholdMemberProfile         |
| families                          | Housing & Households         | 8       | Family                         |
| residency_requests                | Housing & Households         | 16      | ResidencyRequest               |
| document_type_properties          | Document System              | 9       | DocumentTypeProperty           |
| document_requirements_definitions | Document System              | 6       | DocumentRequirementsDefinition |
| document_rules                    | Document System              | 5       | (Pivot)                        |
| document_transactions             | Document System              | 18      | DocumentTransaction            |
| transaction_requirements          | Document System              | 8       | TransactionRequirement         |
| clearances                        | Document Detail Tables       | 5       | Clearance                      |
| business_clearances               | Document Detail Tables       | 9       | BusinessClearance              |
| construction_clearances           | Document Detail Tables       | 5       | ConstructionClearance          |
| tricycle_clearances               | Document Detail Tables       | 8       | TricycleClearance              |
| jobseeker_certificates            | Document Detail Tables       | 4       | JobseekerCertificate           |
| guardianship_certificates         | Document Detail Tables       | 7       | GuardianshipCertificate        |
| indigency_certificates            | Document Detail Tables       | 6       | IndigencyCertificate           |
| indigencysps_certificates         | Document Detail Tables       | 7       | IndigencySPSCertificate        |
| residency_certificates            | Document Detail Tables       | 6       | ResidencyCertificate           |
| solo_parent_certificates          | Document Detail Tables       | 7       | SoloParentCertificate          |
| notifications                     | System & Infrastructure      | 8       | (Laravel built-in)             |
| jobs                              | System & Infrastructure      | 7       | (Laravel Queue)                |
| failed_jobs                       | System & Infrastructure      | 7       | (Laravel Queue)                |
| telescope_entries                 | System & Infrastructure      | 8       | (Laravel Telescope)            |
| telescope_entries_tags            | System & Infrastructure      | 2       | (Laravel Telescope)            |
| telescope_monitoring              | System & Infrastructure      | 1       | (Laravel Telescope)            |

### Key Table Descriptions

#### users

Stores all registered residents and officials. Linked to barangay via `barangay_id`. Key fields include `uuid` (from eGovPH), `family_id`, demographic data, `egov_data` (JSON of valid IDs for requirement checking), `digital_signature`, and `profile_photos`.

#### barangay_terms

Each record represents one official's tenure in a position. A NULL `ended_at` means the term is currently active. Used by the authorization middleware to determine signing authority.

#### delegations

Records a specific grant of signing authority from a Barangay Captain (`granter_term_id`) to a delegate official (`delegate_term_id`). Scoped to document types and has an `expires_at` timestamp.

#### document_transactions

The central record for every document request. Tracks `requester_id`, `approver_id`, `on_behalf_of_id` (for delegated authority), `signing_capacity` (Native or Acting), `checksum` (for tamper detection), `request_origin` (online or walk-in), and lifecycle timestamps.

#### household_member_profiles

Junction table linking users to households. Key fields include `role` (head, spouse, member), `membership_type` (primary_resident, transient, associate), and `presence_status` (present, out of town). The combination of `role = 'Head'` and `end_date IS NULL` is enforced as unique per household.

---

## 8. Key Design Decisions

### Decision: Unified Transaction Table vs. Split Tables

**Context (Week 2 of development):** The initial design considered having separate tables per document type for tracking transactions.

**Decision:** A single `document_transactions` table was adopted with a `document_type_id` FK pointing to `document_type_properties`, and a `doc_type_model` field used to polymorphically route to the document-specific detail tables (e.g., `clearances`, `indigency_certificates`).

**Why:** A unified table simplifies querying for all pending requests in a barangay's queue, allows a single authorization middleware to apply to all document types, and makes audit trails straightforward. Document-specific fields are kept in their own detail tables (Tables 14–23) which are linked via `transaction_id`.

---

### Decision: Term-Based Approver ID

**Context:** The initial design stored the approver's `user_id` in `document_transactions`.

**Decision:** Changed to store `approver_id` as a reference to `barangay_terms.id` (the term record) rather than directly to `users.id`.

**Why:** This provides a legally accurate audit trail. Storing the `term_id` permanently links the document to the specific legal capacity in which the official was acting at the time of signing — not just who the person was. Even years later, the system can answer "Who was legally authorized to sign this document and in what capacity?" This is critical for provincial governance accountability.

---

### Decision: Migration from barangay_code (String) to barangay_id (Integer FK)

**Context (Feb. 25–26):** The initial implementation used `barangay_code` (a string matching the PSGC/eGovPH code format) as the primary linking identifier between tables.

**Decision:** Migrated to a formal `barangay_id` integer foreign key across all primary relationships.

**Why:** String-based joins are less performant at scale, and the PSGC/eGovPH code mismatch issue (different zero-padding formats between systems) was causing intermittent authorization failures. The integer FK system is faster, safer, and avoids format ambiguity. The `barangay_code` string is still retained in the `barangays` table for external API integration purposes.

---

### Decision: Spatie-Laravel-Permission for RBAC

**Context (Feb. 13):** The initial implementation used a custom permission system.

**Decision:** Replaced with Spatie Laravel Permission.

**Why:** Spatie provides a well-tested, industry-standard RBAC implementation with built-in Filament integration, model-level permission scoping, and a permission cache. This reduced custom code and prevented edge cases in permission inheritance.

---

### Decision: Frontend Architecture — Vue.js → Laravel Livewire

**Context (Feb. 6):** The initial frontend was built with Vue.js.

**Decision:** Migrated to Laravel Livewire.

**Why:** The project is backend-heavy with data that requires real-time server validation (barangay scoping, residency checks, document eligibility). Livewire keeps all logic on the server, eliminating the need for a separate API layer between the frontend and backend and reducing the surface area for authorization bypass. It also aligns with the Laravel + Filament stack used for the official panels.

---

### Decision: Document Processing via Queue Jobs

**Context (March 9):** Document approval previously executed synchronously in the HTTP request cycle.

**Decision:** Moved to asynchronous Laravel queue jobs (`ProcessDocumentRequest` and `ProcessDocumentApproval`).

**Why:** Browsershot PDF generation via headless Chrome (Puppeteer) can take 2–10 seconds. Running this synchronously blocked the HTTP response and caused timeout errors. Queue jobs allow the official to receive an immediate response while the PDF generation and notification delivery happen in the background. The `afterCommit()` dispatch ensures jobs only run after the database transaction is fully committed, preventing race conditions.

---

### Decision: Cache Strategy for Performance

**Context (March 9):** Repeated database hits for barangay lookups and channel authorization were identified via Laravel Telescope as a performance bottleneck.

**Decision:** Implemented `Cache::remember()` for:

- Channel authorization (`channel_auth_{userId}_{barangayCode}`, 15 min TTL)
- Municipality barangay ID lookups (`municipality_{municipalityId}_barangay_ids`, 30 min TTL)
- Municipality stats (`municipality_{municipalityId}_stats`, 30 min TTL)
- All barangays (`all_barangays`)
- All municipalities (`all_municipalities`)
- Spatie permission cache (forgotten on every role assignment/revocation)

**Cache Invalidation Rules:**

- `channel_auth_{userId}_{barangayCode}` — forget when a BarangayTerm is ended, deactivated, or reassigned, and when a delegation is revoked.
- `municipality_{municipalityId}_barangay_ids` — forget when a barangay is added to or removed from a municipality.
- `all_barangays` — forget when any barangay is created or deleted.
- `all_municipalities` — forget when any municipality is created or deleted.

---

## 9. Development Changelog

### Week 1 (Feb. 2–6, 2026): Initial Setup & Core Architecture

**Feb. 2** — Laravel project environment setup; Git source control configured; core dependencies installed (Livewire, Filament, Sanctum); local development environment verified with OrbStack.

**Feb. 3** — Initial database migrations for core user and barangay tables; User model configured with fillable fields, casts, and soft deletes; Spatie Laravel Permission integrated with initial roles seeder.

**Feb. 4** — Authentication integration with the Bataan Portal SSO using OAuth (`BataenoAuthController`); OAuth redirect URI configured via environment variables.

**Feb. 5** — Documented online and walk-in document request flows; test coverage added for walk-in request scenario; `nullable requester_id` introduced to accommodate walk-in requests.

**Feb. 6** — Frontend migrated from Vue.js to Laravel Livewire; SSO authentication flow verified as regression-free.

---

### Week 2 (Feb. 9–13, 2026): Filament Admin, Document System, Real-Time

**Feb. 9** — Initial Filament admin panel setup; Spatie Laravel Permission integrated for roles within the panel; user management capabilities added.

**Feb. 10** — Document request form built for residents using Livewire with dynamic required fields driven by document type models.

**Feb. 11** — Full document request and signing lifecycle implemented; `GovernanceService` for signing authority validation; `DocumentController` and `DocumentRequestController` with API routes; feature tests with access control validations.

**Feb. 12** — Laravel Reverb set up for real-time broadcasting; `DocumentRequestCreated` and `DocumentIssued` broadcast events implemented; channel authorization logic in `channels.php`.

**Feb. 13** — Official Filament Panel introduced with barangay-level statistics dashboard; PDF generation via Blade templates and Spatie Browsershot/Puppeteer; barangay-level access control middleware configured.

---

### Week 3 (Feb. 16–20, 2026): Profile, NFC, Household Architecture

**Feb. 16 (Change Log: Feb. 16)** — Fixed former official access control issue (officials could access system after term ended); fixed misconfigured field blocking residency requests; implemented dynamic barangay switching via household profiles.

**Feb. 17** — Profile page overhaul: new profile photo engine, User model expanded for "Outsiders," password management and session security sections added.

**Feb. 18** — NFC infrastructure layer built: UID Bridge fixes, UID map support, real-time NFC reader status updates, NFC logic merged into Walk-In Request workflow.

**Feb. 19** — `IndigencySPSCertificate` and `SoloParentCertificate` models created with migrations; both registered in document type properties system.

**Feb. 20** — Comprehensive household data architecture introduced: `House` model (physical structures), `Household` model (economic unit), `HouseholdMemberProfile` pivot model, and `ResidencyRequest` model.

---

### Week 4 (Feb. 23–27, 2026): Household Profiles, Lineage, Barangay ID Migration

**Feb. 23** — Resident-facing Livewire household dashboard built: view active residences, presence switching, new residence registration, join existing residence flow, Household Head invitation workflow.

**Feb. 24** — Official-side approval workflow for `ResidencyRequest` implemented; user-centric document storage path established.

**Feb. 25 (Change Log: Feb. 13 — Additions)** — Full platform migration from string-based `barangay_code` to formal `barangay_id` foreign key system; `UserObserver` implemented for automated family lifecycle management (nuclear family pattern).

**Feb. 26** — Lineage visualization upgraded from DOM-based leader-line to SVG-based D3.js; `LineageSeeder` updated for `barangay_id`-based relationships.

**Feb. 27** — Bataan Portal API resident lookup migrated to unified `POST /api/user` endpoint; PhilID QR parsing with custom blood type decoding; Double-Guard duplicate detection implemented.

---

### Week 5 (Mar. 2–6, 2026): Inhabitants View, UI Overhaul, City Admin Panel

**Mar. 2** — Inhabitants Modal for House resource built; Household InfoList implemented; `barangay_id` scoping bug on House model fixed.

**Mar. 3** — Landing page overhauled: WebGL-based animated gradient (Grainient.js), DotGrid enhancements with alpha-based dot transitions and touch device detection; ghost cursor bug fixed; landing page copy updated to "Province-Wide, Digital First."

**Mar. 4** — `BataenoAuthController` updated with specific OAuth scopes; walk-in NFC flow simplified to local database lookups only; registration wizard refined.

**Mar. 5** — City Admin Filament panel introduced at `/city-admin`; User model extended with `canAccessPanel`, `getTenants`, `canAccessTenant`, `getActiveMunicipality`, `getActiveMunicipalityCode`; read-only municipality-scoped resources built for Barangays, Officials, Residents, Families, Houses, and Households.

**Mar. 6** — Reusable `BarangayFilter` class introduced for panel-aware filtering; HouseholdResource infolist restructured into two-column desktop layout; mobile visual bug in `RepeatableEntry` member grid fixed; eager loading and `Cache::remember` applied for N+1 query elimination; document request and approval processing moved into Laravel queue jobs.

---

### Week 6 (Mar. 9–10, 2026): Performance, Queue Jobs, Completion

**Mar. 9** — Laravel Telescope integrated for request profiling and debugging; queue jobs `ProcessDocumentRequest` and `ProcessDocumentApproval` finalized with database driver; dynamic barangay filtering in city admin; critical tenancy bypass issue fixed for Super Admin; `DocumentIssued` event broadcast consolidated into Filament notification system; request-level property caching on `getActiveBarangayIds`; persistent `Cache::remember` on `getTenants`; database indexes on `deleted_at` and composite columns; broadcasting auth channel caching.

**Mar. 10** — End-to-end verification of queue job pipeline (ProcessDocumentRequest and ProcessDocumentApproval); final testing of City Admin panel resources, barangay filter scoping, and inhabitants modal panel-awareness; change log and daily accomplishment reports finalized.

---

## 10. Impact & Next Steps

### Impact

**For Residents:** The Bataeño Pass Barangay Module eliminates the need to physically visit barangay halls during office hours to obtain common certificates. Residents can submit requests digitally at any time, receive real-time status notifications, and download signed PDFs directly to their Digital Vault. For residents with secondary residencies (e.g., students or seasonal workers staying in boarding houses), the presence-switching system allows them to obtain local clearances in their current barangay without forfeiting their primary residency benefits.

**For Barangay Officials:** The platform replaces manual logbooks and disconnected spreadsheets with a structured, role-enforced system. Officials benefit from a live request queue, NFC-assisted walk-in processing, and automatic term expiry enforcement — ensuring that only currently authorized officials can issue documents. The delegation system allows continuity of service when the Barangay Captain is unavailable, while maintaining a clear audit trail for every signed document.

**For City/Municipal Administrators:** The City Admin Panel gives municipal administrators a read-only, municipality-scoped view across all barangays — enabling oversight of leadership directories, household registries, and document activity without requiring access to the barangay-level management tools.

**For Provincial Governance:** The term-based approver ID design and document hash verification system create a legally defensible audit trail for every document issued. The migration to integer foreign keys and the caching layer position the system for province-wide scaling as additional municipalities and barangays are onboarded.

---

### Recommended Next Steps

**1. Push Notifications (Mobile)** — Integrating FCM (Firebase Cloud Messaging) or APNs would extend real-time notifications to mobile devices, improving responsiveness for residents who may not actively monitor the web portal.

**2. Document Renewal Automation** — Currently, residents must manually re-request documents as they expire. An automated renewal reminder job could notify residents 30 days before expiry and pre-populate the renewal form with existing data.

**3. Barangay-Level Analytics Dashboard** — The data architecture supports detailed analytics (document request volumes, processing times, household growth by barangay). Adding a dedicated analytics view to the Official Panel would help barangay administrators make data-driven resource decisions.

**4. Offline Mode for Walk-In Processing** — The current walk-in workflow requires a live server connection. A service worker-based offline queue would allow officials to encode walk-in requests during connectivity outages and sync when back online.

**5. Full eGovPH Attribute Sync** — Currently, eligibility flags (e.g., "Low Income," "Solo Parent") are sourced from the `egov_data` JSON field populated at registration. A scheduled sync job against the eGovPH API would keep these attributes current without requiring residents to re-register.

**6. Expanding Document Types** — The polymorphic document architecture is designed to accommodate new document types with minimal schema changes. Potential additions include Barangay Health Certificates, Burial Assistance Certifications, and Scholarship Endorsement Letters.

**7. Integration with Provincial-Level Systems** — The City Admin Panel is currently read-only. A future phase could enable bidirectional data sharing with provincial social welfare systems, automatically flagging indigency certificate holders for eligibility in provincial subsidy programs.
