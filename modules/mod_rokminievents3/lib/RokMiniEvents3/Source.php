<?php
/**
 * @version   $Id: Source.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKMINIEVENTS3') or die('Restricted access');

/**
 *
 */
interface RokMiniEvents3_Source
{
	/**
	 * @abstract
	 *
	 * @param $params
	 */
	function getEvents(&$params);

	/**
	 * Checks to see if the source is available to be used
	 * @abstract
	 * @return bool
	 */
	function available();
}
