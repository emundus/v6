<?php
    defined('_JEXEC') or die('Restricted access');

    class EmundusWorkflowViewCondition extends JViewLegacy {
        function display($tpl = null) {
            $jinput = JFactory::getApplication()->input;

            //display the template
            $layout = $jinput->getString('layout', null);
            if($layout == 'add') {
                //Do stuff
            }

            parent::display($tpl);
        }
    }