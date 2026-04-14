<?php
session_name('lp');
session_start();

if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'chronicle-japan.jp') === false) {
  header("Location:https://chronicle-japan.jp/lp-hakata/");
  exit();
}
if (!isset($_POST['form_csrf_token'])) {
  header('Location: ' . "../", true, 302);
  exit;
}
header('X-FRAME-OPTIONS: SAMEORIGIN');

require('./form/form_util.php');
foreach ($_POST as $key => $postdata) {
  $_SESSION[$key] = $_POST[$key];
}
?>
<?php require('./_header.php'); ?>

<div class="wrapper" x-data="{open:false}">
  <header class="header js-header">
    <div class="header__inner u-gutter">
      <div class="header__logo pc:u-none"><img src="./images/logo.svg" alt="CHRONICLE JAPAN"></div>
      <div class="header__menu" :class="open ? 'is-active' : '' " @click="open = !open"><span> </span><span> </span>
      </div>
    </div>
  </header>
  <div class="drawer" :class="open ? 'is-active' : ''">
    <div class="drawer__inner"><a class="c-button u-mb-50" href="https://maps.app.goo.gl/nVnhzd2wzsBkHM2J8"
        target="_blank"><span>無料体験はこちら</span>
        <div class="c-button__icon"></div>
      </a>
      <div class="drawe-links"><a class="drawe-link" href="./#features" @click="open = false">
          <div>クロニクルジャパンの魅力</div>
        </a><a class="drawe-link" href="./#price" @click="open = false">
          <div>料金プラン</div>
        </a><a class="drawe-link" href="./#flow" @click="open = false">
          <div>初回無料体験の流れ</div>
        </a><a class="drawe-link" href="./#voice" @click="open = false">
          <div>お客様の声</div>
        </a><a class="drawe-link" href="./#faq" @click="open = false">
          <div>よくある質問</div>
        </a><a class="drawe-link" href="./#access" @click="open = false">
          <div>アクセス</div>
        </a></div>
    </div>
  </div>
  <aside class="aside-content1">
    <div class="aside-content1__inner">
      <h1 class="header__logo"><img src="./images/logo.svg" alt="CHRONICLE JAPAN"></h1>
      <div class="aside-content1__menus"><a class="aside-content1__menu js-aside-link" href="./#features">
          <div>クロニクルジャパンの魅力</div>
        </a><a class="aside-content1__menu js-aside-link" href="./#price">
          <div>料金プラン</div>
        </a><a class="aside-content1__menu js-aside-link" href="./#flow">
          <div>初回無料体験の流れ</div>
        </a><a class="aside-content1__menu js-aside-link" href="./#voice">
          <div>お客様の声</div>
        </a><a class="aside-content1__menu js-aside-link" href="./#faq">
          <div>よくある質問</div>
        </a><a class="aside-content1__menu js-aside-link" href="./#access">
          <div>アクセス</div>
        </a></div>
      <div class="aside-content2__buttons"><a class="c-button u-ml-0" href="./#contact"><span>無料体験はこちら</span>
          <div class="c-button__icon"></div>
        </a></div>
    </div>
  </aside>
  <div class="main">
    <svg xmlns="http://www.w3.org/2000/svg" style="display:none">
      <symbol id="arrow1" viewBox="0 0 20 20">
        <g id="グループ_30584" data-name="グループ 30584" transform="translate(0 0.481)">
          <g id="楕円形_28" data-name="楕円形 28" transform="translate(0 -0.481)" fill="none" stroke="#fff"
            stroke-width="1.5">
            <circle cx="10" cy="10" r="10" stroke="none" />
            <circle cx="10" cy="10" r="9.25" fill="none" />
          </g>
          <path id="Icon_open-arrow-right" data-name="Icon open-arrow-right"
            d="M4.608,0V1.843H0v.922H4.608V4.608L7.374,2.277Z" transform="translate(6.173 7.039)" fill="#fff"
            stroke="#fff" stroke-width="0.5" />
        </g>
      </symbol>
    </svg>
    <section id="top">
      <section class="contact u-py-80" id="contact">
        <div class="u-px-15">
          <div class="u-mb-35">
            <div class="c-label">CONTACT FORM</div>
            <h2 class="c-text-28 u-mb-35">無料体験・見学申し込み</h2>
          </div>
          <div class="contact-tel-box">
            <div class="contact-tel-box__t1">お電話でのお問い合わせ</div>
            <div class="contact-tel-box__t2">受付時間：9:00~22:00 (定休日：日曜・月曜)</div>
            <a class="contact-tel-box__t3" href="tel:092-600-0242"><sub>TEL.</sub>092-600-0242</a>
            <div class="contact-tel-box__t4">ご質問などもお気軽にお問い合わせください。</div>
          </div>
          <div class="contact-form">
            <form action="./form/mail.php" method="post">
              <?php
              $formmode = "confirm";
              $csrf_token = esc($_POST['form_csrf_token']);
              require('./_form.php');
              ?>
              <div class="form-buttons">
                <button class="c-button" type="submit"><span>送信する</span><div class="c-button__icon"></div></button>
                <a class="c-button c-button--back" href="javascript:void(0)" onclick="history.back()" ><span>戻る</span><div class="c-button__icon"></div></a>
              </div>
            </form>
          </div>
        </div>
      </section>
    </section>
    <footer class="footer">
      <div class="footer__inner">
        <div class="footer__copyright">© 2025 CHRONICLE JAPAN.</div>
      </div>
    </footer>
  </div>
</div>
<?php require('./_footer.php'); ?>