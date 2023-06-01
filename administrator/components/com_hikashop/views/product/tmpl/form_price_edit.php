<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><dl class="hika_options">
	<dt><label><?php echo JText::_('PRICE'); ?></label></dt>
	<dd>
		<input type="hidden" id="hikashop_<?php echo $this->form_key; ?>_id_edit" name="" value="<?php echo @$this->price->price_id; ?>>"/>
		<input type="text" size="5" style="width:70px;" onchange="window.productMgr.updatePrice(false, '<?php echo $this->form_key; ?>')" id="hikashop_<?php echo $this->form_key; ?>_edit" name="" value="<?php if($this->config->get('floating_tax_prices',0)){ echo @$this->price->price_value_with_tax; }else{ echo @$this->price->price_value; } ?>"/>
	</dd>
<?php
if(!$this->config->get('floating_tax_prices',0)){ ?>
	<dt><label><?php echo JText::_('PRICE_WITH_TAX'); ?></label></dt>
	<dd>
		<input type="text" size="5" style="width:70px;" onchange="window.productMgr.updatePrice(true, '<?php echo $this->form_key; ?>')" id="hikashop_<?php echo $this->form_key; ?>_with_tax_edit" name="" value="<?php echo @$this->price->price_value_with_tax; ?>"/>
	</dd>
<?php } ?>
	<dt><label><?php echo JText::_('CURRENCY'); ?></label></dt>
<?php
$width = '80px';
if (HIKASHOP_J40) {
	$width = '110px';
} ?>
	<dd>
		<?php echo $this->currencyType->display('', @$this->price->price_currency_id, 'size="1" style="width:'.$width.'"','hikashop_' . $this->form_key . '_currency_edit'); ?>
	</dd>
	<dt><label><?php echo JText::_('MINIMUM_QUANTITY'); ?></label></dt>
	<dd>
		<input type="text" size="5" style="width:70px;" id="hikashop_<?php echo $this->form_key; ?>_qty_edit" name="" value="<?php echo $this->price->price_min_quantity; ?>"/>
	</dd>
<?php if(hikashop_level(2)) { ?>
	<dt><label><?php echo JText::_('START_DATE'); ?></label></dt>
	<dd>
		<?php echo JHTML::_('calendar', hikashop_getDate((@$this->price->price_start_date?@$this->price->price_start_date:''),'%Y-%m-%d %H:%M'), 'price_start_date', 'hikashop_' . $this->form_key . '_start_date_edit', hikashop_getDateFormat('%d %B %Y %H:%M'), array('size' => '20', 'showTime' => true)); ?>
	</dd>
	<dt><label><?php echo JText::_('END_DATE'); ?></label></dt>
	<dd>
		<?php echo JHTML::_('calendar', hikashop_getDate((@$this->price->price_end_date?@$this->price->price_end_date:''),'%Y-%m-%d %H:%M'), 'price_end_date', 'hikashop_' . $this->form_key . '_end_date_edit', hikashop_getDateFormat('%d %B %Y %H:%M'), array('size' => '20', 'showTime' => true)); ?>
	</dd>
	<dt><label><?php echo JText::_('ACCESS_LEVEL'); ?></label></dt>
	<dd>
		<?php echo $this->joomlaAcl->display('hikashop_' . $this->form_key . '_acl_edit'.$this->price->price_id, @$this->price->price_access, true, true, 'hikashop_' . $this->form_key . '_acl_edit'); ?>
	</dd>
	<dt><label><?php echo JText::_('USERS'); ?></label></dt>
	<dd>
<?php
echo $this->nameboxVariantType->display(
	'hikashop_' . $this->form_key . '_user_edit',
	explode(',',trim((string)@$this->price->price_users,',')),
	hikashopNameboxType::NAMEBOX_MULTIPLE,
	'user',
	array(
		'id' => 'hikashop_' . $this->form_key . '_user_edit',
		'add' => true,
		'default_text' => 'PLEASE_SELECT'
	)
);
?>
	</dd>
<?php } ?>
<?php if($this->jms_integration){ ?>
	<dt><label><?php echo JText::_('SITE_ID'); ?></label></dt>
	<dd>
		<?php echo str_replace('class="custom-select"','class="custom-select no-chzn" style="width:90px;"', MultisitesHelperUtils::getComboSiteIDs( @$this->price->price_site_id, 'hikashop_' . $this->form_key . '_site_edit', JText::_( 'SELECT_A_SITE'))); ?>
	</dd>
<?php } ?>
</dl>
<div style="float:right">
	<button onclick="return window.productMgr.addPrice('<?php echo $this->form_key; ?>');" class="btn btn-success">
		<i class="fa fa-save"></i> <?php echo JText::_('HIKA_OK'); ;?>
	</button>
</div>
<button onclick="<?php if(!empty($this->price->price_id)) echo 'window.productMgr.restorePriceRow('.$this->price->price_id.');'; ?>return window.productMgr.cancelNewPrice('<?php echo $this->form_key ?>');" class="btn btn-danger">
	<i class="fa fa-times"></i> <?php echo JText::_('HIKA_CANCEL'); ;?>
</button>
<div style="clear:both"></div>
