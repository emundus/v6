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
        JPluginHelper::importPlugin('emundus');
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
            $fullRecherche =
                $db->quoteName('sc.label') .
                ' LIKE ' .
                $db->quote('%' . $recherche . '%');
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
            JLog::add('component/com_emundus_onboard/models/campaign | Error when try to get number of campaigns : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return 0;
        }
    }

    function getAssociatedCampaigns($filter, $sort, $recherche, $lim, $page) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

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
            $fullRecherche =
                $db->quoteName('sc.label') .
                ' LIKE ' .
                $db->quote('%' . $recherche . '%');
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
            JLog::add('component/com_emundus_onboard/models/campaign | Error when try to get list of campaigns : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus_onboard/models/campaign | Error when try to get campaigns associated to programs : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return new stdClass();
        }
    }

    public function deleteCampaign($data) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');

        if (count($data) > 0) {
            try {
                $dispatcher = JEventDispatcher::getInstance();
                $dispatcher->trigger('onBeforeCampaignDelete', $data);
                $dispatcher->trigger('callEventHandler', ['onBeforeCampaignDelete', ['campaign' => $data]]);

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
                $res = $db->execute();

                if ($res) {
                    $dispatcher->trigger('onAfterCampaignDelete', $data);
                    $dispatcher->trigger('callEventHandler', ['onAfterCampaignDelete', ['campaign' => $data]]);
                }
                return $res;
            } catch (Exception $e) {
                JLog::add('component/com_emundus_onboard/models/campaign | Error when delete campaigns : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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

            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onBeforeCampaignUnpublish', $data);
            $dispatcher->trigger('callEventHandler', ['onBeforeCampaignUnpublish', ['campaign' => $data]]);

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
                $res = $db->execute();

                if ($res) {
                    $dispatcher->trigger('onAfterCampaignUnpublish', $data);
                    $dispatcher->trigger('callEventHandler', ['onAfterCampaignUnpublish', ['campaign' => $data]]);
                }
                return $res;
            } catch (Exception $e) {
                JLog::add('component/com_emundus_onboard/models/campaign | Error when unpublish campaigns : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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

            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onBeforeCampaignPublish', $data);
            $dispatcher->trigger('callEventHandler', ['onBeforeCampaignPublish', ['campaign' => $data]]);
            try {
                $fields = [$db->quoteName('published') . ' = 1'];
                $sc_conditions = [$db->quoteName('id').' IN ('.implode(", ", array_values($data)).')'];

                $query->update($db->quoteName('#__emundus_setup_campaigns'))
                    ->set($fields)
                    ->where($sc_conditions);

                $db->setQuery($query);
                $res = $db->execute();

                if ($res) {
                    $dispatcher->trigger('onAfterCampaignPublish', $data);
                    $dispatcher->trigger('callEventHandler', ['onAfterCampaignPublish', ['campaign' => $data]]);
                }
                return $res;
            } catch (Exception $e) {
                JLog::add('component/com_emundus_onboard/models/campaign | Error when publish campaigns : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
                JLog::add('component/com_emundus_onboard/models/campaign | Error when duplicate campaigns : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add(preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_onboard');
            return [];
        }
    }

    public function createCampaign($data) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');
        $settings = JModelLegacy::getInstance('settings', 'EmundusonboardModel');
        $email = JModelLegacy::getInstance('email', 'EmundusonboardModel');

        $lang = JFactory::getLanguage();
        $actualLanguage = substr($lang->getTag(), 0 , 2);

        $i = 0;

        $labels = new stdClass;
        $limit_status = [];

        if (!empty($data)) {

            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onBeforeCampaignCreate', $data);
            $dispatcher->trigger('callEventHandler', ['onBeforeCampaignCreate', ['campaign' => $data]]);

            foreach ($data as $key => $val) {
                if ($key == 'profileLabel') {
                    array_splice($data, $i, 1);
                }
                if ($key == 'label') {
                    $labels->fr = $data['label']['fr'];
                    $labels->en = $data['label']['en'];
                    $data['label'] = $data['label'][$actualLanguage];
                }
                if ($key == 'limit_status') {
                    $limit_status = $data['limit_status'];
                    array_splice($data, $i, 1);
                }
                if ($key == 'profile_id') {
                    $query->select('id')
                        ->from($db->quoteName('#__emundus_setup_profiles'))
                        ->where($db->quoteName('published') . ' = 1')
                        ->andWhere($db->quoteName('status') . ' = 1');
                    $db->setQuery($query);
                    $data['profile_id'] = $db->loadResult();
                    if(empty($data['profile_id'])){
                        unset($data['profile_id']);
                        $data['published'] = 0;
                    }
                }
                $i++;
            }

            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_campaigns'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',', $db->Quote(array_values($data))));

            try {
                $db->setQuery($query);
                $db->execute();
                $campaign_id = $db->insertid();

                $falang->insertFalang($labels,$campaign_id,'emundus_setup_campaigns','label');

                if($data['is_limited'] == 1){
                    foreach ($limit_status as $key => $limit_statu) {
                        if($limit_statu == 'true'){
                            $query->clear()
                                ->insert($db->quoteName('#__emundus_setup_campaigns_repeat_limit_status'));
                            $query->set($db->quoteName('parent_id') . ' = ' . $db->quote($campaign_id))
                                ->set($db->quoteName('limit_status') . ' = ' . $db->quote($key));
                            $db->setQuery($query);
                            $db->execute();
                        }
                    }
                }

                $user = JFactory::getUser();
                $settings->onAfterCreateCampaign($user->id);

                // Create a default trigger
                $query->clear()
                    ->select('id')
                    ->from($db->quoteName('#__emundus_setup_programmes'))
                    ->where($db->quoteName('code') . ' LIKE ' . $db->quote($data['training']));
                $db->setQuery($query);
                $pid = $db->loadResult();

                $emails = $email->getTriggersByProgramId($pid);

                if(empty($emails)) {
                    $trigger = array(
                        'status' => 1,
                        'model' => 1,
                        'action_status' => 'to_current_user',
                        'target' => -1,
                        'program' => $pid,
                    );
                    $email->createTrigger($trigger, array(), $user);
                }
                //

                // Create teaching unity
                $this->createYear($data);
                //

                $dispatcher->trigger('onAfterCampaignCreate', $campaign_id);
                $dispatcher->trigger('callEventHandler', ['onAfterCampaignCreate', ['campaign' => $campaign_id]]);

                return $campaign_id;
            } catch (Exception $e) {
                JLog::add('component/com_emundus_onboard/models/campaign | Error when create the campaign : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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

        $lang = JFactory::getLanguage();
        $actualLanguage = substr($lang->getTag(), 0 , 2);

        $limit_status = [];

        if (!empty($data)) {
            $fields = [];
            $labels = new stdClass;

            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onBeforeCampaignUpdate', $data);
            $dispatcher->trigger('callEventHandler', ['onBeforeCampaignUpdate', ['campaign' => $cid]]);

            foreach ($data as $key => $val) {
                if ($key == 'label') {
                    $labels = $data['label'];
                    $data['label'] = $data['label'][$actualLanguage];
                    $fields[] = $db->quoteName($key) . ' = ' . $db->quote($data['label']);
                } else if ($key == 'limit_status') {
                    $limit_status = $data['limit_status'];
                }
                else if ($key !== 'profileLabel' && $key !== 'progid' && $key !== 'status') {
                    $insert = $db->quoteName($key) . ' = ' . $db->quote($val);
                    $fields[] = $insert;
                }
            }

            $falang->updateFalang($labels,$cid,'emundus_setup_campaigns','label');

            $query->update($db->quoteName('#__emundus_setup_campaigns'))
                ->set($fields)
                ->where($db->quoteName('id') . ' = ' . $db->quote($cid));

            try {
                $db->setQuery($query);
                $db->execute();

                $query->clear()
                    ->delete($db->quoteName('#__emundus_setup_campaigns_repeat_limit_status'))
                    ->where($db->quoteName('parent_id') . ' = ' . $db->quote($cid));
                $db->setQuery($query);
                $db->execute();

                if ($data['is_limited'] == 1) {
                    foreach ($limit_status as $key => $limit_statu) {
                        if($limit_statu == 'true'){
                            $query->clear()
                                ->insert($db->quoteName('#__emundus_setup_campaigns_repeat_limit_status'));
                            $query->set($db->quoteName('parent_id') . ' = ' . $db->quote($cid))
                                ->set($db->quoteName('limit_status') . ' = ' . $db->quote($key));
                            $db->setQuery($query);
                            $db->execute();
                        }
                    }
                }


                // Create teaching unity
                $this->createYear($data);
                //

                $dispatcher->trigger('onAfterCampaignUpdate', $data);
                $dispatcher->trigger('callEventHandler', ['onAfterCampaignUpdate', ['campaign' => $cid]]);
                return true;
            } catch (Exception $e) {
                JLog::add('component/com_emundus_onboard/models/campaign | Error when update the campaign : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    public function createYear($data,$profile = null) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $prid = !empty($profile) ? $profile : $data['profile_id'];

        try {
            // Create teaching unity
            $query->select('count(id)')
                ->from($db->quoteName('#__emundus_setup_teaching_unity'))
                ->where($db->quoteName('profile_id') . ' = ' . $db->quote($prid))
                ->andWhere($db->quoteName('schoolyear') . ' = ' . $db->quote($data['year']))
                ->andWhere($db->quoteName('code') . ' = ' . $db->quote($data['training']));
            $db->setQuery($query);
            $teaching_unity_exist = $db->loadResult();

            if ($teaching_unity_exist == 0) {
                $query->clear()
                    ->insert($db->quoteName('#__emundus_setup_teaching_unity'))
                    ->set($db->quoteName('code') . ' = ' . $db->quote($data['training']))
                    ->set($db->quoteName('label') . ' = ' . $db->quote($data['label']))
                    ->set($db->quoteName('schoolyear') . ' = ' . $db->quote($data['year']))
                    ->set($db->quoteName('published') . ' = 1')
                    ->set($db->quoteName('profile_id') . ' = ' . $db->quote($prid));
                $db->setQuery($query);
                $db->execute();
            }

            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/campaign | Error when create the campaign : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return $e->getMessage();
        }
        //
    }

    public function getCampaignById($id) {
        if (empty($id)) {
            return false;
        }

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $results = new stdClass();

        try {
            $query->select(['sc.*', 'spr.label AS profileLabel','sp.id as progid'])
                ->from($db->quoteName('#__emundus_setup_campaigns', 'sc'))
                ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'spr').' ON '.$db->quoteName('spr.id').' = '.$db->quoteName('sc.profile_id'))
                ->leftJoin($db->quoteName('#__emundus_setup_programmes', 'sp').' ON '.$db->quoteName('sp.code').' = '.$db->quoteName('sc.training'))
                ->where($db->quoteName('sc.id') . ' = ' . $id);

            $db->setQuery($query);
            $results->campaign = $db->loadObject();
            $results->label = $falang->getFalang($id,'emundus_setup_campaigns','label');
            if($results->campaign->is_limited == 1){
                $query->clear()
                    ->select('limit_status')
                    ->from($db->quoteName('#__emundus_setup_campaigns_repeat_limit_status'))
                    ->where($db->quoteName('parent_id') . ' = ' . $db->quote($results->campaign->id));
                $db->setQuery($query);
                $results->campaign->status = $db->loadObjectList();
            }

            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_setup_programmes'))
                ->where($db->quoteName('code') . ' LIKE ' . $db->quote($results->campaign->training));
            $db->setQuery($query);
            $results->program = $db->loadObject();
            return $results;
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/campaign | Error at getting the campaign by id ' . $id . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus_onboard/models/campaign | Error at getting the campaign created today : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function updateProfile($profile, $campaign) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $form = JModelLegacy::getInstance('form', 'EmundusonboardModel');

        $query->select('label,year,training')
            ->from($db->quoteName('#__emundus_setup_campaigns'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($campaign));

        try {
            $db->setQuery($query);
            $schoolyear = $db->loadAssoc();

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

            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__emundus_setup_campaigns'))
                ->set($db->quoteName('profile_id') . ' = ' . $db->quote($profile))
                ->where($db->quoteName('id') . ' = ' . $db->quote($campaign));

            $db->setQuery($query);
            $db->execute();

            // Create teaching unity
            $this->createYear($schoolyear,$profile);
            //

            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/campaign | Error at updating setup_profile of the campaign: ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function getCampaignsToAffect() {
        $db = $this->getDbo();

        // Get campaigns that don't have applicant files
        $query = 'select sc.id,sc.label 
                  from jos_emundus_setup_campaigns as sc
                  where (
                    select count(cc.id)
                    from jos_emundus_campaign_candidature as cc
                    left join jos_emundus_users as u on u.id = cc.applicant_id
                    where cc.campaign_id = sc.id
                    and u.profile NOT IN (2,4,5,6)
                  ) = 0';
        //

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/campaign | Error getting campaigns without setup_profiles associated: ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
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
            JLog::add('component/com_emundus_onboard/models/campaign | Error getting campaigns without setup_profiles associated with search terms : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function createDocument($document,$types,$cid,$pid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $lang = JFactory::getLanguage();
        $actualLanguage = substr($lang->getTag(), 0 , 2);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');

        $types = implode(";", array_values($types));
        $query
            ->insert($db->quoteName('#__emundus_setup_attachments'));

        $query
            ->set($db->quoteName('lbl') . ' = ' . $db->quote('_em'))
            ->set($db->quoteName('value') . ' = ' . $db->quote($document['name'][$actualLanguage]))
            ->set($db->quoteName('description') . ' = ' . $db->quote($document['description'][$actualLanguage]))
            ->set($db->quoteName('allowed_types') . ' = ' . $db->quote($types))
            ->set($db->quoteName('ordering') . ' = ' . $db->quote(0))
            ->set($db->quoteName('nbmax') . ' = ' . $db->quote($document['nbmax']));

        /// insert image resolution if image is found
        if($document['minResolution'] != null and $document['maxResolution'] != null) {
            if(empty($document['minResolution']['width']) or (int)$document['minResolution']['width'] == 0) {
                $document['minResolution']['width'] = 'null';
            }

            if(empty($document['minResolution']['height']) or (int)$document['minResolution']['height'] == 0) {
                $document['minResolution']['height'] = 'null';
            }

            if(empty($document['maxResolution']['width']) or (int)$document['maxResolution']['width'] == 0) {
                $document['maxResolution']['width'] = 'null';
            }

            if(empty($document['maxResolution']['height']) or (int)$document['maxResolution']['height'] == 0) {
                $document['maxResolution']['height'] = 'null';
            }

            $query
                ->set($db->quoteName('min_width') . ' = ' . $document['minResolution']['width'])
                ->set($db->quoteName('min_height') . ' = ' . $document['minResolution']['height'])
                ->set($db->quoteName('max_width') . ' = ' . $document['maxResolution']['width'])
                ->set($db->quoteName('max_height') . ' = ' . $document['maxResolution']['height']);
        }

        try{

            $db->setQuery($query);
            $db->execute();
            $newdocument = $db->insertid();
            $falang->insertFalang($document['name'],$newdocument,'emundus_setup_attachments','value');
            $falang->insertFalang($document['description'],$newdocument,'emundus_setup_attachments','description');

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
                ->where($db->quoteName('profile_id') . ' = ' . $db->quote($pid));
            $db->setQuery($query);
            $ordering = $db->loadResult();

            $query->clear()
                ->insert($db->quoteName('#__emundus_setup_attachment_profiles'));
            $query->set($db->quoteName('profile_id') . ' = ' . $db->quote($pid))
                ->set($db->quoteName('attachment_id') . ' = ' . $db->quote($newdocument))
                ->set($db->quoteName('mandatory') . ' = ' . $db->quote($document['mandatory']))
                ->set($db->quoteName('ordering') . ' = ' . $db->quote($ordering + 1));
            $db->setQuery($query);
            $db->execute();
            return $newdocument;
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/campaign | Cannot create a document : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return $e->getMessage();
        }
    }

    public function updateDocument($document,$types,$did,$pid) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $lang = JFactory::getLanguage();
        $actualLanguage = substr($lang->getTag(), 0 , 2);

        $falang = JModelLegacy::getInstance('falang', 'EmundusonboardModel');

        $types = implode(";", array_values($types));

        $query
            ->update($db->quoteName('#__emundus_setup_attachments'));
        $query
            ->set($db->quoteName('value') . ' = ' . $db->quote($document['name'][$actualLanguage]))
            ->set($db->quoteName('description') . ' = ' . $db->quote($document['description'][$actualLanguage]))
            ->set($db->quoteName('allowed_types') . ' = ' . $db->quote($types))
            ->set($db->quoteName('nbmax') . ' = ' . $db->quote($document['nbmax']));

        /// many cases
        if(isset($document['minResolution'])) {

            /// isset + !empty - !is_null === !empty (just it)
            if(!empty($document['minResolution']['width'])) {
                $query
                    ->set($db->quoteName('min_width') . ' = ' . $document['minResolution']['width']);
            } else {
                $query
                    ->set($db->quoteName('min_width') . ' = null');
            }

            /// isset + !empty - !is_null === !empty (just it)
            if(!empty($document['minResolution']['height'])) {
                $query
                    ->set($db->quoteName('min_height') . ' = ' . $document['minResolution']['height']);
            } else {
                $query
                    ->set($db->quoteName('min_height') . ' = null');
            }
        } else {
            $query
                ->set($db->quoteName('min_width') . ' = null')
                ->set($db->quoteName('min_height') . ' = null');
        }

        if(isset($document['maxResolution'])) {
            /// isset + !empty - !is_null === !empty (just it)
            if(!empty($document['maxResolution']['width'])) {
                $query
                    ->set($db->quoteName('max_width') . ' = ' . $document['maxResolution']['width']);
            } else {
                $query
                    ->set($db->quoteName('max_width') . ' = null');
            }

            /// isset + !empty - !is_null === !empty (just it)
            if(!empty($document['maxResolution']['height'])) {
                $query
                    ->set($db->quoteName('max_height') . ' = ' . $document['maxResolution']['height']);
            } else {
                $query
                    ->set($db->quoteName('max_height') . ' = null');
            }
        } else {
            $query
                ->set($db->quoteName('max_width') . ' = null')
                ->set($db->quoteName('max_height') . ' = null');
        }

        $query->where($db->quoteName('id') . ' = ' . $db->quote($did));

        try{

            $db->setQuery($query);
            $db->execute();
            $query->clear()
                ->update($db->quoteName('#__emundus_setup_attachment_profiles'))
                ->set($db->quoteName('mandatory') . ' = ' . $db->quote($document['mandatory']))
                ->where($db->quoteName('attachment_id') . ' = ' . $db->quote($did));
            $db->setQuery($query);
            $db->execute();


            $falang->updateFalang($document['name'],$did,'emundus_setup_attachments','value');
            $falang->updateFalang($document['description'],$did,'emundus_setup_attachments','description');

            $query->clear()
                ->select('count(id)')
                ->from($db->quoteName('#__emundus_setup_attachment_profiles'))
                ->where($db->quoteName('profile_id') . ' = ' . $db->quote($pid))
                ->andWhere($db->quoteName('attachment_id') . ' = ' . $db->quote($did));
            $db->setQuery($query);
            $assignations = $db->loadResult();

            if(empty($assignations)) {

                $query->clear()
                    ->select('max(ordering)')
                    ->from($db->quoteName('#__emundus_setup_attachment_profiles'))
                    ->where($db->quoteName('profile_id') . ' = ' . $db->quote($pid));
                $db->setQuery($query);
                $ordering = $db->loadResult();
                if ($did !==20){
                    $query->clear()
                        ->insert($db->quoteName('#__emundus_setup_attachment_profiles'));
                    $query->set($db->quoteName('profile_id') . ' = ' . $db->quote($pid))
                        ->set($db->quoteName('attachment_id') . ' = ' . $db->quote($did))
                        ->set($db->quoteName('mandatory') . ' = ' . $db->quote($document['mandatory']))
                        ->set($db->quoteName('ordering') . ' = ' . $db->quote($ordering + 1));
                    $db->setQuery($query);
                } else {
                    $query->clear()
                        ->insert($db->quoteName('#__emundus_setup_attachment_profiles'));
                    $query->set($db->quoteName('profile_id') . ' = ' . $db->quote($pid))
                        ->set($db->quoteName('attachment_id') . ' = ' . $db->quote($did))
                        ->set($db->quoteName('mandatory') . ' = ' . $db->quote($document['mandatory']))
                        ->set($db->quoteName('displayed') . ' = '. 0)
                        ->set($db->quoteName('ordering') . ' = ' . $db->quote($ordering + 1));
                }

                $db->execute();

            }
            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/campaign | Cannot update a document ' . $did . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return $e->getMessage();
        }
    }

    function getCampaignCategory($cid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $campaign_dropfile_cat = null;
            $query->select('id,params')
                ->from($db->quoteName('#__categories'))
                ->where('json_extract(`params`, "$.idCampaign") LIKE ' . $db->quote('"'.$cid.'"'))
                ->andWhere($db->quoteName('extension') . ' = ' . $db->quote('com_dropfiles'));
            $db->setQuery($query);
            $campaign_dropfile_cat = $db->loadResult();

            if(!$campaign_dropfile_cat){
                JPluginHelper::importPlugin('emundus', 'setup_category');
                $dispatcher = JEventDispatcher::getInstance();
                $dispatcher->trigger('onAfterCampaignCreate', $cid);
                $this->getCampaignCategory($cid);
            }
            return $campaign_dropfile_cat;
        } catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/campaign | Cannot get dropfiles category of the campaign ' . $cid . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function getCampaignDropfilesDocuments($campaign_cat) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('*')
                ->from($db->quoteName('#__dropfiles_files'))
                ->where($db->quoteName('catid') . ' = ' . $db->quote($campaign_cat))
                ->group($db->quoteName('ordering'));
            $db->setQuery($query);
            return $db->loadObjectList();
        }  catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/campaign | Cannot get dropfiles documents of the category ' . $campaign_cat . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function getDropfileDocument($did){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('*')
                ->from($db->quoteName('#__dropfiles_files'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($did));
            $db->setQuery($query);
            return $db->loadObject();
        }  catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/campaign | Cannot get the dropfile document ' . $did . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function deleteDocumentDropfile($did){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try{
            $query->select('file,catid')
                ->from($db->quoteName('#__dropfiles_files'))
                ->where($db->quoteName('id') . ' = ' . $db->quote(($did)));
            $db->setQuery($query);
            $file = $db->loadObject();
            unlink('media/com_dropfiles/' . $file->catid . '/' . $file->file);

            $query->clear()
                ->delete($db->quoteName('#__dropfiles_files'))
                ->where($db->quoteName('id') . ' = ' . $db->quote(($did)));
            $db->setQuery($query);
            return $db->execute();
        }  catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/campaign | Cannot delete the dropfile document ' . $did . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function editDocumentDropfile($did,$name){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try{
            $query->update($db->quoteName('#__dropfiles_files'))
                ->set($db->quoteName('title') . ' = ' . $db->quote($name))
                ->where($db->quoteName('id') . ' = ' . $db->quote(($did)));
            $db->setQuery($query);
            return $db->execute();
        }  catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/campaign | Cannot update the dropfile document ' . $did . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function updateOrderDropfileDocuments($documents){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try{
            foreach ($documents as $document) {
                $query->clear()
                    ->update($db->quoteName('#__dropfiles_files'))
                    ->set($db->quoteName('ordering') . ' = ' . $db->quote($document['ordering']))
                    ->where($db->quoteName('id') . ' = ' . $db->quote(($document['id'])));
                $db->setQuery($query);
                $db->execute();
            }

            return true;
        }  catch (Exception $e) {
            JLog::add('component/com_emundus_onboard/models/campaign | Cannot reorder the dropfile documents : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function getFormDocuments($pid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try{
            $query->select('*')
                ->from($db->quoteName('#__modules'))
                ->where('json_extract(`note`, "$.pid") LIKE ' . $db->quote('"'.$pid.'"'));
            $db->setQuery($query);
            $form_module = $db->loadObject();

            $files = array();

            if($form_module != null) {
                // create the DOMDocument object, and load HTML from string
                $dochtml = new DOMDocument();
                $dochtml->loadHTML($form_module->content);

                // gets all DIVs
                $links = $dochtml->getElementsByTagName('a');
                foreach($links as $link) {
                    $file = new stdClass;
                    if($link->hasAttribute('href')) {
                        $file->link = $link->getAttribute('href');
                        $file->name = $link->textContent;
                    }
                    if($link->parentNode->hasAttribute('id')) {
                        $file->id = $link->parentNode->getAttribute('id');
                    }
                    $files[] = $file;
                }
            }

            return $files;
        }  catch (Exception $e) {
            JLog::add('Error : ' . $e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return false;
        }
    }

    public function editDocumentForm($did,$name,$pid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try{
            $query->select('*')
                ->from($db->quoteName('#__modules'))
                ->where('json_extract(`note`, "$.pid") LIKE ' . $db->quote('"'.$pid.'"'));
            $db->setQuery($query);
            $form_module = $db->loadObject();

            if($form_module != null) {
                // create the DOMDocument object, and load HTML from string
                $dochtml = new DOMDocument();
                $dochtml->loadHTML($form_module->content);

                // gets all DIVs
                $link_li = $dochtml->getElementById($did);
                $link = $link_li->firstChild;
                $link->textContent = $name;
                $link->parentNode->replaceChild($link,$link_li->firstChild);

                $newcontent = explode('</body>',explode('<body>',$dochtml->saveHTML())[1])[0];

                $query->clear()
                    ->update('#__modules')
                    ->set($db->quoteName('content') . ' = ' . $db->quote($newcontent))
                    ->where($db->quoteName('id') . '=' .  $db->quote($form_module->id));
                $db->setQuery($query);

                return $db->execute();
            } else {
                return true;
            }
        }  catch (Exception $e) {
            JLog::add('Error updating form document in component/com_emundus_onboard/models/campaign: '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function deleteDocumentForm($did,$pid){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try{
            $query->select('*')
                ->from($db->quoteName('#__modules'))
                ->where('json_extract(`note`, "$.pid") LIKE ' . $db->quote('"'.$pid.'"'));
            $db->setQuery($query);
            $form_module = $db->loadObject();

            // create the DOMDocument object, and load HTML from string
            $dochtml = new DOMDocument();
            $dochtml->loadHTML($form_module->content);

            // gets all DIVs
            $link = $dochtml->getElementById($did);
            unlink($link->firstChild->getAttribute('href'));
            $link->parentNode->removeChild($link);

            $newcontent = explode('</body>',explode('<body>',$dochtml->saveHTML())[1])[0];

            if(strpos($newcontent,'<li') === false) {
                $query->clear()
                    ->select('m.id')
                    ->from($db->quoteName('#__menu', 'm'))
                    ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'sp') . ' ON ' . $db->quoteName('sp.menutype') . ' = ' . $db->quoteName('m.menutype'))
                    ->where($db->quoteName('sp.id') . ' = ' . $db->quote($pid));
                $db->setQuery($query);
                $mids = $db->loadObjectList();

                foreach ($mids as $mid) {
                    $query->clear()
                        ->delete($db->quoteName('#__modules_menu'))
                        ->where($db->quoteName('moduleid') . ' = ' . $db->quote($form_module->id))
                        ->andWhere($db->quoteName('menuid') . ' = ' . $db->quote($mid->id));
                    $db->setQuery($query);
                    $db->execute();
                }

                $query->clear()
                    ->delete('#__modules')
                    ->where($db->quoteName('id') . '=' .  $db->quote($form_module->id));
                $db->setQuery($query);
                return $db->execute();
            } else {
                $query->clear()
                    ->update('#__modules')
                    ->set($db->quoteName('content') . ' = ' . $db->quote($newcontent))
                    ->where($db->quoteName('id') . '=' .  $db->quote($form_module->id));
                $db->setQuery($query);
                return $db->execute();
            }
        }  catch (Exception $e) {
            JLog::add('Error updating form document in component/com_emundus_onboard/models/campaign: '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
}
