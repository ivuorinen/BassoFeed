<?php
/**
 * Basso Radio Showtimes
 *
 * @author Ismo Vuorinen
 * @version $Id$
 * @copyright __MyCompanyName__, 22 September, 2010
 * @package default
 **/

/**
 * Class for you peeps to get your fav. shows upcoming
 * showtime as an iCal-formatted feed. Includes crude
 * caching so basso.fi won't get bombed too much, yo!
 **/

/**
* BassoFeed
* To get your groove on!
*/
class BassoFeed
{
    var $channel;
    var $showtimes;
    var $showinfo;
    var $cachefile;
    var $cachetime;
    
    function __construct($channel)
    {
        $this->channel      = $channel;
        $this->cachefile    = "cache-{$channel}.txt";
        $this->cachetime    = 900;
        
        $this->generate();
    }
    
    function generate()
    {
        $fetch = file_get_html(
            "http://www.basso.fi/radio/".$this->channel
        );
        
        $this->showinfo     = $this->get_showinfo($fetch);
        $this->showtimes    = $this->get_showtimes($fetch);
        
        
        #$this->ical_feed();
    }
    

    
    function get_showtimes($fetch)
    {
        foreach($fetch->find('div.column_entry') as $m) {
            $div = $m->innertext;
            
            $items[] = $div;
        }
        
        $finnish_dates = array(
            "Maanantai", "Tiistai", "Keskiviikko",
            "Torstai", "Perjantai", "Lauantai", "Sunnuntai"
        );
        
        $items = $items[1];
        $items = str_replace("<br />", "|", $items);
        $items = str_replace("Tulevia lähetysaikoja", "", $items);
        $items = explode("|", strip_tags($items));
        
        foreach ($items as $n => $item) {
            
            $item = trim($item);
            if( !empty($item) && strlen($item) > 2 ) {
                $item = str_replace($finnish_dates, "", $item);
                
                $dates = explode("-", trim($item));
                $dates2 = explode(" ", $dates[0]);

                $dates_from  = $dates[0];
                $dates_to    = $dates2[0]." ".$dates[1];
                
                $date = $dates2[0];
                list($day, $month, $year) = explode(".", $date);
                $date = "20{$year}-$month-$day";
                
                $time_f     = strtotime($date." ".$dates2[1]);
                $time_t     = strtotime($date." ".$dates[1]);
                
                // We take -2 as timezone info 'coz the times are in +2
                $date_f     = $this->unixToiCal( $time_f, -2 );
                $date_t     = $this->unixToiCal( $time_t, -2 );
                
                $stuff[$n]["time_f"]     = $time_f;
                $stuff[$n]["time_t"]     = $time_t;
                $stuff[$n]["date_f"]     = $date_f;
                $stuff[$n]["date_t"]     = $date_t;
                
                // int mktime ([ int $hour = date("H") [, int $minute = date("i") [, int $second = date("s") [, int $month = date("n") [, int $day = date("j") [, int $year = date("Y") [, int $is_dst = -1 ]]]]]]] )

            }
        }
        return $stuff;
    }
    
    function get_showinfo($fetch)
    {
        foreach($fetch->find('div#main_column_1') as $m) {
            $div = $m->innertext;
            
            $items[] = $div;
        }
        
        $title = $fetch->find("h1", 0);
        $title = $title->plaintext;
        
        $cleaned = $items[0];
        $cleaned = str_replace("&nbsp;", " ", $cleaned);
        $cleaned = strip_tags($cleaned, "<div><h1>");
        $clean_array = explode("\n", $cleaned);
        foreach ($clean_array as $clean) {
            $clean = trim( strip_tags($clean, "<h1>") );
            
            if(
                strlen( $clean ) > 10 &&
                !preg_match('/\<h1\>/i', $clean) )
            {
                $c[] = $clean;
            }
        }
        
        $cleaned = $c;
        
        $desc = $cleaned[2];
        
        
        $data = array(
            "title"     => $title,
            "desc"      => $desc,
            #"cleaned"   => $cleaned,
            #"raw"       => $items
        );
        
        return $data;
    }
    
    function ical_item()
    {
        /**
        *   BEGIN:VCALENDAR
        *   VERSION:2.0
        *   PRODID:-//hacksw/handcal//NONSGML v1.0//EN
        *   BEGIN:VEVENT
        *   UID:uid1@example.com
        *   DTSTAMP:19970714T170000Z
        *   ORGANIZER;CN=John Doe:MAILTO:john.doe@example.com
        *   DTSTART:19970714T170000Z
        *   DTEND:19970715T035959Z
        *   SUMMARY:Bastille Day Party
        *   END:VEVENT
        *   END:VCALENDAR
        */
    }
    
    /**
     * unixToiCal
     * Unix timestamp to iCal spec format
     * @author  chubby at chicks dot com
     * @see     http://fi.php.net/manual/en/function.date.php#83429
     * @return  str
     **/
    function unixToiCal($uStamp = 0, $tzone = 0.0) {
        $uStampUTC = $uStamp + ($tzone * 3600);
        $stamp  = date("Ymd\THis\Z", $uStampUTC);
        return $stamp;
    }

}