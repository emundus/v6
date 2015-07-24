<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2011, Think Network GmbH, Munich
 *
 * All rights reserved.  The Joom!Fish project is a set of extentions for
 * the content management system Joomla!. It enables Joomla!
 * to manage multi lingual sites especially in all dynamic information
 * which are stored in the database.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * $Id: help.php 1551 2011-03-24 13:03:07Z akede $
 * @package joomfish
 * @subpackage help
 *
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once JPATH_ROOT.'/administrator/components/com_falang/legacy/controller.php';

/**
 * The JoomFish Tasker manages the general tasks within the Joom!Fish admin interface
 *
 */
class HelpController extends LegacyController  {
	/**
	 * Joom!Fish Controler for the Control Panel
	 * @param array		configuration
	 * @return joomfishTasker
	 */
	function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask( 'show',  'display' );
		$this->registerTask('postInstall', 'postInstall');
		$this->registerTask('information', 'information');
	}

	/**
	 * Standard display control structure
	 * 
	 */
	function display($cachable = false, $urlparams = array())
	{
		$this->view =  $this->getView("help");
		parent::display();
	}
	
	function cancel()
	{
		$this->setRedirect( 'index.php?option=com_falang' );
	}
	
	function postinstall() {
		// get the view
		$this->view =  $this->getView("help");

		// Set the layout
		$this->view->setLayout('postinstall');
		$this->view->display();
	}
	
	function information() {
		// get the view
		$this->view =  $this->getView("help");

		// Set the layout
		$this->view->setLayout('information');
		$this->view->display();
	}
}
?>
