<?php
require __DIR__.'/../includes/mail.php';
putenv('BREVO_API_KEY=mock-key');putenv('BREVO_SENDER_EMAIL=sender@example.invalid');
function check($value,$label){if(!$value)throw new RuntimeException($label);echo 'PASS: '.$label.PHP_EOL;}
$send=fn($reply)=>sendBrevoMessage('customer@example.invalid','Test','Subject','<p>test</p>',fn($body,$key)=>$reply);
check($send(['status'=>201,'body'=>'{"messageId":"mock-id"}','error'=>0])['success'],'provider acceptance with message ID');
foreach([['status'=>401,'body'=>'{"message":"invalid key"}'],['status'=>400,'body'=>'{"message":"sender invalid"}'],['status'=>201,'body'=>'{}'],['status'=>0,'body'=>false,'error'=>28],['status'=>201,'body'=>'invalid JSON']] as $case)check(!$send($case)['success'],'provider/timeout/malformed response produces failure');
$r=sendBrevoMessage('customer@example.invalid','Test','Subject','body',function(){throw new Error('mock fatal');});check(!$r['success'],'PHP Error is caught without blank-page failure');
check(!sendBrevoMessage('invalid','Test','Subject','body',function(){throw new Exception('must not send');})['success'],'invalid recipient rejected before sending');
