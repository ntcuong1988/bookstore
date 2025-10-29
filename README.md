# Bookstore Mini+ (Advanced)

Auth + Admin CRUD + Search/Pagination + API + CSRF + Tests + CI

## Default Account
- Admin: `admin / admin123`

## Setup
1) Import `sql/schema.sql` to MySQL (phpMyAdmin or CLI).
2) `composer install`
3) Run: `php -S localhost:8001 -t public`

## Tests
- Unit: `composer test:unit`
- Acceptance: `composer test:acceptance`
- All: `composer test`
