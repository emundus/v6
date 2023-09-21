<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2020 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

class plgEmundusSetup_category extends JPlugin {

	private $app;
	private $db;
	private $query;

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);

		$this->app = Factory::getApplication();

	    if (version_compare(JVERSION, '4.0', '>'))
	    {
		    $this->db = Factory::getContainer()->get('DatabaseDriver');
		} else {
			$this->db = Factory::getDbo();
	    }

        $this->query = $this->db->getQuery(true);

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.setupCategory.php'), JLog::ALL, array('com_emundus_setupCategory'));
    }


    function onAfterCampaignCreate($id) {
        try {
            $label = $this->app->input->getString("jos_emundus_setup_campaigns___label");

            if($label == null){
                $this->query
                    ->clear()
                    ->select($this->db->quoteName('label'))
                    ->from($this->db->quoteName('#__emundus_setup_campaigns'))
                    ->where($this->db->quoteName('id') . ' = ' . $this->db->quote($id));
                $this->db->setQuery($this->query);
                $label = $this->db->loadResult();
            }

            $name = JFilterOutput::stringURLSafe($label);

            $this->query
                ->clear()
                ->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__categories'))
                ->where($this->db->quoteName('extension') . ' LIKE ' .$this->db->quote('com_dropfiles'))
                ->andWhere('json_extract(`params`, "$.idCampaign") LIKE ' . $this->db->quote('"'.$id.'"'));

            $this->db->setQuery($this->query);

            $cat_id = $this->db->loadResult();

            if(!$cat_id) {
	            if (version_compare(JVERSION, '4.0', '>'))
	            {
		            $this->db = Factory::getContainer()->get(DatabaseInterface::class);

				} else {
		            Factory::$database = null;
	                $this->db = JFactory::getDbo();
	            }

	            $this->query = $this->db->getQuery(true);

                $table = JTable::getInstance('category');

                $data = array();
                $data['path'] = $name;
                $data['alias'] = $name . '-' . rand(1000,99999);
                $data['title'] = $label;
                $data['parent_id'] = 1;
                $data['extension'] = "com_dropfiles";
                $data['published'] = 1;
                $data['params'] = json_encode(array("idCampaign" =>"".$id));
                $table->setLocation($data['parent_id'], 'last-child');
                $table->bind($data);
				
                if (!$table->store()) {
                    JLog::add('Could not Insert data into jos_categories with error : ' . $table->getError(), JLog::ERROR, 'com_emundus_setupCategory');
                }

				if(!empty($table->id))
				{
					// Insert columns.
					$columns = array('id', 'type', 'path', 'params', 'theme');

					// Insert values.
					$values = array($table->id, $this->db->quote('default'), $this->db->quote(''), $this->db->quote('{\"usergroup\":[\"1\"],\"ordering\":\"ordering\",\"orderingdir\":\"asc\",\"marginleft\":\"10\",\"margintop\":\"10\",\"marginright\":\"10\",\"marginbottom\":\"10\",\"columns\":\"2\",\"showsize\":\"1\",\"showtitle\":\"1\",\"showversion\":\"1\",\"showhits\":\"1\",\"showdownload\":\"1\",\"bgdownloadlink\":\"#76bc58\",\"colordownloadlink\":\"#ffffff\",\"showdateadd\":\"1\",\"showdatemodified\":\"0\",\"showsubcategories\":\"1\",\"showcategorytitle\":\"1\",\"showbreadcrumb\":\"1\",\"showfoldertree\":\"0\"}'), $this->db->quote(''));

					// Prepare the insert query.
					$this->query
						->clear()
						->insert($this->db->quoteName('#__dropfiles'))
						->columns($this->db->quoteName($columns))
						->values(implode(',', $values));
					$this->db->setQuery($this->query);
					$this->db->execute();
				}

            } else {

                // Fields to update.
                $fields = array(
                    $this->db->quoteName('path') . ' = ' . $this->db->quote($name),
                    $this->db->quoteName('title') . ' = ' . $this->db->quote($label),
                    $this->db->quoteName('alias') . ' = ' . $this->db->quote($name)
                );

                // Conditions for which records should be updated.
                $conditions = array(
                    'json_extract(`params`, "$.idCampaign") LIKE ' . $this->db->quote('"'.$id.'"'),
                    $this->db->quoteName('extension') . ' LIKE ' . $this->db->quote('com_dropfiles')
                );

                $this->query
                    ->clear()
                    ->update($this->db->quoteName('#__categories'))
                    ->set($fields)
                    ->where($conditions);

                $this->db->setQuery($this->query);
                $this->db->execute();

                $this->query
                    ->clear()
                    ->update($this->db->quoteName('#__categories'))
                    ->set($this->db->quoteName('title') . ' = ' . $this->db->quote($label))
                    ->where($this->db->quoteName('name') . ' LIKE ' . $this->db->quote('com_dropfiles.category'.$cat_id));

                $this->db->setQuery($this->query);
                $this->db->execute();
            }

            return true;
        } catch (Exception $e) {
            JLog::add(str_replace("\n", "", $this->query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_setupCategory');
            return false;
        }
    }

    function onCampaignDelete($ids) {

        $ids = is_array($ids) ? $ids : array($ids);
        if (empty($ids)) {
            return false;
        }

        try {
            $app = JFactory::getApplication();

            $table = JTable::getInstance('category');

            foreach ($ids AS $id) {

                $this->query
                    ->clear()
                    ->select($this->db->quoteName('id'))
                    ->from($this->db->quoteName('jos_categories'))
                    ->where('json_extract(`params`, "$.idCampaign") LIKE ' . $this->db->quote('"'.$id.'"'));

                $this->db->setQuery($this->query);
                $idCategory = $this->db->loadResult();

                if($idCategory) {
                    $table->load($idCategory);
                    $table->delete();

                    $this->query
                        ->clear()
                        ->delete($this->db->quoteName('jos_dropfiles'))
                        ->where($this->db->quoteName('id') . ' = '.$idCategory);

                    $this->db->setQuery($this->query);
                    $this->db->execute();

                    $this->query
                        ->clear()
                        ->delete($this->db->quoteName('jos_dropfiles_files'))
                        ->where($this->db->quoteName('catid') . ' = '.$idCategory);

                    $this->db->setQuery($this->query);
                    $this->db->execute();
                }
            }

            return true;
        } catch (Exception $e) {
            JLog::add(str_replace("\n", "", $this->query.' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_setupCategory');
            return false;
        }
    }
}
