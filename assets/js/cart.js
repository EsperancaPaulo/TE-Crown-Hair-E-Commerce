/* =========================================
   TE_CROWN HAIR - CART FUNCTIONALITY
   ========================================= */


/*
 * Format currency
 */

function formatCurrency(amount) {

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

function getCart() {

    return JSON.parse(
        localStorage.getItem("teCrownCart")
    ) || [];

}


/*
 * Save cart
 */

function saveCart(cart) {

    localStorage.setItem(
        "teCrownCart",
        JSON.stringify(cart)
    );

}


/*
 * Get product image
 */

function getProductImage(item) {

    const images = {

        "Straight Lace Wig":
            "../assets/images/products/straight-lace-wig.png",

        "Body Wave Wig":
            "../assets/images/products/body-wave-wig.png",

        "Deep Wave Wig":
            "../assets/images/products/deep-wave-wig.png",

        "HD Lace Frontal Wig":
            "../assets/images/products/hd-lace-frontal-wig.png",

        "Premium Hair Extensions":
            "../assets/images/products/premium-hair-extensions.png",

        "Curly Hair Extensions":
            "../assets/images/products/curly-hair-extensions.png",

        "Premium Hair Brush":
            "../assets/images/products/premium-hair-brush.png",

        "Premium Wig Cap":
            "../assets/images/products/premium-wig-cap.png"

    };


    return images[item.name] || null;

}


/*
 * Display cart
 */

function displayCart() {

    const cart =
        getCart();


    const cartItems =
        document.getElementById(
            "cartItems"
        );


    const emptyCart =
        document.getElementById(
            "emptyCart"
        );


    /*
     * Empty cart
     */

    if (cart.length === 0) {

        cartItems.innerHTML = "";

        emptyCart.style.display =
            "block";

        updateCartSummary();

        updateCheckoutButton();

        return;

    }


    emptyCart.style.display =
        "none";


    /*
     * Generate cart items
     */

    cartItems.innerHTML = "";


    cart.forEach(function(item, index) {

        const cartItem =
            document.createElement("div");


        cartItem.className =
            "cart-item";


        /*
         * Product image
         */

        const productImage =
            getProductImage(item);


        let imageHTML = "";


        if (productImage) {

            imageHTML = `

                <img
                    src="${productImage}"
                    alt="${item.name}"
                >

            `;

        } else {

            imageHTML = `

                <span>
                    ${item.category
                        ? item.category.toUpperCase()
                        : "PRODUCT"
                    }
                </span>

            `;

        }


        /*
         * Product variation information
         */

        let variationHTML = "";


        if (item.colour) {

            variationHTML += `

                <p class="cart-item-option">

                    <strong>Colour:</strong>
                    ${item.colour}

                </p>

            `;

        }


        if (item.length) {

            variationHTML += `

                <p class="cart-item-option">

                    <strong>Length:</strong>
                    ${item.length}"

                </p>

            `;

        }


        /*
         * Cart item HTML
         */

        cartItem.innerHTML = `

            <div class="cart-item-image">

                ${imageHTML}

            </div>


            <div class="cart-item-details">

                <p class="product-category">

                    ${
                        item.category
                            ? item.category.toUpperCase()
                            : "PRODUCT"
                    }

                </p>


                <h3>
                    ${item.name}
                </h3>


                ${variationHTML}


                <p class="cart-item-price">

                    ${formatCurrency(
                        item.price
                    )}

                </p>


                <button
                    type="button"
                    class="remove-button"
                    onclick="removeCartItem(${index})"
                >
                    Remove
                </button>

            </div>


            <div class="cart-item-quantity">

                <button
                    type="button"
                    onclick="decreaseCartQuantity(${index})"
                >
                    −
                </button>


                <input
                    type="number"
                    value="${item.quantity}"
                    min="1"
                    max="10"
                    onchange="changeCartQuantity(
                        ${index},
                        this.value
                    )"
                >


                <button
                    type="button"
                    onclick="increaseCartQuantity(${index})"
                >
                    +
                </button>

            </div>


            <div class="cart-item-total">

                ${formatCurrency(
                    item.price *
                    item.quantity
                )}

            </div>

        `;


        cartItems.appendChild(
            cartItem
        );

    });


    updateCartSummary();

    updateCheckoutButton();

}


/*
 * Increase quantity
 */

function increaseCartQuantity(index) {

    const cart =
        getCart();


    if (
        cart[index].quantity < 10
    ) {

        cart[index].quantity++;

    }


    saveCart(cart);

    displayCart();

}


/*
 * Decrease quantity
 */

function decreaseCartQuantity(index) {

    const cart =
        getCart();


    if (
        cart[index].quantity > 1
    ) {

        cart[index].quantity--;

    }


    saveCart(cart);

    displayCart();

}


/*
 * Change quantity manually
 */

function changeCartQuantity(
    index,
    value
) {

    const cart =
        getCart();


    let quantity =
        parseInt(value);


    if (
        isNaN(quantity) ||
        quantity < 1
    ) {

        quantity = 1;

    }


    if (quantity > 10) {

        quantity = 10;

    }


    cart[index].quantity =
        quantity;


    saveCart(cart);

    displayCart();

}


/*
 * Remove product
 */

function removeCartItem(index) {

    const cart =
        getCart();


    cart.splice(index, 1);


    saveCart(cart);


    displayCart();

}


/*
 * Update subtotal and total
 */

function updateCartSummary() {

    const cart =
        getCart();


    let subtotal = 0;


    cart.forEach(function(item) {

        subtotal +=
            item.price *
            item.quantity;

    });


    const subtotalElement =
        document.getElementById(
            "cartSubtotal"
        );


    const totalElement =
        document.getElementById(
            "cartTotal"
        );


    if (subtotalElement) {

        subtotalElement.textContent =
            formatCurrency(
                subtotal
            );

    }


    if (totalElement) {

        totalElement.textContent =
            formatCurrency(
                subtotal
            );

    }

}


/*
 * Enable / disable checkout
 */

function updateCheckoutButton() {

    const cart =
        getCart();


    const checkoutButton =
        document.getElementById(
            "checkoutButton"
        );


    if (!checkoutButton) {

        return;

    }


    if (cart.length === 0) {

        checkoutButton.classList.add(
            "checkout-disabled"
        );


        checkoutButton.setAttribute(
            "aria-disabled",
            "true"
        );


        checkoutButton.textContent =
            "CART IS EMPTY";


        checkoutButton.onclick =
            function(event) {

                event.preventDefault();

                alert(
                    "Please add at least one product to your cart before checking out."
                );

            };

    } else {

        checkoutButton.classList.remove(
            "checkout-disabled"
        );


        checkoutButton.removeAttribute(
            "aria-disabled"
        );


        checkoutButton.textContent =
            "PROCEED TO CHECKOUT";


        checkoutButton.onclick =
            null;

    }

}


/*
 * Load cart
 */

document.addEventListener(
    "DOMContentLoaded",
    function() {

        displayCart();

    }
);