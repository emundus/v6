<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$ajax = false;
if(!empty($this->upload_ajax))
	$ajax = true;
$product_type = ((!empty($this->params->product_type) && $this->params->product_type == 'variant') || !empty($this->editing_variant)) ? 'variant' : 'product';
$upload = $this->aclEdit('files/upload');
$options = array(
	'classes' => array(
		'mainDiv' => 'hikamarket_main_file_div',
		'contentClass' => 'hikamarket_product_files',
		'btn_add' => 'fas fa-plus',
		'btn_upload' => 'fas fa-upload'
	),
	'upload' => $upload,
	'upload_base_url' => 'index.php?option=com_hikamarket&ctrl=upload',
	'toolbar' => array(
		$this->popup->display(
			'<span class="fas fa-plus"></span>',
			'MARKET_ADD_FILE',
			hikamarket::completeLink('product&task=file&pid='.$this->product->product_id,true),
			'hikamarket_file_add',
			750, 460, 'class="hikabtn hikabtn-primary" onclick="return window.productMgr.addFile(this, '.(int)$this->product->product_id.',\''.$product_type.'\');"', '', 'link'
		)
	),
	'tooltip' => true,
	'text' => ($upload ? JText::_('HIKAM_PRODUCT_FILES_EMPTY_UPLOAD') : JText::_('HIKAM_PRODUCT_FILES_EMPTY')),
	'uploader' => array('product', 'product_file'),
	'vars' => array(
		'product_id' => $this->product->product_id,
		'product_type' => $product_type,
		'file_type' => 'file'
	),
	'ajax' => $ajax
);

$content = array();
if(!empty($this->product->files)) {
	foreach($this->product->files as $k => $file) {
		$file->product_id = $this->product->product_id;
		$file->product_type = $product_type;
		$this->params = $file;
		$content[] = $this->loadTemplate('file_entry');
	}
}

if(empty($this->editing_variant))
	echo $this->uploaderType->displayFileMultiple('hikamarket_product_file', $content, $options);
else
	echo $this->uploaderType->displayFileMultiple('hikamarket_product_variant_file', $content, $options);

if(empty($this->editing_variant))
	echo $this->popup->display('','MARKET_FILE','','hikamarket_product_file_edit',750, 460,'', '', 'link');
else
	echo $this->popup->display('','MARKET_FILE','','hikamarket_product_variant_file_edit',750, 460,'', '', 'link');

?>
<script type="text/javascript">
window.productMgr.addFile = function(el, pid, type) {
	var t = window.hikamarket;
	if(type === undefined || type == '') type = 'product';
	if(type == 'variant') type = 'product_variant';
	t.submitFct = function(data) {
		var o = window.Oby, d = document, c = d.getElementById('hikamarket_'+type+'_file_content');
		if(data.cid) {
			var url = "<?php echo hikamarket::completeLink('product&task=file_entry&pid=HIKAPID&cid=HIKACID',true); ?>";
			o.xRequest(
				url.replace('HIKAPID',pid).replace('HIKACID',data.cid),
				null,
				function(xhr,params){
					var myData = document.createElement('div');
					hkjQuery(myData).html(xhr.responseText);
					c.appendChild(myData);
					hkjQuery('#hikamarket_'+type+'_file_empty').hide();
				}
			);
		}
	};
	t.openBox(el);
	return false;
};
window.productMgr.editFile = function(el, id, pid, type) {
	var t = window.hikamarket, href = null, n = el;
	if(type === undefined || type == '') type = 'product';
	if(type == 'variant') type = 'product_variant';
	t.submitFct = function(data) {
		var o = window.Oby, c = el;
		while(c && !o.hasClass(c, 'hikamarket_'+type+'_file'))
			c = c.parentNode;
		if(c && data.cid) {
			var url = "<?php echo hikamarket::completeLink('product&task=file_entry&pid=HIKAPID&cid=HIKACID',true); ?>";
			o.xRequest(
				url.replace('HIKAPID', pid).replace('HIKACID',data.cid),
				null,
				function(xhr,params){
					var myData = document.createElement('div');
					hkjQuery(myData).html(xhr.responseText);
					c.parentNode.replaceChild(myData, c);
				}
			);
		}
	};
	if(el.getAttribute('rel') == null) {
		href = el.href;
		n = 'hikamarket_'+type+'_file_edit';
	}
	t.openBox(n,href,(el.getAttribute('rel') == null));
	return false;
};
window.productMgr.delFile = function(el, type) {
	if(!confirm('<?php echo $this->escape(JText::_('PLEASE_CONFIRM_DELETION')); ?>')) return false;
	if(type === undefined || type == '') type = 'product';
	if(type == 'variant') type = 'product_variant';
	return window.hkUploaderList['hikamarket_'+type+'_file'].delBlock(el);
};
window.hikashop.ready(function() {
	hkjQuery('#hikamarket_product<?php if(!empty($this->editing_variant)) { echo '_variant'; } ?>_file_content').sortable({
		cursor: "move",
		placeholder: "ui-state-highlight",
		forcePlaceholderSize: true
	});
});
</script>
