# Mobius - Mobile

Flutter cross-platform mobile application for the Mobius Smart Recycling Bin ecosystem.

## Overview

The Mobius mobile app provides users with an intuitive interface to interact with the smart recycling system, track waste management, and access recycling analytics.

## Platform Support

- Android
- iOS
- Web
- Linux
- macOS
- Windows

## Tech Stack

- **Framework**: Flutter 3+
- **Language**: Dart
- **Package Manager**: pub

## Setup

### Prerequisites
- Flutter SDK 3.0 or higher
- Dart SDK (comes with Flutter)
- Android Studio (for Android development)
- Xcode (for iOS development, macOS only)
- VS Code or Android Studio with Flutter plugin

### Installation

1. **Verify Flutter Installation**
   ```bash
   flutter doctor
   ```
   Ensure all required dependencies are installed.

2. **Install Dependencies**
   ```bash
   flutter pub get
   ```

3. **Run the App**
   ```bash
   # Run on connected device or emulator
   flutter run

   # Run on specific platform
   flutter run -d chrome      # Web
   flutter run -d macos       # macOS
   flutter run -d android     # Android
   flutter run -d ios         # iOS
   ```

## Development

### Project Structure

```
mobile/
├── lib/
│   └── main.dart           # Application entry point
├── android/                # Android-specific configuration
├── ios/                    # iOS-specific configuration
├── web/                    # Web-specific configuration
├── linux/                  # Linux-specific configuration
├── macos/                  # macOS-specific configuration
├── windows/                # Windows-specific configuration
├── test/                   # Test files
├── pubspec.yaml            # Dependencies and metadata
└── analysis_options.yaml   # Dart linter configuration
```

See [../ARCHITECTURE.md](../ARCHITECTURE.md) for detailed directory documentation.

### Running Tests

```bash
# Run all tests
flutter test

# Run tests with coverage
flutter test --coverage

# Run specific test file
flutter test test/widget_test.dart
```

### Code Formatting

```bash
# Format all Dart files
flutter format .

# Check formatting without changes
flutter format --dry-run .
```

### Linting

```bash
# Analyze code for issues
flutter analyze
```

## Building

### Android

```bash
# Debug APK
flutter build apk --debug

# Release APK
flutter build apk --release

# App Bundle (for Play Store)
flutter build appbundle --release
```

Debug APK location: `build/app/outputs/flutter-apk/app-debug.apk`
Release APK location: `build/app/outputs/flutter-apk/app-release.apk`

### iOS

```bash
# Build for iOS (requires macOS and Xcode)
flutter build ios --release

# Build without code signing (for testing)
flutter build ios --release --no-codesign
```

### Web

```bash
# Build for web
flutter build web --release
```

Output location: `build/web/`

### Desktop

```bash
# macOS
flutter build macos --release

# Linux
flutter build linux --release

# Windows
flutter build windows --release
```

## Configuration

### App Name
The app display name is configured in:
- **Android**: `android/app/src/main/AndroidManifest.xml` (android:label)
- **iOS**: `ios/Runner/Info.plist` (CFBundleDisplayName and CFBundleName)
- **pubspec.yaml**: Package name (mobius_mobile)

### App Icon
To generate app icons for all platforms:
1. Install flutter_launcher_icons:
   ```bash
   flutter pub add --dev flutter_launcher_icons
   ```
2. Configure in `pubspec.yaml`
3. Run:
   ```bash
   flutter pub run flutter_launcher_icons
   ```

### Package Name / Bundle ID
- **Android**: `com.mobiusvision.mobile` (in `android/app/build.gradle`)
- **iOS**: `com.mobiusvision.mobile` (in Xcode project settings)

## Dependencies

Key dependencies are listed in `pubspec.yaml`:

```yaml
dependencies:
  flutter:
    sdk: flutter
  cupertino_icons: ^1.0.8  # iOS-style icons

dev_dependencies:
  flutter_test:
    sdk: flutter
  flutter_lints: ^5.0.0    # Recommended linter rules
```

### Adding Packages

```bash
# Add a new package
flutter pub add package_name

# Add a dev dependency
flutter pub add --dev package_name

# Remove a package
flutter pub remove package_name

# Update all packages
flutter pub upgrade
```

## Debugging

### Flutter DevTools

```bash
# Start DevTools
flutter pub global activate devtools
flutter pub global run devtools
```

### Debug Mode Features
- Hot reload: Press `r` in terminal
- Hot restart: Press `R` in terminal
- Performance overlay: Press `P` in terminal
- Widget inspector: Press `i` in terminal

### Logging

```dart
import 'dart:developer' as developer;

developer.log('Message', name: 'my.app.category');
```

## API Integration

To connect to the Laravel backend:

1. Add HTTP package:
   ```bash
   flutter pub add http
   ```

2. Configure API base URL:
   ```dart
   const String apiBaseUrl = 'http://localhost:8000/api';
   ```

3. Make API calls:
   ```dart
   import 'package:http/http.dart' as http;

   final response = await http.get(
     Uri.parse('$apiBaseUrl/endpoint')
   );
   ```

## Deployment

### Android (Google Play Store)
1. Configure signing key in `android/app/build.gradle`
2. Build release APK or App Bundle
3. Upload to Google Play Console
4. See: [Flutter Android Deployment](https://docs.flutter.dev/deployment/android)

### iOS (App Store)
1. Configure signing in Xcode
2. Build release IPA
3. Upload to App Store Connect via Xcode or Transporter
4. See: [Flutter iOS Deployment](https://docs.flutter.dev/deployment/ios)

### Web
1. Build web release: `flutter build web --release`
2. Deploy `build/web/` directory to hosting provider
3. See: [Flutter Web Deployment](https://docs.flutter.dev/deployment/web)

## Troubleshooting

### Flutter Doctor Issues
```bash
flutter doctor -v
```
Follow the recommendations to fix any issues.

### Clear Build Cache
```bash
flutter clean
flutter pub get
```

### Android Build Issues
```bash
cd android
./gradlew clean
cd ..
flutter clean
flutter pub get
```

### iOS Build Issues
```bash
cd ios
rm -rf Pods Podfile.lock
pod install
cd ..
flutter clean
flutter pub get
```

## Resources

- [Flutter Documentation](https://docs.flutter.dev/)
- [Dart Documentation](https://dart.dev/guides)
- [Flutter Cookbook](https://docs.flutter.dev/cookbook)
- [Material Design Components](https://docs.flutter.dev/ui/widgets/material)
- [Cupertino (iOS) Components](https://docs.flutter.dev/ui/widgets/cupertino)
- [Flutter Packages](https://pub.dev/)
