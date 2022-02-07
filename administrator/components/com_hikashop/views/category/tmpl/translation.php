<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.4.0
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
$this->translation = false;
$this->setLayout('normal');
echo $this->loadTemplate();
