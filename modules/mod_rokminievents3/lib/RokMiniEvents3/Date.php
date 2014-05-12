<?php
/**
 * @version   $Id: Date.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('ROKMINIEVENTS3') or die('Restricted access');

class RokMiniEvents3_Date extends RTCommon_Date
{
	private $dayFormat = '%e';
	private $monthFormat = '%b';
	private $yearFormat = '%Y';
	private $timeFormat = '$I:%M%p';

	public function setFormats($day, $month, $year, $time)
	{
		$this->dayFormat   = $day;
		$this->monthFormat = $month;
		$this->yearFormat  = $year;
		$this->timeFormat  = $time;
	}

	public function getDay()
	{
		return $this->toFormat($this->dayFormat);
	}

	public function getMonth()
	{
		return $this->toFormat($this->monthFormat);
	}

	public function getYear()
	{
		return $this->toFormat($this->yearFormat);
	}

	public function getTime()
	{
		return $this->toFormat($this->timeFormat);
	}

	public function setDayFormat($dayFormat)
	{
		$this->dayFormat = $dayFormat;
	}

	public function getDayFormat()
	{
		return $this->dayFormat;
	}

	public function setMonthFormat($monthFormat)
	{
		$this->monthFormat = $monthFormat;
	}

	public function getMonthFormat()
	{
		return $this->monthFormat;
	}

	public function setTimeFormat($timeFormat)
	{
		$this->timeFormat = $timeFormat;
	}

	public function getTimeFormat()
	{
		return $this->timeFormat;
	}

	public function setYearFormat($yearFormat)
	{
		$this->yearFormat = $yearFormat;
	}

	public function getYearFormat()
	{
		return $this->yearFormat;
	}
}
