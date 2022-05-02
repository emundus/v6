<?php
/**
 * Required System plugin if using Fabrik
 * Enables Fabrik to override some J classes
 *
 * @package     Joomla.Plugin
 * @subpackage  System
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;
use Fabrik\Helpers\Worker;

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');

/**
 * Joomla! Fabrik system
 *
 * @package     Joomla.Plugin
 * @subpackage  System
 * @since       3.0
 */
class PlgSystemFabrik extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param   object &$subject The object to observe
	 * @param   array  $config   An array that holds the plugin configuration
	 *
	 * @since    1.0
	 */
	public function __construct(&$subject, $config)
	{
		// Could be component was uninstalled but not the plugin
		if (!JFile::exists(JPATH_SITE . '/components/com_fabrik/fabrik.php'))
		{
			return;
		}

		/**
		 * Moved these from defines.php to here, to fix an issue with Kunena.  Kunena imports the J!
		 * JForm class in their system plugin, in the class constructor  So if we wait till onAfterInitialize
		 * to do this, we blow up.  So, import them here, and make sure the Fabrik plugin has a lower ordering
		 * than Kunena's.  We might want to set our default to -1.
		 */
		$app     = JFactory::getApplication();
		$version = new JVersion;
		$base    = 'components.com_fabrik.classes.' . str_replace('.', '', $version->RELEASE);

		// Test if Kunena is loaded - if so notify admins
		if (class_exists('KunenaAccess'))
		{
			$msg = 'Fabrik: Please ensure the Fabrik System plug-in is ordered before the Kunena system plugin';

			if ($app->isAdmin())
			{
				$app->enqueueMessage($msg, 'error');
			}
		}
		else
		{
			$loaded = true;

		    if (version_compare($version->RELEASE, '3.8', '<'))
            {
                $loaded = JLoader::import($base . '.field', JPATH_SITE . '/administrator', 'administrator.');
            }
			else
            {
                $loaded = JLoader::import($base . '.FormField', JPATH_SITE . '/administrator', 'administrator.');
            }

            if (!$loaded)
            {
	            if ($app->isAdmin() && $app->input->get('option') === 'com_fabrik')
	            {
		            $app->enqueueMessage('Fabrik cannot find files required for this version of Joomla.  <b>DO NOT</b> use the Fabrik backend admin until this is resolved.  Please visit <a href="http://fabrikar.com/forums">our web site</a> and check for announcements about this version', 'error');
	            }
            }
		}

        // The fabrikfeed doc type has been deprecated.  For backward compat, change it use standard J! feed instead
        if (version_compare($version->RELEASE, '3.8', '>=')) {
            if ($app->input->get('format') === 'fabrikfeed') {
                $app->input->set('format', 'feed');
            }
        }

		if (version_compare($version->RELEASE, '3.1', '<='))
		{
			JLoader::import($base . '.layout.layout', JPATH_SITE . '/administrator', 'administrator.');
			JLoader::import($base . '.layout.base', JPATH_SITE . '/administrator', 'administrator.');
			JLoader::import($base . '.layout.file', JPATH_SITE . '/administrator', 'administrator.');
			JLoader::import($base . '.layout.helper', JPATH_SITE . '/administrator', 'administrator.');
		}

		if (!file_exists(JPATH_LIBRARIES . '/fabrik/include.php'))
		{
			throw new Exception('PLG_FABRIK_SYSTEM_AUTOLOAD_MISSING');
		}

		require_once JPATH_LIBRARIES . '/fabrik/include.php';

		parent::__construct($subject, $config);

		jimport('joomla.filesystem.file');

		/**
		 * Added allow_user_defines to global config, defaulting to No, so even if a user_defines.php is present
		 * it won't get used unless this option is specifically set.  Did this because it looks like a user_defines.php
		 * managed to creep in to a release ZIP at some point, so some people unknowingly have one, which started causing
		 * issues after we added some more includes to defines.php.
		 */
		$fbConfig         = JComponentHelper::getParams('com_fabrik');
		$allowUserDefines = $fbConfig->get('allow_user_defines', '0') === '1';
		$p                = JPATH_SITE . '/plugins/system/fabrik/';
		$defines          = $allowUserDefines && JFile::exists($p . 'user_defines.php') ? $p . 'user_defines.php' : $p . 'defines.php';
		require_once $defines;

		$this->setBigSelects();
	}

	/**
	 * Get Page JavaScript from either session or cached .js file
	 *
	 * @return string
	 */
	public static function js()
	{
		/**
		 * We need to cache the requirejs stuff, as we insert it at the end of the document AFTER Joomla has written
		 * out the system cache, so loading a cached page will not have requirejs on the end.
		 */

		$config = JFactory::getConfig();
		$app = JFactory::getApplication();
		$script = '';
		$session = JFactory::getSession();

		/**
		 * Whenever we cache a view, we add the cache ID to this session variable, by calling
		 * FabrikHelperHTML::addToSessionCacheIds().  This gets cleared at the end of this function, so if there's
		 * anything in there, it was added on this page load.
		 *
		 * The theory is that if the view isn't cached, buildJs() will find everything it needs in our own session
		 * variables (fabrik.js.config, fabrik.js.scripts, etc).  If it is cached, the view won't have run, so we
		 * don't have our own session data, but we'll get it back from the cache.
		 */
		if ($session->has('fabrik.js.cacheids'))
		{
			/**
			 * NOTE that we use a different cache group name, 'fabrik_cacheids', NOT the default 'fabrik'.  This is
			 * because the main 'fabrik' cache could get cleared out from under us at any time, like if someone else
			 * submits a form, or anything else happens that causes Fabrik to do a $cache-clean().  This means that
			 * the 'fabrik_cacheids' cache could grow quite large, and will need to be cleaned occasionally.
			 */
			$cache = Worker::getCache(null, 'fabrik_cacheids');
			$cacheIds = $session->get('fabrik.js.cacheids', array());

			/**
			 * It's conceivable multiple views may have been rendered (modules, content plugins), so serialize them
			 * to get a unique ID for each combo.  In certain corner cases there may be an empty ID, so check and ignore.
			 */
			$cacheId = serialize($cacheIds);

			if (!empty($cacheId))
			{
				 // We got an ID, so ask the cache for it.
				$script = $cache->call(array('PlgSystemFabrik', 'buildJs'), $cacheId);
			}
			else
			{
				// No viable ID, so just build
				$script = self::buildJs();
			}
		}
		else
		{
			// nothing in the session cacheids, so just build.
			$script = self::buildJs();
		}

		// clear the session data
		self::clearJs();

		return $script;
	}

	/**
	 * Clear session js store
	 *
	 * @return  void
	 */
	public static function clearJs()
	{
		$session = JFactory::getSession();
		$session->clear('fabrik.js.scripts');
		$session->clear('fabrik.js.cacheids');
		$session->clear('fabrik.js.head.scripts');
		$session->clear('fabrik.js.config');
		$session->clear('fabrik.js.shim');
		$session->clear('fabrik.js.jlayouts');
	}

	/**
	 * Store head script in session js store,
	 * used by partial document type to exclude scripts already loaded, when building modal windows
	 *
	 * @return  void
	 */
	public static function storeHeadJs()
	{
		$session = JFactory::getSession();
		$doc = JFactory::getDocument();
		$app = JFactory::getApplication();
		$key = md5($app->input->server->get('REQUEST_URI', '', 'string'));

		if (!empty($key))
		{
			$key = 'fabrik.js.head.cache.' . $key;

			// if this is 'html', it's a main page load, so clear the cache for this page and start again
			if ($app->input->get('format', 'html') === 'html')
			{
				$session->clear($key);
			}

			$scripts = $doc->_scripts;
			$existing = $session->get($key);

			/**
			 * if we already have scripts for this page, merge the new ones.  For example, this might be an AJAX
			 * call loading an element, so we just want to add any new scripts to the list, not blow it away and replace
			 */
			if (!empty($existing))
			{
				$existing = json_decode($existing);
				$existing = ArrayHelper::fromObject($existing);
				$scripts = array_merge($scripts, $existing);
			}

			$scripts = json_encode($scripts);
			$session->set($key, $scripts);
		}
	}

	/**
	 * Build Page <script> tag for insertion into DOM
	 *
	 * @return string
	 */
	public static function buildJs()
	{
		$session = JFactory::getSession();
		$config  = (array) $session->get('fabrik.js.config', array());
		$config  = implode("\n", $config);

		$js = (array) $session->get('fabrik.js.scripts', array());
		$js = implode("\n", $js);

		$jLayouts = (array) $session->get('fabrik.js.jlayouts', array());
		$jLayouts = json_encode(ArrayHelper::toObject($jLayouts));
		$js       = str_replace('%%jLayouts%%', $jLayouts, $js);

		if ($config . $js !== '')
		{
			/*
			 * Load requirejs into a DOM generated <script> tag - then load require.js code.
			 * Avoids issues with previous implementation where we were loading requirejs at the end of the head and then
			 * loading the code at the bottom of the page.
			 * For example this previous method broke with the codemirror editor which first
			 * tests if its inside requirejs (false) then loads scripts via <script> node creation. By the time the secondary
			 * scripts were loaded, Fabrik had loaded requires js, and conflicts occurred.
			 */
			$jsAssetBaseURI = FabrikHelperHTML::getJSAssetBaseURI();
			$rjs            = $jsAssetBaseURI . 'media/com_fabrik/js/lib/require/require.js';
			$script         = '<script>
            setTimeout(function(){
            jQuery.ajaxSetup({
  cache: true
});
				 jQuery.getScript( "' . $rjs . '", function() {
				' . "\n" . $config . "\n" . $js . "\n" . '
			});
			 }, 600);
			</script>
      ';
		}
		else
		{
			$script = '';
		}

		return $script;
	}

	/**
	 * Insert require.js config an app ini script into body.
	 *
	 * @return  void
	 */
	public function onAfterRender()
	{
		// Could be component was uninstalled but not the plugin
		if (!class_exists('FabrikString'))
		{
			return;
		}

		$formats = array (
			'html',
			'partial'
		);

		$app    = JFactory::getApplication();

		/*
		if (!in_array($app->input->get('format', 'html'), $formats))
		{
			return;
		}
		*/

		$script = self::js();
		//self::clearJs();
		self::storeHeadJs();

		$version           = new JVersion;
		$lessThanThreeFour = version_compare($version->RELEASE, '3.4', '<');
		$content           = $lessThanThreeFour ? JResponse::getBody() : $app->getBody();

		if (!stristr($content, '</body>'))
		{
			$content .= $script;
		}
		else
		{
			$content = FabrikString::replaceLast('</body>', $script . '</body>', $content);
		}

		$lessThanThreeFour ? JResponse::setBody($content) : $app->setBody($content);
	}

	/**
	 * Need to call this here otherwise you get class exists error
	 *
	 * @since   3.0
	 *
	 * @return  void
	 */
	public function onAfterInitialise()
	{
		//jimport('joomla.filesystem.file');

		/**
		 * Added allow_user_defines to global config, defaulting to No, so even if a user_defines.php is present
		 * it won't get used unless this option is specifically set.  Did this because it looks like a user_defines.php
		 * managed to creep in to a release ZIP at some point, so some people unknowingly have one, which started causing
		 * issues after we added some more includes to defines.php.
		 */
		/*
		$fbConfig         = JComponentHelper::getParams('com_fabrik');
		$allowUserDefines = $fbConfig->get('allow_user_defines', '0') === '1';
		$p                = JPATH_SITE . '/plugins/system/fabrik/';
		$defines          = $allowUserDefines && JFile::exists($p . 'user_defines.php') ? $p . 'user_defines.php' : $p . 'defines.php';
		require_once $defines;

		$this->setBigSelects();
		*/
	}

    /**
     * If a command line like finder_indexer.php is run, it won't call onAfterInitialise, and will then run content
     * plugins, and ours will bang out because "Fabrik system plugin has not been run".  So onStartIndex, initialize
     * our plugin.
     *
     * @since   3.8
     *
     * @return  void
     */
	public function onStartIndex()
    {
        $this->onAfterInitialise();
    }

	/**
	 * From Global configuration setting, set big select for main J database
	 *
	 * @since    3.0.7
	 *
	 * @return  void
	 */
	protected function setBigSelects()
	{
		if (class_exists('FabrikWorker'))
		{
			$db = JFactory::getDbo();
			FabrikWorker::bigSelects($db);
		}
	}

	/**
	 * Fabrik Search method
	 *
	 * The sql must return the following fields that are
	 * used in a common display routine: href, title, section, created, text,
	 * browsernav
	 *
	 * @param   string    $text     Target search string
	 * @param   JRegistry $params   Search plugin params
	 * @param   string    $phrase   Matching option, exact|any|all
	 * @param   string    $ordering Option, newest|oldest|popular|alpha|category
	 *
	 * @return  array
	 */
	public static function onDoContentSearch($text, $params, $phrase = '', $ordering = '')
	{
		$app      = JFactory::getApplication();
		$package  = $app->getUserState('com_fabrik.package', 'fabrik');
		$fbConfig = JComponentHelper::getParams('com_fabrik');

		if (defined('COM_FABRIK_SEARCH_RUN'))
		{
			return;
		}

		$input = $app->input;
		define('COM_FABRIK_SEARCH_RUN', true);
		JModelLegacy::addIncludePath(COM_FABRIK_FRONTEND . '/models', 'FabrikFEModel');

		$db = FabrikWorker::getDbo(true);

		require_once JPATH_SITE . '/components/com_content/helpers/route.php';

		// Load plugin params info
		//$limit = $params->def('search_limit', 50);
		$limit = $params->get('search_limit', 50);
		$text  = trim($text);

		if ($text == '')
		{
			return array();
		}

		switch ($ordering)
		{
			case 'oldest':
				$order = 'a.created ASC';
				break;

			case 'popular':
				$order = 'a.hits DESC';
				break;

			case 'alpha':
				$order = 'a.title ASC';
				break;

			case 'category':
				$order  = 'b.title ASC, a.title ASC';
				$morder = 'a.title ASC';
				break;

			case 'newest':
			default:
				$order = 'a.created DESC';
				break;
		}

		// Set heading prefix
		$headingPrefix = $params->get('include_list_title', true);

		// Get all tables with search on
		$query = $db->getQuery(true);
		$query->select('id')->from('#__{package}_lists')->where('published = 1');
		$db->setQuery($query);

		$list    = array();
		$ids     = $db->loadColumn();
		$section = $params->get('search_section_heading');
		$urls    = array();

		// $$$ rob remove previous search results?
		$input->set('resetfilters', 1);

		// Ensure search doesn't go over memory limits
		$memory    = FabrikWorker::getMemoryLimit();
		$usage     = array();
		$memSafety = 0;

		$listModel = JModelLegacy::getInstance('list', 'FabrikFEModel');
		$app       = JFactory::getApplication();

		foreach ($ids as $id)
		{
			// Re-ini the list model (was using reset() but that was flaky)
			$listModel = JModelLegacy::getInstance('list', 'FabrikFEModel');

			// $$$ geros - http://fabrikar.com/forums/showthread.php?t=21134&page=2
			$key = 'com_' . $package . '.list' . $id . '.filter.searchall';
			$app->setUserState($key, null);
			$usage[] = memory_get_usage();

			if (count($usage) > 2)
			{
				$diff = $usage[count($usage) - 1] - $usage[count($usage) - 2];

				if ($diff + $usage[count($usage) - 1] > $memory - $memSafety)
				{
					$msg = FText::_('PLG_FABRIK_SYSTEM_SEARCH_MEMORY_LIMIT');
					$app->enqueueMessage($msg);
					break;
				}
			}

			// $$$rob set this to current table
			// Otherwise the fabrik_list_filter_all var is not used
			$input->set('listid', $id);

			$listModel->setId($id);
			$searchFields = $listModel->getSearchAllFields();

			if (empty($searchFields))
			{
				continue;
			}

			$filterModel = $listModel->getFilterModel();
			$requestKey  = $filterModel->getSearchAllRequestKey();

			// Set the request variable that fabrik uses to search all records
			$input->set($requestKey, $text, 'post');

			$table  = $listModel->getTable();
			$params = $listModel->getParams();

			/*
			 * $$$ hugh - added 4/12/2015, if user doesn't have view list and view details, no searchee
			 */
			if (!$listModel->canView() || !$listModel->canViewDetails())
			{
				continue;
			}

			// Treat J! search as boolean, we check for com_search mode in list filter model getAdvancedSearchMode()
			$params->set('search-mode-advanced', '1');

			// The table shouldn't be included in the search results or we have reached the max number of records to show.
			if (!$params->get('search_use') || $limit <= 0)
			{
				continue;
			}

			// Set the table search mode to OR - this will search ALL fields with the search term
			$params->set('search-mode', 'OR');

			/**
			 * Disable pagination limits.
			 * For now, use filter_list_max limit, just to prevent totally unconstrained queries,
			 * might add seperate config setting for global search max at some point.
			 */
			$listModel->setLimits(0, $fbConfig->get('filter_list_max', 100));

			$allRows      = $listModel->getData();
			$elementModel = $listModel->getFormModel()->getElement($params->get('search_description', $table->label), true);
			$descName     = is_object($elementModel) ? $elementModel->getFullName() : '';

			$elementModel = $listModel->getFormModel()->getElement($params->get('search_title', 0), true);
			$title        = is_object($elementModel) ? $elementModel->getFullName() : '';

			/**
			 * $$$ hugh - added date element ... always use raw, as anything that isn't in
			 * standard MySQL format will cause a fatal error in J!'s search code when it does the JDate create
			 */
			$elementModel = $listModel->getFormModel()->getElement($params->get('search_date', 0), true);
			$dateElement  = is_object($elementModel) ? $elementModel->getFullName() : '';

			if (!empty($dateElement))
			{
				$dateElement .= '_raw';
			}

			$aAllowedList = array();
			$pk           = $table->db_primary_key;

			foreach ($allRows as $group)
			{
				foreach ($group as $oData)
				{
					$pkval = $oData->__pk_val;

					if ($app->isAdmin() || $params->get('search_link_type') === 'form')
					{
						$href = $oData->fabrik_edit_url;
					}
					else
					{
						$href = $oData->fabrik_view_url;
					}

					if (!in_array($href, $urls))
					{
						$limit--;
						if ($limit < 0)
						{
							continue;
						}
						$urls[] = $href;
						$o      = new stdClass;

						if (isset($oData->$title))
						{
							$o->title = $headingPrefix ? $table->label . ' : ' . $oData->$title : $oData->$title;
						}
						else
						{
							$o->title = $table->label;
						}

						$o->_pkey   = $table->db_primary_key;
						$o->section = $section;
						$o->href    = $href;

						// Need to make sure it's a valid date in MySQL format, otherwise J!'s code will pitch a fatal error
						if (isset($oData->$dateElement) && FabrikString::isMySQLDate($oData->$dateElement))
						{
							$o->created = $oData->$dateElement;
						}
						else
						{
							$o->created = '';
						}

						$o->browsernav = 2;

						if (isset($oData->$descName))
						{
							$o->text = $oData->$descName;
						}
						else
						{
							$o->text = '';
						}

						$o->title       = strip_tags($o->title);
						$o->title       = html_entity_decode($o->title);
						$aAllowedList[] = $o;
					}
				}

				$list[] = $aAllowedList;
			}
		}

		$allList = array();

		foreach ($list as $li)
		{
			if (is_array($li) && !empty($li))
			{
				$allList = array_merge($allList, $li);
			}
		}
		if ($limit < 0)
		{
			$language = JFactory::getLanguage();
			$language->load('plg_system_fabrik', JPATH_SITE . '/plugins/system/fabrik');
			$msg = FText::_('PLG_FABRIK_SYSTEM_SEARCH_LIMIT');
			$app->enqueueMessage($msg);
		}

		return $allList;
	}

	/**
	 * If a form or details view has set a canonical link - removed any J created links
	 *
	 * @throws Exception
	 */
	public function onAfterDispatch()
	{
		$doc     = JFactory::getDocument();
		$session = JFactory::getSession();
		$package = JFactory::getApplication()->getUserState('com_fabrik.package', 'fabrik');

		if (isset($doc->_links) && $session->get('fabrik.clearCanonical'))
		{
			$session->clear('fabrik.clearCanonical');

			foreach ($doc->_links as $k => $link)
			{
				if ($link['relation'] == 'canonical' && !strstr($k, $package))
				{
					unset($doc->_links[$k]);
					break;
				}
			}
		}
	}

	/**
	 * Global config has been saved.
	 * Check the product key and if it exists create an update site entry
	 * Update server XML manifest generated from update/premium.php
	 *
	 * @param string          $option
	 * @param JTableExtension $data
	 */
	function onExtensionAfterSave($option, $data)
	{
		if ($option !== 'com_config.component')
		{
			return;
		}

		if ($data->get('name') !== 'com_fabrik')
		{
			return;
		}

		$props      = $data->getProperties();
		$params     = new JRegistry($props['params']);
		$productKey = $params->get('fabrik_product_key', '');

		if ($productKey === '')
		{
			return;
		}

		$table = JTable::getInstance('Updatesite');
		$table->load(array('name' => 'Fabrik - Premium'));
		$table->save(array(
			'type' => 'collection',
			'name' => 'Fabrik - Premium',
			'enabled' => 1,
			'location' => 'http://localhost:81/fabrik31x/public_html/update/premium.php?productKey=' . $productKey
		));
	}
}
