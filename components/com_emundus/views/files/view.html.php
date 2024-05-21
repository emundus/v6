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

	public function __construct($config = array())
	{
		$menu = JFactory::getApplication()->getMenu();
		$current_menu = $menu->getActive();
		$menu_params = $menu->getParams(@$current_menu->id);
		$session = JFactory::getSession();

		if (!empty($menu_params)) {
			$this->use_module_for_filters = boolval($menu_params->get('em_use_module_for_filters', 0));
		} else {
			$this->use_module_for_filters = false;
		}

		if ($this->use_module_for_filters) {
			$session->set('last-filters-use-adavanced', true);
		} else {
			$session->set('last-filters-use-adavanced', false);
		}

		parent::__construct($config);
	}

    public function display($tpl = null)
    {
		$current_user = JFactory::getUser();
		if (!EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
			die( JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS') );
		}

		$app = JFactory::getApplication();

	    $this->itemId = $app->input->getInt('Itemid', null);
	    $this->cfnum = $app->input->getString('cfnum', null);

		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_dir'] = JFactory::getSession()->get( 'filter_order_Dir' );
		$lists['order']     = JFactory::getSession()->get( 'filter_order' );
		$this->assignRef('lists', $lists);
		$this->assignRef('actions', $actions);
		$pagination = $this->get('Pagination');
		$this->assignRef('pagination', $pagination);

		parent::display($tpl);
	}

}
?>

