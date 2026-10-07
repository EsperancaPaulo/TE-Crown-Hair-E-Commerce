<?php

require_once '../includes/auth.php';

requireAdmin();

require_once '../includes/db.php';

$message = "";
$messageType = "";

/*
|--------------------------------------------------------------------------
| Handle Product Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $productName = trim($_POST["productName"] ?? "");
    $categoryId = intval($_POST["category"]);
    $description = trim($_POST["description"] ?? "");
    $price = floatval($_POST["price"] ?? 0);
    $stock = intval($_POST["stock"] ?? 0);
    $status = $_POST["status"] ?? "active";

    /*
    |--------------------------------------------------------------------------
    | Basic Validation
    |--------------------------------------------------------------------------
    */

    if (
        $productName === "" ||
        $categoryId <= 0 ||
        $price <= 0 ||
        $stock < 0
    ) {

        $message = "Please complete all required fields correctly.";
        $messageType = "error";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Handle Image Upload
        |--------------------------------------------------------------------------
        */

        $imageName = null;

        if (
            isset($_FILES["productImage"]) &&
            $_FILES["productImage"]["error"] === UPLOAD_ERR_OK
        ) {

            $uploadDirectory =
                "../assets/images/products/";

            $originalName =
                $_FILES["productImage"]["name"];

            $temporaryFile =
                $_FILES["productImage"]["tmp_name"];

            $fileExtension =
                strtolower(
                    pathinfo(
                        $originalName,
                        PATHINFO_EXTENSION
                    )
                );

            $allowedExtensions = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];

            if (
                in_array(
                    $fileExtension,
                    $allowedExtensions
                )
            ) {

                $imageName =
                    strtolower(
                        preg_replace(
                            "/[^a-zA-Z0-9-_\.]/",
                            "-",
                            $originalName
                        )
                    );

                $destination =
                    $uploadDirectory . $imageName;

                move_uploaded_file(
                    $temporaryFile,
                    $destination
                );

            } else {

                $message =
                    "Invalid image format. Please upload JPG, JPEG, PNG or WEBP.";

                $messageType = "error";

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Insert Product
        |--------------------------------------------------------------------------
        */

        if ($messageType !== "error") {

            $sql = "
                INSERT INTO products
                (
                    category_id,
                    name,
                    description,
                    price,
                    image,
                    stock,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ";

            $stmt =
                $conn->prepare($sql);

            if ($stmt) {

                $stmt->bind_param(
                    "issdsis",
                    $categoryId,
                    $productName,
                    $description,
                    $price,
                    $imageName,
                    $stock,
                    $status
                );

                if ($stmt->execute()) {

                    header(
                        "Location: products.php?success=added"
                    );

                    exit;

                } else {

                    $message =
                        "Failed to add product: " .
                        $stmt->error;

                    $messageType = "error";

                }

                $stmt->close();

            } else {

                $message =
                    "Database error: " .
                    $conn->error;

                $messageType = "error";

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| Get Categories
|--------------------------------------------------------------------------
*/

$categories = $conn->query(
    "SELECT id, name FROM categories ORDER BY name ASC"
);

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

                <h1>Add Product</h1>

                <p>
                    Add a new product to the TE_Crown Hair catalogue.
                </p>

            </div>

        </div>


        <!-- ERROR MESSAGE -->

        <?php if ($message !== ""): ?>

            <div
                class="admin-form-message <?php echo $messageType; ?>"
            >
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <!-- =========================
             PRODUCT FORM
             ========================= -->

        <div class="admin-panel admin-form-panel">

            <div class="admin-form-header">

                <h2>Product Information</h2>

                <p>
                    Enter the product details below.
                </p>

            </div>


            <form
                method="POST"
                enctype="multipart/form-data"
                class="admin-product-form"
            >

                <!-- PRODUCT NAME -->

                <div class="admin-form-group">

                    <label for="productName">
                        Product Name
                    </label>

                    <input
                        type="text"
                        id="productName"
                        name="productName"
                        placeholder="e.g. Body Wave Wig"
                        required
                    >

                </div>


                <!-- CATEGORY -->

                <div class="admin-form-group">

                    <label for="category">
                        Category
                    </label>

                    <select
                        id="category"
                        name="category"
                        required
                    >

                        <option value="">
                            Select Category
                        </option>

                        <?php if ($categories): ?>

                            <?php while ($category = $categories->fetch_assoc()): ?>

                                <option
                                    value="<?php echo $category['id']; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $category['name']
                                    );
                                    ?>

                                </option>

                            <?php endwhile; ?>

                        <?php endif; ?>

                    </select>

                </div>


                <!-- DESCRIPTION -->

                <div class="admin-form-group">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        placeholder="Enter product description..."
                    ></textarea>

                </div>


                <!-- PRICE + STOCK -->

                <div class="admin-form-row">

                    <div class="admin-form-group">

                        <label for="price">
                            Price (R)
                        </label>

                        <input
                            type="number"
                            id="price"
                            name="price"
                            min="0.01"
                            step="0.01"
                            placeholder="2300.00"
                            required
                        >

                    </div>


                    <div class="admin-form-group">

                        <label for="stock">
                            Stock Quantity
                        </label>

                        <input
                            type="number"
                            id="stock"
                            name="stock"
                            min="0"
                            step="1"
                            placeholder="10"
                            required
                        >

                    </div>

                </div>


                <!-- IMAGE -->

                <div class="admin-form-group">

                    <label for="productImage">
                        Product Image
                    </label>

                    <input
                        type="file"
                        id="productImage"
                        name="productImage"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small class="admin-form-help">
                        Accepted formats: JPG, JPEG, PNG and WEBP.
                    </small>

                </div>


                <!-- STATUS -->

                <div class="admin-form-group">

                    <label for="status">
                        Product Status
                    </label>

                    <select
                        id="status"
                        name="status"
                    >

                        <option value="active">
                            Active
                        </option>

                        <option value="inactive">
                            Inactive
                        </option>

                    </select>

                </div>


                <!-- BUTTONS -->

                <div class="admin-form-actions">

                    <a
                        href="products.php"
                        class="admin-secondary-button"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="admin-primary-button"
                    >
                        Add Product
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>


<?php include '../includes/footer.php'; ?>