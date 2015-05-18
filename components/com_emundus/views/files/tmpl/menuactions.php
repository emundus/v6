<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_emundus
 * @copyright   Copyright (C) 2014 Décision Publique. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
if (!empty($this->items)) :
?>
<div class="navbar-collapse collapse navbar-inverse-collapse">
<ul class="nav navbar-nav" style="display:<?php echo $this->display; ?>">
<?php
//var_dump($this->items);
    $multi = $_GET['multi'];
    foreach ($this->items as $i => $item) :
        
        //$style = $item->action['multi']!=-1?'style="display:none;"':'style="display:block;"';
        if (@$item->level_diff == -1) 
            echo '<li class="dropdown" style="background-color:none"><a class="em-dropdown" id="em-menu-'.$i.'" href="#">'.@$item->title.'<b class="caret"></b></a>';
        else
            echo '<li class="em-actions" id="'.@$item->note.'" multi="'.@$item->action['multi'].'"><a id="l_'.@$item->note.'" multi="'.@$item->action['multi'].'" href="'.@$item->link.'">'.@$item->title.'</a>';
        // The next item is deeper.
        if (@$item->deeper) {
            echo '<ul class="dropdown-menu" id="em-dp-'.$i.'" role="menu" aria-labelledby="em-menu-'.$i.'">';
        }
        // The next item is shallower.
        elseif (@$item->shallower) {
            echo '</li>';
            echo str_repeat('</ul></li>', @$item->level_diff);
        }
        // The next item is on the same level.
        else {
            echo '</li>';
        }
    endforeach;
?>
</ul>
</div>
<script>
    $('.em-actions[multi="0"]').hide();
    $('.em-actions[multi="1"]').hide();
</script>
<?php
endif;
?>
