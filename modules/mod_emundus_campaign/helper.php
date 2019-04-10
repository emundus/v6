<?php
    defined('_JEXEC') or die('Access Deny');

    class modEmundusCampaignHelper
    {
        private $totalCurrent;
        private $totalFutur;
        private $totalPast;
        private $total;

        public function __construct()
        {
            $this->totalCurrent=0;
            $this->totalFutur=0;
            $this->totalPast=0;
            $this->total=0;
            $this->offset=JFactory::getApplication()->get('offset', 'UTC');
            try {
                $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
                $dateTime = $dateTime->setTimezone(new DateTimeZone($this->offset));
                $this->now = $dateTime->format('Y-m-d H:i:s');
                //echo "::".$this->now;
            } catch(Exception $e) {
                echo $e->getMessage() . '<br />';
            }
        }

        /* **** CURRENT **** */
        public function getCurrent($condition, $teachingUnityDates = null)
        {
            $db = JFactory::getDbo();
            $query  = $db->getQuery(true);
            if ($teachingUnityDates) {
                $query
                    ->select('ca.*, pr.*, tu.date_start as formation_start, tu.date_end as formation_end')
                    ->from($db->qn('#__emundus_setup_campaigns', 'ca'))
                    ->join('LEFT', $db->qn('#__emundus_setup_programmes', 'pr') . ' ON ' . $db->qn('pr.code') . ' = ' . $db->qn('ca.training'))
                    ->join('LEFT', $db->qn('#__emundus_setup_teaching_unity', 'tu') . ' ON ' . $db->qn('tu.code') . ' = ' . $db->qn('ca.training'))
                    ->where('ca.training = pr.code AND ca.published=1 AND "'.$this->now.'" <= ca.end_date and "'.$this->now.'">= ca.start_date '.$condition);
            }
            else {
                $query  = $db->getQuery(true);
                $query->select('ca.*, pr.*');
                $query->from('#__emundus_setup_campaigns as ca, #__emundus_setup_programmes as pr');
                $query->where('ca.training = pr.code AND ca.published=1 AND "'.$this->now.'" <= ca.end_date and "'.$this->now.'">= ca.start_date '.$condition);
            }
            //
            $db->setQuery($query);
            //die(str_replace('#_', 'jos', $db->getQuery()));
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
            $pagination = new JPagination(modEmundusCampaignHelper::getTotalCurrent($condition), $mainframe->getUserState('limitstart'), 2 );
            return $pagination;
        }

        /* **** PAST **** */
        public function getPast($condition, $teachingUnityDates = null) {
            $db = JFactory::getDbo();
            $query  = $db->getQuery(true);
            if ($teachingUnityDates) {
                $query
                    ->select('ca.*, pr.*, tu.date_start as formation_start, tu.date_end as formation_end')
                    ->from($db->qn('#__emundus_setup_campaigns', 'ca'))
                    ->join('LEFT', $db->qn('#__emundus_setup_programmes', 'pr') . ' ON ' . $db->qn('pr.code') . ' = ' . $db->qn('ca.training'))
                    ->join('LEFT', $db->qn('#__emundus_setup_teaching_unity', 'tu') . ' ON ' . $db->qn('tu.code') . ' = ' . $db->qn('ca.training'))
                    ->where('ca.training = pr.code AND ca.published=1 AND "'.$this->now.'" >= ca.end_date '.$condition);
            }
            else {
                $query
                    ->select('ca.*, pr.*')
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
                    ->select('ca.*, pr.*, tu.date_start as formation_start, tu.date_end as formation_end')
                    ->from($db->qn('#__emundus_setup_campaigns', 'ca'))
                    ->join('LEFT', $db->qn('#__emundus_setup_programmes', 'pr') . ' ON ' . $db->qn('pr.code') . ' = ' . $db->qn('ca.training'))
                    ->join('LEFT', $db->qn('#__emundus_setup_teaching_unity', 'tu') . ' ON ' . $db->qn('tu.code') . ' = ' . $db->qn('ca.training'))
                    ->where('ca.training = pr.code AND ca.published=1 AND "'.$this->now.'" <= ca.start_date '.$condition);
            }
            else {
                $query
                    ->select('ca.*, pr.apply_online')
                    ->from('#__emundus_setup_campaigns as ca,#__emundus_setup_programmes as pr')
                    ->where('ca.training = pr.code AND ca.published=1 AND "'.$this->now.'" <= ca.start_date '.$condition);
            }

            $db->setQuery($query);
            $list = (array) $db->loadObjectList();
            $this->totalFutur = count($list);

            return $list;
        }


        /* **** ALL **** */
        public function getProgram($condition) {
            $db = JFactory::getDbo();
            $query  = $db->getQuery(true);
            $query->select('ca.*, pr.apply_online, pr.code');
            $query->from('#__emundus_setup_campaigns as ca, #__emundus_setup_programmes as pr');
            $query->where('ca.training = pr.code AND ca.published=1 '.$condition);

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
    }
?>