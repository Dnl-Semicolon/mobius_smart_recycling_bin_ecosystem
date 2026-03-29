"""HTTP client for communicating with the Laravel backend."""

from __future__ import annotations

import httpx

from bin_os.config import AppConfig


class BackendClient:
    def __init__(self, config: AppConfig):
        self.base_url = config.backend.url.rstrip("/")
        self.serial = config.bin.serial
        self._client = httpx.AsyncClient(timeout=10.0)
        self._bin_id: int | None = None

    async def resolve_bin_id(self) -> int | None:
        """Look up our bin_id from the backend by serial number."""
        if self._bin_id is not None:
            return self._bin_id
        try:
            resp = await self._client.get(f"{self.base_url}/bins/resolve/{self.serial}")
            if resp.status_code == 200:
                self._bin_id = resp.json()["data"]["id"]
                return self._bin_id
        except (httpx.HTTPError, KeyError):
            pass
        return None

    async def send_detection(
        self,
        bin_id: int,
        waste_type: str,
        confidence: int,
        weight_g: int = 0,
        image_bytes: bytes | None = None,
        brand: str = "",
    ) -> dict | None:
        """POST /detect with edge-computed classification."""
        data = {
            "bin_id": str(bin_id),
            "waste_type": waste_type,
            "confidence": str(confidence),
            "weight_g": str(weight_g),
        }
        if brand:
            data["detected_brand"] = brand

        files = None
        if image_bytes:
            files = {"image": ("capture.jpg", image_bytes, "image/jpeg")}

        try:
            resp = await self._client.post(f"{self.base_url}/detect", data=data, files=files)
            if resp.status_code == 201:
                return resp.json()
        except httpx.HTTPError:
            pass
        return None

    async def send_feedback(self, detection_event_id: int, accurate: bool) -> bool:
        """POST feedback for a detection event."""
        try:
            resp = await self._client.post(
                f"{self.base_url}/detection-events/{detection_event_id}/feedback",
                json={"accurate": accurate},
                headers={"Accept": "application/json"},
            )
            return resp.status_code == 200
        except httpx.HTTPError:
            return False

    async def heartbeat(self, bin_id: int, fill_level: int, ip_address: str, compartments: dict) -> bool:
        """Send periodic heartbeat to backend."""
        try:
            resp = await self._client.post(
                f"{self.base_url}/bins/{bin_id}/heartbeat",
                json={
                    "fill_level": fill_level,
                    "compartments": compartments,
                    "ip_address": ip_address,
                },
                headers={"Accept": "application/json"},
            )
            return resp.status_code == 200
        except httpx.HTTPError:
            return False

    async def health_check(self) -> bool:
        """Check if backend is reachable."""
        try:
            resp = await self._client.get(f"{self.base_url}/public/stats")
            return resp.status_code == 200
        except httpx.HTTPError:
            return False

    async def close(self) -> None:
        await self._client.aclose()
