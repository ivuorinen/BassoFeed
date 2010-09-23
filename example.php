<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Europe/Helsinki');

echo "<pre>";
require_once("bassofeed.php");

// http://simplehtmldom.sourceforge.net/manual.htm
require_once("simple_html_dom.php");

$basso_alas = new BassoFeed('alas');
#print_r($basso_alas);
