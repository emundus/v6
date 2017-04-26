<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopSearchType {
	public function display($map, $value) {
		$app = JFactory::getApplication();
		if($app->isAdmin()) {
			return HIKASHOP_BACK_RESPONSIVE ? $this->displayBootstrap($map, $value) : $this->displayClassic($map, $value);
		}
		return $this->displayClassic($map, $value);
	}

	public function displayBootstrap($map, $value) {
		return '
	<div class="input-prepend input-append">
		<span class="add-on"><i class="icon-filter"></i></span>
		<input type="text" name="'.$map.'" id="'.$map.'" value="'.$this->escape($value).'" class="text_area" placeholder="'.JText::_('HIKA_SEARCH').'"/>
		<button class="btn" onclick="this.form.limitstart.value=0;this.form.submit();"><i class="icon-search"></i></button>
		<button class="btn" onclick="this.form.limitstart.value=0;document.getElementById(\''.$map.'\').value=\'\';this.form.submit();"><i class="icon-remove"></i></button>
	</div>';
	}

	public function displayClassic($map, $value) {
		return '
	<div class="hikashop_search_block">
		<input type="text" name="'.$map.'" id="'.$map.'" value="'.$this->escape($value).'" class="text_area" placeholder="'.JText::_('HIKA_SEARCH').'"/>
		<button class="btn" onclick="this.form.limitstart.value=0;this.form.submit();"><?php echo JText::_(\'GO\'); ?></button>
		<button class="btn" onclick="this.form.limitstart.value=0;document.getElementById(\''.$map.'\').value=\'\';this.form.submit();"><?php echo JText::_(\'RESET\'); ?></button>
	</div>
';
	}

	protected function escape($value) {
		return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
	}
}
