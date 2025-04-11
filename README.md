LaravelCPQ
================================

A lightweight Configure-Price-Quote (CPQ) solution for Laravel.

Installation
--------------------------------

You can install the package via composer:

```sh
composer require hzmwelec/cpq
```

Database
--------------------------------

You should publish and run migrations:

```sh
php artisan vendor:publish --provider="Hzmwelec\CPQ\CPQServiceProvider" --tag=migrations
php artisan migrate
```

Create versions and products with Artisan Commands
--------------------------------

The concept of `version` is introduced to prevent quoting inconsistencies.
By locking, unlocking, and switching between versions,
you can ensure that users do not quoting while configurations and rules are being edited.

To create a new CPQ version and product via the command line, use:

```sh
php artisan cpq:create-version ${version_name}
php artisan cpq:create-product ${version_id} ${product_name} ${product_code}
```

Import Factors
--------------------------------

CPQ factors are attributes of a product and can be batch imported.
To publish the import files, run:

```sh
php artisan vendor:publish --provider="Hzmwelec\CPQ\CPQServiceProvider" --tag=imports
```

To import factors, use:

```sh
php artisan cpq:import-factors ${product_id} database/imports/${factor_file}
```
