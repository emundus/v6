<?php
/**
 * @package        Joomla
 * @subpackage     Events Booking
 * @author         HUBINET Brice
 * @copyright      Copyright (C) 2021 eMundus Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

class plgEventbookingEmundus extends CMSPlugin
{
    /**
     * Database object.
     *
     * @var    JDatabaseDriver
     */
    protected $db;

    /**
     * Constructor
     *
     * @param   object &$subject   The object to observe
     * @param   array   $config    An optional associative array of configuration settings.
     *                             Recognized key values include 'name', 'group', 'params', 'language'
     *                             (this list is not meant to be comprehensive).
     */
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
    }

    /**
     * Method to create Jomsocial account for subscriber and assign him to selected Jomsocial groups when subscription is active
     *
     * @param $row
     *
     * @return bool
     */
    public function onAfterSaveEvent($row, $data, $isNew)
    {
        $user = JFactory::getUser();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        JLog::addLogger(['text_file' => 'com_emundus.eventbooking.error.php'], JLog::ERROR, 'com_emundus');

        $event_id = $row->id;

        try {
            $query->select('id')
                ->from($db->quoteName('#__emundus_setup_campaigns'))
                ->where($db->quoteName('event') . ' = ' . $db->quote($event_id));
            $db->setQuery($query);
            $campaign = $db->loadResult();

            if(empty($campaign)){
                $isNew = true;
            }

            if ($isNew) {
                // Create Hikashop product
                $quantity = $row->event_capacity;
                $price = $row->individual_price;
                $label = $row->title;
                $category = 12;

                if(empty($price)){
                    $price = 0;
                }

                $product_code = 'product-' . $row->id;

                $db = JFactory::getDbo();

                // Create a product
                $query = "INSERT INTO jos_hikashop_product (product_parent_id, product_name, product_description, product_quantity, product_code, product_published, product_hit, product_created, product_sale_start, product_sale_end, product_delay_id, product_tax_id, product_type, product_vendor_id, product_manufacturer_id, product_url, product_weight, product_keywords, product_weight_unit, product_modified, product_meta_description, product_dimension_unit, product_width, product_length, product_height, product_max_per_order, product_access, product_group_after_purchase, product_min_per_order, product_contact, product_display_quantity_field, product_last_seen_date, product_sales, product_waitlist, product_layout, product_average_score, product_total_vote, product_page_title, product_alias, product_price_percentage, product_msrp, product_canonical, product_warehouse_id, product_quantity_layout, product_sort_price, product_description_raw, product_description_type, product_option_method, product_condition) 
                VALUES (0,".$db->quote($label).", '', ".$quantity.", ".$db->quote($product_code).", 1, 0, ".time().", 0, 0, 0, 0, 'main', 0, 0, '', 0.000, '', 'kg', ".time().", '', 'm', 0.000, 0.000, 0.000, 0, 'all', '', 0, 0, 0, 0, 0, 0, '', 0, 0, '', ".$db->quote($product_code).", 0.0000000, ".$price.", '', 0, '', ".$price.", null, null, 0, 'NewCondition')";

                $db->setQuery($query);
                $db->execute();

                $product_id = $db->insertid();

                $query = "INSERT INTO jos_hikashop_product_category (category_id, product_id, ordering) VALUES (".$category.", ".$product_id.", 1)";
                $db->setQuery($query);
                $db->execute();

                $query = "INSERT INTO jos_hikashop_price (price_currency_id, price_product_id, price_value, price_min_quantity, price_access, price_site_id, price_users, price_start_date, price_end_date) VALUES (1, ".$product_id.",".$price.", 0, 'all', '', '', 0, 0)";
                $db->setQuery($query);
                $db->execute();

                // Create campaign
                $query = $db->getQuery(true);
                $query->clear()
                    ->insert($db->quoteName('#__emundus_setup_campaigns'))
                    ->set($db->quoteName('user') . ' = ' . $db->quote($user->id))
                    ->set($db->quoteName('label') . ' = ' . $db->quote($row->title))
                    ->set($db->quoteName('description') . ' = ' . $db->quote($row->description))
                    ->set($db->quoteName('short_description') . ' = ' . $db->quote($row->short_description))
                    ->set($db->quoteName('start_date') . ' = ' . $db->quote($row->registration_start_date))
                    ->set($db->quoteName('end_date') . ' = ' . $db->quote($row->cut_off_date))
                    ->set($db->quoteName('profile_id') . ' = ' . $db->quote(1001))
                    ->set($db->quoteName('training') . ' = ' . $db->quote('prog'))
                    ->set($db->quoteName('year') . ' = ' . $db->quote('2021-2022'))
                    ->set($db->quoteName('published') . ' = ' . $db->quote(1))
                    ->set($db->quoteName('event') . ' = ' . $db->quote($row->id))
                    ->set($db->quoteName('hikashop_product') . ' = ' . $db->quote($product_id));
                $db->setQuery($query);
                $db->execute();

                $campaign = $db->insertid();
            } else {
                $query->clear()
                    ->update($db->quoteName('#__emundus_setup_campaigns'))
                    ->set($db->quoteName('user') . ' = ' . $db->quote($user->id))
                    ->set($db->quoteName('label') . ' = ' . $db->quote($row->title))
                    ->set($db->quoteName('description') . ' = ' . $db->quote($row->description))
                    ->set($db->quoteName('short_description') . ' = ' . $db->quote($row->short_description))
                    ->set($db->quoteName('start_date') . ' = ' . $db->quote($row->registration_start_date))
                    ->set($db->quoteName('end_date') . ' = ' . $db->quote($row->cut_off_date))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($campaign));
                $db->setQuery($query);
                $db->execute();
            }

            $query->clear()
                ->update($db->quoteName('#__eb_events'))
                ->set($db->quoteName('registration_handle_url') . ' = ' . $db->quote(JURI::base() . 'apply?course=prog&cid='.$campaign.'&Itemid=3056'))
                ->where($db->quoteName('id') . ' = ' . $event_id);
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e){
            JLog::add('Error create campaign at event creation : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }

        return true;
    }
}
