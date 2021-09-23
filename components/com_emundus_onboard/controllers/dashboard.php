<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      James Dean
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusonboardControllerdashboard extends JControllerLegacy
{

    var $model = null;

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->model = $this->getModel('dashboard');
    }

    /**
     * Get the last active campaign
     */
    public function getLastCampaignActive(){
        try {
            $campaigns = $this->model->getLastCampaignActive();

            $tab = array('status' => 0, 'msg' => 'success', 'data' => $campaigns);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getpalettecolors(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $app    = JFactory::getApplication();
            $menu   = $app->getMenu();
            $active = $menu->getActive();
            if(empty($active)){
                $menuid = 1079;
            } else {
                $menuid = $active->id;
            }

            $query->select('m.params')
                ->from($db->quoteName('#__modules','m'))
                ->leftJoin($db->quoteName('#__modules_menu','mm').' ON '.$db->quoteName('mm.moduleid').' = '.$db->quoteName('m.id'))
                ->where($db->quoteName('m.module') . ' LIKE ' . $db->quote('mod_emundus_dashboard_vue'))
                ->andWhere($db->quoteName('mm.menuid') . ' = ' . $menuid);

            $db->setQuery($query);
            $modules = $db->loadColumn();

            foreach ($modules as $module) {
                $params = json_decode($module, true);
                if (in_array(JFactory::getSession()->get('emundusUser')->profile,$params['profile'])) {
                    $colors = $params['colors'];
                }
            }

            $tab = array('status' => 0, 'msg' => 'success', 'data' => $colors);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getwidgets(){
        try {
            $widgets = $this->model->getwidgets();

            $tab = array('status' => 0, 'msg' => 'success', 'data' => $widgets);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilescountbystatus(){
        try {
            $results = $this->model->getfilescountbystatus();

            $tab = array('msg' => 'success', 'files' => $results['files'], 'status' => $results['status']);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilesbycampaign(){
        $jinput = JFactory::getApplication()->input;

        $cid = $jinput->getInt('cid');

        try {
            $files = $this->model->getfilesbycampaign($cid);

            $tab = array('msg' => 'success', 'data' => $files);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getusersbyday(){
        try {
            $results = $this->model->getusersbyday();

            $tab = array('msg' => 'success', 'users' => $results['users'], 'days' => $results['days'], 'total' => $results['total']);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfirstcoordinatorconnection(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $user = JFactory::getUser();
            $table = JTable::getInstance('user', 'JTable');
            $table->load($user->id);

            $params = $user->getParameters();
            if ($params->get('first_login_date')) {
                $register_at = $params->get('first_login_date');
            } else {
                $register_at = '0000-00-00 00:00:00';
            }

            $tab = array('msg' => 'success', 'data' => $register_at);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilescountbystatusgroupbydate(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $category = [];

        try {
            $jinput = JFactory::getApplication()->input;

            $program = $jinput->getString('program');

            $query->select('value as seriesname,step')
                ->from($db->quoteName('#__emundus_setup_status'));
            $db->setQuery($query);
            $status = $db->loadObjectList();
            foreach ($status as $key => $statu){
                $status[$key]->data = [];
            }
            $dataset = $status;

            $query->clear()
                ->select('min(cc.date_time)')
                ->from($db->quoteName('#__emundus_campaign_candidature','cc'));
            if(!empty($program)){
                $query->leftJoin($db->quoteName('#__emundus_setup_campaigns','sc').' ON '.$db->quoteName('sc.id').' = '.$db->quoteName('cc.campaign_id'))
                    ->where($db->quoteName('sc.training').' LIKE '.$db->quote($program));
            }
            $db->setQuery($query);
            $start_date = new DateTime($db->loadResult());

            $end_date = new DateTime();

            $category[] = $start_date->format('d/m');

            while($start_date < $end_date) {
                $query->clear()
                    ->select('count(cc.id) as value, cc.status as seriesname, cc.date_time as date')
                    ->from($db->quoteName('#__emundus_campaign_candidature','cc'));
                if(!empty($program)){
                    $query->leftJoin($db->quoteName('#__emundus_setup_campaigns','sc').' ON '.$db->quoteName('sc.id').' = '.$db->quoteName('cc.campaign_id'));
                }
                $query->where($db->quoteName('cc.date_time') . ' < ' . $db->quote($start_date->format('Y-m-d H:i:s')));
                if(!empty($program)){
                    $query->andWhere($db->quoteName('sc.training').' LIKE '.$db->quote($program));
                }
                $query->group('cc.status');
                $db->setQuery($query);
                $files = $db->loadObjectList();
                if(!empty($files)) {
                    foreach ($dataset as $key => $data) {
                        foreach ($files as $index => $file) {
                            if ($file->seriesname == $data->step) {
                                $dataset[$key]->data[] = $file;
                                break;
                            }
                        }
                        $neededObject = array_filter(
                            $files,
                            function ($e) use (&$data) {
                                return $e->seriesname == $data->step;
                            }
                        );
                        if (empty($neededObject)) {
                            $empty_file = new stdClass;
                            $empty_file->value = "0";
                            $empty_file->seriesname = $data->step;
                            $empty_file->date = $start_date->format('Y-m-d H:i:s');
                            $dataset[$key]->data[] = $empty_file;
                        }
                    }
                } else {
                    foreach ($dataset as $key => $data) {
                        if (empty($dataset[$key]->data)) {
                            $empty_file = new stdClass;
                            $empty_file->value = "0";
                            $empty_file->seriesname = $data->step;
                            $dataset[$key]->data[] = $empty_file;
                        }
                    }
                }

                $start_date->modify('+1 week');
                if($start_date < $end_date) {
                    $category[] = $start_date->format('d/m');
                }
            }

            foreach ($category as $key => $date){
                $value = $date;
                $category[$key] = new stdClass();
                $category[$key]->label = $value;
            }

            $tab = array('msg' => 'success', 'dataset' => $dataset, 'category' => $category);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilescountbystatusandsession(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $category = [];


        try {
            $jinput = JFactory::getApplication()->input;

            $program = $jinput->getString('program');

            $query->select('value as seriesname,step')
                ->from($db->quoteName('#__emundus_setup_status'));
            $db->setQuery($query);
            $status = $db->loadObjectList();
            foreach ($status as $key => $statu){
                $status[$key]->data = [];
            }
            $dataset = $status;

            $query->clear()
                ->select('sc.id,sc.label,stu.id as year')
                ->from($db->quoteName('#__emundus_setup_campaigns','sc'))
                ->leftJoin($db->quoteName('#__emundus_setup_teaching_unity','stu').' ON '.$db->quoteName('stu.schoolyear').' LIKE '.$db->quoteName('sc.year'))
                ->order('stu.id');
            if(!empty($program)){
                $query->where($db->quoteName('sc.training').' LIKE '.$db->quote($program));;
            }
            $db->setQuery($query);
            $campaigns = $db->loadObjectList();

            foreach ($campaigns as $campaign){
                if($campaign->year != 13 && $campaign->year != 16) {
                    $category[] = $campaign->label;
                }
                $query->clear()
                    ->select('count(cc.id) as value, cc.status as seriesname')
                    ->from($db->quoteName('#__emundus_campaign_candidature','cc'));
                if(!empty($program)){
                    $query->leftJoin($db->quoteName('#__emundus_setup_campaigns','sc').' ON '.$db->quoteName('sc.id').' = '.$db->quoteName('cc.campaign_id'));
                }
                $query->where($db->quoteName('cc.campaign_id') . ' = ' . $db->quote($campaign->id));
                if(!empty($program)){
                    $query->andWhere($db->quoteName('sc.training').' LIKE '.$db->quote($program));
                }
                $query->group('cc.status');
                $db->setQuery($query);
                $files = $db->loadObjectList();

                if(!empty($files)) {
                    foreach ($dataset as $key => $data) {
                        foreach ($files as $index => $file) {
                            if ($file->seriesname == $data->step) {
                                if($campaign->year != 13 && $campaign->year != 16) {
                                    $dataset[$key]->data[] = $file;
                                } else {
                                    $number_1 = (int)$dataset[$key]->data[0]->value + (int)$file->value;
                                    $combine_file_1 = new stdClass;
                                    $combine_file_1->value = (string)$number_1;
                                    $combine_file_1->seriesname = $data->step;

                                    $number_2 = (int)$dataset[$key]->data[0]->value + (int)$file->value;
                                    $combine_file_2 = new stdClass;
                                    $combine_file_2->value = (string)$number_2;
                                    $combine_file_2->seriesname = $data->step;

                                    $dataset[$key]->data[0] = $combine_file_1;
                                    $dataset[$key]->data[1] = $combine_file_2;
                                }
                                break;
                            }
                        }
                        $neededObject = array_filter(
                            $files,
                            function ($e) use (&$data) {
                                return $e->seriesname == $data->step;
                            }
                        );
                        if (empty($neededObject)) {
                            $empty_file = new stdClass;
                            $empty_file->value = "0";
                            $empty_file->seriesname = $data->step;
                            if($campaign->year != 13 && $campaign->year != 16) {
                                $dataset[$key]->data[] = $empty_file;
                            }
                        }
                    }
                } else {
                    foreach ($dataset as $key => $data) {
                        if (empty($dataset[$key]->data)) {
                            $empty_file = new stdClass;
                            $empty_file->value = "0";
                            $empty_file->seriesname = $data->step;
                            if($campaign->year != 13 && $campaign->year != 16) {
                                $dataset[$key]->data[] = $empty_file;
                            }
                        }
                    }
                }
            }

            foreach ($category as $key => $date){
                $value = $date;
                $category[$key] = new stdClass();
                $category[$key]->label = explode('-',$value)[1];
            }

            $tab = array('msg' => 'success', 'dataset' => $dataset, 'category' => $category);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilescountbystatusandcourses(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $category = [];


        try {
            $jinput = JFactory::getApplication()->input;

            $program = $jinput->getString('program');
            $session = $jinput->getString('session');

            $query->select('value as seriesname,step')
                ->from($db->quoteName('#__emundus_setup_status'));
            $db->setQuery($query);
            $status = $db->loadObjectList();
            foreach ($status as $key => $statu){
                $status[$key]->data = [];
            }
            $dataset = $status;

            $query->clear()
                ->select('id,cours_fr')
                ->from($db->quoteName('data_cours_universitaire'))
                ->where($db->quoteName('session') . ' IN (' . $session . ')');
            $db->setQuery($query);
            $courses = $db->loadObjectList();

            foreach ($courses as $course){
                $category[] = $course->cours_fr;
                $query->clear()
                    ->select('count(cc.id) as value, cc.status as seriesname')
                    ->from($db->quoteName('#__emundus_campaign_candidature','cc'))
                    ->leftJoin($db->quoteName('jos_emundus_1002_00','cu').' ON '.$db->quoteName('cu.fnum').' = '.$db->quoteName('cc.fnum'))
                    ->where($db->quoteName('cu.e_369_7829') . ' = ' . $db->quote($course->id))
                    ->orWhere($db->quoteName('cu.e_369_7832') . ' = ' . $db->quote($course->id))
                    ->group('cc.status');
                $db->setQuery($query);
                $files = $db->loadObjectList();

                if(!empty($files)) {
                    foreach ($dataset as $key => $data) {
                        foreach ($files as $index => $file) {
                            if ($file->seriesname == $data->step) {
                                $dataset[$key]->data[] = $file;
                                break;
                            }
                        }
                        $neededObject = array_filter(
                            $files,
                            function ($e) use (&$data) {
                                return $e->seriesname == $data->step;
                            }
                        );
                        if (empty($neededObject)) {
                            $empty_file = new stdClass;
                            $empty_file->value = "0";
                            $empty_file->seriesname = $data->step;
                            $dataset[$key]->data[] = $empty_file;
                        }
                    }
                } else {
                    foreach ($dataset as $key => $data) {
                        if (empty($dataset[$key]->data)) {
                            $empty_file = new stdClass;
                            $empty_file->value = "0";
                            $empty_file->seriesname = $data->step;
                            $dataset[$key]->data[] = $empty_file;
                        }
                    }
                }
            }

            foreach ($category as $key => $date){
                $value = $date;
                $category[$key] = new stdClass();
                $category[$key]->label = $value;
            }

            $tab = array('msg' => 'success', 'dataset' => $dataset, 'category' => $category);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilescountbystatusandcoursesprecollege(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $category = [];


        try {
            $jinput = JFactory::getApplication()->input;

            $program = $jinput->getString('program');
            $session = $jinput->getString('session');

            $query->select('value as seriesname,step')
                ->from($db->quoteName('#__emundus_setup_status'));
            $db->setQuery($query);
            $status = $db->loadObjectList();
            foreach ($status as $key => $statu){
                $status[$key]->data = [];
            }
            $dataset = $status;

            $query->clear()
                ->select('id,course_fr')
                ->from($db->quoteName('data_cours_electif_pre_universitaire_session_' . $session))
                ->where($db->quoteName('published') . ' = 1');
            $db->setQuery($query);
            $courses = $db->loadObjectList();

            foreach ($courses as $course){
                $category[] = $course->course_fr;
                $query->clear()
                    ->select('count(cc.id) as value, cc.status as seriesname')
                    ->from($db->quoteName('#__emundus_campaign_candidature','cc'))
                    ->leftJoin($db->quoteName('jos_emundus_1001_04','cu').' ON '.$db->quoteName('cu.fnum').' = '.$db->quoteName('cc.fnum'));
                if($session == 1){
                    $query->where($db->quoteName('cu.e_366_7803') . ' = ' . $db->quote($course->id))
                        ->orWhere($db->quoteName('cu.cours_voeu_2') . ' = ' . $db->quote($course->id))
                        ->orWhere($db->quoteName('cu.e_366_7805') . ' = ' . $db->quote($course->id))
                        ->orWhere($db->quoteName('cu.cours_voeu_1_1') . ' = ' . $db->quote($course->id));
                } elseif ($session == 2){
                    $query->where($db->quoteName('cu.e_366_7804') . ' = ' . $db->quote($course->id))
                        ->orWhere($db->quoteName('cu.cours_voeu_2_2') . ' = ' . $db->quote($course->id))
                        ->orWhere($db->quoteName('cu.e_366_7806') . ' = ' . $db->quote($course->id))
                        ->orWhere($db->quoteName('cu.cours_voeu_1_2') . ' = ' . $db->quote($course->id));
                }
                $query->group('cc.status');
                $db->setQuery($query);
                $files = $db->loadObjectList();

                if(!empty($files)) {
                    foreach ($dataset as $key => $data) {
                        foreach ($files as $index => $file) {
                            if ($file->seriesname == $data->step) {
                                $dataset[$key]->data[] = $file;
                                break;
                            }
                        }
                        $neededObject = array_filter(
                            $files,
                            function ($e) use (&$data) {
                                return $e->seriesname == $data->step;
                            }
                        );
                        if (empty($neededObject)) {
                            $empty_file = new stdClass;
                            $empty_file->value = "0";
                            $empty_file->seriesname = $data->step;
                            $dataset[$key]->data[] = $empty_file;
                        }
                    }
                } else {
                    foreach ($dataset as $key => $data) {
                        if (empty($dataset[$key]->data)) {
                            $empty_file = new stdClass;
                            $empty_file->value = "0";
                            $empty_file->seriesname = $data->step;
                            $dataset[$key]->data[] = $empty_file;
                        }
                    }
                }
            }

            foreach ($category as $key => $date){
                $value = $date;
                $category[$key] = new stdClass();
                $category[$key]->label = $value;
            }

            $tab = array('msg' => 'success', 'dataset' => $dataset, 'category' => $category);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getfilescountbynationalities(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $category = [];


        try {
            $jinput = JFactory::getApplication()->input;

            $program = $jinput->getString('program');

            $query->select('value as seriesname,step')
                ->from($db->quoteName('#__emundus_setup_status'));
            $db->setQuery($query);
            $status = $db->loadObjectList();
            foreach ($status as $key => $statu){
                $status[$key]->data = [];
            }
            $dataset = $status;

            $query->clear()
                ->select('id,label_fr as label')
                ->from($db->quoteName('data_nationality'));
            $db->setQuery($query);
            $nationalities = $db->loadObjectList();

            foreach ($nationalities as $nationality){
                $query->clear()
                    ->select('count(cc.id) as value, cc.status as seriesname')
                    ->from($db->quoteName('#__emundus_campaign_candidature','cc'))
                    ->leftJoin($db->quoteName('jos_emundus_1001_00','n').' ON '.$db->quoteName('n.fnum').' = '.$db->quoteName('cc.fnum'));

                if(!empty($program)){
                    $query->leftJoin($db->quoteName('#__emundus_setup_campaigns','sc').' ON '.$db->quoteName('sc.id').' = '.$db->quoteName('cc.campaign_id'));
                }

                $query->where($db->quoteName('n.e_360_7752').' = '.$db->quote($nationality->id));

                if(!empty($program)){
                    $query->andWhere($db->quoteName('sc.training').' LIKE '.$db->quote($program));
                }

                $query->group('cc.status');
                $db->setQuery($query);
                $files = $db->loadObjectList();

                if(!empty($files)) {
                    foreach ($dataset as $key => $data) {
                        foreach ($files as $index => $file) {
                            if ($file->seriesname == $data->step) {
                                $dataset[$key]->data[] = $file;
                                break;
                            }
                        }
                        $neededObject = array_filter(
                            $files,
                            function ($e) use (&$data) {
                                return $e->seriesname == $data->step;
                            }
                        );
                        if (empty($neededObject)) {
                            $empty_file = new stdClass;
                            $empty_file->value = "0";
                            $empty_file->seriesname = $data->step;
                            $dataset[$key]->data[] = $empty_file;
                        }
                    }
                    $category[] = $nationality->label;
                }
            }

            foreach ($category as $key => $date){
                $value = $date;
                $category[$key] = new stdClass();
                $category[$key]->label = $value;
            }

            $tab = array('msg' => 'success', 'dataset' => $dataset, 'category' => $category);
        } catch (Exception $e) {
            $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
        }
        echo json_encode((object)$tab);
        exit;
    }
}
