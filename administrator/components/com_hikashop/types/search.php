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
class hikashopSearchType {
	public function display($map, $value, $options = array()) {
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator')) {
			return HIKASHOP_BACK_RESPONSIVE ? $this->displayBootstrap($map, $value) : $this->displayClassic($map, $value);
		}
		return $this->displayClassic($map, $value);
	}

	public function displayBootstrap($map, $value, $options = array()) {
		$id = isset($options['id']) ? $options['id'] : $map;

		return '
	<div class="input-prepend input-append">
		<span class="add-on"><i class="icon-filter"></i></span>
		<input type="text" name="'.$map.'" id="'.$id.'" value="'.$this->escape($value).'" class="text_area" placeholder="'.JText::_('HIKA_SEARCH').'"/>
		<button class="btn" onclick="this.form.limitstart.value=0;this.form.submit();"><i class="icon-search"></i></button>
		<button class="btn" onclick="this.form.limitstart.value=0;document.getElementById(\''.$id.'\').value=\'\';this.form.submit();"><i class="icon-remove"></i></button>
	</div>';
	}

	public function displayClassic($map, $value, $options = array()) {
		$id = isset($options['id']) ? $options['id'] : $map;

		return '
	<div class="hikashop_search_block">
		<input type="text" name="'.$map.'" id="'.$id.'" value="'.$this->escape($value).'" class="text_area" placeholder="'.JText::_('HIKA_SEARCH').'"/>
		<button class="btn" onclick="this.form.limitstart.value=0;this.form.submit();">'.JText::_('GO').'</button>
		<button class="btn" onclick="this.form.limitstart.value=0;document.getElementById(\''.$id.'\').value=\'\';this.form.submit();">'.JText::_('RESET').'</button>
	</div>
';
	}

	protected function escape($value) {
		return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
	}
}
