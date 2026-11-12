<?php
require __DIR__.'/../includes/mail.php';
putenv('BREVO_API_KEY=mock-key');putenv('BREVO_SENDER_EMAIL=sender@example.invalid');
function check($value,$label){if(!$value)throw new RuntimeException($label);echo 'PASS: '.$label.PHP_EOL;}
$send=fn($reply)=>sendBrevoMessage('customer@example.invalid','Test','Subject','<p>test</p>',fn($body,$key)=>$reply);
check($send(['status'=>201,'body'=>'{"messageId":"mock-id"}','error'=>0])['success'],'provider acceptance with message ID');
foreach([['status'=>401,'body'=>'{"message":"invalid key"}'],['status'=>400,'body'=>'{"message":"sender invalid"}'],['status'=>201,'body'=>'{}'],['status'=>0,'body'=>false,'error'=>28],['status'=>201,'body'=>'invalid JSON']] as $case)check(!$send($case)['success'],'provider/timeout/malformed response produces failure');
$r=sendBrevoMessage('customer@example.invalid','Test','Subject','body',function(){throw new Error('mock fatal');});check(!$r['success'],'PHP Error is caught without blank-page failure');
check(!sendBrevoMessage('invalid','Test','Subject','body',function(){throw new Exception('must not send');})['success'],'invalid recipient rejected before sending');

$delivery = ['firstName'=>'Example','lastName'=>'Customer','address'=>'1 Test Street','city'=>'Johannesburg','province'=>'Gauteng','postalCode'=>'2000','phone'=>'0712345678','notes'=>'Use <side> gate'];
$items = [['name'=>'Wig & Cap','colour'=>'Brown','length'=>'16','quantity'=>2,'price'=>'2300.00']];
$transport = static function ($body) {
    $message = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    check($message['to'][0]['email'] === 'customer@example.invalid', 'order confirmation addresses the supplied account email');
    check(str_contains($message['subject'], '#TC123'), 'order reference is included in the subject');
    foreach (['Wig &amp; Cap', 'Brown', '16 inches', 'R2,300.00', 'R4,600.00', 'Johannesburg', '0712345678', 'Use &lt;side&gt; gate', 'pending'] as $value) {
        check(str_contains($message['htmlContent'], $value), 'confirmation includes '. $value);
    }
    return ['status'=>201,'body'=>'{"messageId":"fixture-confirmation"}'];
};
check(sendOrderConfirmationEmail('customer@example.invalid','Example',123,$delivery,$items,'4600.00',$transport), 'confirmation succeeds with an accepted mock delivery');
