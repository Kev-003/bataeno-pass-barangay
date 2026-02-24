# Setup Guide for Bataeno Pass Barangay

Follow these steps to set up the project on a new device (Windows/Mac/Linux).

## 1. Prerequisites

- **PHP 8.2+**
- **Node.js 18+** & **npm**
- **Composer**
- **MySQL** or **MariaDB**
- **Chrome/Chromium** (Required for PDF generation via Browsershot)

## 2. Initial Setup

1. **Clone the repository** (if you haven't already).
2. **Copy Environment File**:
    - Open terminal in the project root.
    - Run: `copy .env.example .env` (Windows) or `cp .env.example .env` (Mac/Linux).
3. **Install Dependencies**:
    - Run: `composer install`
    - Run: `npm install`
4. **Generate Application Key**:
    - Run: `php artisan key:generate`

## 3. Database Configuration

1. **Create Database**:
    - Open your MySQL tool (XAMPP/Laragon/HeidiSQL).
    - Create a new database named `laravel` (or whatever you prefer).
2. **Update `.env`**:
    - Update `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` to match your local setup.
3. **Run Migrations & Seeders**:
    - Run: `php artisan migrate --seed`
    - _Note: This will create the initial Admin user and sample barangay data._

## 4. Storage & Assets

1. **Create Storage Link**:
    - Run: `php artisan storage:link`
2. **Build Assets**:
    - Run: `npm run dev` (for development) or `npm run build` (for production).

## 5. Troubleshooting (Common Issues)

### Browsershot / PDF Generation Errors

If you see errors related to `Browsershot` or `node` failing:

- **Windows PATH**: Ensure Node.js is in your System PATH.
- **Node Path**: If standard detection fails, add `NODE_BINARY_PATH` to your `.env` pointing to your node.exe (e.g., `C:\Program Files\nodejs\node.exe`).
- **Chrome Path**: If Browsershot can't find Chrome, you may need to use `.setChromePath()` or set an environment variable.

### Reverb / Real-time Notifications

- Ensure `php artisan reverb:start` is running if you need real-time updates.

### Permission Denied

- On Linux/Mac: Run `chmod -R 775 storage bootstrap/cache`.
- On Windows: Usually not required unless using WSL.
