<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

class CPanelModelCPanel extends JModelLegacy
{
	protected $_modelName = 'cpanel';

	/**
	 * return the model name
	 */
	public function getName() {
		return $this->_modelName;
	}

	/**
	 * Get a list of panel state information
	 */
	public function getPanelStates() {
            //sbou TODO rewrite this part
            return array();
		$panelStates = array();
		$systemState = $this->_checkSystemState();
                if (isset($systemState)) {
                    $panelStates['directory_state'] = $systemState['directory_state'];
                    $panelStates['directory'] = $systemState['directory'];
                    $panelStates['extension_state'] = $systemState['extension_state'];
                    $panelStates['extension'] = $systemState['extension'];
                    $panelStates['performance_state'] = $systemState['performance_state'];
                    $panelStates['performance'] = $systemState['performance'];
                }
		//$panelStates['system'] = $this->_getSystemInfo();

		return $panelStates;
	}

	/**
	 * Get a list of content informations
	 */
	public function getContentInfo() {
		$contentInfo = array();
		$contentInfo['unpublished'] = $this->_testUnpublisedTranslations();
		return $contentInfo;
	}

	/**
	 * Get the list of published tabs, based on the ID
	 */
	public function getPublishedTabs() {
		$tabs = array();

		$pane = new stdClass();
		$pane->title = 'Information';
		$pane->name = 'Information';
		$pane->alert = false;
		$tabs[] = $pane;

		// Validating other tabs based on extension configuration
		// JFTODO Move all panels to their own administrator module
		$params = JComponentHelper::getParams('com_falang');
//		if( $params->get('showPanelNews', 1) ) {
//			$pane = new stdClass();
//			$pane->title = 'News';
//			$pane->name = 'JFNews';
//			$pane->alert = false;
//			$tabs[] = $pane;
//		}
//		if( $params->get('showPanelUnpublished', 1) ) {
//			$pane = new stdClass();
//			$pane->title = 'TITLE_UNPUBLISHED';
//			$pane->name = 'ContentState';
//			$pane->alert = false;
//			$tabs[] = $pane;
//		}
//		if( $params->get('showPanelState', 1) ) {
//			$pane = new stdClass();
//			$pane->title = 'System State';
//			$pane->name = 'SystemState';
//			$pane->alert = false;
//			$tabs[] = $pane;
//		}

		return $tabs;
	}

	/**
	 * This method checks the different system states based on the definition in the component XML file.
	 * @return array with rows of the different component states
	 *
	 */
	private function _checkSystemState() {
		$db = JFactory::getDBO();

		$checkResult = array();

		// Read the file to see if it's a valid template XML file
		$xmlDoc = new DOMDocument();

		$xmlfile = FALANG_ADMINPATH .DS.'sql'.DS. 'check.xml';

		if (!$xmlDoc->load( $xmlfile)) {
			return $checkResult;
		}

		$element = $xmlDoc->documentElement;

		// Joomla 1.5 uses install
//		if ($element->nodeName != 'install') {
//			return $checkResult;
//		}
//		if ($element->getAttribute( "type" ) != "component") {
//			return $checkResult;
//		}
		$checkElements = $xmlDoc->getElementsByTagName('check')->item(0);
		if (!isset($checkElements) || !$checkElements->hasChildNodes()){
			return $checkResult;
		}
		// Default values of different master states
		$checkResult['directory_state'] = true;
		$checkResult['extension_state'] = true;
		$checkResult['performance_state'] = true;

		foreach ($checkElements->childNodes as $child){
			$type = $child->nodeName;
			$check = new stdClass();
			switch ($type) {
				case 'directory':
					$check->description = $child->textContent;
					$check->result = is_writable(JPATH_SITE .DS. $check->description) ? true : false;
					$check->resultText = $check->result ? JText::_('writable') : JText::_('not writable');
					$check->link = '';
					$checkResult[$type][] = $check;
					$checkResult[$type. '_state'] = $checkResult[$type. '_state'] & $check->result;
					break;

				case 'extension':
					$check->description = JText::_($child->getAttribute('name'));
                                        //sbou
                                        $table = 'extensions';
					$type = $child->getAttribute('type');
                                        //fin sbou
					$field = $child->getAttribute('field');
					$value = $child->getAttribute('value');
					$name = $child->getAttribute('name');
					$condition = $child->textContent;

					if ($field=='ordering'){
                                                //sbou
						$sql = "SELECT extension_id, element, ordering FROM #__$table  WHERE type= '$type' AND $condition ORDER BY ordering";
                                                //fin sbou
						$db->setQuery($sql);
						$resultValues = $db->loadObjectList();
						if (array_key_exists($value,$resultValues) && $resultValues[$value]->element==$name){
							$check->result = true ;
							$check->resultText = JText::_($field);
							$check->link = JURI::root().'administrator/index.php?option=com_'.$table.'&client=task=editA&hidemainmenu=1&id='.$resultValues[$value]->id;
						}
						else {
                                                        //sbou
							//$sql = "SELECT $field, id FROM #__$table WHERE $condition";
							$sql = "SELECT $field, extension_id FROM #__$table WHERE type = '$type' AND $condition";
                                                        //fin sbou
							$db->setQuery($sql);
							$resultValue = $db->loadRow();
							$check->result = false;
							$check->resultText = JText::_('un'.$field);
							$check->link = JURI::root().'administrator/index.php?option=com_'.$table.'&client=task=editA&hidemainmenu=1&id='.$resultValue[1];
						}
					}
					else {
						//sbou
                                                //$sql = "SELECT $field, id FROM #__$table WHERE $condition";
                                                $sql = "SELECT $field, extension_id FROM #__$table WHERE type = '$type' AND $condition";
                                                //sbou
						$db->setQuery($sql);
						$resultValue = $db->loadRow();

						if( $resultValue != null ) {
							$check->result = ($value == $resultValue[0]) ? true : false;
							$check->resultText = $check->result ? JText::_($field) : JText::_('un'.$field);

							$check->link = JURI::root().'administrator/index.php?option=com_'.$table.'&client=task=editA&hidemainmenu=1&id='.$resultValue[1];
						} else {
							$check->result = false;
							$check->resultText = JText::_('not installed');

							$check->link = '';
						}
					}

					$checkResult[$type][] = $check;
                                        dump($type);
                                        dump($check->result);
					$checkResult[$type. '_state'] = $checkResult[$type. '_state'] & $check->result;
					break;
					
				case 'performance':
					$check->description = JText::_($child->getAttribute('name'));
					$check->name = $child->getAttribute('name');
					$check->type = $child->getAttribute('type');
					$check->link = $child->getAttribute('link');
					$check->link = ($check->link != '' && preg_match('/http:/i', $check->link)) ? JURI::root() .$check->link : $check->link;
					
					if($check->type=='database') {
						$checkfunction = $child->getAttribute('check_function');
						$check_true = $child->getAttribute('check_true');
						$check_false = $child->getAttribute('check_false');
						$optimal_value = $child->getAttribute('optimal_value');
						$check->current = $db->name;
						$check->available = function_exists($checkfunction) ? $check_true : $check_false;
						$check->optimal = $optimal_value;
						if($check->available==$optimal_value && $check->available != $check->current) {
							$check->result = false;
							$check->resultText = JText::sprintf('JF_PERFORMANCE_NOT_OPTIMAL', $check->current, $check->optimal);
						} else {
							$check->result = true;
							$check->resultText = JText::sprintf('JF_PERFORMANCE_OPTIMAL', $check->current, $check->optimal);
						}
					} elseif ($check->type=='php') {
						$check->required = $child->getAttribute('required');
						$check->optimal = $child->getAttribute('optimal_value');
						$check->current = phpversion();
						if (version_compare($check->current,$check->required,"<")){
							$check->result = false;
							$check->resultText = JText::sprintf('JF_PERFORMANCE_LESS_REQUIRED', $check->current, $check->required);
						} elseif(version_compare($check->current,$check->required,">=") && version_compare($check->current,$check->optimal,"<")) {
							$check->result = true;
							$check->resultText = JText::sprintf('JF_PERFORMANCE_NOT_OPTIMAL', $check->current, $check->optimal);
						} else {
							$check->result = true;
							$check->resultText = JText::sprintf('JF_PERFORMANCE_OPTIMAL', $check->current, $check->optimal);
						}
					} elseif ($check->type=='config') {
						$check->value = $child->getAttribute('value');
						$check->optimal = $child->getAttribute('optimal_value');
						$jfm = FalangManager::getInstance();
						$check->current = $jfm->getCfg($check->value);
						if($check->current == $check->optimal) {
							$check->result = true;
							$check->resultText = JText::sprintf('JF_PERFORMANCE_CONFIG_OPTIMAL', JText::_($check->value), $check->current);
						} else {
							$check->result = false;
							$check->resultText = JText::sprintf('JF_PERFORMANCE_CONFIG_NOT_OPTIMAL', JText::_($check->value), $check->current, $check->optimal);
						}
					}
					
					
					$checkResult[$type][] = $check;
					$checkResult[$type. '_state'] = $checkResult[$type. '_state'] & $check->result;
					break;
			}
		}
		return $checkResult;
	}

	
	/**
	 * Testing if old installation is found and upgraded?
	 * This method is rebuild and checks now for old JoomFish installations not MambelFish anymore!
	 * @return int		0 := component not installed, 1 := installed but not upgraded, 2 := installed and upgraded
	 */
	private function _testOldInstall()
	{
		$db = JFactory::getDBO();
		$oldInstall = 0;

		$db->setQuery( "SHOW TABLES LIKE '%jf_%'" );
		$db->execute();
		$rows = $db->loadResultArray();
		foreach ($rows as $row) {
			if( preg_match( '/mbf_content/i', $row ) ) {
				$oldInstall = 1;
				break;
			}
		}

		$jfManager = FalangManager::getInstance();
		if( $oldInstall == 1 && $jfManager->getCfg( 'mbfupgradeDone' ) ) {
			$oldInstall = 2;
		}

		return $oldInstall;
	}

	/**
	 * This method gethers certain information of the system which can be used for presenting
	 * @return array with inforation about the system
	 */
	private function _getSystemInfo() {
		$db = JFactory::getDBO();

		$db->setQuery( 'SELECT count(DISTINCT reference_id, reference_table) FROM #__falang_content');
		$db->execute();
		$translations = $db->loadResult();

		$res = array( 'translations' => $translations );
		return $res;
	}

	/**
	 * Start of a function to obtain overview summary of orphan translations
	 *
	 * @return array of orphan tables or nothing if no orphans found
	 */
	private function _testOrphans( ) {
		global  $mainframe;

		$config	= JFactory::getConfig();
		$dbprefix = $config->get("dbprefix");
		$db = JFactory::getDBO();

		$orphans = array();
		$tranFilters=array();
		$filterHTML=array();

		$query = "select distinct CONCAT('".$dbprefix."',reference_table) from #__falang_content";
		$db->setQuery( $query );
		$tablesWithTranslations = $db->loadResultArray();

		$query = "SHOW TABLES";
		$db->setQuery( $query );
		$tables = $db->loadResultArray();

		$allContentElements = $this->_falangManager->getContentElements();
		foreach ($allContentElements as $catid=>$ce){
			$tablename = $dbprefix.$ce->referenceInformation["tablename"];
			if (in_array($tablename,$tables) &&
			in_array($tablename,$tablesWithTranslations)){
				$db->setQuery( $ce->createOrphanSQL( -1, null, -1, -1,$tranFilters ) );
				$rows = $db->loadObjectList();
				if ($db->getErrorNum()) {
					$this->_message = $db->stderr();
					return false;
				}

				$total = count($rows);
				if ($total>0) {
					$orphans[] = array( 'catid' => $catid, 'name' => $ce->Name, 'total' => $total);
				}
			}
		}

		foreach ($tablesWithTranslations as $twv) {
			if (!in_array($twv,$tables)) {
				$this->_message = "Translations exists for table <b>$twv</b> which is no longer in the database<br/>";
			}
		}
		return $orphans;
	}

	/**
	 * This method tests for the content elements and their original/translation status
	 * It will return an array listing all content element names including information about how may originals
	 *
	 * @param array 	$originalStatus	array with original state values if exist
	 * @param int		$phase	which phase of the status check
	 * @param string	$statecheck_i	running row number starting with -1!
	 * @param string	$message	system message
	 * @param array		$languages	array of availabe languages
	 * @return array	with resulting rows
	 */
	private function _testOriginalStatus($originalStatus, &$phase, &$statecheck_i, &$message, $languages) {
		$dbprefix = $config->get("dbprefix");
		$db = JFactory::getDBO();
		$tranFilters=array();
		$filterHTML=array();
		$sql = '';

		switch ($phase) {
			case 1:
				$originalStatus = array();

				$sql = "select distinct CONCAT('".$dbprefix."',reference_table) from #__falang_content";
				$db->setQuery( $sql );
				$tablesWithTranslations = $db->loadResultArray();

				$sql = "SHOW TABLES";
				$db->setQuery( $sql );
				$tables = $db->loadResultArray();

				$allContentElements = $this->_falangManager->getContentElements();

				foreach ($allContentElements as $catid=>$ce){
					$ceInfo = array();
					$ceInfo['name'] = $ce->Name;
					$ceInfo['catid'] = $catid;
					$ceInfo['total'] = '??';
					$ceInfo['missing_table'] = false;
					$ceInfo['message'] = '';

					$tablename = $dbprefix.$ce->referenceInformation["tablename"];
					if (in_array($tablename,$tables)){
						// get total count of table entries
						$db->setQuery( 'SELECT COUNT(*) FROM ' .$tablename );
						$ceInfo['total'] = $db->loadResult();

						if( in_array($tablename,$tablesWithTranslations) ) {
							// get orphans
							$db->setQuery( $ce->createOrphanSQL( -1, null, -1, -1,$tranFilters ) );
							$rows = $db->loadObjectList();
							if ($db->getErrorNum()) {
								$this->_message = $db->stderr();
								return false;
							}
							$ceInfo['orphans'] = count($rows);

							// get number of valid translations
							$ceInfo['valid'] = 0;


							// get number of outdated translations
							$ceInfo['outdated'] = $ceInfo['total'] - $ceInfo['orphans'] - $ceInfo['valid'];

						}else {
							$ceInfo['orphans'] = '0';
						}
					} elseif (!in_array($tablename, $tables)) {
						$ceInfo['missing_table'] = true;
						$ceInfo['message'] = JText::sprintf(TABLE_DOES_NOT_EXIST, $tablename );
					}
					$originalStatus[] = $ceInfo;
				}
				$message = JText::sprintf('ORIGINAL_PHASE1_CHECK', '');
				$phase ++;
				$statecheck_i = 0;
				break;

			case 2:
				if( is_array($originalStatus) && count ($originalStatus)>0 ) {
					if( $statecheck_i>=0 && $statecheck_i<count($originalStatus)) {
						$stateRow = $originalStatus[$statecheck_i];

						foreach ($languages as $lang) {
							$sql = "SELECT * FROM #__falang_content as jfc" .
							"\n  WHERE jfc.language_id=" .$lang->id .
							"\n    AND jfc.reference_table='" .$stateRow['catid'] ."'".
							"\n    AND jfc.published=1" .
							"\n	 GROUP BY reference_id";
							$db->setQuery($sql);
							$rows = $db->loadRowList();
							$key = 'langentry_' .$lang->getLanguageCode();
							$stateRow[$key] = count($rows);
						}
					}

					if ($statecheck_i<count($originalStatus)-1) {
						$statecheck_i ++;
						$message = JText::sprintf('ORIGINAL_PHASE1_CHECK', ' ('. $originalStatus[$statecheck_i]['name'] .')');
					} else {
						$message = JText::_('ORIGINAL_PHASE2_CHECK');
						$phase = 3;	// exit
					}
				} else {
					$phase = 3; // exit
					$message = JText::_('ORIGINAL_PHASE2_CHECK');
				}
				break;
		}

		return $originalStatus;
	}

	/**
	 * This method checks the translation status
	 * The process follows goes through out all existing translations and checks their individual status.
	 * The output is a summary information based grouped by content element files and the languages
	 *
	 * @param array 	$translationStatus	array with translation state values
	 * @param int		$phase	which phase of the status check
	 * @param string	$statecheck_i	running row number starting with -1!
	 * @param string	$message	system message
	 */
	private function _testTranslationStatus( $translationStatus, &$phase, &$statecheck_i, &$message ) {
		$db = JFactory::getDBO();

		$sql = '';

		switch ($phase) {
			case 1:
				$sql = "SELECT jfc.reference_table, jfc.language_id, jfl.name AS language" .
				"\n FROM #__falang_content AS jfc" .
				"\n JOIN #__languages AS jfl ON jfc.language_id = jfl.id" .
				"\n GROUP BY jfc.reference_table, jfc.language_id";
				$db->setQuery($sql);
				$rows = $db->loadObjectList();

				$translationStatus = array();
				if( is_array($rows) && count($rows)>0 ) {
					foreach ($rows as $row) {
						$status = array();
						$contentElement = $this->_falangManager->getContentElement( $row->reference_table );
						$status['content'] = $contentElement->Name;
						$status['catid'] = $row->reference_table;
						$status['language_id'] = $row->language_id;
						$status['language'] = $row->language;

						$status['total'] = '';
						$status['state_valid'] = '';
						$status['state_unvalid'] = '';
						$status['state_missing'] = '';
						$status['state'] = '';
						$status['published'] = '';

						$sql = "SELECT * FROM #__falang_content" .
						"\n WHERE reference_table='" .$row->reference_table. "'" .
						"\n   AND language_id=" .$row->language_id .
						"\n GROUP BY reference_id";
						$db->setQuery($sql);
						$totalrows = $db->loadRowList();
						if( $totalrows = $db->loadRowList() ) {
							$status['total'] = count($totalrows);
						}

						$translationStatus[] = $status;
					}

					$message = JText::_('TRANSLATION_PHASE1_GENERALCHECK');
					$phase ++;
				} else {
					$message = JText::_('No Translation available');
					$phase = 4;		// exit
				}
				break;

			case 2:
				if( is_array($translationStatus) && count ($translationStatus)>0 ) {

					for ($i=0; $i<count($translationStatus); $i++) {
						$stateRow = $translationStatus[$i];
						$sql = "select *" .
						"\n from #__falang_content as jfc" .
						"\n where published=1" .
						"\n and reference_table='" .$stateRow['catid']. "'".
						"\n and language_id=" .$stateRow['language_id'].
						"\n group by reference_ID";

						$db->setQuery($sql);
						if( $rows = $db->loadRowList() ) {
							$stateRow['published'] = count($rows);
						} else {
							$stateRow['published'] = 0;
						}
					}
				}

				$message = JText::sprintf('TRANSLATION_PHASE2_PUBLISHEDCHECK', '');
				$phase ++;
				break;

			case 3:
				if( is_array($translationStatus) && count ($translationStatus)>0 ) {
					if( $statecheck_i>=0 && $statecheck_i<count($translationStatus)) {
						$stateRow = $translationStatus[$statecheck_i];

						$contentElement = $this->_falangManager->getContentElement( $stateRow['catid'] );
						$filters = array();

						// we need to find an end, thats why the filter is at 10.000!
						$db->setQuery( $contentElement->createContentSQL( $stateRow['language_id'], null, 0, 10000,$filters ) );
						if( $rows = $db->loadObjectList() ) {
							$stateRow['state_valid'] = 0;
							$stateRow['state_unvalid'] = 0;
							$stateRow['state_missing'] = 0;

							for( $i=0; $i<count($rows); $i++ ) {
								$contentObject = new ContentObject( $stateRow['language_id'], $contentElement );
								$contentObject->readFromRow( $rows[$i] );
								$rows[$i] = $contentObject;

								switch( $contentObject->state ) {
									case 1:
										$stateRow['state_valid'] ++;
										break;
									case 0:
										$stateRow['state_unvalid'] ++;
										break;
									case -1:
									default:
										$stateRow['state_missing'] ++;
										break;
								}
							}
						}

					}

					if ($statecheck_i<count($translationStatus)-1) {
						$statecheck_i ++;
						$message = JText::sprintf('TRANSLATION_PHASE2_PUBLISHEDCHECK', ' ('. $translationStatus[$statecheck_i]['content'] .'/' .$translationStatus[$statecheck_i]['language'].')');
					} else {
						$message = JText::_('TRANSLATION_PHASE3_STATECHECK');
						$phase = 4;	// exit
					}

				} else {
					$message = JText::_('TRANSLATION_PHASE3_STATECHECK');
					$phase = 4; // exit
				}

				break;
		}


		return $translationStatus;
	}

	/**
	 * This method creates an overview of unpublished translations independed of the content element
	 * @return array 	of unpublished translations or null
	 */
	private function _testUnpublisedTranslations() {
		$db = JFactory::getDBO();
		$unpublishedTranslations = null;
                //sbou
		$sql = "select jfc.reference_table, jfc.reference_id, jfc.language_id, jfl.title as language" .
                //$sql = "select jfc.reference_table, jfc.reference_id, jfc.language_id, jfl.name as language" .
		"\n from #__falang_content as jfc, #__languages as jfl" .
		//"\n where published=0  and jfc.language_id = jfl.id" .
                "\n where jfl.published=0  and jfc.language_id = jfl.lang_id" .
		"\n group by jfc.reference_table, jfc.reference_id, jfc.language_id" .
		"\n limit 0, 50";
                //fin sbou
		$db->setQuery($sql);
		if( $rows = $db->loadObjectList() ) {
			foreach ($rows as $row) {
				$unpublished = array();
				$unpublished['reference_table'] = $row->reference_table;
				$unpublished['catid'] = $row->reference_table;
				$unpublished['reference_id'] = $row->reference_id;
				$unpublished['language_id'] = $row->language_id;
				$unpublished['language'] = $row->language;
				$unpublishedTranslations[] = $unpublished;
			}
		}
		return $unpublishedTranslations;
	}

	public function updateDownloadId(){

		// For joomla versions < 3.1 (no extra query available)
		if (version_compare(JVERSION, '3.1', 'lt')) {
			return;
		}

		$db = $this->getDbo();
		// Get current extension ID
		$extension_id = $this->getExtensionId();
		if (!$extension_id)
		{
			return;
		}

		$component = JComponentHelper::getComponent('com_falang');
		$dlid = $component->params->get('downloadid', '');

		if (empty($dlid)) return;

		// store only valid downloadid
		if (!preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid)) return;

		$extra_query = "'dlid=$dlid'";

		// Get the update sites for current extension
		$query = $db->getQuery(true)
			->select($db->qn('update_site_id'))
			->from($db->qn('#__update_sites_extensions'))
			->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));
		$db->setQuery($query);
		$updateSiteIDs = $db->loadColumn(0);

		// Loop through all update sites
		foreach ($updateSiteIDs as $id)
		{
			$query = $db->getQuery(true)
				->update('#__update_sites')
				->set('extra_query = '.$extra_query)
				->where('update_site_id = "'.$id.'"');
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Get extension Id
	 *
	 * @params void
	 *
	 * @return  extension id
	 *
	 * @since 1.1.7
	 *
	 */
	public function getExtensionId()
	{
		$db = $this->getDbo();
		$extensionType = 'package';
		$extensionElement = 'pkg_falang';
		// Get current extension ID
		$query = $db->getQuery(true)
			->select($db->qn('extension_id'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q($extensionType))
			->where($db->qn('element') . ' = ' . $db->q($extensionElement));
		$db->setQuery($query);
		$extension_id = $db->loadResult();
		if (empty($extension_id))
		{
			return 0;
		}
		else
		{
			return $extension_id;
		}
	}
}

