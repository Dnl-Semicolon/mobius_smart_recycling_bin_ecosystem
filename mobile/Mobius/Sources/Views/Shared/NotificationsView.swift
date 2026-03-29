import SwiftUI

struct NotificationsView: View {
    @Environment(NotificationManager.self) private var notificationManager

    var body: some View {
        Group {
            if notificationManager.notifications.isEmpty && !notificationManager.isLoading {
                emptyState
            } else {
                notificationList
            }
        }
        .navigationTitle("Notifications")
        .navigationBarTitleDisplayMode(.inline)
        .toolbar {
            ToolbarItem(placement: .topBarTrailing) {
                if notificationManager.unreadCount > 0 {
                    Button("Read All") {
                        HapticManager.impact(.light)
                        Task { await notificationManager.markAllAsRead() }
                    }
                    .font(.subheadline)
                }
            }
        }
        .task {
            await notificationManager.fetchNotifications(refresh: true)
        }
        .refreshable {
            await notificationManager.fetchNotifications(refresh: true)
        }
    }

    // MARK: - List

    private var notificationList: some View {
        List {
            ForEach(groupedSections, id: \.label) { section in
                Section(section.label) {
                    ForEach(section.items) { notification in
                        notificationRow(notification)
                            .onTapGesture {
                                HapticManager.selection()
                                Task { await notificationManager.markAsRead(notification) }
                            }
                            .onAppear {
                                if notification.id == notificationManager.notifications.last?.id {
                                    Task { await notificationManager.loadMore() }
                                }
                            }
                    }
                }
            }

            if notificationManager.isLoading {
                HStack {
                    Spacer()
                    ProgressView()
                    Spacer()
                }
                .listRowBackground(Color.clear)
            }
        }
    }

    // MARK: - Empty State

    private var emptyState: some View {
        VStack(spacing: 12) {
            Image(systemName: "bell.slash")
                .font(.system(size: 40))
                .foregroundStyle(.secondary)
            Text("No Notifications")
                .font(.headline)
            Text("You're all caught up!")
                .font(.subheadline)
                .foregroundStyle(.secondary)
        }
        .frame(maxWidth: .infinity, maxHeight: .infinity)
    }

    // MARK: - Row

    private func notificationRow(_ notification: AppNotification) -> some View {
        HStack(alignment: .top, spacing: 14) {
            Image(systemName: notification.type.icon)
                .font(.system(size: 16, weight: .medium))
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(notification.type.iconColor, in: RoundedRectangle(cornerRadius: 7))

            VStack(alignment: .leading, spacing: 4) {
                HStack {
                    Text(notification.title)
                        .font(.subheadline)
                        .fontWeight(notification.isRead ? .regular : .bold)
                    Spacer()
                    Text(notification.createdAt, style: .relative)
                        .font(.caption)
                        .foregroundStyle(.secondary)
                }
                Text(notification.body)
                    .font(.caption)
                    .foregroundStyle(.secondary)
                    .lineLimit(2)
            }

            if !notification.isRead {
                Circle()
                    .fill(.blue)
                    .frame(width: 8, height: 8)
            }
        }
        .contentShape(Rectangle())
    }

    // MARK: - Grouping

    private struct GroupedSection {
        let label: String
        let items: [AppNotification]
    }

    private var groupedSections: [GroupedSection] {
        let calendar = Calendar.current
        var today: [AppNotification] = []
        var yesterday: [AppNotification] = []
        var earlier: [AppNotification] = []

        for n in notificationManager.notifications {
            if calendar.isDateInToday(n.createdAt) {
                today.append(n)
            } else if calendar.isDateInYesterday(n.createdAt) {
                yesterday.append(n)
            } else {
                earlier.append(n)
            }
        }

        var sections: [GroupedSection] = []
        if !today.isEmpty { sections.append(GroupedSection(label: "Today", items: today)) }
        if !yesterday.isEmpty { sections.append(GroupedSection(label: "Yesterday", items: yesterday)) }
        if !earlier.isEmpty { sections.append(GroupedSection(label: "Earlier", items: earlier)) }
        return sections
    }
}

#Preview {
    NavigationStack {
        NotificationsView()
    }
    .environment(NotificationManager.mockWithNotifications())
}
