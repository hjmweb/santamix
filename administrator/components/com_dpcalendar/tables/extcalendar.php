<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarTableExtcalendar extends JTable
{

	public function __construct (&$db)
	{
		parent::__construct('#__dpcalendar_extcalendars', 'id', $db);
	}

	public function bind ($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	public function store ($updateNulls = false)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		if ($this->id)
		{
			// Existing item
			$this->modified = $date->toSql();
			$this->modified_by = $user->get('id');
		}
		else
		{
			if (! (int) $this->created)
			{
				$this->created = $date->toSql();
			}
			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}

		// Set publish_up to null date if not set
		if (! $this->publish_up)
		{
			$this->publish_up = $this->_db->getNullDate();
		}

		// Set publish_down to null date if not set
		if (! $this->publish_down)
		{
			$this->publish_down = $this->_db->getNullDate();
		}

		// Verify that the alias is unique
		$table = JTable::getInstance('Extcalendar', 'DPCalendarTable');
		if ($table->load(array(
				'alias' => $this->alias
		)) && ($table->id != $this->id || $this->id == 0))
		{
			$this->alias = JString::increment($this->alias);
		}

		if (empty($this->_rules))
		{
			$this->_rules = new JRules(array(
					'core.edit' => array(),
					'core.create' => array(),
					'core.delete' => array()
			));
		}

		// Attempt to store the user data.
		return parent::store($updateNulls);
	}

	protected function _getAssetName ()
	{
		$k = $this->_tbl_key;
		return 'com_dpcalendar.extcalendar.' . (int) $this->$k;
	}

	protected function _getAssetParentId (JTable $table = null, $id = null)
	{
		$asset = JTable::getInstance('Asset');
		$asset->loadByName('com_dpcalendar');
		return $asset->id;
	}

	public function check ()
	{
		// Check for valid name
		if (trim($this->title) == '')
		{
			$this->setError(JText::sprintf('COM_DPCALENDAR_EXTCALENDAR_ERR_TABLES_NAME', ''));
			return false;
		}

		// Check for existing name
		$query = 'SELECT id FROM #__dpcalendar_extcalendars WHERE title = ' . $this->_db->Quote($this->title);
		$this->_db->setQuery($query);

		$xid = (int) $this->_db->loadResult();
		if ($xid && $xid != (int) $this->id)
		{
			$this->setError(JText::sprintf('COM_DPCALENDAR_EXTCALENDAR_ERR_TABLES_NAME', htmlentities($this->title)));
			return false;
		}

		if (empty($this->alias))
		{
			$this->alias = $this->title;
		}
		$this->alias = JApplication::stringURLSafe($this->alias);
		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = JFactory::getDate()->format("Y-m-d-H-i-s");
		}

		if (empty($this->language))
		{
			$this->language = '*';
		}

		// Check the publish down date is not earlier than publish up.
		if ($this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up)
		{
			$this->setError(JText::_('JGLOBAL_START_PUBLISH_AFTER_FINISH'));
			return false;
		}

		// Clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (! empty($this->metakey))
		{
			$bad_characters = array(
					"\n",
					"\r",
					"\"",
					"<",
					">"
			);

			$after_clean = JString::str_ireplace($bad_characters, "", $this->metakey);
			$keys = explode(',', $after_clean);
			$clean_keys = array();
			foreach ($keys as $key)
			{
				if (trim($key))
				{
					$clean_keys[] = trim($key);
				}
			}
			$this->metakey = implode(", ", $clean_keys);
		}

		$this->color = str_replace('#', '', $this->color);

		return true;
	}

	public function publish ($pks = null, $state = 1, $userId = 0)
	{
		$k = $this->_tbl_keys;

		if (! is_null($pks))
		{
			foreach ($pks as $key => $pk)
			{
				if (! is_array($pk))
				{
					$pks[$key] = array(
							$this->_tbl_key => $pk
					);
				}
			}
		}

		$userId = (int) $userId;
		$state = (int) $state;

		// If there are no primary keys set check to see if the instance key is
		// set.
		if (empty($pks))
		{
			$pk = array();

			foreach ($this->_tbl_keys as $key)
			{
				if ($this->$key)
				{
					$pk[$this->$key] = $this->$key;
				}
				// We don't have a full primary key - return false
				else
				{
					return false;
				}
			}

			$pks = array(
					$pk
			);
		}

		foreach ($pks as $pk)
		{
			// Update the publishing state for rows with the given primary keys.
			$query = $this->_db->getQuery(true)
				->update($this->_tbl)
				->set('state = ' . (int) $state);

			// Determine if there is checkin support for the table.
			if (property_exists($this, 'checked_out') || property_exists($this, 'checked_out_time'))
			{
				$query->where('(checked_out = 0 OR checked_out = ' . (int) $userId . ')');
				$checkin = true;
			}
			else
			{
				$checkin = false;
			}

			// Build the WHERE clause for the primary keys.
			$this->appendPrimaryKeys($query, $pk);

			$this->_db->setQuery($query);
			$this->_db->execute();

			// If checkin is supported and all rows were adjusted, check them
			// in.
			if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
			{
				$this->checkin($pk);
			}

			$ours = true;

			foreach ($this->_tbl_keys as $key)
			{
				if ($this->$key != $pk[$key])
				{
					$ours = false;
				}
			}

			if ($ours)
			{
				$this->published = $state;
			}
		}

		$this->setError('');

		return true;
	}
}
