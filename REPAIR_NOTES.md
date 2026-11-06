# Repair verification — 30 October 2026

Changes were applied to the existing TE_Crown_Hair app, retaining its brand assets, catalogue and existing database records. `database/migrate.php` is an additive, repeatable upgrade. No live account or product fixtures were created. A separate `te_crown_repair_test` database was used for all test orders and payment simulations.

## Implemented

- Shared login uses the stored database role and refreshes role, verification and active status on protected requests. Admin pages reject customer access. Customers return to checkout after signing in when appropriate.
- Branded, responsive customer dashboard/history with cream/brown/gold actions, profile details, order badges and a designed empty state; dedicated POST logout, session cookie protections, login throttling, form CSRF protection, cache restrictions and browser account-switch cleanup.
- Each customer order read and demo-payment write is scoped to the authenticated session ID. Client-provided customer IDs do not determine ownership. Admin access remains separate and explicit.
- Checkout validates delivery data, product IDs, positive whole quantities, aggregate stock and current database prices. It records orders, item options, delivery and inventory reservations atomically with row locks. A unique request key prevents duplicate orders.
- Local demo payment supports success, decline, retry and cancellation with persisted DEMO references, server-side outcome validation, locked state transitions and replay protection. Cancellation restores stock once. No financial details are collected and no funds move. Demo totals do not count as real paid revenue.
- Admin dashboard/customer/orders now read actual database records. Admin customer activation persists and revokes existing sessions; order transitions are checked. Product uploads validate MIME, image format, size, random names and save errors. Categories and product forms have CSRF protection and numeric/status validation.
- Product cards have consistent image sizing; missing image assets fall back to the existing logo. Homepage products/prices now match the database. About uses the existing logo.
- Private paths are denied by the development router and Apache rules. Configuration examples contain no secrets. Contact messages now persist, with private customer tracking and admin replies; no email notifications are claimed.

## Verified

- PHP syntax checks across application and test files; JavaScript syntax checks for modified modules.
- HTTP checks using separate guest, customer, second customer and admin cookie sessions: redirects, access denial, ownership, forged IDs, order creation, price tampering, stock and quantity rejection, demo success/decline/cancel/retry, duplicate prevention, admin product/category changes, duplicate category handling, deactivation and logout.
- Test product stock began at 10 and ended at 8 after one two-unit paid demo order and one cancelled one-unit order; repeated requests did not deduct or restore stock twice.
- Browser flow: catalogue → cart → sign-in → checkout → demo success → saved confirmation. Customer dashboard reviewed at a 390 px viewport. Logout and account switching showed the second customer's own empty history.
- Brevo SDK installed; required configuration keys present (values not displayed); current request classes construct correctly. Registration still invokes the existing Brevo sender. Valid token unlocks login, a used token is rejected, and expired tokens are rejected. Time handling is explicitly UTC. No external verification email was sent, so inbox delivery and credential validity at Brevo are not confirmed.

## Remaining limits

This is a local payment simulator, not a real payment gateway. Live funds/refunds, shipping rates, automated unpaid reservation expiry, verification-email resend and external contact email notifications remain future work. Unpaid demo orders reserve stock until cancelled. The expired/undelivered verification-email path currently requires shop assistance. Missing product images need suitable real assets; the logo fallback prevents broken cards. Apache rules require server support; other hosts need equivalent private-path rules and HTTPS. Browser checks cover representative desktop/mobile paths, not every device or assistive technology. No production deployment was performed, and this repair does not claim every possible defect has been eliminated.


## Enquiry and administration follow-up

- Added private customer enquiry summaries, paginated history, full message/reply detail, admin enquiry filtering, responses and Open / In progress / Replied / Resolved status management. The authenticated session binds signed-in submissions. Guest records remain unlinked; matching email alone never grants account access. Guest replies are internal only and previous unsent messages cannot be recovered.
- Added repeatable schema upgrades for enquiries and replies. Existing live records were preserved. New enquiry fixtures and responses were created only in the isolated test database.
- Unified the Products-style sidebar across all eight admin pages and removed the storefront footer from admin while keeping shared scripts and proper document structure. Improved Categories CRUD, Customers directory, Orders list/details/status controls and responsive layouts. Customers has no sample/fallback dataset.
- Verified complete Contact → admin reply/status → customer view flow, another customer's denied reads/writes, forbidden customer/admin actions, forged identity fields, guest email-match isolation, CSRF/input validation, safely escaped messages, duplicate submissions/replies and stale admin updates. Re-ran checkout/inventory/auth checks and category create/edit/delete/protected-delete checks.
- Reviewed every admin page visually at desktop size, and representative admin/customer enquiry views at 390 px. The user's customer page was refreshed. The visible admin tab was switched from the fictional preview to the actual shop login at `http://127.0.0.1:8001/pages/login.php`; an existing real admin credential is required to view actual records, and no real credential was available to the agent. The user's signed-in customer session was preserved.

- During the live admin login check, the submitted request completed with an explicit invalid-credentials message. A new login GET returned in about 2 ms and no database lock was active. The earlier loading delay was not conclusively reproduced, so no credential or role override was made. Added a four-worker bounded local startup command and local deferred Bootstrap assets to remove the single-worker/CDN bottlenecks. Real valid-admin sign-in still requires the user's correct existing credentials. Password recovery was subsequently implemented and verified as described below.


## Password recovery and checkout follow-up

- Added Forgot password and Reset password forms using the existing Brevo integration. Tokens are random, hashed at rest, single-use and valid for 30 minutes. Requests are neutral about account existence/delivery, rate-limited and CSRF-protected. Sending failures leave no usable token. Password and confirmation rules are validated server-side. Active/verified eligibility and stored role are preserved. Successful resets invalidate prior sessions for the recovered account through an authentication version; other accounts remain signed in.
- Tested using injected mock email and isolated fixture accounts: unknown and unverified account handling, hashed token storage, CSRF, invalid/expired/replaced/reused tokens, password rules, a successful HTTP reset and subsequent stored-role login, old password rejection, session revocation, separate-customer preservation, throttling and send failure. No real email was sent and no real password was changed. Inbox delivery is unconfirmed. The actual recovery request page was opened for the user to initiate their own email.
- Diagnosed the actual stuck checkout without reading personal field values: the form had no invalid fields, no browser script errors, and the server had returned the older-cart missing-ID error. Added safe unique-name compatibility while retaining authoritative prices, stock checks and duplicate keys. An isolated legacy-cart checkout/retry matched the database price, returned one order and was cancelled in the simulator. The user's filled real checkout was not reloaded or submitted by the agent. Error/loading feedback is now adjacent to the button and requests have a bounded wait.


## Test-card checkout and full-site regression — 6 November 2026
- Replaced outcome buttons with the supplied reference's test-card flow: live masked card preview, name/number/expiry/CVC fields, three selectable demo numbers, explicit test-mode pay button, order summary and separate cancellation. Only opaque demo tokens are posted; card inputs are never stored or transmitted.
- Added pending-review transaction history with an additive migration. Pending remains unpaid with stock reserved and can be retried or cancelled. Approval, decline, cancellation, replay protection, ownership and demo-revenue exclusion remain consistent.
- Updated checkout/confirmation wording and navigation active states. Fixed missing-product HTTP status, fractional product quantities, combined variant stock limits and catalogue matching by product ID. Product success/validation notices now stay inline instead of blocking browser popups.
- Fixed the oversized storefront mobile brand/menu layout. Blocked contradictory legacy paid-and-cancelled admin updates.
- Verified PHP/JavaScript syntax; customer/admin HTTP flows; stock invariants; enquiry/privacy/stale edits; recovery with mocked email; additional card tampering, fulfilment, 26 page and local-asset checks; browser-only validator tests. Test records are isolated in te_crown_payment_test. No live charge or external email was made.
