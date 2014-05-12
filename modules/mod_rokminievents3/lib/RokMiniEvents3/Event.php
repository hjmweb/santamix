<?php
/**
 * @version   $Id: Event.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKMINIEVENTS3') or die('Restricted access');

class RokMiniEvents3_Event
{
	/**
	 * @var RokMiniEvents3_Date
	 */
	private $start;

	/**
	 * @var RokMiniEvents3_Date
	 */
	private $end;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var bool
	 */
	private $allDay;

	/**
	 * @var bool
	 */
	private $noEndTime;

	/**
	 * @var string
	 */
	private $link = '#';


	public function __construct($start, $end, $title, $desciption = '', $link = '#')
	{
		$this->setStart(new RokMiniEvents3_Date($start));
		$this->setEnd(new RokMiniEvents3_Date($end));
		$this->setTitle($title);
		$this->setDescription($desciption);
		$this->setLink($link);
		//set all day if it hasn't been set
		if (!isset($this->allDay)) {
			$this->setAllDay($this->isAllDayCheck($start, $end));
		}
	}

	public function setFormats($day, $month, $year, $time)
	{
		if (isset($this->start)) $this->start->setFormats($day, $month, $year, $time);
		if (isset($this->end)) $this->end->setFormats($day, $month, $year, $time);
	}

	public function setOffset($offset)
	{
		if (isset($this->start)) $this->start->setOffset($offset);
		if (isset($this->end)) $this->end->setOffset($offset);
	}

	public function setDescription($description)
	{
		$this->description = $description;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function setEnd(RokMiniEvents3_Date $end)
	{
		$this->end = $end;
	}

	public function getEnd()
	{
		return $this->end;
	}

	public function setStart(RokMiniEvents3_Date $start)
	{
		$this->start = $start;
	}

	public function getStart()
	{
		return $this->start;
	}

	public function setTitle($title)
	{
		$this->title = $title;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function setLink($link)
	{
		$this->link = $link;
	}

	public function getLink()
	{
		return $this->link;
	}

	public function setNoEndTime($noEndTime)
	{
		$this->noEndTime = $noEndTime;
	}

	public function isNoEndTime()
	{
		return $this->noEndTime;
	}

	public function setAllDay($allDay)
	{
		$this->allDay = $allDay;
	}

	public function isAllDay()
	{
		return $this->allDay;
	}

	// Time format is UNIX timestamp or
	// PHP strtotime compatible strings
	public function isAllDayCheck($time1, $time2, $precision = 6)
	{
		//if its already true just return it
		if (isset($this->allDay)) return $this->allDay;

		//if both times are null must be all day
		if (is_null($time1) && is_null($time2)) {
			return true;
		}

		//if both times are not null but one is, can't be all day
		if (is_null($time1) || is_null($time2)) {
			return false;
		}

		// If not numeric then convert texts to unix timestamps
		if (!is_numeric($time1)) {
			$time1 = strtotime($time1);
		}
		if (!is_numeric($time2)) {
			$time2 = strtotime($time2);
		}

		//if both times are the same its all day
		if ($time1 == $time2) {
			return true;
		}

		// If time1 is bigger than time2
		// Then swap time1 and time2
		if ($time1 > $time2) {
			$ttime = $time1;
			$time1 = $time2;
			$time2 = $ttime;
		}

		$time_diff = $time2 - $time1;

		// Set up intervals and diffs arrays
		$intervals = array('year', 'month', 'day', 'hour', 'minute', 'second');
		$diffs     = array();

		// Loop thru all intervals
		foreach ($intervals as $interval) {
			// Set default diff to 0
			$diffs[$interval] = 0;
			// Create temp time from time1 and interval
			$ttime = strtotime("+1 " . $interval, $time1);
			// Loop until temp time is smaller than time2
			while ($time2 >= $ttime) {
				$time1 = $ttime;
				$diffs[$interval]++;
				// Create new temp time from time1 and interval
				$ttime = strtotime("+1 " . $interval, $time1);
			}
		}

		$count = 0;
		$times = array();
		// Loop thru all diffs
		foreach ($diffs as $interval => $value) {
			// Break if we have needed precission
			if ($count >= $precision) {
				break;
			}
			// Add value and interval
			// if value is bigger than 0
			if ($value > 0) {
				// Add s if value is not 1
				if ($value != 1) {
					$interval .= "s";
				}
				// Add value and interval to times array
				$times[] = $value . " " . $interval;
				$count++;
			}
		}

		//if times array is exactly one day must be all day
		if (count($times) == 1 && $times[0] == '1 day') {
			return true;
		}

		//if time difference is one day or within one second of a day must be all day
		if ($time_diff <= 86401 && $time_diff >= 86399) {
			return true;
		}

		//we made it this far must not be all day
		return false;
	}
}
