<?php
#header('Content-Type: text/calendar; charset=utf-8');
#error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('Europe/Helsinki');

require_once("simple_html_dom.php");
require_once("bassofeed.php");

#echo "<pre>";
#$basso_alas = new BassoFeed('alas');
#echo $basso_alas->get_ical();

#echo "\n----------------------------\n\n\n\n";

$basso_multiple = new BassoFeed(array('alas', 'darkdays'));
echo $basso_multiple->get_ical();
