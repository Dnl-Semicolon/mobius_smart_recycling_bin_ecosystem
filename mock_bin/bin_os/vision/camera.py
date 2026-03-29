"""Camera abstraction — captures frames via OpenCV. Works on Mac and Pi."""

from __future__ import annotations

import asyncio

import cv2
import numpy as np


class Camera:
    def __init__(self, camera_index: int = 0):
        self._index = camera_index
        self._cap: cv2.VideoCapture | None = None
        self._lock = asyncio.Lock()

    def open(self) -> bool:
        if self._index < 0:
            return False
        self._cap = cv2.VideoCapture(self._index)
        if not self._cap.isOpened():
            self._cap = None
            return False
        # Set reasonable resolution
        self._cap.set(cv2.CAP_PROP_FRAME_WIDTH, 640)
        self._cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 480)
        return True

    def is_open(self) -> bool:
        return self._cap is not None and self._cap.isOpened()

    async def capture_frame(self) -> np.ndarray | None:
        """Capture a single frame. Returns BGR numpy array or None."""
        if not self.is_open():
            return None
        async with self._lock:
            ret, frame = await asyncio.to_thread(self._cap.read)
            return frame if ret else None

    def capture_frame_sync(self) -> np.ndarray | None:
        """Synchronous frame capture for MJPEG streaming."""
        if not self.is_open():
            return None
        ret, frame = self._cap.read()
        return frame if ret else None

    def close(self) -> None:
        if self._cap is not None:
            self._cap.release()
            self._cap = None
