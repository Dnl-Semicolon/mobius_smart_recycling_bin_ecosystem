# Hero Image Generation / Prompt Pack

> Replacement for `HeroAurora` (the CSS stand-in currently shipping). Generate the image, drop into `backend-v2/resources/images/heroes/`, swap the component.

## Direction

The hero piece should read as **operational, premium, slightly otherworldly**. Not "AI art." Not "eco." Not a literal cup or bin. A visual that says **"value flowing where waste used to be."** Loops or near-loops well so we can convert to a 12 second WebM later.

Anchor mood references (do NOT name to the model):
- Stripe.com hero gradient (the colour drench, the depth, the motion-pregnant stillness).
- Vercel.com hero (matte black with chroma blooming through).
- impeccable.style/neo-mirai (asymmetric, kinetic).
- Apple Vision Pro launch graphics (translucent material, shallow focus).
- Refik Anadol abstract data sculptures (organic motion of an inorganic system).

## Vague-on-purpose intent

I want the image-gen tool to interpret. Don't over-specify shapes. Pin colour, mood, depth, material. Let the model invent the form.

## Prompt v1 (Midjourney)

```
abstract photographic still, viscous translucent emerald liquid suspended in zero gravity, faint cyan light blooming from inside, midnight teal void background, shallow depth of field, cinematic, slightly damp atmosphere, premium product photography aesthetic, no text, no logo, no recognizable objects, sense of weightless value being captured, 16:9 --ar 16:9 --style raw --v 6 --s 250
```

## Prompt v2 (Midjourney, alternative composition)

```
deep space gradient nebula, midnight teal core, electric mint and cyan tendrils curling outward like jellyfish bioluminescence, no stars, no celestial objects, soft volumetric light, photographic depth of field, calm but alive, cinematic widescreen, no text no logos no characters, --ar 16:9 --style raw --v 6 --s 350
```

## Prompt v3 (Gemini / Imagen variant)

```
A cinematic abstract photograph of glowing emerald light rising through a dark teal liquid medium, like ink in water filmed in slow motion. Strong directional rim light from the right. Shallow depth of field, photographic grain. Midnight teal background, no objects, no people, no text. Wide aspect ratio.
```

## What to send back

Pick the one closest to the brand voice. Crop / regenerate variations until the colour drench feels right against the headline. Send the chosen `.png` (or `.webm` if motion is generated) for integration. I will:

1. Place file at `backend-v2/resources/images/heroes/sharp-future-hero.{png,webm,poster.jpg}`.
2. Replace `HeroAurora` with a `<picture>` / `<video>` element that retains the same outer dimensions so layout stays.
3. Keep `HeroAurora` as a fallback for slow connections / failed loads.

## Constraints (hard)

- 16:9 base, exportable to 1920×1080 minimum.
- Loop or near-loop friendly if a video is generated. Two motion options worth trying: continuous slow drift (Stripe-style), or breathing pulse (Apple-style).
- Colour-keyed to: midnight teal background `oklch(0.13 0.05 215)`, primary chroma in the cyan-emerald range `oklch(0.78 0.22 175)`. Don't drift toward navy, don't drift toward grass-green.
- No text, no logos, no people, no recognisable objects.
- File size budget: 400KB for poster JPG, 2MB for WebM loop.
