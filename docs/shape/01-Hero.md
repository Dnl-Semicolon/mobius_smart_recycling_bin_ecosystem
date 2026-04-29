# Hero — Design Brief

> Output of `/impeccable shape Hero`. Read once, edit if wrong, confirm. `/impeccable craft Hero` reads this each time before code. Not a substitute for PRODUCT.md.

---

## 1. Feature Summary

The hero is the first viewport of `/` and the primary brand surface for Mobius. It carries the positioning, anchors the visual language of the entire page, and converts the highest-intent visitor (a beverage-brand decision-maker) into the partner-onboarding flow at `/get-started`. Recyclers and councils are visible through navigation and downstream sections, not through a competing hero CTA.

The brief covers four parallel deliverables sharing one structure:

- **wireframe** on `feat/home-wireframe`: mid-fi, desktop-only, neutral grayscale, labelled placeholders.
- **sharp-future** on `feat/home-hifi-sharp-future`: motion-led, drenched-color, Vercel and Neo Mirai energy.
- **precise-premium** on `feat/home-hifi-precise-premium`: restrained, hairline, product-photographic, Linear and Apple-developer energy.
- **calm-infra** on `feat/home-hifi-calm-infra`: dataviz-led, committed-color, Cloudflare and Stripe Climate energy.

## 2. Primary User Action

A beverage-brand decision-maker (HQ-side marketing, sustainability, or operations lead at a chain like Tealive, Mixue, ZUS, Starbucks Malaysia, or Coca-Cola Malaysia) lands on `/`, reads the hero in under five seconds, and forms one impression: **Mobius is the team I would hire to build my brand's recycling-revenue system.** The next action is either clicking the single primary CTA "Become a partner" (which routes to the existing `/get-started` Inertia page) or continuing to scroll for proof.

Secondary actions exist but are deliberately non-competing in the hero: recyclers and councils have their own CTAs *later* in the page (in the Three-audience proof section), not in the hero. This is a deliberate departure from the "audience switcher in hero" pattern. The hero commits to brands first.

## 3. Design Direction

The shared design laws from `SKILL.md` apply to every spin. The bans on hero-metric template, identical card grids, gradient text, side-stripe borders, glassmorphism-as-default, and modal-as-first-thought hold throughout. No em dashes anywhere in copy. OKLCH only, never `#000` or `#fff`, neutrals tinted toward the brand hue.

### Per-spin design direction

| Spin | Color strategy | Theme scene sentence | Anchor references |
|---|---|---|---|
| **wireframe** | Restrained, zero-chroma. Tinted-cool grayscale only. One placeholder accent reserved for "this is where colour lands." | "A second-year designer reviewing layout structure on a 27-inch monitor in a school studio at 11am, deciding whether the hero needs a stat row before any visual treatment is applied." → light, no commitment. | None per spin. The wireframe is the structural common ancestor. |
| **sharp-future** | **Drenched.** A single load-bearing chroma takes the hero. Reference: Stripe.com gradient hero applied to a midnight-teal base with electric cyan-emerald highlights. The image IS the colour. | "A regional brand-marketing director at her standing desk in a dim Singapore office at 6pm, reviewing competitive sustainability initiatives on a 32-inch monitor while her CMO asks for differentiation by Friday." → forces a dark hero. | Stripe.com homepage. Vercel.com. impeccable.style/neo-mirai. |
| **precise-premium** | **Restrained.** Tinted neutrals plus one accent at ≤10% surface. Reference: Linear's near-black on tinted-light cream, with a single warm copper accent reserved for the primary CTA only. | "An operations lead at a beverage HQ on his MacBook in a bright glass-walled meeting room, comparing vendors on a shared screen while his procurement team takes notes." → forces a light hero, restraint, museum-caption energy. | Linear app marketing. Apple developer documentation pages. Rivian.com vehicle detail pages. |
| **calm-infra** | **Committed.** A single institutional hue carries 30-60% of surface. Reference: Stripe Climate's verifiable-data palette and Cloudflare's orange dashboards, but specifically NOT eco-green and NOT corporate-navy. A calm slate-teal in the OKLCH 0.55 / 0.08 / 220 range carries the dataviz. | "A council sustainability officer reviewing the page on her work laptop in fluorescent municipal-office lighting, taking handwritten notes in a printed brief, planning to forward the link to two committee members by end of day." → forces a light, legible, institutional hero. | Stripe Climate's certifications page. Cloudflare's network-status pages. Stripe's docs front page. |

### Typography (per brand.md font-selection procedure)

For each spin, three brand-voice words are written first, then a font is picked from real catalogs. None of the reflex-reject list is used (Inter, DM Sans, Plus Jakarta, Instrument Sans/Serif, Plex, Space Mono/Grotesk, Fraunces, Cormorant, Playfair, Outfit, Syne, Newsreader, Lora, Crimson are all banned).

License decision (locked): open-source families only. Klim Type Foundry families (Söhne, Untitled) are reserved for a post-traction upgrade. Today's picks pass the physical-object cross-check below and avoid the reflex-reject list.

| Spin | Three brand-voice words | Display | Body | Numerals / mono |
|---|---|---|---|---|
| **wireframe** | Honest, structural, neutral | System UI sans (`-apple-system, BlinkMacSystemFont, "Segoe UI"`) | System UI sans | System UI sans, tabular figures. |
| **sharp-future** | Inevitable, kinetic, plural | **Switzer (Indian Type Foundry, free)** at heavy weights for the headline. Tight tracking, large-scale display rhythm. Switzer is a Söhne-family grotesque from a non-American foundry, which sidesteps the Inter/DM Sans monoculture. | **Switzer** for body, weight-driven contrast inside one family. | **JetBrains Mono** on the stat strip (free, OFL). Not on the reject list. |
| **precise-premium** | Engineered, exact, considered | **General Sans (Indian Type Foundry, free)** at moderate display scale. A humanist grotesque with deliberate ink-traps and slightly narrow proportions. Pairs as a museum-caption companion to mono. | **General Sans** body. Hairline tracking. | **JetBrains Mono** for the museum-caption stat numerals. Tabular figures locked. |
| **calm-infra** | Calm, evidential, civic | **Source Serif 4 (Adobe, free, OFL)** for the headline. A modern transitional serif designed for long-reading legibility. Reads as "official document," which is the calm-infra brief. | **Public Sans (USWDS, free)** for body. Public Sans is the United States Web Design System's civic typeface, literally engineered for government legibility — the spin's three voice words ("calm, evidential, civic") and the body family align by construction. | Public Sans, tabular. |

Cross-check (per brand.md): none of the picks is the reflex first reach. Switzer was chosen over Söhne because it has the same physical-object feel (precise grotesque) without the licensing weight; General Sans was chosen over Inter or Plus Jakarta (both reflex-reject); Public Sans + Source Serif 4 was chosen over Inter or Newsreader (both reject). The pairing for calm-infra (civic-government sans + transitional serif) is intentionally adjacent to USWDS and Stripe Climate's reading-level documents, not editorial-magazine.

### Color tokens (per spin, OKLCH)

These are starting tokens. Each spin's `app.css` adds a layer on top of the existing Tailwind v4 OKLCH variables in `backend-v2/resources/css/app.css:10-62`.

| Token | wireframe | sharp-future | precise-premium | calm-infra |
|---|---|---|---|---|
| Page background | `oklch(0.98 0.005 240)` | `oklch(0.16 0.04 220)` | `oklch(0.97 0.012 70)` | `oklch(0.985 0.008 220)` |
| Body ink | `oklch(0.18 0.005 240)` | `oklch(0.96 0.02 220)` | `oklch(0.18 0.02 150)` | `oklch(0.22 0.02 220)` |
| Primary accent | `oklch(0.55 0.04 240)` | `oklch(0.78 0.22 175)` | `oklch(0.62 0.16 50)` | `oklch(0.55 0.08 220)` |
| Secondary surface | `oklch(0.94 0.005 240)` | `oklch(0.22 0.06 220)` | `oklch(0.93 0.014 70)` | `oklch(0.95 0.01 220)` |
| Hairline | `oklch(0.85 0.005 240)` | `oklch(0.32 0.04 220)` | `oklch(0.85 0.02 70)` | `oklch(0.88 0.012 220)` |

## 4. Scope

| Aspect | wireframe | hifi spins (each) |
|---|---|---|
| **Fidelity** | Mid-fi (real copy where decided, labelled placeholders elsewhere). | High-fi to production-ready. |
| **Breadth** | Hero only for this brief. Other sections in later briefs. | Same. |
| **Interactivity** | Static visual. No motion. No JS state. | Interactive, real React, real Inertia, real motion. |
| **Viewports** | Desktop only (1440 and 1920). | 360 / 768 / 1024 / 1440 / 1920. |
| **Time intent** | Quick exploration to validate IA before motion and color land. | Polish until ship. |

Scope is task-scoped. None of these values persist into PRODUCT.md.

## 5. Layout Strategy

### Wireframe topology (shared structural common ancestor)

```
┌──────────────────────────────────────────────────────────────────────┐
│  NAV   [Mobius logo]                  [text links]      [primary CTA]│
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│                                                                      │
│   [HEADLINE LINE 1]                       ┌────────────────────┐     │
│   [HEADLINE LINE 2]                       │                    │     │
│                                           │   HERO_VISUAL      │     │
│   [SUBHEAD over 1-2 lines, max 65-75ch]   │   (placeholder /   │     │
│                                           │    AI-gen image /  │     │
│   [primary CTA]   [secondary text link]   │    photo / dataviz)│     │
│                                           │                    │     │
│                                           └────────────────────┘     │
│                                                                      │
├──────────────────────────────────────────────────────────────────────┤
│   STAT_1                STAT_2                  STAT_3               │
│   4 billion             12,000+                 1.5x                 │
│   cups discarded        branded outlets         loyalty multiplier   │
│   yearly in Malaysia    nationwide              on brand-match cups  │
└──────────────────────────────────────────────────────────────────────┘
```

Asymmetric two-column hero (60/40 text-left, visual-right). Stats strip below hero, separator hairline above. Visual hierarchy: headline → subhead → primary CTA → visual treatment → stats.

### Per-spin overrides

- **sharp-future.** The visual extends behind the nav (translucent nav, no opaque fill until scroll). The right-column visual breaks the column boundary slightly, bleeding past the page gutter on viewports ≥1440. Headline scale `clamp(48px, 7vw, 96px)`. Single column on viewports <768.
- **precise-premium.** A 12-column rigid grid is visible at the hairline level. The right-column visual is a single 35mm-look render of the smart bin with code-drawn AI annotation lines (hairlines, mono digit counts) overlaying the photo. Stats strip is restyled as museum-caption format: small uppercase label above each numeral, three columns with generous gutter. Headline scale `clamp(40px, 5.5vw, 72px)`.
- **calm-infra.** Topology shifts: text-top, dataviz-bottom, full-bleed across viewport. The dataviz is a stylized vector map of Penang and KL with bin nodes and route lines, animated to "tick in" once on page-load. Stats are integrated into a flowing sentence below the headline, not a row. Headline scale `clamp(36px, 4.5vw, 64px)` with serif. Layout reads more like Stripe's docs front than its homepage.

## 6. Key States

Hero on `/` is largely a static surface, but several states still need handling.

| State | Behaviour |
|---|---|
| **Default** | Page loads. Headline renders SSR-immediate. Image and motion hydrate after first paint. |
| **Loading (image)** | Visual area renders a tinted-neutral placeholder using the spin's secondary-surface OKLCH token. No spinner, no skeleton-shimmer. The placeholder is the same color as the hero card so the missing image is felt only as an absence, not a defect. |
| **Image-load error** | Typography-only fallback. Visual area collapses, body content reflows to fill. Logged client-side via existing Inertia error pipeline; no user-visible error message. |
| **Reduced-motion preference** | All looped or scroll-tied motion downgrades to a single 250ms fade-in on first paint. No drift, no breathing nodes, no gradient cycling. |
| **Print / forwarded screenshot** | Print stylesheet renders headline, subhead, and stats only. No image. (Council buyers print briefs.) |
| **Below 360px viewport** | Defer. Acceptable to break gracefully. 360 is the smallest target. |
| **JS disabled** | Headline, subhead, stats, and CTA all visible and functional via SSR Inertia. The visual placeholder shows; motion does not. |
| **Slow connection (image >2s)** | After 1.5s, fade in the typography-only fallback. When the image arrives, fade it in over 400ms. |

No empty state, no error state in the user-facing sense, no first-time-user state (this is a marketing surface, not a feature). No authentication branching: a logged-in user sees the same hero, with their existing dashboard link in the nav (already present in `welcome.tsx:nav`).

## 7. Interaction Model

### Page-load motion (per spin)

| Event | wireframe | sharp-future | precise-premium | calm-infra |
|---|---|---|---|---|
| First paint to interactive | Static. | Background gradient is already animating. Headline staggers word-by-word over 600ms (ease-out-quart). Subhead and CTA fade in at 400ms. | Hairlines draw in over 600ms (ease-out-expo). Image fades in over 600ms with a subtle Ken-Burns scale (1.0 → 1.02). Headline fades in once hairlines complete. | Map vector renders progressively, node-by-node, over 1200ms (ease-out-quint). Headline and subhead are static. The map "ticks" each node like a delivery confirmation. |
| Scroll past hero | Sticky nav stays transparent over hero only. | Nav transitions to opaque deep-tinted near-black at scroll Y > 200. | Nav adds a single hairline divider at scroll Y > 100. | Nav is already opaque at first paint. No transition. |

All motion respects `prefers-reduced-motion: reduce` per WCAG.

### Hover and click

| Element | Behaviour per spin |
|---|---|
| Primary CTA hover | wireframe: underline. sharp-future: button background shifts to the secondary chroma in 150ms. precise-premium: label tracking widens by 0.02em, no color change. calm-infra: a small directional arrow slides in from the right of the label. |
| Primary CTA click | All spins: Inertia `<Link href={GetStartedRoute.url()}>` via Wayfinder. Routes to existing `/get-started` page. |
| Secondary text-link hover | wireframe: underline. sharp-future: cyan underline draws in left-to-right. precise-premium: hairline underline appears statically. calm-infra: underline plus a subtle color shift toward the primary slate-teal. |
| Stat hover | wireframe: none. sharp-future: numeral wiggle (≤2px translate). precise-premium: no hover. calm-infra: numeral pulse highlight to the primary slate-teal. |

### No audience switcher in any spin

A "Brands / Recyclers / Councils" tabbed copy switcher was considered and rejected. It dilutes the brands-first commitment locked in section 2. The other audiences get distinct sections downstream. The hero is unambiguous.

## 8. Content Requirements

The wireframe carries placeholder labels. Each hifi spin commits to its own copy. No em dashes anywhere. No `--`. Use periods, commas, colons, and semicolons.

### Wireframe (placeholder copy with labels)

```
HEADLINE: [Declarative claim, 8 to 12 words, period-terminated.]
SUBHEAD:  [Supporting statement, 22 to 35 words, two sentences max.]
CTA_PRIMARY:   Become a partner
CTA_SECONDARY: See how it works
HERO_VISUAL:   [AI-gen abstract / product render / dataviz placeholder]
STAT_1: 4 billion / disposable cups discarded yearly in Malaysia
STAT_2: 12,000+ / branded beverage outlets nationwide
STAT_3: 1.5x / loyalty multiplier on brand-match cup recycling
```

### sharp-future

```
HEADLINE: Recycling infrastructure for beverage brands.
SUBHEAD:  Mobius builds the bins, the AI, the rewards layer, and the route operations your outlets need. Tailored to your brand. Deployed at every store.
CTA_PRIMARY:   Become a partner
CTA_SECONDARY: Read the technology
HERO_VISUAL:   AI-generated abstract motion piece. Concept: viscous translucent liquid flowing through suspended geometric forms, brand-currency tokens cascading through cup silhouettes, midnight-teal base, electric cyan-emerald highlights, no logos, no people, no realistic objects. Cinematic depth, shallow focus. Loops every 12 seconds.
STAT_STRIP:    "4B disposable cups discarded yearly in Malaysia. 12,000+ branded beverage outlets. 1.5x loyalty multiplier on brand-match recycling. 0% handled by today's bin networks."
```

### precise-premium

```
HEADLINE: We build your brand's recycling system.
SUBHEAD:  Mobius is the team beverage brands hire to design, install, and operate their disposable-cup recycling layer. Hardware, AI, and software. All tailored to your outlets.
CTA_PRIMARY:   Become a partner
CTA_SECONDARY: View the system
HERO_VISUAL:   Studio render of the smart bin (35mm-look, single decisive photograph), with code-drawn AI annotation lines overlaying the photo. Hairline arrows label the camera, the IoT board, the brand panel. Mono digit counts (e.g. 0.94 confidence, 12ms detection) appear on hover.
STATS: Three museum-caption blocks.
  OUTLETS WE COULD COVER  /  12,000+
  ANNUAL CUP VOLUME       /  4 BILLION
  LOYALTY UPLIFT          /  1.5x
```

### calm-infra

```
HEADLINE: Recycling infrastructure, deployed by brand, audited by city.
SUBHEAD:  Mobius operates branded smart-bin networks across beverage outlets, generating verifiable recycling streams that brands report and councils audit.
CTA_PRIMARY:   Speak to our team
CTA_SECONDARY: Operating data and reports
HERO_VISUAL:   Stylized vector map of Penang and KL, with bin nodes (dots), route lines (thin curves), and brand-loyalty flow indicators (small directional arrows). Node count ticks up as the map renders. Built in SVG, no raster image.
STAT_PROSE:    "Across an estimated 12,000 branded beverage outlets in Malaysia, roughly 4 billion disposable cups enter the waste stream every year. Mobius captures, identifies, and ties every cup back to the brand that issued it."
```

### Microcopy locked across all spins

- "Mobius" never lowercased except in URLs.
- "Beverage brand" preferred over "brand owner" or "F&B chain".
- "Outlet" preferred over "store" or "location" (consistent with internal Plan model).
- "Recycling infrastructure" preferred over "recycling system" except where context demands the latter.
- Never "smart" as an adjective without a noun. "Smart bin" is fine; "smart recycling" is banned (it appears in the current `/` and is part of the AI-slop the redesign escapes).
- Never "AI-powered" or "powered by AI". The AI is implicit in "dual-axis computer vision" or in the product evidence sections; it is not a hero adjective.

## 9. Recommended References

The following impeccable reference files are the most valuable during craft for the Hero. Loaded by future `/impeccable craft Hero` calls.

- `reference/typography.md` and `reference/typeset.md`: every spin demands deliberate font selection per brand.md procedure. The reflex-reject list is non-negotiable.
- `reference/color-and-contrast.md`: three different color strategies (Restrained, Drenched, Committed) need contrast verification against WCAG AA.
- `reference/spatial-design.md`: asymmetric two-column hero layout with deliberate rhythm. Per-spin overrides need spatial reasoning, not Tailwind defaults.
- `reference/motion-design.md` and `reference/animate.md`: sharp-future (motion-led) and calm-infra (dataviz tick-in) both need motion-system thinking, not ad-hoc Framer Motion.
- `reference/responsive-design.md`: hifi spins are responsive down to 360px. The Stripe-mirror text-left layout collapses to single column at 768.
- `reference/interaction-design.md`: CTA hover behaviours and the (rejected) audience-switcher reasoning.
- `reference/brand.md`: already loaded for this brief. Re-loaded each craft for the bans list.

## 10. Open Questions (for craft, not for shape)

These are decisions deferred to `/impeccable craft Hero`. They do not block confirmation of this brief.

1. **Type families: locked to open-source.** Switzer / General Sans / JetBrains Mono / Public Sans / Source Serif 4. All free, all OFL or equivalent, none on the reflex-reject list. Klim upgrade is post-traction and out of scope this redesign.
2. **sharp-future hero image generation.** Midjourney/Gemini prompt drafted in section 8. User generates the source. We process to MP4/WebM loop in `resources/images/heroes/sharp-future-hero.{mp4,webm,poster.jpg}`. First image generated in shape-preview, refined in craft.
3. **precise-premium hero photography.** A studio render of the smart bin is needed. If no real bin asset exists, a Blender render or a high-quality Midjourney "studio product photography of a black-and-warm-wood smart recycling bin with brand panel, 35mm lens, soft north light, charcoal backdrop" can substitute for first cut. Real product photography is post-launch.
4. **calm-infra map data source.** Stylized vector for first cut (drawn in Figma, exported SVG). Real OSM data via OSRM is post-launch.
5. **Stat numbers.** "4 billion" and "12,000+" are aspirational/sector-level claims. Need a one-line citation footnote (`reference: Malaysia Plastics Pact 2023 estimate` or similar) so council buyers can audit. Defer the citation source to craft.
6. **Wayfinder route helper for `/get-started`.** Confirm `GetStarted.create.url()` is the right call (per survey, the route is GET `/get-started` named via `LeadController::create`). Verify Wayfinder name during craft.

---

## Confirmation request

Per `/impeccable shape` protocol: this brief is the contract. Read it once. If anything is materially wrong, reply with "edit section N: ..." and I revise. If the brief is right, reply "approved, proceed" and I move to `/impeccable craft Hero` against the wireframe branch first, then the three hifi spins in turn.

Do not approve without reading. The brief propagates.
