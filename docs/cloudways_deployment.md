# Cloudways Deployment Guide

This guide covers how to deploy the Billy application to Cloudways hosting platform.

## Prerequisites

1. A Cloudways account
2. SSH access to your Cloudways server
3. Git repository with SSH keys configured
4. Basic knowledge of the command line

## Initial Setup on Cloudways

### 1. Create a New Application

1. Log in to your Cloudways account
2. Click on the "+" button to add a new application
3. Select "Laravel" as the application type
4. Choose your server (DigitalOcean)
5. Configure the application settings:
   - Application Name: Billy
   - Application URL: your-domain.com (or use the default Cloudways domain)
6. Click "ADD APPLICATION" to create the application

### 2. Configure SSH Keys

If you haven't already set up SSH keys for your Cloudways server:

1. Navigate to "Server Management" > Your Server > "Settings & Packages" > "SSH Key Management"
2. Add your public SSH key
3. Make sure the key is also added to your Git repository for deployment access

## Deployment Process

### 1. Connect to the Server via SSH

```bash
ssh user@your-server-ip
```

Replace `user` and `your-server-ip` with your Cloudways server credentials.

### 2. Navigate to the Application Directory

```bash
cd applications/your-application-name/public_html
```

The default directory structure in Cloudways places Laravel applications in the `public_html` folder.

### 3. Set Up Git Repository

If this is your first deployment:

```bash
# Clear the existing directory
rm -rf *

# Clone your repository
git clone git@your-repository-url.git .
```

Make sure to include the trailing dot (`.`) to clone into the current directory.

### 4. Install Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 5. Set Up Environment File

```bash
cp .env.example .env
```

Now edit the `.env` file with your application's settings:

```bash
vi .env
```

Configure the following:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=your-application-url

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

You can find your database credentials in the Cloudways platform under "Application Management" > "Application Settings" > "Database Details".

### 6. Generate Application Key

```bash
php artisan key:generate
```

### 7. Set Proper Permissions

```bash
chmod -R 775 storage bootstrap/cache
```

### 8. Run Migrations

For a fresh installation:

```bash
php artisan migrate
```

### 9. Compile Assets

```bash
npm install
npm run build
```

### 10. Configure Cache

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 11. Set Up Cron Job for Scheduler (if needed)

Cloudways allows you to add cron jobs from the platform:

1. Navigate to "Server Management" > Your Server > "Settings & Packages" > "Cron Job Management"
2. Add a new cron job with the following command:
   ```
   php /home/master/applications/your-application-name/public_html/artisan schedule:run >> /dev/null 2>&1
   ```
3. Set the frequency to "Every Minute"

## Subsequent Deployments

For future updates to your application:

```bash
# Connect to the server
ssh user@your-server-ip

# Navigate to application directory
cd applications/your-application-name/public_html

# Pull latest changes
git pull

# Install dependencies (if changed)
composer install --no-dev --optimize-autoloader

# Run migrations (if database changed)
php artisan migrate

# Update assets (if frontend changed)
npm install
npm run build

# Clear and rebuild cache
php artisan optimize:clear
php artisan optimize
```

## Database Configuration on Cloudways

### Finding Database Credentials

1. Log in to your Cloudways account
2. Navigate to "Applications" > Your Application > "Application Settings"
3. Click on "Database Details"
4. You'll find the following information:
   - Database Name
   - Database Username
   - Database Password
   - Database Host (usually 127.0.0.1)

### Managing Databases

Cloudways provides phpMyAdmin for database management:

1. Navigate to "Applications" > Your Application > "Access Details"
2. Find the "Database" section
3. Click on the "Launch phpMyAdmin" button
4. Log in with your database credentials

## Troubleshooting

### Common Issues

1. **Permission Denied Errors**
   - Ensure proper file permissions: `chmod -R 775 storage bootstrap/cache`
   - Check that the web server user has write access to the required directories

2. **Database Connection Errors**
   - Verify database credentials in the `.env` file
   - Check if the database exists and is accessible

3. **Blank Page or 500 Error**
   - Check the Laravel logs: `storage/logs/laravel.log`
   - Check the server error logs: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
   - Temporarily enable debug mode in `.env` (APP_DEBUG=true) to see detailed error messages

4. **Git Clone/Pull Issues**
   - Verify SSH key configuration
   - Check repository access permissions

5. **Composer Memory Limit**
   - If Composer runs out of memory, use: `COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader`

### Getting Help

If you encounter issues not covered here:

1. Cloudways Support: Available via live chat or tickets
2. Cloudways Knowledge Base: https://support.cloudways.com/
3. Community Forums: https://community.cloudways.com/

## Security Considerations

1. **Keep APP_DEBUG=false in Production**
   - Debug mode exposes sensitive information when errors occur

2. **Secure Environment Files**
   - Never commit `.env` files to your repository
   - Restrict access to `.env` files on the server

3. **Regular Updates**
   - Keep Laravel and all packages updated
   - Run `composer update` regularly in your development environment and deploy the updated `composer.lock`

4. **Database Backups**
   - Set up automated backups in Cloudways
   - Navigate to "Server Management" > Your Server > "Backups"
   - Configure backup frequency and retention

## Performance Optimization

1. **Enable Caching**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Use Cloudways Breeze (Varnish)**
   - Navigate to "Server Management" > Your Server > "Settings & Packages" > "Advanced"
   - Enable Breeze for your application

3. **Configure Redis** (if needed)
   - Cloudways provides Redis out of the box
   - Update your `.env` file:
     ```
     CACHE_DRIVER=redis
     SESSION_DRIVER=redis
     QUEUE_CONNECTION=redis
     ```
   - Install the Redis PHP extension if not already available
