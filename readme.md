# BusinessLeague Marketing for Magento (Internal Team Guide)

This repository contains the source code, tests, and CI/CD configuration for the BusinessLeague Marketing module for Magento2 / Adobe Commerce.  

---

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
