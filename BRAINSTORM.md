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
