<?php
/**
 * @version   $Id: break.php 6813 2013-01-28 04:28:56Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since       11.1
 */
class JFormFieldBreak extends JFormField
{
	/**
	 * @var string
	 */
	protected $type = 'Break';

	/**
	 * @return string
	 */
	protected function getLabel()
	{
		$doc     = JFactory::getDocument();
		$version = new JVersion();
		$doc->addStyleDeclaration(".rok-break {border-bottom:1px solid #eee;font-size:16px;color:#0088CC;margin-top:15px;padding:2px 0;width:100%}");

		if (isset($this->element['label']) && !empty($this->element['label'])) {
			$label   = JText::_((string)$this->element['label']);
			$css     = (string)$this->element['class'];
			$version = new JVersion();
			if (version_compare($version->getShortVersion(), '3.0', '>=')) {
				return '<div class="rok-break ' . $css . '">' . $label . '</div>';
			} else {
				return '<label class="rok-break ' . $css . '">' . $label . '</label>';
			}
		} else {
			return;
		}

	}

	/**
	 * @return mixed
	 */
	protected function getInput()
	{
		return;
	}

}
