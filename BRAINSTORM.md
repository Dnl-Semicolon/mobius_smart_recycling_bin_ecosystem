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

*Waiting for Daniel's next prompt.*
