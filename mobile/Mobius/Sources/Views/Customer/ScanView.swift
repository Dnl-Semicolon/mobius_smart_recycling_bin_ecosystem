import AVFoundation
import SwiftUI

struct ScanView: View {
    @Environment(CustomerService.self) private var customerService
    @State private var cameraStatus: AVAuthorizationStatus = AVCaptureDevice.authorizationStatus(for: .video)
    @State private var scannedCode: String?
    @State private var manualCode = ""
    @State private var showManualEntry = false
    @State private var scannerSession: QRScannerSession?
    @State private var binSession: BinSession?
    @State private var isStartingSession = false
    @State private var sessionError: String?

    var body: some View {
        Group {
            switch cameraStatus {
            case .authorized:
                cameraLiveView
            case .notDetermined:
                cameraPromptView
            case .denied, .restricted:
                cameraDeniedView
            @unknown default:
                cameraDeniedView
            }
        }
        .navigationTitle("Scan")
        .navigationBarTitleDisplayMode(.inline)
        .sheet(isPresented: $showManualEntry) {
            ManualCodeEntrySheet(code: $manualCode, onSubmit: { code in
                Task { await startBinSession(serial: code) }
            })
        }
        .onChange(of: scannedCode) { _, newCode in
            guard let code = newCode, binSession == nil, !isStartingSession else { return }
            Task { await startBinSession(serial: code) }
        }
    }

    // MARK: - Live Camera Feed

    private var cameraLiveView: some View {
        ZStack {
            // Camera preview
            if let session = scannerSession {
                CameraPreviewView(session: session.captureSession)
                    .ignoresSafeArea()
            }

            // Overlay
            VStack {
                Spacer()

                // Viewfinder frame
                RoundedRectangle(cornerRadius: 20)
                    .strokeBorder(.white.opacity(0.6), lineWidth: 3)
                    .frame(width: 250, height: 250)
                    .shadow(color: .black.opacity(0.3), radius: 10)

                Spacer()

                // Bottom controls
                VStack(spacing: 16) {
                    if let session = binSession {
                        sessionBanner(session)
                    } else if isStartingSession {
                        HStack(spacing: 10) {
                            ProgressView()
                                .tint(.white)
                            Text("Starting session...")
                                .font(.subheadline.weight(.medium))
                                .foregroundStyle(.white)
                        }
                        .padding(14)
                        .background(.ultraThinMaterial)
                        .clipShape(RoundedRectangle(cornerRadius: 14))
                        .padding(.horizontal, 20)
                    } else if let error = sessionError {
                        errorBanner(error)
                    } else if let code = scannedCode {
                        scannedCodeBanner(code)
                    } else {
                        Text("Point at a bin QR code")
                            .font(.subheadline.weight(.medium))
                            .foregroundStyle(.white)
                            .shadow(color: .black.opacity(0.5), radius: 4)
                    }

                    manualEntryButton
                        .foregroundStyle(.white.opacity(0.8))
                }
                .padding(.bottom, 40)
            }
        }
        .onAppear {
            startScanner()
        }
        .onDisappear {
            scannerSession?.stop()
        }
    }

    private func scannedCodeBanner(_ code: String) -> some View {
        HStack(spacing: 10) {
            Image(systemName: "qrcode")
                .font(.title3)
            VStack(alignment: .leading, spacing: 2) {
                Text("QR Code Found")
                    .font(.caption.weight(.semibold))
                Text(code)
                    .font(.subheadline.monospaced())
                    .lineLimit(1)
            }
            Spacer()
            Image(systemName: "chevron.right")
                .font(.caption.bold())
                .foregroundStyle(.white.opacity(0.6))
        }
        .padding(14)
        .background(.ultraThinMaterial)
        .clipShape(RoundedRectangle(cornerRadius: 14))
        .padding(.horizontal, 20)
        .transition(.move(edge: .bottom).combined(with: .opacity))
    }

    private func sessionBanner(_ session: BinSession) -> some View {
        HStack(spacing: 10) {
            Image(systemName: "checkmark.circle.fill")
                .font(.title3)
                .foregroundStyle(.green)
            VStack(alignment: .leading, spacing: 2) {
                Text("Session Active!")
                    .font(.caption.weight(.semibold))
                Text(session.outletName ?? session.binSerial)
                    .font(.subheadline)
                    .lineLimit(1)
                Text("Deposit your item within \(session.sessionExpiresIn)s")
                    .font(.caption2)
                    .foregroundStyle(.white.opacity(0.7))
            }
            Spacer()
        }
        .padding(14)
        .background(.ultraThinMaterial)
        .clipShape(RoundedRectangle(cornerRadius: 14))
        .padding(.horizontal, 20)
        .transition(.move(edge: .bottom).combined(with: .opacity))
    }

    private func errorBanner(_ message: String) -> some View {
        HStack(spacing: 10) {
            Image(systemName: "exclamationmark.triangle.fill")
                .font(.title3)
                .foregroundStyle(.red)
            VStack(alignment: .leading, spacing: 2) {
                Text("Scan Failed")
                    .font(.caption.weight(.semibold))
                Text(message)
                    .font(.caption)
                    .lineLimit(2)
            }
            Spacer()
            Button {
                HapticManager.impact(.medium)
                withAnimation(.snappy) {
                    sessionError = nil
                    scannedCode = nil
                }
            } label: {
                Text("Retry")
                    .font(.caption.bold())
            }
        }
        .padding(14)
        .background(.ultraThinMaterial)
        .clipShape(RoundedRectangle(cornerRadius: 14))
        .padding(.horizontal, 20)
        .transition(.move(edge: .bottom).combined(with: .opacity))
    }

    private func startBinSession(serial: String) async {
        isStartingSession = true
        sessionError = nil

        let result = await customerService.scanBin(serial: serial)

        withAnimation(.snappy) {
            if let session = result {
                HapticManager.notification(.success)
                binSession = session
                scannerSession?.stop()
            } else {
                HapticManager.notification(.error)
                sessionError = customerService.error ?? "Bin not found"
            }
            isStartingSession = false
        }
    }

    private func startScanner() {
        guard scannerSession == nil else {
            scannerSession?.start()
            return
        }
        let binding = $scannedCode
        let session = QRScannerSession { code in
            Task { @MainActor in
                withAnimation(.snappy) {
                    binding.wrappedValue = code
                }
                HapticManager.notification(.success)
            }
        }
        scannerSession = session
        session.start()
    }

    // MARK: - Permission Not Yet Requested

    private var cameraPromptView: some View {
        VStack(spacing: 24) {
            Spacer()

            Image(systemName: "camera.fill")
                .font(.system(size: 56))
                .foregroundStyle(.secondary)
                .padding(.bottom, 4)

            Text("Camera Access Needed")
                .font(.title3.bold())

            Text("Mobius uses your camera to scan QR codes on recycling bins.")
                .font(.subheadline)
                .foregroundStyle(.secondary)
                .multilineTextAlignment(.center)
                .padding(.horizontal, 40)

            Button {
                Task {
                    await AVCaptureDevice.requestAccess(for: .video)
                    cameraStatus = AVCaptureDevice.authorizationStatus(for: .video)
                }
            } label: {
                HStack {
                    Image(systemName: "camera.fill")
                    Text("Enable Camera")
                }
                .fontWeight(.medium)
                .frame(maxWidth: .infinity)
            }
            .buttonStyle(.borderedProminent)
            .tint(.green)
            .controlSize(.large)
            .padding(.horizontal, 32)

            manualEntryButton

            Spacer()
        }
    }

    // MARK: - Permission Denied

    private var cameraDeniedView: some View {
        VStack(spacing: 24) {
            Spacer()

            Image(systemName: "camera.badge.ellipsis")
                .font(.system(size: 56))
                .foregroundStyle(.secondary)
                .padding(.bottom, 4)

            Text("Camera Access Required")
                .font(.title3.bold())

            Text("Camera permission was denied. Enable it in Settings to scan bin QR codes.")
                .font(.subheadline)
                .foregroundStyle(.secondary)
                .multilineTextAlignment(.center)
                .padding(.horizontal, 40)

            Button {
                if let url = URL(string: UIApplication.openSettingsURLString) {
                    UIApplication.shared.open(url)
                }
            } label: {
                HStack {
                    Image(systemName: "gear")
                    Text("Open Settings")
                }
                .fontWeight(.medium)
                .frame(maxWidth: .infinity)
            }
            .buttonStyle(.borderedProminent)
            .tint(.green)
            .controlSize(.large)
            .padding(.horizontal, 32)

            manualEntryButton

            Spacer()
        }
        .onReceive(NotificationCenter.default.publisher(for: UIApplication.willEnterForegroundNotification)) { _ in
            cameraStatus = AVCaptureDevice.authorizationStatus(for: .video)
        }
    }

    // MARK: - Shared

    private var manualEntryButton: some View {
        Button {
            showManualEntry = true
        } label: {
            HStack {
                Image(systemName: "keyboard")
                Text("Enter code manually")
            }
            .font(.subheadline)
            .foregroundStyle(.secondary)
        }
    }
}

struct ManualCodeEntrySheet: View {
    @Binding var code: String
    var onSubmit: (String) -> Void
    @Environment(\.dismiss) private var dismiss

    var body: some View {
        NavigationStack {
            VStack(spacing: 24) {
                Text("Enter the code printed on the bin")
                    .font(.subheadline)
                    .foregroundStyle(.secondary)

                TextField("MBR-2026-001", text: $code)
                    .font(.title3.monospaced())
                    .multilineTextAlignment(.center)
                    .padding()
                    .background(.quaternary.opacity(0.5))
                    .clipShape(RoundedRectangle(cornerRadius: 12))
                    .padding(.horizontal)

                Button {
                    onSubmit(code)
                    dismiss()
                } label: {
                    Text("Submit")
                        .fontWeight(.semibold)
                        .frame(maxWidth: .infinity)
                }
                .buttonStyle(.borderedProminent)
                .tint(.green)
                .controlSize(.large)
                .padding(.horizontal)
                .disabled(code.isEmpty)

                Spacer()
            }
            .padding(.top)
            .navigationTitle("Enter Code")
            .navigationBarTitleDisplayMode(.inline)
            .toolbar {
                ToolbarItem(placement: .topBarTrailing) {
                    Button("Cancel") { dismiss() }
                }
            }
        }
        .presentationDetents([.medium])
    }
}

#Preview {
    NavigationStack {
        ScanView()
    }
    .environment(CustomerService.mockEmpty())
}
