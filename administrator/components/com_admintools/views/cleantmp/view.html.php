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

if (!class_exists('JoomlaCompatView'))
{
	if (interface_exists('JView'))
	{
		abstract class JoomlaCompatView extends JViewLegacy
		{
		}
	}
	else
	{
		class JoomlaCompatView extends JView
		{
		}
	}
}

class AdmintoolsViewCleantmp extends JoomlaCompatView
{
	public function display($tpl = null)
	{
		$model = $this->getModel();
		$state = $model->getState('scanstate', false);

		$total = max(1, $model->totalFolders);
		$done = $model->doneFolders;

		if ($state)
		{
			if ($total > 0)
			{
				$percent = min(max(round(100 * $done / $total), 1), 100);
			}

			$more = true;
		}
		else
		{
			$percent = 100;
			$more = false;
		}

		$this->more = $more;
		$this->setLayout('default');

		$this->percentage = $percent;

		if ($more)
		{
			$script = <<<JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
(function($){
	$(document).ready(function(){
		document.forms.adminForm.submit();
	});
})(akeeba.jQuery)

JS;

			JFactory::getDocument()->addScriptDeclaration($script);
		}

		parent::display();
	}
}