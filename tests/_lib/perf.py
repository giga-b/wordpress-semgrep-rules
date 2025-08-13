#!/usr/bin/env python3
"""
Lightweight performance measurement utilities for test scripts.

Measures per-operation:
- wall_time_seconds
- process_rss_delta_bytes (current process)
- process_cpu_time_seconds (user+system)
- cpu_utilization_estimate (cpu_time / wall_time, 0..N cores)
"""

from __future__ import annotations

import time
import os
from dataclasses import dataclass
from typing import Dict

import psutil


@dataclass
class PerfTracker:
    start_wall: float
    start_rss: int
    start_cpu_time: float


def _proc() -> psutil.Process:
    try:
        return psutil.Process(os.getpid())
    except Exception:
        return psutil.Process()


def start_perf() -> PerfTracker:
    proc = _proc()
    rss = 0
    cpu_time = 0.0
    try:
        rss = proc.memory_info().rss
        c = proc.cpu_times()
        cpu_time = float(getattr(c, 'user', 0.0) + getattr(c, 'system', 0.0))
    except Exception:
        pass
    return PerfTracker(start_wall=time.time(), start_rss=rss, start_cpu_time=cpu_time)


def stop_perf(tracker: PerfTracker) -> Dict:
    end_wall = time.time()
    wall_time = max(0.0, end_wall - tracker.start_wall)

    proc = _proc()
    end_rss = 0
    end_cpu = 0.0
    try:
        end_rss = proc.memory_info().rss
        c = proc.cpu_times()
        end_cpu = float(getattr(c, 'user', 0.0) + getattr(c, 'system', 0.0))
    except Exception:
        pass

    rss_delta = max(0, end_rss - tracker.start_rss)
    cpu_time = max(0.0, end_cpu - tracker.start_cpu_time)
    cpu_util = (cpu_time / wall_time) if wall_time > 0 else 0.0

    return {
        'wall_time_seconds': wall_time,
        'process_rss_delta_bytes': rss_delta,
        'process_cpu_time_seconds': cpu_time,
        'cpu_utilization_estimate': cpu_util
    }


