import { motion, useReducedMotion } from 'motion/react';

/*
 * Hairline schematic of the Mobius smart bin. Not photorealistic. Reads
 * as a technical drawing - thin strokes, deliberate proportions, mono
 * captions. Annotation lines draw in over 600ms once the page hydrates.
 */

const annotations: Array<{
    targetX: number;
    targetY: number;
    bendX: number;
    labelX: number;
    labelY: number;
    label: string;
    metric: string;
    metricLabel: string;
    delay: number;
}> = [
    {
        targetX: 200,
        targetY: 78,
        bendX: 320,
        labelX: 332,
        labelY: 82,
        label: 'Sensor head',
        metric: '0.94',
        metricLabel: 'detection confidence',
        delay: 0.6,
    },
    {
        targetX: 230,
        targetY: 144,
        bendX: 340,
        labelX: 352,
        labelY: 148,
        label: 'Brand panel',
        metric: 'A2',
        metricLabel: 'tooled, 1.4mm steel',
        delay: 0.8,
    },
    {
        targetX: 200,
        targetY: 268,
        bendX: 320,
        labelX: 332,
        labelY: 272,
        label: 'IoT board',
        metric: '12ms',
        metricLabel: 'inference cycle',
        delay: 1.0,
    },
    {
        targetX: 200,
        targetY: 384,
        bendX: 320,
        labelX: 332,
        labelY: 388,
        label: 'Drop chute',
        metric: '1080p',
        metricLabel: 'cup, lid, straw classes',
        delay: 1.2,
    },
];

export function BinSchematic() {
    const prefersReducedMotion = useReducedMotion();
    const annotationInitial = prefersReducedMotion ? false : { pathLength: 0, opacity: 0 };
    const captionInitial = prefersReducedMotion ? false : { opacity: 0, x: 6 };

    return (
        <div className="relative h-full w-full" aria-hidden>
            <svg
                viewBox="0 0 540 460"
                preserveAspectRatio="xMidYMid meet"
                className="block h-full w-full"
            >
                <defs>
                    <pattern
                        id="precise-grid"
                        width="36"
                        height="36"
                        patternUnits="userSpaceOnUse"
                    >
                        <path
                            d="M 36 0 L 0 0 0 36"
                            fill="none"
                            stroke="var(--color-border)"
                            strokeWidth="0.4"
                            opacity="0.6"
                        />
                    </pattern>
                </defs>

                <rect width="540" height="460" fill="url(#precise-grid)" />

                <motion.g
                    initial={prefersReducedMotion ? false : { opacity: 0, scale: 0.985 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ duration: 0.8, ease: [0.22, 1, 0.36, 1] }}
                    style={{ transformOrigin: '180px 230px' }}
                >
                    <rect
                        x="120"
                        y="60"
                        width="160"
                        height="350"
                        rx="14"
                        fill="var(--color-card)"
                        stroke="var(--color-foreground)"
                        strokeWidth="1.1"
                    />
                    <rect
                        x="135"
                        y="78"
                        width="130"
                        height="3"
                        fill="var(--color-foreground)"
                        opacity="0.85"
                    />
                    <circle cx="200" cy="78" r="6.5" fill="var(--color-card)" stroke="var(--color-foreground)" strokeWidth="1" />
                    <circle cx="200" cy="78" r="2.2" fill="var(--color-foreground)" />
                    <rect
                        x="148"
                        y="124"
                        width="104"
                        height="42"
                        rx="3"
                        fill="none"
                        stroke="var(--color-foreground)"
                        strokeWidth="0.9"
                    />
                    <text
                        x="200"
                        y="151"
                        textAnchor="middle"
                        fontSize="11"
                        letterSpacing="2"
                        fill="var(--color-foreground)"
                        style={{ textTransform: 'uppercase', fontFamily: 'var(--font-sans)' }}
                    >
                        BRAND
                    </text>
                    <rect
                        x="148"
                        y="194"
                        width="104"
                        height="156"
                        rx="2"
                        fill="none"
                        stroke="var(--color-border)"
                        strokeWidth="0.9"
                    />
                    <line
                        x1="148"
                        y1="266"
                        x2="252"
                        y2="266"
                        stroke="var(--color-border)"
                        strokeWidth="0.7"
                        strokeDasharray="2 3"
                    />
                    <text
                        x="200"
                        y="248"
                        textAnchor="middle"
                        fontSize="9"
                        letterSpacing="1.4"
                        fill="var(--color-muted-foreground)"
                        style={{ textTransform: 'uppercase', fontFamily: 'var(--font-mono)' }}
                    >
                        IoT BOARD
                    </text>
                    <text
                        x="200"
                        y="304"
                        textAnchor="middle"
                        fontSize="9"
                        letterSpacing="1.4"
                        fill="var(--color-muted-foreground)"
                        style={{ textTransform: 'uppercase', fontFamily: 'var(--font-mono)' }}
                    >
                        BATTERY
                    </text>
                    <rect
                        x="156"
                        y="368"
                        width="88"
                        height="22"
                        rx="3"
                        fill="var(--color-muted)"
                        stroke="var(--color-foreground)"
                        strokeWidth="0.8"
                    />
                    <text
                        x="200"
                        y="383"
                        textAnchor="middle"
                        fontSize="8"
                        letterSpacing="1.6"
                        fill="var(--color-foreground)"
                        style={{ textTransform: 'uppercase', fontFamily: 'var(--font-mono)' }}
                    >
                        DROP CHUTE
                    </text>
                </motion.g>

                {annotations.map((a, i) => (
                    <g key={i}>
                        <motion.path
                            d={`M ${a.targetX} ${a.targetY} L ${a.bendX} ${a.targetY}`}
                            fill="none"
                            stroke="var(--color-foreground)"
                            strokeWidth="0.7"
                            initial={annotationInitial}
                            animate={{ pathLength: 1, opacity: 1 }}
                            transition={{
                                delay: a.delay,
                                duration: 0.5,
                                ease: [0.22, 1, 0.36, 1],
                            }}
                        />
                        <motion.g
                            initial={captionInitial}
                            animate={{ opacity: 1, x: 0 }}
                            transition={{
                                delay: a.delay + 0.2,
                                duration: 0.4,
                                ease: [0.22, 1, 0.36, 1],
                            }}
                        >
                            <text
                                x={a.labelX}
                                y={a.labelY - 10}
                                fontSize="8"
                                letterSpacing="1.4"
                                fill="var(--color-muted-foreground)"
                                style={{
                                    textTransform: 'uppercase',
                                    fontFamily: 'var(--font-mono)',
                                }}
                            >
                                0{i + 1} · {a.label}
                            </text>
                            <text
                                x={a.labelX}
                                y={a.labelY + 6}
                                fontSize="20"
                                fontWeight="500"
                                fill="var(--color-foreground)"
                                style={{
                                    fontFamily: 'var(--font-mono)',
                                    fontVariantNumeric: 'tabular-nums',
                                }}
                            >
                                {a.metric}
                            </text>
                            <text
                                x={a.labelX}
                                y={a.labelY + 22}
                                fontSize="10"
                                fill="var(--color-muted-foreground)"
                                style={{ fontFamily: 'var(--font-sans)' }}
                            >
                                {a.metricLabel}
                            </text>
                        </motion.g>
                    </g>
                ))}

                <motion.g
                    initial={prefersReducedMotion ? false : { opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ delay: 1.4, duration: 0.5 }}
                >
                    <text
                        x="22"
                        y="442"
                        fontSize="9"
                        letterSpacing="1.6"
                        fill="var(--color-muted-foreground)"
                        style={{
                            textTransform: 'uppercase',
                            fontFamily: 'var(--font-mono)',
                        }}
                    >
                        MOBIUS · M-01 · UNIT 1.4 · 2026
                    </text>
                </motion.g>
            </svg>
        </div>
    );
}
