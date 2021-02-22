<?php
    defined('_JEXEC') or die('Restricted access');

    class EmundusWorkflowViewWorkflow extends JViewLegacy {
        public function display($tpl = null) {
            $jinput = JFactory::getApplication()->input;

            $layout = $jinput->getString('layout', null);
            if($layout == 'add') {
                // do stuff
            }

            parent::display($tpl);
        }
    }