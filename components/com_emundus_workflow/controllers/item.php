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

    class EmundusworkflowControlleritem extends JControllerLegacy {
        var $model = null;

        public function __construct($config=array()) {
            parent::__construct($config);
            $this->model = $this->getModel('item'); //get item model
        }

        public function createItem() {

        }

        public function deleteItem($id) {}

        public function updateItemOrder($id,$old_order,$new_order) {}

        public function getOrder($id) {}

        public function getParent($id) {}

        public function getChild($id) {}
    }
