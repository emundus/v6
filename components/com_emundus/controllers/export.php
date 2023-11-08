<?php
/**
 * @version         $Id: export.php 750 2020-05-05 22:29:38Z brivalland $
 * @package         Joomla
 * @copyright   (C) 2020 eMundus LLC. All rights reserved.
 * @license         GNU General Public License
 */

// ensure this file is being included by a parent file
defined('_JEXEC') or die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'export.php');

//client api for file conversion
use TheCodingMachine\Gotenberg\Client;
use TheCodingMachine\Gotenberg\ClientException;
use TheCodingMachine\Gotenberg\DocumentFactory;
use TheCodingMachine\Gotenberg\OfficeRequest;
use TheCodingMachine\Gotenberg\HTMLRequest;
use TheCodingMachine\Gotenberg\Request;
use TheCodingMachine\Gotenberg\RequestException;
use GuzzleHttp\Psr7\LazyOpenStream;

use Joomla\CMS\Factory;

/**
 * Custom report controller
 * @package     Emundus
 */
class EmundusControllerExport extends JControllerLegacy
{
	protected $app;

	private $_user;

	public function __construct($config = array())
	{

		$this->app   = Factory::getApplication();
		$this->_user = $this->app->getIdentity();

		parent::__construct($config);
	}

	public function display($cachable = false, $urlparams = false)
	{
		// Set a default view if none exists
		if (!$this->input->get('view')) {
			$default = 'application_form';
			$this->input->set('view', $default);
		}
		parent::display();
	}

	public function to_pdf()
	{
		$eMConfig             = JComponentHelper::getParams('com_emundus');
		$gotenberg_activation = $eMConfig->get('gotenberg_activation', 0);
		$gotenberg_url        = $eMConfig->get('gotenberg_url', 'http://localhost:3000');

		$res = new stdClass();

		if ($gotenberg_activation != 1) {
			$res->status = false;
			$res->msg    = JText::_('COM_EMUNDUS_ERROR_EXPORT_API_DESACTIVATED');
			echo json_encode($res);
			exit();
		}


		$fnum            = $this->input->getString('fnum', null);
		$file_src        = $this->input->getString('src', null);
		$file_src_format = $this->input->getString('type', null);
		$file_dest       = $this->input->getString('dest', null);

		$user_id = (int) substr($fnum, -7);

		if (EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $fnum)) {
			require JPATH_LIBRARIES . '/emundus/vendor/autoload.php';

			$src = JPATH_ROOT . DS . 'tmp' . DS . $file_src;
			if (!empty($file_dest) && !empty($user_id)) {
				$dest = EMUNDUS_PATH_ABS . $user_id . DS . $file_dest;
			}
			else {
				$dest = JPATH_ROOT . DS . 'tmp' . DS . $file_src . '.pdf';
			}

			$client = new Client($gotenberg_url, new \Http\Adapter\Guzzle6\Client());
			$files  = [
				DocumentFactory::makeFromPath($file_src, $src),
			];
			///
			try {
				if ($file_src_format != 'html') {
					//Office
					$request = new OfficeRequest($files);
				}
				else {
					// HTML
					// @todo define parts of html source (header, footer, body)
					$header  = '';
					$footer  = '';
					$assets  = '';
					$request = new HTMLRequest($src);
					$request->setHeader($header);
					$request->setFooter($footer);
					$request->setAssets($assets);
					$request->setPaperSize(Request::A4);
					$request->setMargins(Request::NO_MARGINS);
					$request->setScale(0.75);
				}

				# store method allows you to... store the resulting PDF in a particular destination.
				$client->store($request, $dest);

				# if you wish to redirect the response directly to the browser, you may also use:
				$client->post($request);
			}
			catch (RequestException $e) {
				# this exception is thrown if given paper size or margins are not correct.
				$res->status = false;
				$res->msg    = JText::_('COM_EMUNDUS_ERROR_EXPORT_MARGIN');
				echo json_encode($res);
				exit();
			}
			catch (ClientException $e) {
				# this exception is thrown by the client if the API has returned a code != 200.
				$res->status = false;
				$res->msg    = JText::_('COM_EMUNDUS_ERROR_EXPORT_API');
				echo json_encode($res);
				exit();
			}

			$res->status = true;
			$res->msg    = '<a href="' . $dest . '" target="_blank">' . $dest . '</a>';
			echo json_encode($res);
			exit();
		}
		else {
			$res->status = false;
			$res->msg    = JText::_('ACCESS_DENIED');
			echo json_encode($res);
			exit();
		}
	}

	public function getprofiles()
	{
		$current_user = JFactory::getUser();

		if (!EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
			die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
		}
		else {


			$code = $this->input->getVar('code', null);
			$camp = $this->input->getVar('camp', null);

			$code = explode(',', $code);
			$camp = explode(',', $camp);

			require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
			$m_profile = $this->getModel('Profile');
			$profiles  = $m_profile->getProfileIDByCampaigns($camp, $code);

			echo json_encode((object) $profiles);
			exit();
		}
	}
}
