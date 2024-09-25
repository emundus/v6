<?php
/**
 * @package	eMundus for Joomla!
 * @version	1.39.1
 * @author	emundus.fr
 * @copyright	(C) 2024 eMundus All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die('Restricted access');
?><?php
jimport('joomla.plugin.plugin');
class plgSystemEmundus extends JPlugin {

	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	public function onBeforeCompileHead(){

		if(version_compare(JVERSION,'3.7','<'))
			return;

		$app = Factory::getApplication();
		if($app->isClient('administrator')) {
			if(empty($_REQUEST['option']) || $_REQUEST['option'] != 'com_hikashop')
				return;
		}

		$doc = Factory::getDocument();
		$head = $doc->getHeadData();

		if(empty($head['scripts']))
			return;

		$js_files = array('jquery.js', 'jquery.min.js', 'jquery-noconflict.js', 'jquery.ui.core.js', 'jquery.ui.core.min.js');
		$newScripts = array();
		foreach($head['scripts'] as $file => $data) {
			foreach($js_files as $js_file) {
				if(strpos($file,'media/jui/js/'.$js_file)=== false)
					continue;
				$newScripts[$file] = $data;
			}
		}
		foreach($head['scripts'] as $file => $data){
			if(!isset($newScripts[$file]))
				$newScripts[$file] = $data;
		}
		$head['scripts'] = $newScripts;

		$doc->setHeadData($head);
	}
}
