<?php
/**
 * @version     calcom June 2024 eMundus
 * @package     Fabrik
 * @copyright   Copyright (C) 2024 emundus.fr. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Processing of availability management forms
 */

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';
require_once JPATH_SITE.'/components/com_emundus/classes/api/Api.php';
require_once JPATH_SITE.'/components/com_emundus/classes/api/CalCom.php';

// No direct access
use Joomla\CMS\Factory;
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
     * The event which runs before storing (saving) data to the database
     *
     * @return bool
     */
    public function onBeforeStore()
    {
        $w          = new CalCom(true);
        $app        = Factory::getApplication();
        $id         = $app->input->get('jos_emundus_setup_availabilities___id');
        $name       = $app->input->get('jos_emundus_setup_availabilities___name', null, "raw") ?: '';
        $start_date = $app->input->get('jos_emundus_setup_availabilities___start_date') ?: '';
        $end_date   = $app->input->get('jos_emundus_setup_availabilities___end_date') ?: '';
        $length     = $app->input->get('jos_emundus_setup_availabilities___event_length') ?: '';
        $event_name = $app->input->get('jos_emundus_setup_availabilities___event_name', null, "raw") ?: '';
        $formModel  = $this->getModel();

        $db         = Factory::getDbo();
        $query       = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('jos_emundus_setup_availabilities'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($id));

        $db->setQuery($query);
        $request = $db->loadObject();

        if(!empty($request))
        {
            if($name !== $request->name)
            {
                $w->patchUser($name, $request->user_id, $request->schedule_id);
            }
            $start_time = new DateTime($request->start_date);
            $end_time = new DateTime($request->end_date);
            if($start_date !== $start_time->format('Y-m-dHis') || $end_date !== $end_time->format('Y-m-dHis'))
            {
                if(!Factory::getSession()->get('cal_com_access_token_' . $request->user_id))
                {
                    $access_token = $w->refreshingAccessToken($request->user_id);
                    Factory::getSession()->set('cal_com_access_token_' . $request->user_id, array($access_token['data']->data->accessToken, (new DateTime('now'))->modify('+1 hour')));
                }
                $access_token = Factory::getSession()->get('cal_com_access_token_' . $request->user_id);
                $w->patchSchedule($access_token[0], $request->user_id, $request->schedule_id, $start_date, $end_date);
            }
            if($length !== $request->event_length || $event_name !== $request->event_name)
            {
                $w->deleteEventType($request->event_id);
                $event_type = $w->postEventType($length, $event_name, $request->user_id);
            }
            $event_id = $event_type ? $event_type['data']->event_type->id : $request->event_id;
        }
        else
        {
            $user = $w->postUser($name);
            Factory::getSession()->set('cal_com_access_token_' . $user['data']->data->user->id, array($user['data']->data->accessToken, (new DateTime('now'))->modify('+1 hour')));
            $w->deleteSchedule($user['data']->data->accessToken, $user['data']->data->user->defaultScheduleId, $user['data']->data->user->id);
            $schedule = $w->postSchedule($user['data']->data->accessToken, $user['data']->data->user->id, $start_date, $end_date);
            $w->patchSchedule($user['data']->data->accessToken, $user['data']->data->user->id, $schedule['data']->data->id, $start_date, $end_date);
            $event_type = $w->postEventType($length, $event_name, $user['data']->data->user->id);
            $event_id = $event_type['data']->event_type->id;
            $formModel->updateFormData('jos_emundus_setup_availabilities___schedule_id', $schedule['data']->data->id);
            $formModel->updateFormData('jos_emundus_setup_availabilities___user_id', $user['data']->data->user->id);
        }

        $formModel->updateFormData('jos_emundus_setup_availabilities___event_id', $event_id);

        return true;
    }

}
