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

class PlgFabrik_FormEmunduseventhandler extends plgFabrik_Form
{
    /**
     * @var bool dispatch emundus events
     */
    public $dispatchEvents = false;


    /**
     * @param $subject
     * @param $config
     */
    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);
        JPluginHelper::importPlugin('emundus');
    }

    public function onLoad()
    {
        if ($this->dispatchEvents === true) {
            JEventDispatcher::getInstance()->trigger('callEventHandler', ['onLoad', $this->getProcessData()]);
        }
    }

    public function onBeforeLoad()
    {
        if ($this->dispatchEvents === true) {
            JEventDispatcher::getInstance()->trigger('callEventHandler', ['onBeforeLoad', $this->getProcessData()]);
        }
    }

    public function onBeforeProcess()
    {
        if ($this->dispatchEvents === true) {
            JEventDispatcher::getInstance()->trigger('callEventHandler', ['onBeforeProcess', $this->getProcessData()]);
        }
    }

    public function onAfterProcess()
    {
        if ($this->dispatchEvents === true) {
            JEventDispatcher::getInstance()->trigger('callEventHandler', ['onAfterProcess', $this->getProcessData()]);
        }
    }

    public function onBeforeCalculations()
    {
        if ($this->dispatchEvents === true) {
            JEventDispatcher::getInstance()->trigger('callEventHandler', ['onBeforeCalculations', $this->getProcessData()]);
        }
    }

    public function onAfterCalculations()
    {
        if ($this->dispatchEvents === true) {
            JEventDispatcher::getInstance()->trigger('callEventHandler', ['onAfterCalculations', $this->getProcessData()]);
        }
    }
}
