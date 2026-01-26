# BusinessLeague Marketing for Magento (Internal Team Guide)

This repository contains the source code, tests, and CI/CD configuration for the BusinessLeague Marketing module for Magento2 / Adobe Commerce.  

---

## Table of Contents

- [Requirements](#requirements)
- [Project Structure](#project-structure)
- [Local Development with Docker](#local-development-with-docker)
- [Setup & Installation](#setup--installation)
- [Configuration](#configuration)
- [Development Workflow](#development-workflow)
- [Testing](#testing)
- [Code Quality & Linting](#code-quality--linting)
- [Continuous Integration](#continuous-integration)
- [Release & Distribution](#release--distribution)
- [Contributing](#contributing)
- [Troubleshooting](#troubleshooting)
- [References](#references)

---

## Requirements

- Magento 2 / Adobe Commerce (supported by the module)
- PHP 7.1.3+ 
- Composer
- Docker & Docker Compose (for local development and testing)

---

## Project Structure

This is the structure that exists in this repository today:

```
.
├── Block/                               # Adminhtml blocks
├── Model/                               # Domain logic and config models
├── Observer/                            # Event observers
├── Plugin/                              # Magento plugins
├── Setup/                               # Uninstall script
├── Test/                                # Unit & integration tests + coverage artifacts
├── ViewModel/                           # View models
├── docker/                              # Local Magento Docker stack
├── etc/                                 # Module configuration (module.xml, acl.xml, system.xml, di.xml, etc.)
├── view/                                # Templates, layouts, JS, and assets
├── composer.json                        # Composer config & scripts
├── registration.php                     # Module registration
└── readme.md                            # This file
```
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

## Setup & Installation

1. Clone the repository:
   ```sh
   git clone https://github.com/2Parale/magento-plugin.git
   cd magento-plugin
   ```

2. Install PHP dependencies:
   ```sh
   composer install
   ```

---

## Configuration

- Configuration is available in Magento Admin: Stores → Configuration → BusinessLeague Marketing.
- Internal defaults and configuration keys live in `etc/config.xml`.
- Never commit secrets or production credentials.

---

## Development Workflow

- Follow Magento coding standards and PSR-12.
- Use feature branches and submit pull requests for review.
- Run tests and linters locally (see below) before pushing.

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
###Unit Testing

- Unit tests are in `Test/Unit`.
- Uses PHPUnit. Tests are in `businessleaguemarketing/tests/Unit`.
- To run the unit test suite:
   ```bash
   composer unit-test
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

---

## Continuous Integration

- Circle CI is configured in `.circleci/config.yml`.
- On every PR or push to main, CI will:
  - Set up environment variables
  - Build containers
  - Run code linter
  - Run unit tests and coverage
  - Run integration tests 

---

## Release & Distribution

1. Update the module version in composer.json and etc/module.xml.

2. Create a ZIP archive of the module package. Ensure the `vendor` folder is NOT included.

4. On the target system, extract the ZIP outside the Magento project, for example:
   `/var/modules/business-league-marketing`

5. Register the module as a Composer path repository in the Magento project's composer.json:
   ```
   {
     "type": "path",
     "url": "/var/modules/business-league-marketing",
     "options": {
       "symlink": false
     }
   }
   ```

6. Install the module via Composer:
   ```bash
   composer require twoperformant/business-league-marketing
   ```

7. Run Magento setup commands:
   ```bash
   bin/magento module:enable TwoPerformant_BusinessLeagueMarketing
   bin/magento setup:upgrade
   bin/magento cache:flush
   ```

---

## Contributing

- Follow Magento coding standards and naming conventions.
- Write tests for new features and bugfixes.
- Document public methods and classes.
- Use descriptive commit messages.

---

## Troubleshooting

- Autoloader issues: run `composer dump-autoload`.
- Ports already in use: stop other services on 8000 or adjust `docker/docker-compose.yml`.
- Magento not ready: check container logs and wait for the initial bootstrap to finish.
- Module not installing: ensure `vendor/` exists and re-run setup/upgrade.

---

## References

- Magento Developer Docs: [Magento 2 developer documentation](https://developer.adobe.com/commerce/docs/)
- Magento Coding Standards: [Magento Coding Standard](https://developer.adobe.com/commerce/php/coding-standards/)
  
