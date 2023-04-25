<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;


class ExportController extends FormController
{

    function __construct( )
    {
        parent::__construct();
        $this->registerDefaultTask('show');
    }

    public function process(){
        // Set output format to raw
        Factory::getApplication()->input->set('format', 'raw');

        $model = $this->getModel('export', 'exportModel');
        $model->process();
        $this->setRedirect( 'index.php?option=com_falang&task=export.show' );


    }

    public function cancel($key = null)
    {
        $this->setRedirect( 'index.php?option=com_falang' );
    }

}