# Service App

This project is configured to run entirely via Docker Compose.

## Services Included
- **PHP 8.2** (Backend - Laravel on port 8000)
- **Bun** (Frontend - Vue/Vite on port 5173)
- **PostgreSQL 15** (Database on port 5432)
- **MailHog** (Local Mail Server on port 8025 for UI, port 1025 for SMTP)

## Prerequisites
- Docker & Docker Compose

## Getting Started

1. **Environment Configuration**
   Copy `.env.example` to `.env` (if you haven't already). In your `.env` file, make sure your configurations point to the Docker containers to enable internal networking:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=pgsql
   DB_PORT=5432
   DB_DATABASE=service_app
   DB_USERNAME=postgres
   DB_PASSWORD=P@ssw0rd123

   MAIL_MAILER=smtp
   MAIL_HOST=mailhog
   MAIL_PORT=1025
   ```
   *(Note: Jika Anda ingin tetap menjalankan `php artisan serve` di host lokal alih-alih menggunakan Docker untuk PHP, biarkan `DB_HOST` dan `MAIL_HOST` bernilai `127.0.0.1`)*

2. **Starting the Services**
   Jalankan perintah berikut untuk mengunduh image dan menjalankan semua service di background:
   ```bash
   docker-compose up -d
   ```

3. **Install Dependencies & Database Migrations**
   *(Hanya jika sebelumnya Anda belum punya vendor folder di backend, dan untuk memastikan db termigrasi ke postgre dalam Docker)*
   ```bash
   docker exec -it service-app-php composer install
   docker exec -it service-app-php php artisan migrate --seed
   ```

4. **Accessing the App**
   - **Backend API:** [http://localhost:8000](http://localhost:8000)
   - **Frontend UI:** [http://localhost:5173](http://localhost:5173)
   - **MailHog UI:** [http://localhost:8025](http://localhost:8025)

## Stopping the Services
Untuk menghentikan semua container dan menghapusnya:
```bash
docker-compose down
```
Jika hanya ingin stop sementara tanpa down:
```bash
docker-compose stop
```
