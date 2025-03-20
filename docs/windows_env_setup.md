# Windows Development Environment Setup

This guide covers how to set up a development environment for Laravel on Windows using XAMPP.

## Required Software

1. **XAMPP** (includes PHP, MySQL, Apache)
2. **Composer**
3. **Node.js and npm**
4. **Git**

## Installation Instructions

### 1. Install XAMPP

1. Download XAMPP from the [official website](https://www.apachefriends.org/download.html)
   - Choose the version with PHP 8.1+ for Laravel compatibility

2. Run the installer:
   - Follow the installation wizard
   - Install XAMPP to the default location (`C:\xampp`) or a location of your choice
   - Select components (at minimum, you need Apache, PHP, MySQL, and phpMyAdmin)

3. Start XAMPP Control Panel:
   - Launch the XAMPP Control Panel
   - Start Apache and MySQL services by clicking the "Start" buttons next to them
   - These services need to be running whenever you're working on your Laravel project

4. Verify installations:
   - Open a browser and navigate to `http://localhost/`
   - You should see the XAMPP welcome page
   - Visit `http://localhost/phpmyadmin` to verify MySQL and phpMyAdmin are working

5. Configure PHP (if needed):
   - The PHP configuration file is located at `C:\xampp\php\php.ini`
   - Ensure the following extensions are enabled (no semicolon at the beginning of the line):
     ```
     extension=curl
     extension=fileinfo
     extension=mbstring
     extension=openssl
     extension=pdo_mysql
     extension=pdo_sqlite
     extension=sqlite3
     ```

### 2. Install Composer

1. Download the Composer installer from the [Composer website](https://getcomposer.org/download/)
   - Click "Composer-Setup.exe" to download the installer

2. Run the installer:
   - Follow the installation wizard
   - When prompted to select the PHP executable, choose the one from your XAMPP installation
     (e.g., `C:\xampp\php\php.exe`)

3. Verify Composer installation:
   - Open a new Command Prompt
   - Run `composer --version`
   - You should see Composer version information

### 4. Install Node.js and npm

1. Download Node.js from the [Node.js website](https://nodejs.org/)
   - Choose the LTS (Long Term Support) version

2. Run the installer:
   - Follow the installation wizard
   - Accept the default settings

3. Verify Node.js and npm installation:
   - Open a new Command Prompt
   - Run `node --version` and `npm --version`
   - You should see version information for both

### 5. Install Git

1. Download Git from the [Git website](https://git-scm.com/download/win)

2. Run the installer:
   - Follow the installation wizard
   - Accept the default settings or customize as needed

3. Verify Git installation:
   - Open a new Command Prompt
   - Run `git --version`
   - You should see Git version information

## Setting Up the Laravel Project

Once all the required software is installed, you can set up the Laravel project:

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd billy
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Copy the environment file:
   ```bash
   copy .env.example .env
   ```

4. Generate an application key:
   ```bash
   php artisan key:generate
   ```

5. Configure the database connection in the `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=billy
   DB_USERNAME=root
   DB_PASSWORD=your_mysql_root_password
   ```

6. Create the database:
   - Open MySQL Workbench or MySQL Command Line Client
   - Log in with your root password
   - Run the following SQL command:
     ```sql
     CREATE DATABASE billy;
     ```

7. Run migrations:
   ```bash
   php artisan migrate
   ```

8. Install frontend dependencies:
   ```bash
   npm install
   ```

9. Compile assets:
   ```bash
   npm run dev
   ```

10. Start the development server:
    ```bash
    php artisan serve
    ```

11. Visit `http://localhost:8000` in your web browser to see the application

## Debugging Tools

For a better development experience, consider installing the following tools:

### 1. VS Code Extensions

- PHP Intelephense
- Laravel Blade Snippets
- Laravel Snippets
- Laravel Extra Intellisense
- PHP Debug

### 2. Laravel Debugbar

1. Install via Composer:
   ```bash
   composer require barryvdh/laravel-debugbar --dev
   ```

2. Publish the configuration:
   ```bash
   php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"
   ```

### 3. Configuring Xdebug (Optional)

For step debugging, Xdebug can be installed:

1. Download the appropriate Xdebug DLL from [Xdebug website](https://xdebug.org/download)
2. Place the DLL in your PHP `ext` directory
3. Add the following to your `php.ini`:
   ```
   [Xdebug]
   zend_extension=xdebug
   xdebug.mode=debug
   xdebug.start_with_request=yes
   xdebug.client_port=9003
   ```
4. Restart your web server

## Troubleshooting

### Common Issues

1. **"php" is not recognized as an internal or external command**
   - Make sure PHP is correctly added to your PATH
   - Open a new Command Prompt after modifying the PATH

2. **MySQL connection errors**
   - Verify MySQL is running
   - Check your `.env` file for correct database credentials
   - Make sure the database exists

3. **Composer memory limit errors**
   - Add `COMPOSER_MEMORY_LIMIT=-1` before your Composer command
   - Example: `COMPOSER_MEMORY_LIMIT=-1 composer install`

4. **Permission issues**
   - Run Command Prompt or PowerShell as Administrator

5. **Node.js errors during npm install**
   - Clear npm cache: `npm cache clean --force`
   - Delete the `node_modules` directory and run `npm install` again
