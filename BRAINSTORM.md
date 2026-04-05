# Mobius Brainstorm Log

Living document. Daniel talks, Claude responds, decisions get logged.

---

## Session 1 — 2026-04-05, ~6:30 PM MYT

### Context
- Viva 4 is tomorrow at 2:30 PM
- backend-v2 is a fresh Laravel React starter kit (Inertia + shadcn/ui)
- Schema V3 is clean: 28 tables, single migration
- Seeder is stripped: 3 plans (Basic RM199, Pro RM499, Custom), 5 users (one per role)
- Landing page exists: hero, how it works, pricing (3 cards, no perks yet, dumb buttons)
- Sign In is in footer (admin backdoor), no registration link in nav
- Admin dashboard + Users index page working

### What Daniel said
> Don't output any code, just have a back and forth with me. Every time I send a prompt, log it here. This is a living document — prompts, responses, decisions. We'll be going through this a lot.

### Current state of the landing page
- Nav: Mobius logo, Pricing, How It Works. Dashboard link only if logged in.
- Hero: "Smart Recycling for Beverage Brands"
- How It Works: 3 generic cards
- Pricing: Basic | Pro | Custom (no features listed, buttons disabled)
- Footer: full name + subtle Sign In link

### Open questions (to discuss)
1. What do the "Get Started" / "Contact Sales" buttons do?
2. What's the flow from landing page → becoming a paying subscriber?
3. What perks go in each tier?
4. How does admin onboard a new brand?
5. Vouchers — next after pricing is settled

### Decisions made so far
- 2 priced tiers + 1 custom (contact sales)
- No Stripe checkout for now
- Landing page is for guiding users, not spamming registration
- Registration should pipeline communication (forms → emails → admin approval)
- Sign In hidden in footer

---

---

## Session 2 — 2026-04-05, ~10:00 PM MYT

### What Daniel said
> Very weird. When I fill in the form, convert the lead, login as brand owner, go to billing — I see "Custom plan — contact admin for payment arrangements." Why can't I see Pay Now? Why is it auto-set to one year? Why doesn't the flow have a back and forth? My Stripe sandbox has prices at RM49 and RM149 (Basic and Premium). Find the gaps, lay it on me.

### Gaps Identified

1. **stripe_price_id is NULL in DB** — seeder used `env()` which returns null when config is cached. All 3 plans have no Stripe price mapped → billing page always shows "contact admin."

2. **Price mismatch** — Stripe dashboard: Basic=RM49/mo, Premium=RM149/mo. Landing page: Basic=RM199/mo, Pro=RM499/mo. Completely different numbers. Which is truth?

3. **Subscription instantly "active"** — Convert action sets status='active' + ends_at=now()+1year. No "pending payment" state. Brand owner gets full access without paying.

4. **No payment gate** — Even if Stripe worked, paying doesn't change our org_subscription status. There's no "paid" vs "unpaid" distinction.

5. **Hardcoded 1-year dates** — Arbitrary, not tied to plan billing cycle or actual payment.

6. **No back-and-forth** — Conversion is one click. Should be: convert → pending_payment → brand sees "pay now" → pays via Stripe → subscription activates.

### Decisions Made
- **Prices:** Use Stripe prices — RM49/mo (Basic), RM149/mo (Premium). Update landing page + seeder to match.
- **Lifecycle:** Convert creates subscription as `pending_payment`. Only flips to `active` after Stripe payment succeeds.
- **Flow:** Convert → pending_payment → brand sees "Pay Now" → Stripe Checkout → success → status=active.

### What was fixed
1. Seeder: hardcoded stripe_price_id values, prices RM49/RM149, plan names Basic/Premium/Custom
2. Landing page: auto-reflects DB prices
3. Convert action: sets status='pending_payment', no dates
4. Migration: added 'pending_payment' to enum, dates nullable
5. Billing page: shows "Pay Now" when pending, "Manage via Stripe" when active
6. Billing success handler: flips to active, sets dates
7. Stripe Checkout: works end-to-end with test card 4242

### Stripe works! But new gaps surfaced...

---

## Session 3 — 2026-04-05, ~10:20 PM MYT

### What Daniel said
> Ok yeah works. But why until 2027-04-05? Did we have a good enough flow to choose how many months? An email mailtrap service? A way for admin to see all this? A backdoor to manipulate dates at the database level to mock when time has passed to see states like renewal and contract end? This needs its own long hard thinking. Think about it. No code. And think about the custom tier and how that works.

### My Analysis

#### Problem 1: Why 2027-04-05? (Hardcoded 1 year)
The billing success handler does `now()->addYear()`. This is wrong because:
- The user never chose a subscription duration
- Stripe already manages the billing cycle (monthly recurring)
- Our `ends_at` should sync WITH Stripe, not be hardcoded independently
- If Stripe charges monthly, our system shouldn't say "you have 1 year"

**The real question:** What does `ends_at` even mean in our system?

Options:
- **A) Let Stripe own the billing cycle entirely.** Our `org_subscription` just tracks `status` (pending/active/cancelled). No dates. Stripe handles when to charge, when to cancel. We check `$user->subscribed('default')` to know if they're active.
- **B) Contract dates separate from billing.** `starts_at` = when they paid. `ends_at` = contract term (6mo, 12mo, 24mo — chosen at conversion or checkout). Stripe handles recurring payment within that contract. When contract ends, they renew or churn.
- **C) Hybrid.** Stripe owns monthly billing. Our system tracks a "contract term" that represents the minimum commitment. Admin can set this per org.

**My recommendation: Option A for now.** Let Stripe own everything. Our `org_subscription.status` mirrors Stripe's status. Remove `starts_at`/`ends_at`/`renews_at` from being meaningful — or just populate them from Stripe's data on webhook/success. For the viva, this is cleanest: "We use Stripe to manage billing. The system syncs."

#### Problem 2: No duration choice
Currently the flow is: convert → pending → pay → active. Nowhere does the user choose monthly vs yearly, or a contract length. 

For viva demo, keep it simple:
- Stripe prices are monthly recurring
- When they pay, they're subscribed monthly
- If we want yearly option later, create separate Stripe prices and let the user pick at checkout

#### Problem 3: Admin can't see billing
Admin billing page is still a placeholder. It should show:
- All org subscriptions with status, plan, org name
- Ability to see Stripe payment status
- For Custom plans: admin manually sets status (no Stripe)

#### Problem 4: Email (Mailtrap)
Mailtrap is already configured in .env (SMTP credentials are there). We're not sending any emails yet. Where emails make sense:
- After lead form submission → "We received your inquiry"
- After conversion → "Your account has been created" (with credentials)
- After payment → "Payment confirmed, subscription active"

These are nice-to-have for the viva. Laravel's Mail is already set up, just need to trigger `Mail::send()` at the right points.

#### Problem 5: No backdoor to manipulate dates for demo
For the viva, Daniel needs to show what happens when:
- Subscription is about to expire
- Subscription has expired
- Payment failed (past_due)

Options:
- **Admin panel date override:** Admin can edit subscription dates directly
- **Artisan command:** `php artisan subscription:simulate-expiry {org_id}`
- **phpMyAdmin:** Just edit the dates directly in the DB (fastest for demo)

**My recommendation:** For viva, just use phpMyAdmin to change dates. No code needed. If we want it cleaner, add an admin "edit subscription" page later.

#### Problem 6: Custom Tier — How does it work?

The Custom plan (price=0, no Stripe price) is fundamentally different:
- No self-service checkout
- Admin negotiates pricing offline
- Admin manually creates the subscription with custom terms

Flow for Custom:
1. Lead clicks "Contact Sales" on Custom card → same form, `selected_plan_id=3`
2. Admin sees the lead, knows it's Custom → reaches out (email/phone)
3. They negotiate: maybe RM800/mo for 50 bins, 2-year contract
4. Admin converts the lead → subscription created as `pending_payment`
5. BUT there's no Stripe checkout for custom. Instead:
   - Option A: Admin manually flips status to `active` and sets custom dates
   - Option B: Admin creates a custom Stripe invoice and sends it
   - Option C: Admin records a manual payment in our `payments` table

**My recommendation: Option A for now.** Admin has an "Activate Subscription" button on the admin billing page for custom plans. They set the dates manually. Payment is handled offline (bank transfer, invoice, etc.). For the viva, this shows the system handles both self-service (Stripe) AND enterprise (manual) flows.

### Summary of what's needed

| Priority | What | Effort |
|----------|------|--------|
| 1 | Remove hardcoded dates from success handler, sync with Stripe | Small |
| 2 | Admin billing page: show all subscriptions, manual activate for custom | Medium |
| 3 | Email notifications at key points (lead, conversion, payment) | Medium |
| 4 | Date manipulation for demo: use phpMyAdmin, no code needed | None |
| 5 | Duration choice at checkout (monthly vs yearly) | Later |
| 6 | Stripe webhooks for real-time status sync | Later |

### Decisions needed from Daniel
1. Do we let Stripe own the billing cycle (Option A) or track contract terms separately?
2. For Custom plans: admin manually activates, or do we need something more?
3. Do we build email notifications tonight or is that a "later" thing?

---

## Session 5 — 2026-04-06, ~12:15 AM MYT — Billing Reality Check

### What Daniel said
> "The subscription story is solid now" bruh no it's not. We haven't done anything for solidifying the two different tiers with constraints and help/error messages, nor tested Custom plans, nor solidified Stripe integration. Stripe has so many features, APIs — we need to make sure every Stripe feature or data can be seen via our app IF we can't properly configure a manage-via-stripe functionality. Viewing invoices, receipts, storing PDFs. The billing page has to be solid for brand owners and admins. We should see everything. Only with all the data to our disposal, we can enforce shit. Make it real world.

### The Root Problem

We have TWO subscription systems that don't talk to each other:

1. **`organization_subscriptions`** — Our table. Tracks org → plan relationship, status, dates. Created during lead conversion.
2. **Cashier's `subscriptions`** — Stripe's table. Created when user pays via Stripe Checkout. Tracks stripe_id, stripe_status, stripe_price.

After `migrate:fresh --seed`, the Cashier table is empty but our org_subscriptions exist. After Stripe payment, Cashier creates a record but our system might not sync. This drift is the source of every bug.

### What "Solid Billing" Actually Looks Like

For the brand owner, `/brand/billing` should show:
- Current plan name + price
- Subscription status (synced with Stripe, not our own flag)
- Next billing date (from Stripe)
- Payment method on file (last 4 digits)
- Invoice history (from Stripe — date, amount, status, PDF link)
- "Manage Subscription" button → Stripe billing portal (change plan, cancel, update payment)

For admin, `/admin/billing` should show:
- All org subscriptions in a table
- Each row: org name, plan, status, last payment date, next billing date
- Click into detail: see that org's invoice history
- For Custom plans: manual activation + ability to send Stripe invoice

### What Stripe Billing Portal Gives Us For Free

Stripe's hosted billing portal (accessed via `$user->redirectToBillingPortal()`) lets the customer:
- View invoices and download PDFs
- Update payment method
- Cancel subscription
- View upcoming invoice

**If we make the billing portal work, we get 80% of "solid billing" for free.** We don't need to build invoice views, receipt downloads, etc. Stripe handles it.

### What We Actually Need to Build

1. **Single source of truth:** Use Cashier's `$user->subscribed('default')` as THE subscription check. Our `org_subscription` is just the business contract — Stripe is the payment reality.
2. **Billing page enrichment:** Pull data from Cashier/Stripe — next billing date, payment method, recent invoices.
3. **Billing portal must work:** The "Manage via Stripe" button needs to redirect to Stripe's portal.
4. **Custom plans:** Admin creates a Stripe Invoice manually (in Stripe dashboard for now), sends to brand. Brand pays via Stripe-hosted invoice page.
5. **Plan constraints:** If Basic allows 3 bins, enforce it when admin tries to pair a 4th bin.

### Priority for viva (remaining hours)

| # | What | Why | Time |
|---|------|-----|------|
| 1 | Make billing portal work + enrich billing page with Stripe data | This IS the subscription story | 45 min |
| 2 | Plan constraints (bin limits, feature gates) | Shows real business logic | 30 min |
| 3 | Landing page is done ✓ | Already has perks |  |
| 4 | Vouchers | Lecturer flagged this | 1-2 hrs |
| 5 | Everything else | After viva | - |

---

## Session 6 — 2026-04-06, ~2:00 AM MYT — Pricing Model

### What Daniel said
> We should get back to pricing model. Stripe is mostly done. But no custom tier yet, no actual constraints or a page to show what brand owners can do in their tier. Schema must be uplifted before UI can be uplifted. All must flow through Stripe.

### Current state of pricing
- Landing page: Basic (RM49/mo), Premium (RM149/mo), Custom (Let's Talk)
- Plan features in DB but not enforced anywhere
- No constraints (e.g., Basic allows 3 bins but nothing stops creating 10)
- Custom tier has no Stripe flow — admin manually activates
- No page showing brand owners what their plan includes/limits

### What "pricing model" means for the viva

The lecturer wants to see:
1. **Clear differentiation** between tiers — not just price, but what you GET
2. **Enforcement** — if you're on Basic, you can't exceed your bin limit
3. **Upgrade path** — brand owner can see they're hitting limits and upgrade
4. **Custom flow** — enterprise deals handled professionally

### Questions to figure out
1. What are the actual feature differences between Basic/Premium/Custom?
2. Where do we enforce limits? (bin creation? outlet creation? features hidden?)
3. Does the brand owner see a "your plan" page showing what's included vs locked?
4. Can they upgrade from Basic to Premium themselves via Stripe?

### Daniel's response
> ok. happy. now, back to where we were. [re-raised the 3 questions about pricing]

### My thinking on each question

**1. What does each tier actually get?**

Current features in the DB seeder are reasonable but untested. The question is whether these are HARD limits (enforced in code) or SOFT limits (shown on the pricing page but not enforced). For an FYP, hard limits are more impressive — they show real business logic.

Proposed tier breakdown:

| Feature | Basic (RM49) | Premium (RM149) | Custom |
|---------|-------------|----------------|--------|
| Bins | 3 max | 15 max | Negotiated |
| Outlets | 1 | 5 | Unlimited |
| Staff accounts | 2 | 10 | Unlimited |
| Analytics | Basic (counts only) | Advanced (charts, trends) | Full |
| Support | Email | Priority | Dedicated |
| AI waste detection | Yes | Yes | Yes |
| Brand detection | No | Yes | Yes |
| Route optimization | No | Yes | Yes |
| Voucher system | No | Yes | Yes |
| API access | No | No | Yes |

**2. Where do we enforce?**

Two layers:
- **Hard limits**: bin count, outlet count, staff count — checked at creation time. Error message: "Your Basic plan allows 3 bins. You have 3. Upgrade to Premium for up to 15."
- **Feature gates**: sidebar items hidden if plan doesn't include them. e.g., Basic doesn't see "Vouchers" in sidebar. If they somehow hit the URL, they get a 403.

Both are needed. Hard limits prevent overselling. Feature gates keep the UI clean.

Where the check lives: a middleware or a helper method on the Organization/Subscription model that reads the plan's features JSON and checks against current counts.

**3. Custom tier payment**

Two realistic options for the viva:
- **Option A**: Admin creates a custom Stripe Price in Stripe dashboard (e.g., RM800/mo), adds its price_id to our plan record, brand pays via normal checkout. Clean, everything through Stripe.
- **Option B**: Admin manually activates (already built). Payment is offline (bank transfer, invoice).

Option A is cleaner and keeps everything in Stripe. For the viva demo: admin can show they'd create a custom Stripe price, assign it, and the brand pays through the same flow.

### Daniel's Corrections (Final Tier Breakdown)

| Feature | Basic (RM49) | Premium (RM149) | Custom |
|---------|-------------|----------------|--------|
| Bins | 3 | 15 | Negotiated |
| Outlets | 3 | 15 | Unlimited |
| Staff | 3 | 10 | Unlimited |
| AI detection | Yes | Yes | Yes |
| Brand detection | Yes | Yes | Yes |
| Route optimization | Yes | Yes | Yes |
| Vouchers | Yes | Yes | Yes |
| API access (ERP integration) | No | No | Yes |

**Daniel's reasoning:**
- Basic shouldn't be cruel — 3 bins in 1 outlet is stupid, make it 3 outlets
- Brand detection YES on all — prevents dumping competitor cups (negative multiplier), core to the ecosystem
- Route optimization YES on all — it's a collector concern, not a brand concern, included by default
- Vouchers YES on all — if users earn points they MUST be able to cash out, no exceptions
- API access = ERP integration for large custom clients, makes sense as custom-only

**Enforcement decisions:**
- Hard limits: bin count, outlet count, staff count — checked BEFORE the create page loads (not just at submit). Show the limit with upgrade prompt on the page itself.
- Feature gates: NOT on sidebar. Everyone sees everything. Only API access is gated (custom only).
- Route monitoring: brand owners and store owners should see routes relevant to their bins/outlets in a read-only view

**Custom pricing decision:**
- Start with admin using Stripe dashboard to create custom price → assign price_id to plan
- If it works, later build in-app Stripe API integration so admin can create prices without leaving the app
- "That's how 3rd party integrations should work"

**Route visibility insight (new):**
- Store owner can see: "Assigned collector is doing pickup at ZUS Coffee, ETA 20min to Starbucks Hillside"
- Scoped to their bins/outlets but aware of the collector's full route context
- This is a future build but the schema supports it (collection_routes + route_stops + outlet relationships)

### Daniel's follow-up — deeper thinking needed

> What does seeder features JSON have to do with anything? No need to edit schema?
> Go into hard planning mode. Think twice on everything.
> When enforcement is done, we need to complete custom tier.
> Then dynamic pricing: yearly subscriptions, loyalty discounts (1 year = cheaper next year), admin-mutable campaigns (20% off first year for new brands, early adopter 10% extra), monthly vs yearly toggle.
> Lecturer must feel like this can attract people.

**Daniel is right — this needs schema work, not just seeder changes.** The `features` JSON on plans is not enough. We need:

1. **Plan limits table** or structured columns — not loose JSON. Schema must enforce what each plan allows.
2. **Pricing model flexibility** — monthly vs yearly, promotional pricing, campaign discounts, loyalty pricing
3. **Coupons/promotions** — Stripe has native coupon/promotion code support. We should use it.
4. **Custom tier completion** — admin creates custom Stripe prices via our app, not Stripe dashboard

This is a full planning session, not a quick fix.

### ChatGPT's verdict (Session 7)

**The answer: Hybrid. Schema columns for enforced limits. JSON only for loose metadata.**

Key takeaways:
- Custom is NOT unlimited. Custom = negotiated package with per-org overrides.
- `plans` table: structured columns for bin_limit, outlet_limit, staff_limit
- `organization_subscriptions` table: nullable override columns (custom_bin_limit, etc.)
- `effective_limit = org_override ?? plan_default ?? unlimited`
- Features JSON stays ONLY for boolean flags (api_access) and descriptive notes
- Stripe handles discount execution (coupons, promo codes). Our DB stores business meaning (why campaign exists, who's eligible).
- For viva: Basic + Premium + Custom + yearly + 1-2 discount examples. Not a full campaign engine.

**Build order confirmed:**
1. plans table gets proper limit columns ✅
2. org_subscriptions gets override columns ✅
3. Helper methods compute effective limits ✅
4. UI shows plan entitlements ✅
5. Enforcement checks limits ✅
6. Custom subscription override form for admin ✅
7. **Custom tier Stripe product/price flow — IN PROGRESS**
8. Yearly pricing
9. 1-2 discount/campaign examples

---

## Session 8 — 2026-04-06, ~5:00 AM MYT — Custom Tier Stripe Integration

### The Problem

Admin customizes a subscription (RM10,000/yr, 200/200/200 limits) but the brand owner still sees "RM0/mo, contact admin." The billing page doesn't reflect the custom arrangement because there's no Stripe price created for the custom deal.

### Daniel's Proposed Flow
1. Admin converts lead → custom subscription created (pending_payment)
2. Admin customizes: sets limits, price, billing interval via `/admin/billing/{id}/customize`
3. Admin clicks "Create Stripe Price" → backend calls Stripe API → creates Product + Price for this org
4. Stripe price_id saved to `organization_subscriptions.stripe_price_id`
5. Brand owner visits `/brand/billing` → sees custom price + "Pay via Stripe" button
6. Brand owner pays → Stripe checkout for their exact custom price → subscription activates

### Schema Change
Add `stripe_price_id` nullable string column to `organization_subscriptions` — the org-specific Stripe price for custom deals.

### Key Design Decision
Custom orgs each get their own Stripe Product + Price. Admin creates them via our UI. This is how 3rd party integrations should work — admin stays in our app, our backend calls Stripe APIs.

### Business Context
Mobius is a real startup: RM7k seed, RM6k market access grant, 2-year incubator program, RM300k revenue target. The work must be production-quality.

---

## Session 7 — 2026-04-06, ~3:30 AM MYT — Custom Tier & Schema Design

### Daniel's Thinking

> If database columns are source of truth, good. But what about custom tier? Wouldn't loosely typed JSON be better?
> Custom = unlimited, but how unlimited? Starbucks (nationwide, every state) vs Luckin (KL only) — both custom tier but wildly different scale and price.
> They won't pay the same custom price after negotiation.

### The Real Insight

Daniel is right. The problem isn't "columns vs JSON" — it's that **Custom is not a fixed plan. It's a per-org negotiation.**

Basic and Premium are products on a shelf — fixed price, fixed limits, same for everyone.
Custom is a contract — negotiated price, negotiated limits, unique per org.

So the question becomes: **do we model Custom as a plan at all, or as something else?**

### Analysis

**Option A: Custom is a plan with overridable limits**

The `plans` table has columns for limits (bin_limit, outlet_limit, staff_limit). Basic and Premium have fixed values. Custom has NULL for all limits (meaning unlimited by default).

BUT — when admin negotiates with Starbucks (500 bins, 200 outlets, RM2000/mo) vs Luckin (50 bins, 15 outlets, RM500/mo), those specifics need to live somewhere.

Where? On the `organization_subscriptions` table as **overrides**:
- `org_subscriptions.custom_bin_limit` — if set, overrides plan's bin_limit
- `org_subscriptions.custom_outlet_limit` — same
- `org_subscriptions.custom_staff_limit` — same
- The Stripe price is already per-org (admin creates a custom Stripe price for each deal)

So the enforcement logic becomes:
```
effective_bin_limit = org_subscription.custom_bin_limit ?? plan.bin_limit ?? unlimited
```

This is clean: fixed plans have fixed limits, custom plans have per-org overrides.

**Option B: All limits live on org_subscriptions (no plan columns)**

Every org's limits are set when the subscription is created. Basic creates with (3, 3, 3). Premium with (15, 15, 10). Custom with whatever admin sets.

Downside: if we change Basic's bin limit from 3 to 5, we'd have to update every existing Basic org_subscription. With plan columns, we just change the plan and everyone gets it.

**Option C: Plans have defaults, org_subscriptions have overrides (hybrid)**

Best of both:
- `plans` table: `bin_limit`, `outlet_limit`, `staff_limit` — the defaults
- `organization_subscriptions` table: `custom_bin_limit`, `custom_outlet_limit`, `custom_staff_limit` — nullable overrides
- Enforcement: `override ?? plan_default ?? unlimited`

This means:
- Basic orgs: limits come from plan (3, 3, 3)
- Premium orgs: limits come from plan (15, 15, 10)
- Custom Starbucks: overrides (500, 200, 50)
- Custom Luckin: overrides (50, 15, 10)
- If we raise Basic bin limit to 5, all Basic orgs get it automatically

### Recommendation: Option C (Hybrid)

**Schema changes needed:**

`plans` table — add columns:
- `bin_limit` INT nullable (null = unlimited)
- `outlet_limit` INT nullable
- `staff_limit` INT nullable

`organization_subscriptions` table — add columns:
- `custom_bin_limit` INT nullable (overrides plan if set)
- `custom_outlet_limit` INT nullable
- `custom_staff_limit` INT nullable
- `custom_price_monthly` DECIMAL nullable (for display — actual charge is in Stripe)

Keep `features` JSON for boolean feature flags (api_access, etc.) that aren't numeric limits.

**Enforcement helper:**
```php
// On Organization model
public function getEffectiveLimit(string $key): ?int
{
    $sub = $this->subscription;
    $override = $sub?->{"custom_{$key}"};
    if ($override !== null) return $override;
    return $sub?->plan?->{$key}; // null = unlimited
}
```

### Dynamic Pricing (Stripe-native)

For campaigns, discounts, loyalty pricing — Stripe handles all of this:

| Feature | Stripe concept | How it works |
|---------|---------------|-------------|
| Monthly vs yearly | Two Stripe Prices per product | Toggle at checkout |
| First year 20% off | Stripe Coupon (duration: once, percent_off: 20) | Applied at checkout |
| Early adopter 10% extra | Stripe Coupon (stackable) | Applied by admin |
| Loyalty (year 2 cheaper) | Stripe Subscription Schedule | Auto-applies after first period |
| Custom pricing | Stripe Price (per-org) | Admin creates in dashboard or via API |

We don't need to build a pricing engine — Stripe IS the pricing engine. Our job is to:
1. Let admin create/manage coupons via our app (using Stripe API)
2. Apply coupons during checkout
3. Display the effective price to the brand owner

### What to build for the viva

Priority order:
1. Add limit columns to plans + override columns to org_subscriptions
2. Build enforcement helper on Organization model
3. Add limit checks to create pages (show "X of Y used")
4. Admin can set custom limits when activating a custom subscription
5. Stripe coupons: admin creates a coupon, applies it during conversion
6. Monthly/yearly toggle on pricing page + checkout

### Daniel's answers
1. **Stripe owns billing cycle.** Ticket it — use Stripe simulation for demo. Our system syncs with Stripe, not the other way.
2. **Custom tier STILL goes through Stripe.** Admin creates a Stripe product/invoice for the custom deal, brand owner pays it through Stripe billing portal. Everything tracked in Stripe.
3. **Flow first, emails later.** Skip mailtrap for now.

---

## Session 4 — 2026-04-05, ~10:30 PM MYT — Full Flow Audit for Miss Kee

### What Daniel said
> Before we do all this, first rescan our flow and see how far we've made it for Miss Kee, and where is the most logical steps to implement first.

### Flow Audit — What Works Right Now

| Step | Status | What happens |
|------|--------|-------------|
| 1. Visitor sees landing | WORKS | Pricing cards: Basic RM49, Premium RM149, Custom |
| 2. Clicks plan → lead form | WORKS | Form at /get-started?plan={id}, creates registration_request |
| 3. Thank you page | WORKS | Simple confirmation |
| 4. Admin sees leads | WORKS | /admin/leads — table with status badges |
| 5. Admin views lead detail | WORKS | /admin/leads/{id} — all info, approve/reject |
| 6. Admin converts lead | WORKS | Creates org + brand + user + subscription (pending_payment) |
| 7. Conversion success | WORKS | Shows credentials (email + generated password) |
| 8. Brand owner logs in | WORKS | Redirects to /brand dashboard |
| 9. Brand dashboard | WORKS | Shows org, brand, subscription status, outlets |
| 10. Billing — pay via Stripe | WORKS | Stripe Checkout → payment → status flips to active |
| 11. Brand manages staff | WORKS | /brand/staff — create store_owner users |
| 12. Brand/admin creates outlet | WORKS | /brand/outlets or /admin/outlets — Google Places address |
| 13. Admin pairs bin | WORKS | /admin/bins — auto-serial, assigns to outlet |
| 14. Admin dashboard stats | WORKS | Real counts: users, orgs, leads, outlets, bins |
| 15. Profile + Account settings | WORKS | Phone, roles, org, points — per role |

### What's BROKEN or INCOMPLETE

| # | Issue | Impact for viva |
|---|-------|----------------|
| 1 | Admin billing page is placeholder | Lecturer can't see subscription management from admin side |
| 2 | Stripe billing portal link untested | Brand owner can't manage/cancel subscription via Stripe |
| 3 | Custom plan has no payment flow | Can't demo enterprise/custom deal |
| 4 | Hardcoded 1-year dates on payment success | Looks wrong, doesn't sync with Stripe |
| 5 | Stripe subscription wiped but our DB still shows it | Sync gap |
| 6 | Landing page plan perks empty | Cards look bare |
| 7 | No voucher system | Lecturer flagged this specifically |
| 8 | No detection/recycling pipeline | Core tech not shown |
| 9 | No route optimization | Lecturer flagged this |

### What Miss Kee Actually Flagged (from VIVA_FIXES.md)

1. Payment module ← WE HAVE THIS NOW (Stripe works)
2. Contract lifecycle (start/end/renewal) ← PARTIALLY (dates are wrong, but Stripe tracks it)
3. Route optimization ← NOT IN v2 YET
4. Store owner ↔ real bin ← PARTIALLY (outlet + bin CRUD exists, no live data)
5. Brand subscription payment ← WORKS
6. Vouchers with dynamic quotas ← NOT YET

### Priority Order for Tonight

**Tier 1 — Complete the subscription story (30 min):**
- Admin billing page: real table of all org subscriptions
- Fix dates: remove hardcoded 1-year, let Stripe be source of truth
- Billing portal link for brand owners

**Tier 2 — Landing page polish (15 min):**
- Fill in plan perks/features for Basic, Premium, Custom
- Make the pricing section tell a compelling story

**Tier 3 — Vouchers (1-2 hrs):**
- Normal vouchers: always redeemable, no quota
- Dynamic vouchers: time-limited, quota-capped (Hari Raya promo)
- Admin creates voucher templates
- Public user redeems with points

**Tier 4 — Everything else (defer to after viva):**
- Detection pipeline, route optimization, emails
- These are core tech but need significant work
- Better to show a clean business flow than a broken tech demo

### The Viva Demo Script (What Daniel walks through)

1. "Here's our landing page — brands can see pricing and submit interest"
2. "I'm the admin — here's a lead that just came in" → show lead detail
3. "I convert them to a brand" → show conversion with credentials
4. "Now they log in and see their dashboard — subscription pending payment"
5. "They go to billing, pay via Stripe" → live Stripe checkout
6. "Subscription is now active — they can manage staff, create outlets"
7. "As admin, I pair a bin to their outlet" → show bin creation
8. "They see their outlet and bin in their dashboard — system is live"
9. "Users earn points from recycling and can redeem vouchers" (if we build it)

Steps 1-8 ALL WORK RIGHT NOW. Step 9 is the next build.
