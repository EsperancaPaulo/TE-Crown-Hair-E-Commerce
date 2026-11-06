<?php
// Only fictional fixtures. The injected sender never makes a network/email call.
if (PHP_SAPI!=='cli' || !in_array(getenv('DB_NAME'),['te_crown_repair_test','te_crown_payment_test'],true)) exit("Use DB_NAME=te_crown_repair_test only.\n");
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/password-reset.php';
$base=$argv[1] ?? 'http://127.0.0.1:8017';
if (!preg_match('~^http://(?:127\.0\.0\.1|localhost):[0-9]+$~',$base)) exit("Use a loopback test server.\n");
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
function checkRecovery($condition,$label) {if (!$condition) throw new RuntimeException($label);echo 'PASS: '.$label."\n";}
function recoveryClient() {return tempnam(sys_get_temp_dir(),'recovery-cookies-');}
function recoveryHttp($jar,$path,$data=null) {
    global $base;
    $curl=curl_init($base.$path);
    curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_COOKIEJAR=>$jar,CURLOPT_COOKIEFILE=>$jar,CURLOPT_TIMEOUT=>8]);
    if ($data!==null) curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($data)]);
    $body=curl_exec($curl);if ($body===false) throw new RuntimeException('Local test request failed.');
    $result=[curl_getinfo($curl,CURLINFO_RESPONSE_CODE),$body,curl_getinfo($curl,CURLINFO_EFFECTIVE_URL)];unset($curl);return $result;
}
function recoveryCsrf($body) {if (!preg_match('/name="csrf" value="([a-f0-9]+)"/',$body,$matches)) throw new RuntimeException('Missing test CSRF.');return $matches[1];}
function recoveryLogin($jar,$email,$password) {
    [, $body]=recoveryHttp($jar,'/pages/login.php');
    return recoveryHttp($jar,'/pages/login.php',['csrf'=>recoveryCsrf($body),'email'=>$email,'password'=>$password]);
}
$adminJar=recoveryClient();$otherJar=recoveryClient();$resetJar=recoveryClient();
$stmt=$conn->prepare("SELECT id,role,email_verified,auth_version FROM users WHERE email='admin@example.invalid'");$stmt->execute();$user=$stmt->get_result()->fetch_assoc();
if (!$user) exit("Prepare the isolated fixtures first.\n");
$id=(int)$user['id'];$initialVersion=(int)$user['auth_version'];
foreach (['request-ip|fixture-request','request-email|admin@example.invalid','request-email|missing-reset@example.invalid','request-email|expired@example.invalid','request-email|neutral-response@example.invalid','complete-ip|127.0.0.1','request-ip|127.0.0.1'] as $scope) {
    $bucket=hash('sha256','password-reset|'.$scope);$clean=$conn->prepare('DELETE FROM auth_attempts WHERE bucket=?');$clean->bind_param('s',$bucket);$clean->execute();
}
$old='RepairTestPass123!';$new='RecoveredFixturePass123!';
try {
    [,,$url]=recoveryLogin($adminJar,'admin@example.invalid',$old);checkRecovery(str_contains($url,'/admin/index.php'),'fixture admin signs in before recovery');
    [,,$url]=recoveryLogin($otherJar,'other@example.invalid',$old);checkRecovery(str_contains($url,'/pages/account.php'),'separate customer session available');
    $sendCount=0;$captured='';
    $sender=function($email,$name,$link) use (&$sendCount,&$captured) {$sendCount++;$captured=$link;return true;};
    requestPasswordReset($conn,'missing-reset@example.invalid','fixture-request',$base,$sender);
    checkRecovery($sendCount===0,'unknown account sends no email');
    requestPasswordReset($conn,'expired@example.invalid','fixture-request',$base,$sender);
    checkRecovery($sendCount===0,'unverified account cannot bypass verification through recovery');
    // These are freshly prepared fixture addresses, never real customer data.
    requestPasswordReset($conn,'admin@example.invalid','fixture-request',$base,$sender);
    checkRecovery($sendCount===1,'request uses injected mock sender; no external email sent');
    parse_str(parse_url($captured,PHP_URL_FRAGMENT),$fragment);$first=$fragment['token'] ?? '';
    checkRecovery(strlen($first)===64 && parse_url($captured,PHP_URL_QUERY)===null,'random token uses URL fragment, not logged query string');
    $row=$conn->query('SELECT token_hash,mail_status FROM password_resets WHERE user_id='.$id)->fetch_assoc();
    checkRecovery($row['token_hash']!==$first && hash_equals(hash('sha256',$first),$row['token_hash']) && $row['mail_status']==='sent','database stores only token hash');
    [, $form]=recoveryHttp($resetJar,'/pages/reset-password.php');$csrf=recoveryCsrf($form);
    $fields=['csrf'=>$csrf,'token'=>$first,'password'=>$new,'confirm_password'=>$new];
    [$status]=recoveryHttp($resetJar,'/pages/reset-password.php',[...$fields,'csrf'=>'invalid']);checkRecovery($status===403,'reset requires valid CSRF');
    [$status]=recoveryHttp($resetJar,'/pages/reset-password.php',[...$fields,'token'=>str_repeat('c',64)]);checkRecovery($status===422,'wrong reset token rejected');
    [$status]=recoveryHttp($resetJar,'/pages/reset-password.php',[...$fields,'password'=>'short','confirm_password'=>'short']);checkRecovery($status===422,'short password rejected');
    [$status]=recoveryHttp($resetJar,'/pages/reset-password.php',[...$fields,'confirm_password'=>'DifferentFixturePassword']);checkRecovery($status===422,'password confirmation mismatch rejected');
    $conn->query('UPDATE password_resets SET expires_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 SECOND) WHERE user_id='.$id);
    [$status]=recoveryHttp($resetJar,'/pages/reset-password.php',$fields);checkRecovery($status===422,'expired token rejected');
    requestPasswordReset($conn,'admin@example.invalid','fixture-request',$base,$sender);parse_str(parse_url($captured,PHP_URL_FRAGMENT),$fragment);$second=$fragment['token'];
    checkRecovery($first!==$second && completePasswordReset($conn,$first,$new)===false,'new request replaces earlier token');
    $fields['token']=$second;
    [$status,$body]=recoveryHttp($resetJar,'/pages/reset-password.php',$fields);checkRecovery($status===200 && str_contains($body,'Password updated'),'correct reset completes through HTTP form');
    [$status]=recoveryHttp($resetJar,'/pages/reset-password.php',$fields);checkRecovery($status===422,'successful token cannot be reused');
    $after=$conn->query('SELECT role,email_verified,auth_version FROM users WHERE id='.$id)->fetch_assoc();
    checkRecovery($after['role']===$user['role'] && (int)$after['email_verified']===(int)$user['email_verified'] && (int)$after['auth_version']===$initialVersion+1,'reset preserves role and verification, advances session version');
    [,,$url]=recoveryHttp($adminJar,'/admin/index.php');checkRecovery(str_contains($url,'/pages/login.php'),'existing recovered-account session is revoked');
    [,,$url]=recoveryHttp($otherJar,'/pages/account.php');checkRecovery(str_contains($url,'/pages/account.php'),'another customer session remains valid');
    [, $body,$url]=recoveryLogin($resetJar,'admin@example.invalid',$old);checkRecovery(str_contains($body,'Incorrect email or password.'),'previous password no longer signs in');
    [,,$url]=recoveryLogin($resetJar,'admin@example.invalid',$new);checkRecovery(str_contains($url,'/admin/index.php'),'new password signs in with stored admin role');
    requestPasswordReset($conn,'admin@example.invalid','fixture-request',$base,function(){return false;});
    $row=$conn->query('SELECT token_hash,mail_status FROM password_resets WHERE user_id='.$id)->fetch_assoc();
    checkRecovery($row['mail_status']==='failed','mail failure is recorded without claiming delivery');
    $calls=0;requestPasswordReset($conn,'admin@example.invalid','fixture-request',$base,function()use(&$calls){$calls++;return true;});checkRecovery($calls===0,'per-email request throttle prevents further sends');
    $jar=recoveryClient();[, $form]=recoveryHttp($jar,'/pages/forgot-password.php');
    [$status,$body]=recoveryHttp($jar,'/pages/forgot-password.php',['csrf'=>recoveryCsrf($form),'email'=>'neutral-response@example.invalid']);
    checkRecovery($status===200 && str_contains($body,'If this email belongs to an active, verified account') && str_contains($body,'can’t confirm delivery'),'request response is account-neutral and does not promise delivery');
    unlink($jar);
    echo "Password recovery checks passed. Only isolated fixture passwords changed; no external email sent.\n";
} finally {
    // Restore only the known fictional admin fixture for subsequent tests.
    $hash=password_hash($old,PASSWORD_DEFAULT);$stmt=$conn->prepare('UPDATE users SET password=? WHERE id=?');$stmt->bind_param('si',$hash,$id);$stmt->execute();
    $stmt=$conn->prepare('DELETE FROM password_resets WHERE user_id=?');$stmt->bind_param('i',$id);$stmt->execute();
    foreach ([$adminJar,$otherJar,$resetJar] as $file) if (is_file($file)) unlink($file);
}
