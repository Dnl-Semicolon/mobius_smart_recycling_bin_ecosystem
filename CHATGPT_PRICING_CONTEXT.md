# Mobius Smart Recycling Bin Ecosystem — Pricing Model Design Context

## What is Mobius?

Mobius is a B2B SaaS platform for beverage brands (Starbucks, Mixue, ZUS Coffee, etc.) in Malaysia. We deploy AI-powered smart recycling bins at their outlets. The system:
- Detects waste types (cups, lids, straws) and identifies the cup brand via computer vision
- Awards points to public users who recycle properly
- Optimizes collection routes for waste collectors
- Provides analytics to brand owners about recycling behavior at their outlets

## Who pays?

**Beverage brand organizations** (franchise HQs) are the paying customers. They subscribe to a plan to deploy bins at their outlets. The flow:

1. Brand visits landing page, sees pricing, submits interest form (lead)
2. Admin reviews lead, converts to a brand (creates org + brand + user account + subscription)
3. Brand owner pays via Stripe Checkout
4. Admin creates outlets and pairs bins
5. System goes live

## Current state

We have three plans seeded in the database:

| Plan | Monthly Price | Stripe Price ID |
|------|-------------|-----------------|
| Basic | RM49 | price_1THxnGPdFnIiZZ0SiAdiATkW |
| Premium | RM149 | price_1THxnaPdFnIiZZ0SLL6GxwWs |
| Custom | RM0 (negotiated) | NULL (admin creates per-org) |

Current `features` JSON on each plan is just a loose bag — nothing enforces it.

Stripe Checkout + webhooks + billing portal are wired up and working for Basic and Premium. Custom has manual activation by admin.

## Agreed tier limits

| Feature | Basic (RM49/mo) | Premium (RM149/mo) | Custom (negotiated) |
|---------|----------------|-------------------|-------------------|
| Bins | 3 max | 15 max | Per negotiation |
| Outlets | 3 max | 15 max | Per negotiation |
| Staff accounts | 3 max | 10 max | Per negotiation |
| AI waste detection | Yes | Yes | Yes |
| Brand detection (competitor deterrent) | Yes | Yes | Yes |
| Route optimization | Yes | Yes | Yes |
| Voucher/rewards system | Yes | Yes | Yes |
| API access (ERP integration) | No | No | Yes |

Key insight: **every feature that makes the ecosystem work is available on ALL tiers**. The only differentiators are scale (how many bins/outlets/staff) and API access for enterprise ERP integration.

## The Custom Tier Problem

Custom is NOT a fixed plan — it's a per-org negotiation.

**Example scenario:**
- **Starbucks Malaysia**: 300+ outlets nationwide, every state. Wants 500 bins. Negotiated price: RM2,000/mo.
- **Luckin Coffee Malaysia**: 20 outlets in KL only. Wants 50 bins. Negotiated price: RM500/mo.

Both are "Custom" but with completely different limits and prices. The schema must support this.

## Proposed schema approach

**Plans table** (defaults):
```
plans
├── id
├── name (Basic, Premium, Custom)
├── price_monthly, price_yearly
├── bin_limit (INT nullable, null = unlimited)
├── outlet_limit (INT nullable)
├── staff_limit (INT nullable)
├── features (JSON for boolean flags like api_access)
├── stripe_price_id
└── is_active
```

**Organization subscriptions table** (per-org overrides):
```
organization_subscriptions
├── id
├── organization_id
├── plan_id
├── status (pending_payment, active, past_due, cancelled, expired)
├── custom_bin_limit (INT nullable — overrides plan if set)
├── custom_outlet_limit (INT nullable)
├── custom_staff_limit (INT nullable)
├── custom_price_monthly (DECIMAL nullable — for display)
├── starts_at, ends_at, renews_at
└── timestamps
```

**Enforcement logic:**
```
effective_limit = org_subscription.custom_X_limit ?? plan.X_limit ?? unlimited
```

- Basic org: limits come from plan (3, 3, 3)
- Premium org: limits come from plan (15, 15, 10)
- Custom Starbucks: overrides (500, 200, 50)
- Custom Luckin: overrides (50, 15, 10)
- If we raise Basic from 3 to 5 bins, all Basic orgs get it automatically

## Dynamic pricing requirements

The lecturer wants to see pricing that can **attract brands** — not just flat rates. Ideas:

1. **Monthly vs yearly billing** — yearly should be cheaper (e.g., 2 months free)
2. **First year discount** — 20% off first year for new brands
3. **Early adopter discount** — extra 10% for brands joining in 2026
4. **Loyalty pricing** — year 2 is cheaper than year 1 (reward retention)
5. **Campaigns** — admin can create time-limited promotions (e.g., "Hari Raya special: 30% off first 3 months")

## Stripe capabilities we can leverage

| Feature we want | Stripe concept | Notes |
|----------------|---------------|-------|
| Monthly vs yearly | Two Stripe Prices per product | One monthly, one annual — user picks at checkout |
| First year 20% off | Stripe Coupon (`duration: once`, `percent_off: 20`) | Applied at checkout, auto-expires after first billing cycle |
| Early adopter 10% | Stripe Coupon (stackable with above) | Admin creates, applies during conversion |
| Loyalty (year 2 cheaper) | Stripe Subscription Schedule | Phases: year 1 at price A, year 2 at price B |
| Campaigns | Stripe Promotion Codes | Public codes (HARIRAYA2026) or admin-applied |
| Custom pricing | Stripe Price (per-org) | Admin creates a unique price in Stripe for each custom deal |

## Questions for you (ChatGPT)

1. **Schema validation**: Is the hybrid approach (plan defaults + org overrides) the right pattern? Or is there a cleaner way to model per-org custom limits?

2. **Pricing model attractiveness**: Given this is a B2B SaaS for beverage brands in Malaysia, what pricing strategies would make the most sense? The current RM49/RM149 feels too simple. What would make a franchise HQ say "this is a no-brainer"?

3. **Campaign/discount schema**: Should we model campaigns/discounts in our own database, or rely entirely on Stripe coupons? If our own DB, what does the schema look like?

4. **Yearly pricing**: What's the typical discount for annual billing in B2B SaaS? 2 months free (16.7% off)? 20%? Something else?

5. **Custom tier onboarding**: For the admin creating a custom deal — what fields should the "Create Custom Subscription" form have? Just limits + price, or more (contract term, SLA, dedicated support contact)?

6. **Feature gating UX**: When a Basic org tries to create a 4th bin, what's the best UX? A modal? A banner on the create page? A disabled button with tooltip?

## Tech stack

- Laravel 13 (PHP 8.4)
- React 19 + Inertia.js v3
- Tailwind CSS v4 + shadcn/ui
- Laravel Cashier v16 (Stripe integration)
- MySQL

## Constraints

- This is a final year project (FYP) — viva is today at 2:30 PM
- The lecturer specifically flagged: payment module, contract lifecycle, dynamic vouchers
- Code must work, not just look good
- Building one view at a time, testing as we go
