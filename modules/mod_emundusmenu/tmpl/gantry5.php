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
?>
<nav class="g-main-nav <?php echo $class_sfx;?>" data-g-hover-expand="true"
    <?php
    $tag = '';
    if ($params->get('tag_id')!=NULL) {
        $tag = $params->get('tag_id').'';
        echo ' id="'.$tag.'"';
    }
    ?>>
    <ul class="g-toplevel">
        <?php
        foreach ($list as $i => &$item) :
        $item->anchor_css="item";
        $class = 'item-'.$item->id.' g-standard';
        if ($item->id == $active_id) {
            $class .= ' current';
        }

        if (in_array($item->id, $path)) {
            $class .= ' active';
        }
        elseif ($item->type == 'alias') {
            $aliasToId = $item->params->get('aliasoptions');
            if (count($path) > 0 && $aliasToId == $path[count($path)-1]) {
                $class .= ' active';
            }
            elseif (in_array($aliasToId, $path)) {
                $class .= ' alias-parent-active';
            }
        }

        if ($item->parent) {
            $class .= ' g-parent';
        }

        if (!empty($class)) {
            $class = ' class="g-menu-item g-menu-'.trim($class) .'"';
        }

        echo '<li'.$class.'>';

        // Render the menu item.
        switch ($item->type) :
            case 'separator':
            case 'url':
            case 'component':
                require JModuleHelper::getLayoutPath('mod_menu', 'default_'.$item->type);
                break;

            default:
                require JModuleHelper::getLayoutPath('mod_menu', 'default_url');
                break;
        endswitch;

        // The next item is deeper.
        if ($item->deeper) {
            echo '<span class="g-menu-parent-indicator" data-g-menuparent=""></span>';
            echo '<ul class="g-dropdown g-dropdown-right g-fade g-inactive">';
            echo '<li class="g-dropdown-column">';
            echo '<div class="g-grid"><div class="g-block size-100"><ul class="g-sublevel"><li class="g-level-'.($item->level).' g-go-back"><a class="g-menu-item-container" href="#" data-g-menuparent=""><span class="g-menu-item-content"><span class="g-menu-item-title">Back</span></span></a></li>';
        }
        // The next item is shallower.
        elseif ($item->shallower) {
            echo '</li>';
            echo str_repeat('</ul></div></div></li></ul>', $item->level_diff);
        }
        // The next item is on the same level.
        else {
            echo '</li>';
        }
    endforeach;
    ?>
    </ul>
</nav>
