<?php

/*
 * Get the current page name
 */

$currentPage =
    basename($_SERVER['PHP_SELF']);


/*
 * Determine active navigation section
 */

$shopPages = [
    'shop.php',
    'product.php'
];

$accountPages = [
    'login.php',
    'register.php',
    'account.php'
];

$cartPages = [
    'cart.php',
    'checkout.php',
    'order-confirmation.php'
];


/*
 * Check active sections
 */

$isHome =
    $currentPage === 'index.php';

$isShop =
    in_array($currentPage, $shopPages);

$isAbout =
    $currentPage === 'about.php';

$isContact =
    $currentPage === 'contact.php';

$isAccount =
    in_array($currentPage, $accountPages);

$isCart =
    in_array($currentPage, $cartPages);

?>

<nav class="navbar navbar-expand-lg">

    <div class="container">

        <!-- Logo / Brand -->

       <a
    class="navbar-brand"
    href="/index.php"
>
    <img
        src="/assets/images/logo/logo.png"
        alt="TE_Crown Hair Logo"
        class="navbar-logo"
    >

    <span class="navbar-brand-name">
        TE_Crown Hair
    </span>
</a>


        <!-- Mobile Menu Button -->

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
            aria-controls="mainNavbar"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <!-- Navigation -->

        <div
            class="collapse navbar-collapse"
            id="mainNavbar"
        >

            <ul class="navbar-nav mx-auto">


                <!-- HOME -->

                <li class="nav-item">

                    <a
                        class="nav-link <?php echo $isHome ? 'active' : ''; ?>"
                        href="/index.php"
                        <?php echo $isHome ? 'aria-current="page"' : ''; ?>
                    >
                        Home
                    </a>

                </li>


                <!-- SHOP -->

                <li class="nav-item">

                    <a
                        class="nav-link <?php echo $isShop ? 'active' : ''; ?>"
                        href="/pages/shop.php"
                        <?php echo $isShop ? 'aria-current="page"' : ''; ?>
                    >
                        Shop
                    </a>

                </li>


                <!-- ABOUT -->

                <li class="nav-item">

                    <a
                        class="nav-link <?php echo $isAbout ? 'active' : ''; ?>"
                        href="/pages/about.php"
                        <?php echo $isAbout ? 'aria-current="page"' : ''; ?>
                    >
                        About
                    </a>

                </li>


                <!-- CONTACT -->

                <li class="nav-item">

                    <a
                        class="nav-link <?php echo $isContact ? 'active' : ''; ?>"
                        href="/pages/contact.php"
                        <?php echo $isContact ? 'aria-current="page"' : ''; ?>
                    >
                        Contact
                    </a>

                </li>

            </ul>


            <!-- Right-side actions -->

            <div class="navbar-actions">


                <!-- ACCOUNT -->

                <a
                    class="<?php echo $isAccount ? 'active' : ''; ?>"
                    href="/pages/login.php"
                    <?php echo $isAccount ? 'aria-current="page"' : ''; ?>
                >
                    Account
                </a>


                <!-- CART -->

                <a
                    class="<?php echo $isCart ? 'active' : ''; ?>"
                    href="/pages/cart.php"
                    <?php echo $isCart ? 'aria-current="page"' : ''; ?>
                >
                    Cart
                </a>

            </div>

        </div>

    </div>

</nav>
