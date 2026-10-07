<?php

require_once '../includes/auth.php';

requireAdmin();

include '../includes/header.php';

?>

<div class="admin-layout">

    <!-- ==============================
         ADMIN SIDEBAR
         ============================== -->

    <aside class="admin-sidebar">

        <div class="admin-brand">

            <img
                src="../assets/images/logo/logo.png"
                alt="TE_Crown Hair Logo"
            >

            <h2>TE_Crown Hair</h2>

            <p>ADMIN PANEL</p>

        </div>


        <nav class="admin-navigation">

            <a
                href="index.php"
                class="admin-nav-link active"
            >
                Dashboard
            </a>

            <a
                href="products.php"
                class="admin-nav-link"
            >
                Products
            </a>

            <a
                href="categories.php"
                class="admin-nav-link"
            >
                Categories
            </a>

            <a
                href="customers.php"
                class="admin-nav-link"
            >
                Customers
            </a>

            <a
                href="orders.php"
                class="admin-nav-link"
            >
                Orders
            </a>

            <a
                href="../index.php"
                class="admin-nav-link"
            >
                View Website
            </a>

        </nav>


        <div class="admin-sidebar-bottom">

            <a
    href="../pages/logout.php"
    class="admin-logout"
>
    Logout
</a>

        </div>

    </aside>


    <!-- ==============================
         MAIN CONTENT
         ============================== -->

    <main class="admin-main">

        <div class="admin-topbar">

            <div>

                <p class="admin-page-label">
                    ADMIN PANEL
                </p>

                <h1>
                    Dashboard
                </h1>

            </div>

            <div class="admin-user">
                Administrator
            </div>

        </div>


        <!-- ==============================
             STATISTICS
             ============================== -->

        <section class="admin-stat-grid">

            <div class="admin-stat-card">

                <div class="admin-stat-icon">
                    P
                </div>

                <div>

                    <p>
                        Total Products
                    </p>

                    <h2>
                        8
                    </h2>

                </div>

            </div>


            <div class="admin-stat-card">

                <div class="admin-stat-icon">
                    C
                </div>

                <div>

                    <p>
                        Total Customers
                    </p>

                    <h2>
                        24
                    </h2>

                </div>

            </div>


            <div class="admin-stat-card">

                <div class="admin-stat-icon">
                    O
                </div>

                <div>

                    <p>
                        Total Orders
                    </p>

                    <h2>
                        12
                    </h2>

                </div>

            </div>


            <div class="admin-stat-card">

                <div class="admin-stat-icon">
                    R
                </div>

                <div>

                    <p>
                        Total Sales
                    </p>

                    <h2>
                        R18,450
                    </h2>

                </div>

            </div>

        </section>


        <!-- ==============================
             RECENT ORDERS
             ============================== -->

        <section class="admin-panel">

            <div class="admin-panel-header">

                <div>

                    <h2>
                        Recent Orders
                    </h2>

                    <p>
                        Overview of the latest customer orders.
                    </p>

                </div>

                <a
                    href="orders.php"
                    class="admin-view-all"
                >
                    View All
                </a>

            </div>


            <div class="admin-table-wrapper">

                <table class="admin-table">

                    <thead>

                        <tr>

                            <th>
                                Order ID
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <tr>

                            <td>
                                #TC001
                            </td>

                            <td>
                                Test Customer
                            </td>

                            <td>
                                02 Oct 2026
                            </td>

                            <td>
                                R2,300
                            </td>

                            <td>

                                <span class="status-badge status-processing">
                                    Processing
                                </span>

                            </td>

                            <td>

                                <a
                                    href="orders.php"
                                    class="admin-action-link"
                                >
                                    View
                                </a>

                            </td>

                        </tr>


                        <tr>

                            <td>
                                #TC002
                            </td>

                            <td>
                                Amanda Smith
                            </td>

                            <td>
                                01 Oct 2026
                            </td>

                            <td>
                                R3,500
                            </td>

                            <td>

                                <span class="status-badge status-completed">
                                    Completed
                                </span>

                            </td>

                            <td>

                                <a
                                    href="orders.php"
                                    class="admin-action-link"
                                >
                                    View
                                </a>

                            </td>

                        </tr>


                        <tr>

                            <td>
                                #TC003
                            </td>

                            <td>
                                Sarah Mokoena
                            </td>

                            <td>
                                30 Sep 2026
                            </td>

                            <td>
                                R1,850
                            </td>

                            <td>

                                <span class="status-badge status-pending">
                                    Pending
                                </span>

                            </td>

                            <td>

                                <a
                                    href="orders.php"
                                    class="admin-action-link"
                                >
                                    View
                                </a>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </section>


        <!-- ==============================
             QUICK ACTIONS
             ============================== -->

        <section class="admin-panel">

            <div class="admin-panel-header">

                <div>

                    <h2>
                        Quick Actions
                    </h2>

                    <p>
                        Manage the main areas of the store.
                    </p>

                </div>

            </div>


            <div class="admin-quick-actions">

                <a
                    href="add-product.php"
                    class="admin-quick-card"
                >

                    <strong>
                        Add Product
                    </strong>

                    <span>
                        Add a new product to the store.
                    </span>

                </a>


                <a
                    href="categories.php"
                    class="admin-quick-card"
                >

                    <strong>
                        Manage Categories
                    </strong>

                    <span>
                        Create and manage product categories.
                    </span>

                </a>


                <a
                    href="customers.php"
                    class="admin-quick-card"
                >

                    <strong>
                        View Customers
                    </strong>

                    <span>
                        Manage registered customers.
                    </span>

                </a>


                <a
                    href="orders.php"
                    class="admin-quick-card"
                >

                    <strong>
                        Manage Orders
                    </strong>

                    <span>
                        View and update customer orders.
                    </span>

                </a>

            </div>

        </section>

    </main>

</div>


<?php
include '../includes/footer.php';
?>
