# QRScanner Backend - Setup poza K8s

## Wymagania
- PHP 8.2+
- MySQL 8.0+
- Composer
- Nginx lub Apache

## 1. Setup bazy danych

```sql
CREATE DATABASE qrscanner;
USE qrscanner;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('owner', 'technician', 'viewer') DEFAULT 'viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE devices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    manufacturer VARCHAR(255),
    serial_number VARCHAR(255) UNIQUE,
    install_date DATE,
    qr_code LONGTEXT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_serial (serial_number)
);

CREATE TABLE technical_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    device_id INT NOT NULL,
    user_id INT NOT NULL,
    record_date DATE NOT NULL,
    record_type ENUM('inspection', 'maintenance', 'repair', 'testing') NOT NULL,
    description TEXT NOT NULL,
    technician VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_device (device_id),
    INDEX idx_date (record_date)
);
```

## 2. Konfiguracja backendu

### 2a. .env w katalogu backend/
```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=qrscanner
DB_USER=root
DB_PASSWORD=KochamGabi14
JWT_SECRET=super-secret-jwt-key-at-least-32-characters-long-2026
APP_ENV=production
APP_DEBUG=false
```

### 2b. Instalacja zależności
```bash
cd backend
composer install
```

## 3. Uruchomienie na porcie 9944

### Opcja A: PHP Built-in Server (szybki test)
```bash
cd backend
php -S 0.0.0.0:9944 -t public
```

### Opcja B: Nginx + PHP-FPM (produkcja)

**1. Instalacja PHP-FPM:**
```bash
apt-get install php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-curl php8.2-gd
```

**2. Konfiguracja PHP-FPM** (`/etc/php/8.2/fpm/pool.d/qrscanner.conf`):
```ini
[qrscanner]
user = www-data
group = www-data
listen = 127.0.0.1:9000
listen.owner = www-data
listen.group = www-data
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
```

Restart:
```bash
systemctl restart php8.2-fpm
```

**3. Konfiguracja Nginx** (`/etc/nginx/sites-available/qrscanner`):
```nginx
server {
    listen 9944;
    server_name _;
    root /path/to/qrscanner/backend/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Enable:
```bash
ln -s /etc/nginx/sites-available/qrscanner /etc/nginx/sites-enabled/
nginx -t
systemctl restart nginx
```

## 4. Test

```bash
curl http://51.83.161.218:9944/api/health
```

Backend powinien być dostępny na: **http://51.83.161.218:9944/api**
