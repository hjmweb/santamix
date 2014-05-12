<?php
/**
 * @version   $Id: timerange.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// no direct access
defined('_JEXEC') or die();

/**
 *
 */
class JFormFieldTimeRange extends JFormField
{

    /**
     * @var string
     */
    public $type = 'TimeRange';

    /**
     *
     */
    public function getInput()
    {

        $document = JFactory::getDocument();
        $version = new JVersion();

        if (version_compare($version->getShortVersion(), '3.0', '<')) {

            $js = "window.addEvent('load', function() {
                $('" . $this->id . "').addEvent('change', function(){
                    var sel = this.getSelected().get('value');
                    $$('." . $this->element['name'] . "').getParent('li').setStyle('display','none');
                    $$('.'+sel).getParent('li').setStyle('display','block');
                }).fireEvent('change');
            });";

        } else {

            $js = "
            window.addEvent('load', function() {
            var chzn = $('" . $this->id . "_chzn');
                if(chzn!=null){
                    chzn.addEvent('click', function(){
                        $$('." . $this->element['name'] . "').getParent('div.control-group').setStyle('display','none');
                        var text = $('" . $this->id . "_chzn').getElement('span').get('text');
                        var options = $('" . $this->id . "').getElements('option');
                        options.each(function(option) {
                        var optText = String(option.get('text'));
                        var optValue = String(option.get('value'));
                            if(text == optText){
                                var sel = optValue;
                            }
                            $$('.'+sel).getParent('div.control-group').setStyle('display','block');
                        });
                    }).fireEvent('click');
                }
            });";
        }
        $document->addScriptDeclaration($js);

        $options = array();
        $options[] = JHtml::_('select.option', "time_span", JText::_('ROKMINIEVENTS3_TIME_SPAN'), 'value', 'text');
        $options[] = JHtml::_('select.option', "predefined_ranges", JText::_('ROKMINIEVENTS3_PREDEFINEDRANGES'), 'value', 'text');
        $html = JHtml::_('select.genericlist', $options, $this->name, ' size="' . $this->element['size'] . '" ', 'value', 'text', $this->value, $this->id);

        return $html;
    }
}

?>