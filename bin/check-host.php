<?php
// Deployment diagnostics only. Never reveal environment values or accept web requests.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$failures = 0;
function hostCheck($ok, $label) {
    global $failures;
    echo ($ok ? 'PASS: ' : 'FAIL: ').$label.PHP_EOL;
    if (!$ok) $failures++;
}
hostCheck(version_compare(PHP_VERSION, '8.4.1', '>='), 'PHP meets the committed dependency minimum (8.4.1)');
foreach (['mysqli', 'curl', 'fileinfo', 'json', 'openssl'] as $extension) {
    hostCheck(extension_loaded($extension), 'PHP extension '.$extension);
}
hostCheck(function_exists('mysqli_stmt_get_result'), 'mysqli native result support');
hostCheck(is_file(__DIR__.'/../vendor/autoload.php'), 'Composer dependencies installed');
hostCheck(is_writable(__DIR__.'/../assets/images/products'), 'Product image directory writable');
$settings=[];
foreach (@file(__DIR__.'/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
    if (str_contains($line, '=') && !str_starts_with(trim($line), '#')) {
        [$key,$value]=explode('=', $line, 2);$settings[trim($key)]=trim(trim($value), "\"'");
    }
}
foreach (['DB_HOST','DB_USER','DB_NAME','BREVO_API_KEY','BREVO_SENDER_EMAIL'] as $key) {
    hostCheck((getenv($key) ?: ($settings[$key] ?? '')) !== '', $key.' configured (value hidden)');
}
$url=getenv('APP_URL') ?: ($settings['APP_URL'] ?? '');$parts=parse_url($url);
hostCheck(is_array($parts) && ($parts['scheme'] ?? '') === 'https' && !empty($parts['host']) && !in_array(strtolower($parts['host']), ['localhost','127.0.0.1','::1','[::1]'], true) && !isset($parts['user']) && !isset($parts['pass']) && !isset($parts['query']) && !isset($parts['fragment']) && in_array($parts['path'] ?? '', ['', '/'], true), 'APP_URL is a public HTTPS origin');
echo 'Also verify database migrations, HTTPS sessions, web-server deny rules, backups and real email delivery using HOSTING.md.'.PHP_EOL;
exit($failures ? 1 : 0);
