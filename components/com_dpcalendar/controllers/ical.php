<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.controller');

class DPCalendarControllerIcal extends JControllerLegacy
{

	public function download ()
	{
		$cat = DPCalendarHelper::getCalendar(JRequest::getInt('id'));
		$childrens = $cat->getChildren();
		$catarr = array(
				JRequest::getInt('id')
		);
		if ($childrens)
		{
			foreach ($childrens as $c)
			{
				$catarr[] = $c->id;
			}
		}
		DPCalendarHelperIcal::createIcalFromCalendar($catarr, true);
		return true;
	}
}
