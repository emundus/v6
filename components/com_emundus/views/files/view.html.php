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
	protected $use_module_for_filter = false;

	protected bool $open_file_in_modal = false;
	protected string $modal_ratio = '66/33';
	protected string $modal_left_panel_tabs = '';

	public function __construct($config = array())
	{
		$menu = JFactory::getApplication()->getMenu();
		$session = JFactory::getSession();

		$this->use_module_for_filters = false;
        if (!empty($menu)) {
            $current_menu = $menu->getActive();
			if (!empty($current_menu)) {
				$menu_params = $menu->getParams($current_menu->id);
				if (!empty($menu_params)) {
					$this->use_module_for_filters = boolval($menu_params->get('em_use_module_for_filters', 0));

					$this->open_file_in_modal     = boolval($menu_params->get('em_open_file_in_modal', 0));
					if ($this->open_file_in_modal) {
						$this->modal_ratio = $menu_params->get('em_modal_ratio', '66/33');
						$this->modal_left_panel_tabs = $menu_params->get('em_modal_left_panel_tabs', 'evaluation');
					}
				}
			}
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

