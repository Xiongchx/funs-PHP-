<?php
require './common/init.php';
require './common/library/Captcha.php';
$code= Captcha::create();
captcha_save($code);
Captcha::show($code);
?>