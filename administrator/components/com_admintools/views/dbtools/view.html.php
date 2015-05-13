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

class AdmintoolsViewDbtools extends F0FViewHtml
{
	protected function onBrowse($tpl = null)
	{
		$lastTable = $this->getModel()->getState('lasttable', '');
		$percent = $this->getModel()->getState('percent', '');

		$this->setLayout('optimize');
		$this->percentage = $percent;

		$document = JFactory::getDocument();

		$script = "\n;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error\n// due to missing trailing semicolon and/or newline in their code.\n";
		$script .= '(function($){$(document).ready(function(){' . "\n";

		if (!empty($lastTable))
		{
			$script .= "document.forms.adminForm.submit();\n";
		}
		else
		{
			$script .= "window.setTimeout('parent.SqueezeBox.close();', 3000);\n";
		}
		$script .= '})})(akeeba.jQuery);' . ";\n";
		$document->addScriptDeclaration($script);
	}
}