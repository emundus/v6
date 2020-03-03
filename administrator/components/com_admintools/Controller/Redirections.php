<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
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
		$model = $this->getModel()->savestate(false);
		$ids = $this->getIDsFromRequest($model, true);

		$error = null;

		try
		{
			$status = true;

			foreach ($ids as $id)
			{
				$model->find($id);
				$model->copy([
					'published' => 0
				]);
			}
		}
		catch (\Exception $e)
		{
			$status = false;
			$error = $e->getMessage();
		}

		// Redirect
		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->container->componentName . '&view=' . $this->container->inflector->pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $error, 'error');
		}
		else
		{
			$textKey = strtoupper($this->container->componentName . '_LBL_' . $this->container->inflector->singularize($this->view) . '_COPIED');
			$this->setRedirect($url, \JText::_($textKey));
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
