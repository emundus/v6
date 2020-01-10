<?php
/**
 * @version		$Id: default_separator.php 21322 2011-05-11 01:10:29Z dextercowley $
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
$title = $item->anchor_title ? 'title="'.$item->anchor_title.'" ' : '';
$anchor_css = $item->anchor_css ? $item->anchor_css : '';

if( $class == '' )
{
	$class='class="separate"';
}

$class = rtrim($class,'"');
$class = str_replace($class, $class.' '.$anchor_css.'"',$class);

if ($item->menu_image) {
		$item->params->get('menu_text', 1 ) ?
		$linktype = '<img src="'.$item->menu_image.'" alt="'.$item->title.'" /><span class="image-title">'.$item->title.'</span> ' :
		$linktype = '<img src="'.$item->menu_image.'" alt="'.$item->title.'" />';
}
else { $linktype = $item->title;
}

?>
<span
  <?php echo $class; ?> <?php if (strpos($class, 'dropdown-toggle') !== false){echo'data-toggle="dropdown"';} ?> <?php echo $title; ?>><?php echo $title; ?><?php echo $linktype; ?>
</span>
