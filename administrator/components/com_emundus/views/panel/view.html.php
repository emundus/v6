<?php
/**
 * Emundus Admin Home Page View
 * @package Joomla.Administrator
 * @subpackage eMundus
 * @copyright Copyright (C) 2015-2023 emundus.fr. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView;

jimport('joomla.application.component.view');
jimport( 'joomla.application.component.helper' );

/**
 * eMundus Admin Home Page View
 *
 * @package     Joomla.Administrator
 * @subpackage  Emundus
 * @since       3.0
 */
class EmundusAdminViewPanel extends HtmlView
{
    public $version = '';

    function display($tpl = null)
    {
    	JHTML::stylesheet( 'administrator/components/com_emundus/assets/css/emundus.css' );

		$document = Factory::getApplication()->getDocument();
		$document->setTitle(JText::_('COM_EMUNDUS_TITLE') . ' :: ' .JText::_('COM_EMUNDUS_CONTROL_PANEL'));

		// Set toolbar items for the page
        ToolBarHelper::title( JText::_('COM_EMUNDUS_TITLE') .' :: '. JText::_( 'COM_EMUNDUS_HEADER' ), 'emundus' );
        ToolBarHelper::preferences('com_emundus', '580', '750');
        ToolBarHelper::help( 'screen.cpanel', true);

        $xmlDoc = new DOMDocument();
        if ($xmlDoc->load(JPATH_SITE.'/administrator/components/com_emundus/emundus.xml')) {
            $release_version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
            $this->version = $release_version;
        }

        parent::display($tpl);
    }
}
