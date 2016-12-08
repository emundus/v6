<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Controller\Mixin\PredefinedTaskList;
use Akeeba\AdminTools\Admin\Model\MasterPassword;
use Akeeba\AdminTools\Admin\Model\Updates;
use Akeeba\Engine\Util\RandomValue;
use AkeebaGeoipProvider;
use FOF30\Container\Container;
use FOF30\Controller\Controller;
use JFactory;
use JText;
use JUri;

class ControlPanel extends Controller
{
	use PredefinedTaskList;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = [
			'browse', 'login', 'updategeoip', 'updateinfo', 'selfblocked', 'unblockme', 'applydlid', 'resetSecretWord', 'forceUpdateDb'
		];
	}

	public function onBeforeBrowse()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\ControlPanel $model */
		$model = $this->getModel();

		// Upgrade the database schema if necessary
		try
		{
			$model->checkAndFixDatabase();
		}
		catch (\RuntimeException $e)
		{
			// The update is stuck. We will display a warning in the Control Panel
		}

		// Update the magic parameters
		$model->updateMagicParameters();

		// Refresh the update site definitions if required. Also takes into account any change of the Download ID
		// in the Options.
		/** @var Updates $updateModel */
		$updateModel = $this->container->factory->model('Updates');
		$updateModel->refreshUpdateSite();

		// Reorder the Admin Tools plugin if necessary
		if ($this->container->params->get('reorderplugin', 1))
		{
			$model->reorderPlugin();
		}
	}

	public function login()
	{
		/** @var MasterPassword $model */
		$model = $this->getModel('MasterPassword');
		$password = $this->input->get('userpw', '', 'raw');
		$model->setUserPassword($password);

		$url = 'index.php?option=com_admintools';
		$this->setRedirect($url);
	}

	public function updategeoip()
	{
		$this->csrfProtection();

		// Load the GeoIP library if it's not already loaded
		if (!class_exists('AkeebaGeoipProvider'))
		{
			if (@file_exists(JPATH_PLUGINS . '/system/akgeoip/lib/akgeoip.php'))
			{
				if (@include_once JPATH_PLUGINS . '/system/akgeoip/lib/vendor/autoload.php')
				{
					@include_once JPATH_PLUGINS . '/system/akgeoip/lib/akgeoip.php';
				}
			}
		}

		$geoip  = new AkeebaGeoipProvider();
		$result = $geoip->updateDatabase();

		$url = 'index.php?option=com_admintools';

		$customRedirect = $this->input->getBase64('returnurl', '');
		$customRedirect = empty($customRedirect) ? '' : base64_decode($customRedirect);

		if ($customRedirect && JUri::isInternal($customRedirect))
		{
			$url = $customRedirect;
		}

		if ($result === true)
		{
			$msg = JText::_('COM_ADMINTOOLS_MSG_GEOGRAPHICBLOCKING_DOWNLOADEDGEOIPDATABASE');
			$this->setRedirect($url, $msg);
		}
		else
		{
			$this->setRedirect($url, $result, 'error');
		}
	}

	public function updateinfo()
	{
		/** @var Updates $updateModel */
		$updateModel = $this->container->factory->model('Updates')->tmpInstance();
		$updateInfo  = (object)$updateModel->getUpdates();

		$result = '';

		if ($updateInfo->hasUpdate)
		{
			$strings = array(
				'header'  => JText::sprintf('COM_ADMINTOOLS_MSG_CONTROLPANEL_UPDATEFOUND', $updateInfo->version),
				'button'  => JText::sprintf('COM_ADMINTOOLS_MSG_CONTROLPANEL_UPDATENOW', $updateInfo->version),
				'infourl' => $updateInfo->infoURL,
				'infolbl' => JText::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_MOREINFO'),
			);

			$result = <<<ENDRESULT
	<div class="alert alert-warning">
		<h3>
			<span class="icon icon-exclamation-sign glyphicon glyphicon-exclamation-sign"></span>
			{$strings['header']}
		</h3>
		<p>
			<a href="index.php?option=com_installer&view=Update" class="btn btn-primary">
				{$strings['button']}
			</a>
			<a href="{$strings['infourl']}" target="_blank" class="btn btn-small btn-info">
				{$strings['infolbl']}
			</a>
		</p>
	</div>
ENDRESULT;
		}

		echo '###' . $result . '###';

		// Cut the execution short
		JFactory::getApplication()->close();
	}

	public function selfblocked()
	{
		$externalIP = $this->input->getString('ip', '');

		/** @var \Akeeba\AdminTools\Admin\Model\ControlPanel $model */
		$model = $this->getModel();

		$result = (int)$model->isMyIPBlocked($externalIP);

		echo '###' . $result . '###';

		JFactory::getApplication()->close();
	}

	public function unblockme()
	{
		$externalIP = $this->input->getString('ip', '');

		/** @var \Akeeba\AdminTools\Admin\Model\ControlPanel $model */
		$model = $this->getModel();

		$model->unblockMyIP($externalIP);

		$this->setRedirect('index.php?option=com_admintools', JText::_('COM_ADMINTOOLS_CONTROLPANEL_IP_UNBLOCKED'));
	}

	/**
	 * Applies the Download ID when the user is prompted about it in the Control Panel
	 */
	public function applydlid()
	{
		$this->csrfProtection();

		$msg     = JText::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_INVALIDDOWNLOADID');
		$msgType = 'error';
		$dlid    = $this->input->getString('dlid', '');

		// If the Download ID seems legit let's apply it
		if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
		{
			$msg     = null;
			$msgType = null;

			$this->container->params->set('downloadid', $dlid);
			$this->container->params->save();
		}

		// Redirect back to the control panel
		$url       = '';
		$returnurl = $this->input->get('returnurl', '', 'base64');

		if (!empty($returnurl))
		{
			$url = base64_decode($returnurl);
		}

		if (empty($url))
		{
			$url = \JUri::base() . 'index.php?option=com_admintools';
		}

		$this->setRedirect($url, $msg, $msgType);
	}

	/**
	 * Reset the Secret Word for front-end and remote backup
	 *
	 * @return  void
	 */
	public function resetSecretWord()
	{
		$this->csrfProtection();

		$session   = JFactory::getSession();
		$newSecret = $session->get('newSecretWord', null, 'admintools.cpanel');

		if (empty($newSecret))
		{
			$random    = new RandomValue();
			$newSecret = $random->generateString(32);
			$session->set('newSecretWord', $newSecret, 'admintools.cpanel');
		}

		$this->container->params->set('frontend_secret_word', $newSecret);
		$this->container->params->save();

		$msg     = JText::sprintf('COM_ADMINTOOLS_MSG_CONTROLPANEL_FESECRETWORD_RESET', $newSecret);
		$msgType = null;

		$url = 'index.php?option=com_admintools';
		$this->setRedirect($url, $msg, $msgType);
	}

	public function reloadUpdateInformation()
	{
		$msg = null;

		/** @var Updates $model */
		$model = $this->container->factory->model('Updates')->tmpInstance();
		$model->getUpdates(true);

		$msg = JText::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_UPDATE_INFORMATION_RELOADED');
		$url = 'index.php?option=com_admintools';

		$this->setRedirect($url, $msg);
	}

	/**
	 * Resets the "updatedb" flag and forces the database updates
	 */
	public function forceUpdateDb()
	{
		// Reset the flag so the updates could take place
		$this->container->params->set('updatedb', null);
		$this->container->params->save();

		/** @var \Akeeba\AdminTools\Admin\Model\ControlPanel $model */
		$model = $this->getModel();

		try
		{
			$model->checkAndFixDatabase();
		}
		catch (\RuntimeException $e)
		{
			// This should never happen, since we reset the flag before execute the update, but you never know
		}

		$this->setRedirect('index.php?option=com_admintools');
	}
}