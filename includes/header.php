<?php
$configFile = __DIR__ . '/../config/app.php';
require_once $configFile;

$pageTitle = $pageTitle ?? 'CarWash Management System';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo htmlspecialchars(asset_url('assets/css/style.css')); ?>">
</head>
<body>