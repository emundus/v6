<?php
    defined('_JEXEC') or die('Access Deny');

    class modEmundusBookInterviewHelper {

        public function hasUserBooked($userId) {

            try {

                $db = JFactory::getDbo();
                $db->setQuery('SELECT COUNT(id) FROM #__dpcalendar_events WHERE booking_information LIKE '.$userId);
                if ($db->loadResult() > 0)
                    return true;
                else
                    return false;

            } catch (Exception $e) {
                die($e->getMessage());
            }

        }


        public function getEvents($user) {

            $db = JFactory::getDbo();
            $offset = JFactory::getConfig()->get('offset');

            $now = date("Y-m-d H:i:s");

            try {

                $query = "SELECT id, title, start_date, description
                FROM #__dpcalendar_events
                WHERE metakey IS NULL
                AND start_date >= ".$db->Quote($now)."
                AND catid IN (
                    SELECT GROUP_CONCAT(id)
                    FROM jos_categories
                    WHERE extension LIKE \"com_dpcalendar\"
                    AND params LIKE '%\"program\":\"".$user->code."\"%'
                    GROUP BY id
                )";

                $db->setQuery($query);
                $events = $db->loadObjectList();

            } catch (Exception $e) {
                die($e->getMessage());
            }


            foreach($events as $event) {

                $interview_dt = new DateTime($event->start_date, new DateTimeZone('GMT'));
                $interview_dt->setTimezone(new DateTimeZone($offset));
                $event->start_date = $interview_dt->format("Y-m-d H:i:s");

            }

            return $events;

        }

        public function getNextInterview($user) {

            $db = JFactory::getDbo();

            try {

                // Get the timestamp for the event as well as maybe some other info?
                $db = JFactory::getDbo();
                $db->setQuery('SELECT id,start_date FROM #__dpcalendar_events WHERE booking_information LIKE '.$user->id);
                return $db->loadObject();

            } catch (Exception $e) {
                die($e->getMessage());
            }

        }

    }

?>