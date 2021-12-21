<?php
/**
 * @package    StripeConnect for Joomla! HikaShop
 * @version    1.0.6
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2020 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
?>
<div class="hikashop_stripeconnect_thankyou" id="hikashop_stripeconnect_thankyou">
	<span id="hikashop_stripeconnect_thankyou_message" class="hikashop_stripeconnect_thankyou_message">
<?php
	echo JText::_('THANK_YOU_FOR_PURCHASE');
	if(!empty($this->payment_params->return_url)) {
		echo '<br/><a href="'.$this->escape($this->payment_params->return_url).'">'.JText::_('GO_BACK_TO_SHOP').'</a>';
	}
?>
	</span>
</div>
<?php
if(!empty($this->payment_params->return_url)) {
	$doc = JFactory::getDocument();
	$doc->addScriptDeclaration('window.hikashop.ready(function(){ window.location="'.$this->escape($this->payment_params->return_url).'"});');
}
