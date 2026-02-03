#!/usr/bin/env python3
import argparse
import json

from PIL import Image
import torch
from torchvision import models


def main() -> None:
    parser = argparse.ArgumentParser(description="Run one image through a pretrained model.")
    parser.add_argument("image_path", help="Path to an input image")
    args = parser.parse_args()

    weights = models.MobileNet_V2_Weights.DEFAULT
    model = models.mobilenet_v2(weights=weights)
    model.eval()

    image = Image.open(args.image_path).convert("RGB")
    tensor = weights.transforms()(image).unsqueeze(0)

    with torch.no_grad():
        logits = model(tensor)
        probs = torch.nn.functional.softmax(logits, dim=1)

    confidence, index = probs.max(1)
    categories = weights.meta.get("categories", [])
    label = categories[index.item()] if categories else f"class_{index.item()}"

    print(json.dumps({"waste_type": label, "confidence": float(confidence.item())}))


if __name__ == "__main__":
    main()
