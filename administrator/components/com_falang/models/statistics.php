<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

require_once JPATH_ROOT.'/administrator/components/com_falang/models/JFModel.php';

jimport( 'joomla.application.component.model' );

/**
 * @package		Joom!Fish
 * @subpackage	Model.statistics
 */
class StatisticsModelStatistics extends JFModel
{
	var $_modelName = 'statistics';

	/**
	 * return the model name
	 */
	function getName() {
		return $this->_modelName;
	}
	
	/**
	 * This method checks the translation status
	 * The process follows goes through out all existing translations and checks their individual status.
	 * The output is a summary information based grouped by content element files and the languages
	 *
	 * @access protected
	 * @param array 	$translationStatus	array with translation state values
	 * @param int		$phase	which phase of the status check
	 * @param string	$statecheck_i	running row number starting with -1!
	 * @param string	$message	system message
	 */
	function testTranslationStatus( $translationStatus, &$phase, &$statecheck_i, &$message ) {
		$db = JFactory::getDBO();
		$jfManager = FalangManager::getInstance();

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
						$contentElement = $jfManager->getContentElement( $row->reference_table );
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

						$contentElement = $jfManager->getContentElement( $stateRow['catid'] );
						$filters = array();

						// trap missing content element files
						if (is_null($contentElement)){
							$message = JText::_('TRANSLATION_PHASE3_STATECHECK');
							$stateRow['state_valid'] = 0;
							$stateRow['state_unvalid'] = 0;
							$stateRow['state_missing'] = 0;
							$statecheck_i ++;
							break;
						}

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
	function testOriginalStatus($originalStatus, &$phase, &$statecheck_i, &$message, $languages) {
		$db = JFactory::getDBO();
		$jfManager = FalangManager::getInstance();
		$tranFilters=array();
		$filterHTML=array();
		$sql = '';

		switch ($phase) {
			case 1:
				$originalStatus = array();

				$sql = "select distinct CONCAT('".$db->getPrefix()."',reference_table) from #__falang_content";
				$db->setQuery( $sql );
				$tablesWithTranslations = $db->loadResultArray();

				$sql = "SHOW TABLES";
				$db->setQuery( $sql );
				$tables = $db->loadResultArray();

				$allContentElements = $jfManager->getContentElements();

				foreach ($allContentElements as $catid=>$ce){
					$ceInfo = array();
					$ceInfo['name'] = $ce->Name;
					$ceInfo['catid'] = $catid;
					$ceInfo['total'] = '??';
					$ceInfo['missing_table'] = false;
					$ceInfo['message'] = '';

					$tablename = $db->getPrefix().$ce->referenceInformation["tablename"];
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
}

