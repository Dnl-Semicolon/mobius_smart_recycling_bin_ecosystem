"""Mobius Smart Recycling Bin — Entry Point.

Usage:
    python main.py                           # default config.yaml
    python main.py --config custom.yaml      # custom config
    python main.py --serial MBR-2026-002 --port 9002  # override via CLI
"""

from __future__ import annotations

import argparse
import os
from pathlib import Path

import uvicorn
from dotenv import load_dotenv

from bin_os.brain import BinBrain
from bin_os.config import AppConfig, load_config
from bin_os.network.api_client import BackendClient
from bin_os.vision.camera import Camera


def build_detector(config: AppConfig):
    """Build the appropriate detector based on config."""
    if config.vision.detector == "openai":
        from bin_os.vision.openai_detector import OpenAIDetector
        return OpenAIDetector(model=config.vision.openai_model)
    elif config.vision.detector == "yolo":
        from bin_os.vision.yolo_detector import YOLODetector
        return YOLODetector(model_path=config.vision.model_path)
    elif config.vision.detector == "hybrid":
        from bin_os.vision.hybrid_detector import HybridDetector
        return HybridDetector(
            model_path=config.vision.model_path,
            openai_model=config.vision.openai_model,
        )
    elif config.vision.detector == "mock":
        from bin_os.vision.mock_detector import MockDetector
        return MockDetector()
    else:
        raise ValueError(f"Unknown detector: {config.vision.detector}")


def main():
    parser = argparse.ArgumentParser(description="Mobius Smart Recycling Bin")
    parser.add_argument("--config", default="config.yaml", help="Path to config YAML file")
    parser.add_argument("--serial", help="Override bin serial number")
    parser.add_argument("--port", type=int, help="Override server port")
    parser.add_argument("--camera", type=int, help="Override camera index (-1 for no camera)")
    parser.add_argument("--detector", choices=["openai", "yolo", "mock", "hybrid"], help="Override detector type")
    parser.add_argument("--env", default="../backend/.env", help="Path to .env file for API keys")
    args = parser.parse_args()

    # Load .env for API keys (shares backend's .env by default)
    env_path = Path(args.env)
    if env_path.exists():
        load_dotenv(env_path)

    # Load config
    config_path = Path(args.config)
    config = load_config(config_path)

    # Apply CLI overrides
    if args.serial:
        config.bin.serial = args.serial
    if args.port:
        config.server.port = args.port
    if args.camera is not None:
        config.hardware.camera_index = args.camera
    if args.detector:
        config.vision.detector = args.detector

    print(f"╔══════════════════════════════════════════╗")
    print(f"║     MOBIUS SMART RECYCLING BIN           ║")
    print(f"║     Serial: {config.bin.serial:<27s} ║")
    print(f"║     Port:   {config.server.port:<27d} ║")
    print(f"║     Detect: {config.vision.detector:<27s} ║")
    print(f"╚══════════════════════════════════════════╝")

    # Initialize components
    brain = BinBrain(config=config)
    camera = Camera(camera_index=config.hardware.camera_index)
    detector = build_detector(config)
    backend = BackendClient(config)

    # Open camera
    if config.hardware.camera_index >= 0:
        if camera.open():
            print(f"[camera] Opened camera index {config.hardware.camera_index}")
        else:
            print(f"[camera] Failed to open camera index {config.hardware.camera_index} — running headless")
    else:
        print("[camera] Camera disabled (index = -1)")

    # Build and run FastAPI app
    from bin_os.server.app import create_app
    app = create_app(config, brain, camera, detector, backend)

    print(f"\n  Display: http://localhost:{config.server.port}")
    print(f"  Status:  http://localhost:{config.server.port}/api/status")
    print(f"  Camera:  http://localhost:{config.server.port}/camera/stream\n")

    try:
        uvicorn.run(app, host=config.server.host, port=config.server.port, log_level="warning")
    finally:
        camera.close()


if __name__ == "__main__":
    main()
