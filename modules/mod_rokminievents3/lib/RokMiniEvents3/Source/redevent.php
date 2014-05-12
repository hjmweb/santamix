<?php
/**
 * @version   $Id: redevent.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');

if (!defined('REDEVENT_PATH_SITE')) DEFINE('REDEVENT_PATH_SITE', JPATH_SITE . '/components/com_redevent');

class RokMiniEvents3SourceRedEvent extends RokMiniEvents3_SourceBase
{
	function getEvents(&$params)
	{
		include_once(REDEVENT_PATH_SITE . '/classes/output.class.php');
		include_once(REDEVENT_PATH_SITE . '/helpers/route.php');
		include_once(REDEVENT_PATH_SITE . '/helpers/helper.php');
		include_once(REDEVENT_PATH_SITE . '/classes/useracl.class.php');
		include_once('redevent/model.php');

		// load language file
		$language = JFactory::getLanguage();
		$language->load('com_redevent', JPATH_ROOT);

		$model = new RokMiniEvents3SourceRedEventModel($params);
		$rows  = $model->getData();

		$total_count = 1;
		$total_max   = $params->get('redevent_total', 10);
        $events = array();
        if(empty($rows)) return $events;
		foreach ($rows as $row) {
			if ($params->get('redevent_links') != 'link_no') {

				$link = array(
					'internal' => ($params->get('redevent_links') == 'link_internal') ? true : false,
					'link'     => JRoute::_(REdeventHelperRoute::getDetailsRoute($row->slug, $row->xslug))
				);
			} else {
				$link = false;
			}

			date_default_timezone_set('UTC');
			$offset = 0;
			if ($params->get('redevent_dates_format', 'utc') == 'joomla') {
				$conf     = JFactory::getConfig();
				$timezone = $conf->get('offset');
				$offset   = $timezone * 3600 * -1;
			}

			$startdate = strtotime($row->dates . ' ' . $row->times) + $offset;
			$enddate   = $row->enddates ? strtotime($row->enddates . ' ' . $row->endtimes) + $offset : strtotime($row->dates . ' ' . $row->endtimes) + $offset;
			$event     = new RokMiniEvents3_Event($startdate, $enddate, $row->title, $row->summary, $link);
			if ($startdate === $enddate) $event->setAllDay(true);
			if (!$row->enddates && !$row->endtimes) $event->setNoEndTime(true);
			$events[] = $event;
			$total_count++;
			if ($total_count > $total_max) break;
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
		$query = 'select count(*) from #__extensions where element = ' . $db->Quote('com_redevent') . ' AND enabled=1';
		$db->setQuery($query);
		$count = (int)$db->loadResult();
		if ($count > 0) {
			return true;
		}
		return false;
	}
}
