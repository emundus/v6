<?php
/**
 * @package     FaLang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

if( !isset($this->catid) || $this->catid == "" || $this->language_id==-1) {
	echo $this->loadTemplate('noselection');
} else {
	echo $this->loadTemplate('list');
}
?>
