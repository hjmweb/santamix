<?php
/**
 * @version   $Id: eventlistvenue.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */
defined('_JEXEC') or die();

/**
 * @package     gantry
 * @subpackage  admin.elements
 */
class JFormFieldEventListVenue extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'EventListVenue';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getInput()
	{

		$db    = JFactory::getDBO();
		$query = 'SELECT id, venue AS NAME FROM #__eventlist_venues WHERE published = 1 ORDER BY ordering';

		$db->setQuery($query);
		$categories = $db->loadObjectList();

		$options   = array();
        $options[] = JHTML::_('select.option', '0', JText::_('ROKMINIEVENTS3_REDEVENT_ALL_VENUES'));
		foreach ($categories as $option) {
			$options[] = JHTML::_('select.option', $option->id, $option->name);
		}
		return JHTML::_('select.genericlist', $options, $this->name, 'class="inputbox"', 'value', 'text', $this->value, $this->name);
	}
}
