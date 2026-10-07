<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Brevo\Brevo;
use Brevo\TransactionalEmails\Requests\SendTransacEmailRequest;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestSender;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem;

function loadBrevoEnvironment()
{
    $envFile = __DIR__ . '/../.env';

    if (!file_exists($envFile)) {
        return [];
    }

    $envLines = file(
        $envFile,
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );

    $env = [];

    foreach ($envLines as $line) {
        $line = trim($line);

        if ($line === "" || str_starts_with($line, "#")) {
            continue;
        }

        [$key, $value] = array_pad(
            explode("=", $line, 2),
            2,
            ""
        );

        $env[trim($key)] = trim($value);
    }

    return $env;
}

function sendVerificationEmail(
    $recipientEmail,
    $recipientName,
    $verificationLink
) {
    $env = loadBrevoEnvironment();

    $apiKey = $env["BREVO_API_KEY"] ?? "";
    $senderEmail = $env["BREVO_SENDER_EMAIL"] ?? "";
    $senderName = $env["BREVO_SENDER_NAME"] ?? "TE_Crown Hair";

    if ($apiKey === "" || $senderEmail === "") {
        return [
            "success" => false,
            "message" => "Brevo email configuration is incomplete."
        ];
    }

    try {

        $client = new Brevo(
            apiKey: $apiKey
        );

        $request = new SendTransacEmailRequest([
            "subject" => "Verify Your TE_Crown Hair Account",

            "htmlContent" => "
                <div style='
                    font-family: Arial, sans-serif;
                    max-width: 600px;
                    margin: auto;
                    padding: 30px;
                    background-color: #fdf8f5;
                    color: #3f302a;
                '>

                    <h2 style='
                        color: #6b4f45;
                        text-align: center;
                    '>
                        Welcome to TE_Crown Hair
                    </h2>

                    <p>
                        Hello " . htmlspecialchars($recipientName) . ",
                    </p>

                    <p>
                        Thank you for creating your TE_Crown Hair account.
                        Please verify your email address before logging in.
                    </p>

                    <div style='text-align: center; margin: 30px 0;'>

                        <a href='" . htmlspecialchars($verificationLink) . "'
                           style='
                               background-color: #6b4f45;
                               color: #ffffff;
                               padding: 14px 24px;
                               text-decoration: none;
                               border-radius: 6px;
                               display: inline-block;
                               font-weight: bold;
                           '>
                            VERIFY MY EMAIL
                        </a>

                    </div>

                    <p>
                        If you did not create this account, you can safely
                        ignore this email.
                    </p>

                    <p>
                        Regards,<br>
                        <strong>TE_Crown Hair</strong>
                    </p>

                </div>
            ",

            "sender" => new SendTransacEmailRequestSender([
                "name" => $senderName,
                "email" => $senderEmail
            ]),

            "to" => [
                new SendTransacEmailRequestToItem([
                    "email" => $recipientEmail,
                    "name" => $recipientName
                ])
            ]
        ]);

        $result = $client
            ->transactionalEmails
            ->sendTransacEmail($request);

        return [
            "success" => true,
            "message" => "Verification email sent successfully.",
            "message_id" => $result->messageId ?? null
        ];

    } catch (Exception $e) {

        return [
            "success" => false,
            "message" => "Unable to send verification email: "
                . $e->getMessage()
        ];
    }
}

?>
