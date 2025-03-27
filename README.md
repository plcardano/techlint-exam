# Techlint Mid Backend Developer Practical Exam

# IP Address Management System

This repository contains a web-based IP address management solution that allows authenticated users to record IP addresses with labels and comments, perform CRUD operations, and maintain a secure audit log.

## Repository Structure

The project is split into three main components:

- **techlint-exam-laravel**: Backend API built with Laravel 12
- **techlint-exam-client**: Frontend client built with Vue 3 + Vite
- **Postman Collection**: API documentation and testing collection

## Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- npm or yarn
- MySQL 8.0+

## Backend Setup (techlint-exam-laravel)

1. Navigate to the backend directory:

   ```bash
   cd techlint-exam-laravel
   ```

2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Create a `.env` file by copying the example:

   ```bash
   cp .env.example .env
   ```

4. Configure your database connection in the `.env` file:

   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=techlint_exam
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Generate an application key:

   ```bash
   php artisan key:generate
   ```

6. Generate JWT secret:

   ```bash
   php artisan jwt:secret
   ```

7. Run the database migrations and seed the database:

   ```bash
   php artisan migrate --seed
   ```

8. Start the development server:
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000/api`.

## Frontend Setup (techlint-exam-client)

1. Navigate to the frontend directory:

   ```bash
   cd techlint-exam-client
   ```

2. Install JavaScript dependencies:

   ```bash
   npm install
   ```

3. Create a `.env` file for the frontend:

   ```bash
   cp .env.example .env
   ```

4. Configure the API endpoint in the `.env` file:

   ```
   VITE_API_BASE_URL=http://localhost:8000/api
   ```

5. Start the development server:
   ```bash
   npm run dev
   ```

The client application will be available at `http://localhost:5173`.

## Postman Collection

1. Import the Postman collection from the `Postman Collection` folder into your Postman application.
2. Set up an environment with the following variables:
   - `base_url`: `http://localhost:8000/api`
   - `token`: Leave empty initially (will be filled by the login request)

## Default Users

The database seeder creates two default users:

- **Admin User**

  - Email: admin@example.com
  - Password: password
  - Role: super-admin

- **Regular User**
  - Email: user@example.com
  - Password: password
  - Role: regular

## Features

- **Authentication**: Secure JWT authentication with refresh token support
- **IP Address Management**:
  - Create, read, update, and delete IP addresses
  - Add labels and comments to IP addresses
  - Regular users can only modify their own entries
  - Super-admins can modify or delete any entry
- **Audit Logging**:
  - Records all changes made by users
  - Tracks login/logout events
  - Monitors changes on IP addresses
  - Cannot be deleted by any user
  - Only accessible by super-admins
