<?php
/**
 * Securitycheck Pro package
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Installer\Installer as JInstaller;
use Joomla\CMS\Factory as JFactory;
use Joomla\Filesystem\File as JFile;
use Joomla\CMS\Language\Text as JText;

/**
 * Script file of Securitycheck Pro component
 */
class com_SecuritycheckproInstallerScript
{
    // Check if we are calling update method. It's used in 'install_message' function
    public $update = false;
    
    // Resultado de la desinstalación del componente Securitycheck
    public $result_free = "";
    public $id_free;
    
    // 'memory_limit' demasiado bajo
    public $memory_limit = '';
    
    // url plugin habilitado?
    public $url_plugin_enabled = false;        
    	
	// Badge style (" . $this->badge_style . " for J3 and bg- for J4)
	private $badge_style = "badge-";
            
    /* Función que desinstala el componente Securitycheck */
    private function _unistall_Securitycheck()
    {
        
        $db = JFactory::getDbo();
        $installer = new JInstaller();
        
        $columnName      = $db->quoteName("extension_id");
        $tableExtensions = $db->quoteName("#__extensions");
        $type              = $db->quoteName("type");
        $columnElement   = $db->quoteName("element");

        // Uninstall Securitycheck component
        $db->setQuery(
            "SELECT 
					$columnName
				FROM
					$tableExtensions
				WHERE
					$type='component'
				AND
					$columnElement='com_securitycheck'"        
        );

        $this->id_free = $db->loadResult();

        if ($this->id_free) {
            $this->result_free = $installer->uninstall('component', $this->id_free, 1);
        }
    }    
    
    
    /* Migrate blacklist and whitelist lists from config to a database for each one */
    private function _34_version_changes()
    {
    
        // Extraemos la información necesario de la tabla #_extensions        
        $db = JFactory::getDBO();
		try {
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__extensions'));
			$query->where($db->quoteName('element') . ' = ' . $db->quote('com_securitycheckpro'));
			$db->setQuery($query);
			$db->execute();
			$result = $db->loadAssocList();
		} catch(\Exception $e)
        {
			$result = null;
        }
		        
        // Si no existe versión previa no es necesario hacer ninguna acción
        if (!empty($result)) {
        
            // Decodificamos la información de la versión, que está en formato json en la entrada 'manifest_cache'
            $stack = json_decode($result[0]["manifest_cache"], true);
            
            // Versión de Securitycheck Pro instalada
            $scpro_version = $stack["version"];
            
            // Si la versión instalada es menor a la 3.4, hemos de realizar las comprobaciones
            if (version_compare($scpro_version, "3.4", "lt")) {
                // Configuración del plugin
				$query = $db->getQuery(true);
				$query->select($db->quoteName('storage_value'));
				$query->from($db->quoteName('#__securitycheckpro_storage'));
				$query->where($db->quoteName('storage_key') . ' = ' . $db->quote('pro_plugin'));                
                $db->setQuery($query);
                $db->execute();
                $result = $db->loadAssoc();				
				
				if (!empty($result)) {
					// Storage config in array format
					$storage_config = json_decode($result['storage_value'], true);
					
					 // Extraemos las listas negra y blanca                    										
					$blacklist = explode(",",$storage_config['blacklist']);
					$whitelist = explode(",",$storage_config['whitelist']);
					
					// Create blacklist table
					$query = "CREATE TABLE IF NOT EXISTS `#__securitycheckpro_blacklist` ( `ip` VARCHAR(26) NOT NULL, PRIMARY KEY (`ip`)) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
					$db->setQuery($query);
					$db->execute();
					
					// Create whitelist table
					$query = "CREATE TABLE IF NOT EXISTS `#__securitycheckpro_whitelist` ( `ip` VARCHAR(26) NOT NULL, PRIMARY KEY (`ip`)) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
					$db->setQuery($query);
					$db->execute();
					
					// Add blacklisted ips to the new table
                    foreach ($blacklist as $ip)
					{
						if ( !empty($ip) ) {
							$valor = (object) array(
								'ip' => $ip							
							);
							$db->insertObject('#__securitycheckpro_blacklist', $valor, 'ip');							
						}
					}
					
					// Add witelisted ips to the new table
                    foreach ($whitelist as $ip)
					{
						if ( !empty($ip) ) {
							$valor = (object) array(
								'ip' => $ip							
							);
							$db->insertObject('#__securitycheckpro_whitelist', $valor, 'ip');							
						}
					}		
					
					// Delete blacklist and whitelist data from config
					unset($storage_config['blacklist']);
					unset($storage_config['whitelist']);
					
					// Update the storage_value with the new value
					$storage_config_json = json_encode($storage_config);				
					$query = "UPDATE #__securitycheckpro_storage SET storage_value = " . $db->quote($storage_config_json) . " WHERE storage_key='pro_plugin'";
					$db->setQuery($query);
					$db->execute();				
                    
                }
            }
        }
        
    }
	
	/* Uninstall installer plugin since 3.4.4 version as it will be no longer needed */
    private function _uninstall_installer_plugin()
    {
		
		$db = JFactory::getDBO();
		
		$columnName      = $db->quoteName("extension_id");
        $tableExtensions = $db->quoteName("#__extensions");
        $type              = $db->quoteName("type");
        $columnElement   = $db->quoteName("element");
        $columnType      = $db->quoteName("folder");
		
		try {
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__extensions'));
			$query->where($db->quoteName('element') . ' = ' . $db->quote('com_securitycheckpro'));
			$db->setQuery($query);
			$db->execute();
			$result = $db->loadAssocList();
		} catch(\Exception $e)
        {
			$result = null;
        }
		        
        // Si no existe versión previa no es necesario hacer ninguna acción
        if (!empty($result)) {
        
            // Decodificamos la información de la versión, que está en formato json en la entrada 'manifest_cache'
            $stack = json_decode($result[0]["manifest_cache"], true);
            
            // Versión de Securitycheck Pro instalada
            $scpro_version = $stack["version"];
            
            // Si la versión instalada es menor a la 3.4, hemos de desinstalar el plugin
            if (version_compare($scpro_version, "3.4.4", "lt")) {
				// Uninstall Installer plugin
				$db->setQuery(
					"SELECT 
						$columnName
					FROM
						$tableExtensions
					WHERE
						$type='plugin'
					AND
						$columnElement='securitycheckpro_installer'
					AND
						$columnType='installer'"
				);

				$id = $db->loadResult();
				
				if ($id) {
					$installer = new JInstaller();
					$uninstall_result = $installer->uninstall('plugin', $id, 1);        
				} else 
				{
					// The plugin is not installed??
				}
				
				if ($uninstall_result) {
					JFactory::getApplication()->enqueueMessage("'Securitycheck Pro Installer' plugin has been succesfuly uninstalled. It's no longer needed.");
				} else {
					JFactory::getApplication()->enqueueMessage("Error uninstalling 'Securitycheck Pro Installer' plugin. Needs to be removed manually.", 'error');
				}
				
				
			}
		}	
	}
    
    /**
     * Joomla! pre-flight event
     * 
     * @param string     $type   Installation type (install, update, discover_install)
     * @param JInstaller $parent Parent object
     */
    public function preflight($type, $parent)
    {
        // Only allow to install on PHP 5.3.0 or later
        if (!version_compare(PHP_VERSION, '5.3.0', 'ge')) {        
            JFactory::getApplication()->enqueueMessage('Securitycheck Pro requires, at least, PHP 5.3.0', 'error');
            return false;
        } else if (version_compare(JVERSION, '3.0.0', 'lt')) {
            // Only allow to install on Joomla! 3.0.0 or later, but not in 2.5 branch
            JFactory::getApplication()->enqueueMessage("This version doesn't work in Joomla! 2.5 branch", 'error');
            return false;
        } else if (version_compare(JVERSION, '4.0', 'ge')) {
			$this->badge_style = "bg-";
		}
        
        // Check if the 'mb_strlen' function is enabled
        if (!function_exists("mb_strlen")) {
            JFactory::getApplication()->enqueueMessage("The 'mb_strlen' function is not installed in your host. Please, ask your hosting provider about how to install it.", 'warning');
            return false;
        }
        
        // Do changes for versions previous to 3.4
        $this->_34_version_changes();  

		$this->	_uninstall_installer_plugin();	
        
        $this->_unistall_Securitycheck();    
        
    }
    
    /**
     * Runs after install, update or discover_update
     *
     * @param string     $type   install, update or discover_update
     * @param JInstaller $parent 
     */
    function postflight($type, $parent)
    {
        // Inicializamos las variables
        $existe_tabla = false;
                
        $db = JFactory::getDBO();
        $total_rows = $db->getTableList();
        
        if (!(is_null($total_rows))) {
            foreach ($total_rows as $table_name)
            {
                if (strstr($table_name, "securitycheckpro_logs")) {
                    $existe_tabla = true;
                }
            }
        }
        
        if (!$existe_tabla) {
            // Disable Securitycheck Pro plugin
            $tableExtensions = $db->quoteName("#__extensions");
            $columnElement   = $db->quoteName("element");
            $columnType      = $db->quoteName("type");
            $columnEnabled   = $db->quoteName("enabled");
            $db->setQuery(
                "UPDATE 
					$tableExtensions
				SET
					$columnEnabled=0
				WHERE
					$columnElement='securitycheckpro'
				AND
					$columnType='plugin'"
            );
            $db->execute();
            
            // Disable Securitycheck Pro Cron plugin
            $db->setQuery(
                "UPDATE 
					$tableExtensions
				SET
					$columnEnabled=0
				WHERE
					$columnElement='securitycheckpro_cron'
				AND
					$columnType='plugin'"
            );

            $db->execute();
            JFactory::getApplication()->enqueueMessage('There has been an error when creating database tables. Securitycheck Pro Web Firewall and Cron plugin has been disabled.', 'warning');
        }    
		
		try
        {
			if (!$this->update)
			{			
				// Establecemos la configuración 'Easy config' para la configuración inicial
				require_once JPATH_ADMINISTRATOR. DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'cpanel.php';
				$model = new SecuritycheckprosModelCpanel();
				$two_factor = $model->Set_Easy_Config();
			}
        }
        catch(\Exception $e)
        {
          
        }
		
        
    }
    
    /**
     * method to install the component
     *
     * @return void
     */
    function install($parent)
    {
        // General settings
        $status = new stdClass();
        $status->modules = array();
        
        // Array to store module and plugin installation results
        $result = array();
        $indice = 0;
        
        $installer = new JInstaller();
        
        
        $manifest = $parent->getParent()->getManifest();
        $source = $parent->getParent()->getPath('source');
        
        // Install module
        $db = JFactory::getDbo();
        $result[$indice] = $installer->install($source. DIRECTORY_SEPARATOR .'modules' . DIRECTORY_SEPARATOR .'mod_scpadmin_quickicons');
        $indice++;
                
        // Enable and configure module
        $query = "UPDATE #__modules SET position='icon', ordering = '1', published = '1' WHERE module='mod_scpadmin_quickicons'";
        $db->setQuery($query);
        $db->execute();
        
        $query = "SELECT id FROM #__modules WHERE module='mod_scpadmin_quickicons'";
        $db->setQuery($query);
        $modID = $db->loadResult();
                
        // If the module_id is empty, we'll get an SQL error and the installion process will break
        if ((!empty($modID)) && (is_int(intval($modID)))) {                        
           /* $query = "REPLACE #__modules_menu (moduleid,menuid) VALUES ({$modID}, 0)";
			$query = "UPDATE #__modules_menu SET ordering = '-100' WHERE name='System - url inspector'";			
			$db->setQuery($query);
            $db->execute();*/
        }
                
        $status->modules[] = array('name'=>'Securitycheck Pro - Quick Icons','client'=>'administrator', 'result'=>$result); 
        
        // Install plugins
                        
        foreach($manifest->plugins->plugin as $plugin)
        {
            $installer = new JInstaller();
            $attributes = $plugin->attributes();
            $plg = $source . DIRECTORY_SEPARATOR . $attributes['folder']. DIRECTORY_SEPARATOR . $attributes['plugin'];
            $result[$indice] = $installer->install($plg);
            $indice++;
        }
        
        // Update the URL inspector plugin ordering; it must be published the last
        $query = "UPDATE #__extensions SET ordering = '-100' WHERE name='System - url inspector'";
        $db->setQuery($query);
        $db->execute();
        
        // Check if url plugin is enabled
        $query = "SELECT enabled from #__extensions WHERE name='System - url inspector'";
        $db->setQuery($query);
        $this->url_plugin_enabled = $db->loadResult();

        $db = JFactory::getDbo();
        $tableExtensions = $db->quoteName("#__extensions");
        $columnElement   = $db->quoteName("element");
        $columnType      = $db->quoteName("type");
        $columnEnabled   = $db->quoteName("enabled");
            
        // Enable Securitycheck Pro plugin
        $db->setQuery(
            "UPDATE 
				$tableExtensions
			SET
				$columnEnabled=1
			WHERE
				$columnElement='securitycheckpro'
			AND
				$columnType='plugin'"
        );

        $db->execute();
        
        
        // Enable Securitycheck Pro Installer plugin
        $db->setQuery(
            "UPDATE 
				$tableExtensions
			SET
				$columnEnabled=1
			WHERE
				$columnElement='securitycheckpro_installer'
			AND
				$columnType='plugin'"
        );

        $db->execute();
                
        // Extract 'memory_limit' value cutting the last character
        $memory_limit = ini_get('memory_limit');
        $memory_limit = (int) substr($memory_limit, 0, -1);
                
        // If $memory_limit value is less or equal than 128, then whe will not enable de Cron plugin to avoid issues
        if (($memory_limit > 128) && (!$this->update)) {
        
            // Enable Securitycheck Pro Cron plugin
            $db->setQuery(
                "UPDATE 
					$tableExtensions
				SET
					$columnEnabled=1
				WHERE
					$columnElement='securitycheckpro_cron'
				AND
					$columnType='plugin'"
            );

            $db->execute();
        }
                
        // Install message
        $this->install_message($this->id_free, $this->result_free, $result, $status, $memory_limit);
    }
    
    /**
     * method to uninstall the component
     *
     * @return void
     */
    function uninstall($parent)
    {
    
        // General settings
        $status = new stdClass();
        $status->modules = array();
        
        // Array to store uninstall results
        $result = array();
        
        $db = JFactory::getDbo();
        
        // Uninstall module
        $db->setQuery("SELECT extension_id FROM #__extensions WHERE type = 'module' AND element = 'mod_scpadmin_quickicons' LIMIT 1");
        (int) $id = $db->loadResult();
        if ($id) {
            $installer = new JInstaller();
            $result[0] = $installer->uninstall('module', $id);
            $status->modules[] = array('name'=>'Securitycheck Pro - Quick Icons','client'=>'administrator', 'result'=>$result);            
        }
        
        $columnName      = $db->quoteName("extension_id");
        $tableExtensions = $db->quoteName("#__extensions");
        $type              = $db->quoteName("type");
        $columnElement   = $db->quoteName("element");
        $columnType      = $db->quoteName("folder");
        $result = '';
            
        // Uninstall  Securitycheck Pro plugin
        $db->setQuery(
            "SELECT 
				$columnName
			FROM
				$tableExtensions
			WHERE
				$type='plugin'
			AND
				$columnElement='securitycheckpro'
			AND
				$columnType='system'"
        );

        $id = $db->loadResult();

        if ($id) {
            $installer = new JInstaller();
            $result[1] = $installer->uninstall('plugin', $id, 1);        
        } else {
            $result[1] = false;
        }
        
        // Uninstall  Securitycheck Pro Cron plugin
        $db->setQuery(
            "SELECT 
				$columnName
			FROM
				$tableExtensions
			WHERE
				$type='plugin'
			AND
				$columnElement='securitycheckpro_cron'
			AND
				$columnType='system'"
        );

        $id = $db->loadResult();

        if ($id) {
            $installer = new JInstaller();
            $result[2] = $installer->uninstall('plugin', $id, 1);        
        } else 
        {
            $result[2] = false;
        }
        
        // Uninstall  Securitycheck Pro URL inspector
        $db->setQuery(
            "SELECT 
				$columnName
			FROM
				$tableExtensions
			WHERE
				$type='plugin'
			AND
				$columnElement='url_inspector'
			AND
				$columnType='system'"
        );

        $id = $db->loadResult();

        if ($id) {
            $installer = new JInstaller();
            $result[3] = $installer->uninstall('plugin', $id, 1);        
        } else {
            $result[3] = false;
        }        
                        
        // Uninstall message
        $this->uninstall_message($result, $status);
        
    }
    
    /**
     * method to update the component
     *
     * @return void
     */
    function update($parent)
    {        
        // This variable is updated.
        $this->update = true;        
        $this->install($parent);        
    }
    
    /**
     * method to show the install message
     *
     * @return void
     */
    function install_message($id_free,$result_free,$result,$status,$memory_limit)
    {
        // Initialize variables
        $cabecera = '';
        $result_ok = '';
        $result_not_ok = '';
            
        if (!($this->update)) {
            $cabecera = JText::_('COM_SECURITYCHECKPRO_HEADER_INSTALL');
            $result_ok = JText::_('COM_SECURITYCHECKPRO_INSTALLED');
            $result_not_ok = JText::_('COM_SECURITYCHECKPRO_NOT_INSTALLED');
        } else 
        {
            $cabecera = JText::_('COM_SECURITYCHECKPRO_HEADER_UPDATE');
            $result_ok = JText::_('COM_SECURITYCHECKPRO_UPDATED');
            $result_not_ok = JText::_('COM_SECURITYCHECKPRO_NOT_UPDATED');
        }
        
        ?>
        <img src='../media/com_securitycheckpro/images/tick_48x48.png' style='float: left; margin: 5px;'>
        <?php
        if (!($this->update)) {            
            ?>
            <h1><?php echo $cabecera ?></h1>
            <h2><?php echo JText::_('COM_SECURITYCHECKPRO_WELCOME'); ?></h2>
            <?php 
        } else {
            ?>
            <h2><?php echo $cabecera ?></h2>
            <?php
        }
        ?>
            <div class="securitycheck-bootstrap">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="title" colspan="2"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION'); ?></th>
                        <th width="30%"><?php echo JText::_('COM_SECURITYCHECKPRO_STATUS'); ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                <tbody>
                    <tr>
                        <td colspan="2">Securitycheck Pro <?php echo JText::_('COM_SECURITYCHECKPRO_COMPONENT'); ?></td>
                        <td>
        <?php 
                                $span = "<span class=\"badge " . $this->badge_style . "success\">";                                
        ?>
          <?php echo $span . $result_ok; ?>
                            </span>
                        </td>
                    </tr>
                    <tr class="row0">
                        <td class="key" colspan="2">Securitycheck Pro <?php echo JText::_('COM_SECURITYCHECKPRO_PLUGIN'); ?></td>
        <?php
        if ($result[1]) {
            ?>
                            <td>
            <?php 
            $span = "<span class=\"badge " . $this->badge_style . "success\">";                                
            ?>
            <?php echo $span . $result_ok; ?>
                                </span>
            <?php 
            $span = "<span class=\"badge " . $this->badge_style . "info\">";    
            $message = JText::_('COM_SECURITYCHECKPRO_PLUGIN_ENABLED');                                                                                    
            ?>
            <?php echo $span . $message; ?>
                            </td>
            <?php
        } else {
            ?>
                            <td>
            <?php 
            $span = "<span class=\"badge " . $this->badge_style . "danger\">";                                
            ?>
            <?php echo $span . $result_not_ok; ?>
                                </span>
                            </td>
            <?php
        }
        ?>
                    </tr>
                    <tr class="row0">
                        <td class="key" colspan="2">Securitycheck Pro Cron <?php echo JText::_('Plugin'); ?></td>
        <?php
        if ($result[2]) {
            ?>
                        <td>
            <?php 
            $span = "<span class=\"badge " . $this->badge_style . "success\">";                                
            ?>
            <?php echo $span . $result_ok; ?>
                            </span>
            <?php 
            $limit = false;
            if ($this->update) {
                $span = "<span class=\"badge " . $this->badge_style . "info\">";    
                $message = JText::_('COM_SECURITYCHECKPRO_PLUGIN_ENABLED');
            } else if ($memory_limit > 128) {
                $span = "<span class=\"badge " . $this->badge_style . "info\">";    
                $message = JText::_('COM_SECURITYCHECKPRO_PLUGIN_ENABLED');
            } else if ($memory_limit <= 128) {
                $span = "<span class=\"badge " . $this->badge_style . "warning\">";
                $message = JText::_('COM_SECURITYCHECKPRO_PLUGIN_DISABLED');
                $limit = true;
            }
            ?>
            <?php echo $span . $message; ?>
                            </span>
            <?php
            if ($limit) {
                ?>
                                <br/>
                                <tr>
                                    <td>
                <?php echo JText::_('COM_SECURITYCHECKPRO_MEMORY_LIMIT_LOW'); ?>    
                                    </td>                                 
                                </tr>
                <?php
            }
            ?>
                        </td>
            <?php
        } else {
            ?>
                        <td>
            <?php 
            $span = "<span class=\"badge " . $this->badge_style . "danger\">";                                
            ?>
            <?php echo $span . $result_not_ok; ?>
                            </span>
                        </td>
            <?php
        }
        ?>
                    </tr>
                    <tr class="row0">
                        <td class="key" colspan="2">URL Inspector <?php echo JText::_('Plugin'); ?></td>
        <?php
        if ($result[3]) {
            ?>
                            <td>
            <?php 
            $span = "<span class=\"badge " . $this->badge_style . "success\">";                                
            ?>
            <?php echo $span . $result_ok; ?>
                                </span>
            <?php 
            if ($this->url_plugin_enabled) {
                $span = "<span class=\"badge " . $this->badge_style . "info\">";    
                $message = JText::_('COM_SECURITYCHECKPRO_PLUGIN_ENABLED');
            } else 
            {
                $span = "<span class=\"badge " . $this->badge_style . "danger\">";    
                $message = JText::_('COM_SECURITYCHECKPRO_PLUGIN_DISABLED');
            }
            ?>
            <?php echo $span . $message; ?>                                
                            </td>
            <?php
        } else
                        {
            ?>
                            <td>
            <?php 
            $span = "<span class=\"badge " . $this->badge_style . "danger\">";                                
            ?>
            <?php echo $span . $result_not_ok; ?>
                                </span>
                            </td>
            <?php
        }
        ?>
                    </tr>                    
        <?php
        if (count($status->modules) > 0) {
            ?>
                        <tr class="row0">
                        <td class="key" colspan="2">Securitycheck Pro Info <?php echo JText::_('COM_SECURITYCHECKPRO_MODULE'); ?></td>
            <?php
            if ($status->modules['0']['result']) {
                ?>
                            <td>
                <?php 
                $span = "<span class=\"badge " . $this->badge_style . "success\">";                                
                ?>
                <?php echo $span . $result_ok; ?>
                                </span>
                <?php 
                $span = "<span class=\"badge " . $this->badge_style . "info\">";    
                $message = JText::_('COM_SECURITYCHECKPRO_PLUGIN_ENABLED');                                                                                    
                ?>
                <?php echo $span . $message; ?>
                            </td>
                <?php
            } else
            {
                ?>
                            <td>
                <?php 
                $span = "<span class=\"badge " . $this->badge_style . "danger\">";                                
                ?>
                <?php echo $span . $result_not_ok; ?>
                                </span>
                            </td>                            
                <?php
            }
            ?>
                        </tr>
            <?php
        }
        if ($id_free) {
            ?>
                        <tr class="row0">
                            <td class="key" colspan="2">Securitycheck <?php echo JText::_('COM_SECURITYCHECK_COMPONENT'); ?></td>
            <?php
            if ($result_free) {
                ?>
                            <td>
                <?php 
                $span = "<span class=\"badge " . $this->badge_style . "success\">";                                
                ?>
                <?php echo $span . JText::_('COM_SECURITYCHECKPRO_UNINSTALLED'); ?>
                                </span>
                            </td>                                    
                <?php
            } else 
            {
                ?>
                            <td>
                <?php 
                $span = "<span class=\"badge " . $this->badge_style . "danger\">";                                
                ?>
                <?php echo $span . JText::_('COM_SECURITYCHECK_NOT_UNINSTALLED'); ?>
                                </span>
                            </td>                            
                <?php
            }
            ?>
                        </tr>
            <?php
        }
        ?>
                </tbody>
            </table>
            </div>
        <?php
    }

    /**
     * method to show the uninstall message
     *
     * @return void
     */
    function uninstall_message($result,$status)
    {
        ?>
        <h1><?php echo JText::_('COM_SECURITYCHECKPRO_HEADER_UNINSTALL'); ?></h1>
        <h2><?php echo JText::_('COM_SECURITYCHECKPRO_GOODBYE'); ?></h2>
        <div class="securitycheck-bootstrap">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="title" colspan="2"><?php echo JText::_('COM_SECURITYCHECKPRO_EXTENSION'); ?></th>
                    <th width="30%"><?php echo JText::_('COM_SECURITYCHECKPRO_STATUS'); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
            <tbody>
                <tr>
                    <td colspan="2">Securitycheck Pro <?php echo JText::_('COM_SECURITYCHECKPRO_COMPONENT'); ?></td>
                    <td>
        <?php 
          $span = "<span class=\"badge " . $this->badge_style . "success\">";                                
        ?>
         <?php echo $span . JText::_('COM_SECURITYCHECKPRO_UNINSTALLED'); ?>
                        </span>
                    </td>                    
                </tr>
                <tr class="row0">
                    <td class="key" colspan="2">Securitycheck Pro <?php echo JText::_('COM_SECURITYCHECKPRO_PLUGIN'); ?></td>
        <?php
        if ($result[1]) {
            ?>
                        <td>
            <?php 
            $span = "<span class=\"badge " . $this->badge_style . "success\">";                                
            ?>
            <?php echo $span . JText::_('COM_SECURITYCHECKPRO_UNINSTALLED'); ?>
                            </span>
                        </td>
            <?php
        } else 
        {
            ?>
                        <td>
            <?php 
            $span = "<span class=\"badge " . $this->badge_style . "danger\">";                                
            ?>
            <?php echo $span . JText::_('COM_SECURITYCHECKPRO_NOT_INSTALLED'); ?>
                            </span>
                        </td>                        
            <?php
        }
        ?>
                </tr>
                <tr class="row0">
                    <td class="key" colspan="2">Securitycheck Pro Cron <?php echo JText::_('Plugin'); ?></td>
        <?php
        if ($result[2]) {
            ?>
                        <td>
            <?php 
            $span = "<span class=\"badge " . $this->badge_style . "success\">";                                
            ?>
            <?php echo $span . JText::_('COM_SECURITYCHECKPRO_UNINSTALLED'); ?>
                            </span>
                        </td>
            <?php
        } else 
        {
            ?>
                        <td>
            <?php 
            $span = "<span class=\"badge " . $this->badge_style . "danger\">";                                
            ?>
            <?php echo $span . JText::_('COM_SECURITYCHECKPRO_NOT_INSTALLED'); ?>
                            </span>
                        </td>
            <?php
        }
        ?>
                </tr>
                <tr class="row0">
                    <td class="key" colspan="2">URL Inspector <?php echo JText::_('Plugin'); ?></td>
        <?php
        if ($result[3]) {
            ?>
                        <td>
            <?php 
            $span = "<span class=\"badge " . $this->badge_style . "success\">";                                
            ?>
            <?php echo $span . JText::_('COM_SECURITYCHECKPRO_UNINSTALLED'); ?>
                            </span>
                        </td>
            <?php
        } else
        {
            ?>
                        <td>
            <?php 
            $span = "<span class=\"badge " . $this->badge_style . "danger\">";                                
            ?>
            <?php echo $span . JText::_('COM_SECURITYCHECKPRO_NOT_INSTALLED'); ?>
                            </span>
                        </td>
            <?php
        }
        ?>
                </tr>                
        <?php
        if (count($status->modules) > 0) {
            ?>
                    <tr class="row0">
                    <td class="key" colspan="2">Securitycheck Pro Info <?php echo JText::_('COM_SECURITYCHECKPRO_MODULE'); ?></td>
            <?php
            if ($status->modules['0']['result']) {
                ?>
                        <td>
                <?php 
                   $span = "<span class=\"badge " . $this->badge_style . "success\">";                                
                ?>
                <?php echo $span . JText::_('COM_SECURITYCHECKPRO_UNINSTALLED'); ?>
                            </span>
                        </td>
                    <?php
            } else
            {
                ?>
                        <td>
                <?php 
                 $span = "<span class=\"badge " . $this->badge_style . "danger\">";                                
                ?>
                <?php echo $span . JText::_('COM_SECURITYCHECKPRO_NOT_INSTALLED'); ?>
                            </span>
                        </td>
                  <?php
            }
            ?>
                    </tr>
            <?php
        }
        ?>
            </tbody>
        </table>
        </div>
        <?php
    }
}
?>
