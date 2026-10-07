/* ==========================================
   TE_CROWN HAIR SHOP
   ========================================== */


/* ==========================================
   CART FUNCTIONS
   ========================================== */

function getCart() {

    return JSON.parse(
        localStorage.getItem("teCrownCart")
    ) || [];

}


function saveCart(cart) {

    localStorage.setItem(
        "teCrownCart",
        JSON.stringify(cart)
    );

}


/* ==========================================
   ADD TO CART
   ========================================== */

function addToCart(button) {

    const productCard =
        button.closest(".product-item");

    if (!productCard) {
        return;
    }


    const productName =
        productCard
            .querySelector("h3")
            .textContent
            .trim();


    const priceText =
        productCard
            .querySelector(".shop-product-price")
            .textContent
            .trim();


    const price =
        parseFloat(
            priceText.replace(/[^\d.]/g, "")
        );


    const category =
        productCard.getAttribute(
            "data-category"
        );


    let cart = getCart();


    const existingProduct =
        cart.find(
            item =>
                item.name === productName
        );


    if (existingProduct) {

        if (existingProduct.quantity < 10) {

            existingProduct.quantity++;

        }

    } else {

        cart.push({

            name: productName,

            price: price,

            category: category,

            quantity: 1

        });

    }


    saveCart(cart);


    setButtonToGoToCart(button);

    showCartConfirmation(productName);

}


/* ==========================================
   CHANGE BUTTON TO GO TO CART
   ========================================== */

function setButtonToGoToCart(button) {

    button.textContent =
        "GO TO CART";

    button.classList.add(
        "go-to-cart-button"
    );


    button.onclick = function() {

        window.location.href =
            "/pages/cart.php";

    };

}


/* ==========================================
   CART CONFIRMATION
   ========================================== */

function showCartConfirmation(productName) {

    let notification =
        document.getElementById(
            "cartNotification"
        );


    if (!notification) {

        notification =
            document.createElement("div");

        notification.id =
            "cartNotification";

        notification.style.position =
            "fixed";

        notification.style.top =
            "30px";

        notification.style.right =
            "30px";

        notification.style.zIndex =
            "9999";

        notification.style.background =
            "#3b2924";

        notification.style.color =
            "#ffffff";

        notification.style.padding =
            "16px 22px";

        notification.style.borderRadius =
            "4px";

        notification.style.boxShadow =
            "0 5px 20px rgba(0,0,0,0.15)";

        notification.style.fontSize =
            "14px";

        document.body.appendChild(
            notification
        );

    }


    notification.innerHTML =
        "✓ Added to Cart<br>" +
        "<strong>" +
        productName +
        "</strong>";


    notification.style.display =
        "block";


    setTimeout(function() {

        notification.style.display =
            "none";

    }, 2500);

}


/* ==========================================
   CATEGORY FILTER
   ========================================== */

function filterProducts(
    category,
    button
) {

    const products =
        document.querySelectorAll(
            ".product-item"
        );


    const noProducts =
        document.getElementById(
            "noProducts"
        );


    document
        .querySelectorAll(
            ".category-button"
        )
        .forEach(function(item) {

            item.classList.remove(
                "active"
            );

        });


    if (button) {

        button.classList.add(
            "active"
        );

    }


    let visibleProducts = 0;


    products.forEach(function(product) {

        const productCategory =
            product.getAttribute(
                "data-category"
            );


        const matches =
            category === "all" ||
            productCategory === category;


        if (matches) {

            product.style.display =
                "";

            visibleProducts++;

        } else {

            product.style.display =
                "none";

        }

    });


    if (noProducts) {

        noProducts.style.display =
            visibleProducts === 0
                ? "block"
                : "none";

    }

}


/* ==========================================
   SEARCH PRODUCTS
   ========================================== */

function searchProducts() {

    const searchInput =
        document.getElementById(
            "productSearch"
        );


    const searchTerm =
        searchInput
            .value
            .toLowerCase()
            .trim();


    const products =
        document.querySelectorAll(
            ".product-item"
        );


    const noProducts =
        document.getElementById(
            "noProducts"
        );


    let visibleProducts = 0;


    products.forEach(function(product) {

        const productName =
            product.getAttribute(
                "data-name"
            );


        const productCategory =
            product
                .querySelector(
                    ".product-category"
                )
                .textContent
                .toLowerCase();


        const matches =
            productName.includes(
                searchTerm
            ) ||
            productCategory.includes(
                searchTerm
            );


        if (matches) {

            product.style.display =
                "";

            visibleProducts++;

        } else {

            product.style.display =
                "none";

        }

    });


    if (noProducts) {

        noProducts.style.display =
            visibleProducts === 0
                ? "block"
                : "none";

    }

}


/* ==========================================
   SEARCH WITH ENTER KEY
   ========================================== */

document.addEventListener(
    "DOMContentLoaded",
    function() {

        const searchInput =
            document.getElementById(
                "productSearch"
            );


        if (searchInput) {

            searchInput.addEventListener(
                "keypress",
                function(event) {

                    if (
                        event.key === "Enter"
                    ) {

                        event.preventDefault();

                        searchProducts();

                    }

                }
            );

        }


        /*
         * Check which products are already
         * in the customer's cart.
         */

        const cart = getCart();


        document
            .querySelectorAll(
                ".product-item"
            )
            .forEach(function(product) {

                const productName =
                    product
                        .querySelector("h3")
                        .textContent
                        .trim();


                const button =
                    product.querySelector(
                        ".add-cart-button"
                    );


                if (!button) {
                    return;
                }


                const exists =
                    cart.some(
                        item =>
                            item.name ===
                            productName
                    );


                if (exists) {

                    setButtonToGoToCart(
                        button
                    );

                }

            });

    }
);