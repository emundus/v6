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
		$linktype = '<img src="'.$item->menu_image.'" alt="'.$item->title.'" /><span class="image-title" style="display: none;opacity: 0">'.$item->title.'</span>' :
		$linktype = '<img src="'.$item->menu_image.'" alt="'.$item->title.'" />';
} else {
    $linktype = '<span class="simple-letter">'.$item->title[0].'</span><span class="image-title" style="display: none;opacity: 0">'.$item->title.'</span>';
}

if($item->deeper) {
    $linktype .= '<span class="g-menu-parent-indicator parent-indicator-close" style="display: none;" id="parent_' . $item->id .'" data-g-menuparent=""></span>';
}

$flink = $item->flink;
$flink = JFilterOutput::ampReplace(htmlspecialchars($flink));

// Gantry 5 menu elements have a completely different HTML structure when found inside a dropdown (level > 1)
switch ($item->browserNav) :
	default:
	case 0:
	    if ($item->level > 1) :?>
            <a class="g-menu-item-container" href="<?php echo $flink; ?>" <?php echo $title; ?>>
                <span class="g-menu-item-content">
                    <span class="g-menu-item-title"><?php echo $linktype; ?></span>
                </span>
            </a>
        <?php else :?>
            <a <?php echo $class; ?><?php if ($item->deeper): echo 'onclick="enableSubLevel(' . $item->id . ')"'; else: echo 'href="' . $flink . '"'; endif; ?><?php echo $title; ?>><?php echo $linktype; ?></a>
        <?php endif;
    break;

	case 1:
		// _blank
		if ($item->level > 1) :?>
            <a class="g-menu-item-container" href="<?php echo $flink; ?>" target="_blank" <?php echo $title; ?>>
                <span class="g-menu-item-content">
                    <span class="g-menu-item-title"><?php echo $linktype; ?></span>
                </span>
            </a>
		<?php else :?>
            <a <?php echo $class; ?>href="<?php echo $flink; ?>" target="_blank" <?php echo $title; ?>><?php echo $linktype; ?></a>
        <?php endif;
    break;

    case 2:
		// window.open
		$options = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,'.$params->get('window_open');
        if ($item->level > 1) :?>
            <a class="g-menu-item-container" href="<?php echo $flink; ?>" onclick="window.open(this.href,'targetWindow','<?php echo $options;?>');return false;" <?php echo $title; ?>>
                <span class="g-menu-item-content">
                    <span class="g-menu-item-title"><?php echo $linktype; ?></span>
                </span>
            </a>
	    <?php else :?>
            <a <?php echo $class; ?>href="<?php echo $flink; ?>" onclick="window.open(this.href,'targetWindow','<?php echo $options;?>');return false;" <?php echo $title; ?>><?php echo $linktype; ?></a>
        <?php endif;
        break;
endswitch;
