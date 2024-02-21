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
	public function onBeforeLoad()
	{
		$form_to_check = $this->getParam('form_to_check', 0);

		if (!empty($form_to_check)) {
			$app = JFactory::getApplication();
			$fnum = $app->input->get->getString('rowid', '');

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
						$query->clear()
							->select('id')
							->from($table_name)
							->where('fnum LIKE ' . $db->quote($fnum));

						$db->setQuery($query);
						$id = $db->loadResult();

						if (empty($id)) {
							$menu = $app->getMenu();
							$current_menu = $menu->getActive();
							$menutype = $current_menu->get('menutype');

							$query->clear()
								->select('path')
								->from('jos_menu')
								->where('menutype = ' . $db->quote($menutype))
								->andWhere('link LIKE "%formid=' . $form_to_check . '"');

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
				} catch (Exception $e) {
					JLog::add('Error occured ' .  $e->getMessage(), JLog::ERROR, 'plugin_emunduscheckformsfilled.error');
				}
			}
		}
	}
}
