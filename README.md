# memo


## Security upgrade and deployment

This application now requires PHP 8.5.12 or newer in the 8.x line, Laravel 13,
and Node.js 22.23.4 or newer. Run `composer install`, `npm ci`, and `npm run build`.
CI installs the committed locks, exercises authorization and CSRF behavior with
an in-memory SQLite database, and runs Composer/npm security audits.

All memo routes require an existing user's HTTP Basic authentication. Serve the
application only over HTTPS; do not expose HTTP Basic credentials on plain HTTP.
No public registration route is enabled. Provision authorized users through the
normal controlled application administration process, with hashed passwords.

The owner migration adds nullable `memos.user_id`. New memos belong to the signed-in
user. Existing unowned memos remain stored but inaccessible: determine each row's
legitimate owner and backfill `user_id` explicitly before production rollout. Do
not assign all existing data to the first user or infer ownership from IDs.
Take and test a database backup before `php artisan migrate --force`.
The migration has not been run against an existing production database.

The former GET deletion endpoint now returns 405; deletion requires a CSRF-protected
DELETE form. Request validation matches the database's 300-character content limit.
CSS is built locally instead of depending on external font/CSS CDNs. Sessions use
JSON serialization; deploying this upgrade requires users to authenticate again.
