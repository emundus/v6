<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopSpreadsheetHelper {
	var $format;
	var $filename;
	var $separator;
	var $decimal_separator;
	var $currLine;
	var $buffer;
	var $forceQuote;
	var $forceText;
	var $progressive;
	var $headerSent;
	var $excelSecurity;

	function __construct() {
		$this->init();
	}

	function init($format = 'csv', $filename = 'export', $sep = ';', $forceQuote = false, $decimal_separator = '.', $forceText = false) {
		$this->currLine = -1;
		$this->buffer = '';
		$this->separator = ';';
		$this->filename = $filename;
		$this->forceQuote = $forceQuote;
		$this->forceText = $forceText;
		$this->progressive = false;
		$this->headerSent = false;
		$this->excelSecurity = "'";

		switch( strtolower($format) ) {
			case 'xls':
				$this->format = 1;
				$this->buffer .= pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
				break;

			default:
			case 'csv':
				$this->format = 0;
				$this->separator = $sep;
				$this->decimal_separator = $decimal_separator;
				$this->buffer .= chr(239) . chr(187) . chr(191);
				break;
		}

		if( empty($this->filename) )
			$this->filename = 'export';

		if( $this->format == 1 )
			$this->filename .= '.xls';
		else
			$this->filename .= '.csv';
	}

	function send() {
		if(!$this->headerSent) {
			if( $this->format == 1 )
				$this->buffer .= pack("ss", 0x0A, 0x00);

			header('Pragma: public');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Type: application/force-download');
			header('Content-Type: application/octet-stream');
			header('Content-Type: application/download');
			header('Content-Disposition: attachment;filename='.$this->filename.' ');
			header('Content-Transfer-Encoding: binary ');
			if(!$this->progressive) {
				header('Content-Length: '.strlen($this->buffer));
			}
			$this->headerSent = true;
		}

		echo $this->buffer;
		$this->buffer = '';

		if(!$this->progressive)
			exit;
	}

	function flush() {
		if($this->progressive){
			if(!$this->headerSent) {
				$this->send();
			} else {
				echo $this->buffer;
				$this->buffer = '';
			}
		}
	}

	function get() {
		if( $this->format == 1 )
			$this->buffer .= pack('vv',0x000A,0x0000);

		$ret = $this->buffer;
		$this->buffer = '';

		return $ret;
	}

	function writeNumber($row, $col, $value, $lastOne) {
		if( $this->format == 1 ) {
			$this->currLine = $row;
			$this->buffer .= pack("sssss", 0x203, 14, $row, $col, 0x0);
			$this->buffer .= pack("d", $value);
		} else {
			if( $this->currLine < $row )
				$this->newLine();
			$this->currLine = $row;

			$floatValue = (float)hikashop_toFloat($value);
			if($floatValue == (int)$floatValue)
				$this->buffer .= (int)$value;
			else
				$this->buffer .= rtrim(number_format($floatValue, 5, $this->decimal_separator, ''), '0,.');

			if(!$lastOne)
				$this->buffer .= $this->separator;
		}
	}

	function writeText($row, $col, $value, $lastOne) {
		if( empty($value) || is_array($value) || is_object($value)) {
			$value = '';
		}
		if( $this->format == 1 ) {
			$this->currLine = $row;
			$len = strlen($value);
			$this->buffer .= pack("ssssss", 0x204, 8 + $len, $row, $col, 0x0, $len);
			$this->buffer .= $value;
		} else {
			if( $this->currLine < $row )
				$this->newLine();
			$this->currLine = $row;

			if(!empty($value) && !empty($this->excelSecurity) && in_array(@substr($value, 0, 1), array('=','+','-','@')))
				$value = "'".$value;

			if( strpos($value, '"') !== false) {
				$value = '"' . str_replace('"','""',$value) . '"';
			} elseif( $this->forceQuote || (strpos($value, $this->separator) !== false) || (strpos($value, "\n") !== false) || (trim($value) != $value) ) {
				$value = '"' . $value . '"';
			}
			$this->buffer .= $value;
			if(!$lastOne)
				$this->buffer .= $this->separator;
		}
	}

	function newLine() {
		if( $this->format == 0 ) {
			$this->buffer .= "\r\n";
		}
	}

	function writeLine($data) {
		$i = 0;
		$this->currLine++;
		if( $this->currLine > 0 )
			$this->newLine();
		end($data);
		$last = key($data);
		reset($data);
		foreach($data as $k => $value) {
			$lastOne = false;
			if ($last===$k)
				$lastOne = true;
			if(is_array($value))
				continue;

			if(
				!$this->forceText &&
				is_numeric($value) &&
				(preg_match('[^0-9]',$value) || ltrim($value, '0') === (string)$value || '0' === (string)$value || '0.00000' === (string)$value)
			) {
				$this->writeNumber($this->currLine, $i++, $value, $lastOne);
			} else {
				$this->writeText($this->currLine, $i++, $value, $lastOne);
			}
		}
		if($this->progressive) {
			$this->flush();
		}
	}
}
