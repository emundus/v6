<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$ajax = false;
if(!empty($this->upload_ajax))
	$ajax = true;
$options = array(
	'classes' => array(
		'mainDiv' => 'hikashop_main_image_div',
		'contentClass' => 'hikamarket_category_image',
		'btn_add' => 'fa fa-plus',
		'btn_upload' => 'fa fa-upload'
	),
	'upload' => true,
	'tooltip' => true,
	'upload_base_url' => 'index.php?option=com_hikashop&ctrl=upload',
	'gallery' => true,
	'text' => JText::_('HIKA_CATEGORY_IMAGE_EMPTY_UPLOAD'),
	'uploader' => array('category', 'category_image'),
	'vars' => array(
		'category_id' => @$this->element->category_id,
		'file_type' => 'category'
	),
	'ajax' => $ajax
);

$content = '';
if(!empty($this->element->file_id) && !empty($this->element->file_path)) {
	$this->params = new stdClass();
	$this->params->file_id = $this->element->file_id;
	$this->params->file_path = $this->element->file_path;
	$this->params->file_ref_id = $this->element->file_ref_id;
	$this->params->category_id = $this->element->file_ref_id;
	$content = $this->loadTemplate('image_entry');
}

echo $this->uploaderType->displayImageSingle('hikashop_category_image', $content, $options);

?>
<script type="text/javascript">
if(!window.categoryMgr)
	window.categoryMgr = {};
window.categoryMgr.editImage = function(el, id, type) {
	var w = window, t = w.hikashop, href = null, n = el;
	if(type === undefined || type == '') type = 'category';
	t.submitFct = function(data) {};
	if(el.getAttribute('rel') == null) {
		href = el.href;
		n = 'hikashop_category_image_edit';
	}
	t.openBox(n,href);
	return false;
};
</script>
