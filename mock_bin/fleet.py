"""Fleet launcher — spawn multiple mock bin instances from fleet.yaml."""

from __future__ import annotations

import subprocess
import sys
import signal
import time
from pathlib import Path

import yaml


def main():
    fleet_path = Path("fleet.yaml")
    if not fleet_path.exists():
        print("fleet.yaml not found")
        sys.exit(1)

    with open(fleet_path) as f:
        fleet = yaml.safe_load(f)

    bins = fleet.get("bins", [])
    if not bins:
        print("No bins configured in fleet.yaml")
        sys.exit(1)

    processes: list[subprocess.Popen] = []

    def shutdown(sig, frame):
        print("\nShutting down fleet...")
        for p in processes:
            p.terminate()
        for p in processes:
            p.wait(timeout=5)
        sys.exit(0)

    signal.signal(signal.SIGINT, shutdown)
    signal.signal(signal.SIGTERM, shutdown)

    print(f"Launching {len(bins)} bin(s)...\n")

    for cfg in bins:
        cmd = [
            sys.executable, "main.py",
            "--serial", cfg["serial"],
            "--port", str(cfg["port"]),
            "--camera", str(cfg.get("camera_index", -1)),
            "--detector", cfg.get("detector", "mock"),
        ]
        p = subprocess.Popen(cmd)
        processes.append(p)
        print(f"  [{cfg['serial']}] PID {p.pid} → http://localhost:{cfg['port']}")

    print(f"\nFleet running. Press Ctrl+C to stop all.\n")

    # Wait for all processes
    try:
        while True:
            for p in processes:
                ret = p.poll()
                if ret is not None:
                    print(f"  Process {p.pid} exited with code {ret}")
            time.sleep(1)
    except KeyboardInterrupt:
        shutdown(None, None)


if __name__ == "__main__":
    main()
