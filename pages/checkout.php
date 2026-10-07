<?php
include '../includes/header.php';
include '../includes/navbar.php';
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
                to place your order.
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

                        <form id="checkoutForm">

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


                                <!-- CARD -->

                                <label
                                    class="payment-option"
                                    for="cardPayment"
                                >

                                    <input
                                        type="radio"
                                        id="cardPayment"
                                        name="paymentMethod"
                                        value="card"
                                    >

                                    <div>

                                        <strong>
                                            Card Payment
                                        </strong>

                                        <p>
                                            Pay securely using a
                                            debit or credit card.
                                        </p>

                                    </div>

                                </label>


                                <!-- EFT -->

                                <label
                                    class="payment-option"
                                    for="eftPayment"
                                >

                                    <input
                                        type="radio"
                                        id="eftPayment"
                                        name="paymentMethod"
                                        value="eft"
                                    >

                                    <div>

                                        <strong>
                                            EFT / Bank Transfer
                                        </strong>

                                        <p>
                                            Make a bank transfer
                                            using the provided
                                            payment details.
                                        </p>

                                    </div>

                                </label>

                            </div>


                            <!-- ==============================
                                 CARD DETAILS
                                 ============================== -->

                            <div
                                id="cardDetails"
                                class="payment-details"
                                style="display: none;"
                            >

                                <h3>
                                    Card Details
                                </h3>


                                <div class="mb-4">

                                    <label for="cardName">
                                        Cardholder Name
                                    </label>

                                    <input
                                        type="text"
                                        id="cardName"
                                        class="form-control"
                                    >

                                </div>


                                <div class="mb-4">

                                    <label for="cardNumber">
                                        Card Number
                                    </label>

                                    <input
                                        type="text"
                                        id="cardNumber"
                                        class="form-control"
                                        maxlength="19"
                                        placeholder="1234 5678 9012 3456"
                                    >

                                </div>


                                <div class="row">

                                    <div class="col-md-6 mb-4">

                                        <label for="expiryDate">
                                            Expiry Date
                                        </label>

                                        <input
                                            type="text"
                                            id="expiryDate"
                                            class="form-control"
                                            placeholder="MM/YY"
                                            maxlength="5"
                                        >

                                    </div>


                                    <div class="col-md-6 mb-4">

                                        <label for="cvv">
                                            CVV
                                        </label>

                                        <input
                                            type="password"
                                            id="cvv"
                                            class="form-control"
                                            maxlength="4"
                                            placeholder="123"
                                        >

                                    </div>

                                </div>

                            </div>


                            <!-- ==============================
                                 EFT DETAILS
                                 ============================== -->

                            <div
                                id="eftDetails"
                                class="payment-details"
                                style="display: none;"
                            >

                                <h3>
                                    EFT Payment Instructions
                                </h3>

                                <p>
                                    After placing your order,
                                    TE_Crown Hair will provide
                                    the bank transfer details
                                    required to complete payment.
                                </p>

                                <p>
                                    Your order will be processed
                                    after payment confirmation.
                                </p>

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
                                Calculated
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
