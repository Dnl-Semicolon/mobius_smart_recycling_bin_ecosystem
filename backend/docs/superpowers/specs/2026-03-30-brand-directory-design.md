# Brand Directory & Registration Redesign

## Problem

The current `/register/brand` page is a blank form where anyone types in a brand name. This looks unprofessional for a viva/demo and doesn't leverage the brand data we already have. There's no concept of a "known brands catalog" that users can search through.

## Solution

A **Brand Directory** (catalog of known F&B brands) with a searchable registration flow, similar to how GitHub Education lets you search for your university.

## Data Model

### New table: `brand_applications`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| brand_id | FK brands, nullable | Set when claiming existing catalog brand. Null for new brand requests. |
| user_id | FK users | The applicant |
| status | string (ApplicationStatus) | pending / approved / rejected |
| brand_name | string | Copied from catalog brand or typed by user |
| description | text, nullable | For new brand requests |
| website_url | string, nullable | For new brand requests |
| logo_path | string, nullable | For new brand requests |
| contact_person | string | |
| contact_email | string | Same as user email |
| contact_phone | string, nullable | |
| rejection_reason | text, nullable | |
| reviewed_by | FK users, nullable | |
| reviewed_at | timestamp, nullable | |
| timestamps | | |

### Existing `brands` table (no schema changes)

- **Catalog (unclaimed):** `user_id IS NULL`, `status = 'approved'`, `active = true`
- **Registered (claimed):** `user_id IS NOT NULL`, `status = 'approved'`
- **Pending (old flow):** `status = 'pending'` — no brands currently have this status. The old registration flow is replaced by this design. The `brand_applications` table is now the source of truth for all pending applications.

## Registration Flows

### Flow A: Claim Existing Brand

1. User searches dropdown on `/register/brand`
2. Picks a brand (e.g., "Starbucks") -> card shows logo, name, website
3. User fills: contact person, phone, email, password
4. Submit -> creates User (public_user role) + BrandApplication (brand_id set, status=pending)
5. Brand record stays untouched (approved, user_id=null)
6. Admin reviews application -> approves -> `brand.user_id = user.id`, user gets StoreOwner role

### Flow B: New Brand Request

1. User clicks "Can't find your brand? Request to add it"
2. Full form appears: brand name, description, website, logo upload + contact + password
3. Submit -> creates User (public_user role) + BrandApplication (brand_id=null, status=pending)
4. Admin reviews -> approves -> creates Brand (status=approved, active=true, user_id set) from application data, user gets StoreOwner role
5. New brand is now permanently in the catalog/directory

### Duplicate Prevention

- Brands with `user_id IS NOT NULL` are hidden from dropdown (already registered)
- Brands with a pending BrandApplication are hidden from dropdown (claim in progress)
- New brand requests: slug validated against existing brands

## Pages

### `/register/brand` (redesigned)

- Searchable dropdown at top (searches brands where user_id IS NULL and no pending application)
- When brand selected: brand info card (logo, name, website, color) + contact/password fields only
- "Can't find your brand?" -> expands full form with brand details + contact/password
- Single submit button for both paths

### Admin: "Brand Directory" (new sidebar page)

- Table of all brands in the catalog
- Columns: name, logo, website, status (Claimed/Available), claimed by
- Add new brand entries, edit existing ones (name, logo, website, color, description)
- This is how user CRUDs logos/colors for seeded brands

### Admin: "Applications > Brands" (modified)

- Now queries `brand_applications` table instead of brands with status=pending
- Shows: applicant name, brand (existing or new), submitted date, status
- Approve/reject flow updated to work with BrandApplication model

## Seed Data (~18 brands)

Pre-seeded into `brands` table with `user_id = null`, `status = 'approved'`, `active = true`:

1. ZUS Coffee (already exists)
2. Starbucks (already exists)
3. MIXUE (already exists)
4. Tealive
5. Gong Cha
6. Tiger Sugar
7. Daboba
8. CoolBlog
9. Boost Juice
10. The Alley
11. Chatime
12. Bask Bear Coffee
13. Lucky Cup
14. Luckin Coffee
15. Tutti Frutti
16. Inside Scoop
17. Llaollao
18. Secret Recipe

Logos, primary colors, and websites to be CRUDed by user via admin panel.

## Emails

Deferred for POC. Using in-app notifications (existing NotificationService). Mailgun integration as follow-up.

## Files to Create/Modify

### New files:
- `database/migrations/XXXX_create_brand_applications_table.php`
- `app/Models/BrandApplication.php`
- `app/Http/Requests/StoreBrandApplicationRequest.php`
- `resources/views/admin/brand-directory/index.blade.php`

### Modified files:
- `app/Http/Controllers/Registration/BrandRegistrationController.php` — handle both flows
- `app/Services/ApplicationService.php` — new methods for BrandApplication approval
- `resources/views/registration/brand.blade.php` — searchable dropdown redesign
- `resources/views/admin/applications/brands/index.blade.php` — query BrandApplications
- `resources/views/admin/applications/brands/show.blade.php` — show BrandApplication details
- `resources/views/components/admin/sidebar.blade.php` — add Brand Directory link
- `app/Http/Controllers/Admin/ApplicationController.php` — BrandApplication CRUD
- `routes/web.php` — new admin routes for Brand Directory

## Verification

1. Visit `/register/brand` -> searchable dropdown loads with ~18 brands
2. Select a brand -> info card appears, contact/password fields shown
3. Submit -> application created, admin notified
4. "Can't find?" -> full form, submit -> application created
5. Admin approves existing brand claim -> brand.user_id set, user becomes StoreOwner
6. Admin approves new brand -> new Brand created in catalog, user becomes StoreOwner
7. Already-claimed brands don't appear in dropdown
8. Brands with pending applications don't appear in dropdown
9. Admin Brand Directory page shows all catalog brands with claimed/available status
