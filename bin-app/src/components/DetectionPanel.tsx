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
        style={{
          width: "100%",
          padding: "16px 32px",
          fontSize: 18,
          fontWeight: "bold",
          cursor: detecting ? "wait" : "pointer",
          background: detecting ? "#ccc" : "#000",
          color: "#fff",
          border: "none",
          marginBottom: 16,
        }}
      >
        {detecting ? "Detecting..." : "DETECT"}
      </button>

      {error && (
        <div style={{ padding: 12, background: "#fee", border: "1px solid red", marginBottom: 12 }}>
          {error}
        </div>
      )}

      {result && (
        <table style={{ width: "100%", borderCollapse: "collapse", fontFamily: "monospace" }}>
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
    <tr style={{ borderBottom: "1px solid #ddd" }}>
      <td style={{ padding: "8px 12px", fontWeight: "bold", color: "#666" }}>{label}</td>
      <td style={{ padding: "8px 12px" }}>{value}</td>
    </tr>
  );
}
