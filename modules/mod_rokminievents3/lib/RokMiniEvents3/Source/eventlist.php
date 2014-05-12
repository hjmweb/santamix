<?php
/**
 * @version   $Id: eventlist.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');

class RokMiniEvents3SourceEventList extends RokMiniEvents3_SourceBase
{
	function getEvents(&$params)
	{
		// Reuse existing language file from JomSocial
		$language = JFactory::getLanguage();
		$language->load('com_eventlist', JPATH_ROOT);


		$query_start_date = null;
		$query_end_date   = null;

		if ($params->get('time_range') == 'time_span' || $params->get('rangespan') != 'all_events') {
			$query_start_date = $params->get('startmin');
			$startMax         = $params->get('startmax', false);
			if ($startMax !== false) {
				$query_end_date = $startMax;
			}
		}


		$app = JFactory::getApplication();

		$db       = JFactory::getDBO();
		$user     = JFactory::getUser();
		$user_gid = (int)$user->get('aid');

		$catid = trim($params->get('eventlist_category', 0));
		$venid = trim($params->get('eventlist_venue', 0));

		$categories = '';
		if ($catid != 0) {
			$categories = ' AND catsid = ' . $catid;
		}
		$venues = '';
		if ($venid != 0) {
			$venues = ' AND locid = ' . $venid;
		}

		$dates_start = '';
		if (!empty($query_start_date)) {
			$dates_start = ' AND a.dates >= ' . $db->Quote($query_start_date);
		}
		$dates_end = '';
		if (!empty($query_end_date)) {
			$dates_end = ' AND a.enddates <= ' . $db->Quote($query_end_date);
		}

		$query = 'SELECT a.*,' . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug' . ' FROM #__eventlist_events AS a' . ' WHERE a.published = 1 ' . $categories . $venues . $dates_start . $dates_end . ' ORDER BY a.dates ASC, a.times ASC';

		$db->setQuery($query);
		$rows = $db->loadObjectList();


		$total_count = 1;
		$total_max   = $params->get('eventlist_total', 10);
        $events = array();
        if(empty($rows)) return $events;
		foreach ($rows as $row) {
			if ($params->get('eventlist_links') != 'link_no') {

				$link = array(
					'internal' => ($params->get('eventlist_links') == 'link_internal') ? true : false,
					'link'     => JRoute::_(self::getRoute($row->slug))
				);
			} else {
				$link = false;
			}
			if (!ini_get('date.timezone')) {
				date_default_timezone_set('UTC');
			}
			$offset = 0;
			if ($params->get('eventlist_dates_format', 'utc') == 'joomla') {
				$conf     = JFactory::getConfig();
				$timezone = $conf->get('offset');
				$offset   = $timezone * 3600 * -1;
			}

			$startdate = strtotime($row->dates . ' ' . $row->times) + $offset;
			$enddate   = ($row->enddates) ? strtotime($row->enddates . ' ' . $row->endtimes) + $offset : strtotime($row->dates . ' ' . $row->endtimes) + $offset;
			$event     = new RokMiniEvents3_Event($startdate, $enddate, $row->title, $row->datdescription, $link);
			if ($startdate === $enddate) $event->setAllDay(true);
			if (!$row->enddates && !$row->endtimes) $event->setNoEndTime(true);
			$events[] = $event;
			$total_count++;
			if ($total_count > $total_max) break;
		}

		//$events = array();
		return $events;
	}

	/**
	 * Checks to see if the source is available to be used
	 * @return bool
	 */
	function available()
	{
		$db    = JFactory::getDBO();
		$query = 'select count(*) from #__extensions where element = ' . $db->Quote('com_eventlist') . ' AND enabled=1';
		$db->setQuery($query);
		$count = (int)$db->loadResult();
		if ($count > 0) {
			return true;
		}
		return false;
	}

	/**
	 * Determines an EventList Link
	 *
	 * @param int    The id of an EventList item
	 * @param string The view
	 *
	 * @since 0.9
	 *
	 * @return string determined Link
	 */
	function getRoute($id, $view = 'details')
	{
		//Not needed currently but kept because of a possible hierarchic link structure in future
		$needles = array(
			$view => (int)$id
		);

		//Create the link
		$link = 'index.php?option=com_eventlist&view=' . $view . '&id=' . $id;

		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid=' . $item->id;
		}
		;

		return $link;
	}

	/**
	 * Determines the Itemid
	 *
	 * searches if a menuitem for this item exists
	 * if not the first match will be returned
	 *
	 * @param array The id and view
	 *
	 * @since 0.9
	 *
	 * @return int Itemid
	 */
	function _findItem($needles)
	{
		$component = JComponentHelper::getComponent('com_eventlist');

		$site   = new JSite();
		$menus  = $site->getMenu();
		$items  = $menus->getItems('component_id', $component->id);
		$user   = JFactory::getUser();
		$access = (int)$user->get('aid');

		//Not needed currently but kept because of a possible hierarchic link structure in future
		foreach ($needles as $needle => $id) {
			if (!empty($items)) {
				foreach ($items as $item) {

					if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id) && ($item->access <= $access)) {
						return $item;
					}
				}


				//no menuitem exists -> return first possible match
				foreach ($items as $item) {
					if ($item->access <= $access) {
						return $item;
					}
				}
			}

		}
		return false;
	}
}
