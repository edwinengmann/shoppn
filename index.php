<?php

require_once __DIR__ . '/core/core.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (
    !is_logged_in()
    || !isset($_GET['welcome'])
    || $_GET['welcome'] !== '1'
) {
    redirect(app_url('views/login.php'));
}

require_once __DIR__ . '/views/home.php';
exit;

?>