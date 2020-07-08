<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2020 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

class plgEmundusSetup_category extends JPlugin {

	var $db;
	var $query;

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		$this->db = JFactory::getDbo();
		$this->query = $this->db->getQuery(true);
		
		jimport('joomla.log.log');
		JLog::addLogger(array('text_file' => 'com_emundus.setupCategory.php'), JLog::ALL, array('com_emundus'));
	}

	
	function onCampaignCreate() {
		try {
			$app = JFactory::getApplication();
			$id = $app->input->get("rowid");
			$label = $app->input->getString("jos_emundus_setup_campaigns___label");
			// $nom = StringHelper::increment($label, 'dash');
			$nom = JFilterOutput::stringURLSafe($label);
			
			$this->query = "SELECT COUNT(`id`) FROM `jos_categories` WHERE json_extract(`jos_categories`.`params`, '$.idCampaign') LIKE '\"".$id."\"' AND `extension` LIKE 'com_dropfiles'";
			$this->db->setQuery($this->query);
			$count = $this->db->loadResult();
			
			if($count === "0") {
				$table = JTable::getInstance('category');
				
				$data = array();
				$data['path'] = $nom;
				$data['title'] = $label;
				$data['parent_id'] = 1;
				$data['extension'] = "com_dropfiles";
				$data['published'] = 1;
				$data['params'] = json_encode(array("idCampaign" =>"".$id));
				$table->setLocation($data['parent_id'], 'last-child');
				$table->bind($data);
				if ($table->check()) {
					$table->store();
				} else {
					JLog::add('Could not Insert data into jos_categories.', JLog::ERROR, 'com_emundus');
					return false;
				}
				
				$this->query = "SELECT `id` FROM `jos_categories` WHERE json_extract(`jos_categories`.`params`, '$.idCampaign') LIKE '\"".$id."\"' AND `extension` LIKE 'com_dropfiles' ORDER BY id DESC LIMIT 1";
				$this->db->setQuery($this->query);
				$idCategory = $this->db->loadResult();
				
				$this->query = "INSERT INTO `jos_dropfiles` (`id`, `type`, `cloud_id`, `path`, `params`, `theme`) VALUES ('".$idCategory."', 'default', '', '', '{\"usergroup\":[\"1\"],\"ordering\":\"ordering\",\"orderingdir\":\"asc\",\"marginleft\":\"10\",\"margintop\":\"10\",\"marginright\":\"10\",\"marginbottom\":\"10\",\"columns\":\"2\",\"showsize\":\"1\",\"showtitle\":\"1\",\"showversion\":\"1\",\"showhits\":\"1\",\"showdownload\":\"1\",\"bgdownloadlink\":\"#76bc58\",\"colordownloadlink\":\"#ffffff\",\"showdateadd\":\"1\",\"showdatemodified\":\"0\",\"showsubcategories\":\"1\",\"showcategorytitle\":\"1\",\"showbreadcrumb\":\"1\",\"showfoldertree\":\"0\"}', '')";
				$this->db->setQuery($this->query);
				$this->db->execute();
			} else {
				$label = $this->db->quote($label);
				$nom = $this->db->quote($nom);
				
				$this->query = "UPDATE `jos_categories` SET `path` = ".$nom.", `title` = ".$label.", `alias` = ".$nom." WHERE json_extract(`jos_categories`.`params`, '$.idCampaign') LIKE '\"".$id."\"' AND `extension` LIKE 'com_dropfiles'";
				$this->db->setQuery($this->query);
				$this->db->execute();
				
				$this->query = "SELECT `id` FROM `jos_categories` WHERE json_extract(`jos_categories`.`params`, '$.idCampaign') LIKE '\"".$id."\"' AND `extension` LIKE 'com_dropfiles' ORDER BY id DESC LIMIT 1";
				$this->db->setQuery($this->query);
				$idCategory = $this->db->loadResult();
				
				$this->query = "UPDATE `jos_assets` SET `title` = ".$label." WHERE  `name` LIKE 'com_dropfiles.category.".$idCategory."'";
				$this->db->setQuery($this->query);
				$this->db->execute();
			}
			
			return true;
		} catch (Exception $e) {
			JLog::add($this->query.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}
	
	function onCampaignDelete() {
		try {
			$app =&JFactory::getApplication();
			$ids = $app->input->get('ids', array(), 'method', 'array');
			
			$table = JTable::getInstance('category');
			
			foreach ($ids AS $id) {
				$this->query = "SELECT `id` FROM `jos_categories` WHERE json_extract(`jos_categories`.`params`, '$.idCampaign') LIKE '\"".$id."\"'";
				$this->db->setQuery($this->query);
				$idCategory = $this->db->loadResult();
				
				$table->load($idCategory);
				$table->delete();
				
				$this->query = "DELETE FROM `jos_dropfiles` WHERE id = ".$idCategory;
				$this->db->setQuery($this->query);
				$this->db->execute();
				
				$this->query = "DELETE FROM `jos_dropfiles_files` WHERE catid = ".$idCategory;
				$this->db->setQuery($this->query);
				$this->db->execute();
			}
			
			return true;
		} catch (Exception $e) {
			JLog::add($this->query.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}
}
