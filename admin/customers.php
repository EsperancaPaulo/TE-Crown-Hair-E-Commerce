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

            <a href="/admin/customers.php" class="admin-nav-link active">
                <span>👥</span>
                Customers
            </a>

            <a href="/admin/orders.php" class="admin-nav-link">
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
                <span class="admin-page-label">Customer Management</span>
                <h1>Customers</h1>
            </div>

            <div class="admin-user">
                <span class="admin-user-icon">👤</span>
                <span>Administrator</span>
            </div>

        </div>


        <!-- CUSTOMER STATISTICS -->
        <div class="admin-stat-grid">

            <div class="admin-stat-card">

                <div class="admin-stat-icon">
                    👥
                </div>

                <div>
                    <span>Total Customers</span>
                    <strong>24</strong>
                </div>

            </div>


            <div class="admin-stat-card">

                <div class="admin-stat-icon">
                    ✓
                </div>

                <div>
                    <span>Active Customers</span>
                    <strong>21</strong>
                </div>

            </div>


            <div class="admin-stat-card">

                <div class="admin-stat-icon">
                    +
                </div>

                <div>
                    <span>New This Month</span>
                    <strong>5</strong>
                </div>

            </div>

        </div>


        <!-- CUSTOMER PANEL -->
        <div class="admin-panel">

            <div class="admin-panel-header">

                <div>
                    <h2>Customer Accounts</h2>
                    <p>View and manage registered customers.</p>
                </div>

            </div>


            <!-- SEARCH -->
            <div class="admin-customer-toolbar">

                <div class="admin-customer-search">

                    <input
                        type="text"
                        id="customerSearch"
                        placeholder="Search customers by name or email..."
                    >

                </div>

                <select id="customerStatusFilter">

                    <option value="all">
                        All Customers
                    </option>

                    <option value="active">
                        Active
                    </option>

                    <option value="inactive">
                        Inactive
                    </option>

                </select>

            </div>


            <!-- TABLE -->
            <div class="admin-table-wrapper">

                <table class="admin-table" id="customerTable">

                    <thead>

                        <tr>

                            <th>Customer</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Registered</th>
                            <th>Status</th>
                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>

                        <tr data-status="active">

                            <td>

                                <div class="admin-customer-info">

                                    <div class="admin-customer-avatar">
                                        SG
                                    </div>

                                    <div>
                                        <strong>Sarah Gumede</strong>
                                        <small>Customer #001</small>
                                    </div>

                                </div>

                            </td>

                            <td>
                                sarah@example.com
                            </td>

                            <td>
                                071 234 5678
                            </td>

                            <td>
                                12 Sep 2026
                            </td>

                            <td>
                                <span class="admin-status active">
                                    Active
                                </span>
                            </td>

                            <td>

                                <div class="admin-customer-actions">

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="viewCustomer('Sarah Gumede')"
                                    >
                                        View
                                    </button>

                                    <button
                                        type="button"
                                        class="admin-delete-button"
                                        onclick="toggleCustomer(this, 'Sarah Gumede')"
                                    >
                                        Deactivate
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <tr data-status="active">

                            <td>

                                <div class="admin-customer-info">

                                    <div class="admin-customer-avatar">
                                        LN
                                    </div>

                                    <div>
                                        <strong>Lerato Ndlovu</strong>
                                        <small>Customer #002</small>
                                    </div>

                                </div>

                            </td>

                            <td>
                                lerato@example.com
                            </td>

                            <td>
                                072 456 7890
                            </td>

                            <td>
                                15 Sep 2026
                            </td>

                            <td>
                                <span class="admin-status active">
                                    Active
                                </span>
                            </td>

                            <td>

                                <div class="admin-customer-actions">

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="viewCustomer('Lerato Ndlovu')"
                                    >
                                        View
                                    </button>

                                    <button
                                        type="button"
                                        class="admin-delete-button"
                                        onclick="toggleCustomer(this, 'Lerato Ndlovu')"
                                    >
                                        Deactivate
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <tr data-status="inactive">

                            <td>

                                <div class="admin-customer-info">

                                    <div class="admin-customer-avatar">
                                        AM
                                    </div>

                                    <div>
                                        <strong>Ayanda Mokoena</strong>
                                        <small>Customer #003</small>
                                    </div>

                                </div>

                            </td>

                            <td>
                                ayanda@example.com
                            </td>

                            <td>
                                073 345 6789
                            </td>

                            <td>
                                20 Aug 2026
                            </td>

                            <td>
                                <span class="admin-status inactive">
                                    Inactive
                                </span>
                            </td>

                            <td>

                                <div class="admin-customer-actions">

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="viewCustomer('Ayanda Mokoena')"
                                    >
                                        View
                                    </button>

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="toggleCustomer(this, 'Ayanda Mokoena')"
                                    >
                                        Activate
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <tr data-status="active">

                            <td>

                                <div class="admin-customer-info">

                                    <div class="admin-customer-avatar">
                                        TN
                                    </div>

                                    <div>
                                        <strong>Thandi Nkosi</strong>
                                        <small>Customer #004</small>
                                    </div>

                                </div>

                            </td>

                            <td>
                                thandi@example.com
                            </td>

                            <td>
                                078 567 1234
                            </td>

                            <td>
                                25 Sep 2026
                            </td>

                            <td>
                                <span class="admin-status active">
                                    Active
                                </span>
                            </td>

                            <td>

                                <div class="admin-customer-actions">

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="viewCustomer('Thandi Nkosi')"
                                    >
                                        View
                                    </button>

                                    <button
                                        type="button"
                                        class="admin-delete-button"
                                        onclick="toggleCustomer(this, 'Thandi Nkosi')"
                                    >
                                        Deactivate
                                    </button>

                                </div>

                            </td>

                        </tr>


                        <tr data-status="active">

                            <td>

                                <div class="admin-customer-info">

                                    <div class="admin-customer-avatar">
                                        KM
                                    </div>

                                    <div>
                                        <strong>Karabo Molefe</strong>
                                        <small>Customer #005</small>
                                    </div>

                                </div>

                            </td>

                            <td>
                                karabo@example.com
                            </td>

                            <td>
                                079 678 2345
                            </td>

                            <td>
                                28 Sep 2026
                            </td>

                            <td>
                                <span class="admin-status active">
                                    Active
                                </span>
                            </td>

                            <td>

                                <div class="admin-customer-actions">

                                    <button
                                        type="button"
                                        class="admin-edit-button"
                                        onclick="viewCustomer('Karabo Molefe')"
                                    >
                                        View
                                    </button>

                                    <button
                                        type="button"
                                        class="admin-delete-button"
                                        onclick="toggleCustomer(this, 'Karabo Molefe')"
                                    >
                                        Deactivate
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
   CUSTOMER SEARCH
   ============================== */

const customerSearch =
    document.getElementById("customerSearch");

const customerStatusFilter =
    document.getElementById("customerStatusFilter");

const customerRows =
    document.querySelectorAll("#customerTable tbody tr");


function filterCustomers() {

    const searchTerm =
        customerSearch.value.toLowerCase().trim();

    const selectedStatus =
        customerStatusFilter.value;

    customerRows.forEach(function(row) {

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


customerSearch.addEventListener(
    "input",
    filterCustomers
);

customerStatusFilter.addEventListener(
    "change",
    filterCustomers
);


/* ==============================
   VIEW CUSTOMER
   ============================== */

function viewCustomer(customerName) {

    alert(
        "Customer profile:\n\n" +
        customerName +
        "\n\nCustomer details will be connected to the database later."
    );

}


/* ==============================
   ACTIVATE / DEACTIVATE
   ============================== */

function toggleCustomer(button, customerName) {

    const row = button.closest("tr");

    const status =
        row.querySelector(".admin-status");

    if (row.dataset.status === "active") {

        const confirmed = confirm(
            "Deactivate " +
            customerName +
            "?"
        );

        if (!confirmed) {
            return;
        }

        row.dataset.status = "inactive";

        status.textContent = "Inactive";

        status.classList.remove("active");

        status.classList.add("inactive");

        button.textContent = "Activate";

        button.classList.remove("admin-delete-button");

        button.classList.add("admin-edit-button");

    } else {

        row.dataset.status = "active";

        status.textContent = "Active";

        status.classList.remove("inactive");

        status.classList.add("active");

        button.textContent = "Deactivate";

        button.classList.remove("admin-edit-button");

        button.classList.add("admin-delete-button");

    }

}

</script>


<?php
include '../includes/footer.php';
?>
