# NHC Tenant Portal

**National Housing Corporation - Integrated Tenant Management & Payment Portal**

A comprehensive property management and tenant portal built for the National Housing Corporation of Papua New Guinea. This demo application showcases tenant billing, payment processing, support ticket management, and administrative reporting.

## Tech Stack

- **Backend:** Laravel 12, PHP 8.2+
- **Admin Panel:** FilamentPHP v3
- **Frontend:** Livewire 3, Tailwind CSS, Alpine.js
- **Database:** MariaDB 10.4+ / MySQL 8+
- **PDF Generation:** barryvdh/laravel-dompdf
- **Authorization:** Spatie Laravel Permission
- **Activity Logging:** Spatie Laravel Activitylog

## Features

### Admin Panel (`/admin`)
- Dashboard with statistics widgets (total tenants, revenue, arrears)
- Region, Property, Tenancy, Invoice, and Payment management
- Support ticket management with internal notes
- Tenant CSV bulk import
- PDF reports: Arrears Report, Revenue Collection Report
- Audit logging and system settings
- Role-based access (Super Admin / Admin)

### Client Portal (`/portal`)
- Dashboard with balance overview, next due date, monthly rent
- Invoice listing with status filters and PDF downloads
- Payment history with receipt downloads
- Online payment simulation (demo gateway)
- Support ticket system with threaded chat
- Profile management and password change
- Monthly statement PDF download

### PDF Engine
- Professional invoices with NHC letterhead
- Payment receipts
- Monthly tenant statements
- Arrears report (grouped by region)
- Revenue collection report (by region and payment method)

## Prerequisites

- PHP 8.2+ with extensions: `zip`, `intl`, `pdo_mysql`, `mbstring`
- Composer 2.x
- Node.js 18+ and NPM
- MariaDB 10.4+ or MySQL 8+
- Git

## Installation

### 1. Clone the repository

```bash
cd nhc-portal
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install frontend dependencies and build

```bash
npm install
npm run build
```

### 4. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database connection:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nhc_portal
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Create the database

```sql
CREATE DATABASE nhc_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Run migrations and seed demo data

```bash
php artisan migrate:fresh --seed
```

This creates all tables and seeds:
- 4 regions (NCD, Morobe, Eastern Highlands, Madang)
- 19 users (3 staff + 16 tenants)
- 20 properties across all regions
- 16 tenancies (15 active + 1 terminated)
- 80+ invoices with mixed statuses
- 60+ payment records
- 3 support tickets with threaded comments
- System settings (NHC organization info)

### 7. Start the development server

```bash
php artisan serve --port=8001
```

Visit: `http://localhost:8001`

## Demo Credentials

| Role | Email | Password |
|------|-------|----------|
| **Super Admin** | `superadmin@nhc.gov.pg` | `demo2026!` |
| **Admin (NCD)** | `admin.ncd@nhc.gov.pg` | `demo2026!` |
| **Admin (Morobe)** | `admin.morobe@nhc.gov.pg` | `demo2026!` |
| **Tenant** | `tenant@example.pg` | `demo2026!` |

- **Super Admin / Admin** accounts are redirected to the Admin Panel at `/admin`
- **Tenant** account is redirected to the Client Portal at `/portal`

## User Roles

| Role | Access | Description |
|------|--------|-------------|
| `super_admin` | Full system | System manager, all regions, settings, user management |
| `admin` | Region-scoped | Staff member, manages tenants/properties in assigned region |
| `client` | Portal only | Tenant, views invoices, makes payments, submits tickets |

## Key URLs

| Page | URL |
|------|-----|
| Login | `/admin/login` |
| Admin Dashboard | `/admin` |
| Client Portal | `/portal` |
| Admin Reports | `/admin/reports` |

## Demo Walkthrough

1. **Login as Super Admin** (`superadmin@nhc.gov.pg`) to see the full admin panel
2. **Browse Properties** under Property Management to see 20 properties across 4 regions
3. **View Invoices** under Financial to see all billing with PDF download buttons
4. **View Payments** to see payment history with receipt downloads
5. **Check Reports** page to download Arrears and Revenue reports
6. **Login as Tenant** (`tenant@example.pg`) to see the client portal
7. **Dashboard** shows balance, next due date, and recent activity
8. **Make a Payment** to simulate an online payment (use any card number)
9. **Support Tickets** to view and respond to maintenance requests
10. **Download PDFs** from invoices, payments, and monthly statement

## Currency

All amounts are in **PGK** (Papua New Guinea Kina).

## Project Structure

```
nhc-portal/
├── app/
│   ├── Filament/           # Admin panel resources, pages, widgets
│   ├── Http/
│   │   ├── Controllers/    # PdfController
│   │   ├── Middleware/      # RedirectClientFromAdmin
│   │   └── Responses/      # LoginResponse (role-based redirect)
│   ├── Livewire/Portal/    # Client portal Livewire components
│   ├── Models/             # Eloquent models (9 models)
│   └── Services/           # PdfService
├── database/
│   ├── migrations/         # 15 migration files
│   └── seeders/            # RoleSeeder, DemoDataSeeder, SettingsSeeder
├── resources/views/
│   ├── errors/             # Custom 403, 404, 500 pages
│   ├── filament/           # Filament custom views
│   ├── layouts/            # Portal layout (portal.blade.php)
│   ├── livewire/portal/    # Portal Blade templates
│   └── pdf/                # PDF templates (5 templates)
└── routes/web.php          # Portal and PDF routes
```

## License

This is a demo application built for National Housing Corporation, Papua New Guinea.
