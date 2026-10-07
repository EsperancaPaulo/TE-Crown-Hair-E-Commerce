<?php

require_once '../includes/auth.php';

requireAdmin();

require_once '../includes/db.php';

$message = "";
$messageType = "";


/*
|--------------------------------------------------------------------------
| Deactivate Product
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";
    $productId = intval($_POST["productId"] ?? 0);

    if (
        $action === "deactivate" &&
        $productId > 0
    ) {

        $stmt = $conn->prepare(
            "UPDATE products SET status = 'inactive' WHERE id = ?"
        );

        $stmt->bind_param(
            "i",
            $productId
        );

        if ($stmt->execute()) {

            header(
                "Location: products.php?success=deactivated"
            );

            exit;

        } else {

            $message =
                "Unable to deactivate product.";

            $messageType = "error";

        }

        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| Success Messages
|--------------------------------------------------------------------------
*/

if (isset($_GET["success"])) {

    if ($_GET["success"] === "added") {

        $message =
            "Product added successfully.";

        $messageType = "success";

    }

    if ($_GET["success"] === "updated") {

        $message =
            "Product updated successfully.";

        $messageType = "success";

    }

    if ($_GET["success"] === "deactivated") {

        $message =
            "Product deactivated successfully.";

        $messageType = "success";

    }

}


/*
|--------------------------------------------------------------------------
| Fetch Products
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        products.id,
        products.name,
        products.description,
        products.price,
        products.image,
        products.stock,
        products.status,
        products.created_at,
        categories.name AS category_name
    FROM products
    INNER JOIN categories
        ON products.category_id = categories.id
    ORDER BY products.id DESC
";

$result = $conn->query($sql);

?>

<?php include '../includes/header.php'; ?>


<div class="admin-layout">

    <!-- =========================
         SIDEBAR
         ========================= -->

    <aside class="admin-sidebar">

        <div class="admin-brand">

            <img
                src="/assets/images/logo/logo.png"
                alt="TE_Crown Hair Logo"
            >

            <div>

                <strong>TE_Crown Hair</strong>

                <span>Admin Panel</span>

            </div>

        </div>


        <nav class="admin-navigation">

            <a
                href="index.php"
                class="admin-nav-link"
            >
                <span>▦</span>
                Dashboard
            </a>


            <a
                href="products.php"
                class="admin-nav-link active"
            >
                <span>🛍</span>
                Products
            </a>


            <a
                href="categories.php"
                class="admin-nav-link"
            >
                <span>▤</span>
                Categories
            </a>


            <a
                href="customers.php"
                class="admin-nav-link"
            >
                <span>👥</span>
                Customers
            </a>


            <a
                href="orders.php"
                class="admin-nav-link"
            >
                <span>🛒</span>
                Orders
            </a>

        </nav>


        <div class="admin-sidebar-bottom">

            <a
                href="/index.php"
                class="admin-nav-link"
            >
                <span>🌐</span>
                View Website
            </a>


            <a
                href="#"
                class="admin-nav-link admin-logout"
            >
                <span>↪</span>
                Logout
            </a>

        </div>

    </aside>


    <!-- =========================
         MAIN CONTENT
         ========================= -->

    <main class="admin-main">

        <!-- TOP BAR -->

        <div class="admin-topbar">

            <div class="admin-page-label">
                Product Management
            </div>

            <div class="admin-user">
                <span>Administrator</span>
            </div>

        </div>


        <!-- PAGE HEADER -->

        <div class="admin-page-header">

            <div>

                <h1>Products</h1>

                <p>
                    Manage products available on the TE_Crown Hair website.
                </p>

            </div>


            <a
                href="add-product.php"
                class="admin-primary-button"
            >
                + Add Product
            </a>

        </div>


        <!-- SUCCESS / ERROR MESSAGE -->

        <?php if ($message !== ""): ?>

            <div
                class="admin-form-message <?php echo $messageType; ?>"
            >
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <!-- =========================
             SEARCH & FILTER
             ========================= -->

        <div class="admin-product-toolbar">

            <div class="admin-search-box">

                <input
                    type="text"
                    id="productSearch"
                    placeholder="Search products..."
                >

            </div>


            <div class="admin-filter-box">

                <select id="categoryFilter">

                    <option value="all">
                        All Categories
                    </option>

                    <option value="wigs">
                        Wigs
                    </option>

                    <option value="hair extensions">
                        Hair Extensions
                    </option>

                    <option value="accessories">
                        Accessories
                    </option>

                </select>

            </div>

        </div>


        <!-- =========================
             PRODUCTS TABLE
             ========================= -->

        <div class="admin-panel">

            <div class="admin-panel-header">

                <div>

                    <h2>Product List</h2>

                    <p>
                        Products currently stored in the database.
                    </p>

                </div>

            </div>


            <div class="table-responsive">

                <table class="admin-table">

                    <thead>

                        <tr>

                            <th>Product</th>

                            <th>Category</th>

                            <th>Price</th>

                            <th>Stock</th>

                            <th>Status</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody id="productTableBody">

                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($product = $result->fetch_assoc()): ?>

                            <?php

                            $imagePath = !empty($product["image"])
                                ? "/assets/images/products/" .
                                  htmlspecialchars($product["image"])
                                : "";

                            ?>

                            <tr
                                data-category="<?php echo strtolower(htmlspecialchars($product["category_name"])); ?>"
                            >

                                <!-- PRODUCT -->

                                <td>

                                    <div class="admin-product-info">

                                        <?php if (!empty($product["image"])): ?>

                                            <img
                                                src="<?php echo $imagePath; ?>"
                                                alt="<?php echo htmlspecialchars($product["name"]); ?>"
                                                class="admin-product-image"
                                            >

                                        <?php else: ?>

                                            <div class="admin-product-placeholder">
                                                PRODUCT
                                            </div>

                                        <?php endif; ?>


                                        <div>

                                            <strong>
                                                <?php
                                                echo htmlspecialchars(
                                                    $product["name"]
                                                );
                                                ?>
                                            </strong>

                                            <small>
                                                ID:
                                                <?php
                                                echo htmlspecialchars(
                                                    $product["id"]
                                                );
                                                ?>
                                            </small>

                                        </div>

                                    </div>

                                </td>


                                <!-- CATEGORY -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $product["category_name"]
                                    );
                                    ?>

                                </td>


                                <!-- PRICE -->

                                <td>

                                    R<?php
                                    echo number_format(
                                        $product["price"],
                                        2
                                    );
                                    ?>

                                </td>


                                <!-- STOCK -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $product["stock"]
                                    );
                                    ?>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <?php if ($product["status"] === "active"): ?>

                                        <span class="admin-status active">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="admin-status inactive">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="admin-product-actions">

                                        <a
                                            href="edit-product.php?id=<?php echo $product["id"]; ?>"
                                            class="admin-edit-button"
                                        >
                                            Edit
                                        </a>


                                        <?php if ($product["status"] === "active"): ?>

                                            <form
                                                method="POST"
                                                style="display:inline;"
                                                onsubmit="return confirm('Are you sure you want to deactivate this product?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="deactivate"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="productId"
                                                    value="<?php echo $product["id"]; ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="admin-delete-button"
                                                >
                                                    Deactivate
                                                </button>

                                            </form>

                                        <?php else: ?>

                                            <span class="admin-status inactive">
                                                Unavailable
                                            </span>

                                        <?php endif; ?>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="6"
                                class="text-center"
                            >
                                No products found.
                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>


<!-- =========================
     JAVASCRIPT
     ========================= -->

<script>

const productSearch =
    document.getElementById("productSearch");

const categoryFilter =
    document.getElementById("categoryFilter");

const productRows =
    document.querySelectorAll(
        "#productTableBody tr"
    );


function filterProducts() {

    const searchValue =
        productSearch.value
            .toLowerCase()
            .trim();

    const categoryValue =
        categoryFilter.value
            .toLowerCase();

    productRows.forEach(function(row) {

        const productText =
            row.innerText.toLowerCase();

        const rowCategory =
            row.dataset.category || "";

        const matchesSearch =
            productText.includes(searchValue);

        const matchesCategory =
            categoryValue === "all" ||
            rowCategory === categoryValue;

        if (
            matchesSearch &&
            matchesCategory
        ) {

            row.style.display = "";

        } else {

            row.style.display = "none";

        }

    });

}


productSearch.addEventListener(
    "input",
    filterProducts
);


categoryFilter.addEventListener(
    "change",
    filterProducts
);

</script>


<?php include '../includes/footer.php'; ?>
