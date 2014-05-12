<?php
/**
 * @version   $Id: include.php 6813 2013-01-28 04:28:56Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

if (!defined('RTCOMMON'))
{
    define('RTCOMMON', 'RTCOMMON');
    require_once(dirname(__FILE__) . '/RTCommon/ClassLoader.php');
} else {
    $currentLibPath = realpath(dirname(__FILE__).'/..'.'/lib');
    RTCommon_ClassLoader::addPath($currentLibPath);
}
