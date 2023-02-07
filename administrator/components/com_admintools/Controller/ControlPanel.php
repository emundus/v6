<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Controller\Mixin\PredefinedTaskList;
use Akeeba\AdminTools\Admin\Helper\ServerTechnology;
use Akeeba\AdminTools\Admin\Model\MasterPassword;
use Akeeba\AdminTools\Admin\Model\Updates;
use Exception;
use FOF40\Container\Container;
use FOF40\Controller\Controller;
use FOF40\Encrypt\Randval;
use FOF40\Utils\ViewManifestMigration;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use RuntimeException;

class ControlPanel extends Controller
{
	use PredefinedTaskList;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = [
			'browse',
			'login',
			'selfblocked',
			'unblockme',
			'applydlid',
			'resetSecretWord',
			'forceUpdateDb',
			'IpWorkarounds',
			'changelog',
			'endRescue',
			'renameMainPhp',
			'ignoreServerConfigWarn',
			'regenerateServerConfig',
			'helloerror',
		];
	}

	public function helloerror()
	{
		throw new RuntimeException('This is an error');
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
		catch (RuntimeException $e)
		{
			// The update is stuck. We will display a warning in the Control Panel
		}

		// Update the magic parameters
		$model->updateMagicParameters();

		// Delete the old log files if logging is disabled
		$model->deleteOldLogs();

		// Migrate view manifest XML
		ViewManifestMigration::migrateJoomla4MenuXMLFiles($this->container);
		ViewManifestMigration::removeJoomla3LegacyViews($this->container);

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
		$model    = $this->getModel('MasterPassword');
		$password = $this->input->get('userpw', '', 'raw');
		$model->setUserPassword($password);

		$url = 'index.php?option=com_admintools';
		$this->setRedirect($url);
	}

	public function selfblocked()
	{
		$externalIP = $this->input->getString('ip', '');

		/** @var \Akeeba\AdminTools\Admin\Model\ControlPanel $model */
		$model = $this->getModel();

		$result = (int) $model->isMyIPBlocked($externalIP);

		echo '###' . $result . '###';

		$this->container->platform->closeApplication();
	}

	public function unblockme()
	{
		$unblockIP = [];
		$unblockIP[] = $this->input->getString('ip', '');

		/** @var \Akeeba\AdminTools\Admin\Model\ControlPanel $model */
		$model       = $this->getModel();
		$unblockIP[] = $model->getVisitorIP();

		/** @var \Akeeba\AdminTools\Admin\Model\UnblockIP $unblockModel */
		$unblockModel = $this->container->factory->model('UnblockIP')->tmpInstance();
		$unblockModel->unblockIP($unblockIP);

		$this->setRedirect('index.php?option=com_admintools', Text::_('COM_ADMINTOOLS_CONTROLPANEL_IP_UNBLOCKED'));
	}

	public function endRescue()
	{
		$this->container->platform->unsetSessionVar('rescue_timestamp', 'com_admintools');
		$this->container->platform->unsetSessionVar('rescue_username', 'com_admintools');

		$this->setRedirect('index.php?option=com_admintools');
	}

	/**
	 * Applies the Download ID when the user is prompted about it in the Control Panel
	 */
	public function applydlid()
	{
		$this->csrfProtection();

		$msg     = Text::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_INVALIDDOWNLOADID');
		$msgType = 'error';
		$dlid    = $this->input->getString('dlid', '');

		/** @var Updates $updateModel */
		$updateModel = $this->container->factory->model('Updates')->tmpInstance();
		$dlid        = $updateModel->sanitizeLicenseKey($dlid);
		$isValidDLID = $updateModel->isValidLicenseKey($dlid);

		// If the Download ID seems legit let's apply it
		if ($isValidDLID)
		{
			$msg     = null;
			$msgType = null;

			$updateModel->setLicenseKey($dlid);
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
			$url = Uri::base() . 'index.php?option=com_admintools';
		}

		$this->setRedirect($url, $msg, $msgType);
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
		catch (RuntimeException $e)
		{
			// This should never happen, since we reset the flag before execute the update, but you never know
		}

		$this->setRedirect('index.php?option=com_admintools');
	}

	/**
	 * Enables the IP workarounds option or disables the warning
	 */
	public function IpWorkarounds()
	{
		$enable = $this->input->getInt('enable', 0);
		$msg    = null;

		if ($enable)
		{
			$msg = Text::_('COM_ADMINTOOLS_CPANEL_ERR_PRIVNET_ENABLED');
		}

		/** @var \Akeeba\AdminTools\Admin\Model\ControlPanel $model */
		$model = $this->getModel();
		$model->setIpWorkarounds($enable);

		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$returnUrl = $customURL ?: 'index.php?option=com_admintools&view=ControlPanel';

		$this->setRedirect($returnUrl, $msg);
	}

	public function changelog()
	{
		$view = $this->getView();
		$view->setLayout('changelog');

		$this->display(true);
	}

	public function renameMainPhp()
	{
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\ControlPanel $model */
		$model = $this->getModel();
		$model->reenableMainPhp();

		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$returnUrl = $customURL ?: 'index.php?option=com_admintools&view=ControlPanel';

		$this->setRedirect($returnUrl);
	}

	/**
	 * Put a flag inside component configuration so user won't be warned again if he manually edits any server
	 * configuration file. He can enable it again by changing its value inside the component options
	 */
	public function ignoreServerConfigWarn()
	{
		$this->container->params->set('serverconfigwarn', 0);
		$this->container->params->save();

		$this->setRedirect('index.php?option=com_admintools&view=ControlPanel');
	}

	public function regenerateServerConfig()
	{
		$classModel = '';

		if (ServerTechnology::isHtaccessSupported())
		{
			$classModel = 'HtaccessMaker';
		}
		elseif (ServerTechnology::isNginxSupported())
		{
			$classModel = 'NginXConfMaker';
		}
		elseif (ServerTechnology::isWebConfigSupported())
		{
			$classModel = 'WebConfigMaker';
		}

		if (!$classModel)
		{

			$this->setRedirect('index.php?option=com_admintools&view=ControlPanel', Text::_('COM_ADMINTOOLS_CPANEL_SERVERCONFIGWARN_ERR_REGENERATE'), 'error');

			return;
		}

		/** @var \Akeeba\AdminTools\Admin\Model\ServerConfigMaker $model */
		$model = $this->container->factory->model($classModel)->tmpInstance();

		$model->writeConfigFile();

		$this->setRedirect('index.php?option=com_admintools&view=ControlPanel', Text::_('COM_ADMINTOOLS_CPANEL_SERVERCONFIGWARN_REGENERATED'));
	}

	/**
	 * Reset the Secret Word for front-end and remote backup
	 *
	 * @return  void
	 * @throws  Exception
	 */
	public function resetSecretWord()
	{
		$this->csrfProtection();

		$newSecret = $this->container->platform->getSessionVar('newSecretWord', null, 'admintools.cpanel');

		if (empty($newSecret))
		{
			$random    = new Randval();
			$newSecret = $random->getRandomPassword(32);
			$this->container->platform->setSessionVar('newSecretWord', $newSecret, 'admintools.cpanel');
		}

		$this->container->params->set('frontend_secret_word', $newSecret);
		$this->container->params->save();

		$msg     = Text::sprintf('COM_ADMINTOOLS_MSG_CONTROLPANEL_FESECRETWORD_RESET', $newSecret);
		$msgType = null;

		$url = 'index.php?option=com_admintools';
		$this->setRedirect($url, $msg, $msgType);
	}
}
