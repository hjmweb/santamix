<?php
/**
 * Uninstallation File
 * Performs some extra tasks when uninstalling the component
 *
 * @package         Advanced Module Manager
 * @version         4.14.1
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2014 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

class com_AdvancedModulesInstallerScript
{
	protected $_ext = 'advancedmodules';

	public function uninstall($adapter)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		// Delete plugin files
		$folder = JPATH_PLUGINS . '/system/' . $this->_ext;
		if (JFolder::exists($folder))
		{
			JFolder::delete($folder);
		}

		// Delete plugin language files
		$lang_folder = JPATH_ADMINISTRATOR . '/language';
		$languages = JFolder::folders($lang_folder);
		foreach ($languages as $lang)
		{
			$file = $lang_folder . '/' . $lang . '/' . $lang . '.plg_system_' . $this->_ext . '.ini';
			if (JFile::exists($file))
			{
				JFile::delete($file);
			}
			$file = $lang_folder . '/' . $lang . '/' . $lang . '.plg_system_' . $this->_ext . '.sys.ini';
			if (JFile::exists($file))
			{
				JFile::delete($file);
			}
		}
	}
}
