<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.view', JPATH_ADMINISTRATOR);

class DPCalendarViewTools extends DPCalendarView
{

	protected function addToolbar ()
	{
		if (strpos($this->getLayout(), 'import_') !== false && DPCalendarHelper::getActions()->get('core.create'))
		{
			JToolBarHelper::custom('import.add', 'new.png', 'new.png', 'COM_DPCALENDAR_VIEW_TOOLS_IMPORT', false);
			$this->title = 'COM_DPCALENDAR_MANAGER_TOOLS_IMPORT';
			$this->icon = 'import';
		}
		parent::addToolbar();
	}
}
