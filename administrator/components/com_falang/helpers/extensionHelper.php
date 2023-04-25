<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

class  FalangExtensionHelper  {
	
	private static $imagePath;
	
	/**
	 * @return	true if the FaLang extension is correctly installed, configured and activated
	 */
	public static function isFalangActive() {
		$db = JFactory::getDBO();
		if (!is_a($db,"JFalangDatabase")){
			return false;
		}
		return true;
	}
	
	/**
	 * The method cleans the internal image path in order to force a re-check
	 * of images.
	 * @return void
	 */
	public static function cleanImagePathCache() {
		self::$imagePath = null;
	}
	

}
