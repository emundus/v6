<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

require_once JPATH_ROOT.'/administrator/components/com_falang/models/JFModel.php';

jimport( 'joomla.application.component.model' );

/**
 * @package		Falang
 * @subpackage	Model.manage
 */
class ManageModelManage extends JFModel
{
	var $_modelName = 'manage';

	/**
	 * return the model name
	 */
	function getName() {
		return $this->_modelName;
	}
	
	/**
	 * returns the list of available languages
	 */
	function getLanguageList() {
		$jfManager = FalangManager::getInstance();
		$languages = $jfManager->getLanguages( false );		// all languages even non active once
		$defaultLang = $this->get('DefaultLanguage');
		$params = JComponentHelper::getParams( 'com_falang' );
		$showDefaultLanguageAdmin = $params->get("showDefaultLanguageAdmin", false);
		$langOptions = array();
		$langOptions[] = array('value' => -1, 'text' => JText::_('Do not copy') );

		if ( count($languages)>0 ) {
			foreach( $languages as $language )
			{
				if($language->code != $defaultLang || $showDefaultLanguageAdmin) {
					$langOptions[] = array('value' => $language->id, 'text' => $language->name );
				}
			}
		}
		return $langOptions;
	}

	/**
	 * This method copies originals content items to one selected language
	 *
	 * @param unknown_type $original2languageInfo
	 * @param unknown_type $phase
	 * @param unknown_type $statecheck_i
	 * @param unknown_type $message
	 * @return array	Information result array
	 */
	function copyOriginalToLanguage($original2languageInfo, &$phase, &$state_catid, $language_id, $overwrite, &$message) {
		$db = JFactory::getDBO();
		$jfManager = FalangManager::getInstance();
		$sql = '';

		switch ($phase) {
			case 1:
				$original2languageInfo = array();

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
					$ceInfo['existing'] = '??';
					$ceInfo['processed'] = '0';
					$ceInfo['copied'] = '0';
					$ceInfo['copy'] = false;

					$contentTable = $ce->getTable();
					$tablename = $db->getPrefix() . $contentTable->Name;
					if (in_array($tablename,$tables)){
						// get total count of table entries
						$sql = 'SELECT COUNT(*) FROM ' .$tablename. ' AS c';
						if( $contentTable->Filter != ''){
							$sql .= ' WHERE ' .$contentTable->Filter;
						}

						$db->setQuery( $sql );
						$ceInfo['total'] = $db->loadResult();
					}
					$original2languageInfo[$catid] = $ceInfo;
				}
				$phase = 1;		// stays with 1 as the second phase needs the bottom to be clicked
				$message = JText::_('COPY2LANGUAGE_INFO');
				break;

			case 2:
				if( $state_catid != '' ) {
					// removing all content information which are not to be copied!
					$celements = explode(',', $state_catid);
					if( count($celements) < count($original2languageInfo)) {
						$shortList = array();
						foreach ($celements as $element) {
							$shortList[$element] = $original2languageInfo[$element];
						}
						$original2languageInfo = $shortList;
					}
				}
				$phase = 3;

			case 3:
				if( $state_catid != '' ) {
					$celements = explode(',', $state_catid);
					// copy the information per content element file, starting with the first in the list
					$catid = array_shift($celements);
					$catidCompleted = false;

					// coyping the information from the selected content element
					if($catid!='' && $language_id!=0) {
						// get's the config settings on how to store original files
						$storeOriginalText = ($jfManager->getCfg('storageOfOriginal') == 'md5') ? false : true;

						// make sure we are only transfering data within parts (max 100 items at a time)
						$ceInfo =& $original2languageInfo[$catid];
						if(intval($ceInfo['processed']) < intval($ceInfo['total'])) {
							$contentElement = $jfManager->getContentElement( $catid );
							$db->setQuery( $contentElement->createContentSQL( $language_id, null, $ceInfo['processed'], 10,array() ) );

							$rows = $db->loadObjectList();
							if ($db->getErrorNum()) {
                                Factory::getApplication()->enqueueMessage(JTEXT::_('Invalid Content SQL : ') .$db->getErrorMsg(), 'error');
								//JError::raiseError( 500,JTEXT::_('Invalid Content SQL : ') .$db->getErrorMsg());
								return false;
							} else {
								for( $i=0; $i<count($rows); $i++ ) {
									$contentObject = new ContentObject( $language_id, $contentElement );
									$contentObject->readFromRow($rows[$i]);
									if( $overwrite || $contentObject->translation_id == 0) {
										$contentObject->copyContentToTranslation( $rows[$i], $rows[$i] );
										$contentObject->store();
										$ceInfo['copied'] += 1;
									}
									$rows[$i] = $contentObject;
								}
								$ceInfo['processed'] += $i;
								if($ceInfo['processed'] >= $ceInfo['total']) {
									$catidCompleted = true;
								}
							}
						}
					}
					if( $catidCompleted ) {
						if(count($celements)>0) {
							$state_catid = implode(',', $celements);
						} else {
							$state_catid = '';
						}
					}
				}

				$message = JText::_('COPY2LANGUAGE_PROCESS');
				if( $state_catid == '') {
					$phase = 4;		// Successfully finished phase 3
					$message = JText::_('COPY2LANGUAGE_COMPLETED');
				}
				break;
		}

		return $original2languageInfo;
	}
}

