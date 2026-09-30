# Stockroom

A small PHP 8.3 and MySQL 8.4 inventory app. It tracks products, prices, reorder levels, and an audit trail of stock changes. Stock changes are transactional and cannot make inventory negative.

## Run

1. Install Docker Desktop with Docker Compose.
2. Optionally copy `.env.example` to `.env` and set your own database passwords. The built-in values are for local development only.
3. From this directory, run `docker compose up --build -d`.
4. Open http://localhost:8080.

To stop it, run `docker compose down`. Data stays in the `db_data` volume. To erase all app data, run `docker compose down -v`.

The SQL schema runs only when the database volume is first created. This is a local demo without user accounts; do not expose it to the public internet without adding authentication and deployment security.

## Project layout

- `src/public/index.php` wires dependencies and dispatches routes. `src/app/InventoryController.php` handles page, product, and stock actions. Sass source lives in `src/styles/app.scss`; Docker compiles it to the public `app.css` during the build.
- `src/app/ProductRepository.php` and `src/app/StockMovementRepository.php` contain SQL and data access. `InventoryService.php` coordinates transactional stock changes.
- `src/app/Database.php` creates the PDO connection; `InputValidator.php` uses Symfony Validator. The controller uses Symfony HttpFoundation and CSRF components for requests, responses, and form tokens.
- `src/templates/index.html.twig` contains the view and uses Twig's automatic HTML escaping.
- `composer.json` and `composer.lock` define PHP dependencies; `package.json` and `package-lock.json` define Sass. Docker installs and builds both. Apache serves `src/public/`, so application code and templates are not directly accessible over HTTP.
- `database/schema.sql` initializes MySQL on first run.
