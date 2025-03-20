# Deployment Guide

This document outlines the deployment process for the Logicstudio clone system.

## Development Environment Setup

### Prerequisites
- PHP 8.1+
- Composer
- MySQL 8.0+
- Node.js and npm (for asset compilation)
- Git

### Local Development Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd project-directory
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

4. **Configure environment variables**
   - Database connection
   - App URL
   - Mail settings
   - File storage settings

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Run database migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed database with initial data**
   ```bash
   php artisan db:seed
   ```

8. **Install frontend dependencies**
   ```bash
   npm install
   ```

9. **Compile assets**
   ```bash
   npm run dev
   ```

10. **Start the development server**
    ```bash
    php artisan serve
    ```

## Staging Environment

### Server Requirements
- Web server (Nginx recommended)
- PHP 8.1+
- MySQL 8.0+
- Composer
- Node.js and npm
- Git

### Deployment Steps

1. **Prepare the server**
   - Install required software
   - Configure web server
   - Set up MySQL database
   - Configure file permissions

2. **Clone the repository**
   ```bash
   git clone <repository-url> /var/www/staging
   cd /var/www/staging
   ```

3. **Install dependencies**
   ```bash
   composer install --no-dev
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   ```
   - Update environment variables for staging

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Compile assets**
   ```bash
   npm run build
   ```

8. **Set proper permissions**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

9. **Configure web server**
   - Set up Nginx or Apache virtual host
   - Configure SSL certificates
   - Set up proper rewrites for Laravel

10. **Configure caching (optional)**
    ```bash
    php artisan config:cache
    php artisan route:cache
    ```

## Production Environment

### Server Requirements
Same as staging, with additional considerations:
- Higher resource allocation
- Regular backup strategy
- Monitoring system
- SSL certificates

### Deployment Steps

1. **Prepare the production server**
   - Same steps as staging, with production-grade configurations

2. **Clone the repository**
   ```bash
   git clone <repository-url> /var/www/production
   cd /var/www/production
   ```

3. **Switch to the production branch**
   ```bash
   git checkout production
   ```

4. **Install dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install
   ```

5. **Configure environment**
   ```bash
   cp .env.example .env
   ```
   - Update environment variables for production
   - Set APP_ENV=production
   - Set APP_DEBUG=false

6. **Generate application key**
   ```bash
   php artisan key:generate
   ```

7. **Run migrations**
   ```bash
   php artisan migrate --force
   ```

8. **Compile assets**
   ```bash
   npm run build
   ```

9. **Set proper permissions**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

10. **Configure web server**
    - Set up Nginx or Apache virtual host
    - Configure SSL certificates
    - Set up proper rewrites for Laravel

11. **Enable caching**
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

12. **Set up task scheduler**
    Add to crontab:
    ```
    * * * * * cd /var/www/production && php artisan schedule:run >> /dev/null 2>&1
    ```

## Deployment Workflow with Git

### Git Branching Strategy

1. **Main Branches**
   - `main`: Production-ready code
   - `develop`: Development code (feature-complete and tested)

2. **Supporting Branches**
   - `feature/*`: Feature development
   - `bugfix/*`: Bug fixes
   - `hotfix/*`: Urgent production fixes
   - `release/*`: Release preparation

### Workflow Process

1. **Development**
   - Create feature branch from develop
   - Implement and test feature
   - Create pull request to develop
   - Review and merge

2. **Staging Deployment**
   - Merge develop to staging branch
   - Deploy to staging server
   - Test in staging environment

3. **Production Deployment**
   - Create release branch from develop
   - Final testing and version bump
   - Merge to main
   - Tag with version number
   - Deploy to production

### Automated Deployment (Future Enhancement)

1. **CI/CD Pipeline Setup**
   - Configure GitHub Actions or similar CI/CD tool
   - Automate testing
   - Automate deployment to staging and production

2. **Deployment Script**
   ```bash
   #!/bin/bash
   # Example deployment script
   cd /var/www/production
   git pull origin main
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   npm install
   npm run build
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   # Reload services if needed
   ```

## Backup Strategy

### Database Backups

1. **Daily Backups**
   ```bash
   mysqldump -u user -p database_name > /path/to/backups/database_$(date +%Y%m%d).sql
   ```

2. **Backup Rotation**
   - Keep daily backups for a week
   - Keep weekly backups for a month
   - Keep monthly backups for a year

### File Backups

1. **User Uploads**
   - Regularly backup the storage directory
   ```bash
   rsync -avz /var/www/production/storage/app/public /path/to/backups/storage_$(date +%Y%m%d)/
   ```

2. **Application Files**
   - Backup the entire application directory periodically
   ```bash
   rsync -avz --exclude node_modules --exclude vendor /var/www/production /path/to/backups/app_$(date +%Y%m%d)/
   ```

## Monitoring and Maintenance

1. **Application Monitoring**
   - Set up Laravel Log monitoring
   - Configure error reporting

2. **Server Monitoring**
   - Monitor CPU, memory, and disk usage
   - Monitor database performance
   - Set up alerts for critical issues

3. **Regular Maintenance**
   - Apply security updates
   - Review and prune logs
   - Optimize database
   ```bash
   php artisan optimize:clear
   php artisan optimize
   ```

## Rollback Procedure

In case of deployment issues:

1. **Quick Rollback**
   ```bash
   cd /var/www/production
   git checkout <previous-tag>
   composer install --no-dev --optimize-autoloader
   php artisan optimize:clear
   php artisan optimize
   ```

2. **Database Rollback**
   ```bash
   php artisan migrate:rollback
   # Or restore from backup if needed
   ```

3. **Full Restoration**
   - Restore from the most recent backup
   - Restore database from backup
   ```bash
   mysql -u user -p database_name < /path/to/backups/database_backup.sql
   ```
