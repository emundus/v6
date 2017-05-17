<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('user'); ?>" method="post" name="adminForm" id="adminForm"  enctype="multipart/form-data">

<div class="hikashop_backend_tile_edition">
	<div class="hk-container-fluid">

<div class="hkc-lg-6 hikashop_tile_block hikashop_user_edit_general"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('MAIN_INFORMATION'); ?></div>
	<dl class="hika_options large">

		<dt><label><?php
			echo JText::_('HIKA_USER_NAME');
		?></label></dt>
		<dd><?php
			if(!empty($this->user->name) || !empty($this->user->username))
				echo $this->escape(@$this->user->name);
			else
				echo '<em>'.JText::_('GUEST').'</em>';
		?></dd>

<?php if(!empty($this->user->username)) { ?>
		<dt><label><?php
			echo JText::_('HIKA_USERNAME');
		?></label></dt>
		<dd><?php
			echo $this->escape(@$this->user->username);

		?></dd>
<?php } ?>

		<dt><label for="user_email"><?php
			echo JText::_('HIKA_EMAIL');
		?></label></dt>
		<dd class="input_large">
			<input type="text" name="data[user][user_email]" id="user_email" class="inputbox" value="<?php echo $this->escape(@$this->user->user_email); ?>" />
		</dd>

<?php if(hikashop_level(2) && !empty($this->user->geolocation_ip)) { ?>
		<dt><label><?php
			echo JText::_('IP');
		?></label></dt>
		<dd><?php
			echo $this->user->geolocation_ip;
			if($this->user->geolocation_country != 'Reserved') {
				echo ' ( '.$this->user->geolocation_city.' '.$this->user->geolocation_state.' '.$this->user->geolocation_country.' )';
			}
		?></dd>
<?php } ?>

<?php
	if(!empty($this->fields['user'])) {
		foreach($this->fields['user'] as $fieldName => $oneExtraField) {
			$onWhat = ($oneExtraField->field_type == 'radio') ? 'onclick' : 'onchange';
?>
		<dt><label><?php
			echo $this->fieldsClass->getFieldName($oneExtraField);
		?></label></dt>
		<dd><?php
			echo $this->fieldsClass->display(
				$oneExtraField,
				$this->user->$fieldName,
				'data[user]['.$fieldName.']',
				false,
				' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\'user\',0);"',
				false,
				$this->fields['user'],
				$this->user
			);
		?></dd>
<?php
		}
	}
?>

	</dl>
</div></div>

<div class="hkc-lg-6 hikashop_tile_block hikashop_user_addresses_general"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('ADDRESSES'); ?></div>

<?php
echo $this->loadTemplate('address');
?>

</div></div>

<?php
if(hikashop_level(2) && $this->affiliate_active) {
	echo $this->loadTemplate('affiliate');
}
?>

<div class="hkc-lg-12 hikashop_tile_block hikashop_user_orders_general"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('ORDERS'); ?></div>

<table id="hikashop_user_order_listing" class="adminlist table table-striped table-hover table-bordered" cellpadding="1">
	<thead>
		<tr>
			<th class="title titlenum"><?php echo JText::_('HIKA_NUM');?></th>
			<th class="title"><?php echo JText::_('ORDER_NUMBER'); ?></th>
			<th class="title"><?php echo JText::_('PAYMENT_METHOD'); ?></th>
			<th class="title"><?php echo JText::_('DATE'); ?></th>
			<th class="title"><?php echo JText::_('HIKA_LAST_MODIFIED'); ?></th>
			<th class="title"><?php echo JText::_('ORDER_STATUS'); ?></th>
			<th class="title"><?php echo JText::_('HIKASHOP_TOTAL'); ?></th>
			<th class="title"><?php echo JText::_('ID'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
	$k = 0;
	$i = 0;
	foreach($this->rows as $row) {
		$i++;
?>
		<tr class="row<?php echo $k; ?>">
			<td class="hk_center"><?php
				echo $i;
			?></td>
			<td class="hk_center">
				<a href="<?php echo hikashop_completeLink('order&task=edit&cid='.(int)$row->order_id.'&user_id='.(int)$this->user->user_id); ?>"><?php
					echo $row->order_number;
				?></a>
			</td>
			<td class="hk_center"><?php
		if(!empty($row->order_payment_method)) {
			if(!empty($this->payments[$row->order_payment_id])) {
				echo $this->payments[$row->order_payment_id]->payment_name;
			}else{
				echo $row->order_payment_method;
			}
		}
			?></td>
			<td class="hk_center"><?php
				echo hikashop_getDate($row->order_created, '%Y-%m-%d %H:%M');
			?></td>
			<td class="hk_center"><?php
				echo hikashop_getDate($row->order_modified, '%Y-%m-%d %H:%M');
			?></td>
			<td class="hk_center"><?php
				echo '<span class="order-label order-label-' . preg_replace('#[^a-z_0-9]#i', '_', str_replace(' ','_', $row->order_status)).'">' . hikashop_orderStatus($row->order_status) . '</span>'
			?></td>
			<td class="hk_center"><?php
				echo $this->currencyClass->format($row->order_full_price, $row->order_currency_id);
			?></td>
			<td width="1%" class="hk_center"><?php
				echo $row->order_id;
			?></td>
		</tr>
<?php
		$k = 1 - $k;
	}
?>
	</tbody>
</table>
</div></div>

	</div>
</div>
	<input type="hidden" name="cancel_redirect" value="<?php echo base64_encode(JRequest::getString('cancel_redirect')); ?>" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->user->user_id; ?>" />
	<input type="hidden" name="order_id" value="<?php echo JRequest::getInt('order_id', 0); ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="user" />
	<?php echo JHTML::_('form.token'); ?>
</form>
