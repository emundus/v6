<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table id="hikamarket_vendor_users" class="adminlist pad5 table table-striped table-hover" style="width:100%">
	<thead>
		<tr>
			<th class="hikamarket_user_id_title title"><?php
				echo JText::_('ID');
			?></th>
			<th class="hikamarket_user_name_title title"><?php
				echo JText::_('HIKA_NAME');
			?></th>
			<th class="hikamarket_user_email_title title"><?php
				echo JText::_('HIKA_EMAIL');
			?></th>
			<th class="hikamarket_user_acl_title title" style="width:3%"><?php
				echo JText::_('HIKAM_ACL');
			?></th>
			<th class="hikamarket_user_icon_title title" style="width:2%"><?php
				echo hikamarket::tooltip(JText::_('ADD'), '', '', '<button class="btn" onclick="return window.vendorMgr.vendor_toggleUser(this);" type="button" style="margin:0px;"><img style="margin:0px;" src="'.HIKASHOP_IMAGES.'add.png" style="vertical-align:middle"/></button>', '', 0);
			?></th>
		</tr>
	</thead>
<?php
$k = 0;
if(!empty($this->users)) {
	foreach($this->users as $user) {
?>
	<tr class="row<?php echo $k; ?>" id="vendor_users_<?php echo $user->id; ?>">
		<td align="center"><?php echo $user->user_id;?></td>
		<td><?php echo $user->name;?></td>
		<td><?php echo $user->email;?></td>
		<td align="center"><?php echo $this->marketaclType->displayButton('user['.$user->user_id.'][user_access]', @$user->user_vendor_access); ?></td>
		<td align="center">
			<a href="#" onclick="hikamarket.deleteRow(this); return false;"><img src="<?php echo HIKASHOP_IMAGES;?>delete.png" alt="<?php echo JText::_('DELETE'); ?>"/></a>
			<input type="hidden" name="data[users][]" value="<?php echo $user->user_id;?>"/>
		</td>
	</tr>
<?php
		$k = 1 - $k;
	}
}
?>
	<!-- Template line -->
	<tr id="hikamarket_users_tpl" class="row<?php echo $k; ?>" style="display:none;">
		<td align="center">{id}</td>
		<td>{name}</td>
		<td>{email}</td>
		<td align="center"><?php echo $this->marketaclType->displayButton('user[{id}][user_access]', 'all'); ?></td>
		<td align="center">
			<a href="#" onclick="hikamarket.deleteRow(this); return false;"><img src="<?php echo HIKASHOP_IMAGES;?>delete.png" alt="<?php echo JText::_('DELETE'); ?>"/></a>
			<input type="hidden" name="{input_name}" value="{id}"/>
		</td>
	</tr>
</table>
	<div style="display:none;" id="hikamarket_selector_vendor_user_line">
		<?php
			echo $this->nameboxType->display(
				'',
				'',
				hikamarketNameboxType::NAMEBOX_MULTIPLE,
				'user',
				array(
					'delete' => true,
					'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
					'id' => 'hikamarket_add_users'
				)
			);
		?>
		<div style="clear:both;margin-top:4px;"></div>
		<div style="float:right">
			<button onclick="return window.vendorMgr.vendor_addUser(this);" class="btn btn-success"><img src="<?php echo HIKASHOP_IMAGES; ?>plus.png" alt="" style="vertical-align:middle;"/> <?php echo JText::_('HIKAM_ADD_VENDOR_USERS'); ;?></button>
		</div>
		<button onclick="return window.vendorMgr.vendor_toggleUser(this);" class="btn btn-danger"><img src="<?php echo HIKASHOP_IMAGES; ?>cancel.png" alt="" style="vertical-align:middle;"/> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
		<div style="clear:both"></div>
	</div>
<script type="text/javascript">
if(!window.vendorMgr) window.vendorMgr = {};
window.vendorMgr.vendor_toggleUser = function(el) {
	var d = document, element = d.getElementById('hikamarket_selector_vendor_user_line');
	if(element)
		element.style.display = (element.style.display == 'none' ? '' : 'none');
	if(element && element.style.display == 'none') {
		var box = window.oNameboxes['hikamarket_add_users'];
		if(box)
			box.clear();
	}
	return false;
};
window.vendorMgr.vendor_addUser = function(el, id) {
	var box = window.oNameboxes['hikamarket_add_users'];
	if(!box)
		return window.vendorMgr.vendor_toggleUser(el);
	var values = box.get(), htmlData = null;
	box.clear();
	if(values && values.length > 0) {
		for(var i = 0; i < values.length; i++) {
			var email = '';
			if(box.data && box.data[ values[i].value ]) {
				try {
					email = box.data[ values[i].value ].user_email;
				} catch(e) {}
			}
			htmlData = {
				'input_name': 'data[users][]',
				'id': values[i].value,
				'name': values[i].name,
				'email': email,
			};
			window.hikamarket.dupRow('hikamarket_users_tpl', htmlData);
		}
	}
	return window.vendorMgr.vendor_toggleUser(el);
};
</script>
