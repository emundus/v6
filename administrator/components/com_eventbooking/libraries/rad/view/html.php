<?php
/**
 * @package     RAD
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2015 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Joomla CMS Base View Html Class
 *
 * @package      RAD
 * @subpackage   View
 * @since        2.0
 */
class RADViewHtml extends RADView
{
	/**
	 * The view layout.
	 *
	 * @var string
	 */
	protected $layout = 'default';

	/**
	 * The paths queue.
	 *
	 * @var array
	 */
	protected $paths = [];

	/**
	 * Default Itemid variable value for the links in the view
	 *
	 * @var int
	 */
	public $Itemid;

	/**
	 * The input object passed from the controller while creating the view
	 *
	 * @var RADInput
	 */

	protected $input;

	/**
	 * This is a front-end or back-end view.
	 * We need this field to determine whether we need to addToolbar or build the filter
	 *
	 * @var boolean
	 */
	protected $isAdminView = false;

	/**
	 * Options to allow hide default toolbar buttons from backend view
	 *
	 * @var array
	 */
	protected $hideButtons = [];

	/**
	 * The device type is accessing to the view, it can be desktop, tablet or mobile
	 *
	 * @var string
	 */
	protected $deviceType = 'desktop';

	/**
	 * The menu parameter of the view, for frontend
	 *
	 * @var \Joomla\Registry\Registry
	 */
	protected $params;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   array  $config  A named configuration array for object construction
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);

		if (isset($config['layout']))
		{
			$this->layout = $config['layout'];
		}

		if (isset($config['paths']))
		{
			$this->paths = $config['paths'];
		}
		else
		{
			$this->paths = [];
		}

		if (!empty($config['is_admin_view']))
		{
			$this->isAdminView = $config['is_admin_view'];
		}

		if (!empty($config['Itemid']))
		{
			$this->Itemid = $config['Itemid'];
		}

		if (isset($config['input']))
		{
			$this->input = $config['input'];
		}

		if (isset($config['hide_buttons']))
		{
			$this->hideButtons = $config['hide_buttons'];
		}

		$this->deviceType = EventbookingHelper::getDeviceType();
	}

	/**
	 * Method to display the view
	 */
	public function display()
	{
		$this->prepareView();

		echo $this->render();
	}

	/**
	 * Give view a chance to prepare view data. Empty by default, child class can override this method
	 * to prepare data for view if needed
	 */
	protected function prepareView()
	{

	}

	/**
	 * Magic toString method that is a proxy for the render method.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return string The escaped output.
	 */
	public function escape($output)
	{
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Method to get the view layout.
	 *
	 * @return string The layout name.
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Method to get the layout path.
	 *
	 * @param   string  $layout  The layout name.
	 *
	 * @return mixed The layout file name if found, false otherwise.
	 */
	public function getPath($layout)
	{
		$layouts = EventbookingHelperHtml::getPossibleLayouts($layout);

		if ($this->deviceType !== 'desktop')
		{
			$deviceLayouts = [];

			foreach ($layouts as $layout)
			{
				$deviceLayouts[] = $layout . '.' . $this->deviceType;
			}

			$layouts = array_merge($deviceLayouts, $layouts);
		}

		foreach ($layouts as $layout)
		{
			// Get the layout file name.
			$file = JPath::clean($layout . '.php');

			// Find the layout file path.
			$path = JPath::find($this->paths, $file);

			if ($path !== false)
			{
				return $path;
			}
		}

		return false;
	}

	/**
	 * Method to get the view paths.
	 *
	 * @return array The paths queue.
	 */
	public function getPaths()
	{
		return $this->paths;
	}

	/**
	 * Method to render the view.
	 *
	 * @return string The rendered view.
	 *
	 * @throws RuntimeException
	 */
	public function render()
	{
		// Get the layout path.
		$path = $this->getPath($this->getLayout());

		// Check if the layout path was found.
		if (!$path)
		{
			throw new RuntimeException('Layout Path Not Found');
		}

		// Start an output buffer.
		ob_start();

		// Load the layout.
		include $path;

		// Get the layout contents.
		return ob_get_clean();
	}

	/**
	 * Load sub-template for the current layout
	 *
	 * @param   string  $template
	 *
	 * @return string The output of sub-layout
	 * @throws RuntimeException
	 *
	 */
	public function loadTemplate($template, $data = [])
	{
		// Get the layout path.
		$path = $this->getPath($this->getLayout() . '_' . $template);

		// Check if the layout path was found.
		if (!$path)
		{
			throw new RuntimeException(sprintf('Template %s Not Found', $template));
		}

		extract($data);
		// Start an output buffer.
		ob_start();
		// Load the layout.
		include $path;

		// Get the layout contents.
		return ob_get_clean();
	}

	/**
	 * Load common layout for the view
	 *
	 * @param   string  $layout
	 * @param   array   $data
	 *
	 * @return string The output of common layout
	 *
	 * @throws RuntimeException
	 */
	public function loadCommonLayout($layout, $data = [])
	{
		$app      = Factory::getApplication();
		$theme    = EventbookingHelper::getDefaultTheme();
		$layout   = str_replace('/tmpl', '', $layout);
		$filename = basename($layout);
		$filePath = substr($layout, 0, strlen($layout) - strlen($filename));
		$layouts  = EventbookingHelperHtml::getPossibleLayouts($filename);

		if ($this->deviceType !== 'desktop')
		{
			$deviceLayouts = [];

			foreach ($layouts as $layout)
			{
				$deviceLayouts[] = $layout . '.' . $this->deviceType;
			}

			$layouts = array_merge($deviceLayouts, $layouts);
		}

		// Build paths array to get layout from
		$paths   = [];
		$paths[] = JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_eventbooking';
		$paths[] = JPATH_ROOT . '/components/com_eventbooking/themes/' . $theme->name;

		if ($theme->name != 'default')
		{
			$paths[] = JPATH_ROOT . '/components/com_eventbooking/themes/default';
		}

		$foundLayout = '';

		foreach ($layouts as $layout)
		{
			// Get the layout file name.
			if ($filePath)
			{
				$layout = $filePath . $layout;
			}

			foreach ($paths as $path)
			{
				if (file_exists($path . '/' . $layout))
				{
					$foundLayout = $path . '/' . $layout;

					break 2;
				}
			}
		}

		if (empty($foundLayout))
		{
			throw new RuntimeException(Text::sprintf('The given common layout %s does not exist', $layout));
		}

		// Start an output buffer.
		ob_start();
		extract($data);

		// Load the layout.
		include $foundLayout;

		// Get the layout contents.
		return ob_get_clean();
	}

	/**
	 * Method to set the view layout.
	 *
	 * @param   string  $layout  The layout name.
	 *
	 * @return RADViewHtml Method supports chaining.
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;

		return $this;
	}

	/**
	 * Method to set the view paths.
	 *
	 * @param   array  $paths  The paths queue.
	 *
	 * @return RADViewHtml Method supports chaining.
	 */
	public function setPaths($paths)
	{
		$this->paths = $paths;

		return $this;
	}

	/**
	 * Get page params of the given view
	 *
	 * @param   array  $views
	 * @param   array  $query
	 *
	 * @return \Joomla\Registry\Registry
	 */
	protected function getParams($views = [], $query = [])
	{
		// Default to current view
		if (empty($views))
		{
			$views = [$this->getName()];
		}

		if ($this->input->getInt('hmvc_call') && $this->input->getInt('Itemid'))
		{
			$active = Factory::getApplication()->getMenu()->getItem($this->input->getInt('Itemid'));
		}

		if (empty($active))
		{
			$active = Factory::getApplication()->getMenu()->getActive();
		}

		if ($active && isset($active->query['view']) && in_array($active->query['view'], $views))
		{
			$params = $active->getParams();

			if ($active->query['view'] != $this->getName() || array_diff($query, $active->query))
			{
				$params->set('page_title', '');
				$params->set('page_heading', '');
				$params->set('show_page_heading', true);
			}

			return $params;
		}

		return new \Joomla\Registry\Registry;
	}

	/**
	 * Set document meta data
	 *
	 * @return void
	 */
	protected function setDocumentMetadata()
	{
		// Do not change document meta data on an HMVC call
		if ($this->input->getInt('hmvc_call'))
		{
			return;
		}

		/* @var JDocumentHtml $document */
		$document         = Factory::getDocument();
		$siteNamePosition = Factory::getApplication()->get('sitename_pagetitles');
		$siteName         = Factory::getApplication()->get('sitename');

		if ($pageTitle = $this->params->get('page_title'))
		{
			if ($siteNamePosition == 0)
			{
				$document->setTitle($pageTitle);
			}
			elseif ($siteNamePosition == 1)
			{
				$document->setTitle($siteName . ' - ' . $pageTitle);
			}
			else
			{
				$document->setTitle($pageTitle . ' - ' . $siteName);
			}
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('menu-meta_description'))
		{
			$document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('robots'))
		{
			$document->setMetaData('robots', $this->params->get('robots'));
		}
	}

	/**
	 * Method to request user login before they can access to this page
	 *
	 * @param   string  $msg  The redirect message
	 *
	 * @throws Exception
	 */
	protected function requestLogin($msg = 'EB_PLEASE_LOGIN')
	{
		if (Factory::getUser()->get('id'))
		{
			return;
		}

		$app    = Factory::getApplication();
		$active = $app->getMenu()->getActive();

		$option = isset($active->query['option']) ? $active->query['option'] : '';
		$view   = isset($active->query['view']) ? $active->query['view'] : '';

		if ($option == 'com_eventbooking' && $view == strtolower($this->getName()))
		{
			$returnUrl = 'index.php?Itemid=' . $active->id;
		}
		else
		{
			$returnUrl = Uri::getInstance()->toString();
		}

		$url = Route::_('index.php?option=com_users&view=login&return=' . base64_encode($returnUrl), false);

		$app->enqueueMessage(Text::_($msg));
		$app->redirect($url);
	}

	/**
	 * Add feed links to current view
	 *
	 * @return void
	 */
	protected function addFeedLinks()
	{
		/* @var JDocumentHtml $document */
		$document = Factory::getDocument();
		$link     = '&format=feed&limitstart=';
		$attribs  = ['type' => 'application/rss+xml', 'title' => 'RSS 2.0'];
		$document->addHeadLink(Route::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = ['type' => 'application/atom+xml', 'title' => 'Atom 1.0'];
		$document->addHeadLink(Route::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
	}

	/**
	 * Set active menu item used for links generated within the view
	 *
	 * @return void
	 */
	protected function findAndSetActiveMenuItem()
	{
		// Attempt to find the correct menu item for the view if required
		$active = Factory::getApplication()->getMenu()->getActive();

		if ($active && isset($active->query['view']))
		{
			$view = $active->query['view'];
		}
		else
		{
			$view = '';
		}

		if ($view != strtolower($this->getName()))
		{
			$menuId = EventbookingHelperRoute::findView('calendar');

			if ($menuId)
			{
				$this->Itemid = $menuId;
			}
		}
	}
}
