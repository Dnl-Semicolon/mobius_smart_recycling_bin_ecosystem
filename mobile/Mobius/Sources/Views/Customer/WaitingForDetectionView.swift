import SwiftUI

struct WaitingForDetectionView: View {
    let binSession: BinSession
    @Environment(CustomerService.self) private var customerService
    @Environment(\.dismiss) private var dismiss

    @State private var detectedItem: RecyclingHistoryItem?
    @State private var isPolling = true
    @State private var timedOut = false
    @State private var secondsLeft: Int
    @State private var pollStartTime = Date()

    init(binSession: BinSession) {
        self.binSession = binSession
        _secondsLeft = State(initialValue: binSession.sessionExpiresIn)
    }

    var body: some View {
        VStack(spacing: 0) {
            if let item = detectedItem {
                detectionResultView(item)
            } else if timedOut {
                timeoutView
            } else {
                waitingView
            }
        }
        .navigationTitle("Bin Session")
        .navigationBarTitleDisplayMode(.inline)
        .navigationBarBackButtonHidden(isPolling)
        .toolbar {
            if !isPolling {
                ToolbarItem(placement: .topBarTrailing) {
                    Button("Done") {
                        Task { await customerService.fetchStats() }
                        dismiss()
                    }
                }
            }
        }
        .task {
            await startPolling()
        }
        .task {
            await countdownTimer()
        }
    }

    // MARK: - Waiting State

    private var waitingView: some View {
        VStack(spacing: 32) {
            Spacer()

            ZStack {
                Circle()
                    .fill(.green.opacity(0.1))
                    .frame(width: 120, height: 120)

                Image(systemName: "arrow.down.circle")
                    .font(.system(size: 56))
                    .foregroundStyle(.green)
                    .symbolEffect(.pulse, isActive: isPolling)
            }

            VStack(spacing: 8) {
                Text("Place your item in the bin")
                    .font(.title3.weight(.semibold))

                Text(binSession.outletName ?? binSession.binSerial)
                    .font(.subheadline)
                    .foregroundStyle(.secondary)
            }

            Text("\(secondsLeft)s remaining")
                .font(.caption.monospaced())
                .foregroundStyle(.secondary)
                .padding(.horizontal, 16)
                .padding(.vertical, 8)
                .background(.quaternary.opacity(0.5))
                .clipShape(Capsule())

            Spacer()
        }
    }

    // MARK: - Detection Result

    private func detectionResultView(_ item: RecyclingHistoryItem) -> some View {
        VStack(spacing: 24) {
            Spacer()

            // Waste type icon
            ZStack {
                Circle()
                    .fill((item.wasteType?.iconColor ?? .gray).opacity(0.15))
                    .frame(width: 100, height: 100)

                Image(systemName: item.wasteType?.icon ?? "questionmark.circle")
                    .font(.system(size: 44))
                    .foregroundStyle(item.wasteType?.iconColor ?? .gray)
            }

            // Waste type label
            Text(item.wasteTypeLabel ?? item.wasteType?.label ?? "Unknown")
                .font(.title2.weight(.bold))

            // Brand badge
            if let brand = item.detectedBrandName {
                HStack(spacing: 6) {
                    Image(systemName: brandMatchIcon(item.brandMatch))
                        .foregroundStyle(brandMatchColor(item.brandMatch))
                    Text(brand)
                        .font(.subheadline.weight(.medium))
                    if let match = item.brandMatch {
                        Text(match == "match" ? "Brand Match" : "Competitor")
                            .font(.caption)
                            .foregroundStyle(brandMatchColor(item.brandMatch))
                    }
                }
                .padding(.horizontal, 16)
                .padding(.vertical, 8)
                .background(.quaternary.opacity(0.5))
                .clipShape(Capsule())
            }

            // Points earned
            VStack(spacing: 4) {
                Text("+\(item.pointsEarned)")
                    .font(.system(size: 48, weight: .bold, design: .rounded))
                    .foregroundStyle(.green)
                Text("points earned")
                    .font(.subheadline)
                    .foregroundStyle(.secondary)
            }
            .padding(.top, 8)

            // Confidence
            if let confidence = item.confidence {
                Text("Confidence: \(confidence)%")
                    .font(.caption.monospaced())
                    .foregroundStyle(.secondary)
            }

            Spacer()

            Button {
                Task { await customerService.fetchStats() }
                dismiss()
            } label: {
                Text("Done")
                    .fontWeight(.semibold)
                    .frame(maxWidth: .infinity)
            }
            .buttonStyle(.borderedProminent)
            .tint(.green)
            .controlSize(.large)
            .padding(.horizontal, 32)
            .padding(.bottom, 32)
        }
    }

    // MARK: - Timeout State

    private var timeoutView: some View {
        VStack(spacing: 24) {
            Spacer()

            Image(systemName: "clock.badge.xmark")
                .font(.system(size: 56))
                .foregroundStyle(.secondary)

            Text("No detection received")
                .font(.title3.weight(.semibold))

            Text("The session timed out. Make sure the bin is powered on and try again.")
                .font(.subheadline)
                .foregroundStyle(.secondary)
                .multilineTextAlignment(.center)
                .padding(.horizontal, 40)

            Spacer()

            Button {
                dismiss()
            } label: {
                Text("Go Back")
                    .fontWeight(.semibold)
                    .frame(maxWidth: .infinity)
            }
            .buttonStyle(.borderedProminent)
            .tint(.green)
            .controlSize(.large)
            .padding(.horizontal, 32)
            .padding(.bottom, 32)
        }
    }

    // MARK: - Helpers

    private func startPolling() async {
        pollStartTime = Date()
        let result = await customerService.pollForDetection(since: pollStartTime)

        await MainActor.run {
            withAnimation(.snappy) {
                isPolling = false
                if let result {
                    detectedItem = result
                    HapticManager.notification(.success)
                } else {
                    timedOut = true
                }
            }
        }
    }

    private func countdownTimer() async {
        while secondsLeft > 0 && isPolling {
            try? await Task.sleep(nanoseconds: 1_000_000_000)
            if isPolling {
                secondsLeft = max(0, secondsLeft - 1)
            }
        }
    }

    private func brandMatchIcon(_ match: String?) -> String {
        switch match {
        case "match": "checkmark.circle.fill"
        case "competitor": "xmark.circle.fill"
        default: "tag.fill"
        }
    }

    private func brandMatchColor(_ match: String?) -> Color {
        switch match {
        case "match": .green
        case "competitor": .red
        default: .secondary
        }
    }
}
