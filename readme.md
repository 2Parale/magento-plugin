# BusinessLeague Marketing for Magento (Internal Team Guide)

This repository contains the source code, tests, and CI/CD configuration for the BusinessLeague Marketing module for Magento2 / Adobe Commerce.  

---

## Table of Contents

- [Local Development with Docker](#local-development-with-docker)
- [Testing](#testing)
- [Code Quality & Linting](#code-quality--linting)

## Local Development with Docker

This project provides a ready-to-use Docker setup for local development and testing with Magento 2 / Adobe Commerce.

### Prerequisites
- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

### Setup Instructions

1. **Environment Variables**
   
   The Docker setup requires Magento Marketplace API keys, which you must provide using a `.env` file. There is a template available at `docker/.env.sample`. Copy it to create your own environment file:

   ```bash
   cp docker/.env.sample docker/.env
   ```
   Edit `docker/.env` and fill in your Magento Marketplace keys:
   ```env
   MAGENTO_PUBLIC_KEY=your_public_key_here
   MAGENTO_PRIVATE_KEY=your_private_key_here
   ```

2. **Start the Containers**
   
   From the root of this repository, run:
   ```bash
   docker compose -f docker/docker-compose.yml up --build
   ```
   The first build can take several minutes as it installs Magento and dependencies.

3. **Accessing the Services**
   
   - **Magento Storefront:** http://localhost:8000
   - **Magento Admin:** http://localhost:8000/admin (use the value from `--backend-frontname` if customized)
   
   **Default admin credentials:**
   - Username: `admin`
   - Password: `Admin123!`

   You may change these in `docker/docker-compose.yml` under the `command` section for the `magento` service.

4. **Data Persistence**
   
   Database data is persisted in a Docker volume `dbdata` defined in `docker-compose.yml`.

5. **Extra Notes**
   - If you make changes to dependencies or the Docker setup, you may want to rebuild with `--build`.
   - For customizations, you can mount code or configuration as needed using the `volumes` section.

---

## Testing

This project utilizes Magento's built-in testing framework, which is incorporated into the `magento` Docker container. Therefore, all test execution should occur inside the running Magento container to ensure the correct environment and dependencies.

### Integration Testing

- Integration tests are located in the `Test/Integration` directory.
- To run the test suite:

    1. **Start the Docker containers**  
       (Refer to the "Local Development with Docker" section above.)

    2. **Run the integration test suid**  
        ```bash
        docker compose exec magento bash -c "cd dev/tests/integration && ../../../vendor/bin/phpunit ../../../vendor/two-performant/business-league-marketing/Test/Integration/"
        ```


**Note:**
- Ensure all Composer dependencies have been installed before running tests.

---

## Code Quality & Linting

- Uses Magento Coding Standard for linting.
- Run linter:
  ```sh
  composer lint
  ```
  