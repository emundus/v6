<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Controller;

use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The menu controller for ajax requests
 *
 * @since  3.9.0
 */
class AjaxController extends BaseController
{
    /**
     * Method to fetch associations of a menu item
     *
     * The method assumes that the following http parameters are passed in an Ajax Get request:
     * token: the form token
     * assocId: the id of the menu item whose associations are to be returned
     * excludeLang: the association for this language is to be excluded
     *
     * @return  null
     *
     * @since  3.9.0
     */
    public function fetchAssociations()
    {
        if (!Session::checkToken('get')) {
            echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
        } else {
            $assocId   = $this->input->getInt('assocId', 0);

            if ($assocId == 0) {
                echo new JsonResponse(null, Text::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', 'assocId'), true);

                return;
            }

            $excludeLang = $this->input->get('excludeLang', '', 'STRING');

            $associations = Associations::getAssociations('com_menus', '#__menu', 'com_menus.item', (int) $assocId, 'id', '', '');

            unset($associations[$excludeLang]);

            // Add the title to each of the associated records
            Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/tables');
            $menuTable = Table::getInstance('Menu', 'JTable', []);

            foreach ($associations as $lang => $association) {
                $menuTable->load($association->id);
                $associations[$lang]->title = $menuTable->title;
            }

            $countContentLanguages = count(LanguageHelper::getContentLanguages([0, 1], false));

            if (count($associations) == 0) {
                $message = Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_MESSAGE_NONE');
            } elseif ($countContentLanguages > count($associations) + 2) {
                $tags    = implode(', ', array_keys($associations));
                $message = Text::sprintf('JGLOBAL_ASSOCIATIONS_PROPAGATE_MESSAGE_SOME', $tags);
            } else {
                $message = Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_MESSAGE_ALL');
            }

            echo new JsonResponse($associations, $message);
        }
    }
}
