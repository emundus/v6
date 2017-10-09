<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 *  @ modified by Jose A. Luque for Securitycheck Pro Control Center extension
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Load framework base classes
jimport('joomla.application.component.controller');

/**
* Securitycheckpros  Controller
*
*/
class SecuritycheckprosControllerOnlineChecks extends SecuritycheckproController
{


/* Redirige a la página anterior */
function redireccion_malware_scan_status()
{
	$this->setRedirect( 'index.php?option=com_securitycheckpro&controller=filemanager&task=malwarescan_panel&'. JSession::getFormToken() .'=1' );			

}

/* Borra ficheros de logs */
function delete_files()
{
	$model = $this->getModel("onlinechecks");
	$model->delete_files();	
	JRequest::setVar( 'view', 'onlinechecks' );
	
	parent::display();	
}

/* Download suspicious file log */
function download_log_file()
{
	$model = $this->getModel("onlinechecks");	
	$model->download_log_file();
		
	JRequest::setVar( 'view', 'onlinechecks' );
		
	parent::display();	
		
}

}