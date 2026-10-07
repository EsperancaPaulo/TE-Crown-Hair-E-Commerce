<?php

/*
|--------------------------------------------------------------------------
| TE_Crown Hair Authentication Helper
|--------------------------------------------------------------------------
| Handles:
| - Login session checking
| - 120-minute session timeout
| - Customer/admin role checking
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Session timeout
|--------------------------------------------------------------------------
| 120 minutes = 7200 seconds
|--------------------------------------------------------------------------
*/

$session_timeout = 120 * 60;

if (isset($_SESSION["user_id"], $_SESSION["login_time"])) {

    if ((time() - $_SESSION["login_time"]) > $session_timeout) {

        // Session has expired
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {

            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        // Start a new session so we can display a message
        session_start();

        $_SESSION["session_expired"] = true;
    }
}


/*
|--------------------------------------------------------------------------
| Require Login
|--------------------------------------------------------------------------
| Use this on pages that require authentication.
|--------------------------------------------------------------------------
*/

function requireLogin()
{
    if (!isset($_SESSION["user_id"])) {

        header("Location: login.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Require Admin
|--------------------------------------------------------------------------
| Use this on admin pages.
|--------------------------------------------------------------------------
*/

function requireAdmin()
{
    if (!isset($_SESSION["user_id"])) {

        header("Location: ../pages/login.php");
        exit;
    }

    if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {

        header("Location: ../pages/account.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Require Customer
|--------------------------------------------------------------------------
| Use this when a page should only be accessible to customers.
|--------------------------------------------------------------------------
*/

function requireCustomer()
{
    if (!isset($_SESSION["user_id"])) {

        header("Location: login.php");
        exit;
    }

    if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "customer") {

        header("Location: ../admin/index.php");
        exit;
    }
}

?>
