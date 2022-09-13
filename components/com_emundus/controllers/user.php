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
class EmundusControllerUsers extends JControllerLegacy
{
    private $_user = null;

    public function __construct($config = array())
    {
        $this->_user = JFactory::getSession()->get('emundusUser');

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
