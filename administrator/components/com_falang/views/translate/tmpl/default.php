<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

if ($this->showMessage) {
	echo $this->loadTemplate('message');
}

if( !isset($this->catid) || $this->catid == "" || $this->language_id==-1) {
	echo $this->loadTemplate('noselection');
} else {
	echo $this->loadTemplate('list');
}
