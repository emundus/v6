<?php
/**
 * Profile Model for eMundus Component
 * 
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
class EmundusModelExport_select_columns extends JModelList {
	var $_db = null;
	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct(){
		parent::__construct();
		$this->_db = JFactory::getDBO();
	}
}