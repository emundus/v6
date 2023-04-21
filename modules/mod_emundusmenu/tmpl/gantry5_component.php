<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
$class = $item->anchor_css ? 'class="'.$item->anchor_css.'" ' : '';
$title = $item->anchor_title ? 'title="'.$item->anchor_title.'" ' : '';
if ($item->menu_image) {
		$item->params->get('menu_text', 1 ) ?
		$linktype = '<img src="'.$item->menu_image.'" alt="'.$item->title.'" /><span class="image-title">'.$item->title.'</span> ' :
		$linktype = '<img src="'.$item->menu_image.'" alt="'.$item->title.'" />';
}
else {
    $linktype = $item->title;
}

switch ($item->browserNav) :
	default:
	case 0:
        if ($item->level > 1) :?>
            <a class="g-menu-item-container" href="<?php echo $item->flink; ?>" <?php echo $title; ?>>
                <span class="g-menu-item-content">
                    <span class="g-menu-item-title"><?php echo $linktype; ?></span>
                </span>
            </a>
        <?php else :?>
            <a <?php echo $class; ?>href="<?php echo $item->flink; ?>" <?php echo $title; ?>><?php echo $linktype; ?></a>
        <?php endif;
    break;

	case 1:
		// _blank
		if ($item->level > 1) :?>
            <a class="g-menu-item-container" href="<?php echo $item->flink; ?>" target="_blank" <?php echo $title; ?>>
                <span class="g-menu-item-content">
                    <span class="g-menu-item-title"><?php echo $linktype; ?></span>
                </span>
            </a>
		<?php else :?>
            <a <?php echo $class; ?>href="<?php echo $item->flink; ?>" target="_blank" <?php echo $title; ?>><?php echo $linktype; ?></a>
		<?php endif;
    break;

	case 2:
		// window.open
		$options = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,'.$params->get('window_open');
		if ($item->level > 1) :?>
            <a class="g-menu-item-container" href="<?php echo $item->flink; ?>" onclick="window.open(this.href,'targetWindow','<?php echo $options;?>');return false;" <?php echo $title; ?>>
                <span class="g-menu-item-content">
                    <span class="g-menu-item-title"><?php echo $linktype; ?></span>
                </span>
            </a>
		<?php else :?>
            <a <?php echo $class; ?>href="<?php echo $item->flink; ?>" onclick="window.open(this.href,'targetWindow','<?php echo $options;?>');return false;" <?php echo $title; ?>><?php echo $linktype; ?></a>
		<?php endif;
    break;
endswitch;
