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

    public function onBeforeOrderCreate(&$order,&$do)
    {
        JPluginHelper::importPlugin('emundus','custom_event_handler');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopBeforeOrderCreate', ['order' => $order, 'do' => $do]]);
    }

    public function onAfterOrderCreate(&$order)
    {
        JPluginHelper::importPlugin('emundus','custom_event_handler');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopAfterOrderCreate', ['order' => $order]]);

        // We get the emundus payment type from the config
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $em_application_payment = $eMConfig->get('application_payment', 'user');

        $session = JFactory::getSession()->get('emundusUser');
	    $order_id = $order->order_parent_id ?: $order->order_id;

		// find the fnum related to current order (it isn't always the same as the session)
	    $db = JFactory::getDbo();
	    $query = $db->getQuery(true);
	    $query->clear()
		    ->select('order_id')
		    ->from($db->quoteName('#__hikashop_order'))
		    ->where($db->quoteName('order_id') . ' = ' . $order_id .  ' OR ' . $db->quoteName('order_parent_id') . ' = ' . $order_id);
	    $db->setQuery($query);
	    $orders = $db->loadColumn();
	    $orders = empty($orders) ? [$order_id] : $orders;

		$query->clear()
			->select('fnum')
			->from($db->quoteName('#__emundus_hikashop'))
			->where($db->quoteName('order_id') . ' IN (' . implode(',', $orders) . ')');
		$db->setQuery($query);
		$fnum = $db->loadResult();

		if (!empty($fnum)) {
			$user = $session->id;
			require_once (JPATH_SITE.'/components/com_emundus/models/files.php');
			$m_files = new EmundusModelFiles();
			$fnum_infos = $m_files->getFnumInfos($fnum);
			$cid = $fnum_infos['campaign_id'];
			$status = $fnum_infos['status'];
		} else if (!empty($session)) {
            $user = $session->id;
            $fnum = $session->fnum;
            $cid = $session->campaign_id;
            $status = $session->status;
        }
        else {
            JLog::add('Could not get session on order ID nor fnum from order_id. -> '. $order_id, JLog::ERROR, 'com_emundus');
            return false;
        }

        if ($eMConfig->get('hikashop_session')) {
	        $payment_session = JFactory::getSession()->get('emundusPayment', null);
	        if (empty($payment_session->fnum)) {
                $emundus_payment = new StdClass();
                $emundus_payment->user_id = $user;
                $emundus_payment->fnum = $fnum;
                JFactory::getSession()->set('emundusPayment', $emundus_payment);
            }
        }

        $config = hikashop_config();
        $confirmed_statuses = explode(',', trim($config->get('invoice_order_statuses','confirmed,shipped'), ','));
        if (empty($confirmed_statuses)) {
            $confirmed_statuses = array('confirmed','shipped');
        }

	    $query->clear()
		    ->select('*')
		    ->from($db->quoteName('#__emundus_hikashop'));

        switch ($em_application_payment) {
            case 'campaign':
				$query->where($db->quoteName('order_id') . ' IN (' . implode(',', $orders) . ') OR (' . $db->quoteName('campaign_id') . ' = ' . $cid . ' AND ' . $db->quoteName('user') . ' = ' . $user .' ) ');
                break;
            case 'fnum':
				$query->where($db->quoteName('order_id') . ' IN (' . implode(',', $orders) . ') OR ' . $db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));
				break;
            case 'status':
                $query->where($db->quoteName('order_id') . ' IN (' . implode(',', $orders) . ') OR (' . $db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum).' AND '. $db->quoteName('status').' = '.$status.')');
                break;
            case 'user':
            default :
                $query->where($db->quoteName('order_id') . ' IN (' . implode(',', $orders) . ') OR ' . $db->quoteName('user') . ' = ' . $user);
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
        $db         = JFactory::getDbo();

	    if(isset($order->order_parent_id)){
			$order_id = $order->order_parent_id;
		} elseif (isset($order->hikamarket)){
			if(isset($order->hikamarket->parent)){
				$order_id = $order->hikamarket->parent->order_id;
			} else {
				$order_id = $order->order_id;
			}
        } else {
			$order_id = $order->order_id;
		}

        if ($order_id > 0) {
            $query = 'SELECT * FROM #__emundus_hikashop WHERE order_id='.$order_id;
            $db->setQuery($query);

            try {
                $em_order = $db->loadObject();
                if(empty($em_order)){
                    $this->onAfterOrderCreate($order);
                    $query = 'SELECT * FROM #__emundus_hikashop WHERE order_id='.$order_id;
                    $db->setQuery($query);
                    $em_order = $db->loadObject();
                }
                $user = $em_order->user;
                $fnum = $em_order->fnum;
                $cid = $em_order->campaign_id;
                $status = $em_order->status;

            } catch (Exception $exception) {
                JLog::add('Error SQL -> '. preg_replace("/[\r\n]/"," ",$query), JLog::ERROR, 'com_emundus');
                return false;
            }
        }
        else {
            JLog::add('Could not get user session on order ID. -> '. $order_id, JLog::ERROR, 'com_emundus');
            return false;
        }

        $eMConfig = JComponentHelper::getParams('com_emundus');

        if($eMConfig->get('hikashop_session', 0)) {
            if (in_array($order->order_status, ['cancelled', 'confirmed', 'shipped'])) {
                JFactory::getSession()->set('emundusPayment', null);
            }
        }

        $application_payment_status = explode(',', $eMConfig->get('application_payment_status'));
        $status_after_payment = explode(',', $eMConfig->get('status_after_payment'));

        // get the step of paiement
        $key = array_search($status, $application_payment_status);

        $config = hikashop_config();
        $confirmed_statuses = explode(',', trim($config->get('invoice_order_statuses','confirmed,shipped'), ','));

		JPluginHelper::importPlugin('emundus','custom_event_handler');
		\Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopAfterOrderUpdate', ['order' => $order, 'em_order' => $em_order]]);

        if ($status_after_payment[$key] > 0 && in_array($order->order_status, $confirmed_statuses)) {
            require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
            $m_files = new EmundusModelFiles();

			if(!empty($fnum)) {
				$m_files->updateState($fnum, $status_after_payment[$key]);
				JLog::add('Application file status updated to -> ' . $status_after_payment[$key]. ' after order confirmed', JLog::INFO, 'com_emundus');
			}

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
        }
        else {
            $query = 'SELECT * FROM #__hikashop_order WHERE order_id='.$order_id;
            $db->setQuery($query);
            $hika_order = $db->loadObject();

            if(empty($hika_order->order_payment_method)){
                $user = JFactory::getSession()->get('emundusUser');
                require_once (JPATH_SITE . '/components/com_emundus/models/application.php');

                $app = JFactory::getApplication();
                $app->enqueueMessage( JText::_('THANK_YOU_FOR_PURCHASE') );

                $m_application 	= new EmundusModelApplication;
                $redirect = $m_application->getConfirmUrl();

                $app->redirect($redirect);
            }

            JLog::add('Could not set application file status on order ID -> '. $order_id, JLog::ERROR, 'com_emundus');
            return false;
        }

        $this->onAfterOrderCreate($order);
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


    public function onCheckoutWorkflowLoad(&$checkout_workflow, &$shop_closed, $cart_id)
    {
	    JPluginHelper::importPlugin('emundus', 'custom_event_handler');
	    \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopCheckoutWorkflowLoad',
		    ['checkout_workflow' => $checkout_workflow, 'shop_closed' => $shop_closed, 'cart_id' => $cart_id]
	    ]);

	    $eMConfig = JComponentHelper::getParams('com_emundus');
	    if ($eMConfig->get('hikashop_session')) {
		    $session = JFactory::getSession()->get('emundusUser');

		    if (!empty($session) && !empty($session->fnum)) {
			    $app = JFactory::getApplication();
			    $itemId = $app->input->get('Itemid', null,'int');

			    $db = JFactory::getDBO();
			    $query = $db->getQuery(true);

			    $query->select('menutype')
				    ->from('jos_menu')
				    ->where('id = ' . $itemId);

			    try {
				    $db->setQuery($query);
				    $menutype = $db->loadResult();

				    if (!empty($menutype)) {
					    require_once(JPATH_SITE . '/components/com_emundus/models/profile.php');
					    $m_profile = new EmundusModelProfile();
					    $current_profile = $m_profile->getProfileByFnum($session->fnum);

					    if (strpos($menutype, 'menu-profile') !== false && $menutype !== 'menu-profile'.$current_profile) {
						    JLog::add('FNUM ' . $session->fnum  . ' tried to pay product of menu ' . $menutype . ' but its current profile is ' . $current_profile  , JLog::WARNING, 'com_emundus.emundus_hikashop_plugin');
						    $app->enqueueMessage(JText::_('COM_EMUNDUS_WRONG_PRODUCT_FOR_CAMPAIGN'), 'warning');
						    $app->redirect('/');
					    } else {
						    // TODO: is correct product ??
					    }
				    }

			    } catch (Exception $e) {
				    JLog::add('Failed to get menu type associated to user profile ' .  $e->getMessage(), JLog::ERROR, 'com_emundus.emundus_hikashop_plugin');
			    }
		    }
	    }
    }

    public function onBeforeProductListingLoad(&$filters,&$order,&$parent, &$select, &$select2, &$a, &$b, &$on) {
        $app = JFactory::getApplication();

        if(!$app->isAdmin()) {
            JPluginHelper::importPlugin('emundus','custom_event_handler');
            \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopBeforeProductListingLoad',
                ['filters' => $filters, 'order' => $order,'parent' => $parent, 'select' => $select, 'select2' => $select2, 'a' => $a, 'b' => $b, 'on' => $on]
            ]);

            // Nobody can see product list for the moment
            $app->redirect('/');
        }
    }

    public function onAfterCartProductsLoad(&$cart) {
        $params	= JComponentHelper::getParams('com_emundus');
        if ($params->get('hikashop_session')) {
            $payment_session = JFactory::getSession()->get('emundusPayment', null);

            if (empty($payment_session->fnum)) {
                $user = JFactory::getSession()->get('emundusUser');
                $emundus_payment = new StdClass();
                $emundus_payment->user_id = $user->id;
                $emundus_payment->fnum = $user->fnum;

                JFactory::getSession()->set('emundusPayment', $emundus_payment);
            }
        }

        JPluginHelper::importPlugin('emundus','custom_event_handler');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopAfterCartProductsLoad', ['cart' => &$cart]]);
    }

    public function onCheckoutStepList(&$list)
    {
        $list['emundus_return'] = array('name' => 'eMundus - Retour au dossier', 'params' => array('reset_session' => ['name' => JText::_('COM_EMUNDUS_RESET_SESSION_ON_QUIT'), 'type' => 'boolean', 'default' => 0]));
	    JPluginHelper::importPlugin('emundus','custom_event_handler');
	    \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopCheckoutStepList', ['list' => &$list]]);
    }

    public function onCheckoutStepDisplay($layoutName, &$html, &$view, $pos = null, $options = null)
    {
        if ($layoutName != 'emundus_return')
            return;

        $user = JFactory::getSession()->get('emundusUser');
        $layout = '<div><a id="go-back-button" data-fnum="'. $user->fnum . '" class="em-primary-button em-mt-16" style="width:fit-content;" href="' . JUri::base() . 'component/emundus/?task=openfile&fnum=' . $user->fnum . '"><span class="material-icons-outlined">arrow_back</span><span class="em-ml-8">Retour</span></a></div>';

        if ($options['reset_session'] == 1) {
            $layout .= "<script>
                const formData = new FormData();
                const goBack = document.querySelector('#go-back-button');
                formData.append('fnum', goBack.getAttribute('data-fnum'));
                goBack.addEventListener('click', function (e) {
                  e.preventDefault();
                  fetch(window.location.hostname + '/index.php?option=com_emundus&controller=payment&task=resetpaymentsession').then(function(response) {window.location.href = goBack.getAttribute('href');});
                });
            </script>";
        }

        $html .= $layout;

	    JPluginHelper::importPlugin('emundus','custom_event_handler');
	    \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopCheckoutStepDisplay', ['layoutName' => $layoutName, 'html' => &$html]]);
    }

    public function onAfterCheckoutStep($controllerName, &$go_back, $original_go_back, &$controller) {
        $params	= JComponentHelper::getParams('com_emundus');

        if ($params->get('hikashop_session')) {
            $session = JFactory::getSession()->get('emundusUser');
            require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'payment.php');
            $m_payment = new EmundusModelPayment();
            $m_payment->checkPaymentSession($session->fnum, 'onAfterCheckoutStep');
        }

        JPluginHelper::importPlugin('emundus','custom_event_handler');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onHikashopAfterCheckoutStep', ['controllerName' => $controllerName, 'go_back' => &$go_back, 'original_go_back' => $original_go_back, 'controller' => &$controller]]);
    }
}
