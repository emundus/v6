<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><tr>
	<td class="key">
		<label for="data[payment][payment_params][points_mode]"><?php
			echo JText::_('POINTS_MODE');
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.genericlist', $this->data['modes'], "data[payment][payment_params][points_mode]", '', 'value', 'text', @$this->element->payment_params->points_mode);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][value]"><?php
			echo JText::sprintf('RATES', $this->element->payment_name);
		?></label>
	</td>
	<td>
		<?php echo '1 '.JText::sprintf( 'POINTS' ).' '.JText::sprintf( 'EQUALS', $this->element->payment_name); ?>
		<input style="width: 50px;" type="text" name="data[payment][payment_params][value]" value="<?php echo @$this->element->payment_params->value; ?>" />
		<?php  echo $this->data['currency']->currency_code. ' ' .$this->data['currency']->currency_symbol; ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][tax_id]"><?php
			echo JText::_('TAXATION_CATEGORY');
		?></label>
	</td>
	<td><?php
		echo $this->categoryType->display('data[payment][payment_params][tax_id]', @$this->element->payment_params->tax_id, true);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][percent]"><?php
			echo JText::sprintf('GROUP_POINTS_BY', $this->element->payment_name);
		?></label>
	</td>
	<td>
		<input style="width: 50px;" type="text" name="data[payment][payment_params][grouppoints]" value="<?php echo @$this->element->payment_params->grouppoints; ?>" /> <?php echo JText::sprintf( 'POINTS' );?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][percent]"><?php
			echo JText::sprintf('MAXIMUM_POINTS', $this->element->payment_name);
		?></label>
	</td>
	<td>
		<input style="width: 50px;" type="text" name="data[payment][payment_params][maxpoints]" value="<?php echo @$this->element->payment_params->maxpoints; ?>" /> <?php echo JText::sprintf( 'POINTS' );?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][allowshipping]"><?php
			echo JText::sprintf('SHIPPING', $this->element->payment_name);
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][allowshipping]" , '',@$this->element->payment_params->allowshipping);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][partialpayment]"><?php
			echo JText::sprintf('ALLOW_PARTIAL_PAYMENT', $this->element->payment_name);
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][partialpayment]" , 'onclick="setVisible(this.value);"',@$this->element->payment_params->partialpayment	);
	?></td>
</tr>
<?php
$display = '';
if(empty($this->element->payment_params->partialpayment)){
	$display = ' style="display:none;"';
}
?>
<tr>
	<td class="key">
		<div id="opt"<?php echo $display?>>
			<label for="data[payment][payment_params][percentmax]"><?php
				echo JText::sprintf('MAXIMUM_ORDER_PERCENT', $this->element->payment_name);
			?></label>
		</div>
	</td>
	<td>
		<div id="opt2"<?php echo $display?>>
			<input style="width: 50px;" type="text" name="data[payment][payment_params][percentmax]" value="<?php echo @$this->element->payment_params->percentmax; ?>" />%
		</div>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][percent]"><?php
			echo JText::sprintf('MINIMUM_ORDER_PERCENT', $this->element->payment_name);
		?></label>
	</td>
	<td>
		<input style="width: 50px;" type="text" name="data[payment][payment_params][percent]" value="<?php echo @$this->element->payment_params->percent; ?>" />%
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][minimumcost]"><?php
			echo JText::_('MINIMUM_COST');
		?></label>
	</td>
	<td>
		<div id="opt2" style="display:block;">
			<input style="width: 50px;" type="text" name="data[payment][payment_params][minimumcost]" value="<?php echo @$this->element->payment_params->minimumcost; ?>" />
			<?php  echo $this->data['currency']->currency_code. ' ' .$this->data['currency']->currency_symbol; ?>
		</div>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][givebackpoints]"><?php
			echo JText::sprintf('GIVE_BACK_POINTS_IF_CANCELLED', $this->element->payment_name);
		?></label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][givebackpoints]" , '',@$this->element->payment_params->givebackpoints ); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][virtual_coupon]"><?php
			echo JText::sprintf('USE_VIRTUAL_COUPON', $this->element->payment_name);
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][virtual_coupon]" , '',@$this->element->payment_params->virtual_coupon );
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][grouppoints_warning_lvl]"><?php
			echo JText::sprintf('GROUP_POINTS_WARNING_LEVEL', $this->element->payment_name);
		?></label>
	</td>
	<td>
		<input style="width: 50px;" type="text" name="data[payment][payment_params][grouppoints_warning_lvl]" value="<?php echo @$this->element->payment_params->grouppoints_warning_lvl; ?>" /> <?php echo JText::sprintf( 'POINTS' );?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][verified_status]"><?php
			echo JText::_('VERIFIED_STATUS');
		?></label>
	</td>
	<td><?php
		if(empty($this->element->payment_params->verified_status)) {
			$config = hikashop_config();
			$$this->element->payment_params->verified_status = $config->get('order_confirmed_status', 'confirmed');
		}
		echo $this->data['order_statuses']->display('data[payment][payment_params][verified_status]', $this->element->payment_params->verified_status);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][return_url]">
			<?php echo JText::_('RETURN_URL'); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][return_url]" value="<?php echo @$this->element->payment_params->return_url; ?>" />
	</td>
</tr>
