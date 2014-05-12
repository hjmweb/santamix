<?php
/**
 * @version   $Id: jomsocialeventcategory.php 8822 2013-03-27 14:04:22Z steph $
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
class JFormFieldJomSocialEventCategory extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'JomSocialEventCategory';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getInput()
	{
		$attr = '';
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';
		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string)$this->element['readonly'] == 'true' || (string)$this->element['disabled'] == 'true') {
			$attr .= ' disabled="disabled"';
		}
		$attr .= $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';
		$attr .= $this->element['multiple'] ? ' multiple="multiple"' : '';
		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '"' : '';


		require_once(JPATH_ROOT . '/components/com_community/libraries/core.php');
		CFactory::load('helpers', 'event');
		$model = CFactory::getModel('events');

		$categories = $model->getCategories();

		$options   = array();
        $options[] = JHTML::_('select.option', '0', JText::_('JOPTION_ALL_CATEGORIES'));

		$children = array();
		if ($categories) {
			foreach ($categories as $v) {
				$v->title     = $v->name;
				$v->parent_id = $v->parent;
				$pt           = $v->parent_id;
				$list         = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}

		//TODO possibly change to not use JHTML
		$list = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);

		$mitems = array();
		foreach ($list as $item) {
			$item->treename    = JString::str_ireplace('&#160;&#160;-', ' -', $item->treename);
			$item->treename    = JString::str_ireplace('&#160;&#160;', ' -', $item->treename);
			$mitems[$item->id] = $item->treename;
		}

		foreach ($mitems as $key => $val) {
			$options[] = JHtml::_('select.option', $key, $val);
		}
		return JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->name);
	}
}
