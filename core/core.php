<?php



if (session_status() === PHP_SESSION_NONE) {

    session_start();

}



date_default_timezone_set('Africa/Accra');



require_once __DIR__ . '/db_class.php';


function app_url($path = '')
{
    static $basePath = null;

    if ($basePath === null) {
        $applicationRoot = realpath(dirname(__DIR__));
        $executingScript = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
        $scriptUrl = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $rootPrefix = $applicationRoot
            ? rtrim(str_replace('\\', '/', $applicationRoot), '/') . '/'
            : '';
        $scriptPath = $executingScript
            ? str_replace('\\', '/', $executingScript)
            : '';

        if ($rootPrefix !== '' && strpos($scriptPath, $rootPrefix) === 0) {
            $relativeScript = substr($scriptPath, strlen($rootPrefix));
            $scriptSuffix = '/' . $relativeScript;

            if (substr($scriptUrl, -strlen($scriptSuffix)) === $scriptSuffix) {
                $basePath = substr($scriptUrl, 0, -strlen($scriptSuffix));
            }
        }

        if ($basePath === null) {
            $basePath = '';
        }
    }

    return rtrim($basePath, '/') . '/' . ltrim($path, '/');
}



function get_ip()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {

        return $_SERVER['HTTP_CLIENT_IP'];

    }


    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

        return trim(
            explode(
                ',',
                $_SERVER['HTTP_X_FORWARDED_FOR']
            )[0]
        );

    }


    return $_SERVER['REMOTE_ADDR']
        ?? '0.0.0.0';
}



function redirect($url)
{
    header('Location: ' . $url);

    exit;
}



function is_logged_in()
{
    return isset($_SESSION['customer_id']);
}



function is_admin()
{
    return isset($_SESSION['user_role'])
        && (int)$_SESSION['user_role'] === 1;
}



function require_login()
{
    if (!is_logged_in()) {
        $_SESSION['error'] = 'Please log in to continue.';
        redirect(app_url('views/login.php'));
    }
}

function require_admin()
{
    if (!is_admin()) {
        $_SESSION['error'] = 'You do not have permission to access that page.';
        redirect(app_url('index.php'));
    }
}

?>