<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsControllerDbchcol extends FOFController
{
	public function __construct($config = array()) {
		parent::__construct($config);

		$this->modelName = 'dbchcol';
	}

	public function execute($task) {
		if(!in_array($task, array('apply'))) $task = 'browse';
		parent::execute($task);
	}

	public function apply()
	{
		$model = $this->getThisModel();
		$collation = $this->input->getString('collation','utf8_general_ci');
		$model->changeCollation($collation);

		$msg = JText::_('ATOOLS_LBL_DBCHCOLDONE');
		$this->setRedirect('index.php?option=com_admintools&view=dbchcol', $msg);
	}

	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.maintenance');
	}

	protected function onBeforeApply()
	{
		return $this->checkACL('admintools.maintenance');
	}
}