<?php
session_name('lp');
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
require('./phpmailer/PHPMailer.php');
require('./phpmailer/Exception.php');
require('./phpmailer/SMTP.php');

// 文字エンコードを指定
mb_language('uni');
mb_internal_encoding('UTF-8');
$params = $_SESSION;
$settings = require('./config.php');
// POST送信以外は受け付けない
if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    _redirect($settings['pages']['error']);
    echo "不正なアクセスです";exit;
}
// トークン検証
if (isset($settings['options']['csrf']) && $settings['options']['csrf']) {
	if ((!isset($_SESSION['form_token']) || !isset($_POST['form_csrf_token'])) || ($_SESSION['form_token'] != $_POST['form_csrf_token'])) {
		// _redirect($settings['pages']['error']);
		echo "トークンが不正です<br><a href=".$settings['pages']['error'].">戻る</a>";exit;
	}
}
if (!isset($_SESSION['メールアドレス']) || empty($_SESSION['メールアドレス'])) {
    echo "メールアドレスが入力されていません。<br><a href=".$settings['pages']['error'].">戻る</a>";exit;
}

// ドメイン確認
if ((isset($settings['options']['referer']) && $settings['options']['referer'] != '') && $settings['options']['referer'] != $_SERVER["HTTP_REFERER"]) {
	echo "不正なアクセスです";exit;
}
try {
    foreach ($settings['email'] as $key => $value) {
        $body = _createmailbody($value['body'], $params);
        sendMailByPHPMailer($value, $body, $params);
    }
    // _redirect($settings['pages']['complete']);
    // foreach($params as $parmKey => $param) {
    //     unset($_SESSION[$parmKey]);
    // }
    if (isset($_SESSION['form_token'])) {
    	unset($_SESSION['form_token']);
    }
    resetSession();
    _redirect($settings['pages']['complete']);
    exit;
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$e->getMessage()}";
}

function resetSession()
{
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
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
    $paramstext = '';
    $valueSeparator = PHP_EOL;
    if (is_array($params)) {
        foreach ($params as $key => $value) {
            if ($key === "form_token" || $key === "form_csrf_token") {
                continue;
            }
            if (is_array($value)) {
                $paramstext .= sprintf('[%s]%s%s%s', $key, PHP_EOL, implode($valueSeparator, $value), str_repeat(PHP_EOL, 2));
            } else {
                $paramstext .= sprintf('[%s]%s%s%s', $key, PHP_EOL, $value, str_repeat(PHP_EOL, 2));
            }
        }
    }
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

function sendMailByPHPMailer($config, $body, $params)
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
    // 送受信先設定（第二引数は省略可）
    $mail->setFrom($from, $fromName); // 送信者
    $tos = explode(',',$MailTo);
    
    foreach ($tos as $to) {
      $mail->addAddress($to, $toName);   // 宛先
    }

    $mail->addReplyTo($from, 'noreply'); // 返信先
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