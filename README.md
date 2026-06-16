<p align="center">
  <strong>SmartKPI</strong><br>
  Intelligent KPI Management System
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/Vue.js-3-4FC08D?logo=vuedotjs&logoColor=white" />
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/Tailwind-3-06B6D4?logo=tailwindcss&logoColor=white" />
  <img src="https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Bilingual-DE%20%7C%20EN-blue" />
  <img src="https://img.shields.io/badge/GDPR-Compliant-green" />
</p>

---

A **multi-tenant, hierarchical business intelligence SaaS** for tracking KPIs across holdings, companies, departments, and employees. Built with **Laravel 11 + Vue.js 3 + Inertia.js**, bilingual (German/English), fully responsive, and GDPR-compliant.

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Installation](#installation)
- [Demo Credentials](#demo-credentials)
- [Project Structure](#project-structure)
- [Data Model](#data-model)
- [Role System](#role-system)
- [API Reference](#api-reference)
- [Deployment Guide](#deployment-guide)
- [Configuration](#configuration)
- [Troubleshooting](#troubleshooting)
- [Roadmap](#roadmap)

---

## Features

### Phase 1 — Foundation
- **Multi-tenant architecture** — Holdings → Companies → Departments → Employees
- **Bilingual UI** — German (DE) primary, English (EN), switchable per user
- **Responsive design** — Desktop sidebar + mobile bottom nav + tablet adaptive
- **Dark mode** — System-aware toggle with persistent preference
- **5-role RBAC** — Super Admin, Holding Admin, Company Admin, Dept Manager, Employee
- **User invitation** — Email-based invitations with role assignment
- **Organization tree** — Interactive accordion with CRUD modals

### Phase 2 — KPI Engine
- **KPI definitions** — CRUD with bilingual names, formulas, thresholds, units
- **Template library** — Pre-built industry KPI templates (Manufacturing, Sales, Marketing, etc.)
- **Data entry** — Manual input, bulk entry, CSV import
- **3-level dashboards** — Department → Company → Holding with drill-down
- **Trend visualization** — Sparklines, status badges, color-coded indicators

### Phase 3 — Intelligence Engine
- **Problem detection** — Auto-detects threshold breaches, 3-consecutive trends, z-score anomalies
- **Company-specific triggers** — Each company defines its own alert rules per KPI (not global defaults)
- **Per-employee KPI ownership** — Pivot table (`kpi_user`) with roles: responsible, viewer, contributor
- **Cause-effect analysis** — KPI relationship builder, root cause tracing across departments/companies
- **Correlation engine** — Pearson correlation with lag detection
- **Action tracking** — CRUD with assignment, priority, deadline, effectiveness scoring
- **Daily focus (Tagesfokus)** — Auto-selects highest-priority action per department
- **In-app notifications** — Bilingual alerts dispatched to responsible users

### Phase 4 — Advanced Features
- **Cross-company intelligence** — Detect cascading effects across companies in a holding
- **Risk scoring** — Weighted risk calculation per company, holding-level portfolio risk
- **Benchmarking** — Compare departments/companies against each other
- **Forecasting** — Statistical predictions with confidence intervals (linear, exponential, seasonal)
- **Early warning system** — Predict threshold breaches before they happen
- **What-if scenarios** — Model KPI changes and see projected effects
- **OKR goals** — Set company/department goals linked to KPIs with progress tracking
- **REST API** — Full CRUD via Sanctum-authenticated endpoints
- **Webhooks** — Subscribe to KPI events with signature verification

### Phase 5 — Scale & Monetize
- **Subscription plans** — 3-tier pricing (Starter €49 / Professional €149 / Enterprise €399)
- **Onboarding wizard** — 5-step bilingual setup (language → structure → KPIs → team → ready)
- **White-labeling** — Custom app name, colors, logo per tenant
- **GDPR/DSGVO compliance** — Consent management, data export (Art. 20), right to deletion (Art. 17)
- **Audit trail** — Spatie ActivityLog on key models (who changed what, when, diff display)
- **Landing page** — Bilingual marketing page with features, pricing, CTA
- **API documentation** — Interactive endpoint reference
- **Performance** — 12 database indexes, API rate limiting, config/route/view caching

### Phase 6 — AI Intelligence
- **AI Anomaly Detection** — OpenAI-powered pattern analysis detecting spikes, drifts, seasonal breaks, and outliers beyond simple z-scores
- **Smart KPI Recommendations** — AI suggests new KPIs based on company type, existing coverage gaps, and industry best practices
- **Natural Language Insights** — AI-generated bilingual performance reports with trend analysis, key findings, and performance scores
- **Predictive Actions** — AI suggests corrective measures based on past problem resolutions and effectiveness history
- **AI Chatbot (KI-Assistent)** — Conversational interface for querying KPI data, asking questions in natural language (DE/EN)
- **Auto Root Cause Analysis** — AI traces KPI relationship graphs, identifies probable causes with confidence scores, and suggests investigation steps
- **AI Dashboard** — Centralized view of all AI insights with stats, type filters, and quick navigation
- **Chat History** — Persistent chat sessions with conversation memory

---

## Tech Stack

| Layer          | Technology                                           |
|----------------|------------------------------------------------------|
| **Backend**    | Laravel 11, PHP 8.3, Sanctum Auth                    |
| **Frontend**   | Vue.js 3, Inertia.js, Tailwind CSS, vue-i18n         |
| **Database**   | PostgreSQL 16 / MySQL 8 (shared hosting compatible)   |
| **RBAC**       | Spatie Laravel-Permission (5 roles)                   |
| **Audit**      | Spatie Laravel-Activitylog                            |
| **Tenancy**    | stancl/tenancy (DB-per-tenant)                        |
| **Cache**      | Redis (VPS) / File-based (shared hosting)             |
| **AI**         | OpenAI GPT-4o-mini via openai-php/laravel             |
| **Build**      | Vite 5, PostCSS, Autoprefixer                         |

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                       Vue.js 3 SPA                          │
│  (Inertia.js Pages + Components + Tailwind + vue-i18n)      │
├─────────────────────────────────────────────────────────────┤
│                    Inertia.js Bridge                         │
├─────────────────────────────────────────────────────────────┤
│                   Laravel 11 Backend                        │
│  ┌──────────┐  ┌─────────────┐  ┌────────────────────────┐ │
│  │Controllers│  │  Services   │  │     Middleware          │ │
│  │(Inertia)  │  │(KPI Engine, │  │(SetLocale, Inertia,    │ │
│  │           │  │ Detection,  │  │ Auth, RateLimit)        │ │
│  │           │  │ Forecast,   │  │                         │ │
│  │           │  │ AI Service) │  │                         │ │
│  └──────────┘  └──────┬──────┘  └────────────────────────┘ │
│                       │                                     │
│              ┌────────▼────────┐                            │
│              │  OpenAI API     │                            │
│              │  (GPT-4o-mini)  │                            │
│              └─────────────────┘                            │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              Eloquent ORM + Spatie RBAC               │   │
│  └──────────────────────────────────────────────────────┘   │
├─────────────────────────────────────────────────────────────┤
│            PostgreSQL 16 / MySQL 8 Database                 │
│  (Tenants, Companies, Departments, KPIs, Values,            │
│   Problems, Actions, Forecasts, Subscriptions, etc.)        │
└─────────────────────────────────────────────────────────────┘
```

### Multi-Level Data Flow

```
Tenant (Holding GmbH)
  ├── Company A
  │     ├── Department 1
  │     │     ├── KPI: Error Rate  ← owned by Employee X (responsible)
  │     │     │     ├── Values (time-series)
  │     │     │     ├── Alert Rules (company-specific thresholds)
  │     │     │     ├── Problems (auto-detected)
  │     │     │     │     ├── Root Causes (traced via KPI relationships)
  │     │     │     │     └── Actions (assigned to employees)
  │     │     │     └── Forecasts (predicted values)
  │     │     └── KPI: Production Volume ← owned by Employee Y
  │     └── Department 2
  └── Company B
        └── Department 3
              └── KPIs → Cross-Company Effects ← detected automatically
```

---

## Installation

### One-Command Install (Recommended)

```bash
git clone https://github.com/mrshahbazdev/SmartKPI.git
cd SmartKPI
chmod +x install.sh
./install.sh
```

The installer supports **multiple modes**:

```bash
./install.sh              # Interactive — asks all questions
./install.sh --quick      # Quick — uses defaults (MySQL, German, demo data)
./install.sh --update     # Update — pulls code, migrates, rebuilds
./install.sh --health     # Health check — diagnoses issues
./install.sh --restore    # Restore — picks from saved backups
./install.sh --uninstall  # Remove — drops DB, cleans up files
./install.sh --help       # Help — shows all options
```

### What the Installer Does (14 Steps)

| Step | Action                              | Details                                                    |
|------|-------------------------------------|------------------------------------------------------------|
| 1    | System requirements                 | PHP 8.2+, extensions (pdo, mbstring, curl...), Composer     |
| 2    | Configuration                       | URL, DB engine (MySQL/PostgreSQL), credentials, locale      |
| 3    | Database creation                   | cPanel UAPI / MySQL CLI / PostgreSQL CLI                    |
| 4    | `.env` setup                        | Auto-configures DB, cache, session, queue                   |
| 5    | Composer install                    | Production-optimized (`--no-dev --optimize-autoloader`)     |
| 6    | App key generation                  | Encryption key for sessions & cookies                       |
| 7    | Frontend assets                     | Uses pre-built assets or runs `npm run build`               |
| 8    | Database migration                  | Creates all tables (30+ migrations)                         |
| 9    | Database seeding                    | Demo data: Dentex Holding, 2 companies, 6 depts, 4 users   |
| 10   | Storage & permissions               | Symlinks, chmod 755/775, web server ownership               |
| 11   | Cache optimization                  | Config, route, view, event caching                          |
| 12   | Web server config                   | Root `.htaccess`, cPanel symlinks, Nginx hints              |
| 13   | Cron setup                          | Laravel scheduler (cPanel instructions or auto-add)         |
| 14   | Health check                        | Verifies everything works                                   |

### Supported Environments

| Environment                                | DB Engine   | Cache       | Status    |
|--------------------------------------------|-------------|-------------|-----------|
| **cPanel** (SiteGround, Hostinger, etc.)   | MySQL       | File-based  | Full      |
| **Plesk** (Contabo, IONOS)                 | MySQL       | File-based  | Full      |
| **VPS** (DigitalOcean, Hetzner, AWS)       | PostgreSQL  | Redis       | Full      |
| **Docker**                                 | PostgreSQL  | Redis       | Full      |
| **Local Dev** (macOS, Linux)               | Either      | File-based  | Full      |

### Manual Install

```bash
# Clone
git clone https://github.com/mrshahbazdev/SmartKPI.git
cd SmartKPI

# Dependencies
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Database (edit .env first with your credentials)
php artisan migrate --seed

# Build & serve
npm run build
php artisan serve     # http://localhost:8000
npm run dev           # Vite dev server (hot reload)
```

### System Requirements

| Requirement     | Minimum          | Recommended       |
|-----------------|------------------|-------------------|
| PHP             | 8.2              | 8.3               |
| Database        | MySQL 8 / PG 15  | MySQL 8 / PG 16   |
| Node.js         | 18.x             | 20.x (optional*)  |
| Composer        | 2.x              | 2.7+              |
| Disk Space      | 100 MB           | 500 MB            |
| RAM             | 256 MB           | 512 MB            |
| SSH Access      | Required         | Required          |

*\*Node.js is optional for deployment — pre-built assets are committed to git.*

### PHP Extensions Required

`pdo`, `pdo_mysql`/`pdo_pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`, `fileinfo`, `dom`

---

## Demo Credentials

| Role           | Email                | Password   | Scope                        |
|----------------|----------------------|------------|------------------------------|
| Super Admin    | admin@smartkpi.com   | `password` | Full system access           |
| Holding Admin  | mueller@dentex.de    | `password` | All companies in Dentex      |
| Company Admin  | schmidt@dentex.de    | `password` | Dentex Produktion GmbH       |
| Dept Manager   | weber@dentex.de      | `password` | Qualitätskontrolle dept      |

### Demo Data Structure

```
Dentex Holding GmbH (Tenant)
├── Dentex Produktion GmbH
│   ├── Fertigung (Manufacturing)
│   ├── Qualitätskontrolle (Quality)
│   └── Logistik (Logistics)
└── Dentex Vertrieb GmbH
    ├── Innendienst (Inside Sales)
    ├── Außendienst (Field Sales)
    └── Marketing

Subscription Plans: Starter (€49) | Professional (€149) | Enterprise (€399)
KPI Templates: 8 bilingual templates across Manufacturing, Quality, Sales, Marketing
```

---

## Project Structure

```
SmartKPI/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                    # Login, Register
│   │   │   ├── Api/                     # REST API (Sanctum)
│   │   │   ├── DashboardController      # Main dashboard
│   │   │   ├── CompanyDashboardController
│   │   │   ├── DepartmentDashboardController
│   │   │   ├── HoldingDashboardController
│   │   │   ├── KpiDefinitionController  # KPI CRUD
│   │   │   ├── KpiValueController       # Data entry
│   │   │   ├── ProblemController        # Auto-detected problems
│   │   │   ├── ActionController         # Action tracking
│   │   │   ├── AlertRuleController      # Company-specific triggers
│   │   │   ├── KpiRelationshipController# Cause-effect chains
│   │   │   ├── ForecastController       # Predictions
│   │   │   ├── ScenarioController       # What-if
│   │   │   ├── GoalController           # OKR goals
│   │   │   ├── HoldingIntelligenceController  # Risk, benchmark
│   │   │   ├── OnboardingController     # 5-step wizard
│   │   │   ├── PricingController        # Subscription plans
│   │   │   ├── GdprController           # Privacy, export, deletion
│   │   │   ├── WhiteLabelController     # Branding
│   │   │   ├── AuditTrailController     # Activity log
│   │   │   ├── WebhookController        # Event webhooks
│   │   │   └── ReportController         # PDF/Excel reports
│   │   └── Middleware/
│   │       ├── HandleInertiaRequests     # Shared props (locale, user)
│   │       └── SetLocale                # Per-user language
│   ├── Models/
│   │   ├── Tenant, Company, Department  # Org hierarchy
│   │   ├── KpiDefinition, KpiValue      # KPI engine
│   │   ├── Problem, RootCause, Action   # Intelligence engine
│   │   ├── Forecast, Scenario, Goal     # Advanced analytics
│   │   ├── AlertRule, KpiRelationship   # Rules & relationships
│   │   ├── SubscriptionPlan, TenantSubscription  # Billing
│   │   ├── TenantSetting               # White-label config
│   │   ├── ConsentRecord, DataExportRequest  # GDPR
│   │   └── Webhook, CrossCompanyEffect  # Integrations
│   └── Services/
│       ├── ProblemDetectionService       # Auto-detection engine
│       ├── ForecastService              # Statistical predictions
│       ├── RiskScoringService           # Risk calculation
│       └── CrossCompanyService          # Cross-company analysis
├── resources/js/
│   ├── Components/
│   │   └── Layout/AppShell.vue          # Main responsive layout
│   ├── Pages/                           # 35+ Inertia pages
│   │   ├── Dashboard/                   # Main + level-specific
│   │   ├── Kpis/                        # CRUD, templates
│   │   ├── Problems/                    # Timeline, detail
│   │   ├── Actions/                     # CRUD
│   │   ├── Holding/                     # Risk, benchmark, cross-effects
│   │   ├── Forecast/                    # Predictions, warnings
│   │   ├── Scenarios/, Goals/           # What-if, OKR
│   │   ├── Onboarding/Wizard.vue        # 5-step setup
│   │   ├── Pricing/Index.vue            # Plan comparison
│   │   ├── Settings/                    # White-label, GDPR, audit trail
│   │   ├── Landing/Index.vue            # Marketing page
│   │   └── ApiDocs/Index.vue            # API documentation
│   └── i18n/
│       ├── de.json                      # 430+ German translations
│       └── en.json                      # 430+ English translations
├── database/
│   ├── migrations/                      # 30+ migrations
│   └── seeders/DatabaseSeeder.php       # Demo data + subscription plans
├── routes/
│   ├── web.php                          # 70+ web routes
│   └── api.php                          # REST API routes (v1)
├── install.sh                           # Advanced auto-installer
├── .htaccess                            # Root → public/ redirect
└── public/
    ├── .htaccess                        # Laravel front controller
    └── build/                           # Pre-built Vite assets
```

---

## Data Model

```
┌──────────────────────────────────────────────────────────────────────┐
│                           CORE HIERARCHY                             │
├──────────────────────────────────────────────────────────────────────┤
│  Tenants ──1:N──> Companies ──1:N──> Departments ──1:N──> Users     │
│                                          │                           │
│                                          ├──1:N──> KpiDefinitions   │
│                                          │              │            │
│                                          │        N:M (kpi_user)    │
│                                          │         with role pivot   │
│                                          │              │            │
│                                          │         ──1:N──> KpiValues│
├──────────────────────────────────────────────────────────────────────┤
│                        INTELLIGENCE LAYER                            │
├──────────────────────────────────────────────────────────────────────┤
│  KpiDefinition ──1:N──> Problems ──1:N──> RootCauses               │
│                              │                 │                     │
│                              └──1:N──> Actions ─┘                   │
│                                          │                           │
│  KpiDefinition ──1:N──> AlertRules (company-specific triggers)      │
│  KpiDefinition ──M:N──> KpiRelationships (cause ↔ effect)          │
│  KpiDefinition ──1:N──> Forecasts (predictions)                     │
├──────────────────────────────────────────────────────────────────────┤
│                          PLATFORM LAYER                              │
├──────────────────────────────────────────────────────────────────────┤
│  SubscriptionPlans ──1:N──> TenantSubscriptions                     │
│  TenantSettings (white-label, onboarding state)                     │
│  ConsentRecords (GDPR consents per user)                            │
│  DataExportRequests (Art. 17/20 requests)                           │
│  CompanyRiskScores, CrossCompanyEffects, Scenarios, Goals           │
└──────────────────────────────────────────────────────────────────────┘
```

---

## Role System

| Role            | Scope               | Permissions                                                        |
|-----------------|----------------------|--------------------------------------------------------------------|
| `super_admin`   | Full system          | Everything — all tenants, companies, system settings                |
| `holding_admin` | Entire holding       | All companies, cross-company analysis, risk overview, benchmarks   |
| `company_admin` | One company          | All departments in company, alert rules, team management           |
| `dept_manager`  | One department       | Department KPIs, problem review, action assignment                 |
| `employee`      | Own KPIs only        | View/enter data for assigned KPIs, manage own actions              |

### Per-Employee KPI Ownership

```
kpi_user pivot table:
  ├── kpi_definition_id
  ├── user_id
  ├── role: responsible | viewer | contributor
  └── timestamps
```

Each employee sees **only their assigned KPIs** in their dashboard and daily focus.

---

## API Reference

### Authentication

All API endpoints require **Laravel Sanctum** Bearer token:

```
Authorization: Bearer <your-api-token>
```

### Base URL

```
https://your-domain.com/api/v1
```

### Endpoints

| Method | Endpoint                      | Description                                     |
|--------|-------------------------------|-------------------------------------------------|
| GET    | `/api/v1/kpis`                | List KPIs (paginated, filterable by company/category) |
| GET    | `/api/v1/kpis/{id}`           | Get single KPI with relationships               |
| GET    | `/api/v1/kpis/{id}/values`    | Get KPI values (date range: `?from=&to=`)        |
| POST   | `/api/v1/kpis/{id}/values`    | Record new value (triggers problem detection)    |
| GET    | `/api/v1/kpis/{id}/analytics` | Analytics: count, min, max, avg, median, stddev, trend |

### Rate Limiting

API requests are throttled at **60 requests/minute** per user (configurable via `throttle:api`).

### Example

```bash
# List all KPIs
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://your-domain.com/api/v1/kpis

# Record a KPI value
curl -X POST \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"value": 95.5, "recorded_at": "2026-06-13", "notes": "Weekly production"}' \
     https://your-domain.com/api/v1/kpis/1/values
```

---

## Deployment Guide

### cPanel / SiteGround

```bash
# 1. SSH into your server
ssh user@your-server.com

# 2. Clone into home directory (NOT public_html)
cd ~
git clone https://github.com/mrshahbazdev/SmartKPI.git
cd SmartKPI

# 3. Run installer
chmod +x install.sh
./install.sh

# 4. Point domain (the installer handles .htaccess)
# If project is outside public_html, installer creates symlink automatically
```

### VPS (Nginx + PostgreSQL)

```bash
# 1. Clone
cd /var/www
git clone https://github.com/mrshahbazdev/SmartKPI.git
cd SmartKPI

# 2. Run installer (will detect VPS + Nginx)
chmod +x install.sh
./install.sh

# 3. Nginx config
sudo nano /etc/nginx/sites-available/smartkpi
```

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/SmartKPI/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/smartkpi /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### Updating

```bash
cd SmartKPI
./install.sh --update
# Auto: backup → git pull → composer → npm build → migrate → cache
```

---

## Configuration

### Key `.env` Variables

| Variable             | Default          | Description                              |
|----------------------|------------------|------------------------------------------|
| `APP_LOCALE`         | `de`             | Default UI language (`de` or `en`)       |
| `APP_FALLBACK_LOCALE`| `en`             | Fallback if translation key missing      |
| `DB_CONNECTION`      | `pgsql`          | `mysql` for shared hosting               |
| `CACHE_STORE`        | `redis`          | `file` for shared hosting                |
| `QUEUE_CONNECTION`   | `redis`          | `sync` for shared hosting                |
| `SESSION_DRIVER`     | `database`       | `file` for shared hosting                |
| `OPENAI_API_KEY`     | (empty)          | OpenAI API key for AI features           |
| `OPENAI_ORGANIZATION`| (empty)          | OpenAI organization ID (optional)        |

### Subscription Plans (Seeded)

| Plan          | Price    | Companies | KPIs | Users | API | Forecast | Cross-Co | White-Label |
|---------------|----------|-----------|------|-------|-----|----------|----------|-------------|
| Starter       | €49/mo   | 1         | 20   | 5     | -   | -        | -        | -           |
| Professional  | €149/mo  | 5         | 100  | 25    | Yes | Yes      | -        | -           |
| Enterprise    | €399/mo  | Unlimited | 999  | 999   | Yes | Yes      | Yes      | Yes         |

---

## Troubleshooting

### Common Issues

| Problem                          | Solution                                                         |
|----------------------------------|------------------------------------------------------------------|
| 500 Error after install          | `chmod -R 775 storage bootstrap/cache`                           |
| Blank page / no styles           | Run `npm run build` or check `public/build/manifest.json` exists |
| Database connection refused      | Verify `.env` credentials, check DB service is running           |
| "Class not found" errors         | `composer dump-autoload`                                         |
| Session/login not working        | `php artisan config:clear && php artisan cache:clear`            |
| Translations not showing         | Check `resources/js/i18n/de.json` and `en.json`                  |
| cPanel DB prefix issue           | Database name must be `username_dbname` on cPanel                |
| PHP version too low              | cPanel → MultiPHP Manager → Select PHP 8.3                      |

### Health Check

```bash
./install.sh --health
```

Checks: PHP version, extensions, DB connection, storage permissions, build assets, disk space, APP_KEY.

### Logs

```bash
# Application log
tail -f storage/logs/laravel.log

# Installation log
cat storage/logs/install.log
```

### Reset Everything

```bash
php artisan migrate:fresh --seed    # Reset DB + reseed demo data
php artisan optimize:clear          # Clear all caches
```

---

## Roadmap

- [x] **Phase 1** — Foundation (Auth, i18n, Shell, Org CRUD)
- [x] **Phase 2** — KPI Engine (Definitions, Data Entry, Dashboards)
- [x] **Phase 3** — Intelligence (Problem Detection, Actions, Ownership)
- [x] **Phase 4** — Advanced (Forecasting, Risk, Goals, API)
- [x] **Phase 5** — Scale (Billing, Onboarding, GDPR, Landing)
- [x] **Phase 6** — AI Intelligence (Anomaly Detection, Chatbot, Root Cause, Recommendations)
- [ ] **Phase 7** — Mobile App (PWA / React Native)

---

## License

Proprietary — All rights reserved.

---

<p align="center">
  <strong>SmartKPI</strong> — Built with Laravel 11 + Vue.js 3<br>
  <em>Intelligent. Bilingual. GDPR-Compliant.</em>
</p>
