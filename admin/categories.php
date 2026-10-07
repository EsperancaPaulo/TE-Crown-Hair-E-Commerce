<?php

require_once '../includes/auth.php';

requireAdmin();

require_once '../includes/db.php';

$message = "";
$messageType = "";


/*
|--------------------------------------------------------------------------
| Handle Category Actions
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | Add Category
    |--------------------------------------------------------------------------
    */

    if ($action === "add") {

        $name = trim($_POST["categoryName"] ?? "");
        $description = trim($_POST["description"] ?? "");


        if ($name === "") {

            $message =
                "Category name is required.";

            $messageType = "error";

        } else {

            $stmt = $conn->prepare(
                "INSERT INTO categories (name, description)
                 VALUES (?, ?)"
            );

            $stmt->bind_param(
                "ss",
                $name,
                $description
            );


            if ($stmt->execute()) {

                header(
                    "Location: categories.php?success=added"
                );

                exit;

            } else {

                if ($conn->errno === 1062) {

                    $message =
                        "This category already exists.";

                } else {

                    $message =
                        "Failed to add category: " .
                        $stmt->error;

                }

                $messageType = "error";

            }

            $stmt->close();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Edit Category
    |--------------------------------------------------------------------------
    */

    if ($action === "edit") {

        $categoryId =
            intval($_POST["categoryId"] ?? 0);

        $name =
            trim($_POST["categoryName"] ?? "");

        $description =
            trim($_POST["description"] ?? "");


        if (
            $categoryId <= 0 ||
            $name === ""
        ) {

            $message =
                "Please provide a valid category.";

            $messageType = "error";

        } else {

            $stmt = $conn->prepare(
                "UPDATE categories
                 SET name = ?, description = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "ssi",
                $name,
                $description,
                $categoryId
            );


            if ($stmt->execute()) {

                header(
                    "Location: categories.php?success=updated"
                );

                exit;

            } else {

                $message =
                    "Failed to update category: " .
                    $stmt->error;

                $messageType = "error";

            }

            $stmt->close();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Delete Category
    |--------------------------------------------------------------------------
    */

    if ($action === "delete") {

        $categoryId =
            intval($_POST["categoryId"] ?? 0);


        if ($categoryId > 0) {

            /*
             * Check whether products are using
             * this category before deleting it.
             */

            $checkStmt = $conn->prepare(
                "SELECT COUNT(*) AS product_count
                 FROM products
                 WHERE category_id = ?"
            );

            $checkStmt->bind_param(
                "i",
                $categoryId
            );

            $checkStmt->execute();

            $checkResult =
                $checkStmt->get_result();

            $countData =
                $checkResult->fetch_assoc();

            $productCount =
                intval($countData["product_count"]);

            $checkStmt->close();


            if ($productCount > 0) {

                $message =
                    "This category cannot be deleted because it is being used by " .
                    $productCount .
                    " product(s).";

                $messageType = "error";

            } else {

                $deleteStmt = $conn->prepare(
                    "DELETE FROM categories WHERE id = ?"
                );

                $deleteStmt->bind_param(
                    "i",
                    $categoryId
                );


                if ($deleteStmt->execute()) {

                    header(
                        "Location: categories.php?success=deleted"
                    );

                    exit;

                } else {

                    $message =
                        "Failed to delete category.";

                    $messageType = "error";

                }

                $deleteStmt->close();

            }

        }

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
            "Category added successfully.";

        $messageType = "success";

    }

    if ($_GET["success"] === "updated") {

        $message =
            "Category updated successfully.";

        $messageType = "success";

    }

    if ($_GET["success"] === "deleted") {

        $message =
            "Category deleted successfully.";

        $messageType = "success";

    }

}


/*
|--------------------------------------------------------------------------
| Fetch Categories
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        categories.id,
        categories.name,
        categories.description,
        categories.created_at,
        COUNT(products.id) AS product_count
    FROM categories
    LEFT JOIN products
        ON categories.id = products.category_id
    GROUP BY
        categories.id,
        categories.name,
        categories.description,
        categories.created_at
    ORDER BY categories.id ASC
";

$result =
    $conn->query($sql);

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
                class="admin-nav-link"
            >
                <span>🛍</span>
                Products
            </a>


            <a
                href="categories.php"
                class="admin-nav-link active"
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
                Category Management
            </div>

            <div class="admin-user">
                <span>Administrator</span>
            </div>

        </div>


        <!-- PAGE HEADER -->

        <div class="admin-page-header">

            <div>

                <h1>Categories</h1>

                <p>
                    Manage product categories for the TE_Crown Hair catalogue.
                </p>

            </div>

        </div>


        <!-- MESSAGE -->

        <?php if ($message !== ""): ?>

            <div
                class="admin-form-message <?php echo $messageType; ?>"
            >
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <!-- =========================
             ADD CATEGORY
             ========================= -->

        <div class="admin-panel admin-form-panel">

            <div class="admin-form-header">

                <h2>Add Category</h2>

                <p>
                    Create a new category for your products.
                </p>

            </div>


            <form
                method="POST"
                class="admin-product-form"
            >

                <input
                    type="hidden"
                    name="action"
                    value="add"
                >


                <div class="admin-form-group">

                    <label for="categoryName">
                        Category Name
                    </label>

                    <input
                        type="text"
                        id="categoryName"
                        name="categoryName"
                        placeholder="e.g. Hair Care"
                        required
                    >

                </div>


                <div class="admin-form-group">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        placeholder="Describe this category..."
                    ></textarea>

                </div>


                <div class="admin-form-actions">

                    <button
                        type="submit"
                        class="admin-primary-button"
                    >
                        + Add Category
                    </button>

                </div>

            </form>

        </div>


        <!-- =========================
             CATEGORY LIST
             ========================= -->

        <div class="admin-panel">

            <div class="admin-panel-header">

                <div>

                    <h2>Category List</h2>

                    <p>
                        Categories currently stored in the database.
                    </p>

                </div>

            </div>


            <div class="admin-category-grid">

                <?php if ($result && $result->num_rows > 0): ?>

                    <?php while ($category = $result->fetch_assoc()): ?>

                        <div class="admin-category-card">

                            <div class="admin-category-icon">
                                🗂
                            </div>


                            <div class="admin-category-info">

                                <h3>
                                    <?php
                                    echo htmlspecialchars(
                                        $category["name"]
                                    );
                                    ?>
                                </h3>


                                <p>

                                    <?php
                                    if (
                                        !empty(
                                            $category["description"]
                                        )
                                    ) {

                                        echo htmlspecialchars(
                                            $category["description"]
                                        );

                                    } else {

                                        echo "No description provided.";

                                    }
                                    ?>

                                </p>


                                <span>

                                    <?php
                                    echo intval(
                                        $category["product_count"]
                                    );
                                    ?>

                                    Product(s)

                                </span>

                            </div>


                            <div class="admin-category-actions">

                                <button
                                    type="button"
                                    class="admin-edit-button"
                                    onclick="editCategory(
                                        <?php echo $category["id"]; ?>,
                                        '<?php echo htmlspecialchars($category["name"], ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($category["description"] ?? "", ENT_QUOTES); ?>'
                                    )"
                                >
                                    Edit
                                </button>


                                <form
                                    method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this category?');"
                                >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete"
                                    >

                                    <input
                                        type="hidden"
                                        name="categoryId"
                                        value="<?php echo $category["id"]; ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="admin-delete-button"
                                    >
                                        Delete
                                    </button>

                                </form>

                            </div>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <p>
                        No categories found.
                    </p>

                <?php endif; ?>

            </div>

        </div>

    </main>

</div>


<!-- =========================
     EDIT CATEGORY MODAL
     ========================= -->

<div
    id="editCategoryModal"
    class="admin-modal"
>

    <div class="admin-modal-content">

        <div class="admin-modal-header">

            <h2>Edit Category</h2>

            <button
                type="button"
                onclick="closeEditModal()"
            >
                &times;
            </button>

        </div>


        <form
            method="POST"
            class="admin-product-form"
        >

            <input
                type="hidden"
                name="action"
                value="edit"
            >

            <input
                type="hidden"
                id="editCategoryId"
                name="categoryId"
            >


            <div class="admin-form-group">

                <label for="editCategoryName">
                    Category Name
                </label>

                <input
                    type="text"
                    id="editCategoryName"
                    name="categoryName"
                    required
                >

            </div>


            <div class="admin-form-group">

                <label for="editCategoryDescription">
                    Description
                </label>

                <textarea
                    id="editCategoryDescription"
                    name="description"
                    rows="4"
                ></textarea>

            </div>


            <div class="admin-form-actions">

                <button
                    type="button"
                    class="admin-secondary-button"
                    onclick="closeEditModal()"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="admin-primary-button"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>


<script>

function editCategory(
    id,
    name,
    description
) {

    document.getElementById(
        "editCategoryId"
    ).value = id;


    document.getElementById(
        "editCategoryName"
    ).value = name;


    document.getElementById(
        "editCategoryDescription"
    ).value = description;


    document.getElementById(
        "editCategoryModal"
    ).classList.add("show");

}


function closeEditModal() {

    document.getElementById(
        "editCategoryModal"
    ).classList.remove("show");

}

</script>


<?php include '../includes/footer.php'; ?>