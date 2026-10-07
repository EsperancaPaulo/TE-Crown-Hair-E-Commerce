<?php

require_once '../includes/auth.php';

requireAdmin();

require_once '../includes/db.php';
?>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar">

        <div class="admin-brand">
            <img src="/assets/images/logo/logo.png" alt="TE_Crown Hair Logo">
            <h2>TE_Crown Hair</h2>
            <span>ADMIN PANEL</span>
        </div>

        <nav class="admin-navigation">

            <a href="/admin/index.php" class="admin-nav-link">
                <span>📊</span>
                Dashboard
            </a>

            <a href="/admin/products.php" class="admin-nav-link">
                <span>🛍️</span>
                Products
            </a>

            <a href="/admin/categories.php" class="admin-nav-link">
                <span>📁</span>
                Categories
            </a>

            <a href="/admin/customers.php" class="admin-nav-link">
                <span>👥</span>
                Customers
            </a>

            <a href="/admin/orders.php" class="admin-nav-link active">
                <span>📦</span>
                Orders
            </a>

            <a href="/index.php" class="admin-nav-link">
                <span>🌐</span>
                View Website
            </a>

        </nav>

        <div class="admin-sidebar-bottom">
            <a href="#" class="admin-logout">
                <span>🚪</span>
                Logout
            </a>
        </div>

    </aside>


    <!-- MAIN CONTENT -->
    <main class="admin-main">

        <!-- TOP BAR -->
        <div class="admin-topbar">

            <div>
                <span class="admin-page-label">Order Management</span>
                <h1>Orders</h1>
            </div>

            <div class="admin-user">
                <span class="admin-user-icon">👤</span>
                <span>Administrator</span>
            </div>

        </div>


        <!-- ORDER STATISTICS -->
        <div class="admin-stat-grid">

            <div class="admin-stat-card">

                <div class="admin-stat-icon">
                    📦
                </div>

                <div>
                    <span>Total Orders</span>
                    <strong>12</strong>
                </div>

            </div>


            <div class="admin-stat-card">

                <div class="admin-stat-icon">
                    ⏳
                </div>

                <div>
                    <span>Pending Orders</span>
                    <strong>3</strong>
                </div>

            </div>


            <div class="admin-stat-card">

                <div class="admin-stat-icon">
                    ✓
                </div>

                <div>
                    <span>Completed Orders</span>
                    <strong>7</strong>
                </div>

            </div>


            <div class="admin-stat-card">

                <div class="admin-stat-icon">
                    R
                </div>

                <div>
                    <span>Total Sales</span>
                    <strong>R18,450</strong>
                </div>

            </div>

        </div>


        <!-- ORDERS PANEL -->
        <div class="admin-panel">

            <div class="admin-panel-header">

                <div>
                    <h2>Customer Orders</h2>
                    <p>View and manage customer orders.</p>
                </div>

            </div>


            <!-- SEARCH AND FILTER -->
            <div class="admin-order-toolbar">

                <input
                    type="text"
                    id="orderSearch"
                    placeholder="Search by order ID or customer..."
                >

                <select id="orderStatusFilter">

                    <option value="all">
                        All Orders
                    </option>

                    <option value="pending">
                        Pending
                    </option>

                    <option value="processing">
                        Processing
                    </option>

                    <option value="shipped">
                        Shipped
                    </option>

                    <option value="completed">
                        Completed
                    </option>

                    <option value="cancelled">
                        Cancelled
                    </option>

                </select>

            </div>


            <!-- TABLE -->
            <div class="admin-table-wrapper">

                <table class="admin-table" id="ordersTable">

                    <thead>

                        <tr>

                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>

                        <!-- ORDER 1 -->
                        <tr data-status="completed">

                            <td>
                                <strong>#TC001</strong>
                            </td>

                            <td>
                                Sarah Gumede
                            </td>

                            <td>
                                29 Sep 2026
                            </td>

                            <td>
                                1
                            </td>

                            <td>
                                R2,300
                            </td>

                            <td>
                                <span class="admin-payment paid">
                                    Paid
                                </span>
                            </td>

                            <td>

                                <span class="admin-order-status completed">
                                    Completed
                                </span>

                            </td>

                            <td>

                                <div class="admin-order-actions">

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="viewOrder('TC001')"
                                    >
                                        View
                                    </button>

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="updateOrder('TC001')"
                                    >
                                        Update
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- ORDER 2 -->
                        <tr data-status="processing">

                            <td>
                                <strong>#TC002</strong>
                            </td>

                            <td>
                                Lerato Ndlovu
                            </td>

                            <td>
                                30 Sep 2026
                            </td>

                            <td>
                                2
                            </td>

                            <td>
                                R4,450
                            </td>

                            <td>
                                <span class="admin-payment paid">
                                    Paid
                                </span>
                            </td>

                            <td>

                                <span class="admin-order-status processing">
                                    Processing
                                </span>

                            </td>

                            <td>

                                <div class="admin-order-actions">

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="viewOrder('TC002')"
                                    >
                                        View
                                    </button>

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="updateOrder('TC002')"
                                    >
                                        Update
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- ORDER 3 -->
                        <tr data-status="pending">

                            <td>
                                <strong>#TC003</strong>
                            </td>

                            <td>
                                Ayanda Mokoena
                            </td>

                            <td>
                                30 Sep 2026
                            </td>

                            <td>
                                1
                            </td>

                            <td>
                                R1,800
                            </td>

                            <td>
                                <span class="admin-payment pending">
                                    Pending
                                </span>
                            </td>

                            <td>

                                <span class="admin-order-status pending">
                                    Pending
                                </span>

                            </td>

                            <td>

                                <div class="admin-order-actions">

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="viewOrder('TC003')"
                                    >
                                        View
                                    </button>

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="updateOrder('TC003')"
                                    >
                                        Update
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- ORDER 4 -->
                        <tr data-status="shipped">

                            <td>
                                <strong>#TC004</strong>
                            </td>

                            <td>
                                Thandi Nkosi
                            </td>

                            <td>
                                01 Oct 2026
                            </td>

                            <td>
                                1
                            </td>

                            <td>
                                R2,800
                            </td>

                            <td>
                                <span class="admin-payment paid">
                                    Paid
                                </span>
                            </td>

                            <td>

                                <span class="admin-order-status shipped">
                                    Shipped
                                </span>

                            </td>

                            <td>

                                <div class="admin-order-actions">

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="viewOrder('TC004')"
                                    >
                                        View
                                    </button>

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="updateOrder('TC004')"
                                    >
                                        Update
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- ORDER 5 -->
                        <tr data-status="cancelled">

                            <td>
                                <strong>#TC005</strong>
                            </td>

                            <td>
                                Karabo Molefe
                            </td>

                            <td>
                                01 Oct 2026
                            </td>

                            <td>
                                2
                            </td>

                            <td>
                                R2,100
                            </td>

                            <td>
                                <span class="admin-payment paid">
                                    Paid
                                </span>
                            </td>

                            <td>

                                <span class="admin-order-status cancelled">
                                    Cancelled
                                </span>

                            </td>

                            <td>

                                <div class="admin-order-actions">

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="viewOrder('TC005')"
                                    >
                                        View
                                    </button>

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="updateOrder('TC005')"
                                    >
                                        Update
                                    </button>

                                </div>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>


<script>

/* ==============================
   SEARCH & FILTER
   ============================== */

const orderSearch =
    document.getElementById("orderSearch");

const orderStatusFilter =
    document.getElementById("orderStatusFilter");

const orderRows =
    document.querySelectorAll("#ordersTable tbody tr");


function filterOrders() {

    const searchTerm =
        orderSearch.value.toLowerCase().trim();

    const selectedStatus =
        orderStatusFilter.value;

    orderRows.forEach(function(row) {

        const rowText =
            row.textContent.toLowerCase();

        const rowStatus =
            row.getAttribute("data-status");

        const matchesSearch =
            rowText.includes(searchTerm);

        const matchesStatus =
            selectedStatus === "all" ||
            rowStatus === selectedStatus;

        if (matchesSearch && matchesStatus) {

            row.style.display = "";

        } else {

            row.style.display = "none";

        }

    });

}


orderSearch.addEventListener(
    "input",
    filterOrders
);

orderStatusFilter.addEventListener(
    "change",
    filterOrders
);


/* ==============================
   VIEW ORDER
   ============================== */

function viewOrder(orderId) {

    alert(
        "Order Details\n\n" +
        "Order: #" + orderId +
        "\n\nOrder details will be connected to MySQL later."
    );

}


/* ==============================
   UPDATE ORDER
   ============================== */

function updateOrder(orderId) {

    const newStatus = prompt(
        "Enter new order status:\n\n" +
        "pending\n" +
        "processing\n" +
        "shipped\n" +
        "completed\n" +
        "cancelled"
    );

    if (!newStatus) {
        return;
    }

    const status =
        newStatus.toLowerCase().trim();

    const validStatuses = [
        "pending",
        "processing",
        "shipped",
        "completed",
        "cancelled"
    ];

    if (!validStatuses.includes(status)) {

        alert(
            "Invalid status. Please enter one of the listed statuses."
        );

        return;
    }

    alert(
        "Order #" +
        orderId +
        " updated to " +
        status +
        "."
    );

}

</script>


<?php
include '../includes/footer.php';
?>
