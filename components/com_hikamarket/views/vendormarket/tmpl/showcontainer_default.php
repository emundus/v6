<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikamarket_vendor_page" class="hikamarket_vendor_page">
	<div id="hikamarket_vendor_top" class="hikamarket_vendor_top">
		<h1><?php echo $this->vendor->vendor_name; ?></h1>

<div class="hk-row-fluid">
<?php
	if(!empty($this->vendor_image->url) || !empty($this->vendor->extraData->afterImage)) {
?>
	<div class="hkc-md-3">
		<div class="hikamarket_vendor_image">
<?php
		if(!empty($this->vendor_image->url)) {
?>
			<img src="<?php echo $this->vendor_image->url; ?>" alt=""/>
<?php
		}
?>
		</div>
<?php if(!empty($this->vendor->extraData->afterImage)) { echo implode("\r\n",$this->vendor->extraData->afterImage); } ?>
	</div>
	<div class="hkc-md-9">
<?php
	} else {
?>
	<div class="hkc-md-12">
<?php
	}
?>
		<div class="hikamarket_vendor_contact">
<?php
	if($this->config->get('display_vendor_contact', 0)) {
		echo $this->popup->display(
			'<span>'.JText::_('CONTACT_VENDOR').'</span>',
			'CONTACT_VENDOR',
			hikamarket::completeLink('shop.product&task=contact&target=vendor&vendor_id='.$this->vendor->vendor_id, true),
			'hikamarket_contactvendor_popup',
			array(
				'width' => 750, 'height' => 460, 'type' => 'link',
				'attr' => 'class="hikashop_cart_button hikabtn hikabtn-primary"'
			)
		);
	}
?>
		</div>
		<div class="hikamarket_vendor_vote">
<?php
	if($this->config->get('display_vendor_vote',0)) {
		$js = '';
		echo hikamarket::getLayout('shop.vote', 'mini', $this->voteParams, $js);
	}
?>
		</div>
<?php if(!empty($this->vendor->extraData->beforeFields)) { echo implode("\r\n",$this->vendor->extraData->beforeFields); } ?>
		<div class="hikamarket_vendor_fields">
<?php
	if(!empty($this->extraFields['vendor'])) {
?>
			<dl class="hikam_options">
<?php
		foreach($this->extraFields['vendor'] as $fieldName => $oneExtraField) {
?>
				<dt><span id="hikamarket_vendor_custom_name_<?php echo $oneExtraField->field_id;?>" class="hikamarket_vendor_custom_name"><?php
					echo $this->fieldsClass->trans($oneExtraField->field_realname);
				?></span></dt>
				<dd><span id="hikamarket_vendor_custom_value_<?php echo $oneExtraField->field_id;?>" class="hikamarket_vendor_custom_value"><?php
					echo $this->fieldsClass->show($oneExtraField, $this->vendor->$fieldName);
				?></span></dd>
<?php
		}
?>
			</dl>
<?php
	}
?>
		</div>
	</div>
	<div class="hkc-md-12">
<?php if(!empty($this->vendor->extraData->beforeDesc)) { echo implode("\r\n",$this->vendor->extraData->beforeDesc); } ?>
		<div id="hikamarket_vendor_description" class="hikamarket_vendor_description"><?php
			if($this->config->get('vendor_description_content_plugins', 0))
				echo $this->secure($this->vendor->vendor_description);
			else
				echo JHTML::_('content.prepare', $this->vendor->vendor_description);
		?></div>
<?php if(!empty($this->vendor->extraData->afterDesc)) { echo implode("\r\n",$this->vendor->extraData->afterDesc); } ?>
	</div>
<?php
	if(!empty($this->vendor->vendor_location_lat) && !empty($this->vendor->vendor_location_long)) {
		hikamarket::loadJslib('leaflet');
?>
	<div class="hkc-md-12">
		<div id="vendor_map" class="hikamarket_vendor_map" style="height:200px;"></div>
<script type="text/javascript">
var osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', osmAttrib = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors', osm = L.tileLayer(osmUrl, {maxZoom: 18, attribution: osmAttrib});
var map = L.map('vendor_map').setView([<?php echo hikamarket::toFloat($this->vendor->vendor_location_lat); ?>, <?php echo hikamarket::toFloat($this->vendor->vendor_location_long); ?>], 15).addLayer(osm);
var marker = L.marker([<?php echo hikamarket::toFloat($this->vendor->vendor_location_lat); ?>, <?php echo hikamarket::toFloat($this->vendor->vendor_location_long); ?>]).addTo(map);
</script>
	</div>
<?php
	}
?>
</div>

<?php if($this->config->get('display_vendor_vote', 0)) { ?>
	<div id="hikashop_comment_form" class="hikamarket_vendor_vote"><?php
		$js = '';
		echo hikamarket::getLayout('shop.vote', 'listing', $this->voteParams, $js);
		echo hikamarket::getLayout('shop.vote', 'form', $this->voteParams, $js);
	?></div>
<?php } ?>
<?php if(!empty($this->vendor->extraData->bottom)) { echo implode("\r\n",$this->vendor->extraData->bottom); } ?>
	<div style="clear:both"></div>
	<div class="hikamarket_submodules" id="hikamarket_submodules" style="clear:both">
<?php
if(!empty($this->modules)) {
	hikaInput::get()->set('force_using_filters', 1);
	foreach($this->modules as $module) {
		echo JModuleHelper::renderModule($module);
	}
}
?>
	</div>
<?php if(!empty($this->vendor->extraData->afterModules)) { echo implode("\r\n",$this->vendor->extraData->afterModules); } ?>
	</div>
</div>
