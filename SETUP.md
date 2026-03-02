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

## 6. NFC Walk-In Hardware Setup (No Mock)

For real card tapping in walk-in flow, run these in separate terminals:

1. `npm run nfc-server`
2. `python -m venv .venv`
3. `.\.venv\Scripts\Activate.ps1`
4. `pip install -r scripts\requirements.txt`
5. `npm run nfc-bridge`
6. `npm run dev` and `php artisan serve`

Optional `.env` value:

- `NFC_SOCKET_URL=http://127.0.0.1:8001`

### Browsershot / PDF Generation Errors

If you see errors related to `Browsershot` or `node` failing:

- **Windows PATH**: Ensure Node.js is in your System PATH.
- **Node Path**: If standard detection fails, add `NODE_BINARY_PATH` to your `.env` pointing to your node.exe (e.g., `C:\Program Files\nodejs\node.exe`).
- **Chrome Path**: If Browsershot can't find Chrome, you may need to use `.setChromePath()` or set an environment variable.

### Reverb / Real-time Notifications

- Ensure `php artisan reverb:start` is running if you need real-time updates.

### Bataeno Pass 401 Unauthorized (Token Issue)

If you get a 401 error during login, it's usually an OAuth mismatch.

1. **Redirect URI**: In your `.env`, `BATAENO_REDIRECT_URI` must match **exactly** what is registered in the Bataeno Pass Developer Portal (including `http://` and the port).
2. **Client Credentials**: Verify `BATAENO_PASS_CLIENT_ID` and `BATAENO_PASS_CLIENT_SECRET`.
3. **Port Mismatch**: If you run on `localhost:8001` but your registered URI is `localhost:8000`, the token exchange will fail. Ensure your `APP_URL` and `php artisan serve` port match your portal settings.

### cURL error 77 (SSL Issue)

On Windows, PHP often can't find a valid SSL certificate bundle for API calls.

1. Download `cacert.pem` from [curl.se/ca/cacert.pem](https://curl.se/ca/cacert.pem).
2. Save it to your PHP folder.
3. Edit `php.ini` and set:
    - `curl.cainfo = "C:\path\to\cacert.pem"`
    - `openssl.cafile = "C:\path\to\cacert.pem"`
4. Restart your PHP server.

### Permission Denied

- On Linux/Mac: Run `chmod -R 775 storage bootstrap/cache`.
- On Windows: Usually not required unless using WSL.
