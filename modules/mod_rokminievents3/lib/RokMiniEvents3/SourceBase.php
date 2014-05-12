<?php
/**
 * @version   $Id: SourceBase.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKMINIEVENTS3') or die('Restricted access');

abstract class RokMiniEvents3_SourceBase implements RokMiniEvents3_Source
{
	protected static function getTime($params, $date)
	{
		$display = $params->get('timedisplay', 24);

		if ($display == '24') return date('H:i', $date); else return date('h:iA', $date);
	}
}
