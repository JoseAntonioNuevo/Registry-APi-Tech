# Registry API

This repository contains a RESTful API for a registry system. The API keeps track of a set of items and provides endpoints to manipulate and query it. The `.env` file has been included in the repository to simplify the setup and usage of the application.

## Table of Contents

- [Registry API](#registry-api)
  - [Table of Contents](#table-of-contents)
  - [Features](#features)
  - [Setup Instructions](#setup-instructions)
    - [Prerequisites](#prerequisites)
    - [Using Docker](#using-docker)
    - [Running the Application (Manually, without Docker)](#running-the-application-manually-without-docker)
  - [Running Tests](#running-tests)
    - [Common Testing Issues](#common-testing-issues)
  - [Endpoints Overview](#endpoints-overview)
  - [Notes on the `.env` File](#notes-on-the-env-file)

---

## Features

- **Check** if an item is in the registry.
- **Add** items to the registry.
- **Remove** items from the registry.
- Compare the registry with another set using **diff**.
- **Invert** the logic of the registry (invert the state of all checks).

---

## Setup Instructions

To make onboarding easier, the `.env` file has been provided. It includes all necessary configurations required for the application to run locally.

### Prerequisites

Ensure you have the following installed:

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)
- PHP (if running without Docker)
- Composer
- MySQL (if running without Docker)

### Using Docker

The application is pre-configured for Docker. To spin up the environment:

1. Build and start the Docker containers:

   ```bash
   docker-compose up --build
   ```

2. Once the services are up, you can access the application at:

   ```
   http://localhost:8000
   ```

3. The database service will also be running within Docker. Configuration for the MySQL database (username, password, etc.) is provided in the `.env` file.

### Running the Application (Manually, without Docker)

If you prefer running the application without Docker:

1. Install PHP dependencies:

   ```bash
   composer install
   ```

2. Configure the `.env` file:

   - The `.env` file already contains example configurations (uploaded for simplicity). Ensure the following fields are correctly set:
     - `DB_CONNECTION`: Database connection type (e.g., `mysql`).
     - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Database credentials.

3. Run the migrations to set up the database schema:

   ```bash
   php artisan migrate
   ```

4. Start the development server:

   ```bash
   php artisan serve
   ```

5. Access the application at:
   ```
   http://127.0.0.1:8000
   ```

---

## Running Tests

The application includes feature tests to verify the correctness of the API. To execute the tests:

1. Ensure the testing database is correctly configured in your `.env` file:

   ```
   DB_CONNECTION=mysql
   DB_DATABASE=testing_database
   DB_USERNAME=root
   DB_PASSWORD=password
   ```

2. Run the tests using PHPUnit:

   ```bash
   php artisan test
   ```

   Or:

   ```bash
   ./vendor/bin/phpunit
   ```

3. The test suite covers various scenarios, such as:
   - Adding items to the registry.
   - Checking item existence.
   - Removing items.
   - Comparing sets using `diff`.
   - Inverting the state of the registry.

### Common Testing Issues

- **Database-related errors**: Ensure the testing database is created and migrations are run in the testing environment:
  ```bash
  php artisan migrate --env=testing
  ```

---

## Endpoints Overview

| Method | Endpoint        | Description                            | Example Input / Output                                                               |
| ------ | --------------- | -------------------------------------- | ------------------------------------------------------------------------------------ |
| GET    | `/check/{item}` | Check if an item is in the registry.   | Input: `red`; Output: `{"message": OK}`                                              |
| POST   | `/add`          | Add an item to the registry.           | Input: `{"item": "yellow"}`; Output: `{"message": "OK"}`                             |
| DELETE | `/remove`       | Remove an item from the registry.      | Input: `{"item": "red"}`; Output: `{"message": "OK"}`                                |
| POST   | `/diff`         | Compare the registry with another set. | Input: `{"items": ["red", "green"]}`; Output: `{"message": "OK", "diff": ["green"]}` |
| PUT    | `/invert`       | Invert the registry logic.             | Output: `{"message": "OK"}`                                                          |

---

## Notes on the `.env` File

To simplify setup for new developers, the `.env` file has been included in the repository. It contains default configurations for the local environment, such as:

- Database credentials.
- Application key.
- Docker-based configurations.

**Security Note**: In a production environment, uploading the `.env` file is a security risk. Ensure it is excluded from version control in real-world scenarios by adding it to `.gitignore`.

---
