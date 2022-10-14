<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikamarketSearchType {
	public function display($map, $value, $options = array()) {
		$app = JFactory::getApplication();
		if(hikamarket::isAdmin()) {
			return HIKASHOP_BACK_RESPONSIVE ? $this->displayBootstrap($map, $value, $options) : $this->displayClassic($map, $value, $options);
		}
		return $this->displayClassic($map, $value, $options);
	}

	public function displayBootstrap($map, $value, $options = array()) {
		$id = isset($options['id']) ? $options['id'] : $map;

		return '
	<div class="input-prepend input-append">
		<span class="add-on"><i class="icon-filter"></i></span>
		<input type="text" name="'.$map.'" id="'.$id.'" value="'.$this->escape($value).'" class="text_area" placeholder="'.JText::_('HIKA_SEARCH').'"/>
		<button class="btn" onclick="if(this.form.limitstart){this.form.limitstart.value=0;}this.form.submit();"><i class="icon-search"></i></button>
		<button class="btn" onclick="if(this.form.limitstart){this.form.limitstart.value=0;}document.getElementById(\''.$id.'\').value=\'\';this.form.submit();"><i class="icon-remove"></i></button>
	</div>';
	}

	public function displayClassic($map, $value, $options = array()) {
		$id = isset($options['id']) ? $options['id'] : $map;

		return '
	<div class="hikamarket_search_block">
		<input type="text" name="'.$map.'" id="'.$id.'" value="'.$this->escape($value).'" class="text_area" placeholder="'.JText::_('HIKA_SEARCH').'"/>
		<button class="hikabtn" onclick="if(this.form.limitstart){this.form.limitstart.value=0;}this.form.submit();">'.JText::_('GO').'</button>
		<button class="hikabtn" onclick="if(this.form.limitstart){this.form.limitstart.value=0;}document.getElementById(\''.$id.'\').value=\'\';this.form.submit();">'.JText::_('RESET').'</button>
	</div>
';
	}

	protected function escape($value) {
		return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
	}
}
