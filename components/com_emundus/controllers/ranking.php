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
            $response['data'] = $this->model->getFilesUserCanRank($user->id);
            $response['status'] = true;
            $response['msg'] = Text::_('SUCCESS');
            $response['code'] = 200;
        }

        if ($response['code'] === 403) {
            header('HTTP/1.1 403 Forbidden');
            echo $response['msg'];
            exit;
        }

        echo json_encode($response);
        exit;
    }

    public function updateFileRank()
    {
        $response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'data' => [], 'code' => 403];
        $user = Factory::getUser();

        if (EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            $id = $this->app->input->getInt('id', 0);
            $rank = $this->app->input->getInt('rank', -1);
            $hierarchy_id = $this->app->input->getInt('hierarchy_id', 0);
            $files_user_can_rank = $this->model->getAllFilesRankerCanAccessTo($user->id);

            if (!empty($files_user_can_rank) && in_array($id, $files_user_can_rank)) {
                try {
                    $response['status'] = $this->model->updateFileRank($id, $user->id, $rank, $hierarchy_id);

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

        if ($response['code'] === 403) {
            header('HTTP/1.1 403 Forbidden');
            echo $response['msg'];
            exit;
        }

        echo json_encode($response);
        exit;
    }
}

?>