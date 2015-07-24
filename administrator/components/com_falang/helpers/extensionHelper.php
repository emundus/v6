<?php



defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * @package falang
 * @since	1.0
 */
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
