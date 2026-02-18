# OptiMine Fleet Management System — Fully Functional

Platform status: Fully functional and production-ready.

A comprehensive fleet management platform for mining operations, built with Laravel, Livewire, and PostgreSQL.

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Running the Application](#running-the-application)
- [Key Features Guide](#key-features-guide)
- [API Integration](#api-integration)
- [Project Structure](#project-structure)
- [Testing](#testing)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)
- [License](#license)

## 🎯 Overview

OptiMine is a modern fleet management system designed specifically for mining operations. It provides real-time tracking, maintenance scheduling, fuel management, geofencing, and comprehensive reporting capabilities for mining fleets.

### Key Capabilities

- **Real-time Fleet Tracking**: Monitor all machines with live GPS updates on interactive maps
- **Geofence Management**: Create and manage virtual boundaries for mine areas
- **Maintenance Scheduling**: Book and track preventive and corrective maintenance
- **Fuel Management**: Allocate fuel to mine areas and track consumption
- **Mine Area Management**: Define operational boundaries with 4-point coordinate systems
- **Comprehensive Reporting**: Generate detailed reports on fleet operations
- **Multi-tenant Architecture**: Team-based isolation with secure data access

## ✨ Features

### Fleet Management
- Live map view with satellite and standard map styles
- Real-time machine location tracking
- Machine health monitoring
- Activity status tracking (active, idle, maintenance, offline)
- Fleet movement replay with time-lapse visualization

### Maintenance Management
- 10 maintenance types: Preventive, Corrective, Predictive, Emergency, Routine, Inspection, Calibration, Overhaul, Breakdown, Seasonal
- Priority-based scheduling (Low, Medium, High, Critical)
- Cost estimation and tracking
- Required parts inventory management
- Technician notes and work history

### Fuel Management
- Mine area-based fuel allocation
- Fuel tank creation and management
- Consumption tracking and reporting
- Tank capacity monitoring
- Multi-tank support per mine area

### Geofencing
- Custom boundary creation with coordinate points
- Mine area association for each geofence
- Real-time boundary violation alerts
- Visual boundary display on maps

### Mine Area Management
- Precise 4-point coordinate boundary definition
- Interactive map-based drawing tools
- Visual feedback with hover markers and click animations
- Real-time coordinate preview
- Mine area naming and management

### Reporting
- Fleet activity reports
- Maintenance history reports
- Fuel consumption analysis
- Custom date range selection
- Export capabilities

## 🛠 Technology Stack

### Backend
- **Framework**: Laravel 10.x
- **Real-time Components**: Livewire 3.x
- **Database**: PostgreSQL 14+
- **Authentication**: Laravel Fortify + Jetstream
- **API**: Laravel Sanctum for API authentication

### Frontend
- **CSS Framework**: Tailwind CSS 3.x
- **UI Components**: DaisyUI
- **JavaScript**: Alpine.js (via Livewire)
- **Maps**: Leaflet 1.9.4
- **Build Tool**: Vite

### Development Tools
- **PHP**: 8.1+
- **Composer**: 2.x
- **Node.js**: 18.x+
- **npm**: 9.x+

## 📦 Requirements

- PHP >= 8.1
- PostgreSQL >= 14.0
- Composer >= 2.0
- Node.js >= 18.0
- npm >= 9.0
- Git

### PHP Extensions Required
- OpenSSL
- PDO
- PDO_PGSQL
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd mines
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Environment Configuration

```bash
cp .env.example .env
```

Edit `.env` file with your configuration:

```env
APP_NAME=OptiMine
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=optimine
DB_USERNAME=your_username
DB_PASSWORD=your_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

## 💾 Database Setup

### 1. Create PostgreSQL Database

```bash
createdb optimine
```

Or via PostgreSQL:

```sql
CREATE DATABASE optimine;
```

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. Seed Database (Optional)

```bash
php artisan db:seed
```

## 🏃 Running the Application

### Development Mode

Start the Laravel development server:

```bash
php artisan serve
```

In a separate terminal, start the Vite development server:

```bash
npm run dev
```

Access the application at: `http://localhost:8000`

### Production Build

Build assets for production:

```bash
npm run build
```

## 📚 Key Features Guide

### Live Map

Navigate to the Live Map to view real-time fleet locations:

- **Toggle Layers**: Show/hide machines and geofences
- **Map Styles**: Switch between Standard (OpenStreetMap) and Satellite (Esri World Imagery)
- **Machine Details**: Click on machine markers for detailed information
- **Auto-refresh**: Map updates automatically with latest data

### Mine Area Management

Create mine areas with precise boundaries:

1. Navigate to Mine Areas
2. Click "Create New Mine Area"
3. Enter mine area name
4. Enter exactly 4 coordinate points (latitude, longitude)
5. Use the drawing tool to visually place points on the map
6. Preview shows your boundary in real-time
7. Submit to save

**Drawing Mode Features**:
- Hover to preview marker placement
- Click to add points with visual feedback
- See instruction popup for guidance
- Points automatically connect to form boundary

### Geofence Management

Create geofences associated with mine areas:

1. Navigate to Geofences
2. Click "Create New Geofence"
3. Select the mine area for this geofence
4. Enter geofence name
5. Add coordinate points to define the boundary
6. Submit to save

### Maintenance Booking

Schedule maintenance for machines:

1. Navigate to Maintenance Dashboard
2. Click "Book Maintenance"
3. Select machine from dropdown
4. Choose maintenance type (10 options available)
5. Set priority level
6. Enter title and description
7. Schedule date and estimated duration
8. Add cost estimate and required parts
9. Include technician notes if needed
10. Submit to book

### Fuel Management

Allocate fuel to mine areas:

1. Navigate to Fuel Management
2. Select mine area from dropdown
3. View existing fuel tanks for that area
4. Click "Create New Tank" to add a tank
5. Enter tank name and capacity
6. Submit to create
7. Track fuel allocation and consumption

## 🔌 API Integration

### Authentication

The system uses Laravel Sanctum for API authentication. To access the API:

1. Obtain an API token from user settings
2. Include token in request headers:

```bash
Authorization: Bearer your-api-token-here
```

### Example Endpoints

```bash
# Get all machines
GET /api/machines

# Get machine details
GET /api/machines/{id}

# Create geofence
POST /api/geofences

# Get maintenance records
GET /api/maintenance-records
```

### External API Integration

The system supports external API integrations for:
- GPS tracking devices
- Machine telemetry systems
- Fuel monitoring systems

Contact your system administrator for API documentation and credentials.

## 📁 Project Structure

```
mines/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # API and web controllers
│   │   └── Livewire/         # Livewire components
│   ├── Models/               # Eloquent models
│   ├── Services/             # Business logic services
│   └── Policies/             # Authorization policies
├── config/                   # Configuration files
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── public/                   # Public assets
├── resources/
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript files
│   └── views/                # Blade templates
├── routes/
│   ├── web.php              # Web routes
│   ├── api.php              # API routes
│   └── console.php          # Console routes
├── storage/                  # Storage for logs, cache, etc.
├── tests/                    # Test files
└── vendor/                   # Composer dependencies
```

## 🧪 Testing

Run the test suite:

```bash
php artisan test
```

Run specific test file:

```bash
php artisan test tests/Feature/MachineTest.php
```

Run with coverage:

```bash
php artisan test --coverage
```

## 🚢 Deployment

### Production Checklist

1. **Environment Configuration**
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Optimize Application**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Build Assets**
   ```bash
   npm run build
   ```

4. **Database Migration**
   ```bash
   php artisan migrate --force
   ```

5. **Set Permissions**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

6. **Configure Web Server**
   - Point document root to `public/` directory
   - Enable HTTPS with valid SSL certificate
   - Configure proper PHP-FPM settings

### Web Server Configuration

#### Nginx Example

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/optimine/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Apache Example

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/optimine/public

    <Directory /var/www/optimine/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## 🔧 Troubleshooting

### Common Issues

#### 1. Map Not Loading

**Problem**: Map tiles not appearing on Live Map page

**Solution**:
- Check internet connection for CDN access
- Verify Leaflet CDN is accessible
- Check browser console for JavaScript errors
- Clear browser cache

#### 2. Session Expired Errors

**Problem**: Users getting logged out frequently

**Solution**:
```bash
# Check session configuration
php artisan config:clear
php artisan cache:clear

# Verify SESSION_DRIVER in .env
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

#### 3. CSRF Token Mismatch

**Problem**: 419 errors on form submission

**Solution**:
- Clear application cache: `php artisan cache:clear`
- Clear config cache: `php artisan config:clear`
- Verify session is working properly
- Check that cookies are enabled in browser

#### 4. Database Connection Failed

**Problem**: Cannot connect to PostgreSQL

**Solution**:
```bash
# Verify PostgreSQL is running
sudo systemctl status postgresql

# Check database credentials in .env
# Test connection
php artisan tinker
> DB::connection()->getPdo();
```

#### 5. Permission Denied Errors

**Problem**: Storage or cache permission errors

**Solution**:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 6. Livewire Component Not Updating

**Problem**: Changes not reflecting in Livewire components

**Solution**:
```bash
php artisan livewire:discover
php artisan view:clear
php artisan config:clear
```

#### 7. Assets Not Loading in Production

**Problem**: CSS/JS files returning 404

**Solution**:
```bash
# Rebuild assets
npm run build

# Check public/build directory exists
# Verify asset paths in blade templates
```

### Getting Help

If you encounter issues not covered here:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server error logs
3. Enable debug mode temporarily: `APP_DEBUG=true`
4. Review the [Laravel Documentation](https://laravel.com/docs)
5. Check [Livewire Documentation](https://livewire.laravel.com/docs)

## 📄 License

This project is proprietary software. All rights reserved.

For licensing inquiries, contact: [your-contact@email.com]

---

**OptiMine Fleet Management System** - Built with ❤️ for Mining Operations

**Version**: 2.1  
**Last Updated**: February 18, 2026
