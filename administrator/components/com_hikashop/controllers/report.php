<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class ReportController extends hikashopController{
	var $type='widget';
	var $pkey = 'widget_id';
	var $table = 'widget';
	var $orderingMap ='widget_ordering';

	function __construct($config = array())
	{
		$this->modify[]='apply_table';
		parent::__construct($config);
		$this->modify_views[]='tableform';
		$this->display[]='csv';
		$this->display[]='edit';
	}

	function tableform(){
		hikaInput::get()->set( 'layout', 'tableform'  );
		return $this->display();
	}

	function csv(){
		hikaInput::get()->set( 'layout', 'csv'  );
		return $this->display();
	}

	function apply_table(){
		if($this->store()){
			echo '<html><head><script type="text/javascript">parent.window.location.href=\''.hikashop_completeLink('report&task=edit&cid[]='.hikashop_getCID('widget_id'), false, true).'\';</script></head><body></body></html>';
			exit;
		}
	}

	function save(){
		$dashboard = hikaInput::get()->getVar('dashboard');
		if($dashboard){
			$this->store();
			$this->setRedirect(hikashop_completeLink('dashboard', false, true));
		}else{
			$this->store();
			return $this->listing();
		}
	}

	function cancel(){
		$dashboard = hikaInput::get()->getVar('dashboard');
		if($dashboard){
			$this->setRedirect(hikashop_completeLink('dashboard', false, true));
		}else{
			$this->setRedirect(hikashop_completeLink('report', false, true));
		}
	}
}
