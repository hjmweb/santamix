<?php
/**
 * @package         Advanced Module Manager
 * @version         4.14.1
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2014 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Modules Component Module Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.5
 */
class AdvancedModulesModelModules extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  An optional associative array of configuration settings.
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'client_id',
				'id', 'a.id',
				'ordering', 'a.ordering',
				'color', 'a.color',
				'title', 'a.title',
				'note', 'a.note',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'published', 'a.published',
				'access', 'a.access', 'access_level',
				'ordering', 'a.ordering',
				'module', 'a.module',
				'language', 'a.language',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'client_id', 'a.client_id',
				'position', 'a.position',
				'pages',
				'name', 'e.name',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$accessId = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', null, 'int');
		$this->setState('filter.access', $accessId);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		$position = $this->getUserStateFromRequest($this->context . '.filter.position', 'filter_position', '', 'string');
		$this->setState('filter.position', $position);

		$module = $this->getUserStateFromRequest($this->context . '.filter.module', 'filter_module', '', 'string');
		$this->setState('filter.module', $module);

		$menuid = $this->getUserStateFromRequest($this->context . '.filter.menuid', 'filter_menuid', '', 'string');
		$this->setState('filter.menuid', $menuid);

		$clientId = $app->input->getString('client_id', '');
		if ($clientId !== '')
		{
			if ($clientId != $app->getUserState($this->context . '.client_id'))
			{
				$app->setUserState($this->context . '.client_id', $clientId);
				$app->input->set('limitstart', 0);
			}
		}
		else
		{
			$clientId = $app->getUserState($this->context . '.client_id');
		}
		$clientId = (int) $clientId;
		$this->setState('stfilter.client_id', $clientId);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// List state information.
		parent::populateState('position', 'asc');

		// Load the parameters.
		$params = JComponentHelper::getParams('com_advancedmodules');
		$this->setState('params', $params);

	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string    A prefix for the store id.
	 *
	 * @return  string    A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.position');
		$id .= ':' . $this->getState('filter.module');
		$id .= ':' . $this->getState('filter.menuid');
		$id .= ':' . $this->getState('stfilter.client_id');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Returns an object list
	 *
	 * @param   string The query
	 * @param   int    Offset
	 * @param   int    The number of records
	 * @return  array
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$ordering = strtolower($this->getState('list.ordering', 'ordering'));
		$orderDirn = strtoupper($this->getState('list.direction', 'ASC'));

		if (in_array($ordering, array('pages', 'name')))
		{
			$this->_db->setQuery($query);
			$result = $this->_db->loadObjectList();
			$this->translate($result);
			JArrayHelper::sortObjects($result, $ordering, $orderDirn == 'DESC' ? -1 : 1, true, true);
			$total = count($result);
			$this->cache[$this->getStoreId('getTotal')] = $total;
			if ($total < $limitstart)
			{
				$limitstart = 0;
				$this->setState('list.start', 0);
			}
			return array_slice($result, $limitstart, $limit ? $limit : null);
		}
		else if ($ordering == 'color')
		{
			$this->_db->setQuery($query);
			$result = $this->_db->loadObjectList();
			$this->translate($result);
			$newresult = array();
			foreach ($result as $i => $row)
			{
				$registry = new JRegistry;
				$registry->loadString($row->advancedparams);
				$params = $registry->toObject();
				$color = isset($params->color) ? str_replace('#', '', $params->color) : 'none';
				$color = empty($color) ? 'none' : $color;
				$newresult['_' . $color . '_' . (($i + 1) / 10000)] = $row;
			}
			if ($orderDirn == 'DESC')
			{
				krsort($newresult);
			}
			else
			{
				ksort($newresult);
			}
			$newresult = array_values($newresult);
			$total = count($newresult);
			$this->cache[$this->getStoreId('getTotal')] = $total;
			if ($total < $limitstart)
			{
				$limitstart = 0;
				$this->setState('list.start', 0);
			}
			return array_slice($newresult, $limitstart, $limit ? $limit : null);
		}
		else
		{
			if ($ordering == 'ordering')
			{
				$query->order('a.position ASC');
				$ordering = 'a.ordering';
			}
			if ($ordering == 'language')
			{
				$ordering = 'l.title';
			}
			$query->order($this->_db->quoteName($ordering) . ' ' . $orderDirn);
			if ($ordering == 'position')
			{
				$query->order('a.ordering ASC');
			}
			$result = parent::_getList($query, $limitstart, $limit);
			$this->translate($result);
			return $result;
		}
	}

	/**
	 * Translate a list of objects
	 *
	 * @param   array The array of objects
	 * @return  array The array of translated objects
	 */
	protected function translate(&$items)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$lang = JFactory::getLanguage();
		$client = $this->getState('stfilter.client_id') ? 'administrator' : 'site';

		foreach ($items as $item)
		{
			$extension = $item->module;
			$source = constant('JPATH_' . strtoupper($client)) . "/modules/$extension";
			$lang->load("$extension.sys", constant('JPATH_' . strtoupper($client)), null, false, true)
				|| $lang->load("$extension.sys", $source, null, false, true);
			$item->name = JText::_($item->name);
			$registry = new JRegistry;
			$registry->loadString($item->advancedparams);
			$params = $registry->toObject();
			if (isset($params->mirror_module) && $params->mirror_module && isset($params->mirror_moduleid) && !empty($params->mirror_moduleid))
			{
				$query->clear()
					->select('MIN(mm.menuid) AS pages')
					->from('#__modules AS a')
					->join('LEFT', '#__modules_menu AS mm ON mm.moduleid = a.id')
					->where('a.id = ' . (int) $params->mirror_moduleid['0']);
				$db->setQuery($query);
				$item->pages = $db->loadResult();
				if ($params->mirror_module == 2)
				{
					if (is_null($item->pages))
					{
						$item->pages = 0;
					}
					else
					{
						$item->pages = $item->pages * -1;
					}
				}
			}
			if (is_null($item->pages))
			{
				$item->pages = JText::_('JNONE');
			}
			elseif ($item->pages < 0)
			{
				$item->pages = JText::_('COM_MODULES_ASSIGNED_VARIES_EXCEPT');
			}
			elseif ($item->pages > 0)
			{
				$item->pages = JText::_('COM_MODULES_ASSIGNED_VARIES_ONLY');
			}
			else
			{
				$item->pages = JText::_('JALL');
			}
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			// Select the required fields from the table.
			->select('a.id')
			->from('#__modules AS a')

			// Join over the language
			->select('l.title AS language_title')
			->join('LEFT', '#__languages AS l ON l.lang_code = a.language')

			// Join over the users for the checked out user.
			->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id=a.checked_out')

			// Join over the asset groups.
			->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access')

			// Join over the module menus
			->select('MIN(mm.menuid) AS pages')
			->join('LEFT', '#__modules_menu AS mm ON mm.moduleid = a.id')

			// Join over the extensions
			->select('e.name AS name')
			->join('LEFT', '#__extensions AS e ON e.element = a.module')

			// Group
			->group(
				'a.id, a.title, a.note, a.position, a.module, a.language, a.checked_out,' .
				'a.checked_out_time, a.published, a.access, a.ordering, l.title, uc.name, ag.title, e.name,' .
				'l.lang_code, uc.id, ag.id, mm.moduleid, e.element, a.publish_up, a.publish_down, e.enabled'
			);

		// Filter by module
		$module = $this->getState('filter.module');
		if ($module)
		{
			$query->where('a.module = ' . $db->quote($module));
		}

		// Filter by menuid
		$menuid = $this->getState('filter.menuid');
		switch ($menuid)
		{
			case '':
				break;
			case '0':
				$query->where('mm.menuid = 0');
				break;
			case '-':
				$query->where('mm.menuid IS NULL');
				break;
			case '-1':
				$query->where('mm.menuid LIKE \'-%\'');
				break;
			case '-2':
				$query->where('mm.menuid NOT LIKE \'-%\' AND mm.menuid != 0');
				break;
			default:
				$query->where('(mm.menuid IN (0, ' . (int) $menuid . ') OR (mm.menuid LIKE \'-%\' AND mm.menuid != ' . $db->quote('-' . (int) $menuid) . '))');
				break;
		}

		// Filter by position
		$position = $this->getState('filter.position');
		if ($position && $position != 'none')
		{
			$query->where('a.position = ' . $db->quote($position));
		}

		elseif ($position == 'none')
		{
			$query->where('a.position = ' . $db->quote(''));
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(' . 'a.title LIKE ' . $search . ' OR a.note LIKE ' . $search . ')');
			}
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('a.access = ' . (int) $access);
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where('a.language = ' . $db->quote($language));
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published == '')
		{
			$query->where('(a.published IN (0, 1))');
		}

		// Filter by client.
		$clientId = $this->getState('stfilter.client_id');
		if (is_numeric($clientId))
		{
			$query->where('a.client_id = ' . (int) $clientId . ' AND e.client_id =' . (int) $clientId);
		}

		$ids = array();

		$db->setQuery($query);
		$items = $db->loadObjectList();
		foreach ($items as $item)
		{
			if (JFactory::getUser()->authorise('core.edit', 'com_modules.module.' . $item->id))
			{
				$ids[] = $item->id;
			}
		}

		$query->clear('where');
		if (empty($ids))
		{
			$query->where('1 = 0');
		}
		else
		{
			$query->where('a.id IN (' . implode(',', $ids) . ')');
		}

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.note, a.position, a.module, a.language,' .
					'a.checked_out, a.checked_out_time, a.published+2*(e.enabled-1) as published, a.access, a.ordering, a.publish_up, a.publish_down'
			)
		);

		// Join advanced params
		$query->select('aa.params AS advancedparams')
			->join('LEFT', '#__advancedmodules AS aa ON aa.moduleid = a.id');

		return $query;
	}
}
