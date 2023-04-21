<?php
/**
 * @version 2: emunduscampaign 2019-04-11 Hugo Mor
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All r
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have
 * to the GNU General Public License, and as distr
 * is derivative of works licensed under the GNU G
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and det
 * @description Création de dossier de candidature
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */
include_once(JPATH_BASE.'/components/com_emundus/controllers/messages.php');
class PlgHikashopEmundusPrice extends JPlugin
{

    public function onAfterCartProductsLoad($cart)
    {
        $session = JFactory::getSession();
        $userFnum = $session->get('emundusUser')->fnum;

        $price = $this->selectPrice($userFnum);// Function to find the price by fnum

        if (!empty($price)) {
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

    public function selectPrice($fnum){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $table = $this->params['table'];
        $field = $this->params['element'];
        // Conditions for which records should be updated.
        $conditions = $db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum);

        $query
            ->select($db->quoteName(array($field)))
            ->from($db->quoteName($table))
            ->where($conditions);
        //die($query->__toString());
        $db->setQuery($query);

        return $db->loadResult();
    }
}