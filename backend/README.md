# Mobius - Backend

Laravel 12 backend for the Mobius Smart Recycling Bin ecosystem, providing API endpoints, admin interface, and business logic.

## Tech Stack

- **Laravel**: 12.x
- **PHP**: 8.2+
- **UI Framework**: Livewire 3 with Flux UI (free edition)
- **Authentication**: Laravel Fortify with 2FA support
- **Testing**: Pest 4 with browser testing
- **Database**: SQLite (development), MySQL/PostgreSQL (production)
- **Cache/Queue**: Database driver (development)
- **Development Tools**: Laravel Boost, Laravel Pint

## Features

### Authentication (Laravel Fortify)
- User registration
- Email verification
- Two-factor authentication (2FA) with QR codes
- Password reset
- Profile management

### UI Components (Livewire Flux)
- Pre-built UI components (buttons, forms, modals, etc.)
- Reactive components without page reloads
- Dark mode support

### Testing (Pest 4)
- Unit and feature tests
- Browser testing capabilities
- Test coverage for critical paths

## Setup

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` to configure your environment. Key settings:
- `APP_NAME` - Already set to "Mobius"
- `APP_URL` - Your application URL
- `DB_CONNECTION` - Database driver (sqlite, mysql, pgsql)
- `MAIL_MAILER` - Email driver (log for dev, smtp for production)

### 3. Database Setup
```bash
# Run migrations
php artisan migrate

# (Optional) Seed database with test data
php artisan db:seed
```

### 4. Start Development Server
```bash
# Option 1: Laravel Artisan
php artisan serve

# Option 2: With Vite asset bundling
composer run dev
# or
npm run dev & php artisan serve
```

Visit `http://localhost:8000` to view the application.

## Development

### Running Tests
```bash
# Run all tests
php artisan test

# Run with compact output
php artisan test --compact

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run with filter
php artisan test --filter=testName
```

### Code Formatting
```bash
# Format all files
vendor/bin/pint

# Check formatting without changes
vendor/bin/pint --test

# Format only changed files
vendor/bin/pint --dirty
```

### Artisan Commands
```bash
# List all available commands
php artisan list

# Create new Livewire component
php artisan make:livewire ComponentName

# Create new model with migration and factory
php artisan make:model ModelName -mf

# Create new test
php artisan make:test TestName
php artisan make:test TestName --unit

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Project Structure

```
backend/
├── app/
│   ├── Actions/          # Fortify authentication actions
│   ├── Http/Controllers/ # HTTP controllers
│   ├── Livewire/         # Livewire components
│   └── Models/           # Eloquent models
├── bootstrap/            # Framework bootstrap
├── config/               # Configuration files
├── database/
│   ├── factories/        # Model factories for testing
│   ├── migrations/       # Database migrations
│   └── seeders/          # Database seeders
├── public/               # Web root (index.php, assets)
├── resources/
│   ├── css/              # CSS source files
│   ├── js/               # JavaScript source files
│   └── views/            # Blade templates
├── routes/
│   ├── web.php           # Web routes
│   └── console.php       # Console commands
├── storage/              # Application storage (logs, uploads, cache)
├── tests/                # Pest test suite
└── vendor/               # Composer dependencies
```

See [../ARCHITECTURE.md](../ARCHITECTURE.md) for detailed directory and configuration documentation.

## Configuration Files

Key configuration files in `config/`:

- **app.php** - Application name, locale, timezone, environment
- **fortify.php** - Authentication features (registration, 2FA, password reset)
- **database.php** - Database connections and drivers
- **mail.php** - Email delivery configuration
- **cache.php** - Caching strategy
- **queue.php** - Background job processing
- **session.php** - Session handling

All config files support environment-based configuration via `.env` file.

## Livewire Components

Livewire components are located in:
- `app/Livewire/` - Component classes
- `resources/views/livewire/` - Component templates

### Creating Components
```bash
# Create new component
php artisan make:livewire Posts/CreatePost

# Creates:
# - app/Livewire/Posts/CreatePost.php
# - resources/views/livewire/posts/create-post.blade.php
```

### Testing Livewire
```php
use Livewire\Livewire;

it('creates a post', function () {
    Livewire::test(CreatePost::class)
        ->set('title', 'My Post')
        ->call('save')
        ->assertRedirect('/posts');
});
```

## Authentication

Authentication is handled by Laravel Fortify. Key features:

### Registration & Login
- Routes: `/register`, `/login`, `/logout`
- Views: `resources/views/auth/`
- Actions: `app/Actions/Fortify/`

### Two-Factor Authentication
Enable in user settings:
1. Navigate to Settings > Security
2. Enable 2FA
3. Scan QR code with authenticator app
4. Confirm with verification code

### Password Reset
- Route: `/forgot-password`
- Email templates: `resources/views/emails/`

## API Development

When creating API endpoints:
1. Define routes in `routes/api.php` (if needed)
2. Create controllers with `php artisan make:controller Api/ControllerName`
3. Use API resources for data transformation: `php artisan make:resource ResourceName`
4. Add authentication with Sanctum (if needed)

## Database

### Migrations
```bash
# Create new migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migration (drop all tables)
php artisan migrate:fresh
```

### Factories & Seeders
```bash
# Create factory
php artisan make:factory ModelNameFactory

# Create seeder
php artisan make:seeder TableNameSeeder

# Run seeders
php artisan db:seed
```

## Deployment

### Production Checklist
1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Configure production database
4. Configure mail driver (SMTP, SES, etc.)
5. Set up queue worker for background jobs
6. Configure cache driver (Redis recommended)
7. Run optimizations:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   composer install --optimize-autoloader --no-dev
   npm run build
   ```

### Queue Worker (Production)
```bash
# Start queue worker
php artisan queue:work

# With supervisor for automatic restart
# See: https://laravel.com/docs/queues#supervisor-configuration
```

## Troubleshooting

### Vite Error
If you see "Unable to locate file in Vite manifest":
```bash
npm run build
# or
npm run dev
```

### Permission Issues
```bash
chmod -R 775 storage bootstrap/cache
```

### Clear All Caches
```bash
php artisan optimize:clear
```

## Resources

- [Laravel Documentation](https://laravel.com/docs/12.x)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [Flux UI Components](https://flux.laravel.com)
- [Pest Testing Documentation](https://pestphp.com/docs)
- [Laravel Fortify Documentation](https://laravel.com/docs/12.x/fortify)
