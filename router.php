<?php
// Use: php -S localhost:8001 router.php
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH) ?: '/');
if (preg_match('~(?:^|/)[.]|^/(?:includes|database|vendor|tests)(?:/|$)|[.](?:sql|log|lock|md)$|^/(?:composer[.]json|test-email[.]php)$~i',$path)) { http_response_code(404); exit('Not found'); }
$resolved=realpath(__DIR__.$path);
if ($resolved && !str_starts_with($resolved,__DIR__.DIRECTORY_SEPARATOR) && $resolved!==__DIR__) { http_response_code(404);exit; }
return false;
