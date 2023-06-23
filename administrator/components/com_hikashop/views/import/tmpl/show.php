<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
	<div id="hikashop_import_form" class="hk-row-fluid hikashop_backend_tile_edition">
	<div class="hkc-md-4">
		<div class="hikashop_tile_block">
			<div>
				<div class="hikashop_tile_title"><?php
					echo JText::_('IMPORT');
				?></div>
		<?php
		if(HIKASHOP_J30){
			echo JHTML::_('hikaselect.radiolist', $this->importValues, 'importfrom', 'class="custom-select" size="1" onclick="updateImport(this.value);"', 'value', 'text','file',false,false,true);
		}else{
			echo JHTML::_('hikaselect.radiolist', $this->importValues, 'importfrom', 'class="custom-select" size="1" onclick="updateImport(this.value);"', 'value', 'text','file');
		}
		if(hikashop_level(2)) {
			?>
				<dl class="hika_options">
					<dt><?php echo JText::_('PRODUCT_TEMPLATE'); ?></dt>
					<dd><?php echo $this->nameboxType->display(
								'template_product',
								0,
								hikashopNameboxType::NAMEBOX_SINGLE,
								'product',
								array(
									'delete' => true,
									'sort' => true,
									'default_text' => '<em>'.JText::_('NO_PRODUCT_TEMPLATE').'</em>',
								)
							);
				?></dd>
				</dl>
	<?php
		}
 ?>
			</div>
		</div>
	</div>
	<div class="hkc-md-8">
		<div class="hikashop_tile_block">
			<div>
	<?php
	foreach($this->importData as $data){
		echo '<div id="'.$data->key.'"';
		if($data->key != 'file') echo ' style="display:none"';
		echo '>';
		echo '<div class="hikashop_tile_title">'.$data->text.'</div>';
		if($data->key=='folder' && !hikashop_level(2)){
			echo hikashop_getUpgradeLink('business');
		}elseif($data->key=='vm' && !$this->vm){
			echo '<small style="color:red">'.JText::sprintf('HAS_NOT_BEEN_FOUND', 'VirtueMart').'</small>';
		}elseif($data->key=='mijo' && !$this->mijo){
			echo '<small style="color:red">'.JText::sprintf('HAS_NOT_BEEN_FOUND', 'Mjoshop').'</small>';
		}elseif($data->key=='redshop' && !$this->reds){
			echo '<small style="color:red">'.JText::sprintf('HAS_NOT_BEEN_FOUND', 'Redshop').'</small>';
		}else{
			if(in_array($data->key,array('file','textarea','folder','vm','mijo','redshop','openc'))) include(dirname(__FILE__).DS.$data->key.'.php');
			else echo $data->data;
		}
		echo '</div>';
		}?>
			</div>
		</div>
	</div>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
