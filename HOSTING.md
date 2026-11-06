# Hosting TE Crown Hair

Deployment target: a complete PHP/MySQL host, chosen on 6 November 2026. The application is not a static Netlify site.

## Requirements

- PHP **8.4.1 or newer**, matching the committed Composer lock file. Prefer a currently supported version supplied by the hosting provider.
- PHP mysqli with mysqlnd/get_result, curl, fileinfo, JSON and OpenSSL; functioning PHP sessions and a writable private session directory.
- Composer 2 and an InnoDB-capable MySQL database with utf8mb4 support. Validate the schema/migration against the provider's MySQL version in staging before launch.
- Apache 2.4 with mod_rewrite and AllowOverride enabled so the included `.htaccess` applies. Nginx requires equivalent rules configured by the host.
- HTTPS, persistent writable product-image storage, database backups, outbound HTTPS for Brevo and a verified mail sender.
- A domain pointing to the hosting account. Domain and hosting account have not yet been selected.

## Deployment values to fill in

| Setting | Value |
| --- | --- |
| Hosting provider / plan | To be selected |
| Public HTTPS address | To be added |
| Git branch | `main` |
| Repository | `https://github.com/EsperancaPaulo/TE-Crown-Hair-E-Commerce` |
| Database host/name/user | Configure privately on the host |
| Brevo sender/domain | Configure and verify privately |
| Backup location / schedule | To be configured |

## First installation

1. Create a staging site and an empty database. Create a dedicated database user with access only to this application's database.
2. Deploy the repository with hidden files included. Run `composer install --no-dev --prefer-dist --optimize-autoloader` and `composer check-platform-reqs --no-dev` on the host. Never use `--ignore-platform-reqs` to work around an incompatible PHP version.
3. Set the host's private environment variables using `.env.example` as the key list. If the provider requires a `.env` file, create it privately on the server; never commit it. Set `APP_URL` to the final trusted HTTPS origin, with no path, query or fragment. Configure DB and Brevo values privately.
4. The application currently reads its database name from configuration, while `database/schema.sql` contains `CREATE DATABASE` and `USE te_crown_hair`. On managed hosting, import a copy with those two statements removed into the already selected empty database. Do not import the original unchanged when the host assigns a prefixed database name. The schema creates catalogue data, but no real customer/admin account.
5. Run `php database/migrate.php` using the same database configuration. This adds supporting order, enquiry, reset and demo transaction tables without resetting existing records.
6. Run `php bin/check-host.php`, then check the site through its actual HTTPS address.
7. Ensure `assets/images/products` is writable by PHP using the host's normal ownership/permission settings. Keep uploads on persistent storage and include them in backups. Avoid granting write access to the rest of the application.
8. Create/register and verify the owner's account through the intended process. Have an authorised maintainer assign the admin role privately after verifying identity; never import the test fixture users into this database.

## Existing-site upgrades

Back up the current database, uploaded images and private settings. Deploy the new application and run `php database/migrate.php` during a maintenance window before accepting traffic; new code expects the migration's columns and tables. Do not re-import `schema.sql` over existing records. Do not run `tests/prepare.php` against the hosting database.

## Web server configuration

Use the repository root as the document root. The bundled `.htaccess` disables directory listings and blocks direct HTTP access to configuration, dependency metadata, SQL, internal includes, migrations, dependencies, command-line scripts and tests. Verify that it is actually enforced by the host.

For Nginx, configure equivalent rules for hidden paths, `/includes/`, `/database/`, `/vendor/`, `/tests/`, `/bin/`, `composer.json`, `composer.lock`, `test-email.php`, and SQL/log/Markdown files. Do not deploy with a generic static-file server that can serve PHP source.

Terminate HTTPS in the trusted hosting configuration. The application sets secure session cookies when PHP sees HTTPS; a reverse proxy must provide a trusted HTTPS setup. Do not blindly trust client-provided forwarding headers. Keep error display disabled for visitors and send errors to private logs.

## Hosted smoke checks

- Registration: real inbox delivery, verification link opens the correct hosted address, single use and expiration work.
- Customer and admin login, role redirects, logout, account deactivation and session expiration.
- Password recovery: real delivery, correct hosted reset link, expiration/single use, and old-session revocation.
- Catalogue, options, cart quantities, checkout, validation and current database prices.
- Demo card approved/pending/declined, retry and cancellation; matching customer/admin status and stock effects.
- Enquiries and replies remain private; guest replies are internal and no automatic enquiry email is claimed.
- Product/category changes and image uploads persist after deployment/restart.
- Private files/directories return 403/404, directory listings are disabled, HTTPS is enforced, and sessions have Secure/HttpOnly/SameSite cookies.
- Backups can be restored on staging.

Payment remains a **local simulator**. No real funds are collected. Configure a separate live payment provider, refund procedure and shipping policy before commercial transactions. Unpaid reservations currently require explicit cancellation; they do not automatically expire.

## Netlify decision

Netlify does not execute this application's PHP routes. Do not select the repository root as a Netlify static publish directory. The owner chose PHP/MySQL hosting to preserve the complete customer/admin application. A future Netlify solution would require a separately hosted PHP backend/proxy design or a backend rewrite, each with its own integration and hosted checks.

References checked 6 November 2026:
- https://answers.netlify.com/t/how-can-i-use-php/84186
- https://docs.netlify.com/build/functions/overview/
- https://docs.netlify.com/manage/routing/redirects/rewrites-proxies/

After the hosted address is confirmed, add it to the editable Word guide. The current guide's older payment screenshots also need revision before issuing a new guide version.
