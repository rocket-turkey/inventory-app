# Stockroom

A small PHP 8.3 and MySQL 8.4 inventory app. It tracks products, prices, reorder levels, and an audit trail of stock changes. Stock changes are transactional and cannot make inventory negative.

## Run

1. Install Docker Desktop with Docker Compose.
2. Optionally copy `.env.example` to `.env` and set your own database passwords. The built-in values are for local development only.
3. From this directory, run `docker compose up --build -d`.
4. Open http://localhost:8080.

To stop it, run `docker compose down`. Data stays in the `db_data` volume. To erase all app data, run `docker compose down -v`.

## VS Code without local PHP

This machine runs Docker Engine in Debian WSL, so open the project in a **WSL: Debian** VS Code window first. From PowerShell, run:

```powershell
code --remote wsl+Debian /mnt/c/Users/dgaddis/Projects/2026-09-30/wr/outputs/inventory-app/
```

Then run **Dev Containers: Reopen in Container** from the Command Palette. The WSL and Dev Containers extensions are recommended in `.vscode/extensions.json`. The editor will use PHP 8.3 inside Docker for its built-in PHP validation, without a Windows PHP or Docker CLI installation. Run `docker compose up --build -d` from a WSL terminal in this project to serve the app at http://localhost:8080.

If VS Code still reports that `docker` is missing, check that the lower-left corner says **WSL: Debian** before reopening in the container. Once connected, it should say **Dev Container: Stockroom PHP**. A normal Windows VS Code window still looks for a Windows Docker and PHP executable.

Composer dependencies are built into `/var/www/app/vendor` while the editor opens the source at `/workspace`. `.vscode/settings.json` adds that dependency path to Intelephense so Symfony and Twig classes resolve in the editor. If old diagnostics remain after the setting changes, run **Developer: Reload Window** in VS Code.

The SQL schema runs only when the database volume is first created. This is a local demo without user accounts; do not expose it to the public internet without adding authentication and deployment security.

## Project layout

- `src/public/index.php` wires dependencies and dispatches routes. `src/app/InventoryController.php` handles page, product, and stock actions. Sass source lives in `src/styles/app.scss`; Docker compiles it to the public `app.css` during the build.
- `src/app/ProductRepository.php` and `src/app/StockMovementRepository.php` contain SQL and data access. `InventoryService.php` coordinates transactional stock changes.
- `src/app/Database.php` creates the PDO connection; `InputValidator.php` uses Symfony Validator. The controller uses Symfony HttpFoundation and CSRF components for requests, responses, and form tokens.
- `src/templates/index.html.twig` contains the view and uses Twig's automatic HTML escaping.
- `composer.json` and `composer.lock` define PHP dependencies; `package.json` and `package-lock.json` define Sass. Docker installs and builds both. Apache serves `src/public/`, so application code and templates are not directly accessible over HTTP.
- `database/schema.sql` initializes MySQL on first run.
