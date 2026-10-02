# Stockroom

A small PHP 8.3 and MySQL 8.4 inventory app. It tracks products, prices, reorder levels, and an audit trail of stock changes. Stock changes are transactional and cannot make inventory negative.

## Run in GitHub Codespaces

Open this repository in a Codespace. Its dev container uses `compose.yaml` to start both the PHP web service and MySQL. After Codespaces finishes building, open the **Ports** tab and choose **Open in Browser** for the forwarded **Stockroom** port (80). Keep the port private: this demo has no user accounts.

When you change `.devcontainer/devcontainer.json` or `compose.yaml`, run **Codespaces: Rebuild Container** from the Command Palette. You do not need to run `docker compose` inside the Codespace terminal; the dev container starts the services.

## Run locally

1. Install Docker Engine with Docker Compose. On Windows, run Docker from a WSL distribution with Docker access.
2. Optionally copy `.env.example` to `.env` and set your own database passwords. The built-in values are for local development only.
3. From this directory, run `docker compose up --build -d`.
4. Open http://localhost:8080.

To stop it, run `docker compose down`. Data stays in the `db_data` volume. To erase all app data, run `docker compose down -v`.

## VS Code without local PHP

Open `~/Projects/inventory-app` in a **WSL: Debian** VS Code window, then run **Dev Containers: Reopen in Container** from the Command Palette. The Dev Containers setup starts both services and connects the editor to the web container. The WSL and Dev Containers extensions are recommended in `.vscode/extensions.json`.

The editor uses PHP 8.3 inside Docker for validation. Composer dependencies are built into `/var/www/app/vendor` while the source opens at `/workspace`. `.vscode/settings.json` adds the dependency path to Intelephense so Symfony and Twig classes resolve. If old diagnostics remain, run **Developer: Reload Window** in VS Code.

The SQL schema runs only when the database volume is first created. This is a local demo without user accounts; do not expose it to the public internet without adding authentication and deployment security.

## Project layout

- `src/public/index.php` wires dependencies and dispatches routes. `src/app/InventoryController.php` handles page, product, and stock actions. Sass source lives in `src/styles/app.scss`; Docker compiles it to the public `app.css` during the build.
- `src/app/ProductRepository.php` and `src/app/StockMovementRepository.php` contain SQL and data access. `InventoryService.php` coordinates transactional stock changes.
- `src/app/Database.php` creates the PDO connection; `InputValidator.php` uses Symfony Validator. The controller uses Symfony HttpFoundation and CSRF components for requests, responses, and form tokens.
- `src/templates/index.html.twig` contains the view and uses Twig's automatic HTML escaping.
- `composer.json` and `composer.lock` define PHP dependencies; `package.json` and `package-lock.json` define Sass. Docker installs and builds both. Apache serves `src/public/`, so application code and templates are not directly accessible over HTTP.
- `database/schema.sql` initializes MySQL on first run.
