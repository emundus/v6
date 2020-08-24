<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// include('../../administrator/comp');

/**
 * Script file of emundus_onboard component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_emundus_onboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
class com_emundus_onboardInstallerScript
{
	/**
	 * This method is called after a component is installed.
	 *
	 * @param  \stdClass $parent - Parent object calling this method.
	 *
	 * @return void
	 */
	public function install($parent)
	{
		$parent->getParent();
	}

	/**
	 * This method is called after a component is uninstalled.
	 *
	 * @param  \stdClass $parent - Parent object calling this method.
	 *
	 * @return void
	 */
	public function uninstall($parent)
	{
		$db = JFactory::getDbo();

		// Delete the onboarding menu module
		$query = "SELECT id FROM jos_modules
              WHERE title = 'Menu-onboarding'";
		$db->setQuery($query);
		$moduleId = $db->loadObject()->id;

		$query = "DELETE FROM jos_modules_menu WHERE moduleid = " . $moduleId;
		$db->setQuery($query);
		$db->execute();

		$query = "DELETE FROM jos_modules WHERE id = " . $moduleId;
		$db->setQuery($query);
		$db->execute();
		//

		// Delete all items from onboardingmenu
		$query = "DELETE FROM jos_menu WHERE menutype = 'onboardingmenu'";
		$db->setQuery($query);
		$db->execute();
		//

		// Delete the onboarding item from coordinator menu
		$query = "DELETE FROM jos_menu WHERE menutype = 'coordinatormenu' AND alias='onboarding'";
		$db->setQuery($query);
		$db->execute();
		//

		// Delete the onboarding menutype
		$query = "DELETE FROM jos_menu_types WHERE menutype = 'onboardingmenu'";
		$db->setQuery($query);
		$db->execute();
		//

		// Remove added column into jos_emundus_setup_attachement_profiles
		$query = "ALTER TABLE jos_emundus_setup_attachment_profiles DROP COLUMN ordering";
		$db->setQuery($query);
		$db->execute();

		$query = "ALTER TABLE jos_emundus_setup_attachment_profiles DROP COLUMN published";
		$db->setQuery($query);
		$db->execute();

        $query = "ALTER TABLE jos_emundus_setup_attachment_profiles DROP COLUMN campaign_id";
        $db->setQuery($query);
        $db->execute();
		//
		echo '<p>Composant Onboarding désinstallé avec succès.</p>';
	}

	/**
	 * This method is called after a component is updated.
	 *
	 * @param  \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 */
	public function update($parent)
	{
		// echo '<p>' .
		//   JText::sprintf(
		//     'COM_EMUNDUS_ONBOARD_UPDATE_TEXT',
		//     $parent->get('manifest')->version
		//   ) .
		//   '</p>';
	}

	/**
	 * Runs just before any installation action is performed on the component.
	 * Verifications and pre-requisites should run in this function.
	 *
	 * @param  string    $type   - Type of PreFlight action. Possible values are:
	 *                           - * install
	 *                           - * update
	 *                           - * discover_install
	 * @param  \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 */
	public function preflight($type, $parent)
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		$token = JSession::getFormToken();
	}

	/**
	 * Runs right after any installation action is performed on the component.
	 *
	 * @param  string    $type   - Type of PostFlight action. Possible values are:
	 *                           - * install
	 *                           - * update
	 *                           - * discover_install
	 * @param  \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 */
	function postflight($type, $parent)
	{
		/*$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$token = JSession::getFormToken();

		$db = JFactory::getDbo();

		// Create jos_emundus_setup_attachment_profiles if not exist
		$query = "
    CREATE TABLE IF NOT EXISTS jos_emundus_setup_attachment_profiles (
    id int(11) NOT NULL AUTO_INCREMENT,
    profile_id int(11) NOT NULL,
    attachment_id int(11) NOT NULL,
    displayed tinyint(1) NOT NULL DEFAULT '1',
    mandatory tinyint(1) NOT NULL,
    bank_needed tinyint(1) DEFAULT NULL,
    duplicate int(1) NOT NULL DEFAULT '0' COMMENT 'duplicate this document for all application files',
    PRIMARY KEY (id),
    UNIQUE KEY attachment_profile (profile_id,attachment_id),
    KEY profile_id (profile_id),
    KEY attachment_id (attachment_id),
    KEY displayed (displayed),
    KEY mandatory (mandatory)
    ) ENGINE=InnoDB AUTO_INCREMENT=229 DEFAULT CHARSET=utf8 COMMENT='Liste des pièces jointes par profil'";
		$db->setQuery($query);
		$db->execute();

		$query = "ALTER TABLE jos_emundus_setup_attachment_profiles ADD ordering TINYINT(1) NOT NULL AFTER mandatory";
		$db->setQuery($query);
		$db->execute();

		$query = "ALTER TABLE jos_emundus_setup_attachment_profiles ADD published TINYINT(1) NOT NULL DEFAULT '1' AFTER ordering";
		$db->setQuery($query);
		$db->execute();
		//

		// Retrieve component and template id
		$query = "SELECT extension_id FROM jos_extensions WHERE element = 'com_emundus_onboard'";
		$db->setQuery($query);
		$componentId = $db->loadObject()->extension_id;

		$query = "SELECT id FROM jos_template_styles WHERE template = 'emundus'";
		$db->setQuery($query);
		$templateId = $db->loadObject()->id;
		//

		// Create a new menutype onboarding
		$query = "INSERT INTO jos_menu_types(asset_id, menutype, title, description, client_id)
              VALUES(253,'onboardingmenu','Onboarding Menu','The main menu to onboarding component',0)";
		$db->setQuery($query);
		$db->execute();
		echo '<p>Nouveau type de menu crée : onboarding</p>';
		//

		// Create the onboarding item to display in coordinator menu
		$query = "SELECT rgt FROM jos_menu
              WHERE menutype = 'coordinatormenu' AND alias = 'parametres'";
		$db->setQuery($query);
		$rgt = $db->loadObject()->rgt;

		$query = "UPDATE jos_menu SET rgt = rgt + 2 WHERE rgt >" . $rgt;
		$db->setQuery($query);
		$db->execute();

		$query = "UPDATE jos_menu SET lft = lft + 2 WHERE lft >" . $rgt;
		$db->setQuery($query);
		$db->execute();

		$rgt = intval($rgt) + 1;
		$lft = intval($rgt) + 2;

		$query = "INSERT INTO jos_menu(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out_time, access, img, template_style_id, params, lft, rgt, language)
              VALUES('coordinatormenu', 'OnBoarding', 'onboarding', '', 'onboarding/campaigns', 'index.php?option=com_emundus_onboard&view=campaign', 'component', 1, 1, 1," . $componentId . ", '2020-04-07 18:36:12', 7, ''," . $templateId . " ,'{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_image_css\":\"\",\"menu_text\":1,\"menu_show\":1,\"page_title\":\"\",\"show_page_heading\":\"\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"menu-meta_keywords\":\"\",\"robots\":\"\",\"secure\":0}'," . $rgt . "," . $lft . ", '*')";
		$db->setQuery($query);
		$db->execute();
		echo '<p>Lien onboarding ajouté au menu coordinateur</p>';
		//

		// Create the campaign item to display in onboarding menu
		$query = "SELECT rgt FROM jos_menu
              WHERE menutype = 'main' AND alias = 'emundus-onboard'";
		$db->setQuery($query);
		$rgt = $db->loadObject()->rgt;

		$rgt = intval($rgt) + 1;
		$lft = intval($rgt) + 2;

		$query = "INSERT INTO jos_menu(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out_time, access, img, template_style_id, params, lft, rgt, language)
              VALUES('onboardingmenu', 'Campagne d\'appel', 'campaigns', '', 'onboarding/campaigns', 'index.php?option=com_emundus_onboard&view=campaign', 'component', 1, 1, 1," . $componentId . ", '2020-04-07 18:36:12', 7, ''," . $templateId . " ,'{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_image_css\":\"\",\"menu_text\":1,\"menu_show\":1,\"page_title\":\"\",\"show_page_heading\":\"\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"menu-meta_keywords\":\"\",\"robots\":\"\",\"secure\":0}'," . $rgt . "," . $lft . ", '*')";
		$db->setQuery($query);
		$db->execute();
		echo '<p>Lien campagne ajouté au menu onboarding</p>';
		//

		// Create the program item to display in onboarding menu
		$query = "SELECT rgt FROM jos_menu
              WHERE menutype = 'onboardingmenu' AND alias = 'campaigns'";
		$db->setQuery($query);
		$rgt = $db->loadObject()->rgt;

		$rgt = intval($rgt) + 1;
		$lft = intval($rgt) + 2;

		$query = "INSERT INTO jos_menu(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out_time, access, img, template_style_id, params, lft, rgt, language)
              VALUES('onboardingmenu', 'Programme', 'programs', '', 'onboarding/programs', 'index.php?option=com_emundus_onboard&view=program', 'component', 1, 1, 1," . $componentId . ", '2020-04-07 18:36:12', 7, ''," . $templateId . " ,'{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_image_css\":\"\",\"menu_text\":1,\"menu_show\":1,\"page_title\":\"\",\"show_page_heading\":\"\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"menu-meta_keywords\":\"\",\"robots\":\"\",\"secure\":0}'," . $rgt . "," . $lft . ", '*')";
		$db->setQuery($query);
		$db->execute();
		echo '<p>Lien programme ajouté au menu onboarding</p>';
		//

		// Create the email item to display in onboarding menu
		$query = "SELECT rgt FROM jos_menu
              WHERE menutype = 'onboardingmenu' AND alias = 'programs'";
		$db->setQuery($query);
		$rgt = $db->loadObject()->rgt;

		$rgt = intval($rgt) + 1;
		$lft = intval($rgt) + 2;

		$query = "INSERT INTO jos_menu(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out_time, access, img, template_style_id, params, lft, rgt, language)
              VALUES('onboardingmenu', 'Email', 'emails', '', 'onboarding/emails', 'index.php?option=com_emundus_onboard&view=email', 'component', 1, 1, 1," . $componentId . ", '2020-04-07 18:36:12', 7, ''," . $templateId . " ,'{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_image_css\":\"\",\"menu_text\":1,\"menu_show\":1,\"page_title\":\"\",\"show_page_heading\":\"\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"menu-meta_keywords\":\"\",\"robots\":\"\",\"secure\":0}'," . $rgt . "," . $lft . ", '*')";
		$db->setQuery($query);
		$db->execute();
		echo '<p>Lien email ajouté au menu onboarding</p>';
		//

		// Create the form item to display in onboarding menu
		$query = "SELECT rgt FROM jos_menu
              WHERE menutype = 'onboardingmenu' AND alias = 'emails'";
		$db->setQuery($query);
		$rgt = $db->loadObject()->rgt;

		$rgt = intval($rgt) + 1;
		$lft = intval($rgt) + 2;

		$query = "INSERT INTO jos_menu(menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, checked_out_time, access, img, template_style_id, params, lft, rgt, language)
              VALUES('onboardingmenu', 'Formulaire', 'forms', '', 'onboarding/forms', 'index.php?option=com_emundus_onboard&view=form', 'component', 1, 1, 1," . $componentId . ", '2020-04-07 18:36:12', 7, ''," . $templateId . " ,'{\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"menu_image_css\":\"\",\"menu_text\":1,\"menu_show\":1,\"page_title\":\"\",\"show_page_heading\":\"\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"menu-meta_keywords\":\"\",\"robots\":\"\",\"secure\":0}'," . $rgt . "," . $lft . ", '*')";
		$db->setQuery($query);
		$db->execute();
		echo '<p>Lien formulaire ajouté au menu onboarding</p>';
		//

		// Create a new menu module
		$query = "INSERT INTO jos_modules(asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
              VALUES(253, 'Menu-onboarding', '', '', 1, 'header-onboarding', 0, '2020-04-07 18:36:12', '2020-04-07 18:36:12', '2099-01-01 00:00:00', 1, 'mod_menu', 7, 0, '{\"menutype\":\"onboardingmenu\",\"base\":\"\",\"startLevel\":1,\"endLevel\":0,\"showAllChildren\":1,\"tag_id\":\"\",\"class_sfx\":\"\",\"window_open\":\"\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":1,\"cache_time\":900,\"cachemode\":\"itemid\",\"module_tag\":\"div\",\"bootstrap_size\":\"0\",\"header_tag\":\"h3\",\"header_class\":\"\",\"style\":\"0\"}', 0, '*')";
		$db->setQuery($query);
		$db->execute();
		$moduleId = $db->insertid();

		$query = "INSERT INTO jos_modules_menu(moduleid, menuid)
              VALUES(" . $moduleId . ", 0)";
		$db->setQuery($query);
		$db->execute();
		echo '<p>Module du menu onboarding crée</p>';
		//*/

		echo '<strong>Installation terminée avec succès</strong>';

	}
}
