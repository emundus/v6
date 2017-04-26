<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_product_files_main" class="hikashop_product_files_main">
<?php
if(!empty($this->element->files)) {

	$freeDownload = false;
	foreach($this->element->files as $file) {
		if(!empty($file->file_free_download)) {
			$freeDownload = true;
			break;
		}
	}

	if($freeDownload) {
		global $Itemid;
		$url_itemid = (!empty($Itemid) ? '&Itemid='.$Itemid : '');
?>
	<fieldset class="hikashop_product_files_fieldset">
		<legend><?php echo JText::_('DOWNLOADS'); ?></legend>
<?php
		foreach($this->element->files as $file) {
			if(empty($file->file_free_download))
				continue;

			if(empty($file->file_name))
				$file->file_name = $file->file_path;
?>
		<a class="hikashop_product_file_link" href="<?php echo hikashop_completeLink('product&task=download&file_id=' . $file->file_id.$url_itemid); ?>"><?php echo $file->file_name; ?></a><br/>
<?php
		}

?>
	</fieldset>
<?php
	}
}
?>
</div>
