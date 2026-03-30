import type { ClassifyResult, DetectionResponse, BinResolveResponse, AiHealthResponse } from "../types";

const LARAVEL_URL = import.meta.env.VITE_LARAVEL_URL;
const AI_URL = import.meta.env.VITE_AI_URL;

export async function resolveBin(serial: string): Promise<BinResolveResponse> {
  const res = await fetch(`${LARAVEL_URL}/bins/resolve/${serial}`);
  if (!res.ok) throw new Error(`Bin not found: ${serial}`);
  return res.json();
}

export async function classifyImage(imageBlob: Blob): Promise<ClassifyResult> {
  const formData = new FormData();
  formData.append("image", imageBlob, "frame.jpg");

  const res = await fetch(`${AI_URL}/classify`, {
    method: "POST",
    body: formData,
  });
  if (!res.ok) throw new Error(`Classification failed: ${res.status}`);
  return res.json();
}

export async function reportDetection(
  binId: number,
  result: ClassifyResult,
  imageBlob?: Blob
): Promise<DetectionResponse> {
  const formData = new FormData();
  formData.append("bin_id", String(binId));
  formData.append("waste_type", result.waste_type);
  formData.append("confidence", String(result.confidence));
  if (result.brand) {
    formData.append("detected_brand", result.brand);
  }
  if (imageBlob) {
    formData.append("image", imageBlob, "detection.jpg");
  }

  const res = await fetch(`${LARAVEL_URL}/detect`, {
    method: "POST",
    body: formData,
  });
  if (!res.ok) throw new Error(`Report failed: ${res.status}`);
  return res.json();
}

export async function checkLaravelHealth(): Promise<boolean> {
  try {
    const res = await fetch(`${LARAVEL_URL}/public/stats`, { signal: AbortSignal.timeout(5000) });
    return res.ok;
  } catch {
    return false;
  }
}

export async function checkAiHealth(): Promise<AiHealthResponse | null> {
  try {
    const res = await fetch(`${AI_URL}/health`, { signal: AbortSignal.timeout(5000) });
    if (!res.ok) return null;
    return res.json();
  } catch {
    return null;
  }
}
