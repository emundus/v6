<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class TranslationsController extends hikashopController{
	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		$this->display[]='load';
		if(!$skip){
			$this->registerDefaultTask('load');
		}
		hikaInput::get()->set('tmpl','component');
	}
	function load(){
		hikashop_nocache();
		hikashop_cleanBuffers();
		header('X-Robots-Tag: noindex');
		$translations = (string) hikaInput::get()->getVar('translations');
		if(empty($translations)) {
			echo '{No translation keys found}';
			exit;
		}
		$translations = explode(',',$translations);
		$results = array();
		foreach($translations as $translation) {
			$results[$translation] = JText::_('HIKA_JS_'.$translation);
		}
		echo json_encode($results);
		exit;
	}
}
