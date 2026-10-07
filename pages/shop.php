<?php

require_once '../includes/db.php';

include '../includes/header.php';
include '../includes/navbar.php';


/* ==========================================
   GET CATEGORIES
   ========================================== */

$categoryQuery = "
    SELECT id, name
    FROM categories
    ORDER BY name ASC
";

$categoryResult = $conn->query($categoryQuery);


/* ==========================================
   GET PRODUCTS
   ========================================== */

$productQuery = "
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
    WHERE products.status = 'active'
    ORDER BY products.id ASC
";

$productResult = $conn->query($productQuery);

?>

<main>

    <!-- ==============================
         SHOP HEADER
         ============================== -->

    <section class="shop-header">

        <div class="container">

            <p class="section-label">
                TE_CROWN HAIR
            </p>

            <h1>
                Shop Our Products
            </h1>

            <p>
                Discover our collection of premium wigs,
                hair extensions and accessories.
            </p>

        </div>

    </section>


    <!-- ==============================
         SEARCH & FILTERS
         ============================== -->

    <section class="shop-controls">

        <div class="container">

            <div class="search-container">

                <input
                    type="text"
                    id="productSearch"
                    class="search-input"
                    placeholder="Search products..."
                >

                <button
                    type="button"
                    class="search-button"
                    onclick="searchProducts()"
                >
                    SEARCH
                </button>

            </div>


            <div class="category-filters">

                <!-- ALL -->
                <button
                    type="button"
                    class="category-button active"
                    onclick="filterProducts('all', this)"
                >
                    ALL
                </button>


                <?php if ($categoryResult && $categoryResult->num_rows > 0): ?>

                    <?php while ($category = $categoryResult->fetch_assoc()): ?>

                        <?php

                        $categorySlug = strtolower(
                            str_replace(
                                ' ',
                                '-',
                                trim($category['name'])
                            )
                        );

                        ?>

                        <button
                            type="button"
                            class="category-button"
                            onclick="filterProducts(
                                '<?php echo htmlspecialchars($categorySlug); ?>',
                                this
                            )"
                        >
                            <?php
                            echo htmlspecialchars(
                                strtoupper($category['name'])
                            );
                            ?>
                        </button>

                    <?php endwhile; ?>

                <?php endif; ?>

            </div>

        </div>

    </section>


    <!-- ==============================
         PRODUCTS
         ============================== -->

    <section class="shop-products">

        <div class="container">

            <div
                class="row g-4"
                id="productGrid"
            >

                <?php if ($productResult && $productResult->num_rows > 0): ?>

                    <?php while ($product = $productResult->fetch_assoc()): ?>

                        <?php

                        /*
                         * Convert database category name
                         * into the same format used by
                         * the JavaScript filters.
                         *
                         * Example:
                         * Wigs → wigs
                         * Hair Extensions → hair-extensions
                         * Accessories → accessories
                         */

                        $categorySlug = strtolower(
                            str_replace(
                                ' ',
                                '-',
                                trim($product['category_name'])
                            )
                        );

                        ?>

                        <div
                            class="col-lg-3 col-md-6 product-item"
                            data-category="<?php echo htmlspecialchars($categorySlug); ?>"
                            data-name="<?php echo htmlspecialchars(strtolower($product['name'])); ?>"
                        >

                            <div class="shop-product-card">


                                <!-- PRODUCT IMAGE -->
                                <div class="shop-product-image">

                                    <?php if (!empty($product['image'])): ?>

                                        <img
                                            src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                        >

                                    <?php else: ?>

                                        <span>
                                            No Image
                                        </span>

                                    <?php endif; ?>

                                </div>


                                <!-- PRODUCT INFORMATION -->
                                <div class="shop-product-info">

                                    <p class="product-category">

                                        <?php
                                        echo htmlspecialchars(
                                            strtoupper(
                                                $product['category_name']
                                            )
                                        );
                                        ?>

                                    </p>


                                    <h3>

                                        <?php
                                        echo htmlspecialchars(
                                            $product['name']
                                        );
                                        ?>

                                    </h3>


                                    <p class="shop-product-price">

                                        R<?php
                                        echo number_format(
                                            $product['price'],
                                            2
                                        );
                                        ?>

                                    </p>


                                    <!-- ACTIONS -->
                                    <div class="product-actions">

                                        <a
                                            href="product.php?id=<?php echo (int)$product['id']; ?>"
                                            class="view-product-button"
                                        >
                                            VIEW PRODUCT
                                        </a>


                                        <?php if ((int)$product['stock'] > 0): ?>

                                            <button
                                                type="button"
                                                class="add-cart-button"
                                                onclick="addToCart(this)"
                                            >
                                                ADD TO CART
                                            </button>

                                        <?php else: ?>

                                            <button
                                                type="button"
                                                class="add-cart-button"
                                                disabled
                                            >
                                                OUT OF STOCK
                                            </button>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php endwhile; ?>

                <?php endif; ?>

            </div>


            <!-- ==============================
                 NO PRODUCTS MESSAGE
                 ============================== -->

            <div
                id="noProducts"
                class="no-products"
                style="<?php echo ($productResult && $productResult->num_rows > 0) ? 'display: none;' : ''; ?>"
            >

                <h3>
                    No products found.
                </h3>

                <p>
                    Try another search or category.
                </p>

            </div>

        </div>

    </section>

</main>


<script src="../assets/js/shop.js"></script>


<?php
include '../includes/footer.php';
?>