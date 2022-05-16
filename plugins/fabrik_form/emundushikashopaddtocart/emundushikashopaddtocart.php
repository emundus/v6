<?php
/**
 * @version 2: emundusisapplicationsent 2018-12-04 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Locks access to a file if the file is not of a certain status.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */

class PlgFabrik_FormEmundushikashopaddtocart extends plgFabrik_Form {


    /**
     * Status field
     *
     * @var  string
     */
    protected $URLfield = '';

    /**
     * Get the fields value regardless of whether its in joined data or no
     *
     * @param   string  $pname    Params property name to get the value for
     * @param   array   $data     Posted form data
     * @param   mixed   $default  Default value
     *
     * @return  mixed  value
     */
    public function getParam($pname, $default = '') {
        $params = $this->getParams();

        if ($params->get($pname) == '') {
            return $default;
        }

        return $params->get($pname);
    }

    /**
     * Main script.
     *
     * @return  bool
     */
    public function onBeforeProcess() {

        $mainframe = JFactory::getApplication();
        $jinput = JFactory::getApplication()->input;

        $current_user = JFactory::getUser();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if (!$mainframe->isAdmin()) {
            try {
                require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_hikashop' . DS . 'helpers' . DS . 'helper.php');
                require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
                $m_files = new EmundusModelFiles();
                $cartClass = hikashop_get('class.cart');

                jimport('joomla.log.log');
                JLog::addLogger(['text_file' => 'com_emundus.hikashopAddToCart.php'], JLog::ALL, ['com_emundus']);

                $elts = explode(',', $this->getParam('elts_to_check'));

                // Get datas and table
                $data = $this->getProcessData();
                $query->select('db_table_name')
                    ->from($db->quoteName('#__fabrik_lists'))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($data['listid']));
                $db->setQuery($query);
                $table = $db->loadResult();
                //

                // Get Hikashop user
                $query->select('user_id')
                    ->from($db->quoteName('#__hikashop_user'))
                    ->where($db->quoteName('user_cms_id') . ' = ' . $current_user->id);
                $db->setQuery($query);
                $hikashop_user = $db->loadResult();
                //

                $fnum = $data[$table . '___fnum_raw'];
                $fnumInfos = $m_files->getFnumInfos($fnum);

                // Get old orders
                $query->clear()
                    ->select('GROUP_CONCAT(hop.product_id) as old_products,eh.id,eh.order_id,ho.order_status')
                    ->from($db->quoteName('#__emundus_hikashop','eh'))
                    ->leftJoin($db->quoteName('#__hikashop_order_product','hop').' ON '.$db->quoteName('hop.order_id').' = '.$db->quoteName('eh.order_id'))
                    ->leftJoin($db->quoteName('#__hikashop_product_category','hp').' ON '.$db->quoteName('hp.product_id').' = '.$db->quoteName('hop.product_id'))
                    ->leftJoin($db->quoteName('#__hikashop_order','ho').' ON '.$db->quoteName('ho.order_id').' = '.$db->quoteName('eh.order_id'))
                    ->where($db->quoteName('eh.fnum') . ' = ' . $db->quote($fnum))
                    ->andWhere($db->quoteName('eh.status') . ' = ' . $fnumInfos['status']);
                $db->setQuery($query);
                $emundus_order = $db->loadObject();
                //

                if(!empty($emundus_order->id) && $emundus_order->order_status != 'confirmed') {
                    // Get existing carts
                    $query->clear()
                        ->select('hc.cart_id')
                        ->from($db->quoteName('#__emundus_hikashop', 'eh'))
                        ->leftJoin($db->quoteName('#__hikashop_cart', 'hc') . ' ON ' . $db->quoteName('hc.cart_id') . ' = ' . $db->quoteName('eh.cart_id'))
                        ->where($db->quoteName('eh.fnum') . ' = ' . $db->quote($fnum))
                        ->andWhere($db->quoteName('eh.cart_id') . ' IS NOT NULL')
                        ->andWhere($db->quoteName('eh.status') . ' = ' . $fnumInfos['status']);
                    $db->setQuery($query);
                    $emundus_cart = $db->loadResult();
                    //

                    // If no cart existing create it
                    if (empty($emundus_cart)) {
                        $cart = new stdClass;
                        $cart->cart_name = 'Cart of fnum ' . $fnum;

                        $emundus_cart = $cartClass->save($cart);
                        //$cart_id = $cartClass->getCurrentCartId();

                        $query->clear()
                            ->update($db->quoteName('#__emundus_hikashop'))
                            ->set($db->quoteName('cart_id') . ' = ' . $db->quote($emundus_cart))
                            ->set($db->quoteName('order_id') . ' = ' . $db->quote(null))
                            ->where($db->quoteName('id') . ' = ' . $db->quote($emundus_order->id));
                        $db->setQuery($query);
                        $db->execute();
                    } else {
                        $query->clear()
                            ->update($db->quoteName('#__emundus_hikashop'))
                            ->set($db->quoteName('order_id') . ' = ' . $db->quote(null))
                            ->where($db->quoteName('id') . ' = ' . $db->quote($emundus_order->id));
                        $db->setQuery($query);
                        $db->execute();
                    }
                } else {
                    // Create the cart
                    $cart = new stdClass;
                    $cart->cart_name = $fnum;

                    $emundus_cart = $cartClass->save($cart);

                    $columns = ['user', 'fnum', 'campaign_id', 'status', 'cart_id'];
                    $values = [$current_user->id, $db->quote($fnum), $fnumInfos['id'], $fnumInfos['status'], $emundus_cart];

                    $query->clear()
                        ->insert($db->quoteName('#__emundus_hikashop'))
                        ->columns($columns)
                        ->values(implode(',', $values));
                    $db->setQuery($query);
                    $db->execute();
                }

                if(!empty($emundus_cart)) {
                    // Update current_cart
                    $query->clear()
                        ->update($db->quoteName('#__hikashop_cart'))
                        ->set($db->quoteName('cart_current') . ' = 0')
                        ->where($db->quoteName('cart_id') . ' = ' . $db->quote($cartClass->getCurrentCartId()));
                    $db->setQuery($query);
                    $db->execute();

                    $query->clear()
                        ->update($db->quoteName('#__hikashop_cart'))
                        ->set($db->quoteName('cart_current') . ' = 1')
                        ->where($db->quoteName('cart_id') . ' = ' . $db->quote($emundus_cart));
                    $db->setQuery($query);
                    $db->execute();
                }

                $query->select('product_id')
                    ->from($db->quoteName('#__hikashop_cart_product'))
                    ->where($db->quoteName('cart_id') . ' = ' . $db->quote($emundus_cart));
                $db->setQuery($query);
                $products_in_cart = $db->loadColumn();

                $products = array();

                $index_p = 0;
                foreach ($elts as $key => $elt) {
                    $query->clear()
                        ->select('table_join,table_join_key')
                        ->from($db->quoteName('#__fabrik_joins'))
                        ->where($db->quoteName('table_key') . ' = ' . $db->quote(explode('___',$elt)[1]))
                        ->andWhere($db->quoteName('join_from_table') . ' = ' . $db->quote(explode('___',$elt)[0]));
                    $db->setQuery($query);
                    $table_join = $db->loadObject();

                    if(empty($table_join)){
                        $query->clear()
                            ->select('table_join,table_join_key')
                            ->from($db->quoteName('#__fabrik_joins'))
                            ->where($db->quoteName('table_key') . ' = ' . $db->quote(explode('___',$elt)[1]));
                        $db->setQuery($query);
                        $table_join = $db->loadObject();
                    }

                    if(!empty($table_join)){
                        if($table_join->table_join_key == 'parent_id'){
                            $prid = explode('___',$elt)[1];
                        } else {
                            $prid = $table_join->table_join_key;
                        }

                        $query->clear()
                            ->select('distinct ' . $prid)
                            ->from($db->quoteName($table_join->table_join));
                        $db->setQuery($query);
                        $options = $db->loadColumn();

                        foreach ($options as $option){
                            if(in_array($option,$products_in_cart)){
                                $query->clear()
                                    ->delete('#__hikashop_cart_product')
                                    ->where($db->quoteName('cart_id') . ' = ' . $db->quote($emundus_cart))
                                    ->andWhere($db->quoteName('product_id') . ' = ' . $db->quote($option));
                                $db->setQuery($query);
                                $db->execute();
                            }
                        }
                    }

                    $data_elt_raw = $elt . '_raw';
                    // Add to products
                    if (in_array($data_elt_raw, array_keys($data))) {
                        if(is_array($data[$data_elt_raw])) {
                            foreach ($data[$data_elt_raw] as $product) {
                                if (!empty($product) && !in_array((int)$product, explode(',',$emundus_order->old_products))) {
                                    $products[$index_p]['id'] = (int)$product;
                                    $products[$index_p]['qty'] = 1;
                                    $index_p++;
                                }
                            }
                        } else {
                            if (!empty($data[$data_elt_raw]) && !in_array((int)$data[$data_elt_raw], explode(',',$emundus_order->old_products))) {
                                $products[$index_p]['id'] = (int)$data[$data_elt_raw];
                                $products[$index_p]['qty'] = 1;
                                $index_p++;
                            }
                        }
                    }
                    //
                }

                $result = $cartClass->addProduct((int)$emundus_cart, $products);
                // Create order
                //
            } catch (Exception $e){
                JLog::add('plugins/fabrik_forms/emundushikashopaddtocart/emundushikashopaddtocart.php | Cannot add products to cart : ' . preg_replace("/[\r\n]/"," ",$e->getMessage()), JLog::ERROR, 'com_emundus');
                return $e->getMessage();
            }
        }
        return true;
    }
}
