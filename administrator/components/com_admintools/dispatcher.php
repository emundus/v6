<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsDispatcher extends F0FDispatcher
{
	public function onBeforeDispatch()
	{
		$result = parent::onBeforeDispatch();

		if ($result)
		{
			// Clear com_modules and com_plugins cache (needed when we alter module/plugin state)
			$core_components = array('com_modules', 'com_plugins');

			foreach ($core_components as $component)
			{
				try
				{
					$cache = JFactory::getCache($component);
					$cache->clean();
				}
				catch (Exception $e)
				{
					// suck it up
				}
			}

			// Merge the language overrides
			$paths = array(JPATH_ROOT, JPATH_ADMINISTRATOR);
			$jlang = JFactory::getLanguage();
			$jlang->load($this->component, $paths[0], 'en-GB', true);
			$jlang->load($this->component, $paths[0], null, true);
			$jlang->load($this->component, $paths[1], 'en-GB', true);
			$jlang->load($this->component, $paths[1], null, true);

			$jlang->load($this->component . '.override', $paths[0], 'en-GB', true);
			$jlang->load($this->component . '.override', $paths[0], null, true);
			$jlang->load($this->component . '.override', $paths[1], 'en-GB', true);
			$jlang->load($this->component . '.override', $paths[1], null, true);

			// Load Akeeba Strapper
			if (!defined('ADMINTOOLSMEDIATAG'))
			{
				$staticFilesVersioningTag = md5(ADMINTOOLS_VERSION . ADMINTOOLS_DATE);
				define('ADMINTOOLSMEDIATAG', $staticFilesVersioningTag);
			}
			include_once JPATH_ROOT . '/media/akeeba_strapper/strapper.php';
			AkeebaStrapper::$tag = ADMINTOOLSMEDIATAG;
			AkeebaStrapper::bootstrap();
			AkeebaStrapper::jQueryUI();
			AkeebaStrapper::addCSSfile('admin://components/com_admintools/media/css/backend.css');

			// Work around non-transparent proxy and reverse proxy IP issues
			if (class_exists('F0FUtilsIp', true))
			{
				F0FUtilsIp::workaroundIPIssues();
			}

			// Control Check
			$view = F0FInflector::singularize($this->input->getCmd('view', $this->defaultView));

			if ($view == 'liveupdate')
			{
				$url = JUri::base() . 'index.php?option=com_admintools';
				JFactory::getApplication()->redirect($url);

				return;
			}

			// ========== Master PW check ==========
			/** @var AdmintoolsModelMasterpw $model */
			$model = F0FModel::getAnInstance('Masterpw', 'AdmintoolsModel');
			if (!$model->accessAllowed($view))
			{
				$url = ($view == 'cpanel') ? 'index.php' : 'index.php?option=com_admintools&view=cpanel';
				JFactory::getApplication()->redirect($url, JText::_('ATOOLS_ERR_NOTAUTHORIZED'), 'error');

				return;
			}
		}

		return $result;
	}
}