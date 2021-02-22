<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      James Dean
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusworkflowControllerworkflow extends JControllerLegacy {

    var $model= null;
    public function __construct($config=array()) {
        parent::__construct($config);
        $this->model = $this->getModel("workflow");
        //Do Stuff
    }

    public function createConditionByItem($item_id,$data) {}

    public function updateConditionByItem($item_id,$old_condition,$new_condition) {}

    public function getConditionByItem($item_id) {}
}
