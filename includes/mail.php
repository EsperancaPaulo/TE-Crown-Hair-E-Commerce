<?php
// Direct HTTPS transport avoids shared-host restrictions in optional SDK transports.
function loadBrevoEnvironment() {
    $settings=[];
    foreach (@file(__DIR__.'/../.env',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        if (str_contains($line,'=') && !str_starts_with(trim($line),'#')) {
            [$key,$value]=explode('=',$line,2);$settings[trim($key)]=trim(trim($value),"\"'");
        }
    }
    return $settings;
}
function sendBrevoMessage($email,$name,$subject,$html,?callable $transport=null) {
    $settings=loadBrevoEnvironment();
    $key=getenv('BREVO_API_KEY') ?: ($settings['BREVO_API_KEY']??'');
    $sender=getenv('BREVO_SENDER_EMAIL') ?: ($settings['BREVO_SENDER_EMAIL']??'');
    $senderName=getenv('BREVO_SENDER_NAME') ?: ($settings['BREVO_SENDER_NAME']??'TE_Crown Hair');
    $failure=['success'=>false,'message'=>'The email service could not accept the message. Please try again shortly.'];
    if (!$key || !filter_var($sender,FILTER_VALIDATE_EMAIL) || !filter_var($email,FILTER_VALIDATE_EMAIL)) return $failure;
    try {
        $body=json_encode(['sender'=>['name'=>$senderName,'email'=>$sender],'to'=>[['email'=>$email,'name'=>$name]],'subject'=>$subject,'htmlContent'=>$html],JSON_THROW_ON_ERROR);
        if ($transport) { $response=$transport($body,$key); }
        else {
            if (!function_exists('curl_init')) return $failure;
            $curl=curl_init('https://api.brevo.com/v3/smtp/email');
            curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$body,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>12,CURLOPT_HTTPHEADER=>['accept: application/json','content-type: application/json','api-key: '.$key],CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2]);
            $raw=curl_exec($curl);$response=['status'=>(int)curl_getinfo($curl,CURLINFO_RESPONSE_CODE),'body'=>$raw,'error'=>curl_errno($curl)];
            unset($curl);
        }
        $decoded=json_decode(is_string($response['body']??null)?$response['body']:'',true);
        if (($response['error']??0)===0 && ($response['status']??0)===201 && is_string($decoded['messageId']??null) && $decoded['messageId']!=='') {
            return ['success'=>true,'message'=>'The email service accepted the message.','message_id'=>$decoded['messageId']];
        }
        // Log only status/error numbers: never credentials, recipient, link or provider payload.
        error_log('Transactional email not accepted; HTTP '.(int)($response['status']??0).'; transport '.(int)($response['error']??0));
    } catch (Throwable $exception) { error_log('Transactional email transport failed.'); }
    return $failure;
}
function sendVerificationEmail($recipientEmail,$recipientName,$verificationLink) {
    $name=htmlspecialchars($recipientName,ENT_QUOTES,'UTF-8');$url=htmlspecialchars($verificationLink,ENT_QUOTES,'UTF-8');
    return sendBrevoMessage($recipientEmail,$recipientName,'Verify Your TE_Crown Hair Account',"<div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:30px;background:#fdf8f5;color:#3f302a'><h2>Welcome to TE_Crown Hair</h2><p>Hello {$name},</p><p>Please verify your email address before logging in.</p><p><a href='{$url}' style='display:inline-block;padding:14px 24px;background:#6b4f45;color:white;text-decoration:none'>VERIFY MY EMAIL</a></p><p>This link expires in 30 minutes. Only the newest link works.</p><p>If you did not create this account, ignore this message.</p><p>TE_Crown Hair</p></div>");
}
function sendPasswordResetEmail($recipientEmail,$recipientName,$resetLink) {
    parse_str(parse_url($resetLink,PHP_URL_QUERY) ?? '',$resetParts);
    if (empty($resetParts['token'])) parse_str(parse_url($resetLink,PHP_URL_FRAGMENT) ?? '',$resetParts);
    $code=htmlspecialchars($resetParts['token']??'',ENT_QUOTES,'UTF-8');
    $plain=htmlspecialchars(preg_replace('/[?#].*$/','',$resetLink),ENT_QUOTES,'UTF-8');
    $name=htmlspecialchars($recipientName,ENT_QUOTES,'UTF-8');$url=htmlspecialchars($resetLink,ENT_QUOTES,'UTF-8');
    return sendBrevoMessage($recipientEmail,$recipientName,'Reset your TE_Crown Hair password',"<div style='font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:32px;background:#faf5ef;color:#513b2b'><h2>Reset your password</h2><p>Hello {$name},</p><p><a href='{$url}' style='display:inline-block;padding:14px 24px;background:#6b4f45;color:white;text-decoration:none'>CHOOSE A NEW PASSWORD</a></p><p>If the button does not open correctly, visit <a href='{$plain}' style='color:#6b4f45;font-weight:bold;text-decoration:underline'>the password reset page</a> and paste this recovery code:</p><p style='overflow-wrap:anywhere;font-family:monospace'>{$code}</p><p>This link expires in 30 minutes and can be used once. Only the newest link works.</p><p>If you did not request this, ignore this email. Your password has not changed.</p><p>TE_Crown Hair</p></div>")['success'];
}

function sendOrderConfirmationEmail(string $email, string $name, int $orderId, array $delivery, array $items, string $amount, ?callable $transport = null): bool
{
    $escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $money = static fn($value) => 'R'.number_format((float)$value, 2);
    $reference = '#TC'.$orderId;
    $date = (new DateTimeImmutable('now', new DateTimeZone('Africa/Johannesburg')))->format('d M Y, H:i').' SAST';
    $rows = '';
    foreach ($items as $item) {
        $options = [];
        if ($item['colour'] !== '') {
            $options[] = 'Colour: '.$item['colour'];
        }
        if ($item['length'] !== '') {
            $options[] = 'Length: '.$item['length'].' inches';
        }
        $description = $escape($item['name']);
        if ($options) {
            $description .= '<br><small>'.$escape(implode(' / ', $options)).'</small>';
        }
        $quantity = (int)$item['quantity'];
        $rows .= '<tr><td>'.$description.'</td><td>'.$quantity.'</td><td>'.$money($item['price'])
            .'</td><td>'.$money($item['price'] * $quantity).'</td></tr>';
    }
    $deliveryAddress = implode('<br>', array_map($escape, [
        $delivery['firstName'].' '.$delivery['lastName'], $delivery['address'],
        $delivery['city'].', '.$delivery['province'].' '.$delivery['postalCode'], $delivery['phone'],
    ]));
    $notes = $delivery['notes'] === '' ? '' : '<p><strong>Delivery instructions</strong><br>'.nl2br($escape($delivery['notes'])).'</p>';
    $customer = $escape($name);
    $total = $money($amount);
    $html = <<<HTML
    <div style="font-family:Arial,sans-serif;max-width:640px;margin:auto;color:#3f302a">
        <h1>Order received {$reference}</h1>
        <p>Hello {$customer}, thank you for ordering from TE Crown Hair.</p>
        <p><strong>Placed</strong> {$date}<br><strong>Order status</strong> Pending<br>
        <strong>Payment</strong> Demo card, pending at the time of ordering. Test mode only, with no real charge.</p>
        <h2>Items ordered</h2>
        <table cellpadding="8" style="width:100%;border-collapse:collapse" border="1">
            <thead><tr><th>Product</th><th>Quantity</th><th>Unit price</th><th>Line total</th></tr></thead>
            <tbody>{$rows}</tbody>
        </table>
        <p>Subtotal {$total}<br>Delivery included (R0.00)<br><strong>Total {$total}</strong></p>
        <h2>Delivery details</h2>
        <p>{$deliveryAddress}</p>
        {$notes}
        <p>Sign in to My Account on the TE Crown Hair website to view your order and its latest status.</p>
    </div>
    HTML;
    return sendBrevoMessage($email, $name, 'TE Crown Hair order confirmation '.$reference, $html, $transport)['success'];
}
