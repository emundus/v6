<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_emundus
 * @copyright	Copyright (C) 2014 Décision Publique. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
if (!empty($this->items)) :
?>
<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-inverse-collapse">
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
</button>
<span class="navbar-brand" href="#">Actions</span>
<div class="navbar-collapse collapse navbar-inverse-collapse">
<ul class="nav navbar-nav" style="display:<?php echo $this->display; ?>">
<?php
//var_dump($this->items);
	foreach ($this->items as $i => $item) :
        if ($item->level == 1) {
            echo '<li class="dropdown" style="background-color:none"><a class="em-dropdown" id="em-menu-' . $i . '" href="#">' . @$item->title . '<b class="caret"></b></a>';
        } else {
            $multiple = 0;
            if (isset($_GET['multi']) && !empty($_GET['multi'])) {
                $multiple = $_GET['multi'];
            }
            switch ($multiple) {
                case 0 : if ($item->action['multi']==-1) {
                    echo '<li class="em-actions" id="' . $item->note . '" multi="' . $item->action['multi'] . '"><a id="l_' . $item->note . '" multi="' . $item->action['multi'] . '" href="' . $item->link . '">' . $item->title . '</a>';
                    }
                    break;

                case 1 : echo '<li class="em-actions" id="' . $item->note . '" multi="' . $item->action['multi'] . '"><a id="l_' . $item->note . '" multi="' . $item->action['multi'] . '" href="' . $item->link . '">' . $item->title . '</a>';
                    break;

                default: if ($item->action['multi']==-1 || $item->action['multi']==1) {
                    echo '<li class="em-actions" id="' . $item->note . '" multi="' . $item->action['multi'] . '"><a id="l_' . $item->note . '" multi="' . $item->action['multi'] . '" href="' . $item->link . '">' . $item->title . '</a>';
                }

            }

        }
        // The next item is deeper.
        if ($item->deeper) {
            echo '<ul class="dropdown-menu" id="em-dp-' . $i . '" role="menu" aria-labelledby="em-menu-' . $i . '">';
        } // The next item is shallower.
        elseif ($item->shallower) {
            echo '</li>';
            echo str_repeat('</ul></li>', @$item->level_diff);
        } // The next item is on the same level.
        else {
            echo '</li>';
        };
	endforeach;
?>
</ul>
</div>

<?php
endif;
?>
