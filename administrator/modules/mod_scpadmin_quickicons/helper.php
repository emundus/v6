<?php
/**
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die;

abstract class modScpadminQuickIconsHelper
{
	/**
	 * Stack to hold buttons
	 *
	 * @since	1.6
	 */
	protected static $buttons = array();

	/**
	 * Helper method to return button list.
	 *
	 * This method returns the array by reference so it can be
	 * used to add custom buttons or remove default ones.
	 *
	 * @param	JRegistry	The module parameters.
	 *
	 * @return	array	An array of buttons
	 * @since	1.6
	 */
	public static function &getButtons($params)
	{
		
	// Initialize defaults
	$media_folder = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;  
	$check_vulnerable_components_image = '';
	$check_vulnerable_components_label = '';
	$check_not_readed_logs_image = '';
	$check_not_readed_logs_label = '';
	$check_new_versions_image = '';
	$check_new_versions_label = '';
	$url_file_permissions = '';
	$url_file_integrity = '';
	$check_malwarescan_image = '';
	$check_malwarescan_label = '';

	// Make sure Securitycheck Pro is installed, or quit
	$installed = @file_exists(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'securitycheckpro.php');
	if(!$installed) return;

	// Make sure Securitycheck Pro Component is enabled
	jimport('joomla.application.component.helper');
	if (!JComponentHelper::isEnabled('com_securitycheckpro', true))
	{
		JError::raiseError('E_JPNOTENABLED', JText::_('MOD_SECURITYCHECKPRO_NOT_ENABLED'));
		return;
	}

		
	// Set default parameters
	$params->def('check_vulnerable_extensions', 1); // Check vulnerable components enabled
	$params->def('check_not_readed_logs', 1); // Check logs not readed
	$params->def('check_file_permissions', 1); // Check file permissions
	$params->def('check_file_integrity', 1); // Check file integrity
	$params->def('check_malwarescan', 1); // Check malwarescan

	// Load the language files
	$jlang = JFactory::getLanguage();
	$jlang->load('mod_scpadmin_quickicons', JPATH_ADMINISTRATOR, 'en-GB', true);
	$jlang->load('mod_scpadmin_quickicons', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
	$jlang->load('mod_scpadmin_quickicons', JPATH_ADMINISTRATOR, null, true);

	// Import Securitycheckpros models
	JLoader::import('joomla.application.component.model');
	JLoader::import('cpanel', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR. 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');
	JLoader::import('filemanager', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR. 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');
	$cpanel_model = JModelLegacy::getInstance( 'cpanel', 'SecuritycheckprosModel');
	$filemanager_model = JModelLegacy::getInstance( 'filemanager', 'SecuritycheckprosModel');
	
	$mainframe = JFactory::getApplication();
	
	if ( (empty($cpanel_model)) || (empty($filemanager_model)) ) {		
		$mainframe->setUserState( "exists_filemanager", false );	
		return;
	} else if ( !empty($filemanager_model) ) {
		$mainframe->setUserState( "exists_filemanager", true );
	}
		
	$key = (string)$params;
	if (!isset(self::$buttons[$key])) {
		$context = $params->get('context', 'mod_scpadmin_quickicons');
		if ($context == 'mod_scpadmin_quickicons')
		{
			// Load mod_scpadmin_quickicons language file in case this method is called before rendering the module
		JFactory::getLanguage()->load('mod_scpadmin_quickicons');
		}
		// Array is empty because we will add icons later
		self::$buttons[$key] = array();

		if( $params->get('check_vulnerable_extensions', 1) == 1 ) {	
		
			// Check for vulnerable components
			$cpanel_model->buscarQuickIcons();
			
			// Vulnerable components
			$db = JFactory::getDBO();
			$query = 'SELECT COUNT(*) FROM #__securitycheckpro WHERE Vulnerable="Si"';
			$db->setQuery( $query );
			$db->execute();	
			$vuln_extensions = $db->loadResult();
			
			// Undefined vulnerable components
			$query = 'SELECT COUNT(*) FROM #__securitycheckpro WHERE Vulnerable="Indefinido"';
			$db->setQuery( $query );
			$db->execute();	
			$undefined_vuln_extensions = $db->loadResult();
			
			if ( $vuln_extensions > 0 ) {
				//$check_vulnerable_extensions_image = 'scp-close';
				$check_vulnerable_extensions_image = 'warning';
				$check_vulnerable_extensions_label = JText::_('MOD_SECURITYCHECKPRO_VULNERABLE_EXTENSIONS');
			} else if  ( $undefined_vuln_extensions > 0 ) {
				$check_vulnerable_extensions_image = 'help';
				$check_vulnerable_extensions_label = JText::_('MOD_SECURITYCHECKPRO_VULNERABLE_EXTENSIONS');
			} else {
				//$check_vulnerable_extensions_image = 'scp-checkmark';
				$check_vulnerable_extensions_image = 'checkmark';
				$check_vulnerable_extensions_label = JText::_('MOD_SECURITYCHECKPRO_NO_VULNERABLE_EXTENSIONS');
			}
			
			$array_vuln_extensions = array(
						'link' => JRoute::_( 'index.php?option=com_securitycheckpro&controller=securitycheckpro&'. JSession::getFormToken() .'=1' ),
						'image' => $check_vulnerable_extensions_image,
						'text' => $check_vulnerable_extensions_label,
						'access' => true
					);
			array_push(self::$buttons[$key],$array_vuln_extensions);
		}
				
		if( $params->get('check_not_readed_logs', 1) == 1 ) {
			
			// Check for unread logs
			(int) $logs_pending = $cpanel_model->LogsPending();
				
			if ( $logs_pending == 0 ) {
				$check_not_readed_logs_image = 'drawer';
				$check_not_readed_logs_label = JText::_('MOD_SECURITYCHECKPRO_NOT_UNREAD_LOGS');
			} else {
				$check_not_readed_logs_image = 'drawer-2';
				$check_not_readed_logs_label = JText::_('MOD_SECURITYCHECKPRO_UNREAD_LOGS');
			}
			
			$array_not_readed_logs = array(
						'link' => JRoute::_('index.php?option=com_securitycheckpro&controller=securitycheckpro&task=view_logs'),
						'image' => $check_not_readed_logs_image,
						'text' => $check_not_readed_logs_label,
						'access' => true
					);
			array_push(self::$buttons[$key],$array_not_readed_logs);
		}

		if( $params->get('check_file_permissions', 1) == 1 ) {
			
			// Get files with incorrect permissions from database
			$files_with_incorrect_permissions = $filemanager_model->loadStack("filemanager_resume","files_with_incorrect_permissions");
				
			if ( $files_with_incorrect_permissions == 0 ) {
				$check_file_permissions_image = 'checkbox';
				$check_file_permissions_label = JText::_('MOD_SECURITYCHECKPRO_FILE_PERMISSIONS_OK');
			} else {
				$check_file_permissions_image = 'checkbox-unchecked';
				$check_file_permissions_label = JText::_('MOD_SECURITYCHECKPRO_FILE_PERMISSIONS_WRONG');
			}
			
			$array_check_file_permissions = array(
						'link' => $url = JRoute::_( 'index.php?option=com_securitycheckpro&controller=filemanager&view=filemanager&'. JSession::getFormToken() .'=1' ),
						'image' => $check_file_permissions_image,
						'text' => $check_file_permissions_label,
						'access' => true
					);
			array_push(self::$buttons[$key],$array_check_file_permissions);
		}
		
		if( $params->get('check_file_integrity', 1) == 1 ) {
			
			// Get files with incorrect permissions from database
			$files_with_bad_integrity = $filemanager_model->loadStack("fileintegrity_resume","files_with_bad_integrity");
				
			if ( $files_with_bad_integrity == 0 ) {
				$check_file_integrity_image = 'locked';
				$check_file_integrity_label = JText::_('MOD_SECURITYCHECKPRO_FILE_INTEGRITY_OK');
			} else {
				$check_file_integrity_image = 'cancel';
				$check_file_integrity_label = JText::_('MOD_SECURITYCHECKPRO_FILE_INTEGRITY_WRONG');
			}
			
			$array_check_file_integrity = array(
						'link' => JRoute::_( 'index.php?option=com_securitycheckpro&controller=filemanager&task=files_integrity_panel&'. JSession::getFormToken() .'=1' ),
						'image' => $check_file_integrity_image,
						'text' => $check_file_integrity_label,
						'access' => true
					);
			array_push(self::$buttons[$key],$array_check_file_integrity);
		}
		
		if( $params->get('check_malwarescan', 1) == 1 ) {
			
			// Get suspicious files from database
			$suspicious_files = $filemanager_model->loadStack("malwarescan_resume","suspicious_files");
				
			if ( $suspicious_files == 0 ) {
				$check_malwarescan_image = 'thumbs-up';
				$check_malwarescan_label = JText::_('MOD_SECURITYCHECKPRO_MALWARESCAN_OK');
			} else {
				$check_malwarescan_image = 'thumbs-down';
				$check_malwarescan_label = JText::_('MOD_SECURITYCHECKPRO_MALWARESCAN_WRONG');
			}
			
			$array_malwarescan_integrity = array(
						'link' => JRoute::_( 'index.php?option=com_securitycheckpro&controller=filemanager&task=malwarescan_panel&'. JSession::getFormToken() .'=1' ),
						'image' => $check_malwarescan_image,
						'text' => $check_malwarescan_label,
						'access' => true
					);
			array_push(self::$buttons[$key],$array_malwarescan_integrity);
		}
		
	}
		return self::$buttons[$key];
	}

	/**
	 * Get the alternate title for the module
	 *
	 * @param	JRegistry	The module parameters.
	 * @param	object		The module.
	 *
	 * @return	string	The alternate title for the module.
	 */
	public static function getTitle($params, $module)
	{
		$key = $params->get('context', 'mod_scpadmin_quickicons') . '_title';
		if (JFactory::getLanguage()->hasKey($key))
		{
			return JText::_($key);
		}
		else
		{
			return $module->title;
		}
	}
}
