<?php
/**
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper as JComponentHelper;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Router\Route as JRoute;

abstract class modScpadminQuickIconsHelper
{
    /**
     * Stack to hold buttons
     *
     * @since 1.6
     */
    protected static $buttons = array();

    /**
     * Helper method to return button list.
     *
     * This method returns the array by reference so it can be
     * used to add custom buttons or remove default ones.
     *
     * @param JRegistry    The module parameters.
     *
     * @return array    An array of buttons
     * @since  1.6
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
        if(!$installed) { return;
        }

        // Make sure Securitycheck Pro Component is enabled
        if (!JComponentHelper::isEnabled('com_securitycheckpro', true)) {
            JFactory::getApplication()->enqueueMessage(JText::_('MOD_SECURITYCHECKPRO_NOT_ENABLED'), 'error');
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
        $cpanel_model = JModelLegacy::getInstance('cpanel', 'SecuritycheckprosModel');
        $filemanager_model = JModelLegacy::getInstance('filemanager', 'SecuritycheckprosModel');
    
        $mainframe = JFactory::getApplication();
    
        if ((empty($cpanel_model)) || (empty($filemanager_model))) {        
            $mainframe->setUserState("exists_filemanager", false);    
            return;
        } else if (!empty($filemanager_model)) {
            $mainframe->setUserState("exists_filemanager", true);
        }
        
        $key = (string)$params;
        if (!isset(self::$buttons[$key])) {
            $context = $params->get('context', 'mod_scpadmin_quickicons');
            if ($context == 'mod_scpadmin_quickicons') {
                // Load mod_scpadmin_quickicons language file in case this method is called before rendering the module
                JFactory::getLanguage()->load('mod_scpadmin_quickicons');
            }
            // Array is empty because we will add icons later
            self::$buttons[$key] = array();
			
			if($params->get('check_vulnerable_extensions', 1) == 1) {    
			
				// Check for vulnerable components
                $cpanel_model->buscarQuickIcons();
            
                // Vulnerable components
                $db = JFactory::getDBO();
                $query = "SELECT COUNT(*) FROM #__securitycheckpro WHERE Vulnerable='Si'";
                $db->setQuery($query);
                $db->execute();    
                $vuln_extensions = $db->loadResult();
				            
                // Undefined vulnerable components
                $query = "SELECT COUNT(*) FROM #__securitycheckpro WHERE Vulnerable='Indefinido'";
                $db->setQuery($query);
                $db->execute();    
                $undefined_vuln_extensions = $db->loadResult();
            
                if ($vuln_extensions > 0) {
                    if (version_compare(JVERSION, '3.20', 'lt')) {
                        $check_vulnerable_extensions_image = 'warning';
                    } else
                    { 
                        $check_vulnerable_extensions_image = 'fa fa-exclamation';
                        $document = JFactory::getDocument();
                        $document->addScriptDeclaration(
                            "
						function scp_vuln_extensions() {
						var link     = document.getElementById('plg_quickicon_scp_vuln_extensions'),
										linkSpan = link.querySelectorAll('span.j-links-link');
						link.classList.add('danger');
					}
					
					document.addEventListener('DOMContentLoaded', function() {
						setTimeout(scp_vuln_extensions, 2000)
					});
					"
                        );    
                    }
                
                    $check_vulnerable_extensions_label = JText::_('MOD_SECURITYCHECKPRO_VULNERABLE_EXTENSIONS');
                } else if  ($undefined_vuln_extensions > 0) {
                    if (version_compare(JVERSION, '3.20', 'lt')) {
                        $check_vulnerable_extensions_image = 'help';
                    } else
                    { 
                        $check_vulnerable_extensions_image = 'fa fa-question-circle ';
                        $document = JFactory::getDocument();
                        $document->addScriptDeclaration(
                            "
						function scp_vuln_extensions() {
						var link     = document.getElementById('plg_quickicon_scp_vuln_extensions'),
										linkSpan = link.querySelectorAll('span.j-links-link');
						link.classList.add('warning');
					}
					
					document.addEventListener('DOMContentLoaded', function() {
						setTimeout(scp_vuln_extensions, 2000)
					});
					"
                        );    
                    }
                
                    $check_vulnerable_extensions_label = JText::_('MOD_SECURITYCHECKPRO_VULNERABLE_EXTENSIONS');
                } else
                {
                
                    if (version_compare(JVERSION, '3.20', 'lt')) {
                        $check_vulnerable_extensions_image = 'checkmark';
                    } else 
                    { 
                        $check_vulnerable_extensions_image = 'fa fa-check-circle ';
                        $document = JFactory::getDocument();
                        $document->addScriptDeclaration(
                            "
						function scp_vuln_extensions() {
						var link     = document.getElementById('plg_quickicon_scp_vuln_extensions'),
										linkSpan = link.querySelectorAll('span.j-links-link');
						link.classList.add('success');
					}
					
					document.addEventListener('DOMContentLoaded', function() {
						setTimeout(scp_vuln_extensions, 2000)
					});
					"
                        );    
                    }
                
                    $check_vulnerable_extensions_label = JText::_('MOD_SECURITYCHECKPRO_NO_VULNERABLE_EXTENSIONS');
                }
            
                $array_vuln_extensions = array
                (
                'link' => JRoute::_('index.php?option=com_securitycheckpro&controller=securitycheckpro&'. JSession::getFormToken() .'=1'),
                'image' => $check_vulnerable_extensions_image,
                'text' => $check_vulnerable_extensions_label,
                'id'    => 'plg_quickicon_scp_vuln_extensions',
                'access' => true
                );
                array_push(self::$buttons[$key], $array_vuln_extensions);
            }
                
            if($params->get('check_not_readed_logs', 1) == 1) {
            
                // Check for unread logs
                (int) $logs_pending = $cpanel_model->LogsPending();
				                
                if ($logs_pending == 0) {
                    if (version_compare(JVERSION, '3.20', 'lt')) {
                          $check_not_readed_logs_image = 'drawer';
                    } else
                    { 
                        $check_not_readed_logs_image = 'fa fa-inbox';
                        $document = JFactory::getDocument();
                        $document->addScriptDeclaration(
                            "
						function scp_logs() {
						var link     = document.getElementById('plg_quickicon_scp_logs'),
										linkSpan = link.querySelectorAll('span.j-links-link');
						link.classList.add('success');
					}
					
					document.addEventListener('DOMContentLoaded', function() {
						setTimeout(scp_logs, 2000)
					});
					"
                        );    
                    }
                
                    $check_not_readed_logs_label = JText::_('MOD_SECURITYCHECKPRO_NOT_UNREAD_LOGS');
                } else
                {
                    if (version_compare(JVERSION, '3.20', 'lt')) {
                        $check_not_readed_logs_image = 'drawer-2';
                    } else
                    { 
                        $check_not_readed_logs_image = 'fapro fa-inbox-in';
                        $document = JFactory::getDocument();
                        // Add style to avoid fapro icons not to be shown aligned to fa icons
                        $style = '.quick-icons a .fapro { margin: 5px auto -3px !important; }';
                        $document->addStyleDeclaration($style);
                        $document->addScriptDeclaration(
                            "
						function scp_logs() {
						var link     = document.getElementById('plg_quickicon_scp_logs'),
										linkSpan = link.querySelectorAll('span.j-links-link');
						link.classList.add('danger');
					}
					
					document.addEventListener('DOMContentLoaded', function() {
						setTimeout(scp_logs, 2000)
					});
					"
                        );    
                    }
                            
                    $check_not_readed_logs_label = JText::_('MOD_SECURITYCHECKPRO_UNREAD_LOGS');
                }
            
                $array_not_readed_logs = array
                (
                'link' => JRoute::_('index.php?option=com_securitycheckpro&controller=securitycheckpro&task=view_logs'),
                'image' => $check_not_readed_logs_image,
                'text' => $check_not_readed_logs_label,
                'id'    => 'plg_quickicon_scp_logs',
                'access' => true
                );
                array_push(self::$buttons[$key], $array_not_readed_logs);
            }

            if($params->get('check_file_permissions', 1) == 1) {
            
                // Get files with incorrect permissions from database
                $files_with_incorrect_permissions = $filemanager_model->loadStack("filemanager_resume", "files_with_incorrect_permissions");
				
				if ($files_with_incorrect_permissions == 0) {
                    if (version_compare(JVERSION, '3.20', 'lt')) {
                          $check_file_permissions_image = 'checkbox';
                    } else 
                    { 
                        $check_file_permissions_image = 'fa fa-check-square';
                        $document = JFactory::getDocument();
                        $document->addScriptDeclaration(
                            "
						function scp_permissions() {
						var link     = document.getElementById('plg_quickicon_scp_permissions'),
										linkSpan = link.querySelectorAll('span.j-links-link');
						link.classList.add('success');
					}
					
					document.addEventListener('DOMContentLoaded', function() {
						setTimeout(scp_permissions, 2000)
					});
					"
                        );
                    }
                        
                    $check_file_permissions_label = JText::_('MOD_SECURITYCHECKPRO_FILE_PERMISSIONS_OK');
                } else
                {
                    if (version_compare(JVERSION, '3.20', 'lt')) {
                        $check_file_permissions_image = 'checkbox-unchecked';
                    } else
                    { 
                        $check_file_permissions_image = 'fa fa-square';
                        $document = JFactory::getDocument();
                        $document->addScriptDeclaration(
                            "
						function scp_permissions() {
						var link     = document.getElementById('plg_quickicon_scp_permissions'),
										linkSpan = link.querySelectorAll('span.j-links-link');
						link.classList.add('danger');
					}
					
					document.addEventListener('DOMContentLoaded', function() {
						setTimeout(scp_permissions, 2000)
					});
					"
                        );    
                    }
                
                    $check_file_permissions_label = JText::_('MOD_SECURITYCHECKPRO_FILE_PERMISSIONS_WRONG');
                }
            
                $array_check_file_permissions = array
                (
                'link' => $url = JRoute::_('index.php?option=com_securitycheckpro&controller=filemanager&view=filemanager&'. JSession::getFormToken() .'=1'),
                'image' => $check_file_permissions_image,
                'text' => $check_file_permissions_label,
                'id'    => 'plg_quickicon_scp_permissions',
                'access' => true
                );
                array_push(self::$buttons[$key], $array_check_file_permissions);
            }
        
            if($params->get('check_file_integrity', 1) == 1) {
            
                // Get files with incorrect permissions from database
                $files_with_bad_integrity = $filemanager_model->loadStack("fileintegrity_resume", "files_with_bad_integrity");
				                
                if ($files_with_bad_integrity == 0) {
                    if (version_compare(JVERSION, '3.20', 'lt')) {
                          $check_file_integrity_image = 'locked';
                    } else 
                    { 
                        $check_file_integrity_image = 'fa fa-lock';
                        $document = JFactory::getDocument();
                        $document->addScriptDeclaration(
                            "
						function scp_integrity() {
						var link     = document.getElementById('plg_quickicon_scp_integrity'),
										linkSpan = link.querySelectorAll('span.j-links-link');
						link.classList.add('success');
					}
					
					document.addEventListener('DOMContentLoaded', function() {
						setTimeout(scp_integrity, 2000)
					});
					"
                        );
                    }
                
                    $check_file_integrity_label = JText::_('MOD_SECURITYCHECKPRO_FILE_INTEGRITY_OK');
                } else
                {
                    if (version_compare(JVERSION, '3.20', 'lt')) {
                        $check_file_integrity_image = 'cancel';
                    } else 
                    { 
                        $check_file_integrity_image = 'fa fa-unlock';
                        $document = JFactory::getDocument();
                        $document->addScriptDeclaration(
                            "
						function scp_integrity() {
						var link     = document.getElementById('plg_quickicon_scp_integrity'),
										linkSpan = link.querySelectorAll('span.j-links-link');
						link.classList.add('danger');
					}
					
					document.addEventListener('DOMContentLoaded', function() {
						setTimeout(scp_integrity, 2000)
					});
					"
                        );
                    }
                
                    $check_file_integrity_label = JText::_('MOD_SECURITYCHECKPRO_FILE_INTEGRITY_WRONG');
                }
            
                $array_check_file_integrity = array
                (
                'link' => JRoute::_('index.php?option=com_securitycheckpro&controller=cpanel&task=go_to_fileintegrity()'),
                'image' => $check_file_integrity_image,
                'text' => $check_file_integrity_label,
                'id'    => 'plg_quickicon_scp_integrity',
                'access' => true
                );
                array_push(self::$buttons[$key], $array_check_file_integrity);
            }
        
            if($params->get('check_malwarescan', 1) == 1) {
            
                // Get suspicious files from database
                $suspicious_files = $filemanager_model->loadStack("malwarescan_resume", "suspicious_files");
				
                if ($suspicious_files == 0) {
                    if (version_compare(JVERSION, '3.20', 'lt')) {
                          $check_malwarescan_image = 'thumbs-up';
                    } else 
                    { 
                        $check_malwarescan_image = 'fa fa-thumbs-up';
                        $document = JFactory::getDocument();
                        $document->addScriptDeclaration(
                            "
						function scp_malware() {
						var link     = document.getElementById('plg_quickicon_scp_malware'),
										linkSpan = link.querySelectorAll('span.j-links-link');
						link.classList.add('success');
					}
					
					document.addEventListener('DOMContentLoaded', function() {
						setTimeout(scp_malware, 2000)
					});
					"
                        );
                    }
                
                    $check_malwarescan_label = JText::_('MOD_SECURITYCHECKPRO_MALWARESCAN_OK');
                } else 
                {
                    if (version_compare(JVERSION, '3.20', 'lt')) {
                               $check_malwarescan_image = 'thumbs-down';
                    } else 
                    { 
                        $check_malwarescan_image = 'fa fa-bug';
                        $document = JFactory::getDocument();
                        $document->addScriptDeclaration(
                            "
						function scp_malware() {
						var link     = document.getElementById('plg_quickicon_scp_malware'),
										linkSpan = link.querySelectorAll('span.j-links-link');
						link.classList.add('danger');
					}
					
					document.addEventListener('DOMContentLoaded', function() {
						setTimeout(scp_malware, 2000)
					});
					"
                        );
                    }
                
                    $check_malwarescan_label = JText::_('MOD_SECURITYCHECKPRO_MALWARESCAN_WRONG');
                }
            
                $array_malwarescan_integrity = array
                (            
                'link' => JRoute::_('index.php?option=com_securitycheckpro&controller=cpanel&task=go_to_malware()'),
                'image' => $check_malwarescan_image,
                'text' => $check_malwarescan_label,
                'id'    => 'plg_quickicon_scp_malware',
                'access' => true
                );
                array_push(self::$buttons[$key], $array_malwarescan_integrity);
            }
        
        }
        return self::$buttons[$key];
    }

    /**
     * Get the alternate title for the module
     *
     * @param JRegistry    The module parameters.
     * @param object        The module.
     *
     * @return string    The alternate title for the module.
     */
    public static function getTitle($params, $module)
    {
        $key = $params->get('context', 'mod_scpadmin_quickicons') . '_title';
        if (JFactory::getLanguage()->hasKey($key)) {
            return JText::_($key);
        }
        else
        {
            return $module->title;
        }
    }
}
