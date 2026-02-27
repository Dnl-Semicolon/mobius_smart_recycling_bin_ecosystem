#!/usr/bin/env python3
"""
Capture one frame from your MacBook camera and run YOLO inference.

Usage:
    python ai/src/camera_infer.py                    # COCO pretrained (detects cups, bottles, etc.)
    python ai/src/camera_infer.py --model best.pt    # Your custom trained model

What happens:
    1. Opens your camera for ~2 seconds
    2. Captures a single frame
    3. Runs YOLOv11 object detection
    4. Saves the annotated image to ai/runs/camera_snap.jpg
    5. Opens it so you can see the detections

Hold a cup in front of your camera before running!
"""
from __future__ import annotations

import argparse
import subprocess
import sys
import time
from pathlib import Path


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Camera + YOLO inference")
    parser.add_argument("--model", default="yolo11n.pt", help="Model path")
    parser.add_argument("--conf", type=float, default=0.25, help="Confidence threshold")
    return parser.parse_args()


def main() -> int:
    args = parse_args()

    try:
        import cv2
    except ImportError:
        print("Installing opencv-python...")
        subprocess.check_call([sys.executable, "-m", "pip", "install", "opencv-python", "--quiet"])
        import cv2

    from ultralytics import YOLO

    output_dir = Path("ai/runs")
    output_dir.mkdir(parents=True, exist_ok=True)
    snap_path = output_dir / "camera_snap.jpg"
    result_path = output_dir / "camera_snap_result.jpg"

    # Capture a frame
    print("Opening camera... hold a cup in front!")
    print("Capturing in 2 seconds...")
    cap = cv2.VideoCapture(0)

    if not cap.isOpened():
        print("ERROR: Could not open camera. Check System Settings > Privacy > Camera.")
        return 1

    # Warm up camera (auto-exposure needs a moment)
    for _ in range(30):
        cap.read()

    time.sleep(0.5)
    ret, frame = cap.read()
    cap.release()

    if not ret or frame is None:
        print("ERROR: Failed to capture frame.")
        return 1

    cv2.imwrite(str(snap_path), frame)
    print(f"Captured: {snap_path} ({frame.shape[1]}x{frame.shape[0]})")

    # Run inference
    print(f"\nRunning YOLO inference (model={args.model}, conf={args.conf})...")
    model = YOLO(args.model)

    t0 = time.perf_counter()
    results = model.predict(
        source=str(snap_path),
        conf=args.conf,
        imgsz=640,
        verbose=False,
    )
    latency = (time.perf_counter() - t0) * 1000

    result = results[0]
    boxes = result.boxes

    # Print detections
    print(f"\nDone in {latency:.0f}ms")
    if boxes is None or len(boxes) == 0:
        print("No detections. Try holding a cup closer to the camera.")
    else:
        print(f"Found {len(boxes)} object(s):\n")
        for i in range(len(boxes)):
            cls_id = int(boxes.cls[i].item())
            cls_name = model.names.get(cls_id, str(cls_id))
            conf = float(boxes.conf[i].item())
            print(f"  {i+1}. {cls_name} ({conf:.0%})")

    # Save annotated image
    annotated = result.plot()
    cv2.imwrite(str(result_path), annotated)
    print(f"\nSaved annotated image: {result_path}")

    # Open it
    subprocess.run(["open", str(result_path)])
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
