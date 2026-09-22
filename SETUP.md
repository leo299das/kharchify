# Kharchify (Expense Tracker) - Project Setup Guide

Welcome to **Kharchify**, a modern Laravel-powered expense tracking and personal finance management application.

---

## 🚀 Quick Setup (1-Click Automation)

### Windows Users
Simply double-click:
```bat
setup.bat
```
or run in PowerShell:
```powershell
.\setup.ps1
```

### macOS / Linux / Git Bash Users
Run in your terminal:
```bash
chmod +x setup.sh
./setup.sh
```

The automated script will:
1. Validate PHP (>= 8.2), Composer, and Node.js/NPM.
2. Initialize and configure your `.env` file.
3. Install PHP dependencies via Composer.
4. Generate the `APP_KEY` and link the storage folder.
5. Install NPM packages and compile production frontend assets with Vite.
6. Run database migrations and seed default demo data & categories.
7. Clear and optimize application caches.
8. Offer to launch the application in your browser.

---

## 📋 System Prerequisites

Before running the project, make sure you have:
- **PHP >= 8.2** (included with modern XAMPP) with extensions: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `gd`, `fileinfo`
- **Composer** (PHP dependency manager) -> [getcomposer.org](https://getcomposer.org/)
- **Node.js (>= 18)** & **NPM** -> [nodejs.org](https://nodejs.org/)
- **MySQL / MariaDB** (via XAMPP) or **SQLite**

---

## 🛠️ Manual Step-by-Step Installation

If you prefer to run the setup commands manually, follow these steps:

### 1. Clone or Open the Project
```bash
cd c:/xampp/htdocs/expense-tracker2
```

### 2. Copy Environment Configuration
```bash
cp .env.example .env
```
*(On Windows Command Prompt: `copy .env.example .env`)*

### 3. Configure Database in `.env`
Open `.env` in any text editor and adjust database parameters:

#### Option A: MySQL / XAMPP (Default)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kharchify
DB_USERNAME=root
DB_PASSWORD=
```
> **Note**: In phpMyAdmin (`http://localhost/phpmyadmin`), create a database named `kharchify` (utf8mb4_unicode_ci) if it doesn't already exist.

#### Option B: SQLite
```env
DB_CONNECTION=sqlite
```
Create the SQLite file:
```bash
# Windows PowerShell
New-Item -ItemType File -Force database/database.sqlite
# Or CMD
type nul > database\database.sqlite
```

### 4. Install PHP & Node Dependencies
```bash
composer install
npm install
```

### 5. Generate Application Key & Storage Link
```bash
php artisan key:generate
php artisan storage:link
```

### 6. Run Migrations & Seeders
```bash
php artisan migrate --seed
```
*To completely reset and re-seed the database at any time, run:*
```bash
php artisan migrate:fresh --seed
```

### 7. Build Frontend Assets
```bash
npm run build
```

---

## 🏃 Running the Application

### Option 1: Double-Click Launcher
- Double-click **`start.bat`** to start the Laravel server and automatically open `http://127.0.0.1:8000`.
- Double-click **`dev.bat`** if you are actively editing styles/Blade files to run Vite with Hot Module Replacement (HMR).

### Option 2: Command Line
```bash
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2 (Optional, for real-time asset development):
npm run dev
```

Visit: **`http://127.0.0.1:8000`**

---

## 🔑 Default Demo Account

When database seeding is executed, the following demo account is created:

| Field | Credentials |
|---|---|
| **Email** | `test@example.com` |
| **Password** | `password` |
| **Plan** | Full Features Plan (₹150/mo) |
| **Pre-loaded Data** | 11 core categories + realistic multi-month sample expenses |

---

## 💎 Available Subscription Plans

1. **Basic Plan — ₹49 / month** (`basic`):
   - Daily & monthly expense logging
   - Standard category management
   - Monthly expense overview & totals
   - Recent transaction history
   - Basic search and date filters
   - Responsive mobile & desktop console

2. **Medium Plan — ₹94 / month** (`medium`):
   - Everything in Basic Plan
   - Unlimited expense & custom category logging
   - Interactive visual line trends & category donut split charts
   - Monthly spending budget goal & live progress pacing meter
   - Spending warning alerts (at 80% and 100% capacity)
   - One-Click CSV / Excel data export

3. **Full Features Plan — ₹150 / month** (`pro`):
   - Everything in Medium Plan
   - Instant formatted PDF expense statements with custom date filtering
   - Smart financial health score & spending highlights
   - Recurring bills & due payments checklist tracker
   - Top merchant expense highlights & average daily spend
   - Priority customer support & cloud backup

You can also register a new account at `http://127.0.0.1:8000/register`.

---

## 🔧 Useful Artisan Commands

| Command | Description |
|---|---|
| `php artisan serve` | Start local development server |
| `php artisan migrate` | Run pending migrations |
| `php artisan migrate:fresh --seed` | Wipe DB, run all migrations and seed fresh demo data |
| `php artisan db:seed` | Seed default categories and demo user |
| `php artisan optimize:clear` | Clear cache, routes, views, and config |
| `php artisan storage:link` | Create public storage symlink for avatars/attachments |

---

## ❓ Troubleshooting

1. **"SQLSTATE[HY000] [1049] Unknown database 'kharchify'"**:
   - Open phpMyAdmin at `http://localhost/phpmyadmin` and create a database named `kharchify`.

2. **"PHP is not recognized as an internal or external command"**:
   - Add `C:\xampp\php` to your Windows System Environment Variable `PATH`.

3. **Vite assets not styling properly**:
   - Run `npm run build` or start the development server using `dev.bat` or `npm run dev`.

4. **Permission issues on storage directory**:
   - Run `php artisan storage:link` and ensure `storage/` and `bootstrap/cache/` directories are writable.
