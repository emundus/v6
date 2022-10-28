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
jimport('joomla.application.component.controller');
jimport('joomla.application.component.view');

$hikashopHelperFile = rtrim(JPATH_ADMINISTRATOR,'/').'/components/com_hikashop/helpers/helper.php';
if(!file_exists($hikashopHelperFile)) {
	throw new RuntimeException('HikaShop not installed ( www.hikashop.com )', 500);
	exit;
}
if(!defined('HIKASHOP_COMPONENT'))
	include_once($hikashopHelperFile);

define('HIKAMARKET_COMPONENT','com_hikamarket');
define('HIKAMARKET_LIVE',rtrim(JURI::root(),'/').'/');
define('HIKAMARKET_ROOT',rtrim(JPATH_ROOT,DS).DS);
define('HIKAMARKET_FRONT',rtrim(JPATH_SITE,DS).DS.'components'.DS.HIKAMARKET_COMPONENT.DS);
define('HIKAMARKET_BACK',rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.HIKAMARKET_COMPONENT.DS);
define('HIKAMARKET_HELPER',HIKAMARKET_BACK.'helpers'.DS);
define('HIKAMARKET_BUTTON',HIKAMARKET_BACK.'buttons');
define('HIKAMARKET_CLASS',HIKAMARKET_BACK.'classes'.DS);
define('HIKAMARKET_INC',HIKAMARKET_BACK.'inc'.DS);
define('HIKAMARKET_VIEW',HIKAMARKET_BACK.'views'.DS);
define('HIKAMARKET_TYPE',HIKAMARKET_BACK.'types'.DS);
define('HIKAMARKET_MEDIA',HIKAMARKET_ROOT.'media'.DS.HIKAMARKET_COMPONENT.DS);
define('HIKAMARKET_DBPREFIX','#__hikamarket_');

define('HIKAMARKET_NAME','HikaMarket');
define('HIKAMARKET_LNAME','hikamarket');
define('HIKAMARKET_TEMPLATE',HIKASHOP_FRONT.'templates'.DS);
define('HIKAMARKET_URL','https://www.hikashop.com/');
define('HIKAMARKET_UPDATEURL',HIKAMARKET_URL.'index.php?option=com_updateme&ctrl=update&task=');
define('HIKAMARKET_HELPURL',HIKAMARKET_URL.'index.php?option=com_updateme&ctrl=doc&component='.HIKAMARKET_LNAME.'&page=');
define('HIKAMARKET_REDIRECT',HIKAMARKET_URL.'index.php?option=com_updateme&ctrl=redirect&page=');

$app = JFactory::getApplication();
$isAdmin = version_compare(JVERSION,'4.0','<') ? $app->isAdmin() : $app->isClient('administrator');
if($isAdmin) {
	define('HIKAMARKET_CONTROLLER',HIKAMARKET_BACK.'controllers'.DS);
	define('HIKAMARKET_IMAGES','../media/'.HIKAMARKET_COMPONENT.'/images/');
	define('HIKAMARKET_CSS','../media/'.HIKAMARKET_COMPONENT.'/css/');
	define('HIKAMARKET_JS','../media/'.HIKAMARKET_COMPONENT.'/js/');
	define('HIKAMARKET_SWF','../media/'.HIKAMARKET_COMPONENT.'/swf/');
	$css_type = 'backend';
} else {
	define('HIKAMARKET_CONTROLLER',HIKAMARKET_FRONT.'controllers'.DS);
	define('HIKAMARKET_IMAGES',JURI::base(true).'/media/'.HIKAMARKET_COMPONENT.'/images/');
	define('HIKAMARKET_CSS',JURI::base(true).'/media/'.HIKAMARKET_COMPONENT.'/css/');
	define('HIKAMARKET_JS',JURI::base(true).'/media/'.HIKAMARKET_COMPONENT.'/js/');
	define('HIKAMARKET_SWF',JURI::base(true).'/media/'.HIKAMARKET_COMPONENT.'/swf/');
	$css_type = 'frontend';
}
$lang = JFactory::getLanguage();
$lang->load(HIKAMARKET_COMPONENT, JPATH_SITE);

class hikamarket {

	private static $configCache = array();

	public static function get($name) {
		$namespace = 'hikamarket';
		if(substr($name,0,5) == 'shop.') {
			$namespace = 'hikashop';
			$name = substr($name,5);
		}
		if(substr($name,0,7) == 'serial.') {
			$namespace = 'hikaserial';
			$name = substr($name,7);
		}
		list($group,$class) = explode('.',$name,2);
		if($group == 'controller') {
			if($namespace == 'hikamarket')
				$className = $class . 'Market' . ucfirst($group);
			else if($namespace == 'hikaserial')
				$className = $class . 'Serial' . ucfirst($group);
			else
				$className = $class . ucfirst($group);
		} elseif(strpos($class, '-') === false) {
			$className = $namespace . ucfirst($class) . ucfirst($group);
		} else {
			$blocks = explode('-', $class);
			$blocks = array_map('ucfirst', $blocks);
			$className = $namespace . implode('', $blocks) . ucfirst($group);
		}
		if(class_exists($className.'Override'))
			$className .= 'Override';
		if(!class_exists($className)) {
			$class = str_replace('-', DS, $class);
			$const = constant(strtoupper($namespace . '_' . $group));
			$app = JFactory::getApplication();
			$path = JPATH_THEMES.DS.$app->getTemplate().DS.'html'.DS.'com_'.$namespace.DS.'administrator'.DS;
			$constOverride = str_replace(constant(strtoupper($namespace.'_BACK')), $path, $const);

			jimport('joomla.filesystem.file');
			if(JFile::exists($constOverride . $class . '.override.php')) {
				$originalFile = $const.$class.'.php';
				include_once($constOverride . $class . '.override.php');
				$className .= 'Override';
			} elseif(JFile::exists($const . $class . '.php')) {
				include_once $const . $class . '.php';
			} elseif($group == 'controller') {
				self::getPluginController($class);
			}
			if(!class_exists($className)) {
				return null;
			}
		}

		$args = func_get_args();
		array_shift($args);
		switch(count($args)) {
			case 4:
				return new $className($args[0], $args[1], $args[2], $args[3]);
			case 3:
				return new $className($args[0], $args[1], $args[2]);
			case 2:
				return new $className($args[0], $args[1]);
			case 1:
				return new $className($args[0]);
		}
		return new $className();
	}

	public static function &config($market = true, $reload = false) {
		if(self::$configCache === null)
			self::$configCache = array();
		if(!$market) {
			if(!isset(self::$configCache['shop']) || self::$configCache['shop'] === null)
				self::$configCache['shop'] =& hikashop_config();
			if(self::$configCache['shop'] === null || $reload || !is_object(self::$configCache['shop']) || self::$configCache['shop']->get('configClassInit', 0) == 0) {
				self::$configCache['shop'] = self::get('shop.class.config');
				if( self::$configCache['shop'] === null ) die(HIKASHOP_NAME.' config not found');
				self::$configCache['shop']->load();
			}
			return self::$configCache['shop'];
		}
		if(!isset(self::$configCache['market']) || self::$configCache['market'] === null || $reload){
			self::$configCache['market'] = self::get('class.config');
			if( self::$configCache['market'] === null ) die(HIKAMARKET_NAME.' config not found');
			self::$configCache['market']->load();
		}
		return self::$configCache['market'];
	}

	public static function level($level) {
		$config = self::config();
		return ($config->get($config->get('level'), 0) >= $level);
	}

	public static function import($type, $name, $dispatcher = null) {
		$type = preg_replace('#[^A-Z0-9_\.-]#i', '', $type);
		$name = preg_replace('#[^A-Z0-9_\.-]#i', '', $name);
		$path = JPATH_PLUGINS.DS.$type.DS.$name.DS.$name.'.php';

		$instance = false;
		if(file_exists($path)) {
			require_once($path);
			$className = 'plg'.$type.$name;
			if(class_exists($className)) {
				if($dispatcher == null) {
					if(defined('HIKASHOP_J40') && HIKASHOP_J40)
						$dispatcher = JFactory::getContainer()->get('dispatcher');
					else
						$dispatcher = JDispatcher::getInstance();
				}
				$instance = new $className($dispatcher, array('name' => $name, 'type' => $type));
			}
		}
		return $instance;
	}

	public static function completeLink($link, $popup = false, $redirect = false, $js = false) {
		$namespace = HIKAMARKET_COMPONENT;
		if(substr($link,0,5)=='shop.'){
			$namespace = HIKASHOP_COMPONENT;
			$link=substr($link,5);
		}
		if($popup === 'ajax') $link .= '&tmpl=raw';
		else if($popup) $link .= '&tmpl=component';
		$ret = JRoute::_('index.php?option='.$namespace.'&ctrl='.$link, !$redirect);
		if(!$js)
			return $ret;

		if(strpos($link, '{') !== false && strpos($ret, '{') === false)
			return JURI::root(true) . '/index.php?option='.$namespace.'&ctrl='.$link;
		return str_replace('&amp;', '&', $ret);
	}

	public static function table($name, $component = true) {
		if( $component === true || $component === 'market' ) {
			if( substr($name, 0, 5) == 'shop.' ) {
				return HIKASHOP_DBPREFIX . substr($name, 5);
			}
			if( substr($name, 0, 7) == 'joomla.' ) {
				return '#__'.substr($name, 7);
			}
			return HIKAMARKET_DBPREFIX . $name;
		}
		if( $component === 'shop' ) {
			return HIKASHOP_DBPREFIX . $name;
		}
		return '#__'.$name;
	}

	public static function secureField($fieldName) {
		if (!is_string($fieldName) || preg_match('|[^a-z0-9#_.-]|i',$fieldName) !== 0 ){
			die('field "'.$fieldName .'" not secured');
		}
		return $fieldName;
	}

	public static function limitString($string, $limit, $replacement = '...', $tooltip = false) {
		if(empty($string) || !is_string($string))
			return '';
		$l = strlen($string);
		if($l <= $limit)
			return $string;

		$nbExtra = $l - $limit + strlen($replacement);
		$new_string = substr($string, 0, $l - ceil(($l + $nbExtra) / 2)) . $replacement . substr($string, floor(($l + $nbExtra) / 2));
		if($tooltip)
			return hikamarket::tooltip($string, '', '', $new_string, '', 0);
		return $new_string;
	}

	public static function getLayout($controller, $layout, $params, &$js) {
		$app = JFactory::getApplication();
		$component = HIKAMARKET_COMPONENT;
		$base_path = HIKAMARKET_FRONT;
		if(hikamarket::isAdmin()) {
			$base_path = HIKAMARKET_BACK;
		}
		if( substr($controller, 0, 5) == 'shop.' ) {
			$controller = substr($controller, 5);
			$component = HIKASHOP_COMPONENT;
			$base_path = HIKASHOP_FRONT;
			if(hikamarket::isAdmin()) {
				$base_path = HIKASHOP_BACK;
			}
		}
		$base_path = rtrim($base_path, DS);
		$document = JFactory::getDocument();

		$ctrl = new HikaShopBridgeController(array(
			'name' => $controller,
			'base_path' => $base_path
		));
		$viewType = $document->getType();

		$view = $ctrl->getView('', $viewType, '', array('base_path' => $base_path));
		$folder	= $base_path.DS.'views'.DS.$view->getName().DS.'tmpl';
		$view->addTemplatePath($folder);
		$folder	= JPATH_BASE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.$component.DS.$view->getName();
		$view->addTemplatePath($folder);
		$old = $view->setLayout($layout);
		ob_start();
		$view->display(null, $params);
		$js = @$view->js;
		if(!empty($old))
			$view->setLayout($old);
		return ob_get_clean();
	}

	public static function getPluginController($ctrl) {
		if(empty($ctrl))
			return false;

		$app = JFactory::getApplication();
		JPluginHelper::importPlugin('hikamarket');
		JPluginHelper::importPlugin('hikashop');
		$controllers = $app->triggerEvent('onHikamarketPluginController', array($ctrl));

		if(empty($controllers))
			return false;
		foreach($controllers as $k => &$c) {
			if(!is_array($c) && is_string($c))
				$c = array('name' => $c);
			if(empty($c['name'])) {
				unset($controllers[$k]);
				continue;
			}
			if(empty($c['type']))
				$c['type'] = 'hikamarket';
		}
		unset($c);

		if(count($controllers) > 1)
			return false;

		$controller = reset($controllers);

		if(empty($controller['prefix']))
			$controller['prefix'] = 'ctrl';

		$type = preg_replace('#[^A-Z0-9_\.-]#i', '', $controller['type']);
		$name = preg_replace('#[^_A-Z0-9_\.-]#i', '', $controller['name']);
		$prefix = preg_replace('#[^_A-Z0-9]#i', '', $controller['prefix']);
		$path = JPATH_PLUGINS.DS.$type.DS.$name.DS;

		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		if(!empty($controller['component']) && preg_match('#^com_[_a-zA-Z0-9]+$#', $controller['component'])) {
			$path = rtrim(JPATH_SITE,DS).DS.'components'.DS.$controller['component'].DS;
			$file = isset($controller['file']) ? preg_replace('#[^A-Z0-9_\.-]#i', '', $controller['file']) : $name;
			if(JFile::exists($path.'controllers'.DS.$file.'.php') && JFolder::exists($path.'views'.DS.$name.'market'.DS)) {
				include_once($path.'controllers'.DS.$file.'.php');
				return true;
			}
		}

		if(!JFile::exists($path.$name.'_'.$prefix.'.php') || !JFolder::exists($path.'views'.DS))
			return false;

		include_once($path.$name.'_'.$prefix.'.php');
		return true;
	}

	public static function getCID($field = '', $int = true) {
		$res = hikaInput::get()->get('cid', array(), 'array');
		if(!empty($res))
			$res = reset($res);
		if(empty($res) && !empty($field))
			$res = hikaInput::get()->getCmd($field, 0);
		if($int)
			return intval($res);
		return $res;
	}

	public static function getMenu($title = '', $tpl = null) {
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$base_path = rtrim(HIKASHOP_BACK, DS);
		$controller = new HikaShopBridgeController(
			array(
				'base_path' => $base_path,
				'name' => 'menu'
			)
		);
		$viewType = $document->getType();
		$view = $controller->getView('', $viewType, '', array('base_path' => $base_path));
		$folder	= JPATH_BASE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.HIKASHOP_COMPONENT.DS.$view->getName();
		$view->addTemplatePath($folder);
		$view->setLayout('default');
		ob_start();
		$view->display($tpl, $title);
		return ob_get_clean();
	}

	public static function setTitle($name, $picture, $link) {
		$shopConfig = hikamarket::config(false);
		$menu_style = $shopConfig->get('menu_style', 'title_bottom');
		if(HIKASHOP_J30) $menu_style = 'content_top';
		$html = '<a class="hikamarket_title_link hikamarket_title_j'.(int)HIKASHOP_JVERSION.'" href="'.hikamarket::completeLink($link).'">'.$name.'</a>';
		if($menu_style != 'content_top') {
			$html = hikamarket::getMenu($html);
		}
		JToolBarHelper::title('<i class="fas fa-'.$picture.' hika-title-icons"></i>'.$html, ' hika-hide');

		$doc = JFactory::getDocument();
		$app = JFactory::getApplication();
		$doc->setTitle($app->getCfg('sitename'). ' - ' .JText::_('JADMINISTRATION').' - '.$name);
	}

	public static function footer() {
		$app = JFactory::getApplication();
		$config = self::config();
		$shopConfig = self::config(false);

		if(!self::isAdmin() && $shopConfig->get('show_footer',true)=='-1') return '';

		$description = $config->get('description_'.strtolower($config->get('level')),'Joomla!<sup>&reg;</sup> Ecommerce System');
		$link = HIKAMARKET_URL;
		$aff = $shopConfig->get('partner_id');
		if(!empty($aff)){
			$link.='?partner_id='.$aff;
		}
		$text = '<!-- HikaMarket Component powered by '.$link.' -->'."\r\n".'<!-- version '.$config->get('level').' : '.$config->get('version').' -->';
		if(hikaInput::get()->getInt('marketbuild',0)) $text .= '<!-- 2206222240 -->';
		if(!$shopConfig->get('show_footer',true))
			return $text;

		$text .= '<div class="hikamarket_footer" style="text-align:center" align="center"><a href="'.$link.'" target="_blank" title="'.HIKAMARKET_NAME.' : '.strip_tags($description).'">'.HIKAMARKET_NAME;
		if(self::isAdmin()) {
			$text .= ' '.$config->get('level').' '.$config->get('version');
		}
		$text .= ', '.$description.'</a></div>'."\r\n";
		return $text;
	}

	public static function initModule() {
		static $done = false;
		$fe = hikaInput::get()->getInt('hikamarket_front_end_main', 0);
		if($done || !empty($fe))
			return true;

		$done = true;
		$lang = JFactory::getLanguage();
		$override_path = hikashop_getLanguagePath(JPATH_ROOT).'/overrides/'.$lang->getTag().'.override.ini';
		$lang->load(HIKASHOP_COMPONENT, JPATH_SITE);
		if(file_exists($override_path))
			hikashop_loadTranslationFile($override_path);
		return true;
	}

	public static function tooltip($desc, $title = '', $image = 'tooltip.png', $name = '', $href = '', $link = 1) {
		self::loadJslib('tooltip');
		$desc = htmlspecialchars($desc, ENT_COMPAT, 'UTF-8');
		$title = htmlspecialchars($title, ENT_COMPAT, 'UTF-8');
		if(!$name) $name = JHTML::_('image', $image, 'Tooltip', null, true);
		if($href) $name = '<a href="' . $href . '">' . $name . '</a>';
		if($title) $desc = '&lt;strong&gt;'.$title.'&lt;/strong&gt;&lt;br/&gt;' . $desc;
		return '<span data-toggle="hk-tooltip" data-title="' . $desc . '">' . $name . '</span>';
	}

	public static function cancelBtn($url = '') {
		$cancel_url = hikaInput::get()->getString('cancel_redirect');
		if(!empty($cancel_url) || !empty($url)) {
			$toolbar = JToolBar::getInstance('toolbar');
			if(!empty($cancel_url))
				$toolbar->appendButton('Link', 'cancel', JText::_('HIKA_CANCEL'), base64_decode($cancel_url) );
			else
				$toolbar->appendButton('Link', 'cancel', JText::_('HIKA_CANCEL'), $url );
		} else {
			JToolBarHelper::cancel();
		}
	}

	public static function loadVendor($full = false, $reset = false) {
		static $vendor = null;
		$app = JFactory::getApplication();

		if($reset) {
			$app->setUserState(HIKAMARKET_COMPONENT.'.vendor_id', null);
			$vendor = null;
			return true;
		}

		if(!isset($vendor)) {
			$user_id = self::loadUser(false, false);
			$vendor_id = (int)$app->getUserState(HIKAMARKET_COMPONENT.'.vendor_id');

			if(empty($user_id)) {
				if($vendor_id != 0)
					$app->setUserState(HIKAMARKET_COMPONENT.'.vendor_id', null);
				return null;
			}

			if($vendor_id == -1) {
				$vendor = false;
				return null;
			}

			$vendorClass = self::get('class.vendor');
			if(empty($vendor_id)) {
				$vendor = $vendorClass->get($user_id, 'user');
			} else {
				$vendor = $vendorClass->get($vendor_id);
			}

			if($vendor === null) {
				$user = JFactory::getUser();
				$administrator = $user->authorise('core.admin');

				if($administrator) {
					$vendor = $vendorClass->get(1);
				}
			}

			if($vendor === null) {
				$config = hikamarket::config();
				$auto_registration = (int)$config->get('allow_registration', 0);
				if($auto_registration === 3) {
					$shopuser = self::loadUser(true);
					if(!empty($shopuser)) {
						$vendor_creation = $vendorClass->onAfterUserCreate($shopuser, true);
						if(!empty($vendor_creation))
							$vendor = $vendorClass->get($vendor_creation->vendor_id);
					}
				}
			}
		}

		if($vendor === null || $vendor === false) {
			$vendor = false;
			$app->setUserState(HIKAMARKET_COMPONENT.'.vendor_id', -1);
			return null;
		}

		$app->setUserState(HIKAMARKET_COMPONENT.'.vendor_id', $vendor->vendor_id);

		if($full)
			return $vendor;
		return $vendor->vendor_id;
	}

	public static function loginVendor() {
		$vendor = self::loadVendor(true, false);
		$app = JFactory::getApplication();

		if($vendor === null) {
			$user = JFactory::getUser();
			if($user->guest) {
				$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));
				global $Itemid;
				$url = '';
				if(!empty($Itemid)) { $url = '&Itemid=' . $Itemid; }
				$url = 'index.php?option=com_users&view=login' . $url;
				$app->redirect(JRoute::_($url . '&return='.urlencode(base64_encode(hikamarket::currentUrl())), false));
				return false;
			} else {
				$app->enqueueMessage(JText::_('PLEASE_LOGIN_AS_VENDOR'));
				$app->redirect('index.php');
				return false;
			}
		} else if($vendor->vendor_id > 1 && $vendor->vendor_published == 0) {
			$app->enqueueMessage(JText::_('VENDOR_EXPIRED'));
			$app->redirect('index.php');
			return false;
		}
		return true;
	}

	public static function isVendorProduct($product_id, $vendor_id = -1) {
		static $vendorProductCache = array();

		if($vendor_id === null || $vendor_id == -1) {
			$vendor_id = self::loadVendor(false, false);
			if($vendor_id == null)
				return false;
		}

		if(!empty($vendor_id) && (int)$vendor_id == 0)
			return false;
		if(!empty($product_id) && (int)$product_id <= 0)
			return false;

		if($vendor_id == 1)
			return true;

		if((int)$vendor_id > 1 && (int)$product_id > 0) {
			if(empty($vendorProductCache[$vendor_id]))
				$vendorProductCache[$vendor_id] = array();

			if(isset($vendorProductCache[$vendor_id][$product_id]))
				return $vendorProductCache[$vendor_id][$product_id];

			$db = JFactory::getDBO();
			$query = 'SELECT count(pr.product_id) '.
					' FROM ' . hikamarket::table('shop.product') . ' AS pr '.
					' LEFT JOIN ' . hikamarket::table('shop.product') . ' AS pa ON (pr.product_type = ' . $db->Quote('variant') . ' AND pa.product_id = pr.product_parent_id) '.
					' WHERE pr.product_id = '. (int)$product_id . ' AND (pr.product_vendor_id = ' . (int)$vendor_id . ' OR (pr.product_vendor_id = 0 AND pa.product_vendor_id = ' . (int)$vendor_id . '))';
			$db->setQuery($query);
			$vendorProductCache[$vendor_id][$product_id] = ((int)$db->loadResult() == 1);

			return $vendorProductCache[$vendor_id][$product_id];
		}
		return true;
	}

	public static function isVendorCategory($category_id, $vendor_id = -1, $check_extra_categories = false) {
		static $vendorCategoryCache = array();

		if($vendor_id === null || $vendor_id == -1) {
			$vendor_id = self::loadVendor(false, false);
			if($vendor_id == null)
				return false;
		} else
			$vendor_id = (int)$vendor_id;

		if(!empty($vendor_id) && (int)$vendor_id == 0)
			return false;
		if(!empty($category_id) && (int)$category_id <= 0)
			return false;

		if($vendor_id == 1)
			return true;

		$config = hikamarket::config();
		$vendor_chroot_category = (int)$config->get('vendor_chroot_category', 0);
		$chroot_category = (int)$config->get('vendor_root_category', 0);

		if($vendor_id > 1 && $category_id == 0)
			return ( ($vendor_chroot_category == 0) || ($vendor_chroot_category == 2 && ($chroot_category == 0 || $chroot_category == 1)) );

		if($vendor_id > 1 && $category_id > 0) {
			if($vendor_chroot_category == 0)
				return true;

			$db = JFactory::getDBO();

			if(!in_array($vendor_chroot_category, array(1,2)))
				return false;

			if(empty($vendorCategoryCache[$vendor_id]))
				$vendorCategoryCache[$vendor_id] = array();

			$keyCategory = (int)$category_id;
			if($check_extra_categories)
				$keyCategory .= 'ex';

			if(isset($vendorCategoryCache[$vendor_id][$keyCategory]))
				return $vendorCategoryCache[$vendor_id][$keyCategory];

			$extra_categories = array();
			if($check_extra_categories) {
				$vendorClass = self::get('class.vendor');
				$vendor = self::loadVendor(true, false);
				$extra_categories = $vendorClass->getExtraCategories($vendor);
				if(!empty($extra_categories)) {
					if($vendor_chroot_category == 2)
						$extra_categories = array_merge(array((int)$chroot_category), $extra_categories);
					hikamarket::toInteger($extra_categories);
				}
			}

			$query = 'SELECT cat.category_id '.
					' FROM '.hikamarket::table('shop.category').' AS cat '.
					' INNER JOIN '.hikamarket::table('shop.category').' AS rootcat ON (cat.category_left >= rootcat.category_left AND cat.category_right <= rootcat.category_right) '.
					' WHERE cat.category_id = ' . (int)$category_id;

			if($vendor_chroot_category == 1) {
				if(empty($extra_categories))
					$query .= ' AND rootcat.category_namekey = '. $db->Quote('vendor_'.$vendor_id);
				else
					$query .= ' AND (rootcat.category_namekey = '. $db->Quote('vendor_'.$vendor_id) . ' OR rootcat.category_id IN ('.implode(',', $extra_categories).'))';
			} else if($vendor_chroot_category == 2) {
				if(empty($extra_categories))
					$query .= ' AND rootcat.category_id = ' . (int)$chroot_category;
				else
					$query .= ' AND rootcat.category_id IN (' . implode(',', $extra_categories) . ')';
			}

			$db->setQuery($query);
			$ret = $db->loadResult();

			$vendorCategoryCache[$vendor_id][$keyCategory] = !empty($ret);
			return $vendorCategoryCache[$vendor_id][$keyCategory];
		}
		return true;
	}

	public static function isVendorOrder($order_id, $vendor_id = -1) {
		static $vendorOrderCache = array();

		if($vendor_id === null || $vendor_id == -1) {
			$vendor_id = self::loadVendor(false, false);
			if($vendor_id == null)
				return false;
		} else
			$vendor_id = (int)$vendor_id;

		if(!empty($vendor_id) && (int)$vendor_id == 0)
			return false;
		if(!empty($order_id) && (int)$order_id <= 0)
			return false;

		if($vendor_id == 1)
			return true;

		if($vendor_id > 1 && $order_id > 0) {
			if(empty($vendorOrderCache[$vendor_id]))
				$vendorOrderCache[$vendor_id] = array();

			if(isset($vendorOrderCache[$vendor_id][$order_id]))
				return $vendorOrderCache[$vendor_id][$order_id];

			$db = JFactory::getDBO();
			$query = 'SELECT count(hko.order_id) FROM ' . hikamarket::table('shop.order') . ' AS hko '.
				' WHERE hko.order_type IN (\'sale\',\'subsale\') AND hko.order_id = '. (int)$order_id . ' AND hko.order_vendor_id = ' . (int)$vendor_id;
			$db->setQuery($query);
			$vendorOrderCache[$vendor_id][$order_id] = ((int)$db->loadResult() == 1);

			return $vendorOrderCache[$vendor_id][$order_id];
		}
		return true;
	}

	public static function isEditableOrder($order_id) {
		static $editableOrderCache = array();

		if((int)$order_id <= 0)
			return false;

		if(isset($editableOrderCache[$order_id]))
			return $editableOrderCache[$order_id];

		$db = JFactory::getDBO();
		$query = 'SELECT o.order_type, COUNT(DISTINCT op.order_vendor_id) as vendor_count '.
			' FROM ' . hikamarket::table('shop.order') . ' AS o '.
			' LEFT JOIN ' . hikamarket::table('shop.order') . ' AS op ON (o.order_parent_id > 0 AND o.order_parent_id = op.order_parent_id AND op.order_type = ' . $db->Quote('subsale') . ') ' .
			' WHERE (o.order_id = ' . (int)$order_id . ')';
		$db->setQuery($query);
		$ret = $db->loadObject();

		$editableOrderCache[$order_id] = ((int)$ret->vendor_count == 1 || $ret->order_type == 'sale');

		return $editableOrderCache[$order_id];
	}

	public static function isVendorDiscount($discount_id, $vendor_id = -1) {
		static $vendorDiscountCache = array();

		if($vendor_id === null || $vendor_id == -1) {
			$vendor_id = self::loadVendor(false, false);
			if($vendor_id == null)
				return false;
		} else
			$vendor_id = (int)$vendor_id;

		if(!empty($vendor_id) && (int)$vendor_id == 0)
			return false;
		if(!empty($discount_id) && (int)$discount_id <= 0)
			return false;

		if($vendor_id == 1)
			return true;

		if($vendor_id > 1 && $discount_id > 0) {
			if(empty($vendorDiscountCache[$vendor_id]))
				$vendorDiscountCache[$vendor_id] = array();

			if(isset($vendorDiscountCache[$vendor_id][$discount_id]))
				return $vendorDiscountCache[$vendor_id][$discount_id];

			$db = JFactory::getDBO();
			$query = 'SELECT count(a.discount_id) FROM ' . hikamarket::table('shop.discount') . ' AS a WHERE a.discount_id = '. (int)$discount_id . ' AND a.discount_target_vendor = ' . (int)$vendor_id;
			$db->setQuery($query);
			$vendorDiscountCache[$vendor_id][$discount_id] = ((int)$db->loadResult() == 1);

			return $vendorDiscountCache[$vendor_id][$discount_id];
		}
		return true;
	}

	public static function isVendorCharacteristic($characteristic_id, $characteristic_parent = 0, $vendor_id = -1) {
		static $vendorCharacteristicCache = array();

		if($vendor_id === null || $vendor_id == -1) {
			$vendor_id = self::loadVendor(false, false);
			if($vendor_id == null)
				return false;
		} else
			$vendor_id = (int)$vendor_id;

		if(!empty($vendor_id) && (int)$vendor_id == 0)
			return false;
		if(!empty($characteristic_id) && (int)$characteristic_id <= 0)
			return false;

		if(empty($vendorCharacteristicCache[$vendor_id]))
			$vendorCharacteristicCache[$vendor_id] = array();

		if(isset($vendorCharacteristicCache[$vendor_id][$characteristic_id]))
			return $vendorCharacteristicCache[$vendor_id][$characteristic_id];

		$characteristic_parent = (int)$characteristic_parent;
		if((int)$characteristic_parent > 0) {
			$r = self::isVendorCharacteristic($characteristic_parent, 0, $vendor_id);
			if(!$r)
				$r = self::isVendorCharacteristic($characteristic_parent, 0, 1);
			if(!$r) {
				$vendorCharacteristicCache[$vendor_id][$characteristic_id] = false;
				return false;
			}
		}

		$vendor_ids = array((int)$vendor_id);
		if($vendor_id == 1)
			$vendor_ids[] = 0;

		$db = JFactory::getDBO();
		$query = 'SELECT count(c.characteristic_id) FROM ' . hikamarket::table('shop.characteristic') . ' AS c '.
			' WHERE c.characteristic_id = '. (int)$characteristic_id . ' AND c.characteristic_parent_id = ' . (int)$characteristic_parent .
			' AND c.characteristic_vendor_id IN (' . implode(',', $vendor_ids) . ')';
		$db->setQuery($query);
		$vendorCharacteristicCache[$vendor_id][$characteristic_id] = ((int)$db->loadResult() == 1);

		return $vendorCharacteristicCache[$vendor_id][$characteristic_id];
	}

	public static function isVendorPlugin($plugin_id, $plugin_type = '', $vendor_id = -1) {
		static $vendorPluginCache = array();

		if(empty($plugin_type))
			return false;

		if($vendor_id === null || $vendor_id == -1) {
			$vendor_id = self::loadVendor(false, false);
			if($vendor_id == null)
				return false;
		} else
			$vendor_id = (int)$vendor_id;

		if(!empty($vendor_id) && (int)$vendor_id == 0)
			return false;
		if(!empty($plugin_id) && (int)$plugin_id <= 0)
			return false;

		if($vendor_id == 1)
			return true;

		$plugin_id = (int)$plugin_id;
		$config = self::config();
		$plugin_vendor_config = (int)$config->get('plugin_vendor_config', 0);
		if($plugin_vendor_config == 0)
			return false;

		if(empty($vendorPluginCache[$vendor_id]))
			$vendorPluginCache[$vendor_id] = array();
		if(empty($vendorPluginCache[$vendor_id][$plugin_type]))
			$vendorPluginCache[$vendor_id][$plugin_type] = array();

		$types = array(
			'plugin' => array(
				'id' => 'plugin_id',
				'table' => 'shop.plugin',
				'key' => 'plugin_vendor_id'
			),
			'payment' => array(
				'id' => 'payment_id',
				'table' => 'shop.payment',
				'key' => 'payment_vendor_id'
			),
			'shipping' => array(
				'id' => 'shipping_id',
				'table' => 'shop.shipping',
				'key' => 'shipping_vendor_id'
			)
		);

		if(empty($types[$plugin_type]))
			return false;

		$p = $types[$plugin_type];

		if(isset($vendorPluginCache[$vendor_id][$plugin_type][$plugin_id]))
			return $vendorPluginCache[$vendor_id][$plugin_type][$plugin_id];

		if($plugin_vendor_config == 1) {
			$db = JFactory::getDBO();
			$query = 'SELECT count(p.'.$p['id'].') FROM ' . hikamarket::table($p['table']) . ' AS p '.
				' WHERE p.'.$p['id'].' = ' . (int)$plugin_id . ' AND p.'.$p['key'].' = ' . (int)$vendor_id;
			$db->setQuery($query);
			$vendorPluginCache[$vendor_id][$plugin_type][$plugin_id] = ((int)$db->loadResult() == 1);

			return $vendorPluginCache[$vendor_id][$plugin_type][$plugin_id];
		}

		return false;
	}

	public static function isVendorCustomer($customer_id, $vendor_id = -1) {
		static $vendorCustomerCache = array();

		if($vendor_id === null || $vendor_id == -1) {
			$vendor_id = self::loadVendor(false, false);
			if($vendor_id == null)
				return false;
		} else
			$vendor_id = (int)$vendor_id;

		if(!empty($vendor_id) && (int)$vendor_id == 0)
			return false;
		if(!empty($customer_id) && (int)$customer_id <= 0)
			return false;

		if($vendor_id == 1)
			return true;

		if($vendor_id > 1 && $customer_id > 0) {
			if(empty($vendorCustomerCache[$vendor_id]))
				$vendorCustomerCache[$vendor_id] = array();

			if(isset($vendorCustomerCache[$vendor_id][$customer_id]))
				return $vendorCustomerCache[$vendor_id][$customer_id];

			$db = JFactory::getDBO();
			$query = 'SELECT count(cv.customer_id) FROM ' . hikamarket::table('customer_vendor') . ' AS cv WHERE cv.customer_id = '. (int)$customer_id . ' AND cv.vendor_id = ' . (int)$vendor_id;
			$db->setQuery($query);
			$vendorCustomerCache[$vendor_id][$customer_id] = ((int)$db->loadResult() == 1);

			return $vendorCustomerCache[$vendor_id][$customer_id];
		}
		return true;
	}

	public static function getCartVendors() {
		static $currentCart = null;

		if($currentCart == null) {
			$cartClass = hikamarket::get('shop.class.cart');
			$currentCart = $cartClass->loadFullCart(true);
		}

		if(empty($currentCart->products))
			return null;

		$vendors = array();
		foreach($currentCart->products as $product) {
			$p = (int)@$product->product_vendor_id;
			if($p == 1)
				$p = 0;

			$vendors[$p] = $p;
		}

		if(count($vendors) == 1)
			return reset($vendors);
		return $vendors;
	}

	public static function acl($action) {
		$action = strtolower($action);

		$user_access = self::getAclUser(null);
		if($user_access === false)
			return false;

		$vendor_access = self::getAclVendor(null);
		if($vendor_access === false)
			return false;

		if(strpos($action, '/') === false)
			$action = str_replace('_', '/', $action);

		$ret = false;
		if(!empty($user_access)) {
			$ret = self::aclTest($action, $user_access);
			if($ret === false)
				return false;
		}

		$vendor_ret = self::aclTest($action, $vendor_access);

		if($vendor_ret === false)
			return false;
		if($ret === 1 && $vendor_ret !== -1)
			return true;
		if($ret === -1 && $vendor_ret !== -1)
			return true;
		if($vendor_ret)
			return true;
		return false;
	}

	public static function aclOr($actions) {
		if(!is_array($actions) || empty($actions) || self::getAclUser(null) === false || self::getAclVendor(null) === false)
			return false;
		foreach($actions as $action) {
			$r = self::acl($action);
			if($r === true)
				return true;
		}
		return false;
	}

	public static function aclAnd($actions) {
		if(!is_array($actions) || empty($actions) || self::getAclUser(null) === false || self::getAclVendor(null) === false)
			return false;
		foreach($actions as $action) {
			$r = self::acl($action);
			if($r === false)
				return false;
		}
		return true;
	}

	public static function getAclUser($user = null) {
		if($user === null)
			$user = self::loadUser(true, false);

		if(!empty($user) && is_int($user) && (int)$user > 0) {
			$userClass = self::get('shop.class.user');
			$user = $userClass->get( (int)$user );
			if(empty($user))
				$user = null;
		}

		if($user === null)
			return false;

		static $user_accesses = array();
		$user_access = array();

		if(isset($user_accesses[(int)$user->user_id]))
			$user_access = $user_accesses[(int)$user->user_id];

		if(!empty($user_access))
			return $user_access;

		if(!empty($user->user_vendor_access)) {
			if($user->user_vendor_access == 'all')
				$user->user_vendor_access = '*';
			if(!empty($user->user_vendor_access) && strpos($user->user_vendor_access, '/') === false) // Compat
				$user->user_vendor_access = str_replace('_', '/', $user->user_vendor_access);
			$user_access = explode(',', trim(strtolower($user->user_vendor_access), ','));
			sort($user_access, SORT_STRING);
		}
		$user_accesses[(int)$user->user_id] = $user_access;
		return $user_access;
	}

	public static function getAclVendor($vendor = null) {
		if($vendor === null)
			$vendor = self::loadVendor(true, false);

		if(!empty($vendor) && is_int($vendor) && (int)$vendor > 0) {
			$vendorClass = self::get('class.vendor');
			$vendor = $vendorClass->get( (int)$vendor );
			if(empty($vendor))
				$vendor = null;
		}

		if($vendor === null)
			return false;

		static $vendor_accesses = array();
		$vendor_access = array();

		if(isset($vendor_accesses[(int)$vendor->vendor_id]))
			$vendor_access = $vendor_accesses[(int)$vendor->vendor_id];

		if(!empty($vendor_access))
			return $vendor_access;

		if(!empty($vendor->vendor_access)) {
			if($vendor->vendor_access == 'all')
				$vendor->vendor_access = '*';

			if(is_string($vendor->vendor_access)) {
				if(!empty($vendor->vendor_access) && strpos($vendor->vendor_access, '/') === false) // ACL Compat
					$vendor->vendor_access = str_replace('_', '/', $vendor->vendor_access);
				$vendor_access = explode(',', trim(strtolower($vendor->vendor_access), ','));
			} else {
				$vendor->vendor_access = implode(',', $vendor->vendor_access);
				if(!empty($vendor->vendor_access) && strpos($vendor->vendor_access, '/') === false) // ACL Compat
					$vendor->vendor_access = str_replace('_', '/', $vendor->vendor_access);
				$vendor_access = explode(',', trim(strtolower($vendor->vendor_access), ','));
			}
			sort($vendor_access, SORT_STRING);

			if(reset($vendor_access) == '@0')
				$vendor_access[] = array_shift($vendor_access);

			if(strpos($vendor->vendor_access, '@') !== false) {
				$config = self::config();
				$joomla_acl = self::get('type.joomla_acl');
				$gs = $joomla_acl->getList();
				$groups = array();
				foreach($gs as $g) {
					$groups[$g->id] = $g;
				}
				unset($gs);

				$vendor_extra_access = array();
				foreach($vendor_access as $k => $ax) {
					if(substr($ax,0,1) != '@')
						continue;

					unset($vendor_access[$k]);
					$ax_id = (int)substr($ax,1);
					if($ax_id == 0) {
						$default_access = $config->get('store_default_access', 'all');
						if($default_access == 'all') $default_access = '*';
						if(!empty($default_access) && strpos($default_access, '/') === false) // ACL Compat
							$default_access = str_replace('_', '/', $default_access);
						$accesses = explode(',', trim(strtolower($default_access), ','));
						sort($accesses, SORT_STRING);
						$vendor_extra_access = array_merge($vendor_extra_access, $accesses);
					} else {
						$group = (isset($groups[$ax_id])) ? $group = $groups[$ax_id] : null;
						while(!empty($group)) {
							$default_access = $config->get('vendor_acl_'.$group->id, '');
							if(!empty($default_access) && strpos($default_access, '/') === false) // ACL Compat
								$default_access = str_replace('_', '/', $default_access);
							$accesses = explode(',', trim(strtolower($default_access), ','));
							sort($accesses, SORT_STRING);
							array_push($vendor_extra_access, '-');
							$vendor_extra_access = array_merge($vendor_extra_access, $accesses);
							$group = (isset($groups[$group->parent_id])) ? $group = $groups[$group->parent_id] : null;
						}
					}
				}
				$vendor_access = array_merge($vendor_access, $vendor_extra_access);
			}
		} else {
			$config = self::config();
			$default_access = $config->get('store_default_access', 'all');
			if($default_access == 'all')
				$default_access = '*';
			if(!empty($default_access) && strpos($default_access, '/') === false) // ACL Compat
				$default_access = str_replace('_', '/', $default_access);
			$vendor_access = explode(',', trim(strtolower($default_access), ','));
			sort($vendor_access, SORT_STRING);
		}

		$vendor_accesses[(int)$vendor->vendor_id] = $vendor_access;
		return $vendor_access;
	}

	public static function aclTest($action, $access) {
		$ret = false;
		$actlen = strlen($action);
		if(is_string($access)) {
			$access = explode(',', trim(strtolower($access), ','));
			sort($access, SORT_STRING);
		}
		if(empty($access))
			$access = array();

		$me = 0;
		$parentInc = false; $parentBan = false;
		$childInc = false; $childBan = false;
		foreach($access as $a) {
			if($a == '-' && ($me != 0 || $parentBan || $parentInc))
				break;

			if($a == '*') { $ret = true; continue; }
			if($a == '!') { $ret = false; continue; }
			if($a == $action) { $me = 1; continue; }
			if($a == ('!'.$action)) { $me = -1; continue; }

			$l = strlen($a);
			if((!$childInc || $childInc > $actlen) && substr_compare($action, $a, 0, $actlen) == 0) { $childInc = $actlen; continue; }
			if((!$childBan || $childBan > $actlen) && substr_compare('!'.$action, $a, 0, $actlen + 1) == 0) { $childBan = $actlen; continue; }
			if($l > 0) {
				if((!$parentInc || $parentInc < $l) && substr_compare($a, $action, 0, $l) == 0) { $parentInc = $l; continue; }
				if((!$parentBan || $parentBan < $l) && substr_compare($a, '!'.$action, 0, $l) == 0) { $parentBan = $l - 1; continue; }
			}
		}

		if($me == 1) {
			$ret = true;
		} else if($me == -1) {
			$ret = false;
		} else if($parentBan || $parentInc) {
			if($parentBan && $parentInc) {
				$ret = ($parentInc > $parentBan);
			} else if($parentBan)
				$ret = false;
			else
				$ret = true;
		}
		if($ret && $childBan) { $ret = 1; }
		if(!$ret && $childInc) { $ret = -1; }

		return $ret;
	}

	public static function addACLFilters(&$filters, $field, $table = '', $type = 'user', $allowNull = false, $target_id = 0) {
		if($type == 'user')
			return hikashop_addACLFilters($filters, $field, $table, 2, $allowNull, $target_id);

		if(empty($target_id) || (int)$target_id == 0) {
			$vendor = self::loadVendor(true);
		} else {
			$vendorClass = self::get('class.vendor');
			$vendor = $vendorClass->get($target_id);
		}
		if(empty($vendor) || empty($vendor->vendor_access))
			return;

		if(!empty($table))
			$table.='.';

		$acl_filters = array($table . $field . " = 'all'");
		$vendor_access = explode(',', $vendor->vendor_access);
		foreach($vendor_access as $access) {
			if(substr($access, 0, 1) == '@') {
				$acl_filters[] = $table . $field . " LIKE '%," . (int)substr($access, 1) . ",%'";
			}
		}
		unset($vendor_access);
		if($allowNull)
			$acl_filters[] = 'ISNULL(' . $table . $field . ')';

		$filters[] = '(' . implode(' OR ', $acl_filters) . ')';
	}

	public static function deny($redirect = '', $msg = '', $type = '', $tmpl = '') {
		if(empty($msg))
			$msg = JText::_('HIKAM_PAGE_DENY');
		if(empty($type))
			$type = 'error';

		if(empty($tmpl))
			$tmpl = hikaInput::get()->getString('tmpl', '');

		if($tmpl == 'json') {
			ob_end_clean();
			echo '{err:"' . str_replace(array('\\', '"'), array('\\\\', '\\"'), $msg) . '"}';
			exit;
		}

		$app = JFactory::getApplication();
		$app->enqueueMessage($msg, $type);
		if(empty($redirect)) {
			if($tmpl == 'component') {
				$app->redirect('index.php?tmpl=component');
			}
			$app->redirect('index.php');
			return false;
		}

		if($tmpl == 'component') {
			$app->redirect(hikamarket::completeLink($redirect, true, true));
		}
		$app->redirect(hikamarket::completeLink($redirect, false, true));
		return false;
	}

	public static function getFormToken() {
		static $token = null;
		if($token == null) {
			if(HIKASHOP_J30)
				$token = JSession::getFormToken();
			else
				$token = JUtility::getToken();
		}
		return $token;
	}

	public static function isClient($forceClient = null) {
		static $testsClient = array();
		if($forceClient == null) $forceClient = 'administrator';
		if(isset($testsClient[$forceClient]))
			return $testsClient[$forceClient];
		$app = JFactory::getApplication();
		if(HIKASHOP_J40) {
			$testsClient[$forceClient] = $app->isClient($forceClient);
		} else {
			$testsClient[$forceClient] = ($forceClient == 'administrator') ? $app->isAdmin() : $app->isSite();
		}
		return $testsClient[$forceClient];
	}
	public static function isAdmin() {
		return self::isClient('administrator');
	}

	public static function loadJslib($name) {
		static $marketLibs = array();
		$doc = JFactory::getDocument();
		$name = strtolower($name);
		if(isset($marketLibs[$name]))
			return $marketLibs[$name];

		$ret = true;
		switch($name) {
			case 'otree':
				$doc->addScript(HIKAMARKET_JS.'otree.js?v='.HIKAMARKET_RESSOURCE_VERSION);
				$doc->addStyleSheet(HIKAMARKET_CSS.'otree.css?v='.HIKAMARKET_RESSOURCE_VERSION);
				$ret = true;
				break;
			case 'leaflet':
				$doc->addScript(HIKAMARKET_JS.'leaflet.js?v='.HIKAMARKET_RESSOURCE_VERSION);
				$doc->addStyleSheet(HIKAMARKET_CSS.'leaflet.css?v='.HIKAMARKET_RESSOURCE_VERSION);
				$ret = true;
				break;
			default:
				$ret = false;
				break;
		}

		if(!$ret)
			$ret = hikashop_loadJslib($name);

		$marketLibs[$name] = $ret;
		return $ret;
	}

	public static function orderStatus($order_status) {
		$order_upper = HikaStringHelper::strtoupper(trim($order_status));
		$order_upper = str_replace(array(' ','(',')','[',']','=','"','\''), '_', $order_upper);
		$tmp = 'ORDER_STATUS_' . $order_upper;
		$ret = JText::_($tmp);
		if($ret != $tmp)
			return $ret;
		$ret = JText::_($order_upper);
		if($ret != $order_upper)
			return $ret;
		return $order_status;
	}

	public static function cloning(&$object) {
		if(is_array($object)) {
			$ret = array();
			foreach($object as $k => $v) {
				$ret[$k] = self::cloning($v);
			}
			return $ret;
		}
		if(is_object($object)) {
			$ret = new stdClass();
			foreach(get_object_vars($object) as $k => $v) {
				$ret->$k = self::cloning($v);
			}
			return $ret;
		}
		return $object;
	}

	public static function headerNoCache() {
		if(headers_sent())
			return false;
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
		return true;
	}

	public static function addUserACLFilters(&$filters, $field, $table = '', $level = 2, $allowNull = false, $user_id = 0) {
		if(!empty($level) && !hikashop_level($level))
			return;

		if(empty($user_id) || (int)$user_id == 0) {
			$my = JFactory::getUser();
		} else {
			$userClass = self::get('shop.class.user');
			$hkUser = $userClass->get($user_id);
			$my = JFactory::getUser($hkUser->user_cms_id);
		}

		jimport('joomla.access.access');
		$config = self::config(false);
		$userGroups = JAccess::getGroupsByUser($my->id, (bool)$config->get('inherit_parent_group_access')); //$my->authorisedLevels();

		if(!empty($userGroups)) {
			if(!empty($table))
				$table .= '.';
			$acl_filters = array($table . $field . ' = \'all\'');
			foreach($userGroups as $userGroup) {
				$acl_filters[] = $table . $field . ' LIKE \'%,' . (int)$userGroup . ',%\'';
			}
			if($allowNull)
				$acl_filters[] = 'ISNULL(' . $table . $field . ')';
			$filters[] = '(' . implode(' OR ', $acl_filters) . ')';
		}
	}

	public static function addVendorACLFilters(&$filters, $field, $table = '', $level = 1, $allowNull = false, $vendor_id = 0) {
		if(!empty($level) && !hikamarket::level($level))
			return;

		if(empty($vendor_id) || (int)$vendor_id == 0) {
			$vendor = self::loadVendor(true);
		} else {
			$vendorClass = self::get('class.vendor');
			$vendor = $vendorClass->get($vendor_id);
		}

		if(empty($vendor))
			return;

		$vendorGroups = array();
		$vendor_access = explode(',', trim(strtolower($vendor->vendor_access), ','));
		foreach($vendor_access as $access) {
			if(substr($access, 0, 1) == '@')
				$vendorGroups[] = (int)substr($access, 1);
		}

		if(!empty($table))
			$table .= '.';
		$acl_filters = array($table . $field . ' = \'all\'');
		foreach($vendorGroups as $vendorGroup) {
			$acl_filters[] = $table . $field . ' LIKE \'%,' . (int)$vendorGroup . ',%\'';
		}
		if($allowNull)
			$acl_filters[] = 'ISNULL(' . $table . $field . ')';
		$filters[] = '(' . implode(' OR ', $acl_filters) . ')';
	}

	public static function getProductEditionUrl($product_id, $parameters = '') {
		$config = self::config();

		$edition_itemid = (int)$config->get('edition_default_menu', 0);
		if(empty($edition_itemid))
			$edition_itemid = (int)$config->get('vendor_default_menu', 0);

		$url_itemid = (!empty($edition_itemid)) ? '&Itemid='.$edition_itemid : '';

		if(!empty($parameters))
			$parameters = '&' . $parameters;

		switch($config->get('product_edit_cancel_url', '')) {
			case 'product':
				return hikamarket::completeLink('product&task=edit&cancel_action=product&cid=' . $product_id . $parameters . $url_itemid);
			case 'current_url':
				$current_url = base64_encode(hikamarket::currentUrl());
				return hikamarket::completeLink('product&task=edit&cancel_action=url&cid=' . $product_id . $parameters . '&cancel_url=' . $current_url . $url_itemid);
			case 'listing':
				return hikamarket::completeLink('product&task=edit&cid=' . $product_id . $parameters . $url_itemid);
		}
		return hikamarket::completeLink('product&task=edit&cancel_action=product&cid=' . $product_id . $parameters . $url_itemid);
	}

	public static function toInteger(&$array) {
		if(is_array($array))
			$array = array_map('intval', $array);
		else
			$array = array();
	}

	public static function loadUser($full = false, $reset = false) {
		return hikashop_loadUser($full, $reset);
	}

	public static function isAllowed($allowedGroups, $id = null, $type = 'user') {
		return hikashop_isAllowed($allowedGroups, $id, $type);
	}

	public static function display($messages, $type = 'success', $return = false, $close = true) {
		return hikashop_display($messages, $type, $return, $close);
	}

	public static function createDir($dir, $report = true) {
		return hikashop_createDir($dir, $report);
	}

	public static function search($searchString, $object, $exclude = '') {
		static $displaySearch = null;
		if($displaySearch === null) {
			if(hikamarket::isAdmin()) {
				$displaySearch = true;
			} else {
				$config = self::config();
				$displaySearch = $config->get('display_search', true);
			}
		}
		if(empty($displaySearch))
			return $object;
		return hikashop_search($searchString, $object, $exclude);
	}

	public static function getDate($time = 0, $format = '%d %B %Y %H:%M') {
		return hikashop_getDate($time, $format);
	}

	public static function currentUrl($checkInRequest = '') {
		return hikashop_currentUrl($checkInRequest);
	}

	public static function increasePerf() {
		return hikashop_increasePerf();
	}

	public static function cleanBuffers() {
		return hikashop_cleanBuffers();
	}

	public static function encodeNumber(&$data, $type = 'order', $format = '') {
		return hikashop_encode($data, $type, $format);
	}

	public static function toFloat($val) {
		return hikashop_toFloat($val);
	}

	public static function getTime($date) {
		return hikashop_getTime($date);
	}

	public static function getCurrency() {
		return hikashop_getCurrency();
	}

	public static function getZone($type = 'shipping') {
		return hikashop_getZone($type);
	}

	public static function getEscaped($text, $extra = false) {
		return hikashop_getEscaped($text, $extra);
	}

	public static function cleanUrl($url, $forceInternURL = false) {
		return hikashop_cleanUrl($url, $forceInternURL);
	}

	public static function absoluteUrl($text) {
		return hikashop_absoluteUrl($text);
	}

	public static function setPageTitle($title) {
		return hikashop_setPageTitle($title);
	}

	public static function disallowUrlRedirect($url) {
		return hikashop_disallowUrlRedirect($url);
	}

	public static function unserialize($data) {
		if(!is_string($data))
			return false;
		if(!preg_match_all('#[OC]:[0-9]+:"([-_a-zA-Z0-9]+)":[0-9]+:\{#iU', $data, $matches))
			return unserialize($data);
		if(!empty($matches[1])) {
			foreach($matches[1] as $m) {
				if($m != 'stdClass')
					return false;
			}
		}
		return unserialize($data);
	}

}

class hikamarketController extends hikashopBridgeController {

	protected $type = '';
	protected $publish_return_view = 'listing';
	protected $rights = array(
		'display' => array(),
		'add' => array(),
		'edit' => array(),
		'modify' => array(),
		'delete' => array()
	);
	protected $pluginCtrl = null;
	protected $default_task = 'listing';

	public function __construct($config = array(), $skip = false) {
		if($skip)
			return;

		if(!empty($this->pluginCtrl)) {
			$config['base_path'] = JPATH_PLUGINS.DS.$this->pluginCtrl[0].DS.$this->pluginCtrl[1].DS;
		}

		parent::__construct($config);
		$this->registerDefaultTask($this->default_task);
	}

	protected function renderingLayout($name = '', $options = array()) {
		if(empty($name)) return false;
		if(!empty($options) && isset($options['hidemainmenu'])) {
			hikaInput::get()->set('hidemainmenu', 1);
		}
		hikaInput::get()->set('layout', $name);
		return $this->display();
	}

	public function listing() {
		return $this->renderingLayout('listing');
	}

	public function show() {
		return $this->renderingLayout('show');
	}

	public function edit() {
		return $this->renderingLayout('form', array('hidemainmenu' => 1));
	}

	public function add() {
		return $this->renderingLayout('form', array('hidemainmenu' => 1));
	}

	public function apply() {
		$status = $this->store();
		return $this->edit();
	}

	public function save() {
		$this->store();
		return $this->listing();
	}

	public function store() {
		return false;
	}

	protected function adminStore($token = false) {
		$app = JFactory::getApplication();
		if($token) {
			JSession::checkToken() || die('Invalid Token');
		}
		if(empty($this->type))
			return false;
		$class = hikamarket::get('class.'.$this->type);
		if( $class === null )
			return false;
		$status = $class->saveForm();
		if($status) {
			$app->enqueueMessage(JText::_('HIKAM_SUCC_SAVED'), 'message');
			hikaInput::get()->set('cid', $status);
			hikaInput::get()->set('fail', null);
		} else {
			$app->enqueueMessage(JText::_('ERROR_SAVING'), 'error');
			if(!empty($class->errors)) {
				foreach($class->errors as $err) {
					$app->enqueueMessage($err, 'error');
				}
			}
		}
		return $status;
	}

	protected function adminRemove() {
		JSession::checkToken() || die('Invalid Token');
		$cids = hikaInput::get()->get('cid', array(), 'array');
		$class = hikamarket::get('class.'.$this->type);
		$num = $class->delete($cids);
		if($num) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS', count($cids)), 'message');
		}
		return $this->listing();
	}

	public function publish() {
		$cid = hikaInput::get()->post->get('cid', array(), 'array');
		hikamarket::toInteger($cid);
		return $this->toggle($cid,1);
	}

	public function unpublish() {
		$cid = hikaInput::get()->post->get('cid', array(), 'array');
		hikamarket::toInteger($cid);
		return $this->toggle($cid,0);
	}

	public function display($tpl = null, $params = null) {
		if(HIKASHOP_J30) {
			$document = JFactory::getDocument();
			$view = $this->getView('', $document->getType(), '', array('base_path' => $this->basePath));
			if($view->getLayout() == 'default' && hikaInput::get()->getString('layout', '') != '')
				$view->setLayout(hikaInput::get()->getString('layout'));
		}

		$shopConfig = hikamarket::config(false);
		$menu_style = $shopConfig->get('menu_style', 'title_bottom');
		if(HIKASHOP_J30) $menu_style = 'content_top';
		if($menu_style == 'content_top') {
			if(hikamarket::isAdmin() && hikaInput::get()->getString('tmpl') !== 'component') {
				echo hikamarket::getMenu();
			}
		}
		return parent::display($tpl, $params);
	}

	private function toggle($cid, $publish) {
		if(empty($cid)) {
			throw new RuntimeException('No items selected', 500);
			return;
		}

		if(isset($this->dispatch) && in_array($this->dispatch, 'toggle') ) {
			$app = JFactory::getApplication();
			JPluginHelper::importPlugin('hikamarket');
			$unset = array();
			$objs = array();
			foreach($cid as $k => $id){
				$element = new stdClass();
				$name = reset($this->toggle);
				$element->$name = $id;
				$publish_name = key($this->toggle);
				$element->$publish_name = (int)$publish;
				$do = true;
				$app->triggerEvent('onBefore'.ucfirst($this->type).'Update', array(&$element, &$do));
				if(!$do) {
					$unset[] = $k;
				} else {
					$objs[$k] = &$element;
				}
			}
			if(!empty($unset)) {
				foreach($unset as $u) {
					unset($cid[$u]);
				}
			}
		}

		$cids = implode(',', $cid);
		$db = JFactory::getDBO();
		$query = 'UPDATE '.hikamarket::table($this->type).' SET '.key($this->toggle).' = '.(int)$publish.' WHERE '.reset($this->toggle).' IN ( '.$cids.' )';
		$db->setQuery($query);
		if(!$db->execute()) {
			throw new RuntimeException($db->getErrorMsg(), 500);
			return;
		} elseif(isset($this->dispatch) && in_array($this->dispatch, 'toggle')) {
			if(!empty($objs)) {
				foreach($objs as $element) {
					$app->triggerEvent('onAfter'.ucfirst($this->type).'Update', array(&$element));
				}
			}
		}
		$task = $this->publish_return_view;
		return $this->$task();
	}

	public function getModel($name = '', $prefix = '', $config = array(),$do = false) {
		if($do) return parent::getModel($name, $prefix , $config);
		return false;
	}

	public function execute($task) {
		if(HIKASHOP_J30) {
			if(empty($task))
				$task = @$this->taskMap['__default'];
			if(!empty($task) && !$this->authorize($task)) {
				throw new JAccessExceptionNotallowed(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
			}
		}
		return parent::execute($task);
	}

	public function authorize($task) {
		if($this->isIn($task, array('modify','delete'))) {
			if(JSession::checkToken('request')) {
				return true;
			}
			return false;
		}
		return $this->isIn($task);
	}

	public function authorise($task) {
		return $this->authorize($task);
	}

	public function isIn($task, $lists = array('*')) {
		if(!is_array($lists)) {
			$lists = array($lists);
		}
		$task = strtolower($task);
		foreach($lists as $list) {
			if($list == '*') {
				foreach($this->rights as $rights) {
					if(!empty($rights) && in_array($task, $rights)) {
						return true;
					}
				}
			} elseif(!empty($this->rights[$list]) && in_array($task, $this->rights[$list])) {
				return true;
			}
		}
		return false;
	}

	protected function saveorder() {
		if(empty($this->ordering)) {
			return $this->listing();
		}

		if(!empty($this->ordering['table']) && !empty($this->ordering['pkey']) && (empty($this->ordering['groupMap']) || isset($this->ordering['groupVal'])) && !empty($this->ordering['orderingMap'])) {
			$orderClass = hikamarket::get('shop.helper.order');
			$orderClass->pkey = $this->ordering['pkey'];
			$orderClass->table = $this->ordering['table'];
			$orderClass->groupMap = $this->ordering['groupMap'];
			$orderClass->groupVal = $this->ordering['groupVal'];
			$orderClass->orderingMap = $this->ordering['orderingMap'];
			if(!empty($this->ordering['main_pkey'])) {
				$orderClass->main_pkey = $this->ordering['main_pkey'];
			}
			$orderClass->save(false);
		}
		return $this->listing();
	}

	public function getUploadSetting($upload_key, $caller = '') {
		return false;
	}

	public function manageUpload($upload_key, &$ret, $uploadConfig, $caller = '') { }
}

class hikamarketClass extends JObject {
	protected $db;
	protected $tables = array();
	protected $pkeys = array();
	protected $namekeys = array();
	protected $toggle = array();
	protected $deleteToggle = array();

	public function  __construct($config = array()){
		$this->db = JFactory::getDBO();
		return parent::__construct($config);
	}

	public function get($element, $default = null) {
		if(empty($element))
			return null;
		$pkey = end($this->pkeys);
		$namekey = end($this->namekeys);
		$table = $this->getTable(); // hikamarket::table(end($this->tables));
		if(!is_numeric($element) && !empty($namekey)) {
			$pkey = $namekey;
		}
		$query = 'SELECT * FROM '.$table.' WHERE '.$pkey.' = '.$this->db->Quote($element);
		$this->db->setQuery($query, 0, 1);
		$ret = $this->db->loadObject();
		return $ret;
	}

	public function getRaw($element, $default = null) {
		static $multiTranslation = null;
		if(empty($element))
			return null;
		$pkey = end($this->pkeys);
		$namekey = end($this->namekeys);
		$table = $this->getTable(); // hikamarket::table(end($this->tables));
		if(!is_numeric($element) && !empty($namekey)) {
			$pkey = $namekey;
		}
		if($multiTranslation === null) {
			$translationHelper = hikamarket::get('shop.helper.translation');
			$multiTranslation = $translationHelper->isMulti(true);
		}
		$query = 'SELECT * FROM '.$table.' WHERE '.$pkey.' = '.$this->db->Quote($element);
		$this->db->setQuery($query, 0, 1);
		if($multiTranslation) {
			if(!hikamarket::isAdmin() && class_exists('JFalangDatabase')) {
				$ret = $this->db->loadObject('stdClass', false);
			} elseif(!hikamarket::isAdmin() && (class_exists('JFDatabase') || class_exists('JDatabaseMySQLx'))) {
				$ret = $this->db->loadObject(false);
			} else {
				$ret = $this->db->loadObject();
			}
		} else {
			$ret = $this->db->loadObject();
		}
		return $ret;
	}

	public function save(&$element) {
		if(empty($this->tables))
			return false;
		$pkey = end($this->pkeys);
		if(empty($pkey)) {
			$pkey = end($this->namekeys);
		} elseif(empty($element->$pkey)) {
			$t = end($this->namekeys);
			if(!empty($t)) {
				if(!empty($element->$t)) {
					$pkey = $t;
				} else {
					$element->$t = $this->getNamekey($element);
					if($element->$t === false)
						return false;
				}
			}
		}
		$table = $this->getTable(); // hikamarket::table(end($this->tables));
		$obj =& $element;

		if(empty($element->$pkey)) {
			$this->db->setQuery($this->getInsert($table, $obj));
			$status = $this->db->execute();
		} else {
			if( count( (array)$element ) > 1 ) {
				$status = $this->db->updateObject($table, $obj, $pkey);
			} else {
				$status = true;
			}
		}
		if($status)
			return empty($element->$pkey) ? $this->db->insertid() : $element->$pkey;
		return false;
	}

	private function getInsert($table, &$obj, $keyName = null) {
		$sql = 'INSERT IGNORE INTO '.$this->db->quoteName($table).' ( %s ) VALUES ( %s ) ';
		$fields = array();
		$values = array();
		foreach (get_object_vars($obj) as $k => $v) {
			if(is_array($v) || is_object($v) || $v === null || $k[0] == '_' ) {
				continue;
			}
			$fields[] = $this->db->quoteName($k);
			$values[] = $this->db->Quote($v);
		}
		return sprintf($sql, implode(',', $fields), implode(',', $values));
	}

	public function delete(&$elements) {
		if(empty($this->tables))
			return false;
		if(empty($elements))
			return false;
		if(!is_array($elements))
			$elements = array($elements);

		$isNumeric = is_numeric(reset($elements));
		foreach($elements as $key => $val) {
			$elements[$key] = $this->db->Quote($val);
		}

		$columns = $isNumeric ? $this->pkeys : $this->namekeys;

		if(empty($columns) || empty($elements))
			return false;

		$otherElements = array();
		$otherColumn = '';
		foreach($columns as $i => $column) {
			if(empty($column)) {
				$query = 'SELECT '.($isNumeric?end($this->pkeys):end($this->namekeys)).' FROM '.hikamarket::table(end($this->tables)).' WHERE '.($isNumeric?end($this->pkeys):end($this->namekeys)).' IN ( '.implode(',',$elements).');';
				$this->db->setQuery($query);
				$otherElements = $this->db->loadColumn();

				foreach($otherElements as $key => $val) {
					$otherElements[$key] = $this->db->Quote($val);
				}
				break;
			}
		}

		$tables = array();
		if(is_array($this->tables)) {
			foreach($this->tables as $i => $oneTable) {
				$tables[$i] = hikamarket::table($oneTable);
			}
		} else {
			$tables[0] = hikamarket::table($this->tables);
		}

		$result = true;
		foreach($tables as $i => $oneTable) {
			$column = $columns[$i];
			if(empty($column)) {
				$whereIn = ' WHERE '.($isNumeric?$this->namekeys[$i]:$this->pkeys[$i]).' IN ('.implode(',',$otherElements).')';
			} else {
				$whereIn = ' WHERE '.$column.' IN ('.implode(',',$elements).')';
			}
			$query = 'DELETE FROM '.$oneTable.$whereIn;
			$this->db->setQuery($query);
			$result = $this->db->execute() && $result;
		}
		return $result;
	}

	public function toggleId($task, $value = null) {
		if(!empty($this->toggle[$task]))
			return $this->toggle[$task];
		return false;
	}

	public function toggle($task, $key, $value = null) {
		return null;
	}

	public function toggleDelete($value1 = '', $value2 = '') {
		if(!empty($this->deleteToggle))
			return $this->deleteToggle;
		return false;
	}

	public function getTable() {
		if(!empty($this->tables))
			return hikamarket::table(end($this->tables));
		return false;
	}
}

class hikamarketView extends hikashopBridgeView {
	protected $triggerView = false;
	protected $allowInlineJavascript = false;
	public $displayView = true;
	public $toolbar = array();

	public function display($tpl = null) {
		$lang = JFactory::getLanguage();
		if($lang->isRTL()) $this->direction = 'rtl';

		if($this->triggerView) {
			$app = JFactory::getApplication();
			JPluginHelper::importPlugin('hikamarket');
			$view =& $this;
			$app->triggerEvent('onHikamarketBeforeDisplayView', array(&$view));
		}

		if(!empty($this->toolbar)) {
			$toolbarHelper = hikamarket::get('helper.toolbar');
			$toolbarHelper->process($this->toolbar);
		}

		if($this->displayView) {
			if(HIKASHOP_J40 && !$this->allowInlineJavascript) {
				ob_start();
				parent::display($tpl);
				$html = ob_get_clean();

				$domd = new DOMDocument();
				libxml_use_internal_errors(true);
				$domd->loadHTML('<?xml encoding="UTF-8">'.$html);
				libxml_use_internal_errors(false);
				$doc = JFactory::getDocument();
				foreach(iterator_to_array($domd->getElementsByTagName('script')) as $node) {
					$doc->addScriptDeclaration($domd->saveHTML($node->firstChild));
					$node->parentNode->removeChild($node);
				};
				echo $domd->saveHTML();
			} else {
				parent::display($tpl);
			}
		}

		if($this->triggerView)
			$app->triggerEvent('onHikamarketAfterDisplayView', array(&$view));
	}

	protected function &getPageInfo($default = '', $dir = 'asc', $filters = array()) {
		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest($this->paramBase.'.search', 'search', '', 'string');

		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$filter_fullorder = hikaInput::get()->getString('filter_fullorder', null);
		if($filter_fullorder != null && strpos($filter_fullorder, ' ') !== false) {
			$filter_fullorder = explode(' ', $filter_fullorder, 2);
			$filter_fullorder[1] = strtolower($filter_fullorder[1]);
			if($filter_fullorder[1] == 'asc' || $filter_fullorder[1] == 'desc') {
				$app->setUserState($this->paramBase.'.filter_order', $filter_fullorder[0]);
				$app->setUserState($this->paramBase.'.filter_order_Dir', $filter_fullorder[1]);
				if(hikaInput::get()->getString('filter_order', null) == null) {
					hikaInput::get()->set('filter_order',  $filter_fullorder[0]);
					hikaInput::get()->set('filter_order_Dir',  $filter_fullorder[1]);
				}
			}
		}
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($this->paramBase.'.filter_order', 'filter_order', $default, 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($this->paramBase.'.filter_order_Dir', 'filter_order_Dir',	$dir, 'word');

		$pageInfo->limit = new stdClass();
		$pageInfo->limit->value = $app->getUserStateFromRequest($this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		if(empty($pageInfo->limit->value))
			$pageInfo->limit->value = 500;
		if((hikaInput::get()->getVar('search', null) != null && hikaInput::get()->getVar('search', null) != $app->getUserState($this->paramBase.'.search')) || hikaInput::get()->getVar('limit', $pageInfo->limit->value) != $app->getUserState($this->paramBase.'.list_limit')) {
			$app->setUserState($this->paramBase.'.limitstart', 0);
			$pageInfo->limit->start = 0;
		} else {
			$pageInfo->limit->start = $app->getUserStateFromRequest($this->paramBase.'.limitstart', 'limitstart', 0, 'int');
		}

		if(!empty($filters)) {
			$reset = false;
			foreach($filters as $k => $v) {
				$type = 'string';
				if(is_int($v)) $type = 'int';

				if(!$reset) $oldValue = $app->getUserState($this->paramBase.'.filter_'.$k, $v);
				$newValue = $app->getUserStateFromRequest($this->paramBase.'.filter_'.$k, 'filter_'.$k, $v, $type);
				$reset = $reset || ($oldValue != $newValue);
				$pageInfo->filter->$k = $newValue;
			}
			if($reset) {
				$app->setUserState($this->paramBase.'.limitstart',0);
				$pageInfo->limit->start = 0;
			}
		}

		$pageInfo->search = HikaStringHelper::strtolower($app->getUserStateFromRequest($this->paramBase.'.search', 'search', '', 'string'));
		$pageInfo->search = trim($pageInfo->search);

		$this->assignRef('pageInfo', $pageInfo);
		return $pageInfo;
	}

	protected function getPageInfoTotal($query, $countValue = '*') {
		if(empty($this->pageInfo))
			return false;

		$db = JFactory::getDBO();
		$app = JFactory::getApplication();

		$db->setQuery('SELECT COUNT('.$countValue.') '.$query);
		if(empty($this->pageInfo->elements))
			$this->pageInfo->elements = new stdClass();
		$this->pageInfo->elements->total = (int)$db->loadResult();
		if((int)$this->pageInfo->limit->start >= $this->pageInfo->elements->total) {
			$this->pageInfo->limit->start = 0;
			$app->setUserState($this->paramBase.'.limitstart', 0);
		}
	}

	protected function processFilters(&$filters, &$order, $searchMap = array(), $orderingAccept = array()) {
		if(!empty($this->pageInfo->search)) {
			$db = JFactory::getDBO();
			$searchVal = '\'%' . $db->escape(HikaStringHelper::strtolower($this->pageInfo->search), true) . '%\'';
			if(is_array($filters))
				$filters[] = '('.implode(' LIKE '.$searchVal.' OR ',$searchMap).' LIKE '.$searchVal.')';
		}
		if(!empty($filters)) {
			if(is_array($filters))
				$filters = ' WHERE ('. implode(') AND (', $filters) . ')';
		} else {
			$filters = '';
		}

		if(!empty($this->pageInfo->filter->order->value)) {
			$t = '';
			if(strpos($this->pageInfo->filter->order->value, '.') !== false)
				list($t,$v) = explode('.', $this->pageInfo->filter->order->value, 2);

			$dir = strtolower($this->pageInfo->filter->order->dir);
			if(!in_array($dir, array('asc', 'desc')))
				$this->pageInfo->filter->order->dir = 'ASC';

			if(empty($orderingAccept) || in_array($t.'.', $orderingAccept) || in_array($this->pageInfo->filter->order->value, $orderingAccept))
				$order = ' ORDER BY '.$this->pageInfo->filter->order->value.' '.strtoupper($this->pageInfo->filter->order->dir);
		}
	}

	protected function getPagination($max = 500, $limit = 100) {
		if(empty($this->pageInfo))
			return false;

		if($this->pageInfo->limit->value == $max)
			$this->pageInfo->limit->value = $limit;

			$pagination = hikashop_get('helper.pagination', $this->pageInfo->elements->total, $this->pageInfo->limit->start, $this->pageInfo->limit->value);
		$this->assignRef('pagination', $pagination);
		return $pagination;
	}

	protected function getOrdering($value = '', $doOrdering = true) {
		$this->assignRef('doOrdering', $doOrdering);

		$ordering = new stdClass();
		$ordering->ordering = false;

		if($doOrdering) {
			$ordering->ordering = false;
			$ordering->orderUp = 'orderup';
			$ordering->orderDown = 'orderdown';
			$ordering->reverse = false;
			if(!empty($this->pageInfo) && $this->pageInfo->filter->order->value == $value) {
				$ordering->ordering = true;
				if($this->pageInfo->filter->order->dir == 'desc') {
					$ordering->orderUp = 'orderdown';
					$ordering->orderDown = 'orderup';
					$ordering->reverse = true;
				}
			}
		}
		$this->assignRef('ordering', $ordering);
		return $ordering;
	}

	protected function loadRef($refs) {
		foreach($refs as $key => $name) {
			$obj = hikamarket::get($name);
			if(!empty($obj))
				$this->assignRef($key, $obj);
			unset($obj);
		}
	}

	protected function loadHkLayout($layout, $params = array()) {
		$backup_paths = $this->_path['template'];

		if(hikamarket::isAdmin()) {
			$layout_path = rtrim(JPath::clean(HIKAMARKET_BACK), '\/') . '/views/layouts/tmpl';
		} else {
			$layout_path = rtrim(JPath::clean(HIKAMARKET_FRONT), '\/') . '/views/layouts/tmpl';
		}

		$app = JFactory::getApplication();
		$component = JApplicationHelper::getComponentName();
		$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $component);
		$fallback = JPATH_THEMES . '/' . $app->getTemplate() . '/html/' . $component . '/layouts';

		$this->_path['template'] = array();
		$this->_addPath('template', array($layout_path, $fallback));

		if(!isset($this->params) || (empty($this->params) && is_array($this->params)))
			$this->params = new JRegistry();

		$backup_params = array();
		foreach($params as $k => $v) {
			$backup_params[$k] = $this->params->get($k, null);
			$this->params->set($k, $v);
		}

		$current_name = $this->getName();
		$this->_name = 'layouts';

		$current_layout = $this->getLayout();
		$this->setLayout($layout);

		$ret = $this->loadTemplate();

		$this->setLayout($current_layout);

		$this->_name = $current_name;

		$this->_path['template'] = $backup_paths;

		foreach($backup_params as $k => $v) {
			$this->params->set($k, $v);
		}

		return $ret;
	}

	public function escape($var, $search = false) {
		if($search === false || empty($this->pageInfo) || empty($this->pageInfo->search))
			return parent::escape($var);
		return hikamarket::search($this->pageInfo->search, parent::escape($var));
	}

	public function search($var, $escape = false) {
		if(empty($this->pageInfo) || empty($this->pageInfo->search))
			return $escape ? parent::escape($var) : $var;
		return hikamarket::search($this->pageInfo->search, $escape ? parent::escape($var) : $var);
	}

	public function loadTemplate($tpl = null){
		$config = hikashop_config();
		$active = $config->get('display_view_files', 0);
		if(!$active)
			return parent::loadTemplate($tpl);

		if(hikamarket::isAdmin() && $active != 2)
			return parent::loadTemplate($tpl);

		return '<div class="hikashop_view_files_border"><div class="hikashop_view_files_title"><span>'.
			$this->getName().' / '.$this->getLayout().(!empty($tpl)?'_':'').$tpl.'.php</span></div><div class="hikashop_view_files_wrapper">'.
			parent::loadTemplate($tpl).
			'</div></div>';
	}

	public function assignRef($name, &$ref) {
		$this->$name =& $ref;
	}
}

spl_autoload_register(function($classname) {
	if($classname == 'hikamarketPlugin') {
		include_once __DIR__ . '/plugin.php';
		return;
	}
	if(substr($classname, 0, 10) != 'hikamarket')
		return;
});

$configMarket = hikamarket::config();
define('HIKAMARKET_RESSOURCE_VERSION', str_replace('.', '', $configMarket->get('version')));
$doc = JFactory::getDocument();
$doc->addScript(HIKAMARKET_JS.'hikamarket.js?v='.HIKAMARKET_RESSOURCE_VERSION);
$cssMarket = $configMarket->get('css_'.$css_type, 'default');
if(!empty($cssMarket))
	$doc->addStyleSheet(HIKAMARKET_CSS.$css_type.'_'.$cssMarket.'.css?v='.HIKAMARKET_RESSOURCE_VERSION);
if(!hikamarket::isAdmin()) {
	$styleCssMarket = $configMarket->get('css_style','');
	if(!empty($styleCssMarket))
		$doc->addStyleSheet(HIKAMARKET_CSS.'style_'.$styleCssMarket.'.css?v='.HIKAMARKET_RESSOURCE_VERSION);
}
if(!empty($cssMarket) || !empty($styleCssMarket)) {
	$lang = JFactory::getLanguage();
	if($lang->isRTL())
		$doc->addStyleSheet(HIKAMARKET_CSS.'rtl.css?v='.HIKAMARKET_RESSOURCE_VERSION);
}
if(defined('HIKASHOP_COMPONENT') && defined('HIKASHOP_BACK_RESPONSIVE') && defined('HIKASHOP_RESPONSIVE')) {
	if((hikamarket::isAdmin() && HIKASHOP_BACK_RESPONSIVE) || (!hikamarket::isAdmin() && HIKASHOP_RESPONSIVE))
		$doc->addScriptDeclaration("\r\n".'window.Oby.ready(function(){setTimeout(function(){window.hikamarket.noChzn();},100);});');
}
