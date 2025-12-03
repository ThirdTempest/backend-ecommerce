Project Setup Guide
This guide provides step-by-step instructions on how to set up and run the E-SHOP application (Backend & Frontend) locally after cloning the repository from GitHub.

1. Prerequisites
Ensure you have the following installed on your machine:

PHP (v8.2 or higher)
Composer (Dependency Manager for PHP)
Node.js (v18 or higher) & NPM
MySQL (Database)
Git
2. Clone the Repository
git clone <your-repo-url>
cd <your-repo-folder>
3. Backend Setup (Laravel)
The backend is located in the 
Ecomercebackend
 directory.

Step 1: Navigate to Backend Directory
cd Ecomercebackend
Step 2: Install Dependencies
composer install
Step 3: Configure Environment
Copy the example environment file:
cp .env.example .env
Open 
.env
 and configure your database settings:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_db  # Make sure this database exists in MySQL
DB_USERNAME=root          # Your MySQL username
DB_PASSWORD=              # Your MySQL password
Configure Email (Required for OTP):
Option A (Resend - Recommended):
MAIL_MAILER=resend
RESEND_KEY=re_123456... # Get from resend.com
MAIL_FROM_ADDRESS=onboarding@resend.dev
Option B (Log - For offline testing):
MAIL_MAILER=log
Step 4: Generate App Key
php artisan key:generate
Step 5: Run Migrations
Create the database tables:

php artisan migrate
Step 6: Start the Server
php artisan serve
The backend will run at http://127.0.0.1:8000.