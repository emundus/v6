<?php
class EmundusViewUser extends JViewLegacy
{
    var $_user = null;
    var $_db = null;

    function __construct($config = array())
    {
        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'javascript.php');
        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'files.php');
        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'access.php');
        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'export.php');
        require_once(JPATH_COMPONENT . DS . 'models' . DS . 'users.php');
        require_once(JPATH_COMPONENT . DS . 'models' . DS . 'files.php');

        $this->_user = JFactory::getUser();
        $this->_db = JFactory::getDBO();

        parent::__construct($config);
    }

    function display($tpl = null)
    {
        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::base(true) . '/media/com_emundus/css/emundus_activation.css');

        $user = JFactory::getUser();

        if($user->guest != 0){
            $app = JFactory::getApplication();
            $message = JText::_('ACCESS_DENIED');
            $app->redirect(JRoute::_('index.php', false), $message, 'warning');
        }
        $layout = JFactory::getApplication()->input->getString('layout', null);
        switch ($layout) {

            default :
                $query = $this->_db->getQuery(true);

                $query->select($this->_db->quoteName('email'));
                $query->from($this->_db->quoteName('#__users'));
                $query->where($this->_db->quoteName('id') . ' LIKE ' . $this->_db->quote($user->id));

                $this->_db->setQuery($query);

                $email = $this->_db->loadResult();
                $this->assignRef('user', $email);
                break;

        }
        parent::display($tpl);

    }
}
