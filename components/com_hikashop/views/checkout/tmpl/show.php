<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo $this->checkoutHelper->completeLink('cid='. $this->step, false, false, false, $this->itemid); ?>" method="post" id="hikashop_checkout_form" name="hikashop_checkout_form" enctype="multipart/form-data" onsubmit="if(window.checkout.onFormSubmit){ return window.checkout.onFormSubmit(this); }">
<input type="hidden" name="task" value="submitstep"/>
<input type="hidden" name="<?php echo hikashop_getFormToken(); ?>" id="hikashop_checkout_token" value="1"/>
<input type="hidden" name="cart_id" value="<?php echo $this->cart_id; ?>"/>
<input type="submit" style="display:none;"/>
<div id="hikashop_checkout" data-checkout-step="<?php echo $this->step; ?>" class="hikashop_checkout_page hikashop_checkout_page_step<?php echo $this->step; ?>">
	<div class="hikashop_checkout_loading_elem"></div>
	<div class="hikashop_checkout_loading_spinner"></div>
<?php

if((int)$this->config->get('display_checkout_bar', 2) > 0) {
	echo $this->displayBlock('bar', 0, array(
		'display_end' => ((int)$this->config->get('display_checkout_bar', 2) == 1)
	));
}
if($this->hasSeparator)
	echo $this->displayBlock('separator', 0, array('type' => 'start'));
$handleEnter = array();
$last = 0;
if(!empty($this->extraData['checkout']) && !empty($this->extraData['checkout']->checkout_top)) { echo implode("\r\n", $this->extraData['checkout']->checkout_top); }

foreach($this->workflow['steps'][$this->workflow_step]['content'] as $k => $content) {
	$handleEnter[] = 'window.checkout.handleEnter(\''.$content['task'].'\','.$this->step.','.$k.');';
	echo $this->displayBlock($content['task'], $k, @$content['params']);
	$last = $k;
}

if(!empty($this->extraData['checkout']) && !empty($this->extraData['checkout']->checkout_bottom)) { echo implode("\r\n", $this->extraData['checkout']->checkout_bottom); }

if($this->hasSeparator)
	echo $this->displayBlock('separator', $last+1, array('type' => 'end'));

echo $this->displayBlock('buttons', 0, array());

if(!empty($this->extra_data))
	echo implode("\r\n", $this->extra_data);

$doc = JFactory::getDocument();
$doc->addScript(HIKASHOP_JS.'checkout.js');
$js = '
window.checkout.token = "'.hikashop_getFormToken().'";
window.checkout.urls.show = "'.hikashop_completeLink('checkout&task=showblock'.$this->cartIdParam.'&Itemid='.$this->itemid, 'ajax', false, true).'";
window.checkout.urls.submit = "'.hikashop_completeLink('checkout&task=submitblock'.$this->cartIdParam.'&Itemid='.$this->itemid, 'ajax', false, true).'";
window.checkout.urls.submitstep = "'.hikashop_completeLink('checkout&task=submitstep'.$this->cartIdParam.'&Itemid='.$this->itemid, 'ajax', false, true).'";
window.Oby.registerAjax("checkout.step.completed",function(params){ document.getElementById("hikashop_checkout_form").submit(); });
window.Oby.registerAjax("cart.empty",function(params){ setTimeout(function(){ window.location.reload(); },150); });
window.Oby.registerAjax("cart.updated",function(params){ if(!params || !params.resp || !params.resp.empty) return; window.Oby.fireAjax("cart.empty",null); });
window.hikashop.ready(function(){
	'.implode("\r\n\t", $handleEnter).'
});
';
$doc->addScriptDeclaration($js);
?>
</div>
</form>
