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
 
class EmundusViewAmetys extends JViewLegacy
{
	protected $itemId;
	protected $task;
	protected $token;

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

    public function display($tpl = null)
    {
    	// translation to load in javacript file ; /media/com_emundus/em_files.js
    	// put it in com_emundus/emundus.php

	    $this->itemId = JFactory::getApplication()->input->getInt('Itemid', null);
	    $this->task = JFactory::getApplication()->input->getInt('task', null);
	    $this->token = JFactory::getApplication()->input->getInt('token', null);
		
		parent::display($tpl);
	}

}
?>

