<?php
/**
* @version $Id: class.jumicoder.php 92 2009-02-15 17:08:02Z martin2hajek $
* @package Joomla! 1.5.x, Jumi editors-xtd plugin
* @copyright (c) 2009 Martin Hajek
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*
*/

class JumiCoder
{
	public static function encode($source)
	{
		return htmlspecialchars(stripslashes($source), ENT_NOQUOTES);
	}
	
	public static function decode($source,$strip)
	{ //$strip==1 then stripslashes: For Jumicoder only. For code parsing do not do that.
		if (!function_exists("htmlspecialchars_decode")) {
			return ($strip == 1) ? strtr(stripslashes($source), array_flip(get_html_translation_table(HTML_SPECIALCHARS, ENT_NOQUOTES))) : strtr($source, array_flip(get_html_translation_table(HTML_SPECIALCHARS, ENT_NOQUOTES))); //for PHP 4
		} else {
    	return ($strip == 1) ? htmlspecialchars_decode(stripslashes($source), ENT_NOQUOTES) : htmlspecialchars_decode($source, ENT_NOQUOTES);
    }
	}
	
	public static function viewEntities($source)
	{ //just replaces & to &amp; so that &xxx; is seen by a browser as an entity
		return str_replace('&', '&amp;', $source);
	}
	
	public static function cleanRubbish($source)
	{ // cleans from the $source (encoded code in an article) possible rubbish brought by wysiwyg
		$cleaningTab = array(	"<br>" => "\n", "<br />" => "\n",	"<p>" => "\n", "</p>" => "\n", "&nbsp;" => " ", "&#160;" => " ", chr(hexdec('C2')).chr(hexdec('A0')) => '');
		foreach ($cleaningTab as $key => $value) {
    	$source = str_replace($key, $value, $source);
		}
		return $source;
	}	
}

?>
