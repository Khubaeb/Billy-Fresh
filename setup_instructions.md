# Billy Project Setup Instructions

Follow these steps to set up the Billy project with XAMPP:

## 1. Install Composer

First, you need to install Composer (if not already installed):

1. Download the Composer installer from [getcomposer.org](https://getcomposer.org/download/)
2. Run the installer, selecting XAMPP's PHP executable (C:/xampp/php/php.exe)
3. Verify installation by opening a new command prompt and running `composer --version`

## 2. Install PHP Dependencies

From the project directory (`C:/CODE/TechLogicx/Billy`), run:

```bash
composer install
```

This will create the vendor directory and install all required PHP packages.

## 3. Create the Database

1. Open phpMyAdmin at http://localhost/phpmyadmin
2. Click "New" in the left sidebar
3. Enter "billy" as the database name
4. Click "Create"

## 4. Generate Application Key

From the project directory, run:

```bash
php artisan key:generate
```

This will update your .env file with a secure application key.

## 5. Run Migrations

Set up the database tables by running:

```bash
php artisan migrate
```

This will create all the necessary database tables based on our migrations.

## 6. Install Frontend Dependencies (Optional for now)

If you want to compile the frontend assets:

```bash
npm install
npm run dev
```

## 7. Start the Development Server

Start the Laravel development server:

```bash
php artisan serve
```

Then visit http://localhost:8000 in your browser.

## Alternative: Using XAMPP's Apache

Instead of using `php artisan serve`, you can:

1. Create a symbolic link or copy the project to C:/xampp/htdocs/billy
2. Access the site via http://localhost/billy/public

## Next Steps

Once the application is running, you'll be able to:

1. Register a new user account
2. Log in to the dashboard
3. Explore the UI we've implemented for the various modules

Remember that we've currently only implemented the UI foundation, not the full functionality of each module.
