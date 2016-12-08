<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Site\Dispatcher;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Dispatcher\Dispatcher as AdminDispatcher;
use Akeeba\AdminTools\Admin\Model\ConfigureWAF;
use FOF30\Container\Container;
use FOF30\Dispatcher\Mixin\ViewAliases;
use FOF30\Utils\Ip;
use JFactory;
use JText;
use JUri;
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
	protected $viewNameAliases = array();

	/**
	 * If set to true, any GET request to the alias view will result in an HTTP 301 permanent redirection to the real
	 * view name.
	 *
	 * This does NOT apply to POST, PUT, DELETE etc URLs. When you submit form data you cannot have a redirection. The
	 * browser will _typically_ not resend the submitted data.
	 *
	 * @var  bool
	 */
	protected $permanentAliasRedirectionOnGET = false;

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
			throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
		}

		$this->onBeforeDispatchViewAliases();

		// Load the FOF language
		$lang = $this->container->platform->getLanguage();
		$lang->load('lib_fof30', JPATH_SITE, 'en-GB', true, true);
		$lang->load('lib_fof30', JPATH_SITE, null, true, false);

		// Laod the version file
		@include_once($this->container->backEndPath . '/version.php');

		if (!defined('ADMINTOOLS_VERSION'))
		{
			define('ADMINTOOLS_VERSION', 'dev');
			define('ADMINTOOLS_DATE', date('Y-m-d'));
		}

		// Work around non-transparent proxy and reverse proxy IP issues when the feature is enabled and the plugin
		// has not done the same already.
		/** @var ConfigureWAF $wafModel */
		$wafModel = $this->container->factory->model('ConfigureWAF')->tmpInstance();
		$wafConfig = $wafModel->getConfig();

		if ($wafConfig['ipworkarounds'] && !isset($_SERVER['FOF_REMOTE_ADDR']))
		{
			Ip::workaroundIPIssues();
		}

		// Am I in the Block view?
		$inBlockView = $this->container->session->get('block', false, 'com_admintools');

		if ($inBlockView)
		{
			$this->container->session->set('block', false, 'com_admintools');

			// We have to go through JFactory to alter the application's input!
			$input = JFactory::getApplication()->input;
			$input->set('option', 'com_admintools');
			$input->set('view', 'Blocks');
			$input->set('task', 'browse');

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
			$input = JFactory::getApplication()->input;
			$input->set('view', 'FileScanner');
			$input->set('task', $task);

			return;
		}

		// In all other cases pretend we're not here
		throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
	}
}