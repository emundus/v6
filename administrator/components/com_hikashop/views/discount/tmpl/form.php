<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<div id="page-discount">
<form action="<?php echo hikashop_completeLink('discount'); ?>" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">

<div class="hikashop_backend_tile_edition">
	<div class="hk-container-fluid">

<div class="hkc-lg-6 hikashop_tile_block hikashop_discount_edit_general"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('MAIN_INFORMATION'); ?></div>
	<dl class="hika_options large">

		<dt><label for="discount_code"><?php
			echo JText::_('DISCOUNT_CODE');
		?></label></dt>
		<dd class="input_large">
			<input type="text" name="data[discount][discount_code]" id="discount_code" class="inputbox" value="<?php echo $this->escape(@$this->element->discount_code); ?>" />
		</dd>

		<dt><label><?php
			echo JText::_('DISCOUNT_TYPE');
		?></label></dt>
		<dd><?php
			echo $this->type->display('data[discount][discount_type]', @$this->element->discount_type, true);
		?></dd>

		<dt><label for="discount_flat_amount"><?php
			echo JText::_('DISCOUNT_FLAT_AMOUNT');
		?></label></dt>
		<dd class="input">
			<input type="text" name="data[discount][discount_flat_amount]" id="discount_flat_amount" class="inputbox" value="<?php echo $this->escape(@$this->element->discount_flat_amount); ?>" />
			<?php echo $this->currency->display('data[discount][discount_currency_id]', @$this->element->discount_currency_id); ?>
		</dd>

		<dt><label for="discount_percent_amount"><?php
			echo JText::_('DISCOUNT_PERCENT_AMOUNT');
		?></label></dt>
		<dd>
			<input type="text" name="data[discount][discount_percent_amount]" id="discount_percent_amount" class="inputbox" value="<?php echo $this->escape(@$this->element->discount_percent_amount); ?>" />%
		</dd>
		<dt data-discount-display="coupon"><label for="discount_shipping_percent"><?php
			echo JText::_('DISCOUNT_SHIPPING_PERCENTAGE');
		?></label></dt>
		<dd data-discount-display="coupon">
<?php if(hikashop_level(1)) { ?>
			<input type="text" name="data[discount][discount_shipping_percent]" id="discount_shipping_percent" class="inputbox" value="<?php echo $this->escape(@$this->element->discount_shipping_percent); ?>" />%
<?php } else {
		echo hikashop_getUpgradeLink('essential');
}?>

		</dd>
		<dt data-discount-display="coupon"><label><?php
			echo JText::_('AUTOMATIC_TAXES');
		?></label></dt>
		<dd data-discount-display="coupon"><?php
			echo JHTML::_('hikaselect.booleanlist', 'data[discount][discount_tax]', 'onchange="hikashopToggleTax(this.value);"', @$this->element->discount_tax);
		?></dd>
		<dt data-discount-display="coupon" data-tax-display="1"><label><?php
			echo JText::_('TAXATION_CATEGORY');
		?></label></dt>
		<dd data-discount-display="coupon" data-tax-display="1"><?php
			echo $this->categoryType->display('data[discount][discount_tax_id]', @$this->element->discount_tax_id);
		?></dd>

		<dt><label for="discount_used_times"><?php
			echo JText::_('DISCOUNT_USED_TIMES');
		?></label></dt>
		<dd>
			<input type="text" name="data[discount][discount_used_times]" id="discount_used_times" class="inputbox" value="<?php echo $this->escape(@$this->element->discount_used_times); ?>" />
		</dd>

		<dt><label><?php
			echo JText::_('HIKA_PUBLISHED');
		?></label></dt>
		<dd><?php
			echo JHTML::_('hikaselect.booleanlist', 'data[discount][discount_published]', '', @$this->element->discount_published);
		?></dd>

	</dl>
</div></div>

<div class="hkc-lg-6 hikashop_tile_block hikashop_discount_edit_attributes"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('RESTRICTIONS'); ?></div>
	<dl class="hika_options large">

		<dt><label><?php
			echo JText::_('DISCOUNT_START_DATE');
		?></label></dt>
		<dd><?php
			echo JHTML::_('calendar', (@$this->element->discount_start ? hikashop_getDate(@$this->element->discount_start, '%Y-%m-%d %H:%M') : ''), 'data[discount][discount_start]', 'discount_start', hikashop_getDateFormat('%d %B %Y %H:%M'), array('size' => '20'));
		?></dd>

		<dt><label><?php
			echo JText::_('DISCOUNT_END_DATE');
		?></label></dt>
		<dd><?php
			echo JHTML::_('calendar', (@$this->element->discount_end ? hikashop_getDate(@$this->element->discount_end, '%Y-%m-%d %H:%M') : ''), 'data[discount][discount_end]', 'discount_end', hikashop_getDateFormat('%d %B %Y %H:%M'), array('size' => '20'));
		?></dd>

<?php if(!hikashop_level(1)) { ?>
		<dt><label><?php echo JText::_('HIKA_ADVANCED_RESTRICTIONS'); ?></label></dt>
		<dd><?php
			echo hikashop_getUpgradeLink('essential');
		?></dd>
<?php } ?>

<?php
	JPluginHelper::importPlugin('hikashop');
	$app = JFactory::getApplication();
	$html = array();
	$table = array();
	$app->triggerEvent('onDiscountBlocksDisplay', array(&$this->element, &$html));
	if(!empty($html)) {
		foreach($html as $h) {
			$h = trim($h);
			if(strtolower(substr($h, 0, 3)) != '<tr') {
				echo $h;
				continue;
			}
			$table[] = $h;
		}
		unset($html);
	}
?>
	</dl>
<?php
if(!empty($table)) {
?>
	<table class="admintable table" style="width:100%">
		<tbody><?php
			echo implode("\r\n", $table);
		?></tbody>
	</table>
<?php
}
?>
</div></div>
<div class="clear_both"></div>

<?php
	if(hikashop_level(1)) {
		echo $this->loadTemplate('restrictions');
	}
?>

	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->discount_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="discount" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<script type="text/javascript">
window.hikashop.ready(function(){ window.hikashop.dlTitle(); });
function hikashopToggleTax(value) {
	var elements = document.querySelectorAll("[data-tax-display]");
	for(var i = elements.length - 1; i >= 0; i--) {
		elements[i].style.display = (elements[i].getAttribute("data-tax-display") == value) ? "none" : "";
	}
}
window.hikashop.ready( function(){ hikashopToggleTax('<?php echo (int) @$this->element->discount_tax; ?>'); });
</script>
</div>
