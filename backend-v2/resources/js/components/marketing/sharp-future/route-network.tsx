import { motion, useInView, useReducedMotion } from 'motion/react';
import { useRef } from 'react';

/*
 * Sharp-future variant of the bin network. Real Penang Island outline
 * with bin nodes placed at real-ish coordinates inside Georgetown,
 * Bayan Lepas, and the rest of the east-coast urban corridor. The
 * abstract two-cluster layout (Penang + KL) was replaced per user
 * feedback: a single recognisable map of Penang reads as actual pilot
 * geography, not as a dataviz pattern.
 *
 * Projection is hand-rolled equirectangular against the island bounds
 * north 5.49 / south 5.22 / west 100.18 / east 100.36. Coordinates
 * approximate: this is a marketing visual, not a tile map.
 *
 * viewBox is portrait to match the real island aspect ratio.
 */

const VIEW_W = 800;
const VIEW_H = 1200;
const NORTH = 5.49;
const SOUTH = 5.22;
const WEST = 100.18;
const EAST = 100.36;

const project = (lat: number, lng: number) => ({
    x: ((lng - WEST) / (EAST - WEST)) * VIEW_W,
    y: ((NORTH - lat) / (NORTH - SOUTH)) * VIEW_H,
});

type Bin = {
    name: string;
    lat: number;
    lng: number;
    radius: number;
    full?: boolean;
};

const BINS: Bin[] = [
    { name: 'Tanjung Tokong (Straits Quay)', lat: 5.4503, lng: 100.3042, radius: 5, full: true },
    { name: 'Gurney Drive', lat: 5.4424, lng: 100.3105, radius: 5 },
    { name: 'Komtar (CBD)', lat: 5.4143, lng: 100.3294, radius: 6, full: true },
    { name: 'Air Itam', lat: 5.4083, lng: 100.2731, radius: 4 },
    { name: 'Jelutong', lat: 5.3946, lng: 100.3064, radius: 5 },
    { name: 'USM Penang', lat: 5.3589, lng: 100.3041, radius: 5, full: true },
    { name: 'Sungai Nibong', lat: 5.3441, lng: 100.2944, radius: 4 },
    { name: 'Bayan Baru', lat: 5.3326, lng: 100.2884, radius: 5 },
    { name: 'Bayan Lepas FTZ', lat: 5.3284, lng: 100.2832, radius: 5, full: true },
    { name: 'Penang Airport', lat: 5.2920, lng: 100.2738, radius: 4 },
];

// Coastline traced from Penang Island outline. Bezier control points were
// hand-tuned to the equirectangular projection above; not survey-grade.
const COASTLINE = `
    M 478 28
    Q 540 50 575 110
    Q 610 150 600 188
    Q 595 215 612 250
    Q 660 295 668 332
    L 624 388
    Q 575 410 552 432
    L 562 502
    Q 590 540 595 600
    Q 610 700 605 778
    Q 600 855 562 900
    Q 520 945 462 935
    L 410 920
    Q 360 935 320 905
    Q 270 880 235 815
    L 180 745
    Q 130 700 120 638
    L 110 540
    Q 92 470 96 410
    Q 100 330 110 270
    Q 120 215 145 175
    Q 175 130 220 95
    Q 280 60 360 40
    Q 420 25 478 28
    Z
`;

export function RouteNetwork() {
    const prefersReducedMotion = useReducedMotion();
    const ref = useRef<HTMLDivElement>(null);
    const inView = useInView(ref, { once: true, margin: '-80px' });

    const projectedBins = BINS.map((b) => ({ ...b, ...project(b.lat, b.lng) }));
    const activePath = buildSmoothPath([
        projectedBins[2], // Komtar
        projectedBins[4], // Jelutong
        projectedBins[5], // USM
        projectedBins[8], // Bayan Lepas FTZ
    ]);
    const candidatePaths = [
        buildSmoothPath([projectedBins[0], projectedBins[1], projectedBins[2]]),
        buildSmoothPath([projectedBins[2], projectedBins[3], projectedBins[5]]),
        buildSmoothPath([projectedBins[5], projectedBins[6], projectedBins[7], projectedBins[9]]),
    ];

    return (
        <div ref={ref} className="relative mx-auto w-full max-w-[1280px]" aria-hidden>
            <svg
                viewBox={`0 0 ${VIEW_W} ${VIEW_H}`}
                preserveAspectRatio="xMidYMid meet"
                className="block h-[420px] w-full md:h-[520px] lg:h-[600px]"
            >
                <defs>
                    <pattern id="route-grid" width="60" height="60" patternUnits="userSpaceOnUse">
                        <path
                            d="M 60 0 L 0 0 0 60"
                            fill="none"
                            stroke="var(--color-border)"
                            strokeWidth="0.5"
                            opacity="0.3"
                        />
                    </pattern>
                    <radialGradient id="route-glow" cx="50%" cy="40%" r="60%">
                        <stop offset="0%" stopColor="var(--color-primary)" stopOpacity="0.18" />
                        <stop offset="100%" stopColor="var(--color-primary)" stopOpacity="0" />
                    </radialGradient>
                    <linearGradient id="route-island-fill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor="var(--color-card)" stopOpacity="0.65" />
                        <stop offset="100%" stopColor="var(--color-card)" stopOpacity="0.35" />
                    </linearGradient>
                </defs>

                <rect width={VIEW_W} height={VIEW_H} fill="url(#route-grid)" />

                <ellipse cx={VIEW_W * 0.55} cy={VIEW_H * 0.45} rx={320} ry={420} fill="url(#route-glow)" />

                <motion.path
                    d={COASTLINE}
                    fill="url(#route-island-fill)"
                    stroke="var(--color-primary)"
                    strokeWidth="1.5"
                    strokeOpacity="0.7"
                    initial={prefersReducedMotion ? false : { pathLength: 0, opacity: 0 }}
                    animate={inView ? { pathLength: 1, opacity: 1 } : { pathLength: 0, opacity: 0 }}
                    transition={{ delay: 0.1, duration: 1.6, ease: [0.22, 1, 0.36, 1] }}
                />

                {candidatePaths.map((d, i) => (
                    <motion.path
                        key={`candidate-${i}`}
                        d={d}
                        fill="none"
                        stroke="var(--color-foreground)"
                        strokeWidth="1.2"
                        strokeOpacity="0.22"
                        strokeDasharray="3 7"
                        initial={prefersReducedMotion ? false : { pathLength: 0 }}
                        animate={inView ? { pathLength: 1 } : { pathLength: 0 }}
                        transition={{
                            delay: 1.0 + i * 0.15,
                            duration: 0.9,
                            ease: [0.22, 1, 0.36, 1],
                        }}
                    />
                ))}

                <motion.path
                    d={activePath}
                    fill="none"
                    stroke="var(--color-primary)"
                    strokeWidth="2.2"
                    strokeLinecap="round"
                    initial={prefersReducedMotion ? false : { pathLength: 0 }}
                    animate={inView ? { pathLength: 1 } : { pathLength: 0 }}
                    transition={{ delay: 1.4, duration: 1.4, ease: [0.22, 1, 0.36, 1] }}
                />

                {projectedBins.map((bin, i) => {
                    const delay = 1.8 + i * 0.06;
                    return (
                        <motion.g
                            key={bin.name}
                            initial={prefersReducedMotion ? false : { scale: 0, opacity: 0 }}
                            animate={inView ? { scale: 1, opacity: 1 } : { scale: 0, opacity: 0 }}
                            transition={{
                                delay,
                                duration: 0.5,
                                ease: [0.22, 1, 0.36, 1],
                            }}
                            style={{ transformOrigin: `${bin.x}px ${bin.y}px` }}
                        >
                            <circle
                                cx={bin.x}
                                cy={bin.y}
                                r={bin.radius + 4}
                                fill={bin.full ? 'var(--color-primary)' : 'var(--color-foreground)'}
                                opacity={bin.full ? 0.28 : 0.16}
                            />
                            <circle
                                cx={bin.x}
                                cy={bin.y}
                                r={bin.radius}
                                fill={bin.full ? 'var(--color-primary)' : 'var(--color-foreground)'}
                            />
                            {bin.full && !prefersReducedMotion && (
                                <motion.circle
                                    cx={bin.x}
                                    cy={bin.y}
                                    r={bin.radius}
                                    fill="var(--color-primary)"
                                    initial={{ scale: 1, opacity: 0.55 }}
                                    animate={
                                        inView
                                            ? { scale: 4.2, opacity: 0 }
                                            : { scale: 1, opacity: 0 }
                                    }
                                    transition={{
                                        delay: delay + 0.6,
                                        duration: 2.4,
                                        repeat: Infinity,
                                        repeatDelay: 0.4,
                                        ease: 'easeOut',
                                    }}
                                    style={{ transformOrigin: `${bin.x}px ${bin.y}px` }}
                                />
                            )}
                        </motion.g>
                    );
                })}

                <motion.g
                    initial={{ opacity: 0 }}
                    animate={inView ? { opacity: 1 } : { opacity: 0 }}
                    transition={{ delay: 0.4, duration: 0.8 }}
                >
                    <text
                        x={VIEW_W * 0.5}
                        y={VIEW_H * 0.06}
                        textAnchor="middle"
                        fill="var(--color-foreground)"
                        fontSize="20"
                        fontWeight="500"
                        letterSpacing="3"
                        style={{ textTransform: 'uppercase' }}
                    >
                        Penang
                    </text>
                    <text
                        x={VIEW_W * 0.5}
                        y={VIEW_H * 0.085}
                        textAnchor="middle"
                        fill="var(--color-muted-foreground)"
                        fontSize="11"
                        letterSpacing="1.6"
                    >
                        pilot zone · 4 bins ready for collection
                    </text>
                </motion.g>
            </svg>
        </div>
    );
}

/**
 * Build a smooth quadratic-bezier path through an ordered list of points.
 * Used for both the active optimised route and the rejected candidates so
 * they share the same curvature character.
 */
function buildSmoothPath(points: Array<{ x: number; y: number }>): string {
    if (points.length === 0) return '';
    if (points.length === 1) return `M ${points[0].x} ${points[0].y}`;
    let d = `M ${points[0].x} ${points[0].y}`;
    for (let i = 1; i < points.length; i++) {
        const prev = points[i - 1];
        const curr = points[i];
        const cx = (prev.x + curr.x) / 2;
        const cy = (prev.y + curr.y) / 2 - Math.abs(curr.x - prev.x) * 0.15;
        d += ` Q ${cx} ${cy} ${curr.x} ${curr.y}`;
    }
    return d;
}
