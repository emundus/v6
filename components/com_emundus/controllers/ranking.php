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

require_once (JPATH_ROOT . '/components/com_emundus/helpers/access.php');

class EmundusControllerRanking extends JControllerLegacy
{
    public function __construct($config = array())
    {
        $user = Factory::getUser();

        if (!EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            throw new Exception('Access denied');
        }

        parent::__construct($config);
    }

    public function getMyFilesToRank() {
        $response = ['status' => false, 'msg' => Text::_('ACCESS_DENIED'), 'data' => [], 'code' => 403];
        $user = Factory::getUser();

        if (EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            $model = $this->getModel('ranking');
            $response['data'] = $model->getFilesUserCanRank($user->id);
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
}
?>