<?php
/**
 * @package    StripeConnect for Joomla! HikaShop
 * @version    1.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2020 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class stripeconnectmarketViewstripeconnectmarket extends HikamarketView {
	public function display($tpl = null, $params = array()) {
		$this->params =& $params;
		$fct = $this->getLayout();
		if(method_exists($this, $fct)) {
			if($this->$fct() === false)
				return;
		}
		parent::display($tpl);
	}

	public function cpanel() {
		$plugin = JPluginHelper::getPlugin('hikashoppayment', 'stripeconnect');
		$plugin_params = new JRegistry(@$plugin->params);
		unset($plugin);

		$this->stripe_client_id = $plugin_params->get('client_id', '');
		unset($plugin_params);

		$vendor = $this->params['vendor'];
		$this->stripe_account = @$vendor->vendor_params->stripe_account_id;

		$urls = parse_url(HIKASHOP_LIVE);
		if(isset($urls['path']) AND strlen($urls['path'])>0){
			$mainurl = substr(HIKASHOP_LIVE, 0, strrpos(HIKASHOP_LIVE,$urls['path'])).'/';
			$otherarguments = trim(str_replace($mainurl, '', HIKASHOP_LIVE), '/');
			if(strlen($otherarguments) > 0)
				$otherarguments .= '/';
		} else {
			$mainurl = HIKASHOP_LIVE;
		}

		$redirect_url = rtrim($mainurl, '/') . hikamarket::completeLink('stripeconnect&task=oauth', false, true);

		$stripeClass = hikamarket::get('class.plg_stripeconnect');
		$stripeAPI = $stripeClass->getStripeAPI();

		if($stripeAPI === false)
			return;

		$this->connect_url = $stripeAPI->getOAuthAuthorizeUrl(array(
	        'scope' => 'read_write',
	        'redirect_uri' => $redirect_url,
	        'state' => 'vendor.'.$vendor->vendor_id,
    	));
	}

}
