<?php
/**
 * @version   $Id: redeventcategory.php 8913 2013-03-29 07:02:08Z steph $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');

/**
 * @package     gantry
 * @subpackage  admin.elements
 */
class JFormFieldRedEventCategory extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'RedEventCategory';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getInput()
	{

		$db    = JFactory::getDBO();
		$query = 'SELECT id, catname as name from #__redevent_categories where published = 1 order by ordering';

		$db->setQuery($query);
		$categories = $db->loadObjectList();

		$options   = array();
        $options[] = JHTML::_('select.option', '0', JText::_('JOPTION_ALL_CATEGORIES'));
		foreach ($categories as $option) {
			$options[] = JHTML::_('select.option', $option->id, $option->name);
		}
		return JHTML::_('select.genericlist', $options, $this->name, 'class="inputbox" multiple="multiple"', 'value', 'text', $this->value, $this->name);
	}
}
