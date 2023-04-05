<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\Component\Installer\Administrator\Model\DatabaseModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Installer Database Controller
 *
 * @since  2.5
 */
class DatabaseController extends BaseController
{
    /**
     * Tries to fix missing database updates
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.5
     * @todo    Purge updates has to be replaced with an events system
     */
    public function fix()
    {
        // Check for request forgeries.
        $this->checkToken();

        // Get items to fix the database.
        $cid = (array) $this->input->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        if (empty($cid)) {
            $this->app->getLogger()->warning(
                Text::_(
                    'COM_INSTALLER_ERROR_NO_EXTENSIONS_SELECTED'
                ),
                ['category' => 'jerror']
            );
        } else {
            /** @var DatabaseModel $model */
            $model = $this->getModel('Database');
            $model->fix($cid);

            /** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $updateModel */
            $updateModel = $this->app->bootComponent('com_joomlaupdate')
                ->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);
            $updateModel->purge();

            // Refresh versionable assets cache
            $this->app->flushAssets();
        }

        $this->setRedirect(Route::_('index.php?option=com_installer&view=database', false));
    }

    /**
     * Provide the data for a badge in a menu item via JSON
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function getMenuBadgeData()
    {
        if (!$this->app->getIdentity()->authorise('core.manage', 'com_installer')) {
            throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
        }

        $model = $this->getModel('Database');

        $changeSet = $model->getItems();

        $changeSetCount = 0;

        foreach ($changeSet as $item) {
            $changeSetCount += $item['errorsCount'];
        }

        echo new JsonResponse($changeSetCount);
    }
}
