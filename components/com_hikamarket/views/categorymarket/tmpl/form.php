<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><script type="text/javascript">
window.categoryMgr = {};
window.categoryMgr.cpt = {};
</script>
<form action="<?php echo hikamarket::completeLink('category');?>" method="post" name="hikamarket_form" id="hikamarket_categories_form" enctype="multipart/form-data">
	<table class="hikam_blocks">
		<tr>
			<td class="hikam_block_l hikam_block_x150">
<?php
	if(hikamarket::acl('category/edit/images')) {
		echo $this->loadTemplate('image');
	}
?>
			</td>
			<td class="hikam_block_r">
				<dl class="hikam_options">
<?php if(hikamarket::acl('category/edit/name')) { ?>
					<dt class="hikamarket_category_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
					<dd class="hikamarket_category_name"><input type="text" name="data[category][category_name]" value="<?php echo @$this->category->category_name; ?>"/></dd>

<?php } else { ?>
					<dt class="hikamarket_category_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
					<dd class="hikamarket_category_name"><?php echo @$this->category->category_name; ?></dd>
<?php }

	if(hikamarket::acl('category/edit/parent') && empty($this->isVendorRoot)) { ?>
					<dt class="hikamarket_category_parent"><label><?php echo JText::_('CATEGORY_PARENT'); ?></label></dt>
					<dd class="hikamarket_category_parent"><?php
						echo $this->categoryType->displaySingle('data[category][category_parent_id]', @$this->category->category_parent_id, '', $this->rootCategory);
					?></dd>
<?php }

	if(hikamarket::acl('category/edit/published') && empty($this->isVendorRoot)) { ?>
					<dt class="hikamarket_category_published"><label><?php echo JText::_('HIKA_PUBLISHED'); ?></label></dt>
					<dd class="hikamarket_category_published"><?php
						echo $this->radioType->booleanlist('data[category][category_published]', '', @$this->category->category_published);
					?></dd>
<?php }

	if(hikamarket::acl('category/edit/customfields')) {
		if(!empty($this->fields)) {
?>
				</dl>
<?php
			foreach($this->fields as $fieldName => $oneExtraField) {
?>
				<dl id="hikashop_category_<?php echo $fieldName; ?>" class="hikam_options">
					<dt class="hikamarket_category_<?php echo $fieldName; ?>"><?php echo $this->fieldsClass->getFieldName($oneExtraField); ?></dt>
					<dd class="hikamarket_category_<?php echo $fieldName; ?>"><?php
						$onWhat = 'onchange';
						if($oneExtraField->field_type == 'radio')
							$onWhat = 'onclick';
						echo $this->fieldsClass->display($oneExtraField, $this->category->$fieldName, 'data[category]['.$fieldName.']', false, ' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\'category\',0);"');
					?></dd>
				</dl>
<?php
			}
?>
				<dl class="hikam_options">
<?php
		}
	}
?>
				</dl>
			</td>
		</tr>
<?php
	if(hikamarket::acl('category/edit/description')) {
		if(!$this->config->get('front_small_editor')) { ?>
		<tr class="hikamarket_category_description">
			<td colspan="2">
				<label class="hikamarket_category_description_label"><?php echo JText::_('HIKA_DESCRIPTION'); ?></label>
				<?php echo $this->editor->display();?>
				<div style="clear:both"></div>
			</td>
		</tr>
<?php	} else { ?>
		<tr>
			<td colspan="2">
				<dl class="hikam_options">
					<dt class="hikamarket_category_description"><label><?php echo JText::_('HIKA_DESCRIPTION'); ?></label></dt>
					<dd class="hikamarket_category_description"><?php echo $this->editor->display();?><div style="clear:both"></div></dd>
				</dl>
			</td>
		</tr>
<?php	}
	}
?>
		<tr>
			<td colspan="2">
				<dl class="hikam_options">
<?php
		if(hikamarket::acl('category/edit/metadescription')) { ?>
					<dt class="hikamarket_category_metadescription"><label><?php echo JText::_('CATEGORY_META_DESCRIPTION'); ?></label></dt>
					<dd class="hikamarket_category_metadescription"><textarea id="hikamarket_category_metadescription_textarea" cols="35" rows="2" name="data[category][category_meta_description]"><?php echo $this->escape(@$this->category->category_meta_description); ?></textarea></dd>
<?php
		}

		if(hikamarket::acl('category/edit/keywords')) { ?>
					<dt class="hikamarket_category_keywords"><label><?php echo JText::_('CATEGORY_KEYWORDS'); ?></label></dt>
					<dd class="hikamarket_category_keywords"><textarea id="hikamarket_category_keywords_textarea" cols="35" rows="2" name="data[category][category_keywords]"><?php echo $this->escape(@$this->category->category_keywords); ?></textarea></dd>
<?php
		}

		if(hikamarket::acl('category/edit/pagetitle')) { ?>
					<dt class="hikamarket_category_pagetitle"><label><?php echo JText::_('PAGE_TITLE'); ?></label></dt>
					<dd class="hikamarket_category_pagetitle"><input type="text" size="45" name="data[category][category_page_title]" value="<?php echo $this->escape(@$this->category->category_page_title); ?>" /></dd>
<?php
		}

		if(hikamarket::acl('category/edit/alias')) { ?>
					<dt class="hikamarket_category_alias"><label><?php echo JText::_('HIKA_ALIAS'); ?></label></dt>
					<dd class="hikamarket_category_alias"><input type="text" size="45" name="data[category][category_alias]" value="<?php echo $this->escape(@$this->category->category_alias); ?>" /></dd>
<?php
		}

		if(hikamarket::acl('category/edit/translations')) {
			if(!empty($this->category->translations) && !empty($this->category->category_id)) {
?>					<dt class="hikamarket_product_translations"><label><?php echo JText::_('HIKA_TRANSLATIONS'); ?></label></dt>
					<dd class="hikamarket_product_translations"><?php
					foreach($this->category->translations as $language_id => $translation){
						$lngName = $this->translationHelper->getFlag($language_id);
						echo '<div class="hikamarket_multilang_button">' .
							$this->popup->display(
								$lngName, $lngName,
								hikamarket::completeLink('category&task=edit_translation&category_id=' . @$this->category->category_id.'&language_id='.$language_id, true),
								'hikamarket_category_translation_'.$language_id,
								760, 480, '', '', 'link'
							).
							'</div>';
					}
					?></dd>
<?php
			}
		}

		if(hikamarket::acl('category/edit/acl') && hikashop_level(2)) { ?>
					<dt class="hikamarket_category_acl"><label><?php echo JText::_('ACCESS_LEVEL'); ?></label></dt>
					<dd class="hikamarket_category_acl"><?php
						$category_access = 'all';
						if(isset($this->category->category_access))
							$category_access = $this->category->category_access;
						echo $this->joomlaAcl->display('data[category][category_access]', $category_access, true, true);
					?></dd>
<?php }
?>
				</dl>
			</td>
		</tr>
	</table>
	<input type="hidden" name="cancel_action" value="<?php echo @$this->cancel_action; ?>"/>
	<input type="hidden" name="cid[]" value="<?php echo @$this->category->category_id; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="ctrl" value="category"/>
	<?php echo JHTML::_('form.token'); ?>
</form>
