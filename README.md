<p align="center">
    <a href="#">
        <img src="./public/uploads/logo.png" width="200" alt="Acedemia logo">
    </a>
</p>

<h1 align="center">Acedemia School ERP Software</h1>

<p align="center">
    A complete school management and administration suite built on Laravel 11.
    Acedemia brings students, staff, classes, fees, exams, and communication
    together in one web platform.
</p>

## What Acedemia Covers

### People

- Student records, admissions, and session enrolment
- Staff profiles plus daily staff attendance
- Guardian linking and parent portal access
- Student, teacher, and staff ID cards with print support

### Teaching and Learning

- Smart routines for classes and exams, syllabus and assignments
- Lesson plans, course material, and e-learning content
- Online exams with admit cards and seat plans
- Question bank with tagging, difficulty levels, and quiz attempts

### Attendance

- Daily student and staff attendance registers
- QR code check-in and check-out for students, teachers, and devices
- Offline attendance capture with later sync
- Leave tracking and attendance reports

### Money

- Fee setup, collection, fines, and deposits
- General accounts: payments, receipts, contra, journals, fund transfers
- Chart of accounts with financial reports
- Payroll, payslips, and salary sheets

### Library and Shop

- Book catalogue with CSV import and barcode label printing
- Issue and return flow with member records and fine collection
- Campus shop inventory: stock, sales, purchases, and POS billing

### Communication

- Built-in SMS module with queued delivery
- In-app notifications and announcements
- Student feedback collection
- Dynamic frontend pages for the school website

### Certificates and Documents

- Testimonials, transfer certificates, and character certificates
- Recommendation and abroad letters
- Attendance certificates
- Result cards, transcripts, and merit lists

### Administration

- Roles and granular permissions for web and API
- Global and academic settings, master configuration
- Branch and institute management with data isolation
- User activity logs and demo-mode safeguards

## Technology

Acedemia is powered by Laravel 11 with a modular codebase. Each domain
(academics, exams, fees, library, and more) ships as its own module with
dedicated models, services, controllers, and views.

- Token and session authentication via Passport and Sanctum, with Breeze screens
- Spatie packages for permissions, media, and PDF output
- Maatwebsite Excel for CSV and spreadsheet import and export
- Barcode printing through milon/barcode
- Friendly alerts with realrashid/sweet-alert
- Telescope for local debugging and query insight

## Server Needs

| Requirement | Version |
|-------------|---------|
| PHP | 8.2 or newer |
| MySQL | 5.x or newer |
| Extensions | Fileinfo, GD2, JSON, OpenSSL, PDO, XML |

## Getting Started

1. Copy `.env.example` to `.env` and fill in the database credentials.
2. Install dependencies with `composer install` and `npm install`.
3. Generate the key: `php artisan key:generate`.
4. Run migrations and seeders: `php artisan migrate --seed`.
5. Build the frontend: `npm run build`.
6. Serve the app: `php artisan serve`.

## Key Packages

- `php: ^8.2`
- `browner12/helpers: ^3.6`
- `laravel/framework: ^11.9`
- `laravel/passport: ^12.0`
- `laravel/sanctum: ^4.0`
- `laravel/tinker: ^2.9`
- `maatwebsite/excel: ^3.1`
- `realrashid/sweet-alert: *`
- `spatie/laravel-medialibrary: ^11.0`
- `spatie/laravel-pdf: ^1.5`
- `spatie/laravel-permission: ^6.9`
