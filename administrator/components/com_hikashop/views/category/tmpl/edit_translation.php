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
echo $this->tabs->startPane( 'translations');
$language_id = key($this->element->translations);
?>
<h4 style="float:left"><?php echo $this->transHelper->getFlag($language_id). ' ' . JText::_('HIKA_TRANSLATIONS'); ?></h4>
<div class="toolbar" id="toolbar" style="float: right;">
	<button class="btn btn-success" type="button" onclick="submitbutton('save_translation');"><i class="fa fa-save"></i> <?php echo JText::_('OK'); ?></button>
</div>
<div style="clear:both"></div>
<div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=category" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
<?php
if(!empty($this->element->translations)) {
	foreach($this->element->translations as $language_id => $translation) {
		$this->category_name_input = "translation[category_name][".$language_id."]";
		$this->element->category_name = @$translation->category_name->value;
		$this->editor->name = 'translation_category_description_'.$language_id;
		$this->element->category_description = @$translation->category_description->value;
		if(!empty($this->transHelper->falang) && isset($translation->category_name->published)){
			$this->category_name_published = $translation->category_name->published;
			$this->category_name_id = $translation->category_name->id;
		}
		if(!empty($this->transHelper->falang) && isset($translation->category_description->published)){
			$this->category_description_published = $translation->category_description->published;
			$this->category_description_id = $translation->category_description->id;
		}
		$this->category_meta_description_input = "translation[category_meta_description][".$language_id."]";
		$this->element->category_meta_description = @$translation->category_meta_description->value;
		if(!empty($this->transHelper->falang) && isset($translation->category_meta_description->published)){
			$this->category_meta_description_published = $translation->category_meta_description->published;
			$this->category_meta_description_id = $translation->category_meta_description->id;
		}

		$this->category_keywords_input = "translation[category_keywords][".$language_id."]";
		$this->element->category_keywords = @$translation->category_keywords->value;
		if(!empty($this->transHelper->falang) && isset($translation->category_keywords->published)){
			$this->category_keywords_published = $translation->category_keywords->published;
			$this->category_keywords_id = $translation->category_keywords->id;
		}

		$this->category_page_title_input = "translation[category_page_title][".$language_id."]";
		$this->element->category_page_title = @$translation->category_page_title->value;
		if(!empty($this->transHelper->falang) && isset($translation->category_page_title->published)){
			$this->category_page_title_published = $translation->category_page_title->published;
			$this->category_page_title_id = $translation->category_page_title->id;
		}
		$this->category_alias_input = "translation[category_alias][".$language_id."]";
		$this->element->category_alias = @$translation->category_alias->value;
		if(!empty($this->transHelper->falang) && isset($translation->category_alias->published)){
			$this->category_alias_published = $translation->category_alias->published;
			$this->category_alias_id = $translation->category_alias->id;
		}
		$this->category_canonical_input = "translation[category_canonical][".$language_id."]";
		$this->element->category_canonical = @$translation->category_canonical->value;
		if(!empty($this->transHelper->falang) && isset($translation->category_canonical->published)){
			$this->category_canonical_published = $translation->category_canonical->published;
			$this->category_canonical_id = $translation->category_canonical->id;
		}

		$this->setLayout('normal');
		echo $this->loadTemplate();

?>
	<table class="admintable"  width="100%">
<?php
		if(!empty($this->fields)) {
			foreach($this->fields as $fieldName => $oneExtraField) {
				if($this->fields[$fieldName]->field_type == 'text' && @$this->fields[$fieldName]->field_options['translatable'] == 1) {
?>
		<tr>
			<td class="key">
				<?php echo $this->fieldsClass->getFieldName($oneExtraField); ?>
			</td>
			<td>
				<input type="text" id="<?php $this->fields[$fieldName]->field_id ?>" name="<?php echo "translation[$fieldName][".$language_id."]"; ?>" value="<?php if(!isset($translation->$fieldName->value))@$translation->$fieldName->value='';echo $this->escape(@$translation->$fieldName->value); ?>"/>
			</td>
		</tr>
<?php
				}

				if($this->fields[$fieldName]->field_type == 'textarea' && @$this->fields[$fieldName]->field_options['translatable'] == 1) {
?>
		<tr>
			<td class="key">
				<?php echo $this->fieldsClass->getFieldName($oneExtraField); ?>
			</td>
			<td>
				<textarea id="category_<?php echo $fieldName; ?>" cols="46" rows="2" name="<?php echo "translation[$fieldName][".$language_id."]"; ?>"><?php if(!isset($translation->$fieldName->value))@$translation->$fieldName->value='';echo $this->escape(@$translation->$fieldName->value); ?></textarea>
			</td>
		</tr>
<?php
				}

				if($this->fields[$fieldName]->field_type == 'wysiwyg' && @$this->fields[$fieldName]->field_options['translatable'] == 1) {
?>
		<tr>
			<td class="key">
				<?php echo $this->fieldsClass->getFieldName($oneExtraField); ?>
			</td>
			<td width="100%"></td>
		</tr>
		<tr>
			<td colspan="2" width="100%">
<?php
					$this->editor->name = 'translation_' . $fieldName . '_' . $language_id;
					$this->editor->content = @$translation->$fieldName->value;
					echo $this->editor->display();
?>
			</td>
		</tr>
<?php
				}
			}
		}
?>
	</table>
<?php

	}
}

?>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->category_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="category" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
