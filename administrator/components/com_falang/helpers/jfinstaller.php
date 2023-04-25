<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;

defined('_JEXEC') or die;

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
	function __construct() {
		$this->_iswin = (substr(PHP_OS, 0, 3) == 'WIN');
		$this->_elementdir = Path::clean( JPATH_ADMINISTRATOR . '/components/com_falang/contentelements' );

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
			if(!File::copy($this->_uploadfile, $this->_elementdir .DS. $this->_realname) ) {
				$this->errno = 2;
				$this->error = Text::_('COM_FALANG_CONTENT_ELEMENT_INSTALLER_FILEUPLOAD_ERROR');
				return false;
			}
		} else {
			foreach ($this->_uploadfile as $file ) {
				if(! @File::copy($this->_unpackdir .DS . $file, $this->_elementdir .DS. $file) ) {
					$this->errno = 2;
					$this->error = Text::_('COM_FALANG_CONTENT_ELEMENT_INSTALLER_FILEUPLOAD_ERROR');
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
		$base_Dir 		= Path::clean( JPATH_BASE. '/media' );

		$archivename 	= $base_Dir . $this->_realname;
		$tmpdir 		= uniqid( 'install_' );

		$extractdir 	= Path::clean( $base_Dir . $tmpdir );
		$archivename 	= Path::clean( $archivename, false );
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
                $this->error = Text::_('COM_FALANG_CONTENT_ELEMENT_INSTALLER_UNZIP_ERROR');
                return false;
             }

		}

		// Try to find the correct install dir. in case that the package have subdirs
		// Save the install dir for later cleanup

		$this->_uploadfile = Folder::files($extractdir, '' );

		if (count( $this->_uploadfile ) == 1) {
			if (is_dir( $extractdir . $this->_uploadfile[0] )) {
				$this->_unpackdir = Path::clean( $extractdir . $this->_uploadfile[0] );
				$this->_uploadfile = Folder::files( $extractdir, '' );
			}
		}

		return true;
	}
}


