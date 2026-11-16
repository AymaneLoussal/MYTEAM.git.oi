<?php

declare(strict_types=1);

$host = $argv[1] ?? 'smtp.gmail.com';
$port = filter_var($argv[2] ?? '587', FILTER_VALIDATE_INT);
if ($port !== 587) {
    throw new InvalidArgumentException('This probe supports Gmail STARTTLS on port 587 only.');
}

function responseLines($socket): array
{
    $lines = [];
    while (($line = fgets($socket, 2048)) !== false) {
        $lines[] = rtrim($line, "\r\n");
        if (strlen($line) >= 4 && $line[3] === ' ') {
            break;
        }
    }
    return $lines;
}

$address = gethostbyname($host);
if ($address === $host) {
    throw new RuntimeException('DNS resolution failed.');
}

$socket = @stream_socket_client("tcp://{$host}:{$port}", $errorCode, $errorMessage, 10, STREAM_CLIENT_CONNECT);
if (! is_resource($socket)) {
    throw new RuntimeException("TCP connection failed ({$errorCode}).");
}
stream_set_timeout($socket, 10);

$banner = responseLines($socket);
if (! isset($banner[0]) || ! str_starts_with($banner[0], '220')) {
    throw new RuntimeException('SMTP greeting was not accepted.');
}

fwrite($socket, "EHLO ftmball.local\r\n");
$ehlo = responseLines($socket);
if (! isset($ehlo[0]) || ! str_starts_with($ehlo[0], '250')) {
    throw new RuntimeException('SMTP EHLO failed.');
}

fwrite($socket, "STARTTLS\r\n");
$startTls = responseLines($socket);
if (! isset($startTls[0]) || ! str_starts_with($startTls[0], '220')) {
    throw new RuntimeException('SMTP STARTTLS was not offered.');
}

$crypto = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
if ($crypto !== true) {
    throw new RuntimeException('TLS negotiation or certificate validation failed.');
}

fwrite($socket, "QUIT\r\n");
fclose($socket);

printf("SMTP connectivity verified: DNS, TCP %d, greeting, EHLO, STARTTLS, and TLS certificate validation. Authentication and delivery were not attempted.\n", $port);
