<?php
/**
 * @package LiveUpdate
 * @copyright Copyright ©2011-2013 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license GNU LGPLv3 or later <http://www.gnu.org/copyleft/lesser.html>
 */

defined('_JEXEC') or die();

/**
 * Configuration class for your extension's updates. Override to your liking.
 */
class LiveUpdateConfig extends LiveUpdateAbstractConfig
{
	var $_extensionName			= 'com_falang';
	var $_extensionTitle		= 'Falang';
	var $_updateURL				= 'https://www.faboba.com/index.php?option=com_ars&lang=en&view=update&format=ini&id=2';
	var $_requiresAuthorization	= true;
	var $_versionStrategy		= 'vcompare';
    var $_storageAdapter		= 'component';
    var $_storageConfig			= array(
        'extensionName'	=> 'com_falang',
        'key'			=> 'liveupdate'
    );


    function __construct()
    {
        JLoader::import('joomla.filesystem.file');
        include_once( JPATH_ADMINISTRATOR . '/components/com_falang/version.php');
        $version = new FalangVersion();

        $this->_cacerts = dirname(__FILE__).'/../assets/cacert.pem';

        switch ($version->_versiontype) {
            case "basic" :
                    $this->_requiresAuthorization = true;
                    $this->_updateURL = 'https://www.faboba.com/index.php?option=com_ars&lang=en&view=update&format=ini&id=3';
                    break;
            case "standard" :
                    $this->_requiresAuthorization = true;
                    $this->_updateURL = 'https://www.faboba.com/index.php?option=com_ars&lang=en&view=update&format=ini&id=4';
                    break;
            case "pro" :
                    $this->_requiresAuthorization = true;
                    $this->_updateURL = 'https://www.faboba.com/index.php?option=com_ars&lang=en&view=update&format=ini&id=5';
                    break;
            default :
                    $this->_requiresAuthorization = false;
                    $this->_updateURL = 'https://www.faboba.com/index.php?option=com_ars&lang=en&view=update&format=ini&id=2';
        }

        $this->_extensionTitle = 'Falang '. $version->_versiontype .' version';

        // Do we need authorized URLs?
        //$this->_requiresAuthorization = !$isFree;

        parent::__construct();
    }
}