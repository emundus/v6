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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

class EmundusControllerPayment extends JControllerLegacy
{
    public function __construct()
    {
        parent::__construct();

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
        $jinput = JFactory::getApplication()->input;
        $format = $jinput->get('format', '');
        $fnum = $emundusUser->fnum;
        $body = file_get_contents('php://input');
        $body = json_decode($body, true);

        if (!empty($fnum)) {
            $params = JComponentHelper::getParams('com_emundus');
            $model = new EmundusModelPayment();
            $model->createPaymentOrder($fnum, 'flywire');

            $response = array(
                'success' => true,
                'message' => '',
                'data' => array(
                    'locale' => 'fr-FR',
                    'provider' => 'embed2.0',
                    'currency' => 'EUR',
                    'recipient' => $params->get('flywire_recipient'),
                    'env' => $params->get('flywire_mode'),
                    'fnum' => $fnum,
                    'callback_url' => JUri::base() . 'index.php?option=com_emundus&controller=webhook&task=updateFlywirePaymentInfos&token=' . JFactory::getConfig()->get('secret') . '&guest=1&format=raw',
                    'callback_id' => $model->setPaymentUniqid($fnum),
                    'amount' => $model->getPrice($fnum) * 100,
                )
            );

            $response['data'] = array_merge($response['data'], $body);
            $response['data'] = $model->getFlywireExtendedConfig($response['data']);


            $config = $response['data'];
            $config['initiator'] = 'emundus';
            $model->saveConfig($fnum, $config);

            require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
            require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
            $m_files = new EmundusModelFiles;
            $fnumInfos = $m_files->getFnumInfos($fnum);

            EmundusModelLogs::log(95, $fnumInfos['applicant_id'], $fnum, 38, 'u', 'COM_EMUNDUS_PAYMENT_INITIALISATION', json_encode($response['data']));
        }

        if ($format == 'json') {
            echo json_encode($response);
            exit;
        } else {
            return $response;
        }
    }

    public function updateFlywirePaymentInfos()
    {
        $data = [];
        $jinput = JFactory::getApplication()->input;
        $data['status'] = $jinput->get('status', '');
        $data['amount'] = $jinput->get('amount', '');
        $data['at'] = $jinput->get('at', '');
        $data['id'] = $jinput->get('id', '');
        $data['callback_id'] = $jinput->get('callback_id', '');
        $fnum = $jinput->get('fnum', '');

        if (!empty($fnum) && !empty($data['callback_id'])) {
            $model = new EmundusModelPayment();
            $model->updateFlywirePaymentInfos($fnum, $data['callback_id'], $data);
        } else {
            JLog::add('Can not update payment infos : fnum or callback_id is empty, received : ' . json_encode($data), JLog::WARNING, 'com_emundus.payment');
        }
    }

    public function updateFileTransferPayment()
    {
        $emundusUser = JFactory::getSession()->get('emundusUser');

        $model = new EmundusModelPayment();
        $updated = $model->updateFileTransferPayment($emundusUser);

        echo json_encode(array('status' => $updated));
        exit;
    }

    public function resetpaymentsession()
    {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $redirect = $jinput->get('redirect', false);
        $model = $this->getModel('payment');
        $model->resetPaymentSession();

        if ($redirect) {
            $app->redirect('/');
        }
    }


    public function checkpaymentsession()
    {
        $is_valid = true;
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $fnum = $jinput->get('fnum', false);

        if (!empty($fnum)) {
            $model = $this->getModel('payment');
            $is_valid = $model->checkPaymentSession();
        }

        echo json_encode(array('response' => $is_valid));
        exit;
    }
}