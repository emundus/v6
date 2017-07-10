<?php
/**
* @ Copyright (c) 2011 - Jose A. Luque
* @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die();

class SecuritycheckprosViewLogView extends SecuritycheckproView
{
	function display($tpl = null)
	{
		$model = $this->getModel();
		$file_content = $model->view_log();
		
		
		// Ponemos los datos en el template
		$this->assignRef('file_content',$file_content);
				
		parent::display($tpl);
	}

}