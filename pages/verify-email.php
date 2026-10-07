<?php

session_start();

require_once '../includes/db.php';

$message = "";
$messageType = "error";

$token = trim($_GET["token"] ?? "");

if ($token === "") {

    $message = "Invalid verification link.";

} else {

    $stmt = $conn->prepare(
        "SELECT id, email, verification_expires
         FROM users
         WHERE verification_token = ?
         AND email_verified = 0
         LIMIT 1"
    );

    $stmt->bind_param("s", $token);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        $expires = strtotime($user["verification_expires"]);

        if ($expires === false || $expires < time()) {

            $message =
                "This verification link has expired. Please register again or request a new verification email.";

        } else {

            $updateStmt = $conn->prepare(
                "UPDATE users
                 SET email_verified = 1,
                     verification_token = NULL,
                     verification_expires = NULL
                 WHERE id = ?"
            );

            $updateStmt->bind_param(
                "i",
                $user["id"]
            );

            if ($updateStmt->execute()) {

                $message =
                    "Your email address has been successfully verified. You can now log in to your TE_Crown Hair account.";

                $messageType = "success";

            } else {

                $message =
                    "We could not verify your email address. Please try again.";
            }

            $updateStmt->close();
        }

    } else {

        $message =
            "This verification link is invalid or has already been used.";
    }

    $stmt->close();
}

?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main class="auth-page">

    <div class="auth-container">

        <div class="auth-card">

            <div class="auth-header">

                <?php if ($messageType === "success"): ?>

                    <h1>Email Verified</h1>

                    <p>
                        Your TE_Crown Hair account is now verified.
                    </p>

                <?php else: ?>

                    <h1>Verification Failed</h1>

                    <p>
                        We could not verify your email address.
                    </p>

                <?php endif; ?>

            </div>

            <div class="admin-form-message <?php echo htmlspecialchars($messageType); ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

            <?php if ($messageType === "success"): ?>

                <div style="text-align: center; margin-top: 25px;">

                    <a
                        href="login.php"
                        class="auth-submit"
                        style="display: inline-block; text-decoration: none;"
                    >
                        LOGIN TO YOUR ACCOUNT
                    </a>

                </div>

            <?php else: ?>

                <div style="text-align: center; margin-top: 25px;">

                    <a
                        href="register.php"
                        class="auth-submit"
                        style="display: inline-block; text-decoration: none;"
                    >
                        RETURN TO REGISTRATION
                    </a>

                </div>

            <?php endif; ?>

        </div>

    </div>

</main>

<?php include '../includes/footer.php'; ?>
