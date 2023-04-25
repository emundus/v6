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
class ImportModelImport extends JModelForm
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_FALANG';


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
			'com_falang.import', 'import',	array('control' => 'jform',	'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

    public function process()
    {
        // Prepare variables
        $input = JFactory::getApplication()->input;
        $data = $input->get('jform', null, 'array');
        $targetlanguage = $data['destinationlanguage'];



        $sourcehash = array();
        $stats_items_stored = 0;
        $stats_items_skipped = 0;

        $dedup = array();

        $files = new JInput($_FILES, array());
        $file = $files->get('jform', null, 'array');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $inputfile = $file['tmp_name']['translationFile'];
        jimport('joomla.filesystem.file');

        $filetype = JFile::getExt($file['name']['translationFile']);
        switch (strtoupper($filetype)) {
            case 'XML':
                $translation = $this->importXML($inputfile, $dedup);
                break;
            case 'XLF':
            case 'XLIFF':
                $translation = $this->importXLIFF($inputfile, $dedup);
                break;
            default:
                throw new runtimeException('Format not supported');
        }

        $falangManager = FalangManager::getInstance();
        $targetlanguageId = $falangManager->getLanguageID($targetlanguage);
        $published = 1;//mark field as published

        $user = JFactory::getUser();



        //$translation have the right array to import
        foreach ($translation as $contentArray){

            $key = key($contentArray);
            $pieces = explode('.',$key);
            $reference_id = $pieces[0];
            $table = $pieces[1];
            $contentElement = $falangManager->getContentElement($table);
            $objs = array_shift($contentArray);
            JLoader::import( 'models.ContentObject',FALANG_ADMINPATH);
            $actContentObject = new ContentObject( $targetlanguageId, $contentElement );
            $actContentObject->loadFromContentID( $reference_id );

            $elementTable = $actContentObject->_contentElement->getTable();

            for( $i=0; $i<count($elementTable->Fields); $i++ )
            {
                $field     = $elementTable->Fields[$i];
                $fieldName = $field->Name;
                if ($field->Translate)
                {
                    if (isset($objs[$key . "." . $fieldName]))
                    {

                        $translationValue = $objs[$key . "." . $fieldName];

                        $fieldContent                  = new falangContent($db);
                        $fieldContent->reference_id    = $reference_id;
                        $fieldContent->language_id     = $targetlanguageId;
                        $fieldContent->reference_table = $table;
                        $fieldContent->reference_field = $db->escape($fieldName);
                        $fieldContent->value           = $translationValue;
                        // original value will be already md5 encoded - based on that any encoding isn't needed!
                        //$fieldContent->original_value = $originalValue;
                        //$fieldContent->original_text = !is_null($originalText)?$originalText:"";

                        $fieldContent->modified = JFactory::getDate()->toSql();

                        $fieldContent->modified_by = $user->id;
                        $fieldContent->published   = $published;

                        //utilisation du bind de
                        $field->translationContent = $fieldContent;

                    }
                    else
                    {
                        //non existing field in translation file
                        $translationValue = $field->originalValue;

                        $fieldContent                  = new falangContent($db);
                        $fieldContent->reference_id    = $reference_id;
                        $fieldContent->language_id     = $targetlanguageId;
                        $fieldContent->reference_table = $table;
                        $fieldContent->reference_field = $db->escape($fieldName);
                        $fieldContent->value           = $translationValue;
                        // original value will be already md5 encoded - based on that any encoding isn't needed!
                        //$fieldContent->original_value = $originalValue;
                        //$fieldContent->original_text = !is_null($originalText)?$originalText:"";

                        $fieldContent->modified = JFactory::getDate()->toSql();

                        $fieldContent->modified_by = $user->id;
                        $fieldContent->published   = $published;

                        //utilisation du bind de
                        $field->translationContent = $fieldContent;
                    }
                }//if translate
            }

            if ($actContentObject->state == -1) {
                $actContentObject->modified =  JFactory::getDate()->toSql();
                $actContentObject->store();
                $stats_items_stored++;
            } else {

                $stats_items_skipped++;
            }

        }

        JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_FALANG_IMPORT_SUCCESS', $stats_items_stored,$stats_items_skipped));

    }


	protected function importXLIFF($inputfile, &$dedup) {
		$translation = array();

		// Read file and detect and remove bom
		$content = file_get_contents($inputfile);
		if (strncmp($content, "\xef\xbb\xbf", 3) == 0) {
			$content = substr($content, 3);
		}

		// Read XLIFF File
		$xml = simplexml_load_string( str_replace('xmlns=', 'ns=', $content));
		if ($xml !== FALSE) {
			$items = $xml->xpath('//trans-unit/target[contains("final signed-off translated", @state)]/..');
			foreach($items as $item) {
				$attribs = $item->attributes();
				$dedup[(string)$attribs['id']] = (string)$item[0]->target;;

				$path = explode('.', (string)$attribs['id']);
				$id = array_shift($path);

				$root = array();
				$target = & $root;
				$i = 0;
				foreach($path as $obj) {
					$i++;
					$target[$obj] = array();
					$target = & $target[$obj];
					if ($i == 3) {
						if ((string)$attribs['extradata'] != '') {
							$target['SOURCEHASH'] = array($id=>(string)$attribs['extradata']);
						} else {
							$file = $item[0]->xpath('../..');
							$fileattribs = $file[0]->attributes();
							$target['SOURCEHASH'] = array($id=>(string)$fileattribs['original']);
						}
					}
				}

				$target[$id] = (string)$item[0]->target;
				var_dump($root);
				$translation = array_merge_recursive($translation,$root);
				//$translation = jDictionTranslationHelper::array_merge_recursive($translation, $root);
			}
		}

		return $translation;
	}


	protected function importXML($inputfile, &$dedup) {
		$translation = array();

		// Read file and detect and remove bom
		$content = file_get_contents($inputfile);
		if (strncmp($content, "\xef\xbb\xbf", 3) == 0) {
			$content = substr($content, 3);
		}

		// Read XLIFF File
		$xml = simplexml_load_string( str_replace('xmlns=', 'ns=', $content));
		if ($xml !== FALSE) {
			$items = $xml->xpath('//file[@original]');
			foreach($items as $item) {
				$root = array();
				$element = array();
				$attribs = $item->attributes();
				$targets = $item->body->xpath('trans-unit/target/..');
				foreach ($targets as $target) {
					$eattrib = $target->attributes();
					$element[(string)$eattrib['id']] = (string)$target[0]->target;
				}

				$root[(string)$attribs['original']] = $element;

//				$dedup[(string)$attribs['id']] = (string)$item[0]->target;;


				$translation[] = $root;
			}
		}

		return $translation;
	}

}
