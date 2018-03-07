<?php
    defined('_JEXEC') or die('Access Deny');


    class modEmundusTimeslotsHelper {

        public function getCalendars() {

            $db = JFactory::getDBo();

            // Get the parent cal ID, this is the one we omit from the list
            $eMConfig = JComponentHelper::getParams('com_emundus');
            $parent_id = $eMConfig->get('parentCalId');

            $query = 'SELECT id, title FROM #__categories
                        WHERE extension LIKE "com_dpcalendar"
                        AND id != '.$parent_id;

            try {

                $db->setQuery($query);
                return $db->loadObjectList();

            } catch (Exception $e) {
                JLog::add('Error getting calendars for module/calendar_create_timeslots at query: '.$query, JLog::ERROR, 'com_emundus');
                return false;
            }

        }

    }

?>