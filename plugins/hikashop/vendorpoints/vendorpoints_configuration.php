<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
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
				<label for="data[plugin][plugin_params][limittype]"><?php
					echo JText::_('LIMIT_POINTS_BY_TYPE');
				?></label>
			</td>
			<td><?php
				echo JHTML::_('hikaselect.booleanlist', "data[plugin][plugin_params][limittype]" , '',@$this->element->plugin_params->limittype);
			?></td>
		</tr>
	</table>
</fieldset>
<fieldset class="adminform">
	<legend><?php echo JText::_('CATEGORIES_POINTS'); ?></legend>
	<div style="text-align:right;">
		<a class="modal" id="hikashop_cat_popup" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("product&task=selectcategory&control=plugin",true ); ?>">
			<button class="btn" type="button" onclick="return false"><img src="<?php echo HIKASHOP_IMAGES; ?>add.png" style="vertical-align:middle"/><?php echo JText::_('ADD');?></button>
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
		for($i = 0,$a = count($this->data['categories']);$i<$a;$i++){
			$row =& $this->data['categories'][$i];
			if(!empty($row->category_id)){
?>
			<tr id="category_<?php echo $row->category_id;?>">
				<td>
					<a href="<?php echo hikashop_completeLink('category&task=edit&cid='.$row->category_id); ?>"><?php echo $row->category_name; ?></a>
				</td>
				<td align="center">
					<input type="text" name="category_points[<?php echo $row->category_id;?>]" id="category_points[<?php echo $row->category_id;?>]" value="<?php echo (int)@$row->category_points; ?>" />
				</td>
				<td width="1%" align="center">
					<?php echo $row->category_id; ?>
					<div id="category_div_<?php echo $row->category_id;?>">
						<input type="hidden" name="category[<?php echo $row->category_id;?>]" id="category[<?php echo $row->category_id;?>]" value="<?php echo $row->category_id;?>"/>
					</div>
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
	<table>
