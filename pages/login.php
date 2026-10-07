<?php

session_start();

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

    header("Location: account.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $message = "Please enter your email address and password.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } else {

        $stmt = $conn->prepare(
            "SELECT
                id,
                name,
                email,
                password,
                role,
                email_verified
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

            } elseif ((int)$user["email_verified"] !== 1) {

                $message =
                    "Please verify your email address before logging in. Check your inbox for the verification email.";

                $messageType = "error";

            } else {

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["user_role"] = $user["role"];
                $_SESSION["login_time"] = time();

                $stmt->close();

                if ($user["role"] === "admin") {

                    header("Location: ../admin/index.php");
                    exit;
                }

                header("Location: account.php");
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
            >

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
