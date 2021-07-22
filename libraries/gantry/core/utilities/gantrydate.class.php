<?php
/**
 * @version        $Id: gantrydate.class.php 2389 2012-08-15 05:46:13Z btowles $
 * @author         RocketTheme http://www.rockettheme.com
 * @copyright      Copyright (C) 2007 - 2020 RocketTheme, LLC
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * derived from Joomla with original copyright and license
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is within the rest of the framework
defined('GANTRY_VERSION') or die();


class GantryDate
{
	/**
	 * Unix timestamp
	 *
	 * @var     int|boolean
	 * @access  protected
	 */
	protected  $date = false;

	/**
	 * Time offset (in seconds)
	 *
	 * @var     string
	 * @access  protected
	 */
	protected  $offset = 0;

	/**
	 * Creates a new instance of JDate representing a given date.
	 *
	 * Accepts RFC 822, ISO 8601 date formats as well as unix time stamps.
	 * If not specified, the current date and time is used.
	 *
	 * @param mixed $date     optional the date this JDate will represent.
	 * @param int   $tzOffset optional the timezone $date is from
	 */
	public function __construct($date = 'now', $tzOffset = 0)
	{
		if ($date == 'now' || empty($date)) {
			$this->date = strtotime(gmdate("M d Y H:i:s", time()));
			return;
		}

		$tzOffset *= 3600;
		if (is_numeric($date)) {
			$this->date = $date - $tzOffset;
			return;
		}

		if (preg_match('~(?:(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun),\\s+)?(\\d{1,2})\\s+([a-zA-Z]{3})\\s+(\\d{4})\\s+(\\d{2}):(\\d{2}):(\\d{2})\\s+(.*)~i', $date, $matches)) {
			$months     = Array(
				'jan' => 1,
				'feb' => 2,
				'mar' => 3,
				'apr' => 4,
				'may' => 5,
				'jun' => 6,
				'jul' => 7,
				'aug' => 8,
				'sep' => 9,
				'oct' => 10,
				'nov' => 11,
				'dec' => 12
			);
			$matches[2] = strtolower($matches[2]);
			if (!isset($months[$matches[2]])) {
				return;
			}
			$this->date = mktime($matches[4], $matches[5], $matches[6], $months[$matches[2]], $matches[1], $matches[3]);
			if ($this->date === false) {
				return;
			}

			if ($matches[7][0] == '+') {
				$tzOffset = 3600 * substr($matches[7], 1, 2) + 60 * substr($matches[7], -2);
			} elseif ($matches[7][0] == '-') {
				$tzOffset = -3600 * substr($matches[7], 1, 2) - 60 * substr($matches[7], -2);
			} else {
				if (strlen($matches[7]) == 1) {
					$oneHour = 3600;
					$ord     = ord($matches[7]);
					if ($ord < ord('M')) {
						$tzOffset = (ord('A') - $ord - 1) * $oneHour;
					} elseif ($ord >= ord('M') && $matches[7] != 'Z') {
						$tzOffset = ($ord - ord('M')) * $oneHour;
					} elseif ($matches[7] == 'Z') {
						$tzOffset = 0;
					}
				}
				switch ($matches[7]) {
					case 'UT':
					case 'GMT':
						$tzOffset = 0;
				}
			}
			$this->date -= $tzOffset;
			return;
		}
		if (preg_match('~(\\d{4})-(\\d{2})-(\\d{2})[T\s](\\d{2}):(\\d{2}):(\\d{2})(.*)~', $date, $matches)) {
			$this->date = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			if ($this->date == false) {
				return;
			}
			if (isset($matches[7][0])) {
				if ($matches[7][0] == '+' || $matches[7][0] == '-') {
					$tzOffset = 60 * (substr($matches[7], 0, 3) * 60 + substr($matches[7], -2));
				} elseif ($matches[7] == 'Z') {
					$tzOffset = 0;
				}
			}
			$this->date -= $tzOffset;
			return;
		}
		$this->date = (strtotime($date) == -1) ? false : strtotime($date);
		if ($this->date) {
			$this->date -= $tzOffset;
		}
	}

	/**
	 * Set the date offset (in hours)
	 *
	 * @access public
	 *
	 * @param float $offset The offset in hours
	 */
	public function setOffset($offset)
	{
		$this->offset = 3600 * $offset;
	}

	/**
	 * Get the date offset (in hours)
	 *
	 * @access public
	 * @return integer
	 */
	public function getOffset()
	{
		return ((float)$this->offset) / 3600.0;
	}

	/**
	 * Gets the date as an RFC 822 date.
	 *
	 * @param bool $local
	 *
	 * @return string a date in RFC 822 format
	 * @link http://www.ietf.org/rfc/rfc2822.txt?number=2822 IETF RFC 2822
	 * (replaces RFC 822)
	 */
	public function toRFC822($local = false)
	{
		$date = ($local) ? $this->date + $this->offset : $this->date;
		$date = ($this->date !== false) ? date('D, d M Y H:i:s', $date) . ' +0000' : null;
		return $date;
	}

	/**
	 * Gets the date as an ISO 8601 date.
	 *
	 * @param bool $local
	 *
	 * @return string a date in ISO 8601 (RFC 3339) format
	 * @link http://www.ietf.org/rfc/rfc3339.txt?number=3339 IETF RFC 3339
	 */
	public function toISO8601($local = false)
	{
		$date   = ($local) ? $this->date + $this->offset : $this->date;
		$offset = $this->getOffset();
		$offset = ($local && $this->offset) ? sprintf("%+03d:%02d", $offset, abs(($offset - intval($offset)) * 60)) : 'Z';
		$date   = ($this->date !== false) ? date('Y-m-d\TH:i:s', $date) . $offset : null;
		return $date;
	}

	/**
	 * Gets the date as in MySQL datetime format
	 *
	 * @param bool $local
	 *
	 * @return string a date in MySQL datetime format
	 * @link http://dev.mysql.com/doc/refman/4.1/en/datetime.html MySQL DATETIME
	 *       format
	 */
	public function toMySQL($local = false)
	{
		$date = ($local) ? $this->date + $this->offset : $this->date;
		$date = ($this->date !== false) ? date('Y-m-d H:i:s', $date) : null;
		return $date;
	}

	/**
	 * Gets the date as UNIX time stamp.
	 *
	 * @param bool $local
	 *
	 * @return string a date as a unix time stamp
	 */
	public function toUnix($local = false)
	{
		$date = null;
		if ($this->date !== false) {
			$date = ($local) ? $this->date + $this->offset : $this->date;
		}
		return $date;
	}

	/**
	 * Gets the date in a specific format
	 *
	 * Returns a string formatted according to the given format. Month and weekday names and
	 * other language dependent strings respect the current locale
	 *
	 * @param string $format  The date format specification string (see {@link PHP_MANUAL#strftime})
	 *
	 * @return string a date in a specific format
	 */
	public function toFormat($format = '%Y-%m-%d %H:%M:%S')
	{
		$date = ($this->date !== false) ? $this->gstrftime($format, $this->date + $this->offset) : null;

		return $date;
	}

	/**
	 * Translates needed strings in for JDate::toFormat (see {@link PHP_MANUAL#strftime})
	 *
	 * @access protected
	 *
	 * @param string $format The date format specification string (see {@link PHP_MANUAL#strftime})
	 * @param int    $time   Unix timestamp
	 *
	 * @return string a date in the specified format
	 */
	protected function gstrftime($format, $time)
	{
		if (strpos($format, '%a') !== false) $format = str_replace('%a', $this->dayToString(date('w', $time), true), $format);
		if (strpos($format, '%A') !== false) $format = str_replace('%A', $this->dayToString(date('w', $time)), $format);
		if (strpos($format, '%b') !== false) $format = str_replace('%b', $this->monthToString(date('n', $time), true), $format);
		if (strpos($format, '%B') !== false) $format = str_replace('%B', $this->monthToString(date('n', $time)), $format);
		if (PHP_OS == 'WINNT') {
			$format = str_replace("%h", "%b", $format);
			$format = str_replace("%e", "%#d", $format);
		}
		$date = strftime($format, $time);
		return $date;
	}

	/**
	 * Translates month number to string
	 *
	 * @access protected
	 *
	 * @param int  $month The numeric month of the year
	 * @param bool $abbr  Return the abreviated month string?
	 *
	 * @return string month string
	 */
	protected function monthToString($month, $abbr = false)
	{
		switch ($month) {
			case 1:
				return $abbr ? JText::_('JANUARY_SHORT') : JText::_('JANUARY');
			case 2:
				return $abbr ? JText::_('FEBRUARY_SHORT') : JText::_('FEBRUARY');
			case 3:
				return $abbr ? JText::_('MARCH_SHORT') : JText::_('MARCH');
			case 4:
				return $abbr ? JText::_('APRIL_SHORT') : JText::_('APRIL');
			case 5:
				return $abbr ? JText::_('MAY_SHORT') : JText::_('MAY');
			case 6:
				return $abbr ? JText::_('JUNE_SHORT') : JText::_('JUNE');
			case 7:
				return $abbr ? JText::_('JULY_SHORT') : JText::_('JULY');
			case 8:
				return $abbr ? JText::_('AUGUST_SHORT') : JText::_('AUGUST');
			case 9:
				return $abbr ? JText::_('SEPTEMBER_SHORT') : JText::_('SEPTEMBER');
			case 10:
				return $abbr ? JText::_('OCTOBER_SHORT') : JText::_('OCTOBER');
			case 11:
				return $abbr ? JText::_('NOVEMBER_SHORT') : JText::_('NOVEMBER');
			case 12:
				return $abbr ? JText::_('DECEMBER_SHORT') : JText::_('DECEMBER');
		}
		return '';
	}

	/**
	 * Translates day of week number to string
	 *
	 * @access protected
	 *
	 * @param int  $day  The numeric day of the week
	 * @param bool $abbr Return the abreviated day string?
	 *
	 * @return string day string
	 */
	protected function dayToString($day, $abbr = false)
	{
		switch ($day) {
			case 0:
				return $abbr ? JText::_('SUN') : JText::_('SUNDAY');
			case 1:
				return $abbr ? JText::_('MON') : JText::_('MONDAY');
			case 2:
				return $abbr ? JText::_('TUE') : JText::_('TUESDAY');
			case 3:
				return $abbr ? JText::_('WED') : JText::_('WEDNESDAY');
			case 4:
				return $abbr ? JText::_('THU') : JText::_('THURSDAY');
			case 5:
				return $abbr ? JText::_('FRI') : JText::_('FRIDAY');
			case 6:
				return $abbr ? JText::_('SAT') : JText::_('SATURDAY');
		}
		return '';
	}

}
