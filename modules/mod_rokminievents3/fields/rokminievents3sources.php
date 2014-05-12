<?php
/**
 * @version   $Id: rokminievents3sources.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.filesystem.folder');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');

/**
 *
 */
class JFormFieldRokMiniEvents3Sources extends JFormField
{
    /**
     * @var string
     */
    static $ROKMINIEVENTS3_ROOT;
    /**
     * @var string
     */
    static $SOURCE_DIR;

    /**
     * @var array
     */
    protected $element_dirs = array();

    /**
     * @var string
     */
    public $type = 'RokMiniEvents3Sources';

    /**
     * @param null $parent
     */
    public function __construct($form = null)
    {
        if (!defined('ROKMINIEVENTS3')) define('ROKMINIEVENTS3','ROKMINIEVENTS3');

        // Set base dirs
        self::$ROKMINIEVENTS3_ROOT = JPATH_ROOT.'/modules/mod_rokminievents3';
        self::$SOURCE_DIR = self::$ROKMINIEVENTS3_ROOT.'/lib/RokMiniEvents3/Source';

        //load up the RTCommon
        require_once(self::$ROKMINIEVENTS3_ROOT. '/lib/include.php');

        parent::__construct($form);
    }

    /**
     * @return string
     */
    protected function getLabel()
    {
        $version = new JVersion();
        if (version_compare($version->getShortVersion(), '3.0', '<')) {
            return;
        } else {

            $doc =JFactory::getDocument();
            $buffer = '';

            $form = RokSubfieldForm::getInstanceFromForm($this->form);

            JForm::addFieldPath(dirname(__FILE__) . '/fields');

            $sourcesets = $form->getSubFieldsets('rokminievents3-sources');


            $css="
                .rok-break {border-bottom:1px solid #eee;font-size:16px;color:#0088CC;margin-top:15px;padding:2px 0;width:100%;}
                div.themeset > div.control-label {margin-bottom:18px}
                div.themeset > div.controls {margin-bottom:18px}
             ";
            $doc->addStyleDeclaration($css);

            $label = JText::_((string)$this->element['label']);
            $css   = (string)$this->element['class'];

            $buffer .= '<div class="rok-break ' . $css . '">' . $label . '</div>';

            foreach ($sourcesets as $sourceset => $sourceset_val) {
                $sourceset_fields = $form->getSubFieldset('rokminievents3-sources', $sourceset, 'params');
                ob_start();
                ?>
                <div class="sourceset" id="srouceset-<?php echo $sourceset;?>">

                    <?php foreach ($sourceset_fields as $sourceset_field): ?>
                    <div class="control-group">

                        <div class="control-label themeset">
                            <?php echo $sourceset_field->getLabel(); ?>
                        </div>
                        <div class="controls themeset">
                            <?php echo $sourceset_field->getInput(); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            <?php
                $buffer .= ob_get_clean();
            }
            return $buffer;
        }
    }

    /**
     * @return string
     */
    protected function getInput()
    {

        $version = new JVersion();
        if (version_compare($version->getShortVersion(), '3.0', '<')) {

            $doc =JFactory::getDocument();
            $buffer = '';

            $form = RokSubfieldForm::getInstanceFromForm($this->form);

            JForm::addFieldPath(dirname(__FILE__) . '/fields');

            $sourcesets = $form->getSubFieldsets('rokminievents3-sources');

            foreach($sourcesets as $sourceset => $sourceset_val)
            {
                $sourceset_fields = $form->getSubFieldset('rokminievents3-sources', $sourceset, 'params');
                ob_start();
                ?>
                <div class="sourceset" id="srouceset-<?php echo $sourceset;?>">
                    <ul class="themeset">
                    <?php foreach ($sourceset_fields as $sourceset_field): ?>
                        <li>
                            <?php echo $sourceset_field->getLabel(); ?>
                            <?php echo $sourceset_field->getInput(); ?>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
                <?php
                $buffer .= ob_get_clean();
            }
            return $buffer;

        } else {
            return;
        }
    }


}