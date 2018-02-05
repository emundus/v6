<?php
/**
* @version		$Id: mod_emunduspanel.php 7692 2016-04-18 rivalland $
* @package		Joomla
* @copyright	Copyright (C) 2016 emundus.fr. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
include_once(JPATH_BASE.'/components/com_emundus/models/campaign.php');
include_once(JPATH_BASE.'/components/com_emundus/models/profile.php');

$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/lib/Semantic-UI-CSS-master/semantic.min.css" );
// overide css
$header_class = $params->get('header_class', '');
if (!empty($header_class))
	$document->addStyleSheet("media/com_emundus/lib/Semantic-UI-CSS-master/components/site.".$header_class.".css" );
$document->addStyleSheet("media/com_emundus/css/emundus.css" );

$db	= JFactory::getDBO();
$current_user = JFactory::getuser();
$user = JFactory::getSession()->get('emundusUser');

$app = JFactory::getApplication();
$fnum = $app->input->getString('fnum', null);


if (isset($user->menutype))
	$user_menutype = $user->menutype;
else
	$user_menutype = 'mainmenu';

$folder = $params->get('folder', '');
$show_profile_link = $params->get('show_profile_link', 1);
$show_start_link = $params->get('show_start_link', 1);
$show_programme_title = $params->get('show_programme_title', 1);
$show_title = $params->get('show_title', 'My Form');
$text = $params->get($user_menutype, '');
$img = $params->get($user_menutype.'_img', '');
$is_text = $params->get($user_menutype.'_text', '');
$img = explode(',',$img);
$col = 0;
$t__ = '';
$i = 1;
$module_title = '';

if (is_array($text) && !empty($text)) {
	foreach ($text as $t) {
		if(count($text) != $i)
			$t__ .= $t.',';
		else
			$t__ .= $t;
		$i++;
	}
} else {
	$t__ = $text;
}

$btn_profile = '<a class="circular ui icon button" href="'.JRoute::_('index.php?option=com_users&view=profile&layout=edit').'"><i class="user icon"></i>'.JText::_('PROFILE').'</a>';

/***** get an applicant campaign *******/
$m_campaign = new EmundusModelCampaign;
$campaigns = $m_campaign->getCampaignByApplicant($current_user->id);
/***** get applicant profiles *******/
$m_profiles = new EmundusModelProfile;
$applicant_profiles = $m_profiles->getApplicantsProfilesArray();
/***************************************/

if (!empty($t__)) {

	/*if($user_menutype == 'mainmenu')
		$query = 'SELECT m.menutype, m.title, m.alias, m.link, m.id, m.params FROM #__menu m WHERE m.id IN ('.$t__.') ORDER BY m.lft ASC';
	else */

	$query = 'SELECT m.menutype, m.title, m.alias, m.link, m.id, m.params
				FROM #__menu m
				WHERE m.id IN ('.$t__.')
				ORDER BY m.lft ASC';
	$db->setQuery($query);
	$res = $db->loadObjectList();

	if (count($res > 0)) {
		$tab = array();

		if($user->applicant == 1) {
			$link = $res[0]->link.'&Itemid='.$res[0]->id;
			if (!empty($fnum)) {
				$app->redirect( $link );
			}
			$btn_start = '<a class="btn btn-warning" role="button" href="'.JRoute::_($link).'"><i class="right arrow icon"></i>'.JText::_('START').'</a>';
		}
		else {
			$btn_start = '';
		}

		echo '<div class="emundus_home_page" id="em-panel">';
		$j = 0;
		foreach($res as $r){
			$menu_params = json_decode($r->params, true);
			$src = '';
			if (empty($img[$j]) && !empty($menu_params['menu_image']) && empty($menu_params['menu-anchor_css']))
				$src = JURI::base(true).$menu_params['menu_image'];
			else
				$src = JURI::base(true).$folder.''.$img[$j];

			$img = '';
			if (!empty($src)) {
				$img = '<img src="'.$src.'" />';
			}
			if (!empty($menu_params['menu-anchor_css']))
				$glyphicon = '<i class="'.$menu_params['menu-anchor_css'].'"></i>';
			else
				$glyphicon = '';

			$str = '<a href="'.JRoute::_($r->link.'&Itemid='.$r->id).'">'.$glyphicon.'</a>';
			if($is_text == '1')
				$str .= '<br/><a class="text" href="'.$r->link.'&Itemid='.$r->id.'">'.$r->title.'</a>';
			$tab[] = $str;
			$j++;
		}

		$col = count($tab);
	}

} elseif(!$current_user->guest) {
	$query = 'SELECT m.menutype, m.title, m.alias, m.link, m.id, m.params
              FROM #__menu m
              WHERE published=1
              AND menutype like "'.$user_menutype.'"
              AND m.link <> ""
              AND m.link <> "#"
              ORDER BY m.parent_id DESC, m.lft, m.level, m.menutype, m.id ASC';
	$db->setQuery($query);
	$res = $db->loadObjectList();
	if (count($res > 0)) {
		$tab = array();
		$tab_temp = array();

		if($user->applicant == 1){
			$module_title = $show_title;
			$link = $res[0]->link.'&Itemid='.$res[0]->id;
			if (!empty($fnum)) {
				$app->redirect( $link );
				exit();
			}
			$btn_start = '<a class="btn btn-warning" role="button" href="'.JRoute::_($link).'"><i class="right arrow icon"></i>'.JText::_('START').'</a>';
		}else{
			$module_title = '';
			$btn_start = '';
		}
		foreach($res as $r){
			$menu_params = json_decode($r->params, true);

			if (!empty($menu_params['menu-anchor_css'])) {
				$glyphicon = '<i class="'.$menu_params['menu-anchor_css'].'"></i>';
				$icon = '';
			} else {
				$glyphicon = '';
				if (!empty($menu_params['menu_image']))
					$icon = '<img src="'.JURI::base(true).$menu_params['menu_image'].'" />';
				else
					$icon = '<img src="'.JURI::base(true).$folder.'files_grey.png" />';
			}

			$str = '<a href="'.JRoute::_($r->link.'&Itemid='.$r->id).'">'.$glyphicon.$icon.' <br />'.$r->title.'</a>';

			$tab[] = $str;
		}
		$col = count($tab);
	}

}

//------switch profile------------------


	/*
		$uid = JFactory::getUser()->id;
		$db = JFactory::getDbo();
		$query = 'select  profile
		from #__emundus_users where user_id = '.$uid;
		$db->setQuery($query);
		$uData = $db->loadAssoc();

		$uid = JFactory::getUser()->id;
		$db = JFactory::getDbo();
		$query1 = 'select u.username as login, u.email, u.password, eu.firstname, eu.lastname, eu.profile, eu.university_id, up.profile_value as newsletter
		from #__users as u
		left join #__emundus_users as eu on eu.user_id = u.id
		left join #__user_profiles as up on (up.user_id = u.id and up.profile_key like "emundus_profiles.newsletter")
		where u.id = '.$uid;
		$db->setQuery($query1);
		$uData = $db->loadAssoc();

		$query2 = "select esp.id , esp.label, esp.acl_aro_groups, esp.published
		from #__emundus_setup_profiles as esp
		left join #__emundus_users_profiles as eup on eup.profile_id = esp.id
		where eup.user_id = " .$uid;
		$db->setQuery($query2);
		$profiles = $db->loadObjectList();
	 */


//----------------------------------------

if (count(@$user->fnums) > 0 || EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
	require(JModuleHelper::getLayoutPath('mod_emunduspanel'));
}

