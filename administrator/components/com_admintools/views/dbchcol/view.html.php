<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsViewDbchcol extends F0FViewHtml
{
	protected function onBrowse($tpl = null)
	{
		$this->setLayout('choose');
	}
}