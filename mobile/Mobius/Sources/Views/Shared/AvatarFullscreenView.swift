import SwiftUI

/// Fullscreen avatar viewer — shows the squared original image with dismiss.
struct AvatarFullscreenView: View {
    let avatarUrl: String?
    let name: String
    @Environment(\.dismiss) private var dismiss
    @State private var opacity: Double = 0

    var body: some View {
        ZStack {
            Color.black
                .ignoresSafeArea()
                .onTapGesture { dismiss() }

            VStack(spacing: 20) {
                Spacer()

                if let urlString = avatarUrl, let url = URL(string: urlString) {
                    AsyncImage(url: url) { phase in
                        switch phase {
                        case .success(let image):
                            image
                                .resizable()
                                .scaledToFit()
                                .clipShape(RoundedRectangle(cornerRadius: 12))
                                .shadow(color: .white.opacity(0.08), radius: 20)
                        case .failure:
                            Image(systemName: "photo")
                                .font(.largeTitle)
                                .foregroundStyle(.gray)
                        default:
                            ProgressView()
                                .tint(.white)
                        }
                    }
                    .frame(maxWidth: 320, maxHeight: 320)
                }

                Text(name)
                    .font(.headline)
                    .foregroundStyle(.white)

                Spacer()
            }
            .padding()
        }
        .opacity(opacity)
        .onAppear {
            withAnimation(.easeOut(duration: 0.2)) {
                opacity = 1
            }
        }
        .overlay(alignment: .topTrailing) {
            Button {
                dismiss()
            } label: {
                Image(systemName: "xmark")
                    .font(.system(size: 15, weight: .semibold))
                    .foregroundStyle(.white)
                    .frame(width: 32, height: 32)
                    .background(.white.opacity(0.2), in: Circle())
            }
            .padding()
        }
    }
}

#Preview {
    AvatarFullscreenView(avatarUrl: nil, name: "Daniel Tan")
}
