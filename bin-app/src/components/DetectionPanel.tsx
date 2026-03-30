import type { ClassifyResult } from "../types";

interface Props {
  result: ClassifyResult | null;
  userId: number | null;
  detecting: boolean;
  onDetect: () => void;
  disabled: boolean;
  error: string;
  autoMode: boolean;
  onToggleAuto: () => void;
  countdown: number;
}

export function DetectionPanel({
  result,
  userId,
  detecting,
  onDetect,
  disabled,
  error,
  autoMode,
  onToggleAuto,
  countdown,
}: Props) {
  return (
    <div>
      <div className="flex gap-2 mb-4">
        <button
          onClick={onDetect}
          disabled={detecting || disabled}
          className={`flex-1 py-4 px-6 text-lg font-bold text-white border-none ${
            detecting || disabled ? "bg-gray-400 cursor-not-allowed" : "bg-black cursor-pointer hover:bg-gray-800"
          }`}
        >
          {detecting ? "Detecting..." : "DETECT"}
        </button>
        <button
          onClick={onToggleAuto}
          className={`px-4 py-4 text-sm font-bold border-2 ${
            autoMode
              ? "bg-green-600 text-white border-green-600"
              : "bg-white text-black border-black hover:bg-gray-100"
          }`}
        >
          {autoMode ? "AUTO" : "MANUAL"}
        </button>
      </div>

      {autoMode && !detecting && (
        <p className="text-xs text-gray-500 mb-3 font-mono">
          Next scan in {countdown}s
        </p>
      )}

      {error && (
        <div className="p-3 bg-red-50 border border-red-500 text-red-700 mb-3 text-sm">
          {error}
        </div>
      )}

      {result && (
        <table className="w-full font-mono text-sm border-collapse">
          <tbody>
            <Row label="Waste Type" value={result.waste_type} />
            <Row label="Confidence" value={`${result.confidence}%`} />
            <Row label="Brand" value={result.brand || "—"} />
            <Row label="Detector" value={result.detector} />
            <Row label="User" value={userId ? `#${userId} (points awarded)` : "anonymous"} />
          </tbody>
        </table>
      )}
    </div>
  );
}

function Row({ label, value }: { label: string; value: string }) {
  return (
    <tr className="border-b border-gray-200">
      <td className="py-2 px-3 font-bold text-gray-500">{label}</td>
      <td className="py-2 px-3">{value}</td>
    </tr>
  );
}
