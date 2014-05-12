<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR);
if (! class_exists('DPCalendarHelper'))
{
	return;
}

JLoader::import('joomla.application.component.model');
JModelLegacy::addIncludePath(JPATH_SITE . DS . 'components' . DS . 'com_dpcalendar' . DS . 'models', 'DPCalendarModel');

$model = JModelLegacy::getInstance('Calendar', 'DPCalendarModel');
$model->getState();
$model->setState('filter.parentIds', $params->get('ids', array(
		'root'
)));
$ids = array();
foreach ($model->getItems() as $calendar)
{
	$ids[] = $calendar->id;
}

$startDate = DPCalendarHelper::getDate();

// Round to the last quater
$startDate->sub(new DateInterval("PT" . $startDate->format("s") . "S"));
$startDate->sub(new DateInterval("PT" . ($startDate->format("i") % 15) . "M"));

$startDate = $startDate->format('U');

$model = JModelLegacy::getInstance('Events', 'DPCalendarModel');
$model->getState();
$model->setState('list.limit', 1);
$model->setState('list.direction', $params->get('order', 'asc'));
$model->setState('category.id', $ids);
$model->setState('category.recursive', true);
$model->setState('filter.search', $params->get('filter', ''));
$model->setState('list.start-date', $startDate);
$model->setState('list.end-date', null);

$item = $model->getItems();
if (empty($item))
{
	$item = null;
}
else
{
	$item = reset($item);
}

require JModuleHelper::getLayoutPath('mod_dpcalendar_counter', $params->get('layout', 'default'));
