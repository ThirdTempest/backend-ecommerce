🇵🇭 E-SHOP


✨ Key Features

Tailwind CSS V3: Fully responsive design with Dark Mode support across all pages (class strategy).

Secure Authentication: User registration includes mandatory OTP Email Verification before login.

Role Management: Segregated access for Admin users (is_admin=1).

Checkout Flow: Multi-step cart management, shipping information collection, and integration readiness for PayMongo Checkout Sessions.

Product Management (Admin): Full CRUD for products, including real file storage logic (saving the image_url path) and inventory monitoring.

Dynamic UI: Home page features image placeholders for rotating banners and dynamic product sections (New Arrivals, Sale).

🛠️ Installation and Setup

Prerequisites

PHP (8.2+)

Composer

MySQL Database (or equivalent supported by Laravel)

composer install

cp .env.example .env

php artisan key:generate

* set up your .env [ DATABASE, MAILER, PAYMONGO API AND WEBHOOK]

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=[DATABASE]
DB_USERNAME=[USERNAME]
DB_PASSWORD=[PASSWORD]

MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=[GMAIL]
MAIL_PASSWORD=[GMAILPASSWORD]
MAIL_FROM_ADDRESS=[GMAIL]
MAIL_FROM_NAME="${APP_NAME}"

# PayMongo
PAYMONGO_PUBLIC_KEY=[PUBLICAPI]
PAYMONGO_SECRET_KEY=[SECRETAPI]
PAYMONGO_WEBHOOK_SECRET= [WEBHOOKURL]       

# Payments config
CURRENCY=PHP
PAYMONGO_PAYMENT_METHOD_TYPES=gcash,paymaya,card

import the ecommerce.sql into database.

php artisan storage:link

php artisan serve


