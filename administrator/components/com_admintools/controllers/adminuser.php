<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * A feature to change the site's database prefix - Controller
 */
class AdmintoolsControllerAdminuser extends FOFController
{
	public function __construct($config = array()) {
		parent::__construct($config);

		$this->modelName = 'adminuser';
	}

	public function execute($task) {
		if(!in_array($task, array('change'))) $task = 'browse';
		parent::execute($task);
	}

	function change()
	{
		if (!$this->checkACL('admintools.security'))
		{
			return false;
		}

		$prefix = $this->input->getCmd('prefix', null);
		$isHuman = $this->input->getInt('ishuman', 0);

		if($isHuman == 1) {
			$model = $this->getThisModel();
			$model->setState($prefix);
			$result = $model->swapAccounts();

			$msg = JText::sprintf('ATOOLS_LBL_ADMINUSER_OK');
			$msgType = 'message';
		} else {
			$msg = JText::_('COM_ADMINTOOLS_LBL_COMMON_NOTAHUMAN');
			$msgType = 'error';
		}
		$url = 'index.php?option=com_admintools&view=adminuser';
		$this->setRedirect($url, $msg, $msgType);

		$this->redirect();
	}

	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.security');
	}
}