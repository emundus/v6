<?php
/**
 * @version 2: emundusconfirmpost 2018-09-06 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Valide l'envoie d'un dossier de candidature et change le statut.
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

class PlgFabrik_FormEmundusencryptdatas extends plgFabrik_Form
{

    /**
     * Decrypt datas before display them in applicant form
     *
     * @since version
     */
    public function onLoad() {
        $cipher = "aes-128-cbc";

        $encryption_key = JFactory::getConfig()->get('secret');

        $formModel = $this->getModel();
        $datas = $formModel->data;

        //Decrypt data
        foreach($datas as $key => $data){
            if(is_array(json_decode($data))){
                $data = json_decode($data);
                foreach($data as $index => $subvalue){
                    $decrypted_data = openssl_decrypt($subvalue, $cipher, $encryption_key, 0);
                    if($decrypted_data !== false){
                        $data[$index] = $decrypted_data;
                    }
                }
                $formModel->data[$key] = json_encode($data);
            } else {
                $decrypted_data = openssl_decrypt($data, $cipher, $encryption_key, 0);
                if($decrypted_data !== false){
                    $formModel->data[$key] = $decrypted_data;
                }
            }
        }
    }

    /**
     * Encrypt datas before store them in DB using aes encryption
     *
     * @return bool|void
     *
     * @since version
     */
	public function onBeforeProcess() {
        //Define cipher
        $cipher = "aes-128-cbc";

        //Generate a 256-bit encryption key
        $encryption_key = JFactory::getConfig()->get('secret');

        $formModel = $this->getModel();
        $datas = $formModel->formData;

        //Data to encrypt
        foreach($datas as $key => $data){
            if(strpos($key,'jos_emundus') !== false && strpos($key,'raw') === false && strpos($key,'fnum') === false && strpos($key,'id') === false && strpos($key,'user') === false && strpos($key,'time_date') === false){
                if(is_array($data)){
                    foreach($data as $index => $subvalue){
                        $data[$index] = openssl_encrypt($subvalue, $cipher, $encryption_key, 0);
                    }
                    $formModel->updateFormData($key,$data);
                } else {
                    $encrypted_data = openssl_encrypt($data, $cipher, $encryption_key, 0);
                    $formModel->updateFormData($key,$encrypted_data);
                }
            }
        }
	}
}
