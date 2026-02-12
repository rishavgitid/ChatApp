# ChatApp

A real-time chat application built with Laravel 12, Laravel Reverb, Laravel Passport, and Bootstrap 5.

## Requirements

### System Requirements

- **PHP**: ^8.2 or higher
- **Composer**: Latest version
- **Node.js**: 18.x or higher
- **NPM**: 9.x or higher
- **Database**: SQLite (default) or MySQL/PostgreSQL
- **Web Server**: Apache/Nginx (for production)

## Dependencies

### PHP Dependencies (Composer)

**Production:**
- `laravel/framework` (^12.0) - The Laravel Framework
- `laravel/passport` (^13.4) - OAuth2 authentication
- `laravel/reverb` (^1.7) - Real-time WebSocket server

## Local Machine Setup

### 1. Clone the Repository

```bash
git clone https://github.com/rishavgitid/ChatApp.git
cd ChatApp
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the example environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Run Database Migrations

```bash
php artisan migrate
```

### 6. Install Laravel Passport

```bash
php artisan passport:install
```

This will create the encryption keys needed to generate secure access tokens.

### 9. Build Frontend Assets

```bash
npm run build
```

For development with hot-reload:

```bash
npm run dev
```




## Deployment

### Production Deployment Steps

#### 1. Server Requirements

Ensure your server meets the [system requirements](#requirements) listed above.

#### 2. Clone and Install

```bash
git clone https://github.com/rishavgitid/ChatApp.git
cd ChatApp
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

#### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

#### 4. Database Setup

```bash
php artisan migrate --force
php artisan passport:install --force
```

#### 5. Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

#### 6. Set Permissions

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 7. Configure Web Server

**For Nginx:**

```nginx
server {
    listen 80;
    server_name yourdomain.com;
 
    root /var/www/html/ChatApp/public;
    index index.php index.html index.htm;
 
    access_log /var/log/nginx/chatapp_access.log;
    error_log /var/log/nginx/chatapp_error.log;
 
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
 
    location /app/ {
        proxy_pass http://127.0.0.1:9000;
        proxy_http_version 1.1;
 
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
 
        proxy_read_timeout 60s;
        proxy_send_timeout 60s;
    }
 
 
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
 
    location ~ /\.ht {
        deny all;
    }
}
```

#### 9. Configuration reverb on server

```
https://laravel.com/docs/12.x/reverb#running-server
```
