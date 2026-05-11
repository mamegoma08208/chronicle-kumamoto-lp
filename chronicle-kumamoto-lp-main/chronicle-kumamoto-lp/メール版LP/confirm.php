<?php
require __DIR__ . '/inc/session_bootstrap.php';
chronicle_lp_bootstrap_session();

require __DIR__ . '/form/form_util.php';

// メール送信失敗後の再表示（GET）。mail.php がセッションにフラグを立ててリダイレクトする。
$isMailErrorReplay = false;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['mail_err'])) {
  if (empty($_SESSION['mail_submit_error'])) {
    header('Location: ./index.php', true, 302);
    exit;
  }
  $isMailErrorReplay = true;
  unset($_SESSION['mail_submit_error']);
}

// 本番: chronicle-japan.jp のみ。localhost 系 Referer は「自ホストが開発用」のときだけ許可。
$httpHost     = (string) ($_SERVER['HTTP_HOST'] ?? '');
$isDevServer  = (bool) preg_match('/\A(localhost|127\.0\.0\.1)(:\d+)?\z/i', $httpHost);
$referer      = $_SERVER['HTTP_REFERER'] ?? '';
$refererHost = $referer !== '' ? parse_url($referer, PHP_URL_HOST) : null;
$refererAllowed = false;
if (is_string($refererHost) && $refererHost !== '') {
  if ($refererHost === 'chronicle-japan.jp' || str_ends_with($refererHost, '.chronicle-japan.jp')) {
    $refererAllowed = true;
  } elseif (($refererHost === 'localhost' || $refererHost === '127.0.0.1') && $isDevServer) {
    $refererAllowed = true;
  }
}
if (!$refererAllowed) {
  header("Location:https://chronicle-japan.jp/lp-hakata/");
  exit();
}

if ($isMailErrorReplay) {
  $mailErrorBannerText = '正しく送信できませんでした。もう一度お試しください。';
} else {
  $mailErrorBannerText = '';
  if (!isset($_POST['form_csrf_token'])) {
    header('Location: ' . "../", true, 302);
    exit;
  }
  foreach ($_POST as $key => $postdata) {
    $_SESSION[$key] = $_POST[$key];
  }
}

header('X-FRAME-OPTIONS: SAMEORIGIN');
header('Cache-Control: no-store, no-cache, must-revalidate');
?>
<?php require('./_header.php'); ?>

<div id="contact" class="form-wrap">
  <div style="margin-bottom:24px;">
    <p class="sec-eyebrow fade-up">CONTACT FORM</p>
    <div class="form-title fade-up">無料体験・見学申し込み</div>
  </div>
  <div class="contact-tel-box fade-up">
    <div class="contact-tel-box__t1">お電話でのお問い合わせ</div>
    <div class="contact-tel-box__t2">受付時間：9:00~22:00 (定休日：日曜・月曜)</div>
    <a class="contact-tel-box__t3" href="tel:0926000242"><sub>TEL.</sub>092-600-0242</a>
    <div class="contact-tel-box__t4">ご質問などもお気軽にお問い合わせください。</div>
  </div>
  <div class="form-divider fade-up">または</div>

  <form action="./form/mail.php" method="post">
    <?php if ($mailErrorBannerText !== '') : ?>
      <p class="mail-submit-error" role="alert"><?php echo htmlspecialchars($mailErrorBannerText, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <?php
    $formmode = "confirm";
    $csrf_token = $isMailErrorReplay
      ? esc((string) ($_SESSION['form_csrf_token'] ?? ''))
      : esc((string) $_POST['form_csrf_token']);
    require('./_form.php');
    ?>
    <div class="form-buttons" style="margin-top:24px;display:flex;flex-direction:column;gap:12px;">
      <button class="form-submit fade-up" type="submit">送信する</button>
      <a class="sec-cta-outline fade-up" href="javascript:void(0)" onclick="history.back()">戻る</a>
    </div>
  </form>
</div>

<?php require('./_footer.php'); ?>
