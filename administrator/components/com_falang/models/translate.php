<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2011, Think Network GmbH, Munich
 *
 * All rights reserved.  The Joom!Fish project is a set of extentions for
 * the content management system Joomla!. It enables Joomla!
 * to manage multi lingual sites especially in all dynamic information
 * which are stored in the database.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * $Id: translate.php 1551 2011-03-24 13:03:07Z akede $
 * @package joomfish
 * @subpackage Models
 *
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

JLoader::register('JFModel', FALANG_ADMINPATH .DS. 'models' .DS. 'JFModel.php' );

/**
 * This is the corresponding module for translation management
 * @package		Joom!Fish
 * @subpackage	Translate
 */
class TranslateModelTranslate extends JFModel
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
		$db = JFactory::getDBO();
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

			$sql= "DELETE from #__falang_content WHERE reference_table='$catid' and language_id=$language_id and reference_id=$contentid";
			$db->setQuery($sql);
			$db->query();
			if( $db->getErrorNum() != 0 ) {
				$this->setError(JText::_('Something dodgy going on here'));
				JError::raiseWarning( 400,JTEXT::_('No valid table information: ') .$db->getErrorMsg());
				continue;
			} else {
				$this->setState('message', JText::_('Translation successfully deleted'));
			}
		}
		return $message;
	}

}

