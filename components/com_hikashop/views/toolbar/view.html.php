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
class toolbarViewtoolbar extends hikashopView {

	public $ctrl = 'toolbar';
	public $icon = '';
	public $triggerView = false;

	public function display($tpl = null, $params = array()) {
		$this->params =& $params;
		$fct = $this->getLayout();
		if($fct == 'default')
			$fct = 'show';
		if(method_exists($this, $fct)) {
			if($this->$fct() === false)
				return;
		}
		if(isset($this->toolbar))
			unset($this->toolbar);
		parent::display($tpl);
	}

	public function show() {
		$title = $this->params->get('title', null);
		$this->assignRef('title', $title);

		$toolbar = $this->params->get('toolbar', null);
		if(empty($toolbar))
			return true;

		$config = hikashop_config();
		$this->assignRef('config', $config);

		$this->loadRef(array(
			'popupHelper' => 'helper.popup',
			'dropdownHelper'=> 'helper.dropdown',
		));

		$toBeDisplayed = array();

		foreach($toolbar as $tool) {
			if(isset($tool['acl']) && !$tool['acl'])
				continue;
			if(isset($tool['display']) && !$tool['display'])
				continue;

			$toBeDisplayed[] = $tool;
		}


		$this->assignRef('data', $toBeDisplayed);


	}
}
