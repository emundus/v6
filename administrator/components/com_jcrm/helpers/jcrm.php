<?php

/**
 * @version     1.0.0
 * @package     com_jcrm
 * @copyright   Copyright (C) 2014. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      Décision Publique <dev@emundus.fr> - http://www.emundus.fr
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Jcrm helper.
 */
class JcrmHelper {

    /**
     * Configure the Linkbar.
     */
    public static function addSubmenu($vName = '') {
        		JSubMenuHelper::addEntry(
			JText::_('COM_JCRM_TITLE_CONTACTS'),
			'index.php?option=com_jcrm&view=contacts',
			$vName == 'contacts'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_JCRM_TITLE_SYNCS'),
			'index.php?option=com_jcrm&view=syncs',
			$vName == 'syncs'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_JCRM_TITLE_MIGRATE'),
			'index.php?option=com_jcrm&view=migrate',
			$vName == 'migrate'
		);

    }

    /**
     * Gets a list of the actions that can be performed.
     *
     * @return	JObject
     * @since	1.6
     */
    public static function getActions() {
        $user = JFactory::getUser();
        $result = new JObject;

        $assetName = 'com_jcrm';

        $actions = array(
            'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
        );

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }

    public static function buildContactFromOrg($contact)
    {
        $newContact = new stdClass();
        $newContact->id = $contact->id;
        $newContact->first_name = null;
        $newContact->last_name = null;
        $newContact->type = 1;
        $newContact->organisation = addslashes($contact->name);
        $newContact->full_name = addslashes($contact->name);
        $newContact->phone = $contact->phone_account;
        $newContact->email = $contact->director_email;
        $photo = array("mediatype"=>"image/svg", "uri"=>"/media/com_jcrm/images/contacts/org.svg");
        $jcard = array(
            'version' => "4.0",
            "n" => array("", "", "", "", ""),
            "fn" => $newContact->organisation,
            "org" => $newContact->organisation,
            "photo" => $photo,
        );
        $mail = new stdClass();
        $mail->type = 'work';
        $mail->uri = $contact->director_email;
        $jcard['email'] = array($mail);
        $phoneWork = new stdClass();
        $phoneWork->type = 'work';
        $phoneWork->tel = $contact->phone_account;
        $phoneFax = new stdClass();
        $phoneFax->type = 'fax';
        $phoneFax->tel = $contact->phone_fax;
        $jcard['phone'] = array($phoneWork, $phoneFax);
        $adress = new stdClass();
        $adress->type = 'work';
        $adress->array = array();
        $adress->array[0] = (!empty($contact->address_street_2))?$contact->address_street:$contact->address_street . ';' . $contact->address_street_2;
        $adress->array[1] = $contact->address_postalcode;
        $adress->array[2] = $contact->address_city;
        $adress->array[3] = $contact->address_country;
        $jcard['adr'] = array($adress);
        $jcard['other'] = array();
        $jcard['infos'] ="date_entered:".($contact->date_entered !== null)?$contact->date_entered:date('Y-m-d', time())."\n".
                         "logo_name:".$contact->logo_name."\n".
                         "account_type:".$contact->account_type."\n".
                         "account_speciality:".$contact->account_speciality."\n".
                         "cours_list:".$contact->cours_list."\n".
                         "degrees_list:".$contact->degrees_list."\n".
                         "research_areas_list:".$contact->research_areas_list."\n".
                         "annual_appropriations:".$contact->annual_appropriations."\n".
                         "website:".$contact->website."\n".
                         "director_name:".$contact->director_name."\n".
                         "location:".$contact->location."\n".
                         "economic_information:".$contact->economic_information."\n".
                         "number_student_places:".$contact->number_student_places."\n".
                         "number_students:".$contact->number_students."\n".
                         "code_account:".$contact->code_account."\n".
                         "faculties_list:".$contact->faculties_list."\n".
                         "areas_of_excellence:".$contact->areas_of_excellence."\n".
                         "agreements_list:".$contact->agreements_list."\n".
                         "practical_info:".$contact->practical_info."\n".
                         "comment:".$contact->comment."\n";
        $newContact->jcard = $jcard;
        return $newContact;
    }

    public static function buildContactFromContactBk($contact)
    {
        $newContact = new stdClass();
        $newContact->first_name = addslashes($contact->first_name);
        $newContact->last_name = addslashes($contact->last_name);
        $newContact->type = 0;
        $newContact->organisation = addslashes($contact->orga);
        $newContact->full_name = $newContact->first_name.' '.$newContact->last_name;
        $newContact->phone = $contact->phone_work;
        $newContact->email = $contact->email;
        $photo = array("mediatype"=>"image/svg", "uri"=>"/media/com_jcrm/images/contacts/user.svg");
        $jcard = array(
            'version' => "3.0",
            "n" => array($newContact->first_name, $newContact->last_name, "", "", ""),
            "fn" => $newContact->full_name,
            "org" => $newContact->organisation,
            "photo" => $photo,
        );
        $mail = new stdClass();
        $mail->type = 'work';
        $mail->uri = $contact->email;
        $jcard['email'] = array($mail);
        $phoneWork = new stdClass();
        $phoneWork->type = 'work';
        $phoneWork->tel = $contact->phone_work;
        $phoneFax = new stdClass();
        $phoneFax->type = 'fax';
        $phoneFax->tel = $contact->phone_fax;
        $jcard['phone'] = array($phoneWork, $phoneFax);
        $adress = new stdClass();
        $adress->type = 'work';
        $adress->array = array();
        $adress->array[0] = $contact->primary_address_street;
        $adress->array[1] = $contact->primary_address_postalcode;
        $adress->array[2] = $contact->primary_address_city;
        $adress->array[3] = "";
        $jcard['adr'] = array($adress);
        $other = new stdClass();
        $other->type = 'title';
        $other->value = $contact->title;
        if(!empty($other->value))
        {
            $jcard['other'] = array($other);
        }
        else
        {
            $jcard['other'] = array();
        }
        $jcard['infos'] ="date_entered:".($contact->date_entered !== null)?$contact->date_entered:date('Y-m-d', time())."\n".
                        "country_code:".$contact->country_code."\n".
                        "department:".$contact->department."\n".
                        "salutation:".$contact->salutation."\n".
                        "salutation:".$contact->salutation."\n".
                        "website:".$contact->website."\n".
                        "comment:".$contact->comment."\n".
                        "referent_postgraduate:".$contact->referent_postgradutate."\n".
                        "referent_esa_name:".$contact->referent_esa_name."\n".
                        "contact_type:".$contact->contact_type."\n";
        $newContact->jcard = $jcard;
        return $newContact;
    }

}
