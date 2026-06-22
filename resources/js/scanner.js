import { Html5Qrcode } from 'html5-qrcode';
import { parseScannerPayload, shouldAcceptDecode } from './scannerPayload';

function initScanner() {
    const root = document.getElementById('scanner-app');
    if (!root || root.dataset.ready === '1') {
        return;
    }
    root.dataset.ready = '1';

    let endpoint = root.dataset.redeemEndpoint;
    const csrf = root.dataset.csrf;
    const panel = document.getElementById('scan-panel');
    const result = document.getElementById('scan-result');
    const tokenInput = document.getElementById('qr_token');
    const sessionInput = document.getElementById('meal_session_id');
    const modeInput = document.getElementById('scan_mode');
    const cameraSelect = document.getElementById('camera_id');
    const startButton = document.getElementById('camera-start');
    const stopButton = document.getElementById('camera-stop');
    const submitButton = document.getElementById('redeem-btn');
    const cameraMessage = document.getElementById('camera-message');
    const state = { pending: false, lastToken: null, lastAt: 0, windowMs: 2500 };
    let scanner = null;
    let running = false;

    async function submitToken(rawPayload) {
        const token = parseScannerPayload(rawPayload);
        if (!shouldAcceptDecode(state, token)) {
            if (!token) {
                showMessage('Unsupported QR payload.', false);
            }
            return;
        }

        state.pending = true;
        submitButton.disabled = true;
        result.textContent = 'Processing...';

        try {
            if (running) {
                await stopCamera();
            }

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    qr_token: token,
                    meal_session_id: sessionInput.value,
                    device_id: navigator.userAgent.slice(0, 80),
                    idempotency_key: crypto.randomUUID ? crypto.randomUUID() : String(Date.now()),
                }),
            });
            const data = await response.json();
            showMessage(formatResult(data), response.ok);
            if (navigator.vibrate) {
                navigator.vibrate(response.ok ? 80 : [80, 50, 80]);
            }
        } catch (error) {
            panel.className = 'p-4 text-dark bg-warning';
            result.textContent = 'Network error. Try again.';
        } finally {
            state.pending = false;
            submitButton.disabled = false;
        }
    }

    function showMessage(message, ok) {
        panel.className = 'p-4 text-white ' + (ok ? 'bg-success' : 'bg-danger');
        result.textContent = message;
    }

    function formatResult(data) {
        const lines = [
            data.message,
            data.participant?.name ? `Participant: ${data.participant.name}` : null,
            data.participant?.status ? `Status: ${data.participant.status}` : null,
            data.meal_session?.name ? `Session: ${data.meal_session.name}` : null,
            data.remaining_entitlement ? `Remaining: ${data.remaining_entitlement.remaining}/${data.remaining_entitlement.total}` : null,
            data.redemption_number ? `Redemption: ${data.redemption_number}` : null,
            data.rejection_code ? `Reason: ${data.rejection_code}` : null,
        ];

        return lines.filter(Boolean).join('\n');
    }

    async function loadCameras() {
        if (!Html5Qrcode) {
            cameraMessage.textContent = 'Camera scanning is not supported in this browser.';
            return;
        }

        try {
            const cameras = await Html5Qrcode.getCameras();
            cameraSelect.innerHTML = '';
            cameras.forEach((camera) => {
                const option = document.createElement('option');
                option.value = camera.id;
                option.textContent = camera.label || `Camera ${cameraSelect.length + 1}`;
                cameraSelect.appendChild(option);
            });
            cameraSelect.disabled = cameras.length === 0;
            startButton.disabled = cameras.length === 0;
            cameraMessage.textContent = cameras.length === 0 ? 'No camera found. Use manual token input.' : '';
        } catch (error) {
            cameraMessage.textContent = window.isSecureContext ? 'Camera permission was denied or unavailable.' : 'Camera requires HTTPS except on localhost.';
            startButton.disabled = true;
        }
    }

    async function startCamera() {
        if (running) {
            return;
        }

        scanner = scanner || new Html5Qrcode('camera-preview', false);
        await scanner.start(
            cameraSelect.value,
            { fps: 8, qrbox: { width: 240, height: 240 } },
            (decodedText) => submitToken(decodedText),
        );
        running = true;
        startButton.disabled = true;
        stopButton.disabled = false;
    }

    async function stopCamera() {
        if (scanner && running) {
            await scanner.stop();
            running = false;
        }
        startButton.disabled = false;
        stopButton.disabled = true;
    }

    submitButton.addEventListener('click', () => submitToken(tokenInput.value));
    startButton.addEventListener('click', () => startCamera().catch(() => {
        cameraMessage.textContent = 'Unable to start camera. Check permission and camera selection.';
    }));
    stopButton.addEventListener('click', () => stopCamera());
    modeInput.addEventListener('change', () => {
        endpoint = modeInput.value === 'validate' ? root.dataset.validateEndpoint : root.dataset.redeemEndpoint;
    });
    window.addEventListener('beforeunload', () => {
        if (scanner && running) {
            scanner.stop().catch(() => {});
        }
    });

    loadCameras();
}

window.HeadCounterScanner = {
    parseScannerPayload,
    shouldAcceptDecode,
    init: initScanner,
};

document.addEventListener('DOMContentLoaded', initScanner);
