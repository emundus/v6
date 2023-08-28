<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
//error_reporting(E_ALL);
jimport( 'joomla.application.component.view');
/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */

class EmundusViewFiles extends JViewLegacy
{
	protected $itemId;
	protected $actions;
	protected $use_module_for_filters;
	protected array $lists;
	protected JPagination $pagination;

	public function __construct($config = array())
	{
		$menu = JFactory::getApplication()->getMenu();
		$current_menu = $menu->getActive();
		$menu_params = $menu->getParams(@$current_menu->id);
		$this->use_module_for_filters = boolval($menu_params->get('em_use_module_for_filters', 0));

		parent::__construct($config);
	}

    public function display($tpl = null)
    {
		$current_user = JFactory::getUser();
		if (!EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
			die( JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS') );
		}

		$m_files = new EmundusModelFiles();

    	// translation to load in javacript file ; /media/com_emundus/em_files.js
    	// put it in com_emundus/emundus.php
		//JHTML::stylesheet("media/jui/css/chosen.min.css");

		$app = JFactory::getApplication();

	    $this->itemId = $app->input->getInt('Itemid', null);
	    $this->cfnum = $app->input->getString('cfnum', null);

		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_dir'] = JFactory::getSession()->get( 'filter_order_Dir' );
		$lists['order']     = JFactory::getSession()->get( 'filter_order' );
		$this->lists = $lists;
		$this->pagination = $m_files->getPagination();

		parent::display($tpl);
	}

}
?>

