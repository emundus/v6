<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="hikabtn" type="button" onclick="window.hikamarket.submitform('galleryselect','adminForm');">
			<img style="vertical-align: middle" src="<?php echo HIKASHOP_IMAGES; ?>save.png"/> <?php echo JText::_('OK'); ?>
		</button>
	</div>
</fieldset>
<form action="<?php echo hikamarket::completeLink('upload&task=galleryimage', true); ?>" method="post" name="adminForm" id="adminForm">
	<table width="100%" height="100%" class="adminlist" style="width:100%;height:100%;">
		<thead>
			<tr>
				<th></th>
				<th>
					<?php echo JText::_('FILTER');?>:
					<input type="text" name="search" id="galleryimage_search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="document.adminForm.submit();" />
					<button class="hikabtn" onclick="document.adminForm.limitstart.value=0;this.form.submit();"><i class="fas fa-search"></i></button>
					<button class="hikabtn" onclick="document.adminForm.limitstart.value=0;document.getElementById('galleryimage_search').value='';this.form.submit();"><i class="fas fa-times"></i></button>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td></td>
				<td>
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tr>
			<td width="130px" height="100%" style="width:130px;vertical-align:top;">
				<div style="width:130px;height:100%;overflow:auto;">
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
			echo hikamarket::completeLink('upload&task=galleryimage&folder={FOLDER}&uploader='.$this->uploader.'&field='.$this->field.$params, true, true) ;
		?>";
		document.location = url.replace('{FOLDER}', node.value.replace('/', '|'));
	}
}
</script>
				</div>
			</td>
			<td>
				<ul id="hikaGallery">
<?php
if(!empty($this->dirContent)) {
	foreach($this->dirContent as $k => $content) {
		$chk_uid = 'hikaGalleryChk_' . $k . '_' . uniqid();

		if(!empty($this->vendorPath))
			$content->path = str_replace($this->vendorPath, '', $content->path);
?>
	<li class="hikaGalleryItem">
		<a class="hikaGalleryPhoto" href="#" onclick="return window.hikagallery.select(this, '<?php echo $chk_uid; ?>');">
			<img src="<?php echo str_replace('//', '/', $content->thumbnail->url); ?>" alt="<?php echo $content->filename; ?>"/>
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
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
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
