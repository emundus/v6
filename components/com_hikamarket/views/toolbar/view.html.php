<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class toolbarViewtoolbar extends hikamarketView {

	protected $ctrl = 'toolbar';
	protected $icon = '';
	protected $triggerView = false;

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
		$toolbar = $this->params->get('toolbar', null);
		if(empty($toolbar))
			return false;

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$this->loadRef(array(
			'popup' => 'shop.helper.popup'
		));

		$toolbarLeft = array();
		$toolbarRight = array();
		foreach($toolbar as $tool) {
			if(isset($tool['acl']) && !$tool['acl'])
				continue;
			if(isset($tool['display']) && !$tool['display'])
				continue;

			if(!empty($tool['pos']) && $tool['pos'] === 'right')
				$toolbarRight[] = $tool;
			else
				$toolbarLeft[] = $tool;
		}

		$this->assignRef('rawdata', $toolbar);
		$this->assignRef('left', $toolbarLeft);
		$this->assignRef('right', $toolbarRight);
	}
}
