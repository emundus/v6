<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if($this->config->get('multilang_display')=='popups'&&!empty($this->element->category_id)){
	echo '<div class="hikashop_multilang_buttons" id="hikashop_multilang_buttons">';
	foreach($this->element->translations as $language_id => $translation){
		echo '<a class="modal" rel="{handler: \'iframe\', size: {x: 760, y: 480}}" href="'.hikashop_completeLink("category&task=edit_translation&category_id=".@$this->element->category_id.'&language_id='.$language_id,true ).'"><div class="hikashop_multilang_button">'.$this->transHelper->getFlag($language_id).'</div></a>';
	}
	echo '</div>';
}
	echo $this->tabs->startPane('translations');
		echo $this->tabs->startPanel(JText::_('MAIN_INFORMATION'), 'main_translation');
			$this->translation = false;
			$this->setLayout('normal');
			echo $this->loadTemplate();
			$this->translation = true;
		echo $this->tabs->endPanel();
		if($this->config->get('multilang_display')!='popups' && !empty($this->element->translations)){
			foreach($this->element->translations as $language_id => $translation){
				echo $this->tabs->startPanel($this->transHelper->getFlag($language_id), 'translation_'.$language_id);
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
				echo $this->tabs->endPanel();
			}
		}
	echo $this->tabs->endPane();
