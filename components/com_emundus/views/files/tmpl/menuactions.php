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

    <div class="container-nav em-container-menuaction">

        <span class="navbar-brand" href="#"><?php echo JText::_('COM_EMUNDUS_ACTIONS'); ?></span>
        <div class="navbar-collapse collapse navbar-inverse-collapse">

            <ul class="nav navbar-nav em-container-menuaction-nav" style="display:<?php echo $this->display; ?>">
                <?php

                $multiple = JRequest::getVar('multi', '0', 'get','INT', JREQUEST_NOTRIM); //nb of ckecked ckeckbox

                foreach ($this->items as $i => $item) :

                    if ($item->level == 1) {

                        echo '<li class="dropdown" style="background-color:transparent"><a class="em-dropdown" id="em-menu-' . $i . '" href="#">' . @$item->title . '<b class="caret"></b></a>';
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
    </div>
    <div class="em-close-minimise">
        <div class="btn-group pull-right">
            <button id="em-close-file" class="btn btn-danger btn-xxl"><span class="material-icons">close</span></button>
        </div>
    </div>

<?php
endif;
?>
<div class="modal fade" id="em-modal-actions" style="z-index:99999" tabindex="-1" role="dialog" aria-labelledby="em-modal-actions" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <table style="width: 95%">
                    <tr>
                        <td>
                            <h4 class="modal-title" id="em-modal-actions-title"><?php echo JText::_('COM_EMUNDUS_FORM_TITLE');?></h4>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <table>
                    <tr>
                        <td style="text-align: right;" id="can-val">
                            <button type="button" class="btn btn-danger" data-dismiss="modal"><?php echo JText::_('COM_EMUNDUS_ACTIONS_CANCEL')?></button>
                            <button style="margin-left:5px;" type="button" class="btn btn-success"><?php echo JText::_('COM_EMUNDUS_OK');?></button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="em-modal-form" style="z-index:99999" tabindex="-1" role="dialog" aria-labelledby="em-modal-actions" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="em-modal-actions-title"><?php echo JText::_('COM_EMUNDUS_LOADING');?></h4>
            </div>
            <div class="modal-body">
                <img alt="Loading..." src="<?php echo JURI::base(); ?>media/com_emundus/images/icones/loader-line.gif">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><?php echo JText::_('COM_EMUNDUS_ACTIONS_CANCEL')?></button>
            </div>
        </div>
    </div>
</div>
<script>
    //$('#countCheckedCheckbox').html('');

    $('#em-close-file').click(function(){
        hideCount();

        $('.em-check').prop('checked',false);
        $('.em-check-all-all').prop('checked',false);
        reloadActions('files', undefined, false);
    })
</script>


