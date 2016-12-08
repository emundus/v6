<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Controller\Mixin\CustomACL;
use FOF30\Controller\DataController;

class Redirections extends DataController
{
	use CustomACL;

	public function copy()
	{
		// CSRF prevention
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\Redirections $model */
		$model = $this->getModel();
		$item = $model->find();

		$data = array('published' => 0);

		$url = 'index.php?option=com_admintools&view=Redirections';

		try
		{
			$item->copy($data);

			$this->setRedirect($url);
		}
		catch (\Exception $e)
		{
			$this->setRedirect($url, $e->getMessage(), 'error');
		}
	}

	public function applypreference()
	{
		$newState = $this->input->getInt('urlredirection', 1);

		/** @var \Akeeba\AdminTools\Admin\Model\Redirections $model */
		$model = $this->getModel();
		$model->setRedirectionState($newState);


		$url = 'index.php?option=com_admintools&view=Redirections';
		$this->setRedirect($url, \JText::_('COM_ADMINTOOLS_LBL_REDIRECTION_PREFERENCE_SAVED'));
	}
}