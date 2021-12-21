<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class sef_hikamarket {
	public function create($string) {
		$string = str_replace('&amp;', '&', preg_replace('#(index\.php\??)#i', '', $string));
		$query = array();
		$allValues = explode('&',$string);
		foreach($allValues as $oneValue) {
			list($var,$val) = explode('=', $oneValue);
			$query[$var] = $val;
		}
		$segments = array();

		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(class_exists('hikamarket') || include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php')) {
			$shopConfig = hikamarket::config(false);
			if($shopConfig->get('activate_sef',1)) {

			}
		}

		if (isset($query['ctrl'])) {
			$segments[] = $query['ctrl'];
			unset($query['ctrl']);
			if(isset($query['task'])) {
				$segments[] = $query['task'];
				unset($query['task']);
			}
		} elseif(isset($query['view'])) {
			$segments[] = $query['view'];
			unset($query['view']);
			if(isset($query['layout'])){
				$segments[] = $query['layout'];
				unset($query['layout']);
			}
		}

		if(isset($query['cid']) && isset($query['name'])) {
			if(is_numeric($query['name'])) {
				$query['name'] = $query['name'] . '-';
			}
			$segments[] = $query['cid'] . ':' . $query['name'];
			unset($query['cid']);
			unset($query['name']);
		}
		unset($query['option']);
		if(isset($query['Itemid']))
			unset($query['Itemid']);
		if(!empty($query)) {
			foreach($query as $name => $value){
				$segments[] = $name . ':' . $value;
			}
		}
		return implode('/', $segments);
	}
}
