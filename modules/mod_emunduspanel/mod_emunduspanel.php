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

$document = JFactory::getDocument();
$document->addStyleSheet( JURI::base()."media/com_emundus/lib/Semantic-UI-CSS-master/semantic.min.css" );
// overide css
$header_class = $params->get('header_class', '');
if (!empty($header_class)) {
	$document->addStyleSheet( JURI::base()."media/com_emundus/lib/Semantic-UI-CSS-master/components/site.".$header_class.".css" );
}
$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus.css" );

$db	= JFactory::getDBO();
$user = JFactory::getUser();

$app = JFactory::getApplication();
$fnum = $app->input->getString('fnum', null);


if(isset($user->menutype)) $user_menutype = $user->menutype;
else $user_menutype = 'mainmenu';
$folder = $params->get('folder', '');
$show_profile_link = $params->get('show_profile_link', 1);
$show_start_link = $params->get('show_start_link', 1);
$show_title = $params->get('show_title', 1);
$text = $params->get($user_menutype, '');
$img = $params->get($user_menutype.'_img', '');
$is_text = $params->get($user_menutype.'_text', '');
$img = explode(',',$img);
$col = 0;
$t__ = '';
$i = 1;

if(is_array($text) && !empty($text)){
	foreach($text as $t){
		if(count($text) != $i) $t__ .= $t.',';
		else $t__ .= $t;
		$i++;
	}
}else{
	$t__ = $text;
}

$btn_profile = '<button class="circular ui icon button"><a href="'.JRoute::_('index.php?option=com_users&view=profile&layout=edit').'"><i class="user icon"></i>'.JText::_('PROFILE').'</a></button>';

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
			if (empty($img[$j]) && !empty($menu_params['menu_image']) && empty($menu_params['menu-anchor_css']))
				$src = JURI::base().$menu_params['menu_image'];
			else
				$src = $folder.''.$img[$j];

			if (!empty($menu_params['menu-anchor_css']))
				$glyphicon = '<i class="'.$menu_params['menu-anchor_css'].'"></i>';
			else
				$glyphicon = '';

			$str = '<a href="'.JRoute::_($r->link.'&Itemid='.$r->id).'">'.$glyphicon.'<img src="'.JURI::Base().'/'.$src.'" /></a>';
			if($is_text == '1')
				$str .= '<br/><a class="text" href="'.$r->link.'&Itemid='.$r->id.'">'.$r->title.'</a>';
			$tab[] = $str;
			$j++;
		}

		$col = count($tab);
	}

} elseif(!$user->guest) { 
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
			$link = $res[0]->link.'&Itemid='.$res[0]->id;
			if (!empty($fnum)) { 
				$app->redirect( $link );
				exit();
			}
			$btn_start = '<a class="btn btn-warning" role="button" href="'.JRoute::_($link).'"><i class="right arrow icon"></i>'.JText::_('START').'</a>';
		}else{
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
					$icon = '<img src="'.JURI::base().$menu_params['menu_image'].'" />';
				else
					$icon = '<img src="'.JURI::Base().$folder.'files_grey.png" />';
			}
			
			$str = '<a href="'.JRoute::_($r->link.'&Itemid='.$r->id).'">'.$glyphicon.$icon.' <br />'.$r->title.'</a>';

			$tab[] = $str;
		}
		$col = count($tab);

	}

}

require(JModuleHelper::getLayoutPath('mod_emunduspanel'));