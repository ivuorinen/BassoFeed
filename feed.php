<?php
header('Content-Type: text/calendar; charset=utf-8');
date_default_timezone_set('Europe/Helsinki');

require_once("simple_html_dom.php");
require_once("bassofeed.php");

$basso_multiple = new BassoFeed( array(
        'alas',
        'back2mad',
//        'beatniks',
        'darkdays',
        'lauantaijatsit',
//        'monterosso',
//        'sunnuntaikooma', tauolla
//        'teslarok',
//        'helsinkisubconscious',
        'sunnuntaisiskot',
//        'laos'
        'nerdnetwork'
) );
echo $basso_multiple->get_ical();

