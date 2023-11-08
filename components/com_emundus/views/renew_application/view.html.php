<?php
/**
 * @package    Joomla
 * @subpackage eMundus
 * @copyright  Copyright (C) 2015 emundus.fr. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * View class to file e new application
 *
 * @package     Joomla
 * @subpackage  eMundus
 * @since       6.0
 */
class EmundusViewRenew_application extends JViewLegacy
{
	private $_user;

	protected $applicant_can_renew;
	protected $current_user;
	protected $statut;

	function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'javascript.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'filters.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'list.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'emails.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'export.php');

		$this->_user = JFactory::getUser();

		parent::__construct($config);
	}

	function display($tpl = null)
	{

		if ($this->_user->guest) {
			die(JText::_('ACCESS_DENIED'));
		}

		$document = JFactory::getDocument();
		$document->addStyleSheet("media/com_emundus/css/emundus.css");

		$eMConfig                  = JComponentHelper::getParams('com_emundus');
		$this->applicant_can_renew = $eMConfig->get('applicant_can_renew', '0');

		$this->current_user = $this->_user;
		$this->statut       = $this->get('statut');

		parent::display($tpl);
	}
}

?>