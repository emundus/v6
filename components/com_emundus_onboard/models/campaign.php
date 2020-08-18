<?php
/**
 * Messages model used for the new message dialog.
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
use Joomla\CMS\Date\Date;

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_onboard/models');

class EmundusonboardModelcampaign extends JModelList
{
    var $model_program = null;
    public function __construct($config = array()) {
        parent::__construct($config);
        $this->model_program = JModelLegacy::getInstance('program', 'EmundusonboardModel');
    }

    function getCampaignCount($filter, $recherche)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $date = new Date();

        // Get affected programs
        $user = JFactory::getUser();
        $programs = $this->model_program->getUserPrograms($user->id);
        //

        if ($filter == 'notTerminated') {
            $filterCount =
                'Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00"';
        } elseif ($filter == 'Terminated') {
            $filterCount =
                'Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' <= ' .
                $db->quote($date) .
                ' AND end_date != "0000-00-00 00:00:00"';
        } elseif ($filter == 'Publish') {
            $filterCount =
                $db->quoteName('sc.published') .
                ' = 1 AND (Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00")';
        } elseif ($filter == 'Unpublish') {
            $filterCount =
                $db->quoteName('sc.published') .
                ' = 0 AND (Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00")';
        } else {
            $filterCount = '1';
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        } else {
            $rechercheLbl =
                $db->quoteName('sc.label') .
                ' LIKE ' .
                $db->quote('%' . $recherche . '%');
            $rechercheResume =
                $db->quoteName('sc.short_description') .
                ' LIKE ' .
                $db->quote('%' . $recherche . '%');
            $rechercheDescription =
                $db->quoteName('sc.description') .
                ' LIKE ' .
                $db->quote('%' . $recherche . '%');
            $fullRecherche =
                $rechercheLbl .
                ' OR ' .
                $rechercheResume .
                ' OR ' .
                $rechercheDescription;
        }

        $query
            ->select('COUNT(sc.id)')
            ->from($db->quoteName('#__emundus_setup_campaigns', 'sc'))
            ->where($filterCount)
            ->andWhere($fullRecherche)
            ->andWhere($db->quoteName('sc.training') . ' IN (' . implode(',',$db->quote($programs)) . ')');

        try {
            $db->setQuery($query);
            return $db->loadResult();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return 0;
        }
    }

    function getAssociatedCampaigns($filter, $sort, $recherche, $lim, $page) {
        if (empty($lim)) {
            $limit = 25;
        } else {
            $limit = $lim;
        }

        if (empty($page)) {
            $offset = 0;
        } else {
            $offset = ($page - 1) * $limit;
        }

        if (empty($sort)) {
            $sort = 'DESC';
        }

        $sortDb = 'sc.id ';

        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $date = new Date();

        // Get affected programs
        $user = JFactory::getUser();
        $programs = $this->model_program->getUserPrograms($user->id);
        //

        if ($filter == 'notTerminated') {
            $filterDate =
                'Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00"';
        } elseif ($filter == 'Terminated') {
            $filterDate =
                'Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' <= ' .
                $db->quote($date) .
                ' AND end_date != "0000-00-00 00:00:00"';
        } elseif ($filter == 'Publish') {
            $filterDate =
                $db->quoteName('sc.published') .
                ' = 1 AND (Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00")';
        } elseif ($filter == 'Unpublish') {
            $filterDate =
                $db->quoteName('sc.published') .
                ' = 0 AND (Date(' .
                $db->quoteName('sc.end_date') .
                ')' .
                ' >= ' .
                $db->quote($date) .
                ' OR end_date = "0000-00-00 00:00:00")';
        } else {
            $filterDate = '1';
        }

        if (empty($recherche)) {
            $fullRecherche = 1;
        } else {
            $rechercheLbl =
                $db->quoteName('sc.label') .
                ' LIKE ' .
                $db->quote('%' . $recherche . '%');
            $rechercheResume =
                $db->quoteName('sc.short_description') .
                ' LIKE ' .
                $db->quote('%' . $recherche . '%');
            $rechercheDescription =
                $db->quoteName('sc.description') .
                ' LIKE ' .
                $db->quote('%' . $recherche . '%');
            $fullRecherche =
                $rechercheLbl .
                ' OR ' .
                $rechercheResume .
                ' OR ' .
                $rechercheDescription;
        }

        $query
            ->select([
                'sc.*',
                'COUNT(cc.id) AS nb_files',
                'sp.label AS program_label',
                'sp.id AS program_id',
                'sp.published AS published_prog'
            ])
            ->from($db->quoteName('#__emundus_setup_campaigns', 'sc'))
            ->leftJoin(
                $db->quoteName('#__emundus_campaign_candidature', 'cc') .
                ' ON ' .
                $db->quoteName('cc.campaign_id') .
                ' = ' .
                $db->quoteName('sc.id')
            )
            ->leftJoin(
                $db->quoteName('#__emundus_setup_programmes', 'sp') .
                ' ON ' .
                $db->quoteName('sp.code') .
                ' LIKE ' .
                $db->quoteName('sc.training')
            )
            ->where($filterDate)
            ->andWhere($fullRecherche)
            ->andWhere($db->quoteName('sc.training') . ' IN (' . implode(',',$db->quote($programs)) . ')')
            ->group($sortDb)
            ->order($sortDb . $sort);

        try {
            $db->setQuery($query, $offset, $limit);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return new stdClass();
        }
    }

    function getCampaignsByProgram($program){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $date = new Date();

        $query
            ->select('sc.*')
            ->from($db->quoteName('#__emundus_setup_programmes','sp'))
            ->leftJoin(
                $db->quoteName('#__emundus_setup_campaigns', 'sc') .
                ' ON ' .
                $db->quoteName('sp.code') .
                ' LIKE ' .
                $db->quoteName('sc.training')
            )
            ->where($db->quoteName('sp.id') . ' = ' . $db->quote($program))
            ->andWhere($db->quoteName('sc.end_date') . ' >= ' . $db->quote($date));

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e){
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return new stdClass();
        }
    }

    public function deleteCampaign($data) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');

        if (count($data) > 0) {
            try {
                foreach (array_values($data) as $id) {
                    $falang->deleteFalang($id,'emundus_setup_campaigns','label');
                }

                $cc_conditions = [
                    $db->quoteName('campaign_id').' IN ('.implode(", ", array_values($data)).')'
                ];

                $query->delete($db->quoteName('#__emundus_campaign_candidature'))
                    ->where($cc_conditions);

                $db->setQuery($query);
                $db->execute();

                $sc_conditions = [
                    $db->quoteName('id').' IN ('.implode(", ", array_values($data)).')'
                ];

                $query->clear()
                    ->delete($db->quoteName('#__emundus_setup_campaigns'))
                    ->where($sc_conditions);

                $db->setQuery($query);
                return $db->execute();

            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    public function unpublishCampaign($data) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (!empty($data)) {

            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            try {
                $fields = [
                	$db->quoteName('published').' = 0'
                ];
                $sc_conditions = [
                	$db->quoteName('id').' IN ('.implode(", ", array_values($data)).')'
                ];

                $query->update($db->quoteName('#__emundus_setup_campaigns'))
                    ->set($fields)
                    ->where($sc_conditions);

                $db->setQuery($query);
                return $db->execute();
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    public function publishCampaign($data) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            try {
                $fields = [$db->quoteName('published') . ' = 1'];
                $sc_conditions = [$db->quoteName('id').' IN ('.implode(", ", array_values($data)).')'];

                $query->update($db->quoteName('#__emundus_setup_campaigns'))
                    ->set($fields)
                    ->where($sc_conditions);

                $db->setQuery($query);
                return $db->execute();
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    public function duplicateCampaign($data) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (!empty($data)) {
            try {
                $columns = array_keys(
                    $db->getTableColumns('#__emundus_setup_campaigns')
                );

                $columns = array_filter($columns, function ($k) {
                    return $k != 'id' && $k != 'date_time';
                });

                foreach ($data as $id) {
                    $query->clear()
                        ->select(implode(',', $db->qn($columns)))
                        ->from($db->quoteName('#__emundus_setup_campaigns'))
                        ->where($db->quoteName('id') . ' = ' . $id);

                    $db->setQuery($query);
                    $values[] = implode(', ', $db->quote($db->loadRow()));
                }

                $query->clear()
                    ->insert($db->quoteName('#__emundus_setup_campaigns'))
                    ->columns(implode(',', $db->quoteName($columns)))
                    ->values($values);

                $db->setQuery($query);
                return $db->execute();
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    //TODO Throw in the years model
    function getYears() {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('DISTINCT(tu.schoolyear)')
            ->from($db->quoteName('#__emundus_setup_teaching_unity', 'tu'))
            ->order('tu.id DESC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return [];
        }
    }

    public function createCampaign($data) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');
        $settings = JModelLegacy::getInstance('settings', 'EmundusonboardModel');

        $i = 0;

        $label_fr = '';
        $label_en = '';

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                if ($key == 'profileLabel') {
                    array_splice($data, $i, 1);
                }
                if ($key == 'label') {
                    $label_fr = $data['label']['fr'];
                    $label_en = $data['label']['en'];
                    $data['label'] = $data['label']['fr'];
                }
                $i++;
            }

            $query->insert($db->quoteName('#__emundus_setup_campaigns'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',', $db->Quote(array_values($data))));

            try {
                $db->setQuery($query);
                $db->execute();
                $campaign_id = $db->insertid();

                $falang->insertFalang($label_fr,$label_en,$campaign_id,'emundus_setup_campaigns','label');

                $user = JFactory::getUser();
                $settings->onAfterCreateCampaign($user->id);

                return $campaign_id;
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    public function updateCampaign($data, $cid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');

        $label_fr = '';
        $label_en = '';

        if (!empty($data)) {
            $fields = [];

            foreach ($data as $key => $val) {
                if ($key == 'label') {
                    $label_fr = $data['label']['fr'];
                    $label_en = $data['label']['en'];
                    $data['label'] = $data['label']['fr'];
                }
                if ($key !== 'profileLabel') {
                    $insert = $db->quoteName(htmlspecialchars($key)) . ' = ' . $db->quote(htmlspecialchars($val));
                    $fields[] = $insert;
                }
            }

            $falang->updateFalang($label_fr,$label_en,$cid,'emundus_setup_campaigns','label');

            $query->update($db->quoteName('#__emundus_setup_campaigns'))
                ->set($fields)
                ->where($db->quoteName('id') . ' = ' . $db->quote($cid));

            try {
                $db->setQuery($query);
                return $db->execute();
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    public function createYear($data) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if (!empty($data)) {

            foreach ($data as $key => $val) {
                $data[$key] = htmlspecialchars($data[$key]);
            }

            $query->insert($db->quoteName('#__emundus_setup_teaching_unity'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',', $db->Quote(array_values($data))));

            try {
                $db->setQuery($query);
                return $db->execute();
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    public function getCampaignById($id) {
        if (empty($id)) {
            return false;
        }

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $results = new stdClass();

        $query->select(['sc.*', 'spr.label AS profileLabel'])
            ->from($db->quoteName('#__emundus_setup_campaigns', 'sc'))
            ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'spr').' ON '.$db->quoteName('spr.id').' = '.$db->quoteName('sc.profile_id'))
            ->where($db->quoteName('sc.id') . ' = ' . $id);

        try {
            $db->setQuery($query);
            $results->campaign = $db->loadObject();
            try {
                $results->label = $falang->getFalang($id,'emundus_setup_campaigns','label');
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return false;
            }
            return $results;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    public function getCreatedCampaign() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $currentDate = date('Y-m-d H:i:s');

        $query->select('*')
            ->from($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('date_time') . ' = ' . $db->quote($currentDate));

        $db->setQuery($query);

        try {
            $db->setQuery($query);
            return $db->loadObject();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    public function updateProfile($profile, $campaign) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $form = JModelLegacy::getInstance('form', 'EmundusonboardModel');

        $query->select('year')
            ->from($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($campaign));

        try {
            $db->setQuery($query);
            $schoolyear = $db->loadResult();

            $query->clear()
                ->update($db->quoteName('#__emundus_setup_attachment_profiles'))
                ->set($db->quoteName('profile_id') . ' = ' . $db->quote($profile))
                ->where($db->quoteName('campaign_id') . ' = ' . $db->quote($campaign));
            $db->setQuery($query);
            $db->execute();

            // Create checklist menu if documents are asked
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('alias') . ' = ' . $db->quote('checklist-' . $profile));
            $db->setQuery($query);
            $checklist = $db->loadObject();

            if ($checklist == null) {
                $form->addChecklistMenu($profile);
            }

            $query->clear()
                ->update($db->quoteName('#__emundus_setup_campaigns'))
                ->set($db->quoteName('profile_id') . ' = ' . $db->quote($profile))
                ->where($db->quoteName('id') . ' = ' . $db->quote($campaign));

            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->update($db->quoteName('#__emundus_setup_teaching_unity'))
                ->set($db->quoteName('profile_id') . ' = ' . $db->quote($profile))
                ->where($db->quoteName('schoolyear') . ' = ' . $db->quote($schoolyear));

            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    public function getCampaignsToAffect() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $date = new Date();

        // Get affected programs
        $user = JFactory::getUser();
        $programs = $this->model_program->getUserPrograms($user->id);
        //

        $query->select('id,label')
            ->from($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('profile_id') . ' IS NULL')
            ->andWhere($db->quoteName('end_date') . ' >= ' . $db->quote($date))
            ->andWhere($db->quoteName('training') . ' IN (' . implode(',',$db->quote($programs)) . ')');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    public function getCampaignsToAffectByTerm($term){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $date = new Date();

        // Get affected programs
        $user = JFactory::getUser();
        $programs = $this->model_program->getUserPrograms($user->id);
        //

        $searchName = $db->quoteName('label').' LIKE '.$db->quote('%' . $term . '%');

        $query->select('id,label')
            ->from($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('profile_id') . ' IS NULL')
            ->andWhere($db->quoteName('end_date') . ' >= ' . $db->quote($date))
            ->andWhere($searchName)
            ->andWhere($db->quoteName('training') . ' IN (' . implode(',',$db->quote($programs)) . ')');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    public function createDocument($document,$types,$cid,$pid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');

        $types = implode(";", array_values($types));

        $query
            ->insert($db->quoteName('#__emundus_setup_attachments'));
        $query
            ->set($db->quoteName('lbl') . ' = ' . $db->quote('_em'))
            ->set($db->quoteName('value') . ' = ' . $db->quote($document['name']['fr']))
            ->set($db->quoteName('description') . ' = ' . $db->quote($document['description']['fr']))
            ->set($db->quoteName('allowed_types') . ' = ' . $db->quote($types))
            ->set($db->quoteName('ordering') . ' = ' . $db->quote(0))
            ->set($db->quoteName('nbmax') . ' = ' . $db->quote($document['nbmax']));

        try{
            $db->setQuery($query);
            $db->execute();
            $newdocument = $db->insertid();

            $falang->insertFalang($document['name']['fr'],$document['name']['en'],$newdocument,'emundus_setup_attachments','value');
            $falang->insertFalang($document['description']['fr'],$document['description']['en'],$newdocument,'emundus_setup_attachments','description');

            $query
                ->clear()
                ->update($db->quoteName('#__emundus_setup_attachments'))
                ->set($db->quoteName('lbl') . ' = ' . $db->quote('_em' . $newdocument))
                ->where($db->quoteName('id') . ' = ' . $db->quote($newdocument));
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->select('max(ordering)')
                ->from($db->quoteName('#__emundus_setup_attachment_profiles'))
                ->where($db->quoteName('campaign_id') . ' = ' . $db->quote($cid));
            $db->setQuery($query);
            $ordering = $db->loadResult();

            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_attachment_profiles'));
            $query->set($db->quoteName('profile_id') . ' = ' . $db->quote($pid))
                ->set($db->quoteName('campaign_id') . ' = ' . $db->quote($cid))
                ->set($db->quoteName('attachment_id') . ' = ' . $db->quote($newdocument))
                ->set($db->quoteName('mandatory') . ' = ' . $db->quote(0))
                ->set($db->quoteName('ordering') . ' = ' . $db->quote($ordering + 1));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return $e->getMessage();
        }
    }
}
