<?php
session_start();
session_unset();
session_destroy();

require __DIR__ . '/config/app.php';
header('Location: ' . app_url('login.php'));
exit;