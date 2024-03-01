<?php

/**
 * @package    Joomla
 * @subpackage eMundus
 * @link       http://www.emundus.fr
 * @copyright    Copyright (C) 2024 eMundus SAS. All rights reserved.
 * @license    GNU/GPL
 * @author     eMundus SAS - LEGENDRE Jérémy
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

jimport('joomla.application.component.view');

/**
 * Classement View
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      1.39.0
 */
class EmundusViewClassement extends JViewLegacy
{
    var $user = null;
    var $db = null;
    var $hierarchy_id = null;

    function __construct($config = array())
    {
        $this->user = Factory::getUser();
        $this->db = Factory::getDBO();

        parent::__construct($config);
    }

    function display($tpl = null)
    {
        parent::display($tpl);
    }
}
