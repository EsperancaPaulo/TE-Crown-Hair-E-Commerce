<?php
require_once '../includes/auth.php';require_once '../includes/db.php';require_once '../includes/order-view.php';require_once '../includes/password-reset.php';
header('Referrer-Policy: no-referrer');header('X-Frame-Options: DENY');
$error='';$complete=isset($_GET['updated']) && !empty($_SESSION['password_reset_complete']);
unset($_SESSION['password_reset_complete']);
if ($_SERVER['REQUEST_METHOD']==='POST') {
    checkCsrf();
    $password=$_POST['password'] ?? null;$confirmation=$_POST['confirm_password'] ?? null;
    try {
        if (!passwordResetThrottle($conn,'complete-ip|'.($_SERVER['REMOTE_ADDR'] ?? ''),20,900)) {
            http_response_code(429);$error='Too many reset attempts. Please wait 15 minutes and request a new link.';
        } elseif (!validRecoveryPassword($password)) {
            http_response_code(422);$error='Use at least 12 characters. If your passphrase is very long, choose a shorter one.';
        } elseif (!is_string($confirmation) || !hash_equals($password,$confirmation)) {
            http_response_code(422);$error='The passwords do not match. Please enter them again.';
        } else {
            $userId=completePasswordReset($conn,$_POST['token'] ?? null,$password);
            if (!$userId) {http_response_code(422);$error='This reset link is invalid, expired or already used. Request a new link.';}
            else {
                // Other sessions for this account are rejected by auth_version on their next request.
                if ((int)($_SESSION['user_id'] ?? 0)===$userId) {$_SESSION=[];session_regenerate_id(true);$_SESSION['csrf']=bin2hex(random_bytes(32));}
                $_SESSION['password_reset_complete']=true;
                header('Location: reset-password.php?updated=1',true,303);exit;
            }
        }
    } catch (Throwable $exception) {http_response_code(503);$error='Your password could not be reset. Please try again later.';}
}
include '../includes/header.php';include '../includes/navbar.php'; ?>
<main class="recovery-page"><section class="recovery-card" aria-labelledby="resetHeading"><p class="account-eyebrow">A FRESH START</p><h1 id="resetHeading"><?php echo $complete?'Password updated':'Choose a new password'; ?></h1>
<?php if ($complete): ?><p class="enquiry-notice" role="status">Your password has been updated. Existing sessions for this account have been signed out. Sign in with your new password.</p><a class="recovery-primary" href="login.php">Go to login</a><?php if (isset($_SESSION['user_id'])): ?><p class="recovery-small">You are still signed in to a different account in this browser. Log out of that account before signing in with the recovered account.</p><?php endif; ?>
<?php else: ?><p class="recovery-intro">Use a unique passphrase with at least 12 characters. Don’t share it in chat or reuse a password from another site.</p><?php if ($error): ?><p class="enquiry-notice enquiry-error" role="alert"><?php echo e($error); ?></p><?php endif; ?>
<p id="resetLinkNotice" class="recovery-small" role="status">Open the complete link from your reset email to continue. JavaScript must be enabled to read the secure link.</p>
<form method="post" id="passwordResetForm" class="recovery-form"><?php echo csrfField(); ?><input type="hidden" name="token" id="resetToken" value="<?php echo e(is_string($_POST['token'] ?? null) && preg_match('/^[a-f0-9]{64}$/',$_POST['token']) ? $_POST['token'] : ''); ?>"><fieldset id="resetFields" disabled><label for="newPassword">New password</label><input type="password" name="password" id="newPassword" class="form-control" required minlength="12" maxlength="72" autocomplete="new-password"><label for="confirmPassword">Confirm new password</label><input type="password" name="confirm_password" id="confirmPassword" class="form-control" required minlength="12" maxlength="72" autocomplete="new-password"><button type="submit" class="recovery-primary">Save new password</button></fieldset></form>
<p class="recovery-small">For your security, only the newest unused link works.</p><a class="recovery-back" href="forgot-password.php">Request a new reset link</a><script src="/assets/js/password-reset.js" defer></script>
<?php endif; ?></section></main><?php include '../includes/footer.php'; ?>
