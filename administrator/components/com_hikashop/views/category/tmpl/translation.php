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
if($this->config->get('multilang_display')=='popups'&&!empty($this->element->category_id)){
	echo '<div class="hikashop_multilang_buttons" id="hikashop_multilang_buttons">';
	foreach($this->element->translations as $language_id => $translation){
		echo '<a class="modal" rel="{handler: \'iframe\', size: {x: '.(int)$this->config->get('multi_language_edit_x', 760).', y: '.(int)$this->config->get('multi_language_edit_y', 480).'}}" href="'.hikashop_completeLink("category&task=edit_translation&category_id=".@$this->element->category_id.'&language_id='.$language_id,true ).'"><div class="hikashop_multilang_button hikashop_language_'.$language_id.'"">'.$this->transHelper->getFlag($language_id).'</div></a>';
	}
	echo '</div>';
}
$this->translation = false;
$this->setLayout('normal');
echo $this->loadTemplate();
