<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php $type=$this->plugin_type;
$id_field = $type.'_id';
$plugin_type = $type.'_type';
if($this->config->get('multilang_display')=='popups'&&!empty($this->element->$id_field)) {
	echo '<div class="hikashop_multilang_buttons" id="hikashop_multilang_buttons">';
	$popupHelper = hikashop_get('helper.popup');
	foreach($this->element->translations as $language_id => $translation){
		echo $popupHelper->display(
			'<div class="hikashop_multilang_button hikashop_language_'.$language_id.'"">'.$this->transHelper->getFlag($language_id).'</div>',
			$this->transHelper->getFlag($language_id),
			'\''."index.php?option=com_hikashop&ctrl=plugins&task=edit_translation&".$id_field."=".$this->element->$id_field."&type=".$type.'&language_id='.$language_id.'&tmpl=component\'',
			'hikashop_edit_'.$language_id.'_translations',
			(int)$this->config->get('multi_language_edit_x', 760),(int)$this->config->get('multi_language_edit_y', 480), '', '', 'link',true
		);
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
				echo $this->tabs->startPanel($this->transHelper->getFlag($language_id), 'translation_'.$language_id);
					$plugin_name = $type.'_name';
					$plugin_name_input =$plugin_name.'_input';
					$plugin_description = $type.'_description';
					$this->$plugin_name_input = "translation[plugin_name][".$language_id."]";
					$this->element->$plugin_name = @$translation->$plugin_name->value;
					$this->editor->name = 'translation_plugin_description_'.$language_id;
					$this->element->$plugin_description = @$translation->$plugin_description->value;
					$plugin_name_published = $plugin_name.'_published';
					$plugin_name_id = $plugin_name.'_id';
					if(!empty($this->transHelper->falang) && isset($translation->$plugin_name->published)){
						$this->$plugin_name_published = $translation->$plugin_name->published;
						$this->$plugin_name_id = $translation->$plugin_name->id;
					}
					$plugin_description_published = $plugin_description.'_published';
					$plugin_description_id = $plugin_description.'_id';
					if(!empty($this->transHelper->falang) && isset($translation->$plugin_description->published)){
						$this->$plugin_description_published = $translation->$plugin_description->published;
						$this->$plugin_description_id = $translation->$plugin_description->id;
					}
					$this->setLayout('normal');
					echo $this->loadTemplate();
				echo $this->tabs->endPanel();
			}
		}
	echo $this->tabs->endPane();
