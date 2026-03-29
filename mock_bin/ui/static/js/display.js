/* Mobius Bin Display — WebSocket client and UI controller */

const WASTE_LABELS = {
    paper_cup: "Paper Cup", plastic_cup: "Plastic Cup", lid: "Lid",
    straw: "Straw", napkin: "Napkin", liquid_waste: "Liquid Waste", unknown: "Unknown"
};

let ws = null;
let currentState = "booting";
let maintenanceOn = false;

// Upload + feedback state
let uploadedImageB64 = null;
let lastDetectionData = null;
let lastDetectionEventId = null;
let lastBrand = "";

// ── WebSocket ───────────────────────────────────

function connectWS() {
    const protocol = location.protocol === "https:" ? "wss:" : "ws:";
    ws = new WebSocket(`${protocol}//${location.host}/ws`);

    ws.onopen = () => log("Connected to bin");
    ws.onclose = () => {
        log("Disconnected — reconnecting...");
        setTimeout(connectWS, 2000);
    };
    ws.onerror = () => {};

    ws.onmessage = (event) => {
        const msg = JSON.parse(event.data);
        handleEvent(msg.event, msg.data);
    };
}

function sendCommand(action, extra = {}) {
    if (ws && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({ action, ...extra }));
    }
}

// ── Event handlers ──────────────────────────────

function handleEvent(event, data) {
    switch (event) {
        case "init":
            updateFullState(data);
            break;
        case "state_change":
            showState(data.new);
            if (data.status) updateGauges(data.status);
            break;
        case "drain_start":
            animateDrain(data.liquid_ml, data.duration_sec);
            break;
        case "detection_complete":
            lastDetectionData = data;
            lastBrand = data.brand || "";
            showResult(data);
            updateGauges(data);
            break;
        case "detection_meta":
            lastDetectionEventId = data.detection_event_id || null;
            if (data.brand) lastBrand = data.brand;
            break;
        case "uploaded_image":
            uploadedImageB64 = "data:image/jpeg;base64," + data.image_base64;
            break;
        case "upload_error":
            log("Upload error: " + data.error);
            break;
        case "detection_rejected":
            log("Rejected: " + data.waste_type + " @ " + data.confidence + "%");
            break;
        case "reset":
            if (data.status) updateGauges(data.status);
            else updateGauges(data);
            log("Compartments reset");
            break;
        case "heartbeat":
            log("Heartbeat: " + (data.success ? "OK" : "FAILED"));
            break;
    }
}

function updateFullState(status) {
    showState(status.state);
    updateGauges(status);
    document.getElementById("itemsToday").textContent = status.total_items_today + " items today";
}

// ── State display ───────────────────────────────

const STATE_PANELS = [
    "stateIdle", "stateDetecting", "stateDraining",
    "stateResult", "stateFeedback", "stateMaintenance", "stateBooting"
];

function showState(state) {
    currentState = state;
    STATE_PANELS.forEach(id => {
        document.getElementById(id).classList.add("hidden");
    });

    const dot = document.getElementById("statusDot");
    dot.className = "w-3 h-3 rounded-full";

    switch (state) {
        case "idle":
            document.getElementById("stateIdle").classList.remove("hidden");
            dot.classList.add("bg-green-500", "animate-pulse");
            // Clear upload state
            uploadedImageB64 = null;
            lastDetectionData = null;
            lastDetectionEventId = null;
            lastBrand = "";
            const preview = document.getElementById("uploadedPreview");
            preview.classList.add("hidden");
            preview.src = "";
            const feed = document.getElementById("cameraFeed");
            feed.classList.remove("hidden");
            break;
        case "detecting":
            document.getElementById("stateDetecting").classList.remove("hidden");
            dot.classList.add("bg-amber-400", "animate-pulse");
            // Show uploaded image during detection if available
            if (uploadedImageB64) {
                const dp = document.getElementById("detectingPreview");
                dp.src = uploadedImageB64;
                dp.classList.remove("hidden");
            } else {
                document.getElementById("detectingPreview").classList.add("hidden");
            }
            break;
        case "draining":
            document.getElementById("stateDraining").classList.remove("hidden");
            dot.classList.add("bg-amber-400", "animate-pulse");
            break;
        case "result":
            document.getElementById("stateResult").classList.remove("hidden");
            dot.classList.add("bg-green-500");
            // Show uploaded image in result
            if (uploadedImageB64) {
                const rp = document.getElementById("resultPreview");
                rp.src = uploadedImageB64;
                rp.classList.remove("hidden");
            } else {
                document.getElementById("resultPreview").classList.add("hidden");
            }
            break;
        case "feedback":
            document.getElementById("stateFeedback").classList.remove("hidden");
            dot.classList.add("bg-green-500");
            break;
        case "maintenance":
            document.getElementById("stateMaintenance").classList.remove("hidden");
            dot.classList.add("bg-red-500");
            maintenanceOn = true;
            break;
        case "booting":
            document.getElementById("stateBooting").classList.remove("hidden");
            dot.classList.add("bg-amber-400", "animate-pulse");
            break;
    }
}

// ── Drain animation ─────────────────────────────

function animateDrain(liquidMl, durationSec) {
    const bar = document.getElementById("drainProgress");
    const amount = document.getElementById("drainAmount");
    amount.textContent = "~" + liquidMl + "mL remaining drink";
    bar.style.width = "0%";

    const start = Date.now();
    const duration = durationSec * 1000;

    function tick() {
        const elapsed = Date.now() - start;
        const pct = Math.min(100, (elapsed / duration) * 100);
        bar.style.width = pct + "%";
        if (elapsed < duration) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
}

// ── Result display ──────────────────────────────

function showResult(data) {
    const type = data.waste_type;
    document.getElementById("resultType").textContent = WASTE_LABELS[type] || type;
    document.getElementById("resultConfidence").textContent = "Confidence: " + data.confidence + "%";

    const weightEl = document.getElementById("resultWeight");
    if (data.weight_g) {
        weightEl.textContent = data.weight_g + "g";
    } else {
        weightEl.textContent = "";
    }

    const liquidEl = document.getElementById("resultLiquid");
    if (data.liquid_drained_ml > 0) {
        liquidEl.textContent = data.liquid_drained_ml + "mL liquid drained";
    } else {
        liquidEl.textContent = "";
    }
}

// ── Feedback flow ───────────────────────────────

function advanceToFeedback() {
    sendCommand("advance_to_feedback");
    // Populate feedback screen
    if (lastDetectionData) {
        const type = lastDetectionData.waste_type;
        document.getElementById("feedbackType").textContent = WASTE_LABELS[type] || type;
        document.getElementById("feedbackBrand").textContent = lastBrand || "Brand not detected";
        document.getElementById("feedbackCompartment").textContent =
            "Sorted to: " + (type === "liquid_waste" ? "Liquid" : "Solids");
    }
}

function submitFeedback(accurate) {
    sendCommand("submit_feedback", { accurate });
    log("Feedback: " + (accurate ? "Accurate" : "Inaccurate"));
}

// ── Image upload (tap camera to upload) ─────────

function setupUploadBackdoor() {
    const container = document.getElementById("cameraContainer");
    const fileInput = document.getElementById("imageUpload");

    container.addEventListener("click", () => {
        if (currentState === "idle") {
            fileInput.click();
        }
    });

    // Keyboard accessibility
    container.addEventListener("keydown", (e) => {
        if ((e.key === "Enter" || e.key === " ") && currentState === "idle") {
            e.preventDefault();
            fileInput.click();
        }
    });

    fileInput.addEventListener("change", (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (event) => {
            const dataUrl = event.target.result;
            const b64 = dataUrl.split(",")[1];

            // Resize before sending (cap at 640x480 for WebSocket)
            resizeImage(dataUrl, 640, 480, (resizedB64) => {
                uploadedImageB64 = "data:image/jpeg;base64," + resizedB64;

                // Show uploaded image in camera area
                const preview = document.getElementById("uploadedPreview");
                preview.src = uploadedImageB64;
                preview.classList.remove("hidden");
                document.getElementById("cameraFeed").classList.add("hidden");

                // Send to server
                sendCommand("upload_and_detect", { image_base64: resizedB64 });
                log("Uploaded image for detection");
            });
        };
        reader.readAsDataURL(file);
        e.target.value = ""; // Reset so same file can be re-selected
    });
}

function resizeImage(dataUrl, maxW, maxH, callback) {
    const img = new Image();
    img.onload = () => {
        let w = img.width;
        let h = img.height;
        if (w > maxW || h > maxH) {
            const ratio = Math.min(maxW / w, maxH / h);
            w = Math.round(w * ratio);
            h = Math.round(h * ratio);
        }
        const canvas = document.createElement("canvas");
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0, w, h);
        const result = canvas.toDataURL("image/jpeg", 0.85);
        callback(result.split(",")[1]);
    };
    img.src = dataUrl;
}

// ── Gauge updates ───────────────────────────────

function updateGauges(data) {
    const compartments = data.compartments;
    if (!compartments) return;

    setGauge("Solids", compartments.solids?.fill_percent || 0);
    setGauge("Liquid", compartments.liquid?.fill_percent || 0);

    const overall = data.fill_level ?? Math.max(
        compartments.solids?.fill_percent || 0,
        compartments.liquid?.fill_percent || 0
    );
    setGauge("Overall", overall);

    const weightEl = document.getElementById("totalWeight");
    if (weightEl && data.total_weight_g !== undefined) {
        weightEl.textContent = data.total_weight_g >= 1000
            ? (data.total_weight_g / 1000).toFixed(1) + "kg"
            : data.total_weight_g + "g";
    }

    const heightEl = document.getElementById("fillHeight");
    if (heightEl && data.fill_height_cm !== undefined) {
        heightEl.textContent = data.fill_height_cm + "cm";
    }

    const heightBar = document.getElementById("fillHeightBar");
    if (heightBar && data.fill_height_cm !== undefined) {
        const pct = Math.min(100, (data.fill_height_cm / 30) * 100);
        heightBar.style.height = pct + "%";
        heightBar.className = "absolute bottom-0 w-full transition-all duration-500 rounded-sm";
        if (pct >= 80) heightBar.classList.add("bg-red-500");
        else if (pct >= 60) heightBar.classList.add("bg-amber-400");
        else heightBar.classList.add("bg-green-500");
    }

    if (data.total_items_today !== undefined) {
        document.getElementById("itemsToday").textContent = data.total_items_today + " items today";
    }
}

function setGauge(name, percent) {
    const fill = document.getElementById("fill" + name);
    const value = document.getElementById("value" + name);
    if (!fill || !value) return;

    fill.style.width = percent + "%";
    value.textContent = percent + "%";
}

// ── Dev controls ────────────────────────────────

function toggleDevPanel() {
    document.getElementById("devBody").classList.toggle("hidden");
}

function simulateDeposit() {
    const wasteType = document.getElementById("devWasteType").value;
    const confidence = parseInt(document.getElementById("devConfidence").value) || 85;
    sendCommand("simulate_deposit", { waste_type: wasteType, confidence });
    log("Simulating: " + wasteType + " @ " + confidence + "%");
}

function captureAndDetect() {
    sendCommand("capture_and_detect");
    log("Capturing and detecting...");
}

function resetFill() {
    sendCommand("reset_fill");
    log("Resetting fill levels...");
}

function toggleMaintenance() {
    if (maintenanceOn) {
        sendCommand("maintenance_off");
        maintenanceOn = false;
        log("Maintenance mode OFF");
    } else {
        sendCommand("maintenance_on");
        maintenanceOn = true;
        log("Maintenance mode ON");
    }
}

function log(msg) {
    const el = document.getElementById("devLog");
    if (!el) return;
    const time = new Date().toLocaleTimeString();
    el.innerHTML += "<div>[" + time + "] " + msg + "</div>";
    el.scrollTop = el.scrollHeight;
}

// ── Init ────────────────────────────────────────

document.addEventListener("DOMContentLoaded", () => {
    connectWS();
    setupUploadBackdoor();
});
