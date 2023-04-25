<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2020. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;


jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class com_falangInstallerScript
{

        /** @var string The component's name */
        protected $_faboba_extension = 'pkg_falang';

        protected $_previous_version = null;

        /** @var array The list of extra modules and plugins to install */
        private $installation_queue = array(
            // modules => { (folder) => { (module) => { (position), (published) } }* }*
            'modules' => array(
                'site' => array(
                    'falang' => array('position-7', 1)
                )
            ),
             // plugins => { (folder) => { (element) => (published) }* }*
            'plugins' => array(
                'system' => array(
                    'falangdriver'				=> 1,
                    'falangquickjump'		=> 1
                )
            )
        );

        // plugins => { (folder) => { (element) => (published) }* }*
        private $installation_params = array(
           'components' => array(
                       'falang' => array(
                           'frontEndEdition'  => 0,
                           'show_tpl_lang' =>0,
                           'show_list'  => 1,
                           'show_form' => 1,
                           'component_list' => 'com_menus#menu#id#items,item#10#13com_content#content#id#default,articles,article,featured#10#13com_categories#categories#id#default,categories,category#10#13com_modules#modules#id#default,modules,module#10#13com_newsfeeds#newsfeeds#id#default,newsfeeds,newsfeed#10#13com_fields#fields#id#default,fields,field#10#13com_fields#fields_groups#id#default,groups,group',
	                       'copy_images_and_urls' => 0,
	                       'advanced_menu_show' => 0,
                           'advanced_router' => 0,
	                       'update_caching' => 1,
                           'copy_custom_fields' => 0,
	                       'ie_list' => 'content,Articles#10#13menu,Menu#10#13modules,Modules'
                   )
           )
        );

        /** @var array Obsolete files and folders to remove from the Core release only */
        private $falangRemoveFilesFree = array(
            'files'	=> array(
                'administrator/components/com_falang/views/translate/tmpl/popup.php'
            ),
            'folders' => array()
        );

    private $falangRemoveFilesPaid = array(
        'files'	=> array(
            'administrator/components/com_falang/views/translate/tmpl/popup_free.php'
        ),
        'folders' => array()
    );


    function install($parent)
        {
        }

        function uninstall($parent)
        {
        }

        function update($parent)
        {
            //change the component_list structure from the 2.0 falang version
            if (version_compare($this->_previous_version, '2.0.0', 'eq')) {
                $params['component_list'] = $this->installation_params['components']['falang']['component_list'];
                $this->setParams( $params );
            }
            //update module params for advance dropdown and show_name value version 2.2.1+
            if (version_compare($this->_previous_version, '2.2.0', 'le')) {
                $db = JFactory::getDbo();
                $db->setQuery('SELECT params FROM #__extensions WHERE name = ' . $db->quote('mod_falang'));
                $params = json_decode($db->loadResult(), true);
                $params['show_name'] = '0';
                $params['advanced_dropdown'] = '0';

                $paramsString = json_encode( $params );
                $db->setQuery('UPDATE #__extensions SET params = ' .
                    $db->quote($paramsString) .
                    ' WHERE name = ' . $db->quote('mod_falang'));
                $db->execute();

                //update module params
                $query = $db->getQuery(true);
                $db->getQuery(true);
                $query->select('id,params')
                    ->from('#__modules')
                    ->where('module= "mod_falang"');
                $db->setQuery($query);
                $rows =  $db->loadObjectList();

                foreach ($rows as $row) {
                    $params = json_decode($row->params,true);
                    $params['show_name'] = '0';
                    $params['advanced_dropdown'] = '0';
                    $paramsString = json_encode( $params );
                    $db->setQuery('UPDATE #__modules SET params = ' .
                        $db->quote($paramsString) .
                        ' WHERE id = ' . $row->id);
                    $db->execute();
                }

            }

        }

        function preflight($type, $parent)
        {
        	//DONE in package script file
	        $this->_previous_version = $this->getParam('version');
        }

		/**
		 * Runs right after any installation action is preformed on the component.
		 *
		 * @param  string    $type   - Type of PostFlight action. Possible values are:
		 *                           - * install
		 *                           - * update
		 *                           - * discover_install
		 * @param  \stdClass $parent - Parent object calling object.
		 *
		 * @return void
		 */
        function postflight($type, $parent)
        {
                JLoader::import('joomla.filesystem.file');
                include_once( JPATH_ADMINISTRATOR . '/components/com_falang/version.php');
                $version = new FalangVersion();

                $isFalangFree = $version->_versiontype == 'free'?true:false;
                if($isFalangFree) {
                    $falangRemoveFiles = $this->falangRemoveFilesFree;
                } else {
                    $falangRemoveFiles = $this->falangRemoveFilesPaid;
                }
                $this->_removeObsoleteFilesAndFolders($falangRemoveFiles);

                $status = $this->_installSubextensions($parent,$type);
                $this->_setDefaultParams($type);

                // Remove update site
                $this->_removeUpdateSite();

	            //Fix plugin order languagefilter and falangdriver (due to the 3.6.2 joomla version)
                //no more necessary with Joomla 4
	            //require_once JPATH_ADMINISTRATOR.'/components/com_falang/helpers/controllerHelper.php';
	            //FalangControllerHelper::_checkPlugin();
        }


        private function _installSubextensions($parent,$type) {
                $src = $parent->getParent()->getPath('source');

                $db = JFactory::getDbo();

                $status = new JObject();
                $status->modules = array();
                $status->plugins = array();

                // Falang use native joomla installation package for components, modules and plugins
                // Modules configuration
                if(count($this->installation_queue['modules'])) {
                        foreach($this->installation_queue['modules'] as $folder => $modules) {
                                if(count($modules)) foreach($modules as $module => $modulePreferences) {
                                        // Was the module already installed?
                                        $sql = $db->getQuery(true)
                                            ->select('COUNT(*)')
                                            ->from('#__modules')
                                            ->where($db->qn('module') . ' = ' . $db->q('mod_' . $module));
                                        $db->setQuery($sql);
                                        $count = $db->loadResult();
                                        // Modify where it's published and its published state
                                        if($count == 1 && $type == 'install') {
                                                // A. Position and state
                                                list($modulePosition, $modulePublished) = $modulePreferences;

                                                $sql = $db->getQuery(true)
                                                    ->update($db->qn('#__modules'))
                                                    ->set($db->qn('position').' = '.$db->q($modulePosition))
                                                    ->where($db->qn('module').' = '.$db->q('mod_'.$module));
                                                if($modulePublished) {
                                                        $sql->set($db->qn('published').' = '.$db->q('1'));
                                                }
                                                $db->setQuery($sql);
                                                $db->execute();

                                                // C. Link to all pages
                                                $query = $db->getQuery(true);
                                                $query->select('id')->from($db->qn('#__modules'))
                                                    ->where($db->qn('module').' = '.$db->q('mod_'.$module));
                                                $db->setQuery($query);
                                                $moduleid = $db->loadResult();

                                                $query = $db->getQuery(true);
                                                $query->select('*')->from($db->qn('#__modules_menu'))
                                                    ->where($db->qn('moduleid').' = '.$db->q($moduleid));
                                                $db->setQuery($query);
                                                $assignments = $db->loadObjectList();
                                                $isAssigned = !empty($assignments);
                                                if(!$isAssigned) {
                                                        $o = (object)array(
                                                            'moduleid'	=> $moduleid,
                                                            'menuid'	=> 0
                                                        );
                                                        $db->insertObject('#__modules_menu', $o);
                                                }

                                        }

                                }


                        }
                }

                // Falang use native joomla installation package for components, modules and plugins
                // Plugins publish
                if(count($this->installation_queue['plugins'])) {
                        foreach ($this->installation_queue['plugins'] as $folder => $plugins) {
                                if (count($plugins)) foreach ($plugins as $plugin => $published) {
                                        // Was the plugin already installed?
//                                        $query = $db->getQuery(true)
//                                            ->select('COUNT(*)')
//                                            ->from($db->qn('#__extensions'))
//                                            ->where($db->qn('element').' = '.$db->q($plugin))
//                                            ->where($db->qn('folder').' = '.$db->q($folder));
//                                        $db->setQuery($query);
//                                        $count = $db->loadResult();
//                                    if($published && !$count) {
                                        if($published ) {
                                                $query = $db->getQuery(true)
                                                    ->update($db->qn('#__extensions'))
                                                    ->set($db->qn('enabled').' = '.$db->q('1'))
                                                    ->where($db->qn('element').' = '.$db->q($plugin))
                                                    ->where($db->qn('folder').' = '.$db->q($folder));
                                                $db->setQuery($query);
                                                $db->execute();
                                        }
                                }
                        }

                }
                return $status;
        }

        private function _setDefaultParams($type){


                $db = JFactory::getDBO();
                $updateParams = false;

                if(count($this->installation_params['components'])) {
                        foreach ($this->installation_params['components'] as $folder => $components) {
                                if (count($components)) {
                                    $sql = $db->getQuery(true)
                                        ->select($db->qn('params'))
                                        ->from($db->qn('#__extensions'))
                                        ->where($db->qn('element') . " = " . $db->q('com_' . $folder));
                                    $db->setQuery($sql);
                                    $config_ini = $db->loadResult();

                                    $config_ini = json_decode($config_ini, true);
                                    if (empty($config_ini)){$config_ini=array();}

                                    foreach ($components as $componentParams => $value) {

                                        if (!array_key_exists($componentParams, $config_ini)) {
                                            //set value to key
                                            $config_ini[$componentParams] = $value;
                                            $updateParams = true;
                                        }

                                    }
                                    //update params
                                    if ($updateParams) {
                                        $query = $db->getQuery(true);
                                        $query->update($db->quoteName('#__extensions'));
                                        $parameter = new JRegistry;
                                        $parameter->loadArray($config_ini);
                                        $defaults = json_encode($config_ini); // JSON format for the parameters
                                        $defaults = str_replace('#10#13','\r\n',$defaults);
                                        $query->set($db->quoteName('params') . ' = ' . $db->q($defaults));
                                        $query->where($db->quoteName('name') . ' = ' . $db->q('com_' . $folder));
                                        $db->setQuery($query);
                                        $db->execute();
                                    }
                                }
                        }
                }




        }

    /**
     * Removes obsolete files and folders
     *
     * @param array $falangRemoveFiles
     */
    private function _removeObsoleteFilesAndFolders($falangRemoveFiles)
    {
        // Remove files
        jimport('joomla.filesystem.file');
        if(!empty($falangRemoveFiles['files'])) foreach($falangRemoveFiles['files'] as $file) {
            $f = JPATH_ROOT.'/'.$file;
            if(!JFile::exists($f)) continue;
            JFile::delete($f);
        }

        // Remove folders
        jimport('joomla.filesystem.file');
        if(!empty($falangRemoveFiles['folders'])) foreach($falangRemoveFiles['folders'] as $folder) {
            $f = JPATH_ROOT.'/'.$folder;
            if(!JFolder::exists($f)) continue;
            JFolder::delete($f);
        }

        //remove joomla 2.X content element
        if (version_compare(JVERSION,'3.0.0','<')) {
            $f = JPATH_ROOT . '/administrator/components/com_falang/contentelements/tags.xml';
            if (JFile::exists($f)) {
                JFile::delete($f);
            }
        }
        //remove joomla 3.X content element
        if (version_compare(JVERSION,'3.4','>=')) {
            $f = JPATH_ROOT . '/administrator/components/com_falang/contentelements/weblinks.xml';
            if (JFile::exists($f)) {
                JFile::delete($f);
            }
        }

		//version before 3.7.0 don't support custom fields
	    if( version_compare(JVERSION, '3.7.0', '<') ) {
		    $f = JPATH_ROOT . '/administrator/components/com_falang/contentelements/fields.xml';
		    if (JFile::exists($f)) {
			    JFile::delete($f);
		    }
		    $f = JPATH_ROOT . '/administrator/components/com_falang/contentelements/fields_groups.xml';
		    if (JFile::exists($f)) {
			    JFile::delete($f);
		    }
		    $f = JPATH_ROOT . '/administrator/components/com_falang/contentelements/fields_values.xml';
		    if (JFile::exists($f)) {
			    JFile::delete($f);
		    }

	    }

	    //remove custom fiels value
	    $f = JPATH_ROOT . '/administrator/components/com_falang/contentelements/fields_values.xml';
	    if (JFile::exists($f)) {
		    JFile::delete($f);
	    }

    }


    /**
     * Removes obsolete update sites created for old falang version type collection on package
     */
    private function _removeUpdateSite()
    {
        // Get some info on all the stuff we've gotta delete
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select(array(
                $db->qn('s').'.'.$db->qn('update_site_id'),
                $db->qn('e').'.'.$db->qn('extension_id'),
                $db->qn('e').'.'.$db->qn('element'),
                $db->qn('s').'.'.$db->qn('location'),
            ))
            ->from($db->qn('#__update_sites').' AS '.$db->qn('s'))
            ->join('INNER',$db->qn('#__update_sites_extensions').' AS '.$db->qn('se').' ON('.
                $db->qn('se').'.'.$db->qn('update_site_id').' = '.
                $db->qn('s').'.'.$db->qn('update_site_id')
                .')')
            ->join('INNER',$db->qn('#__extensions').' AS '.$db->qn('e').' ON('.
                $db->qn('e').'.'.$db->qn('extension_id').' = '.
                $db->qn('se').'.'.$db->qn('extension_id')
                .')')
            ->where($db->qn('s').'.'.$db->qn('type').' = '.$db->q('collection'))
            ->where($db->qn('e').'.'.$db->qn('type').' = '.$db->q('package'))
            ->where($db->qn('e').'.'.$db->qn('element').' = '.$db->q($this->_faboba_extension))
        ;
        $db->setQuery($query);
        $oResult = $db->loadObject();

        // If no record is found, do nothing. We've already killed the monster!
        if(is_null($oResult)) return;

        // Delete the #__update_sites record
        $query = $db->getQuery(true)
            ->delete($db->qn('#__update_sites'))
            ->where($db->qn('update_site_id').' = '.$db->q($oResult->update_site_id));
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $exc) {
            // If the query fails, don't sweat about it
        }

        // Delete the #__update_sites_extensions record
        $query = $db->getQuery(true)
            ->delete($db->qn('#__update_sites_extensions'))
            ->where($db->qn('update_site_id').' = '.$db->q($oResult->update_site_id));
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $exc) {
            // If the query fails, don't sweat about it
        }

        // Delete the #__updates records
        $query = $db->getQuery(true)
            ->delete($db->qn('#__updates'))
            ->where($db->qn('update_site_id').' = '.$db->q($oResult->update_site_id));
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $exc) {
            // If the query fails, don't sweat about it
        }
    }

    /*
         * get a variable from the manifest file (actually, from the manifest cache).
         */
    function getParam( $name ) {
        $db = JFactory::getDbo();
        $db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_falang"');
        $manifest = json_decode( $db->loadResult(), true );
        return $manifest[ $name ];
    }

    /*
         * sets parameter values in the component's row of the extension table
         */
    function setParams($param_array) {
        if ( count($param_array) > 0 ) {
            // read the existing component value(s)
            $db = JFactory::getDbo();
            $db->setQuery('SELECT params FROM #__extensions WHERE name = "com_falang"');
            $params = json_decode( $db->loadResult(), true );
            // add the new variable(s) to the existing one(s)
            foreach ( $param_array as $name => $value ) {
                $params[ (string) $name ] = (string) $value;
            }
            // store the combined new and existing values back as a JSON string
            $paramsString = json_encode($params); // JSON format for the parameters
            $paramsString = str_replace('#10#13','\r\n',$paramsString);
            $db->setQuery('UPDATE #__extensions SET params = ' .
                $db->quote( $paramsString ) .
                ' WHERE name = "com_falang"' );
            $db->execute();
        }
    }
}
