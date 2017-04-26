<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_<?php echo $this->type; ?>_address_selection"<?php if(empty($this->addresses)) { echo ' style="display:none;"'; } ?>>
<?php
if($this->address_selector == 1) {
	if(!empty($this->addresses)) {
		foreach($this->addresses as $address) {
			$checked = ($address->address_default == 1) ? ' checked="checked"' : '';
?>
<div id="hikashop_address_<?php echo $this->type; ?>_selection_<?php echo $address->address_id; ?>" class="address_selection<?php echo ($address->address_default == 1) ? ' address_selected':''; ?>">
	<input id="hikashop_<?php echo $this->type; ?>_address_radio_<?php echo $address->address_id;?>" onclick="window.localPage.selectAddr(this, '<?php echo $this->type; ?>');" class="checkout_<?php echo $this->type; ?>_address_radio" type="radio" name="hikashop_address_<?php echo $this->type; ?>" value="<?php echo $address->address_id;?>"<?php echo $checked; ?>/>
<?php
			$this->address_id = (int)$address->address_id;
			$this->address = $address;
			$this->setLayout('show');
			echo $this->loadTemplate();
?>
</div>
<?php
		}
	}
?>
	<div id="hikashop_<?php echo $this->type; ?>_address_template" class="address_selection" style="display:none;">
		<input id="hikashop_<?php echo $this->type; ?>_address_radio_{VALUE}" class="checkout_<?php echo $this->type; ?>_address_radio" type="radio" name="hikashop_address" value="{VALUE}"/>
		{CONTENT}
	</div>
<?php if(!empty($this->show_new_btn)) { ?>
	<div class="" style="margin-top:6px;">
		<a class="btn btn-success" href="#newAddress" onclick="return window.localPage.newAddr(this, '<?php echo $this->type; ?>');"><?php echo JText::_('HIKA_NEW'); ?></a>
	</div>
<?php }
}

if($this->address_selector == 2) {
	$current = 0;
	$values = array();
	if(!empty($this->addresses)) {
		$addressClass = hikashop_get('class.address');
		foreach($this->addresses as $k => $address) {
			$addr = $addressClass->miniFormat($address);
			$values[] = JHTML::_('select.option', $k, $addr);

			if($address->address_default == 1)
				$current = $k;
		}
	}
	$values[] = JHTML::_('select.option', 0, JText::_('HIKASHOP_NEW_ADDRESS_ITEM'));
	echo JHTML::_('select.genericlist', $values, 'hikashop_address_'.$this->type, 'class="hikashop_field_dropdown" onchange="window.localPage.selectAddr(this, \''.$this->type.'\');"', 'value', 'text', $current, 'hikashop_address_'.$this->type.'_selector');
?><div id="hikashop_selected_<?php echo $type; ?>_address">
<?php
	if(isset($this->addresses[$current]))
		$address = $this->addresses[$current];
	else
		$address = reset($this->addresses);

	$this->address_id = (int)$address->address_id;
	$this->address = $address;
	$this->setLayout('show');
	echo $this->loadTemplate();
?>
</div>
<?php
}
?>
</div>
<div id="hikashop_<?php echo $this->type; ?>_address_zone">
<?php
if(empty($this->addresses)) {
	$this->address_id = 0;
	$this->edit = true;
	$this->address = null;
	$this->setLayout('show');
	echo $this->loadTemplate();
}
?>
</div>

<?php
static $hikashop_address_select_once = false;
if(!$hikashop_address_select_once) {
	$hikashop_address_select_once = true;
?>
<script type="text/javascript">
if(!window.addressMgr) window.addressMgr = {};

<?php ?>
</script>
<?php
}
