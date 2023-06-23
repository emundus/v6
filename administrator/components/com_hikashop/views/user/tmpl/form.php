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
<form action="<?php echo hikashop_completeLink('user'); ?>" method="post" name="adminForm" id="adminForm"  enctype="multipart/form-data">

<div class="hikashop_backend_tile_edition">
	<div class="hk-container-fluid">

<div class="hkc-lg-6 hikashop_tile_block hikashop_user_edit_general"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('MAIN_INFORMATION'); ?></div>
<?php if(!empty($this->extraData->main_top) && !empty($this->extraData->main_top)) { echo implode("\r\n", $this->extraData->main_top); } ?>
	<dl class="hika_options">
		<dt><label><?php
			echo JText::_('HIKA_USER_NAME');
		?></label></dt>
		<dd><?php
			if(!empty($this->user->name) || !empty($this->user->username))
				echo $this->escape(@$this->user->name);
			else
				echo '<em>'.JText::_('GUEST').'</em>';
		?></dd>
	</dl>

<?php if(!empty($this->user->username)) { ?>
	<dl class="hika_options">
		<dt><label><?php
			echo JText::_('HIKA_USERNAME');
		?></label></dt>
		<dd><?php
			echo $this->escape(@$this->user->username);

		?></dd>
	</dl>
<?php } ?>

	<dl class="hika_options">
		<dt><label for="user_email"><?php
			echo JText::_('HIKA_EMAIL');
		?></label></dt>
		<dd class="input_large">
			<input type="text" name="data[user][user_email]" id="user_email" class="inputbox" value="<?php echo $this->escape(@$this->user->user_email); ?>" />
		</dd>
	</dl>

<?php if(hikashop_level(2) && !empty($this->user->geolocation_ip)) { ?>
	<dl class="hika_options">
		<dt><label><?php
			echo JText::_('IP');
		?></label></dt>
		<dd><?php
			echo $this->user->geolocation_ip;
			if($this->user->geolocation_country != 'Reserved') {
				echo ' ( '.$this->user->geolocation_city.' '.$this->user->geolocation_state.' '.$this->user->geolocation_country.' )';
			}
		?></dd>
	</dl>
<?php } elseif($this->config->get('user_ip', 1) && !empty($this->user->user_created_ip)) { ?>
	<dl class="hika_options">
		<dt><label><?php
			echo JText::_('IP');
		?></label></dt>
		<dd><?php
			echo $this->user->user_created_ip;
		?></dd>
	</dl>
<?php } ?>
<?php
	if(!empty($this->fields['user'])) {
		$after = array();
		foreach($this->fields['user'] as $fieldName => $oneExtraField) {
			$onWhat = ($oneExtraField->field_type == 'radio') ? 'onclick' : 'onchange';
			$html = $this->fieldsClass->display(
				$oneExtraField,
				$this->user->$fieldName,
				'data[user]['.$fieldName.']',
				false,
				' '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\'user\',0);"',
				false,
				$this->fields['user'],
				$this->user
			);
			if($oneExtraField->field_type == 'hidden') {
				$after[] = $html;
				continue;
			}
?>
	<dl id="hikashop_user_<?php echo $fieldName; ?>" class="hika_options">
		<dt><label><?php
			echo $this->fieldsClass->getFieldName($oneExtraField);
		?></label></dt>
		<dd class="input_large"><?php
			echo $html;
		?></dd>
	</dl>
<?php
		}

		if(count($after)) {
			echo implode("\r\n", $after);
		}
	}
?>
<?php if(!empty($this->extraData->main_bottom) && !empty($this->extraData->main_bottom)) { echo implode("\r\n", $this->extraData->main_bottom); } ?>
</div></div>

<div class="hkc-lg-6 hikashop_tile_block hikashop_user_addresses_general"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('ADDRESSES'); ?></div>
	<div class="hk-row-fluid">
		<div class="hkc-lg-6">
<?php
$this->type = 'billing';
echo $this->loadTemplate('address');
?>
		</div>
		<div class="hkc-lg-6">
<?php
$this->type = 'shipping';
echo $this->loadTemplate('address');
?>
		</div>
	</div>
</div></div>
<?php if(!empty($this->extraData->after_address) && !empty($this->extraData->after_address)) { echo implode("\r\n", $this->extraData->after_address); } ?>
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
<?php if(!empty($this->extraData->end) && !empty($this->extraData->end)) { echo implode("\r\n", $this->extraData->end); } ?>
	</div>
</div>
	<input type="hidden" name="cancel_redirect" value="<?php echo base64_encode(hikaInput::get()->getString('cancel_redirect')); ?>" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->user->user_id; ?>" />
	<input type="hidden" name="order_id" value="<?php echo hikaInput::get()->getInt('order_id', 0); ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="user" />
	<?php echo JHTML::_('form.token'); ?>
</form>
