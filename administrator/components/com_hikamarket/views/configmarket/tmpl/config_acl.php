<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_backend_tile_edition">
	<div class="hk-container-fluid">

	<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('HIKAM_DEFAULT_STORE_ACCESS'); ?></div>
<?php
	echo $this->marketaclType->display('config[store_default_access]', $this->config->get('store_default_access', '*'));
?>
	</div></div>

<?php if(hikamarket::level(1)) { ?>
	<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('MAIN_VENDOR_ACL'); ?></div>
<?php
		$acl = '';
		if(!isset($this->vendor->vendor_acl))
			$acl = '';
		else
			$acl = $this->vendor->vendor_acl;
		echo $this->marketaclType->display('vendor_access', $acl, 'vendor_access_inherit');
?>
		</dl>
	</div></div>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('MAIN_VENDOR_GROUP'); ?></div>
<?php
		$vendor_group = '';
		if(isset($this->vendor->vendor_group))
			$vendor_group = $this->vendor->vendor_group;
		echo $this->joomlaaclType->display('vendor_group', $vendor_group, false, false);
?>
	</div></div>
<?php } ?>

	</div>
	<div style="clear:both" class="clr"></div>
</div>
