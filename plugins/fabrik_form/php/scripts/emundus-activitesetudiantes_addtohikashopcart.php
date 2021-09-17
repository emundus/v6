<?php
    $mainframe = JFactory::getApplication();
    $jinput = JFactory::getApplication()->input;

    $current_user = JFactory::getSession()->get('emundusUser');
    $fnum = $jinput->get('rowid');

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_hikashop' . DS . 'helpers' . DS . 'helper.php');
    require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
    $m_files = new EmundusModelFiles();
    $cartClass = hikashop_get('class.cart');

    jimport('joomla.log.log');
    JLog::addLogger(['text_file' => 'com_emundus.hikashopAddToCart.php'], JLog::ALL, ['com_emundus_hikashopAddToCart']);

    $fnumInfos = $m_files->getFnumInfos($fnum);

    // Get Hikashop user
    $query
        ->select('user_id')
        ->from($db->quoteName('#__hikashop_user'))
        ->where($db->quoteName('user_cms_id') . ' = ' . $current_user->id);

    try {
        $db->setQuery($query);
        $hikashop_user = $db->loadResult();
    } catch (Exception $e) {
        JLog::add('plugin/fabrik_form/php/scripts/emundus-activitesetudiantes_addtohikashopcart error :'.$query->__toString().' : '.$e->getMessage(), JLog::ERROR, 'com_emundus_hikashopAddToCart');
    }

    // Get existing order of the file
    $query
        ->clear()
        ->select('id')
        ->from($db->quoteName('#__emundus_hikashop','eh'))
        ->where($db->quoteName('eh.fnum') . ' = ' . $db->quote($fnum));

    try {
        $db->setQuery($query);
        $emundus_order = $db->loadResult();
    } catch (Exception $e) {
        JLog::add('plugin/fabrik_form/php/scripts/emundus-activitesetudiantes_addtohikashopcart error :'.$query->__toString().' : '.$e->getMessage(), JLog::ERROR, 'com_emundus_hikashopAddToCart');
    }

    if (!empty($emundus_order)) {
        $query
            ->clear()
            ->select('ho.order_id')
            ->from($db->quoteName('#__hikashop_order','ho'))
            ->leftJoin($db->quoteName('#__emundus_hikashop', 'eh') . ' ON ' . $db->quoteName('eh.order_id') . ' = ' . $db->quoteName('ho.order_id'))
            ->where($db->quoteName('eh.fnum') . ' = ' . $db->quote($fnum))
            ->andWhere($db->quoteName('ho.order_status') . ' = ' . $db->quote('confirmed'));

        try {
            $db->setQuery($query);
            $order_confirmed = $db->loadResult();
        } catch (Exception $e) {
            JLog::add('plugin/fabrik_form/php/scripts/emundus-activitesetudiantes_addtohikashopcart error :'.$query->__toString().' : '.$e->getMessage(), JLog::ERROR, 'com_emundus_hikashopAddToCart');
        }

        if(empty($order_confirmed)) {
            // Get existing cart
            $query
                ->clear()
                ->select('hc.cart_id')
                ->from($db->quoteName('#__emundus_hikashop', 'eh'))
                ->leftJoin($db->quoteName('#__hikashop_cart', 'hc') . ' ON ' . $db->quoteName('hc.cart_id') . ' = ' . $db->quoteName('eh.cart_id'))
                ->where($db->quoteName('eh.fnum') . ' = ' . $db->quote($fnum))
                ->andWhere($db->quoteName('eh.cart_id') . ' IS NOT NULL');

            try {
                $db->setQuery($query);
                $emundus_cart = $db->loadResult();
            } catch (Exception $e) {
                JLog::add('plugin/fabrik_form/php/scripts/emundus-activitesetudiantes_addtohikashopcart error :'.$query->__toString().' : '.$e->getMessage(), JLog::ERROR, 'com_emundus_hikashopAddToCart');
            }

            if (empty($emundus_cart)) {
                // Create a new cart
                $cart = new stdClass;
                $cart->cart_name = $fnum;

                $emundus_cart = $cartClass->save($cart);

                $query
                    ->clear()
                    ->update($db->quoteName('#__emundus_hikashop'))
                    ->set($db->quoteName('cart_id') . ' = ' . $db->quote($emundus_cart))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($emundus_order));

                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (Exception $e) {
                    JLog::add('plugin/fabrik_form/php/scripts/emundus-activitesetudiantes_addtohikashopcart error :'.$query->__toString().' : '.$e->getMessage(), JLog::ERROR, 'com_emundus_hikashopAddToCart');
                }
            }

            $query
                ->clear()
                ->select('count(cart_product_id)')
                ->from($db->quoteName('#__hikashop_cart_product'))
                ->where($db->quoteName('cart_id') . ' = ' . $db->quote($emundus_cart))
                ->andWhere($db->quoteName('product_id') . ' = ' . $db->quote($fnumInfos['hikashop_product']));

            try {
                $db->setQuery($query);
                $already_added = $db->loadResult();
            } catch (Exception $e) {
                JLog::add('plugin/fabrik_form/php/scripts/emundus-activitesetudiantes_addtohikashopcart error :'.$query->__toString().' : '.$e->getMessage(), JLog::ERROR, 'com_emundus_hikashopAddToCart');
            }

            if ($already_added == 0) {
                $dateTime = new DateTime();

                $query = "INSERT INTO jos_hikashop_cart_product (cart_id, product_id, cart_product_quantity, cart_product_parent_id, cart_product_modified, cart_product_option_parent_id, cart_product_wishlist_id, cart_product_wishlist_product_id, cart_product_ref_price) 
            VALUES (" . $emundus_cart . "," . (int)$fnumInfos['hikashop_product'] . ", 1, 0, " . $dateTime->getTimestamp() . ", 0, 0, 0, null)";

                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (Exception $e) {
                    JLog::add('plugin/fabrik_form/php/scripts/emundus-activitesetudiantes_addtohikashopcart error :'.$query.' : '.$e->getMessage(), JLog::ERROR, 'com_emundus_hikashopAddToCart');
                }
            }
        }
    } else {
        // Create the cart
        $cart = new stdClass;
        $cart->cart_name = $fnum;

        $emundus_cart = $cartClass->save($cart);

        $columns = ['user', 'fnum', 'campaign_id', 'status', 'cart_id'];
        $values = [$current_user->id, $db->quote($fnum), $fnumInfos['id'], $fnumInfos['status'], $emundus_cart];

        $query
            ->clear()
            ->insert($db->quoteName('#__emundus_hikashop'))
            ->columns($columns)
            ->values(implode(',', $values));

        try {
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e) {
            JLog::add('plugin/fabrik_form/php/scripts/emundus-activitesetudiantes_addtohikashopcart error :'.$query->__toString().' : '.$e->getMessage(), JLog::ERROR, 'com_emundus_hikashopAddToCart');
        }

        $dateTime = new DateTime();

        $query = "INSERT INTO jos_hikashop_cart_product (cart_id, product_id, cart_product_quantity, cart_product_parent_id, cart_product_modified, cart_product_option_parent_id, cart_product_wishlist_id, cart_product_wishlist_product_id, cart_product_ref_price) 
        VALUES (" . $emundus_cart . "," . (int)$fnumInfos['hikashop_product'] . ", 1, 0, " . $dateTime->getTimestamp() . ", 0, 0, 0, null)";

        try {
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e) {
            JLog::add('plugin/fabrik_form/php/scripts/emundus-activitesetudiantes_addtohikashopcart error :'.$query.' : '.$e->getMessage(), JLog::ERROR, 'com_emundus_hikashopAddToCart');
        }
    }

    if (!empty($emundus_cart)) {

        $query = $db->getQuery(true);
        // Update current_cart
        $query
            ->clear()
            ->update($db->quoteName('#__hikashop_cart'))
            ->set($db->quoteName('cart_current') . ' = 0')
            ->where($db->quoteName('user_id') . ' = ' . $db->quote($hikashop_user));

        try {
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e) {
            JLog::add('plugin/fabrik_form/php/scripts/emundus-activitesetudiantes_addtohikashopcart error :'.$query.' : '.$e->getMessage(), JLog::ERROR, 'com_emundus_hikashopAddToCart');
        }

        $query
            ->clear()
            ->update($db->quoteName('#__hikashop_cart'))
            ->set($db->quoteName('cart_current') . ' = 1')
            ->where($db->quoteName('cart_id') . ' = ' . $db->quote($emundus_cart));

        try {
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e) {
            JLog::add('plugin/fabrik_form/php/scripts/emundus-activitesetudiantes_addtohikashopcart error :'.$query.' : '.$e->getMessage(), JLog::ERROR, 'com_emundus_hikashopAddToCart');
        }
    }
