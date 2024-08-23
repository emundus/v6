<?php
/**
 * @version 2: EmundusAssigntogroup 2020-02 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2020 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Assign application to group
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

class PlgFabrik_FormEmunduscheckformsfilled extends plgFabrik_Form {
    /**
     * Status field
     *
     * @var  string
     */
    protected $URLfield = '';

    /**
     * Get an element name
     *
     * @param   string  $pname  Params property name to look up
     * @param   bool    $short  Short (true) or full (false) element name, default false/full
     *
     * @return	string	element full name
     */
    public function getFieldName($pname, $short = false) {
        $params = $this->getParams();

        if ($params->get($pname) == '')
            return '';

        $elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

        return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
    }

    /**
     * Get the fields value regardless of whether its in joined data or no
     *
     * @param   string  $pname    Params property name to get the value for
     * @param   array   $data     Posted form data
     * @param   mixed   $default  Default value
     *
     * @return  mixed  value
     */
    public function getParam($pname, $default = '') {
        $params = $this->getParams();

        if ($params->get($pname) == '') {
            return $default;
        }

        return $params->get($pname);
    }

	/**
	 * Main script.
	 *
	 */
    public function onLoad()
    {
        $form_to_check = $this->getParam('form_to_check', 0);

        if (!empty($form_to_check)) {
            $app = JFactory::getApplication();

            $formModel = $this->getModel();
            $current_table_name = $formModel->getForm()->db_table_name;

            $fnum_tag = '{'.$current_table_name.'___fnum}';
            // get fnum using multiple options otherwise it could be empty
            if (empty($fnum_tag) || strpos($fnum_tag, '{') === 0) {
                $fnum = $app->input->get('rowid');
            } else {
                $fnum = $fnum_tag;
            }
            if (empty($fnum)) {
                $fnum = $app->input->get($current_table_name.'___fnum');
            }
            if (empty($fnum)) {
                $fnum = JFactory::getSession()->get('emundusUser')->fnum;
            }

            if (!empty($fnum)) {
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);

                $query->select('jfl.db_table_name')
                    ->from($db->quoteName('jos_fabrik_lists', 'jfl'))
                    ->leftJoin($db->quoteName('jos_fabrik_forms', 'jff') . ' ON jff.id = jfl.form_id')
                    ->where($db->quoteName('jff.id') . ' = ' . $db->quote($form_to_check));

                try {
                    $db->setQuery($query);
                    $table_name = $db->loadResult();

                    if (!empty($table_name)) {
                        // Do not redirect if the user is not an applicant
                        $session = JFactory::getSession();
                        $current_user = $session->get('emundusUser');

                        if ($current_user->applicant == 1) {
                            $query->clear()
                                ->select('id')
                                ->from($table_name)
                                ->where('fnum LIKE ' . $db->quote($fnum));
                            $db->setQuery($query);
                            $id = $db->loadResult();

                            if (empty($id)) {
                                $menu = $app->getMenu();
                                $current_menu = $menu->getActive();
                                if (JFactory::getUser()->id == 95) {
                                    $menutype = 'menu-profile1015';
                                } else {
                                    $menutype = $current_menu->get('menutype');
                                }

                                $query->clear()
                                    ->select('jm.id as `itemid`, jfl.db_table_name')
                                    ->from($db->quoteName('#__menu','jm'))
                                    ->leftJoin($db->quoteName('#__fabrik_forms','jff').' ON '.$db->quoteName('jff.id').' = SUBSTRING_INDEX(SUBSTRING('.$db->quoteName('jm.link').', LOCATE("formid=", '.$db->quoteName('jm.link').') + 7, 4), "&", 1)')
                                    ->leftJoin($db->quoteName('#__fabrik_lists','jfl').' ON '.$db->quoteName('jfl.form_id').' = '.$db->quoteName('jff.id'))
                                    ->where($db->quoteName('jm.menutype').' = '.$db->quote($menutype))
                                    ->andWhere($db->quoteName('jm.link').' LIKE '.$db->quote('%formid%'));
                                $db->setQuery($query);
                                $menuforms = $db->loadObjectList();

                                $form_to_redirect = '';
                                foreach($menuforms as $form) {
                                    if ($form->db_table_name == $table_name) {
                                        $form_to_redirect = $form->itemid;
                                    }
                                }

                                $query->clear()
                                    ->select('path')
                                    ->from('jos_menu')
                                    ->where('id = '. $db->quote($form_to_redirect));
                                $db->setQuery($query);
                                $path = $db->loadResult();

                                if (!empty($path)) {
                                    $app->enqueueMessage(JText::_('PLG_FABRIK_FORM_EMUNDUS_CHECKFORMSFILLED_REDIRECT_MESSAGE'));
                                    $app->redirect($path);
                                } else {
                                    $app->enqueueMessage(JText::_('PLG_FABRIK_FORM_EMUNDUS_CHECKFORMSFILLED_ERROR_COULD_NOT_REDIRECT'), 'error');
                                    $app->redirect('/');
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    JLog::add('Error occured ' .  $e->getMessage(), JLog::ERROR, 'plugin_emunduscheckformsfilled.error');
                }
            }
        }
    }
}
