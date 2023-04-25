<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Component\Fields\Administrator\Model\FieldModel;


//Global definitions use for front
if( !defined('DS') ) {
    define( 'DS', DIRECTORY_SEPARATOR );
}


jimport('joomla.plugin.plugin');

/**
 * Falang Driver Plugin
 */
class plgSystemFalangdriver extends CMSPlugin
{

    public function __construct(&$subject, $config = array())
    {


        parent::__construct($subject, $config);
        //load plugin language
        $this->loadLanguage();

        $this->setupCoreFileOverride();

        // This plugin is only relevant for use within the frontend!
        if (JFactory::getApplication()->isClient('administrator')) {
            return;
        }

        //@since 2.9.0
        //add this setup in the constuctor due to system plugin who use $this->db (constucted by reflexion of JPlugin)
        //and no more in the onAfterInitialise
        if (!$this->isFalangDriverActive()) {
            $this->setupDatabaseDriverOverride();
        }

    }

    /**
     * System Event: onAfterInitialise
     *
     * @return    string
     */
    function onAfterInitialise()
    {
        // This plugin is only relevant for use within the frontend!
        if (JFactory::getApplication()->isClient('administrator')) {
            return;
        }

        //fix for joomla > 3.4.0
        $app = JFactory::getApplication();
        if ($app->isClient('site')) {
            $router = $app->getRouter();

            // attach build rules for translation on SEF
            $router->attachBuildRule(array($this, 'buildRule'));

            // attach build rules for translation on SEF
            $router->attachParseRule(array($this, 'parseRule'));
        }
        //end fix

        //override joomla compoenent routeur
        $this->setupAdvancedRouter();

    }

    public function buildRule(&$router, &$uri)
    {
        $lang = $uri->getVar('lang');
        $default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');

        //we build the route for category list article
        if ($lang != $default_lang && $uri->getVar('id') != null && $uri->getVar('catid') != null &&
            $uri->getVar('option') == 'com_content') {//&& $uri->getVar('view') == 'article'

            $fManager = FalangManager::getInstance();
            $id_lang = $fManager->getLanguageID($lang);

            // Make sure we have the id and the alias
            if (strpos($uri->getVar('id'), ':') > 0) {
                list($tmp, $id) = explode(':', $uri->getVar('id'), 2);
                $db = JFactory::getDbo();
                $dbQuery = $db->getQuery(true)
                    ->select('fc.value')
                    ->from('#__falang_content fc')
                    ->where('fc.reference_id = ' . (int)$tmp)
                    ->where('fc.language_id = ' . (int)$id_lang)
                    ->where('fc.reference_field = \'alias\'')
                    ->where('fc.published = 1')
                    ->where('fc.reference_table = \'content\'');

                $db->setQuery($dbQuery);
                $alias = $db->loadResult();
                if (isset($alias)) {
                    $uri->setVar('id', $tmp . ':' . $alias);
                }
            }
            // Make sure we have the id and the alias
            if (strpos($uri->getVar('catid'), ':') > 0) {
                list($tmp2, $catid) = explode(':', $uri->getVar('catid'), 2);

                $db = JFactory::getDbo();
                $dbQuery = $db->getQuery(true)
                    ->select('fc.value')
                    ->from('#__falang_content fc')
                    ->where('fc.reference_id = ' . (int)$tmp2)
                    ->where('fc.language_id = ' . (int)$id_lang)
                    ->where('fc.reference_field = \'alias\'')
                    ->where('fc.published = 1')
                    ->where('fc.reference_table = \'categories\'');

                $db->setQuery($dbQuery);
                $alias = $db->loadResult();
                if (isset($alias)) {
                    $uri->setVar('catid', $tmp2 . ':' . $alias);
                }
            }
        }

        //fix canonical if sef plugin is enabled
        $sef_plugin = JPluginHelper::getPlugin('system', 'sef');
        if (!empty($sef_plugin)) {
            if ($lang != $default_lang && $uri->getVar('id') != null && $uri->getVar('catid') != null &&
                $uri->getVar('option') == 'com_content') {//&& $uri->getVar('view') == 'article'
                $fManager = FalangManager::getInstance();
                $id_lang = $fManager->getLanguageID($lang);

                // Make sure we have the id and the alias
                if (strpos($uri->getVar('id'), ':') === false) {
                    //we use id in the query to be translated.
                    $db = JFactory::getDbo();
                    $dbQuery = $db->getQuery(true)
                        ->select('alias,id')
                        ->from('#__content')
                        ->where('id=' . (int)$uri->getVar('id'));
                    $db->setQuery($dbQuery);
                    $alias = $db->loadResult();
                    if (isset($alias)) {
                        $uri->setVar('id', $uri->getVar('id') . ':' . $alias);
                    }
                }
            }
        }

        //build route for hikashop product
        if ($uri->getVar('option') == 'com_hikashop' && $uri->getVar('ctrl') == 'product' && $uri->getVar('task') == 'show') {
            // on native language look in falang table
            if ($default_lang != $lang) {
                $fManager = FalangManager::getInstance();
                $id_lang = $fManager->getLanguageID($lang);
                $id = $uri->getVar('cid');
                $db = JFactory::getDbo();
                $dbQuery = $db->getQuery(true)
                    ->select('fc.value')
                    ->from('#__falang_content fc')
                    ->where('fc.reference_id = ' . (int)$id)
                    ->where('fc.language_id = ' . (int)$id_lang)
                    ->where('fc.reference_field = \'product_alias\'')
                    ->where('fc.published = 1')
                    ->where('fc.reference_table = \'hikashop_product\'');

                $db->setQuery($dbQuery);
                $alias = $db->loadResult();
                if (isset($alias)) {
                    $uri->setVar('name', $alias);
                }

            } else {
                // translated languague look in native table
                $id = $uri->getVar('cid');
                $db = JFactory::getDbo();
                $dbQuery = $db->getQuery(true)
                    ->select('product_alias')
                    ->from('#__hikashop_product')
                    ->where('product_id = ' . (int)$id);
                $db->setQuery($dbQuery);
                $alias = $db->loadResult();
                if (isset($alias)) {
                    $uri->setVar('name', $alias);
                }
            }
            //
        }
        //build route for hikahsop category list
        if ($uri->getVar('option') == 'com_hikashop' && $uri->getVar('ctrl') == 'category' && $uri->getVar('task') == 'listing') {
            // on native language look in falang table
            if ($default_lang != $lang) {
                $fManager = FalangManager::getInstance();
                $id_lang = $fManager->getLanguageID($lang);
                $id = $uri->getVar('cid');
                $db = JFactory::getDbo();
                $dbQuery = $db->getQuery(true)
                    ->select('fc.value')
                    ->from('#__falang_content fc')
                    ->where('fc.reference_id = ' . (int)$id)
                    ->where('fc.language_id = ' . (int)$id_lang)
                    ->where('fc.reference_field = \'category_alias\'')
                    ->where('fc.published = 1')
                    ->where('fc.reference_table = \'hikashop_category\'');

                $db->setQuery($dbQuery);
                $alias = $db->loadResult();
                if (isset($alias)) {
                    $uri->setVar('name', $alias);
                }

            } else {
                // translated languague look in native table
                $id = $uri->getVar('cid');
                $db = JFactory::getDbo();
                $dbQuery = $db->getQuery(true)
                    ->select('category_alias')
                    ->from('#__hikashop_category')
                    ->where('category_id = ' . (int)$id);
                $db->setQuery($dbQuery);
                $alias = $db->loadResult();
                if (isset($alias)) {
                    $uri->setVar('name', $alias);
                }
            }
        }
        //build route for k2 category list
        //v2.2.2 add download test due to download link bug in other case.
        if ($uri->getVar('option') == 'com_k2' && $uri->getVar('view') == 'item' && $uri->getVar('task') != 'download') {
            // on native language look in falang table
            if ($default_lang != $lang) {
                $fManager = FalangManager::getInstance();
                $id_lang = $fManager->getLanguageID($lang);

                // Make sure we have the id and the alias
                if (strpos($uri->getVar('id'), ':') > 0) {
                    list($tmp, $id) = explode(':', $uri->getVar('id'), 2);
                    $db = JFactory::getDbo();
                    $dbQuery = $db->getQuery(true)
                        ->select('fc.value')
                        ->from('#__falang_content fc')
                        ->where('fc.reference_id = ' . (int)$tmp)
                        ->where('fc.language_id = ' . (int)$id_lang)
                        ->where('fc.reference_field = \'alias\'')
                        ->where('fc.published = 1')
                        ->where('fc.reference_table = \'k2_items\'');

                    $db->setQuery($dbQuery);
                    $alias = $db->loadResult();
                    if (isset($alias)) {
                        $uri->setVar('id', $tmp . ':' . $alias);
                    }
                }
            } else {
                // translated languague look in native table
                $tmp = $uri->getVar('id');
                // Make sure we have the id and the alias
                if (strpos($tmp, ':') > 0) {
                    list($tmp, $id) = explode(':', $tmp, 2);
                }

                $db = JFactory::getDbo();
                $dbQuery = $db->getQuery(true)
                    ->select('alias')
                    ->from('#__k2_items')
                    ->where('id = ' . (int)$tmp);
                $db->setQuery($dbQuery);
                $alias = $db->loadResult();
                if (isset($alias)) {
                    $uri->setVar('id', $tmp . ':' . $alias);
                }
            }
        }

        if ($uri->getVar('option') == 'com_djcatalog2' && $uri->getVar('view') == 'item') {
            $this->buildRuleAlias($uri, 'djc2_items', 'alias');
        }


        return array();
    }

    public function buildRuleAlias(&$uri, $reference_table, $alias_name)
    {
        $lang = $uri->getVar('lang');
        $default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');

        //look in Falang Table
        if ($default_lang != $lang) {
            $fManager = FalangManager::getInstance();
            $id_lang = $fManager->getLanguageID($lang);

            // Make sure we have the id and the alias
            if (strpos($uri->getVar('id'), ':') > 0) {
                list($id, $tmp) = explode(':', $uri->getVar('id'), 2);
                $db = JFactory::getDbo();
                $dbQuery = $db->getQuery(true);
                $dbQuery->select('fc.value')
                    ->from('#__falang_content fc')
                    ->where('fc.reference_id = ' . $dbQuery->q($id))
                    ->where('fc.language_id = ' . $dbQuery->q($id_lang))
                    ->where('fc.reference_field = ' . $dbQuery->q($alias_name))
                    ->where('fc.published = 1')
                    ->where('fc.reference_table = ' . $dbQuery->q($reference_table));

                $db->setQuery($dbQuery);
                $alias = $db->loadResult();
                if (isset($alias)) {
                    $uri->setVar('id', $id . ':' . $alias);
                }
            }
        } else {
            // translated languague look in native table
            $tmp = $uri->getVar('id');
            // Make sure we have the id and the alias
            if (strpos($tmp, ':') > 0) {
                list($tmp, $id) = explode(':', $tmp, 2);
            }

            $db = JFactory::getDbo();
            $dbQuery = $db->getQuery(true);
            $dbQuery->select($dbQuery->qn($alias_name))
                ->from($dbQuery->qn('#__' . $reference_table))
                ->where('id = ' . $dbQuery->q($tmp));
            $db->setQuery($dbQuery);
            $alias = $db->loadResult();
            if (isset($alias)) {
                $uri->setVar('id', $tmp . ':' . $alias);
            }
        }
    }

    public function parseRule(&$router, &$uri)
    {
        static $done = false;
        if (!$done) {
            $done = true;
            $conf = JFactory::getConfig();
            $lang = JFactory::getLanguage();
            $default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');

            //fix for virtuemart / lang must be reset
            if (JComponentHelper::isEnabled('com_virtuemart', true)){
                if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');
                VmConfig::loadConfig();
                vmLanguage::$jSelLangTag = false;
                vmLanguage::initialise(true);
            }


            // Workaround for Joomla > 3.7.0, we need to set the correct language for the cache handler because the menu get already cached with the
            // language defined in JApplicationSite::initialiseApp(), but this language is the wrong if we change the language because language detection
            // is done in the Joomla system plugin languagefilter. The solution is to load the com_menus cache and set the correct language and reload
            // the menu, this is already done for Jooma 3.4.0, it seams that the cache works since 3.7.0

            //TODO 4.0 remove this probably language already set
//            if ($conf->get('caching', 0) > 0) {
//                $cache = JFactory::getCache('com_menus', 'callback');
//                $cache->options['language'] = $lang->getTag();
//                if ($lang->getTag() != $default_lang) {
//                    $cache->options['caching'] = false;
//                }
//            }
//
//            //reload menu
//            JFactory::getApplication()->getMenu()->__construct();
//            //rewrite Menu route with translated alias
//            $app = JFactory::getApplication();
//            $menu = $app->getMenu()->getMenu();
//
//            //workaround for Joomla > 3.7.0 continue.
//            if ($conf->get('caching', 0) > 0) {
//                foreach ($menu as &$item) {
//                    $item->route = '';
//                    if ($item->level > 1) {
//                        if (array_key_exists($item->parent_id, $menu)) {
//                            $item->route = $menu[$item->parent_id]->route . '/';
//                        }
//                    }
//                    $item->route .= $item->alias;
//                }
//            }
//
        }
        return array();
    }

    public function isFalangDriverActive()
    {
        $db = JFactory::getDBO();

        return is_a($db, 'JFalangDatabase');
    }


    function onAfterDispatch()
    {
        if (JFactory::getApplication()->isClient('site') && $this->isFalangDriverActive()) {
            include_once(JPATH_ADMINISTRATOR . '/components/com_falang/version.php');
            $version = new FalangVersion();
            if ($version->_versiontype == 'free') {
                FalangManager::setBuffer();
            }
            return true;
        }
    }


    function setupDatabaseDriverOverride()
    {
        //override only the override file exist
        if (file_exists(dirname(__FILE__) . '/falang_database.php')) {
            require_once(dirname(__FILE__) . '/falang_database.php');

            $conf = JFactory::getConfig();

            $host = $conf->get('host');
            $user = $conf->get('user');
            $password = $conf->get('password');
            $db = $conf->get('db');
            $dbprefix = $conf->get('dbprefix');
            $driver = $conf->get('dbtype');
            $debug = $conf->get('debug');

            $options = array('driver' => $driver, "host" => $host, "user" => $user, "password" => $password, "database" => $db, "prefix" => $dbprefix, "select" => true);
            $db = new JFalangDatabase($options);
            //sbou4
            $db->setDebug($debug);

            //TODO 4.0 voir si nécéssaire et comment le remettre en place
            //            if ($db->getErrorNum() > 2)
            //            {
            //                JError::raiseError('joomla.library:' . $db->getErrorNum(), 'JDatabase::getInstance: Could not connect to database <br/>' . $db->getErrorMsg());
            //            }

            $container = Factory::getContainer();
            Factory::$database = null;//
            Factory::$database = $db;


        }

    }

    /*
     * Override services in the container
     * */

    public function onBeforeExecute(BeforeExecuteEvent $event) {
        return;
    }


    private function setBuffer()
    {
        $doc = JFactory::getDocument();
        $cacheBuf = $doc->getBuffer('component');

        $cacheBuf2 =
            '<div><a title="Faboba : Cr&eacute;ation de composant'.
            'Joomla" style="font-size: 9px;; visibility: visible;'.
            'display:inline;" href="http://www.faboba'.
            '.com" target="_blank">FaLang tra'.
            'nslation syste'.
            'm by Faboba</a></div>';

        if ($doc->_type == 'html')
            $doc->setBuffer($cacheBuf . $cacheBuf2,'component');

    }


    /*
     * Use trigger to activate the language selection in the template
     */
    function onContentPrepareForm($form, $data)
    {
        if (JFactory::getApplication()->isClient('site')){return;}

	    $this->enabledTplTranslation($form,$data);

	    $custom_fields = JPluginHelper::isEnabled('system', 'fields');
	    if ($custom_fields){
		    $this->loadCustomFields($form, $data);
	    }
    }

	//use to set the value of the custom fields to the falang translation form
	//custom fields exist only since Joomla 3.7
	//actually can't work because fields_values table don't have id key

	private function loadCustomFields($form, $data){

		$input = JFactory::getApplication()->input;
		$option = $input->get('option');
		$task = $input->get('task');
		$catid = $input->get('catid');
		$reference_id = $input->get('reference_id');
		$cid = $input->get('cid');
		$language_id = $input->get('language_id'); //from quickump it's this one
		$select_language_id = $input->get('select_language_id');//from falang list it's selectec langauge

		if ($option == 'com_falang' && ($task == 'translate.edit' || ($task == 'translate.apply') )) {


			//JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

			$context = $form->getName();

			// When a category is edited, the context is com_categories.categorycom_content
			if (strpos($context, 'com_categories.category') === 0) {
				$context = str_replace('com_categories.category', '', $context) . '.categories';
			}

			$parts = FieldsHelper::extract($context, $form);

			if (!$parts) {
				return true;
			}

			if (empty($reference_id)){
				$reference_id = current($cid);
			}

			//load category from original item to set to the translation
			//necessary to load the related custom fields even if a translation for them don't exist.
			if ( !empty($reference_id) && $catid == 'content'){
				//sbou4
				$model =  new Joomla\Component\Content\Administrator\Model\ArticleModel;
//				JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
				//$model = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request'=>true));
				$item = $model->getItem($reference_id);
				$data->catid = $item->catid;
			}

			// Getting the fields
			$fields = FieldsHelper::getFields($parts[0] . '.' . $parts[1], $data);


			$db = JFactory::getDbo();
			$fManager = FalangManager::getInstance();
			$content_element = $fManager->getContentElement($catid);

			if (empty($content_element)){
				return;
			}


			if (empty($language_id)){
				$language_id = $select_language_id;
			}
			//load com_fields values (json format)
			$translations =  $fManager->getRawFieldTranslations($content_element->getTableName(),'com_fields',$reference_id,$language_id);


			if (empty($translations)) {
				$params = JComponentHelper::getParams('com_falang');
				$copy_cusom_fields = $params->get('copy_custom_fields',false);


				if ($copy_cusom_fields == false){
					return true;
				}

				$original = $fManager->getRawFieldOrigninal($reference_id);

				//load orinal customfield to translation
				foreach ($fields as $field)
				{
					if (isset($original[$field->id])){
						$value  = $original[$field->id];
						$form->setValue($field->name, 'com_fields', $value);
					}

				}

			}

			$json_value = json_decode($translations);
			foreach ($fields as $field)
			{
				if (isset($json_value->{$field->name})) {
					$form->setValue($field->name, 'com_fields', $json_value->{$field->name});
				}
			}

		}

		return true;
	}

	//use to enable template by langugage (paid version only)
	private function enabledTplTranslation($form, $data){
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_falang');
		$show_tpl_lang = $params->get('show_tpl_lang');

		if (!isset($show_tpl_lang) || $show_tpl_lang == '0' ) {return;}


		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}
		if ((is_array($data) && array_key_exists('home', $data))
			|| ((is_object($data) && isset($data->home) ))) {
			$form->setFieldAttribute('home', 'readonly', 'false');
		}
	}

	//throw by Falang
	// use for joomla 3.7+ to save the custom fields in th custom fields table
	public function onAfterTranslationSave($post){
		//system fields plugins need to be published.
		$fields_plugin = JPluginHelper::getPlugin('system', 'fields');
		if (empty($fields_plugin)){return true;}

		$input = JFactory::getApplication()->input;
		$catid = $input->get('catid');
		$language_id = $input->get('language_id');
		$reference_id = $input->get('reference_id');
		$formData = new JRegistry($input->get('jform', '', 'array'));
		$context = $catid;

		//First release only content supported.
		if ($context != 'content'){return;}

		//TODO not set article here
		//load the content item to have the custom field associated with the categories of this item.
		if ( !empty($reference_id) && $catid == 'content'){
			//load default cateogry for this content
			//need to use admin model
			//sbou4
			//JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_content/models', 'ContentModel');
			$model =  new Joomla\Component\Content\Administrator\Model\ArticleModel;
			//$model = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request'=>true));
			$contentParams = JComponentHelper::getParams('com_content');
			$model->setState('params', $contentParams);
			$item = $model->getItem($reference_id);
		}

		$fields = FieldsHelper::getFields('com_'.$context. '.' . 'article', $item);

		if (!$fields) {
			return true;
		}

		// Get the translated fields data
		$fieldsData = !empty($formData) ? (array)$formData['com_fields'] : array();

		// Loading the model
		//sbou4
		//$model = JModelLegacy::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
		//$model = FieldModel::getInstance('Field', array('ignore_request'=>true));
		$db = JFactory::getDbo();
		$user = JFactory::getUser();

		$values = array();
		// Loop over the fields
		foreach ($fields as $field) {
			// Determine the value if it is available from the data
			$value = key_exists($field->name, $fieldsData) ? $fieldsData[$field->name] : null;
			$values[$field->name] = $value;
		}


		//save $values array in json format
		if (!empty($values)){
			//get previous com_fields falang translation
			//get previous value if exit to make update or insert

			$query = $db->getQuery(true);
			$query->select($query->qn('id'))
				->from($query->qn('#__falang_content'))
				->where($db->qn('language_id') . ' = ' . $db->q($language_id))
				->where($db->qn('reference_id') . ' = ' . $reference_id)
				->where($db->qn('reference_field') . ' = ' . $db->q('com_fields'))
				->where($db->qn('reference_table') . ' = ' . $db->q($context));

			$db->setQuery($query);
			$falangId = $db->loadResult();


			$jsonValues = json_encode($values);
			$fieldContent = new falangContent($db);
			if (isset($falangId)){$fieldContent->id = $falangId;}
			$fieldContent->reference_id = $reference_id ;
			$fieldContent->language_id = $language_id;
			$fieldContent->reference_table= $context;
			$fieldContent->reference_field= 'com_fields';
			$fieldContent->value = $jsonValues;
			// the original value don't exist for custom_fields.
			$fieldContent->original_value = md5(null);
			//$fieldContent->original_text = !is_null($originalText)?$originalText:"";

			$fieldContent->modified =  JFactory::getDate()->toSql();

			$fieldContent->modified_by = $user->id;
			$fieldContent->published= true;

			$fieldContent->store();

		}

		return true;
	}

	/**
	 * We need to prepare custom fields per plugin because #__fields_values doesn't have a primary key
	 *
	 * @param $context
	 * @param $item
	 * @param $field
	 */
	public function onCustomFieldsBeforePrepareField($context, $item, $field) {

		// We only work in frontend
		if (!JFactory::getApplication()->isClient('site')) {
			return;
		}

		list($component, $view) = explode('.', $context, 2);

		if (strpos($component, "com_")=== 0)	{
			$component_name = substr($component, 4);
		} else {
			$component_name = $component;
		}

		$fManager = FalangManager::getInstance();

		$content_element = $fManager::getInstance()->getContentElement($component_name);

		if (empty($content_element)){
			return;
		}

		$languageTag  = JFactory::getLanguage()->getTag();
		$id_lang = $fManager->getLanguageID($languageTag);

		$translations = FalangManager::getInstance()->getRawFieldTranslations($content_element->getTableName(),'com_fields',$item->{$content_element->getReferenceId()},$id_lang);

		if (empty($translations)) {
			return;
		}
		//supposed to be array
		$json_value = json_decode($translations,true);

		if (isset($json_value[$field->name])) {

			$field->valueUntranslated    = $field->value;
			$field->rawvalueUntranslated = $field->rawvalue;

			if ($field->type != 'repeatable'){
				$field->value                = $json_value[$field->name];
				$field->rawvalue             = $json_value[$field->name];
			} else {
				//repeatable value are json encoded
				$field->value                = json_encode($json_value[$field->name]);
				$field->rawvalue             = json_encode($json_value[$field->name]);
			}

		}

	}

	//@since 2.9.7
	public function setupAdvancedRouter()
	{
		//support advanced router
		jimport('joomla.application.component.helper');
		$params          = JComponentHelper::getParams('com_falang');
		$advanced_router = $params->get('advanced_router', 0);

		if (!isset($advanced_router) || $advanced_router == '0')
		{
			return;
		}
		$app = JFactory::getApplication();
		$router = $app->getRouter();

		//loop on folder to override each component router.
		jimport('joomla.filesystem.folder');
		$folders = JFolder::folders(JPATH_PLUGINS . '/system/falangdriver/routers/');
		if (count($folders))
		{
			foreach ($folders as $folder)
			{
				$router_file_path  = JPATH_PLUGINS . '/system/falangdriver/routers/' . $folder . '/router.php';
				if (file_exists($router_file_path))
				{
					require_once $router_file_path;
					$router_name = 'Falang'.str_replace('com_','',$folder).'Router';
					$crouter = new $router_name($app, $app->getMenu());
					$router->setComponentRouter($folder, $crouter);
				}
			}
		}
	}

	//@since 3.4.3
	public function setupCoreFileOverride(){
		//for front and back
		//override Front-end Language file for site and admin section. use for user language configuration
		JLoader::register('Joomla\CMS\Form\Field\FrontendlanguageField', dirname(__FILE__).'/overrides/libraries/src/Form/Field/FrontendlanguageField.php', true);

		//for back

		//for front

	}

}