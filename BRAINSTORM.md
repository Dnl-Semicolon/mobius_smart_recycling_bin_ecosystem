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

### What needs to change
1. Seeder: hardcode stripe_price_id values (not env()), update prices to RM49/RM149
2. Landing page: reflect RM49/RM149
3. Convert action: set status='pending_payment' instead of 'active'
4. Add 'pending_payment' to subscription status enum in migration
5. Billing page: show "Pay Now" when status=pending_payment
6. Billing success handler: update org_subscription status to 'active', set dates based on Stripe
7. Plan names: Basic and Premium (matching Stripe)
