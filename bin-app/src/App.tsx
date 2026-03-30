import { useRef, useState, useEffect } from "react";
import { CameraFeed, type CameraFeedHandle } from "./components/CameraFeed";
import { QRDisplay } from "./components/QRDisplay";
import { DetectionPanel } from "./components/DetectionPanel";
import { resolveBin, classifyImage, reportDetection } from "./services/api";
import type { ClassifyResult } from "./types";
import "./App.css";

const BIN_SERIAL = import.meta.env.VITE_BIN_SERIAL || "MBR-2026-001";

function App() {
  const cameraRef = useRef<CameraFeedHandle>(null);

  const [binId, setBinId] = useState<number | null>(null);
  const [status, setStatus] = useState("Resolving bin...");
  const [statusError, setStatusError] = useState(false);

  const [detecting, setDetecting] = useState(false);
  const [result, setResult] = useState<ClassifyResult | null>(null);
  const [userId, setUserId] = useState<number | null>(null);
  const [error, setError] = useState("");

  useEffect(() => {
    resolveBin(BIN_SERIAL)
      .then((res) => {
        setBinId(res.data.id);
        setStatus(`Bin #${res.data.id} — ${res.data.serial_number}`);
        setStatusError(false);
      })
      .catch((err) => {
        setStatus(`Failed to resolve bin: ${err.message}`);
        setStatusError(true);
      });
  }, []);

  async function handleDetect() {
    if (!binId) {
      setError("Bin not resolved yet");
      return;
    }

    setDetecting(true);
    setError("");
    setResult(null);
    setUserId(null);

    try {
      const blob = await cameraRef.current?.captureFrame();
      if (!blob) {
        setError("Failed to capture frame");
        return;
      }
      setStatus("Classifying...");

      const classResult = await classifyImage(blob);
      setResult(classResult);
      setStatus("Reporting to backend...");

      const detection = await reportDetection(binId, classResult);
      setUserId(detection.data.user_id);

      setStatus(`Detection #${detection.data.id} recorded`);
      setStatusError(false);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Detection failed");
      setStatus("Error — see details");
      setStatusError(true);
    } finally {
      setDetecting(false);
    }
  }

  return (
    <div className="grid grid-cols-[1fr_384px] h-screen bg-white text-black">
      <div className="flex flex-col bg-black">
        <CameraFeed ref={cameraRef} />
        <div
          className={`px-4 py-2 font-mono text-xs border-t border-gray-700 ${
            statusError ? "text-red-500 bg-gray-900" : "text-green-400 bg-gray-900"
          }`}
        >
          {status}
        </div>
      </div>

      <div className="flex flex-col gap-6 p-6 border-l-2 border-black overflow-y-auto">
        <h1 className="text-xl font-bold tracking-tight">MOBIUS BIN</h1>
        <QRDisplay serial={BIN_SERIAL} />
        <hr className="border-gray-200" />
        <DetectionPanel
          result={result}
          userId={userId}
          detecting={detecting}
          onDetect={handleDetect}
          error={error}
        />
      </div>
    </div>
  );
}

export default App;
