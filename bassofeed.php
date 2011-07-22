<?php

/**
 * Basso Radio Showtimes
 *
 * Class for you peeps to get your fav. shows upcoming
 * showtime as an iCal-formatted feed. Includes crude
 * caching so basso.fi won't get bombed too much, yo!
 *
 * @author  Ismo Vuorinen
 * @version 0.1.2010-09-22
 * @copyright Copyright (c) 2010, Ismo Vuorinen
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 * @package default
 * @uses    simple_html_dom Scraping data and making it easier to handle
 * @see     http://en.wikipedia.org/wiki/ICalendar
 * @todo    Documentation
 * @todo    More testing
 * */
class BassoFeed {

    /**
     * Show you listen to
     * @var string
     * */
    var $show;
    /**
     * List of showtimes
     * @var array
     */
    var $showtimes;
    /**
     * Show title, desc, etc.
     * @var array
     */
    var $showinfo;
    /**
     * File (and possibly folder) you use as cache
     * @var string Default: "./cache/[show].txt"
     */
    var $cachefile;
    /**
     * How long the programpage should be cached in seconds
     * @var int Default: 900
     */
    var $cachetime;
    /**
     * Is the data coming from cache or not
     * @var bool
     */
    var $from_cache = 0;
    /**
     * Dealing with multiple shows?
     * @var bool
     */
     var $multiple_shows = false;

    function __construct($show) {

        if( is_array($show) ) {
            $this->multiple_shows = true;
            $this->multiple($show);
            $this->get_ical();

        } else {
            $this->multiple_shows = false;
            $this->show = $show;
            $this->cachefile = dirname(__FILE__) . DIRECTORY_SEPARATOR . "cache/{$this->show}.txt";
            $this->cachetime = 900;

            $this->generate();
        }
        return false;
    }

    function multiple($array = array()) {

        if( !empty($array) ) {
            $this->cachetime = 900;
            $alltimes = array();

            foreach ($array as $show) {

                $this->show = $show;
                $this->cachefile    = dirname(__FILE__) . DIRECTORY_SEPARATOR . "cache/{$this->show}.txt";
                $data = $this->cache();

                $this->showtimes[$show] = $this->get_showtimes($data);
                $this->showinfo[$show]  = $this->get_showinfo($data);
            }


        }

    }

    /**
     * generate
     *
     * The action sequence of the script.
     * Used to run the process:
     * * cache verification
     * * scraping
     * * gets
     *
     * @return void
     * @author Ismo Vuorinen
     * */
    function generate() {
        // Load the data
        $data = $this->cache();

        // Process the data
        $this->showinfo = $this->get_showinfo($data);
        $this->showtimes = $this->get_showtimes($data);

        // Echo the iCal
        $this->get_ical();
    }

    /**
     * cache
     * Fetches the page to a cachefile and returns it
     *
     * @return mixed
     * @author Ismo Vuorinen
     * */
    function cache() {
        $filemtime = 0;
        if (is_readable($this->cachefile)) {
            $filemtime = filemtime($this->cachefile);
        }
        if (
                !$filemtime || (time() - $filemtime >= $this->cachetime)
        ) {
            $fetch = file_get_html(
                            "http://www.basso.fi/radio/" . $this->show
            );

            // Service is being updated
            if( preg_match( '/Palvelua/i', $fetch) ) {
                $this->from_cache = true;
                return file_get_html($this->cachefile);
            }

            file_put_contents($this->cachefile, $fetch);
            $this->from_cache = false;
            return $fetch;
        } else {
            $this->from_cache = true;
            return file_get_html($this->cachefile);
        }
    }

    /**
     * get_showtimes
     * Process loaded showpage and find our showtimes
     * @param   mixed $fetch
     * @uses    simple_html_dom::find|simple_html_dom::innertext
     */
    function get_showtimes($fetch) {
        // Find our sidebar columns and get the insides
        foreach ($fetch->find('div.column_entry') as $m) {
            $div = $m->innertext;
            $items[] = $div;
        }

        // List of finnish daynames for elimination from the strings
        $finnish_dates = array(
            "Maanantai", "Tiistai", "Keskiviikko",
            "Torstai", "Perjantai", "Lauantai", "Sunnuntai"
        );

        // Take the found broadcast times, strip tags and explode it
        $items = $items[1];
        $items = str_replace("<br />", "|", $items);
        $items = str_replace("Tulevia lähetysaikoja", "", $items);
        $items = explode("|", strip_tags($items));

        // Take the processed showtimes and mangle to right format
        foreach ($items as $n => $item) {
            $item = trim($item);

            if (!empty($item) && strlen($item) > 2) {
                // Remove finnish daynames
                $item = str_replace($finnish_dates, "", $item);

                // Split into 2 vars; start and end times
                $dates = explode("-", trim($item));
                $dates2 = explode(" ", $dates[0]);

                $dates_from = $dates[0];
                $dates_to = $dates2[0] . " " . $dates[1];

                $date = $dates2[0];
                list($day, $month, $year) = explode(".", $date);
                $date = "20{$year}-$month-$day"; // We are on the 21st cent.
                // Unix timestamps
                $time_f = strtotime($date . " " . $dates2[1]);
                $time_t = strtotime($date . " " . $dates[1]);

                // We take -2 as timezone info 'coz the times are in +3
                $date_f = $this->unixToiCal($time_f, -3);
                $date_t = $this->unixToiCal($time_t, -3);

                $stuff[$n]["time_f"] = $time_f;
                $stuff[$n]["time_t"] = $time_t;
                $stuff[$n]["date_f"] = $date_f;
                $stuff[$n]["date_t"] = $date_t;
            }
        }
        return $stuff;
    }

    /**
     * get_showinfo
     * Parses the show info from fetched data
     *
     * @return  array
     * @todo    Document me
     * @author  Ismo Vuorinen
     * */
    function get_showinfo($fetch) {
        foreach ($fetch->find('div#main_column_1') as $m) {
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
            $clean = trim(strip_tags($clean, "<h1>"));

            if (
                    strlen($clean) > 10 &&
                    !preg_match('/\<h1\>/i', $clean)) {
                $c[] = $clean;
            }
        }

        $cleaned = $c;

        $desc = $cleaned[2];


        $data = array(
            "title"     => str_replace(";", "\;", $title),
            "desc"      => str_replace(";", "\;", $desc),
            "desc"      => str_replace(",", "\,", $desc),
            "url"       => "http://www.basso.fi/radio/" . $this->show
        );

        return $data;
    }

    /**
     * get_ical
     * Echo iCal-formatted calendar
     *
     * @return  str
     * @todo    Document me
     * @author  Ismo Vuorinen
     * */
    function get_ical() {

        $cal =      "BEGIN:VCALENDAR\n"
                .   "VERSION:2.0\n"
                .   "X-WR-CALNAME:BassoRadioFeed\n"
                .   "PRODID:-//BASSOFEED/FEED/EN\n"
                .   "CALSCALE:GREGORIAN\n"
                .   "X-WR-TIMEZONE:Europe/Helsinki\n"
                .   "METHOD:PUBLISH\n";

        if( $this->multiple_shows ) {
            foreach ($this->showinfo as $show => $info) {

                foreach ($this->showtimes[$show] as $i) {
                    $cal .= "BEGIN:VEVENT\n"
                        .   "SUMMARY:{$this->showinfo[$show]["title"]}\n"
                        .   "DESCRIPTION:{$this->showinfo[$show]["desc"]}\n"
                        .   "LOCATION:Basso Radio 102.8 FM\n"
                        .   "UID:{$this->show}-{$i["date_f"]}/basso.fi\n"
                        .   "URL:http://basso.fi/radio/{$this->show}\n"
                        .   "DTSTART;VALUE=DATE-TIME;TZID=GMT:{$i["date_f"]}\n"
                        .   "DTEND;VALUE=DATE-TIME;TZID=GMT:{$i["date_t"]}\n"
                        .   "DTSTAMP:{$i["date_f"]}\n"
                        .   "BEGIN:VALARM\n"
                        .   "TRIGGER:-PT15M\n"
                        .   "ACTION:DISPLAY\n"
                        .   "DESCRIPTION:{$this->showinfo[$show]["title"]}\n"
                        .   "END:VALARM\n"
                        .   "END:VEVENT\n";
                }
            }

        } else {
            // One show info gets looped
            foreach ($this->showtimes as $i) {

                $cal .= "BEGIN:VEVENT\n"
                    .   "SUMMARY:{$this->showinfo["title"]}\n"
                    .   "DESCRIPTION:{$this->showinfo["desc"]}\n"
                    .   "LOCATION:Basso Radio 102.8 FM\n"
                    .   "UID:{$this->show}-{$i["date_f"]}/basso.fi\n"
                    .   "URL:http://basso.fi/radio/{$this->show}\n"
                    .   "DTSTART;VALUE=DATE-TIME;TZID=GMT:{$i["date_f"]}\n"
                    .   "DTEND;VALUE=DATE-TIME;TZID=GMT:{$i["date_t"]}\n"
                    .   "DTSTAMP:{$i["date_f"]}\n"
                    .   "BEGIN:VALARM\n"
                    .   "TRIGGER:-PT15M\n"
                    .   "ACTION:DISPLAY\n"
                    .   "DESCRIPTION:{$this->showinfo["title"]}\n"
                    .   "END:VALARM\n"
                    .   "END:VEVENT\n";
            }

        }

        $cal .= "END:VCALENDAR\n";

        return str_replace("\n", "\r\n", $cal);
    }

    /**
     * unixToiCal
     * Unix timestamp to iCal spec format
     * @author  chubby at chicks dot com
     * @see     http://fi.php.net/manual/en/function.date.php#83429
     * @return  str
     * */
    function unixToiCal($uStamp = 0, $tzone = 0.0) {
        $uStampUTC = $uStamp + ($tzone * 3600);
        $stamp = date("Ymd\THis\Z", $uStampUTC);
        return $stamp;
    }

}