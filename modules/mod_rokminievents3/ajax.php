<?php
/**
 * @version   $Id: ajax.php 20134 2014-04-03 19:18:10Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2013 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

@ini_set('magic_quotes_runtime', 0);
@ini_set('zend.ze1_compatibility_mode', '0');

function get_absolute_path($path)
{
	$path      = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
	$parts     = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
	$absolutes = array();
	foreach ($parts as $part) {
		if ('.' == $part) continue;
		if ('..' == $part) {
			array_pop($absolutes);
		} else {
			$absolutes[] = $part;
		}
	}
	$prefix = '';
	if (DIRECTORY_SEPARATOR == '/') $prefix = '/';
	return $prefix . implode(DIRECTORY_SEPARATOR, $absolutes);
}

if (method_exists('JSession', 'checkToken')) {
	function check_token($method = 'post')
	{
		if ($method == 'default') {
			$method = 'request';
		}
		return JSession::checkToken($method);
	}
} else {
	function check_token($method = 'post')
	{
		return JRequest::checkToken($method);
	}
}

//we know the path from root so we remove it to get the root
define('_JEXEC', 1);
define('JPATH_BASE', get_absolute_path(dirname($_SERVER['SCRIPT_FILENAME']) . '/../..'));
define('JPATH_MYWEBAPP', dirname($_SERVER['SCRIPT_FILENAME']));

require_once(JPATH_BASE . '/includes/defines.php');
require_once(JPATH_BASE . '/includes/framework.php');
//require_once (JPATH_BASE . '/libraries/joomla/factory.php');
require_once (JPATH_LIBRARIES . '/import.php');
require_once (JPATH_LIBRARIES . '/cms.php');

// Pre-Load configuration.
ob_start();
require_once JPATH_CONFIGURATION . '/configuration.php';
ob_end_clean();

// Now that you have it, use jimport to get the specific packages your application needs.
jimport('joomla.environment.uri');
jimport('joomla.utilities.date');
jimport('joomla.utilities.utility');
jimport('joomla.event.dispatcher');
jimport('joomla.utilities.arrayhelper');

//It's an application, so let's get the application helper.
jimport('joomla.application.helper');
jimport('joomla.application.module.helper');

$app = JFactory::getApplication('site');
$app->initialise();

check_token('request') or jexit('Invalid Token');

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!defined('ROKMINIEVENTS3')) define('ROKMINIEVENTS3','ROKMINIEVENTS3');
if (!defined('ROKMINIEVENTS3_ROOT')) define('ROKMINIEVENTS3_ROOT', dirname(__FILE__));
require_once(ROKMINIEVENTS3_ROOT . '/lib/include.php');

require_once (JPATH_MYWEBAPP . '/includes/renderer.php');
$renderer = new RTRenderer();
$renderer->render();


