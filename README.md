# Hopo

Management software for indoor playgrounds.

## What it does

- **Session tracking** — Start/stop play sessions with RFID bracelets
- **Automatic pricing** — Calculate costs based on playtime
- **Fiscal receipts** — Print receipts via connected cash register
- **Reports** — Daily summaries, traffic analysis, revenue tracking

## Tech Stack

- Laravel 12 / PHP 8.2+
- Tailwind CSS
- SQLite (dev) / PostgreSQL (prod)

## Quick Start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

Open `http://localhost:8000`

## License

Proprietary
