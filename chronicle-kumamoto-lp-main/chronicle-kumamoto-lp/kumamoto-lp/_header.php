<?php
// index.php から抽出した断片のみ使用（index.php 本体は変更しない）
readfile(__DIR__ . '/_fragment_index_head.html');
$chrome = file_get_contents(__DIR__ . '/_fragment_index_chrome_top.html');
$chrome = preg_replace('/href="#([a-zA-Z0-9_-]+)"/', 'href="index.php#$1"', $chrome);
$chrome = str_replace('href="#">', 'href="index.php">', $chrome);
echo $chrome;
// index 下部スクリプト用（.hero 参照のためのダミー）
echo '<section class="hero" aria-hidden="true" style="height:1px;min-height:1px!important;margin:0;padding:0;overflow:hidden;clip:rect(0,0,0,0);position:absolute;left:-9999px;width:1px;"></section>';
// index の CSS に無い旧 confirm 用クラス（文言は confirm.php のまま）
echo '<style>
.contact-tel-box{margin-bottom:20px;}
.contact-tel-box__t1{font-size:14px;font-weight:700;color:var(--dark);margin-bottom:6px;}
.contact-tel-box__t2{font-size:13px;color:var(--gray);margin-bottom:10px;}
.contact-tel-box__t3{display:block;font-size:22px;font-weight:900;color:var(--blue);text-decoration:none;margin:8px 0;}
.contact-tel-box__t4{font-size:12px;color:var(--gray-light);line-height:1.7;}
.form-buttons .sec-cta-outline{text-align:center;}
.mail-submit-error{color:#cc3333;font-size:14px;font-weight:700;margin:0 0 14px;line-height:1.6;}
</style>';
