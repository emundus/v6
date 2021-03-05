<?php
/**
 *
 * @package	eMundus
 * @version	1.18.0
 * @author	James Dean
 * @copyright (C) 2021 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die('Restricted access');

class plgEmundusNantes_link_employeur extends JPlugin {

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.nantes_link_employeur.php'), JLog::ALL, array('com_emundus_nantes_link_employeur'));
    }

    function onAfterSaveEmundusUser($user, $params = []) {

        $res = true;

        $employeur = $params ?? $user;

        if (!empty($employeur['university_id'])) {

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            // Look for the employeur link to that category
            $query
                ->select('*')
                ->from($db->qn('#__contact_details'))
                ->where($db->qn('catid') . ' = ' . $employeur['university_id'])
                ->andWhere($db->qn('user_id') . ' = ' . $employeur['id']);

            $db->setQuery($query);

            try {
                $existing_user = $db->loadObject();
                JLog::add('contact user   :'.$existing_user->user_id, JLog::INFO, 'com_emundus_nantes_link_employeur');

            } catch (Exception $e) {
                JLog::add('Error finding employeur at query :'.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus_nantes_link_employeur');
                return;
            }

            // Get the category info
            $query
                ->clear()
                ->select(['data.*', $db->qn('country.name_fr', 'country_label')])
                ->from($db->qn('data_employers', 'data'))
                ->leftjoin($db->qn('jos_emundus_country', 'country') . ' ON ' . $db->qn('data.country') . ' = ' . $db->qn('country.id'))
                ->where($db->qn('cat_id') . ' = ' . $employeur['university_id']);

            $db->setQuery($query);

            try {
                $cat = $db->loadObject();
                JLog::add('Category   :'.$cat->id, JLog::INFO, 'com_emundus_nantes_link_employeur');

            } catch (Exception $e) {
                JLog::add('Error getting category at query :'.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus_nantes_link_employeur');
                return;
            }

            if (!empty($cat)) {

                $organization = new stdClass();
                $organization->name = $employeur['name'];
                $organization->user_id = $employeur['id'];
                $organization->email_to = $employeur['email'];
                $organization->con_position = $cat->siret;
                $organization->address = $cat->adresse_1. ' ' . $cat->adresse_2;
                $organization->postcode = $cat->zipcode;
                $organization->suburb = $cat->city_1;
                $organization->country = $cat->country_label;
                $organization->catid = $cat->cat_id;

                if (empty($existing_user)) {
                    $organization->alias = $cat->siret . '_' . $employeur['id'];
                    $organization->published = 1;

                    $organization->language = '*';
                    $organization->metakey = '';
                    $organization->metadesc = '';
                    $organization->metadata = '{"robots":"","rights":""}';
                    $organization->params = '{"show_contact_category":"","show_contact_list":"","presentation_style":"","show_tags":"","show_info":"","show_name":"","show_position":"","show_email":"","add_mailto_link":"","show_street_address":"","show_suburb":"","show_state":"","show_postcode":"","show_country":"","show_telephone":"","show_mobile":"","show_fax":"","show_webpage":"","show_image":"","show_misc":"","allow_vcard":"","show_articles":"","articles_display_num":"","show_profile":"","show_links":"","linka_name":"","linka":false,"linkb_name":"","linkb":false,"linkc_name":"","linkc":false,"linkd_name":"","linkd":false,"linke_name":"","linke":false,"contact_layout":"","show_email_form":"","show_email_copy":"","banned_email":"","banned_subject":"","banned_text":"","validate_session":"","custom_reply":"","redirect":""}';
                    $res = $db->insertObject('#__contact_details', $organization, 'id');

                } else {
                    $res = JFactory::getDbo()->updateObject('#__contact_details', $organization, $existing_user->id);
                }

                JLog::add('result on user  :'.$res, JLog::INFO, 'com_emundus_nantes_link_employeur');

            }
        }

        return $res;
    }
}