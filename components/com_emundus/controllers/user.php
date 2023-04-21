<?php
/**
 * @package    Joomla
 * @subpackage eMundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');


/**
 * users Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      2.0.0
 */
class EmundusControllerUser extends JControllerLegacy
{
    private $_user = null;
    private $_db = null;
    private $m_user = null;

    public function __construct($config = array())
    {
        require_once(JPATH_COMPONENT . DS . 'models' . DS . 'user.php');

        $this->_user = JFactory::getSession()->get('emundusUser');
        $this->_db = JFactory::getDBO();
        $this->m_user = new EmundusModelUser();

        parent::__construct($config);
    }


    public function display($cachable = false, $urlparams = false)
    {
        // Set a default view if none exists
        if (!JRequest::getCmd('view')) {
            $default = 'user';
            JRequest::setVar('view', $default);
        }

        if ($this->_user->guest == 0)
            parent::display();
        else
            echo JText::_('ACCESS_DENIED');
    }
}
