<?php
/**
 * Messages model used for the new message dialog.
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
use Joomla\CMS\Date\Date;

class EmundusModelsettings extends JModelList {

    /**
     * Get all colors available for status and tags
     *
     * @return string[]
     *
     * @since 1.0
     */
    function getColorClasses(){
        return array(
            'lightpurple' => '#FBE8FF',
            'purple' => '#EBE9FE',
            'darkpurple' => '#663399',
            'lightblue' => '#E0F2FE',
            'blue' => '#D1E9FF',
            'darkblue' => '#D1E0FF',
            'lightgreen' => '#CCFBEF',
            'green' => '#C4F0E1',
            'darkgreen' => '#BEDBD0',
            'lightyellow' => '#FFFD7E',
            'yellow' => '#FDF7C3',
            'darkyellow' => '#FEF0C7',
            'lightorange' => '#FFEDCF',
            'orange' => '#FCEAD7',
            'darkorange' => '#FFE5D5',
            'lightred' => '#EC644B',
            'red' => '#FEE4E2',
            'darkred' => '#FEE4E2',
            'lightpink' => '#ffeaea',
            'pink' => '#FCE7F6',
            'darkpink' => '#FFE4E8',
            'default' => '#EBECF0',
        );
    }

    /**
     * A helper function that replace spaces and special characters
     *
     * @param $string
     * @return array|string|string[]|null
     *
     * @since 1.12.0
     */
    function clean($string) {
        $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    /**
     * Get all status available and check if files is associated
     *
     * @return array|false|mixed
     *
     * @since 1.0
     */
    function getStatus() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'falang.php');
        $falang = new EmundusModelFalang;

        $query->select('*')
            ->from ($db->quoteName('#__emundus_setup_status'))
            ->order('ordering ASC');

        try {
            $db->setQuery($query);
            $status = $db->loadObjectList();
            foreach ($status as $statu){
                $statu->label = new stdClass;

                $statu->label = $falang->getFalang($statu->step,'emundus_setup_status','value',$statu->value);

                $statu->edit = 1;
                $query->clear()
                    ->select('count(id)')
                    ->from($db->quoteName('#__emundus_campaign_candidature'))
                    ->where($db->quoteName('status') . ' = ' . $db->quote($statu->step));
                $db->setQuery($query);
                $files = $db->loadResult();

                if($files > 0){
                    $statu->edit = 0;
                }
            }

            return $status;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/settings | Error at getting status : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Get all emundus tags available
     *
     * @return array|false|mixed
     *
     * @since 1.0
     */
    function getTags() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__emundus_setup_action_tag'))
            ->order($db->quoteName('label'));

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/settings | Error at getting action tags : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Delete a tag, foreign key delete also all files associated to this tag
     *
     * @param $id
     * @return false|mixed
     *
     * @since 1.0
     */
    function deleteTag($id) {
		$deleted = false;

		if (!empty($id)) {
			$db = $this->getDbo();
			$query = $db->getQuery(true);

			$query->delete($db->quoteName('#__emundus_setup_action_tag'))
				->where($db->quoteName('id') . ' = ' . $id);

			try {
				$db->setQuery($query);
				$deleted=  $db->execute();
			} catch(Exception $e) {
				JLog::add('component/com_emundus/models/settings | Cannot delete the tag ' . $id . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
				$deleted = false;
			}
		}

		return $deleted;
    }

    /**
     * Create a emundus tag with a default label and color
     *
     * @return false|mixed|null
     *
     * @since 1.0
     */
    function createTag() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->insert('#__emundus_setup_action_tag')
            ->set($db->quoteName('label') . ' = ' . $db->quote('Nouvelle étiquette'))
            ->set($db->quoteName('class') . ' = ' . $db->quote('label-default'));

        try {
            $db->setQuery($query);
            $db->execute();
            $newtagid = $db->insertid();

            $query->clear()
                ->select('*')
                ->from ($db->quoteName('#__emundus_setup_action_tag'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($newtagid));

            $db->setQuery($query);
            return $db->loadObject();

        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/settings | Cannot create a tag : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Create a new status
     *
     * @return false|mixed|null
     *
     * @since 1.0
     */
    function createStatus() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'falang.php');
        $falang = new EmundusModelFalang;

        $query->select('MAX(step)')
            ->from($db->quoteName('#__emundus_setup_status'));
        $db->setQuery($query);
        $newstep = $db->loadResult() + 1;

        $query->clear()
            ->select('MAX(ordering)')
            ->from($db->quoteName('#__emundus_setup_status'));
        $db->setQuery($query);
        $newordering = $db->loadResult() + 1;

        $query->clear()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__emundus_setup_status'))
            ->where($db->quoteName('value') . ' LIKE ' . $db->quote('Nouveau statut%'));
        $db->setQuery($query);
        $existing = $db->loadResult();
        if($existing > 0) {
            $increment = $existing + 1;
        } else {
            $increment = '';
        }

        $query->clear()
            ->insert('#__emundus_setup_status')
            ->set($db->quoteName('value') . ' = ' . $db->quote('Nouveau statut ' . $increment))
            ->set($db->quoteName('step') . ' = ' . $db->quote($newstep))
            ->set($db->quoteName('ordering') . ' = ' . $db->quote($newordering))
            ->set($db->quoteName('class') . ' = ' . $db->quote('default'));

        try {
            $db->setQuery($query);
            $db->execute();
            $newstatusid = $db->insertid();

            $query->clear()
                ->select('*')
                ->from ($db->quoteName('#__emundus_setup_status'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($newstatusid));

            $db->setQuery($query);
            $status = $db->loadObject();

            $status->label = new stdClass;
            $status->label->fr = 'Nouveau statut';
            $status->label->en = 'New status';
            $status->edit = 1;

            $falang->insertFalang($status->label, $newstep, 'emundus_setup_status', 'value');

            return $status;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/settings | Cannot create a status : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Update a status (label and colors)
     *
     * @param $status
     * @return array|false
     *
     * @since 1.0
     */
    function updateStatus($status,$label,$color) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'translations.php');
        $m_translations = new EmundusModelTranslations;

        $lang_to = $m_translations->getDefaultLanguage()->lang_code;

        $classes = $this->getColorClasses();
        $results = [];

        try {
            $class = array_search($color, $classes);

            $query->clear()
                ->update('#__falang_content')
                ->set($db->quoteName('value') . ' = ' . $db->quote($class))
                ->where(array(
                    $db->quoteName('reference_id') . ' = ' . $db->quote($status),
                    $db->quoteName('reference_table') . ' = ' . $db->quote('emundus_setup_status'),
                    $db->quoteName('reference_field') . ' = ' . $db->quote('class'),
                    $db->quoteName('language_id') . ' = 2'
                ));
            $db->setQuery($query);
            $db->execute();

            $results[] = $m_translations->updateFalangTranslation($label, $lang_to,'emundus_setup_status',$status,'value');

            $query->clear()
                ->update('#__emundus_setup_status')
                ->set($db->quoteName('value') . ' = ' . $db->quote($label))
                ->set($db->quoteName('class') . ' = ' . $db->quote($class))
                ->where($db->quoteName('step') . ' = ' . $db->quote($status));
            $db->setQuery($query);
            $db->execute();

            return $results;
        } catch (Exception $e){
            JLog::add('component/com_emundus/models/settings | Cannot update status : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function updateStatusOrder($status){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            foreach ($status as $order => $statu) {
                $query->clear()
                    ->update('#__emundus_setup_status')
                    ->set($db->quoteName('ordering') . ' = ' . $db->quote($order))
                    ->where($db->quoteName('step') . ' = ' . $db->quote($statu));
                $db->setQuery($query);
                $db->execute();
            }

            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/settings | Cannot update status order : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Delete a status that is not associated to files
     *
     * @param $id
     * @param $step
     * @return false|mixed
     *
     * @since 1.0
     */
    function deleteStatus($id,$step) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->delete($db->quoteName('#__falang_content'))
            ->where($db->quoteName('reference_id') . ' = ' . $db->quote($step))
            ->andWhere($db->quoteName('reference_table') . ' = ' . $db->quote('emundus_setup_status'));
        try {
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->delete($db->quoteName('#__emundus_setup_status'))
                ->where($db->quoteName('id') . ' = ' . $id);

            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/settings | Cannot delete the status ' . $id . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Update emundus tags (label and colors)
     *
     * @param $tags
     * @return array|false
     *
     * @since 1.0
     */
    function updateTags($tag, $label, $color) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $classes = $this->getColorClasses();

        try {
            $class = array_search($color, $classes);
			$class = !empty($class) ? $class : 'default';

            $query->clear()
                ->update('#__emundus_setup_action_tag')
                ->set($db->quoteName('label') . ' = ' . $db->quote($label))
                ->set($db->quoteName('class') . ' = ' . $db->quote('label-' . $class))
                ->where($db->quoteName('id') . ' = ' . $db->quote($tag));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e){
            JLog::add('component/com_emundus/models/settings | Cannot update tags : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Get footer articles from the module mod_emundus_footer
     *
     * @return false|stdClass
     *
     * @since 1.28.0
     */
    function getFooterArticles() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $footers = new stdClass();

        $query->select('id as id, params as params')
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('position') . ' LIKE ' . $db->quote('footer-a'))
            ->andWhere($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emundus_footer'));

        try {
            $db->setQuery($query);
            $params = $db->loadObject();

            if (!empty($params)) {
                $params = json_decode($params->params);

                $footers->column1 = $params->mod_emundus_footer_texte_col_1 !== 'null' ? $params->mod_emundus_footer_texte_col_1 : '';
                $footers->column2 = $params->mod_emundus_footer_texte_col_2 !== 'null' ? $params->mod_emundus_footer_texte_col_2 : '';
                return $footers;
            } else {
                return $this->getOldFooterArticles();
            }
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/settings | Error at getting footer articles : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Deprecated footer handling
     * Get footer content from custom module in footer-a position
     *
     * @since 1.0
     */
    private function getOldFooterArticles() {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $footers = new stdClass();
        $query->select('id as id,content as content')
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('position') . ' LIKE ' . $db->quote('footer-a'));

        try {
            $db->setQuery($query);
            $footers->column1 = $db->loadObject()->content;

            $query->clear()
                ->select('id as id,content as content')
                ->from($db->quoteName('#__modules'))
                ->where($db->quoteName('position') . ' LIKE ' . $db->quote('footer-b'));

            $db->setQuery($query);
            $footers->column2 = $db->loadObject()->content;

            return $footers;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/settings | Error at getting footer articles : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Get a Joomla article
     *
     * @param $lang_code
     * @param $article_id
     * @param $article_alias
     * @param $reference_field
     * @return false|mixed|null
     *
     * @since 1.29.0
     */
    function getArticle($lang_code,$article_id = 0,$article_alias = '',$reference_field = 'introtext'){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('lang_id')
            ->from($db->quoteName('#__languages'))
            ->where($db->quoteName('lang_code') . ' = ' . $db->quote($lang_code));
        $db->setQuery($query);
        $lang_id = $db->loadResult();

        $query->clear()
            ->select('*')
            ->from($db->quoteName('#__content'));

        if(!empty($article_id)) {
            $query->where($db->quoteName('id') . ' = ' . $article_id);
        } else {
            $query->where($db->quoteName('alias') . ' = ' . $db->quote($article_alias));
        }

        try {
            $db->setQuery($query);
            $article = $db->loadObject();

            $query->clear()
                ->select('value')
                ->from($db->quoteName('#__falang_content'))
                ->where(array(
                    $db->quoteName('reference_id') . ' = ' . $article->id,
                    $db->quoteName('reference_table') . ' = ' . $db->quote('content'),
                    $db->quoteName('reference_field') . ' = ' . $db->quote($reference_field),
                    $db->quoteName('language_id') . ' = ' . $db->quote($lang_id),
                    $db->quoteName('published') . ' = ' . $db->quote(1)
                ));
            $db->setQuery($query);
            $result = $db->loadResult();

            if (!empty($result)){
                $article->{$reference_field} = $result;
            } else {
	            $currentLang = JFactory::getLanguage();
				if ($currentLang->lang_code != $lang_code) {
					$query->clear()
						->select('title, introtext, alias')
						->from($db->quoteName('#__content'))
						->where('id = ' . $article->id);

					$db->setQuery($query);
					$article_content = $db->loadAssoc();

					foreach ($article_content as $key => $content) {
						$article->{$key} = $content;
					}
				}
            }

            return $article;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/settings | Cannot get article ' . $article_id . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Update a Joomla article
     *
     * @param $content
     * @param $lang_code
     * @param $article_id
     * @param $article_alias
     * @param $reference_field
     * @return false|mixed
     *
     * @since 1.29.0
     */
    function updateArticle($content,$lang_code,$article_id = 0,$article_alias = '',$reference_field = 'introtext') {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try{
            $query->select('lang_id')
                ->from($db->quoteName('#__languages'))
                ->where($db->quoteName('lang_code') . ' = ' . $db->quote($lang_code));
            $db->setQuery($query);
            $lang_id = $db->loadResult();

            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__content'));

            if (!empty($article_id)) {
                $query->where($db->quoteName('id') . ' = ' . $article_id);
            } else {
                $query->where($db->quoteName('alias') . ' = ' . $db->quote($article_alias));
            }
            $db->setQuery($query);
            $article = $db->loadObject();

            // Update content
            $query->clear()
                ->select('value')
                ->from($db->quoteName('#__falang_content'))
                ->where(array(
                    $db->quoteName('reference_id') . ' = ' . $article->id,
                    $db->quoteName('reference_table') . ' = ' . $db->quote('content'),
                    $db->quoteName('reference_field') . ' = ' . $db->quote($reference_field),
                    $db->quoteName('language_id') . ' = ' . $db->quote($lang_id),
                    $db->quoteName('published') . ' = ' . $db->quote(1)
                ));
            $db->setQuery($query);
            $falang_result = $db->loadResult();

            if(empty($falang_result)) {
                $query->clear()
                    ->update($db->quoteName('#__content'))
                    ->set($db->quoteName('introtext') . ' = ' . $db->quote($content))
                    ->where($db->quoteName('id') . ' = ' . $article->id);
                $db->setQuery($query);
                return $db->execute();
            } else {
                $query->clear()
                    ->update('#__falang_content')
                    ->set($db->quoteName('value') . ' = ' . $db->quote($content))
                    ->where(array(
                        $db->quoteName('reference_id') . ' = ' . $article->id,
                        $db->quoteName('reference_table') . ' = ' . $db->quote('content'),
                        $db->quoteName('reference_field') . ' = ' . $db->quote($reference_field),
                        $db->quoteName('language_id') . ' = ' . $db->quote($lang_id)
                    ));
                $db->setQuery($query);
                return $db->execute();
            }
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/settings | Error at updating article ' . $article_id . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Update the emundus footer module with 2 columns
     *
     * @param $col1
     * @param $col2
     * @return bool|mixed
     *
     * @since 1.28.0
     */
    function updateFooter($col1,$col2) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('params')
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emundus_footer'));

        $db->setQuery($query);
        $params = $db->loadResult();

        if (!empty($params)) {
            $params = json_decode($params);

            $params->mod_emundus_footer_texte_col_1 = $col1;
            $params->mod_emundus_footer_texte_col_2 = $col2;

            $query->clear()
                ->update($db->quoteName('#__modules'))
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
                ->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emundus_footer'));

            try {
                $db->setQuery($query);
                return $db->execute();
            } catch(Exception $e) {
                JLog::add('component/com_emundus/models/settings | Error at updating footer : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
            }
        } else {
            return $this->updateOldFooter($col1,$col2);
        }
    }

    /**
     * Deprecated footer handling
     *
     * @param $col1
     * @param $col2
     * @return bool
     *
     * @since 1.0
     */
    private function updateOldFooter($col1,$col2) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->update($db->quoteName('#__modules'))
            ->set($db->quoteName('content') . ' = ' . $db->quote($col1))
            ->where($db->quoteName('position') . ' LIKE ' . $db->quote('footer-a'));

        try {
            $db->setQuery($query);
            $db->execute();

            $query->clear()
                ->update($db->quoteName('#__modules'))
                ->set($db->quoteName('content') . ' = ' . $db->quote($col2))
                ->where($db->quoteName('position') . ' LIKE ' . $db->quote('footer-b'));
            $db->setQuery($query);
            $db->execute();

            return true;
        } catch(Exception $e) {
            JLog::add('component/com_emundus/models/settings | Error at updating footer articles : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Get emundus tags published for wysiwig editor (emails, settings, formbuilder)
     *
     * @return array|false|mixed
     *
     * @since 1.10.0
     */
    function getEditorVariables() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $lang = JFactory::getLanguage();
        $actualLanguage = substr($lang->getTag(), 0 , 2);
        if($actualLanguage == 'fr'){
            $language = 2;
        } else {
            $language = 1;
        }

        $query->select('st.id as id,st.tag as `value`,fc.value as description')
            ->from($db->quoteName('#__emundus_setup_tags','st'))
            ->leftJoin($db->quoteName('#__falang_content','fc').' ON '.$db->quoteName('fc.reference_id').' = '.$db->quoteName('st.id'))
            ->where($db->quoteName('st.published') . ' = ' . $db->quote(1))
            ->andWhere($db->quoteName('fc.reference_field') . ' = ' . $db->quote('description'))
            ->andWhere($db->quoteName('fc.language_id') . ' = ' . $db->quote($language))
            ->andWhere($db->quoteName('fc.reference_table') . ' = ' . $db->quote('emundus_setup_tags'));

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/settings | Error at getting editor variables : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Update the main logo store in a module
     *
     * @param $newcontent
     * @return false|mixed
     *
     * @since 1.0
     */
    function updateLogo($newcontent){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->update($db->quoteName('#__modules'))
                ->set($db->quoteName('content') . ' = ' . $db->quote($newcontent))
                ->where($db->quoteName('id') . ' = 90');
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add('Error : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function onAfterCreateCampaign($user_id) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('count(id)')
            ->from($db->quoteName('#__emundus_setup_campaigns'));
        $db->setQuery($query);

        try {
            if ($db->loadResult() === '1') {
                $this->removeParam('first_login',$user_id);
                return $this->createParam('first_form', $user_id);
            }
            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/settings | Error at set tutorial param after create a campaign : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function onAfterCreateForm($user_id) {
        try {
            $this->removeParam('first_form',$user_id);
            $this->createParam('first_formbuilder', $user_id);
            $this->createParam('first_documents', $user_id);
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/settings | Error at set tutorial param after create a campaign : ' .$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }


    /**
     * @param         $param String The param to be saved in the user account.
     *
     * @param   null  $user_id
     *
     * @return bool
     * @since version
     */
    function createParam($param, $user_id) {

        $user = JFactory::getUser($user_id);

        $table = JTable::getInstance('user', 'JTable');
        $table->load($user->id);

        // Check if the param exists but is false, this avoids accidetally resetting a param.
        $params = $user->getParameters();
        if (!$params->get($param, true)) {
            return true;
        }

        // Store token in User's Parameters
        $user->setParam($param, true);

        // Get the raw User Parameters
        $params = $user->getParameters();

        // Set the user table instance to include the new token.
        $table->params = $params->toString();

        // Save user data
        if (!$table->store()) {
            JLog::add('component/com_emundus/models/settings | Error when create a param in the user ' . $user_id . ' : ' .$table->getError(), JLog::ERROR, 'com_emundus');
            return false;
        }
        return true;
    }

    function removeParam($param, $user_id) {

        $user = JFactory::getUser($user_id);

        $table = JTable::getInstance('user', 'JTable');
        $table->load($user->id);

        // Check if the param exists but is false, this avoids accidetally resetting a param.
        $params = $user->getParameters();
        if (!$params->get($param, true)) {
            return true;
        }

        // Store token in User's Parameters
        $user->setParam($param, false);

        // Get the raw User Parameters
        $params = $user->getParameters();

        // Set the user table instance to include the new token.
        $table->params = $params->toString();

        // Save user data
        if (!$table->store()) {
            JLog::add('component/com_emundus/models/settings | Error when remove a param from the user ' . $user_id . ' : ' .$table->getError(), JLog::ERROR, 'com_emundus');
            return false;
        }
        return true;
    }

    function getDatasFromTable($table){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        if(strpos($table, 'data_') !== false){

            $query->select('join_column_val,translation')
                ->from($db->quoteName('#__emundus_datas_library'))
                ->where($db->quoteName('database_name') . ' LIKE ' . $db->quote($table));
            $db->setQuery($query);
            $columntodisplay = $db->loadObject();

            if(boolval($columntodisplay->translation)){
                $columntodisplay->join_column_val = $columntodisplay->join_column_val . '_en,' . $columntodisplay->join_column_val . '_fr';
            }

            $query->clear()
                ->select('*')
                ->from($db->quoteName($table));
            $db->setQuery($query);

            try {
                return $db->loadAssocList();
            } catch (Exception $e) {
                JLog::add('component/com_emundus/models/settings | Error at getting datas from databasejoin table ' . $table . ' : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
                return false;
            }
        } else {
            return false;
        }
    }

    function saveDatas($form){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $name = strtolower($this->clean($form['label']));

        // Check if a table already get the same name and increment them
        $query->clear()
            ->select('COUNT(*)')
            ->from($db->quoteName('information_schema.tables'))
            ->where($db->quoteName('table_name') . ' LIKE ' . $db->quote('%data_' . $name . '%'));
        $db->setQuery($query);
        $result = $db->loadResult();

        $increment = '00';
        if ($result < 10) {
            $increment = '0' . strval($result);
        } elseif ($result > 10) {
            $increment = strval($result);
        }

        $table_name = 'data_' . $name . '_' . $increment;
        //

        $query->insert($db->quoteName('#__emundus_datas_library'));
        $query->set($db->quoteName('database_name') . ' = ' . $db->quote($table_name))
            ->set($db->quoteName('join_column_val') . ' = ' . $db->quote('value'))
            ->set($db->quoteName('label') . ' = ' . $db->quote($form['label']))
            ->set($db->quoteName('description') . ' = ' . $db->quote($form['desc']))
            ->set($db->quoteName('created') . ' = ' . $db->quote(date('Y-m-d H:i:s')));
        $db->setQuery($query);
        try {
            $db->execute();

            // Create the new table
            $table_query = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
            id int(11) NOT NULL AUTO_INCREMENT,
            value_fr varchar(255) NOT NULL,
            value_en varchar(255) NOT NULL,
            PRIMARY KEY (id)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
            $db->setQuery($table_query);
            $db->execute();
            //

            // Insert values
            $query = $db->getQuery(true);
            foreach($form['db_values'] as $values) {
                $query->clear()
                    ->insert($db->quoteName($table_name));
                $query->set($db->quoteName('value_fr') . ' = ' . $db->quote($values['fr']))
                    ->set($db->quoteName('value_en') . ' = ' . $db->quote($values['en']));
                $db->setQuery($query);
                $db->execute();
            }
            //

            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/settings | Error at saving datas in a new databasejion table : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function saveImportedDatas($form,$datas){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $name = strtolower($this->clean($form['label']));

        // Check if a table already get the same name and increment them
        $query->clear()
            ->select('COUNT(*)')
            ->from($db->quoteName('information_schema.tables'))
            ->where($db->quoteName('table_name') . ' LIKE ' . $db->quote('%data_' . $name . '%'));
        $db->setQuery($query);
        $result = $db->loadResult();

        $increment = '00';
        if ($result < 10) {
            $increment = '0' . strval($result);
        } elseif ($result > 10) {
            $increment = strval($result);
        }

        $table_name = 'data_' . $name . '_' . $increment;
        //

        $columns = array_keys($datas[0]);
        unset($datas[0]);
        foreach ($columns as $key => $column) {
            $columns[$key] = strtolower($this->clean($column));
        }

        $query->insert($db->quoteName('#__emundus_datas_library'));
        $query->set($db->quoteName('database_name') . ' = ' . $db->quote($table_name))
            ->set($db->quoteName('join_column_val') . ' = ' . $db->quote($columns[0]))
            ->set($db->quoteName('label') . ' = ' . $db->quote($form['label']))
            ->set($db->quoteName('description') . ' = ' . $db->quote($form['desc']))
            ->set($db->quoteName('translation') . ' = ' . $db->quote(0))
            ->set($db->quoteName('created') . ' = ' . $db->quote(date('Y-m-d H:i:s')));
        $db->setQuery($query);
        try {
            $db->execute();

            // Create the new table
            $table_query = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
            id int(11) NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
            $db->setQuery($table_query);
            $db->execute();

            foreach ($columns as $key => $column) {
                $query = "ALTER TABLE " . $table_name . " ADD " . $column . " VARCHAR(255) NULL";
                $db->setQuery($query);
                $db->execute();
            }
            //

            // Insert values
            $query = $db->getQuery(true);
            foreach($datas as $value) {
                $query->clear()
                    ->insert($db->quoteName($table_name));
                foreach (array_keys($value) as $key => $column){
                    $query->set($db->quoteName(strtolower($this->clean($column))) . ' = ' . $db->quote(array_values($value)[$key]));
                }
                $db->setQuery($query);
                $db->execute();
            }
            //

            return true;
        } catch (Exception $e) {
            JLog::add('component/com_emundus/models/settings | Error at saving imported datas : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function checkFirstDatabaseJoin($user_id) {
        $user = JFactory::getUser($user_id);

        try {
            $table = JTable::getInstance('user', 'JTable');
            $table->load($user->id);

            // Check if the param exists but is false, this avoids accidetally resetting a param.
            $params = $user->getParameters();
            return $params->get('first_databasejoin', true);
        } catch (Exception $e){
            JLog::add('component/com_emundus/models/settings | Error at checking if its the first databasejoin of the user ' . $user_id . ' : ' .$table->getError(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    function moveUploadedFileToDropbox($file,$name,$extension,$campaign_cat,$filesize){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        try {
            //CHECK OREDERING BEFORE INSERT
            $query->select('ordering')
                ->from($db->quoteName('#__dropfiles_files'))
                ->where($db->quoteName('catid') . ' = ' . $db->quote($campaign_cat));
            $db->setQuery($query);
            $orderings = $db->loadColumn();
            $order = $orderings[sizeof($orderings) - 1] + 1;

            $dateTime = new Date('now', 'UTC');
            $now = $dateTime->toSQL();

            $query->clear()
                ->insert($db->quoteName('#__dropfiles_files'));
            $query->set($db->quoteName('catid') . ' = ' . $db->quote($campaign_cat))
                ->set($db->quoteName('file') . ' = ' . $db->quote($file))
                ->set($db->quoteName('state') . ' = ' . $db->quote(1))
                ->set($db->quoteName('ordering') . ' = ' . $db->quote($order))
                ->set($db->quoteName('title') . ' = ' . $db->quote($name))
                ->set($db->quoteName('description') . ' = ' . $db->quote(''))
                ->set($db->quoteName('ext') . ' = ' . $db->quote($extension))
                ->set($db->quoteName('size') . ' = ' . $db->quote($filesize))
                ->set($db->quoteName('hits') . ' = ' . $db->quote(0))
                ->set($db->quoteName('version') . ' = ' . $db->quote(''))
                ->set($db->quoteName('created_time') . ' = ' . $db->quote($now))
                ->set($db->quoteName('modified_time') . ' = ' . $db->quote($now))
                ->set($db->quoteName('publish') . ' = ' . $db->quote($now))
                ->set($db->quoteName('author') . ' = ' . $db->quote(JFactory::getUser()->id))
                ->set($db->quoteName('language') . ' = ' . $db->quote(''));
            $db->setQuery($query);
            $db->execute();
            return $db->insertid();
        }  catch (Exception $e) {
            JLog::add('component/com_emundus/models/settings | Error at moving uploaded file ' . $file . ' to the dropbox category ' . $campaign_cat . ': ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

	function getBannerModule(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		try {
			$query->select('params')
				->from($db->quoteName('#__modules'))
				->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emundus_banner'))
				->andWhere($db->quoteName('published') . ' = 1');
			$db->setQuery($query);
			return $db->loadResult();
		}
		catch (Exception $e) {
			JLog::add('Error : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			return false;
		}
	}

	function updateBannerImage(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		try {
			$query->select('*')
				->from($db->quoteName('#__modules'))
				->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emundus_banner'))
				->andWhere($db->quoteName('published') . ' = 1');
			$db->setQuery($query);
			$module = $db->loadObject();

			if(!empty($module)){
				$params = json_decode($module->params);
				$params->mod_em_banner_image = 'images/custom/default_banner.png';

				$query->clear()
					->update($db->quoteName('#__modules'))
					->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
					->where($db->quoteName('id') . ' = ' . $db->quote($module->id));
				$db->setQuery($query);
				return $db->execute();
			} else {
				return false;
			}
		}
		catch (Exception $e) {
			JLog::add('Error : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			return false;
		}
	}

	function getOnboardingLists() {
		$lists = [];

		$group = 'com_emundus';
		$cache_id = 'onboarding_lists';
		$cache_data = null;

		require_once (JPATH_ROOT .'/components/com_emundus/helpers/cache.php');
		$h_cache = new EmundusHelperCache('com_emundus', '', 86400, 'component');
		if ($h_cache->isEnabled()) {
			$cache_data = $h_cache->get($cache_id);
		}

		if (empty($cache_data)) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('`default`, value')
				->from($db->quoteName('#__emundus_setup_config'))
				->where($db->quoteName('namekey') . ' = ' . $db->quote('onboarding_lists'));

			try {
				$db->setQuery($query);

				$data = $db->loadObject();
				if (!empty($data->value) || !empty($data->default)) {
					if (!empty($data->value)) {
						$lists = json_decode($data->value, true);
					} else {
						$lists = json_decode($data->default, true);
					}

					foreach($lists as $lk => $list) {
						if ($lk === 'campaigns') {
							$eMConfig = JComponentHelper::getParams('com_emundus');
							$allow_pinned_campaign = $eMConfig->get('allow_pinned_campaign', 0);

							if (!$allow_pinned_campaign) {
								foreach($list['tabs'] as $tk => $tab) {
									if ($tab['key'] === 'campaign') {
										foreach ($tab['actions'] as $ak => $action) {
											if ($action['name'] === 'pin' || $action['name'] === 'unpin') {
												unset($tab['actions'][$ak]);
											}
										}
										$list['tabs'][$tk] = $tab;
										break;
									}
								}
							}
						}

						$list['title'] = JText::_($list['title']);

						foreach($list['tabs'] as $tk => $tab) {
							$tab['title'] = JText::_($tab['title']);

							foreach($tab['actions'] as $ak => $action) {
								$action['label'] = JText::_($action['label']);
								if(!empty($action['confirm'])) {
									$action['confirm'] = JText::_($action['confirm']);
								}
								$tab['actions'][$ak] = $action;
							}

							foreach($tab['filters'] as $fk => $filter) {
								$filter['label'] = JText::_($filter['label']);
								$tab['filters'][$fk] = $filter;
							}

							$list['tabs'][$tk] = $tab;
						}

						$lists[$lk] = $list;
					}
					if ($h_cache->isEnabled()) {
						$h_cache->set($cache_id, $lists);
					}
				}
			} catch (Exception $e) {
				JLog::add('Error getting onboarding lists in model at query : '. $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}
		} else {
			$lists = $cache_data;
		}

		return $lists;
	}
}
