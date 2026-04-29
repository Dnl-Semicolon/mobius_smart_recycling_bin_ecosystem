/*
 * Code-driven stand-in for the sharp-future AI-generated hero motion piece
 * described in docs/shape/01-Hero.md section 8. Three drifting radial blobs
 * over a midnight-teal base, mix-blend "screen" so the chroma builds where
 * they overlap. Pure CSS animations; respects prefers-reduced-motion.
 *
 * Replace this component with a real video / WebM loop when the AI-gen
 * asset is generated. Keep the same outer dimensions so layout stays.
 */

export function HeroAurora() {
    return (
        <div
            className="pointer-events-none absolute inset-0 overflow-hidden"
            aria-hidden
            style={{ backgroundColor: 'oklch(0.13 0.05 215)' }}
        >
            <div
                className="absolute -top-1/3 left-[10%] h-[140%] w-[60%] motion-safe:animate-[sharp-drift-a_22s_ease-in-out_infinite]"
                style={{
                    background:
                        'radial-gradient(closest-side, oklch(0.78 0.22 175 / 0.55), transparent 70%)',
                    mixBlendMode: 'screen',
                    filter: 'blur(40px)',
                }}
            />
            <div
                className="absolute -bottom-1/4 right-[5%] h-[120%] w-[55%] motion-safe:animate-[sharp-drift-b_28s_ease-in-out_infinite]"
                style={{
                    background:
                        'radial-gradient(closest-side, oklch(0.6 0.2 200 / 0.55), transparent 70%)',
                    mixBlendMode: 'screen',
                    filter: 'blur(50px)',
                }}
            />
            <div
                className="absolute top-1/4 right-[28%] h-[80%] w-[45%] motion-safe:animate-[sharp-drift-c_18s_ease-in-out_infinite]"
                style={{
                    background:
                        'radial-gradient(closest-side, oklch(0.55 0.18 165 / 0.45), transparent 70%)',
                    mixBlendMode: 'screen',
                    filter: 'blur(36px)',
                }}
            />

            <div
                className="absolute inset-0"
                style={{
                    background:
                        'radial-gradient(ellipse at top, transparent 30%, oklch(0.12 0.04 220 / 0.65) 80%)',
                }}
            />

            <svg
                className="absolute inset-0 h-full w-full opacity-[0.08] mix-blend-overlay"
                aria-hidden
            >
                <filter id="sharp-noise">
                    <feTurbulence type="fractalNoise" baseFrequency="0.85" numOctaves="2" />
                    <feColorMatrix values="0 0 0 0 1  0 0 0 0 1  0 0 0 0 1  0 0 0 0.6 0" />
                </filter>
                <rect width="100%" height="100%" filter="url(#sharp-noise)" />
            </svg>
        </div>
    );
}
