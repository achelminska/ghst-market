[ghst_market_ascii.html](https://github.com/user-attachments/files/27906125/ghst_market_ascii.html)<div align="center">

```
[Uploading ghst_mark<div style="background:#1a1a1a; border-radius: var(--border-radius-lg); padding: 2rem; overflow-x: auto;">
<pre style="font-family: 'Courier New', Courier, monospace; font-size: 11px; line-height: 1.2; color: #ffffff; margin: 0; white-space: pre; letter-spacing: 0;">░██████╗░██╗  ██╗███████╗████████╗    ███╗   ███╗ █████╗ ██████╗ ██╗  ██╗███████╗████████╗
██╔════╝░██║  ██║██╔════╝╚══██╔══╝    ████╗ ████║██╔══██╗██╔══██╗██║ ██╔╝██╔════╝╚══██╔══╝
██║  ███╗███████║███████╗   ██║       ██╔████╔██║███████║██████╔╝█████╔╝ █████╗     ██║   
██║   ██║██╔══██║╚════██║   ██║       ██║╚██╔╝██║██╔══██║██╔══██╗██╔═██╗ ██╔══╝     ██║   
╚██████╔╝██║  ██║███████║   ██║       ██║ ╚═╝ ██║██║  ██║██║  ██║██║  ██╗███████╗   ██║   
 ╚═════╝ ╚═╝  ╚═╝╚══════╝   ╚═╝       ╚═╝     ╚═╝╚═╝  ╚═╝╚═╝  ╚═╝╚═╝  ╚═╝╚══════╝   ╚═╝  </pre>
</div>et_ascii.html…]()


```

**Marketplace for digital creative assets**

*Because Itch.io's UI is an abomination and someone had to fix it.*

[![Tests](https://github.com/achelminska/ghst-market/actions/workflows/tests.yml/badge.svg)](https://github.com/achelminska/ghst-market/actions/workflows/tests.yml)
[![Linter](https://github.com/achelminska/ghst-market/actions/workflows/lint.yml/badge.svg)](https://github.com/achelminska/ghst-market/actions/workflows/lint.yml)
[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

</div>

---

## What is GHSTmarket?

GHSTmarket is a marketplace platform for buying and downloading digital creative assets — fonts, graphics, templates, music, indie game files, and more. Think Itch.io, but with a UI that doesn't make you want to close the tab immediately.

Creators can publish their work, set a price (or make it free), and earn virtual credits when others purchase it. Buyers browse, search, buy, and download — with all purchased assets available forever from their personal library.

> **University project** — Advanced Web Services Programming, WSB-NLU, 2025/2026  
> Built with Laravel 12 + Livewire + Flux UI + PostgreSQL

---

## Features

### For Buyers
- 🔍 **Search & filter** — by name, description, category, tags, price (free/paid), date, popularity
- 💳 **Virtual wallet** — top up your balance and spend it on assets
- 📦 **My Purchases** — all bought assets in one place, downloadable at any time
- 👤 **Creator profiles** — browse assets by a specific creator

### For Creators
- 📤 **Upload assets** — zip, png, jpg, mp3, ttf, pdf (up to 50 MB)
- 🏷️ **Tags & categories** — up to 5 tags per product
- 💰 **Earn credits** — your balance grows when someone buys your work
- ✏️ **Manage listings** — edit, unpublish, or delete your products

### For Admins
- 🗂️ **Category management** — add, edit, delete categories
- 🔒 **Access control** — admin flag per user, no separate panel needed

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 (PHP 8.3+) |
| Database | PostgreSQL |
| Views | Blade Templates |
| Reactivity | Livewire v3 |
| UI Components | Flux UI |
| Client interactions | Alpine.js |
| Styling | Tailwind CSS |
| Auth scaffold | Laravel Livewire Starter Kit |
| ORM | Eloquent |
| File storage | Laravel Storage (local driver) |
| Testing | Pest |
| Code quality | Laravel Pint |
| CI | GitHub Actions |

---

## Database Schema

```
users ──────────────────────────────────────────────┐
  id, name, email, password                          │
  balance (decimal)                                  │
  is_admin (boolean)                                 │
  is_active (boolean)                                │
                                                     │
categories                    tags                   │
  id, name, slug                id, name, slug        │
       │                             │               │
       │ hasMany              M:N pivot              │
       ▼                             ▼               │
products ◄───────────────── product_tag             │
  id, title, description                             │
  price (decimal)                                    │
  file_path (string)                                 │
  category_id (FK)                                   │
  user_id (FK) ───────────────────────────────────────┘
       │
       │ hasMany
       ▼
purchases
  id, user_id (FK), product_id (FK)
  purchased_at (timestamp)
```

---

## Getting Started

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 22+
- PostgreSQL

### Installation

```bash
# Clone the repository
git clone https://github.com/achelminska/ghst-market.git
cd ghst-market

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Database setup

Create a PostgreSQL database and update your `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ghstmarket
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Then run migrations:

```bash
php artisan migrate
```

Optionally seed with test data:

```bash
php artisan db:seed
```

### Run locally

```bash
composer run dev
```

App will be available at [http://localhost:8000](http://localhost:8000)

---

## Development workflow

```
main        ← stable, protected — merged via PR after CI passes
  └─ develop ← day-to-day work
       └─ feature/xxx ← one branch per feature
```

CI runs automatically on every push and PR to `develop` and `main`:
- **Tests** — Pest test suite on PHP 8.4 and 8.5
- **Linter** — Laravel Pint code style check

---

## Project structure

```
app/
  Actions/Fortify/     ← auth actions (register, reset password)
  Concerns/            ← shared validation rule traits
  Http/Controllers/    ← request handling
  Livewire/            ← Livewire components
  Models/              ← Eloquent models
database/
  migrations/          ← database schema
  factories/           ← test data factories
  seeders/             ← database seeders
resources/views/
  layouts/             ← app & auth layouts
  pages/               ← page views
  components/          ← reusable Blade components
routes/
  web.php              ← web routes
  settings.php         ← settings routes
tests/
  Feature/             ← feature tests
  Unit/                ← unit tests
```

---

## Validation rules

| Rule | Details |
|---|---|
| Product price | Must be ≥ 0 |
| Product title | 3–100 characters |
| Product description | 20–2000 characters |
| Product tags | Minimum 1, maximum 5 |
| Upload file | Extensions: zip, png, jpg, mp3, ttf, pdf · Max size: 50 MB |
| User email | Unique, valid format |
| User password | Min 8 characters, at least one digit |
| Wallet top-up | Amount must be > 0 |
| Purchase | Blocked if balance is insufficient |

---

## License

[MIT](LICENSE) — do whatever you want with it.

---

<div align="center">
  <sub>Built with ☕ and mild frustration at Itch.io's design choices.</sub>
</div>
