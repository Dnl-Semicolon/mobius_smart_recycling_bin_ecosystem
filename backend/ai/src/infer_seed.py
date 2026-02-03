#!/usr/bin/env python3
import csv
import json
from pathlib import Path

from PIL import Image
import torch
from torchvision import models


def load_model():
    weights = models.MobileNet_V2_Weights.DEFAULT
    model = models.mobilenet_v2(weights=weights)
    model.eval()
    return model, weights


def infer(image_path: Path, model, weights):
    image = Image.open(image_path).convert("RGB")
    tensor = weights.transforms()(image).unsqueeze(0)

    with torch.no_grad():
        logits = model(tensor)
        probs = torch.nn.functional.softmax(logits, dim=1)

    confidence, index = probs.max(1)
    categories = weights.meta.get("categories", [])
    label = categories[index.item()] if categories else f"class_{index.item()}"
    return label, float(confidence.item())


def main() -> None:
    labels_path = Path("ai/data/cups_raw/seed/labels_seed.csv")
    output_path = Path("ai/runs/seed_inference_results.jsonl")

    model, weights = load_model()

    output_path.parent.mkdir(parents=True, exist_ok=True)

    with labels_path.open(newline="", encoding="utf-8") as handle, output_path.open(
        "w", encoding="utf-8"
    ) as out:
        reader = csv.DictReader(handle)
        for row in reader:
            filename = row["filename"].strip()
            expected = row["waste_type"].strip()
            image_path = labels_path.parent / filename

            predicted, confidence = infer(image_path, model, weights)

            record = {
                "filename": filename,
                "predicted_waste_type": predicted,
                "confidence": confidence,
                "expected_waste_type": expected,
            }
            out.write(json.dumps(record) + "\n")


if __name__ == "__main__":
    main()
