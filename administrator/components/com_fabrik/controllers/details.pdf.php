<?php
/**
 * Details controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       1.6
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;

jimport('joomla.application.component.controllerform');

/**
 * Details controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.0
 */
class FabrikAdminControllerDetails extends FormController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var	 string
	 */
	protected $text_prefix = 'COM_FABRIK_FORM';

	/**
	 * Show the form in the admin
	 *
	 * @return  void
	 */
	public function view()
	{
		$document = Factory::getDocument();
		$model = Factory::getApplication()->bootComponent('com_fabrik')->getMVCFactory()->createModel('Form', 'FabrikFEModel');
		$app = Factory::getApplication();
		$input = $app->input;
		$input->set('tmpl', 'component');
		$input->set('view', 'details');
		$viewType = $document->getType();
		$this->setPath('view', COM_FABRIK_FRONTEND . '/views');
		$viewLayout	= $input->get('layout', 'default');
		$this->name = 'Fabrik';
		$view = $this->getView('Form', $viewType, '');
		$view->setModel($model, true);

		// Set the layout
		$view->setLayout($viewLayout);

		// @TODO check for cached version
		ToolBarHelper::title(Text::_('COM_FABRIK_MANAGER_FORMS'), 'file-2');

		$view->display();
		FabrikAdminHelper::addSubmenu($input->getWord('view', 'lists'));
	}
}
