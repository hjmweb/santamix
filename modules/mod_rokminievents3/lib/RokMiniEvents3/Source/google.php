<?php
/**
 * @version   $Id: google.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');

class RokMiniEvents3SourceGoogle extends RokMiniEvents3_SourceBase
{
	function getEvents(&$params)
	{
        //simplepie has many strict standards errors, since they are not minievents errors they have been suppressed
		jimport('simplepie.simplepie');

		$id      = $params->get('google_gid', '');
		$orderby = $params->get('google_orderby', 'starttime');

		$query = '?singleevents=true&orderby=' . $orderby . '&sortorder=a&max-results=' . $params->get('google_maxresults', 25);

		if ($params->get('time_range') != 'time_span' && $params->get('rangespan') == 'all_events') {
            if ($params->get('google_past')){
                $query .= '&futureevents=false';
            } else {
                $query .= '&futureevents=true';
            }
		} else {
			$startMin = $params->get('startmin');

			$query .= "&start-min=" . $startMin;
			$startMax = $params->get('startmax', false);
			if ($startMax !== false) {
				$query .= "&start-max=" . $startMax;
			}
		}

		$query = 'http://www.google.com/calendar/feeds/' . $id . '/public/full' . $query;

		$rss = @new SimplePie($query, JPATH_CACHE, $params->get('google_gcache', 3600));
		@$rss->enable_order_by_date(false);
        if ($rss->error) return $events['error'] = $rss->error;

        $items = @$rss->get_items();

        $events = array();
        if(empty($items)) return $events;
		foreach ($items as $item) {
			$when      = @$item->get_item_tags('http://schemas.google.com/g/2005', 'when');
			$link      = ($params->get('google_links') != 'link_no') ? array(
				'internal' => ($params->get('google_links') == 'link_internal') ? true : false,
				'link'     => @$item->get_link()
			) : false;

            //date_default_timezone_set('UTC');
            $startdatetime = str_split($when[0]['attribs']['']['startTime'], 19);
            $enddatetime = str_split($when[0]['attribs']['']['endTime'], 19);

			$startdate = str_replace('T', ' ', $startdatetime[0]);
			$enddate   = str_replace('T', ' ', $enddatetime[0]);
            $time_diff = (int)$enddate - (int)$startdate;

            $event = new RokMiniEvents3_Event($startdate, $enddate, @$item->get_title(), @$item->get_content(), $link);
            if ($startdate==$enddate || ($time_diff <= 86401 && $time_diff >= 86399)) $event->setAllDay(true);

            $events[] = $event;
		}
		return $events;
	}

	function available()
	{
		return true;
	}
}
