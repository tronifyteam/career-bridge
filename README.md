# Migrant Work TW — Setup Guide for Hostinger VPS

## Quick Start (Local Development)

```bash
# Clone the repository
git clone https://github.com/ArtaRizki/migrant_work_tw_be.git
cd migrant_work_tw_be

# Install dependencies
composer install

# Copy environment file
cp .env.example .env
php artisan key:generate

# Create SQLite database & seed
touch database/database.sqlite
php artisan migrate --seed

# Start the server
php artisan serve
```

**API Base URL:** `http://localhost:8000/api`
**Admin Panel:** `http://localhost:8000/admin`

---

## Test Accounts

| Role         | Email              | Password     |
|:-------------|:-------------------|:-------------|
| Worker       | worker@2ne5.tw     | Password123!  |
| Company      | company@2ne5.tw    | Password123!  |
| Factory      | factory@2ne5.tw    | Password123!  |
| Family Care  | care@2ne5.tw       | Password123!  |
| Agency       | agency@2ne5.tw     | Password123!  |

---

## API Endpoints

### Auth
| Method | Endpoint                | Auth | Description           |
|:-------|:------------------------|:-----|:----------------------|
| POST   | /api/auth/register      | ❌   | Register new user     |
| POST   | /api/auth/login         | ❌   | Login & get token     |
| POST   | /api/auth/forgot-password | ❌ | Request password reset |
| POST   | /api/auth/logout        | ✅   | Logout & revoke token |
| GET    | /api/auth/me            | ✅   | Get current user      |
| PUT    | /api/auth/role          | ✅   | Assign user role      |
| PUT    | /api/auth/profile       | ✅   | Save profile data     |

### Jobs
| Method | Endpoint                    | Auth | Description              |
|:-------|:----------------------------|:-----|:-------------------------|
| GET    | /api/jobs                   | ❌   | List jobs (search/filter)|
| GET    | /api/jobs/{id}              | ❌   | Get job details          |
| POST   | /api/jobs                   | ✅   | Create job (employer)    |
| PUT    | /api/jobs/{id}              | ✅   | Update job               |
| DELETE | /api/jobs/{id}              | ✅   | Delete job               |

### Applications
| Method | Endpoint                       | Auth | Description              |
|:-------|:-------------------------------|:-----|:-------------------------|
| POST   | /api/jobs/{id}/apply           | ✅   | Apply to a job           |
| GET    | /api/applications              | ✅   | My applications          |
| GET    | /api/jobs/{id}/applicants      | ✅   | Job applicants (employer)|
| PUT    | /api/applications/{id}/status  | ✅   | Update app status        |

### Chat
| Method | Endpoint                    | Auth | Description              |
|:-------|:----------------------------|:-----|:-------------------------|
| GET    | /api/chats                  | ✅   | List conversations       |
| GET    | /api/chats/{userId}         | ✅   | Get messages             |
| POST   | /api/chats/{userId}         | ✅   | Send message             |
| PUT    | /api/chats/{msgId}/read     | ✅   | Mark as read             |

### Lookup & Dashboard
| Method | Endpoint                    | Auth | Description              |
|:-------|:----------------------------|:-----|:-------------------------|
| GET    | /api/categories             | ❌   | List job categories      |
| GET    | /api/cities                 | ❌   | List Taiwan cities       |
| GET    | /api/dashboard/worker       | ✅   | Worker dashboard stats   |
| GET    | /api/dashboard/employer     | ✅   | Employer dashboard stats |

### Auth Header
```
Authorization: Bearer {token}
```

---

## SQLite Database

The database file is located at `database/database.sqlite`.

### Open with DBeaver:
1. Open DBeaver → New Connection → SQLite
2. Path: `D:\INFORMATICS\FREELANCE\migrant_work_tw_be\database\database.sqlite`
3. Click "Test Connection" → "Finish"

---

## Hostinger VPS Deployment

### Prerequisites
- Hostinger VPS (KVM 8 or higher tier)
- Ubuntu 22.04 LTS
- PHP 8.2+ with extensions: sqlite3, mbstring, xml, curl, zip
- Composer
- Nginx
- Supervisor
- Git

### Step-by-step

```bash
# 1. SSH into your VPS
ssh root@your-vps-ip

# 2. Install dependencies
apt update && apt upgrade -y
apt install -y php8.2 php8.2-fpm php8.2-sqlite3 php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip nginx supervisor git unzip

# 3. Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# 4. Clone the project
cd /var/www
git clone https://github.com/ArtaRizki/migrant_work_tw_be.git
cd migrant_work_tw_be

# 5. Install dependencies
composer install --no-dev --optimize-autoloader

# 6. Setup environment
cp .env.production .env
php artisan key:generate

# 7. Create & seed database
touch database/database.sqlite
php artisan migrate --seed

# 8. Set permissions
chown -R www-data:www-data /var/www/migrant_work_tw_be
chmod -R 755 /var/www/migrant_work_tw_be
chmod -R 775 storage bootstrap/cache
chmod 664 database/database.sqlite

# 9. Configure Nginx
cp deploy/nginx.conf /etc/nginx/sites-available/migrant_work_tw_be
ln -s /etc/nginx/sites-available/migrant_work_tw_be /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx

# 10. Configure Supervisor
cp deploy/supervisor.conf /etc/supervisor/conf.d/migrant-worker.conf
supervisorctl reread && supervisorctl update

# 11. SSL Certificate (Let's Encrypt)
apt install -y certbot python3-certbot-nginx
certbot --nginx -d yourdomain.com -d www.yourdomain.com

# 12. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Future Deployments
```bash
cd /var/www/migrant_work_tw_be
bash deploy/deploy.sh
```

---

## Tech Stack

- **Framework:** Laravel 12
- **Auth:** Laravel Sanctum (token-based)
- **Database:** SQLite
- **Admin Panel:** Blade + Bootstrap 5
- **Server:** Nginx + PHP-FPM
- **Queue:** Database driver + Supervisor
