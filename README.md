<p align="center"><img src="public/bataeno_pass_logo.svg" width="200" alt="Bataeño Pass Logo"></p>
<h1 align="center" class="text-8xl text-white">
                Bataeño Pass
            </h1>

<h1 align="center">Bataeño Pass — Barangay Module</h1>

<p align="center">
  Barangay management system built during a PITO internship at the Provincial Government of Bataan.<br/>
  Led system analysis, ERD design, full-stack implementation, and technical documentation.<br/>
  Digitizes document requests, household registry, and official term management province-wide.
</p>
 
<p align="center">
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=flat&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/Livewire-4E56A6?style=flat&logo=livewire&logoColor=white" />
  <img src="https://img.shields.io/badge/Filament-FDAE4B?style=flat&logoColor=black" />
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/D3.js-F9A03C?style=flat&logo=d3.js&logoColor=white" />
</p>
 
---

> ⚠️ **Demo Notice:** This repository is seeded with dummy data only. All names, addresses, and records visible in the application are fictional and generated for demonstration purposes. No real personal information is stored or exposed.

---

## Internship Context

**Role:** Intern — System Analyst, Database Designer, Full-Stack Developer, Technical Documentation Lead  
**Co-Author:** Russel Matthew F. Santos

**Organization:** Provincial Government of Bataan — PITO

**Internship Period:** February 2, 2026 – March 10, 2026

**Technology Officers:**

- Mr. Paolo Nuestro
- Mr. Nixon Somoza
- Mr. Bryan Gonzales

---

## Overview

The **Barangay Module** is a vertical extension of the existing Bataeño Pass provincial identity platform. It brings government document services — certificates, clearances, and residency records — down to the barangay level across Bataan province, replacing manual paper-based workflows with a structured, role-enforced digital system.

The platform serves three distinct access tiers:

| Role                      | Access                                                | Scope                                         |
| ------------------------- | ----------------------------------------------------- | --------------------------------------------- |
| **Residents**             | Request documents, manage household, view lineage     | Livewire resident portal                      |
| **Barangay Officials**    | Approve requests, issue signed PDFs, manage residents | Official Filament panel (per-barangay tenant) |
| **City/Municipal Admins** | Read-only oversight across all barangays              | City Admin panel (`/city-admin`)              |

---

## Features

- **Digital Document Issuance** — Barangay Clearance, Indigency Certificate, Certificate of Residency, Business Clearance, and more. Supports both online requests and walk-in processing via NFC card tap or PhilID QR scan.
- **Signing Authority Enforcement** — Triple Match check (Auth, Authority, Scope) + hierarchy validation (Native Captain vs. Acting delegate) before any document is issued.
- **Household Registry** — Layered `House → Household → HouseholdMemberProfile` architecture with presence mutex, jurisdictional locking, and atomic head succession.
- **Real-Time Notifications** — Laravel Reverb WebSocket broadcasting with cached channel authorization.
- **Family Lineage Visualization** — SVG-based D3.js interactive tree with zoom, pan, gender-coded gradients, and deceased ancestor styling.
- **Multi-Tenancy** — Filament panels scoped per barangay (Officials) and per municipality (City Admin).
- **Async PDF Generation** — Browsershot + Puppeteer offloaded to Laravel Queue jobs with SHA-256 tamper detection and one-time download tokens.
- **Official Term Management** — Full audit trail with delegation support — Captains can grant granular, time-limited signing authority to Secretaries.

---

## Tech Stack

| Layer                 | Technology                            |
| --------------------- | ------------------------------------- |
| Backend               | Laravel (PHP)                         |
| Resident UI           | Laravel Livewire                      |
| Admin Panels          | Filament PHP                          |
| Authentication        | eGovPH / Bataeño Pass SSO (OAuth 2.0) |
| Roles & Permissions   | Spatie Laravel Permission             |
| Real-Time             | Laravel Reverb (WebSockets)           |
| PDF Generation        | Spatie Browsershot + Puppeteer        |
| Lineage Visualization | D3.js + Alpine.js                     |
| Queue Processing      | Laravel Queue (database driver)       |
| Debugging             | Laravel Telescope                     |
| Local Dev             | OrbStack                              |

---

## Database

38 tables across 8 functional groups:

```
Core Location          → municipalities, barangays
Users & Auth           → users (29 cols), password_reset_tokens, personal_access_tokens
Roles & Permissions    → permissions, roles, model_has_roles, model_has_permissions, role_has_permissions
Barangay Governance    → barangay_terms, delegations
Housing & Households   → houses, households, household_member_profiles, families, residency_requests
Document System        → document_type_properties, document_transactions, transaction_requirements, ...
Document Detail Tables → clearances, business_clearances, indigency_certificates, residency_certificates, ...
System & Infra         → notifications, jobs, failed_jobs, telescope_entries, ...
```

ERD reference: [dbdiagram.io/d/ERDBrgy](https://dbdiagram.io/d/ERDBrgy-697aaeb0bd82f5fce2f3aac1)

---

## Getting Started

### Requirements

- PHP 8.2+
- Composer
- Node.js + npm
- MySQL 8.0+
- Puppeteer / Chromium (for PDF generation)

### Installation

```bash
git clone https://github.com/YOUR_USERNAME/bataeno-pass-barangay.git
cd bataeno-pass-barangay

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Configure your `.env` — database credentials, app URL, and queue driver (`QUEUE_CONNECTION=database`).

```bash
php artisan migrate
php artisan db:seed
```

Start the queue worker for PDF generation and notifications:

```bash
php artisan queue:work
```

Start the dev server:

```bash
php artisan serve
npm run dev
```

---

## Key Design Decisions

A few decisions worth calling out:

**Term-based approver ID** — `document_transactions.approver_id` references `barangay_terms.id`, not `users.id`. This creates a legally defensible audit trail — the document is permanently linked to the specific capacity and period in which the official was authorized to sign.

**Integer FK migration** — The system migrated from string-based `barangay_code` identifiers to integer `barangay_id` foreign keys mid-project after PSGC/eGovPH code format mismatches caused intermittent authorization failures.

**No FK on `household_head_id`** — `households.household_head_id` references `household_member_profiles.id` without a database-level FK to avoid a circular dependency. Head assignment is managed at the application layer.

**Queue jobs for PDF** — Browsershot via headless Chromium can take 2–10 seconds. Moving approval processing to `ProcessDocumentApproval` with `->afterCommit()` dispatch prevents race conditions and HTTP timeouts.

Full decision log is documented in the [technical docs](docs/).

---

## Documentation

Full technical documentation is available in the `/docs` directory and mirrors the live portfolio docs viewer:

- Project Overview
- System Architecture
- Feature Specifications
- Database Design
- Key Design Decisions
- Development Changelog

---

## License

No license — public for portfolio and reference purposes. Not open for reuse or redistribution without permission.
