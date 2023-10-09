<?php
/**
 * @package     Joomla
 * @subpackage  com_emunudus_onboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * eMundus Gallery View
 *
 * @since  0.0.1
 */
class EmundusViewGallery extends JViewLegacy {
	private $app;

	protected $id;

	public function __construct($config = array())
	{
		$this->app = Factory::getApplication();

		parent::__construct($config);
	}

	function display($tpl = null) {
        $input = $this->app->input;

        $layout = $input->getString('layout', null);
        if ($layout == 'add') {
            $this->id = $input->getString('gid', null);
		}

		parent::display($tpl);
	}
}
