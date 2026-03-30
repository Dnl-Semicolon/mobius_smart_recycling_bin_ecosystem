import type { ClassifyResult } from "../types";

interface Props {
  result: ClassifyResult | null;
  userId: number | null;
  detecting: boolean;
  onDetect: () => void;
  error: string;
}

export function DetectionPanel({ result, userId, detecting, onDetect, error }: Props) {
  return (
    <div>
      <button
        onClick={onDetect}
        disabled={detecting}
        className={`w-full py-4 px-8 text-lg font-bold text-white border-none mb-4 ${
          detecting ? "bg-gray-400 cursor-wait" : "bg-black cursor-pointer hover:bg-gray-800"
        }`}
      >
        {detecting ? "Detecting..." : "DETECT"}
      </button>

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
