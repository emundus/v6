<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */
jimport('joomla.application.component.view');

use Joomla\CMS\Factory;

class EmundusControllerEmailalert extends JControllerLegacy
{
	protected $app;

	private $_db;

	function display($cachable = false, $urlparams = false)
	{
		// Set a default view if none exists
		if (!$this->input->get('view')) {
			$default = 'emailalert';
			$this->input->set('view', $default);
		}

		parent::display();
	}

	function __construct($config = array())
	{
		parent::__construct($config);

		$this->_db = Factory::getDBO();

		$this->app = Factory::getApplication();

	}

	function generate()
	{
		$model = $this->getModel('emailalert');
		$key   = $model->getKey();
		if ($key) {
			$model->getInsert();
		}
		else {
			echo JText::_('NOT_ALLOWED');
		}
	}

	function send()
	{

		$model = $this->getModel('emailalert');
		$key   = $model->getKey();

		if ($key) {
			$emailfrom = $this->app->get('mailfrom');
			$fromname  = $this->app->get('fromname');
			$message   = $model->getSend();

			foreach ($message as $m) {
				if (JUtility::sendMail($emailfrom, $fromname, $m->email, $m->subject, $m->message, true)) {
					usleep(100);
					$query = 'UPDATE #__messages SET state = 0 WHERE user_id_to =' . $m->user_id_to;
					$this->_db->setQuery($query);
					$this->_db->execute();
				}
			}
		}
		else {
			echo JText::_('NOT_ALLOWED');
		}
	}
}

?>