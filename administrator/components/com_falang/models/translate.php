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

/**
 * This is the corresponding module for translation management
 * @package		Falang
 * @subpackage	Translate
 */
class FalangModelTranslate extends JFModel
{
	var $_modelName = 'translate';

	/**
	 * return the model name
	 */
	function getName() {
		return $this->_modelName;
	}

	/**
	 * Method to prepare the language list for the translation backend
	 * The method defines that all languages are being presented except the default language
	 * if defined in the config.
	 * @return array of languages
	 */
	function getLanguages() {
		$jfManager = FalangManager::getInstance();
		return $jfManager->getLanguages(false);
	}


    protected function populateState($ordering = null, $direction = null)
    {

        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        // Populate data used by controller
        $app	= JFactory::getApplication();
        $catid = $app->getUserStateFromRequest('selected_catid', 'catid', '');

        //get Translation filter from content element
        if (!empty($catid) ) {
            $falangManager = FalangManager::getInstance();
            $contentElement = $falangManager->getContentElement( $catid );
            if (!$contentElement){
                $catid = "content";
                $contentElement = $falangManager->getContentElement( $catid );
            }

            JLoader::import('models.TranslationFilter',FALANG_ADMINPATH);
            $tranFilters = getTranslationFilters($catid,$contentElement);
            foreach ($tranFilters as $tranFilter){
                $filter = $this->getUserStateFromRequest('filter.'.$tranFilter->filterType, $tranFilter->filterType.'_filter_value',$tranFilter->filterNullValue);
                $this->setState('filter.'.$tranFilter->filterType, $filter);
            }
        }

    }
	/**
	 * Deletes the selected translations (only the translations of course)
	 * @return string	message
	 */
	function _removeTranslation( $catid, $cid ) {
		$message = '';
		$db = Factory::getDbo();
		foreach( $cid as $cid_row ) {
			list($translationid, $contentid, $language_id) = explode('|', $cid_row);

			$jfManager = FalangManager::getInstance();
			$contentElement = $jfManager->getContentElement( $catid );
			$contentTable = $contentElement->getTableName();
			$contentid= intval($contentid);
			$translationid = intval($translationid);

			// safety check -- complete overkill but better to be safe than sorry

			// get the translation details
			JLoader::import( 'models.FalangContent',FALANG_ADMINPATH);
			$translation = new FalangContent($db);
			$translation->load($translationid);

			if (!isset($translation) || $translation->id == 0)		{
				$this->setState('message', JText::sprintf('NO_SUCH_TRANSLATION', $translationid));
				continue;
			}

			// make sure translation matches the one we wanted
			if ($contentid != $translation->reference_id){
				$this->setState('message', JText::_('Something dodgy going on here'));
				continue;
			}

			//sbou4
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__falang_content'))
				->where('reference_table = '.$db->q($catid))
				->where('language_id = '.$db->q($language_id))
				->where('reference_id = '.$db->q($contentid));
			//$sql= "DELETE from #__falang_content WHERE reference_table='$catid' and language_id=$language_id and reference_id=$contentid";
			$db->setQuery($query);
			try {
				$db->execute();
				$this->setState('message', JText::_('COM_FALANG_TRANSLATION_DELETED'));
			} catch (Exception $e){
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
//				$this->setError(JText::_('Something dodgy going on here'));
//				JError::raiseWarning( 400,JTEXT::_('No valid table information: ') .$db->getErrorMsg());
				continue;
			}

		}
		return $message;
	}


}

