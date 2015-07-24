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
 * $Id: jfinstaller.php 1551 2011-03-24 13:03:07Z akede $
 * @package joomfish
 * @subpackage jfinstaller
 *
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.file');

/**
 * This class allows general installation of files related to the Joom!Fish project
 * It is a light version of the mosInstaller class without the particular handling of
 * special package XML files within the archieves. All XML files are automatically
 * interpreterted as content element files and installed in the related directories
 *
 */
class jfInstaller {
	var $_iswin			= false;
	var $errno			= 0;
	var $error			= "";
	var $_unpackdir		= "";

	/** @var string The directory where the element is to be installed */
	var $_elementdir 		= '';
	var $_uploadfile		= null;
	var $_realname			= null;
	var $_contentelements	= array();

	/**
	* Constructor
	*/
	function jfInstaller() {
		$this->_iswin = (substr(PHP_OS, 0, 3) == 'WIN');
		$this->_elementdir =JPath::clean( JPATH_COMPONENT_ADMINISTRATOR. DS.'contentelements' );

	}

	/**
	 * Installation of a single file or archive for the falang files
	 * @param array uploadfile	retrieved information transferred by the upload form
	 */
	function install( $uploadfile = null ) {
		if( $uploadfile === null ) {
			return false;
		}
		$this->_uploadfile = $uploadfile['tmp_name'];
		$this->_realname = $uploadfile['name'];

		return $this->upload();
	}

	/**
	* Uploads and unpacks a file
	* @return boolean True on success, False on error
	*/
	function upload() {
		if( !preg_match( '/.xml$/i', $this->_realname ) ) {
			if(! $this->extractArchive() ) {
				return false;
			}
		}

		if( !is_array( $this->_uploadfile ) ) {
			if(!@JFile::copy($this->_uploadfile, $this->_elementdir .DS. $this->_realname) ) {
				$this->errno = 2;
				$this->error = JText::_('COM_FALANG_CONTENT_ELEMENT_INSTALLER_FILEUPLOAD_ERROR');
				return false;
			}
		} else {
			foreach ($this->_uploadfile as $file ) {
				if(! @JFile::copy($this->_unpackdir .DS . $file, $this->_elementdir .DS. $file) ) {
					$this->errno = 2;
					$this->error = JText::_('COM_FALANG_CONTENT_ELEMENT_INSTALLER_FILEUPLOAD_ERROR');
					return false;
				}
			}
		}
		return true;
	}

	/**
	* Extracts the package archive file
	* @return boolean True on success, False on error
	*/
	function extractArchive() {

        //TODO sbou Check installation temp path
		$base_Dir 		=JPath::clean( JPATH_BASE. '/media' );

		$archivename 	= $base_Dir . $this->_realname;
		$tmpdir 		= uniqid( 'install_' );

		$extractdir 	=JPath::clean( $base_Dir . $tmpdir );
		$archivename 	=JPath::clean( $archivename, false );
		$this->_unpackdir = $extractdir;

		if (preg_match( '/.zip$/', $archivename )) {
			// Extract functions
            $zip = new ZipArchive;
            $res = $zip->open($this->_uploadfile);
            if ($res === TRUE) {
                 $zip->extractTo($extractdir);
                 $zip->close();
             } else {
                $this->errno = 2;
                $this->error = JText::_('COM_FALANG_CONTENT_ELEMENT_INSTALLER_UNZIP_ERROR');
                return false;
             }

		}

		// Try to find the correct install dir. in case that the package have subdirs
		// Save the install dir for later cleanup
	    jimport('joomla.filesystem.folder');
		$this->_uploadfile =JFolder::files($extractdir, '' );

		if (count( $this->_uploadfile ) == 1) {
			if (is_dir( $extractdir . $this->_uploadfile[0] )) {
				$this->_unpackdir =JPath::clean( $extractdir . $this->_uploadfile[0] );
				$this->_uploadfile = JFolder::files( $extractdir, '' );
			}
		}

		return true;
	}
}

?>
