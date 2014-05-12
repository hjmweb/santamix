<?php
/**
 * @version   $Id: dateformats.php 6810 2013-01-28 04:04:49Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die();

/**
 * @package     gantry
 * @subpackage  admin.elements
 */
class JFormFieldDateformats extends JFormField
{
    /**
     * @var string
     */
    var $_name = 'DateFormats';

    /**
     * @var string
     */
    public $type = 'DateFormats';

    /**
     * @return mixed
     */
    function getInput()
    {
        $class = ($this->element['class'] ? 'class="' . $this->element['class'] . '"' : 'class="inputbox"');
        $options = array();

        $now =JFactory::getDate();
        $user = JFactory::getUser();
        $config = JFactory::getConfig();

        $now->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

        foreach ($this->element->children() as $opt) {
            $opt->_data = $now->format($opt);
            $options[]     = JHTML::_('select.option', (string)$opt['value'], (string)$opt->_data);
        }
        return JHTML::_('select.genericlist', $options, $this->name, $class, 'value', 'text', $this->value, $this->name);
    }
}
