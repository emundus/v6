<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

// Load framework base classes
JLoader::import('joomla.application.component.view');

class AdmintoolsViewEom extends F0FViewHtml
{
	protected function onBrowse($tpl = null)
	{
		$model = $this->getModel();

		$isOffline = $model->isOffline();
		$htaccess = $model->getHtaccess();

		$this->offline = $isOffline;
		$this->htaccess = $htaccess;

		$this->setLayout('default');

		return true;
	}
}