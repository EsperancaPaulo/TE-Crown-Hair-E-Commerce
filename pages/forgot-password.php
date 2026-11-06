<?php
require_once '../includes/auth.php'; require_once '../includes/db.php'; require_once '../includes/order-view.php'; require_once '../includes/password-reset.php';
header('Referrer-Policy: no-referrer');
header('X-Frame-Options: DENY');
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    checkCsrf();
    $email=$_POST['email'] ?? '';
    if (!is_string($email) || strlen(trim($email))>150 || !filter_var(trim($email),FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);$error='Please enter a valid email address.';
    } else {
        try {
            $baseUrl=passwordRecoveryUrl();
            // No session lock is held while contacting the mail provider.
            session_write_close();
            require_once '../includes/mail.php';
            requestPasswordReset($conn,trim($email),$_SERVER['REMOTE_ADDR'] ?? '',$baseUrl,'sendPasswordResetEmail');
            header('Location: forgot-password.php?requested=1',true,303);exit;
        } catch (Throwable $exception) {
            http_response_code(503);$error='Password recovery is temporarily unavailable. Please try again later. No password has been changed.';
            if (session_status()!==PHP_SESSION_ACTIVE) session_start();
        }
    }
}
include '../includes/header.php';include '../includes/navbar.php'; ?>
<main class="recovery-page"><section class="recovery-card" aria-labelledby="recoveryHeading"><p class="account-eyebrow">LET’S GET YOU BACK IN</p><h1 id="recoveryHeading">Forgot your password?</h1><p class="recovery-intro">Enter the email address registered to your account. You’ll choose your new password privately using a secure email link.</p>
<?php if ($error): ?><p class="enquiry-notice enquiry-error" role="alert"><?php echo e($error); ?></p><?php endif; ?>
<?php if (isset($_GET['requested'])): ?><div class="enquiry-notice" role="status">If this email belongs to an active, verified account, we’ll try to send a reset link. Check your inbox and spam folder. We can’t confirm delivery here. If no message arrives, the email service may be unavailable; try again later or contact the shop. Requests are limited to protect your account.</div><?php endif; ?>
<form method="post" class="recovery-form"><?php echo csrfField(); ?><label for="recoveryEmail">Registered email address</label><input class="form-control" id="recoveryEmail" name="email" type="email" maxlength="150" required autocomplete="email"><button class="recovery-primary" type="submit">Request reset link</button></form><p class="recovery-small">Links expire after 30 minutes. Only the latest link can be used. Requesting a link does not change your password.</p><a class="recovery-back" href="login.php">Back to login</a></section></main>
<?php include '../includes/footer.php'; ?>
