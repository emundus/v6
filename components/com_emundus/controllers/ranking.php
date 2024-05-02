<?php
/**
 * @version     1.39.0
 * @package     eMundus
 * @copyright   (C) 2024 eMundus LLC. All rights reserved.
 * @license     GNU General Public License
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

require_once(JPATH_ROOT . '/components/com_emundus/helpers/access.php');
require_once(JPATH_ROOT . '/components/com_emundus/models/ranking.php');

class EmundusControllerRanking extends JControllerLegacy
{
    public function __construct($config = array())
    {
        $user = Factory::getUser();

        if (!EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            throw new Exception('Access denied');
        }

        $this->app = Factory::getApplication();
        $this->model = new EmundusModelRanking();

        parent::__construct($config);
    }

    public function getMyFilesToRank()
    {
        $response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'data' => [], 'code' => 403];
        $user = Factory::getUser();

        if (EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            $jingput = $this->app->input;
            $page = $jingput->getInt('page', 1);
            $limit = $jingput->getInt('limit', 10);
            $sort = $jingput->getString('order', 'ASC');
            $order_by = $jingput->getString('order_by', 'default');
            $package_id = $jingput->getInt('package_id', 0);

            try {
                $response['data'] = $this->model->getFilesUserCanRank($user->id, $page, $limit, $sort, $order_by, $package_id);
                $response['status'] = true;
                $response['msg'] = Text::_('SUCCESS');
                $response['code'] = 200;
            } catch(Exception $e) {
                $response['msg'] = $e->getMessage();
                $response['code'] = 500;
            }
        }

        $this->sendJSONResponse($response);
    }

    public function getOtherRankingsICanSee()
    {
        $response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'data' => [], 'code' => 403];
        $user = Factory::getUser();

        if (EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            try {
                $response['data'] = $this->model->getOtherRankingsRankerCanSee($user->id);
                $response['status'] = true;
                $response['msg'] = Text::_('SUCCESS');
                $response['code'] = 200;
            } catch(Exception $e) {
                $response['msg'] = $e->getMessage();
                $response['code'] = 500;
            }
        }

        $this->sendJSONResponse($response);
    }

    public function updateFileRanking()
    {
        $response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'data' => [], 'code' => 403];
        $user = Factory::getUser();

        if (EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            $jinput = $this->app->input;
            $id = $jinput->getInt('id', 0);

            if (!empty($id)) {
                $rank = $jinput->getInt('rank', -1);
                $hierarchy_id = $jinput->getInt('hierarchy_id', 0);
                $files_user_can_rank = $this->model->getAllFilesRankerCanAccessTo($user->id);

                if (!empty($files_user_can_rank) && in_array($id, $files_user_can_rank)) {
                    $package_id = $this->model->getPackageIdOfFile($user->id, $id);

                    try {
                        $response['status'] = $this->model->updateFileRanking($id, $user->id, $rank, $hierarchy_id, $package_id);

                        if ($response['status']) {
                            $response['msg'] = Text::_('SUCCESS');
                            $response['code'] = 200;
                        } else {
                            $response['msg'] = Text::_('ERROR');
                            $response['code'] = 500;
                        }
                    } catch(Exception $e) {
                        $response['msg'] = $e->getMessage();
                        $response['code'] = $response['msg'] == 'You cannot rank your own file' ? 403 : 500;
                    }
                }
            }
        }

        $this->sendJSONResponse($response);
    }

    /**
     * @return void
     */
    public function lockFilesOfHierarchyRanking()
    {
        $response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'data' => [], 'code' => 403];
        $user = Factory::getUser();

        if (EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            $jinput = $this->app->input;
            $hierarchy_id = $jinput->getInt('id', 0);
            $user_hierarchy = $this->model->getUserHierarchy($user->id);

            if ($user_hierarchy == $hierarchy_id) {
                $lock = $jinput->getInt('lock', 1);

                $response['status'] = $this->model->toggleLockFilesOfHierarchyRanking($hierarchy_id, $user->id, $lock);
                $response['msg'] = $response['status'] ? Text::_('SUCCESS') : Text::_('ERROR');
                $response['code'] = $response['status'] ? 200 : 500;
            }
        }

        $this->sendJSONResponse($response);
    }

    public function askToLockRankings()
    {
        $response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'data' => [], 'code' => 403];
        $user = Factory::getUser();

        if (EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            $response['code'] = 500;
            $response['msg'] = Text::_('MISSING_PARAMS');

            $jinput = $this->app->input;
            $users = $jinput->getString('users', '');
            $hierarchies = $jinput->getString('hierarchies', '');

            if (!empty($users) || !empty($hierarchies)) {
                $users = json_decode($users, true);
                $hierarchies = json_decode($hierarchies, true);

                try {
                    $result = $this->model->askUsersToLockRankings($user->id, $users, $hierarchies);

                    $response['status'] = $result['asked'];
                    $response['code'] = 200;
                    $response['data'] = $result['asked_to'];
                    $response['msg'] = Text::_('SUCCESS');
                } catch (Exception $e) {
                    $response['code'] = 500;
                    $response['msg'] = $e->getMessage();
                }
            }
        }

        $this->sendJSONResponse($response);
    }

    public function getPackages()
    {
        $response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'data' => [], 'code' => 403];
        $user = Factory::getUser();

        if (EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            try {
                $response['data'] = $this->model->getUserPackages($user->id);
                $response['status'] = true;
                $response['msg'] = Text::_('SUCCESS');
                $response['code'] = 200;
            } catch(Exception $e) {
                $response['msg'] = $e->getMessage();
                $response['code'] = 500;
            }
        }

        $this->sendJSONResponse($response);
    }

    public function getHierarchiesUserCanSee() {
        $response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'data' => [], 'code' => 403];
        $user = Factory::getUser();

        if (EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            try {
                $response['data'] = $this->model->getHierarchiesUserCanSee($user->id);
                $response['status'] = true;
                $response['msg'] = Text::_('SUCCESS');
                $response['code'] = 200;
            } catch(Exception $e) {
                $response['msg'] = $e->getMessage();
                $response['code'] = 500;
            }
        }

        $this->sendJSONResponse($response);
    }

    public function exportRanking()
    {
        $response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'data' => [], 'code' => 403];
        $user = Factory::getUser();

        if (EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            $jinput = $this->app->input;
            $package_ids = $jinput->getString('packageIds', '');

            $package_ids = json_decode($package_ids, true);
            if (!empty($package_ids)) {
                $hierarchy_ids = $jinput->getString('hierarchy_id', '');
                $hierarchy_ids = json_decode($hierarchy_ids, true);
                $columns = $jinput->getString('columns', '');
                $columns = json_decode($columns, true);

                try {
                    $response['data'] = $this->model->exportRanking($user->id, $package_ids, $hierarchy_ids, $columns);
                    $response['status'] = true;
                    $response['msg'] = Text::_('SUCCESS');
                    $response['code'] = 200;
                } catch(Exception $e) {
                    $response['msg'] = $e->getMessage();
                    $response['code'] = 500;
                }
            }
        }

        $this->sendJSONResponse($response);
    }

    private function sendJSONResponse($response) {
        if ($response['code'] === 403) {
            header('HTTP/1.1 403 Forbidden');
            echo $response['msg'];
            exit;
        } else if ($response['code'] === 500) {
            header('HTTP/1.1 500 Internal Server Error');
            echo $response['msg'];
            exit;
        }

        echo json_encode($response);
        exit;
    }
}

?>