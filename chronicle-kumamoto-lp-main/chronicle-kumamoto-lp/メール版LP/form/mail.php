<?php
require dirname(__DIR__) . '/inc/session_bootstrap.php';
chronicle_lp_bootstrap_session();

header('X-FRAME-OPTIONS: SAMEORIGIN');
header('Cache-Control: no-store, no-cache, must-revalidate');

mb_language('uni');
mb_internal_encoding('UTF-8');

$params   = $_SESSION;
$settings = require __DIR__ . '/config.php';
// POST送信以外は受け付けない
if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    _redirect($settings['pages']['error']);
    echo "不正なアクセスです";exit;
}
// トークン検証
if (isset($settings['options']['csrf']) && $settings['options']['csrf']) {
	if ((!isset($_SESSION['form_token']) || !isset($_POST['form_csrf_token'])) || ($_SESSION['form_token'] != $_POST['form_csrf_token'])) {
		$_SESSION['mail_submit_error'] = true;
		header('Location: ../confirm.php?mail_err=1', true, 302);
		exit;
	}
}
if (!isset($_SESSION['メールアドレス']) || empty($_SESSION['メールアドレス'])) {
	$_SESSION['mail_submit_error'] = true;
	header('Location: ../confirm.php?mail_err=1', true, 302);
	exit;
}

// ドメイン確認
if ((isset($settings['options']['referer']) && $settings['options']['referer'] != '') && $settings['options']['referer'] != $_SERVER["HTTP_REFERER"]) {
	echo "不正なアクセスです";exit;
}

$nameForThanks = isset($_SESSION['お名前']) ? preg_replace('/\R/u', '', (string) $_SESSION['お名前']) : '';

// メール送信オフ: PHPMailer を読まず検証のみ通したら index へ（クエリでサンクストースト用）
if (empty($settings['options']['send_mail'])) {
    if (isset($_SESSION['form_token'])) {
        unset($_SESSION['form_token']);
    }
    resetSession();
    $q = http_build_query(['thanks' => '1', 'name' => $nameForThanks], '', '&', PHP_QUERY_RFC3986);
    header('Location: ../index.php?' . $q, true, 302);
    exit;
}

require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

try {
    foreach ($settings['email'] as $key => $value) {
        $body = _createmailbody($value['body'], $params);
        sendMailByPHPMailer($value, $body, $params, $settings);
    }
    if (isset($_SESSION['form_token'])) {
        unset($_SESSION['form_token']);
    }
    resetSession();
    $q = http_build_query(['thanks' => '1', 'name' => $nameForThanks], '', '&', PHP_QUERY_RFC3986);
    header('Location: ../index.php?' . $q, true, 302);
    exit;
} catch (\Throwable $e) {
    $_SESSION['mail_submit_error'] = true;
    header('Location: ../confirm.php?mail_err=1', true, 302);
    exit;
}

function resetSession()
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        if (PHP_VERSION_ID >= 70300) {
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $p['path'],
                'domain'   => $p['domain'],
                'secure'   => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'] ?? 'Lax',
            ]);
        } else {
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
    }
    session_destroy();
}

/**
 * テンプレートからメール本文を生成する。
 *
 * @param string $template
 * @param array $params
 * @return string
 */
function _createmailbody($template, $params = array())
{
    $blocks = [];
    if (is_array($params)) {
        foreach ($params as $key => $value) {
            if ($key === 'form_token' || $key === 'form_csrf_token') {
                continue;
            }
            $lines = [];
            if (is_array($value)) {
                foreach ($value as $item) {
                    $lines[] = '・' . str_replace(["\r\n", "\r", "\n"], ' ', (string) $item);
                }
            } else {
                $str = (string) $value;
                if ($str === '') {
                    $lines[] = '・(未入力)';
                } else {
                    foreach (preg_split('/\R/u', $str) as $line) {
                        $lines[] = '・' . $line;
                    }
                }
            }
            $blocks[] = '■ ' . $key . "\n" . implode("\n", $lines);
        }
    }
    $paramstext = implode("\n\n", $blocks);
    return preg_replace('/<%\s*フォーム入力内容\s*%>/u', $paramstext, $template);
}
/**
 * 別ページにリダイレクトする。
 *
 * @param string $url
 * @return void
 */
function _redirect($url)
{
    header('Location: ' . $url, true, 302);
    exit;
}

function sendMailByPHPMailer($config, $body, $params, $settings = [])
{
    // $mail->SMTPDebug = 2; // デバッグ出力を有効化（レベルを指定）
    // $mail->Debugoutput = function($str, $level) {echo "debug level $level; message: $str<br>";};

    $xMessage = $body;
    $xMessage = str_replace("\r\n", "\r", $xMessage);
    $xMessage = str_replace("\r", "\n", $xMessage);
    $from = $config['from']['address'];
    $fromName = $config['from']['name'];
    $MailTo = $config['to']['address'];
    $toName = "";
    $Subject = $config['subject'];

    $mail = new PHPMailer(true);
    $mail->CharSet = 'utf-8';
    $mail->Encoding = 'base64';

    if (!empty($settings['smtp']['enabled']) && !empty($settings['smtp']['host'])) {
        $mail->isSMTP();
        $mail->Host = $settings['smtp']['host'];
        $mail->Port = (int) $settings['smtp']['port'];
        $mail->SMTPAuth = $settings['smtp']['user'] !== '';
        if ($mail->SMTPAuth) {
            $mail->Username = $settings['smtp']['user'];
            $mail->Password = $settings['smtp']['pass'];
        }
        $sec = $settings['smtp']['secure'] ?? '';
        if ($sec === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($sec === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }
    }

    // 送受信先設定（第二引数は省略可）
    $mail->setFrom($from, $fromName); // 送信者
    $tos = explode(',',$MailTo);
    
    foreach ($tos as $to) {
      $mail->addAddress($to, $toName);   // 宛先
    }

    $mail->clearReplyTos();
    $customerMail = isset($params['メールアドレス']) ? trim((string) $params['メールアドレス']) : '';
    if ($customerMail !== '' && filter_var($customerMail, FILTER_VALIDATE_EMAIL)) {
        $nm = isset($params['お名前']) ? preg_replace('/\R/u', ' ', (string) $params['お名前']) : '';
        $nm = mb_substr($nm, 0, 60, 'UTF-8');
        $mail->addReplyTo($customerMail, $nm !== '' ? $nm : 'お問い合わせ');
    } else {
        $mail->addReplyTo($from, $fromName);
    }
    // $mail->addCC('cc@example.com', '受信者名'); // CC宛先
    $mail->Sender = $from; // Return-path
    // foreach($options['bcc'] as $bcc) {
    //     $mail->addBCC($bcc['address'], $bcc['name']);
    // }
    $mail->Subject = $Subject;
    $mail->Body = $xMessage;
    return $mail->send();
}
function sendMail($config, $body)
{
    $xMessage = $body;
    $xMessage = str_replace("\r\n", "\r", $xMessage);
    $xMessage = str_replace("\r", "\n", $xMessage);
    $from = $config['from']['address'];
    $fromName = $config['from']['name'];
    $MailTo = $config['to']['address'];
    $Subject = $config['subject'];

    $header = '';
    $header .= "Content-Type: text/plain \r\n";
    $header .= "Return-Path: " . $from . " \r\n";
    $header .= "From: " . $fromName . " \r\n";
    $header .= "Sender: " . $fromName . "  \r\n";
    $header .= "Reply-To: " . $from . " \r\n";
    $header .= "Organization: " . $fromName . " \r\n";
    $header .= "X-Sender: " . $from . " \r\n";
    $header .= "X-Priority: 3 \r\n";

    $pfrom   = "-f $from";
    // メール送信実行
    return mb_send_mail($MailTo, $Subject, $xMessage, $header, $pfrom);
}

/*
以下LP最初に貼り付け
session_name('form');
session_start();
$toke_byte = openssl_random_pseudo_bytes(16);
$csrf_token = bin2hex($toke_byte);
// 生成したトークンをセッションに保存します
$_SESSION['form_token'] = $csrf_token;
*/