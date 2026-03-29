import SwiftUI
import WebKit

struct WalkthroughView: View {
    var body: some View {
        WebView(url: URL(string: "http://172.20.10.3:8000/dev/walkthrough")!)
            .ignoresSafeArea()
    }
}

struct WebView: UIViewRepresentable {
    let url: URL

    func makeUIView(context: Context) -> WKWebView {
        let config = WKWebViewConfiguration()
        let webView = WKWebView(frame: .zero, configuration: config)
        webView.isOpaque = false
        webView.backgroundColor = .systemBackground
        webView.scrollView.contentInsetAdjustmentBehavior = .always
        webView.load(URLRequest(url: url))
        return webView
    }

    func updateUIView(_ webView: WKWebView, context: Context) {}
}

#Preview {
    WalkthroughView()
}
