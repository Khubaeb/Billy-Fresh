# Setting Up Billy with XAMPP

This guide provides specific instructions for setting up the Billy project using XAMPP on Windows.

## Prerequisites

- XAMPP installed with PHP 8.1+ (see [Windows Environment Setup Guide](windows_env_setup.md))
- Composer installed
- Node.js and npm installed
- Git installed

## Project Setup Steps

### 1. Clone the Repository

Clone the repository into your XAMPP's htdocs directory:

```bash
cd C:\xampp\htdocs
git clone https://github.com/your-username/billy.git
cd billy
```

### 2. Install Dependencies

Install PHP dependencies using Composer:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

### 3. Configure Environment

Copy the example environment file:

```bash
copy .env.example .env
```

Generate an application key:

```bash
php artisan key:generate
```

### 4. Create Database

1. Start XAMPP Control Panel and ensure MySQL service is running
2. Open phpMyAdmin by navigating to http://localhost/phpmyadmin in your browser
3. Create a new database called `billy`:
   - Click on "New" in the left sidebar
   - Enter "billy" as the database name
   - Click "Create"

### 5. Update Environment Configuration

Edit the `.env` file and set the database connection details:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=billy
DB_USERNAME=root
DB_PASSWORD=
```

By default, XAMPP's MySQL installation has no password for the root user.

### 6. Run Migrations

Run the database migrations to create all necessary tables:

```bash
php artisan migrate
```

### 7. Compile Assets

Compile the frontend assets:

```bash
npm run dev
```

### 8. Run the Application

#### Option 1: Using Built-in Development Server

Run the Laravel built-in development server:

```bash
php artisan serve
```

Visit http://localhost:8000 in your browser.

#### Option 2: Using XAMPP's Apache (Virtual Host)

For a more production-like environment, you can set up a virtual host in Apache:

1. Open the Apache configuration file:
   ```
   C:\xampp\apache\conf\extra\httpd-vhosts.conf
   ```

2. Add a new VirtualHost directive:
   ```apache
   <VirtualHost *:80>
       DocumentRoot "C:/xampp/htdocs/billy/public"
       ServerName billy.local
       <Directory "C:/xampp/htdocs/billy/public">
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

3. Add the domain to your hosts file:
   - Open Notepad as Administrator
   - Open the file: `C:\Windows\System32\drivers\etc\hosts`
   - Add this line: `127.0.0.1 billy.local`
   - Save the file

4. Restart Apache in XAMPP Control Panel

5. Visit http://billy.local in your browser

## Troubleshooting

### Common Issues

1. **Unable to connect to MySQL**
   - Ensure MySQL service is running in XAMPP Control Panel
   - Check that the database credentials in `.env` match your configuration

2. **File permissions issues**
   - Make sure the web server has write permissions for the `storage` and `bootstrap/cache` directories
   - You may need to run: `icacls "C:\xampp\htdocs\billy\storage" /grant Everyone:(OI)(CI)F /T`

3. **500 Server Error**
   - Check the Laravel log at `storage/logs/laravel.log`
   - Check Apache error logs at `C:\xampp\apache\logs\error.log`

4. **"No input file specified" error**
   - Ensure the `.htaccess` file exists in the public directory
   - Make sure `mod_rewrite` is enabled in Apache
