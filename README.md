<p align="center">
  <img src="assets/images/logo/logo.png" alt="TE Crown Hair logo" width="100">
</p>

# TE Crown Hair

### E-Commerce Platform

A database-driven storefront and administration system for TE Crown Hair, a hair and beauty business in Gauteng, South Africa. Developed as a final-year Web Development and E-Commerce project by **Esperanca Paulo**.

Customers can browse products, manage a cart, place demonstration orders and track private enquiries. Administrators manage the catalogue, inventory, customer access, orders and enquiry responses through a shared workspace.

**Status:** Local implementation and automated regression checks completed; hosted verification pending.

**Public website:** To be added after deployment.

**Payments:** Test mode only. No real funds are collected.

[Features](#features) · [Local setup](#local-setup) · [Demo payments](#demo-payments) · [Testing](#testing) · [Hosting](#hosting) · [Current limitations](#current-limitations)

## Features

| Area | Capabilities |
| --- | --- |
| Product discovery | Search, category filters, product details, images, available stock and supported colour/length options. |
| Customer accounts | Registration, email verification, shared login, logout, account dashboard and password recovery. |
| Shopping and orders | Browser-based cart, delivery details, server-validated checkout, saved order history and order details. |
| Demo payments | Card preview, approved/pending/declined test numbers, retry, cancellation and saved demonstration transaction references. |
| Private enquiries | Account-linked messages, enquiry history, progress statuses and administrator responses. Guest submissions remain separate. |
| Catalogue administration | Add/edit products, image uploads, activate/deactivate products and maintain categories. Categories containing products are protected from deletion. |
| Customer administration | Search registered customers, inspect verification/access state and activate/deactivate accounts. |
| Order administration | Review saved purchases, delivery details, payment records and permitted fulfilment transitions. |
| Store overview | Customer/order counts, active products, low stock, open enquiries and paid revenue excluding demo payments. |

## Technology stack

| Layer | Technology |
| --- | --- |
| Application | PHP |
| Database | MySQL / InnoDB |
| Interface | HTML, CSS, JavaScript and locally served Bootstrap 5.3.3 |
| Email | Brevo Transactional Email API |
| Dependencies | Composer with committed lock file |
| Authentication | PHP sessions with server-side customer/admin roles |

## Local setup

### 1. Check the requirements

- **PHP 8.4.1 or newer**, required by the current locked dependencies.
- MySQL with InnoDB and utf8mb4 support.
- Composer 2.
- PHP extensions: `mysqli` with native `get_result` support, `curl`, `fileinfo`, `json` and `openssl`.
- A modern browser with JavaScript enabled.
- A configured Brevo account and verified sender for actual verification/recovery email delivery.

### 2. Clone and install

```bash
git clone https://github.com/EsperancaPaulo/TE-Crown-Hair-E-Commerce.git
cd TE-Crown-Hair-E-Commerce
composer install
composer check-platform-reqs
```

### 3. Configure the environment

Copy the configuration template:

```bash
cp .env.example .env
```

Edit the private `.env` file using your own settings:

| Variable | Purpose |
| --- | --- |
| `DB_HOST` | Database hostname. |
| `DB_USER` | Database username. |
| `DB_PASSWORD` | Database password. |
| `DB_NAME` | Application database name. |
| `APP_URL` | Trusted website origin used in verification and recovery links. Use the exact local address during development and HTTPS when hosted. |
| `BREVO_API_KEY` | Private Brevo API credential. |
| `BREVO_SENDER_EMAIL` | Verified sender email address. |
| `BREVO_SENDER_NAME` | Sender display name. |

The `.env` file is excluded from Git. Never commit credentials, customer exports, password-reset links or database backups.

### 4. Initialise a fresh database

For an empty local installation, import the initial schema and then apply the additive migration:

```bash
mysql -u YOUR_DATABASE_USER -p < database/schema.sql
php database/migrate.php
```

The schema creates and selects `te_crown_hair`; keep `DB_NAME` consistent with that name for this local setup. It includes starter catalogue records, but no preconfigured customer or administrator login.

On managed hosting, create the database through the provider first and import a copy of the schema with its `CREATE DATABASE` and `USE` statements removed. See [HOSTING.md](HOSTING.md).

**Existing installation:** Back up the database and uploaded images, then run `php database/migrate.php`. Do not re-import the initial schema over existing data. Apply migrations before opening upgraded code to traffic.

### 5. Start the application

On macOS/Linux:

```bash
./bin/serve
```

Alternatively, including on Windows:

```bash
php -S 127.0.0.1:8001 router.php
```

Open **http://127.0.0.1:8001** and set `APP_URL` to that same origin. The supplied router blocks direct access to internal and private files. The development server is for local use only.

### 6. Set up account access

Register a customer account and complete email verification before signing in. Customer and administrator accounts use the same login page; the stored role determines which workspace opens.

Administrator access must be assigned privately by an authorised maintainer after verifying the account owner. There is no public administrator registration or customer-to-admin promotion control.

## Customer and administrator workflows

**Customer purchase:** Browse → choose options and quantity → review cart → sign in → enter delivery details → save order → complete test-card checkout → view confirmation and order history.

**Customer enquiry:** Sign in → send a Contact message → open My enquiries → read status updates and administrator responses. Follow-up questions require a new enquiry in the current version.

**Administrator:** Sign in → review dashboard → maintain products/categories → review customer access → manage order fulfilment → respond to enquiries → log out.

## Demo payments

The checkout uses a **local payment simulator**, not a live payment gateway. Use only the displayed test numbers, a demo cardholder name, a future expiry date and a three-digit demo security code.

| Test card | Saved result | Next action |
| --- | --- | --- |
| `4242 4242 4242 4242` | Payment **Paid**; order **Processing**. | Review the order and continue the demonstration fulfilment workflow. |
| `4111 1111 1111 1111` | Payment **Pending**; order remains **Pending**. | Retry with another test card or cancel the unpaid demo order. |
| `4000 0000 0000 0002` | Payment **Failed**; order remains **Pending**. | Retry or cancel the unpaid demo order. |

Cardholder, number, expiry and CVC fields stay in the browser. Only an opaque demo result token is submitted. The server records a unique `DEMO-` transaction reference, and customer/admin views identify the order as a demonstration. Administrators cannot manually change a demo payment result.

Orders are saved and stock is reserved **before** payment simulation. Checkout retries reuse the original request key. Cancelling an unpaid order restores reserved stock once; repeated submissions do not reserve or release it again. Demo payments are excluded from real paid revenue. Delivery is currently included at **R0.00**.

## Authentication and data protection

- Password hashing and verification through PHP's password APIs.
- Server-side role checks and account ownership checks for protected records.
- Session ID regeneration after login and a two-hour session lifetime from login.
- CSRF protection, prepared statements and escaped output.
- Login and password-recovery request throttling.
- Single-use, time-limited email verification and recovery links.
- Recovery tokens stored as hashes; successful resets revoke that account's older sessions.
- Customer deactivation removes access on the next authenticated request.
- Duplicate-submit and stale-edit protection for enquiry responses.

Email delivery depends on valid Brevo credentials, sender configuration and the hosting environment. A form confirmation does not guarantee inbox delivery. Enquiry responses are stored in the application; they do not automatically send email notifications.

## Project structure

```text
TE-Crown-Hair-E-Commerce/
├── admin/                 # Dashboard and management pages
├── assets/                # Styles, scripts, images and bundled Bootstrap
├── bin/                   # Local startup and hosting-readiness checks
├── database/              # Initial schema and additive migrations
├── includes/              # Authentication, database, email and shared layouts
├── pages/                 # Storefront, accounts, checkout and enquiries
├── tests/                 # Isolated fixtures and regression checks
├── .env.example           # Configuration keys without private values
├── .htaccess              # Apache access restrictions
├── composer.json
├── composer.lock
├── HOSTING.md             # Deployment requirements and verification
├── REPAIR_NOTES.md         # Implementation notes
├── index.php
└── router.php             # Protected local development routing
```

The initial schema defines users, categories, products, orders and order items. The migration adds delivery/request metadata, item options, demo payment records, authentication throttling, enquiries/replies and password-reset storage.

## Testing

Regression checks cover customer/admin authentication, ownership, checkout validation, authoritative pricing, inventory, payment outcomes, fulfilment, enquiries, password recovery and private-file access.

Run the browser-form validator checks with:

```bash
node tests/demo_card_form.js
```

HTTP and database tests require a **separate fictional test database** and matching local server. Follow [tests/README.md](tests/README.md) for setup, execution order and expected inventory assertions. Never run fixture setup or mutation tests against the real shop database.

Recovery tests use a mocked mail sender; they do not send external email or prove hosted inbox delivery. Desktop/narrow-screen rendering and live card-field formatting were inspected locally. A stalled older browser popup prevented the final browser payment-submit retest; payment submissions were verified independently through HTTP regression checks.

## Hosting

**Hosting preparation is in progress. The public website address will be added after deployment.**

The complete application requires a PHP/MySQL host. **InfinityFree is the current free-hosting option under consideration**; its setup still needs a hosting-ready file package, a complete database import and verification on the assigned server.

For InfinityFree:

1. Create a free hosting account and choose a domain/subdomain.
2. Confirm the assigned PHP version meets **8.4.1+** and required extensions are available.
3. Create a database and retain the provider's exact connection settings privately.
4. Prepare Composer dependencies locally; the free plan has no server terminal.
5. Upload the prepared application contents into the domain's `htdocs` directory.
6. Import the prepared database tables through phpMyAdmin and configure the private application settings.
7. Enable HTTPS, set `APP_URL` to the hosted origin, and test customer/admin journeys and actual email delivery.

The GitHub source ZIP alone does not contain installed Composer dependencies. CLI-only migrations also need an appropriate deployment/import process on a host without shell access.

See [HOSTING.md](HOSTING.md) for full installation, upgrade and web-server requirements. `php bin/check-host.php` provides initial checks on hosts with CLI access. Netlify cannot directly execute this PHP application.

Provider references: [InfinityFree features](https://www.infinityfree.com/) · [Upload/migration guidance](https://forum.infinityfree.com/t/how-to-migrate-your-website-to-infinityfree/49315/1) · [Terminal limitation](https://forum.infinityfree.com/t/is-there-a-terminal-support-in-free-version/80098)

## Current limitations

- Live card processing, real charges and automated refunds are not implemented.
- Unpaid stock reservations do not automatically expire; cancel unwanted unpaid demo orders explicitly.
- Customer profile/address editing and a reply composer within an existing enquiry are not available.
- Guest enquiries remain unlinked; replies saved against them are internal only.
- Email-verification resend is not self-service; expired or missing verification links require maintainer assistance.
- The cart is stored in the current browser rather than synchronised across devices.
- Commercial shipping, refunds and support policies require configuration before a public trading launch.
- Hosted deployment, real email delivery and final hosted acceptance checks remain outstanding.

## Project information

| | |
| --- | --- |
| Developer | Esperanca Paulo |
| Role | Full-Stack Developer |
| Client | TE Crown Hair |
| Client owner | Sonisia Guilundo |
| Location | Gauteng, South Africa |
| Project type | Final-Year Web Development and E-Commerce Project |

For deployment information, start with [HOSTING.md](HOSTING.md). For implementation history and regression instructions, see [REPAIR_NOTES.md](REPAIR_NOTES.md) and [tests/README.md](tests/README.md).
