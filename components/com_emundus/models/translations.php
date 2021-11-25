<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2015 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

class EmundusModelTranslations extends JModelList
{
    var $_db = null;

    /**
     * Constructor
     *
     * @since 1.5
     */
    function __construct()
    {
        parent::__construct();
        $this->_db = JFactory::getDBO();
    }

    public function getTranslations($type,$location = '',$lang_code = '*'){

    }
}
