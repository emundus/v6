<?php

//First start with information about the Plugin and yourself. For example:
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik_form.emundusrgpd
 *
 * @copyright   Copyright
 * @license     License, for example GNU/GPL
 */

//To prevent accessing the document directly, enter this code:
// no direct access
defined('_JEXEC') or die();

class PlgFabrik_FormEmundusrgpd extends plgFabrik_Form {

    public function onAfterProcess(){
        $checkbox = $this->params->get('emunduscheckbox_rgpd');
        $subject = $this->params->get('emundussubject_rgpd');
        $body = $this->params->get('emundusbody_rgpd');
        $formModel = $this->getModel();
        $uid = $formModel->formDataWithTableName['jos_emundus_users___user_id'];

        $created = date('Y-m-d H:i:s');
        
        if(!empty($checkbox)){
            $checked = $formModel->getElementData($checkbox);
            if($checked){
                //insert
                $userNote = (object) array(
                    'user_id' => $uid,
                    'state'   => 0,
                    'subject' => $subject,
                    'body'    => $body,
                    'created' => $created,
                );

                try
                {
                    $db = JFactory::getDBO();
                    $db->insertObject('#__privacy_consents', $userNote);
                }
                catch (Exception $e)
                {
                    // Do nothing if the save fails
                }
            }
        }
    }
}