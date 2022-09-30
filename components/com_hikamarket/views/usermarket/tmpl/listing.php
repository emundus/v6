<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div>
<form action="<?php echo hikamarket::completeLink('user&task=listing'.$this->url_itemid); ?>" method="post" name="adminForm" id="adminForm">
	<div class="hk-row-fluid">
		<div class="hkc-md-12"><?php
	echo $this->loadHkLayout('search', array(
		'id' => 'hikamarket_user_listing_search',
	));
		?></div>
	</div>
	<div class="hk-row-fluid">
		<div class="hkc-md-12">
			<div class="expand-filters" style="width:auto;">
<?php
?>
			</div>
			<div style="clear:both"></div>
		</div>
	</div>
	<table class="hikam_listing hikam_table" style="width:100%">
		<thead>
			<tr>
				<th class="hikamarket_user_name_title title"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_USER_NAME'), 'juser.name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
				?></th>
				<th class="hikamarket_user_login_title title"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_USERNAME'), 'juser.username', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
				?></th>
				<th class="hikamarket_user_email_title title"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_EMAIL'), 'hkuser.user_email', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value);
				?></th>
<?php
if(!empty($this->fields)) {
	foreach($this->fields as $field) {
?>
				<th class="hikamarket_user_<?php echo $field->field_namekey; ?>_title title"><?php
					echo JHTML::_('grid.sort', $this->fieldsClass->trans($field->field_realname), 'hkuser.'.$field->field_namekey, $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
				?></th>
<?php
	}
}

	if($this->vendor->vendor_id == 1) {
?>
				<th class="hikamarket_user_id_title title">
					<?php echo JHTML::_('grid.sort', JText::_( 'ID' ), 'hkuser.user_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
<?php
	}
?>
			</tr>
		</thead>
<?php if(!isset($this->embbed)) {
	$columns = 3 + count($this->fields);
	if($this->vendor->vendor_id == 1) $columns++;
?>
		<tfoot>
			<tr>
				<td colspan="<?php echo $columns; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
<?php } ?>
		<tbody>
<?php
$k = 0;
$i = 0;
foreach($this->rows as $user) {
	$rowId = 'market_user_'.$user->user_id;
	if($this->manage)
		$url = hikamarket::completeLink('user&task=show&cid='.$user->user_id.$this->url_itemid);
?>
			<tr class="row<?php echo $k; ?>" id="<?php echo $rowId; ?>">
				<td class="hikamarket_user_name_value"><?php
					if(!empty($url))
						echo '<a href="'.$url.'"><i class="fas fa-pencil-alt" style="margin-right:6px;"></i>';
					if(!empty($user->name))
						echo $user->name;
					else
						echo '<em>'.JText::_('HIKAM_GUEST_USER').'</em>';
					if(!empty($url))
						echo '</a>';
				?></td>
				<td class="hikamarket_user_login_value"><?php
					if(!empty($user->username))
						echo $user->username;
					else
						echo '-';
				?></td>
				<td class="hikamarket_user_email_value"><?php echo @$user->user_email; ?></td>
<?php
	if(!empty($this->fields)) {
		foreach($this->fields as $field) {
			$namekey = $field->field_namekey;
?>
				<td class="hikamarket_user_<?php echo $namekey; ?>_value"><?php
					echo $this->fieldsClass->show($field, $user->$namekey);
				?></td>
<?php
		}
	}

	if($this->vendor->vendor_id == 1) {
?>
				<td class="hikamarket_user_id_value"><?php echo $user->user_id; ?></td>
<?php
	}
?>
			</tr>
<?php
	$i++;
	$k = 1 - $k;
}
?>
		</tbody>
	</table>
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="listing" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
