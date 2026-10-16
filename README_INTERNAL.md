# BusinessLeague Marketing for Magento (Internal Team Guide)

This repository contains the source code, tests, and CI/CD configuration for the BusinessLeague Marketing module for Magento2 / Adobe Commerce.  

---

## Table of Contents

- [Requirements](#requirements)
- [Project Structure](#project-structure)
- [Architecture Overview](#architecture-overview)
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
- PHP `~7.1.3 || ~7.2 || ~7.3 || ~7.4 || ~8.1 || ~8.2 || ~8.3 || ~8.4` (see `composer.json`; the Docker stack uses PHP 8.3, CI fast checks use PHP 8.2)
- Composer
- Docker & Docker Compose (for local development and integration testing)
- Magento Marketplace API keys (needed to build the Docker image, which downloads Magento from `repo.magento.com`)

---

## Project Structure

This is the structure that exists in this repository today:

```
.
├── .circleci/config.yml                 # CircleCI pipeline
├── Block/                               # Adminhtml blocks (category commissions form field)
├── Model/                               # Domain logic, config model, validator, backend/source models
├── Observer/                            # Event observers
├── Plugin/                              # Magento plugins
├── Setup/                               # Uninstall script
├── Test/
│   ├── Unit/                            # PHPUnit unit tests (+ Stubs/MagentoFramework.php)
│   └── Integration/                     # Magento integration tests (run inside Docker)
├── ViewModel/                           # View models (pixel tracker, Big Bear tracker, credits)
├── docker/                              # Local Magento Docker stack
├── etc/                                 # Module configuration (module.xml, acl.xml, config.xml, adminhtml/system.xml, frontend/di.xml, frontend/events.xml, frontend/csp_whitelist.xml)
├── view/                                # Templates, layouts, JS, and assets
├── composer.json                        # Composer config & scripts
├── composer.lock
├── phpcs.xml                            # Linting ruleset (Magento2 standard)
├── registration.php                     # Module registration
├── README.md                            # Public/merchant-facing readme (shown on Packagist)
└── README_INTERNAL.md                   # This file
```

---

## Architecture Overview

- **Package / module names:** Composer package `2performant/business-league-marketing`, Magento module `TwoPerformant_BusinessLeagueMarketing`, PSR-4 namespace `TwoPerformant\BusinessLeagueMarketing\` (rooted at the repository root).
- **Click tracking:** `Observer/ClickTrackingScriptRenderer` listens to `layout_load_before` (`etc/frontend/events.xml`) and injects the click script (`click_script_url`, with the Big Bear Unique ID substituted for `__replace_me__`) only when the Big Bear ID is configured and `Model/Validator/Validator` accepts the URL.
- **Attribution preservation:** `Plugin/RedirectPlugin` is a `beforeSetRedirect` plugin on `Magento\Framework\App\Response\Http` (`etc/frontend/di.xml`). It carries the attribution query params (`2pau`, `2ptt`, `2ptu`, `2prp`, `2pdlst`, configurable via `big_bear_params`) over to redirect targets.
- **Sales tracking:** `view/frontend/layout/checkout_onepage_success.xml` renders the sales script (`sls`) and the conversion iframe using `ViewModel/BigBearTracker` and `ViewModel/PixelTracker`. Order data is built by `Model/TransactionInfo` (name, quantity, net value, category, brand; the brand comes from the product attribute configured as *Brand Attribute Name*, default `manufacturer`).
- **Category commissions:** optional per-category commissions with a default fallback; the admin grid is rendered by `Block/Adminhtml/Form/Field/*` and stored via `Model/Config/Backend/CategoryCommissions`. When enabled, the overall commission percent is added to the conversion iframe URL.
- **Credits footer:** `ViewModel/CreditsViewModel` + `view/frontend/templates/credits.phtml` + `view/frontend/layout/default.xml` add the "Active in BusinessLeague" link to the footer on every page (can be disabled in config).
- **CSP:** `etc/frontend/csp_whitelist.xml` whitelists `attr-2p.com` (script-src) and `event.2performant.com` (frame-src). Any new external domain used by the module must be added there.
- **Configuration:** admin settings live under the `twoperformant_identifiers`, `twoperformant_commissions` and `twoperformant_credits` sections (tab "Business League Marketing"); URLs and the Big Bear param list live under `twoperformant/*` in `etc/config.xml`.
- **Uninstall:** `Setup/Uninstall.php` deletes `twoperformant_identifiers/*`, `twoperformant_commissions/*` and `twoperformant/*` from `core_config_data`.

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

   The stack also includes MySQL 8 (host port `33060`) and Elasticsearch 8 (host port `9200`). Magento runs in developer mode, 2FA modules are disabled, and outgoing emails are disabled.

4. **How the module gets into Magento**
   
   The repository root is mounted into the container at `/var/www/html/packages/2performant/business-league-marketing`, and on startup the container registers it as a Composer `path` repository and runs `composer require 2performant/business-league-marketing:@dev`, then `module:enable` and `setup:upgrade`. Because it is mounted, local code changes are visible immediately (flush cache / re-run `setup:upgrade` / `setup:di:compile` when you change `etc/` or DI config). The container logs `TwoPerformant_BusinessLeagueMarketing module is ready.` when it is done; wait for this before running tests.

5. **Data Persistence**
   
   Database data is persisted in a Docker volume `dbdata` defined in `docker-compose.yml`. `docker/init-test-db.sql` and `docker/install-config-mysql.php` provide the separate database/config used by the Magento integration tests. To start from scratch, run `docker compose -f docker/docker-compose.yml down -v`.

6. **Extra Notes**
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

- Configuration is available in Magento Admin: Stores → Configuration → Business League Marketing (sections: Identifiers, Commissions, Credits).
- Internal defaults and configuration keys live in `etc/config.xml` (including the tracking script/iframe URLs and the list of attribution query params).
- The module only does anything useful once the Campaign Unique ID, Confirm ID and Big Bear Unique ID are filled in.
- Never commit secrets or production credentials.

---

## Development Workflow

- Follow Magento coding standards and PSR-12.
- Use feature branches and submit pull requests for review.
- Run tests and linters locally (see below) before pushing.
- The default branch is `main`; branch names follow the ticket pattern (e.g. `PHP-78-Update-readme.md`) and commits/PR titles use the `[Php-NN] Description (#PR)` format.
- If you change user-facing behavior or configuration, also update `README.md` (the public readme).

---

## Testing

This project utilizes Magento's built-in testing framework, which is incorporated into the `magento` Docker container. Therefore, all test execution should occur inside the running Magento container to ensure the correct environment and dependencies.

### Integration Testing

- Integration tests are located in the `Test/Integration` directory.
- To run the test suite:

    1. **Start the Docker containers**  
       (Refer to the "Local Development with Docker" section above.)

    2. **Wait for the container to log** `TwoPerformant_BusinessLeagueMarketing module is ready.` (the first start can take 10+ minutes).

    3. **Run the integration test suite**  
        ```bash
        docker compose -f docker/docker-compose.yml exec magento bash -c "cd dev/tests/integration && ../../../vendor/bin/phpunit ../../../vendor/2performant/business-league-marketing/Test/Integration/"
        ```
### Unit Testing

- Unit tests are in `Test/Unit` and use PHPUnit 9 (config: `Test/Unit/phpunit.local.xml`).
- They run on the host, without Magento. The Magento framework classes they need are provided by `Test/Unit/Stubs/MagentoFramework.php` (loaded via `autoload-dev`); if a test needs a new framework class/interface, add a stub there.
- To run the unit test suite (requires Xdebug or PCOV for coverage; the script sets `XDEBUG_MODE=coverage`):
   ```bash
   composer unit-test
   ```
- Coverage reports (HTML + Clover) are written to `Test/coverage/` (git-ignored).

**Note:**
- Ensure all Composer dependencies have been installed before running tests.

---

## Code Quality & Linting

- Uses Magento Coding Standard for linting (ruleset in `phpcs.xml`; `vendor/`, `docker/`, `Test/` and `dev/` are excluded).
- Run linter:
  ```sh
  composer lint
  ```
- Warnings do not fail the lint (`ignore_warnings_on_exit`); errors do.
- `composer install`/`update` automatically registers the Magento coding standard paths with `phpcs` (`post-install-cmd` / `post-update-cmd`).

---

## Continuous Integration

- Circle CI is configured in `.circleci/config.yml` (no branch filters: it runs on every pushed branch/PR).
- Two jobs run in a single workflow:
  1. `fast-checks` (PHP 8.2 image): `composer install`, `composer lint`, `composer unit-test`.
  2. `test` (runs only if `fast-checks` passes; Ubuntu machine executor): writes `docker/.env` from the CircleCI project variables `MAGENTO_PUBLIC_KEY` and `MAGENTO_PRIVATE_KEY` (the job fails if they are missing), builds the Docker stack, waits up to 15 minutes for the "module is ready" log line, then runs the integration tests.
- CI does **not** publish anything; releasing to Packagist is a manual step (see below).

---

## Release & Distribution

### Releasing on Packagist (standard process)

The module is distributed as the Composer package `2performant/business-league-marketing` on [Packagist](https://packagist.org/packages/2performant/business-league-marketing). The package is **not** auto-updated: pushing to GitHub (or merging a PR) does **not** publish anything. Every release must be triggered manually on Packagist.

Packagist takes versions from git tags in the GitHub repository (`https://github.com/2Parale/magento-plugin`). `composer.json` intentionally has no `version` field, so do not add one (it would conflict with the tag).

1. Make sure all changes for the release are merged into `main` and CI is green.

2. Create and push a semantic version tag from `main` (existing tags: `1.0.0`, `1.0.1`, `1.1.0`, `1.1.1`, `1.1.2`; no `v` prefix):
   ```bash
   git checkout main && git pull
   git tag 1.1.3
   git push origin 1.1.3
   ```
   Optionally also create a GitHub Release for the tag with release notes.

3. Log in to Packagist with the account that maintains the package (woocomerce@2performant.com), open the package page and click **Update** (on the package page; the "Force update" option is available if needed). Wait for it to finish, then verify the new version appears in the package's version list.

   Alternatively, trigger the same update via the Packagist API:
   ```bash
   curl -XPOST -H 'content-type:application/json' \
     "https://packagist.org/api/update-package?username=<PACKAGIST_USER>&apiToken=<API_TOKEN>" \
     -d '{"repository":{"url":"https://packagist.org/packages/2performant/business-league-marketing"}}'
   ```
   (The API token is found in the Packagist profile; never commit it.)

4. Verify in a clean Magento project: `composer require 2performant/business-league-marketing:<new version>`, then run the Magento setup commands listed below.

Note: `etc/module.xml` currently has `setup_version="1.0.0"`. It is the Magento database schema/data version (the module has no setup scripts), not the release version, so it does not need to be bumped for every release.

### Installing from a ZIP (offline / manual distribution)

Use this only when the package cannot be installed from Packagist.

1. Create a ZIP archive of the module package. Ensure the `vendor` folder is NOT included.

2. On the target system, extract the ZIP outside the Magento project, for example:
   `/var/modules/business-league-marketing`

3. Register the module as a Composer path repository in the Magento project's composer.json:
   ```
   {
     "type": "path",
     "url": "/var/modules/business-league-marketing",
     "options": {
       "symlink": false
     }
   }
   ```

4. Install the module via Composer (from the ZIP/path repository, or from Packagist when using the standard process):
   ```bash
   composer require 2performant/business-league-marketing
   ```

5. Run Magento setup commands:
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
  
