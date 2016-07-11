<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

// Load framework base classes
JLoader::import('joomla.application.component.view');

class AdmintoolsViewWcmaker extends F0FViewHtml
{
	public function display($tpl = null)
	{
		parent::display($tpl);
	}

	protected function onBrowse($tpl = null)
	{
		$task = $this->input->getCmd('task', 'browse');

		switch ($task)
		{
			case 'preview':
				/** @var AdmintoolsModelWcmaker $model */
				$model = $this->getModel();
				$webConfig = $model->makeWebConfig();

				$this->webConfig = $webConfig;

				$this->setLayout('plain');

				break;

			default:
				/** @var AdmintoolsModelWcmaker $model */
				$model = $this->getModel();
				$config = $model->loadConfiguration();

				$this->wcconfig = $config;

				$this->loadHelper('servertech');
				$this->isSupported = AdmintoolsHelperServertech::isWebConfigSupported();
				break;
		}
	}
}