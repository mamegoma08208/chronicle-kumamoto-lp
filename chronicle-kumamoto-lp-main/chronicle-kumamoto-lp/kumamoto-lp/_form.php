<?php
// 確認画面用（index.php と同じ form 系クラスで表示）
if (!isset($formmode) || $formmode !== 'confirm') {
  return;
}
$skipKeys = ['form_token', 'form_csrf_token'];
?>
<?php foreach ($_SESSION as $key => $value) : ?>
  <?php
  if (in_array($key, $skipKeys, true)) {
    continue;
  }
  if (is_array($value)) {
    continue;
  }
  ?>
  <div class="form-group fade-up">
    <label><?php echo esc((string) $key); ?></label>
    <div style="padding:13px 14px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--white);white-space:pre-wrap;font-size:15px;line-height:1.7;color:var(--dark);"><?php echo esc((string) $value); ?></div>
  </div>
<?php endforeach; ?>
<input type="hidden" name="form_csrf_token" value="<?php echo $csrf_token; ?>">
