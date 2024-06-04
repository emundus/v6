<?php

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

class JFormFieldGenerateConfig extends FormField
{
	protected $type = 'generateconfig';

	protected function getInput()
	{
		// load generateconfig.js
		$document = Factory::getDocument();
		$document->addScript(JURI::root() . 'plugins/authentication/emundus_oauth2/src/assets/js/generateconfig.js');

		$html = '<button class="btn generate-config">Générer la configuration à partir du lien "Well known"</button>';

		return $html;
	}

	public function getLabel() {
		return '<span style="text-decoration: underline;">' . parent::getLabel() . '</span>';
	}
}