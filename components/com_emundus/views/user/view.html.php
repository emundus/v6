<?php
jimport('joomla.application.component.view');

use Joomla\CMS\Factory;

class EmundusViewUser extends JViewLegacy
{
	protected $_user;
	protected $logo;
	protected $user_email;

	function __construct($config = array())
	{
		$this->_user      = Factory::getUser();
		$this->user_email = $this->_user->email;

		parent::__construct($config);
	}

	function display($tpl = null)
	{
		$document = Factory::getDocument();
		$document->addStyleSheet(JURI::base(true) . '/media/com_emundus/css/emundus_activation.css');

		if ($this->_user->guest != 0) {
			$app     = Factory::getApplication();
			$message = JText::_('ACCESS_DENIED');
			$app->redirect(JRoute::_('index.php', false), $message, 'warning');
		}

		$logo_module = JModuleHelper::getModuleById('90');
		preg_match('#src="(.*?)"#i', $logo_module->content, $tab);
		$pattern = "/^(?:ftp|https?|feed)?:?\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+\|=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

		if ((bool) preg_match($pattern, $tab[1])) {
			$tab[1] = parse_url($tab[1], PHP_URL_PATH);
		}

		$this->logo = JURI::base() . $tab[1];

		parent::display($tpl);

	}
}
