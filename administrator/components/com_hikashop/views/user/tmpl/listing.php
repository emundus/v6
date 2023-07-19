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
<form action="<?php echo hikashop_completeLink('user'); ?>" method="post" name="adminForm" id="adminForm">
<?php
$class_search = "hika_j3_search";
$class_filters = ' no_extrafilter';

if (HIKASHOP_J40) {
	$class_search = "hika_j4_search";
}
if ((!empty($this->extrafilters)) && (count($this->extrafilters))) {
	foreach($this->extrafilters as $name => $filterObj) {
		if ($name == 'filter_partner') {
			$filter_partner = $filterObj->displayFilter($name, $this->pageInfo->filter);
			unset($this->extrafilters[$name]);
		}
	}
} 
if ((!empty($this->extrafilters)) && (count($this->extrafilters))) {
	$class_filters =' hikafilter_extra extra_'.count($this->extrafilters);
}
?>
<div class="hk-row-fluid">
	<div class="hkc-md-5 <?php echo $class_search; ?>"><?php
		echo $this->loadHkLayout('search', array());
	?></div>
	<div class="hkc-md-7 hikashop_listing_filters <?php echo $class_filters; ?>"><?php
		if(@$this->affiliate_active && !empty($this->partner)) {
			echo $this->partner->display("filter_partner",$this->pageInfo->filter->filter_partner, false);
		}
		if ((!empty($this->extrafilters)) && (count($this->extrafilters))) {
?>		<div class="hikashop_listing_filters_column colum_extra hkc-md-3">
<?php		foreach($this->extrafilters as $name => $filterObj) {
				echo $filterObj->displayFilter($name, $this->pageInfo->filter);
			}
?>		
		</div>
<?php	} ?>	
</div>
</div>

<?php
$count = 7 + count($this->fields);
$hikashop_id = "hikashop_user_listing";

if ($this->pageInfo->filter->filter_partner == 1)
	$hikashop_id = "hikashop_partners_listing";
?>
<?php 
	echo $this->loadHkLayout('columns', array()); 
?>
<table id="<?php echo $hikashop_id; ?>" class="adminlist table table-striped table-hover" cellpadding="1">
	<thead>
		<tr>
			<th class="title titlenum"><?php
				echo JText::_('HIKA_NUM');
			?></th>
			<th class="title titlebox">
				<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
			</th>
			<th class="title"><?php
				echo JText::_('HIKA_EDIT');
			?></th>
			<th class="title"><?php
				echo JHTML::_('grid.sort', JText::_('HIKA_USER_NAME'), 'juser.name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
			?></th>
			<th class="title"><?php
				echo JHTML::_('grid.sort', JText::_('HIKA_USERNAME'), 'juser.username', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
			?></th>
			<th class="title"><?php
				echo JHTML::_('grid.sort', JText::_('HIKA_EMAIL'), 'huser.user_email', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
			?></th>
<?php
	if(!empty($this->fields)) {
		foreach($this->fields as $field) {
?>
			<th class="title"><?php
				echo JHTML::_('grid.sort', $this->fieldsClass->trans($field->field_realname), 'huser.'.$field->field_namekey, $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
			?></th>
<?php
		}
	}

	if($this->pageInfo->filter->filter_partner == 1) {
		$count++;
?>
			<th class="title"><?php
				echo JHTML::_('grid.sort', JText::_('TOTAL_UNPAID_AMOUNT'), 'huser.user_unpaid_amount', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
			?></th>
<?php
	}
?>
			<th class="title"><?php
				echo JHTML::_('grid.sort', JText::_('ID'), 'huser.user_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
			?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="<?php echo $count; ?>">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
<?php
	$k = 0;
	$i = 0;
	foreach($this->rows as &$row) {
?>
		<tr class="row<?php echo $k; ?>">
			<td><?php
				echo $this->pagination->getRowOffset($i);
			?></td>
			<td><?php
				echo JHTML::_('grid.id', $i, (int)$row->user_id);
			?></td>
			<td>
<?php if($this->manage){ ?>
				<a href="<?php echo hikashop_completeLink('user&task=edit&cid='.(int)$row->user_id); ?>" title="<?php echo JText::_('HIKA_EDIT'); ?>">
					<i class="fas fa-pen"></i>
				</a>
<?php } ?>
			</td>
			<td><?php
				echo @$row->name;
			?></td>
			<td><?php
				echo @$row->username;
			?></td>
			<td><?php
				echo $row->user_email;
			?></td>
<?php
		if(!empty($this->fields)){
			foreach($this->fields as $field){
				$namekey = $field->field_namekey;
				echo '<td>'.$this->fieldsClass->show($field,$row->$namekey).'</td>';
			}
		}

		if($this->pageInfo->filter->filter_partner == 1) {
?>
			<td>
				<?php
				if(bccomp(sprintf('%F',$row->user_unpaid_amount),0,5)){
					$config =& hikashop_config();
					if(!$config->get('allow_currency_selection',0) || empty($row->user_currency_id)){
						$row->user_currency_id =  $config->get('partner_currency',1);
					}
					echo $this->currencyHelper->format($row->user_unpaid_amount,$row->user_currency_id);
				}
				?>
			</td>
<?php }?>
			<td width="1%"><?php
				echo (int)$row->user_id;
			?></td>
		</tr>
<?php
		$i++;
		$k = 1-$k;
	}
	unset($row);
?>
	</tbody>
</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
<?php if($this->pageInfo->filter->filter_partner == 1) { ?>
<style type="text/css">
@media only screen and (max-width: 800px) {
	table#hikashop_user_listing td:nth-last-child(2),
	table#hikashop_user_listing th:nth-last-child(2){display: none;}
}
</style>
<?php } ?>
