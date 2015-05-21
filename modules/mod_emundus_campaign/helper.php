<?php
	defined('_JEXEC') or die('Access Deny');

    //JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus/models', 'EmundusModel');
    require_once (JPATH_SITE . '/components/com_emundus/models/files.php');

	class modEmundusCampaignHelper
	{

        static function getPagination()
        {
            $model = new EmundusModelFiles;
            return $model->getPagination();

        }

        /* **** CURRENT **** */
        static function getCurrent($condition)
        {
            

            $db = JFactory::getDbo();
            $query	= $db->getQuery(true);
            $query->select('pr.url,ca.*, pr.notes, pr.code, pr.apply_online');
            $query->from('#__emundus_setup_programmes as pr,#__emundus_setup_campaigns as ca');
            $query->where('ca.training = pr.code AND ca.published=1 AND Now() <= ca.end_date and Now()>= ca.start_date '.$condition);

            $db->setQuery($query);
            return (array) $db->loadObjectList();
        }
        static function getTotalCurrent($condition)
        {
            $db = JFactory::getDbo();
            $query	= $db->getQuery(true);
            $query->select('COUNT(*)');
            $query->from('#__emundus_setup_programmes as pr,#__emundus_setup_campaigns as ca');
            $query->where('ca.training = pr.code AND ca.published=1 AND Now() <= ca.end_date and Now()>= ca.start_date '.$condition);
            $db->setQuery($query);
            return $db->loadResult();
        }
        static function getPaginationCurrent($condition) {
            $mainframe = JFactory::getApplication();
            $limitstart 			= $mainframe->getUserStateFromRequest('global.list.limitstart', 'limitstart', 0, 'int');
            $limitstart 			= (2 != 0 ? (floor($limitstart / 2) * 2) : 0);
            $mainframe->setUserState('limitstart', $limitstart);
            jimport('joomla.html.pagination');
            $pagination = new JPagination(modEmundusCampaignHelper::getTotalCurrent($condition), $mainframe->getUserState('limitstart'), 2 );
            return $pagination;
        }

        /* **** PAST **** */
        static function getPast($condition)
        {
            $db = JFactory::getDbo();
            $query	= $db->getQuery(true);
            $query->select('pr.url,ca.*, pr.notes, pr.code, pr.apply_online');
            $query->from('#__emundus_setup_programmes as pr,#__emundus_setup_campaigns as ca');
            $query->where('ca.training = pr.code AND ca.published=1 AND Now() >= ca.end_date '.$condition);

            $db->setQuery($query);
            return (array) $db->loadObjectList();
        }
        function getTotalPast($condition)
        {
            // Load the content if it doesn't already exist
            if (empty($this->_total)) {
                $db = JFactory::getDbo();
                $query	= $db->getQuery(true);
                $query->select('pr.url,ca.*, pr.notes, pr.code, pr.apply_online');
                $query->from('#__emundus_setup_programmes as pr,#__emundus_setup_campaigns as ca');
                $query->where('ca.training = pr.code AND ca.published=1 AND Now() >= ca.end_date '.$condition);

                $this->_total = $this->_getListCount($query);
            }
            return $this->_total;
        }


        /* **** FUTUR **** */
        static function getFutur($condition)
        {
            $db = JFactory::getDbo();
            $query	= $db->getQuery(true);
            $query->select('pr.url,ca.*, pr.notes, pr.code, pr.apply_online');
            $query->from('#__emundus_setup_programmes as pr,#__emundus_setup_campaigns as ca');
            $query->where('ca.training = pr.code AND ca.published=1 AND Now() <= ca.start_date '.$condition);

            $db->setQuery($query);
            return (array) $db->loadObjectList();
        }
        function getTotalFutur($condition)
        {
            // Load the content if it doesn't already exist
            if (empty($this->_total)) {
                $db = JFactory::getDbo();
                $query	= $db->getQuery(true);
                $query->select('pr.url,ca.*, pr.notes, pr.code, pr.apply_online');
                $query->from('#__emundus_setup_programmes as pr,#__emundus_setup_campaigns as ca');
                $query->where('ca.training = pr.code AND ca.published=1 AND Now() <= ca.start_date '.$condition);

                $this->_total = $this->_getListCount($query);
            }
            return $this->_total;
        }


        /* **** ALL **** */
        static function getProgram($condition)
        {
            $db = JFactory::getDbo();
            $query	= $db->getQuery(true);
            $query->select('pr.url,ca.*, pr.notes, pr.code, pr.apply_online');
            $query->from('#__emundus_setup_programmes as pr,#__emundus_setup_campaigns as ca');
            $query->where('ca.training = pr.code AND ca.published=1 '.$condition);

            $db->setQuery($query);
            return (array) $db->loadObjectList();
        }
        function getTotal($condition)
        {
            // Load the content if it doesn't already exist
            if (empty($this->_total)) {
                $db = JFactory::getDbo();
                $query	= $db->getQuery(true);
                $query->select('pr.url,ca.*, pr.notes, pr.code, pr.apply_online');
                $query->from('#__emundus_setup_programmes as pr,#__emundus_setup_campaigns as ca');
                $query->where('ca.training = pr.code AND ca.published=1 '.$condition);

                $this->_total = $this->_getListCount($query);
            }
            return $this->_total;
        }
    }
?>