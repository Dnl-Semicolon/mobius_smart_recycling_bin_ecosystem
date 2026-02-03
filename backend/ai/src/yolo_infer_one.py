#!/usr/bin/env python3
"""
YOLOv11 single-image inference stub.

Usage:
    python ai/src/yolo_infer_one.py /path/to/image.jpg

Outputs JSON to stdout:
    {"class": "cup", "confidence": 0.93, "bbox": [x, y, w, h]}
"""
from __future__ import annotations

import argparse
import json
from pathlib import Path
from typing import Any


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Run YOLOv11 inference on a single image",
    )
    parser.add_argument("image_path", type=Path, help="Path to an image file")
    parser.add_argument(
        "--model",
        default="yolo11n.pt",
        help="Path or name of a YOLOv11 pretrained model",
    )
    return parser.parse_args()


def build_empty_result() -> dict[str, Any]:
    return {
        "class": None,
        "confidence": 0.0,
        "bbox": [0, 0, 0, 0],
    }


def main() -> int:
    args = parse_args()
    image_path: Path = args.image_path.expanduser().resolve()

    if not image_path.exists() or not image_path.is_file():
        print(json.dumps(build_empty_result()))
        return 1

    try:
        from ultralytics import YOLO
    except Exception:
        print(json.dumps(build_empty_result()))
        return 1

    model = YOLO(args.model)
    results = model.predict(source=str(image_path), verbose=False)

    if not results:
        print(json.dumps(build_empty_result()))
        return 0

    result = results[0]
    boxes = result.boxes

    if boxes is None or len(boxes) == 0:
        print(json.dumps(build_empty_result()))
        return 0

    confs = boxes.conf
    best_index = int(confs.argmax().item())

    class_id = int(boxes.cls[best_index].item())
    class_name = model.names.get(class_id, str(class_id))
    confidence = float(confs[best_index].item())

    xywh = boxes.xywh[best_index].tolist()
    bbox = [float(x) for x in xywh]

    output = {
        "class": class_name,
        "confidence": confidence,
        "bbox": bbox,
    }

    print(json.dumps(output))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
