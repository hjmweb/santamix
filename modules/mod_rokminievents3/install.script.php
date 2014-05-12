<?php
/**
 * @version   $Id: install.script.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class Mod_RokMiniEvents3InstallerScript
{

    public function install($parent)
    {
        if ($this->checkForExtender())
            $this->registerExtenderPlugin($parent);
        return true;
    }

    public function update($parent)
    {
	    $old_lang_file = JPATH_SITE.'/language/en-GB/en-GB.mod_rokminievents3.ini';
	    if (@file_exists($old_lang_file) && is_writable($old_lang_file))
	    {
		    @unlink($old_lang_file);
	    }
        if ($this->checkForExtender())
            $this->registerExtenderPlugin($parent);
        return true;
    }

    public function uninstall($parent)
    {
        if ($this->checkForExtender())
            $this->unregisterExtenderPlugin($parent);
    }

    public function preflight($type, $parent)
    {

    }

    /**
     * @return bool
     */
    protected function checkForExtender()
    {
        // if the class exists and is loaded just return
        if (class_exists('plgSystemRokExtender')) return true;
        $plugin_path = JPATH_ROOT . '/plugins/system/rokextender/rokextender.php';

        // if the plugin isnt installed
        if (!file_exists($plugin_path) || !is_file($plugin_path)) return false;

        require_once($plugin_path);

        if (!class_exists('plgSystemRokExtender'))
        {
            //TODO: add error message output
            return false;
        }
        return true;
    }

    protected function registerExtenderPlugin($parent)
    {
        $manifest = $parent->getParent()->getManifest();
        $basepath = str_replace(JPATH_ROOT, '', $parent->getParent()->getPath('extension_root'));

        foreach ($manifest->plugins->plugin as $plugin)
        {
            plgSystemRokExtender::registerExtenderPlugin($basepath . (string)$plugin);
        }
    }

    protected function unregisterExtenderPlugin($parent)
    {
        $manifest = $parent->getParent()->getManifest();
        $basepath = str_replace(JPATH_ROOT, '', $parent->getParent()->getPath('extension_root'));

        foreach ($manifest->plugins->plugin as $plugin)
        {
            plgSystemRokExtender::registerExtenderPlugin($basepath . (string)$plugin);
        }
    }
}