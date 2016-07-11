<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerTmplogcheck extends F0FController
{
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->modelName = 'tmplogcheck';
    }

	public function execute($task)
	{
		if ($task != 'check')
		{
			$task = 'browse';
		}
		parent::execute($task);
	}

	public function check()
	{
        /** @var AdmintoolsModelTmplogcheck $model */
		$model = $this->getThisModel();

        $json['result'] = true;
        $json['msg']    = '';

        try
        {
            $folders = $model->checkFolders();
            $json['msg'] = implode('<br/>', $folders);
        }
        catch(Exception $e)
        {
            $json['result'] = false;
            $json['msg']    = $e->getMessage();
        }

        echo '###'.json_encode($json).'###';

        JFactory::getApplication()->close();
	}

	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.maintenance');
	}

	protected function onBeforeRun()
	{
		return $this->checkACL('admintools.maintenance');
	}
}
