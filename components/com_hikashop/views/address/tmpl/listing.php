<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(empty($this->user_id))
	return;
?>
<div id="hikashop_address_listing">
<div class="header hikashop_header_title"><h1><?php echo JText::_('ADDRESSES');?></h1></div>

<div class="toolbar hikashop_header_buttons" id="toolbar" style="float: right;">
	<table class="hikashop_no_border">
		<tr>
			<td>
<?php
	if(!empty($this->address_selector)) {
?>
	<a href="#newAddress" onclick="return window.localPage.newAddr(this, '<?php echo $this->type; ?>');"><span class="icon-32-new" title="<?php echo JText::_('HIKA_NEW'); ?>"></span><?php echo JText::_('HIKA_NEW'); ?></a>
<?php
	} elseif(!empty($this->use_popup)) {
		echo $this->popup->display(
			'<span class="icon-32-new" title="'. JText::_('HIKA_NEW').'"></span>'. JText::_('HIKA_NEW'),
			'HIKA_NEW',
			hikashop_completeLink('address&task=add',true),
			'hikashop_new_address_popup',
			760, 480, '', '', 'link'
		);
	} else {
?>
	<a href="<?php echo hikashop_completeLink('address&task=add'); ?>"><span class="icon-32-new" title="<?php echo JText::_('HIKA_NEW'); ?>"></span><?php echo JText::_('HIKA_NEW'); ?></a>
<?php
	}
?>
			</td>
			<td>
				<a href="<?php echo hikashop_completeLink('user');?>" >
					<span class="icon-32-back" title="<?php echo JText::_('HIKA_BACK'); ?>"></span> <?php echo JText::_('HIKA_BACK'); ?>
				</a>
			</td>
		</tr>
	</table>
</div>
<div style="clear:both"></div>

<?php
if(!empty($this->addresses)) {
	$ctrl = JRequest::getCmd('ctrl');
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
			<td class="hikashop_address_listing_item_default" style="width:5%">
				<input type="radio" name="address_default" value="<?php echo $this->address->address_id;?>"<?php
					if($this->address->address_default == 1) {
						echo ' checked="checked"';
					}
				?> onclick="this.form.submit();"/>
			</td>
			<td class="hikashop_address_listing_item_actions" style="width:5%">
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
			<td class="hikashop_address_listing_item_details">
				<span><?php
					echo $addressClass->displayAddress($this->fields, $address, 'address');
				?></span>
			</td>
			<td class="hikashop_address_listing_item_actions" style="width:5%">
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
