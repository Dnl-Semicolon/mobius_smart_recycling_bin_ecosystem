"""Configuration loader — reads config.yaml into typed dataclasses."""

from __future__ import annotations

from dataclasses import dataclass, field
from pathlib import Path

import yaml


@dataclass
class BinConfig:
    serial: str = "MBR-2026-001"
    name: str = "Unnamed Bin"


@dataclass
class LocationConfig:
    latitude: float = 5.4370
    longitude: float = 100.3100
    description: str = ""


@dataclass
class BackendConfig:
    url: str = "http://localhost:8000/api/v1"


@dataclass
class ServerConfig:
    host: str = "0.0.0.0"
    port: int = 9001


@dataclass
class HardwareConfig:
    platform: str = "mock"
    camera_index: int = 0


@dataclass
class VisionConfig:
    detector: str = "openai"
    openai_model: str = "gpt-4o-mini"
    confidence_threshold: int = 40
    accept_threshold: int = 70
    model_path: str = "models/mobius-cup-v1.pt"


@dataclass
class DisplayConfig:
    kiosk_mode: bool = False
    show_dev_controls: bool = True


@dataclass
class TimersConfig:
    heartbeat_interval_sec: int = 60
    user_session_timeout_sec: int = 60
    detection_cooldown_sec: int = 3
    result_display_sec: int = 5
    drain_duration_sec: float = 3.5
    feedback_timeout_sec: int = 30


@dataclass
class CompartmentLimits:
    max_volume_ml: int = 15000
    max_weight_g: int = 5000


@dataclass
class CompartmentsConfig:
    solids: CompartmentLimits = field(default_factory=lambda: CompartmentLimits(15000, 5000))
    liquid: CompartmentLimits = field(default_factory=lambda: CompartmentLimits(3000, 3000))


@dataclass
class AppConfig:
    bin: BinConfig = field(default_factory=BinConfig)
    location: LocationConfig = field(default_factory=LocationConfig)
    backend: BackendConfig = field(default_factory=BackendConfig)
    server: ServerConfig = field(default_factory=ServerConfig)
    hardware: HardwareConfig = field(default_factory=HardwareConfig)
    vision: VisionConfig = field(default_factory=VisionConfig)
    display: DisplayConfig = field(default_factory=DisplayConfig)
    timers: TimersConfig = field(default_factory=TimersConfig)
    compartments: CompartmentsConfig = field(default_factory=CompartmentsConfig)


def _merge(dataclass_type, data: dict):
    """Create a dataclass instance from a dict, ignoring unknown keys."""
    if data is None:
        return dataclass_type()
    known = {f.name for f in dataclass_type.__dataclass_fields__.values()}
    filtered = {k: v for k, v in data.items() if k in known}
    return dataclass_type(**filtered)


def load_config(path: str | Path) -> AppConfig:
    """Load config from a YAML file, falling back to defaults for missing keys."""
    path = Path(path)
    if not path.exists():
        return AppConfig()

    with open(path) as f:
        raw = yaml.safe_load(f) or {}

    compartments_raw = raw.get("compartments", {})
    compartments = CompartmentsConfig(
        solids=_merge(CompartmentLimits, compartments_raw.get("solids")),
        liquid=_merge(CompartmentLimits, compartments_raw.get("liquid")),
    )

    return AppConfig(
        bin=_merge(BinConfig, raw.get("bin")),
        location=_merge(LocationConfig, raw.get("location")),
        backend=_merge(BackendConfig, raw.get("backend")),
        server=_merge(ServerConfig, raw.get("server")),
        hardware=_merge(HardwareConfig, raw.get("hardware")),
        vision=_merge(VisionConfig, raw.get("vision")),
        display=_merge(DisplayConfig, raw.get("display")),
        timers=_merge(TimersConfig, raw.get("timers")),
        compartments=compartments,
    )
