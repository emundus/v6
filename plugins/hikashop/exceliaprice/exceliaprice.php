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


include_once(JPATH_BASE.'/components/com_emundus/controllers/messages.php');
class PlgHikashopExceliaPrice extends JPlugin
{

    public function onAfterCartProductsLoad($cart)
    {
        $session = JFactory::getSession();
        $userFnum = $session->get('emundusUser')->fnum;



        $price = $this->params['price'];

        if ($this->getUser($userFnum)) {
            foreach ($cart->cart_products as $cartProduct_id => $cartProduct){
                $cart->cart_products[$cartProduct_id]->cart_product_ref_price=$price;
            }

            foreach ($cart->products as $product_id => $product){
                //change the unit price of the product
                $cart->products[$product_id]->product_sort_price=$price;
                $cart->products[$product_id]->prices[0]->price_value=$price;
                $cart->products[$product_id]->prices[0]->unit_price->price_value=$price;
                //change the unit price with tax
                $cart->products[$product_id]->prices[0]->price_value_with_tax=$price;
                $cart->products[$product_id]->prices[0]->unit_price->price_value_with_tax=$price;
            }
            // Change the total price
            $cart->full_total->prices[0]->price_value = $price;
            $cart->full_total->prices[0]->price_value_with_tax = $price;
        }

    }


    public function getUser($fnum){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $table = $this->params['get_table'];
        $field = $this->params['get_element'];

        $conditions = $db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum);

        $query
            ->select($db->quoteName(array($field)))
            ->from($db->quoteName($table))
            ->where($conditions);
        
        $db->setQuery($query);


        if (!empty($db->loadResult())) {
            $compare_table = $this->params['compare_table'];
            $compare_field = $this->params['compare_element'];

            $conditions =  $db->quoteName($compare_field) . ' LIKE ' . $db->quote($db->loadResult());

            $conditions .= !empty($this->params['publish']) ? ' AND ' . $db->quoteName('published') . ' = 1' : '';

            $query
                ->clear()
                ->select($db->quoteName('id'))
                ->from($db->quoteName($compare_table))
                ->where($conditions);
            $db->setQuery($query);

            if (!empty($db->loadResult())) {
                return true;
            }

            return false;
        }

        return false;
    }

}