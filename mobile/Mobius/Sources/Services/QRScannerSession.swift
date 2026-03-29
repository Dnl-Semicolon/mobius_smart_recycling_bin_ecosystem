@preconcurrency import AVFoundation
import UIKit

/// Manages AVCaptureSession on a dedicated serial queue to avoid
/// Swift 6 strict concurrency issues. All AVFoundation work stays
/// off the MainActor. Scanned QR codes are delivered via a callback
/// dispatched to the main queue.
final class QRScannerSession: NSObject, @unchecked Sendable, AVCaptureMetadataOutputObjectsDelegate {
    private let session = AVCaptureSession()
    private let sessionQueue = DispatchQueue(label: "com.mobiusvision.qrscanner")
    private let onCodeScanned: @Sendable (String) -> Void

    /// Mutable state — only accessed on sessionQueue.
    private var isRunning = false
    private var isConfigured = false

    init(onCodeScanned: @escaping @Sendable (String) -> Void) {
        self.onCodeScanned = onCodeScanned
        super.init()
    }

    /// The underlying capture session — used by the preview layer only.
    var captureSession: AVCaptureSession { session }

    // MARK: - Lifecycle

    func start() {
        sessionQueue.async { [weak self] in
            guard let self, !self.isRunning else { return }
            self.configureIfNeeded()
            self.session.startRunning()
            self.isRunning = true
        }
    }

    func stop() {
        sessionQueue.async { [weak self] in
            guard let self, self.isRunning else { return }
            self.session.stopRunning()
            self.isRunning = false
        }
    }

    // MARK: - Configuration

    private func configureIfNeeded() {
        guard !isConfigured else { return }
        isConfigured = true

        session.beginConfiguration()
        defer { session.commitConfiguration() }

        guard let device = AVCaptureDevice.default(for: .video),
              let input = try? AVCaptureDeviceInput(device: device),
              session.canAddInput(input) else { return }

        session.addInput(input)

        let metadataOutput = AVCaptureMetadataOutput()
        guard session.canAddOutput(metadataOutput) else { return }
        session.addOutput(metadataOutput)

        metadataOutput.setMetadataObjectsDelegate(self, queue: DispatchQueue.main)
        metadataOutput.metadataObjectTypes = [.qr]
    }

    // MARK: - Delegate

    func metadataOutput(
        _ output: AVCaptureMetadataOutput,
        didOutput metadataObjects: [AVMetadataObject],
        from connection: AVCaptureConnection
    ) {
        guard let object = metadataObjects.first as? AVMetadataMachineReadableCodeObject,
              let value = object.stringValue, !value.isEmpty else { return }

        onCodeScanned(value)
    }
}
