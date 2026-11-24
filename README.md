# Badminton Court Booking (Booking GOR)

A web app for booking badminton courts by the hour. Customers can see which time slots are free and book on their own through the website. Admins manage the whole schedule in one place, including bookings that come in through WhatsApp.

Built as a portfolio project with Laravel, based on how a real badminton hall works: bookings are made through WhatsApp and payment is made in person at the venue.

## Problem it solves

At the hall used as a reference, customers had to message the admin on WhatsApp to find out which time slots were free. The schedule was recorded by hand, so mistakes and double bookings were easy to make. This app moves that process to a website without removing the existing habits (booking through WhatsApp and paying at the venue).

## Features

**Customers**
- View the list of courts and their schedules without logging in
- Register and log in, then book a court by the hour (1 to 4 hours)
- Booking detail page with a WhatsApp button to contact the admin
- "My Bookings" page, and self-cancellation up to 3 hours before the start time
- Mobile-friendly layout with a bottom menu

**Admin**
- Summary dashboard
- Manage courts: add, edit, activate or deactivate
- Manage bookings: filter, then confirm, cancel, mark as paid, completed, or no-show
- Manual bookings for orders that come in through WhatsApp or for customers who walk in
- Revenue reports by period, with Excel and PDF export

**System**
- Schedule conflict check, using a row lock on the court inside a database transaction, so two people who click at the same time cannot get the same slot
- Bookings that the admin has not confirmed within 2 hours are cancelled automatically by the scheduler (`bookings:expire`, runs every minute)

## Booking rules

| Rule | Value |
|---|---|
| Duration | 1 to 4 hours, on the hour (for example 19:00 to 21:00) |
| Advance booking | Up to 14 days ahead |
| Active bookings per customer | Maximum of 3 |
| Admin confirmation deadline | 2 hours (never later than the start time) |
| Customer cancellation deadline | More than 3 hours before the start time |
| Time zone | WITA (`Asia/Makassar`) |

## Tech stack

- Laravel 13 (PHP 8.3 or later)
- MySQL
- Bootstrap 5 via Laravel UI, built with Vite
- `maatwebsite/excel` for Excel export and `barryvdh/laravel-dompdf` for PDF export

## Running it locally

Requirements: PHP 8.3+, Composer, Node.js and npm, and MySQL.

```bash
git clone https://github.com/lailahyt/booking-gor.git
cd booking-gor

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Create an empty MySQL database (for example `booking_gor`), then update the database section of your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_gor
DB_USERNAME=root
DB_PASSWORD=
```

Then run the migrations, load the sample data, and build the frontend:

```bash
php artisan migrate
php artisan db:seed --class=BookingDemoSeeder
npm run build
php artisan serve
```

Open `http://localhost:8000`.

For automatic cancellation to work, run the scheduler in a separate terminal:

```bash
php artisan schedule:work
```

### Sample accounts

The sample data from `BookingDemoSeeder` contains 4 courts (Rp30,000 per hour, open 08:00 to 23:00) and two accounts:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@gor.test` | `password` |
| Customer | `pelanggan@gor.test` | `password` |

These accounts are for local testing only. Change the passwords before using the app for real.

## Project structure

```
app/Services/BookingService.php       Booking rules, conflict check, cancellation
app/Services/RevenueReportService.php Revenue report calculations
app/Console/Commands/                 The bookings:expire command
app/Http/Controllers/Admin/           Dashboard, courts, bookings, reports
resources/views/                      Blade views (customer and admin)
routes/web.php                        Application routes
```

## Status

The main features are finished and the app can be used with the pay-at-the-venue flow. Online payment (Midtrans sandbox) is planned as a separate stage.

## Author

Laila Hayati — [GitHub](https://github.com/lailahyt)
