<?php

require_once '../includes/auth.php';

require_once '../includes/db.php';

$message = "";
$messageType = "";

if (isset($_SESSION["user_id"])) {

    if (
        isset($_SESSION["user_role"]) &&
        $_SESSION["user_role"] === "admin"
    ) {
        header("Location: ../admin/index.php");
        exit;
    }

    $destination=($_SESSION["after_login"] ?? "") === "checkout.php" ? "checkout.php" : "account.php"; unset($_SESSION["after_login"]); header("Location: ".$destination);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    checkCsrf();

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $message = "Please enter your email address and password.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } else {
        // Throttle by email and remote address; only a hash is persisted.
        $bucket=hash('sha256',strtolower($email).'|'.($_SERVER['REMOTE_ADDR'] ?? ''));
        $attempt=$conn->prepare("INSERT INTO auth_attempts(bucket,attempts,started_at) VALUES (?,1,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE attempts=IF(started_at < DATE_SUB(UTC_TIMESTAMP(),INTERVAL 15 MINUTE),1,LEAST(attempts+1,100000)), started_at=IF(started_at < DATE_SUB(UTC_TIMESTAMP(),INTERVAL 15 MINUTE),UTC_TIMESTAMP(),started_at)");
        $attempt->bind_param('s',$bucket);$attempt->execute();
        $attempt=$conn->prepare('SELECT attempts FROM auth_attempts WHERE bucket=?');$attempt->bind_param('s',$bucket);$attempt->execute();
        if (($attempt->get_result()->fetch_assoc()['attempts'] ?? 11)>10) { http_response_code(429); exit('Too many sign-in attempts. Please wait 15 minutes and try again.'); }

        $stmt = $conn->prepare(
            "SELECT
                id,
                name,
                email,
                password,
                role,
                email_verified, is_active, auth_version
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (!password_verify($password, $user["password"])) {

                $message =
                    "Incorrect email or password.";

                $messageType = "error";

            } elseif (!(int)$user["is_active"]) {
                $message = "This account is inactive. Please contact the shop."; $messageType = "error";
            } elseif ((int)$user["email_verified"] !== 1) {

                $message =
                    "Please verify your email address before logging in. Check your inbox for the verification email.";

                $messageType = "error";

            } else {

                $clear=$conn->prepare('DELETE FROM auth_attempts WHERE bucket=?');$clear->bind_param('s',$bucket);$clear->execute();
                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["user_role"] = $user["role"];
                $_SESSION["login_time"] = time();
                $_SESSION["auth_version"] = (int)$user["auth_version"];

                $stmt->close();

                if ($user["role"] === "admin") {

                    header("Location: ../admin/index.php");
                    exit;
                }

                $destination=($_SESSION["after_login"] ?? "") === "checkout.php" ? "checkout.php" : "account.php"; unset($_SESSION["after_login"]); header("Location: ".$destination);
                exit;
            }

        } else {

            $message =
                "Incorrect email or password.";

            $messageType = "error";
        }

        $stmt->close();
    }
}

?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main class="auth-page">

    <div class="auth-container">

        <div class="auth-card">

            <div class="auth-header">

                <h1>Welcome Back</h1>

                <p>
                    Login to your TE_Crown Hair account.
                </p>

            </div>

            <?php if ($message !== ""): ?>

                <div class="admin-form-message <?php echo htmlspecialchars($messageType); ?>">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>

            <form
                method="POST"
                class="auth-form"
            ><?php echo csrfField(); ?>

                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        autocomplete="username"
                        placeholder="Enter your email"
                        value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        placeholder="Enter your password"
                        required
                    >

                </div>

                <button
                    type="submit"
                    class="auth-submit"
                >
                    LOGIN
                </button>

                <p class="recovery-login-link"><a href="forgot-password.php">Forgot your password?</a></p>

                <p class="auth-switch">

                    Don't have an account?

                    <a href="register.php">
                        Create Account
                    </a>

                </p>

            </form>

        </div>

    </div>

</main>

<?php include '../includes/footer.php'; ?>
