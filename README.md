
# Laravel + Docker Project Setup Documentation

This guide helps you run the Laravel project with Docker and Docker Compose efficiently.

## Prerequisites

* [Docker](https://www.docker.com/products/docker-desktop/) installed
* [Docker Compose](https://docs.docker.com/compose/install/) installed
* .env file set up (see .env.example)

# Project Structure (Simplified)

## Project Structure

```bash
├── app/
│   ├── Console/
|   ├── DTO/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │       └── Api/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Jobs/
│   ├── Models/
│   ├── Providers/
├── ├── Repositories/
│   └── Article/
│   └── Services/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docker/
│   ├── nginx/
│   ├── php/
│   └── mysql/
├── resources/
│   ├── js/
│   ├── sass/
│   └── views/
├── routes/
│   ├── api.php
│   ├── web.php
│   └── console.php
├── tests/
│   ├── Feature/
│   └── Unit/
├── 
```


## 1. Build and Start the Containers

Install my-project with npm

```bash
docker compose up -d --build
```
This will:
* Build the PHP, Nginx, MySQL, and Redis containers
* It will migrate and seed the database
* Start the application at http://localhost:8000
## 2. Run Laravel Setup Commands

Run this once after the containers are up:
```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```


## 3. Database Access

```bash
Host: 127.0.0.1
Port: 3306
Username: root
Password: (check your .env or docker-compose)
Database: news_aggregator_api
```


## 4. Running Scheduled Jobs (cron)

To run Laravel’s scheduled tasks:

```bash
docker compose exec app php artisan schedule:run
```

## 5. Clear Cache (When Needed)

What optimizations did you make in your code? E.g. refactors, performance improvements, accessibility

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
```

## Running Tests

```bash
docker compose exec app php artisan test
```
