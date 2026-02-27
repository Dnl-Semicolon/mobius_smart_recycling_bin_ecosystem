#!/usr/bin/env python3
"""
Bakeoff evaluation script — implements EVAL_PROTOCOL_v1.md.

Runs yolo val on a model+test set, measures latency, checks FP on empty frames,
and outputs a comprehensive metrics JSON.

Usage:
    python ai/src/evaluate_bakeoff.py \
        --model ai/models/yolov11n_waste_v1/weights/best.pt \
        --data ai/data/data.yaml \
        --name yolov11n_waste_v1 \
        [--empty-dir ai/data/cups_labeled/yolo_day1/test/empty] \
        [--latency-image ai/data/cups_labeled/yolo_day1/test/images/sample.jpg] \
        [--latency-runs 50]

Outputs:
    ai/runs/bakeoff/<name>_metrics.json
"""
from __future__ import annotations

import argparse
import json
import os
import sys
import time
from pathlib import Path
from typing import Any


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Bakeoff evaluation")
    parser.add_argument(
        "--model", type=Path, required=True,
        help="Path to model weights (e.g., best.pt)",
    )
    parser.add_argument(
        "--data", type=Path, required=True,
        help="Path to data.yaml",
    )
    parser.add_argument(
        "--name", type=str, required=True,
        help="Experiment name (used in output filename)",
    )
    parser.add_argument(
        "--empty-dir", type=Path, default=None,
        help="Directory of empty-bin images for FP testing",
    )
    parser.add_argument(
        "--latency-image", type=Path, default=None,
        help="Single image to use for latency benchmarking",
    )
    parser.add_argument(
        "--latency-runs", type=int, default=50,
        help="Number of runs for latency measurement",
    )
    parser.add_argument(
        "--conf", type=float, default=0.25,
        help="Confidence threshold for val (default: 0.25 per protocol)",
    )
    parser.add_argument(
        "--iou", type=float, default=0.45,
        help="NMS IoU threshold (default: 0.45 per protocol)",
    )
    parser.add_argument(
        "--imgsz", type=int, default=640,
        help="Image size (default: 640 per protocol)",
    )
    parser.add_argument(
        "--device", type=str, default="cpu",
        help="Device for evaluation (default: cpu per protocol)",
    )
    return parser.parse_args()


CLASS_NAMES = ["cup", "lid", "straw", "liquid_waste"]


def run_yolo_val(args: argparse.Namespace) -> dict[str, Any]:
    """Run yolo val and extract metrics."""
    from ultralytics import YOLO

    model = YOLO(str(args.model))
    results = model.val(
        data=str(args.data),
        imgsz=args.imgsz,
        conf=args.conf,
        iou=args.iou,
        device=args.device,
        verbose=False,
    )

    # Extract aggregate metrics
    metrics: dict[str, Any] = {}
    metrics["mAP50"] = float(results.box.map50)
    metrics["mAP50_95"] = float(results.box.map)

    # Per-class metrics from results.box
    # results.box.ap50 is an array of AP@0.5 per class
    per_class: dict[str, dict[str, float]] = {}
    ap50_per_class = results.box.ap50
    precision_per_class = results.box.p
    recall_per_class = results.box.r

    for i, name in enumerate(CLASS_NAMES):
        if i < len(ap50_per_class):
            per_class[name] = {
                "AP50": round(float(ap50_per_class[i]), 4),
                "precision": round(float(precision_per_class[i]), 4),
                "recall": round(float(recall_per_class[i]), 4),
            }
        else:
            per_class[name] = {"AP50": 0.0, "precision": 0.0, "recall": 0.0}

    metrics["per_class"] = per_class
    return metrics


def measure_latency(args: argparse.Namespace) -> dict[str, float]:
    """Measure inference latency P50 and P95 over N runs."""
    if args.latency_image is None:
        return {"p50_ms": -1, "p95_ms": -1, "runs": 0}

    from ultralytics import YOLO

    model = YOLO(str(args.model))
    image_path = str(args.latency_image)
    n = args.latency_runs

    # Warmup run
    model.predict(source=image_path, imgsz=args.imgsz, conf=args.conf, verbose=False)

    timings: list[float] = []
    for _ in range(n):
        t0 = time.perf_counter()
        model.predict(source=image_path, imgsz=args.imgsz, conf=args.conf, verbose=False)
        elapsed = (time.perf_counter() - t0) * 1000
        timings.append(elapsed)

    timings.sort()
    p50_idx = max(0, int(n * 0.50) - 1)
    p95_idx = max(0, int(n * 0.95) - 1)

    return {
        "p50_ms": round(timings[p50_idx], 1),
        "p95_ms": round(timings[p95_idx], 1),
        "runs": n,
    }


def count_empty_fp(args: argparse.Namespace) -> dict[str, Any]:
    """Count false positive detections on empty-bin images."""
    if args.empty_dir is None or not args.empty_dir.exists():
        return {"empty_images": 0, "false_positives": -1, "note": "no empty-dir provided"}

    from ultralytics import YOLO

    model = YOLO(str(args.model))
    extensions = {".jpg", ".jpeg", ".png", ".bmp", ".webp"}
    empty_images = [
        f for f in args.empty_dir.iterdir()
        if f.is_file() and f.suffix.lower() in extensions
    ]

    total_fp = 0
    for img in empty_images:
        results = model.predict(
            source=str(img), imgsz=args.imgsz, conf=args.conf, verbose=False,
        )
        if results and results[0].boxes is not None:
            total_fp += len(results[0].boxes)

    return {
        "empty_images": len(empty_images),
        "false_positives": total_fp,
    }


def main() -> int:
    args = parse_args()

    if not args.model.exists():
        print(f"ERROR: Model not found: {args.model}", file=sys.stderr)
        return 1

    if not args.data.exists():
        print(f"ERROR: data.yaml not found: {args.data}", file=sys.stderr)
        return 1

    print(f"Evaluating: {args.name}")
    print(f"  Model: {args.model}")
    print(f"  Data: {args.data}")
    print(f"  Device: {args.device}")
    print()

    # 1. Run yolo val
    print("Running yolo val...")
    val_metrics = run_yolo_val(args)
    print(f"  mAP@0.5: {val_metrics['mAP50']:.4f}")
    print(f"  mAP@0.5:0.95: {val_metrics['mAP50_95']:.4f}")
    print()

    # 2. Measure latency
    print("Measuring latency...")
    latency = measure_latency(args)
    if latency["runs"] > 0:
        print(f"  P50: {latency['p50_ms']}ms, P95: {latency['p95_ms']}ms ({latency['runs']} runs)")
    else:
        print("  Skipped (no --latency-image provided)")
    print()

    # 3. Count FP on empty frames
    print("Checking false positives on empty frames...")
    fp_result = count_empty_fp(args)
    if fp_result["empty_images"] > 0:
        print(f"  {fp_result['false_positives']} FP across {fp_result['empty_images']} empty images")
    else:
        print("  Skipped (no --empty-dir provided)")
    print()

    # 4. Model file size
    model_size_mb = round(os.path.getsize(args.model) / (1024 * 1024), 2)

    # 5. Assemble output
    output: dict[str, Any] = {
        "experiment_name": args.name,
        "model_path": str(args.model),
        "data_yaml": str(args.data),
        "eval_params": {
            "conf_threshold": args.conf,
            "nms_iou_threshold": args.iou,
            "imgsz": args.imgsz,
            "device": args.device,
        },
        "mAP50": round(val_metrics["mAP50"], 4),
        "mAP50_95": round(val_metrics["mAP50_95"], 4),
        "per_class": val_metrics["per_class"],
        "latency": latency,
        "model_size_mb": model_size_mb,
        "false_positives_empty": fp_result,
        "min_class_AP50": round(
            min(c["AP50"] for c in val_metrics["per_class"].values()), 4
        ),
    }

    # Write output
    out_dir = Path("ai/runs/bakeoff")
    out_dir.mkdir(parents=True, exist_ok=True)
    out_path = out_dir / f"{args.name}_metrics.json"
    with open(out_path, "w") as f:
        json.dump(output, f, indent=2)

    print(f"Metrics written to: {out_path}")
    print(f"  mAP@0.5 = {output['mAP50']:.4f}")
    print(f"  min class AP@0.5 = {output['min_class_AP50']:.4f}")
    print(f"  model size = {model_size_mb} MB")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
