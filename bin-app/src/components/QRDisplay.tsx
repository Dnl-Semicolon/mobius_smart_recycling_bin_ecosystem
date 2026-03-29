import { QRCode } from "react-qr-code";

interface Props {
  serial: string;
}

export function QRDisplay({ serial }: Props) {
  return (
    <div style={{ textAlign: "center" }}>
      <div style={{ background: "white", padding: 16, display: "inline-block" }}>
        <QRCode value={serial} size={200} level="M" />
      </div>
      <p style={{ fontFamily: "monospace", marginTop: 8, fontSize: 14 }}>
        {serial}
      </p>
      <p style={{ fontSize: 12, color: "#666" }}>Scan with Mobius app</p>
    </div>
  );
}
