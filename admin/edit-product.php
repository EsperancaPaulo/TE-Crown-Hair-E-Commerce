<?php

require_once '../includes/auth.php';

requireAdmin();

require_once '../includes/db.php';

$message = "";
$messageType = "";


/*
|--------------------------------------------------------------------------
| Get Product ID
|--------------------------------------------------------------------------
*/

$productId = isset($_GET['id'])
    ? intval($_GET['id'])
    : intval($_POST['productId'] ?? 0);

if ($productId <= 0) {

    header("Location: products.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Handle Product Update
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    checkCsrf();

    $productName = trim($_POST["productName"] ?? "");
    $categoryId = intval($_POST["category"] ?? 0);
    $description = trim($_POST["description"] ?? "");
    $price = floatval($_POST["price"] ?? 0);
    $stock = intval($_POST["stock"] ?? 0);
    $status = $_POST["status"] ?? "active";


    $categoryCheck=$conn->prepare('SELECT id FROM categories WHERE id=?'); $categoryCheck->bind_param('i',$categoryId); $categoryCheck->execute(); if (!$categoryCheck->get_result()->num_rows) $categoryId=0;

    /*
    |--------------------------------------------------------------------------
    | Validate Data
    |--------------------------------------------------------------------------
    */

    if (
        $productName === "" ||
        $categoryId <= 0 ||
        $price <= 0 || !preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', (string)($_POST['price'] ?? '')) ||
        $stock < 0 || !in_array($status, ['active','inactive'], true) || strlen($productName)>150 || !preg_match('/^\d+$/', (string)($_POST['stock'] ?? ''))
    ) {

        $message =
            "Please complete all required fields correctly.";

        $messageType = "error";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Get Existing Image
        |--------------------------------------------------------------------------
        */

        $imageName = null;

        $imageStmt = $conn->prepare(
            "SELECT image FROM products WHERE id = ?"
        );

        $imageStmt->bind_param(
            "i",
            $productId
        );

        $imageStmt->execute();

        $imageResult =
            $imageStmt->get_result();

        if ($imageResult->num_rows === 0) {

            $message =
                "Product could not be found.";

            $messageType = "error";

        } else {

            $existingProduct =
                $imageResult->fetch_assoc();

            $imageName =
                $existingProduct["image"];

        }

        $imageStmt->close();


        /*
        |--------------------------------------------------------------------------
        | Handle New Image
        |--------------------------------------------------------------------------
        */

        require_once '../includes/upload.php';
        try { $imageName = productUpload($_FILES['productImage'] ?? null, $imageName); }
        catch (RuntimeException $e) { $message=$e->getMessage(); $messageType='error'; }

        /*
        |--------------------------------------------------------------------------
        | Update Product
        |--------------------------------------------------------------------------
        */

        if ($messageType !== "error") {

            $sql = "
                UPDATE products
                SET
                    category_id = ?,
                    name = ?,
                    description = ?,
                    price = ?,
                    image = ?,
                    stock = ?,
                    status = ?
                WHERE id = ?
            ";

            $stmt =
                $conn->prepare($sql);

            if ($stmt) {

                $stmt->bind_param(
                    "issdsisi",
                    $categoryId,
                    $productName,
                    $description,
                    $price,
                    $imageName,
                    $stock,
                    $status,
                    $productId
                );

                if ($stmt->execute()) {

                    header(
                        "Location: products.php?success=updated"
                    );

                    exit;

                } else {

                    $message =
                        "Failed to update product: " .
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
| Get Current Product
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        category_id,
        name,
        description,
        price,
        image,
        stock,
        status
    FROM products
    WHERE id = ?
";

$stmt =
    $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $productId
);

$stmt->execute();

$result =
    $stmt->get_result();

if ($result->num_rows === 0) {

    $stmt->close();

    header("Location: products.php");
    exit;

}

$product =
    $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Get Categories
|--------------------------------------------------------------------------
*/

$categories =
    $conn->query(
        "SELECT id, name FROM categories ORDER BY name ASC"
    );

?>

<?php require_once '../includes/admin-layout.php'; adminShellStart('Products'); ?>

        <!-- PAGE HEADER -->

        <div class="admin-page-header">

            <div>

                <h1>Edit Product</h1>

                <p>
                    Update the selected product information.
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
             EDIT FORM
             ========================= -->

        <div class="admin-panel admin-form-panel">

            <div class="admin-form-header">

                <h2>
                    <?php
                    echo htmlspecialchars(
                        $product["name"]
                    );
                    ?>
                </h2>

                <p>
                    Product ID:
                    <?php
                    echo htmlspecialchars(
                        $product["id"]
                    );
                    ?>
                </p>

            </div>


            <form
                method="POST"
                enctype="multipart/form-data"
                class="admin-product-form"
            ><?php echo csrfField(); ?>

                <input
                    type="hidden"
                    name="productId"
                    value="<?php echo $product["id"]; ?>"
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
                        value="<?php echo htmlspecialchars($product["name"]); ?>"
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
                                    value="<?php echo $category["id"]; ?>"
                                    <?php
                                    if (
                                        $category["id"] ==
                                        $product["category_id"]
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $category["name"]
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
                    ><?php
                    echo htmlspecialchars(
                        $product["description"]
                    );
                    ?></textarea>

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
                            value="<?php echo htmlspecialchars($product["price"]); ?>"
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
                            value="<?php echo htmlspecialchars($product["stock"]); ?>"
                            required
                        >

                    </div>

                </div>


                <!-- CURRENT IMAGE -->

                <?php if (!empty($product["image"])): ?>

                    <div class="admin-form-group">

                        <label>
                            Current Product Image
                        </label>

                        <div class="admin-current-image">

                            <img
                                src="/assets/images/products/<?php echo htmlspecialchars($product["image"]); ?>"
                                alt="<?php echo htmlspecialchars($product["name"]); ?>"
                            >

                        </div>

                    </div>

                <?php endif; ?>


                <!-- NEW IMAGE -->

                <div class="admin-form-group">

                    <label for="productImage">
                        Replace Product Image
                    </label>

                    <input
                        type="file"
                        id="productImage"
                        name="productImage"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small class="admin-form-help">
                        Leave empty to keep the current image.
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

                        <option
                            value="active"
                            <?php
                            echo $product["status"] === "active"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            <?php
                            echo $product["status"] === "inactive"
                                ? "selected"
                                : "";
                            ?>
                        >
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
                        Save Changes
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>


<?php include '../includes/admin-footer.php'; ?>