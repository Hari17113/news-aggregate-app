# Setting Up Laravel with Docker

## Steps:

### 1. **Create a Laravel Project Using Composer**
First, you will need to create a new Laravel project using Composer. Composer will manage the project’s dependencies and help set up the Laravel framework with all necessary files.

### 2. **Install Docker Desktop**
Docker Desktop is necessary for running Docker containers on your local machine.

### 3. **Create a Dockerfile for PHP Setup**
In your project’s root directory, you have to create a `Dockerfile`. This file will define the PHP image, install PHP extensions required by Laravel, and set up Composer to handle dependencies.

### 4. **Create a Docker Compose File**
you have to create a `docker-compose.yml` file. This file defines the services required for the project, such as Nginx for web serving, MySQL for the database, Redis for caching and PHP server. 

### 5. **Define Nginx Configuration**
The Nginx web server will handle HTTP requests and forward them to the appropriate service. You will need to create a configuration file for Nginx. This configuration will be stored in a `docker/nginx/default.conf` file.

### 6. **Build and Start the Containers**

you have to build and start the Docker containers using ``` docker-compose up --build.``` command

### 7. **Access the Application Container**
After the containers are running, Run ``` docker-compose exec app bash ``` to ssh into the app container.

### 8. **Run Database Migrations**
Once SSH into the container, Run `php artisan migrate` to migrate all tables.

### 9. **Install Composer Dependencies**
Run `composer install` to install composer dependencies.

### 10. **Stop and Remove Containers**
Run `docker-compose down` to down all containers

# API documentation

Here is the postman documentation for news aggregator app - https://documenter.getpostman.com/view/24598531/2sAYBYgWRk 
