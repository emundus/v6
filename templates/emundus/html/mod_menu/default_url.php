<?php
/**
 * @version		$Id: default_url.php 21322 2011-05-11 01:10:29Z dextercowley $
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
// Note. It is important to remove spaces between elements.
/*$class = $item->anchor_css ? 'class="'.$item->anchor_css.'" ' : $class;
$title = $item->anchor_title ? 'title="'.$item->anchor_title.'" ' : '';*/

$anchor_css = $item->anchor_css ? $item->anchor_css : '';
$app	= JFactory::getApplication();
$class = rtrim($class,'"');
$class = str_replace($class, $class.' '.$anchor_css.'"',$class);

if ($item->menu_image) {
		$item->params->get('menu_text', 1 ) ?
		$linktype = '<img src="'.$item->menu_image.'" alt="'.$item->title.'" /><span class="image-title">'.$item->title.'</span> ' :
		$linktype = '<img src="'.$item->menu_image.'" alt="'.$item->title.'" />';
}
else { $linktype = $item->title;
}

$m_pos = $app->getTemplate(true)->params->get( $newparams->position . 'ms');

switch ($item->browserNav) :
	default:
	case 0:
?><a <?php echo $class; ?> href="<?php echo $item->flink; ?>" <?php if (strpos($class, 'dropdown-toggle') !== false){echo'data-toggle="dropdown"';} ?> <?php echo $title; ?>><span class="menuchildicon"></span><?php echo $linktype; ?><?php if((!$justifychk && $m_pos == 'h_menu')  || $m_pos == 'v_menu') {echo '</a>';}

// else{ echo '</a>';}
		break;
	case 1:
		// _blank
?><a <?php echo $class; ?> href="<?php echo $item->flink; ?>" target="_blank" <?php if (strpos($class, 'dropdown-toggle') !== false){echo'data-toggle="dropdown"';} ?> <?php echo $title; ?>><span class="menuchildicon"></span><?php echo $linktype; ?><?php if((!$justifychk && $m_pos == 'h_menu')   || $m_pos == 'v_menu') {echo '</a>';}

// else{ echo '</a>';}
		break;
	case 2:
		// window.open
		$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,'.$params->get('window_open');
?><a <?php echo $class; ?> href="<?php echo $item->flink; ?>" onclick="window.open(this.href,'targetWindow','<?php echo $attribs;?>');return false;" <?php if (strpos($class, 'dropdown-toggle') !== false){echo'data-toggle="dropdown"';} ?> <?php echo $title; ?>><span class="menuchildicon"></span><?php echo $linktype; ?><?php if ((!$justifychk && $m_pos == 'h_menu')  || $m_pos == 'v_menu') {echo '</a>';}
// else{ echo '</a>';}
		break;
endswitch;
