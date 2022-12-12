<?php
/**
 * @version 1: ExceliaPrice 2019-10-30 James Dean
 * @author  James Dean
 * @package Hikashop
 * @copyright Copyright (C) 2018 emundus.fr. All r
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have
 * to the GNU General Public License, and as distr
 * is derivative of works licensed under the GNU G
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and det
 * @description Sets the price to 0 depending if the excelia user exists
 */
// No direct access
defined('_JEXEC') or die('Restricted access');


class PlgHikashopEmundus_hikashop extends JPlugin {

    function __construct(&$subject, $config) {
        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.emundus_hikashop_plugin.php'), JLog::ALL, array('com_emundus'));
        parent::__construct($subject, $config);
    }

    public function onBeforeOrderCreate(&$order, &$do) {
        JPluginHelper::importPlugin('emundus','custom_event_handler');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopBeforeOrderCreate', ['order' => $order, 'do' => $do]]);

        $app = JFactory::getApplication();
        $valid_session = $this->checkHikashopSession();

        if (!$valid_session) {
            $do = false;
            $app->enqueueMessage(JText::_('ANOTHER_HIKASHOP_SESSION_OPENED'), 'error');
            $app->redirect('index.php');
            exit;
        }
    }

    public function onAfterOrderCreate(&$order){
        JPluginHelper::importPlugin('emundus','custom_event_handler');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopAfterOrderCreate', ['order' => $order]]);

        // We get the emundus payment type from the config
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $em_application_payment = $eMConfig->get('application_payment', 'user');

        $session = JFactory::getSession()->get('emundusUser');
        $order_id = $order->order_parent_id ?: $order->order_id;

        if (!empty($session)) {
            $user = $session->id;
            $fnum = $session->fnum;
            $cid = $session->campaign_id;
            $status = $session->status;
        }
        else {
            JLog::add('Could not get session on order ID. -> '. $order_id, JLog::ERROR, 'com_emundus');
            return false;
        }

        $db = JFactory::getDbo();
        $config = hikashop_config();
        $confirmed_statuses = explode(',', trim($config->get('invoice_order_statuses','confirmed,shipped'), ','));

        if(empty($confirmed_statuses)) {
            $confirmed_statuses = array('confirmed','shipped');
        }

        $query = $db->getQuery(true);

        switch ($em_application_payment) {

            case 'campaign':
                $query
                    ->clear()
                    ->select('*')
                    ->from($db->quoteName('#__emundus_hikashop'))
                    ->where($db->quoteName('order_id') . ' = ' . $order_id . ' OR (' . $db->quoteName('campaign_id') . ' = ' . $cid . ' AND ' . $db->quoteName('user') . ' = ' . $user .' ) ');
                break;

            case 'fnum':
                $query
                    ->clear()
                    ->select('*')
                    ->from($db->quoteName('#__emundus_hikashop'))
                    ->where($db->quoteName('order_id') . ' = ' . $order_id . ' OR ' . $db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));
                break;

            case 'status':
                $query
                    ->clear()
                    ->select('*')
                    ->from($db->quoteName('#__emundus_hikashop'))
                    ->where($db->quoteName('order_id') . ' = ' . $order_id . ' OR (' . $db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum).' AND '. $db->quoteName('status').' = '.$status.')');
                break;

            case 'user':
            default :
                $query
                    ->clear()
                    ->select('*')
                    ->from($db->quoteName('#__emundus_hikashop'))
                    ->where($db->quoteName('order_id') . ' = ' . $order_id . ' OR ' . $db->quoteName('user') . ' = ' . $user);
                break;

        }
        try {
            $db->setQuery($query);

            $em_hikas = $db->loadObjectList();
            $em_hika = $em_hikas[sizeof($em_hikas)-1];

            if(empty($em_hika)) {

                $columns = ['user', 'fnum', 'campaign_id', 'order_id', 'status'];
                $values = [$user, $db->quote($fnum), $cid, $order_id, $status];

                $query
                    ->clear()
                    ->insert($db->quoteName('#__emundus_hikashop'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));

                $db->setQuery($query);

            } else {
                JLog::add('Updating Order '. $order_id .' update -> '. preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::INFO, 'com_emundus');

                $fields = array(
                    $db->quoteName('order_id') . ' = ' . $db->quote($order_id)
                );

                $update_conditions = array(
                    $db->quoteName('id') . ' = ' . $em_hika->id
                );

                // Prepare the insert query.
                $query
                    ->clear()
                    ->update($db->quoteName('#__emundus_hikashop'))
                    ->set($fields)
                    ->where($update_conditions);

                $db->setQuery($query);
            }

            $res = $db->execute();

            if ($res) {
                JLog::add('Order '. $order_id .' update -> '. preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::INFO, 'com_emundus');
                return true;
            }
            return $res;

        } catch (Exception $exception) {
            JLog::add('Error SQL -> '. preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function onBeforeOrderUpdate(&$order,&$do)
    {
        JPluginHelper::importPlugin('emundus','custom_event_handler');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopBeforeOrderUpdate', ['order' => $order, 'do' => $do]]);
    }

    public function onAfterOrderUpdate(&$order) {
        $status_reset_session = ['cancelled', 'confirmed'];
        if (in_array($order['order_status'], $status_reset_session)) {
            JFactory::getSession()->set('emundusHikashopUser', null);
        }
        $db         = JFactory::getDbo();
        $order_id = $order->order_parent_id ?: $order->order_id;

        if ($order_id > 0) {
            $query = 'SELECT * FROM #__emundus_hikashop WHERE order_id=' . $order_id;
            $db->setQuery($query);

            try {
                $em_order = $db->loadObject();
                if (empty($em_order)) {
                    $this->onAfterOrderCreate($order);
                    $query = 'SELECT * FROM #__emundus_hikashop WHERE order_id=' . $order_id;
                    $db->setQuery($query);
                    $em_order = $db->loadObject();
                }
                $user = $em_order->user;
                $fnum = $em_order->fnum;
                $cid = $em_order->campaign_id;
                $status = $em_order->status;

            } catch (Exception $exception) {
                JLog::add('Error SQL -> ' . preg_replace("/[\r\n]/", " ", $query), JLog::ERROR, 'com_emundus');
                return false;
            }
        } else {
            JLog::add('Could not get user session on order ID. -> ' . $order_id, JLog::ERROR, 'com_emundus');
            return false;
        }

        $eMConfig = JComponentHelper::getParams('com_emundus');

        $application_payment_status = explode(',', $eMConfig->get('application_payment_status'));
        $status_after_payment = explode(',', $eMConfig->get('status_after_payment'));

        // get the step of paiement
        $key = array_search($status, $application_payment_status);

        $config = hikashop_config();
        $confirmed_statuses = explode(',', trim($config->get('invoice_order_statuses', 'confirmed,shipped'), ','));

        if ($status_after_payment[$key] > 0 && in_array($order->order_status, $confirmed_statuses)) {
            require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
            $m_files = new EmundusModelFiles();
            $m_files->updateState($fnum, $status_after_payment[$key]);
            JLog::add('Application file status updated to -> ' . $status_after_payment[$key], JLog::ERROR, 'com_emundus');

            $query = $db->getQuery(true);
            $query->update('#__emundus_campaign_candidature')
                ->set('submitted = 1')
                ->where('fnum LIKE ' . $db->quote($fnum));

            try {
                $db->setQuery($query);
                $db->execute();
            } catch (Exception $e) {
                JLog::add('Failed to update file submitted after payment ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            }
        } else {
            $query = 'SELECT * FROM #__hikashop_order WHERE order_id=' . $order_id;
            $db->setQuery($query);
            $hika_order = $db->loadObject();

            if (empty($hika_order->order_payment_method)) {
                $user = JFactory::getSession()->get('emundusUser');
                require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'application.php');

                $app = JFactory::getApplication();
                $app->enqueueMessage(JText::_('THANK_YOU_FOR_PURCHASE'));

                $m_application = new EmundusModelApplication;
                $redirect = $m_application->getConfirmUrl();

                $app->redirect($redirect);
            }

            JLog::add('Could not set application file status on order ID -> ' . $order_id, JLog::ERROR, 'com_emundus');
            return false;
        }

        JPluginHelper::importPlugin('emundus','custom_event_handler');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopAfterOrderUpdate', ['order' => $order, 'em_order' => $em_order]]);

        $this->onAfterOrderCreate($order);
    }

    public function onBeforeCartSave(&$element,&$do) {
        $app = JFactory::getApplication();

        if (!$this->checkHikashopSession()) {
            $do = false;
            $app->enqueueMessage(JText::_('ANOTHER_HIKASHOP_SESSION_OPENED'), 'error');
            $app->redirect('index.php');
            exit;
        }
    }

    public function onHikashopBeforeDisplayView(&$view)
    {
        if (!$this->checkHikashopSession()) {
            $app = JFactory::getApplication();
            $app->enqueueMessage(JText::_('ANOTHER_HIKASHOP_SESSION_OPENED'), 'error');
            $app->redirect('index.php');
            exit;
        }
        $user = JFactory::getSession()->get('emundusUser');
        echo '<h3 style="margin: 16px 0;">Récapitulatif</h3><p>Vous êtes sur le paiement de votre dossier ' . $user->fnum . ' de la campagne ' . $user->campaign_name . '</p>
        <form id="quitPaymentForm" style="margin: 16px 0;">
            <input id="goBackToForms" class="btn btn-primary" type="button" value="Revenir au formulaire">
            <input id="goBackToHomepage" class="btn btn-primary" type="button" value="Revenir à la page d\'accueil" onclick="window.location.assign(window.location.origin)">
        </form>
        <script>
            const fnum = "' . $user->fnum  . '";
            const url = window.location.origin + \'/component/emundus/?task=openfile&fnum=\' + fnum;
            document.querySelector("#goBackToForms").addEventListener("click", (e) => {
                e.preventDefault();
                
                xhr = new XMLHttpRequest();
                xhr.open("POST", "index.php?option=com_emundus&controller=payment&task=resetHikashopSession", true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4) {
                        if (xhr.status == 200) {
                            window.location.assign(url);
                        }
                    }
                };
                xhr.send();
            });
            
            document.querySelector("#goBackToHomepage").addEventListener("click", (e) => {
                e.preventDefault();
                
                xhr = new XMLHttpRequest();
                xhr.open("POST", "index.php?option=com_emundus&controller=payment&task=resetHikashopSession", true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4) {
                        if (xhr.status == 200) {
                            window.location.assign(window.location.origin);
                        }
                    }
                };
                xhr.send();
            });
        </script>';
    }

    private function checkHikashopSession(): bool
    {
        $valid_session = true;

        $hikashop_user = JFactory::getSession()->get('emundusHikashopUser');
        $user = JFactory::getSession()->get('emundusUser');

        if (empty($hikashop_user)) {
            $hikashop_user = new StdClass();
            $hikashop_user->user_id = $user->id;
            $hikashop_user->fnum = $user->fnum;

            JFactory::getSession()->set('emundusHikashopUser', $hikashop_user);
        } else if ($hikashop_user->fnum != $user->fnum) {
            $user->fnum = $hikashop_user->fnum;
            JFactory::getSession()->set('emundusUser', $user);
            $valid_session = false;
        }

        return $valid_session;
    }

    public function onAfterOrderConfirm(&$order,&$methods,$method_id)
    {
        JPluginHelper::importPlugin('emundus','custom_event_handler');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopAfterOrderConfirm',
            ['order' => $order, 'methods' => $methods, 'method_id' => $method_id]
        ]);
    }

    public function onAfterOrderDelete($elements)
    {
        JPluginHelper::importPlugin('emundus','custom_event_handler');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopAfterOrderDelete', ['elements' => $elements]]);
    }


    public function onCheckoutWorkflowLoad(&$checkout_workflow, &$shop_closed, $cart_id) {
        JPluginHelper::importPlugin('emundus','custom_event_handler');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopCheckoutWorkflowLoad',
            ['checkout_workflow' => $checkout_workflow, 'shop_closed' => $shop_closed, 'cart_id' => $cart_id]
        ]);
    }

    public function onBeforeProductListingLoad(&$filters,&$order,&$parent, &$select, &$select2, &$a, &$b, &$on) {
        JPluginHelper::importPlugin('emundus','custom_event_handler');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopBeforeProductListingLoad',
            ['filters' => $filters, 'order' => $order,'parent' => $parent, 'select' => $select, 'select2' => $select2, 'a' => $a, 'b' => $b, 'on' => $on]
        ]);

        // Nobody can see product list for the moment
        $app = JFactory::getApplication();
        $app->redirect('/');
    }
}
