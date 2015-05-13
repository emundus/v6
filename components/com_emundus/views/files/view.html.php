<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
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

	public function __construct($config = array())
	{/*
//		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
*/
		
		parent::__construct($config);
	}

    public function display($tpl = null)
    {
		$current_user = JFactory::getUser();
		if( !EmundusHelperAccess::asPartnerAccessLevel($current_user->id) )
			die( JText::_('RESTRICTED_ACCESS') );

    	// translation to load in javacript file ; /media/com_emundus/em_files.js
    	// put it in com_emundus/emundus.php
/*
		JHtml::script('jquery-1.10.2.min.js', JURI::base() . 'media/com_emundus/lib/');
		JHtml::script('jquery-ui-1.8.18.min.js', JURI::base() . 'media/com_emundus/lib/');
		JHtml::script('jquery.doubleScroll.js', JURI::base()."media/com_emundus/lib/" );

    	JHtml::script('bootstrap.min.js', JURI::base() . 'media/com_emundus/lib/bootstrap-emundus/js/');
		JHtml::script('chosen.jquery.min.js', JURI::base()."media/com_emundus/lib/chosen/" );

	    JHTML::script( 'em_files.js', JURI::Base().'media/com_emundus/js/');
    	JHtml::styleSheet('chosen.min.css', JURI::base()."media/com_emundus/lib/chosen/");
	    JHtml::stylesheet('bootstrap.min.css', JURI::base() . 'media/com_emundus/lib/bootstrap-emundus/css/');
	    JHtml::stylesheet('emundus_files.css', JURI::base() . 'media/com_emundus/css/');
*/
	    $this->itemId = JFactory::getApplication()->input->getInt('Itemid', null);

		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_dir'] = JFactory::getSession()->get( 'filter_order_Dir' );
		$lists['order']     = JFactory::getSession()->get( 'filter_order' );
		$this->assignRef('lists', $lists);
		$this->assignRef('actions', $actions);
		$pagination = $this->get('Pagination');
		$this->assignRef('pagination', $pagination);

		//$submitForm = EmundusHelperJavascript::onSubmitForm();
		//$delayAct = EmundusHelperJavascript::delayAct();
		//$this->assignRef('delayAct', $delayAct);
		//$this->assignRef('submitForm', $submitForm);
		
		parent::display($tpl);
	}

}
?>

