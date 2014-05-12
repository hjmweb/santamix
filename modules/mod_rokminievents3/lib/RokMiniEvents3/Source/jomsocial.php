<?php
/**
 * @version   $Id: jomsocial.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');

class RokMiniEvents3SourceJomSocial extends RokMiniEvents3_SourceBase
{
	function getEvents(&$params)
	{
		require_once(JPATH_ROOT . '/components/com_community/libraries/core.php');
		require_once(JPATH_ROOT . '/administrator/components/com_community/tables/configuration.php');
		require_once(JPATH_ROOT . '/components/com_community/models/events.php');
		$cfactory = new CFactory();
		$cfactory->load('helpers', 'event');
		$cfactory->load('helpers', 'string');
		$cfactory->load('helpers', 'time');


		// Reuse existing language file from JomSocial
		$language = JFactory::getLanguage();
		$language->load('com_community', JPATH_ROOT);

		//$model = $cfactory->getModel('Events');

		$model   = new CommunityModelEvents();
		$user_id = null;
		//if ((bool) $params->get( 'jomsocial_user' , false )){
		//    $user = JFactory::getUser();
		//    $user_id = $user->id;
		//}


		$advanced = null;
		if ($params->get('time_range') == 'time_span' || $params->get('rangespan') != 'all_events') {
			$advanced              = array();
			$advanced['startdate'] = $params->get('startmin');
			$startMax              = $params->get('startmax', false);
			if ($startMax !== false) {
				$advanced['enddate'] = $startMax;
			}
		}
		$cat = $params->get('jomsocial_category', 0);
		//if all cats is selected return 0 for cats
		if (is_array($cat) && in_array(0, $cat)) $cat = 0;

		$rows   = $model->getEvents($cat, $user_id, null, null, (bool)$params->get('jomsocial_past', false), false, null, $advanced, $params->get('jomsocial_type', CEventHelper::ALL_TYPES), 0, $params->get('jomsocial_total', 10));
        $events = array();
        if(empty($rows)) return $events;
		foreach ($rows as $row) {
			$table = JTable::getInstance('Event', 'CTable');
			$table->bind($row);
			$handler = CEventHelper::getHandler($table);

			if ($params->get('jomsocial_links') != 'link_no') {
				$link = array(
					'internal' => ($params->get('jomsocial_links') == 'link_internal') ? true : false,
					'link'     => $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewevent&eventid=' . $table->id)
				);
			} else {
				$link = false;
			}

            $conf = JFactory::getConfig();
            $tz = $conf->get('offset');
            $tz_offset = RokMiniEvents3::_get_timezone_offset($tz, false);
            $db_offset =  ($row->offset != 0) ? ($row->offset * 3600 * -1) : 0;

            $startdate = strtotime($row->startdate) - (int)$tz_offset + (int)$db_offset;
            $enddate = strtotime($row->enddate) - (int)$tz_offset + (int)$db_offset;
            $time_diff = (int)$enddate - (int)$startdate;

			$event = new RokMiniEvents3_Event($startdate, $enddate, $row->title, $row->description, $link);
            if ($startdate==$enddate || ($time_diff <= 86401 && $time_diff >= 86399) || $row->allday) $event->setAllDay(true);

			$events[] = $event;
		}

		return $events;
	}

	/**
	 * Checks to see if the source is available to be used
	 * @return bool
	 */
	function available()
	{
		$db    = JFactory::getDBO();
		$query = 'select count(*) from #__extensions where element = ' . $db->Quote('com_community') . ' AND enabled=1';
		$db->setQuery($query);
		$count = (int)$db->loadResult();
		if ($count > 0) {
			return true;
		}
		return false;
	}
}
