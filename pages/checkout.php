<?php

require_once '../includes/auth.php';

requireCustomer();

include '../includes/header.php';
include '../includes/navbar.php';

$customerName = $_SESSION["user_name"] ?? "";
$customerEmail = $_SESSION["user_email"] ?? "";

$nameParts = preg_split('/\s+/', trim($customerName));

$firstName = $nameParts[0] ?? "";
$lastName = count($nameParts) > 1
    ? implode(" ", array_slice($nameParts, 1))
    : "";

?>

<main>

    <!-- ==============================
         CHECKOUT HEADER
         ============================== -->

    <section class="checkout-header">

        <div class="container text-center">

            <p class="section-label">
                TE_CROWN HAIR
            </p>

            <h1>
                Checkout
            </h1>

            <p>
                Complete your details and payment method
                to place your order. Prices and stock are confirmed when you submit.
            </p>

        </div>

    </section>


    <!-- ==============================
         CHECKOUT
         ============================== -->

    <section class="checkout-section">

        <div class="container">

            <div class="row g-5">

                <!-- ==============================
                     CHECKOUT FORM
                     ============================== -->

                <div class="col-lg-7">

                    <div class="checkout-form">

                        <form id="checkoutForm" method="post" action="process-order.php"><?php echo csrfField(); ?>
<input type="hidden" name="request_key" value="<?php echo bin2hex(random_bytes(32)); ?>">
<p id="checkoutMessage" role="status" aria-live="polite"></p>

                            <!-- CUSTOMER INFORMATION -->

                            <h2>
                                Customer Information
                            </h2>


                            <div class="row">

                                <!-- First Name -->

                                <div class="col-md-6 mb-4">

                                    <label for="firstName">
                                        First Name
                                    </label>

                                    <input
    type="text"
    id="firstName"
    name="firstName"
    class="form-control"
    value="<?php echo htmlspecialchars($firstName); ?>"
    required
>

                                </div>


                                <!-- Last Name -->

                                <div class="col-md-6 mb-4">

                                    <label for="lastName">
                                        Last Name
                                    </label>

                                    <input
    type="text"
    id="lastName"
    name="lastName"
    class="form-control"
    value="<?php echo htmlspecialchars($lastName); ?>"
    required
>

                                </div>

                            </div>


                            <!-- Email -->

                            <div class="mb-4">

                                <label for="email">
                                    Email Address
                                </label>

                                <input
    type="email"
    id="email"
    name="email"
    class="form-control"
    value="<?php echo htmlspecialchars($customerEmail); ?>"
    readonly
    required
>

                            </div>


                            <!-- Phone -->

                            <div class="mb-4">

                                <label for="phone">
                                    Phone Number
                                </label>

                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <!-- DELIVERY -->

                            <h2 class="delivery-heading">
                                Delivery Information
                            </h2>


                            <!-- Address -->

                            <div class="mb-4">

                                <label for="address">
                                    Street Address
                                </label>

                                <input
                                    type="text"
                                    id="address"
                                    name="address"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <div class="row">

                                <!-- City -->

                                <div class="col-md-6 mb-4">

                                    <label for="city">
                                        City
                                    </label>

                                    <input
                                        type="text"
                                        id="city"
                                        name="city"
                                        class="form-control"
                                        required
                                    >

                                </div>


                                <!-- Province -->

                                <div class="col-md-6 mb-4">

                                    <label for="province">
                                        Province
                                    </label>

                                    <select
                                        id="province"
                                        name="province"
                                        class="form-control"
                                        required
                                    >

                                        <option value="">
                                            Select Province
                                        </option>

                                        <option value="Gauteng">
                                            Gauteng
                                        </option>

                                        <option value="Western Cape">
                                            Western Cape
                                        </option>

                                        <option value="KwaZulu-Natal">
                                            KwaZulu-Natal
                                        </option>

                                        <option value="Eastern Cape">
                                            Eastern Cape
                                        </option>

                                        <option value="Free State">
                                            Free State
                                        </option>

                                        <option value="Limpopo">
                                            Limpopo
                                        </option>

                                        <option value="Mpumalanga">
                                            Mpumalanga
                                        </option>

                                        <option value="North West">
                                            North West
                                        </option>

                                        <option value="Northern Cape">
                                            Northern Cape
                                        </option>

                                    </select>

                                </div>

                            </div>


                            <!-- Postal Code -->

                            <div class="mb-4">

                                <label for="postalCode">
                                    Postal Code
                                </label>

                                <input
                                    type="text"
                                    id="postalCode"
                                    name="postalCode"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <!-- Additional Notes -->

                            <div class="mb-4">

                                <label for="notes">
                                    Additional Notes
                                    <span>(Optional)</span>
                                </label>

                                <textarea
                                    id="notes"
                                    name="notes"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Any special delivery instructions?"
                                ></textarea>

                            </div>


                            <!-- ==============================
                                 PAYMENT
                                 ============================== -->

                            <h2 class="payment-heading">
                                Payment Method
                            </h2>


                            <div class="payment-methods">


<p>Sandbox checkout — no real money moves. Choose a simulated outcome on the next page. Do not enter real card or banking details.</p>
                                <!-- EFT -->

                                <label
                                    class="payment-option"
                                    for="eftPayment"
                                >

                                    <input
                                        type="radio"
                                        id="eftPayment"
                                        name="paymentMethod"
                                        value="demo" required checked
                                    >

                                    <div>

                                        <strong>
                                            Local payment simulator
                                        </strong>

                                        <p>
                                            Use explicit demo controls for success, decline, or cancellation.
                                        </p>

                                    </div>

                                </label>

                            </div>


                            <!-- ==============================
                                 CARD DETAILS
                                 ============================== -->

                            <div
                                id="eftDetails"
                                class="payment-details"
                                style="display: none;"
                            >

<h3>Demo payment</h3><p>Your order is saved first. The next screen lets you simulate payment without entering financial details. No real payment will be processed.</p>
                            </div>


                            <!-- PLACE ORDER -->

                            <button
                                type="submit"
                                class="place-order-button"
                                id="placeOrderButton"
                            >
                                PLACE ORDER
                            </button>

                        </form>

                    </div>

                </div>


                <!-- ==============================
                     ORDER SUMMARY
                     ============================== -->

                <div class="col-lg-5">

                    <div class="checkout-summary">

                        <h2>
                            Your Order
                        </h2>


                        <div id="checkoutProducts">

                            <!-- Products loaded by JavaScript -->

                        </div>


                        <hr>


                        <div class="summary-row">

                            <span>
                                Subtotal
                            </span>

                            <span id="checkoutSubtotal">
                                R0
                            </span>

                        </div>


                        <div class="summary-row">

                            <span>
                                Delivery
                            </span>

                            <span>
                                Included (R0.00)
                            </span>

                        </div>


                        <hr>


                        <div class="summary-total">

                            <span>
                                Total
                            </span>

                            <span id="checkoutTotal">
                                R0
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

</main>


<script src="../assets/js/checkout.js"></script>


<?php
include '../includes/footer.php';
?>
