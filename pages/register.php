<?php

require_once '../includes/auth.php';

require_once '../includes/db.php';
require_once '../includes/mail.php';

$message = "";
$messageType = "";
$name = ""; $email = "";
require_once '../includes/password-reset.php';

if (isset($_SESSION["user_id"])) {
    if (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin") {
        header("Location: ../admin/index.php");
        exit;
    }

    header("Location: account.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    checkCsrf();
    try {

    $name = trim((is_string($_POST["name"] ?? null) ? $_POST["name"] : ""));
    $email = trim((is_string($_POST["email"] ?? null) ? $_POST["email"] : ""));
    if (($_POST['action'] ?? '') === 'resend') {
        if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>150) throw new InvalidArgumentException('Enter a valid email address to resend verification.');
        $allowedEmail=passwordResetThrottle($conn,'verification-email|'.strtolower($email),3,3600);
        $allowedIp=passwordResetThrottle($conn,'verification-ip|'.($_SERVER['REMOTE_ADDR']??''),10,3600);
        if (!$allowedEmail || !$allowedIp) { http_response_code(429);throw new InvalidArgumentException('Too many verification requests. Please wait an hour before trying again.'); }
        $lookup=$conn->prepare('SELECT id,name,email FROM users WHERE email=? AND email_verified=0 AND is_active=1');$lookup->bind_param('s',$email);$lookup->execute();$user=$lookup->get_result()->fetch_assoc();
        if ($user) {
            $token=bin2hex(random_bytes(32));$expires=gmdate('Y-m-d H:i:s',time()+1800);
            $update=$conn->prepare('UPDATE users SET verification_token=?,verification_expires=? WHERE id=? AND email_verified=0');$update->bind_param('ssi',$token,$expires,$user['id']);$update->execute();
            $result=sendVerificationEmail($user['email'],$user['name'],passwordRecoveryUrl().'/pages/verify-email.php?token='.urlencode($token));
            if (!$result['success']) throw new RuntimeException('Verification transport failed.');
        }
        $message='If this address belongs to an active account awaiting verification, a new verification email has been requested. Check your inbox and Spam/Junk. Use the newest link.';$messageType='success';
    } else {
    $password = (is_string($_POST["password"] ?? null) ? $_POST["password"] : "");
    $confirmPassword = (is_string($_POST["confirm_password"] ?? null) ? $_POST["confirm_password"] : "");

    if ($name === "" || $email === "" || $password === "" || $confirmPassword === "") {

        $message = "Please complete all required fields.";
        $messageType = "error";

    } elseif (strlen($name)>100 || strlen($email)>150 || strlen($password)>72 || str_contains($password,"\0")) {
        $message="Name, email or password is too long."; $messageType="error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } elseif (passwordPolicyError($password)!=='') {

        $message = passwordRequirements();
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

                $message = "This account is awaiting email verification. Use Resend verification email below to request a new link.";
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
                    passwordRecoveryUrl() . "/pages/verify-email.php?token="
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
                        "Your account was created, but we could not send the verification email. Please use Resend verification email below to try again shortly.";

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
    } catch (InvalidArgumentException $exception) {
        $message=$exception->getMessage();$messageType='error';
    } catch (Throwable $exception) {
        error_log('Account registration or verification could not be completed.');
        http_response_code(503);
        $message='We could not complete this request. If your account was already created, use Resend verification email below. Otherwise, try again shortly.';$messageType='error';
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

                <div class="admin-form-message <?php echo htmlspecialchars($messageType); ?>" role="alert" tabindex="-1">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>

            <form
                method="POST"
                class="auth-form"
            ><?php echo csrfField(); ?>

                <div class="form-group">

                    <label for="name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your full name"
                        value="<?php echo htmlspecialchars($name,ENT_QUOTES,'UTF-8'); ?>"
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
                        value="<?php echo htmlspecialchars($email,ENT_QUOTES,'UTF-8'); ?>"
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
                        placeholder="Create a strong password"
                        minlength="12" maxlength="72" autocomplete="new-password" aria-describedby="registrationPasswordHelp"
                        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9\s]).{12,72}"
                        title="At least 12 characters with uppercase, lowercase, a number and a symbol"
                        required
                    >

                </div>

                <p id="registrationPasswordHelp" style="font-size:.85rem;line-height:1.6;margin-bottom:20px"><?php echo htmlspecialchars(passwordRequirements(),ENT_QUOTES,'UTF-8'); ?></p>

                <div class="form-group">

                    <label for="confirm_password">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm your password"
                        minlength="12" maxlength="72" autocomplete="new-password"
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
            <details class="verification-resend" <?php echo $messageType==='error'?'open':''; ?> style="margin-top:24px">
                <summary>Resend verification email</summary>
                <form method="post" class="auth-form" style="margin-top:18px">
                    <?php echo csrfField(); ?><input type="hidden" name="action" value="resend">
                    <div class="form-group"><label for="resendEmail">Registered email address</label><input type="email" id="resendEmail" name="email" value="<?php echo htmlspecialchars($email,ENT_QUOTES,'UTF-8'); ?>" maxlength="150" required autocomplete="email"></div>
                    <button type="submit" class="auth-submit">RESEND VERIFICATION EMAIL</button>
                    <p style="font-size:.85rem;margin-top:12px">Links expire after 30 minutes. Check Spam/Junk and use the newest email.</p>
                </form>
            </details>

        </div>

    </div>

</main>

<?php include '../includes/footer.php'; ?>
