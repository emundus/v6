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
<form action="<?php echo hikashop_completeLink('user'); ?>" method="post" name="adminForm" id="adminForm">

<div class="hk-row">
	<div class="hkc-md-5"><?php
		echo $this->searchType->display('search', $this->pageInfo->search);
	?></div>
	<div class="hkc-md-7"><?php
		if($this->affiliate_active && !empty($this->partner)) {
			echo $this->partner->display("filter_partner",$this->pageInfo->filter->filter_partner, false);
		}
	?></div>
</div>

<?php
$count = 7 + count($this->fields);
?>
<table id="hikashop_user_listing" class="adminlist table table-striped table-hover" cellpadding="1">
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
				<?php echo $this->pagination->getResultsCounter(); ?>
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
			<td class="hk_center"><?php
				echo $this->pagination->getRowOffset($i);
			?></td>
			<td class="hk_center"><?php
				echo JHTML::_('grid.id', $i, (int)$row->user_id);
			?></td>
			<td class="hk_center">
<?php if($this->manage){ ?>
				<a href="<?php echo hikashop_completeLink('user&task=edit&cid='.(int)$row->user_id); ?>"><img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/></a>
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
			<td class="hk_center">
				<?php
				if(bccomp($row->user_unpaid_amount,0,5)){
					$config =& hikashop_config();
					if(!$config->get('allow_currency_selection',0) || empty($row->user_currency_id)){
						$row->user_currency_id =  $config->get('partner_currency',1);
					}
					echo $this->currencyHelper->format($row->user_unpaid_amount,$row->user_currency_id);
				}
				?>
			</td>
<?php }?>
			<td width="1%" class="hk_center"><?php
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
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
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
