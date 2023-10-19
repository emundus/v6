<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(hikashop_level(2)){
	if(!empty($this->extraFields['entry'])){
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
	?>
<form action="<?php echo hikashop_completeLink('entry&task=save'.$url_itemid); ?>" method="post" name="hikashop_entry_form" enctype="multipart/form-data">
	<div id="hikashop_entries_info" class="hikashop_entries_info">
		<div id="new_entry_div_1">
			<div class="hikashop_entry_info">
				<?php
				$this->id = 1;
				echo $this->loadTemplate('fields'); ?>
			</div>
		</div>
	</div>
	<?php if($this->config->get('allow_several_entries',1)){ ?>
	<div id="hikashop_new_entry" class="hikashop_new_entry">
		<a href="#" onclick="hikashopAddEntry('hikashop_entries_info');return false;">
			<?php echo JText::_('ADD_A_NEW_ENTRY'); ?>
		</a>
	</div>
	<?php
	echo $this->cart->displayButton(JText::_('NEXT'),'next',$this->params,'','if(hikashopCheckChangeForm(\'entry\',\'hikashop_entry_form\')) document.forms[\'hikashop_entry_form\'].submit(); return false;','id="hikashop_entry_next_button"');
	}?>
</form>
<div class="clear_both"></div>
<?php }else{
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('NO_CUSTOM_ENTRY_FIELDS_FOUND'));
	}
} ?>
