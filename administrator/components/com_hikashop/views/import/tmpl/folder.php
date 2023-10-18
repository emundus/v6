<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table class="admintable table" cellspacing="1">
	<tr>
		<td class="key" >
		<?php echo JText::_('HIKA_TYPE'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.radiolist',   $this->importFolders, 'importfolderfrom', 'class="custom-select" size="1" onclick="updateImportFolder(this.value);"', 'value', 'text','images'); ?>
		</td>
	</tr>
	<tr>
		<td class="key" >
		<?php echo JText::_('DELETE_FILES_AUTOMATICALLY'); ?>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', 'delete_files_automatically','','1');?>
		</td>
	</tr>
</table>
<div id="images">
	<table class="admintable table" cellspacing="1">
		<tr>
			<td class="key" >
				<?php echo JText::_('FOLDER_PATH'); ?>
			</td>
			<td>
				<input type="text" size="50" name="images_folder" />
			</td>
		</tr>
	</table>
</div>
<div id="files" style="display:none">
	<table class="admintable table" cellspacing="1">
		<tr>
			<td class="key" >
				<?php echo JText::_('FOLDER_PATH'); ?>
			</td>
			<td>
				<input type="text" size="50" name="files_folder" />
			</td>
		</tr>
	</table>
</div>
<div id="both" style="display:none">
	<table class="admintable table" cellspacing="1">
		<tr>
			<td class="key" >
				<?php echo JText::_('FOLDER_PATH'); ?>
			</td>
			<td>
				<input type="text" size="50" name="both_folder" />
			</td>
		</tr>
	</table>
</div>

