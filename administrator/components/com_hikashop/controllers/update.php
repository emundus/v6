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

class updateController extends HikashopBridgeController {
	function __construct($config = array()){
		parent::__construct($config);
		$this->modify_views[] = 'wizard';
		$this->modify[] = 'wizard_save';
		$this->registerDefaultTask('update');
		$this->display[] = 'post_install';
		$this->modify[] = 'process_data_save';
	}

	function install(){
		hikashop_setTitle('HikaShop','heartbeat','update');
		$newConfig = new stdClass();
		$newConfig->installcomplete = 1;
		$config = hikashop_config();
		$config->save($newConfig);
		$updateHelper = hikashop_get('helper.update');
		$updateHelper->addJoomfishElements();
		$updateHelper->addDefaultData();
		$updateHelper->createUploadFolders();
		$lang = JFactory::getLanguage();
		$code = $lang->getTag();
		$updateHelper->installMenu($code);
		if($code != 'en-GB') {
			$updateHelper->installMenu('en-GB');
		}
		$updateHelper->installTags();
		$updateHelper->addUpdateSite();
		$updateHelper->installExtensions();
		if(!empty($updateHelper->freshinstall)){
			$app = JFactory::getApplication();
			$app->redirect(hikashop_completeLink('update&task=wizard', false, true));
		}
		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Link', 'dashboard', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		$this->_iframe(HIKASHOP_UPDATEURL.'install&fromversion='.hikaInput::get()->getCmd('fromversion'));
	}

	function update(){
		$config = hikashop_config();
		if(!hikashop_isAllowed($config->get('acl_update_about_view','all'))){
			hikashop_display(JText::_('RESSOURCE_NOT_ALLOWED'),'error');
			return false;
		}
		hikashop_setTitle(JText::_('UPDATE_ABOUT'),'sync','update');
		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Link', 'dashboard', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		return $this->_iframe(HIKASHOP_UPDATEURL.'update');
	}
	function _iframe($url){
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator')){
			$config =& hikashop_config();
			$menu_style = $config->get('menu_style','title_bottom');
			if(HIKASHOP_J30) $menu_style = 'content_top';
			if($menu_style=='content_top'){
				echo hikashop_getMenu('',$menu_style);
			}
		}

		if(hikashop_isSSL())
			$url = str_replace('http://', 'https://', $url);
?>
		<div id="hikashop_div">
			<iframe allowtransparency="true" scrolling="auto" height="450px" frameborder="0" width="100%" name="hikashop_frame" id="hikashop_frame" src="<?php echo $url.'&level='.$config->get('level').'&component=hikashop&version='.$config->get('version'); ?>"></iframe>
		</div>
<?php
	}
	function wizard(){
		$app = JFactory::getApplication();
		if(!hikashop_isClient('administrator')){
			return;
		}
		$lang = JFactory::getLanguage();
		$code = $lang->getTag();
		$path = hikashop_getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_hikashop.ini';
		jimport('joomla.filesystem.file');
		if(!JFile::exists($path)){
			$url = HIKASHOP_UPDATEURL.'languageload&raw=1&code='.$code;

			$data = '';
			if(function_exists('curl_version')){
				$ch = curl_init();
				$timeout = 5;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				$data = curl_exec($ch);
				curl_close($ch);
			}else{
				$data = file_get_contents($url);
			}
			if(!empty($data)){
				$result = JFile::write($path, $data);
				if(!$result){
					$updateHelper = hikashop_get('helper.update');
					$updateHelper->installMenu($code);
					hikashop_display(JText::sprintf('FAIL_SAVE',$path),'error');
				} else {
					$lang->load(HIKASHOP_COMPONENT, JPATH_SITE, $code, true);
				}
			}else{
				hikashop_display(JText::sprintf('CANT_GET_LANGUAGE_FILE_CONTENT',$path),'error');
			}
		}

		hikaInput::get()->set( 'layout', 'wizard' );
		return parent::display();
	}

	function wizard_save() {
		$layoutType = hikaInput::get()->getVar('layout_type');
		$currency = hikaInput::get()->getVar('currency');
		$taxName = hikaInput::get()->getVar('tax_name');
		$taxRate = hikaInput::get()->getVar('tax_rate');
		$addressCountry = hikaInput::get()->getVar('address_country');
		$data = hikaInput::get()->get('data', array(), 'array');
		$addressState = (!empty($data['address']['address_state'])) ? ($data['address']['address_state']) : '';
		$shopAddress = hikaInput::get()->getVar('shop_address');
		$paypalEmail = hikaInput::get()->getVar('paypal_email');
		$productType = hikaInput::get()->getVar('product_type');
		$dataExample = hikaInput::get()->getVar('data_sample');

		$ratePlugin = hikashop_import('hikashop','rates');
		if($ratePlugin){
			$ratePlugin->updateRates();
		}

		$db = JFactory::getDBO();
		foreach($_POST as $key => $data){
			if($data == '0') continue;
			if(preg_match('#menu#',$key)){ // menu
				if(preg_match('#categories#',$key)){
					$alias = 'hikashop-menu-for-categories-listing';
				}else{
					$alias = 'hikashop-menu-for-products-listing';
				}
				$db->setQuery('SELECT * FROM '.hikashop_table('menu',false).' WHERE `alias` = '.$db->Quote($alias));
				$data = $db->loadAssoc();
				$db->setQuery('SELECT `menutype` FROM '.hikashop_table('menu',false).' WHERE `home` = 1');
				$menutype = $db->loadResult();
				$data['menutype'] = $menutype;
				$menuTable = JTable::getInstance('Menu', 'JTable', array());
				if(is_object($menuTable)){
					$menuTable->save($data);
					if(method_exists($menuTable,'rebuild')){
						$menuTable->rebuild();
					}
				}
			}elseif(preg_match('#module#',$key)){ // module
				if(preg_match('#categories#',$key)){
					$db->setQuery('UPDATE '.hikashop_table('modules',false).' SET `published` = 1 WHERE `title` = '.$db->Quote('Categories on 2 levels'));
					$db->execute();
				}
			}
		}

		$db->setQuery('SELECT `config_value` FROM '.hikashop_table('config').' WHERE `config_namekey` = "default_params"');
		$oldDefaultParams = $db->loadResult();
		$oldDefaultParams = hikashop_unserialize(base64_decode($oldDefaultParams));
		$oldDefaultParams['layout_type'] = preg_replace('#listing_#','',$layoutType);
		$defaultParams = base64_encode(serialize($oldDefaultParams));
		if($addressCountry == 'country_United_States_of_America_223')
			$main_zone = $addressState;
		else
			$main_zone = $addressCountry;
		$zoneClass = hikashop_get('class.zone');
		$zone = $zoneClass->get($main_zone);
		$db->setQuery('REPLACE INTO '.hikashop_table('config').' (config_namekey, config_value) VALUES ("main_tax_zone", '.$db->Quote($zone->zone_id).'), ("store_address", '.$db->Quote($shopAddress).'), ("main_currency", '.$db->Quote($currency).'), ("default_params", '.$db->Quote($defaultParams).')');
		$db->execute();

		$db->setQuery('UPDATE '.hikashop_table('field').' SET `field_default` = '.$db->Quote($addressState).' WHERE field_namekey = "address_state"');
		$db->execute();
		$db->setQuery('UPDATE '.hikashop_table('field').' SET `field_default` = '.$db->Quote($addressCountry).' WHERE field_namekey = "address_country"');
		$db->execute();

		$import_language = hikaInput::get()->getVar('import_language');
		if($import_language != '0'){
			if(preg_match('#_#',$import_language)){
				$languages = explode('_',$import_language);
			}else{
				$languages = array($import_language);
			}
			$updateHelper = hikashop_get('helper.update');
			foreach($languages as $code){
				$path = hikashop_getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_hikashop.ini';
				jimport('joomla.filesystem.file');
				if(!JFile::exists($path)) {
					$url = str_replace('https://','http://',HIKASHOP_UPDATEURL.'languageload&raw=1&code='.$code);
					$ret = $this->retrieveFile($url, $path);
					if($ret === false) {
						hikashop_display(JText::sprintf('CANT_GET_LANGUAGE_FILE_CONTENT',$path),'error');
					} elseif($ret === 1) {
						$updateHelper->installMenu($code);
						hikashop_display(JText::_('HIKASHOP_SUCC_SAVED'),'success');
					} else {
						hikashop_display(JText::sprintf('FAIL_SAVE',$path),'error');
					}
				}
			}
		}

		$install_eu_taxes = hikaInput::get()->getVar('install_eu_taxes');
		if($install_eu_taxes === '1') {
			$jconfig = JFactory::getConfig();
			$tmp_dest = $jconfig->get('tmp_path');

			$url = HIKASHOP_URL.'index.php?option=com_updateme&ctrl=download&plugin=tax_europe';
			$file = JPath::clean($tmp_dest . DS .'european_taxes.zip');
			if(!file_exists($file))
				$ret = $this->retrieveFile($url, $file, true);
			else
				$ret = 1;

			if($ret === 1) {
				if(HIKASHOP_J30)
					jimport('joomla.archive.archive');
				else
					jimport('joomla.filesystem.archive');

				if(HIKASHOP_J40)
					$archiveClass = new Joomla\Archive\Archive();
				else
					$archiveClass = new JArchive();

				$zip = $archiveClass->getAdapter('zip');

				$path = JPath::clean($tmp_dest . DS . 'eu_taxes' . DS); // pathinfo(realpath($file), PATHINFO_DIRNAME);
				if(!JFile::exists($path))
					JFolder::create($path);

				$ret = $zip->extract($file, $path);

				if($ret) {
					$eu_tax_file = JPath::clean($path . '/install.php');
					if(!JFile::exists($eu_tax_file))
						$ret = false;
				}

				if(!$ret) {
					hikashop_display(JText::sprintf('WIZZARD_DATA_ERROR', 'unzipping', 'index.php?option=com_hikashop&ctrl=update&task=wizard'), 'error');
				} else {
					try {
						include_once($eu_tax_file);
					} catch(Exception $e) {}

					if(class_exists('pkg_tax_europeInstallerScript')) {
						$tax_install = new pkg_tax_europeInstallerScript();
						$tax_install->redirect = false;
						$tax_install->preflight(null, null);
						$tax_install->setMainCountry(null);
					}
				}

				if(JFile::exists($path) && strpos($path, 'eu_taxes') !== false)
					JFolder::delete($path);

				if(JFile::exists($file))
					JFile::delete($file);
			}
		}

		if(isset($taxRate) && (!empty($taxRate) || $taxRate != '0')){
			$taxRate = (float)$taxRate / 100;
			$db->setQuery('REPLACE INTO '.hikashop_table('tax').' (tax_namekey,tax_rate) VALUES ('.$db->Quote($taxName).','.(float)$taxRate.')');
			$db->execute();

			$db->setQuery('SELECT `taxation_id` FROM '.hikashop_table('taxation').' ORDER BY `taxation_id` DESC LIMIT 0,1');
			$maxId = $db->loadResult();
			if(is_null($maxId)){
				$maxId = 1;
			}else{
				$maxId = (int)$maxId + 1 ;
			}
			$empty = $db->Quote('');

			$tax = array(
				'taxation_id' => (int)$maxId,
				'zone_namekey' => $empty,
				'category_namekey' => $db->Quote('default_tax'),
				'tax_namekey' => $db->Quote($taxName),
				'taxation_published' => 1,
				'taxation_type' => $empty,
				'taxation_access' => $db->Quote('all'),
				'taxation_cumulative' => 0,
				'taxation_post_code' => $empty,
				'taxation_date_start' => 0,
				'taxation_date_end' => 0,
				'taxation_internal_code' => 0,
				'taxation_note' => $empty,
			);
			if($addressCountry == 'country_United_States_of_America_223')
				$tax['zone_namekey'] = $db->Quote($addressState);
			else
				$tax['zone_namekey'] = $db->Quote($addressCountry);

			$query = 'INSERT INTO '.hikashop_table('taxation').' ('.implode(',',array_keys($tax)).') VALUES ('.implode(',',$tax).')';

			$db->setQuery($query);
			$db->execute();
		}

		if(isset($paypalEmail) && !empty($paypalEmail)){
			$pluginData = array(
				'payment' => array(
					'payment_name' => 'PayPal',
					'payment_published' => '1',
					'payment_images' => 'MasterCard,VISA,Credit_card,PayPal',
					'payment_price' => '',
					'payment_params' => array(
						'url' => 'https://www.paypal.com/cgi-bin/webscr',
						'email' => $paypalEmail,
					),
					'payment_zone_namekey' => '',
					'payment_access' => 'all',
					'payment_id' => '0',
					'payment_type' => 'paypal',
				),
			);
			hikaInput::get()->set('name','paypal');
			hikaInput::get()->set('plugin_type','payment');
			hikaInput::get()->set('data',$pluginData);

			$pluginsController = hikashop_get('controller.plugins');
			$pluginsController->store(true);
		}
		if(isset($productType) && !empty($productType)){
			if($productType == 'real'){
				$forceShipping = 1;
			}else{
				$forceShipping = 0;
			}
			$db->setQuery('REPLACE INTO '.hikashop_table('config').' (config_namekey, config_value) VALUES ("force_shipping", '.(int)$forceShipping.')');
			$db->execute();
			if($productType == 'virtual'){
				$product_type = 'virtual';
			}else{
				$product_type = 'shippable';
			}
			$db->setQuery('REPLACE INTO '.hikashop_table('config').' (config_namekey, config_value) VALUES ("default_product_type", '.(int)$product_type.')');
			$db->execute();
		}

		if ($dataExample==1) //Install data sample
		{
			$app = JFactory::getApplication();
			$app->setUserState('WIZARD_DATA_SAMPLE_PAYPAL',$paypalEmail);
			$app->redirect('index.php?option=com_hikashop&ctrl=update&task=process_data_save&step=1&'.hikashop_getFormToken().'=1');
		}

		$url = 'index.php?option=com_hikashop&ctrl=product&task=add';
		$this->setRedirect($url);
	}

	private function retrieveFile($url, $dest) {
		$data = '';
		if(function_exists('curl_version')){
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$data = curl_exec($ch);
			curl_close($ch);
		} else {
			$data = file_get_contents($url);
		}

		if(empty($data))
			return false;

		jimport('joomla.filesystem.file');
		$result = JFile::write($dest, $data);
		return ($result) ? 1 : 0;
	}

	function state(){
		hikaInput::get()->set( 'layout', 'state' );
		return parent::display();
	}

	function post_install(){
		$this->_iframe(HIKASHOP_UPDATEURL.'install&fromversion='.hikaInput::get()->getCmd('fromversion'));
	}


	function process_data_save() {
		if( !isset($_GET['step']) )
			return;

		$step = hikaInput::get()->getInt('step');
		$url = 'index.php?option=com_hikashop&ctrl=update&task=process_data_save&'.hikashop_getFormToken().'=1&step='.$step;
		$redirect = 'index.php?option=com_hikashop&ctrl=product&task=add';
		$error = false;
		$app = JFactory::getApplication();
		$jconfig = JFactory::getConfig();
		$tmp_dest = $jconfig->get('tmp_path');
		$urlsrc = "http://www.hikashop.com/sampledata/dataexample.zip"; //server URL
		$destination = $tmp_dest.'/dataexample.zip'; //HIKASHOP_ROOT.'tmp\dataexample.zip';
		$path = pathinfo(realpath($destination), PATHINFO_DIRNAME);
		switch ($step)
		{
			case 1: //Download zip
				if(!function_exists('curl_init')){
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::sprintf('CURL_ERROR',$url),'error');
					$error = true;
				}
				else
				{
					$getContent = false;
					$curl = curl_init ();
					curl_setopt($curl, CURLOPT_TIMEOUT, 50);
					curl_setopt ($curl, CURLOPT_URL, $urlsrc);
					curl_setopt($curl, CURLOPT_HEADER, 0);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($curl, CURLOPT_BINARYTRANSFER,1);
					curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, 1);

					$rawdata = curl_exec($curl);

					$httpcode=curl_getinfo($curl, CURLINFO_HTTP_CODE);
					if ($httpcode!=200)
						$getContent = true;

					curl_close ($curl);


					if(file_exists($destination))
						@unlink($destination);
					$fhandle = fopen($destination, "x");
					try {
						fwrite($fhandle, $rawdata);
						fclose($fhandle);
					} catch(Exception $e) {
						echo 'fail : '.$e->getMessage();
						$getContent = true;
					}

					if ($getContent)
						if(!file_put_contents($destination, file_get_contents($urlsrc)))
						{
							$app->enqueueMessage(JText::sprintf('WIZZARD_DATA_ERROR','downloading',$url), 'error');
							$error = true;
						}
				}
			break;

			case 2 : //Unzip
				if(HIKASHOP_J30){
					jimport('joomla.archive.archive');
				}else{
					jimport('joomla.filesystem.archive');
				}
				if(HIKASHOP_J40)
					$archiveClass = new Joomla\Archive\Archive();
				else
					$archiveClass = new JArchive();
				$zip = $archiveClass->getAdapter('zip');

				if(!$zip->extract($destination,$path))
				{
					$app->enqueueMessage(JText::sprintf('WIZZARD_DATA_ERROR','unzipping',$url), 'error');
					$error = true;
				}
			break;

			case 3 ://Copy
				$config = hikashop_config();
				jimport('joomla.filesystem.folder');
				jimport( 'joomla.filesystem.file' );

				$uploadSecudeFolder = str_replace('/','\\',$config->get('uploadsecurefolder','media/com_hikashop/upload/safe/'));
				$src = $path.'\dataexample\upload\safe\\';
				$dst = HIKASHOP_ROOT.$uploadSecudeFolder;

				$files = scandir($src,1);
				foreach($files as $f){
					if($f!='..' && $f!='.' && !file_exists($dst.$f)){
						if(is_dir($src.$f)){
							$ret = JFolder::create($dst.$f);
						}else{
							$ret = JFile::copy($src.$f, $dst.$f, '', true); //Overwrite
						}
						if (!$ret)
							$app->enqueueMessage(JText::sprintf('WIZZARD_DATA_ERROR_COPY',$f,$url), 'error');
					}
				}

				$uploadFolder = str_replace('/','\\',$config->get('uploadfolder','images/com_hikashop/upload/'));
				$src = $path.'\dataexample\upload\\';
				$dst = HIKASHOP_ROOT.$uploadFolder;

				$files = scandir($src,1);
				foreach($files as $f){
					if($f!='..' && $f!='.' && !file_exists($dst.$f)){
						if(is_dir($src.$f)){
							$ret = JFolder::create($dst.$f);
						}else{
							$ret = JFile::copy($src.$f, $dst.$f, '', true); //Overwrite
						}
						if (!$ret)
							$app->enqueueMessage(JText::sprintf('WIZZARD_DATA_ERROR_COPY',$f,$url), 'error');
					}
				}
			break;

			case 4 : //exec script
				$fh = fopen($tmp_dest.'/dataexample/script.sql', 'r+') or die("Can't open file tmp/dataexample/script.sql");
				$data = explode("\r\n",fread($fh,filesize($tmp_dest.'/dataexample/script.sql')));
				$db = JFactory::getDBO();
				foreach ($data as $d)
				{
					if (!empty($d))
					{
						try {
							$db->setQuery($d);
							$db->execute();
						} catch(Exception $e) {
							echo 'Fail query : '.$e->getMessage();
							$error = true;
							$getContent = true;
						}
					}
				}

				$paypalEmail = $app->getUserState('WIZARD_DATA_SAMPLE_PAYPAL');
				if (!empty($paypalEmail))
				{
					$db->setQuery("UPDATE `#__hikashop_payment` SET `payment_published` = '0'");
					$db->execute();
					$db->setQuery("UPDATE `#__hikashop_payment` SET `payment_published` = '1' WHERE `payment_id` = '1'");
					$db->execute();
				}
				else
				{
					$db->setQuery("UPDATE `#__hikashop_payment` SET `payment_published` = '1'");
					$db->execute();
				}

				$categoryClass = hikashop_get('class.category');
				$query = 'SELECT category_namekey,category_left,category_right,category_depth,category_id,category_parent_id FROM `#__hikashop_category` ORDER BY category_left ASC';
				$db->setQuery($query);
				$categories = $db->loadObjectList();
				$root = null;
				$categoryClass->categories = array();
				foreach($categories as $cat)
				{
					$categoryClass->categories[$cat->category_parent_id][]=$cat;
					if(empty($cat->category_parent_id))
						$root = $cat;
				}
				$categoryClass->rebuildTree($root,0,1);
			break;

			case 5 : //Delete everything (try catch ?)
				jimport( 'joomla.filesystem.folder' );
				jimport( 'joomla.filesystem.file' );
				if (!JFolder::delete($path.'\dataexample\\') or !JFile::delete($path.'\dataexample.zip'))
				{
					$app->enqueueMessage(JText::sprintf('WIZZARD_DATA_ERROR','deleting',$url), 'error');
					$error = true;
				}
			break;

			default:
				$error = true;
			break;
		}
		$step++;
		if (!$error)
			$redirect = 'index.php?option=com_hikashop&ctrl=update&task=process_data_save&'.hikashop_getFormToken().'=1&step='.$step;
		if ($step > 5)
			$app->enqueueMessage(JText::_('WIZARD_DATA_END'));
		$app->redirect($redirect);
	}
}
