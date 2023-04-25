<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

class FalangVersion {
	var $_version	= '4.0.0';
    var $_versiontype	= 'basic';
    var $_date	= '2021/09/23';
	var $_status	= 'Stable';
	var $_revision	= '';
	var $_copyyears = '';

	/**
	 * This method delivers the full version information in one line
	 *
	 * @return string
	 */
    function getVersionFull(){
        return 'v' .$this->_version. ' ('.$this->_versiontype.')';
    }

    /**
     * This method delivers the short version information in one line
     *
     * @return string
     */
    function getVersionShort() {
        return $this->_version;
	}

	function getVersionType() {
		return $this->_versiontype;
	}


	/**
	 * This method delivers a special version String for the footer of the application
	 *
	 * @return string
	 */
	function getCopyright() {
		//return '&copy; ' .$this->_copyyears;
            return '';
	}

	/**
	 * Returns the complete revision string for detailed packaging information
	 *
	 * @return unknown
	 */
	function getRevision() {
		return '' .$this->_revision. ' (' .$this->_date. ')';
	}
}
