<?php
/**
 * @version   $Id: RokMiniEvents3Plugin.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 */
class RokMiniEvents3Plugin extends JPlugin
{

    public static $ROKMINIEVENTS3_ROOT;
    public static $SOURCE_DIR;

    public function __construct($parent = null)
    {
        if (!defined('ROKMINIEVENTS3')) define('ROKMINIEVENTS3', 'ROKMINIEVENTS3');

        // Set base dirs
        self::$ROKMINIEVENTS3_ROOT = JPATH_ROOT . '/modules/mod_rokminievents3';
        self::$SOURCE_DIR = self::$ROKMINIEVENTS3_ROOT . '/lib/RokMiniEvents3/Source';

        //load up the RTCommon
        require_once(self::$ROKMINIEVENTS3_ROOT . '/lib/include.php');

        parent::__construct($parent);
    }

    public function onContentPrepareForm($form, $data)
    {
        $app = JFactory::getApplication();
        if (!$app->isAdmin()) return;

        $option = JFactory::getApplication()->input->getWord('option');
        $layout = JFactory::getApplication()->input->getWord('layout');
        $view = JFactory::getApplication()->input->getWord('view');
        $task = JFactory::getApplication()->input->getWord('task');
        $id = JFactory::getApplication()->input->getInt('id');

        $module = $this->getModuleType($data);


        if (in_array($option, array('com_modules', 'com_advancedmodules')) && $layout == 'edit' && $module == 'mod_rokminievents3')
        {

            JForm::addFieldPath(JPATH_ROOT . '/modules/mod_rokminievents3/fields');

            //Find Sources
            $sources = RokMiniEvents3_SourceLoader::getAvailableSources(self::$SOURCE_DIR);

            foreach ($sources as $source_name => $source)
            {
                if (file_exists($source->paramspath) && is_readable($source->paramspath))
                {
                    $form->loadFile($source->paramspath, false);
                    JForm::addFieldPath( dirname($source->paramspath) . "/" . $source->name );
                    //$this->element_dirs[] = dirname($source->paramspath) . "/" . $source->name;
                    $language = JFactory::getLanguage();
                    $language->load('com_'.$source->name, JPATH_ADMINISTRATOR);
                    $language->load($source->name, dirname($source->paramspath), $language->getTag(), true);
                }
            }

            $subfieldform = RokSubfieldForm::getInstanceFromForm($form);

            if (!empty($data) && isset($data->params)) $subfieldform->setOriginalParams($data->params);

            if ($task == 'save' || $task == 'apply')
            {
                $subfieldform->makeSubfieldsVisable();
            }
        }
    }

    protected function getModuleType(&$data)
    {
        if (is_array($data) && isset($data['module']))
        {
            return $data['module'];
        }
        elseif (is_array($data) && empty($data))
        {
            $form = JRequest::getVar('jform');
            if (is_array($form) && array_key_exists('module',$form))
            {
                return $form['module'];
            }
        }
        if (is_object($data) && method_exists( $data , 'get'))
        {
            return $data->get('module');
        }
        return '';
    }
}

