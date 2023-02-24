<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Controller\Mixin\CustomACL;
use Exception;
use FOF40\Controller\DataController;
use Joomla\CMS\Language\Text;

class Redirections extends DataController
{
	use CustomACL;

	public function copy(): void
	{
		// CSRF prevention
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\Redirections $model */
		$model = $this->getModel()->savestate(false);
		$ids   = $this->getIDsFromRequest($model, true);

		$error = null;

		try
		{
			$status = true;

			foreach ($ids as $id)
			{
				$model->find($id);
				$model->copy([
					'published' => 0,
				]);
			}
		}
		catch (Exception $e)
		{
			$status = false;
			$error  = $e->getMessage();
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
			$this->setRedirect($url, Text::_($textKey));
		}
	}

	public function applypreference()
	{
		$newState = $this->input->getInt('urlredirection', 1);

		/** @var \Akeeba\AdminTools\Admin\Model\Redirections $model */
		$model = $this->getModel();
		$model->setRedirectionState($newState);


		$url = 'index.php?option=com_admintools&view=Redirections';
		$this->setRedirect($url, Text::_('COM_ADMINTOOLS_LBL_REDIRECTION_PREFERENCE_SAVED'));
	}
}
