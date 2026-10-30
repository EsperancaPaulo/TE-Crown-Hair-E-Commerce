<?php
mysqli_report(MYSQLI_REPORT_OFF);
$env=[];
foreach (@file(__DIR__.'/../.env', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
    if (str_contains($line,'=') && !str_starts_with(trim($line),'#')) { [$k,$v]=explode('=',$line,2); $env[trim($k)]=trim(trim($v), "\"'"); }
}
$conn = @new mysqli(getenv('DB_HOST') ?: ($env['DB_HOST'] ?? 'localhost'), getenv('DB_USER') ?: ($env['DB_USER'] ?? 'root'), getenv('DB_PASSWORD') ?: ($env['DB_PASSWORD'] ?? ''), getenv('DB_NAME') ?: ($env['DB_NAME'] ?? 'te_crown_hair'));
if ($conn->connect_errno) { http_response_code(503); exit('The shop database is unavailable. Please try again later.'); }
$conn->set_charset('utf8mb4');
$conn->query("SET time_zone = '+00:00'");
