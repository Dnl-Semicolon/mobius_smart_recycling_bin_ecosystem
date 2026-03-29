# Database Snapshot — 2026-03-02

## AI Rollback Instructions

If the database layer (migrations, seeders, factories) has been changed and needs to be rolled back to this snapshot, copy the files from this directory back to their original locations:

```bash
cp -r database/_snapshot_2026_03_02/migrations/* database/migrations/
cp -r database/_snapshot_2026_03_02/factories/* database/factories/
cp -r database/_snapshot_2026_03_02/seeders/* database/seeders/
php artisan migrate:fresh --seed
```

After restoring, verify with `php artisan test --compact` — all 295 tests should pass.

---

## Schema Overview (6 domain tables + Laravel defaults)

### users
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | auto-increment |
| name | varchar | |
| email | varchar | unique |
| role | varchar | default `'public_user'`, values: `admin`, `collector`, `public_user` |
| email_verified_at | datetime | nullable |
| password | varchar | bcrypt hash |
| remember_token | varchar(100) | nullable |
| timestamps | | |

### outlets
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | auto-increment |
| name | varchar | |
| address | varchar(500) | |
| latitude | decimal(10,8) | nullable |
| longitude | decimal(11,8) | nullable |
| contact_name | varchar | nullable |
| contact_phone | varchar(50) | nullable |
| contact_email | varchar | nullable |
| operating_hours | varchar(500) | nullable |
| contract_status | varchar | default `'pending'`, indexed, values: `active`, `inactive`, `pending` |
| notes | text | nullable |
| timestamps + soft deletes | | |

### bins
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | auto-increment |
| serial_number | varchar(50) | unique, format `MBR-YYYY-NNN` |
| fill_level | tinyint unsigned | default 0, range 0-100, indexed |
| status | varchar | default `'active'`, indexed, values: `active`, `inactive`, `maintenance` |
| timestamps + soft deletes | | |

### bin_assignments
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | auto-increment |
| bin_id | FK bins.id | cascade delete, indexed |
| outlet_id | FK outlets.id | cascade delete, indexed |
| assigned_at | datetime | |
| unassigned_at | datetime | nullable (null = currently assigned) |
| | | composite index: `(bin_id, unassigned_at)` |

### detection_events
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | auto-increment |
| bin_id | FK bins.id | cascade delete, indexed |
| waste_type | varchar | nullable, indexed, values: `paper_cup`, `plastic_cup`, `lid`, `straw`, `napkin`, `liquid_waste` |
| confidence | tinyint unsigned | nullable, indexed, range 0-100 |
| image_path | varchar | nullable |
| detected_at | datetime | indexed |
| timestamps | | |

### pickup_requests
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | auto-increment |
| bin_id | FK bins.id | cascade delete |
| status | varchar | default `'pending'`, values: `pending`, `claimed`, `completed`, `cancelled` |
| claimed_by | FK users.id | nullable, set null on delete |
| claimed_at | datetime | nullable |
| completed_at | datetime | nullable |
| timestamps | | |
| | | composite index: `(status, created_at)` |
| | | composite index: `(claimed_by, status, claimed_at)` |

---

## Seed Data Summary

After `php artisan migrate:fresh --seed`, the database contains:

| Table | Rows | Notes |
|-------|------|-------|
| users | 3 | admin, collector, public_user |
| outlets | 10 | 8 active, 1 pending, 1 inactive |
| bins | 15 | 12 active, 1 maintenance, 1 inactive, 1 unassigned active |
| bin_assignments | 14 | 12 current + 2 historical |
| detection_events | ~1050 | weighted random, last 7 days |
| pickup_requests | 5 | 1 claimed, 2 pending, 2 completed |

### Users (hardcoded)
| Email | Role | Password |
|-------|------|----------|
| admin@mobius.test | admin | password |
| collector@mobius.test | collector | password |
| test@example.com | public_user | password |

### Outlets (hardcoded, all real Penang locations)
1. Starbucks Gurney Plaza (active, 3 bins)
2. Starbucks Gurney Paragon (active, 1 bin)
3. Starbucks Sunway Carnival Mall (active, 1 bin)
4. Starbucks 1st Avenue Mall (active, 1 bin)
5. CHAGEE Gurney Plaza (active, 3 bins)
6. Tealive Prangin Mall (active, 1 bin)
7. Tealive Bayan Baru (active, 1 bin)
8. Tealive Gurney Plaza (pending, 0 bins)
9. Oldtown White Coffee Gurney Plaza (active, 1 bin)
10. Arang Coffee Bayan Lepas (inactive/closed, 0 bins)

### Bins (hardcoded)
| Serial | Fill | Status | Outlet |
|--------|------|--------|--------|
| MBR-2024-001 | 25% | active | Starbucks Gurney Plaza |
| MBR-2024-002 | 15% | active | Starbucks Gurney Plaza |
| MBR-2024-003 | 30% | active | Starbucks Gurney Plaza |
| MBR-2024-004 | 45% | active | Starbucks Gurney Paragon |
| MBR-2024-005 | 22% | active | Starbucks 1st Avenue Mall |
| MBR-2024-006 | 55% | active | Starbucks Sunway Carnival Mall |
| MBR-2024-007 | 68% | active | Tealive Prangin Mall |
| MBR-2024-008 | 72% | active | CHAGEE Gurney Plaza |
| MBR-2024-009 | 85% | active | CHAGEE Gurney Plaza |
| MBR-2024-010 | 92% | active | CHAGEE Gurney Plaza |
| MBR-2024-011 | 80% | active | Tealive Bayan Baru |
| MBR-2024-012 | 0% | maintenance | (unassigned) |
| MBR-2024-013 | 35% | active | Oldtown White Coffee Gurney Plaza |
| MBR-2024-014 | 40% | active | (unassigned) |
| MBR-2024-015 | 0% | inactive | (unassigned) |

### Detection Events (generated)
- Formula: `count = (fill_level * 1.5) + random(5, 15)` per active assigned bin
- Weighted distribution: paper_cup 35%, plastic_cup 30%, lid 15%, straw 10%, napkin 5%, liquid_waste 5%
- Confidence: random 70-99
- Timestamps: random within last 7 days
- Only seeded for active bins that have a current assignment (12 bins)

### Pickup Requests (hardcoded logic)
- Bins with fill >= 80% get a pickup request
- First one (MBR-2024-009): claimed by collector, 30 min ago
- Others (MBR-2024-010, MBR-2024-011): pending
- 2 historical completed pickups on low-fill bins (MBR-2024-001, MBR-2024-002)

### Historical Assignments (hardcoded)
- MBR-2024-006 was previously at Starbucks Gurney Paragon (reassigned)
- MBR-2024-012 was previously at Oldtown White Coffee (pulled for maintenance)

---

## Enums (source of truth)

### WasteType (`app/Enums/WasteType.php`)
`paper_cup`, `plastic_cup`, `lid`, `straw`, `napkin`, `liquid_waste`

### BinStatus (`app/Enums/BinStatus.php`)
`active`, `inactive`, `maintenance`

### ContractStatus (`app/Enums/ContractStatus.php`)
`active`, `inactive`, `pending`

### PickupStatus (`app/Enums/PickupStatus.php`)
`pending`, `claimed`, `completed`, `cancelled`

### UserRole (`app/Enums/UserRole.php`)
`admin`, `collector`, `public_user`

---

## Factory States (for tests)

| Factory | States |
|---------|--------|
| UserFactory | `admin()`, `collector()`, `publicUser()`, `unverified()` |
| BinFactory | `active()`, `inactive()`, `maintenance()`, `empty()`, `full()`, `withFillLevel(int)` |
| OutletFactory | `active()`, `inactive()`, `pending()` |
| DetectionEventFactory | `ofType(WasteType)`, `highConfidence()`, `lowConfidence()`, `today()`, `withImage()` |
| PickupRequestFactory | `pending()`, `claimed(?User)`, `completed(?User)`, `cancelled()` |
| BinAssignmentFactory | `historical()`, `current()` |
