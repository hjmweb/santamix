<?php
/**
 * @version   $Id: eventlistcategory.php 8851 2013-03-27 17:08:05Z steph $
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
class JFormFieldtEventListCategory extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'EventListCategory';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getInput()
	{

		$db    = JFactory::getDBO();
		$query = 'SELECT id, catname AS NAME FROM #__eventlist_categories WHERE published = 1 ORDER BY ordering';

		$db->setQuery($query);
		$categories = $db->loadObjectList();

		$options   = array();
        $options[] = JHTML::_('select.option', '0', JText::_('JOPTION_ALL_CATEGORIES'));
		foreach ($categories as $option) {
			$options[] = JHtml::_('select.option', $option->id, $option->name);
		}
		return JHtml::_('select.genericlist', $options, $this->name, 'class="inputbox"', 'value', 'text', $this->value, $this->name);
	}
}
