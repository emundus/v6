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
        $formModel = $this->getModel();
        $datas = $formModel->data;

        //Decrypt data
        foreach($datas as $key => $data){
	        $formModel->data[$key] = EmundusHelperFabrik::decryptDatas($data);
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
        $formModel = $this->getModel();
        $datas = $formModel->formData;

        //Data to encrypt
        foreach($datas as $key => $data){
            if(strpos($key,'jos_emundus') !== false && strpos($key,'raw') === false && strpos($key,'fnum') === false && strpos($key,'id') === false && strpos($key,'user') === false && strpos($key,'time_date') === false){
	            $formModel->updateFormData($key,EmundusHelperFabrik::encryptDatas($data));
            }
        }
	}
}
