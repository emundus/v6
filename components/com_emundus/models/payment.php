<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2022 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      LEGENDRE Jérémy
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');

class EmundusModelPayment extends JModelList
{
    public function __construct()
    {
        parent::__construct();

        // Attach logging system.
        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.payment.php'], JLog::ALL, array('com_emundus.payment'));
    }

    public function getPrice($fnum)
    {
        $price = 0;
        $payment = $this->getPaymentInfos($fnum);

        if (!empty($payment)) {
            $isScholarshipHolder = $this->isScholarshipStudent($fnum);
            if ($isScholarshipHolder) {
                $product = $this->getProduct($payment->scholarship_holder_product_id);
            } else {
                $product = $this->getProduct($payment->product_id);
            }

            if (!empty($product)) {
                $sort_price = str_replace(',', '', $product->product_sort_price);
                $price = number_format((double)$sort_price, 2, ',', '');
            } else {
                JLog::add('Error getting product price : product is empty', JLog::WARNING, 'com_emundus.payment');
            }
        } else {
            JLog::add('Error getting product price : payment is empty', JLog::WARNING, 'com_emundus.payment');
        }

        return $price;
    }

    /**
     * Detect if student is a scholarship student
     * @param $fnum string
     * @return bool
     */
    public function isScholarshipStudent($fnum): bool
    {
        $amIScholarshipStudent = false;

        $params	= JComponentHelper::getParams('com_emundus');
        $scholarship_document_id 	= $params->get('scholarship_document_id', NULL);

        if (!empty($scholarship_document_id) ) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);

            $query->select('COUNT(id)')
                ->from('#__emundus_uploads')
                ->where('fnum = ' . $db->quote($fnum))
                ->andWhere('attachment_id = ' . $db->quote($scholarship_document_id));

            $db->setQuery($query);

            try {
                $amIScholarshipStudent = $db->loadResult() > 0;
            } catch (Exception $e) {
                JLog::add('Error getting scholarship student infos from fnum ('. $fnum .') : '. $e, JLog::ERROR, 'com_emundus.payment');
            }
        }

        return $amIScholarshipStudent;
    }

    /**
     * @return bool
     */
    public function doesScholarshipHoldersNeedToPay(): bool
    {
        $params	= JComponentHelper::getParams('com_emundus');
        return $params->get('pay_scholarship', 0) == 1;
    }

    public function setPaymentUniqid($fnum)
    {
        $uniqid = $fnum . '-' . uniqid();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id, order_id, params')
            ->from($db->quoteName('#__emundus_hikashop'))
            ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));

        $db->setQuery($query);
        $emundusHikashop = $db->loadObject();

        if (empty($emundusHikashop->id)) {
            $this->createPaymentOrder($fnum, 'flywire');
            $emundusHikashop = $db->loadObject();
        } else {
            $this->updateHikashopOrderType($emundusHikashop->order_id, 'flywire');
        }

        if (!empty($emundusHikashop->params)) {
            $params = json_decode($emundusHikashop->params);
            $params->uniqId = $uniqid;
            $params->type = 'flywire';
        } else {
            $params = array('uniqId' => $uniqid);
        }

        $query->clear();
        $query->update($db->quoteName('#__emundus_hikashop'))
            ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
            ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (Exception $e) {
            $uniqid = false;
            JLog::add('Error setting payment uniqid ('. $fnum .') : '. $e, JLog::ERROR, 'com_emundus.payment');
        }

        return $uniqid;
    }

    public function createPaymentOrder($fnum, $type, $order_number = null)
    {
        $order_id = 0;
        $created = false;
        $user_id = $this->getUserIdFromFnum($fnum);
        $hikashop_user_id = $this->getHikashopUserId($user_id);

        if (!empty($hikashop_user_id)) {
            $price = $this->getPrice($fnum);
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $columns = array('order_user_id', 'order_status', 'order_created', 'order_modified', 'order_type', 'order_payment_method', 'order_full_price');
            $values = $db->quote($hikashop_user_id) . ', ' . $db->quote('created') . ', ' . $db->quote(time()) . ', ' . $db->quote(time()) . ', ' . $db->quote($type) . ', ' . $db->quote($type) . ', ' . $db->quote($price);

            if ($order_number !== null) {
                $columns[] = 'order_number';
                $values .= ', ' . $db->quote($order_number);
            }

            $query->clear()
                ->insert($db->quoteName('#__hikashop_order'))
                ->columns($db->quoteName($columns))
                ->values($values);

            $db->setQuery($query);

            try {
                $created = $db->execute();
                $order_id = $db->insertid();
            } catch (Exception $e) {
                JLog::add('Error creating payment order : ' . $e->getMessage(), JLog::WARNING, 'com_emundus.payment');
            }

            if ($created && !empty($order_id)) {
                $this->updateEmundusHikashopOrderId($fnum, $order_id);
            }
        } else {
            JLog::add('Error creating payment order : user is empty', JLog::WARNING, 'com_emundus.payment');
        }

        return $order_id;
    }

    private function getUserIdFromFnum($fnum)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('applicant_id')
            ->from($db->quoteName('#__emundus_campaign_candidature'))
            ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));

        $db->setQuery($query);
        $applicant_id = $db->loadResult();

        return $applicant_id;
    }

    private function getHikashopUserId($user_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('jhu.user_id')
            ->from('#__hikashop_user AS jhu')
            ->where('jhu.user_cms_id = ' . $user_id);

        $db->setQuery($query);
        $hikashop_user_id = $db->loadResult();

        if (empty($hikashop_user_id)) {
            $hikashop_user_id = $this->createHikashopUser($user_id);
        }

        return $hikashop_user_id;
    }

    private function createHikashopUser($user_id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('email')
            ->from('#__users')
            ->where('id = ' . $user_id);

        $db->setQuery($query);

        try {
            $email = $db->loadResult();
        } catch (Exception $e) {
            JLog::add('Error getting email from user id : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.payment');
            return false;
        }

        $query->clear();
        $query->insert('#__hikashop_user')
            ->columns('user_cms_id, user_email')
            ->values($user_id . ', ' . $db->quote($email));

        $db->setQuery($query);

        try {
            $inserted = $db->execute();
        } catch (Exception $e) {
            JLog::add('Error creating hikashop user : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.payment');
        }

        if ($inserted) {
            $query->clear();
            $query->select('user_id')
                ->from('#__hikashop_user')
                ->where('user_cms_id = ' . $user_id);

            $db->setQuery($query);
            $hikashop_user_id = $db->loadResult();
        }

        return $hikashop_user_id;
    }

    /**
     * @param $fnum string
     * @param $order_id int
     * @return bool
     */
    private function updateEmundusHikashopOrderId($fnum, $order_id): bool
    {
        $updated = false;

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
            ->from('#__emundus_hikashop')
            ->where('fnum = ' . $db->quote($fnum));

        $db->setQuery($query);

        try {
            $id = $db->loadResult();
        } catch (Exception $e) {
            JLog::add('Error getting id from fnum : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.payment');
            return false;
        }

        if (empty($id)) {
            $updated = $this->insertEmundusHikashopOrderId($fnum, $order_id);
        } else {
            $query->clear();
            $query->update($db->quoteName('#__emundus_hikashop'))
                ->set($db->quoteName('order_id') . ' = ' . $db->quote($order_id))
                ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));

            $db->setQuery($query);

            try {
                $updated = $db->execute();
            } catch (Exception $e) {
                JLog::add('Error updating emundus hikashop order id : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.payment');
            }
        }

        return $updated;
    }

    /**
     * @param $fnum
     * @param $order_id
     * @return bool
     */
    private function insertEmundusHikashopOrderId($fnum, $order_id): bool
    {
        $inserted = false;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $m_files = new EmundusModelFiles;
        $fnumInfos = $m_files->getFnumInfos($fnum);

        if (!empty($fnumInfos)) {
            $query->clear()
                ->insert($db->quoteName('#__emundus_hikashop'))
                ->columns($db->quoteName(array('user', 'fnum', 'status', 'order_id', 'campaign_id')))
                ->values($db->quote($fnumInfos['applicant_id']) . ', ' . $db->quote($fnum) . ', ' . $db->quote($fnumInfos['status']) . ', ' . $db->quote($order_id) . ', ' . $db->quote($fnumInfos['campaign_id']));

            $db->setQuery($query);

            try {
                $inserted = $db->execute();
            } catch (Exception $e) {
                JLog::add('Error inserting emundus hikashop order id : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.payment');
            }
        } else {
            JLog::add('Error getting fnum infos : fnum is empty', JLog::WARNING, 'com_emundus.payment');
        }

        return $inserted;
    }

    public static function getPaymentInfos($fnum)
    {
        $payment = false;

        if (!empty($fnum)) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);

            $query->select('jespbc.*, jec.campaign_id')
                ->from('#__emundus_setup_payments_by_campaign AS jespbc')
                ->leftJoin('#__emundus_setup_payments_by_campaign_repeat_campaign_id as jespbcr ON jespbcr.parent_id = jespbc.id')
                ->leftJoin('#__emundus_campaign_candidature as jec ON jec.campaign_id = jespbcr.campaign_id')
                ->where('jec.fnum = ' . $db->quote($fnum));

            $db->setQuery($query);

            try {
                $payment = $db->loadObject();
            } catch (Exception $e) {
                JLog::add('Error getting payment infos from fnum ('. $fnum .') : '. $e, JLog::ERROR, 'com_emundus.payment');
            }
        } else {
            JLog::add('Error getting payment infos from fnum : fnum is empty', JLog::WARNING, 'com_emundus.payment');
        }

        return $payment;
    }

    public static function getProduct($product_id)
    {
        $product = false;

        if (!empty($product_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('*')
                ->from('#__hikashop_product')
                ->where($db->quoteName('product_id') . ' = ' . $db->quote($product_id));
            $db->setQuery($query);

            try {
                $product = $db->loadObject();
            } catch (Exception $e) {
                JLog::add('Error getting product ('. $product_id .') : '. $e, JLog::ERROR, 'com_emundus.payment');
            }
        } else {
            JLog::add('Error getting product : product_id is empty', JLog::WARNING, 'com_emundus.payment');
        }

        return $product;
    }

    /**
     * @param string $callback_id
     * @param array $data
     * @return bool
     */
    public function updateFlywirePaymentInfos($callback_id, $data)
    {
        $updated = false;
        $fnum = $this->getFnumFromCallbackId($callback_id);

        if (!empty($fnum)) {
            require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
            require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
            $m_files = new EmundusModelFiles;
            $fnum_infos = $m_files->getFnumInfos($fnum);
            $check_amount = $this->checkAmountCoherence($fnum, $data['amount']);

            if ($check_amount === true) {
                $hikashop_status = '';
                switch ($data['status']) {
                    case 'initiated': // Payment is being initiated
                        $hikashop_status = 'created';
                        break;
                    case 'guaranteed': // funds are received by Flywire, so pending in hikashop
                        $hikashop_status = 'pending';
                        break;
                    case 'delivered': // funds are delivered to the customer, so confirmed in hikashop
                        $hikashop_status = 'confirmed';
                        $this->updateFnumStateFromFlywire($fnum);
                        break;
                    case 'cancelled': // funds are cancelled
                        $hikashop_status = 'cancelled';
                        break;
                    default:
                        // do nothing, each case must be handled separately
                        JLog::add('Error updating flywire payment infos : status ' . $data['status'] . ' is not handled', JLog::ERROR, 'com_emundus.payment');
                        break;
                }

                if (!empty($hikashop_status)) {
                    $updated = $this->updateHikashopPayment($fnum, $hikashop_status, $data);
                    $data['updated'] = $updated;
                    EmundusModelLogs::log(95, $fnum_infos['applicant_id'], $fnum, 38, 'u', 'COM_EMUNDUS_PAYMENT_UPDATE_FLYWIRE_PAYMENT_INFOS', json_encode($data));
                } else {
                    EmundusModelLogs::log(95, $fnum_infos['applicant_id'], $fnum, 38, 'u', 'COM_EMUNDUS_PAYMENT_UPDATE_FLYWIRE_PAYMENT_INFOS', 'Error updating flywire payment infos from given data ' . json_encode($data));
                }
            } else {
                EmundusModelLogs::log(95, $fnum_infos['applicant_id'], $fnum, 38, 'u', 'COM_EMUNDUS_PAYMENT_UPDATE_FLYWIRE_INCOHERENT_PAYMENT_INFOS', $data['amount'] . ' != ' . $this->getPrice($fnum));
                JLog::add('Error updating flywire payment infos : amount is not coherent for fnum ' . $fnum, JLog::ERROR, 'com_emundus.payment');
            }
        } else {
            JLog::add('Error updating flywire payment infos : callback_id is not correct, could be a malicious attempt', JLog::ERROR, 'com_emundus.payment');
        }

        return $updated;
    }

    /**
     * Find callback_id in emundus_hikashop table and return fnum
     * @param string $callback_id
     * @return string|false
     */
    private function getFnumFromCallbackId($callback_id)
    {
        $fnum = false;

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('fnum, params')
            ->from('#__emundus_hikashop')
            ->where($db->quoteName('params') . ' LIKE "%' . $callback_id . '%"');

        $db->setQuery($query);

        try {
            $fnums = $db->loadObjectList();

            foreach($fnums as $fnum_infos) {
                $params = json_decode($fnum_infos->params, true);
                if (isset($params['uniqId']) && $params['uniqId'] == $callback_id) {
                    $fnum = $fnum_infos->fnum;
                    break;
                }
            }
        } catch (Exception $e) {
            JLog::add('Error getting fnum from callback id : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.payment');
        }

        return $fnum;
    }

    private function checkAmountCoherence($fnum, $amount)
    {
        $correctAmount = false;

        $price = (float)$this->getPrice($fnum);

        if (!empty($price)) {
            $correctAmount = (float)$amount == (float)$price;
            if ($correctAmount === false) {
                JLog::add('Amount sent through callback does not correspond to payment on eMundus platform', JLog::WARNING, 'com_emundus.payment');
            }
        } else {
            JLog::add('Error checking amount coherence : price is empty', JLog::ERROR, 'com_emundus.payment');
        }


        return $correctAmount;
    }

    private function updateHikashopPayment($fnum, $hikashop_status, $data)
    {
        $updated = false;
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from('#__emundus_hikashop')
            ->where('fnum = ' . $db->quote($fnum));
        $db->setQuery($query);

        try {
            $hikashop = $db->loadObject();
        } catch (Exception $e) {
            JLog::add('Error getting hikashop infos from fnum ('. $fnum .') : '. $e, JLog::ERROR, 'com_emundus.payment');
        }

        if (!empty($hikashop)) {
            $query->clear();
            $query->update('#__hikashop_order')
                ->set('order_status = ' . $db->quote($hikashop_status))
                ->set('order_invoice_id = ' . $db->quote($data['id']))
                ->where('order_id = ' . $hikashop->order_id);
            $db->setQuery($query);

            try {
                $updated = $db->execute();
            } catch (Exception $e) {
                JLog::add('Error updating hikashop infos from fnum ('. $fnum .') : '. $e, JLog::ERROR, 'com_emundus.payment');
            }

            if ($updated) {
                $params = json_decode($hikashop->params, true);
                $params['flywire_id'] = $data['id'];
                $params['flywire_status'] = $data['status'];
                $params['flywire_amount'] = $data['amount'];
                $params['flywire_at'] = $data['at'];
                $params['initiator'] = 'flywire';

                $query->clear();
                $query->update('#__emundus_hikashop')
                    ->set('params = ' . $db->quote(json_encode($params)))
                    ->where('fnum = ' . $db->quote($fnum));

                $db->setQuery($query);

                try {
                    $updated = $db->execute();
                } catch (Exception $e) {
                    JLog::add('Error updating hikashop infos from fnum ('. $fnum .') : '. $e, JLog::ERROR, 'com_emundus.payment');
                }
            }
        } else {
            JLog::add('Error updating hikashop infos from fnum ('. $fnum .') : hikashop infos are empty', JLog::ERROR, 'com_emundus.payment');
        }

        return $updated;
    }

    private function updateFnumStateFromFlywire($fnum): bool
    {
        $updated = false;

        require_once JPATH_ROOT . '/components/com_emundus/models/profile.php';
        $m_profiles = new EmundusModelProfile();
        $profile = $m_profiles->getProfileByStatus($fnum);

        if (!empty($profile['menutype'])) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);

            $query->select('modu.id, modu.params')
                ->from('#__modules as modu')
                ->leftJoin('#__modules_menu as modm ON modm.moduleid = modu.id')
                ->leftJoin('#__menu as menu ON menu.id = modm.menuid')
                ->where('menu.menutype = ' . $db->quote($profile['menutype']))
                ->andWhere('modu.module = ' . $db->quote('mod_emundus_payment'))
                ->andWhere('modu.published = 1');

            $db->setQuery($query);

            try {
                $module = $db->loadObject();
            } catch (Exception $e) {
                JLog::add('Error getting module infos from fnum ('. $fnum .') : '. $e, JLog::ERROR, 'com_emundus.payment');
            }

            if (!empty($module)) {
                $params = json_decode($module->params, true);
                $params['payment_methods'] = json_decode($params['payment_methods'], true);
                $flywire_method_index = array_search('flywire', $params['payment_methods']['payment_method']);
                if ($flywire_method_index !== false) {
                    $status = $params['payment_methods']['payment_confirmed_state'][$flywire_method_index];

                    if (isset($status)) {
                        require_once JPATH_ROOT . '/components/com_emundus/models/files.php';
                        $m_files = new EmundusModelFiles();
                        $updated = $m_files->updateState($fnum, $status);
                    }
                }
            }
        } else {
            JLog::add('Error updating fnum state after flywire confirmed payment : profile is empty', JLog::ERROR, 'com_emundus.payment');
        }

        return $updated;
    }

    public function getFlywireExtendedConfig($config)
    {
        JPluginHelper::importPlugin('emundus');
        $dispatcher = JEventDispatcher::getInstance();
        $extended_config = $dispatcher->trigger('callEventHandler', ['extendFlywireConfig', ['config' => $config]]);

        if (!empty($extended_config)) {
            foreach($extended_config[0] as $extend) {
                $config = array_merge($config, $extend);
            }
        }

        return $config;
    }

    /**
     * @param $fnum
     * @return array
     */
    public function getConfig($fnum): array
    {
        $config = array();

        if (!empty($fnum)) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);

            $query->select('params')
                ->from('#__emundus_hikashop')
                ->where('fnum = ' . $db->quote($fnum));
            $db->setQuery($query);

            try {
                $params = $db->loadResult();
            } catch (Exception $e) {
                JLog::add('Error getting hikashop infos from fnum ('. $fnum .') : '. $e, JLog::ERROR, 'com_emundus.payment');
            }

            $config = json_decode($params, true);
            if (!empty($config) && !empty($config['sender_first_name'])) {
                // do nothing
            } else {
                $query->clear()
                    ->select('ju.email, jepd.first_name, jepd.last_name, dc.code_iso_2, jepd.city_1, jepd.telephone_1, jepd.street_1')
                    ->from('#__users as ju')
                    ->leftJoin('#__emundus_campaign_candidature as jecc ON jecc.user_id = ju.id')
                    ->leftJoin('#__emundus_personal_detail as jepd ON jepd.fnum LIKE jecc.fnum')
                    ->leftJoin('data_country AS dc ON dc.id = jepd.country_1')
                    ->where('jecc.fnum LIKE ' . $db->quote($fnum));

                $db->setQuery($query);

                try {
                    $data = $db->loadObject();
                } catch (Exception $e) {
                    JLog::add('Error trying to get peronal_details for payment infos', JLog::ERROR, 'com_emundus.payment');
                }

                if (!empty($data)) {
                    $config = array(
                        "sender_first_name" => $data->first_name,
                        "sender_last_name" => $data->last_name,
                        "sender_email" => $data->email,
                        "sender_address1" => $data->street_1,
                        "sender_city" => $data->city_1,
                        "sender_country" => $data->code_iso_2,
                        "sender_phone" => $data->telephone_1
                    );
                }
            }
        }

        return $config;
    }

    /**
     * @param $fnum string
     * @param $new_config array
     * @return bool
     */
    public function saveConfig($fnum, $new_config): bool
    {
        $saved = false;

        if (!empty($fnum)) {
            $config = $this->getConfig($fnum);

            if (!empty($config)) {
                $new_config = array_merge($config, $new_config);

                $db = JFactory::getDBO();
                $query = $db->getQuery(true);

                $query->update('#__emundus_hikashop')
                    ->set('params = ' . $db->quote(json_encode($new_config)))
                    ->where('fnum = ' . $db->quote($fnum));
                $db->setQuery($query);

                try {
                    $saved = $db->execute();
                } catch (Exception $e) {
                    JLog::add('Error saving hikashop infos from fnum ('. $fnum .') : '. $e, JLog::ERROR, 'com_emundus.payment');
                }
            }
        } else {
            JLog::add('Error saving config : fnum is empty', JLog::ERROR, 'com_emundus.payment');
        }

        return $saved;
    }

    public function updateFileTransferPayment($user)
    {
        $updated = false;

        require_once JPATH_ROOT . '/components/com_emundus/models/profile.php';
        $m_profiles = new EmundusModelProfile();
        $profile = $m_profiles->getProfileByStatus($user->fnum);

        if (!empty($profile['menutype'])) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);

            $query->select('modu.id, modu.params')
                ->from('#__modules as modu')
                ->leftJoin('#__modules_menu as modm ON modm.moduleid = modu.id')
                ->leftJoin('#__menu as menu ON menu.id = modm.menuid')
                ->where('menu.menutype = ' . $db->quote($profile['menutype']))
                ->andWhere('modu.module = ' . $db->quote('mod_emundus_payment'))
                ->andWhere('modu.published = 1');

            $db->setQuery($query);

            try {
                $module = $db->loadObject();
            } catch (Exception $e) {
                JLog::add('Error getting module infos from fnum (' . $user->fnum . ') : ' . $e, JLog::ERROR, 'com_emundus.payment');
            }

            if (!empty($module)) {
                $params = json_decode($module->params, true);
                $params['payment_methods'] = json_decode($params['payment_methods'], true);
                $attachmentId = $params['proof_attachment'];

                $query->clear()
                    ->select('jeu.id')
                    ->from('jos_emundus_uploads AS jeu')
                    ->where('jeu.fnum LIKE ' . $db->quote($user->fnum))
                    ->andWhere('jeu.attachment_id = ' . $attachmentId);

                $db->setQuery($query);

                try {
                    $upload_id = $db->loadResult();
                } catch (Exception $e) {
                    JLog::add('Error getting upload id from fnum (' . $user->fnum . ') : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.payment');
                }

                if (!empty($upload_id)) {
                    // create hikashop order for file
                    $order_number = $params['reference_prefix'] . substr($user->fnum, 8, 6) . $user->id;

                    $query->clear()
                        ->select('id, order_id')
                        ->from($db->quoteName('#__emundus_hikashop'))
                        ->where($db->quoteName('fnum') . ' = ' . $db->quote($user->fnum));

                    $db->setQuery($query);
                    $emundusHikashop = $db->loadObject();

                    if (empty($emundusHikashop->id)) {
                        $created = $this->createPaymentOrder($user->fnum, 'transfer', $order_number);
                    } else {
                        $this->updateHikashopOrderType($emundusHikashop->order_id, 'transfer', $order_number);
                    }

                    $data = [
                        'type' => 'transfer',
                        'date' => time(),
                        'status' => 'sent',
                        'order_number' => $order_number
                    ];

                    $query->clear()
                        ->update($db->quoteName('#__emundus_hikashop'))
                        ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($data)))
                        ->where($db->quoteName('fnum') . ' = ' . $db->quote($user->fnum));
                    $db->setQuery($query);

                    try {
                        $updated = $db->execute();
                    } catch (Exception $e) {
                        JLog::add('Error updating emundus hikashop  (' . $user->fnum . ') with data ' . json_encode($data) . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.payment');
                    }

                    if ($updated) {
                        $transfer_method_index = array_search('transfer', $params['payment_methods']['payment_method']);
                        $status = $params['payment_methods']['payment_confirmed_state'][$transfer_method_index];

                        if (isset($status)) {
                            require_once JPATH_ROOT . '/components/com_emundus/models/files.php';
                            $m_files = new EmundusModelFiles();
                            $updated = $m_files->updateState(array($user->fnum), $status);
                        }
                    } else {
                        JLog::add('Updating emundus hikashop  (' . $user->fnum . ') returned false with data ' . json_encode($data), JLog::WARNING, 'com_emundus.payment');
                    }
                }
            }
        }

        return $updated;
    }

    /**
     * @param $order_id
     * @param $type
     * @return $updated bool
     */
    private function updateHikashopOrderType($order_id, $type, $order_number = null): bool
    {
        $updated = false;

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->clear()
            ->update('#__hikashop_order')
            ->set('order_type = ' . $db->quote($type))
            ->set('order_status = ' . $db->quote('created'));

        if (!empty($order_number)) {
            $query->set('order_number = ' . $db->quote($order_number));
        }

        $query->where('order_id = ' . $order_id);

        $db->setQuery($query);

        try {
            $updated = $db->execute();
        } catch (Exception $e) {
            JLog::add('Error trying to update hikashop order type ' . $e->getMessage(), JLog::ERROR, 'com_emundus.payment');
        }

        return $updated;
    }
}
