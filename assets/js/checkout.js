/* =========================================
   TE_CROWN HAIR - CHECKOUT FUNCTIONALITY
   ========================================= */


/*
 * Format currency
 */

function formatCheckoutCurrency(amount) {

    return "R" + amount.toLocaleString(
        "en-ZA",
        {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }
    );

}


/*
 * Get cart
 */

function getCheckoutCart() {

    return JSON.parse(
        localStorage.getItem("teCrownCart")
    ) || [];

}


/*
 * Display order summary
 */

function displayCheckoutSummary() {

    const cart =
        getCheckoutCart();


    const productsContainer =
        document.getElementById(
            "checkoutProducts"
        );


    const subtotalElement =
        document.getElementById(
            "checkoutSubtotal"
        );


    const totalElement =
        document.getElementById(
            "checkoutTotal"
        );


    let subtotal = 0;


    productsContainer.innerHTML = "";


    cart.forEach(function(item) {

        const product =
            document.createElement("div");


        product.className =
            "checkout-product";


        const itemTotal =
            item.price *
            item.quantity;


        subtotal += itemTotal;


        product.innerHTML = `

            <div>

                <h3>
                    ${item.name}
                </h3>

                <p>
                    Quantity: ${item.quantity}
                </p>

            </div>

            <span>
                ${formatCheckoutCurrency(
                    itemTotal
                )}
            </span>

        `;


        productsContainer.appendChild(
            product
        );

    });


    subtotalElement.textContent =
        formatCheckoutCurrency(
            subtotal
        );


    totalElement.textContent =
        formatCheckoutCurrency(
            subtotal
        );

}


/*
 * Payment method switching
 */

function setupPaymentMethods() {

    const paymentMethods =
        document.querySelectorAll(
            'input[name="paymentMethod"]'
        );


    const cardDetails =
        document.getElementById(
            "cardDetails"
        );


    const eftDetails =
        document.getElementById(
            "eftDetails"
        );


    paymentMethods.forEach(function(method) {

        method.addEventListener(
            "change",
            function() {

                /*
                 * Hide both sections
                 */

                cardDetails.style.display =
                    "none";

                eftDetails.style.display =
                    "none";


                /*
                 * Show selected section
                 */

                if (
                    this.value === "card"
                ) {

                    cardDetails.style.display =
                        "block";

                }


                if (
                    this.value === "eft"
                ) {

                    eftDetails.style.display =
                        "block";

                }

            }
        );

    });

}


/*
 * Validate card details
 */

function validateCardDetails() {

    const cardName =
        document.getElementById(
            "cardName"
        ).value.trim();


    const cardNumber =
        document.getElementById(
            "cardNumber"
        ).value.trim();


    const expiryDate =
        document.getElementById(
            "expiryDate"
        ).value.trim();


    const cvv =
        document.getElementById(
            "cvv"
        ).value.trim();


    if (
        cardName === "" ||
        cardNumber === "" ||
        expiryDate === "" ||
        cvv === ""
    ) {

        alert(
            "Please complete all card details."
        );

        return false;

    }


    /*
     * Basic card number validation
     */

    const cleanCardNumber =
        cardNumber.replace(/\s/g, "");


    if (
        !/^\d{16}$/.test(
            cleanCardNumber
        )
    ) {

        alert(
            "Please enter a valid 16-digit card number."
        );

        return false;

    }


    /*
     * Basic expiry validation
     */

    if (
        !/^\d{2}\/\d{2}$/.test(
            expiryDate
        )
    ) {

        alert(
            "Please enter the expiry date in MM/YY format."
        );

        return false;

    }


    /*
     * CVV validation
     */

    if (
        !/^\d{3,4}$/.test(
            cvv
        )
    ) {

        alert(
            "Please enter a valid CVV."
        );

        return false;

    }


    return true;

}


/*
 * Submit order
 */

function submitCheckoutOrder() {

    const cart =
        getCheckoutCart();


    /*
     * Prevent empty-cart checkout
     */

    if (cart.length === 0) {

        alert(
            "Your cart is empty. Please add at least one product before checking out."
        );

        window.location.href =
            "shop.php";

        return;

    }


    const firstName =
        document.getElementById(
            "firstName"
        ).value.trim();


    const lastName =
        document.getElementById(
            "lastName"
        ).value.trim();


    const email =
        document.getElementById(
            "email"
        ).value.trim();


    const phone =
        document.getElementById(
            "phone"
        ).value.trim();


    const address =
        document.getElementById(
            "address"
        ).value.trim();


    const city =
        document.getElementById(
            "city"
        ).value.trim();


    const province =
        document.getElementById(
            "province"
        ).value;


    const postalCode =
        document.getElementById(
            "postalCode"
        ).value.trim();


    const paymentMethod =
        document.querySelector(
            'input[name="paymentMethod"]:checked'
        );


    /*
     * Customer and delivery validation
     */

    if (
        firstName === "" ||
        lastName === "" ||
        email === "" ||
        phone === "" ||
        address === "" ||
        city === "" ||
        province === "" ||
        postalCode === ""
    ) {

        alert(
            "Please complete all required customer and delivery fields."
        );

        return;

    }


    /*
     * Email validation
     */

    if (
        !email.includes("@")
    ) {

        alert(
            "Please enter a valid email address."
        );

        return;

    }


    /*
     * Payment method required
     */

    if (!paymentMethod) {

        alert(
            "Please select a payment method."
        );

        return;

    }


    /*
     * Card validation
     */

    if (
        paymentMethod.value === "card"
    ) {

        if (
            !validateCardDetails()
        ) {

            return;

        }

    }


    /*
     * Calculate total
     */

    let total = 0;


    cart.forEach(function(item) {

        total +=
            item.price *
            item.quantity;

    });


    /*
     * Create order reference
     */

    const orderNumber =
        "TC" +
        Date.now();


    /*
     * Save prototype order
     */

    const order = {

        orderNumber:
            orderNumber,

        customer: {

            firstName:
                firstName,

            lastName:
                lastName,

            email:
                email,

            phone:
                phone

        },

        delivery: {

            address:
                address,

            city:
                city,

            province:
                province,

            postalCode:
                postalCode

        },

        paymentMethod:
            paymentMethod.value,

        paymentStatus:
            paymentMethod.value === "card"
                ? "Paid"
                : "Pending",

        products:
            cart,

        total:
            total,

        orderDate:
            new Date().toISOString()

    };


    /*
     * Save latest order
     */

    localStorage.setItem(
        "teCrownLastOrder",
        JSON.stringify(order)
    );


    /*
     * Clear cart
     */

    localStorage.removeItem(
        "teCrownCart"
    );


    /*
     * Go to confirmation
     */

    window.location.href =
        "order-confirmation.php";

}


/*
 * Initialise checkout
 */

document.addEventListener(
    "DOMContentLoaded",
    function() {

        const cart =
            getCheckoutCart();


        /*
         * Empty cart protection
         */

        if (
            cart.length === 0
        ) {

            alert(
                "Your cart is empty. Please add at least one product before checking out."
            );

            window.location.href =
                "shop.php";

            return;

        }


        displayCheckoutSummary();

        setupPaymentMethods();


        /*
         * Checkout form
         */

        const checkoutForm =
            document.getElementById(
                "checkoutForm"
            );


        checkoutForm.addEventListener(
            "submit",
            function(event) {

                event.preventDefault();

                submitCheckoutOrder();

            }
        );

    }
);
