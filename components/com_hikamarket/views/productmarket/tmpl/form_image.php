<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$ajax = false;
if(!empty($this->upload_ajax))
	$ajax = true;
$product_type = (@$this->params->product_type == 'variant' || @$this->product->product_type == 'variant') ? 'variant' : 'product';
$uploader_id = empty($this->editing_variant) ? 'hikamarket_product_image' : 'hikamarket_product_variant_image';

$upload = $this->aclEdit('images/upload');
$options = array(
	'classes' => array(
		'mainDiv' => 'hikamarket_main_image_div',
		'contentClass' => 'hikamarket_product_images',
		'firstImg' => 'hikamarket_product_main_image_thumb',
		'otherImg' => 'hikamarket_small_image_div',
		'btn_add' => 'fas fa-plus',
		'btn_upload' => 'fas fa-upload'
	),
	'upload' => $upload,
	'upload_base_url' => 'index.php?option=com_hikamarket&ctrl=upload',
	'gallery' => $upload,
	'tooltip' => true,
	'text' => ($upload ? JText::_('HIKAM_PRODUCT_IMAGES_EMPTY_UPLOAD') : JText::_('HIKAM_PRODUCT_IMAGES_EMPTY')),
	'uploader' => array('product', 'product_image'),
	'vars' => array(
		'product_id' => $this->product->product_id,
		'product_type' => $product_type,
		'file_type' => 'product'
	),
	'buttons' => array(),
	'ajax' => $ajax
);

if($this->aclEdit('images/link')) {
	$options['buttons']['image_link' ] = array(
		'tooltip' => JText::_('HIKA_ENTER_IMAGE_PATH'),
		'class' => 'fas fa-link',
		'text' => 'HIKA_ENTER_IMAGE_PATH',
		'id' => $uploader_id.'_urlpopup',
		'url' => hikamarket::completeLink('product&task=image&image_link=1&pathonly=1&pid='.(int)$this->product->product_id, true),
		'onclick' => 'return window.hkUploaderList[\''.$uploader_id.'\'].genericButtonClick(this);',
	);
}

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

echo $this->popup->display('','MARKET_EDIT_IMAGE','','hikamarket_product_image_edit',750, 460,'', '', 'link');
?>
<script type="text/javascript">
window.productMgr.editImage = function(el, id, type) {
	var w = window, t = w.hikamarket, href = null, n = el;
	if(type === undefined || type == '') type = 'product';
	if(type == 'variant') type = 'product_variant';
	if(!w.hkUploaderList['hikamarket_'+type+'_image']) return false;
	if(w.hkUploaderList['hikamarket_'+type+'_image'].imageClickBlocked) return false; // Firefox trick
	t.submitFct = function(data) {};
	if(el.getAttribute('rel') == null) {
		href = el.href;
		n = 'hikamarket_product_image_edit';
	}
	t.openBox(n,href,(el.getAttribute('rel') == null));
	return false;
};
window.productMgr.delImage = function(el, type) {
	if(type === undefined || type == '') type = 'product';
	if(type == 'variant') type = 'product_variant';
	if(!window.hkUploaderList['hikamarket_'+type+'_image']) return false;
	window.hkUploaderList['hikamarket_'+type+'_image'].delImage(el);
	return false;
};
</script>
