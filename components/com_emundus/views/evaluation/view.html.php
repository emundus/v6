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

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
class EmundusViewEvaluation extends JViewLegacy
{
	var $_user = null;
	var $_db = null;
	protected $itemId;
	protected $actions;

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	public function display($tpl = null)
	{
		$this->itemId = JFactory::getApplication()->input->getInt('Itemid', null);
		$this->cfnum  = JFactory::getApplication()->input->getString('cfnum', null);


		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_dir'] = JFactory::getSession()->get('filter_order_Dir');
		$lists['order']     = JFactory::getSession()->get('filter_order');

		parent::display($tpl);
	}
}

?>

