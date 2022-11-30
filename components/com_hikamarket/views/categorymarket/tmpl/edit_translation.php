<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo hikamarket::completeLink('category'); ?>" name="hikamarket_translation_form" id="hikamarket_translation_form" method="post" enctype="multipart/form-data">
<?php
if(!empty($this->category->translations)) {
	foreach($this->category->translations as $language_id => $translation) {
?>
	<table class="hikam_blocks">
		<tr>
			<td class="hikam_block_r">
				<dl class="hikam_options">
<?php if(hikamarket::acl('category/edit/name')) { ?>
					<dt class="hikamarket_category_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
					<dd class="hikamarket_category_name"><input type="text" name="translation[category_name][<?php echo $language_id; ?>]" value="<?php echo @$translation->category_name->value; ?>"/></dd>

<?php } else { ?>
					<dt class="hikamarket_category_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
					<dd class="hikamarket_category_name"><?php echo @$this->category->category_name; ?></dd>
<?php }

		if(hikamarket::acl('category/edit/metadescription')) { ?>
					<dt class="hikamarket_category_metadescription"><label><?php echo JText::_('CATEGORY_META_DESCRIPTION'); ?></label></dt>
					<dd class="hikamarket_category_metadescription"><textarea id="hikamarket_category_metadescription_textarea" cols="35" rows="2" name="translation[category_meta_description][<?php echo $language_id; ?>]"><?php echo $this->escape(@$translation->category_meta_description->value); ?></textarea></dd>
<?php
		}

		if(hikamarket::acl('category/edit/keywords')) { ?>
					<dt class="hikamarket_category_keywords"><label><?php echo JText::_('CATEGORY_KEYWORDS'); ?></label></dt>
					<dd class="hikamarket_category_keywords"><textarea id="hikamarket_category_keywords_textarea" cols="35" rows="2" name="translation[category_keywords][<?php echo $language_id; ?>]"><?php echo $this->escape(@$translation->category_keywords->value); ?></textarea></dd>
<?php
		}

		if(hikamarket::acl('category/edit/pagetitle')) { ?>
					<dt class="hikamarket_category_pagetitle"><label><?php echo JText::_('PAGE_TITLE'); ?></label></dt>
					<dd class="hikamarket_category_pagetitle"><input type="text" size="45" name="translation[category_page_title][<?php echo $language_id; ?>]" value="<?php echo $this->escape(@$translation->category_page_title->value); ?>" /></dd>
<?php
		}

		if(hikamarket::acl('category/edit/alias')) { ?>
					<dt class="hikamarket_category_alias"><label><?php echo JText::_('HIKA_ALIAS'); ?></label></dt>
					<dd class="hikamarket_category_alias"><input type="text" size="45" name="translation[category_alias][<?php echo $language_id; ?>]" value="<?php echo $this->escape(@$translation->category_alias->value); ?>" /></dd>
<?php
		}

		if(hikamarket::acl('category/edit/description') && $this->config->get('front_small_editor')) { ?>
					<dt class="hikamarket_category_description"><label><?php echo JText::_('PRODUCT_DESCRIPTION'); ?></label></dt>
					<dd class="hikamarket_category_description"><div class="hikam_options_nl"></div><?php
						$this->editor->name = 'translation_category_description_' . $language_id;
						$this->editor->content = @$translation->category_description->value;
						echo $this->editor->display();
					?><div style="clear:both"></div></dd>
<?php } ?>
				</dl>
			</td>
		</tr>
<?php if(hikamarket::acl('category/edit/description') && !$this->config->get('front_small_editor')) { ?>
		<tr>
			<td><?php
				$this->editor->name = 'translation_category_description_' . $language_id;
				$this->editor->content = @$translation->category_description->value;
				echo $this->editor->display();
			?><div style="clear:both"></div></td>
		</tr>
<?php } ?>
	</table>
<?php
	}
}
?>
	<input type="hidden" name="cid[]" value="<?php echo @$this->category->category_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="category" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
