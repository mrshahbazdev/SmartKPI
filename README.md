# SmartKPI — Intelligent Business Control System

A multi-tenant, hierarchical business intelligence SaaS built with **Laravel 11 + Vue.js 3 + PostgreSQL**.

**Bilingual:** German (DE) + English (EN) — switchable per user.
**Responsive:** Desktop, tablet, and mobile with adaptive layouts.

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.3), Sanctum Auth, Spatie Permissions
- **Frontend:** Vue.js 3 + Inertia.js + Tailwind CSS + vue-i18n
- **Database:** PostgreSQL 16 / MySQL 8 (shared hosting compatible)
- **Multi-tenancy:** stancl/tenancy (DB-per-tenant)
- **Cache/Queue:** Redis + Laravel Horizon
- **Charts:** ApexCharts (responsive)

## Quick Start

### One-Command Install (cPanel / SiteGround / Shared Hosting)

```bash
git clone https://github.com/mrshahbazdev/SmartKPI.git
cd SmartKPI
chmod +x install.sh
./install.sh
```

The install script automatically:
- Creates MySQL database via cPanel UAPI
- Configures `.env` (DB, URL, cache, locale)
- Runs `composer install` (production optimized)
- Runs `npm install && npm run build`
- Generates app key
- Runs migrations & seeds demo data
- Sets permissions & storage symlinks
- Configures `.htaccess` for Apache

### Manual Install (VPS / Local Development)

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

# 4. Setup database (PostgreSQL or MySQL)
# Create a database named 'smartkpi' and update .env with your credentials

# 5. Run migrations & seed
php artisan migrate --seed

# 6. Start development servers
php artisan serve
npm run dev
```

### Uninstall

```bash
chmod +x uninstall.sh
./uninstall.sh
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

## Features

### Phase 1 — Foundation
- Multi-tenant organization hierarchy
- Bilingual UI (DE/EN) with per-user language preference
- Responsive layout (desktop sidebar + mobile bottom nav)
- Dark mode toggle, Role-based access control (5 roles)
- User invitation system

### Phase 2 — KPI Engine
- KPI definitions CRUD with bilingual names
- Template library, manual/CSV data entry
- 3-level dashboards (Department → Company → Holding)

### Phase 3 — Intelligence Engine
- Auto problem detection (thresholds, trends, anomalies)
- Company-specific trigger rules
- Cause-effect analysis, root cause tracing
- Action tracking with per-employee KPI ownership

### Phase 4 — Advanced Features
- Cross-company intelligence & risk scoring
- Forecasting with confidence intervals
- What-if scenarios, OKR goals
- REST API & webhooks

### Phase 5 — Scale & Monetize
- Subscription plans (Starter/Professional/Enterprise)
- 5-step onboarding wizard
- White-labeling (custom branding per tenant)
- GDPR/DSGVO compliance (consent, data export, deletion)
- Audit trail, landing page, API docs
- Performance indexes, API rate limiting

## Hosting Requirements

| Requirement     | Minimum          | Recommended       |
|-----------------|------------------|-------------------|
| PHP             | 8.2              | 8.3               |
| Database        | MySQL 8 / PG 15  | MySQL 8 / PG 16   |
| Node.js         | 18.x             | 20.x              |
| Disk Space      | 100 MB           | 500 MB            |
| RAM             | 256 MB           | 512 MB            |
| SSH Access      | Required         | Required          |
