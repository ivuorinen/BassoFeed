BassoFeed
---------

Class for you peeps to get your fav. shows upcoming showtime as an iCal-formatted feed. Includes crude caching so basso.fi won't get bombed too much, yo!

Uses the awesome [simplehtmldom](http://simplehtmldom.sourceforge.net/) as basic scraping tool, you should too check it out.

Please create your cache-dir before using in production and please do chmod it as writable.

Usage
-----
To get, for example the awesome Alas-show's broadcast times you 

    header('Content-Type: text/calendar; charset=utf-8');
    date_default_timezone_set('Europe/Helsinki');
    
    require_once("simple_html_dom.php");
    require_once("bassofeed.php");
    
    $basso_alas = new BassoFeed('alas');
    echo $basso_alas->get_ical();

The "new BassoFeed(_showname_)" comes from the url, in Alas' case it's http://www.basso.fi/radio/*alas*
The *Content-Type: text/calendar* -part is really important, use it!