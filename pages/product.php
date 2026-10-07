<?php

require_once '../includes/db.php';

include '../includes/header.php';
include '../includes/navbar.php';


/* ==========================================
   GET PRODUCT ID
   ========================================== */

$productId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 1;


/* ==========================================
   GET PRODUCT FROM DATABASE
   ========================================== */

$stmt = $conn->prepare("
    SELECT
        products.id,
        products.name,
        products.description,
        products.price,
        products.image,
        products.stock,
        products.status,
        categories.name AS category_name
    FROM products
    INNER JOIN categories
        ON products.category_id = categories.id
    WHERE products.id = ?
    AND products.status = 'active'
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $productId
);

$stmt->execute();

$result = $stmt->get_result();

$product = $result->fetch_assoc();

$stmt->close();


/* ==========================================
   PRODUCT NOT FOUND
   ========================================== */

if (!$product) {

    ?>

    <main>

        <section class="product-not-found">

            <div class="container text-center">

                <p class="section-label">
                    TE_CROWN HAIR
                </p>

                <h1>
                    Product Not Found
                </h1>

                <p>
                    The product you are looking for is
                    unavailable or no longer exists.
                </p>

                <a
                    href="/pages/shop.php"
                    class="view-product-button"
                >
                    RETURN TO SHOP
                </a>

            </div>

        </section>

    </main>

    <?php

    include '../includes/footer.php';

    exit;
}


/* ==========================================
   PRODUCT VARIABLES
   ========================================== */

$productName =
    $product['name'];

$productDescription =
    $product['description'];

$productPrice =
    (float) $product['price'];

$productImage =
    $product['image'];

$productStock =
    (int) $product['stock'];

$productCategory =
    $product['category_name'];

$productCategorySlug =
    strtolower(
        str_replace(
            ' ',
            '-',
            trim($productCategory)
        )
    );

?>

<main>

    <!-- ==============================
         PRODUCT DETAILS
         ============================== -->

    <section class="product-details-section">

        <div class="container">

            <div class="row g-5 align-items-start">


                <!-- ==============================
                     PRODUCT IMAGE
                     ============================== -->

                <div class="col-lg-6">

                    <div class="product-details-image">

                        <?php if (!empty($productImage)): ?>

                            <img
                                src="../assets/images/products/<?php echo htmlspecialchars($productImage); ?>"
                                alt="<?php echo htmlspecialchars($productName); ?>"
                                class="product-main-image"
                            >

                        <?php else: ?>

                            <span>
                                No Image Available
                            </span>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- ==============================
                     PRODUCT INFORMATION
                     ============================== -->

                <div class="col-lg-6">

                    <div class="product-details-info">


                        <!-- CATEGORY -->

                        <p class="product-category">

                            <?php
                            echo htmlspecialchars(
                                strtoupper(
                                    $productCategory
                                )
                            );
                            ?>

                        </p>


                        <!-- PRODUCT NAME -->

                        <h1>

                            <?php
                            echo htmlspecialchars(
                                $productName
                            );
                            ?>

                        </h1>


                        <!-- PRICE -->

                        <p class="product-details-price">

                            R<?php
                            echo number_format(
                                $productPrice,
                                2
                            );
                            ?>

                        </p>


                        <!-- DESCRIPTION -->

                        <div class="product-description">

                            <h3>
                                Product Description
                            </h3>

                            <p>

                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $productDescription
                                    )
                                );
                                ?>

                            </p>

                        </div>


                        <!-- STOCK -->

                        <div class="product-stock">

                            <?php if ($productStock > 0): ?>

                                <span class="stock-available">
                                    ✓ In Stock
                                </span>

                                <span>
                                    <?php echo $productStock; ?>
                                    available
                                </span>

                            <?php else: ?>

                                <span class="stock-unavailable">
                                    Out of Stock
                                </span>

                            <?php endif; ?>

                        </div>


                        <?php if ($productStock > 0): ?>


                            <!-- ==============================
                                 WIG OPTIONS
                                 ============================== -->

                            <?php if ($productCategorySlug === 'wigs'): ?>

                                <div class="product-option-section">

                                    <label>
                                        Colour
                                    </label>

                                    <div class="product-options">

                                        <button
                                            type="button"
                                            class="product-option colour-option selected"
                                            data-value="Natural Black"
                                        >
                                            Natural Black
                                        </button>

                                        <button
                                            type="button"
                                            class="product-option colour-option"
                                            data-value="1B"
                                        >
                                            1B
                                        </button>

                                        <button
                                            type="button"
                                            class="product-option colour-option"
                                            data-value="Brown"
                                        >
                                            Brown
                                        </button>

                                        <button
                                            type="button"
                                            class="product-option colour-option"
                                            data-value="Burgundy"
                                        >
                                            Burgundy
                                        </button>

                                        <button
                                            type="button"
                                            class="product-option colour-option"
                                            data-value="Blonde"
                                        >
                                            Blonde
                                        </button>

                                    </div>

                                </div>


                                <div class="product-option-section">

                                    <label>
                                        Length
                                    </label>

                                    <div class="product-options">

                                        <button
                                            type="button"
                                            class="product-option length-option selected"
                                            data-value="12"
                                        >
                                            12"
                                        </button>

                                        <button
                                            type="button"
                                            class="product-option length-option"
                                            data-value="14"
                                        >
                                            14"
                                        </button>

                                        <button
                                            type="button"
                                            class="product-option length-option"
                                            data-value="16"
                                        >
                                            16"
                                        </button>

                                        <button
                                            type="button"
                                            class="product-option length-option"
                                            data-value="18"
                                        >
                                            18"
                                        </button>

                                        <button
                                            type="button"
                                            class="product-option length-option"
                                            data-value="20"
                                        >
                                            20"
                                        </button>

                                    </div>

                                </div>

                            <?php endif; ?>


                            <!-- ==============================
                                 QUANTITY
                                 ============================== -->

                            <div class="product-option-section">

                                <label>
                                    Quantity
                                </label>

                                <div class="quantity-control">

                                    <button
                                        type="button"
                                        onclick="decreaseQuantity()"
                                    >
                                        −
                                    </button>

                                    <input
                                        type="number"
                                        id="quantity"
                                        value="1"
                                        min="1"
                                        max="<?php echo $productStock; ?>"
                                    >

                                    <button
                                        type="button"
                                        onclick="increaseQuantity()"
                                    >
                                        +
                                    </button>

                                </div>

                            </div>


                            <!-- ==============================
                                 ADD TO CART
                                 ============================== -->

                            <button
                                type="button"
                                class="details-cart-button"
                                id="detailsAddToCart"
                            >
                                ADD TO CART
                            </button>


                        <?php else: ?>

                            <button
                                type="button"
                                class="details-cart-button"
                                disabled
                            >
                                OUT OF STOCK
                            </button>

                        <?php endif; ?>


                        <!-- BACK TO SHOP -->

                        <a
                            href="/pages/shop.php"
                            class="back-to-shop-link"
                        >
                            ← Continue Shopping
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </section>

</main>


<script>

const productData = {

    id: <?php echo (int) $product['id']; ?>,

    name: <?php echo json_encode($productName); ?>,

    price: <?php echo $productPrice; ?>,

    category: <?php echo json_encode($productCategorySlug); ?>,

    stock: <?php echo $productStock; ?>,

    image: <?php echo json_encode($productImage); ?>

};


let selectedColour = "Natural Black";

let selectedLength = "12";


/* ==========================================
   QUANTITY
   ========================================== */

function increaseQuantity() {

    const quantityInput =
        document.getElementById("quantity");

    if (!quantityInput) {
        return;
    }

    let quantity =
        parseInt(
            quantityInput.value,
            10
        ) || 1;


    if (quantity < productData.stock) {

        quantity++;

    }


    quantityInput.value =
        quantity;

}


function decreaseQuantity() {

    const quantityInput =
        document.getElementById("quantity");

    if (!quantityInput) {
        return;
    }

    let quantity =
        parseInt(
            quantityInput.value,
            10
        ) || 1;


    if (quantity > 1) {

        quantity--;

    }


    quantityInput.value =
        quantity;

}


/* ==========================================
   COLOUR
   ========================================== */

function selectColour(button) {

    document
        .querySelectorAll(
            ".colour-option"
        )
        .forEach(function(option) {

            option.classList.remove(
                "selected"
            );

        });


    button.classList.add(
        "selected"
    );


    selectedColour =
        button.dataset.value;

}


/* ==========================================
   LENGTH
   ========================================== */

function selectLength(button) {

    document
        .querySelectorAll(
            ".length-option"
        )
        .forEach(function(option) {

            option.classList.remove(
                "selected"
            );

        });


    button.classList.add(
        "selected"
    );


    selectedLength =
        button.dataset.value;

}


/* ==========================================
   GET CART
   ========================================== */

function getCart() {

    return JSON.parse(
        localStorage.getItem(
            "teCrownCart"
        )
    ) || [];

}


/* ==========================================
   SAVE CART
   ========================================== */

function saveCart(cart) {

    localStorage.setItem(
        "teCrownCart",
        JSON.stringify(cart)
    );

}


/* ==========================================
   ADD PRODUCT TO CART
   ========================================== */

function addProductToCart() {

    const quantityInput =
        document.getElementById(
            "quantity"
        );


    if (!quantityInput) {
        return;
    }


    let quantity =
        parseInt(
            quantityInput.value,
            10
        );


    if (
        isNaN(quantity) ||
        quantity < 1
    ) {

        alert(
            "Please select a valid quantity."
        );

        return;

    }


    if (
        quantity >
        productData.stock
    ) {

        alert(
            "Only " +
            productData.stock +
            " item(s) are available."
        );

        quantityInput.value =
            productData.stock;

        return;

    }


    const product = {

        id: productData.id,

        name: productData.name,

        price: productData.price,

        category: productData.category,

        image: productData.image,

        colour:
            productData.category === "wigs"
                ? selectedColour
                : "",

        length:
            productData.category === "wigs"
                ? selectedLength
                : "",

        quantity: quantity

    };


    let cart = getCart();


    /*
     * A product variation is considered
     * the same item when:
     *
     * Product ID matches
     * Colour matches
     * Length matches
     */

    const existingProduct =
        cart.find(function(item) {

            return (
                item.id === product.id &&
                item.colour === product.colour &&
                item.length === product.length
            );

        });


    if (existingProduct) {

        const newQuantity =
            existingProduct.quantity +
            quantity;


        if (
            newQuantity >
            productData.stock
        ) {

            alert(
                "You cannot add more than " +
                productData.stock +
                " of this product."
            );

            return;

        }


        existingProduct.quantity =
            newQuantity;

    } else {

        cart.push(product);

    }


    saveCart(cart);


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
            function() {

                window.location.href =
                    "/pages/cart.php";

            };

    }


    alert(
        productData.name +
        " added to your cart."
    );

}


/* ==========================================
   INITIALISE
   ========================================== */

document.addEventListener(
    "DOMContentLoaded",
    function() {


        /*
         * Colour buttons
         */

        document
            .querySelectorAll(
                ".colour-option"
            )
            .forEach(function(button) {

                button.addEventListener(
                    "click",
                    function() {

                        selectColour(
                            this
                        );

                    }
                );

            });


        /*
         * Length buttons
         */

        document
            .querySelectorAll(
                ".length-option"
            )
            .forEach(function(button) {

                button.addEventListener(
                    "click",
                    function() {

                        selectLength(
                            this
                        );

                    }
                );

            });


        /*
         * Add to Cart
         */

        const addButton =
            document.getElementById(
                "detailsAddToCart"
            );


        if (addButton) {

            addButton.addEventListener(
                "click",
                function(event) {

                    event.preventDefault();

                    addProductToCart();

                }
            );

        }

    }
);

</script>


<?php
include '../includes/footer.php';
?>
