<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

class plgSystemEBDepositReminder extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array    $config   An optional associative array of configuration settings.
	 *
	 */
	public function __construct(&$subject, $config = [])
	{
		if (!file_exists(JPATH_ROOT . '/components/com_eventbooking/eventbooking.php'))
		{
			return;
		}

		parent::__construct($subject, $config);
	}

	/**
	 * Send deposit reminder email
	 *
	 * @throws Exception
	 */
	public function onAfterRespond()
	{
		if (!$this->app)
		{
			return;
		}

		$bccEmail                = $this->params->get('bcc_email', '');
		$numberDays              = (int) $this->params->get('number_days', 7);
		$numberEmailSendEachTime = (int) $this->params->get('number_registrants', 0);

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

		$cacheTime = (int) $this->params->get('cache_time', 20) * 60; // 60 minutes

		if (!EventbookingHelperPlugin::checkAndStoreLastRuntime($this->params, $cacheTime, $this->_name))
		{
			return;
		}

		EventbookingHelper::callOverridableHelperMethod('Mail', 'sendDepositReminder', [$numberDays, $numberEmailSendEachTime, $bccEmail]);
	}
}
