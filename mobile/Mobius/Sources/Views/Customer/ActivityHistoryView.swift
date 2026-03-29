import SwiftUI

struct ActivityHistoryView: View {
    @Environment(CustomerService.self) private var customerService

    var body: some View {
        List {
            if customerService.isLoadingHistory && customerService.history.isEmpty {
                Section {
                    HStack {
                        Spacer()
                        ProgressView("Loading history...")
                        Spacer()
                    }
                }
            } else if customerService.history.isEmpty {
                Section {
                    ContentUnavailableView(
                        "No Activity Yet",
                        systemImage: "leaf.fill",
                        description: Text("Scan a bin QR code and start recycling to see your activity here.")
                    )
                    .listRowBackground(Color.clear)
                }
            } else {
                ForEach(customerService.groupedHistory, id: \.date) { group in
                    Section(group.date) {
                        ForEach(group.items) { item in
                            activityRow(item)
                        }
                    }
                }

                // Pagination trigger
                if customerService.hasMoreHistory {
                    Section {
                        HStack {
                            Spacer()
                            ProgressView()
                                .task {
                                    await customerService.loadMoreHistory()
                                }
                            Spacer()
                        }
                    }
                }
            }
        }
        .refreshable {
            await customerService.fetchHistory(refresh: true)
        }
        .navigationTitle("Activity History")
        .task {
            if customerService.history.isEmpty {
                await customerService.fetchHistory(refresh: true)
            }
        }
    }

    // MARK: - Activity Row

    private func activityRow(_ item: RecyclingHistoryItem) -> some View {
        HStack(spacing: 14) {
            if let wasteType = item.wasteType {
                Image(systemName: wasteType.icon)
                    .font(.system(size: 16, weight: .medium))
                    .foregroundStyle(.white)
                    .frame(width: 32, height: 32)
                    .background(wasteType.iconColor, in: RoundedRectangle(cornerRadius: 7))
            } else {
                Image(systemName: "leaf.fill")
                    .font(.system(size: 16, weight: .medium))
                    .foregroundStyle(.white)
                    .frame(width: 32, height: 32)
                    .background(.gray, in: RoundedRectangle(cornerRadius: 7))
            }

            VStack(alignment: .leading, spacing: 2) {
                HStack(spacing: 5) {
                    Text(item.wasteTypeLabel ?? "Unknown")
                        .font(.body)
                    if let brandName = item.detectedBrandName {
                        Text(brandName)
                            .font(.caption2.weight(.medium))
                            .padding(.horizontal, 5)
                            .padding(.vertical, 1)
                            .background(
                                item.brandMatch == "match" ? Color.green.opacity(0.15) : Color.red.opacity(0.15),
                                in: Capsule()
                            )
                            .foregroundStyle(item.brandMatch == "match" ? .green : .red)
                    }
                }
                Text(item.binSerial ?? "Unknown bin")
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }

            Spacer()

            VStack(alignment: .trailing, spacing: 4) {
                Text("+\(item.pointsEarned) pts")
                    .font(.subheadline.bold())
                    .foregroundStyle(.green)
                Text(item.timeAgo)
                    .font(.caption2)
                    .foregroundStyle(.tertiary)

                // Feedback buttons
                feedbackButtons(for: item)
            }
        }
    }

    // MARK: - Feedback Buttons

    @ViewBuilder
    private func feedbackButtons(for item: RecyclingHistoryItem) -> some View {
        if customerService.feedbackSent.contains(item.id) {
            Image(systemName: "checkmark.circle.fill")
                .font(.caption)
                .foregroundStyle(.green)
        } else {
            HStack(spacing: 12) {
                Button {
                    HapticManager.notification(.success)
                    Task { await customerService.submitFeedback(detectionId: item.id, accurate: true) }
                } label: {
                    Image(systemName: "hand.thumbsup")
                        .font(.body)
                        .foregroundStyle(.green)
                        .frame(width: 44, height: 44)
                        .contentShape(Rectangle())
                }
                .buttonStyle(.plain)

                Button {
                    HapticManager.notification(.warning)
                    Task { await customerService.submitFeedback(detectionId: item.id, accurate: false) }
                } label: {
                    Image(systemName: "hand.thumbsdown")
                        .font(.body)
                        .foregroundStyle(.red)
                        .frame(width: 44, height: 44)
                        .contentShape(Rectangle())
                }
                .buttonStyle(.plain)
            }
        }
    }
}

#Preview {
    NavigationStack {
        ActivityHistoryView()
    }
    .environment(CustomerService.mockWithData())
}
