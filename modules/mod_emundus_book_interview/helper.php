<?php
defined('_JEXEC') or die('Access Deny');

class modEmundusBookInterviewHelper {

	/** Checks if the user has booked an event.
	 *
	 * @param $userId
	 * @param $campaign_start_date
	 *
	 * @return bool
	 */
    public function hasUserBooked($userId, $campaign_start_date) {

	    $db = JFactory::getDbo();
	    $query = $db->getQuery(true);

        try {

            $query->select('COUNT(id)')->from($db->qn('#__dpcalendar_events'))->where($db->qn('booking_information').' LIKE '.$db->q($userId).' AND '.$db->qn('start_date').' > '.$db->q($campaign_start_date));
            $db->setQuery($query);
            return $db->loadResult() > 0;

        } catch (Exception $e) {
            JLog::add('Error in mod_emundus_book_interview at: hasUserBooked', Jlog::ERROR, 'com_emundus');
            return false;
        }

    }


	/** Gets all available events for the user.
	 * @param $user
	 *
	 * @return mixed
	 * @throws Exception
	 */
    public function getEvents($user,$fnum) {

        $db = JFactory::getDbo();
        $offset = JFactory::getConfig()->get('offset');

        $now = date("Y-m-d H:i:s");

        try {

            $query = "SELECT id, title, start_date, description
            FROM #__dpcalendar_events
            WHERE state = 1
            AND (booking_information IS NULL OR booking_information = '')
            AND start_date >= ".$db->Quote($now)."
            AND catid IN (
                SELECT GROUP_CONCAT(id)
                FROM jos_categories
                WHERE extension LIKE \"com_dpcalendar\"
                AND params LIKE '%\"program\":\"".$user->fnums[$fnum]->training."\"%'
                GROUP BY id
            )
            ORDER BY catid ASC";

            $db->setQuery($query);
            $events = $db->loadObjectList();

        } catch (Exception $e) {
            JLog::add('Error in mod_emundus_book_interview at: getEvents', Jlog::ERROR, 'com_emundus');
        }

        foreach($events as $event) {

            $interview_dt = new DateTime($event->start_date, new DateTimeZone('GMT'));
            $interview_dt->setTimezone(new DateTimeZone($offset));
            $event->start_date = $interview_dt->format("Y-m-d H:i:s");

        }

        return $events;

    }


	/** Gets the upcoming interview booked by the user.
	 * @param $user
	 *
	 * @return bool|mixed
	 */
    public function getNextInterview($user) {

	    $db = JFactory::getDbo();
	    $query = $db->getQuery(true);

        try {

	        // Get the timestamp for the event as well as maybe some other info?
            $query->select($db->qn(['dpe.id', 'start_date','cat.title']))
                ->from($db->qn('#__dpcalendar_events','dpe'))
                ->leftjoin($db->qn('#__categories','cat'). ' ON '. $db->qn('cat.id'). ' = '. $db->qn('dpe.catid'))
                ->where($db->qn('booking_information').' LIKE '.$db->q($user->id));
            $db->setQuery($query);
            return $db->loadObject();

        } catch (Exception $e) {
            JLog::add('Error in mod_emundus_book_interview at: getNextInterview', Jlog::ERROR, 'com_emundus');
            return false;
        }

    }
    public function getLastFileInterviewStatus($userid){
        require_once (JPATH_SITE . '/components/com_emundus/helpers/date.php');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            // Get the timestamp for the event as well as maybe some other info?
            $query->select($db->qn(['status','fnum']))
                ->from($db->qn('#__emundus_campaign_candidature','cc'))
                ->leftjoin($db->qn('#__emundus_setup_campaigns','sc').'  ON '. $db->qn('cc.campaign_id').' = '.$db->qn('sc.id'))
                ->where($db->qn('applicant_id').' = '.$db->q($userid))
                ->andwhere($db->qn('status').' = 5')
                ->andwhere($db->qn('sc.id') . ' = (SELECT id FROM jos_emundus_setup_campaigns ORDER BY id DESC LIMIT 1)');
            $db->setQuery($query);

            return $db->loadObject();

        } catch (Exception $e) {
            JLog::add('Error in mod_emundus_book_interview at: getLastFileInterviewStatus', Jlog::ERROR, 'com_emundus');
            return false;
        }
    }

}
