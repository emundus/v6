<?php
/**
 * @version        $Id: default.php 14401 2014-09-16 14:10:00Z brivalland $
 * @package        Joomla
 * @subpackage     Emundus
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license        GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */
defined('_JEXEC') or die('Restricted access');

if ($this->open_file_in_modal) {
    JText::script('COM_EMUNDUS_FILES_EVALUATION');
    JText::script('COM_EMUNDUS_FILES_TO_EVALUATE');
    JText::script('COM_EMUNDUS_FILES_EVALUATED');
    JText::script('COM_EMUNDUS_ONBOARD_FILE');
    JText::script('COM_EMUNDUS_ONBOARD_STATUS');
    JText::script('COM_EMUNDUS_FILES_APPLICANT_FILE');
    JText::script('COM_EMUNDUS_FILES_ATTACHMENTS');
    JText::script('COM_EMUNDUS_FILES_COMMENTS');
    JText::script('COM_EMUNDUS_ONBOARD_NOFILES');
    JText::script('COM_EMUNDUS_FILES_ELEMENT_SELECTED');
    JText::script('COM_EMUNDUS_FILES_ELEMENTS_SELECTED');
    JText::script('COM_EMUNDUS_FILES_UNSELECT');
    JText::script('COM_EMUNDUS_FILES_OPEN_IN_NEW_TAB');
    JText::script('COM_EMUNDUS_FILES_CANNOT_ACCESS');
    JText::script('COM_EMUNDUS_FILES_CANNOT_ACCESS_DESC');
    JText::script('COM_EMUNDUS_FILES_DISPLAY_PAGE');
    JText::script('COM_EMUNDUS_FILES_NEXT_PAGE');
    JText::script('COM_EMUNDUS_FILES_PAGE');
    JText::script('COM_EMUNDUS_FILES_TOTAL');
    JText::script('COM_EMUNDUS_FILES_ALL');
    JText::script('COM_EMUNDUS_FILES_ADD_COMMENT');
    JText::script('COM_EMUNDUS_FILES_CANNOT_ACCESS_COMMENTS');
    JText::script('COM_EMUNDUS_FILES_CANNOT_ACCESS_COMMENTS_DESC');
    JText::script('COM_EMUNDUS_FILES_COMMENT_TITLE');
    JText::script('COM_EMUNDUS_FILES_COMMENT_BODY');
    JText::script('COM_EMUNDUS_FILES_VALIDATE_COMMENT');
    JText::script('COM_EMUNDUS_FILES_COMMENT_DELETE');
    JText::script('COM_EMUNDUS_FILES_ASSOCS');
    JText::script('COM_EMUNDUS_FILES_TAGS');
    JText::script('COM_EMUNDUS_FILES_PAGE_ON');
    JText::script('COM_EMUNDUS_ERROR_OCCURED');
    JText::script('COM_EMUNDUS_ACTIONS_CANCEL');
    JText::script('COM_EMUNDUS_OK');
    JText::script('COM_EMUNDUS_FILES_FILTER_NO_ELEMENTS_FOUND');
}
?>

<input type="hidden" id="view" name="view" value="evaluation">
<div>
    <div>
        <div class="col-md-3 side-panel" style="height: calc(100vh - 72px);overflow-y: auto;">
            <div class="panel panel-info em-containerFilter" id="em-files-filters">
                <div class="panel-heading em-containerFilter-heading">
                    <div>
                        <h3 class="panel-title"><?php echo JText::_('COM_EMUNDUS_FILTERS') ?></h3> &ensp;&ensp;
                    </div>
                    <div class="buttons" style="float:right; margin-top:0px">
                        <div class="em-flex-row">
                            <?php
                            if ($this->use_module_for_filters) {
                                ?>
                                <label for="save-filter" class="em-mr-8 em-flex-row" style="margin-bottom: 0;">
                                    <span class="material-icons-outlined em-pointer em-color-white"
                                          title="<?php echo JText::_('COM_EMUNDUS_ACTIONS_SAVE_BTN'); ?>">save</span>
                                </label>
                                <input type="button" style="display: none" id="save-filter"
                                       title="<?php echo JText::_('COM_EMUNDUS_ACTIONS_SAVE_BTN'); ?>"/>
                                <?php
                            }
                            ?>
                            <label for="clear-search" class="em-flex-row">
                                <span class="material-icons-outlined em-pointer em-color-white"
                                      title="<?php echo JText::_('COM_EMUNDUS_ACTIONS_CLEAR_BTN'); ?>">filter_alt_off</span>
                            </label>
                            <input type="button" style="display: none" id="clear-search"
                                   title="<?php echo JText::_('COM_EMUNDUS_ACTIONS_CLEAR_BTN'); ?>"/>
                        </div>
                    </div>
                </div>

                <div class="panel-body em-containerFilter-body">
                    <?php

                    if (!$this->use_module_for_filters) {
                        echo @$this->filters;
                    }
                    else {
                        echo JHtml::_('content.prepare', '{loadposition emundus_filters}');
                    }
                    ?>
                </div>
            </div>

            <div class="panel panel-info em-hide" id="em-appli-menu">
                <div class="panel-heading em-hide-heading">
                    <h3 class="panel-title"><?php echo JText::_('COM_EMUNDUS_APPLICATION_ACTIONS') ?></h3>
                </div>
                <div class="panel-body em-hide-body">
                    <div class="list-group">
                    </div>
                </div>
            </div>

            <div class="panel panel-info em-hide" id="em-synthesis">
                <div class="panel-heading em-hide-heading">
                    <h3 class="panel-title"><?php echo JText::_('COM_EMUNDUS_APPLICATION_SYNTHESIS') ?></h3>
                </div>
                <div class="panel-body em-hide-body">
                </div>
            </div>

            <div class="panel panel-info em-hide" id="em-assoc-files">
                <div class="panel-heading em-hide-heading">
                    <h3 class="panel-title"><?php echo JText::_('COM_EMUNDUS_ACCESS_LINKED_APPLICATION_FILES'); ?></h3>
                </div>
                <div class="panel-body em-hide-body">
                </div>
            </div>


            <div class="clearfix"></div>
            <div class="panel panel-info em-hide" id="em-last-open">
                <div class="panel-heading em-hide-heading">
                    <h3 class="panel-title"><?php echo JText::_('COM_EMUNDUS_APPLICATION_LAST_OPEN_FILES'); ?></h3>
                </div>
                <div class="panel-body em-hide-body">
                    <div class="list-group">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9 main-panel">
            <div id="em-hide-filters" class="em-close-filter" data-toggle="tooltip" data-placement="top"
                 title=<?php echo JText::_('COM_EMUNDUS_FILTERS_HIDE_FILTER'); ?>">
				<span class="glyphicon glyphicon-chevron-left"></span>
        </div>
        <div class="navbar navbar-inverse em-menuaction">
            <div class="navbar-header em-menuaction-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse"
                        data-target=".navbar-inverse-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <span class="navbar-brand" href="#"><?php echo JText::_('COM_EMUNDUS_ACTIONS'); ?></span>
            </div>

        </div>
        <div class="panel panel-default"></div>
    </div>
</div>

<script type="text/javascript">
    var $ = jQuery.noConflict();

    var itemId = '<?php echo $this->itemId;?>';
    var cfnum = '<?php echo $this->cfnum;?>';
    var filterName = '<?php echo JText::_('COM_EMUNDUS_FILTERS_FILTER_NAME'); ?>';
    var filterEmpty = '<?php echo JText::_('COM_EMUNDUS_FILTERS_ALERT_EMPTY_FILTER'); ?>';
    var nodelete = '<?php echo JText::_('COM_EMUNDUS_FILTERS_CAN_NOT_DELETE_FILTER'); ?>';
    var jJTextArray = ['<?php echo JText::_('COM_EMUNDUS_COMMENTS_ENTER_COMMENT'); ?>',
        '<?php echo JText::_('COM_EMUNDUS_FORM_TITLE'); ?>',
        '<?php echo JText::_('COM_EMUNDUS_COMMENTS_SENT'); ?>'];
    var loading = '<?php echo JURI::base() . 'media/com_emundus/images/icones/loader.gif'; ?>';
    var loadingLine = '<?php echo JURI::base() . 'media/com_emundus/images/icones/loader-line.gif'; ?>';
    $(document).ready(function () {
        $('.chzn-select').chosen({width: '75%'});
        refreshFilter();
        reloadActions();
    })

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    })

</script>