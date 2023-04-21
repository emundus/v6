<?php
defined('_JEXEC') or die('Restricted access');

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
            $query->select('ca.*, pr.apply_online, pr.code,pr.label as programme,pr.color as tag_color, pr.link, tu.date_start as formation_start, tu.date_end as formation_end, pr.programmes as prog_type, pr.id as p_id, pr.notes,ca.is_limited, pr.logo')
                ->from($db->qn('#__emundus_setup_campaigns', 'ca'))
                ->leftJoin($db->qn('#__emundus_setup_programmes', 'pr') . ' ON ' . $db->qn('pr.code') . ' = ' . $db->qn('ca.training'))
                ->leftJoin($db->qn('#__emundus_setup_teaching_unity', 'tu') . ' ON ' . $db->qn('tu.code') . ' = ' . $db->qn('ca.training').' AND '.$db->quoteName('ca.year').' = '.$db->quoteName('tu.schoolyear'))
                ->where('ca.published=1 AND "'.$this->now.'" <= ca.end_date and "'.$this->now.'">= ca.start_date '.$condition);
        } else {
            $query  = $db->getQuery(true);
            $query->select('ca.*, pr.apply_online, pr.code,pr.label as programme,pr.color as tag_color, pr.link, pr.programmes as prog_type, pr.id as p_id, pr.notes, pr.logo');
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
                ->select('ca.*, pr.apply_online, pr.code,pr.label as programme,pr.color as tag_color, pr.link, tu.date_start as formation_start, tu.date_end as formation_end, pr.programmes as prog_type, pr.id as p_id, pr.notes,ca.is_limited, pr.logo')
                ->from($db->qn('#__emundus_setup_campaigns', 'ca'))
                ->leftJoin($db->qn('#__emundus_setup_programmes', 'pr') . ' ON ' . $db->qn('pr.code') . ' = ' . $db->qn('ca.training'))
                ->leftJoin($db->qn('#__emundus_setup_teaching_unity', 'tu') . ' ON ' . $db->qn('tu.code') . ' = ' . $db->qn('ca.training').' AND '.$db->quoteName('ca.year').' = '.$db->quoteName('tu.schoolyear'))
                ->where('ca.published=1 AND "'.$this->now.'" >= ca.end_date '.$condition);
        } else {
            $query
                ->select('ca.*, pr.apply_online, pr.code,pr.label as programme,pr.color as tag_color, pr.link,pr.programmes as prog_type, pr.logo')
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
                ->select('ca.*, pr.apply_online, pr.code,pr.label as programme,pr.color as tag_color, pr.link, tu.date_start as formation_start, tu.date_end as formation_end, pr.programmes as prog_type, pr.id as p_id, pr.notes,ca.is_limited, pr.logo')
                ->from($db->qn('#__emundus_setup_campaigns', 'ca'))
                ->leftJoin($db->qn('#__emundus_setup_programmes', 'pr') . ' ON ' . $db->qn('pr.code') . ' = ' . $db->qn('ca.training'))
                ->leftJoin($db->qn('#__emundus_setup_teaching_unity', 'tu') . ' ON ' . $db->qn('tu.code') . ' = ' . $db->qn('ca.training').' AND '.$db->quoteName('ca.year').' = '.$db->quoteName('tu.schoolyear'))
                ->where('ca.published=1 AND "'.$this->now.'" <= ca.start_date '.$condition);
        } else {
            $query
                ->select('ca.*, pr.apply_online, pr.link,pr.label as programme,pr.color as tag_color,pr.programmes as prog_type, pr.logo')
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
                ->select('ca.*, pr.apply_online, pr.code,pr.label as programme,pr.color as tag_color, pr.link, tu.date_start as formation_start, tu.date_end as formation_end, pr.notes as desc,ca.is_limited,pr.programmes as prog_type, pr.logo')
                ->from($db->qn('#__emundus_setup_campaigns', 'ca'))
                ->leftJoin($db->qn('#__emundus_setup_programmes', 'pr') . ' ON ' . $db->qn('pr.code') . ' = ' . $db->qn('ca.training'))
                ->leftJoin($db->qn('#__emundus_setup_teaching_unity', 'tu') . ' ON ' . $db->qn('tu.code') . ' = ' . $db->qn('ca.training').' AND '.$db->quoteName('ca.year').' = '.$db->quoteName('tu.schoolyear'))
                ->where('ca.training = pr.code AND ca.published=1 '.$condition);
        } else {
            $query
                ->select('ca.*, pr.apply_online, pr.code,pr.label as programme,pr.color as tag_color, pr.link, pr.notes, pr.logo')
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

    public function getFaq(){
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);

        $query
            ->select('c.id,c.title,c.introtext')
            ->from($db->quoteName('#__content', 'c'))
            ->leftJoin($db->quoteName('#__categories', 'ca') . ' ON ' . $db->quoteName('ca.id') . ' = '. $db->quoteName('c.catid'))
            ->where($db->quoteName('ca.alias') . ' LIKE ' . $db->quote('f-a-q'))
            ->andWhere($db->quoteName('c.state') . ' = 1');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            return null;
        }
    }

    public function getFormationsWithType() {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);

        $query
            ->select('*')
            ->from($db->quoteName('data_formation'));

        try {
            $db->setQuery($query);

            $formations = $db->loadObjectList();

            foreach ($formations as $formation) {
                $query
                    ->clear()
                    ->select('repeat.voie_d_acces')
                    ->from($db->quoteName('data_acces_formation_repeat_voie_d_acces', 'repeat'))
                    ->leftJoin($db->quoteName('data_acces_formation', 'daf') . ' ON ' . $db->quoteName('repeat.parent_id') . ' = '. $db->quoteName('daf.id'))
                    ->where($db->quoteName('daf.id') . ' = ' . $formation->id);

                $formation->voies_d_acces = $db->setQuery($query)->loadObjectList();
            }

            return $formations;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getFormationTypes() {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);

        $query
            ->select('*')
            ->from($db->quoteName('data_formation_type'));

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            return null;
        }
    }

    public function getFormationLevels() {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);

        $query
            ->select('*')
            ->from($db->quoteName('data_formation_level'));

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            return null;
        }
    }

    public function getVoiesDAcces() {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);

        $query
            ->select('*')
            ->from($db->quoteName('data_voies_d_acces'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('order'));

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            return null;
        }
    }

    public function addClassToData($data, $formations)
    {
        // Add a custom class parameter to data items
        $data = array_map(function($item) use ($formations) {
            $item->class = !isset($item->class) ? '' : $item->class;

            // find formation associated to item inside formations array
            foreach ($formations as $formation) {
                if ($formation->id == $item->formation) {
                    $item->class .= 'formation_type-' . $formation->type;
                    $item->class .= ' formation_level-' . $formation->level;

                    foreach ($formation->voies_d_acces as $voie) {
                        $item->class .= ' voie_d_acces-' . $voie->voie_d_acces;

                    }

                    break;
                }
            }

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('label')
                ->from('#__emundus_setup_campaigns')
                ->where('id = '.$item->id);

            $db->setQuery($query);
            $item->label = $db->loadResult();

            return $item;
        }, $data);

        return $data;
    }

	public function getLinks(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		try {
			$query->select('params')
				->from($db->quoteName('#__modules'))
				->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emundus_user_dropdown'))
				->andWhere($db->quoteName('published') . ' = 1');
			$db->setQuery($query);
			$params = $db->loadResult();

			if(!empty($params)){
				$params = json_decode($params);
			}

			return $params;
		}
		catch (Exception $e) {
			return new stdClass();
		}
	}
}
