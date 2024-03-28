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

require_once (JPATH_ROOT . '/components/com_emundus/models/ranking.php');

jimport('joomla.application.component.view');

/**
 * Classement View
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      1.39.0
 */
class EmundusViewRanking extends JViewLegacy
{
    var $user = null;
    var $db = null;
    var $hierarchy_id = null;
    var $display_filters = false;
    var $comparison_modal_tabs = array();

    function __construct($config = array())
    {
        $this->user = Factory::getUser();
        $this->db = Factory::getDBO();
        $this->model = new EmundusModelRanking();
        $this->hierarchy_id = $this->model->getUserHierarchy($this->user->id);

        $menu = Factory::getApplication()->getMenu();
        $active = $menu->getActive();
        $query = $this->db->getQuery(true);
        $query->select('jm.id')
            ->from($this->db->quoteName('jos_modules', 'jm'))
            ->leftJoin($this->db->quoteName('jos_modules_menu', 'jmm') . ' ON jm.id = jmm.moduleid')
            ->where('jmm.menuid = ' . $active->id)
            ->andWhere('jm.module = ' . $this->db->quote('mod_emundus_filters'))
            ->andWhere('jm.published = 1');

        $this->db->setQuery($query);
        $module_id = $this->db->loadResult();

        if (!empty($module_id)) {
            $this->display_filters = true;
        }

        $comparison_modal_tabs = $active->getParams()->get('comparison_modal_tabs', []);
        $this->comparison_modal_tabs = implode(',', $comparison_modal_tabs);

        parent::__construct($config);
    }

    function display($tpl = null)
    {
        parent::display($tpl);
    }
}
