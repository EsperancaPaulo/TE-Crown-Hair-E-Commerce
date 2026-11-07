Run the HTTP checks only against an isolated database with the seeded schema and fictional fixture users. Expected fixture: customer ID 1 / customer@example.invalid, admin@example.invalid, other@example.invalid, unverified@example.invalid; password RepairTestPass123!, first three verified. These credentials are test-only and must never be added to the live shop. The HTTP test deactivates/reactivates the fixture customer and creates simulated orders. Run `python3 tests/http_flows.py http://127.0.0.1:8017` against `DB_NAME=te_crown_repair_test php -S 127.0.0.1:8017 router.php` after applying schema and migration to that isolated database. No Brevo email is sent.

For repeatable setup, create an empty database named `te_crown_repair_test`, then run `SHOP_TEST_SETUP=1 DB_NAME=te_crown_repair_test php tests/prepare.php` and `DB_NAME=te_crown_repair_test php database/migrate.php`. The setup refuses other names or a nonempty database. Drop only that isolated database after testing; never reset the actual shop database.

After the HTTP checkout suite, run `DB_NAME=te_crown_repair_test php tests/assert_inventory.php` and `python3 tests/enquiry_flows.py http://127.0.0.1:8017`. The enquiry tests create fictional messages/replies only in that isolated database. They verify customer ownership, admin permissions, guest separation, saved statuses, CSRF, input validation, escaping, duplicate handling and stale-edit rejection. No external email is sent.

Run `DB_NAME=te_crown_repair_test php tests/password_recovery.php http://127.0.0.1:8017` after applying the migration. It injects a mock mail sender, exercises reset HTTP requests with in-memory secrets, changes only the fictional admin fixture and restores its known test password afterward. Never run it against the real shop database. No external email is sent.


Test-card checkout regression (6 November 2026):
- The setup and recovery scripts also accept the dedicated `te_crown_payment_test` database. Create it empty; never reset an existing shop database. Use `SHOP_TEST_SETUP=1 DB_NAME=te_crown_payment_test php tests/prepare.php`, then apply `DB_NAME=te_crown_payment_test php database/migrate.php`.
- Serve it on a separate port, for example `DB_NAME=te_crown_payment_test SHOP_LISTEN=127.0.0.1:8018 ./bin/serve`.
- Run `python3 tests/http_flows.py http://127.0.0.1:8018`, then `DB_NAME=te_crown_payment_test php tests/assert_inventory.php` immediately afterwards. The expected transaction count now includes pending review.
- Run `python3 tests/enquiry_flows.py http://127.0.0.1:8018`, `DB_NAME=te_crown_payment_test php tests/password_recovery.php http://127.0.0.1:8018`, and finally `python3 tests/payment_flows.py http://127.0.0.1:8018`. These create fictional orders/enquiries; they do not send external email.
- Run `node tests/demo_card_form.js` for browser-only card validation, masking, demo-token selection and duplicate-submit checks.
- Browser checks should cover desktop/mobile rendering, test-card selection, invalid fields, pending/declined/approved results, retry, cancellation and matching admin records.

The card form intentionally omits `name` attributes on cardholder, number, expiry and CVC inputs. Only `approved`, `review` or `declined` demo tokens are submitted. Raw test or real card numbers cannot select a result directly on the server.


For the hosted-registration transport fix, run `php tests/mail_transport.php` (mock transport; no external email) and `python3 tests/registration_flows.py http://127.0.0.1:8018` against the isolated fixture server. The registration checks use invalid/unknown addresses without dispatching email. The native production mail transport uses HTTPS certificate verification, bounded timeouts, and requires a provider message ID before reporting acceptance.
