<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Site\Dispatcher;

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Dispatcher\Dispatcher as AdminDispatcher;
use Akeeba\AdminTools\Admin\Model\ConfigureWAF;
use FOF40\Container\Container;
use FOF40\IP\IPHelper as Ip;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use RuntimeException;

class Dispatcher extends AdminDispatcher
{
	/** @var   string  The name of the default view, in case none is specified */
	public $defaultView = 'INVALID';

	/**
	 * Maps view name aliases to actual views. The format is 'alias' => 'RealView'.
	 *
	 * @var  array
	 */
	protected $viewNameAliases = [];

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->viewNameAliases = [
			'block'       => 'Block',
			'filescanner' => 'FileScanner',
		];
	}

	public function onBeforeDispatch()
	{
		@require_once JPATH_ADMINISTRATOR . '/components/com_admintools/version.php';

		// Not the Pro version, nothing for you to do in the front end of the component
		if (!defined("ADMINTOOLS_PRO") || !ADMINTOOLS_PRO)
		{
			throw new RuntimeException(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
		}

		$this->onBeforeDispatchViewAliases();

		// Load the FOF language
		$lang = $this->container->platform->getLanguage();
		$lang->load('lib_fof40', JPATH_SITE, 'en-GB', true, true);
		$lang->load('lib_fof40', JPATH_SITE, null, true, false);

		// Load the version file
		@include_once($this->container->backEndPath . '/version.php');

		if (!defined('ADMINTOOLS_VERSION'))
		{
			define('ADMINTOOLS_VERSION', 'dev');
			define('ADMINTOOLS_DATE', date('Y-m-d'));
		}

		// Work around non-transparent proxy and reverse proxy IP issues when the feature is enabled and the plugin
		// has not done the same already.
		/** @var ConfigureWAF $wafModel */
		$wafModel  = $this->container->factory->model('ConfigureWAF')->tmpInstance();
		$wafConfig = $wafModel->getConfig();

		if ($wafConfig['ipworkarounds'] && !isset($_SERVER['FOF_REMOTE_ADDR']))
		{
			Ip::workaroundIPIssues();
		}

		// Am I in the Block view?
		$inBlockView = $this->container->platform->getSessionVar('block', false, 'com_admintools');

		if ($inBlockView)
		{
			$this->container->platform->setSessionVar('block', false, 'com_admintools');

			// We have to go through JFactory to alter the application's input!
			$input = Factory::getApplication()->input;
			$input->set('option', 'com_admintools');
			$input->set('view', 'Blocks');
			$input->set('task', 'browse');
			$input->set('format', 'html');
			$input->set('layout', null);
			$input->set('tmpl', null);

			return;
		}

		// Am I in the FileScanner view?
		$view = $this->input->getCmd('view', $this->defaultView);
		$task = $this->input->getCmd('task', 'browse');
		$key  = $this->input->get('key', '', 'raw');

		$validKey             = $this->container->params->get('frontend_secret_word', '');
		$isFileScannerEnabled = $this->container->params->get('frontend_enable', 0) != 0;
		$inScannerView        = ($view == 'FileScanner') && ($format = 'raw') && $isFileScannerEnabled && !empty($validKey) && ($validKey == $key);

		if ($inScannerView)
		{
			// We have to go through JFactory to alter the application's input!
			$input = Factory::getApplication()->input;
			$input->set('view', 'FileScanner');
			$input->set('task', $task);
			$input->set('format', 'raw');
			$input->set('layout', null);
			$input->set('tmpl', null);

			return;
		}

		// In all other cases pretend we're not here
		throw new RuntimeException(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
	}
}
