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
class configmarketViewconfigmarket extends hikamarketView {

	const ctrl = 'config';
	const name = 'HIKA_CONFIGURATION';
	const icon = 'wrench';

	public function display($tpl = null, $params = null) {
		$this->params =& $params;
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName();
		$fct = $this->getLayout();
		if(method_exists($this, $fct))
			$this->$fct($params);
		parent::display($tpl);
	}

	public function config($tpl = null) {
		hikamarket::setTitle(JText::_(self::name), self::icon, self::ctrl);

		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$config = hikamarket::config();
		$this->assignRef('config',$config);
		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig',$shopConfig);

		$manage = hikamarket::isAllowed($config->get('acl_config_manage', 'all'));
		$this->assignRef('manage', $manage);

		$defaultPanel = $app->getUserStateFromRequest($this->paramBase.'.default_panel', 'default_panel', 0, 'int');
		$this->assignRef('default_tab', $defaultPanel);

		hikashop_loadJsLib('tooltip');
		$jlanguage = JFactory::getLanguage();
		$jlanguage->load('com_hikamarket_config', JPATH_SITE);

		$this->loadRef(array(
			'popup' => 'shop.helper.popup',
			'tabs' => 'shop.helper.tabs',
			'marketaclType' => 'type.market_acl',
			'categoryType' => 'type.shop_category',
			'editorType' => 'shop.type.editor',
			'uploaderType' => 'type.uploader',
			'menusType' => 'type.menus',
			'radioType' => 'shop.type.radio',
			'cssType' => 'type.css',
			'toggleClass' => 'helper.toggle',
			'joomlaaclType' => 'type.joomla_acl',
			'nameboxType' => 'type.namebox',
			'vendorClass' => 'class.vendor'
		));

		$vendor = $this->vendorClass->get(1);
		$this->assignRef('vendor', $vendor);

		$vendorselect_tables = array('order', 'item', 'user');
		$query = 'SELECT field.field_namekey, field.field_realname, field.field_table '.
			' FROM ' . hikamarket::table('shop.field') . ' AS field '.
			' WHERE field.field_table IN (\''.implode('\',\'', $vendorselect_tables).'\') AND field.field_type = \'plg.market_vendorselectfield\' AND field_published = 1 AND field_frontcomp = 1'.
			' ORDER BY field.field_table, field.field_realname';
		$db->setQuery($query);
		$vendorselect_customfields = $db->loadObjectList();
		$this->assignRef('vendorselect_customfields', $vendorselect_customfields);

		$currencyClass = hikamarket::get('shop.class.currency');
		$curr = '';
		$main_currency_id = $shopConfig->get('main_currency', 1);
		$mainCurr = $currencyClass->getCurrencies($main_currency_id, $curr);
		$this->main_currency = $mainCurr[$main_currency_id];

		$languages = array();
		$lg = JFactory::getLanguage();
		$language = $lg->getTag();
		$styleRemind = 'float:right;margin-right:30px;position:relative;';
		$loadLink = $this->popup->display(
			JText::_('LOAD_LATEST_LANGUAGE'),
			'EDIT_LANGUAGE_FILE',
			hikamarket::completeLink('config&task=latest&code=' . $language, true),
			'loadlatest_language_'.$language,
			800, 500, 'onclick="window.document.getElementById(\'hikashop_messages_warning\').style.display = \'none\';"', '', 'link'
		);
		if(!file_exists(HIKASHOP_ROOT . 'language' . DS . $language . DS . $language . '.' . HIKAMARKET_COMPONENT . '.ini')) {
			if($config->get('errorlanguagemissing', 1)) {
				$noteremind = '<small style="' . $styleRemind . '">' . $this->toggleClass->delete('hikashop_messages_warning', 'errorlanguagemissing-0', 'config', false, JText::_('DONT_REMIND')) . '</small>';
				hikamarket::display(JText::_('MISSING_LANGUAGE') . ' ' . $loadLink . ' ' . $noteremind, 'warning');
			}
		}

		jimport('joomla.filesystem.folder');
		$path = hikashop_getLanguagePath(JPATH_ROOT);
		$dirs = JFolder::folders($path);
		foreach($dirs as $dir) {
			$xmlFiles = JFolder::files($path . DS . $dir, '^([-_A-Za-z]*)\.xml$');
			$xmlFile = array_pop($xmlFiles);
			if($xmlFile == 'install.xml')
				$xmlFile = array_pop($xmlFiles);
			if(empty($xmlFile))
				continue;
			$data = JInstaller::parseXMLInstallFile($path . DS . $dir . DS . $xmlFile);
			$oneLanguage = new stdClass();
			$oneLanguage->language 	= $dir;
			$oneLanguage->name = $data['name'];
			$languageFiles = JFolder::files($path . DS . $dir, '^(.*)\.' . HIKAMARKET_COMPONENT . '\.ini$' );
			$languageFile = reset($languageFiles);

			$linkEdit = hikamarket::completeLink('config&task=language&code='.$oneLanguage->language, true, false, false);
			if(!empty($languageFile)){
				$oneLanguage->edit = $this->popup->display(
					'<span id="image'.$oneLanguage->language.'" alt="'.JText::_('EDIT_LANGUAGE_FILE', true).'" style="font-size:1.2em;"><i class="fas fa-pencil-alt"></i></span>',
					'EDIT_LANGUAGE_FILE',
					$linkEdit,
					'edit_language_'.$oneLanguage->language,
					800, 500, '', '', 'link'
				);
			} else {
				$oneLanguage->edit = $this->popup->display(
					'<span id="image'.$oneLanguage->language.'"alt="'.JText::_('ADD_LANGUAGE_FILE', true).'" style="font-size:1.2em;"><i class="fas fa-plus"></i></span>',
					'ADD_LANGUAGE_FILE',
					$linkEdit,
					'edit_language_'.$oneLanguage->language,
					800, 500, '', '', 'link'
				);
			}
			$languages[] = $oneLanguage;
		}
		$this->assignRef('languages', $languages);

		$emails = array();
		$emailFiles = JFolder::files(HIKAMARKET_MEDIA.'mail'.DS, '^([-_A-Za-z]*)(\.html)?\.php$');
		if(!empty($emailFiles)) {
			foreach($emailFiles as $emailFile) {
				$file = str_replace(array('.html.php', '.php'), '', $emailFile);
				if(substr($file, -9) == '.modified')
					continue;
				$emails[] = array(
					'name' => $file,
					'file' => 'market.'.$file,
					'published' => $shopConfig->get('market.'.$file.'.published')
				);
			}
		}
		$this->assignRef('emails', $emails);
		$emailManage = hikashop_level(2) && hikashop_isAllowed($shopConfig->get('acl_email_manage','all'));
		$this->assignRef('emailManage', $emailManage);

		$statistics = array();
		$statisticsClass = hikamarket::get('class.statistics');
		$statistics = $statisticsClass->getVendor(0);

		JPluginHelper::importPlugin('hikamarket');
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashoppayment');
		$app->triggerEvent('onVendorPanelDisplay', array(&$buttons, &$statistics));

		$vendor_statistics = $config->get('vendor_statistics', null);
		if(!empty($vendor_statistics)) {
			foreach($statistics as $key => &$stat) {
				$stat['published'] = false;
			}
			unset($stat);

			$vendor_statistics = hikamarket::unserialize(base64_decode($vendor_statistics));
			foreach($vendor_statistics as $key => $stat_conf) {
				if(!isset($statistics[$key]))
					continue;

				if(isset($stat_conf['container']))
					$statistics[$key]['container'] = (int)$stat_conf['container'];
				if(isset($stat_conf['slot']))
					$statistics[$key]['slot'] = (int)$stat_conf['slot'];
				if(isset($stat_conf['order']))
					$statistics[$key]['order'] = (int)$stat_conf['order'];
				if(isset($stat_conf['published']))
					$statistics[$key]['published'] = $stat_conf['published'];
				if(!empty($stat_conf['vars'])) {
					if(!isset($statistics[$key]['vars']))
						$statistics[$key]['vars'] = array();

					foreach($stat_conf['vars'] as $k => $v) {
						if(isset($statistics[$key]['vars'][$k]))
							$statistics[$key]['vars'][$k] = $v;
					}
				}
			}

			uasort($statistics, array($this, 'sortStats'));
		}
		$this->assignRef('statistics', $statistics);
		$this->assignRef('statisticsClass', $statisticsClass);

		$query = 'SELECT product.product_id, product.product_name, product.product_code, count(vendor.vendor_id) as `vendor_count`'.
			' FROM '.hikamarket::table('shop.product').' as product '.
			' LEFT JOIN '.hikamarket::table('vendor').' as vendor ON vendor.vendor_template_id = product.product_id '.
			' WHERE product_type = \'template\' '.
			' GROUP BY product.product_id, product.product_name, product.product_code';
		$db->setQuery($query);
		$product_templates = $db->loadObjectList();
		$this->assignRef('product_templates', $product_templates);

		$product_template = null;
		if((int)$config->get('default_template_id', 0) > 0) {
			$query = 'SELECT * FROM '.hikamarket::table('shop.product').' AS a WHERE a.product_type = \'template\' AND a.product_id = ' . (int)$config->get('default_template_id', 0);
			$db->setQuery($query);
			$product_template = $db->loadObject();
		}
		$this->assignRef('product_template', $product_template);

		$manager = true;
		$this->toolbar = array(
			'|',
			array('name' => 'save', 'display' => $manager),
			array('name' => 'apply', 'display' => $manager),
			'hikacancel',
			'|',
			array('name' => 'pophelp', 'target' => 'config'),
			'dashboard'
		);
	}

	protected function sortStats($a, $b) {
		if($a['order'] == $b['order'])
			return 0;
		return ($a['order'] < $b['order']) ? -1 : 1;
	}

	public function acl($tpl = null) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);

		hikamarket::loadJslib('otree');

		$market_acl = hikamarket::get('type.market_acl');
		$this->assignRef('marketaclType', $market_acl);
		$acls = $market_acl->getList();
		$this->assignRef('acls', $acls);

		$joomla_acl = hikamarket::get('type.joomla_acl');
		$this->assignRef('joomlaAclType', $joomla_acl);
		$groups = $joomla_acl->getList();
		$this->assignRef('groups', $groups);

		$aclClass = hikamarket::get('class.acl');

		$title_parameters = '';
		$acl_type = 'user';
		$acl_type = hikaInput::get()->getCmd('acl_type', '');

		if(!empty($acl_type)) {
			$aclConfig = array();
			foreach($groups as $group) {
				$localAclData = $config->get($acl_type . '_acl_' . $group->id, '');
				if($acl_type == 'vendor_options')
					$aclConfig[$group->id] = hikamarket::unserialize($localAclData);
				else
					$aclConfig[$group->id] = explode(',', $localAclData);
			}
			$this->assignRef('aclConfig', $aclConfig);

			$title_parameters = '&acl_type='.$acl_type;

			if($acl_type == 'vendor_options') {
				$currencyClass = hikamarket::get('shop.class.currency');
				$mainCurrency = $shopConfig->get('main_currency', 1);
				$currencyIds = $currencyClass->publishedCurrencies();
				if(!in_array($mainCurrency, $currencyIds))
					$currencyIds = array_merge(array($mainCurrency), $currencyIds);
				$null = null;
				$currencies = $currencyClass->getCurrencies($currencyIds, $null);
				$this->assignRef('currencies', $currencies);
			}

		} else {
			$buttons = array(
				array(
					'name' => JText::_('HIKAM_VENDOR_ACL'),
					'url' => hikamarket::completeLink('config&task=acl&acl_type=vendor'),
					'icon' => 'icon-48-acl'
				),
				array(
					'name' => JText::_('HIKAM_VENDOR_OPTIONS'),
					'url' => hikamarket::completeLink('config&task=acl&acl_type=vendor_options'),
					'icon' => 'icon-48-acl'
				),
				array(
					'name' => JText::_('HIKAM_USER_ACL'),
					'url' => hikamarket::completeLink('config&task=acl&acl_type=user'),
					'icon' => 'icon-48-acl'
				),
			);
			$this->assignRef('buttons', $buttons);
		}
		$this->assignRef('acl_type', $acl_type);

		hikamarket::setTitle(JText::_('HIKAM_ACL'), 'unlock-alt', self::ctrl.'&task=acl'.$title_parameters);

		$manager = true;
		if(!empty($acl_type)) {
			$this->toolbar = array(
				'|',
				array('name' => 'custom', 'icon' => 'save', 'alt' => JText::_('JTOOLBAR_SAVE'), 'task' => 'saveacl', 'check' => false, 'display' => $manager),
				array('name' => 'custom', 'icon' => 'apply', 'alt' => JText::_('JTOOLBAR_APPLY'), 'task' => 'applyacl', 'check' => false, 'display' => $manager),
				array('name' => 'hikacancel', 'url' => hikamarket::completeLink('config&task=acl')),
				'|',
				array('name' => 'pophelp', 'target' => 'config'),
				'dashboard'
			);
		} else {
			$this->toolbar = array(
				'|',
				array('name' => 'pophelp', 'target' => 'config'),
				'dashboard'
			);
		}
	}

	public function sql($tpl = null) {
		hikamarket::setTitle(JText::_('HIKA_CONFIGURATION_SQL'), self::icon, self::ctrl);

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$toolbar = JToolBar::getInstance('toolbar');

		$sql_data = hikaInput::get()->getRaw('sql_data', '');
		$this->assignRef('sql_data', $sql_data);

		$user = JFactory::getUser();
		$iAmSuperAdmin = $user->authorise('core.admin');

		$query_result = '';
		if(!empty($sql_data) && $iAmSuperAdmin) {
			$p = strpos(trim($sql_data), ' ');
			if($p !== false) {
				$db = JFactory::getDBO();
				$sql_data = str_replace('table::', '#__', trim($sql_data));
				$word = strtolower(substr($sql_data, 0, $p));

				if(in_array($word, array('insert', 'update', 'delete'))) {
					$db->setQuery($sql_data);
					try {
						$db->execute();
						$query_result = JText::_('HIKA_X_ROWS_AFFECTED', $db->getAffectedRows());
					} catch(Exception $e) {
						$query_result = JText::_('HIKA_QUERY_FAILURE');
					}
				} else if($word == 'select') {
					$db->setQuery($sql_data);
					try {
						$query_result = $db->loadObjectList();
					} catch(Exception $e) {
						$query_result = JText::_('HIKA_QUERY_FAILURE');
					}
				} else if(in_array($word, array('create', 'drop', 'alter'))) {
					$db->setQuery($sql_data);
					try {
						if( $db->execute() ) {
							$query_result = JText::_('HIKA_QUERY_SUCCESS');
						} else {
							$query_result = JText::_('HIKA_QUERY_FAILURE');
						}
					} catch(Exception $e) {
						$query_result = JText::_('HIKA_QUERY_FAILURE');
					}
				}
			}
		}
		$this->assignRef('query_result', $query_result);

		$this->toolbar = array(
			'|',
			array('name' => 'custom', 'icon' => 'apply', 'alt' => JText::_('HIKAM_APPLY'), 'task' => 'sql', 'check' => false),
			'hikacancel',
			'|',
			array('name' => 'pophelp', 'target' => 'config'),
			'dashboard'
		);
	}

	public function language() {
		$code = hikaInput::get()->getString('code');
		if(empty($code)) {
			hikamarket::display('Code not specified','error');
			return;
		}

		jimport('joomla.filesystem.file');
		$path = hikashop_getLanguagePath(JPATH_ROOT) . DS . $code . DS . $code . '.' . HIKAMARKET_COMPONENT . '.ini';
		$file = new stdClass();
		$file->name = $code;
		$file->path = $path;
		if(JFile::exists($path)) {
			$file->content = file_get_contents($path);
			if(empty($file->content)) {
				hikamarket::display('File not found : '.$path,'error');
			}
		} else {
			hikamarket::display(JText::_('LOAD_ENGLISH_1') . '<br/>' . JText::_('LOAD_ENGLISH_2') . '<br/>' . JText::_('LOAD_ENGLISH_3'), 'info');
			$file->content = file_get_contents(hikashop_getLanguagePath(JPATH_ROOT) . DS . 'en-GB' . DS . 'en-GB.' . HIKAMARKET_COMPONENT . '.ini');
		}
		$override_content = '';
		$override_path = hikashop_getLanguagePath(JPATH_ROOT) . DS . 'overrides' . DS . $code . '.override.ini';
		if(JFile::exists($override_path)) {
			$override_content = file_get_contents($override_path);
		}
		$this->assignRef('override_content', $override_content);
		$this->assignRef('showLatest', $showLatest);
		$this->assignRef('file', $file);
	}

	public function share(){
		$file = new stdClass();
		$file->name = hikaInput::get()->getString('code');
		$this->assignRef('file', $file);
	}

	public function css(){
		$file = hikaInput::get()->getCmd('file');
		$new = false;
		if(empty($file)) {
			$type = hikaInput::get()->getCmd('var');
			$filename = '';
			$new = true;
		} else {
			if(!preg_match('#^([-_A-Za-z0-9]*)_([-_A-Za-z0-9]*)$#i', $file, $result)) {
				hikamarket::display('Could not load the file '.$file.' properly');
				exit;
			}
			$type = $result[1];
			$filename = $result[2];
		}

		$content = hikaInput::get()->getString('csscontent', '');
		if(empty($content) && !$new) {
			$content = file_get_contents(HIKAMARKET_MEDIA . 'css' . DS . $type . '_' . $filename . '.css');
		}
		if(empty($content) && $new && file_exists(HIKAMARKET_MEDIA . 'css' . DS . $type . '_default.css')) {
			$content = file_get_contents(HIKAMARKET_MEDIA . 'css' . DS . $type . '_default.css');
		}

		if($filename == 'default' || $new) {
			$filename = 'custom';
			$new = true;
			$i = 1;
			while(file_exists(HIKAMARKET_MEDIA.'css' . DS . $type . '_' . $filename . '.css')) {
				$filename = 'custom' . $i;
				$i++;
			}
		}

		$this->assignRef('content', $content);
		$this->assignRef('filename', $filename);
		$this->assignRef('new', $new);
		$this->assignRef('type', $type);

		$editor = hikamarket::get('shop.helper.editor');
		$this->assignRef('editor', $editor);
	}

	public function getDoc($key) {
		$namekey = 'HKM_CONFIG_' . strtoupper(trim($key));
		$ret = JText::_($namekey);
		if($ret == $namekey) {
			return '';
		}
		return $ret;
	}

	public function docTip($key) {
		$ret = $this->getDoc($key);
		if(empty($ret))
			return '';
		return 	' data-toggle="hk-tooltip" data-title="'.htmlspecialchars($ret, ENT_COMPAT, 'UTF-8').'"';
	}

	protected function leftmenu($name, $data) {
		$this->menuname = $name;
		$this->menudata = $data;
		$this->setLayout('leftmenu');
		return $this->loadTemplate();
	}
}
