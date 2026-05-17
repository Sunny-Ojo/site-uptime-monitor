# Uptime Monitor API

An API-based site uptime monitor built with Laravel 13. This application allows users to register URLs to monitor, checks them periodically, and sends notifications when their status changes.

## Tech Stack
- **Framework**: Laravel 13.x
- **PHP Version**: 8.4+ (Developed on 8.3.31)
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

## Running the Monitor Checks
To manually trigger the monitor checks, run the following Artisan command:
```bash
php artisan monitor:check
```
*In production, this command should be scheduled to run every minute using Laravel's scheduler.*

## Running Tests
To run the feature and unit tests:
```bash
php artisan test
```
