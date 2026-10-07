<?php
include '../includes/header.php';
include '../includes/navbar.php';
?>

<main>

    <!-- ==============================
         CART HEADER
         ============================== -->

    <section class="cart-header">

        <div class="container text-center">

            <p class="section-label">
                TE_CROWN HAIR
            </p>

            <h1>
                Your Shopping Cart
            </h1>

            <p>
                Review your selected products before checkout.
            </p>

        </div>

    </section>


    <!-- ==============================
         CART
         ============================== -->

    <section class="cart-section">

        <div class="container">

            <div class="row g-5">

                <!-- CART ITEMS -->

                <div class="col-lg-8">

                    <div
                        class="cart-items"
                        id="cartItems"
                    >

                        <!-- Products are loaded here by JavaScript -->

                    </div>


                    <!-- EMPTY CART -->

                    <div
                        id="emptyCart"
                        class="empty-cart"
                        style="display: none;"
                    >

                        <div class="empty-cart-icon">
                            🛍
                        </div>

                        <h2>
                            Your Cart is Empty
                        </h2>

                        <p>
                            You haven't added any products
                            to your cart yet.
                        </p>

                        <a
                            href="shop.php"
                            class="continue-shopping"
                        >
                            START SHOPPING
                        </a>

                    </div>

                </div>


                <!-- ORDER SUMMARY -->

                <div class="col-lg-4">

                    <div class="cart-summary">

                        <h2>
                            Order Summary
                        </h2>


                        <div class="summary-row">

                            <span>
                                Subtotal
                            </span>

                            <span id="cartSubtotal">
                                R0
                            </span>

                        </div>


                        <div class="summary-row">

                            <span>
                                Delivery
                            </span>

                            <span>
                                Calculated at checkout
                            </span>

                        </div>


                        <hr>


                        <div class="summary-total">

                            <span>
                                Total
                            </span>

                            <span id="cartTotal">
                                R0
                            </span>

                        </div>


                        <a
                            href="checkout.php"
                            class="checkout-button"
                            id="checkoutButton"
                        >
                            PROCEED TO CHECKOUT
                        </a>


                        <a
                            href="shop.php"
                            class="continue-shopping"
                        >
                            CONTINUE SHOPPING
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </section>

</main>


<!-- Cart JavaScript -->

<script src="../assets/js/cart.js"></script>


<?php
include '../includes/footer.php';
?>
