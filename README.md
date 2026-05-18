# Uptime Monitor API

An API-based site uptime monitor built with Laravel 13. This application allows users to register URLs to monitor, checks them periodically, and sends notifications when their status changes.

## Tech Stack
- **Framework**: Laravel 13.x
- **PHP Version**: 8.4+ (Works on 8.3.31 too)
- **Database**: MySQL

## Setup Instructions

1. **Clone the repository**:
   ```bash
   git clone git@github.com:Sunny-Ojo/site-uptime-monitor.git
   cd site-uptime-monitor
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Environment Configuration**:
   Copy the example environment file:
   ```bash
   cp .env.example .env
   ```

4. **Create Databases**:
   You need to create two databases in MySQL:
   - One for the application (e.g., `uptime_site_monitor`).
   - One for running tests (exactly `uptime_site_monitor_test`).
   
   ```sql
   CREATE DATABASE uptime_site_monitor;
   CREATE DATABASE uptime_site_monitor_test;
   ```
   
   Update your `.env` file with the credentials for the main database:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=uptime_site_monitor
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

6. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

## API Endpoints & Authentication

All monitor endpoints are protected and require a Bearer token so as to ensure users fetches only their own data.

### Required Headers

Include the following headers in all requests to protected endpoints:

- `Authorization`: `Bearer <your_token>`
- `Accept`: `application/json`
- `Content-Type`: `application/json`

### Auth Endpoints
- **POST** `/api/register`: Register a new user. Returns a token.
- **POST** `/api/login`: Login with existing credentials. Returns a token.
- **POST** `/api/forgot-password`: Send password reset link.
- **POST** `/api/reset-password`: Reset password using token.

### Monitor Endpoints
- **GET** `/api/monitors`: List all monitors for the authenticated user.
- **POST** `/api/monitors`: Create a new monitor.
- **GET** `/api/monitors/{id}/history`: Fetch check history for a specific monitor.

## Running the Monitor Checks
To manually trigger the monitor checks, run the following Artisan command:
```bash
php artisan monitor:check
```

## Running the Queue
To run the queue, run the following Artisan command:
```bash
php artisan queue:work
```

## Running Tests
To run the feature and unit tests:
```bash
php artisan test
```

## Architectural Choices
*   **Database Choice (MySQL)**: I chose MySQL because the `due()` scope on the `Monitor` model relies on the native `TIMESTAMPDIFF` function. This allows us to calculate which monitors are due directly at the database level, ensuring high performance as the number of monitors grows.
