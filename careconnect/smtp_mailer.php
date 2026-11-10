<?php

function careConnectSmtpConfiguration(): ?array
{
    $host = getenv('CARECONNECT_SMTP_HOST');
    $port = getenv('CARECONNECT_SMTP_PORT');
    $encryption = getenv('CARECONNECT_SMTP_ENCRYPTION');
    $username = getenv('CARECONNECT_SMTP_USERNAME');
    $password = getenv('CARECONNECT_SMTP_PASSWORD');
    $from = getenv('CARECONNECT_MAIL_FROM');

    if (
        !is_string($host) ||
        !preg_match('/^[A-Za-z0-9.-]+$/', $host) ||
        !is_string($port) ||
        !ctype_digit($port) ||
        (int) $port < 1 ||
        (int) $port > 65535 ||
        !is_string($encryption) ||
        !in_array($encryption, ['tls', 'ssl'], true) ||
        !is_string($username) ||
        $username === '' ||
        !is_string($password) ||
        $password === '' ||
        !is_string($from) ||
        !filter_var($from, FILTER_VALIDATE_EMAIL) ||
        preg_match('/[\r\n]/', $username . $password . $from)
    ) {
        return null;
    }

    return [
        'host' => $host,
        'port' => (int) $port,
        'encryption' => $encryption,
        'username' => $username,
        'password' => $password,
        'from' => $from,
    ];
}

function careConnectSmtpReadResponse($socket): ?array
{
    $responseLines = [];

    while (($line = fgets($socket, 515)) !== false) {
        $line = rtrim($line, "\r\n");
        $responseLines[] = $line;

        if (preg_match('/^([0-9]{3})([ -])/', $line, $matches) !== 1) {
            return null;
        }

        if ($matches[2] === ' ') {
            return [
                'code' => (int) $matches[1],
                'message' => implode(' ', $responseLines),
            ];
        }
    }

    return null;
}

function careConnectSmtpWrite($socket, string $data): bool
{
    $offset = 0;
    $length = strlen($data);

    while ($offset < $length) {
        $written = fwrite($socket, substr($data, $offset));
        if ($written === false || $written === 0) {
            return false;
        }
        $offset += $written;
    }

    return true;
}

function careConnectSmtpCommand($socket, string $command, array $expectedCodes, string $step): ?array
{
    if (!careConnectSmtpWrite($socket, $command . "\r\n")) {
        error_log('CareConnect SMTP: failed during ' . $step . ' write.');
        return null;
    }

    $response = careConnectSmtpReadResponse($socket);
    if ($response === null || !in_array($response['code'], $expectedCodes, true)) {
        $code = $response === null ? 'no response' : (string) $response['code'];
        error_log('CareConnect SMTP: ' . $step . ' failed (' . $code . ').');
        return null;
    }

    return $response;
}

function careConnectSendEmail(string $recipient, string $subject, string $body): bool
{
    $configuration = careConnectSmtpConfiguration();
    if ($configuration === null) {
        error_log('CareConnect SMTP: required SMTP configuration is missing or invalid.');
        return false;
    }

    if (
        !filter_var($recipient, FILTER_VALIDATE_EMAIL) ||
        preg_match('/[\r\n]/', $recipient . $subject)
    ) {
        error_log('CareConnect SMTP: rejected invalid message address or subject.');
        return false;
    }

    $transport = $configuration['encryption'] === 'ssl' ? 'ssl' : 'tcp';
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'peer_name' => $configuration['host'],
        ],
    ]);
    $socket = @stream_socket_client(
        $transport . '://' . $configuration['host'] . ':' . $configuration['port'],
        $errorNumber,
        $errorMessage,
        15,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if ($socket === false) {
        error_log('CareConnect SMTP: connection failed (' . $errorNumber . ': ' . $errorMessage . ').');
        return false;
    }

    stream_set_timeout($socket, 15);
    $success = false;
    $greeting = careConnectSmtpReadResponse($socket);

    if ($greeting === null || $greeting['code'] !== 220) {
        error_log('CareConnect SMTP: server greeting failed.');
    } else {
        $helloName = preg_replace('/[^A-Za-z0-9.-]/', '', gethostname() ?: 'localhost');
        $helloName = $helloName !== '' ? $helloName : 'localhost';
        $hello = careConnectSmtpCommand($socket, 'EHLO ' . $helloName, [250], 'EHLO');

        if ($hello !== null) {
            if ($configuration['encryption'] === 'tls') {
                $startTls = careConnectSmtpCommand($socket, 'STARTTLS', [220], 'STARTTLS');
                if (
                    $startTls !== null &&
                    stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) === true
                ) {
                    $hello = careConnectSmtpCommand($socket, 'EHLO ' . $helloName, [250], 'EHLO after STARTTLS');
                } else {
                    error_log('CareConnect SMTP: TLS negotiation failed.');
                    $hello = null;
                }
            }

            if (
                $hello !== null &&
                careConnectSmtpCommand($socket, 'AUTH LOGIN', [334], 'AUTH LOGIN') !== null &&
                careConnectSmtpCommand($socket, base64_encode($configuration['username']), [334], 'SMTP username') !== null &&
                careConnectSmtpCommand($socket, base64_encode($configuration['password']), [235], 'SMTP password') !== null &&
                careConnectSmtpCommand($socket, 'MAIL FROM:<' . $configuration['from'] . '>', [250], 'MAIL FROM') !== null &&
                careConnectSmtpCommand($socket, 'RCPT TO:<' . $recipient . '>', [250, 251], 'RCPT TO') !== null &&
                careConnectSmtpCommand($socket, 'DATA', [354], 'DATA') !== null
            ) {
                $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
                $message = 'Date: ' . date(DATE_RFC2822) . "\r\n"
                    . 'From: CareConnect <' . $configuration['from'] . ">\r\n"
                    . 'To: <' . $recipient . ">\r\n"
                    . 'Subject: ' . $encodedSubject . "\r\n"
                    . "MIME-Version: 1.0\r\n"
                    . "Content-Type: text/plain; charset=UTF-8\r\n"
                    . "Content-Transfer-Encoding: 8bit\r\n\r\n"
                    . str_replace(["\r\n", "\r"], "\n", $body);
                $message = str_replace("\n", "\r\n", $message);
                $message = preg_replace('/(^|\r\n)\./', '$1..', $message);

                if (
                    is_string($message) &&
                    careConnectSmtpWrite($socket, $message . "\r\n.\r\n")
                ) {
                    $sent = careConnectSmtpReadResponse($socket);
                    if ($sent !== null && $sent['code'] === 250) {
                        $success = true;
                    } else {
                        $code = $sent === null ? 'no response' : (string) $sent['code'];
                        error_log('CareConnect SMTP: message delivery failed (' . $code . ').');
                    }
                } else {
                    error_log('CareConnect SMTP: failed to transmit message body.');
                }
            }
        }
    }

    if ($success) {
        careConnectSmtpCommand($socket, 'QUIT', [221], 'QUIT');
    }
    fclose($socket);

    return $success;
}

?>
