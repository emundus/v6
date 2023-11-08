<?php

use Joomla\CMS\Factory;

class EmundusViewUser extends JViewLegacy
{
	private $_user;
	private $_db;

	protected $user;

	function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'javascript.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'files.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'export.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'users.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');

		$this->_user = Factory::getUser();
		$this->_db   = Factory::getDBO();

		parent::__construct($config);
	}

	function display($tpl = null)
	{
		$document = Factory::getDocument();
		$document->addStyleSheet(JURI::base(true) . '/media/com_emundus/css/emundus_activation.css');

		if ($this->_user->guest != 0) {
			$app     = JFactory::getApplication();
			$message = JText::_('ACCESS_DENIED');
			$app->redirect(JRoute::_('index.php', false), $message, 'warning');
		}

		$query = $this->_db->getQuery(true);

		$query->select($this->_db->quoteName('email'))
			->from($this->_db->quoteName('#__users'))
			->where($this->_db->quoteName('id') . ' LIKE ' . $this->_db->quote($this->_user->id));
		$this->_db->setQuery($query);
		$this->user = $this->_db->loadResult();

		parent::display($tpl);

	}
}
