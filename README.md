# Mobius Smart Recycling Bin Ecosystem

A smart recycling bin system for automated waste management and recycling optimization.

## Overview

This project aims to create an intelligent recycling ecosystem that can identify, sort, and manage recyclable materials efficiently. The system consists of a Laravel backend for API and admin management, and a Flutter mobile application for cross-platform user interaction.

## Tech Stack

### Backend (Laravel)
- **Framework**: Laravel 12
- **UI**: Livewire 3 with Flux UI components
- **Authentication**: Laravel Fortify (with 2FA support)
- **Testing**: Pest 4 (with browser testing capabilities)
- **Database**: SQLite (development), configurable for production
- **Development Tools**: Laravel Boost, Pint (code formatter)

### Mobile (Flutter)
- **Framework**: Flutter 3+
- **Platforms**: Android, iOS, Web, Linux, macOS, Windows
- **Language**: Dart

## Project Structure

```
mobius_smart_recycling_bin_ecosystem/
├── backend/     # Laravel API and admin interface
├── mobile/      # Flutter cross-platform application
├── ARCHITECTURE.md  # Detailed architecture and configuration guide
└── README.md    # This file
```

## Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & npm
- Flutter SDK 3+
- Git

### Backend Setup
See [backend/README.md](backend/README.md) for detailed Laravel setup instructions.

Quick start:
```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm install && npm run dev
```

### Mobile Setup
See [mobile/README.md](mobile/README.md) for detailed Flutter setup instructions.

Quick start:
```bash
cd mobile
flutter pub get
flutter run
```

## Documentation

- **Architecture Guide**: [ARCHITECTURE.md](ARCHITECTURE.md) - Comprehensive guide to project structure, directories, and configuration files
- **Backend README**: [backend/README.md](backend/README.md) - Laravel-specific documentation
- **Mobile README**: [mobile/README.md](mobile/README.md) - Flutter-specific documentation
- **Agent Guide**: [AGENT_GUIDE.md](AGENT_GUIDE.md) - Guidelines for AI agents working on this project

## Development

### Running Tests
```bash
# Backend tests
cd backend
php artisan test

# Mobile tests
cd mobile
flutter test
```

### Code Formatting
```bash
# Backend (Laravel Pint)
cd backend
vendor/bin/pint

# Mobile (Dart formatter)
cd mobile
flutter format .
```

## License

TBD
