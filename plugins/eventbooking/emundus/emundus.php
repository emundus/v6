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
        $custom_fields = json_decode($row->custom_fields);
        $save_quit = $row->second_reminder_frequency;

        $redirect_url = '';

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
                $label = $row->title;

                // Create Hikashop product
                /*$quantity = $row->event_capacity;
                $price = $row->individual_price;
                $category = 12;

                if(!empty($price)) {
                    $product_code = 'product-' . $row->id;

                    // Create a product
                    $query = "INSERT INTO jos_hikashop_product (product_parent_id, product_name, product_description, product_quantity, product_code, product_published, product_hit, product_created, product_sale_start, product_sale_end, product_delay_id, product_tax_id, product_type, product_vendor_id, product_manufacturer_id, product_url, product_weight, product_keywords, product_weight_unit, product_modified, product_meta_description, product_dimension_unit, product_width, product_length, product_height, product_max_per_order, product_access, product_group_after_purchase, product_min_per_order, product_contact, product_display_quantity_field, product_last_seen_date, product_sales, product_waitlist, product_layout, product_average_score, product_total_vote, product_page_title, product_alias, product_price_percentage, product_msrp, product_canonical, product_warehouse_id, product_quantity_layout, product_sort_price, product_description_raw, product_description_type, product_option_method, product_condition)
                VALUES (0," . $db->quote($label) . ", '', " . $quantity . ", " . $db->quote($product_code) . ", 1, 0, " . time() . ", 0, 0, 0, 0, 'main', 0, 0, '', 0.000, '', 'kg', " . time() . ", '', 'm', 0.000, 0.000, 0.000, 0, 'all', '', 0, 0, 0, 0, 0, 0, '', 0, 0, '', " . $db->quote($product_code) . ", 0.0000000, " . $price . ", '', 0, '', " . $price . ", null, null, 0, 'NewCondition')";

                    $db->setQuery($query);
                    $db->execute();
                    $product_id = $db->insertid();

                    $query = "INSERT INTO jos_hikashop_product_category (category_id, product_id, ordering) VALUES (" . $category . ", " . $product_id . ", 1)";
                    $db->setQuery($query);
                    $db->execute();

                    $query = "INSERT INTO jos_hikashop_price (price_currency_id, price_product_id, price_value, price_min_quantity, price_access, price_site_id, price_users, price_start_date, price_end_date) VALUES (1, " . $product_id . "," . $price . ", 0, 'all', '', '', 0, 0)";
                    $db->setQuery($query);
                    $db->execute();
                }*/

                $query = $db->getQuery(true);
                /*if($custom_fields->field_recurrent != 1){
                    $query->select('start_date,end_date')
                        ->from($db->quoteName('data_recurrence'))
                        ->where($db->quoteName('eb_value') . ' = ' . $db->quote($custom_fields->field_recurrent));
                    $db->setQuery($query);
                    $dates = $db->loadObject();

                    if($row->registration_start_date) {
                        $row->registration_start_date = $dates->start_date;
                    }
                    if($row->cut_off_date) {
                        $row->cut_off_date = $dates->end_date;
                    }
                }*/

                // Create campaign
                $query->clear()
                    ->insert($db->quoteName('#__emundus_setup_campaigns'))
                    ->set($db->quoteName('user') . ' = ' . $db->quote($user->id))
                    ->set($db->quoteName('label') . ' = ' . $db->quote($row->title))
                    ->set($db->quoteName('description') . ' = ' . $db->quote($row->description))
                    ->set($db->quoteName('short_description') . ' = ' . $db->quote($row->short_description))
                    ->set($db->quoteName('start_date') . ' = ' . $db->quote($row->registration_start_date))
                    ->set($db->quoteName('end_date') . ' = ' . $db->quote($row->cut_off_date))
                    ->set($db->quoteName('profile_id') . ' = ' . $db->quote(1043))
                    ->set($db->quoteName('training') . ' = ' . $db->quote('activites'))
                    ->set($db->quoteName('year') . ' = ' . $db->quote('2022-2023'))
                    ->set($db->quoteName('published') . ' = ' . $db->quote(1))
                    ->set($db->quoteName('event') . ' = ' . $db->quote($row->id));
                //->set($db->quoteName('hikashop_product') . ' = ' . $db->quote($product_id));
                $db->setQuery($query);
                $db->execute();

                $campaign = $db->insertid();

                if($custom_fields->field_recurrent != 1){
                    $redirect_url = '/index.php?option=com_fabrik&view=form&formid=449&cid='.$campaign;
                }
                if($save_quit == 1){
                    $redirect_url = '/index.php?option=com_fabrik&view=form&formid=350&eid='.$row->id;
                }
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

                // Activités étudiantes
                $query->clear()
                    ->select($db->quoteName('fnum'))
                    ->from($db->quoteName('#__emundus_campaign_candidature'))
                    ->where($db->quoteName('campaign_id') . ' = ' . $db->quote($campaign));
                $db->setQuery($query);
                $existing_fnums = $db->loadColumn();

                foreach ($existing_fnums as $indiv_fnum) {
                    $query->clear()
                        ->select($db->quoteName('egs.group_id'))
                        ->from($db->quoteName('#__emundus_group_assoc','egs'))
                        ->leftJoin($db->quoteName('#__emundus_setup_groups','esg').' ON '.$db->quoteName('esg.id').' = '.$db->quoteName('egs.group_id'))
                        ->where($db->quoteName('esg.service_gestion') . ' = 1')
                        ->where($db->quoteName('egs.fnum') . ' = ' . $db->quote($indiv_fnum));
                    $db->setQuery($query);
                    $fnum_group_gestion = $db->loadColumn();

                    if (!in_array($custom_fields->field_faculte_gestion, $fnum_group_gestion)) {
                        if (!empty($fnum_group_gestion)) {
                            $conditions = array(
                                $db->quoteName('group_id') . ' IN (' . implode(',', $fnum_group_gestion) . ')',
                                $db->quoteName('fnum') . ' = ' . $db->quote($indiv_fnum)
                            );
                            $query->clear()
                                ->delete($db->quoteName('#__emundus_group_assoc'))
                                ->where($conditions);
                            $db->setQuery($query);
                            $db->execute();
                        }

                        $columns = array('group_id', 'action_id', 'fnum', 'r');
                        $values = array($db->quote($custom_fields->field_faculte_gestion), $db->quote(1), $db->quote($indiv_fnum), $db->quote(1));

                        $query->clear()
                            ->insert($db->quoteName('#__emundus_group_assoc'))
                            ->columns($db->quoteName($columns))
                            ->values(implode(',', $values));
                        $db->setQuery($query);
                        $db->execute();
                    }
                }
            }

            if($custom_fields->field_cm == 3){
                $query->clear()
                    ->select('event_cms')
                    ->from($db->quoteName('#__emundus_setup_campaigns'))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($campaign));
                $db->setQuery($query);
                $cms = $db->loadResult();

                if(empty($cms)){
                    $query->clear()
                        ->insert('#__emundus_setup_attachments')
                        ->set($db->quoteName('lbl') . ' = ' . $db->quote('_em_cms_' . $campaign))
                        ->set($db->quoteName('value') . ' = ' . $db->quote('Certifical médical spécifique - ' . $row->title))
                        ->set($db->quoteName('allowed_types') . ' = ' . $db->quote('pdf'))
                        ->set($db->quoteName('nbmax') . ' = ' . $db->quote(1))
                        ->set($db->quoteName('ordering') . ' = ' . $db->quote(0))
                        ->set($db->quoteName('published') . ' = ' . $db->quote(1))
                        ->set($db->quoteName('default_attachment') . ' = ' . $db->quote(1));
                    $db->setQuery($query);
                    $db->execute();
                    $cms = $db->insertid();

                    $query->clear()
                        ->update($db->quoteName('#__emundus_setup_campaigns'))
                        ->set($db->quoteName('event_cms') . ' = ' . $db->quote($cms))
                        ->where($db->quoteName('id') . ' = ' . $db->quote($campaign));
                    $db->setQuery($query);
                    $db->execute();
                }
            } elseif ($custom_fields->field_cm != 3){
                $query->clear()
                    ->update($db->quoteName('#__emundus_setup_campaigns'))
                    ->set($db->quoteName('event_cms') . ' = null')
                    ->where($db->quoteName('id') . ' = ' . $db->quote($campaign));
                $db->setQuery($query);
                $db->execute();
            }

            $query->clear()
                ->update($db->quoteName('#__eb_events'))
                ->set($db->quoteName('alias') . ' = ' . $db->quote('activite-00' . $row->id))
                ->set($db->quoteName('registration_handle_url') . ' = ' . $db->quote(JURI::base() . 'apply?course=prog&cid='.$campaign.'&Itemid=3056'));
            if($custom_fields->field_recurrent != 1){
                $query->set($db->quoteName('event_date') . ' = ' . $db->quote($row->registration_start_date))
                    ->set($db->quoteName('event_end_date') . ' = ' . $db->quote($row->cut_off_date))
                    ->set($db->quoteName('registration_start_date') . ' = ' . $db->quote($row->registration_start_date))
                    ->set($db->quoteName('cut_off_date') . ' = ' . $db->quote($row->cut_off_date));
            }
            $query->where($db->quoteName('id') . ' = ' . $event_id);
            $db->setQuery($query);
            $db->execute();

            if(!empty($redirect_url)){
                JFactory::getApplication()->redirect($redirect_url);
            }
        } catch (Exception $e){
            JLog::add('Error create campaign at event creation : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }

        return true;
    }
}
