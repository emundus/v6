<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

if($this->config->get('multilang_display')=='popups'&&!empty($this->element->product_id)){
	echo '<div class="hikashop_multilang_buttons" id="hikashop_multilang_buttons">';
	foreach($this->element->translations as $language_id => $translation){
		echo '<a class="modal" rel="{handler: \'iframe\', size: {x:'.(int)$this->config->get('multi_language_edit_x', 760).', y: '.(int)$this->config->get('multi_language_edit_y', 480).'}}" href="'.hikashop_completeLink("product&task=edit_translation&product_id=".@$this->element->product_id.'&language_id='.$language_id,true ).'"><div class="hikashop_multilang_button hikashop_language_'.$language_id.'"">'.$this->transHelper->getFlag($language_id).'</div></a>';
	}
	echo '</div>';
}
	echo $this->tabs->startPane( 'translations');
		echo $this->tabs->startPanel(JText::_('MAIN_INFORMATION'), 'main_translation');
			$this->setLayout('normal');
			echo $this->loadTemplate();
		echo $this->tabs->endPanel();
		if($this->config->get('multilang_display')!='popups' && !empty($this->element->translations)){
			foreach($this->element->translations as $language_id => $translation){
				$this->product_name_input = "translation[product_name][".$language_id."]";
				$this->element->product_name = @$translation->product_name->value;
				if(!empty($this->transHelper->falang) && isset($translation->product_name->published)){
					$this->product_name_published = $translation->product_name->published;
					$this->product_name_id = $translation->product_name->id;
				}

				$this->editor->name = 'translation_product_description_'.$language_id;
				$this->element->product_description = @$translation->product_description->value;
				if(!empty($this->transHelper->falang) && isset($translation->product_description->published)){
					$this->product_description_published = $translation->product_description->published;
					$this->product_description_id = $translation->product_description->id;
				}
				if($this->element->product_type=='main'){
					$this->product_url_input = "translation[product_url][".$language_id."]";
					$this->element->product_url = @$translation->product_url->value;
					if(!empty($this->transHelper->falang) && isset($translation->product_url->published)){
						$this->product_url_published = $translation->product_url->published;
						$this->product_url_id = $translation->product_url->id;
					}

					$this->product_meta_description_input = "translation[product_meta_description][".$language_id."]";
					$this->element->product_meta_description = @$translation->product_meta_description->value;
					if(!empty($this->transHelper->falang) && isset($translation->product_meta_description->published)){
						$this->product_meta_description_published = $translation->product_meta_description->published;
						$this->product_meta_description_id = $translation->product_meta_description->id;
					}

					$this->product_keywords_input = "translation[product_keywords][".$language_id."]";
					$this->element->product_keywords = @$translation->product_keywords->value;
					if(!empty($this->transHelper->falang) && isset($translation->product_keywords->published)){
						$this->product_keywords_published = $translation->product_keywords->published;
						$this->product_keywords_id = $translation->product_keywords->id;
					}

					$this->product_page_title_input = "translation[product_page_title][".$language_id."]";
					$this->element->product_page_title = @$translation->product_page_title->value;
					if(!empty($this->transHelper->falang) && isset($translation->product_page_title->published)){
						$this->product_page_title_published = $translation->product_page_title->published;
						$this->product_page_title_id = $translation->product_page_title->id;
					}

					$this->product_alias_input = "translation[product_alias][".$language_id."]";
					$this->element->product_alias = @$translation->product_alias->value;
					if(!empty($this->transHelper->falang) && isset($translation->product_alias->published)){
						$this->product_alias_published = $translation->product_alias->published;
						$this->product_alias_id = $translation->product_alias->id;
					}
					$this->product_canonical_input = "translation[product_canonical][".$language_id."]";
					$this->element->product_canonical = @$translation->product_canonical->value;
					if(!empty($this->transHelper->falang) && isset($translation->product_canonical->published)){
						$this->product_canonical_published = $translation->product_canonical->published;
						$this->product_canonical_id = $translation->product_canonical->id;
					}
				}
				echo $this->tabs->startPanel($this->transHelper->getFlag($language_id), 'translation_'.$language_id);
					$this->setLayout('normal');
					echo $this->loadTemplate();
				echo $this->tabs->endPanel();
			}
		}
	echo $this->tabs->endPane();
