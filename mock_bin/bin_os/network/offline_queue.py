"""Offline queue — buffers detections in SQLite when backend is down, syncs on reconnect."""

from __future__ import annotations

import json
import sqlite3
import time
from dataclasses import dataclass
from pathlib import Path


DB_PATH = Path(__file__).parent.parent.parent / "data" / "queue.db"


@dataclass
class QueuedDetection:
    id: int
    bin_id: int
    waste_type: str
    confidence: int
    image_bytes: bytes | None
    created_at: float


class OfflineQueue:
    def __init__(self, db_path: Path = DB_PATH):
        db_path.parent.mkdir(parents=True, exist_ok=True)
        self._conn = sqlite3.connect(str(db_path))
        self._conn.execute("""
            CREATE TABLE IF NOT EXISTS queued_detections (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                bin_id INTEGER NOT NULL,
                waste_type TEXT NOT NULL,
                confidence INTEGER NOT NULL,
                image_bytes BLOB,
                created_at REAL NOT NULL
            )
        """)
        self._conn.commit()

    def enqueue(self, bin_id: int, waste_type: str, confidence: int, image_bytes: bytes | None = None) -> None:
        self._conn.execute(
            "INSERT INTO queued_detections (bin_id, waste_type, confidence, image_bytes, created_at) VALUES (?, ?, ?, ?, ?)",
            (bin_id, waste_type, confidence, image_bytes, time.time()),
        )
        self._conn.commit()

    def pending(self) -> list[QueuedDetection]:
        rows = self._conn.execute(
            "SELECT id, bin_id, waste_type, confidence, image_bytes, created_at FROM queued_detections ORDER BY created_at"
        ).fetchall()
        return [QueuedDetection(*row) for row in rows]

    def remove(self, detection_id: int) -> None:
        self._conn.execute("DELETE FROM queued_detections WHERE id = ?", (detection_id,))
        self._conn.commit()

    def count(self) -> int:
        return self._conn.execute("SELECT COUNT(*) FROM queued_detections").fetchone()[0]

    def close(self) -> None:
        self._conn.close()
