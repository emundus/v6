<?php
    defined('_JEXEC') or die('Restricted access');

    class EmundusWorkflowViewItem extends JViewLegacy {
        public function display($tpl = null) {
            $jinput = JFactory::getApplication()->input;

            $layout = $jinput->getString('layout',null);

            if($layout == 'add') {
                $this->id = $jinput->getInt('id', null);
            }

            if($layout == 'update') {
                //do stuff
            }

            parent::display($tpl);
        }
    }
