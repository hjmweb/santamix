<?php
/**
 * @version   $Id: redeventvenue.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');

/**
 * @package     gantry
 * @subpackage  admin.elements
 */
class JFormFieldRedEventVenue extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'RedEventVenue';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getInput()
	{

		$db    = JFactory::getDBO();
		$query = 'SELECT id, venue FROM #__redevent_venues WHERE published = 1 ORDER BY ordering';

		$db->setQuery($query);
        $venues = $db->loadObjectList();

		$options   = array();
        $options[] = JHTML::_('select.option', '0', JText::_('ROKMINIEVENTS3_REDEVENT_ALL_VENUES'));
		foreach ($venues as $venue) {
			$options[] = JHTML::_('select.option', $venue->id, $venue->venue);
		}
		return JHTML::_('select.genericlist', $options, $this->name, 'class="inputbox" multiple="multiple"', 'value', 'text', $this->value, $this->name);
	}
}
