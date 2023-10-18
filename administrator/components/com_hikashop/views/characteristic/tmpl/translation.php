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
	echo $this->tabs->startPane( 'translations');
		echo $this->tabs->startPanel(JText::_('MAIN_INFORMATION'), 'main_translation');
			$this->setLayout('normal');
			echo $this->loadTemplate();
		echo $this->tabs->endPanel();
		if(!empty($this->element->translations)){
			foreach($this->element->translations as $language_id => $translation){
				echo $this->tabs->startPanel($this->transHelper->getFlag($language_id), 'translation_'.$language_id);
					$this->characteristic_value_input = "translation[characteristic_value][".$language_id."]";
					$this->element->characteristic_value = @$translation->characteristic_value->value;
					if(!empty($this->transHelper->falang) && isset($translation->characteristic_value->published)){
						$this->characteristic_value_published = $translation->characteristic_value->published;
						$this->characteristic_value_id = $translation->characteristic_value->id;
					}
					$this->setLayout('normal');
					echo $this->loadTemplate();
				echo $this->tabs->endPanel();
			}
		}
	echo $this->tabs->endPane();
