<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$ajax = false;
if(!empty($this->upload_ajax))
	$ajax = true;

$product_type = (!empty($this->params->product_type) && $this->params->product_type == 'variant') ? 'variant' : 'product';
$uploader_id = empty($this->editing_variant) ? 'hikashop_product_image' : 'hikashop_product_variant_image';
$upload = hikashop_acl('product/edit/images/upload');
$options = array(
	'classes' => array(
		'mainDiv' => 'hikashop_main_image_div',
		'contentClass' => 'hikashop_product_images',
		'firstImg' => 'hikashop_product_main_image_thumb',
		'otherImg' => 'hikashop_small_image_div',
		'btn_add' => 'fa fa-plus',
		'btn_upload' => 'fa fa-upload'
	),
	'upload' => $upload,
	'gallery' => $upload,
	'text' => ($upload ? JText::_('HIKA_PRODUCT_IMAGES_EMPTY_UPLOAD') : JText::_('HIKA_PRODUCT_IMAGES_EMPTY')),
	'tooltip' => true,
	'uploader' => array('product', 'product_image'),
	'vars' => array(
		'product_id' => @$this->product->product_id,
		'product_type' => $product_type,
		'file_type' => 'product'
	),
	'buttons' => array(
		array(
			'tooltip' => JText::_('HIKA_ENTER_IMAGE_PATH'),
			'class' => 'fa fa-link',
			'text' => 'HIKA_ENTER_IMAGE_PATH',
			'id' => $uploader_id.'_urlpopup',
			'url' => hikashop_completeLink('product&task=selectimage&pathonly=1&pid='.@$this->product->product_id,true),
			'onclick' => 'return window.hkUploaderList[\''.$uploader_id.'\'].genericButtonClick(this);',
		),
	),
	'ajax' => $ajax
);

$content = array();
if(!empty($this->product->images)) {
	foreach($this->product->images as $k => $image) {
		$image->product_id = $this->product->product_id;
		$image->product_type = $product_type;
		$this->params = $image;
		$content[] = $this->loadTemplate('image_entry');
	}
}

echo $this->uploaderType->displayImageMultiple($uploader_id, $content, $options);

echo $this->popup->display('',JText::_('EDIT_IMAGE'),'','hikashop_product_image_edit',750, 460,'', '', 'link');
?>
<script type="text/javascript">
window.productMgr.editImage = function(el, id, type) {
	var w = window, t = w.hikashop, href = null, n = el;
	if(type === undefined || type == '') type = 'product';
	if(type == 'variant') type = 'product_variant';
	if(!w.hkUploaderList['hikashop_'+type+'_image']) return false;
	if(w.hkUploaderList['hikashop_'+type+'_image'].imageClickBlocked) return false; // Firefox trick
	t.submitFct = function(data) {};
	if(el.getAttribute('rel') == null) {
		href = el.href;
		n = 'hikashop_product_image_edit';
	}
	t.openBox(n,href);
	return false;
}
window.productMgr.delImage = function(el, type) {
	if(type === undefined || type == '') type = 'product';
	if(type == 'variant') type = 'product_variant';
	if(!window.hkUploaderList['hikashop_'+type+'_image']) return false;
	return window.hkUploaderList['hikashop_'+type+'_image'].delImage(el);
}
</script>
