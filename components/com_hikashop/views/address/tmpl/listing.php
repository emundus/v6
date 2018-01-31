<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2018 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(empty($this->user_id))
	return;
?>
<div id="hikashop_address_listing">
<?php
echo $this->toolbarHelper->process($this->toolbar, JText::_('ADDRESSES'));
if(!empty($this->addresses)) {
	$ctrl = hikaInput::get()->getCmd('ctrl');
?>
<div class="hikashop_address_listing_div">
<form action="<?php echo hikashop_completeLink($ctrl); ?>" name="hikashop_user_address" method="post">
<?php
	if(!empty($this->address_selector)) {
		$this->type = 'user';
		$this->setLayout('select');
		echo $this->loadTemplate();
	} else {
?>
<table class="hikashop_address_listing_table table table-bordered table-striped">
	<thead>
		<tr>
			<th><?php echo JText::_('HIKA_DEFAULT'); ?></th>
			<th><?php echo JText::_('HIKA_EDIT'); ?></th>
			<th><?php echo JText::_('ADDRESS'); ?></th>
			<th><?php echo JText::_('HIKA_DELETE'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
		global $Itemid;
		$addressClass = hikashop_get('class.address');
		$token = hikashop_getFormToken();
		foreach($this->addresses as $address){
			$this->address =& $address;
?>
		<tr class="hikashop_address_listing_item">
			<td data-title="<?php echo JText::_( 'HIKA_DEFAULT' );?>" class="hikashop_address_listing_item_default" style="width:5%">
				<input type="radio" name="address_default" value="<?php echo $this->address->address_id;?>"<?php
					if($this->address->address_default == 1) {
						echo ' checked="checked"';
					}
				?> onclick="this.form.submit();"/>
			</td>
			<td data-title="<?php echo JText::_( 'HIKA_EDIT' );?>" class="hikashop_address_listing_item_actions" style="width:5%">
<?php
			if(!empty($this->use_popup)) {
				echo $this->popup->display(
					'<img src="'. HIKASHOP_IMAGES.'edit.png" title="'. JText::_('HIKA_EDIT').'" alt="'. JText::_('HIKA_EDIT').'" />',
					'HIKA_EDIT',
					hikashop_completeLink('address&task=edit&address_id='.$address->address_id.'&Itemid='.$Itemid, true),
					'hikashop_edit_address_popup_'.$address->address_id,
					760, 480, '', '', 'link'
				);
			} else {
?>
				<a href="<?php echo hikashop_completeLink('address&task=edit&address_id='.$address->address_id.'&Itemid='.$Itemid); ?>"><img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" title="<?php echo JText::_('HIKA_EDIT'); ?>" alt="<?php echo JText::_('HIKA_EDIT'); ?>"/></a>
<?php
			}
?>
			</td>
			<td data-title="<?php echo JText::_( 'ADDRESS' );?>" class="hikashop_address_listing_item_details">
				<span><?php
					echo $addressClass->displayAddress($this->fields, $address, 'address');
				?></span>
			</td>
			<td data-title="<?php echo JText::_( 'HIKA_DELETE' );?>" class="hikashop_address_listing_item_actions" style="width:5%">
				<a onclick="return confirm('<?php echo JText::_('HIKASHOP_CONFIRM_DELETE_ADDRESS', true); ?>');" href="<?php echo hikashop_completeLink('address&task=delete&address_id='.$address->address_id.'&'.$token.'=1&Itemid='.$Itemid);?>" title="<?php echo JText::_('HIKA_DELETE'); ?>"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="<?php echo JText::_('HIKA_DELETE'); ?>" /></a>
			</td>
		</tr>
<?php
	}
?>
	</tbody>
</table>
<?php } ?>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="ctrl" value="<?php echo $ctrl ?>" />
	<input type="hidden" name="task" value="setdefault" />
	<?php echo JHTML::_('form.token'); ?>
</form>
</div>
<?php
}
?>
</div>
<div class="clear_both"></div>
