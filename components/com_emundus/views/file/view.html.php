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

class EmundusViewFile extends JViewLegacy
{
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

    public function display($tpl = null)
    {
		$jinput = JFactory::getApplication()->input;

		// Display the template
		$layout = $jinput->getString('layout', null);

		// Display the template
		parent::display($tpl);
	}

}
?>

