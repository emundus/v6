<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(hikaInput::get()->getVar('tmpl','')=='component'){ ?>
<h1><?php echo JText::_('HIKASHOP_ENTRY');?></h1>
<?php } ?>
<div id="hikashop_entry_form_span_iframe">
	<form action="<?php echo hikashop_completeLink('entry'); ?>" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
			<?php  if(hikaInput::get()->getVar('tmpl','')=='component'){ ?>
			<table>
			<?php }else{?>
			<table class="admintable table" width="700px" style="margin:auto">
			<?php
			}
			foreach($this->extraFields['entry'] as $fieldName => $oneExtraField) {
			?>
				<tr>
					<td class="key">
						<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
					</td>
					<td>
						<?php echo $this->fieldsClass->display($oneExtraField,@$this->entry->$fieldName,'data[entry]['.$fieldName.']',false,'',true); ?>
					</td>
				</tr>
			<?php }	?>
			</table>
		<input type="hidden" name="ctrl" value="entry"/>
		<?php if(hikaInput::get()->getVar('tmpl','')=='component'){ ?>
			<input type="hidden" name="task" value="save"/>
			<input type="hidden" name="tmpl" value="<?php echo hikaInput::get()->getVar('tmpl',''); ?>"/>
		<?php }else{ ?>
			<input type="hidden" name="task" value=""/>
		<?php } ?>
		<input type="hidden" name="data[entry][order_id]" value="<?php echo (int)@$this->entry->order_id;?>"/>
		<input type="hidden" name="data[entry][entry_id]" value="<?php echo (int)@$this->entry->entry_id;?>"/>

		<?php
		echo JHTML::_( 'form.token' );
		if(hikaInput::get()->getVar('tmpl','')=='component'){
			echo $this->cart->displayButton(JText::_('OK'),'ok',$this->params,hikashop_completeLink('entry&task=save'),'if(hikashopCheckChangeForm(\'entry\',\'adminForm\')) document.forms[\'adminForm\'].submit(); return false;','style="float:right"');
		}
		?>
	</form>
</div>
