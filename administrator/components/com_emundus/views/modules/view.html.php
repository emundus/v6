<?php
/**
 * Emundus Admin Modules Page View
 * @package Joomla.Administrator
 * @subpackage eMundus
 * @copyright Copyright (C) 2015-2023 emundus.fr. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

jimport('joomla.application.component.view');
jimport( 'joomla.application.component.helper' );

/**
 * eMundus Admin Modules Page View
 *
 * @package     Joomla.Administrator
 * @subpackage  Emundus
 * @since       3.0
 */
class EmundusAdminViewModules extends HtmlView
{
    public $_user = '';
    public $modules = [];

    function __construct($config = array())
    {
        require_once (JPATH_SITE.'/components/com_emundus/helpers/access.php');

        $this->_user = Factory::getUser();

        parent::__construct($config);
    }

    function display($tpl = null)
    {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            die(JText::_("ACCESS_DENIED"));
        }

        JHTML::stylesheet('administrator/components/com_emundus/assets/css/emundus.css');

        $document = Factory::getApplication()->getDocument();
        $document->setTitle(JText::_('COM_EMUNDUS_TITLE') . ' :: ' .JText::_('COM_EMUNDUS_CONTROL_PANEL'));

        // Set toolbar items for the page
        JToolBarHelper::title(JText::_('COM_EMUNDUS_TITLE') .' :: '. JText::_( 'COM_EMUNDUS_HEADER' ), 'emundus');
        JToolBarHelper::preferences('com_emundus', '580', '750');
        JToolBarHelper::help('screen.cpanel', true);

        $modules = [
            'qcm' => [
                'title' => 'QCM'
            ],
            'anonym_user_sessions' => [
                'title' => 'Dépôt de dossiers anonymes',
                'desc' => 'Installation des formulaires Fabrik et des menus qui permettent le dépôt de dossier sans avoir à se connecter ni créer de compte.',
                'install_button' => 'Installer les formulaires'
            ],
            'homepage' => [
                'title' => 'Nouvelle page d\'accueil',
                'desc' => '',
                'install_button' => 'Installer la page d\'accueil'
            ],
            'checklist' => [
                'title' => 'Remplacer les modules des formulaires candidats',
                'desc' => '',
                'install_button' => 'Installer'
            ]
        ];
        $this->modules = $modules;

        parent::display($tpl);
    }
}
