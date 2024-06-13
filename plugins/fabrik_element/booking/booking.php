<?php
/**
 * Plugin element to render plain text/HTML
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.display
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE.'/components/com_emundus/classes/api/Api.php';
require_once JPATH_SITE.'/components/com_emundus/classes/api/CalCom.php';
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');

use Joomla\Utilities\ArrayHelper;
use classes\api\CalCom;

/**
 * Plugin element to render plain text/HTML
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.booking
 * @since       3.0
 */

class PlgFabrik_ElementBooking extends PlgFabrik_Element
{
    /**
     * Db table field type
     *
     * @var  string
     */
    protected $fieldDesc = 'TEXT';

    /**
     * Does the element's data get recorded in the db
     *
     * @var bool
     */
    protected $recordInDatabase = true;

    /**
     * Set/get if element should record its data in the database
     *
     * @deprecated - not used
     *
     * @return bool
     */

    public function setIsRecordedInDatabase()
    {
        $this->recordInDatabase = true;
    }

    /**
     * Shows the data formatted for the list view
     *
     * @param   string    $data      Elements data
     * @param   stdClass  &$thisRow  All the data in the lists current row
     * @param   array     $opts      Rendering options
     *
     * @return  string	formatted value
     */
    public function renderListData($data, stdClass &$thisRow, $opts = array())
    {
        $profiler = JProfiler::getInstance('Application');
        JDEBUG ? $profiler->mark("renderListData: {$this->element->plugin}: start: {$this->element->name}") : null;

        unset($this->default);
        $value = $this->getValue(ArrayHelper::fromObject($thisRow));

        return parent::renderListData($value, $thisRow, $opts);
    }

    /**
     * Draws the html form element
     *
     * @param   array  $data           To pre-populate element with
     * @param   int    $repeatCounter  Repeat group counter
     *
     * @return  string	elements html
     */

    public function render($data, $repeatCounter = 0)
    {
        $m_users = new EmundusModelUsers();
        $params = $this->getParams();
        $layout = $this->getLayout('form');
        $displayData = new stdClass;
        $displayData->id = $this->getHTMLId($repeatCounter);
        $displayData->value = $this->getValue($data, $repeatCounter);
        $displayData->mode = $this->getParams()->get('mode');
        $user = $m_users->getUserById($this->user->id);

        $bookinggArray = json_decode($user[0]->bookingg, true);
        $displayData->slug = $bookinggArray[2];
        $displayData->owner = $bookinggArray[1];


        return $layout->render($displayData);
    }


    /**
     * Helper method to get the default value used in getValue()
     * Unlike other elements where readonly effects what is displayed, the display element is always
     * read only, so get the default value.
     *
     * @param   array  $data  Form data
     * @param   array  $opts  Options
     *
     * @since  3.0.7
     *
     * @return  mixed	value
     */

    protected function getDefaultOnACL($data, $opts)
    {
        return FArrayHelper::getValue($opts, 'use_default', true) == false ? '' : $this->getDefaultValue($data);
    }

    /**
     * Determines the value for the element in the form view
     *
     * @param   array  $data           Form data
     * @param   int    $repeatCounter  When repeating joined groups we need to know what part of the array to access
     * @param   array  $opts           Options
     *
     * @return  string	value
     */

    public function getValue($data, $repeatCounter = 0, $opts = array())
    {
        $w = new CalCom();
        $id = (int) $data['jos_emundus_users___dispo'][0];
        $availa = $this->getIdsAvailabilities($id);
        $user = $w->getUser($availa->user_id);
        $event = $w->getEventType($availa->event_id);
        $username = $user['data']->data->username;
        $event_slug = $event['data']->event_type->slug;

        return $id !== 0 ? json_encode(array($id, $username, $event_slug)) : json_encode(array($username, $event_slug));
    }

    public function getIdsAvailabilities($id_availability)
    {
        $result = '';

        if (!empty($id_availability)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('user_id as user_id, event_id as event_id')
                ->from('#__emundus_setup_availabilities')
                ->where('id = ' . $id_availability);

            try {
                $db->setQuery($query);
                $result = $db->loadObject();

                if (empty($result)) {
                    $result = '';
                    JLog::add('No availability found ' . $result, JLog::ERROR, 'com_emundus.api');
                }
            } catch (Exception $e) {
                $result = '';
                JLog::add('No availability request good ' . $result . ' ' . $e->getMessage() . ' ' . $query, JLog::ERROR, 'com_emundus.api');
            }
        }

        return $result;

    }


    /**
     * Returns javascript which creates an instance of the class defined in formJavascriptClass()
     *
     * @param   int  $repeatCounter  Repeat group counter
     *
     * @return  array
     */

    public function elementJavascript($repeatCounter)
    {
        $id = $this->getHTMLId($repeatCounter);
        $opts = $this->getElementJSOptions($repeatCounter);
        $opts->owner = $this->getParams()->get('owner');
        return array('FbPanel', $id, $opts);
    }
}
