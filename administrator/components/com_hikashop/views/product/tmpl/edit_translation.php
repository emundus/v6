<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><h4 style="float:left"><?php echo JText::_('HIKA_TRANSLATIONS'); ?> : <?php $translationHelper = hikashop_get('helper.translation'); echo $translationHelper->getFlag(key($this->product->translations)); ?></h4>
<div class="toolbar" id="toolbar" style="float: right;">
	<button class="btn btn-success" type="button" onclick="submitbutton('save_translation');"><i class="fa fa-save"></i> <?php echo JText::_('OK'); ?></button>
</div>
<div style="clear:both"></div>
<form action="<?php echo hikashop_completeLink('product'); ?>" name="adminForm" id="adminForm" method="post" enctype="multipart/form-data">
<?php
if(!empty($this->product->translations)) {
	foreach($this->product->translations as $language_id => $translation) {
?>
	<dl class="hika_options">
<?php if(hikashop_acl('product/edit/name')) { ?>
		<dt class="hikashop_product_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
		<dd class="hikashop_product_name"><input type="text" name="translation[product_name][<?php echo $language_id; ?>]" value="<?php echo $this->escape(@$translation->product_name->value); ?>"/></dd>

<?php } else { ?>
		<dt class="hikashop_product_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
		<dd class="hikashop_product_name"><?php echo @$this->product->product_name; ?></dd>
<?php }

		if($this->product->product_type == 'main') {
			if(hikashop_acl('product/edit/pagetitle')) { ?>
		<dt class="hikashop_product_pagetitle"><label><?php echo JText::_('PAGE_TITLE'); ?></label></dt>
		<dd class="hikashop_product_pagetitle"><input type="text" size="45" name="translation[product_page_title][<?php echo $language_id; ?>]" value="<?php echo $this->escape(@$translation->product_page_title->value); ?>" /></dd>
<?php
			}

			if(hikashop_acl('product/edit/url')) { ?>
		<dt class="hikashop_product_url"><label><?php echo JText::_('URL'); ?></label></dt>
		<dd class="hikashop_product_url"><input type="text" size="45" name="translation[product_url][<?php echo $language_id; ?>]" value="<?php echo $this->escape(@$translation->product_url->value); ?>" /></dd>
<?php
			}

			if(hikashop_acl('product/edit/metadescription')) { ?>
		<dt class="hikashop_product_metadescription"><label><?php echo JText::_('PRODUCT_META_DESCRIPTION'); ?></label></dt>
		<dd class="hikashop_product_metadescription"><textarea id="product_meta_description" cols="35" rows="2" name="translation[product_meta_description][<?php echo $language_id; ?>]"><?php echo $this->escape(@$translation->product_meta_description->value); ?></textarea></dd>
<?php
			}

			if(hikashop_acl('product/edit/keywords')) { ?>
		<dt class="hikashop_product_keywords"><label><?php echo JText::_('PRODUCT_KEYWORDS'); ?></label></dt>
		<dd class="hikashop_product_keywords"><textarea id="product_keywords" cols="35" rows="2" name="translation[product_keywords][<?php echo $language_id; ?>]"><?php echo $this->escape(@$translation->product_keywords->value); ?></textarea></dd>
<?php
			}

			if(hikashop_acl('product/edit/alias')) { ?>
		<dt class="hikashop_product_alias"><label><?php echo JText::_('HIKA_ALIAS'); ?></label></dt>
		<dd class="hikashop_product_alias"><input type="text" size="45" name="translation[product_alias][<?php echo $language_id; ?>]" value="<?php echo $this->escape(@$translation->product_alias->value); ?>" /></dd>
<?php
			}

			if(hikashop_acl('product/edit/canonical')) { ?>
		<dt class="hikashop_product_canonical"><label><?php echo JText::_('PRODUCT_CANONICAL'); ?></label></dt>
		<dd class="hikashop_product_canonical"><input type="text" size="45" name="translation[product_canonical][<?php echo $language_id; ?>]" value="<?php echo $this->escape(@$translation->product_canonical->value); ?>" /></dd>
<?php
			}

		}

		if(!empty($this->fields) && hikashop_acl('product/edit/customfields')) {
			foreach($this->fields as $fieldName => $oneExtraField) {
				if($this->fields[$fieldName]->field_type == 'textarea' && @$this->fields[$fieldName]->field_options['translatable'] == 1) {
					?>
					<dt class="hikashop_product_<?php echo $fieldName; ?>"><label><?php echo $this->fieldsClass->getFieldName($oneExtraField); ?></label></dt>
					<dd class="hikashop_product_<?php echo $fieldName; ?>"><textarea id="product_<?php echo $fieldName; ?>" cols="35" rows="2" name="translation[<?php echo $fieldName; ?>][<?php echo $language_id; ?>]"><?php echo $this->escape(@$translation->$fieldName->value); ?></textarea></dd>
<?php
				}
				if($this->fields[$fieldName]->field_type == 'text' && @$this->fields[$fieldName]->field_options['translatable'] == 1) { ?>
					<dt class="hikashop_product_<?php echo $fieldName; ?>"><label><?php echo $this->fieldsClass->getFieldName($oneExtraField); ?></label></dt>
					<dd class="hikashop_product_<?php echo $fieldName; ?>"><input type="text" size="45" name="translation[<?php echo $fieldName; ?>][<?php echo $language_id; ?>]" value="<?php echo $this->escape(@$translation->$fieldName->value); ?>" /></dd>
<?php
				}
				if($this->fields[$fieldName]->field_type == 'wysiwyg' && @$this->fields[$fieldName]->field_options['translatable'] == 1) { ?>
				</dl>
					<div class="hikashop_product_title_div_wysiwyg"><?php echo $this->fieldsClass->getFieldName($oneExtraField); ?></div>
					<div><?php
						$this->editor->name = 'translation_' . $fieldName . '_' . $language_id;
						$this->editor->content = @$translation->$fieldName->value;
						echo $this->editor->display();
						?><div style="clear:both"></div>
					</div>
				<dl class="hika_options">
<?php
				}
			}
		}
?>
	</dl>
<?php if(hikashop_acl('product/edit/description')) { ?>
	<div class="hikashop_product_title_div_wysiwyg"><?php echo JText::_('DESCRIPTION'); ?></div>
	<div><?php
		$this->editor->name = 'translation_product_description_' . $language_id;
		$this->editor->content = @$translation->product_description->value;
		echo $this->editor->display();
	?><div style="clear:both"></div>
	</div>
<?php }
	}
}
?>
	<input type="hidden" name="cid[]" value="<?php echo @$this->product->product_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="product" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
