<?php

session_start();

require_once '../includes/db.php';
require_once '../includes/mail.php';

$message = "";
$messageType = "";

if (isset($_SESSION["user_id"])) {
    if (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin") {
        header("Location: ../admin/index.php");
        exit;
    }

    header("Location: account.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if ($name === "" || $email === "" || $password === "" || $confirmPassword === "") {

        $message = "Please complete all required fields.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } elseif (strlen($password) < 8) {

        $message = "Password must be at least 8 characters long.";
        $messageType = "error";

    } elseif ($password !== $confirmPassword) {

        $message = "Passwords do not match.";
        $messageType = "error";

    } else {

        $checkStmt = $conn->prepare(
            "SELECT id, email_verified
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();

        $existingUser = $checkStmt->get_result()->fetch_assoc();

        $checkStmt->close();

        if ($existingUser) {

            if ((int)$existingUser["email_verified"] === 0) {

                $message = "An account with this email already exists but has not been verified. Please check your email for the verification link.";
                $messageType = "error";

            } else {

                $message = "An account with this email address already exists. Please log in.";
                $messageType = "error";
            }

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $verificationToken = bin2hex(
                random_bytes(32)
            );

            $verificationExpires = date(
                "Y-m-d H:i:s",
                time() + (30 * 60)
            );

            $role = "customer";

            $stmt = $conn->prepare(
                "INSERT INTO users
                (
                    name,
                    email,
                    password,
                    role,
                    email_verified,
                    verification_token,
                    verification_expires
                )
                VALUES (?, ?, ?, ?, 0, ?, ?)"
            );

            $stmt->bind_param(
                "ssssss",
                $name,
                $email,
                $hashedPassword,
                $role,
                $verificationToken,
                $verificationExpires
            );

            if ($stmt->execute()) {

                $verificationLink =
                    "http://localhost:8001/pages/verify-email.php?token="
                    . urlencode($verificationToken);

                $emailResult = sendVerificationEmail(
                    $email,
                    $name,
                    $verificationLink
                );

                if ($emailResult["success"]) {

                    $message =
                        "Your account has been created. Please check your email and click the verification link before logging in.";

                    $messageType = "success";

                } else {

                    $message =
                        "Your account was created, but we could not send the verification email. Please contact the administrator.";

                    $messageType = "error";
                }

            } else {

                $message =
                    "Unable to create your account. Please try again.";

                $messageType = "error";
            }

            $stmt->close();
        }
    }
}

?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main class="auth-page">

    <div class="auth-container">

        <div class="auth-card">

            <div class="auth-header">

                <h1>Create Account</h1>

                <p>
                    Create your TE_Crown Hair account.
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
                novalidate
            >

                <div class="form-group">

                    <label for="name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your full name"
                        value="<?php echo htmlspecialchars($_POST["name"] ?? ""); ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
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
                        placeholder="Create a password"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="confirm_password">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm your password"
                        required
                    >

                </div>

                <button
                    type="submit"
                    class="auth-submit"
                >
                    CREATE ACCOUNT
                </button>

                <p class="auth-switch">

                    Already have an account?

                    <a href="login.php">
                        Login
                    </a>

                </p>

            </form>

        </div>

    </div>

</main>

<?php include '../includes/footer.php'; ?>
