# Copilot Instructions for Risk Management System

## Project Overview
- **Framework:** Laravel (PHP backend), Vue.js (frontend, see resources/js/app.js)
- **Purpose:** Risk management and documentation system ("SSAS PROJECT")
- **UI:** Uses AdminLTE v2 (see public/adminlte-v2/), customizes via LESS/Grunt

## Key Architecture
- **App Logic:** All main business logic in `app/` (e.g., Risk.php, User.php, ImpactCategory.php)
- **Controllers:** `app/Http/Controllers/` (RESTful, Laravel conventions)
- **Views:** Blade templates in `resources/views/`
- **Frontend:** Vue components registered in `resources/js/app.js`, compiled with Laravel Mix (see webpack.mix.js)
- **Database:** Migrations in `database/migrations/`, seeders/factories for test data
- **Config:** All environment and service config in `config/` and `.env`

## Developer Workflows
- **Install dependencies:**
  - PHP: `composer install`
  - JS/CSS: `npm install`
- **Build assets:** `npm run dev` (development) or `npm run prod` (production)
- **Run server:** `php artisan serve` (or use Laragon/XAMPP)
- **Run tests:** `php artisan test` or `vendor/bin/phpunit`
- **Migrate DB:** `php artisan migrate`
- **Seed DB:** `php artisan db:seed`

## Project Conventions
- **Namespace:** All app code under `App\` (see composer.json autoload)
- **Testing:** Feature and unit tests in `tests/Feature/` and `tests/Unit/`
- **JS/CSS:** Use Laravel Mix for asset pipeline; main entry is `resources/js/app.js` and `resources/sass/app.scss`
- **AdminLTE:** Customizations via LESS in `public/adminlte-v2/build/less/`, built with Grunt (see Gruntfile.js)
- **Routes:**
  - Web: `routes/web.php`
  - API: `routes/api.php`

## Integration Points
- **AdminLTE:** Integrated via public assets, custom skins via LESS/Grunt
- **CKEditor:** Configured in `public/ckeditor/`
- **3rd Party:** See `composer.json` and `package.json` for PHP/JS dependencies

## Examples
- **Add a new model:** Place in `app/`, register migration in `database/migrations/`, controller in `app/Http/Controllers/`, route in `routes/web.php` or `routes/api.php`
- **Add a Vue component:** Place `.vue` file in `resources/js/components/`, register in `resources/js/app.js`

## Tips
- Use Laravel's artisan commands for all code generation and DB tasks
- Follow PSR-4 autoloading (see composer.json)
- For UI, prefer Blade for server-rendered, Vue for dynamic components

---
For more, see README.md or ask a maintainer for project-specific details.
