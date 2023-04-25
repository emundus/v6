<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Amamplace model.
 *
 * @since  1.6
 */
class ExportModelExport extends JModelForm
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_FALANG';


	/**
	 * returns all languages
	 */
	public function getSourceLanguages() {
		$sourcelanguage = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		return $sourcelanguage;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	string $type	The table type to instantiate
	 * @param	string $prefix	A prefix for the table class name. Optional.
	 * @param	array	$config Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'store', $prefix = 'falangTable', $config = array()) {
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm(
			'com_falang.export', 'export',	array('control' => 'jform',	'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	public function process()
	{
		$falangManager = FalangManager::getInstance();
		//need to reload content element due to quickjump side effect
		$contentElements = $falangManager->getContentElements(true);
		$db = JFactory::getDbo();

		// Prepare variables
		$jform = JFactory::getApplication()->input->get('jform', null, 'array');

		if (isset($jform['deduplicate'])) {
			$deduplicate = $jform['deduplicate'];
		} else {
			//$deduplicate = true;
			//use false in the first release due to alias.
			$deduplicate = false;
		}


		//only xliff 1.2 supported
		if (isset($jform['filetype'])) {
			$filetype = $jform['filetype'];
		} else {
			$filetype = 'xml';
		}

		//use tables name but actually export only 1 table only
		if (isset($jform['tables'])) {
			$exporttables = $jform['tables'];
		}

		if (isset($jform['sourcelanguage'])) {
			$sourcelanguage = $jform['sourcelanguage'];
		} else {
			$sourcelanguage = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		if (isset($jform['destinationlanguage'])) {
			$targetlanguage = $jform['destinationlanguage'];
		}

		//only xliff 1.2 supported
		if (isset($jform['state'])) {
			$state = $jform['state'];
		} else {
			$state = 'notexisting';
		}

		// Do we export the native Language?
		$native = ($sourcelanguage == JComponentHelper::getParams('com_languages')->get('site', 'en-GB'));

		$header = array();
		$data = array();
		JLoader::import( 'models.ContentObject',FALANG_ADMINPATH);
		$destinationlanguageId = $falangManager->getLanguageID($targetlanguage);

		//loop on each content element installd
		foreach($contentElements as $contentElement) {

			//check if it's the component we need to export

//			if (!is_null($exporttables) && !in_array($contentElement->getTableName(), $exporttables)) {
			if (!empty($exporttables) && $exporttables != $contentElement->getTableName()) {
				continue;
			}
			$contentTable = $contentElement->getTable();

			$query = $db->getQuery(true);
			$query->select('*')->from('#__'.$contentElement->getTableName(). ' as c');
			if ($contentTable->Filter != '') {
				$query->where($contentTable->Filter);
			}
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			$referenceId = $contentElement->getReferenceId();

			//on boucle sur les items sources

			foreach ($rows as $row){
				//check state of content element for this id.
				$actContentObject = new ContentObject( $destinationlanguageId, $contentElement );
				$actContentObject->loadFromContentID( $row->{$referenceId} );
				if ($state == 'notexisting' && $actContentObject->state > -1 ) {continue;}

				//on boucle sur les field de $contenttable en ne prenant que ceux que l'on traduit
				foreach( $contentTable->Fields as $tableField ) {
					if ($tableField->Type == 'referenceid'){$tmpdata['KEY'] = $row->{$tableField->Name};}
					if ($tableField->Translate != 1){continue;}
					if ($tableField->Type == 'readonlytext'){continue;}
					//first export version don't support param's and attribs
					if ($tableField->Type == 'params'){continue;}
					//on traduit les hiddentext de article uniquement
					if ($tableField->Type == 'hiddentext' && $contentTable->Name != 'content' ){continue;}

					if ($tableField->Name == 'alias'){continue;}//don't export alias auto generate it during import.

					//add field to tempdata array
					$tmpdata[$contentElement->getTableName().'.'.$tableField->Name] = $row->{$tableField->Name};
				}
				$tmpdata['SOURCEHASH'] = ($tmpdata['KEY'].'.'.$exporttables);
				$data[] =  $tmpdata;
			}
		}
		//$header not use yet , it's for params or attribs
		$this->exportXML($data, $header,$exporttables, $deduplicate, $sourcelanguage, $targetlanguage);

		JFactory::getApplication()->enqueueMessage(JText::_('COM_FALANG_EXPORT_SUCCESS'));

	}

	public function exportXLIFF12($data, $header,$exporttables, $deduplicate, $sourcelanguage, $targetlanguage) {

		$version = new FalangVersion();
		$sversion = $version->getVersionShort();

		$output = array();
		$output[] = "<xliff version='1.2' xmlns='urn:oasis:names:tc:xliff:document:1.2'>";
		$deoutput = array();

		$dedup = array();
		foreach ($data as $row) {
			$falangkey = $row['KEY'];
			$sourcehash = $row['SOURCEHASH'];
			$output[] = "<file original='".$sourcehash."' source-language='".$sourcelanguage."' target-language='".$targetlanguage."' datatype='htmlbody' product-name='falang' product-version='".$sversion."'>";
			$output[] = "<body>";
			foreach ($row as $key=>$value) {
				if ($key == 'KEY' || $key == 'SOURCEHASH') {
					continue;
				}
				//$header[$key] will be used next for params translation
				if (is_array($header[$key])) {
					$tmp = json_decode($value, true);
					foreach($header[$key] as $subkey=>$subvalue) {
						if (trim($tmp[$subkey]) == '') {
							continue;
						}
						if ($deduplicate && array_key_exists((string)$tmp[$subkey], $dedup)) {
							$deoutput[] = "<trans-unit id='".$falangkey.'.'.$key.'.'.$subkey."' translate='no' approved='yes' extradata='".$sourcehash."'>";
							$deoutput[] = "<source><![CDATA[".$dedup[(string)$tmp[$subkey]]."]]></source>";
							$deoutput[] = "<target state='translated' equiv-trans='yes' state-qualifier='id-match'>REF:".$dedup[(string)$tmp[$subkey]]."</target>";
							$deoutput[] = "</trans-unit>";
						} else {
							$dedup[(string)$tmp[$subkey]] = $falangkey.'.'.$key.'.'.$subkey;
							$output[] = "<trans-unit id='".$falangkey.'.'.$key.'.'.$subkey."'>";
							$output[] = "<source><![CDATA[".$tmp[$subkey]."]]></source>";
							$output[] = "<target state='needs-translation'><![CDATA[".$tmp[$subkey]."]]></target>";
							$output[] = "</trans-unit>";
						}
					}
				} else {
					if (trim($value) == '') {
						continue;
					}
					if ($deduplicate && array_key_exists((string)$value, $dedup)) {
						$deoutput[] = "<trans-unit id='".$falangkey.'.'.$key."' translate='no' approved='yes' extradata='".$sourcehash."'>";
						$deoutput[] = "<source><![CDATA[".$dedup[(string)$value]."]]></source>";
						$deoutput[] = "<target state='translated' equiv-trans='yes' state-qualifier='id-match'>REF:".$dedup[(string)$value]."</target>";
						$deoutput[] = "</trans-unit>";
					} else {
						$dedup[(string)$value] = $falangkey.'.'.$key;
						$output[] = "<trans-unit id='".$falangkey.'.'.$key."'>";
						$output[] = "<source><![CDATA[".$value."]]></source>";
						$output[] = "<target state='needs-translation'><![CDATA[".$value."]]></target>";
						$output[] = "</trans-unit>";
					}
				}
			}
			$output[] = "</body>";
			$output[] = "</file>";
		}

		if (!empty($deoutput)) {
			$output[] = "<file original='references' source-language='".$sourcelanguage."' target-language='".$targetlanguage."' datatype='htmlbody' product-name='falang' product-version='".$sversion."'>";
			$output[] = "<body>";
			$output = array_merge($output, $deoutput);
			$output[] = "</body>";
			$output[] = "</file>";
		}

		$output[] = "</xliff>";
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"falang-".$exporttables."-".$targetlanguage.".xlf\"");
		echo implode(chr(10),$output);
		Jexit();
	}

	public function exportXML($data, $header,$exporttables, $deduplicate, $sourcelanguage, $targetlanguage) {

		$version = new FalangVersion();
		$sversion = $version->getVersionShort();

        $falangManager = FalangManager::getInstance();
        $contentElement = $falangManager->getContentElement($exporttables);
        JLoader::import( 'models.ContentObject',FALANG_ADMINPATH);
        $targetlanguageId = $falangManager->getLanguageID($targetlanguage);

		$output = array();
		$output[] = "<xml version='1.2'>";
		$deoutput = array();

		$dedup = array();
		foreach ($data as $row) {
			$falangkey = $row['KEY'];
			$sourcehash = $row['SOURCEHASH'];

            $actContentObject = new ContentObject( $targetlanguageId, $contentElement );
            $actContentObject->loadFromContentID( $falangkey );
            $elementTable = $actContentObject->getTable();

			$output[] = "<file original='".$sourcehash."' source-language='".$sourcelanguage."' target-language='".$targetlanguage."' datatype='htmlbody' product-name='falang' product-version='".$sversion."'>";
			$output[] = "<body>";
			foreach ($row as $key=>$value) {
				if ($key == 'KEY' || $key == 'SOURCEHASH') {
					continue;
				}
				//$header[$key] will be used next for params translation
				if (trim($value) == '') {
					continue;
				}
				//get transalted value
                $translated_value = $value;
                if ($actContentObject->state >= 0) {
                    $field = str_replace($exporttables . '.', '', $key);
                    foreach ($elementTable->Fields as $elt ){
                        if ($elt->Name == $field){
                            $translated_value = $elt->translationContent->value;
                            break;//exit for each
                        }
                    }
                }

				$dedup[(string)$value] = $falangkey.'.'.$key;
				$output[] = "<trans-unit id='".$falangkey.'.'.$key."'>";
				$output[] = "<source><![CDATA[".$value."]]></source>";
				$output[] = "<target><![CDATA[".$translated_value."]]></target>";
				$output[] = "</trans-unit>";

			}
			$output[] = "</body>";
			$output[] = "</file>";
		}

		$output[] = "</xml>";
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"falang-".$exporttables."-".$targetlanguage.".xml\"");
		echo implode(chr(10),$output);
		Jexit();
	}

}
