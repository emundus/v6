<?php
	defined('_JEXEC') or die('Access Deny');

    //JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus/models', 'EmundusModel');


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
        }

        /* **** CURRENT **** */
        public function getCurrent($condition)
        {
            $db = JFactory::getDbo();
            $query	= $db->getQuery(true);
            $query->select('ca.*, pr.apply_online');
            $query->from('#__emundus_setup_campaigns as ca, #__emundus_setup_programmes as pr');
            $query->where('ca.training = pr.code AND ca.published=1 AND Now() <= ca.end_date and Now()>= ca.start_date '.$condition);

            //
            $db->setQuery($query);
            //die(str_replace('#_', 'jos', $db->getQuery()));
            $list = (array) $db->loadObjectList();
            $this->totalCurrent = count($list);

            return $list;
        }

        public function getPaginationCurrent($condition) {
            $mainframe      = JFactory::getApplication();
            $limitstart 	= $mainframe->getUserStateFromRequest('global.list.limitstart', 'limitstart', 0, 'int');
            $limitstart 	= (2 != 0 ? (floor($limitstart / 2) * 2) : 0);
            $mainframe->setUserState('limitstart', $limitstart);
            jimport('joomla.html.pagination');
            $pagination = new JPagination(modEmundusCampaignHelper::getTotalCurrent($condition), $mainframe->getUserState('limitstart'), 2 );
            return $pagination;
        }

        /* **** PAST **** */
        public function getPast($condition)
        {
            $db = JFactory::getDbo();
            $query	= $db->getQuery(true);
            $query->select('ca.*, pr.apply_online');
            $query->from('#__emundus_setup_campaigns as ca, #__emundus_setup_programmes as pr');
            $query->where('ca.training = pr.code AND ca.published=1 AND Now() >= ca.end_date '.$condition);

            $db->setQuery($query);
            $list = (array) $db->loadObjectList();
            $this->totalPast = count($list);

            return $list;
        }


        /* **** FUTUR **** */
        public function getFutur($condition)
        {
            $db = JFactory::getDbo();
            $query	= $db->getQuery(true);
            $query->select('ca.*, pr.apply_online');
            $query->from('#__emundus_setup_campaigns as ca,#__emundus_setup_programmes as pr');
            $query->where('ca.training = pr.code AND ca.published=1 AND Now() <= ca.start_date '.$condition);

            $db->setQuery($query);
            $list = (array) $db->loadObjectList();
            $this->totalFutur = count($list);

            return $list;
        }


        /* **** ALL **** */
        public function getProgram($condition)
        {
            $db = JFactory::getDbo();
            $query	= $db->getQuery(true);
            $query->select('ca.*, pr.apply_online');
            $query->from('#__emundus_setup_campaigns as ca, #__emundus_setup_programmes as pr');
            $query->where('ca.training = pr.code AND ca.published=1 '.$condition);

            $db->setQuery($query);
            $list = (array) $db->loadObjectList();
            $this->total = count($list);

            return $list;
        }

        public function getTotalCurrent()
        {
            return $this->totalCurrent;
        }

        public function getTotalPast()
        {
            return $this->totalPast;
        }

        public function getTotalFutur()
        {
            return $this->totalFutur;
        }

        public function getTotal()
        {
            return $this->total;
        }
    }
?>