<?php

session_start();

// Only logged-in users can access the account page
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

include '../includes/header.php';
include '../includes/navbar.php';

?>

<main>

    <!-- ==============================
         ACCOUNT HEADER
         ============================== -->

    <section class="account-header">

        <div class="container text-center">

            <p class="section-label">
                TE_CROWN HAIR
            </p>

            <h1>My Account</h1>

            <p>
                Manage your account and view your orders.
            </p>

        </div>

    </section>


    <!-- ==============================
         ACCOUNT
         ============================== -->

    <section class="account-section">

        <div class="container">

            <div class="row g-5">

                <!-- ACCOUNT DETAILS -->

                <div class="col-lg-4">

                    <div class="account-card">

                        <h2>Account Details</h2>

                        <div class="account-detail">

                            <span>Name</span>

                            <strong>
                                <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
                            </strong>

                        </div>


                        <div class="account-detail">

                            <span>Email</span>

                            <strong>
                                <?php echo htmlspecialchars($_SESSION["user_email"]); ?>
                            </strong>

                        </div>




                        <!-- REAL LOGOUT -->

                        <a
                            href="logout.php"
                            class="logout-button"
                        >
                            LOGOUT
                        </a>

                    </div>

                </div>


                <!-- ORDER HISTORY -->

                <div class="col-lg-8">

                    <div class="account-card">

                        <div class="account-title-row">

                            <h2>Order History</h2>

                            <span>
                                2 Orders
                            </span>

                        </div>


                        <!-- ORDER 1 -->

                        <div class="order-history-item">

                            <div>

                                <span class="order-label">
                                    ORDER #TC1001
                                </span>

                                <h3>
                                    Straight Lace Wig
                                </h3>

                                <p>
                                    29 September 2026
                                </p>

                            </div>


                            <div class="order-info">

                                <strong>
                                    R2,300
                                </strong>

                                <span class="order-status">
                                    Processing
                                </span>

                            </div>

                        </div>


                        <!-- ORDER 2 -->

                        <div class="order-history-item">

                            <div>

                                <span class="order-label">
                                    ORDER #TC1000
                                </span>

                                <h3>
                                    Premium Hair Extensions
                                </h3>

                                <p>
                                    20 September 2026
                                </p>

                            </div>


                            <div class="order-info">

                                <strong>
                                    R1,200
                                </strong>

                                <span class="order-status completed">
                                    Completed
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

</main>

<?php
include '../includes/footer.php';
?>
