// @ts-expect-error CJS module with incorrect default-export type declarations
import { QRCode } from "react-qr-code";

interface Props {
  serial: string;
}

export function QRDisplay({ serial }: Props) {
  return (
    <div className="text-center">
      <div className="inline-block bg-white p-4">
        <QRCode value={serial} size={180} level="M" />
      </div>
      <p className="font-mono text-sm mt-2">{serial}</p>
      <p className="text-xs text-gray-500">Scan with Mobius app</p>
    </div>
  );
}
