<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2013 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPFullcalendar
{

	public static function convertFromPHPDate ($format)
	{
		// Php date to fullcalendar date conversion
		$dateFormat = array(
				'd' => 'dd',
				'D' => 'ddd',
				'j' => 'd',
				'l' => 'dddd',
				'N' => '',
				'w' => '',
				'z' => '',
				'W' => '',
				'S' => 'S',
				'F' => 'MMMM',
				'm' => 'MM',
				'M' => 'MMM',
				'n' => 'M',
				't' => '',
				'L' => '',
				'o' => 'yyyy',
				'Y' => 'yyyy',
				'y' => 'yy',
				'a' => 'tt',
				'A' => 'TT',
				'B' => '',
				'g' => 'h',
				'G' => 'H',
				'h' => 'hh',
				'H' => 'HH',
				'i' => 'mm',
				's' => 'ss',
				'u' => '',
				'e' => '',
				'I' => '',
				'O' => '',
				'P' => '',
				'T' => '',
				'Z' => '',
				'c' => 'u',
				'r' => '',
				'U' => ''
		);

		$newFormat = "";
		$isText = false;
		$i = 0;
		while ($i < strlen($format))
		{
			$chr = $format[$i];
			if ($chr == '"' || $chr == "'")
			{
				$isText = ! $isText;
			}
			$replaced = false;
			if ($isText == false)
			{
				foreach ($dateFormat as $zl => $jql)
				{
					if (substr($format, $i, strlen($zl)) == $zl)
					{
						$chr = $jql;
						$i += strlen($zl);
						$replaced = true;
						break;
					}
				}
			}
			if ($replaced == false)
			{
				$i ++;
			}
			$newFormat .= $chr;
		}

		return $newFormat;
	}
}
