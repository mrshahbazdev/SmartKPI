# SmartKPI — Intelligent Business Control System

A multi-tenant, hierarchical business intelligence SaaS built with **Laravel 11 + Vue.js 3 + PostgreSQL**.

**Bilingual:** German (DE) + English (EN) — switchable per user.
**Responsive:** Desktop, tablet, and mobile with adaptive layouts.

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.3), Sanctum Auth, Spatie Permissions
- **Frontend:** Vue.js 3 + Inertia.js + Tailwind CSS + vue-i18n
- **Database:** PostgreSQL 16
- **Multi-tenancy:** stancl/tenancy (DB-per-tenant)
- **Cache/Queue:** Redis + Laravel Horizon
- **Charts:** ApexCharts (responsive)

## Quick Start

```bash
# 1. Clone
git clone https://github.com/mrshahbazdev/SmartKPI.git
cd SmartKPI

# 2. Install dependencies
composer install
npm install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Setup database (PostgreSQL)
# Create a database named 'smartkpi' and update .env with your credentials

# 5. Run migrations & seed
php artisan migrate --seed

# 6. Start development servers
php artisan serve
npm run dev
```

## Demo Credentials

| Role           | Email               | Password |
|----------------|---------------------|----------|
| Super Admin    | admin@smartkpi.com  | password |
| Holding Admin  | mueller@dentex.de   | password |
| Company Admin  | schmidt@dentex.de   | password |
| Dept Manager   | weber@dentex.de     | password |

## Project Structure

```
app/
├── Models/           # Eloquent models (Tenant, Company, Department, KPI, etc.)
├── Http/
│   ├── Controllers/  # Inertia controllers
│   └── Middleware/    # SetLocale, HandleInertiaRequests
├── Services/         # Business logic (KPI Engine, Analysis, etc.)
resources/
├── js/
│   ├── Components/   # Vue components (Layout, Dashboard, Forms)
│   ├── Pages/        # Inertia pages
│   └── i18n/         # Translation files (de.json, en.json)
├── css/              # Tailwind CSS
└── views/            # Blade templates
lang/
├── de/               # German backend translations
└── en/               # English backend translations
```

## Data Model

```
Tenants (Holdings)
  └── Companies (GmbHs)
       └── Departments
            └── KPI Definitions
                 └── KPI Values (time-series)
                      └── Problems (detected)
                           └── Root Causes
                                └── Actions
                                     └── Results
```

## Roles

- `super_admin` — Full system access
- `holding_admin` — Manage all companies in a holding
- `company_admin` — Manage one company
- `dept_manager` — Manage one department
- `employee` — View & enter data

## Features (Phase 1)

- Multi-tenant organization hierarchy
- Bilingual UI (DE/EN) with per-user language preference
- Responsive layout (desktop sidebar + mobile bottom nav)
- Dark mode toggle
- Role-based access control (5 roles)
- User invitation system
- KPI definition catalog with bilingual names
- Organization tree visualization
- Demo data seeder (Dentex Holding example)
