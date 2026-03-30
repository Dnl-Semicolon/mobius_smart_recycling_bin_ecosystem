import { useState, useEffect } from "react";
import { checkLaravelHealth, checkAiHealth } from "../services/api";

interface HealthState {
  laravel: "ok" | "error" | "checking";
  ai: "ok" | "error" | "checking";
  detector: string;
}

interface Props {
  onHealthChange: (allHealthy: boolean) => void;
}

export function HealthIndicators({ onHealthChange }: Props) {
  const [health, setHealth] = useState<HealthState>({
    laravel: "checking",
    ai: "checking",
    detector: "",
  });

  useEffect(() => {
    async function check() {
      const [laravelOk, aiResult] = await Promise.all([
        checkLaravelHealth(),
        checkAiHealth(),
      ]);

      const newHealth: HealthState = {
        laravel: laravelOk ? "ok" : "error",
        ai: aiResult ? "ok" : "error",
        detector: aiResult?.detector || "",
      };

      setHealth(newHealth);
      onHealthChange(newHealth.laravel === "ok" && newHealth.ai === "ok");
    }

    check();
    const timer = setInterval(check, 10000);
    return () => clearInterval(timer);
  }, [onHealthChange]);

  return (
    <div className="flex gap-4 text-xs font-mono">
      <Dot status={health.laravel} label="Laravel API" />
      <Dot
        status={health.ai}
        label="AI"
      />
    </div>
  );
}

function Dot({ status, label }: { status: string; label: string }) {
  const color =
    status === "ok"
      ? "bg-green-500"
      : status === "error"
        ? "bg-red-500"
        : "bg-yellow-400";

  return (
    <span className="flex items-center gap-1.5">
      <span className={`inline-block w-2 h-2 rounded-full ${color}`} />
      {label}
    </span>
  );
}
