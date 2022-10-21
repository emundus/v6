<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><h2><?php echo JText::_('HIKAM_CONFIRM_VENDOR_DELETION'); ?></h2>
<form action="<?php echo hikamarket::completeLink('vendor'); ?>" method="post" name="adminForm" id="adminForm">
<table class="adminlist pad5 table table-striped table-hover">
	<thead>
		<tr>
			<th class="hikamarket_vendor_num_title title titlenum"><?php echo JText::_('HIKA_NUM');?></th>
			<th class="hikamarket_vendor_name_title title"><?php echo JText::_('HIKA_NAME'); ?></th>
			<th class="hikamarket_vendor_published_title title titletoggle"><?php echo JText::_('HIKA_PUBLISHED'); ?></th>
			<th class="hikamarket_vendor_products_title title titlenum"><?php echo JText::_('HIKAM_PRODUCTS'); ?></th>
			<th class="hikamarket_vendor_orders_title title titlenum"><?php echo JText::_('HIKAM_ORDERS'); ?></th>
			<th class="hikamarket_vendor_userss_title title titlenum"><?php echo JText::_('HIKAM_USERS'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$i = 0;
$k = 0;
foreach($this->vendors as $vendor) {
?>
		<tr>
			<td class="hikamarket_vendor_num_value" align="center"><?php
				echo $i+1;
			?><input type="hidden" value="<?php echo (int)$vendor->vendor_id; ?>" name="cid[]"/></td>
			<td class="hikamarket_vendor_name_value"><?php
				echo $this->escape($vendor->vendor_name);
			?></td>
			<td class="hikamarket_vendor_publish_value" align="center"><?php
				echo $this->toggleClass->display('activate', $vendor->vendor_published);
			?></td>
			<td class="hikamarket_vendor_products_value" align="center"><?php
				echo (int)@$vendor->products;
			?></td>
			<td class="hikamarket_vendor_orders_value" align="center"><?php
				echo (int)@$vendor->orders;
			?></td>
			<td class="hikamarket_vendor_users_value" align="center"><?php
				echo (int)@$vendor->users;
			?></td>
		</tr>
<?php
	$i++;
	$k = 1 - $k;
}
?>
	</tbody>
</table>
	<input type="hidden" name="confirm" value="<?php echo $this->confirm_value; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="<?php echo count($this->vendors); ?>" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php
$doc = JFactory::getDocument();
$doc->addScriptDeclaration('window.hikashop.ready(function(){Joomla.isChecked(true);});');
