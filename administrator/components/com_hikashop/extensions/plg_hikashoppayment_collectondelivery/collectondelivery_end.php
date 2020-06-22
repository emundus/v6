<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_collectondelivery_end" id="hikashop_collectondelivery_end">
	<span class="hikashop_collectondelivery_end_message" id="hikashop_collectondelivery_end_message">
		<?php
		echo JText::_('ORDER_IS_COMPLETE').'<br/>'.
		JText::sprintf('AMOUNT_COLLECTED_ON_DELIVERY',$this->amount,$this->order_number).'<br/>';
		if(!empty($this->url))
			echo JText::sprintf('YOU_CAN_NOW_ACCESS_YOUR_ORDER_HERE', $this->url).'<br/>';
		echo JText::_('THANK_YOU_FOR_PURCHASE');?>
	</span>
</div>
<?php
if(!empty($this->payment_params->return_url)){
	$doc = JFactory::getDocument();
	$doc->addScriptDeclaration("window.hikashop.ready( function() {window.location='".$this->payment_params->return_url."'});");
}
