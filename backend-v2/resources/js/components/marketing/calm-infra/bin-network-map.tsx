import { motion, useReducedMotion } from 'motion/react';

/*
 * Stylised bin-network visualisation for the calm-infra hero.
 * Two clusters: Penang (north-west) and KL (south-east). Node coordinates
 * are hand-placed within a 1440x400 viewBox. The shape suggests Peninsular
 * Malaysia without literally tracing the coastline.
 */

type BinNode = {
    x: number;
    y: number;
    radius: number;
    /** A small fraction of nodes pulse to suggest live ingestion. */
    live?: boolean;
};

const PENANG_CLUSTER: BinNode[] = [
    { x: 330, y: 132, radius: 4, live: true },
    { x: 358, y: 148, radius: 3 },
    { x: 348, y: 168, radius: 3 },
    { x: 388, y: 156, radius: 4 },
    { x: 372, y: 178, radius: 3 },
    { x: 410, y: 172, radius: 3 },
    { x: 398, y: 196, radius: 4, live: true },
    { x: 432, y: 188, radius: 3 },
    { x: 422, y: 212, radius: 3 },
    { x: 458, y: 200, radius: 3 },
    { x: 442, y: 226, radius: 4 },
    { x: 470, y: 220, radius: 3 },
];

const KL_CLUSTER: BinNode[] = [
    { x: 880, y: 240, radius: 4, live: true },
    { x: 906, y: 230, radius: 3 },
    { x: 922, y: 250, radius: 4 },
    { x: 944, y: 240, radius: 3 },
    { x: 894, y: 260, radius: 3 },
    { x: 932, y: 270, radius: 4, live: true },
    { x: 960, y: 258, radius: 3 },
    { x: 912, y: 282, radius: 3 },
    { x: 950, y: 290, radius: 3 },
    { x: 974, y: 276, radius: 4 },
    { x: 992, y: 296, radius: 3 },
    { x: 940, y: 308, radius: 3 },
];

const ROUTE_PATHS = [
    'M 348 168 Q 410 200 442 226',
    'M 388 156 Q 432 200 470 220',
    'M 880 240 Q 920 264 974 276',
    'M 906 230 Q 932 270 992 296',
    'M 442 226 Q 660 240 880 240',
];

export function BinNetworkMap() {
    const prefersReducedMotion = useReducedMotion();
    const allNodes = [
        ...PENANG_CLUSTER.map((n) => ({ ...n, cluster: 'penang' as const })),
        ...KL_CLUSTER.map((n) => ({ ...n, cluster: 'kl' as const })),
    ];

    return (
        <div className="relative w-full" aria-hidden>
            <svg
                viewBox="0 0 1440 400"
                preserveAspectRatio="xMidYMid slice"
                className="block h-[260px] w-full md:h-[320px] lg:h-[380px]"
            >
                <defs>
                    <pattern
                        id="calm-infra-grid"
                        width="60"
                        height="60"
                        patternUnits="userSpaceOnUse"
                    >
                        <path
                            d="M 60 0 L 0 0 0 60"
                            fill="none"
                            stroke="var(--color-border)"
                            strokeWidth="0.5"
                            opacity="0.55"
                        />
                    </pattern>
                    <radialGradient id="calm-infra-glow" cx="50%" cy="50%" r="50%">
                        <stop offset="0%" stopColor="var(--color-primary)" stopOpacity="0.18" />
                        <stop offset="100%" stopColor="var(--color-primary)" stopOpacity="0" />
                    </radialGradient>
                </defs>

                <rect width="1440" height="400" fill="url(#calm-infra-grid)" />

                <ellipse
                    cx="400"
                    cy="180"
                    rx="180"
                    ry="100"
                    fill="url(#calm-infra-glow)"
                />
                <ellipse
                    cx="930"
                    cy="270"
                    rx="180"
                    ry="100"
                    fill="url(#calm-infra-glow)"
                />

                {ROUTE_PATHS.map((d, i) => (
                    <motion.path
                        key={i}
                        d={d}
                        fill="none"
                        stroke="var(--color-primary)"
                        strokeWidth="1.25"
                        strokeOpacity="0.45"
                        strokeDasharray="3 4"
                        initial={prefersReducedMotion ? false : { pathLength: 0, opacity: 0 }}
                        animate={{ pathLength: 1, opacity: 0.45 }}
                        transition={{
                            delay: 0.9 + i * 0.18,
                            duration: 0.9,
                            ease: [0.22, 1, 0.36, 1],
                        }}
                    />
                ))}

                {allNodes.map((node, i) => {
                    const delay = 0.2 + i * 0.04;
                    return (
                        <motion.g
                            key={`${node.cluster}-${i}`}
                            initial={prefersReducedMotion ? false : { scale: 0, opacity: 0 }}
                            animate={{ scale: 1, opacity: 1 }}
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
                                r={node.radius + 2.5}
                                fill="var(--color-primary)"
                                opacity="0.18"
                            />
                            <circle
                                cx={node.x}
                                cy={node.y}
                                r={node.radius}
                                fill="var(--color-primary)"
                            />
                            {node.live && !prefersReducedMotion && (
                                <motion.circle
                                    cx={node.x}
                                    cy={node.y}
                                    r={node.radius}
                                    fill="var(--color-primary)"
                                    initial={{ scale: 1, opacity: 0.55 }}
                                    animate={{ scale: 3.2, opacity: 0 }}
                                    transition={{
                                        delay: delay + 0.6,
                                        duration: 2.4,
                                        repeat: Infinity,
                                        repeatDelay: 0.6,
                                        ease: 'easeOut',
                                    }}
                                    style={{ transformOrigin: `${node.x}px ${node.y}px` }}
                                />
                            )}
                        </motion.g>
                    );
                })}

                <ClusterLabel x={336} y={104} label="Penang" count={PENANG_CLUSTER.length} delay={0.1} />
                <ClusterLabel x={888} y={210} label="Kuala Lumpur" count={KL_CLUSTER.length} delay={0.5} />
            </svg>
        </div>
    );
}

function ClusterLabel({
    x,
    y,
    label,
    count,
    delay,
}: {
    x: number;
    y: number;
    label: string;
    count: number;
    delay: number;
}) {
    return (
        <motion.g
            initial={{ opacity: 0, y: 6 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay, duration: 0.6, ease: [0.22, 1, 0.36, 1] }}
        >
            <text
                x={x}
                y={y}
                fill="var(--color-foreground)"
                fontSize="11"
                fontWeight="500"
                letterSpacing="1.2"
                style={{ textTransform: 'uppercase', fontFamily: 'var(--font-sans)' }}
            >
                {label}
            </text>
            <text
                x={x}
                y={y + 14}
                fill="var(--color-muted-foreground)"
                fontSize="10"
                style={{ fontFamily: 'var(--font-sans)' }}
            >
                {count} pilot bins
            </text>
        </motion.g>
    );
}
