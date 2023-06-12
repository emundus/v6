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
		echo $this->tabs->startPanel(JText::_('MAIN_INFORMATION'), 'main_translation');
			$this->setLayout('normal');
			echo $this->loadTemplate();
		echo $this->tabs->endPanel();
		if(!empty($this->element->translations)){
			foreach($this->element->translations as $language_id => $translation){
				echo $this->tabs->startPanel($this->transHelper->getFlag($language_id), 'translation_'.$language_id);
					$this->banner_title_input = "translation[banner_title][".$language_id."]";
					$this->element->banner_title = @$translation->banner_title->value;
					if(!empty($this->transHelper->falang) && isset($translation->banner_title->published)){
						$this->banner_title_published = $translation->banner_title->published;
						$this->banner_title_id = $translation->banner_title->id;
					}
					$this->banner_url_input = "translation[banner_url][".$language_id."]";
					$this->element->banner_url = @$translation->banner_url->value;
					if(!empty($this->transHelper->falang) && isset($translation->banner_url->published)){
						$this->banner_url_published = $translation->banner_url->published;
						$this->banner_url_id = $translation->banner_url->id;
					}
					$this->banner_image_url_input = "translation[banner_image_url][".$language_id."]";
					$this->element->banner_image_url = @$translation->banner_image_url->value;
					if(!empty($this->transHelper->falang) && isset($translation->banner_image_url->published)){
						$this->banner_image_url_published = $translation->banner_image_url->published;
						$this->banner_image_url_id = $translation->banner_image_url->id;
					}
					$this->banner_comment_input = "translation[banner_comment][".$language_id."]";
					$this->element->banner_comment = @$translation->banner_comment->value;
					if(!empty($this->transHelper->falang) && isset($translation->banner_comment->published)){
						$this->banner_comment_published = $translation->banner_comment->published;
						$this->banner_comment_id = $translation->banner_comment->id;
					}


					$this->setLayout('normal');
					echo $this->loadTemplate();
				echo $this->tabs->endPanel();
			}
		}
	echo $this->tabs->endPane();
