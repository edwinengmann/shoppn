<?php

function paystack_load_local_environment()
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    $envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    if (!is_file($envFile)) {
        return;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        throw new RuntimeException('The local environment file could not be read.');
    }

    foreach ($lines as $line) {
        if (!preg_match(
            '/^\s*(?:export\s+)?(PAYSTACK_SECRET_KEY|APP_BASE_URL)\s*=\s*(.*?)\s*$/',
            $line,
            $matches
        )) {
            continue;
        }

        $name = $matches[1];
        if (getenv($name) !== false) {
            continue;
        }

        $value = trim($matches[2]);
        if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
            $quote = $value[0];
            if (strlen($value) < 2 || substr($value, -1) !== $quote) {
                throw new RuntimeException('The local environment file has an invalid value.');
            }
            $value = substr($value, 1, -1);
        } else {
            $value = preg_replace('/\s+#.*$/', '', $value);
            $value = trim($value);
        }

        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

function paystack_secret_key()
{
    paystack_load_local_environment();
    $secretKey = getenv('PAYSTACK_SECRET_KEY');
    return is_string($secretKey) ? trim($secretKey) : '';
}

function paystack_is_configured()
{
    return paystack_secret_key() !== '';
}

function paystack_callback_url()
{
    paystack_load_local_environment();
    $baseUrl = getenv('APP_BASE_URL');
    if (!is_string($baseUrl) || trim($baseUrl) === '') {
        $host = $_SERVER['SERVER_NAME'] ?? '';
        if (!is_string($host) || $host === '') {
            throw new RuntimeException('Set APP_BASE_URL to configure the Paystack callback.');
        }

        $isHttps = (
            !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off'
        ) || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
        $scheme = $isHttps ? 'https' : 'http';
        $port = (int) ($_SERVER['SERVER_PORT'] ?? 0);
        $portSuffix = $port > 0
            && !(($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443))
            ? ':' . $port
            : '';
        $baseUrl = $scheme . '://' . $host . $portSuffix;
    }

    $baseUrl = rtrim(trim($baseUrl), '/');
    $parts = parse_url($baseUrl);
    if (
        !filter_var($baseUrl, FILTER_VALIDATE_URL)
        || !is_array($parts)
        || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
        || empty($parts['host'])
        || isset($parts['user'])
        || isset($parts['pass'])
        || isset($parts['query'])
        || isset($parts['fragment'])
        || !empty($parts['path'])
    ) {
        throw new RuntimeException('APP_BASE_URL must be an origin such as https://shop.example.');
    }

    return $baseUrl . app_url('actions/paystack_callback_action.php');
}

function paystack_request($method, $path, $payload = null)
{
    $secretKey = paystack_secret_key();
    if ($secretKey === '') {
        throw new RuntimeException('Paystack is not configured.');
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('The PHP cURL extension is required for Paystack.');
    }

    $curl = curl_init('https://api.paystack.co/' . ltrim($path, '/'));
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $secretKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30
    ];

    if ($payload !== null) {
        $json = json_encode($payload);
        if ($json === false) {
            throw new RuntimeException('Could not encode the Paystack request.');
        }
        $options[CURLOPT_POSTFIELDS] = $json;
    }

    curl_setopt_array($curl, $options);
    $responseBody = curl_exec($curl);
    $curlError = curl_error($curl);
    $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($responseBody === false) {
        throw new RuntimeException('Paystack request failed: ' . $curlError);
    }

    $response = json_decode($responseBody, true);
    if (
        $statusCode < 200
        || $statusCode >= 300
        || !is_array($response)
        || empty($response['status'])
        || !isset($response['data'])
    ) {
        $message = is_array($response) && !empty($response['message'])
            ? $response['message']
            : 'Paystack returned an invalid response.';
        throw new RuntimeException($message);
    }

    return $response['data'];
}
