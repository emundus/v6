<?php
/**
 * @version     2: emundusReferentLetter 2018-04-25 Hugo Moracchini
 * @package     Fabrik
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Redirection et chainage des formulaires suivant le profile de l'utilisateur
 */

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';
require_once JPATH_SITE.'/components/com_emundus/classes/api/Api.php';
require_once JPATH_SITE.'/components/com_emundus/classes/api/CalCom.php';

// No direct access
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use classes\api\CalCom;

defined('_JEXEC') or die('Restricted access');



/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */
class PlgFabrik_FormCalcom extends plgFabrik_Form
{
	/**
	 * Status field
	 *
	 * @var  string
	 */
	protected $URLfield = '';

    protected $user_id;
    protected $availability_id;

	/**
	 * Get an element name
	 *
	 * @param   string  $pname  Params property name to look up
	 * @param   bool    $short  Short (true) or full (false) element name, default false/full
	 *
	 * @return    string    element full name
	 */
	public function getFieldName($pname, $short = false)
	{
		$params = $this->getParams();

		if ($params->get($pname) == '')
		{
			return '';
		}

		$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

		return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
	}

	/**
	 * Get the fields value regardless of whether its in joined data or no
	 *
	 * @param   string  $pname    Params property name to get the value for
	 * @param   mixed   $default  Default value
	 *
	 * @return  mixed  value
	 */
	public function getParam($pname, $default = '')
	{
		$params = $this->getParams();

		if ($params->get($pname) == '')
		{
			return $default;
		}

		return $params->get($pname);
	}

	/**
	 * Main script.
	 *
	 * @throws Exception
	 */
    public function onBeforeProcess()
    {
        $app         = Factory::getApplication();
        $id = $app->input->get('table_setup_availabilities___id');

        $db       = Factory::getDbo();
        $query    = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('table_setup_availabilities'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($id));

            $db->setQuery($query);
            $request = $db->loadObject();

        $already_exists = empty($request);

        $w = new CalCom();
        $name = $app->input->get('table_setup_availabilities___Nom') ?: '';
        $start_date = $app->input->get('table_setup_availabilities___date_debut') ?: '';
        $end_date = $app->input->get('table_setup_availabilities___date_fin') ?: '';

        if($already_exists)
        {
            $user = $w->postUser($name);
            $w->deleteSchedule($user['data']->data->accessToken, $user['data']->data->user->defaultScheduleId);
            $schedule = $w->postSchedule($user['data']->data->accessToken);
            $w->patchSchedule($user['data']->data->accessToken, $schedule['data']->data->id, $start_date, $end_date);
            $this->user_id = $user['data']->data->user->id;
            $this->availability_id = $schedule['data']->data->id;
        }
        else
        {
            $w->patchUser($name, $request->id_user, $request->id_availability);
            $this->user_id = $request->id_user;
            $this->availability_id = $request->id_availability;
        }

    }

    public function onBeforeStore()
    {
        $formModel = $this->getModel();
        $formModel->updateFormData('table_setup_availabilities___id_availability', $this->availability_id);
        $formModel->updateFormData('table_setup_availabilities___id_user', $this->user_id);
    }

}
