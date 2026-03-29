"""Bin state machine and core brain logic."""

from __future__ import annotations

import asyncio
import random
import time
from dataclasses import dataclass, field
from enum import Enum
from typing import Any, Callable, Coroutine

from bin_os.config import AppConfig


class BinState(str, Enum):
    BOOTING = "booting"
    IDLE = "idle"
    DETECTING = "detecting"
    DRAINING = "draining"
    RESULT = "result"
    FEEDBACK = "feedback"
    MAINTENANCE = "maintenance"
    OFFLINE = "offline"


# Per-item physical properties: (volume_ml_range, weight_g_range, liquid_ml_range_or_None)
ITEM_PHYSICS: dict[str, dict[str, Any]] = {
    "paper_cup":    {"compartment": "solids", "volume": (320, 380), "weight": (12, 18), "liquid": (50, 120), "drains": True},
    "plastic_cup":  {"compartment": "solids", "volume": (270, 330), "weight": (10, 15), "liquid": (50, 120), "drains": True},
    "lid":          {"compartment": "solids", "volume": (15, 25),   "weight": (4, 7),   "liquid": None,       "drains": False},
    "straw":        {"compartment": "solids", "volume": (8, 14),    "weight": (1, 3),   "liquid": None,       "drains": False},
    "napkin":       {"compartment": "solids", "volume": (20, 40),   "weight": (3, 8),   "liquid": None,       "drains": False},
    "liquid_waste": {"compartment": "liquid", "volume": None,       "weight": None,     "liquid": (150, 250), "drains": True},
}


@dataclass
class Compartment:
    name: str
    max_volume_ml: int
    max_weight_g: int
    current_volume_ml: float = 0.0
    current_weight_g: float = 0.0

    @property
    def fill_percent(self) -> int:
        vol_pct = (self.current_volume_ml / self.max_volume_ml) * 100 if self.max_volume_ml > 0 else 0
        wt_pct = (self.current_weight_g / self.max_weight_g) * 100 if self.max_weight_g > 0 else 0
        return min(100, int(max(vol_pct, wt_pct)))

    def deposit(self, volume_ml: float, weight_g: float) -> None:
        self.current_volume_ml = min(self.max_volume_ml, self.current_volume_ml + volume_ml)
        self.current_weight_g = min(self.max_weight_g, self.current_weight_g + weight_g)

    def reset(self) -> None:
        self.current_volume_ml = 0.0
        self.current_weight_g = 0.0

    def to_dict(self) -> dict:
        return {
            "fill_percent": self.fill_percent,
            "volume_ml": round(self.current_volume_ml),
            "weight_g": round(self.current_weight_g),
            "max_volume_ml": self.max_volume_ml,
            "max_weight_g": self.max_weight_g,
        }


@dataclass
class DetectionResult:
    waste_type: str
    confidence: int
    brand: str = ""
    needs_drain: bool = False
    volume_ml: float = 0.0
    weight_g: float = 0.0
    liquid_ml: float = 0.0


@dataclass
class BinBrain:
    config: AppConfig
    state: BinState = BinState.BOOTING
    compartments: dict[str, Compartment] = field(default_factory=dict)
    last_detection: DetectionResult | None = None
    last_detection_time: float = 0.0
    total_items_today: int = 0
    _listeners: list[Callable[[str, Any], Coroutine]] = field(default_factory=list)

    def __post_init__(self) -> None:
        self.compartments = {
            "solids": Compartment(
                "solids",
                self.config.compartments.solids.max_volume_ml,
                self.config.compartments.solids.max_weight_g,
            ),
            "liquid": Compartment(
                "liquid",
                self.config.compartments.liquid.max_volume_ml,
                self.config.compartments.liquid.max_weight_g,
            ),
        }

    def on_event(self, callback: Callable[[str, Any], Coroutine]) -> None:
        """Register a listener for state change events."""
        self._listeners.append(callback)

    async def _emit(self, event: str, data: Any = None) -> None:
        for listener in self._listeners:
            try:
                await listener(event, data)
            except Exception:
                pass

    @property
    def fill_level(self) -> int:
        """Overall fill level = max of all compartments."""
        if not self.compartments:
            return 0
        return max(c.fill_percent for c in self.compartments.values())

    @property
    def total_weight_g(self) -> float:
        return sum(c.current_weight_g for c in self.compartments.values())

    @property
    def fill_height_cm(self) -> float:
        """Estimated fill height in cm. Assumes 30cm tall bin with 20x25cm cross-section (500cm²)."""
        solids = self.compartments.get("solids")
        if not solids:
            return 0.0
        cross_section_cm2 = 500  # 20cm x 25cm internal area
        return min(30.0, solids.current_volume_ml / cross_section_cm2)

    @property
    def is_on_cooldown(self) -> bool:
        return (time.time() - self.last_detection_time) < self.config.timers.detection_cooldown_sec

    def compartments_dict(self) -> dict:
        return {name: c.to_dict() for name, c in self.compartments.items()}

    def status_dict(self) -> dict:
        return {
            "serial": self.config.bin.serial,
            "name": self.config.bin.name,
            "state": self.state.value,
            "fill_level": self.fill_level,
            "total_weight_g": round(self.total_weight_g),
            "fill_height_cm": round(self.fill_height_cm, 1),
            "compartments": self.compartments_dict(),
            "last_detection": {
                "waste_type": self.last_detection.waste_type,
                "confidence": self.last_detection.confidence,
                "weight_g": round(self.last_detection.weight_g),
                "brand": self.last_detection.brand,
            } if self.last_detection else None,
            "total_items_today": self.total_items_today,
        }

    async def set_state(self, new_state: BinState) -> None:
        old = self.state
        self.state = new_state
        await self._emit("state_change", {"old": old.value, "new": new_state.value, "status": self.status_dict()})

    async def boot(self) -> None:
        await self.set_state(BinState.BOOTING)
        await asyncio.sleep(0.5)
        await self.set_state(BinState.IDLE)

    async def process_detection(self, waste_type: str, confidence: int, brand: str = "") -> DetectionResult:
        """Full deposit procedure: detect → drain (if cup) → deposit → result."""
        physics = ITEM_PHYSICS.get(waste_type)
        if not physics:
            raise ValueError(f"Unknown waste type: {waste_type}")

        # Build result with randomized physics
        vol_range = physics["volume"]
        wt_range = physics["weight"]
        liq_range = physics["liquid"]

        result = DetectionResult(
            waste_type=waste_type,
            confidence=confidence,
            brand=brand,
            needs_drain=physics["drains"],
            volume_ml=random.uniform(*vol_range) if vol_range else 0,
            weight_g=random.uniform(*wt_range) if wt_range else 0,
            liquid_ml=random.uniform(*liq_range) if liq_range else 0,
        )

        # DETECTING state
        await self.set_state(BinState.DETECTING)
        await asyncio.sleep(0.5)

        # DRAINING state (cups and liquid waste only)
        if result.needs_drain and result.liquid_ml > 0:
            await self.set_state(BinState.DRAINING)
            await self._emit("drain_start", {"liquid_ml": round(result.liquid_ml), "duration_sec": self.config.timers.drain_duration_sec})
            await asyncio.sleep(self.config.timers.drain_duration_sec)
            # Deposit liquid
            self.compartments["liquid"].deposit(result.liquid_ml, result.liquid_ml)  # liquid: 1ml ≈ 1g

        # Deposit solid (if applicable)
        target = physics["compartment"]
        if result.volume_ml > 0:
            self.compartments[target].deposit(result.volume_ml, result.weight_g)

        self.last_detection = result
        self.last_detection_time = time.time()
        self.total_items_today += 1

        # RESULT state — UI controls the flow from here (Next → Feedback → Like/Dislike → Idle)
        await self.set_state(BinState.RESULT)
        await self._emit("detection_complete", {
            "waste_type": result.waste_type,
            "confidence": result.confidence,
            "brand": result.brand,
            "weight_g": round(result.weight_g),
            "fill_level": self.fill_level,
            "total_weight_g": round(self.total_weight_g),
            "fill_height_cm": round(self.fill_height_cm, 1),
            "compartments": self.compartments_dict(),
            "liquid_drained_ml": round(result.liquid_ml) if result.needs_drain else 0,
            "total_items_today": self.total_items_today,
        })

        # Safety timeout: auto-return to idle if user walks away
        self._result_timeout_task = asyncio.create_task(self._safety_timeout())

        return result

    async def _safety_timeout(self) -> None:
        """Auto-return to IDLE if no user interaction within timeout."""
        try:
            await asyncio.sleep(self.config.timers.feedback_timeout_sec)
            if self.state in (BinState.RESULT, BinState.FEEDBACK):
                await self.set_state(BinState.IDLE)
        except asyncio.CancelledError:
            pass

    def _cancel_timeout(self) -> None:
        task = getattr(self, "_result_timeout_task", None)
        if task and not task.done():
            task.cancel()

    async def advance_to_feedback(self) -> None:
        """Transition from RESULT to FEEDBACK (user tapped Next)."""
        if self.state == BinState.RESULT:
            self._cancel_timeout()
            await self.set_state(BinState.FEEDBACK)
            # Restart timeout for feedback screen
            self._result_timeout_task = asyncio.create_task(self._safety_timeout())

    async def complete_feedback(self) -> None:
        """Transition from FEEDBACK back to IDLE (user tapped Like/Dislike)."""
        if self.state == BinState.FEEDBACK:
            self._cancel_timeout()
            await self.set_state(BinState.IDLE)

    async def reset_compartments(self) -> None:
        """Reset all compartments to empty (after pickup)."""
        for c in self.compartments.values():
            c.reset()
        self.total_items_today = 0
        await self._emit("reset", {"fill_level": 0, "compartments": self.compartments_dict()})

    async def enter_maintenance(self) -> None:
        await self.set_state(BinState.MAINTENANCE)

    async def exit_maintenance(self) -> None:
        await self.set_state(BinState.IDLE)
