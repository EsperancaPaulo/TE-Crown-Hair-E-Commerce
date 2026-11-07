<?php
function passwordRequirements() {
    return 'Use at least 12 characters, including an uppercase letter, a lowercase letter, a number and a symbol. Maximum 72 bytes.';
}
function passwordPolicyError($password) {
    if (!is_string($password) || str_contains($password,"\0") || strlen($password)>72) return passwordRequirements();
    $length=preg_match_all('/./us',$password);
    if ($length===false || $length<12 || !preg_match('/[A-Z]/',$password) || !preg_match('/[a-z]/',$password) || !preg_match('/[0-9]/',$password) || !preg_match('/[^A-Za-z0-9\s]/u',$password)) return passwordRequirements();
    return '';
}
