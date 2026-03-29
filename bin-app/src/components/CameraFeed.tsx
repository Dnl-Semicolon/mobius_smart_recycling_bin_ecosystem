import { useEffect, useRef, forwardRef, useImperativeHandle, useState } from "react";

export interface CameraFeedHandle {
  captureFrame: () => Promise<Blob | null>;
}

export const CameraFeed = forwardRef<CameraFeedHandle>(function CameraFeed(_, ref) {
  const videoRef = useRef<HTMLVideoElement>(null);
  const [error, setError] = useState<string>("");

  useEffect(() => {
    let stream: MediaStream | null = null;

    navigator.mediaDevices
      .getUserMedia({ video: { facingMode: "environment", width: 640, height: 480 } })
      .then((s) => {
        stream = s;
        if (videoRef.current) {
          videoRef.current.srcObject = s;
        }
      })
      .catch((err) => {
        setError(`Camera error: ${err.message}`);
      });

    return () => {
      stream?.getTracks().forEach((t) => t.stop());
    };
  }, []);

  useImperativeHandle(ref, () => ({
    captureFrame: async () => {
      const video = videoRef.current;
      if (!video || video.readyState < 2) return null;

      const canvas = document.createElement("canvas");
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      canvas.getContext("2d")!.drawImage(video, 0, 0);

      return new Promise<Blob | null>((resolve) => {
        canvas.toBlob(resolve, "image/jpeg", 0.85);
      });
    },
  }));

  if (error) {
    return <div style={{ padding: 20, color: "red", border: "1px solid red" }}>{error}</div>;
  }

  return (
    <video
      ref={videoRef}
      autoPlay
      playsInline
      muted
      style={{ width: "100%", maxHeight: 480, background: "#000", display: "block" }}
    />
  );
});
