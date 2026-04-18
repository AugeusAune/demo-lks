# Service App

A full-stack web application with a Laravel backend and a Vue/Vite frontend (powered by Bun), completely containerized using Docker Compose for a seamless development experience.

## 🚀 Technologies Used

- **Backend:** PHP 8.2 (Laravel Framework)
- **Frontend:** Bun, Vue.js, Vite
- **Database:** PostgreSQL 15
- **Mail Server:** MailHog (Local development SMTP/UI)
- **Containerization:** Docker & Docker Compose

## 📦 Services Overview

The local environment is configured with the following Docker containers:
- **`app` (`service-app-php`)**: Laravel backend running on port `8000`
- **`frontend` (`service-app-bun`)**: Vue/Vite frontend running on port `3000`
- **`pgsql` (`service-app-pgsql`)**: PostgreSQL database running on port `5432`
- **`mailhog` (`service-app-mailhog`)**: Local email testing interface on port `8025`, SMTP on `1025`

## 🛠️ Prerequisites

Make sure you have the following installed on your machine:
- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

## 🏁 Getting Started

Follow these steps to set up the project locally:

### 1. Environment Configuration

Copy the example environment file in the root directory (Backend environment variables):

```bash
cp .env.example .env
```

Ensure your `.env` file is configured to communicate with the Docker containers. The database and email variables should be set to coordinate through Docker's internal network:

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

### 2. Start the Docker Services

Run the following command to build the images, start the containers, and run them in the background:

```bash
docker-compose up -d
```

### 3. Install Dependencies & Setup Database

Once the containers are running, you need to install the PHP dependencies and migrate the database. We execute these commands directly inside the backend container:

```bash
# Install PHP vendor dependencies
docker exec -it service-app-php composer install

# Run database migrations and seed default data
docker exec -it service-app-php php artisan migrate --seed
```

*(Note: Dependencies untuk Frontend sudah otomatis di-install oleh Docker `bun install` saat container `frontend` dijalankan.)*

### 4. Access the Application

Once everything is set up and running smoothly, you can access the various services via your browser:

- 🌐 **Frontend UI:** [http://localhost:3000](http://localhost:3000)
- ⚙️ **Backend API:** [http://localhost:8000](http://localhost:8000)
- 📧 **MailHog UI:** [http://localhost:8025](http://localhost:8025)
- 🗄️ **Database:** `localhost:5432` (Connect via tools like DBeaver or pgAdmin using the credentials from your `.env`)
---

## 🔀 Application Flow (Alur Aplikasi)

Aplikasi ini memiliki beberapa alur utama berdasarkan peran (role) pengguna:

### 1. 🌐 Customer (Public Flow)
- **Cek Status Service / Tracking:** Pelanggan tidak perlu login (public). Mereka dapat langsung melacak status layanan perbaikan (service) barang mereka dengan mengakses fitur tracking dan memasukkan nomor struk/order.

### 2. 👨‍💼 Admin & Cashier Flow
- **Dashboard:** Melihat ringkasan data.
- **Penerimaan Barang (Transaksi Baru):** Kasir/Admin membuat transaksi penerimaan servis, memilih kategori produk/servis yang dibutuhkan, dan mendaftarkan keluhan/kerusakan.
- **Assign Teknisi:** Menugaskan pekerjaan perbaikan ke spesifik teknisi.
- **Berita Acara & Invoice:** Mencetak tanda terima, invoice, atau laporan kerusakan.
- **Master Data (Admin Only):** Admin memiliki akses penuh untuk melakukan CRUD pada data *Users*, *Products*, dan penghapusan *Transactions*.

### 3. 🛠️ Technician Flow
- **Daftar Pekerjaan:** Teknisi login ke sistem. Mereka hanya akan melihat daftar pekerjaan (transaksi perbaikan) yang telah ditugaskan (assigned) untuk mereka.
- **Update Status Servis:** Selama pengerjaan, teknisi akan selalu mengupdate progress status (contoh: *Dalam Antrean*, *Sedang Dikerjakan*, *Menunggu Sparepart*, *Selesai*).
- **Email Notifications:** Setiap update status penting atau tagihan terkait operasional servis akan terintegrasi dengan pengiriman email otomatis.

---

## 🛑 Managing the Services

**To view logs of a specific service:**
```bash
docker-compose logs -f frontend
docker-compose logs -f app
```

**To stop all containers temporarily:**
```bash
docker-compose stop
```

**To restart containers:**
```bash
docker-compose restart
```

**To completely shut down and remove the containers, networks, and volumes:**
```bash
docker-compose down
```
*(Warning: This will not delete the database data because it is mapped to a named volume. To remove volumes as well, you can run `docker-compose down -v`)*

## ⌨️ Common Operations / Commands

Here are some frequent commands you might need during development, run through the Docker containers:

**Backend (Laravel)**
```bash
# Clear caches
docker exec -it service-app-php php artisan optimize:clear

# Generate a new app key
docker exec -it service-app-php php artisan key:generate

# Enter the backend bash prompt directly
docker exec -it service-app-php bash
```

**Frontend (Bun)**
```bash
# Enter the frontend bash prompt to install new packages, etc.
docker exec -it service-app-bun bash

# Alternatively, run bun commands directly
docker exec -it service-app-bun bun install <package-name>
```
