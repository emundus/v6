<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(!isset($this->vendor->vendor_id) || $this->vendor->vendor_id > 1) { ?>
<div class="iframedoc" id="iframedoc"></div>
<div id="hikashop_backend_tile_edition">
<?php if((isset($this->vendor->vendor_id) && $this->vendor->vendor_id > 1) || (!isset($this->vendor->vendor_id) && hikamarket::level(1))) { ?>
	<div id="hikamarket_vendor_edition_header">
		<ul class="hika_tabs" rel="tabs:hikamarket_product_edition_tab_">
			<li class="active"><a href="#vendor" rel="tab:1" onclick="return window.hikashop.switchTab(this);"><?php echo JText::_('HIKA_VENDOR'); ?></a></li>
<?php if(hikamarket::level(1)) { ?>
			<li><a href="#acl" rel="tab:2" onclick="return window.hikashop.switchTab(this);"><?php echo JText::_('ACL'); ?></a></li>
<?php } ?>
<?php if(isset($this->vendor->vendor_id) && $this->vendor->vendor_id > 1) { ?>
			<li><a href="#stats" rel="tab:3" onclick="return window.hikashop.switchTab(this);"><?php echo JText::_('STATISTICS'); ?></a></li>
<?php } ?>
		</ul>
		<div style="clear:both"></div>
<?php
	}
?>
	</div>
<form action="<?php echo hikamarket::completeLink('vendor'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<?php } else { ?>
<div id="hikashop_backend_tile_edition">
<?php } ?>
<?php
JFactory::getDocument()->addScriptDeclaration('
	window.vendorMgr = { cpt:{} };
	window.hikashop.ready(function(){window.hikashop.dlTitle("adminForm");});
');
?>
<?php if(isset($this->vendor->vendor_id) && $this->vendor->vendor_id > 1) { ?>
	<!-- Product edition : main tab -->
	<div id="hikamarket_product_edition_tab_1">
<?php } ?>
	<div class="hk-row-fluid">

	<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php
			echo JText::_('MAIN_INFORMATION');
		?></div>
		<dl class="hika_options">

			<dt class="hikamarket_vendor_name"><label for="data[vendor][vendor_name]"><?php echo JText::_('HIKA_NAME'); ?></label></dt>
			<dd class="hikamarket_vendor_name input_large">
				<input type="text" name="data[vendor][vendor_name]" id="data[vendor][vendor_name]" value="<?php echo $this->escape(@$this->vendor->vendor_name); ?>" />
			</dd>

			<dt class="hikamarket_vendor_email"><label for="data[vendor][vendor_email]"><?php echo JText::_('HIKA_EMAIL'); ?></label></dt>
			<dd class="hikamarket_vendor_email input_large">
				<input type="text" name="data[vendor][vendor_email]" id="data[vendor][vendor_email]" value="<?php echo $this->escape(@$this->vendor->vendor_email); ?>" />
			</dd>

<?php
if(!isset($this->vendor->vendor_id) || $this->vendor->vendor_id > 1) {
?>
			<dt class="hikamarket_vendor_admin"><label for="data_vendor_vendor_admin_id_text"><?php echo JText::_('HIKA_ADMINISTRATOR'); ?></label></dt>
			<dd class="hikamarket_vendor_admin"><?php
		echo $this->nameboxType->display(
			'data[vendor][vendor_admin_id]',
			@$this->vendor_admin->user_id,
			hikamarketNameboxType::NAMEBOX_SINGLE,
			'user',
			array(
				'delete' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
			?></dd>

			<dt class="hikamarket_vendor_published"><label for="data[vendor][vendor_published]"><?php echo JText::_('HIKA_PUBLISHED'); ?></label></dt>
			<dd class="hikamarket_vendor_published"><?php
				echo JHTML::_('hikaselect.booleanlist', 'data[vendor][vendor_published]' , '', @$this->vendor->vendor_published);
			?></dd>

			<dt class="hikamarket_vendor_currency"><label for="datavendorvendor_currency_id"><?php echo JText::_('CURRENCY'); ?></label></dt>
			<dd class="hikamarket_vendor_currency"><?php
				echo $this->currencyType->display("data[vendor][vendor_currency_id]", @$this->vendor->vendor_currency_id);
			?></dd>

<?php if($this->config->get('allow_zone_vendor', 0)) { ?>
			<dt class="hikamarket_vendor_zone"><label for="data_vendor_vendor_zone_text"><?php echo JText::_('ZONE'); ?></label></dt>
			<dd><?php
				echo $this->nameboxType->display(
					'data[vendor][vendor_zone_id]',
					@$this->vendor->vendor_zone_namekey,
					hikamarketNameboxType::NAMEBOX_SINGLE,
					'zone',
					array(
						'delete' => true,
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>'
					)
				);
			?></dd>
<?php } ?>

<?php
	if(file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php')) {
		include_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php');
		if(class_exists('MultisitesHelperUtils') && method_exists('MultisitesHelperUtils', 'getComboSiteIDs')) {
			$comboSiteIDs = MultisitesHelperUtils::getComboSiteIDs(@$this->vendor->vendor_site_id, 'data[vendor][vendor_site_id]', JText::_('SELECT_A_SITE'));
			if(!empty($comboSiteIDs)) {
?>
			<dt class="hikamarket_vendor_siteid"><?php echo JText::_('SITE_ID'); ?></dt>
			<dd class="hikamarket_vendor_siteid"><?php echo $comboSiteIDs; ?></dd>
<?php
			}
		}
	}
?>

<?php
} // Vendor_id > 1
?>

			<dt class="hikamarket_vendor_templateid"><label for="data_vendor_vendor_template_id_text"><?php echo JText::_('VENDOR_PRODUCT_TEMPLATE'); ?></label></dt>
			<dd class="hikamarket_vendor_templateid"><?php
				echo $this->nameboxType->display(
					'data[vendor][vendor_template_id]',
					@$this->vendor->vendor_template_id,
					hikamarketNameboxType::NAMEBOX_SINGLE,
					'product_template',
					array(
						'delete' => true,
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
					)
				);
			?></dd>

			<dt><label><?php echo JText::_('HIKAM_VENDOR_IMAGE'); ?></label></dt>
			<dd>
<?php
	$options = array(
		'upload' => true,
		'gallery' => true,
		'upload_base_url' => 'index.php?option=com_hikamarket&ctrl=upload',
		'text' => JText::_('HIKAM_VENDOR_IMAGE_EMPTY_UPLOAD'),
		'uploader' => array('vendor', 'vendor_image'),
		'vars' => array('vendor_id' => (int)@$this->vendor->vendor_id)
	);

	$content = '';
	if(!empty($this->vendor->vendor_image)) {
		$params = new stdClass();
		$params->file_path = @$this->vendor->vendor_image;
		$params->field_name = 'data[vendor][vendor_image]';
		$params->uploader_id = 'hikamarket_vendor_image';
		$params->delete = true;
		$js = '';
		$content = hikamarket::getLayout('uploadmarket', 'image_entry', $params, $js);
	}

echo $this->uploaderType->displayImageSingle('hikamarket_vendor_image', $content, $options);
?>
				<input type="hidden" value="1" name="data_vendor_image"/>
			</dd>

			<dt class="hikamarket_vendor_alias"><label for="data[vendor][vendor_alias]"><?php echo JText::_('HIKA_ALIAS'); ?></label></dt>
			<dd class="hikamarket_vendor_alias input_large">
				<input type="text" name="data[vendor][vendor_alias]" id="data[vendor][vendor_alias]" value="<?php echo $this->escape(@$this->vendor->vendor_alias); ?>" />
			</dd>

		</dl>
	</div></div>

<?php
	if(!empty($this->extraFields['vendor'])) {
?>
	<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php
			echo JText::_('FIELDS');
		?></div>
		<dl id="hikamarket_vendor_fields" class="hika_options">
<?php
		foreach($this->extraFields['vendor'] as $fieldName => $oneExtraField) {
?>
		<dl class="hika_options" id="hikamarket_vendor_<?php echo $oneExtraField->field_namekey; ?>" style="margin:0;padding:0;">
			<dt class="hikamarket_vendor_<?php echo $fieldName; ?>"><label for="<?php echo $fieldName; ?>"><?php
				echo $this->fieldsClass->getFieldName($oneExtraField);
				if(!empty($oneExtraField->field_required))
					echo ' *';
			?></label></dt>
			<dd class="hikamarket_vendor_<?php echo $fieldName; ?>"><?php
				$onWhat = 'onchange';
				if($oneExtraField->field_type == 'radio')
					$onWhat = 'onclick';
				$oneExtraField->field_required = false;
				echo $this->fieldsClass->display(
					$oneExtraField,
					@$this->vendor->$fieldName,
					'data[vendor]['.$fieldName.']',
					false,
					' ' . $onWhat . '="hikashopToggleFields(this.value,\''.$fieldName.'\',\'vendor\',0,\'hikamarket_\');"',
					false,
					$this->extraFields['vendor'],
					$this->vendor
				);
			?></dd>
		</dl>
<?php
		}
?>
		</dl>
	</div></div>
<?php
	}
?>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php
			echo JText::_('HIKA_DESCRIPTION');
		?></div>
<?php
		$this->editor->content = @$this->vendor->vendor_description;
		$this->editor->name = 'vendor_description';
		$ret = $this->editor->display();
		if($this->editor->editor == 'codemirror')
			echo str_replace(array('(function() {'."\n",'})()'."\n"),array('window.hikashop.ready(function(){', '});'), $ret);
		else
			echo $ret;
?>
		<div style="clear:both"></div>
	</div></div>

	<div class="hkc-xl-clear"></div>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php
			echo JText::_('USERS');
		?></div>
<?php
	$this->setLayout('users');
	echo $this->loadTemplate();
?>
	</div></div>

<?php if(hikamarket::level(1) && (!isset($this->vendor->vendor_id) || $this->vendor->vendor_id > 1)) { ?>
	<div class="hkc-xl-8 hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php
			echo JText::_('VENDOR_FEES');
		?></div>
<?php
	$this->setLayout('fees');
	echo $this->loadTemplate();
?>
	</div></div>
<?php } ?>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php
			echo JText::_('VENDOR_OPTIONS');
		?></div>
<?php
	$this->setLayout('options');
	echo $this->loadTemplate();
?>
	</div></div>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php
			echo JText::_('HIKAM_VENDOR_LOCATION');
		?></div>
<dl class="hika_options">
	<dt class="hikamarket_vendor_location"><label><?php echo JText::_('HIKAM_VENDOR_LOCATION_LAT'); ?></label></dt>
	<dd class="hikamarket_vendor_location">
		<input type="text" id="vendor_location_lat" name="data[vendor][vendor_location_lat]" onchange="window.localPage.updateMapMarket();" value="<?php echo hikamarket::toFloat(@$this->vendor->vendor_location_lat); ?>" />
	</dd>
	<dt class="hikamarket_vendor_location"><label><?php echo JText::_('HIKAM_VENDOR_LOCATION_LONG'); ?></label></dt>
	<dd class="hikamarket_vendor_location">
		<input type="text" id="vendor_location_lon" name="data[vendor][vendor_location_long]" onchange="window.localPage.updateMapMarket();" value="<?php echo hikamarket::toFloat(@$this->vendor->vendor_location_long); ?>" />
	</dd>
</dl>
<?php hikamarket::loadJslib('leaflet'); ?>
		<div id="vendor_map" class="map map-vendor" style="height:200px;"></div>
		<div class="">
			<a href="#search" class="hikabtn hikabtn-primary" onclick="return window.localPage.searchLocation(this);"><i class="fas fa-map-marked-alt"></i> <?php echo JText::_('HIKAM_MAP_SEARCH'); ?></a>
			<a href="#clear" class="hikabtn hikabtn-warning" onclick="return window.localPage.clearLocation(this);"><i class="fas fa-times-circle"></i> <?php echo JText::_('HIKAM_MAP_CLEAR'); ?></a>
		</div>
<?php
$js = '
if(!window.localPage) window.localPage = {};
window.hikashop.ready(function(){
	var w = window, lp = w.localPage,
		osmUrl = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
		osmAttrib = \'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors\',
		osm = L.tileLayer(osmUrl, {maxZoom: 18, attribution: osmAttrib});
	lp.map = L.map("vendor_map").setView(['.hikamarket::toFloat(@$this->vendor->vendor_location_lat) .', '.hikamarket::toFloat(@$this->vendor->vendor_location_long) .'], 15).addLayer(osm);
	lp.marker = null;';
if(!empty($this->vendor->vendor_location_lat) || !empty($this->vendor->vendor_location_long)) {
	$js .= '
	lp.marker = L.marker(['.hikamarket::toFloat($this->vendor->vendor_location_lat).', '.hikamarket::toFloat($this->vendor->vendor_location_long).'])
		.addTo(lp.map);';
} else {
	$js .= '
	lp.map.setView([0, 0], 0);';
}
$js .= '
	lp.map.on("click",function(e){
		window.localPage.setMapMarket(e.latlng);
	});
});
window.localPage.clearLocation = function(btn) {
	var d = document, lp = window.localPage, input = d.getElementById("vendor_location_lat");
	if(input) input.value = "";
	input = d.getElementById("vendor_location_lon");
	if(input) input.value = "";
	if(lp.marker) marker.remove();
	lp.marker = null;
	lp.map.setView([0, 0], 0);
	btn.blur();
	return false;
};
window.localPage.searchLocation = function(btn) {
	var w = window, o = w.Oby, el = null, url = "https://nominatim.openstreetmap.org/search?format=json&limit=1";
	el = document.getElementById("vendor_address_street");
	if(el && el.value) url += "&street=" + encodeURIComponent(el.value);
	el = document.getElementById("vendor_address_city");
	if(el && el.value) url += "&city=" + encodeURIComponent(el.value);
	o.xRequest(url,null,function(xhr){
		if(!xhr.responseText || xhr.status != 200) return;
		var ret = o.evalJSON(xhr.responseText);
		if(!ret || !ret[0]) return;
		window.localPage.setMapMarket(ret[0]);
	});
	btn.blur();
	return false;
};
window.localPage.setMapMarket = function(obj) {
	var d = document, lp = window.localPage, lon = obj.lon ? obj.lon: obj.lng;
	if(!lp.marker)
		lp.marker = L.marker([obj.lat, lon]).addTo(lp.map);
	else
		lp.marker.setLatLng([obj.lat, lon]);
	var zoom = lp.map.getZoom();
	if(zoom <= 1) zoom = 15;
	lp.map.setView([obj.lat, lon], zoom);
	var input = d.getElementById("vendor_location_lat");
	if(input) input.value = obj.lat;
	input = d.getElementById("vendor_location_lon");
	if(input) input.value = lon;
};
window.localPage.updateMapMarket = function() {
	var d = document,
		obj = {lat: 0.0, lon: 0.0},
		input = d.getElementById("vendor_location_lat"),
		val = NaN;
	if(input) val = parseFloat(input.value);
	if(!isNaN(val)) obj.lat = val;
	input = d.getElementById("vendor_location_lon");
	val = NaN;
	if(input) val = parseFloat(input.value);
	if(!isNaN(val)) obj.lon = val;
	window.localPage.setMapMarket(obj);
};
';
JFactory::getDocument()->addScriptDeclaration($js);
?>
	</div></div>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php
			echo JText::_('HIKASHOP_CHECKOUT_TERMS');
		?></div>
<?php
		$this->editor->content = @$this->vendor->vendor_terms;
		$this->editor->name = 'vendor_terms';
		$ret = $this->editor->display();
		if($this->editor->editor == 'codemirror')
			echo str_replace(array('(function() {'."\n",'})()'."\n"),array('window.hikashop.ready(function(){', '});'), $ret);
		else
			echo $ret;
?>
		<div style="clear:both"></div>
	</div></div>

	</div>
<?php if(isset($this->vendor->vendor_id) && $this->vendor->vendor_id > 1) { ?>
	</div>
<?php } ?>

<?php if(hikamarket::level(1) && (!isset($this->vendor->vendor_id) || $this->vendor->vendor_id > 1)) { ?>
	<div id="hikamarket_product_edition_tab_2" style="display:none;"><div class="hk-container-fluid">

	<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('GROUP'); ?></div>
<?php
		$vendor_group = '';
		if(isset($this->vendor->vendor_group))
			$vendor_group = $this->vendor->vendor_group;
		echo $this->joomlaAcl->display('vendor_group', $vendor_group, false, false);
?>
	</div></div>

	<div class="hkc-xl-4 hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('ACL'); ?></div>
<?php
		$acl = '';
		if(!isset($this->vendor->vendor_acl))
			$acl = '';
		else
			$acl = $this->vendor->vendor_acl;
		echo $this->marketaclType->display('vendor_access', $acl, 'vendor_access_inherit');
?>
	</div></div>

	</div></div>
<?php } ?>

	<div style="clear:both" class="clr"></div>
<?php if(!isset($this->vendor->vendor_id) || $this->vendor->vendor_id > 1) { ?>
	<input type="hidden" name="cid[]" value="<?php echo @$this->vendor->vendor_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php } ?>

<?php if(isset($this->vendor->vendor_id) && $this->vendor->vendor_id > 1) { ?>
	<div id="hikamarket_product_edition_tab_3" style="display:none;"><div class="hk-container-fluid">

	<div class="hkc-xl-12 hkc-lg-12 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('ORDERS'); ?> - <span class="hk-label hk-label-blue"><?php echo $this->orders_count; ?></span></div>
<?php
	$this->setLayout('orders');
	echo $this->loadTemplate();
?>
	</div></div>

	<div class="hkc-xl-6 hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('VENDOR_INVOICES'); ?> - <span class="hk-label hk-label-blue"><?php echo $this->invoices_count; ?></span></div>
<?php
	$this->setLayout('invoices');
	echo $this->loadTemplate();
?>
	</div></div>

	<div class="hkc-xl-6 hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('PRODUCTS'); ?> - <span class="hk-label hk-label-blue"><?php echo $this->products_count; ?></span></div>
<?php
	$this->setLayout('products');
	echo $this->loadTemplate();
?>
	</div></div>

	</div></div>
<?php } ?>
</div>
