# Project Setup Guide

Follow the steps below to run this project locally.

## Requirements

Make sure you have installed:

* PHP
* Composer
* Node.js & npm
* MySQL (or any supported database)

---

## Installation Steps

### 1. Clone the repository

```bash
git clone <repository-url>
cd <project-folder>
```

---

### 2. Install PHP dependencies

```bash
composer install
```

---

### 3. Install Node dependencies

```bash
npm install
```

---

### 4. Setup Environment File

Rename the environment file:

```bash
cp .env.example .env
```

Or manually rename `.env.example` to `.env`

---

### 5. Generate Application Key

```bash
php artisan key:generate
```

---

### 6. Configure Database

Open the `.env` file and update your database credentials:

```
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Then run migrations (if applicable):

```bash
php artisan migrate --seed
```

---

### 7. Start the Development Server

In the first terminal:

```bash
php artisan serve
```

In a second terminal:

```bash
npm run dev
```

---

## Project Features

* Admin login
* Admin can edit records
* Admin can block students
* Student login

---

The project should now be running at:

```
http://127.0.0.1:8000
```
