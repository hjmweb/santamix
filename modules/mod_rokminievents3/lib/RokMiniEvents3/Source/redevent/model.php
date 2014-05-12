<?php
/**
 * @version    $Id: model.php 20114 2014-04-02 17:18:27Z btowles $
 * @author     RocketTheme http://www.rockettheme.com
 * @copyright  Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 * @version    1.0 $Id: model.php 20114 2014-04-02 17:18:27Z btowles $
 * @package    Joomla
 * @subpackage redEVENT
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 *             redEVENT is based on EventList made by Christoph Lukes from schlu.net
 *             redEVENT can be downloaded from www.redcomponent.com
 *             redEVENT is free software; you can redistribute it and/or
 *             modify it under the terms of the GNU General Public License 2
 *             as published by the Free Software Foundation.
 *             redEVENT is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU General Public License for more details.
 *             You should have received a copy of the GNU General Public License
 *             along with redEVENT; if not, write to the Free Software
 *             Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

include_once(JPATH_SITE . '/components/com_redevent/models/baseeventslist.php');

class RokMiniEvents3SourceRedEventModel extends RedeventModelBaseEventList
{

	protected $_params = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct($params)
	{
		parent::__construct();

		$this->_params = $params;
		$this->setState('limit', $params->get('redevent_total', 10));
		$this->setState('limitstart', 0);
	}

	/**
	 * Build the where clause
	 *
	 * @access private
	 * @return string
	 */
	function _buildWhere()
	{
		$user = JFactory::getUser();
		$gid  = max($user->getAuthorisedViewLevels());

		// First thing we need to do is to select only needed events
		if ($this->_params->get('redevent_include_archived', 0)) {
			$where[] = ' (x.published = 1 OR x.published = -1) ';
		} else {
			$where[] = ' x.published = 1 ';
		}

		if ($this->_params->get('redevent_featured', 0)) {
			$where[] = ' x.featured = 1 ';
		}

		//$where[] = ' c.access <= ' . $gid;

		if ($cat = $this->_params->get('redevent_category')) {
			$category = $this->getCategory((int)$cat);
			if ($category) {
				$where[] = '(c.id = ' . $this->_db->Quote($category->id) . ' OR (c.lft > ' . $this->_db->Quote($category->lft) . ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))';
			}
		}

		if ($v = $this->_params->get('redevent_venue')) {
			$where[] = ' l.id = ' . $this->_db->Quote($v);
		}

		$query_start_date = null;
		$query_end_date   = null;

		if ($this->_params->get('time_range') == 'time_span' || $this->_params->get('rangespan') != 'all_events') {
			$query_start_date = $this->_params->get('startmin');
			$startMax         = $this->_params->get('startmax', false);
			if ($startMax !== false) {
				$query_end_date = $startMax;
			}
		}

		if (!empty($query_start_date)) {
			$where[] = ' (CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) >= ' . $this->_db->Quote($query_start_date);
		}
		if (!empty($query_end_date)) {
			$where[] = ' (CASE WHEN x.endtimes THEN CONCAT(x.enddates," ",x.endtimes) ELSE x.enddates END) <= ' . $this->_db->Quote($query_end_date);
		}

		return ' WHERE ' . implode(' AND ', $where);
	}

	/**
	 * Build the query
	 *
	 * @access private
	 * @return string
	 */
	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where    = $this->_buildWhere();
		$orderby  = $this->_buildOrderBy();
		$customs  = $this->getCustomFields();
		$xcustoms = $this->getXrefCustomFields();

		$user = JFactory::getUser();
		$gids = $user->getAuthorisedGroups();
		if (!is_array($gids) || !count($gids)) {
			$gids = array(0);
		}
		$gids = implode(',', $gids);


		//Get Events from Database
		$query = 'SELECT x.dates, x.enddates, x.times, x.endtimes, x.registrationend, x.id AS xref, ' . ' x.maxattendees, x.maxwaitinglist, x.course_credit, x.featured, x.icaldetails, x.icalvenue, x.title as session_title, ' . ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title, ' . ' a.id, a.title, a.created, a.datdescription, a.registra, a.datimage, a.summary, a.submission_type_external, ' . ' l.venue, l.city, l.state, l.url, l.street, l.country, ' . ' c.catname, c.id AS catid,' . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug,' . ' CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug';
		// add the custom fields
		foreach ((array)$customs as $c) {
			$query .= ', a.custom' . $c->id;
		}
		// add the custom fields
		foreach ((array)$xcustoms as $c) {
			$query .= ', x.custom' . $c->id;
		}

		$query .= ' FROM #__redevent_event_venue_xref AS x' . ' LEFT JOIN #__redevent_events AS a ON a.id = x.eventid' . ' LEFT JOIN #__redevent_venues AS l ON (l.id = x.venueid AND l.private = 0)' . ' LEFT JOIN #__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id' . ' LEFT JOIN #__redevent_venues_categories AS vc ON (vc.id = xvcat.category_id AND vc.private = 0 AND vc.private IS NULL)' . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id' . ' LEFT JOIN #__redevent_categories AS c ON (c.id = xcat.category_id AND c.private = 0)' . ' LEFT JOIN #__redevent_groups_venues AS gv ON (gv.venue_id = l.id AND gv.group_id IN (' . $gids . ') AND gv.id IS NOT NULL)' . ' LEFT JOIN #__redevent_groups_venues_categories AS gvc ON (gvc.category_id = vc.id AND gvc.group_id IN (' . $gids . ') AND gvc.id IS NOT NULL)' . ' LEFT JOIN #__redevent_groups_categories AS gc ON (gc.category_id = c.id AND gc.group_id IN (' . $gids . ') AND gc.id IS NOT NULL)';

		$query .= $where . ' GROUP BY (x.id) ' . $orderby;
		return $query;
	}

}