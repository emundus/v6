<?php
/**
* @version   $Id: color.php 26100 2015-01-27 14:16:12Z james $
* @author    RocketTheme http://www.rockettheme.com
* @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*
* Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
*
*/
class Color {

	function Color($hex){
		$this->color = $hex;
	}

	function lib_lighten($args) {
		list($color, $delta) = $this->colorArgs($args);

		$hsl = $this->toHSL($color);
		$hsl[3] = $this->clamp($hsl[3] + $delta, 100);
		return $this->toRGB($hsl);
	}

	function lib_darken($args) {
		list($color, $delta) = $this->colorArgs($args);

		$hsl = $this->toHSL($color);
		$hsl[3] = $this->clamp($hsl[3] - $delta, 100);
		return $this->toRGB($hsl);
	}

	function colorArgs($args) {
		if ($args[0] != 'list' || count($args[2]) < 2) {
			return array(array('color', 0, 0, 0));
		}
		list($color, $delta) = $args[2];
		if ($color[0] != 'color')
			$color = array('color', 0, 0, 0);

		$delta = floatval($delta[1]);

		return array($color, $delta);
	}

	function toHSL($color) {
		if ($color[0] == 'hsl') return $color;

		$r = $color[1] / 255;
		$g = $color[2] / 255;
		$b = $color[3] / 255;

		$min = min($r, $g, $b);
		$max = max($r, $g, $b);

		$L = ($min + $max) / 2;
		if ($min == $max) {
			$S = $H = 0;
		} else {
			if ($L < 0.5)
				$S = ($max - $min)/($max + $min);
			else
				$S = ($max - $min)/(2.0 - $max - $min);

			if ($r == $max) $H = ($g - $b)/($max - $min);
			elseif ($g == $max) $H = 2.0 + ($b - $r)/($max - $min);
			elseif ($b == $max) $H = 4.0 + ($r - $g)/($max - $min);

		}

		$out = array('hsl',
			($H < 0 ? $H + 6 : $H)*60,
			$S*100,
			$L*100,
		);

		if (count($color) > 4) $out[] = $color[4]; // copy alpha
		return $out;
	}

	function toRGB_helper($comp, $temp1, $temp2) {
		if ($comp < 0) $comp += 1.0;
		elseif ($comp > 1) $comp -= 1.0;

		if (6 * $comp < 1) return $temp1 + ($temp2 - $temp1) * 6 * $comp;
		if (2 * $comp < 1) return $temp2;
		if (3 * $comp < 2) return $temp1 + ($temp2 - $temp1)*((2/3) - $comp) * 6;

		return $temp1;
	}

	function toRGB($color) {
		if ($color == 'color') return $color;

		$H = $color[1] / 360;
		$S = $color[2] / 100;
		$L = $color[3] / 100;

		if ($S == 0) {
			$r = $g = $b = $L;
		} else {
			$temp2 = $L < 0.5 ?
				$L*(1.0 + $S) :
				$L + $S - $L * $S;

			$temp1 = 2.0 * $L - $temp2;

			$r = $this->toRGB_helper($H + 1/3, $temp1, $temp2);
			$g = $this->toRGB_helper($H, $temp1, $temp2);
			$b = $this->toRGB_helper($H - 1/3, $temp1, $temp2);
		}

		$out = array('color', round($r*255), round($g*255), round($b*255));
		if (count($color) > 4) $out[] = $color[4]; // copy alpha
		return $out;
	}

	function clamp($v, $max = 1, $min = 0) {
		return min($max, max($min, $v));
	}

	function rgbaToHex($color) {
		if ($color[0] != 'color')
			throw new exception("color expected for rgbahex");

		return sprintf("#%02x%02x%02x",
			$color[1],$color[2], $color[3]);
	}

	function hex2RGB($hexStr, $returnAsString = false, $seperator = ',') {
	    $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
	    $rgbArray = array();
	    $rgbArray[] = 'color';
	    if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
	        $colorVal = hexdec($hexStr);
	        $rgbArray[] = 0xFF & ($colorVal >> 0x10);
	        $rgbArray[] = 0xFF & ($colorVal >> 0x8);
	        $rgbArray[] = 0xFF & $colorVal;
	    } elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
	        $rgbArray[] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
	        $rgbArray[] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
	        $rgbArray[] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
	    } else {
	        return false; //Invalid hex color code
	    }

	    return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
	}

	function lighten($pc){
		$pc = str_replace('%', '', $pc);
		$args = array('list', ',', array($this->hex2RGB($this->color), array('%', $pc)));
		$rgb = array_slice($this->lib_lighten($args), 1);

		return $this->rgbaToHex($this->lib_lighten($args));
	}

	function darken($pc){
		$pc = str_replace('%', '', $pc);
		$args = array('list', ',', array($this->hex2RGB($this->color), array('%', $pc)));
		$rgb = array_slice($this->lib_darken($args), 1);

		return $this->rgbaToHex($this->lib_darken($args));
	}
}