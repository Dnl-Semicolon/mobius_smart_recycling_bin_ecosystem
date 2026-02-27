#!/usr/bin/env python3
"""
YOLOv11 single-image inference — contract-compliant output.

Usage:
    python ai/src/yolo_infer_one.py /path/to/image.jpg
    python ai/src/yolo_infer_one.py /path/to/image.jpg --model ai/models/production/best.pt

Outputs JSON to stdout (Inference Contract v1.0.0):
    {
      "detections": [
        {"waste_type": "cup", "confidence": 0.73, "bbox": [x, y, w, h]}
      ],
      "model_version": "yolov11n_waste_v1",
      "latency_ms": 380
    }
"""
from __future__ import annotations

import argparse
import json
import sys
import time
from pathlib import Path
from typing import Any

from yolo_waste_map import map_to_waste_type


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Run YOLOv11 inference on a single image",
    )
    parser.add_argument("image_path", type=Path, help="Path to an image file")
    parser.add_argument(
        "--model",
        default="yolo11n.pt",
        help="Path or name of a YOLOv11 model",
    )
    parser.add_argument(
        "--conf",
        type=float,
        default=0.25,
        help="Confidence threshold for detections",
    )
    parser.add_argument(
        "--imgsz",
        type=int,
        default=640,
        help="Inference image size",
    )
    parser.add_argument(
        "--debug",
        action="store_true",
        help="Print debug JSON to stderr",
    )
    parser.add_argument(
        "--save-debug",
        action="store_true",
        help="Save annotated image when detections exist",
    )
    return parser.parse_args()


def derive_model_version(model_path: str) -> str:
    """Derive a model_version string from the model path.

    Examples:
        "yolo11n.pt" -> "yolo11n_coco"
        "ai/models/yolov11n_waste_v1/weights/best.pt" -> "yolov11n_waste_v1"
        "ai/models/production/best.pt" -> "production"
    """
    p = Path(model_path)
    if p.name == "best.pt" or p.name == "last.pt":
        # Trained model: use parent directory name (or grandparent if inside weights/)
        parent = p.parent
        if parent.name == "weights":
            parent = parent.parent
        return parent.name
    # Pretrained model: strip extension, append _coco
    return p.stem + "_coco"


def build_empty_result(model_version: str, latency_ms: int) -> dict[str, Any]:
    return {
        "detections": [],
        "model_version": model_version,
        "latency_ms": latency_ms,
    }


def print_debug(
    args: argparse.Namespace,
    boxes_count: int,
    top5: list[dict[str, Any]] | str,
) -> None:
    debug_payload = {
        "model": args.model,
        "imgsz": args.imgsz,
        "conf": args.conf,
        "boxes": boxes_count,
        "top5": top5,
    }
    print(json.dumps(debug_payload), file=sys.stderr)


def extract_top5_probs(result: Any, class_names: Any) -> list[dict[str, Any]] | None:
    probs = getattr(result, "probs", None)
    if probs is None:
        return None

    top_indices = getattr(probs, "top5", None)
    top_confs = getattr(probs, "top5conf", None)

    if top_indices is None or top_confs is None:
        return None

    if not isinstance(class_names, dict):
        try:
            class_names = {i: name for i, name in enumerate(class_names)}
        except Exception:
            class_names = {}

    indices_list = list(top_indices)[:5]
    confs_list = list(top_confs)[:5]

    top5 = []
    for idx, conf in zip(indices_list, confs_list):
        class_id = int(idx)
        class_name = class_names.get(class_id, str(class_id))
        top5.append({"class": class_name, "conf": float(conf)})

    return top5


def save_debug_image(result: Any, output_path: Path) -> None:
    try:
        from PIL import Image
    except Exception:
        return

    annotated = result.plot()
    if annotated is None:
        return

    output_path.parent.mkdir(parents=True, exist_ok=True)
    image = Image.fromarray(annotated[:, :, ::-1])
    image.save(output_path)


def main() -> int:
    args = parse_args()
    image_path: Path = args.image_path.expanduser().resolve()
    model_version = derive_model_version(args.model)

    if not image_path.exists() or not image_path.is_file():
        if args.debug:
            print_debug(args, 0, "none")
        print(json.dumps(build_empty_result(model_version, 0)))
        return 1

    try:
        from ultralytics import YOLO
    except Exception:
        if args.debug:
            print_debug(args, 0, "none")
        print(json.dumps(build_empty_result(model_version, 0)))
        return 1

    model = YOLO(args.model)

    t0 = time.perf_counter()
    results = model.predict(
        source=str(image_path),
        imgsz=args.imgsz,
        conf=args.conf,
        verbose=False,
    )
    latency_ms = int(round((time.perf_counter() - t0) * 1000))

    if not results:
        if args.debug:
            print_debug(args, 0, "none")
        print(json.dumps(build_empty_result(model_version, latency_ms)))
        return 0

    result = results[0]
    boxes = result.boxes
    boxes_count = 0 if boxes is None else len(boxes)

    if boxes is None or len(boxes) == 0:
        if args.debug:
            top5 = extract_top5_probs(result, model.names)
            print_debug(args, 0, top5 if top5 is not None else "none")
        print(json.dumps(build_empty_result(model_version, latency_ms)))
        return 0
    elif args.debug:
        top5 = extract_top5_probs(result, model.names)
        print_debug(args, boxes_count, top5 if top5 is not None else "none")

    if args.save_debug:
        output_path = Path("ai/runs/yolo_debug") / f"{image_path.stem}.jpg"
        save_debug_image(result, output_path)

    # Build detections array — all boxes above threshold
    detections: list[dict[str, Any]] = []
    for i in range(len(boxes)):
        class_id = int(boxes.cls[i].item())
        class_name = model.names.get(class_id, str(class_id))
        confidence = float(boxes.conf[i].item())
        xywh = boxes.xywh[i].tolist()
        bbox = [float(x) for x in xywh]

        # Use raw YOLO class name (mapping happens in Laravel bridge)
        # For COCO pretrained, map_to_waste_type translates; for custom-trained, class_name is already correct
        waste_type = map_to_waste_type(class_name) or class_name

        detections.append({
            "waste_type": waste_type,
            "confidence": round(confidence, 4),
            "bbox": bbox,
        })

    # Sort by confidence descending
    detections.sort(key=lambda d: d["confidence"], reverse=True)

    output = {
        "detections": detections,
        "model_version": model_version,
        "latency_ms": latency_ms,
    }

    print(json.dumps(output))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
