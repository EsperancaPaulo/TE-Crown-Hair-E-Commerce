/* =========================================
   TE_CROWN HAIR - PRODUCT FUNCTIONALITY
   ========================================= */


/* =========================================
   SELECTED OPTIONS
   ========================================= */

let selectedColour = "Natural Black";
let selectedLength = "12";


/* =========================================
   QUANTITY - INCREASE
   ========================================= */

function increaseQuantity() {

    const quantityInput =
        document.getElementById("quantity");

    if (!quantityInput) {
        return;
    }

    let quantity =
        parseInt(quantityInput.value, 10);

    if (isNaN(quantity)) {
        quantity = 1;
    }

    if (quantity < 10) {
        quantityInput.value = quantity + 1;
    }

}


/* =========================================
   QUANTITY - DECREASE
   ========================================= */

function decreaseQuantity() {

    const quantityInput =
        document.getElementById("quantity");

    if (!quantityInput) {
        return;
    }

    let quantity =
        parseInt(quantityInput.value, 10);

    if (isNaN(quantity)) {
        quantity = 1;
    }

    if (quantity > 1) {
        quantityInput.value = quantity - 1;
    }

}


/* =========================================
   COLOUR SELECTION
   ========================================= */

function selectColour(button) {

    const colourOptions =
        document.querySelectorAll(".colour-option");

    colourOptions.forEach(function (option) {
        option.classList.remove("selected");
    });

    button.classList.add("selected");

    selectedColour =
        button.getAttribute("data-value");
}


/* =========================================
   LENGTH SELECTION
   ========================================= */

function selectLength(button) {

    const lengthOptions =
        document.querySelectorAll(".length-option");

    lengthOptions.forEach(function (option) {
        option.classList.remove("selected");
    });

    button.classList.add("selected");

    selectedLength =
        button.getAttribute("data-value");
}


/* =========================================
   ADD TO CART
   ========================================= */

function addProductToCart() {

    const quantityInput =
        document.getElementById("quantity");


    if (!quantityInput) {
        return;
    }


    /* Get quantity */

    let quantity =
        parseInt(quantityInput.value, 10);


    /* Validate quantity */

    if (
        isNaN(quantity) ||
        quantity < 1 ||
        quantity > 10
    ) {

        alert(
            "Please select a quantity between 1 and 10."
        );

        return;

    }


    /* Product information */

    const product = {

        name: "Straight Lace Wig",

        price: 2300,

        category: "wigs",

        colour: selectedColour,

        length: selectedLength,

        quantity: quantity

    };


    /* Get current cart */

    let cart =
        JSON.parse(
            localStorage.getItem("teCrownCart")
        ) || [];


    /*
     * IMPORTANT:
     *
     * If this exact product variation
     * is already in the cart, add ONLY
     * the quantity currently selected.
     */

    const existingProduct =
        cart.find(function (item) {

            return (
                item.name === product.name &&
                item.colour === product.colour &&
                item.length === product.length
            );

        });


    if (existingProduct) {

        existingProduct.quantity =
            Math.min(
                existingProduct.quantity + quantity,
                10
            );

    } else {

        cart.push(product);

    }


    /* Save cart */

    localStorage.setItem(
        "teCrownCart",
        JSON.stringify(cart)
    );


    /* Change button */

    const button =
        document.getElementById(
            "detailsAddToCart"
        );


    if (button) {

        button.textContent =
            "GO TO CART";

        button.classList.add(
            "go-to-cart-button"
        );

        button.onclick =
            function () {

                window.location.href =
                    "/pages/cart.php";

            };

    }


    /* Confirmation */

    alert(
        "Straight Lace Wig added to your cart."
    );

}


/* =========================================
   INITIALISE PRODUCT PAGE
   ========================================= */

function initialiseProductPage() {

    /* Colour buttons */

    const colourOptions =
        document.querySelectorAll(".colour-option");


    colourOptions.forEach(function (button) {

        button.onclick =
            function () {

                selectColour(this);

            };

    });


    /* Length buttons */

    const lengthOptions =
        document.querySelectorAll(".length-option");


    lengthOptions.forEach(function (button) {

        button.onclick =
            function () {

                selectLength(this);

            };

    });


    /* Add to Cart */

    const addButton =
        document.getElementById(
            "detailsAddToCart"
        );


    if (addButton) {

        /*
         * Directly assign ONE click function.
         * This replaces any previous onclick.
         */

        addButton.onclick =
            function (event) {

                event.preventDefault();

                addProductToCart();

            };

    }

}


/* =========================================
   START
   ========================================= */

if (
    document.readyState === "loading"
) {

    document.addEventListener(
        "DOMContentLoaded",
        initialiseProductPage
    );

} else {

    initialiseProductPage();

}