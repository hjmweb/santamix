<?php
/**
 * @version   $Id: rsevents.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');


/**
 *
 */
class RokMiniEvents3SourceRSEvents extends RokMiniEvents3_SourceBase
{
    /**
     * @param $params
     * @return array
     */
    function getEvents(&$params)
	{
        if (!class_exists('rseventsproHelper')) {
   			require_once JPATH_SITE.'/components/com_rseventspro/helpers/rseventspro.php';
   		}
        if (!class_exists('RSEvent')) {
            require_once JPATH_SITE.'/components/com_rseventspro/helpers/events.php';
        }
        if (!class_exists('RseventsproHelperRoute')){
            require_once JPATH_SITE.'/components/com_rseventspro/helpers/route.php';
        }

		// Reuse existing language file from JomSocial
		$language = JFactory::getLanguage();
		$language->load('com_rseventspro', JPATH_ROOT);

		$query_start_date = null;
		$query_end_date   = null;

		if ($params->get('time_range') == 'time_span' || $params->get('rangespan') != 'all_events') {
			$query_start_date = $params->get('startmin', null);
			$startMax         = $params->get('startmax', false);
			if ($startMax !== false) {
				$query_end_date = $startMax;
			}
		}

		$db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $nullDate = $db->getNullDate();
        $now = JFactory::getDate()->toSql();

        $query->select('e.id, e.name as title ,e.description, e.start as startdate, e.end as enddate, e.allday');
        $query->select('CONCAT_WS(",", t.id) AS category_ids');
        $query->from('#__rseventspro_events AS e');
        $query->join('left','#__rseventspro_taxonomy AS t ON (t.ide = e.id AND t.type = "category")');
        $query->where('e.published = 1');
        $query->group('e.id');
        $query->order('e.start ASC');

		$catids = $params->get('rsevents_category', array());
        if (isset($catids) && !empty($catids) && !in_array('0', $catids)) {
			foreach ($catids as $catid) {
                $query->where($catid.' IN (' . 'CONCAT_WS(",", t.id)' . ')');
			}
		}

		$locationids = $params->get('rsevents_venue',  array());
        if(!is_array($locationids)) $locationids = array($locationids);
		if (isset($locationids) && !empty($locationids) && !in_array('0', $locationids)) {
            $query->where('e.location IN (' . implode(",", $locationids) . ')');
		}

		if (!empty($query_start_date) && (!is_null($query_start_date))) {
			$rstartdate = $query_start_date;
			if ($params->get('rsevents_past', 0) == 0 && $rstartdate < $now) {
				$rstartdate = $now;
			}
            $query->where('e.start >= ' . $db->quote($rstartdate));
		} else if ($params->get('rsevents_past', 0) == 0) {
			$rstartdate  = $now;
            $query->where('((e.end >= ' . $db->quote($rstartdate) . ') OR (e.start >= ' . $db->quote($rstartdate) . ' AND e.allday = 1))');
		}

		if (!empty($query_end_date) && (!is_null($query_end_date))) {
			$renddate  = $query_end_date;
            $query->where('e.start <= ' . $db->quote($renddate));
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$events = array();
        if(empty($rows)) return $events;
		$total_count = 1;
		$total_max   = $params->get('rsevents_total', 10);
		foreach ($rows as $row) {
			if ($params->get('rsevents_links') != 'link_no') {
				$link = array(
					'internal' => ($params->get('rsevents_links') == 'link_internal') ? true : false,
					'link'     => 'index.php?option=com_rseventspro&layout=show&id='. rseventsproHelper::sef($row->id,$row->title)
				);
			} else {
				$link = false;
			}

			$event = new RokMiniEvents3_Event($row->startdate, $row->enddate, $row->title, $row->description, $link);
			if ($row->allday) $event->setAllDay(true);
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
		$query = 'select count(*) from #__extensions where element = ' . $db->Quote('com_rseventspro') . ' AND enabled=1';
		$db->setQuery($query);
		$count = (int)$db->loadResult();
		if ($count > 0) {
			return true;
		}
		return false;
	}

    /**
     * @param $id
     * @param $list
     * @return bool
     */
    function getChildCategories($id, &$list)
	{
		$db = JFactory::getDBO();

		// Initialize variables
		$return = true;

		// Get all rows with parent of $id
		$query = 'SELECT category as id' . ' FROM #__rsevents_categories' . ' WHERE parent = ' . (int)$id;
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Make sure there aren't any errors
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Recursively iterate through all children... kinda messy
		foreach ($rows as $row) {
			$found = false;
			foreach ($list as $idx) {
				if ($idx == $row->id) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				$list[] = $row->id;
			}
			$return = self::getChildCategories($row->id, $list);
		}
		return $return;
	}
}
