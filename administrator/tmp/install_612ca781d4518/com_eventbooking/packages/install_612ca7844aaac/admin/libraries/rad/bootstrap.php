<?php
/**
 * Register the prefix so that the classes in RAD library can be auto-load
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

// Special case for 32 bits
if (strtotime('2099-12-31 00:00:00') === false)
{
	define('EB_TBC_DATE', '2030-12-31 00:00:00');
}
else
{
	define('EB_TBC_DATE', '2099-12-31 00:00:00');
}

JLoader::registerPrefix('RAD', dirname(__FILE__));
$app = Factory::getApplication();
JLoader::registerPrefix('Eventbooking', JPATH_BASE . '/components/com_eventbooking');
Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_eventbooking/table');

require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/table/eventbooking.php';

if ($app->isClient('administrator'))
{
	// Traits
	JLoader::register('EventbookingViewEvent', JPATH_ROOT . '/components/com_eventbooking/view/event.php');
	JLoader::register('EventbookingViewRegistrants', JPATH_ROOT . '/components/com_eventbooking/view/registrants.php');
	JLoader::register('EventbookingViewRegistrant', JPATH_ROOT . '/components/com_eventbooking/view/registrant.php');

	// Helpers
	JLoader::register('EventbookingHelper', JPATH_ROOT . '/components/com_eventbooking/helper/helper.php');
	JLoader::register('EventbookingHelperCertificate', JPATH_ROOT . '/components/com_eventbooking/helper/certificate.php');
	JLoader::register('EventbookingHelperIcs', JPATH_ROOT . '/components/com_eventbooking/helper/ics.php');
	JLoader::register('EventbookingHelperHtml', JPATH_ROOT . '/components/com_eventbooking/helper/html.php');
	JLoader::register('EventbookingHelperCart', JPATH_ROOT . '/components/com_eventbooking/helper/cart.php');
	JLoader::register('EventbookingHelperRoute', JPATH_ROOT . '/components/com_eventbooking/helper/route.php');
	JLoader::register('EventbookingHelperJquery', JPATH_ROOT . '/components/com_eventbooking/helper/jquery.php');
	JLoader::register('EventbookingHelperData', JPATH_ROOT . '/components/com_eventbooking/helper/data.php');
	JLoader::register('EventbookingHelperDatabase', JPATH_ROOT . '/components/com_eventbooking/helper/database.php');
	JLoader::register('EventbookingHelperMail', JPATH_ROOT . '/components/com_eventbooking/helper/mail.php');
	JLoader::register('EventbookingHelperTicket', JPATH_ROOT . '/components/com_eventbooking/helper/ticket.php');
	JLoader::register('EventbookingHelperAcl', JPATH_ROOT . '/components/com_eventbooking/helper/acl.php');
	JLoader::register('EventbookingHelperBootstrap', JPATH_ROOT . '/components/com_eventbooking/helper/bootstrap.php');
	JLoader::register('EventbookingHelperRegistration', JPATH_ROOT . '/components/com_eventbooking/helper/registration.php');
	JLoader::register('EventbookingHelperValidator', JPATH_ROOT . '/components/com_eventbooking/helper/validator.php');
	JLoader::register('EventbookingHelperPayments', JPATH_ROOT . '/components/com_eventbooking/helper/payments.php');
	JLoader::register('EventbookingHelperEuvat', JPATH_ROOT . '/components/com_eventbooking/helper/euvat.php');
	JLoader::register('EventbookingHelperPdf', JPATH_ROOT . '/components/com_eventbooking/helper/pdf.php');
	JLoader::register('EventbookingHelperPlugin', JPATH_ROOT . '/components/com_eventbooking/helper/plugin.php');

	// Register override classes
	$possibleOverrides = [
		'EventbookingHelperOverrideHelper'       => 'helper.php',
		'EventbookingHelperOverrideMail'         => 'mail.php',
		'EventbookingHelperOverrideJquery'       => 'jquery.php',
		'EventbookingHelperOverrideData'         => 'data.php',
		'EventbookingHelperOverrideRegistration' => 'registration.php',
		'EventbookingHelperOverrideTicket'       => 'ticket.php',
	];

	foreach ($possibleOverrides as $className => $filename)
	{
		JLoader::register($className, JPATH_ROOT . '/components/com_eventbooking/helper/override/' . $filename);
	}
}
else
{
	JLoader::register('EventbookingModelMassmail', JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/massmail.php');
}


JLoader::register('os_payments', JPATH_ROOT . '/components/com_eventbooking/payments/os_payments.php');
JLoader::register('os_payment', JPATH_ROOT . '/components/com_eventbooking/payments/os_payment.php');

JLoader::register('JFile', JPATH_LIBRARIES . '/joomla/filesystem/file.php');

// Validator
/** @var \Composer\Autoload\ClassLoader $autoLoader */
$autoLoader = include JPATH_LIBRARIES . '/vendor/autoload.php';
$autoLoader->setPsr4('Valitron\\', JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/vendor/valitron/src/Valitron');

$config = EventbookingHelper::getConfig();

if ($config->debug)
{
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');
}
else
{
	error_reporting(0);
}

// Disable STRICT_TRANS_TABLES mode
if (EventbookingHelper::isJoomla4())
{
	$db = Factory::getDbo();
	$db->setQuery("SET sql_mode=(SELECT REPLACE(@@sql_mode,'STRICT_TRANS_TABLES',''));");
	$db->execute();
}