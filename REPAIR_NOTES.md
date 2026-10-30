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
- Private paths are denied by the development router and Apache rules. Configuration examples contain no secrets. Contact form no longer claims to have delivered unsent messages.

## Verified

- PHP syntax checks across application and test files; JavaScript syntax checks for modified modules.
- HTTP checks using separate guest, customer, second customer and admin cookie sessions: redirects, access denial, ownership, forged IDs, order creation, price tampering, stock and quantity rejection, demo success/decline/cancel/retry, duplicate prevention, admin product/category changes, duplicate category handling, deactivation and logout.
- Test product stock began at 10 and ended at 8 after one two-unit paid demo order and one cancelled one-unit order; repeated requests did not deduct or restore stock twice.
- Browser flow: catalogue → cart → sign-in → checkout → demo success → saved confirmation. Customer dashboard reviewed at a 390 px viewport. Logout and account switching showed the second customer's own empty history.
- Brevo SDK installed; required configuration keys present (values not displayed); current request classes construct correctly. Registration still invokes the existing Brevo sender. Valid token unlocks login, a used token is rejected, and expired tokens are rejected. Time handling is explicitly UTC. No external verification email was sent, so inbox delivery and credential validity at Brevo are not confirmed.

## Remaining limits

This is a local payment simulator, not a real payment gateway. Live funds/refunds, shipping rates, automated unpaid reservation expiry, password reset, verification-email resend and contact message delivery remain future work. Unpaid demo orders reserve stock until cancelled. The expired/undelivered verification-email path currently requires shop assistance. Missing product images need suitable real assets; the logo fallback prevents broken cards. Apache rules require server support; other hosts need equivalent private-path rules and HTTPS. Browser checks cover representative desktop/mobile paths, not every device or assistive technology. No production deployment was performed, and this repair does not claim every possible defect has been eliminated.
