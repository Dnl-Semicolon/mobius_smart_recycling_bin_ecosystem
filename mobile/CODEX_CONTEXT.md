# Codex Context (Mobile)

This file captures mobile-specific context for AI agent work.

## Stack
- Flutter app, Dart SDK ^3.9.2.
- Dependencies are minimal (`cupertino_icons`), with `flutter_lints` in dev.

## Structure / Entry Point
- Main entry: `lib/main.dart`.
- Platform folders: `android/`, `ios/`, `macos/`, `linux/`, `windows/`, `web/`.

## Conventions
- Keep API base URL configurable; coordinate API changes with backend.
- Follow repo boundary rules from `mobile/AGENT_GUIDE.md`.

## Current Behavior
- Material 3 app titled "Mobius" with a simple welcome screen.
