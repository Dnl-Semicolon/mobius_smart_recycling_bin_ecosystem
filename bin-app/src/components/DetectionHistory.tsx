import { useState, useEffect } from "react";
import type { HistoryItem } from "../types";

interface Props {
  history: HistoryItem[];
}

export function DetectionHistory({ history }: Props) {
  const [, setTick] = useState(0);

  // Re-render every second to update relative timestamps
  useEffect(() => {
    if (history.length === 0) return;
    const timer = setInterval(() => setTick((t) => t + 1), 1000);
    return () => clearInterval(timer);
  }, [history.length]);

  if (history.length === 0) return null;

  return (
    <div>
      <h2 className="text-sm font-bold text-gray-500 mb-2">RECENT DETECTIONS</h2>
      <div className="flex flex-col gap-1">
        {history.map((item) => (
          <div
            key={item.detectionId}
            className="flex items-center justify-between py-2 px-3 bg-gray-50 text-xs font-mono"
          >
            <div className="flex gap-3 items-center">
              <span className="font-bold">{item.waste_type}</span>
              <span className="text-gray-500">{item.confidence}%</span>
              {item.brand && <span className="text-blue-600">{item.brand}</span>}
            </div>
            <div className="flex gap-3 items-center text-gray-400">
              <span>{item.userId ? `#${item.userId}` : "anon"}</span>
              <span>{relativeTime(item.timestamp)}</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

function relativeTime(date: Date): string {
  const seconds = Math.floor((Date.now() - date.getTime()) / 1000);
  if (seconds < 60) return `${seconds}s ago`;
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `${minutes}m ago`;
  return `${Math.floor(minutes / 60)}h ago`;
}
