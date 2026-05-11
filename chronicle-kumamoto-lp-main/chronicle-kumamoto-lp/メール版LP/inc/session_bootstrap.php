<?php
declare(strict_types=1);

/**
 * LP 共通セッション（session_name: lp）
 * 本番 HTTPS では Secure / HttpOnly / SameSite=Lax
 */
function chronicle_lp_bootstrap_session(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    session_name('lp');

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
