<?php
// Password recovery services. Transport is supplied explicitly so tests never send email.
function passwordRecoveryUrl() {
    global $env;
    $configured=getenv('APP_URL') ?: ($env['APP_URL'] ?? '');
    if ($configured==='') {
        $host=$_SERVER['HTTP_HOST'] ?? '';
        if (!preg_match('/^(localhost|127\.0\.0\.1|\[::1\])(?::[0-9]{1,5})?$/i',$host)) {
            throw new RuntimeException('Configure APP_URL before enabling email recovery.');
        }
        $configured=(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off'?'https://':'http://').$host;
    }
    $parts=parse_url($configured);
    $host=strtolower($parts['host'] ?? '');
    $local=in_array($host,['localhost','127.0.0.1','[::1]','::1'],true);
    if (!$parts || !in_array($parts['scheme'] ?? '',$local?['http','https']:['https'],true) || !$host || (isset($parts['user']) || isset($parts['pass'])) || isset($parts['query']) || isset($parts['fragment']) || !in_array($parts['path'] ?? '',['','/'],true)) {
        throw new RuntimeException('APP_URL must be the trusted HTTPS site origin (HTTP is allowed on loopback).');
    }
    return rtrim($configured,'/');
}

function passwordResetThrottle($conn,$scope,$limit,$seconds) {
    $bucket=hash('sha256','password-reset|'.$scope);
    $stmt=$conn->prepare("INSERT INTO auth_attempts(bucket,attempts,started_at) VALUES (?,1,UTC_TIMESTAMP())
        ON DUPLICATE KEY UPDATE attempts=IF(started_at<DATE_SUB(UTC_TIMESTAMP(),INTERVAL ? SECOND),1,LEAST(attempts+1,100000)),
        started_at=IF(started_at<DATE_SUB(UTC_TIMESTAMP(),INTERVAL ? SECOND),UTC_TIMESTAMP(),started_at)");
    $stmt->bind_param('sii',$bucket,$seconds,$seconds);$stmt->execute();
    $stmt=$conn->prepare('SELECT attempts FROM auth_attempts WHERE bucket=?');$stmt->bind_param('s',$bucket);$stmt->execute();
    return (int)$stmt->get_result()->fetch_assoc()['attempts']<=$limit;
}

function requestPasswordReset($conn,$email,$address,$baseUrl,callable $send) {
    // Both existing and unknown accounts consume the same per-address and per-email limits.
    $ipAllowed=passwordResetThrottle($conn,'request-ip|'.$address,10,3600);
    $emailAllowed=passwordResetThrottle($conn,'request-email|'.strtolower($email),3,3600);
    if (!$ipAllowed || !$emailAllowed) return;
    $stmt=$conn->prepare('SELECT id,name,email FROM users WHERE email=? AND is_active=1 AND email_verified=1 LIMIT 1');
    $stmt->bind_param('s',$email);$stmt->execute();$user=$stmt->get_result()->fetch_assoc();
    if (!$user) return;
    $raw=bin2hex(random_bytes(32));$hash=hash('sha256',$raw);
    $stmt=$conn->prepare("INSERT INTO password_resets(user_id,token_hash,expires_at,mail_status) VALUES (?,?,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 30 MINUTE),'pending')
        ON DUPLICATE KEY UPDATE token_hash=VALUES(token_hash),expires_at=VALUES(expires_at),mail_status='pending',created_at=UTC_TIMESTAMP()");
    $stmt->bind_param('is',$user['id'],$hash);$stmt->execute();
    // Fragments never enter server access logs or ordinary Referer headers.
    $link=$baseUrl.'/pages/reset-password.php#token='.$raw;
    try { $accepted=(bool)$send($user['email'],$user['name'],$link); }
    catch (Throwable $exception) { $accepted=false; }
    $status=$accepted?'sent':'failed';
    $stmt=$conn->prepare('UPDATE password_resets SET mail_status=? WHERE user_id=? AND token_hash=?');
    $stmt->bind_param('sis',$status,$user['id'],$hash);$stmt->execute();
    // Do not log recipient, token, request, or transport exception details.
    if (!$accepted) error_log('Password recovery email was not accepted by the configured mail service.');
}

function validRecoveryPassword($password) {
    if (!is_string($password) || strlen($password)>72 || str_contains($password,"\0")) return false;
    $characters=preg_match_all('/./us',$password);
    return $characters!==false && $characters>=12;
}

function completePasswordReset($conn,$raw,$password) {
    if (!is_string($raw) || !preg_match('/^[a-f0-9]{64}$/',$raw) || !validRecoveryPassword($password)) return false;
    $hash=hash('sha256',$raw);
    mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
    $conn->begin_transaction();
    try {
        // Match the authentication lock order used by normal account operations.
        $stmt=$conn->prepare("SELECT r.user_id FROM password_resets r WHERE token_hash=? AND mail_status='sent' AND expires_at>UTC_TIMESTAMP()");
        $stmt->bind_param('s',$hash);$stmt->execute();$candidate=$stmt->get_result()->fetch_assoc();
        if (!$candidate) {$conn->rollback();return false;}
        $stmt=$conn->prepare('SELECT id,is_active,email_verified FROM users WHERE id=? FOR UPDATE');$stmt->bind_param('i',$candidate['user_id']);$stmt->execute();$user=$stmt->get_result()->fetch_assoc();
        $stmt=$conn->prepare("SELECT user_id FROM password_resets WHERE token_hash=? AND mail_status='sent' AND expires_at>UTC_TIMESTAMP() FOR UPDATE");$stmt->bind_param('s',$hash);$stmt->execute();$current=$stmt->get_result()->fetch_assoc();
        if (!$current || !$user || !$user['is_active'] || !$user['email_verified']) {$conn->rollback();return false;}
        $newHash=password_hash($password,PASSWORD_DEFAULT);
        $stmt=$conn->prepare('UPDATE users SET password=?,auth_version=auth_version+1 WHERE id=?');$stmt->bind_param('si',$newHash,$user['id']);$stmt->execute();
        $stmt=$conn->prepare('DELETE FROM password_resets WHERE user_id=?');$stmt->bind_param('i',$user['id']);$stmt->execute();
        $conn->commit();
        return (int)$user['id'];
    } catch (Throwable $exception) {$conn->rollback();throw $exception;}
}
