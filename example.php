<?php
header('Content-Type: text/calendar; charset=utf-8');
date_default_timezone_set('Europe/Helsinki');

require_once("simple_html_dom.php");
require_once("bassofeed.php");

#echo "<pre>";
$basso_alas = new BassoFeed('alas');
echo $basso_alas->get_ical();
