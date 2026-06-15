<?php
define('BASE_URL', '/project_uas');

function app_url(string $path = ''): string
{
    $normalizedPath = '/' . ltrim($path, '/');
    return rtrim(BASE_URL, '/') . $normalizedPath;
}

function asset_url(string $path): string
{
    return app_url($path);
}