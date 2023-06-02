<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>		<tr>
			<td class="key">
				<label for="data[plugin][plugin_params][points_mode]"><?php
					echo JText::_('POINTS_MODE');
				?></label>
			</td>
			<td><?php
				echo JHTML::_('hikaselect.genericlist', $this->data['modes'], "data[plugin][plugin_params][points_mode]", '', 'value', 'text', @$this->element->plugin_params->points_mode);
			?></td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[plugin][plugin_params][notgivewhenuse]"><?php
					echo JText::_('POINTS_GIVE_WHEN_USE');
				?></label>
			</td>
			<td><?php
				$values = array(
					'0' => JHTML::_('select.option', 0, JText::_('HIKA_POINTS_GIVE')),
					'2' => JHTML::_('select.option', 2, JText::_('HIKA_POINTS_GIVE_REMOVE')),
					'1' => JHTML::_('select.option', 1, JText::_('HIKA_POINTS_NOTGIVE')),
				);
				echo JHTML::_('hikaselect.radiolist', $values, "data[plugin][plugin_params][notgivewhenuse]", '', 'value', 'text', @$this->element->plugin_params->notgivewhenuse);
			?></td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[plugin][plugin_params][rounddown]"><?php
					echo JText::_('POINTS_ROUND_DOWN');
				?></label>
			</td>
			<td><?php
				echo JHTML::_('hikaselect.booleanlist', "data[plugin][plugin_params][rounddown]" , '', @$this->element->plugin_params->rounddown);
			?></td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[payment][payment_params][virtualpoints]"><?php
					echo JText::_('GIVE_VIRTUAL_POINTS');
				?></label>
			</td>
			<td><?php
				echo JHTML::_('hikaselect.booleanlist', "data[plugin][plugin_params][virtualpoints]" , '', @$this->element->plugin_params->virtualpoints );
				echo JText::_('PLEASE_NOTE_THAT_THIS_OTPION_SHOULD_BE_TURNED_OFF_IN_MOST_CASES');
			?></td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[plugin][plugin_params][currency_rate]"><?php
					echo JText::_('RATES');
				?></label>
			</td>
			<td>
				<input type="text" name="data[plugin][plugin_params][currency_rate]" value="<?php echo @$this->element->plugin_params->currency_rate; ?>" />
				<?php  echo $this->data['currency']->currency_code. ' ' .$this->data['currency']->currency_symbol; ?>
				<?php echo JText::_( 'EQUALS') . '1 ' . JText::_( 'POINTS'); ?>
		</tr>
		<tr>
			<td class="key">
				<label for="data[plugin][plugin_params][productpoints]"><?php
					echo JText::_('PRODUCT_POINTS');
				?></label>
			</td>
			<td>
				<input type="text" name="data[plugin][plugin_params][productpoints]" value="<?php echo @$this->element->plugin_params->productpoints; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[plugin][plugin_params][product_categories]"><?php
					echo JText::_('PRODUCT_CATEGORIES');
				?></label>
			</td>
			<td><?php
				$nameboxType = hikashop_get('type.namebox');
				echo $nameboxType->display(
					'data[plugin][plugin_params][product_categories]',
					@$this->element->plugin_params->product_categories,
					hikashopNameboxType::NAMEBOX_MULTIPLE,
					'category',
					array(
						'delete' => true,
					)
				);
			?></td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[plugin][plugin_params][limittype]"><?php
					echo JText::_('LIMIT_POINTS_BY_TYPE');
				?></label>
			</td>
			<td><?php
				echo JHTML::_('hikaselect.booleanlist', "data[plugin][plugin_params][limittype]" , '',@$this->element->plugin_params->limittype);
			?></td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[plugin][plugin_params][shippingpoints]"><?php
					echo JText::_('EARN_POINTS_ON_SHIPPING');
				?></label>
			</td>
			<td><?php
				echo JHTML::_('hikaselect.booleanlist', "data[plugin][plugin_params][shippingpoints]" , '',@$this->element->plugin_params->shippingpoints);
			?></td>
		</tr>
	</table>
</fieldset>
<fieldset class="adminform">
	<legend><?php echo JText::_('CATEGORIES_POINTS'); ?></legend>
	<div style="text-align:right;">
		<a id="hikashop_cat_popup" href="#" onclick="return window.hikashop.addCategoryRow();">
			<span class="hikabtn hikabtn-primary"><i class="fas fa-plus"></i> <?php echo JText::_('ADD');?></span>
		</a>
	</div>
	<table class="adminlist table table-striped" cellpadding="1" width="100%">
		<thead>
			<tr>
				<th class="title"><?php
					echo JText::_('HIKA_NAME');
				?></th>
				<th class="title titletoggle"><?php
					echo JText::_('POINTS');
				?></th>
				<th class="title"><?php
					echo JText::_('ID');
				?></th>
			</tr>
		</thead>
		<tbody id="category_listing">
<?php
	if(!empty($this->data['categories'])){
		$k = 0;
		$nameboxType = hikashop_get('type.namebox');
		for($i = 0,$a = count($this->data['categories']);$i<$a;$i++){
			$row =& $this->data['categories'][$i];
			if(!empty($row->category_id)){
?>
			<tr id="category_<?php echo $row->category_id;?>">
				<td>
					<?php echo $nameboxType->display(
							'category['.$i.']',
							$row->category_id,
							hikashopNameboxType::NAMEBOX_SINGLE,
							'category',
							array(
								'delete' => false,
								'root' => 0,
								'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
							)
					); ?>
				</td>
				<td class="hk_center">
					<input type="text" name="category_points[<?php echo $i;?>]" id="category_points_<?php echo $i;?>" value="<?php echo (int)@$row->category_points; ?>" />
				</td>
				<td width="1%" class="hk_center">
					<?php echo $row->category_id; ?>
				</td>
			</tr>
<?php
			}
			$k = 1-$k;
		}
	}
?>
		</tbody>
	</table>
	<table class="admintable table">
		<tr>
			<td class="key">
				<label for="data[plugin][plugin_params][limitcategory]"><?php
					echo JText::_('LIMIT_POINTS_BY_CATEGORY');
				?></label>
			</td>
			<td><?php
				echo JHTML::_('hikaselect.booleanlist', "data[plugin][plugin_params][limitcategory]" , '',@$this->element->plugin_params->limitcategory);
			?></td>
		</tr>
	</table>
</fieldset>
<fieldset class="adminform">
	<legend><?php echo JText::_( 'GROUPS_POINTS' ); ?></legend>
<?php
	if(hikashop_level(2)){
?>
		<table class="admintable table">
<?php
		foreach($this->data['groups'] as $group){
?>
			<tr>
				<td>
					<label for="groups[<?php echo $group->value; ?>]"><?php echo $group->text;?></label>
				</td>
				<td>
					<input type="text" name="groups[<?php echo $group->value; ?>]" value="<?php echo (int)@$group->points; ?>" />
				</td>
			</tr>
<?php
		}
?>
			<tr>
				<td class="key">
					<label for="data[plugin][plugin_params][limitgroup]"><?php
						echo JText::_('LIMIT_POINTS_BY_GROUP');
					?></label>
				</td>
				<td><?php
					echo JHTML::_('hikaselect.booleanlist', "data[plugin][plugin_params][limitgroup]" , '',@$this->element->plugin_params->limitgroup);
				?></td>
			</tr>
		</table>
<?php
	} else {
		echo hikashop_getUpgradeLink('business');
	}
?>
<table>
<script>
window.hikashop.addCategoryRow = function() {
	var target = document.getElementById('category_listing');
	window.hikashop.xRequest('<?php echo hikashop_completeLink('category&task=points_row&tmpl=component', false, false, true); ?>', {update: target, replace: false});
	return false;
};
</script>
