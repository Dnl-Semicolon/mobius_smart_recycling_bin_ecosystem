type LimitInfo = {
    current: number;
    max: number | null;
    reached: boolean;
    unlimited: boolean;
};

export default function LimitBanner({
    label,
    limitInfo,
}: {
    label: string;
    limitInfo: LimitInfo | null;
}) {
    if (!limitInfo) return null;

    if (limitInfo.unlimited) {
        return (
            <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-400">
                {label}: {limitInfo.current} used — <span className="font-medium">unlimited</span>
            </div>
        );
    }

    if (limitInfo.reached) {
        return (
            <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 dark:border-red-800 dark:bg-red-950">
                <p className="text-sm font-medium text-red-700 dark:text-red-400">
                    {label}: {limitInfo.current} of {limitInfo.max} used — limit reached
                </p>
                <p className="mt-1 text-xs text-red-600 dark:text-red-500">
                    Upgrade your plan to add more. Contact admin or visit billing.
                </p>
            </div>
        );
    }

    return (
        <div className="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
            {label}: <span className="font-medium">{limitInfo.current} of {limitInfo.max}</span> used
        </div>
    );
}
