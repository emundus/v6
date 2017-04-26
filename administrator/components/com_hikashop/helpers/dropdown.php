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
class hikashopDropdownHelper {

	public function __construct() {
	}

	public function init() {
		hikashop_loadJsLib('dropdown');
	}

	public function display($label, $data, $options = array()) {
		$this->init();

		$class = '';
		if(!empty($options['up']))
			$class .= ' hkdropup';

		$ret = '<div class="hkdropdown'.$class.'">';

		$extra = '';
		if(!empty($options['label-id']))
			$extra .= ' id="'.$options['label-id'].'"';

		$drop_label = '<span class="hkdropdown-text"'.$extra.'>'.htmlentities($label).'</span>';
		if(!empty($options['hkicon']))
			$drop_label = '<span class="'.htmlentities($options['hkicon']).'" title="'.htmlentities($label).'"></span> '.htmlentities($label);

		$extra = '';
		if(!empty($options['id']))
			$extra .= ' id="'.$options['id'].'"';

		$class = 'btn btn-mini';

		$type = @$options['type'];
		switch($type) {
			case 'caret':
				$ret .= '<a href="#" data-toggle="hkdropdown" class="caret" aria-haspopup="true" aria-expanded="false"></a>';
				break;

			case 'link':
				$ret .= '<a href="#" data-toggle="hkdropdown" aria-haspopup="true" aria-expanded="false">'.$drop_label.' <span class="caret"></span>'.'</a>';
				break;

			case 'button':
			default:
				$ret .= '<button type="button" data-toggle="hkdropdown" class="'.$class.'"'.$extra.' aria-haspopup="true" aria-expanded="false">'.
					$drop_label.
					' <span class="caret"></span>'.
					'</button>';
				break;
		}

		$extra = '';
		if(!empty($options['id']))
			$extra .= ' aria-labelledby="'.$options['id'].'"';

		$class = '';
		if(!empty($options['right']))
			$class .= ' hkdropdown-menu-right';

		$ret .= '<ul class="hkdropdown-menu'.$class.'"'.$extra.'>';
		foreach($data as $d) {
			if(empty($d) || $d === '-') {
				$ret .= '<li role="separator" class="divider"></li>';
				continue;
			}

			if(empty($d['name']))
				continue;

			$name = $d['name'];
			$link = '#';
			$extra = '';

			if(!empty($d['link']))
				$link = $d['link'];
			if(!empty($d['extra']))
				$extra .= ' '.trim($d['extra']);
			if(!empty($d['click']))
				$extra .= ' onclick="'.trim($d['click']).'"';

			if(empty($d['disable']))
				$ret .= '<li><a href="'.$link.'"'.$extra.'>'.$name.'</a></li>';
			else
				$ret .= '<li class="disabled"><a href="#" onclick="return false;">'.$name.'</span></li>';
		}
		$ret .= '</ul></div>';

		return $ret;
	}
}
