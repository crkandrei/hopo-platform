# Hopo Platform

## Stack
- PHP 8.2 + Laravel 12
- MySQL
- Blade templates + Node.js/Vite for assets
- PHPUnit for testing

## Key Conventions
- Controllers in `app/Http/Controllers/`
- Models use Eloquent, keep business logic in Services
- Migrations: always use `DB_DATABASE=hopo_platform_testing` for test migrations
- Never run `migrate:fresh` on the default database (only on `hopo_platform_testing`)
- Views: Blade templates in `resources/views/`

## Testing
- Run tests with: `php artisan test` or `./vendor/bin/phpunit`
- Test database: `hopo_platform_testing`

## Artisan
- Preferred: `php artisan [command]`
- Pre-approved: `php artisan make:*`, `php artisan migrate` (test DB only)

## Docs
@docs/
