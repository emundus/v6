<?php
defined('_JEXEC') or die('Access Deny');

class modEmundusCampaignHelper {
    private $totalCurrent;
    private $totalFutur;
    private $totalPast;
    private $total;

    public function __construct() {
        $this->totalCurrent=0;
        $this->totalFutur=0;
        $this->totalPast=0;
        $this->total=0;
        $this->offset=JFactory::getApplication()->get('offset', 'UTC');
        try {
            $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
            $dateTime = $dateTime->setTimezone(new DateTimeZone($this->offset));
            $this->now = $dateTime->format('Y-m-d H:i:s');
        } catch(Exception $e) {
            echo $e->getMessage() . '<br />';
        }
    }

    /* **** CURRENT **** */
    public function getCurrent($condition, $teachingUnityDates = null) {

        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);
        if ($teachingUnityDates) {
            $query->select('ca.*, pr.apply_online, pr.code, pr.link, tu.date_start as formation_start, tu.date_end as formation_end, pr.programmes as prog_type, pr.id as p_id')
                ->from($db->qn('#__emundus_setup_campaigns', 'ca'))
                ->leftJoin($db->qn('#__emundus_setup_programmes', 'pr') . ' ON ' . $db->qn('pr.code') . ' = ' . $db->qn('ca.training'))
                ->leftJoin($db->qn('#__emundus_setup_teaching_unity', 'tu') . ' ON ' . $db->qn('tu.code') . ' = ' . $db->qn('ca.training').' AND '.$db->quoteName('ca.year').' = '.$db->quoteName('tu.schoolyear'))
                ->where('ca.published=1 AND "'.$this->now.'" <= ca.end_date and "'.$this->now.'">= ca.start_date '.$condition);
        } else {
            $query  = $db->getQuery(true);
            $query->select('ca.*, pr.apply_online, pr.code, pr.link, pr.programmes as prog_type, pr.id as p_id');
            $query->from('#__emundus_setup_campaigns as ca, #__emundus_setup_programmes as pr');
            $query->where('ca.training = pr.code AND ca.published=1 AND "'.$this->now.'" <= ca.end_date and "'.$this->now.'">= ca.start_date '.$condition);
        }
        $db->setQuery($query);
        $list = (array) $db->loadObjectList();
        $this->totalCurrent = count($list);

        return $list;
    }

    public function getPaginationCurrent($condition) {
        $mainframe      = JFactory::getApplication();
        $limitstart     = $mainframe->getUserStateFromRequest('global.list.limitstart', 'limitstart', 0, 'int');
        $limitstart     = (2 != 0 ? (floor($limitstart / 2) * 2) : 0);
        $mainframe->setUserState('limitstart', $limitstart);
        jimport('joomla.html.pagination');
        return new JPagination(modEmundusCampaignHelper::getTotalCurrent($condition), $mainframe->getUserState('limitstart'), 2 );
    }

    /* **** PAST **** */
    public function getPast($condition, $teachingUnityDates = null) {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);
        if ($teachingUnityDates) {
            $query
                ->select('ca.*, pr.apply_online, pr.code, pr.link, tu.date_start as formation_start, tu.date_end as formation_end')
                ->from($db->qn('#__emundus_setup_campaigns', 'ca'))
                ->leftJoin($db->qn('#__emundus_setup_programmes', 'pr') . ' ON ' . $db->qn('pr.code') . ' = ' . $db->qn('ca.training'))
                ->leftJoin($db->qn('#__emundus_setup_teaching_unity', 'tu') . ' ON ' . $db->qn('tu.code') . ' = ' . $db->qn('ca.training').' AND '.$db->quoteName('ca.year').' = '.$db->quoteName('tu.schoolyear'))
                ->where('ca.published=1 AND "'.$this->now.'" >= ca.end_date '.$condition);
        } else {
            $query
                ->select('ca.*, pr.apply_online, pr.code, pr.link')
                ->from('#__emundus_setup_campaigns as ca, #__emundus_setup_programmes as pr')
                ->where('ca.training = pr.code AND ca.published=1 AND "'.$this->now.'" >= ca.end_date '.$condition);
        }

        $db->setQuery($query);
        $list = (array) $db->loadObjectList();
        $this->totalPast = count($list);

        return $list;
    }


    /* **** FUTUR **** */
    public function getFutur($condition, $teachingUnityDates = null) {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);

        if ($teachingUnityDates) {
            $query
                ->select('ca.*, pr.apply_online, pr.code, pr.link, tu.date_start as formation_start, tu.date_end as formation_end')
                ->from($db->qn('#__emundus_setup_campaigns', 'ca'))
                ->leftJoin($db->qn('#__emundus_setup_programmes', 'pr') . ' ON ' . $db->qn('pr.code') . ' = ' . $db->qn('ca.training'))
                ->leftJoin($db->qn('#__emundus_setup_teaching_unity', 'tu') . ' ON ' . $db->qn('tu.code') . ' = ' . $db->qn('ca.training').' AND '.$db->quoteName('ca.year').' = '.$db->quoteName('tu.schoolyear'))
                ->where('ca.published=1 AND "'.$this->now.'" <= ca.start_date '.$condition);
        } else {
            $query
                ->select('ca.*, pr.apply_online, pr.link')
                ->from('#__emundus_setup_campaigns as ca,#__emundus_setup_programmes as pr')
                ->where('ca.training = pr.code AND ca.published=1 AND "'.$this->now.'" <= ca.start_date '.$condition);
        }

        $db->setQuery($query);
        $list = (array) $db->loadObjectList();
        $this->totalFutur = count($list);

        return $list;
    }


    /* **** ALL **** */
    public function getProgram($condition, $teachingUnityDates = null) {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);

        if ($teachingUnityDates) {
            $query
                ->select('ca.*, pr.apply_online, pr.code, pr.link, tu.date_start as formation_start, tu.date_end as formation_end')
                ->from($db->qn('#__emundus_setup_campaigns', 'ca'))
                ->leftJoin($db->qn('#__emundus_setup_programmes', 'pr') . ' ON ' . $db->qn('pr.code') . ' = ' . $db->qn('ca.training'))
                ->leftJoin($db->qn('#__emundus_setup_teaching_unity', 'tu') . ' ON ' . $db->qn('tu.code') . ' = ' . $db->qn('ca.training').' AND '.$db->quoteName('ca.year').' = '.$db->quoteName('tu.schoolyear'))
                ->where('ca.training = pr.code AND ca.published=1 '.$condition);
        } else {
            $query
                ->select('ca.*, pr.apply_online, pr.code, pr.link')
                ->from('#__emundus_setup_campaigns as ca, #__emundus_setup_programmes as pr')
                ->where('ca.training = pr.code AND ca.published=1 '.$condition);
        }

        $db->setQuery($query);
        $list = (array) $db->loadObjectList();
        $this->total = count($list);

        return $list;
    }

    public function getTotalCurrent() {
        return $this->totalCurrent;
    }

    public function getTotalPast() {
        return $this->totalPast;
    }

    public function getTotalFutur() {
        return $this->totalFutur;
    }

    public function getTotal() {
        return $this->total;
    }

    function getCampaignTags($id) {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);

        $query->select('d.*')
            ->from($db->qn('data_tags', 'd'))
            ->leftJoin($db->qn('#__emundus_setup_campaigns_repeat_discipline', 'rd') . ' ON ' . $db->qn('d.id') . " = " . $db->qn("rd.discipline"))
            ->where($db->qn('d.published') . ' = 1 AND ' . $db->qn('rd.parent_id') . ' = ' . $id);

        $db->setQuery($query);
        return $db->loadAssocList('id','label');
    }

    function getReseaux($cid) {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);

        $query->select('reseaux_cult, hors_reseaux')
            ->from($db->qn('#__emundus_setup_campaigns'))
            ->where($db->qn('id') . ' = ' . $cid);

        $db->setQuery($query);
        return $db->loadObject();
    }

    /***
     * Custoom function for Nantes
     * @param $id
     *
     * @return mixed|null
     *
     * @since version
     */
    function getNantesInfos($id) {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);

        $query
            ->select([$db->quoteName('p.public'), $db->quoteName('tu.formation_length'), $db->quoteName('tu.date_start')])
            ->from($db->qn('#__emundus_setup_programmes', 'p'))
            ->leftJoin($db->quoteName('#__emundus_setup_teaching_unity', 'tu') . ' ON ' . $db->quoteName('tu.code') . ' = '. $db->quoteName('p.code'))
            ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('esc.training') . ' = '. $db->quoteName('tu.code') . ' AND ' .$db->quoteName('esc.year') . ' LIKE ' . $db->quoteName('tu.schoolyear'))
            ->where($db->quoteName('esc.id') . ' = ' . $id);

        try {
            $db->setQuery($query);
            return $db->loadObject();
        } catch (Exception $e) {
            return null;
        }
    }
}


