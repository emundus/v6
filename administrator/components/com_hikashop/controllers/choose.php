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
class ChooseController extends hikashopController{
	function __construct(){
		parent::__construct();
		$this->display[]='searchfields';
		$this->display[]='filters';
	}
	function searchfields(){
		hikaInput::get()->set( 'layout', 'searchfields'  );
		return parent::display();
	}
	function filters(){
		hikaInput::get()->set( 'layout', 'filters'  );
		return parent::display();
	}
}
