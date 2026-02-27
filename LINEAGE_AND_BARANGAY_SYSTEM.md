# Family Lineage & Barangay ID Documentation

This document summarizes the core architectural changes made to transition the platform from a string-based `barangay_code` identifier to a formal ID-based relationship system, alongside the new automated family lifecycle rules.

## 1. Barangay Relationship Migration

The platform has fully shifted to using `barangay_id` (foreign key) instead of `barangay_code` (string) for all primary relationships. This ensures database integrity and better performance with Laravel's Eloquent.

### Affected Modules:

- **User Model**: Tenant resolution methods (`getTenants`, `canAccessTenant`) and helper methods (`getActiveBarangayIds`) now use `id`-based lookups.
- **House Model**: Linked to `Barangay` via `barangay_id`.
- **Household Model**: Uses a `hasOneThrough` relationship targeting `Barangay` via `House` using primary keys.
- **Family Model**: Now explicitly linked to `Barangay` via `barangay_id`.
- **BarangayTerm Model**: Tracks official terms using `barangay_id`.

## 2. Automated Family Lifecycle

A sophisticated `UserObserver` has been implemented to manage the "Nuclear Family" pattern. This prevents UI bloat and ensures the digital representation of families matches real-world dynamics.

### Lifecycle Rules:

1. **Creation**: When a resident is assigned both a Father and a Mother, the system automatically creates (or finds) a dedicated family unit for that specific parent pair.
2. **Migration**: Residents are automatically moved into their new nuclear family unit when they originate one (as parents) or join one (as children), keeping lists focused.
3. **Dissolution (Auto-Cleanup)**:
    - A family record is **automatically deleted** if it becomes empty (0 members).
    - A family record is **automatically deleted** if both core parents are deceased AND all children have married or moved into their own family units.
4. **Lone Parent Policy**: A lone surviving parent is allowed to stay in their family record. However, if they are manually moved to join a child's family unit, their old empty family is cleaned up immediately.

## 3. Lineage Tree System (D3.js)

The lineage visualization has been upgraded from DOM-based `leader-line` to a high-performance **SVG-based D3.js** implementation.

- **Integrated in Filament**: Registered as an Alpine.js component (`lineageD3`) within the main application bundle.
- **Data Safety**: Passes complex family JSON via hidden script tags to avoid attribute quoting issues.
- **Interactive Features**: Supports mouse-wheel zoom, click-and-drag panning, and features smooth curved links.
- **Visuals**: Highlighted "Current User," gender-coded node gradients, and specific styling for deceased ancestors (name strikethroughs).

## 4. Seeder Enhancements

The `LineageSeeder` has been updated to support these changes and prevent "ghost families" (empty records created during seeding).

- **Targeted Seeding**: Creates separate family units for different generations.
- **Ghost Elimination**: Performs a final cleanup pass after seeding to delete any family records that were not occupied during the process.

---

_Last Updated: February 26, 2026_
