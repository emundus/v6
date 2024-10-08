<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * A command line cron job to import language Tags to jo_emundus_setup_languages table
 */

// Initialize Joomla framework
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
    require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES')) {
    define('JPATH_BASE', dirname(__DIR__));
    require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

define('DS', DIRECTORY_SEPARATOR);

/**
 * Cron job to trash expired cache data.
 *
 * @since  2.5
 */
class LanguageGenerateTranslationTag extends JApplicationCli {


    /**
     * Entry point for the script
     *
     * @return  void
     *
     * @since   2.5
     */
    public function doExecute() {

        $args = (array)$GLOBALS['argv'];

        if (empty($args[1])) {
            $this->out('Please provide a profile id');
        } else {
            $profile_id = (int)$args[1];

            if (!$this->checkProfileIdExists($profile_id)) {
                $this->out('Given profile id does not exist');
            } else {
                $this->generateTranslationTag($profile_id);
            }
        }
    }

    private function checkProfileIdExists($profile_id)
    {
        $exists = false;

        if (!empty($profile_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->clear()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('jos_emundus_setup_profiles'))
                ->where($db->quoteName('id') . ' = ' . $profile_id);

            try {
                $db->setQuery($query);
                $result = $db->loadResult();

                $exists = !empty($result);
            } catch (Exception $e) {
                $this->out($e->getMessage());
            }
        }

        return $exists;
    }

    private function generateTranslationTag($profile_id)
    {
		if (!empty($profile_id)) {
			require_once JPATH_SITE . '/components/com_emundus/models/form.php';
			$languages = $this->getPlatformLanguages();
			$labels = $this->getLabelsFromProfile($profile_id);

			foreach($languages as $language) {
				$file = JPATH_SITE . '/language/overrides/' . $language. '.override.ini';
				$parsed_file = JLanguageHelper::parseIniFile($file);

				foreach($labels as $tag => $label) {
					if (!array_key_exists($label, $parsed_file)) {
						$parsed_file[$tag] = $label;
						$this->out('Added tag ' . $tag . ' to ' . $language . ' language file with value ' . $label);
					} else {
						unset($labels[$tag]);
					}
				}

				$saved = JLanguageHelper::saveToIniFile($file, $parsed_file);

				if (!$saved) {
					$this->out('Could not save language file for ' . $language);
					die();
				}
			}

			if (!empty($labels)) {
				foreach($labels as $tag => $label) {
					$this->updateLabel($tag);
				}
			}
		}
    }

	private function getLabelsFromProfile($profile_id): array
	{
		$labels = array();

		if (!empty($profile_id)) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$forms = $this->getFormsByProfileId($profile_id);
			foreach($forms as $form) {
				$query->clear()
					->select('label, intro')
					->from($db->quoteName('jos_fabrik_forms'))
					->where($db->quoteName('id') . ' = ' . $form->id);

				$db->setQuery($query);
				$form_data = $db->loadAssoc();
				$form_label_tag = $this->generateTag($form->id, $profile_id, 'jos_fabrik_forms', 'label', 'FORM');
				$labels[$form_label_tag] = $form_data['label'];
				$form_intro_tag = $this->generateTag($form->id, $profile_id, 'jos_fabrik_forms', 'intro', 'FORMINTRO');
				$labels[$form_intro_tag] = $form_data['intro'];

				$groups = $this->getGroupsFromFormId($form->id);
				foreach($groups as $group) {
					$query->clear()
						->select('label')
						->from($db->quoteName('jos_fabrik_groups'))
						->where($db->quoteName('id') . ' = ' . $group->id);

					$db->setQuery($query);
					$group_label = $db->loadResult();
					$group_label_tag = $this->generateTag($group->id, $form->id, 'jos_fabrik_groups', 'label', 'GROUP');
					$labels[$group_label_tag] = $group_label;

					$elements = $this->getElementsFromGroupId($group->id);
					foreach($elements as $element) {
						$query->clear()
							->select('label')
							->from($db->quoteName('jos_fabrik_elements'))
							->where($db->quoteName('id') . ' = ' . $element->id);

						$db->setQuery($query);
						$element_label = $db->loadResult();
						$element_label_tag = $this->generateTag($element->id, $group->id, 'jos_fabrik_elements', 'label', 'ELEMENT');
						$labels[$element_label_tag] = $element_label;
					}
				}
			}
		}

		return $labels;
	}

	private  function updateLabel($tag) {
		if (!empty($tag)) {
			list($type, $ref_id, $id) = explode('_', $tag);

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			switch($type) {
				case 'FORM':
					$query->clear()
						->update($db->quoteName('jos_fabrik_forms'))
						->set($db->quoteName('label') . ' = ' . $db->quote($tag))
						->where($db->quoteName('id') . ' = ' . $id);
					break;
				case 'FORMINTRO':
					$query->clear()
						->update($db->quoteName('jos_fabrik_forms'))
						->set($db->quoteName('intro') . ' = ' . $db->quote($tag))
						->where($db->quoteName('id') . ' = ' . $id);
					break;
				case 'GROUP':
					$query->clear()
						->update($db->quoteName('jos_fabrik_groups'))
						->set($db->quoteName('label') . ' = ' . $db->quote($tag))
						->where($db->quoteName('id') . ' = ' . $id);
					break;
				case 'ELEMENT':
					$query->clear()
						->update($db->quoteName('jos_fabrik_elements'))
						->set($db->quoteName('label') . ' = ' . $db->quote($tag))
						->where($db->quoteName('id') . ' = ' . $id);
					break;
			}

			$db->setQuery($query);
			$db->execute();
		}
	}

    /**
     * @param $id int du formulaire / group / element
     * @param $ref_id int de sa référence (profile utilisateur / formulaire / group)
     * @param $table string la table associée
     * @param $column string
     * @param $tag
     * @return false|string le nouveau nom de la balise = $tag_$ref_id_$id. ex = FORM_95_183
     */
    private function generateTag($id, $ref_id, $table, $column, $tag)
    {
        $inserted_tag = '';

        if (!empty($id) && !empty($ref_id) && !empty($table) && !empty($column) && !empty($tag)) {
            $new_tag = $tag . '_' . $ref_id . '_' . $id;

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->update($table)
                ->set($db->quoteName($column) .' = '. $db->quote($new_tag))
                ->where('id = '. $id);

            try {
                $db->setQuery($query);
                $inserted = $db->execute();

                if ($inserted) {
                    $inserted_tag = $new_tag;
                }
            } catch (Exception $e) {
                JLog::add('cli/LanguageGenerateTranslationTag | Error at updating column '.$column. ' in table '.$table. ' with id ' . $ref_id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            }
        }

        return $inserted_tag;
    }

    private function getPlatformLanguages() : array {
        $languages = [];

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('lang_code'))
            ->from($db->quoteName('#__languages'))
            ->where($db->quoteName('published') . ' = 1');

        try {
            $db->setQuery($query);
            $languages = $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('cli/LanguageGenerateTranslationTag | Error at getting platform languages : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
        }

        return $languages;
    }

    private function getFormsByProfileId($profile_id): array
    {
        $forms = [];

        if (!empty($profile_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select(['menu.link' , 'menu.rgt'])
                ->from ($db->quoteName('#__menu', 'menu'))
                ->leftJoin($db->quoteName('#__menu_types', 'mt').' ON '.$db->quoteName('mt.menutype').' = '.$db->quoteName('menu.menutype'))
                ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'sp').' ON '.$db->quoteName('sp.menutype').' = '.$db->quoteName('mt.menutype'))
                ->where($db->quoteName('sp.id') . ' = '.$profile_id)
                ->where($db->quoteName('menu.parent_id') . ' != 1')
                ->where($db->quoteName('menu.published') . ' = 1')
                ->where($db->quoteName('menu.link') . ' LIKE ' . $db->quote('%option=com_fabrik%'))
                ->group('menu.rgt')
                ->order('menu.rgt ASC');

            try {
                $db->setQuery($query);
                $forms = $db->loadObjectList();

                if (!empty($forms)) {
                    require_once (JPATH_SITE . '/components/com_emundus/models/formbuilder.php');
                    $formbuilder = new EmundusModelFormbuilder;

                    foreach ($forms as $form) {
                        $link = explode('=', $form->link);
                        $form->id = $link[sizeof($link) - 1];

                        $query->clear()
                            ->select('label')
                            ->from($db->quoteName('#__fabrik_forms'))
                            ->where($db->quoteName('id') . ' = ' . $db->quote($form->id));

                        $db->setQuery($query);
                        $form->label = $formbuilder->getJTEXT($db->loadResult());
                    }
                }
            } catch(Exception $e) {
                JLog::add('cli/LanguageGenerateTranslationTag | Error at getting form pages by profile_id ' . $profile_id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            }
        }

        return $forms;
    }

    private function getGroupsFromFormId($form_id): array
    {
        $groups = [];

        if (!empty($form_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('groups.id, groups.name, groups.label')
                ->from($db->quoteName('#__fabrik_groups', 'groups'))
                ->leftJoin($db->quoteName('#__fabrik_formgroup', 'formgroup').' ON '.$db->quoteName('formgroup.group_id').' = '.$db->quoteName('groups.id'))
                ->where($db->quoteName('formgroup.form_id'). ' = '. $form_id);

            try {
                $db->setQuery($query);
                $groups = $db->loadObjectList();
            } catch (Exception $e) {
                JLog::add('cli/LanguageGenerateTranslationTag | Error at getting groups pages by form_id ' . $form_id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            }
        }

        return $groups;
    }

    private function getElementsFromGroupId($group_id): array
    {
        $elements = [];

        if (!empty($group_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('id, label')
                ->from($db->quoteName('#__fabrik_elements', 'elms'))
                ->where($db->quoteName('elms.group_id'). ' = '. $group_id);

            try {
                $db->setQuery($query);
                $elements = $db->loadObjectList();
            } catch (Exception $e) {
                JLog::add('cli/LanguageGenerateTranslationTag | Error at getting elements pages by group_id ' . $group_id . ' : ' . preg_replace("/[\r\n]/"," ",$query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            }
        }

        return $elements;
    }
}

JApplicationCli::getInstance('LanguageGenerateTranslationTag')->execute();
