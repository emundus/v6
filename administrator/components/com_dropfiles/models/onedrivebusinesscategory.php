<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') || die;


/**
 * Class DropfilesModelOnedriveBusinessCategory
 */
class DropfilesModelOnedriveBusinessCategory extends JModelLegacy
{
    /**
     * Onedrive category instance
     *
     * @var DropfilesOneDriveBusiness
     */
    protected $onedriveBusiness;

    /**
     * DropfilesModelOnedriveBusinessCategory constructor.
     *
     * @param array $config Config
     *
     * @return void
     * @throws Exception Throw when application can not start
     * @since  version
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $app = JFactory::getApplication();
        set_include_path(__DIR__ . PATH_SEPARATOR . get_include_path());
        JLoader::register('DropfilesOneDriveBusiness', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesOneDriveBusiness.php');
        $this->onedriveBusiness = new DropfilesOneDriveBusiness();
    }

    /**
     * Change category name
     *
     * @param string $cloudId Cloud id
     * @param string $title   Title
     *
     * @return boolean
     */
    public function changeCategoryName($cloudId, $title)
    {
        if (empty($cloudId)) {
            return false;
        }

        return $this->onedriveBusiness->changeFilename($cloudId, $title);
    }

    /**
     * Change order
     *
     * @param integer $pk Target term id
     *
     * @return void
     */
    public function changeOrder($pk)
    {
        $category = JModelLegacy::getInstance('Category', 'dropfilesModel');
        $params   = DropfilesCloudHelper::getAllOneDriveBusinessConfigs();
        if ($params['connected'] === 1) {
            $itemInfo = $category->getCategory($pk);

            if (DropfilesCloudHelper::getOneDriveBusinessIdByTermId($itemInfo->parent_id)) {
                $this->onedriveBusiness->moveFile(
                    DropfilesCloudHelper::getOneDriveBusinessIdByTermId($pk),
                    DropfilesCloudHelper::getOneDriveBusinessIdByTermId($itemInfo->parent_id)
                );
            } else {
                $this->onedriveBusiness->moveFile(
                    DropfilesCloudHelper::getOneDriveBusinessIdByTermId($pk),
                    $params['onedriveBusinessBaseFolder']->id
                );
            }
        }
    }
}
