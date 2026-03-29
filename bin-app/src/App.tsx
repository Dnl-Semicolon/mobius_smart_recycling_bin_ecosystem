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

  // Resolve bin serial → bin_id on mount
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
      // 1. Capture frame from camera
      const blob = await cameraRef.current?.captureFrame();
      if (!blob) {
        setError("Failed to capture frame");
        return;
      }
      setStatus("Classifying...");

      // 2. Send to AI service for classification
      const classResult = await classifyImage(blob);
      setResult(classResult);
      setStatus("Reporting to backend...");

      // 3. Report detection to Laravel
      const detection = await reportDetection(binId, classResult);
      setUserId(detection.data.user_id);

      // Points are awarded server-side. The bin app shows user attribution;
      // the user checks exact points in their iOS app.
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
    <div className="app">
      <div className="camera-section">
        <CameraFeed ref={cameraRef} />
        <div className={`status-bar ${statusError ? "error" : ""}`}>{status}</div>
      </div>

      <div className="sidebar">
        <h1>MOBIUS BIN</h1>
        <QRDisplay serial={BIN_SERIAL} />
        <hr />
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
