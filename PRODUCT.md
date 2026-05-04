# PRODUCT.md / Mobius

> Brand brief for `/`. Output of `/impeccable teach`. Read once, edit if wrong, commit. `/impeccable shape` and `/impeccable craft` will read this every time.

---

## Register

**Brand.** This is the public face of Mobius. Design IS the product on `/`. The actual app lives elsewhere (mobile, dashboard, bin-client) and uses the `product` register.

## Who is this for

Three audiences, all surfaced on a single `/` page through a shared narrative and three distinct CTAs.

1. **Brand and store partners (B2B).** Beverage brands and outlet operators evaluating whether Mobius is worth deploying at their stores. They want to see ROI evidence, brand-loyalty mechanics, dashboard previews, and a partner-onboarding path. CTA: "Become a partner". Links *to* the existing partner-form route at `/get-started`. Do not touch the form itself this session (it is set for upgrade later), but the link from `/` is required and must be styled as the primary CTA in the hero.
2. **Public recyclers (B2C).** People who would scan a QR on a Mobius bin, dispose of a cup, and earn points or vouchers. They want to know "how does this work" and "where's a bin near me." CTA: "Get the app" or "Find a bin". Routes to App Store / Play Store stub or a placeholder map page. The mobile app screenshots and short demo loops live here.
3. **Agencies, councils, and government (B2G).** Procurement-track buyers evaluating Mobius as urban sustainability infrastructure. They do not sign up online; they collect vendor info and brief their committees. CTA: "Request procurement deck". Routes to a gated PDF download form (name, organisation, email) or a Calendly stub. The PDF itself is a deferred deliverable.

A shared CTA "See the tech" links to a `/tech` or `/docs` stub that lives outside this scoping.

The hero leads with the partners CTA only. Recyclers and councils get distinct CTAs further down the page (in the Three-audience proof section). No three-button row in the hero. No audience-switcher tab.

## What Mobius is

Mobius is a full-stack **ecosystem** for branded recycling at scale. Hardware, AI, and software, built end-to-end and operated as a single platform.

- Hardware: branded smart recycling bins deployed at outlets.
- AI: dual-axis computer vision that identifies both **waste type** (cup, lid, straw, liquid waste) and **cup brand** (e.g. Starbucks, Mixue), enabling brand-loyalty rewards and competitor-deterrent point logic.
- Software: a mobile app for recyclers (points, streaks, vouchers), a dashboard for partner brands (cup-loyalty analytics, outlet performance), a route-optimisation engine for collection logistics (OSRM and VROOM), and a bin client that runs on the hardware itself.

"Working" looks like: a customer drops a Starbucks cup into a Starbucks-branded Mobius bin, gets the full brand multiplier in points, the cup-loyalty card on the partner dashboard increments its "match" counter, and the collection route for that zone is automatically re-optimised when fill thresholds are crossed.

The positioning sentence: **Mobius is the team beverage brands hire to design, install, and operate their disposable-cup recycling layer. Hardware, AI, and software, all tailored to your outlets.**

## Brand personality (winning spin: sharp-future)

This redesign explored three personality directions in parallel branches with the same content and same IA. As of 2026-04-30, **`sharp-future` is the sole winner.** Future shape briefs and craft work target `feat/home-hifi-sharp-future` only. The other two spins are frozen on their branches as historical "we considered it" evidence for the Market Access Grant story and the `~/code/mobius-research` upgrade trail.

| Spin | Status | Three words | Anchor refs | Reads as |
|---|---|---|---|---|
| **sharp-future** | **Winner** | Sharp, confident, future-tense | Vercel, Neo Mirai, Linear marketing | "We are inevitable. The future of recycling is operational, not aspirational." |
| **precise-premium** | Frozen | Precise, technical, premium | Apple developer pages, Linear app, Rivian | "We built every layer of this carefully. Look at the craft." |
| **calm-infra** | Frozen | Calm, evidence-driven, infrastructural | Cloudflare, Stripe Climate, Stripe docs | "Recycling infrastructure for cities and brands. Boringly reliable. Lots of charts." |

Branch layout (sibling branches; git refs cannot have the same name as both a branch and a path prefix, so dashes are used between `home-hifi` and the spin name):
- `main`. Baseline (the current AI-slop `/`). Untouched until the full sharp-future page is ready to merge.
- `feat/home-wireframe`. Neutral mid-fi wireframe with labelled grayscale placeholders, desktop-only. Personality-agnostic. Frozen.
- `feat/home-hifi`. Integration / shared-scaffolding branch (responsive shell, layout primitives that all spins inherit). Frozen.
- `feat/home-hifi-sharp-future`. **Active.** Carries the winning spin. Future section work lands here.
- `feat/home-hifi-precise-premium`. Frozen.
- `feat/home-hifi-calm-infra`. Frozen.

## Anchor references (for sharp-future)

Specific apps and sites, not adjectives.

- **Vercel.com**. Marketing site. Bold display type, dark sections, "future-tense" voice. Primary structural reference.
- **impeccable.style/neo-mirai**. Dense editorial layout, asymmetric grid, motion-driven scroll choreography. Primary visual reference.
- **Stripe.com homepage**. Drenched-color hero applied over a tinted-near-black base. Primary motion reference (gradient drift behaviour).
- **Linear.com marketing**. Restrained hairlines, monospace numerals, deliberate spacing. Reference for stat strip and section dividers.

## Anti-references

These are the patterns this redesign explicitly rejects. The current `/` violates several.

1. **The current `/` itself.** Green-on-white, three-card How-It-Works, three-tier pricing template, light gray separators. This is the "before" state. The whole point of the redesign is to escape it.
2. **Generic eco / sustainability sites.** Heavy greens, leaf icons, forest stock photos, "for a greener tomorrow" copy. Mobius is infrastructure, not an NGO.
3. **Generic AI-app look.** Purple gradients, glow blobs, hero "AI" badges, "Powered by AI" stickers, abstract neural-network background graphics.
4. **Default shadcn / Bootstrap / MUI out of the box.** Out-of-the-box component shapes signal "we did not design this." Components are allowed but must be restyled into the spin.
5. **Hero-metric template.** Giant counter, three feature cards, CTA, equals entire page. The current `/` is exactly this. Banned.

## Sections (locked)

In scroll order on `/`:

1. **Hero.** Headline, subheadline, single primary CTA (Become a partner) with a secondary text-link, sector-context stat strip, drenched motion-led visual. Shipped on `feat/home-hifi-sharp-future` (commit 53c3d90).
2. **How it works and dual-brand AI explainer.** Combined section. The three-step recycler flow on one side, the dual-brand-AI mechanic (cup brand × bin brand → multiplier) on the other. Shape brief pending.
3. **Three-audience proof and live impact counter.** Three "For Brands / For Recyclers / For Councils" panels, each with its own value prop and CTA. Live counter strip (cups recycled, kg CO2 saved, partner brands) above or below. Shape brief pending.
4. **Route optimisation and IoT showcase.** Map visual, brief copy on the OSRM and VROOM auto-dispatch story, IoT bin photography or render. Sells the "ecosystem" claim. Shape brief pending.
5. **Pricing and partnership tiers.** May live as either an in-page section on `/` or as a dedicated `/pricing` route. Real pricing for partners carries over from the current site (Basic, Premium, Custom) but is visually re-skinned and re-framed. Whichever pattern is used, `/` must contain at minimum a clear pricing entry-point with a primary CTA button. Shape brief pending.
6. **Team.** Mobius as solution-makers, not as a student project. No FYP origin language. Founders and operators framing only. Shape brief pending.
7. **Footer.** Contact, socials, demo request, legal stubs.

Nav: hybrid sticky-anchor and dense-editorial. Anchors jump to each section. Sticky on scroll. Compresses or recolours per section (sharp-future specifies translucent over hero, opaque deep-tinted near-black at scroll Y > 200).

## Visual approach

**Hybrid:** one AI-generated abstract hero image, plus real product screenshots, plus code-driven motion for transitions and accents.

- **AI-gen.** Claude writes Midjourney or Gemini prompts for the hero and possibly one or two section accents. User generates. Optionally pass them through Runway or Kling for short subtle motion loops. The current sharp-future hero ships a code-driven `HeroAurora` stand-in (three drifting radial blobs with mix-blend screen) that gets replaced when the real asset is generated. Outer dimensions stay so layout does not shift.
- **Product screenshots.** Real captures from the mobile app, the dashboard, and the bin-client. These carry the "this exists" credibility for partners.
- **Code motion.** `motion/react` (formerly Framer Motion) for choreography, CSS keyframes for restraint. No motion for motion's sake; every transition either reveals content or signals state.

**No-modal rule.** Modals are reserved for marketing or brand moments (e.g. a hero video) or as stubs to real product CRUD that lives elsewhere. No marketing-flow inside a modal.

## Stack

- **Framework:** Inertia.js v3 with React 18 on Laravel 12 (`backend-v2`). Existing stack.
- **Routing:** Wayfinder for typed route bindings between React and Laravel controllers. Use `getStarted().url` over hard-coded paths.
- **Styling:** Tailwind v4 with OKLCH tokens. Each spin lives under a `[data-theme="..."]` scope that redeclares both raw tokens (`--background`) AND the Tailwind theme tokens (`--color-background`). The double declaration is required because `@theme` resolves `var(--background)` once at `:root` and descendants that only redeclare the raw token still inherit the root-resolved color.
- **Motion:** `motion/react` as default, plus CSS keyframes for ambient drift effects.
- **Fonts:** Switzer (Indian Type Foundry, free via Fontshare CDN) for sharp-future. The other spins used General Sans (Fontshare) and Source Serif 4 plus Public Sans (Bunny Fonts). All chosen to avoid the impeccable reflex-reject list (Inter, DM Sans, Plus Jakarta, Plex, Outfit, Syne, Fraunces, Cormorant, Playfair, Newsreader).
- **Forms:** existing Inertia `useForm` patterns. Partner form already exists at `/get-started`; do not touch.
- **Asset pipeline:** Vite, with AI-gen images dropped into `resources/images/` and motion loops as MP4 / WebM.

## Accessibility

WCAG AA baseline. Specifically:

- Colour contrast 4.5:1 for body text, 3:1 for large text. The sharp-future scope (warm-near-white ink on midnight-teal background, electric cyan-emerald primary) must be re-verified after copy changes.
- All motion respects `prefers-reduced-motion`. Animations downgrade to a single fade or no transition. The aurora gradient and the staggered headline reveal both honour this via `motion-safe:` utility and `useReducedMotion()`.
- All audience-CTAs are reachable by keyboard, focus-visible, and announce their destination clearly.
- Live impact counters are not the only place numerical claims appear (screen-reader users get static fallbacks).

## Out of scope (this session and probably the next)

- Procurement deck PDF (the actual document).
- Real `/tech` or `/docs` pages.
- Map-page for "Find a bin near me."
- Partner form upgrade (form exists at `/get-started`, do not touch).
- Mobile app store listings (if no live store entry exists, the CTA is a stub).
- Backend changes. This is `/`-only work.

## Process notes for future sessions

- Read this file before any `/` work. Read `docs/shape/01-Hero.md` for the hero contract.
- `/impeccable shape <section>` is the next step before touching code on any new section. Output is a written brief, not code.
- `/impeccable craft <section>` produces real code in Inertia and React, runs the browser-iteration loop, and ships screenshots into `docs/shape/screens/`.
- Per user habit: log brainstorm sessions to `BRAINSTORM.md`.
- Per user habit: show a text layout plan before writing UI code. Get approval first.
- Per user safety rule: git is allowed in normal workflow (status, diff, branch, switch, commit, push, fetch, log). Destructive operations (`reset --hard`, `clean -f`, `branch -D`, `push --force`) require explicit confirmation. Never reinterpret "rollback / undo / revert" as a git operation; ask what the user actually wants reverted.
