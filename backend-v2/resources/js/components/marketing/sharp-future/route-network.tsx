import { motion, useInView, useReducedMotion } from 'motion/react';
import { useRef } from 'react';

/*
 * Sharp-future variant of the bin-network map. Same data shape as the
 * calm-infra version (two clusters, hand-placed nodes) but rendered with
 * dark surface tokens, electric primary glow, and animated route paths
 * that suggest optimised collection runs rather than static dataviz.
 *
 * Routes draw in after the nodes settle, then a "next pickup" pulse
 * walks along the active route. Respects prefers-reduced-motion.
 */

type BinNode = {
    x: number;
    y: number;
    radius: number;
    full?: boolean;
};

const PENANG: BinNode[] = [
    { x: 290, y: 122, radius: 4 },
    { x: 320, y: 144, radius: 3, full: true },
    { x: 308, y: 168, radius: 3 },
    { x: 348, y: 156, radius: 4 },
    { x: 332, y: 184, radius: 3 },
    { x: 370, y: 174, radius: 3, full: true },
    { x: 358, y: 200, radius: 3 },
    { x: 396, y: 192, radius: 4 },
    { x: 386, y: 218, radius: 3 },
    { x: 422, y: 208, radius: 3, full: true },
];

const KL: BinNode[] = [
    { x: 880, y: 246, radius: 3 },
    { x: 910, y: 234, radius: 4, full: true },
    { x: 928, y: 256, radius: 3 },
    { x: 950, y: 244, radius: 3 },
    { x: 898, y: 268, radius: 4 },
    { x: 936, y: 276, radius: 3, full: true },
    { x: 966, y: 262, radius: 3 },
    { x: 916, y: 288, radius: 3 },
    { x: 956, y: 296, radius: 4, full: true },
    { x: 982, y: 280, radius: 3 },
];

const ACTIVE_ROUTE_PENANG = 'M 320 144 Q 360 165 370 174 Q 400 195 422 208';
const ACTIVE_ROUTE_KL = 'M 910 234 Q 925 256 936 276 Q 950 290 956 296';
const INACTIVE_ROUTES = [
    'M 290 122 Q 332 184 386 218',
    'M 308 168 Q 358 200 396 192',
    'M 880 246 Q 920 268 982 280',
    'M 898 268 Q 928 280 966 262',
];

export function RouteNetwork() {
    const prefersReducedMotion = useReducedMotion();
    const ref = useRef<HTMLDivElement>(null);
    const inView = useInView(ref, { once: true, margin: '-80px' });
    const allNodes = [
        ...PENANG.map((n) => ({ ...n, cluster: 'penang' as const })),
        ...KL.map((n) => ({ ...n, cluster: 'kl' as const })),
    ];

    return (
        <div ref={ref} className="relative w-full" aria-hidden>
            <svg
                viewBox="0 0 1440 400"
                preserveAspectRatio="xMidYMid slice"
                className="block h-[280px] w-full md:h-[340px] lg:h-[400px]"
            >
                <defs>
                    <pattern id="sharp-route-grid" width="80" height="80" patternUnits="userSpaceOnUse">
                        <path
                            d="M 80 0 L 0 0 0 80"
                            fill="none"
                            stroke="var(--color-border)"
                            strokeWidth="0.5"
                            opacity="0.4"
                        />
                    </pattern>
                    <radialGradient id="sharp-route-glow" cx="50%" cy="50%" r="50%">
                        <stop offset="0%" stopColor="var(--color-primary)" stopOpacity="0.3" />
                        <stop offset="100%" stopColor="var(--color-primary)" stopOpacity="0" />
                    </radialGradient>
                </defs>

                <rect width="1440" height="400" fill="url(#sharp-route-grid)" />

                <ellipse cx="370" cy="180" rx="200" ry="120" fill="url(#sharp-route-glow)" />
                <ellipse cx="930" cy="270" rx="200" ry="120" fill="url(#sharp-route-glow)" />

                {INACTIVE_ROUTES.map((d, i) => (
                    <motion.path
                        key={`inactive-${i}`}
                        d={d}
                        fill="none"
                        stroke="var(--color-foreground)"
                        strokeWidth="0.8"
                        strokeOpacity="0.18"
                        strokeDasharray="2 6"
                        initial={prefersReducedMotion ? false : { pathLength: 0 }}
                        animate={inView ? { pathLength: 1 } : { pathLength: 0 }}
                        transition={{
                            delay: 0.6 + i * 0.1,
                            duration: 0.8,
                            ease: [0.22, 1, 0.36, 1],
                        }}
                    />
                ))}

                <motion.path
                    d={ACTIVE_ROUTE_PENANG}
                    fill="none"
                    stroke="var(--color-primary)"
                    strokeWidth="1.5"
                    strokeLinecap="round"
                    initial={prefersReducedMotion ? false : { pathLength: 0 }}
                    animate={inView ? { pathLength: 1 } : { pathLength: 0 }}
                    transition={{ delay: 1.1, duration: 1.0, ease: [0.22, 1, 0.36, 1] }}
                />
                <motion.path
                    d={ACTIVE_ROUTE_KL}
                    fill="none"
                    stroke="var(--color-primary)"
                    strokeWidth="1.5"
                    strokeLinecap="round"
                    initial={prefersReducedMotion ? false : { pathLength: 0 }}
                    animate={inView ? { pathLength: 1 } : { pathLength: 0 }}
                    transition={{ delay: 1.3, duration: 1.0, ease: [0.22, 1, 0.36, 1] }}
                />

                {allNodes.map((node, i) => {
                    const delay = 0.15 + i * 0.035;
                    return (
                        <motion.g
                            key={`${node.cluster}-${i}`}
                            initial={prefersReducedMotion ? false : { scale: 0, opacity: 0 }}
                            animate={inView ? { scale: 1, opacity: 1 } : { scale: 0, opacity: 0 }}
                            transition={{
                                delay,
                                duration: 0.5,
                                ease: [0.22, 1, 0.36, 1],
                            }}
                            style={{ transformOrigin: `${node.x}px ${node.y}px` }}
                        >
                            <circle
                                cx={node.x}
                                cy={node.y}
                                r={node.radius + 3}
                                fill={node.full ? 'var(--color-primary)' : 'var(--color-foreground)'}
                                opacity={node.full ? 0.25 : 0.15}
                            />
                            <circle
                                cx={node.x}
                                cy={node.y}
                                r={node.radius}
                                fill={node.full ? 'var(--color-primary)' : 'var(--color-foreground)'}
                            />
                            {node.full && !prefersReducedMotion && (
                                <motion.circle
                                    cx={node.x}
                                    cy={node.y}
                                    r={node.radius}
                                    fill="var(--color-primary)"
                                    initial={{ scale: 1, opacity: 0.55 }}
                                    animate={
                                        inView
                                            ? { scale: 3.5, opacity: 0 }
                                            : { scale: 1, opacity: 0 }
                                    }
                                    transition={{
                                        delay: delay + 1.0,
                                        duration: 2.2,
                                        repeat: Infinity,
                                        repeatDelay: 0.4,
                                        ease: 'easeOut',
                                    }}
                                    style={{ transformOrigin: `${node.x}px ${node.y}px` }}
                                />
                            )}
                        </motion.g>
                    );
                })}

                <g>
                    <text
                        x="290"
                        y="98"
                        fill="var(--color-foreground)"
                        fontSize="11"
                        fontWeight="500"
                        letterSpacing="1.2"
                        style={{ textTransform: 'uppercase' }}
                    >
                        Penang
                    </text>
                    <text
                        x="290"
                        y="112"
                        fill="var(--color-muted-foreground)"
                        fontSize="9"
                        letterSpacing="0.8"
                    >
                        zone 04 · 3 bins ready
                    </text>
                </g>
                <g>
                    <text
                        x="880"
                        y="220"
                        fill="var(--color-foreground)"
                        fontSize="11"
                        fontWeight="500"
                        letterSpacing="1.2"
                        style={{ textTransform: 'uppercase' }}
                    >
                        Kuala Lumpur
                    </text>
                    <text
                        x="880"
                        y="234"
                        fill="var(--color-muted-foreground)"
                        fontSize="9"
                        letterSpacing="0.8"
                    >
                        zone 11 · 3 bins ready
                    </text>
                </g>
            </svg>
        </div>
    );
}
