<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */


defined('_JEXEC') || die;


/**
 * Class DropfilesViewOneDriveBusiness
 */
class DropfilesViewOneDriveBusiness extends JViewLegacy
{

    /**
     * State
     *
     * @var string
     */
    protected $state;

    /**
     * Display the view
     *
     * @param null|string $tpl Template
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $this->canDo = DropfilesHelper::getActions();
        $this->setLayout($app->input->get('layout', 'default'));
        parent::display($tpl);
    }
}
