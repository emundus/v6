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
$form_key = empty($this->editing_variant) ? 'price' : 'variantprice';

if(empty($this->price->edit)) {
?>
	<td style="white-space: nowrap; cursor:pointer;" onclick="window.productMgr.editPrice(this, '<?php echo $form_key; ?>', <?php echo (int)$this->price_num; ?>,<?php echo (int)@$this->price->price_id; ?>, true); return false;"><?php
		if(!$this->shopConfig->get('floating_tax_prices', 0)) {
			echo $this->currencyClass->format($this->price->price_value, $this->price->price_currency_id). ' / ';
		}
		echo $this->currencyClass->format($this->price->price_value_with_tax, $this->price->price_currency_id);
	?></td>
	<td style="cursor:pointer;" onclick="window.productMgr.editPrice(this, '<?php echo $form_key; ?>', <?php echo (int)$this->price_num; ?>,<?php echo (int)$this->price->price_id; ?>, true); return false;">
<?php
	$restrictions = array();
	$qty = max((int)$this->price->price_min_quantity, 1);
	if($qty > 1 && $this->price_acls['quantity'])
		$restrictions[] = '<strong>'.JText::_('MINIMUM_QUANTITY').'</strong>: '.$qty;

	if($this->price_acls['user'] && !empty($this->price->price_users) && hikashop_level(2)) {
		$users = explode(',',trim($this->price->price_users, ','));
		$text = array();
		foreach($users as $user) {
			if(empty($user))
				continue;
			$data = $this->userClass->get($user);
			if($data)
				$text[] = $data->name;
		}
		$restrictions[] = '<strong>'.JText::_('USERS').'</strong>: '.implode(', ', $text);
	}

	if($this->price_acls['acl'] && isset($this->price->price_access) && $this->price->price_access != 'all' && hikashop_level(2)) {
		$groups = $this->joomlaAcl->getList();
		$access = explode(',', $this->price->price_access);
		$text = array();
		foreach($access as $a) {
			if(empty($a))
				continue;
			foreach($groups as $group) {
				if($group->id == $a) {
					$text[] = $group->text;
					break;
				}
			}
		}
		$restrictions[] = '<strong>'.JText::_('ACCESS_LEVEL').'</strong>: '.implode(', ', $text);
	}

	if($this->price_acls['date'] && (!empty($this->price->price_start_date) || !empty($this->price->price_end_date))) {
		if(!empty($this->price->price_start_date)) {
			$this->price->price_start_date = hikamarket::getDate($this->price->price_start_date, '%d %B %Y %H:%M');
			$restrictions[] = '<strong>'.JText::_('START_DATE').'</strong>: '. $this->price->price_start_date;
		}

		if(!empty($this->price->price_end_date)) {
			$this->price->price_start_date = hikamarket::getDate($this->price->price_end_date, '%d %B %Y %H:%M');
			$restrictions[] = '<strong>'.JText::_('END_DATE').'</strong>: '. $this->price->price_start_date;
		}
	}

	if(!empty($this->price->price_site_id))
		$restrictions[] = '<strong>'.JText::_('SITE_ID').'</strong>: '.$this->price->price_site_id;

	echo implode('<br/>', $restrictions);

	$price_value = $this->shopConfig->get('floating_tax_prices', 0) ? @$this->price->price_value_with_tax : @$this->price->price_value;
?>
		<input type="hidden" name="<?php echo $form_key.'['.$this->price_num.'][price_id]'; ?>" value="<?php echo $this->price->price_id; ?>" />
		<input type="hidden" name="<?php echo $form_key.'['.$this->price_num.'][price_value]'; ?>" value="<?php echo $price_value; ?>" />
<?php if(empty($this->price->price_id) && empty($this->product->product_id) && $this->shopConfig->get('floating_tax_prices', 0) == 0) { ?>
		<input type="hidden" name="<?php echo $form_key.'['.$this->price_num.'][price_value_with_tax]'; ?>" value="<?php echo @$this->price->price_value_with_tax; ?>" />
<?php } ?>
<?php if($this->price_acls['currency']) { ?>
		<input type="hidden" name="<?php echo $form_key.'['.$this->price_num.'][price_currency_id]'; ?>" value="<?php echo $this->price->price_currency_id; ?>"/>
<?php } ?>
<?php if($this->price_acls['quantity']) { ?>
		<input type="hidden" name="<?php echo $form_key.'['.$this->price_num.'][price_min_quantity]'; ?>" value="<?php echo $qty; ?>"/>
<?php } ?>
<?php if(hikashop_level(2) && $this->price_acls['acl']) { ?>
		<input type="hidden" name="<?php echo $form_key.'['.$this->price_num.'][price_access]'; ?>" value="<?php echo $this->price->price_access; ?>"/>
<?php } ?>
<?php if(hikashop_level(2) && $this->price_acls['user']) { ?>
		<input type="hidden" name="<?php echo $form_key.'['.$this->price_num.'][price_users]'; ?>" value="<?php echo $this->price->price_users; ?>"/>
<?php } ?>
<?php if(hikashop_level(2) && $this->price_acls['date']) { ?>
		<input type="hidden" name="<?php echo $form_key.'['.$this->price_num.'][price_start_date]'; ?>" value="<?php echo @$this->price->price_start_date; ?>"/>
		<input type="hidden" name="<?php echo $form_key.'['.$this->price_num.'][price_end_date]'; ?>" value="<?php echo @$this->price->price_end_date; ?>"/>
<?php } ?>
<?php if(!empty($this->jms_integration)) { ?>
		<input type="hidden" name="<?php echo $form_key.'['.$this->price_num.'][price_site_id]'; ?>" value="<?php echo $this->price->price_site_id; ?>"/>
<?php } ?>
	</td>
	<td style="text-align:center">
		<a href="#delete" onclick="window.hikamarket.deleteRow(this); return false;"><i class="fas fa-trash-alt"></i></a>
	</td>
<?php
	return;
}

?>
<td colspan="3">
<dl class="hika_options" id="<?php echo 'hikamarket_price_edit_block_'.$form_key.'_'.$this->price_num; ?>">
	<dt><?php echo JText::_('PRICE'); ?></dt>
	<dd>
		<input type="hidden" name="<?php echo $form_key.'['.$this->price_num.']'; ?>[price_id]" value="<?php echo @$this->price->price_id; ?>"/>
		<input type="text" onchange="window.productMgr.updatePriceValue(<?php echo $this->price_num; ?>, false, '<?php echo $form_key; ?>')" id="hikamarket_<?php echo $form_key; ?>_<?php echo $this->price_num; ?>_edit" name="<?php echo $form_key.'['.$this->price_num.']'; ?>[price_value]" value="<?php if($this->shopConfig->get('floating_tax_prices',0)){ echo @$this->price->price_value_with_tax; }else{ echo @$this->price->price_value; } ?>"/>
		<input type="hidden" name="<?php echo $form_key.'_old['.$this->price_num.']'; ?>[price_value]" value="<?php if($this->shopConfig->get('floating_tax_prices',0)){ echo @$this->price->price_value_with_tax; }else{ echo @$this->price->price_value; } ?>"/>
	</dd>
<?php if(!$this->shopConfig->get('floating_tax_prices', 0)) { ?>
	<dt><?php echo JText::_('PRICE_WITH_TAX'); ?></dt>
	<dd>
		<input type="text" onchange="window.productMgr.updatePriceValue(<?php echo $this->price_num; ?>, true, '<?php echo $form_key; ?>')" id="hikamarket_<?php echo $form_key; ?>_<?php echo $this->price_num; ?>_with_tax_edit" name="<?php if(empty($this->price->price_id) && empty($this->product->product_id)){ echo $form_key.'['.$this->price_num.'][price_value_with_tax]'; } ?>" value="<?php echo @$this->price->price_value_with_tax; ?>"/>
<?php if(empty($this->price->price_id) && empty($this->product->product_id)) { ?>
		<input type="hidden" name="<?php echo $form_key.'_old['.$this->price_num.']'; ?>[price_value_with_tax]" value="<?php echo @$this->price->price_value_with_tax; ?>"/>
<?php } ?>
	</dd>
<?php } ?>
<?php if($this->price_acls['currency']) { ?>
	<dt><?php echo JText::_('CURRENCY'); ?></dt>
	<dd><?php
		if(empty($this->price->price_currency_id))
			$this->price->price_currency_id = $this->default_currency->currency_id;
		echo $this->currencyType->display($form_key.'['.$this->price_num.'][price_currency_id]', @$this->price->price_currency_id, '','hikamarket_' . $form_key . '_currency_edit');
	?><input type="hidden" name="<?php echo $form_key.'_old['.$this->price_num.']'; ?>[price_currency_id]" value="<?php echo @$this->price->price_currency_id; ?>"/></dd>
<?php } ?>
<?php if($this->price_acls['quantity']) { ?>
	<dt><?php echo JText::_('PRODUCT_QUANTITY'); ?></dt>
	<dd>
		<input type="text" id="hikamarket_<?php echo $form_key; ?>_qty_edit" name="<?php echo $form_key.'['.$this->price_num.']'; ?>[price_min_quantity]" value="<?php echo $this->price->price_min_quantity; ?>"/>
		<input type="hidden" name="<?php echo $form_key.'_old['.$this->price_num.']'; ?>[price_min_quantity]" value="<?php echo @$this->price->price_min_quantity; ?>"/>
	</dd>
<?php } ?>
<?php if(hikashop_level(2) && $this->price_acls['acl']) { ?>
	<dt><?php echo JText::_('ACCESS_LEVEL'); ?></dt>
	<dd><?php
		echo $this->joomlaAcl->display($form_key.'['.$this->price_num.'][price_access]', @$this->price->price_access, true, true, 'hikamarket_' . $form_key . '_acl_edit');
	?><input type="hidden" name="<?php echo $form_key.'_old['.$this->price_num.']'; ?>[price_access]" value="<?php echo @$this->price->price_access; ?>"/></dd>
<?php } ?>
<?php if(hikashop_level(2) && $this->price_acls['user']) { ?>
	<dt><?php echo JText::_('USERS'); ?></dt>
	<dd><?php
echo $this->nameboxType->display(
	$form_key.'['.$this->price_num.'][price_users]',
	explode(',',trim(@$this->price->price_users,',')),
	hikamarketNameboxType::NAMEBOX_MULTIPLE,
	'user',
	array(
		'id' => 'hikamarket_' . $form_key . '_' . $this->price_num . '_user_edit',
		'force_data' => true,
		'default_text' => 'PLEASE_SELECT'
	)
);
	?><input type="hidden" name="<?php echo $form_key.'_old['.$this->price_num.']'; ?>[price_users]" value="<?php echo @$this->price->price_users; ?>"/></dd>
<?php } ?>
<?php if(hikashop_level(2) && $this->price_acls['date']) { ?>
	<dt><?php echo JText::_('START_DATE'); ?></dt>
	<dd><?php
		echo JHTML::_('calendar', hikamarket::getDate((@$this->price->price_start_date?@$this->price->price_start_date:''),'%Y-%m-%d %H:%M'), $form_key.'['.$this->price_num.'][price_start_date]', 'hikamarket_' . $form_key .'_'.$this->price_num . '_start_date_edit', '%Y-%m-%d %H:%M', array('size' => '20', 'showTime' => true));
	?><input type="hidden" name="<?php echo $form_key.'_old['.$this->price_num.']'; ?>[price_start_date]" value="<?php echo @$this->price->price_start_date; ?>"/></dd>
	<dt><?php echo JText::_('END_DATE'); ?></dt>
	<dd><?php
		echo JHTML::_('calendar', hikamarket::getDate((@$this->price->price_end_date?@$this->price->price_end_date:''),'%Y-%m-%d %H:%M'), $form_key.'['.$this->price_num.'][price_end_date]', 'hikamarket_' . $form_key .'_'.$this->price_num . '_end_date_edit', '%Y-%m-%d %H:%M', array('size' => '20', 'showTime' => true));
	?><input type="hidden" name="<?php echo $form_key.'_old['.$this->price_num.']'; ?>[price_end_date]" value="<?php echo @$this->price->price_end_date; ?>"/></dd>
<script type="text/javascript">
window.hikashop.ready(function() {
	var d = document, els = document.querySelectorAll("#<?php echo 'hikamarket_price_edit_block_'.$form_key.'_'.$this->price_num; ?> .field-calendar");
	if(!els || typeof(JoomlaCalendar) == "undefined") return;
	for(i = els.length - 1; i >= 0; i--) {
		JoomlaCalendar.init(els[i]);
	}
});
</script>
<?php } ?>
<?php if(!empty($this->jms_integration)) { ?>
	<dt><?php echo JText::_('SITE_ID'); ?></dt>
	<dd><?php
		echo str_replace('class="inputbox"','class="inputbox no-chzn" style="width:90px;"', MultisitesHelperUtils::getComboSiteIDs(@$this->price->price_site_id, 'hikamarket_' . $form_key . '_site_edit', JText::_('SELECT_A_SITE')));
	?></dd>
<?php } ?>
</dl>
<div style="float:right">
	<button class="hikabtn hikabtn-success" onclick="return window.productMgr.editPrice(this, '<?php echo $form_key; ?>', <?php echo (int)$this->price_num;?>,<?php echo (int)$this->price->price_id; ?>, false);">
		<i class="fas fa-check"></i> <?php echo JText::_('HIKA_OK'); ;?>
	</button>
</div>
<button class="hikabtn hikabtn-danger" onclick="return window.productMgr.editPrice(this, '<?php echo $form_key; ?>', <?php echo (int)$this->price_num; ?>,<?php echo (int)$this->price->price_id; ?>, -1);">
	<i class="far fa-times-circle"></i> <?php echo JText::_('HIKA_CANCEL'); ;?>
</button>
</td>
