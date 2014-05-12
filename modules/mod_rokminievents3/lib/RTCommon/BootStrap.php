<?php
/**
 * @version   $Id: BootStrap.php 6813 2013-01-28 04:28:56Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('RTCOMMON') or die('Restricted access');

class RTCommon_BootStrap
{
    private $_fileExtension = ".php";
    public function loadClass($className)
    {
        $commonsPath = realpath(dirname(__FILE__) . '/..');
        $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className) . $this->_fileExtension;
        $full_file_path = $commonsPath . DIRECTORY_SEPARATOR . $fileName;
        if (file_exists($full_file_path) && is_readable($full_file_path))
            require($full_file_path);
    }
}
