<?php
/**
 * @package    Joomla
 * @subpackage eMundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
//error_reporting(E_ALL);
jimport('joomla.application.component.view');

use Joomla\CMS\Factory;

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
class EmundusViewEvaluation extends JViewLegacy
{
	private $app;
	private $_user;
	protected $itemId;
	protected $actions;
	protected bool $use_module_for_filters = false;
	protected bool $open_file_in_modal = false;
	protected string $modal_ratio = '66/33';
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->app   = Factory::getApplication();
		$this->_user = Factory::getUser();

		$menu                         = $this->app->getMenu();
        if (!empty($menu)) {
			$current_menu                 = $menu->getActive();
			if (!empty($current_menu)) {
				$menu_params                  = $menu->getParams($current_menu->id);
                $this->use_module_for_filters = boolval($menu_params->get('em_use_module_for_filters', 0));
				$this->open_file_in_modal     = boolval($menu_params->get('em_open_file_in_modal', 0));

				if ($this->open_file_in_modal) {
					$this->modal_ratio = $menu_params->get('em_modal_ratio', '66/33');
				}
			}
		}
	}

	public function display($tpl = null)
	{
		$app = Factory::getApplication();

		$this->itemId = $app->input->getInt('Itemid', null);
		$this->cfnum  = $app->input->getString('cfnum', null);

		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_dir'] = $app->getSession()->get('filter_order_Dir');
		$lists['order']     = $app->getSession()->get('filter_order');

		parent::display($tpl);
	}
}
?>