<?php
/**
* @version		$Id: mod_emunduspanel.php 7692 2016-04-18 rivalland $
* @package		Joomla
* @copyright	Copyright (C) 2018 emundus.fr. All rights reserved.
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
include_once(JPATH_BASE.'/components/com_emundus/models/users.php');


$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/lib/Semantic-UI-CSS-master/semantic.min.css" );
$document->addStyleSheet("media/com_emundus/css/emundus.css" );
$document->addStyleSheet("modules/mod_emunduspanel/style/emundus.css" );

$db	= JFactory::getDBO();
$current_user = JFactory::getuser();
$user = JFactory::getSession()->get('emundusUser');

$app = JFactory::getApplication();
$fnum = $app->input->getString('fnum', null);

$m_users = new EmundusModelUsers;
$applicant_profiles = $m_users->getApplicantProfiles();

if (isset($user->menutype)) {
    $user_menutype = $user->menutype;
} else {
    $user_menutype = 'mainmenu';
}

/*
 * TCHOOZ PARAMETERS
 */
$profiles = $params->get('profiles');
$title = $params->get('title', '');
$desc_text = $params->get('desc_text', '');

$folder = $params->get('folder', '');
$show_profile_link = $params->get('show_profile_link', 1);
$show_start_link = $params->get('show_start_link', 1);
$show_programme_title = $params->get('show_programme_title', 1);
$show_title = $params->get('show_title', 'My Form');
$text = $params->get($user_menutype, '');
$img = $params->get($user_menutype.'_img', '');
$is_text = $params->get($user_menutype.'_text', '');
$show_menu = $params->get('showmenu', true);
$lean_mode = $params->get('leanmode', false) == 'true';
$img = explode(',',$img);
$col = 0;
$t__ = '';
$i = 1;
$module_title = '';

$link = "index.php";


/*
 * If lean mode is on, we show and hide different aspects of the module based on the user's profiles.
 * - User only has candidate profiles: hide profile selector and menu icons.
 * - User has a mix: show dropdown and bubbles if current profile is not applicant.
 * - User only has non-candidate profile: Do not show select but show bubbles.
 */
if ($lean_mode) {
    $m_profiles = new EmundusModelProfile;
	$app_prof = $m_profiles->getApplicantsProfilesArray();

	$user_prof = [];
	foreach ($user->emProfiles as $prof) {
		$user_prof[] = $prof->id;
	}

	// If all of the user's profiles are found in the list of applicant profiles, then the user is only an applicant.
    $only_applicant = !array_diff($user_prof, $app_prof);
}

if (is_array($text) && !empty($text)) {
	foreach ($text as $t) {
		if (count($text) != $i) {
			$t__ .= $t.',';
		} else {
			$t__ .= $t;
		}
		$i++;
	}
} else {
	$t__ = $text;
}

$btn_profile = '<a class="circular ui icon button" href="'.JRoute::_('index.php?option=com_users&view=profile&layout=edit').'"><i class="user icon"></i>'.JText::_('MOD_EMUNDUS_PANEL_PROFILE').'</a>';

if (!empty($t__)) {

	$query = 'SELECT DISTINCT(m.id), m.menutype, m.title, m.alias, m.link, m.params
				FROM #__menu m
				WHERE m.id IN ('.$t__.')
				ORDER BY m.lft ASC';
	$db->setQuery($query);
	$res = $db->loadObjectList();

	if (!empty($res)) {
		$tab = array();
		$link = $res[0]->link.'&Itemid='.$res[0]->id;

		if ($user->applicant == 1) {
			$btn_start = '<a class="btn btn-warning" role="button" href="'.JRoute::_($link).'"><i class="right arrow icon"></i>'.JText::_('START').'</a>';
		} else {
			$btn_start = '';
		}

		echo '<div class="emundus_home_page" id="em-panel">';
		$j = 0;
		foreach ($res as $r) {
			$menu_params = json_decode($r->params, true);
			$src = '';
			if (empty($img[$j]) && !empty($menu_params['menu_image']) && empty($menu_params['menu-anchor_css'])) {
				$src = JURI::base().$menu_params['menu_image'];
			} else {
				$src = JURI::base().$folder.''.$img[$j];
			}

			$img = '';
			if (!empty($src)) {
				$img = '<img src="'.$src.'" />';
			}

			if (!empty($menu_params['menu-anchor_css'])) {
				$glyphicon = '<i class="'.$menu_params['menu-anchor_css'].'"></i>';
			} else {
				$glyphicon = '';
			}

			$str = '<a href="'.JRoute::_($r->link.'&Itemid='.$r->id).'">'.$glyphicon.'</a>';
			if ($is_text == '1') {
				$str .= '<br/><a class="text" href="'.JRoute::_($r->link.'&Itemid='.$r->id).'">'.$r->title.'</a>';
			}
			$tab[] = $str;
			$j++;
		}

		$col = count($tab);
	}

} elseif (!$current_user->guest) {

	/***** get an applicant campaign *******/
	$m_campaign = new EmundusModelCampaign;
	$campaign = $m_campaign->getCampaignByFnum($user->fnum);
    /***** get applicant profiles *******/
    $m_profiles = new EmundusModelProfile;
    $applicant_profiles = $m_profiles->getApplicantsProfilesArray();
	/***************************************/

	$query = 'SELECT m.menutype, m.title, m.alias, m.link, m.id, m.params
              FROM #__menu m
              WHERE published=1
              AND menutype like "'.$user_menutype.'"
              AND m.link <> ""
              AND m.link <> "#"
              ORDER BY m.parent_id DESC, m.lft, m.level, m.menutype, m.id ASC';
	$db->setQuery($query);
	$res = $db->loadObjectList();

	if (!empty($res)) {
		$tab = array();
		$tab_temp = array();
		$link = $res[0]->link.'&Itemid='.$res[0]->id;

		if ($user->applicant == 1){
			$module_title = $show_title;
			$btn_start = '<a class="btn btn-warning" role="button" href="'.JRoute::_($link).'"><i class="right arrow icon"></i>'.JText::_('START').'</a>';
		} else {
			$module_title = '';
			$btn_start = '';
		}
		foreach ($res as $r) {
			$menu_params = json_decode($r->params, true);

			if (!empty($menu_params['menu-anchor_css'])) {
				$glyphicon = '<i class="'.$menu_params['menu-anchor_css'].'"></i>';
				$icon = '';
			} else {
				$glyphicon = '';
				if (!empty($menu_params['menu_image'])) {
					$icon = '<img src="'.JURI::base().$menu_params['menu_image'].'" />';
				} else {
					$icon = '<img src="'.JURI::base().$folder.'files_grey.png" />';
				}
			}

			$str = '<a href="'.JRoute::_($r->link.'&Itemid='.$r->id).'">'.$glyphicon.$icon.' <br />'.$r->title.'</a>';
			$tab[] = $str;
		}
		$col = count($tab);
	}

}
if (!empty($fnum)) {
	$app->redirect( $link );
}

if($params->get('panel_style', 'default') != 'tchooz_dashboard') {
    if (!empty(@$user->fnums) || EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) {
        require JModuleHelper::getLayoutPath('mod_emunduspanel', $params->get('panel_style', 'default'));
    }
} else {
    if ((!empty(@$user->fnums) || EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) && in_array(JFactory::getSession()->get('emundusUser')->profile,$profiles)) {
        require JModuleHelper::getLayoutPath('mod_emunduspanel', 'tchooz_dashboard');
    }
}

