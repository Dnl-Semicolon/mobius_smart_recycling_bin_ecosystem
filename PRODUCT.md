# PRODUCT.md — Mobius

> Brand brief for `/`. Output of `/impeccable teach`. Read once, edit if wrong, commit. `/impeccable shape` and `/impeccable craft` will read this every time.

---

## Register

**Brand.** This is the public face of Mobius. Design is the product on `/`. The actual app lives elsewhere (mobile, dashboard, bin-client) and uses `product` register.

## Who is this for

Three audiences, all surfaced on a single `/` page through a shared narrative and three distinct CTAs.

1. **Brand and store partners (B2B).** Beverage brands and outlet operators evaluating whether Mobius is worth deploying at their stores. They want to see ROI evidence, brand-loyalty mechanics, dashboard previews, and a partner-onboarding path. CTA: "Become a partner" — links *to* the existing partner-form route (do not touch the form itself this session — it is set for upgrade later — but the link from `/` is required and must be styled as a primary CTA in the chosen spin).
2. **Public recyclers (B2C).** People who would scan a QR on a Mobius bin, dispose of a cup, and earn points/vouchers. They want to know "how does this work" and "where's a bin near me." CTA: "Get the app" or "Find a bin" — routes to App Store/Play Store stub or a placeholder map page. The mobile app screenshots and short demo loops live here.
3. **Agencies, councils, and government (B2G).** Procurement-track buyers evaluating Mobius as urban sustainability infrastructure. They do not sign up online; they collect vendor info and brief their committees. CTA: "Request procurement deck" — routes to a gated PDF download form (name, organisation, email) or a Calendly stub. The PDF itself is a deferred deliverable.

A shared CTA "See the tech" links to a `/tech` or `/docs` stub that lives outside this scoping.

## What Mobius is

Mobius is a full-stack **ecosystem** for branded recycling at scale. Hardware, AI, and software, built end-to-end and operated as a single platform.

- Hardware: branded smart recycling bins deployed at outlets.
- AI: dual-axis computer vision that identifies both **waste type** (cup, lid, straw, liquid waste) and **cup brand** (e.g. Starbucks, Mixue), enabling brand-loyalty rewards and competitor-deterrent point logic.
- Software: a mobile app for recyclers (points, streaks, vouchers), a dashboard for partner brands (cup-loyalty analytics, outlet performance), a route-optimisation engine for collection logistics (OSRM + VROOM), and a bin client that runs on the hardware itself.

"Working" looks like: a customer drops a Starbucks cup into a Starbucks-branded Mobius bin, gets the full brand multiplier in points, the cup-loyalty card on the partner dashboard increments its "match" counter, and the collection route for that zone is automatically re-optimised when fill thresholds are crossed.

The positioning sentence: **Mobius is the operating layer for branded recycling — hardware, AI, and software, built end-to-end.**

## Brand personality (3 spins to explore)

This redesign explicitly tries three personality directions in parallel branches. Same content, same IA, three aesthetic spins. The user picks the winner after seeing them side-by-side.

| Spin | Three words | Anchor refs | Reads as |
|---|---|---|---|
| **sharp-future** | Sharp, confident, future-tense | Vercel, Neo Mirai, Linear marketing | "We are inevitable. The future of recycling is operational, not aspirational." |
| **precise-premium** | Precise, technical, premium | Apple developer pages, Linear app, Rivian | "We built every layer of this carefully. Look at the craft." |
| **calm-infra** | Calm, evidence-driven, infrastructural | Cloudflare, Stripe Climate, Stripe docs | "Recycling infrastructure for cities and brands. Boringly reliable. Lots of charts." |

Branch layout (sibling branches; git refs cannot have the same name as both a branch and a path prefix, so dashes are used between `home-hifi` and the spin name):
- `main` — baseline (the current AI-slop `/`). Untouched.
- `feat/home-wireframe` — neutral mid-fi wireframe with labeled grayscale placeholders, desktop-only. Personality-agnostic.
- `feat/home-hifi` — integration / shared-scaffolding branch for the three hifi spins (responsive shell, layout primitives, motion utilities that all three spins inherit). Diverges from wireframe by adding responsive + animations but staying personality-neutral.
- `feat/home-hifi-sharp-future`, `feat/home-hifi-precise-premium`, `feat/home-hifi-calm-infra` — three sibling branches off `feat/home-hifi`, each carrying a single personality spin.

## Anchor references

Specific apps and sites, not adjectives.

- **impeccable.style/neo-mirai** — dense editorial layout, asymmetric grid, motion-driven scroll choreography. Primary visual reference.
- **Stripe** — climate page, docs, dashboard. Primary structural reference for the scrollable-masterpiece nav and the calm-infra spin.
- **Vercel** — marketing site. Bold display type, dark sections, "future-tense" voice. Primary reference for sharp-future spin.
- **Linear** — calm restraint, hairline borders, monospace numerals. Reference for precise-premium spin.
- **Apple developer pages** — engineering-grade attention to micro-detail. Reference for precise-premium spin.
- **Rivian** — premium hardware brand voice. Reference for precise-premium spin.
- **Cloudflare** — infrastructural, data-forward, almost technical-report aesthetic. Reference for calm-infra spin.
- **Stripe Climate** — sustainability done as infrastructure, not as eco-warmth. Reference for calm-infra spin.

## Anti-references

These are the patterns this redesign explicitly rejects. The current `/` violates several of them.

1. **The current `/` itself.** Green-on-white, three-card How-It-Works, three-tier pricing template, light gray separators. This is the "before" state. The whole point of the redesign is to escape it.
2. **Generic eco/sustainability sites.** Heavy greens, leaf icons, forest stock photos, "for a greener tomorrow" copy. Mobius is infrastructure, not an NGO.
3. **Generic AI-app look.** Purple gradients, glow blobs, hero "AI" badges, "Powered by AI" stickers, abstract neural-network background graphics.
4. **Default shadcn / Bootstrap / MUI out of the box.** Out-of-the-box component shapes signal "we did not design this." Components are allowed but must be restyled into the chosen spin.
5. **Hero-metric template.** Giant counter + three feature cards + CTA = entire page. The current `/` is exactly this. Banned.

## Sections (locked)

In scroll order on `/`:

1. **Hero.** Headline, subheadline, dual CTA (audience-aware), one large visual (AI-generated abstract image, ideally with subtle motion).
2. **How it works + dual-brand AI explainer.** Combined section. The three-step recycler flow on one side, the dual-brand-AI mechanic (cup brand × bin brand → multiplier) on the other.
3. **Three-audience proof + live impact counter.** Three "For Brands / For Recyclers / For Councils" panels, each with its own value prop and CTA. Live counter strip (cups recycled, kg CO2 saved, partner brands) above or below.
4. **Route optimisation + IoT showcase.** Map visual, brief copy on the OSRM/VROOM auto-dispatch story, IoT bin photography or render. Sells the "ecosystem" claim.
5. **Pricing / partnership tiers.** May live as either an in-page section on `/` *or* as a dedicated `/pricing` route — designer's call per spin (the calm-infra spin probably wants a full page, the sharp-future spin probably wants a teaser strip on `/` linking to the full page). Real pricing for partners carries over from the current site (Basic/Premium/Custom) but is visually re-skinned per spin and re-framed per audience. Whichever pattern a spin uses, `/` must contain at minimum a clear pricing entry-point with a primary CTA button.
6. **Team.** Mobius as solution-makers, not as a student project. No FYP origin, no "we are a final year project" language. Founders/operators framing only.
7. **Footer.** Contact, socials, demo request, legal stubs.

Nav: hybrid sticky-anchor + dense-editorial. Anchors jump to each section. Sticky on scroll. Possibly compresses or recolours per section.

## Visual approach

**Hybrid:** one AI-generated abstract hero image + real product screenshots + code-driven motion for transitions and accents.

- **AI-gen.** I (Claude) write Midjourney/Gemini prompts for the hero and possibly one or two section accents. User generates. We optionally pass them through Runway or Kling for short subtle motion loops.
- **Product screenshots.** Real captures from the mobile app, the dashboard, and the bin-client. These carry the "this exists" credibility for partners and councils.
- **Code motion.** Framer Motion for choreography, CSS keyframes for restraint, optional WebGL/Three.js shaders for the calm-infra "data infrastructure" feel. No motion for motion's sake — every transition either reveals content or signals state.

**No-modal rule.** Modals are reserved for marketing/brand moments (e.g. a hero video) or as stubs to real product CRUD that lives elsewhere. No marketing-flow inside a modal.

## Stack

- **Framework:** Inertia.js v3 + React 18 on Laravel 12 (`backend-v2`). Existing stack.
- **Routing:** Wayfinder for typed route bindings between React and Laravel controllers.
- **Styling:** Tailwind CSS, with a per-spin design-token layer.
- **Motion:** Framer Motion as default, plus CSS where Framer is overkill.
- **Forms:** existing Inertia `useForm` patterns. Partner form already exists; do not touch this session.
- **Asset pipeline:** Vite, with AI-gen images dropped into `resources/images/` and motion loops as MP4/WebM.

## Accessibility

WCAG AA baseline. Specifically:

- Colour contrast 4.5:1 for body text, 3:1 for large text. Per-spin token sets must verify.
- All motion respects `prefers-reduced-motion` — animations downgrade to a single fade or no transition.
- All audience-CTAs are reachable by keyboard, focus-visible, and announce their destination clearly.
- Live impact counters are not the only place numerical claims appear (screen-reader users get static fallbacks).

## Out of scope (this session and probably the next)

- Procurement deck PDF (the actual document).
- Real `/tech` or `/docs` pages.
- Map-page for "Find a bin near me."
- Partner form upgrade (form exists, do not touch).
- Mobile app store listings (if no live store entry exists, the CTA is a stub).
- Backend changes. This is `/`-only work.

## Process notes for future sessions

- Read this file before any `/` work.
- `/impeccable shape <feature>` is the next step before touching code on a hifi spin. Output is a written brief, not code.
- `/impeccable craft <feature>` produces real code in Inertia + React.
- Per user habit: log brainstorm sessions to `BRAINSTORM.md`.
- Per user habit: show a text layout plan before writing UI code. Get approval first.
- Per user safety rule: never run git commands. User handles all git.
