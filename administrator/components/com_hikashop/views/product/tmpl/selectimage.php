<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="title" style="float: left;"><h1><?php echo empty($this->element->file_id) ? JText::_('NEW_IMAGE') : JText::_('EDIT_IMAGE'); ?></h1></div>
<div class="toolbar" id="toolbar" style="float: right;">
	<button class="btn btn-success" type="button" onclick="submitbutton('addimage');"><i class="fa fa-save"></i> <?php echo JText::_('OK'); ?></button>
</div>
<div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT; ?>&amp;ctrl=product" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
	<table width="100%">
		<tr>
			<td class="key">
				<label for="file_name">
					<?php echo JText::_( 'HIKA_NAME' ); ?> (ALT)
				</label>
			</td>
			<td>
				<input type="text" name="data[file][file_name]" value="<?php echo $this->escape(@$this->element->file_name); ?>"/>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="file_description">
					<?php echo JText::_( 'HIKA_TITLE' ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="data[file][file_description]" value="<?php echo $this->escape(@$this->element->file_description); ?>"/>
			</td>
		</tr>
<?php
	if(empty($this->element->file_path)){
		$style = '';
		if(empty($_REQUEST['pathonly'])){
?>
		<tr>
			<td class="key">
				<label for="files"><?php
					echo JText::_('HIKA_FILE_MODE');
				?></label>
			</td>

			<td><?php
				$values = array(
					JHTML::_('select.option', 'upload', JText::_('HIKA_FILE_MODE_UPLOAD')),
					JHTML::_('select.option', 'path', JText::_('HIKA_FILE_MODE_PATH'))
				);
				echo JHTML::_('hikaselect.genericlist', $values, "data[filemode]", 'class="custom-select" size="1" onchange="hikashop_switchmode(this);"', 'value', 'text', 'upload');
			?>
			<script type="text/javascript">
			function hikashop_switchmode(el) {
				var d = document, v = el.value, modes = ['upload','path'], e = null;
				for(var i = 0; i < modes.length; i++) {
					mode = modes[i];
					e = d.getElementById('hikashop_'+mode+'_section');
					if(!e) continue;
					if(v == mode) {
						e.style.display = '';
					} else {
						e.style.display = 'none';
					}
				}
			}
			</script>
			</td>
		</tr>
		<tr id="hikashop_upload_section">
			<td class="key">
				<label for="files"><?php
					echo JText::_( 'HIKA_IMAGE' );
				?></label>
			</td>
			<td>
				<input type="file" name="files[]" size="30" />
				<?php echo JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')); ?>
			</td>
		</tr>
<?php
			$style = 'style="display:none;"';
		}else{
?>
		<tr id="hikashop_path_download">
			<td class="key">
				<label for="files"><?php
					echo JText::_('STORE_LOCALLY');
				?></label>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "data[download]" , '',1 ); ?>
				<input type="hidden" name="data[filemode]" value="path"/>
			</td>
		</tr>
<?php
		}
?>
		<tr id="hikashop_path_section" <?php echo $style; ?>>
			<td class="key">
				<label for="files"><?php
					echo JText::_('HIKA_PATH');
				?></label>
			</td>
			<td>
				<input type="text" name="data[filepath]" size="60" value=""/>
			</td>
		</tr>
<?php
	}else{
?>
		<tr>
			<td class="key">
				<label for="files"><?php
					echo JText::_( 'HIKA_IMAGE' );
				?></label>
			</td>
			<td>
				<?php
					$image = $this->image->getThumbnail($this->element->file_path, array(100, 100), array('default' => true));
					if(!empty($image) && $image->success) {
						$attributes = '';
						if($image->external)
							$attributes = ' width="'.$image->req_width.'" height="'.$image->req_height.'"';
						echo '<img src="'.$image->url.'" alt="'.$image->filename.'"'.$attributes.' />';
					} else {
						echo '<img src="" alt="'.@$this->element->file_name.'" />';
					}

				?>
			</td>
		</tr>
<?php
	}
?>
	</table>
	<div class="clr"></div>
	<input type="hidden" name="data[file][file_type]" value="product" />
	<input type="hidden" name="data[file][file_ref_id]" value="<?php echo hikaInput::get()->getInt('product_id'); ?>" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->cid; ?>" />
	<input type="hidden" name="id" value="<?php echo hikaInput::get()->getInt('id');?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="task" value="selectimage" />
	<input type="hidden" name="ctrl" value="product" />
	<input type="hidden" name="pathonly" value="<?php echo hikaInput::get()->getInt('pathonly', 0); ?>" />
<?php if(hikaInput::get()->getInt('legacy', 0)) { ?>
	<input type="hidden" name="legacy" value="1" />
<?php } ?>
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
