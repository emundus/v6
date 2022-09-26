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

$jversion = preg_replace('#[^0-9\.]#i','',JVERSION);
if(version_compare($jversion,'4.0.0','>=')) {
	abstract class HkmRouterBase extends Joomla\CMS\Component\Router\RouterBase {}
} else if(version_compare($jversion,'3.0.0','>=')) {
	abstract class HkmRouterBase extends JComponentRouterBase {}
} else {
	abstract class HkmRouterBase {}
}

class HikamarketRouter extends HkmRouterBase
{
	protected $url_separator = '-';

	public function defineSeparator($sep) {
		$this->url_separator = $sep;
	}

	protected function initMarket() {
		if(defined('HIKAMARKET_COMPONENT'))
			return true;
		$filename = rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).'/components/com_hikamarket/helpers/helper.php';
		if(!file_exists($filename) || !include_once($filename))
			return false;
		return defined('HIKAMARKET_COMPONENT');
	}

	public function build(&$query) {
		$config = null;
		$shopConfig = null;
		$remove_sef_id = false;
		if($this->initMarket()) {
			$config = hikamarket::config();
			$shopConfig = hikamarket::config(false);

			$remove_sef_id = (!empty($shopConfig) && $shopConfig->get('sef_remove_id', 0));
		}
		$segments = array();
		$controller = null;
		$wasView = false;
		if(isset($query['ctrl'])) {
			if(substr($query['ctrl'], -6) == 'market')
				$query['ctrl'] = substr($query['ctrl'], 0, -6);
			$segments[] = $query['ctrl'];
			$controller = $query['ctrl'];
			unset( $query['ctrl'] );
			if (isset($query['task']) && strpos($query['task'], $this->url_separator) === false) {
				$segments[] = $query['task'];
				unset($query['task']);
			}
		} elseif(isset($query['view'])) {
			$wasView = true;
			$controller = $query['view'];
			unset($query['view']);
			if(isset($query['layout'])) {
				unset($query['layout']);
			}
		}

		if(count($segments) == 2 && $segments[0] == 'vendor' && $segments[1] == 'show' && isset($query['Itemid']) && $remove_sef_id) {
			$segments = array();
		}

		if(isset($query['cid']) && isset($query['name'])) {
			if($controller == 'vendor' && $remove_sef_id && !empty($query['name'])) {
				$segments[] = $query['name'];
			} else {
				if(is_numeric($query['name'])) {
					$query['name'] = $query['name'] . $this->url_separator;
				}
				$segments[] = $query['cid'] . $this->url_separator . $query['name'];
			}
			unset($query['cid']);
			unset($query['name']);
		}

		if(empty($query))
			return $segments;
		foreach($query as $name => $value) {
			if(!in_array($name, array('option', 'Itemid', 'start', 'format', 'limitstart', 'lang'))) {
				$segments[] = $name . $this->url_separator . $value;
				unset($query[$name]);
			}
		}
		return $segments;
	}

	public function parse(&$segments) {

		$vars = array();
		if(!empty($segments)) {
			if(!defined('DS'))
				define('DS', DIRECTORY_SEPARATOR);
			$config = null;
			$shopConfig = null;
			if(defined('HIKAMARKET_COMPONENT') || include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php')){
				$config = hikamarket::config();
				$shopConfig = hikamarket::config(false);
			}
			if(count($segments) == 1 && $shopConfig->get('sef_remove_id',0)) {
				$vars['ctrl'] = 'vendor';
				$vars['task'] = 'show';
				if($this->retrieve_url_id($vars, $segments[0])) {
					$segments = array();
					return $vars;
				}
				unset($vars['ctrl']);
				unset($vars['task']);
			}
			$i = 0;
			foreach($segments as $name) {
				if(isset($vars['ctrl']) && isset($vars['task']) && $shopConfig->get('sef_remove_id',0) && $this->retrieve_url_id($vars, $name))
					continue;
				if(strpos($name, $this->url_separator)) {
					list($arg, $val) = explode($this->url_separator ,$name);
					if(is_numeric($arg) && !is_numeric($val)) {
						$vars['cid'] = $arg;
						$vars['name'] = $val;
					} else if(is_numeric($arg))
						$vars['Itemid'] = $arg;
					else
						$vars[$arg] = $val;
				} else {
					$i++;
					if($i == 1)
						$vars['ctrl'] = $name;
					elseif($i == 2)
						$vars['task'] = $name;
				}
			}
		}
		$segments = array();
		return $vars;
	}

	private function retrieve_url_id(&$vars, $name) {
		if(@$vars['ctrl'] !== 'vendor' && @$vars['task'] !== 'show')
			return false;

		if(!empty($vars['cid']) || !$this->initMarket())
			return false;

		$db = JFactory::getDBO();
		$shopConfig = hikamarket::config(false);

		if($shopConfig->get('alias_auto_fill', 1)) {
			$db->setQuery('SELECT vendor_id FROM ' . hikamarket::table('vendor').' WHERE vendor_alias = '.$db->Quote(str_replace(':','-',$name)));
			$retrieved_id = $db->loadResult();
			if($retrieved_id) {
				$vars['cid'] = $retrieved_id;
				$vars['name'] = $name;
				return true;
			}
		}

		$name_regex = '^ *' . str_replace(array('-', ':'), '.+', $name) . ' *$';
		$db->setQuery('SELECT * FROM ' . hikamarket::table('vendor') . ' WHERE vendor_alias REGEXP ' . $db->Quote($name_regex) . ' OR vendor_name REGEXP ' . $db->Quote($name_regex));
		$retrieved = $db->loadObject();
		if($retrieved) {
			$vars['cid'] = $retrieved->vendor_id;
			$vars['name'] = $name;

			if($shopConfig->get('alias_auto_fill', 1) && empty($retrieved->vendor_alias)) {
				$retrieved->alias = $retrieved->vendor_name;
				if(!$shopConfig->get('unicodeslugs')) {
					$lang = JFactory::getLanguage();
					$retrieved->alias = $lang->transliterate($retrieved->alias);
				}

				$app = JFactory::getApplication();
				if(method_exists($app,'stringURLSafe'))
					$retrieved->alias = $app->stringURLSafe($retrieved->alias);
				else
					$retrieved->alias = JFilterOutput::stringURLSafe($retrieved->alias);

				$vendorClass = hikamarket::get('class.vendor');
				$element = new stdClass();
				$element->vendor_id = $retrieved->vendor_id;
				$element->vendor_alias = $retrieved->alias;
				$vendorClass->save($element);
			}
			return true;
		}
		return false;
	}
}

function HikamarketBuildRoute(&$query) {
	$router = new HikamarketRouter();
	$router->defineSeparator(':');
	return $router->build($query);
}

function HikamarketParseRoute($segments) {
	$router = new HikamarketRouter();
	$router->defineSeparator(':');
	return $router->parse($segments);
}

