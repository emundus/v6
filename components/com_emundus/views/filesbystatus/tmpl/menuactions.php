<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_emundus
 * @copyright   Copyright (C) 2015 eMundus. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
if (!empty($this->items)) :
?>

<span class="navbar-brand" href="#"><?php echo JText::_('COM_EMUNDUS_ACTIONS'); ?></span>
<div class="navbar-collapse collapse navbar-inverse-collapse em-collapse-menuaction">
<ul class="nav navbar-nav em-collapse-menuaction-nav" style="display:<?php echo $this->display; ?>">
<?php
    $multiple = JRequest::getVar('multi', '0', 'get','INT', JREQUEST_NOTRIM);
    foreach ($this->items as $i => $item) :
        if ($item->level == 1) {
            echo '<li class="dropdown" style="background-color:none"><a class="em-dropdown" id="em-menu-' . $i . '" href="#">' . @$item->title . '<b class="caret"></b></a>';
        }
        else {
            switch ($multiple) {
                case 0 :
                if ($item->action['multi']==-1) {
                    echo '<li class="em-actions" id="' . $item->note . '" multi="' . $item->action['multi'] . '"><a id="l_' . $item->note . '" multi="' . $item->action['multi'] . '" href="' . $item->link . '">' . $item->title . '</a>';
                }
                break;

                case 1 :
                echo '<li class="em-actions" id="' . $item->note . '" multi="' . $item->action['multi'] . '"><a id="l_' . $item->note . '" multi="' . $item->action['multi'] . '" href="' . $item->link . '">' . $item->title . '</a>';
                break;

                default:
                if ($item->action['multi']==-1 || $item->action['multi']==1) {
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
