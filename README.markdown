BassoFeed
---------

Class for you peeps to get your fav. shows upcoming showtime as an iCal-formatted feed. Includes crude caching so basso.fi won't get bombed too much, yo!

Uses the awesome [simplehtmldom](http://simplehtmldom.sourceforge.net/) as basic scraping tool, you should too check it out.

Usage
-----
To get, for example the awesome Alas-show's broadcast times you 

    header('Content-Type: text/html; charset=utf-8');
    date_default_timezone_set('Europe/Helsinki');
    
    require_once("simple_html_dom.php");
    require_once("bassofeed.php");
    
    $basso_alas = new BassoFeed('alas');
    echo $basso_alas->get_ical();


    