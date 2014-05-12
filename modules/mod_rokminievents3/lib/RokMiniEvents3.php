<?php
/**
 * @version   $Id: RokMiniEvents3.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// no direct access
defined('ROKMINIEVENTS3') or die('Restricted access');

jimport('joomla.utilities.date');

class RokMiniEvents3
{

    protected static $loaded = false;

    public function loadScripts(&$params)
    {
        if (static::$loaded) return;

        JHtml::_('behavior.framework');
        $doc = JFactory::getDocument();
        $URL = JRoute::_(JURI::Root(true) . "/modules/mod_rokminievents3/ajax.php?".JSession::getFormToken()."=1", true);

        $doc->addScript(JURI::Root(true) . '/modules/mod_rokminievents3/tmpl/js/rokminievents3.js');
        $doc->addScriptDeclaration('var RokMiniEvents3URL = "'.$URL.'";');

        static::$loaded = true;
    }

    public function getEvents(JRegistry &$params)
    {
        $conf = JFactory::getConfig();
        if ($conf->get('caching') && $params->get('module_cache')) {
            $user = JFactory::getUser();
            $cache = JFactory::getCache('mod_rokminievents3');
            $cache->setCaching(true);
            $args = array($params);
            $checksum = md5($params->__toString());
            $events = $cache->get(array($this, '_getEvents'), $args, 'mod_rokminievents3-' . $user->get('aid', 0) . '-' . $checksum);
        } else {
            $events = $this->_getEvents($params);
        }

        $events = $this->setTimezone($params, $events);

        $events = $this->trimDescription($params, $events);

        return $events;
    }

    /**
     *
     * @param JRegistry $params
     * @param array $events an array of RokMiniEvents3_Event object to have thier timezones modifed
     * @return an array of modified RokMiniEvents3_Event objects
     */
    protected function setTimezone(JRegistry $params, &$events)
    {
        $conf = JFactory::getConfig();
        $user = JFactory::getUser();

        $server_timezone = $conf->get('offset');
        $forced_timezone = $params->get('timezone', $conf->get('offset'));
        if($user->id > 0){
            $user->load($user->id);
            $local_timezone = $user->getParam('timezone', $conf->get('offset'));
        } else {
            $local_timezone = $server_timezone;
        }

        //set default to server timezone
        $timezone = $server_timezone;

        $localtime = $params->get('localtime', 'local');
        if ($localtime == 'local' && $user->id != 0) {
            //timezone is set to local and user is logged in
            $timezone = $local_timezone;
        } else if ($localtime == 'forced') {
            //timezone is set to forced
            $timezone = $forced_timezone;
        }

        $offset = self::_get_timezone_offset($timezone);

        if (!empty($events)) {
            foreach ($events as $event) {
                $event->setOffset($offset);
            }
        }
        return $events;
    }

    protected function trimDescription(JRegistry &$params, $events)
    {
        $trimmed_events = $events;
        if (count($events) > 0 && $params->get('trim_descirption', 1)) {
            foreach ($events as $event_key => $event) {
                $event->setDescription($this->html_substr($event->getDescription(), $params->get('trim_count', 200), 20, '', '', $params->get('strip_tags', 'a,i,br')));
                $trimmed_events[$event_key] = $event;
            }
        }
        return $trimmed_events;
    }

    public function _getEvents($params)
    {

        $sources = RokMiniEvents3_SourceLoader::getAvailableSources(ROKMINIEVENTS3_ROOT . "/lib/RokMiniEvents3/Source");

        $this->setTimes($params);

        $events = array();
        foreach ($sources as $source) {
            if ((bool)$params->get($source->name, true)) {
                $newevents = $source->source->getEvents($params);

                if (is_array($newevents))
                    $events = array_merge($events, $newevents);
            }
        }

        foreach ($events as $event) {
            $event->setFormats($params->get('dayformat'), $params->get('monthformat'), $params->get('yearformat'), $params->get('timeformat'));

        }

        if ($params->get('sortorder', 'ascending') == 'ascending')
            $cmp = array('RokMiniEvents3', 'sortAscending');
        else
            $cmp = array('RokMiniEvents3', 'sortDescending');

        usort($events, $cmp);

        if (!empty($events) && ($limit = $params->get('limit_count', 0)) > 0) {
            $event_chunks = array_chunk($events, $limit);
            $events = $event_chunks[0];
        }

        if (count($events) <= (int)$params->get('events_pane')) {
            $params->set('timeline', 'none');
        }
        return $events;
    }

    public static function sortAscending($a, $b)
    {
        if ($a->getStart()->toUnix() == $b->getStart()->toUnix()) {
            return 0;
        }
        return ($a->getStart()->toUnix() < $b->getStart()->toUnix()) ? -1 : 1;
    }

    public static function sortDescending($a, $b)
    {
        if ($a->getStart()->toUnix() == $b->getStart()->toUnix()) {
            return 0;
        }
        return ($a->getStart()->toUnix() > $b->getStart()->toUnix()) ? -1 : 1;
    }

    protected function setTimes(JRegistry &$params)
    {
        if ($params->get('time_range') == 'time_span') {
            $startMin = $params->get('startmin');
            $startMax = $params->get('startmax');

            if (!strlen($startMin)) $startMin = date('Y-m-d', time());
            $startMin .= 'T00:00:00';
            $params->set('startmin', $startMin);

            if (strlen($startMax)) {
                $startMax .= 'T23:59:59';
                $params->set('startmax', $startMax);
            }

        } else {
            $rangespan = $params->get('rangespan');
            $now = date('Y-m-d\T00:00:00', time());
            $params->set('startmin', $now);
            $futuretime = $now;

            switch ($rangespan) {
                case 'next_week':
                    $futuretime = date('Y-m-d\T23:59:59', strtotime("+1 week"));
                    break;
                case 'next_2_weeks':
                    $futuretime = date('Y-m-d\T23:59:59', strtotime("+2 week"));
                    break;
                case 'next_3_weeks':
                    $futuretime = date('Y-m-d\T23:59:59', strtotime("+3 week"));
                    break;
                case 'current_month':
                    $month = date('m', time());
                    $futuretime = date('Y-' . $month . '-31\T23:59:59', time());
                    break;
                case 'next_2_months':
                    $futuretime = date('Y-m-d\T23:59:59', strtotime("+2 month"));
                    break;
                case 'next_3_months':
                    $futuretime = date('Y-m-d\T23:59:59', strtotime("+3 month"));
                    break;
                case 'current_year':
                    $year = date('Y', time());
                    $futuretime = date('Y-12-31\T23:59:59', time());
                    break;
            }
            $params->set('startmax', $futuretime);
        }
    }

    public static function getTimelineDates($events, $params)
    {
        $per_pane = $params->get('events_pane');

        $s = 0;
        $e = $per_pane;
        $timeline = array();

        foreach ($events as $event) {
            $start = $end = false;
            $es = $event->getStart();
            $ee = $event->getEnd();
            if (($s % $per_pane) == 0) {
                $start = $es->getDay() . ' ' . $es->getMonth();
                array_push($timeline, array('start' => $start));
            }
            if ((($e + 1) % $per_pane) == 0) {
                $end = $ee->getDay() . ' ' . $ee->getMonth();
                $last = count($timeline) - 1;
                $timeline[$last]['end'] = $end;
            }

            $s++;
            $e++;
        }

        $count = count($timeline) - 1;
        $lastItem = $timeline[$count];
        if (!isset($lastItem['end'])) {
            $countEvt = count($events) - 1;
            $lastItemEvt = $events[$countEvt];
            $end = $lastItemEvt->getEnd()->getDay() . ' ' . $lastItemEvt->getEnd()->getMonth();
            $lastItem['end'] = $end;
            $timeline[$count] = $lastItem;
        }

        return $timeline;
    }

    public static function addSourcesPath($path)
    {
        try {
            if (!RTCommon_ClassLoader::isLoaderRegistered('RokMiniEvents3Sources')) {
                $sourcesLoader = new RokMiniEvents3_SourceLoader();
                RTCommon_ClassLoader::registerLoader('RokMiniEvents3Sources', $sourcesLoader);
            } else {
                $sourcesLoader = RTCommon_ClassLoader::getLoader('RokMiniEvents3Sources');
            }
        } catch (Exception $le) {
            throw $le;
        }

        try {
            $sourcesLoader->addSourcePath($path);
        } catch (RTCommon_Cache_Exception $ce) {
            throw $ce;
        }
    }

    protected function html_substr($posttext, $minimum_length = 200, $length_offset = 20, $cut_words = FALSE, $dots = TRUE, $tags_option = FALSE)
    {

        // $minimum_length:
        // The approximate length you want the concatenated text to be


        // $length_offset:
        // The variation in how long the text can be in this example text
        // length will be between 200 and 200-20=180 characters and the
        // character where the last tag ends

        //we decode the text in case it has been encoded
        //so it displays correctly and tags can be stripped
        $posttext = html_entity_decode($posttext);

        //$tags:
        //allowed html tags, all others will be stripped out
        if ($tags_option) {
            $tags = explode(",", $tags_option);
            $strip_tags = array();
            for ($i = 0; $i < count($tags); $i++) {
                $strip_tags[$i] = '<' . trim($tags[$i]) . '>';
            }
            $tags = implode(',', $strip_tags);

            $posttext = preg_replace('/{.+?}/', '', $posttext);
            $posttext = strip_tags($posttext, $tags);
        }

        // Reset tag counter & quote checker
        $tag_counter = 0;
        $quotes_on = FALSE;
        // Check if the text is too long
        if (strlen($posttext) > $minimum_length) {
            $start = (strpos($posttext, ' ', $minimum_length)) ? strpos($posttext, ' ', $minimum_length) : $minimum_length-3;
            $posttext = substr($posttext, 0, $start);
            if ($dots) {
                $posttext .= '...';
            }
        }
        //TODO fix this portion of the function
        /*// Reset the tag_counter and pass through (part of) the entire text
        $c = 0;
        for ($i = 0; $i < strlen($posttext); $i++)
        {
            // Load the current character and the next one
            // if the string has not arrived at the last character
            $current_char = substr($posttext, $i, 1);
            if ($i < strlen($posttext) - 1)
            {
                $next_char = substr($posttext, $i + 1, 1);
            }
            else
            {
                $next_char = "";
            }
            // First check if quotes are on
            if (!$quotes_on)
            {
                // Check if it's a tag
                // On a "<" add 3 if it's an opening tag (like <a href...)
                // or add only 1 if it's an ending tag (like </a>)
                if ($current_char == '<')
                {
                    if ($next_char == '/')
                    {
                        $tag_counter += 1;
                    }
                    else
                    {
                        $tag_counter += 3;
                    }
                }
                // Slash signifies an ending (like </a> or ... />)
                // substract 2
                if ($current_char == '/' && $tag_counter <> 0) {
                    $tag_counter -= 2;
                }
                // On a ">" substract 1
                if ($current_char == '>') {
                    $tag_counter -= 1;
                }
                // If quotes are encountered, start ignoring the tags
                // (for directory slashes)
                if ($current_char == '"') {
                    $quotes_on = TRUE;
                }
            }
            else
            {
                // IF quotes are encountered again, turn it back off
                if ($current_char == '"') {
                    $quotes_on = FALSE;
                }
            }

            // Count only the chars outside html tags
            if ($tag_counter == 2 || $tag_counter == 0)
            {
                $c++;
            }

            // Check if the counter has reached the minimum length yet,
            // then wait for the tag_counter to become 0, and chop the string there
            if ($c > $minimum_length - $length_offset && $tag_counter == 0 && (preg_match('/\s/',$next_char) || $cut_words == TRUE))
            {
                $posttext = substr($posttext, 0, $i + 1);
                if ($dots)
                {
                    $posttext .= '...';
                }
                return $posttext;
            }
        }*/
        return $posttext;
    }

    public static function _getJSVersion()
    {

        return "";
    }

    public static function _get_timezone_offset($tz, $in_hours=true)
    {
        $dt = new DateTime(null, new DateTimeZone($tz));
        $offset = $dt->getOffset();
        if($in_hours){
            $offset = $offset / 3600;
        }
        return $offset;
    }

    public static function _get_timezone_offset_difference($remote_tz, $origin_tz = null)
    {
        if ($origin_tz === null) {
            if (!is_string($origin_tz = date_default_timezone_get())) {
                return false; // A UTC timestamp was returned -- bail out!
            }
        }
        $origin_dtz = new DateTimeZone($origin_tz);
        $remote_dtz = new DateTimeZone($remote_tz);
        $origin_dt = new DateTime("now", $origin_dtz);
        $remote_dt = new DateTime("now", $remote_dtz);
        $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
        $offset = $offset / 3600;
        return $offset;
    }

    public static function _to_timezone($TimeStr, $TimeZoneNameFrom="UTC", $TimeZoneNameTo="UTC"){
        $date = new DateTime($TimeStr, new DateTimeZone($TimeZoneNameFrom));
        $date->setTimezone(new DateTimeZone($TimeZoneNameTo));
        return $date;
    }
}
