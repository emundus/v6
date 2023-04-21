<?php
jimport('joomla.application.component.view');

class EmundusViewUser extends JViewLegacy
{
    var $_user = null;
    var $_db = null;

    function __construct($config = array())
    {
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
                $user = JFactory::getUser();

                $logo_module = JModuleHelper::getModuleById('90');
                preg_match('#src="(.*?)"#i', $logo_module->content, $tab);
                $pattern = "/^(?:ftp|https?|feed)?:?\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+\|=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

                if ((bool) preg_match($pattern, $tab[1])) {
                    $tab[1] = parse_url($tab[1], PHP_URL_PATH);
                }

                $logo = JURI::base().$tab[1];

                $this->assignRef('logo', $logo);
                $this->assignRef('user_email', $user->email);
                $this->assignRef('user', $user);
                break;

        }
        parent::display($tpl);

    }
}
