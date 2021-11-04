# Introduction
Small containerised product service with an HTTP API.

## Tech

Service is developed using:
- [Laravel] - An open-source PHP web framework
- [MySQL] - Open-source relational database management system
- [Nginx] - A web server that can also be used as a reverse proxy

| Plugin | Description | Link |
| ------ | ------ | ------ |
| Senctum | For user authentication | https://laravel.com/docs/8.x/sanctum
| GitHub | For seeding database from CSV files | https://github.com/Flynsarmy/laravel-csv-seeder

#### Docker
Easy to install and deploy in a Docker container. Use the following command examples from project root, modifying them to fit your particular use case.

- `docker-compose run --rm composer update`
- `docker-compose run --rm artisan migrate`

### Endpoints

- GET '/api/products' returns all available products data.
- Basic authentication for users at POST 'api/auth' endpoint.
- After authentication, user can see user data at GET 'api/user' endpoint.
- Authentiated user can view all products purchased by the user at GET '/api/user/products' endpoint.
- A product can be attach to authenticated user (if it's not already attached) at POST 'api/user/products'.
- A product can be removed from authenticated user at DELETE 'api/user/products'.

**Note: Postman collection is available in 'postman' directory.**

## setup

Clone repository

```sh
git clone https://github.com/mohsininayatkhan/user-products.git .
```
Build docker image

```sh
docker-compose build
```
Start containers

```sh
docker-compose up -d
```
Install dependencies using composer

```sh
docker-compose run --rm composer install
```
Run database migrations

```sh
docker-compose run --rm artisan migrate
```
Run seeders for data import from CSV files

```bash
docker-compose run --rm artisan db:seed
```
**Visit [localhost:8080](http://localhost:8080) after setup/installation**