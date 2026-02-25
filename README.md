# 2Performant BusinessLeague Marketing for Magento 2

Magento 2 / Adobe Commerce extension that integrates 2Performant tracking for click attribution and sales conversion on the checkout success flow.

## Features

- Injects the 2Performant click tracking script on storefront pages.
- Preserves attribution query params (`2pau`, `2ptt`, `2ptu`, `2prp`, `2pdlst`) through redirects.
- Adds checkout success tracking:
	- sales tracking script (`sls`)
	- conversion iframe payload
- Builds order payload with product details (name, quantity, net value, category, brand).
- Supports configurable category-based commissions (with default fallback).
- Includes CSP whitelist entries for required tracking domains.
- Validates remote click script URL before injection.

## Package

- **Composer package:** `2performant/business-league-marketing`
- **Magento module name:** `TwoPerformant_BusinessLeagueMarketing`
- **Type:** `magento2-module`

## Requirements

- Magento 2 / Adobe Commerce
- PHP versions allowed by package constraints: `~7.1.3 || ~7.2.0 || ~7.3.0 || ~7.4.0 || ~8.1.0 || ~8.2.0 || ~8.3.0 || ~8.4.0`

## Installation

Install with Composer:

1. `composer require 2performant/business-league-marketing`
2. `bin/magento module:enable TwoPerformant_BusinessLeagueMarketing`
3. `bin/magento setup:upgrade`
4. `bin/magento cache:flush`

## Configuration

In Magento Admin, go to:

**Stores → Configuration → Business League Marketing**

### 1) Identifiers

- **Campaign Unique ID**
- **Confirm ID**
- **Big Bear Unique ID**
- **Brand Attribute Name** (default: `manufacturer`)

### 2) Commissions

- **Enable Category Commissions**
- **Default Commission**
- **Commissions List** (Category ID → Commission Value)

## How it works

- A frontend observer injects the click script only when `Big Bear Unique ID` is configured and URL validation passes.
- A response redirect plugin preserves attribution parameters across redirects.
- On checkout success, the module renders tracking output using view models:
	- serialized `tpOrder` object for sales script consumption
	- conversion iframe URL containing campaign, confirm, amount, description and transaction id; the overall commission percent is also added when variable commission is enabled.

## Uninstall

The module provides an uninstall routine that removes its config keys from `core_config_data` under:

- `twoperformant_identifiers/*`
- `twoperformant_commissions/*`
- `twoperformant/*`

## License

GPL-3.0-or-later
