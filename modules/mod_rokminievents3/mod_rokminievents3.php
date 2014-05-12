<?php
/**
 * @version   $Id: mod_rokminievents3.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/


// no direct access
defined('_JEXEC') or die('Restricted access');

if (!defined('ROKMINIEVENTS3')) define('ROKMINIEVENTS3','ROKMINIEVENTS3');
if (!defined('ROKMINIEVENTS3_ROOT')) define('ROKMINIEVENTS3_ROOT', dirname(__FILE__));

require_once(ROKMINIEVENTS3_ROOT . '/lib/include.php');

$doc =JFactory::getDocument();
if ($params->get('builtin_css', 1)) $doc->addStyleSheet(JURI::Root(true).'/modules/mod_rokminievents3/tmpl/css/rokminievents3.css');
if (preg_match('/(?i)msie [2-9]/',$_SERVER['HTTP_USER_AGENT'])) $doc->addStyleSheet(JURI::Root(true).'/modules/mod_rokminievents3/tmpl/css/ie.css');

$rokminievents3 = new RokMiniEvents3();
$rokminievents3->loadScripts($params);
$events = $rokminievents3->getEvents($params);

require(JModuleHelper::getLayoutPath('mod_rokminievents3'));
