<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="title" style="float: left;"><h1><?php echo JText::_('HIKA_SELECT_IMAGE'); ?></h1></div>
<div class="toolbar" id="toolbar" style="float: right;">
	<button class="btn btn-success" type="button" onclick="window.hikashop.submitform('galleryselect','adminForm');"><i class="fa fa-save"></i> <?php echo JText::_('OK'); ?></button>
</div>
<form action="<?php echo hikashop_completeLink('upload&task=galleryimage', true); ?>" method="post" name="adminForm" id="adminForm">
	<table width="100%" height="100%" class="adminlist" style="width:100%;height:100%;">
		<thead>
			<tr>
				<th></th>
				<th>
					<?php echo $this->loadHkLayout('search', array()); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td></td>
				<td>
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tr>
			<td width="180px" height="100%" style="width:180px;vertical-align:top;">
				<div style="width:180px;height:100%;overflow:auto;">
<?php
echo $this->treeContent;
?>
<script type="text/javascript">
hikashopGallery.callbackSelection = function(tree,id) {
	var d = document, node = tree.get(id);
	if( node.value && node.name ) {
		var url = "<?php
			$params = '';
			if(!empty($this->uploadConfig['extra'])) {
				foreach($this->uploadConfig['extra'] as $uploadField => $uploadFieldValue) {
					$params .= '&' . urlencode($uploadField) . '=' . urlencode($uploadFieldValue);
				}
			}
			echo hikashop_completeLink('upload&task=galleryimage&folder={FOLDER}&uploader='.$this->uploader.'&field='.$this->field.$params, true, true) ;
		?>";
		document.location = url.replace('{FOLDER}', node.value.replace('/', '|'));
	}
}
</script>
				</div>
			</td>
			<td>
				<ul class="hikaGallery">
<?php
if(!empty($this->dirContent)) {
	hikashop_loadJsLib('tooltip');
	foreach($this->dirContent as $k => $content) {
		$chk_uid = 'hikaGalleryChk_' . $k . '_' . uniqid();

		$tooltip = '';
		if(strlen($content->filename) > 15)
			$tooltip = ' data-toggle="hk-tooltip" data-title="'.htmlentities($content->filename).'"';
?>
	<li class="hikaGalleryItem">
		<a class="hikaGalleryPhoto" href="#" onclick="return window.hikagallery.select(this, '<?php echo $chk_uid; ?>');"<?php echo $tooltip; ?>>
			<img src="<?php echo $content->thumbnail->url; ?>" alt="<?php echo $content->filename; ?>"/>
			<span style="display:none;" class="hikaGalleryChk"><input type="checkbox" id="<?php echo $chk_uid ;?>" name="files[]" value="<?php echo $content->path; ?>"/></span>
			<div class="hikaGalleryCommand">
				<span class="photo_name"><?php echo $content->filename; ?></span>
				<span><?php echo $content->width . 'x' . $content->height; ?></span>
				<span style="float:right"><?php echo $content->size; ?></span>
			</div>
		</a>
	</li>
<?php
	}
}
?>
				</ul>
			</td>
		</tr>
	</table>
<script type="text/javascript">
window.hikagallery = {};
window.hikagallery.select = function(el, id) {
	var d = document, w = window, o = w.Oby, chk = d.getElementById(id);
	if(chk) {
		if(chk.checked) {
			o.removeClass(el.parentNode, 'selected');
		} else {
			o.addClass(el.parentNode, 'selected');
		}
		chk.checked = !chk.checked;
	}
	return false;
}
</script>
	<div class="clr"></div>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="task" value="galleryimage" />
	<input type="hidden" name="ctrl" value="upload" />
	<input type="hidden" name="folder" value="<?php echo $this->destFolder; ?>" />
	<input type="hidden" name="uploader" value="<?php echo $this->uploader; ?>" />
	<input type="hidden" name="field" value="<?php echo $this->field; ?>" />
<?php
	if(!empty($this->uploadConfig['extra'])) {
		foreach($this->uploadConfig['extra'] as $uploadField => $uploadFieldValue) {
?>
	<input type="hidden" name="<?php echo $uploadField; ?>" value="<?php echo $uploadFieldValue; ?>" />
<?php
		}
	}
?>
	<?php echo JHTML::_('form.token'); ?>
</form>
