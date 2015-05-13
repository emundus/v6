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
class AdmintoolsControllerDbprefix extends FOFController
{
	public function __construct($config = array()) {
		parent::__construct($config);

		$this->modelName = 'dbprefix';
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

		$prefix = $this->input->getString('prefix','jos_');
		$model = $this->getThisModel();

		$result = $model->performChanges($prefix);
		$url = 'index.php?option=com_admintools&view=dbprefix';
		if($result !== true) {
			$this->setRedirect($url, $result, 'error');
		} else {
			$this->setRedirect($url, JText::sprintf('ATOOLS_LBL_DBREFIX_OK', $prefix));
		}

		$this->redirect();
	}

	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.security');
	}
}