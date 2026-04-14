<?php
declare(strict_types=1);

/**
 * メール・LP 動作設定（本番）
 *
 * 環境変数（任意・サーバの vhost / .env で設定）
 *   CHRONICLE_LP_SEND_MAIL   true で実送信（PHPMailer）。未設定・false は送信スキップ（完了 UI のみ）
 *   CHRONICLE_SMTP_HOST      SMTP ホスト（send_mail 時）
 *   CHRONICLE_SMTP_PORT      例: 587
 *   CHRONICLE_SMTP_USER
 *   CHRONICLE_SMTP_PASS
 *   CHRONICLE_SMTP_SECURE    tls | ssl | 空
 */
$sendMailEnv = getenv('CHRONICLE_LP_SEND_MAIL');
$sendMail    = filter_var(
    $sendMailEnv === false || $sendMailEnv === '' ? 'false' : $sendMailEnv,
    FILTER_VALIDATE_BOOLEAN
);

$smtpHost = getenv('CHRONICLE_SMTP_HOST') ?: '';
$smtpUser = getenv('CHRONICLE_SMTP_USER') ?: '';
$smtpPass = getenv('CHRONICLE_SMTP_PASS') ?: '';
$smtpPort = (int) (getenv('CHRONICLE_SMTP_PORT') ?: 587);
$smtpSec  = getenv('CHRONICLE_SMTP_SECURE') !== false ? (string) getenv('CHRONICLE_SMTP_SECURE') : 'tls';

return [
    'pages' => [
        'error'    => '../index.php',
        'complete' => '../thanks.php',
    ],
    'options' => [
        'csrf'      => true,
        'referer'   => '',
        'send_mail' => $sendMail,
    ],
    'email' => [
        'notice' => [
            'to' => [
                'address' => 'cj-group@chronicle-japan.jp',
            ],
            'from' => [
                'address' => 'noreply@chronicle-japan.jp',
                'name'    => 'クロニクルジャパン熊本LP',
            ],
            'subject' => 'クロニクル熊本店LPからの問い合わせ',
            'body'    => "以下、熊本店LPフォームより届いた内容です。\n\n<% フォーム入力内容 %>\n",
        ],
    ],
    'smtp' => [
        'enabled' => $sendMail && $smtpHost !== '',
        'host'    => $smtpHost,
        'port'    => $smtpPort > 0 ? $smtpPort : 587,
        'user'    => $smtpUser,
        'pass'    => $smtpPass,
        'secure'  => $smtpSec,
    ],
];
