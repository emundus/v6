<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      Benjamin Rivalland
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

use Joomla\CMS\Factory;

class EmundusControllerPayment extends JControllerLegacy
{
	protected $app;

	public function __construct()
	{
		parent::__construct();

		$this->app = Factory::getApplication();

		// Attach logging system.
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.payment.php'], JLog::ALL, array('com_emundus.payment'));
	}

	/**
	 * called from post method
	 */
	public function getFlywireConfig()
	{
		$emundusUser = JFactory::getSession()->get('emundusUser');

		$format = $this->input->get('format', '');
		$fnum   = $emundusUser->fnum;
		$body   = file_get_contents('php://input');
		$body   = json_decode($body, true);

		if (!empty($fnum)) {
			$params = JComponentHelper::getParams('com_emundus');
			$model  = $this->getModel('Payment');
			$model->createPaymentOrder($fnum, 'flywire');

			$response = array(
				'success' => true,
				'message' => '',
				'data'    => array(
					'locale'       => 'fr-FR',
					'provider'     => 'embed2.0',
					'currency'     => 'EUR',
					'recipient'    => $params->get('flywire_recipient'),
					'env'          => $params->get('flywire_mode'),
					'fnum'         => $fnum,
					'callback_url' => JUri::base() . 'index.php?option=com_emundus&controller=webhook&task=updateFlywirePaymentInfos&token=' . JFactory::getConfig()->get('secret') . '&guest=1&format=raw',
					'callback_id'  => $model->setPaymentUniqid($fnum),
					'amount'       => $model->getPrice($fnum) * 100,
				)
			);

			$response['data'] = array_merge($response['data'], $body);
			$response['data'] = $model->getFlywireExtendedConfig($response['data']);


			$config              = $response['data'];
			$config['initiator'] = 'emundus';
			$model->saveConfig($fnum, $config);

			require_once(JPATH_ROOT . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'logs.php');
			require_once(JPATH_ROOT . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
			$m_files   = $this->getModel('Files');
			$fnumInfos = $m_files->getFnumInfos($fnum);

			EmundusModelLogs::log(95, $fnumInfos['applicant_id'], $fnum, 38, 'u', 'COM_EMUNDUS_PAYMENT_INITIALISATION', json_encode($response['data']));
		}

		if ($format == 'json') {
			echo json_encode($response);
			exit;
		}
		else {
			return $response;
		}
	}

	public function updateFlywirePaymentInfos()
	{
		$data = [];

		$data['status']      = $this->input->get('status', '');
		$data['amount']      = $this->input->get('amount', '');
		$data['at']          = $this->input->get('at', '');
		$data['id']          = $this->input->get('id', '');
		$data['callback_id'] = $this->input->get('callback_id', '');
		$fnum                = $this->input->get('fnum', '');

		if (!empty($fnum) && !empty($data['callback_id'])) {
			$model = $this->getModel('Payment');
			$model->updateFlywirePaymentInfos($fnum, $data['callback_id'], $data);
		}
		else {
			JLog::add('Can not update payment infos : fnum or callback_id is empty, received : ' . json_encode($data), JLog::WARNING, 'com_emundus.payment');
		}
	}

	public function updateFileTransferPayment()
	{
		$emundusUser = JFactory::getSession()->get('emundusUser');

		$model   = $this->getModel('Payment');
		$updated = $model->updateFileTransferPayment($emundusUser);

		echo json_encode(array('status' => $updated));
		exit;
	}

	public function resetpaymentsession()
	{


		$redirect = $this->input->get('redirect', false);
		$model    = $this->getModel('payment');
		$model->resetPaymentSession();

		if ($redirect) {
			$this->app->redirect('/');
		}
	}


	public function checkpaymentsession()
	{
		$is_valid = true;


		$fnum = $this->input->get('fnum', false);

		if (!empty($fnum)) {
			$model    = $this->getModel('payment');
			$is_valid = $model->checkPaymentSession();
		}

		echo json_encode(array('response' => $is_valid));
		exit;
	}
}