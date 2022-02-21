<?php
defined('_JEXEC') || die;
//JHTML::_('behavior.calendar');
if (DropfilesBase::isJoomla40()) {
    $doc = JFactory::getDocument();
    $doc->addScript(JURI::root() . 'components/com_dropfiles/assets/js/chosen.jquery.min.js');
    $doc->addStyleSheet(JURI::root() . 'components/com_dropfiles/assets/css/chosen.css');
} else {
    JHTML::_('behavior.formvalidation');
    JHtml::_('formbehavior.chosen', '.chzn-select');
}

$doc = JFactory::getDocument();
$doc->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/css/ui-lightness/jquery-ui-1.9.2.custom.min.css');
$doc->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/css/jquery.tagit.css');
$doc->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/css/jquery.datetimepicker.css');
$doc->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/css/search_filter.css');

$doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/jquery.colorbox-min.js');
$doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/colorbox.init.js');
$doc->addStyleSheet(JURI::base('true') . '/components/com_dropfiles/assets/css/colorbox.css');

$doc->addScript(JUri::base() . 'components/com_dropfiles/assets/js/jquery-ui-1.9.2.custom.min.js');
$doc->addScript(JUri::base() . 'components/com_dropfiles/assets/js/jquery.tagit.js');
$doc->addScript(JUri::base() . 'components/com_dropfiles/assets/js/jquery.datetimepicker.js');
$doc->addScriptDeclaration('var search_date_format="' . $this->params->get('date_format', 'Y-m-d') . '";');
$doc->addScript(JUri::base() . 'components/com_dropfiles/assets/js/search_filter.js');
$session = JFactory::getSession();
$config = JFactory::getConfig();
$params = $this->params;
$layoutParams = $this->layoutParams;
$filters = $this->filters;
$excludeCategory = $params->get('ref_exclude_category_id');
$excludeCategory = (isset($excludeCategory)) ? $excludeCategory : array();

?>
<script>
    window.catDatas = <?php echo json_encode($this->categories);?>;
    window.catTags = <?php echo json_encode($this->catTags);?> ;
    var filterData = null;
    jQuery(document).ready(function () {
        jQuery('#filter_catid_chzn').removeAttr('style');
        jQuery('.chzn-search input').removeAttr('readonly');
        <?php if ($layoutParams->get('tag_filter', 1) &&
            $layoutParams->get('display_tag', 'searchbox') === 'searchbox') { ?>
        var defaultTags = [];
        var availTags = [];
                            <?php if (isset($filters['ftags'])) { ?>
        var ftags = '<?php echo $filters['ftags'];?>';
        defaultTags = ftags.split(",");
                            <?php } ?>
                            <?php if (!empty($this->allTagsFiles)) { ?>
        availTags = <?php echo $this->allTagsFiles; ?>;
                            <?php } ?>
        jQuery("#input_tags").tagit({
            availableTags: availTags,
            allowSpaces: true,
            initialTags: defaultTags,
            beforeTagAdded: function(event, ui) {
                if (jQuery.inArray(ui.tagLabel, availTags) == -1) {
                    jQuery('span.error-message').css("display", "block").fadeOut(2000);
                    setTimeout(function() {
                        try {
                            jQuery(".input_tags").tagit("removeTagByLabel", ui.tagLabel, 'fast');
                        } catch (e) {
                            console.log(e);
                        }

                    }, 100);

                    return;
                }
                return true;
            }
        });
        <?php } ?>

        <?php if (!empty($filters)) { ?>
        filterData = <?php echo json_encode($filters);?>;
        <?php } ?>

        function addPlaceholder() {
            var $ = jQuery;
            $(".tags-filtering .tagit-new input").attr("placeholder", Joomla.JText._('COM_DROPFILES_SEARCH_TAGS_PLACEHOLDER', 'Input tags here...'));
        }

        //add placeholder when tag search box is selected
        addPlaceholder();

        window.history.pushState(filterData, "", window.location);
    });
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" id="adminForm" name="adminForm" class="dropfiles_search" method="post">
    <input type="hidden" value='com_dropfiles' name="option"/>
    <input type="hidden" value='frontsearch.search' name="task"/>
    <?php if ($this->Itemid) { ?>
        <input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>"/>
    <?php } ?>

    <?php
        $search_class = '';
    if (intval($layoutParams->get('show_filters', 1)) === 1) {
        if (intval($layoutParams->get('cat_filter', 1)) === 0) {
            $search_class = 'fullwidth';
        }
    } else {
        $search_class = 'fullwidth';
    }
    ?>

    <input class="dropfiles_cat_type" type="hidden" name="cat_type" value="<?php echo $filters['cattype']; ?>"/>

    <div id="loader" style="display:none; text-align: center">
        <img src="<?php echo JURI::root(); ?>components/com_dropfiles/assets/images/searchloader.svg"/>
    </div>

    <div class="box-search-filter">
        <div class="searchSection">
            <?php if ($layoutParams->get('show_filters', 1) && $layoutParams->get('cat_filter', 1)) : ?>
                <div class="categories-filtering">
                    <img src="<?php echo JURI::root(); ?>components/com_dropfiles/assets/images/menu.svg" class="material-icons cateicon"/>
                    <div class="cate-lab" style="text-transform: uppercase"><?php echo JText::_('COM_DROPFILES_SEARCH_FILES_CATEGORY_FILTERING'); ?></div>
                    <div class="ui-widget" id="dropfiles-listCate" style="display: none">
                        <select id="filter_catid" class="chzn-select" name="catid">
                            <option value="">
                                <?php echo ' ' . JText::_('COM_DROPFILES_SEARCH_SELECT_ONE'); ?>
                            </option>
                            <?php
                            if (count($this->categories) > 0) {
                                foreach ($this->categories as $key => $category) {
                                    if (!empty($excludeCategory)) {
                                        if ($category->type === 'default') {
                                            if (in_array($category->id, $excludeCategory)) {
                                                continue;
                                            }
                                        } else {
                                            if (in_array($category->cloud_id, $excludeCategory)) {
                                                continue;
                                            }
                                        }
                                        if (!empty($filters['catid']) && (int) $filters['catid'] === $category->id) {
                                            echo '<option selected="selected"  data-type="' . $category->type
                                                . '" value="' . $category->id . '">'
                                                . str_repeat('-', $category->level - 1)
                                                . ' ' . $category->title . '</option>';
                                        } else {
                                            echo '<option  data-type="' . $category->type . '"  value="'
                                                . $category->id . '">'
                                                . str_repeat('-', $category->level - 1) . ' '
                                                . $category->title
                                                . '</option>';
                                        }
                                    } else {
                                        if (!empty($filters['catid']) && (int) $filters['catid'] === $category->id) {
                                            echo '<option selected="selected"  data-type="' . $category->type
                                                . '" value="' . $category->id . '">'
                                                . str_repeat('-', $category->level - 1)
                                                . ' ' . $category->title . '</option>';
                                        } else {
                                            echo '<option  data-type="' . $category->type . '"  value="'
                                                . $category->id . '">'
                                                . str_repeat('-', $category->level - 1) . ' '
                                                . $category->title
                                                . '</option>';
                                        }
                                    }
                                }
                            }
                            ?>
                        </select>
                        <ul class="cate-list" id="cate-list">
                            <?php
                            if (count($this->categories) > 0) { ?>
                                <li class="search-cate">
                                    <input class="qCatesearch" id="dropfilesCategorySearch" data-id="" placeholder="<?php echo JText::_('COM_DROPFILES_SEARCH_QUICK_CATEGORY_SEARCH'); ?>">
                                </li>
                                <li class="cate-item" data-catid="">
                                    <span class="dropfiles-toggle-expand"></span>
                                    <span class="dropfiles-folder-search"></span>
                                    <label><?php echo JText::_('COM_DROPFILES_SEARCH_CATEGORY_ALL');?></label>
                                </li>
                                <?php

                                foreach ($this->categories as $key => $category) {
                                    if ($category->level > 1) {
                                        $down_icon = '<span class="dropfiles-toggle-expand child-cate"></span>';
                                    } else {
                                        $down_icon = '<span class="dropfiles-toggle-expand"></span>';
                                    }
                                    if (!empty($excludeCategory)) {
                                        if ($category->type === 'default') {
                                            if (in_array($category->id, $excludeCategory)) {
                                                continue;
                                            }
                                        } else {
                                            if (in_array($category->cloud_id, $excludeCategory)) {
                                                continue;
                                            }
                                        }
                                        if (!empty($filters['catid']) && (int) $filters['catid'] === $category->id) {
                                            echo '<li class="cate-item choosed"  data-type="' . $category->type
                                                . '" data-catid="' . $category->id . '" data-catlevel="' . $category->level . '">'
                                                . '<span class="space-child">'. str_repeat('-', $category->level - 1) .'</span>'
                                                . $down_icon
                                                . '<span class="dropfiles-folder-search"></span>'
                                                . '<label>' . $category->title .'</label>'
                                                . '</li>';
                                        } else {
                                            echo '<li  class="cate-item" data-type="' . $category->type . '" data-catid="' . $category->id . '" data-catlevel="' . $category->level . '">'
                                                . '<span class="space-child">'. str_repeat('-', $category->level - 1) .'</span>'
                                                . $down_icon
                                                . '<span class="dropfiles-folder-search"></span>'
                                                . '<label>' . $category->title .'</label>'
                                                . '</li>';
                                        }
                                    } else {
                                        if (!empty($filters['catid']) && (int) $filters['catid'] === $category->id) {
                                            echo '<li class="cate-item choosed"  data-type="' . $category->type
                                                . '" data-catid="' . $category->id . '" data-catlevel="' . $category->level . '">'
                                                . '<span class="space-child">'. str_repeat('-', $category->level - 1) .'</span>'
                                                . $down_icon
                                                . '<span class="dropfiles-folder-search"></span>'
                                                . '<label>' . $category->title .'</label>'
                                                . '</li>';
                                        } else {
                                            echo '<li  class="cate-item" data-type="' . $category->type . '" data-catid="' . $category->id . '" data-catlevel="' . $category->level . '">'
                                                . '<span class="space-child">'. str_repeat('-', $category->level - 1) .'</span>'
                                                . $down_icon
                                                . '<span class="dropfiles-folder-search"></span>'
                                                . '<label>' . $category->title .'</label>'
                                                . '</li>';
                                        }
                                    }
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            <div class="only-file input-group clearfix dropfiles_search_input <?php echo $search_class; ?>" id="Search_container">
                <img src="<?php echo JURI::root(); ?>components/com_dropfiles/assets/images/search-24.svg" class="material-icons dropfiles-icon-search"/>
                <input type="text" class="pull-left required" name="q" id="txtfilename"
                       placeholder="<?php echo JText::_('COM_DROPFILES_SEARCH_SEARCH_KEYWORD'); ?>"
                       placeholder="<?php echo JText::_('COM_DROPFILES_SEARCH_SEARCH_KEYWORD'); ?>"
                       value="<?php echo isset($filters['q']) ? $filters['q'] : ''; ?>"/>
                <button id="btnsearch" class="pull-left"><i class="dropfiles-icon-search"></i></button>
            </div>
        </div>

        <?php if ($layoutParams->get('show_filters', 1)) : ?>
            <div class="by-feature feature-border" id="Category_container">
                <?php if (intval($layoutParams->get('tag_filter', 1)) === 1 &&
                intval($layoutParams->get('creation_date', 1)) === 1 &&
                intval($layoutParams->get('update_date', 1)) === 1) : ?>
                    <div class="dropfiles_tab">
                        <button class="tablinks active" onclick="openSearchfilter(event, 'Filter')"><?php echo JText::_('COM_DROPFILES_SEARCH_FILTER_TAB'); ?></button>
                        <button class="tablinks" onclick="openSearchfilter(event, 'Tags')"><?php echo JText::_('COM_DROPFILES_SEARCH_TAGS_TAB'); ?></button>
                        <span class="feature-toggle toggle-arrow-up-alt"></span>
                    </div>
                <?php endif;?>
                <div class="top clearfix">
                    <div class="pull-left">
                        <p class="filter-lab" style="text-transform: uppercase"><?php echo JText::_('COM_DROPFILES_SEARCH_FILTERS') ?></p>
                    </div>
                    <div class="pull-right"><span class="feature-toggle toggle-arrow-up-alt"></span></div>
                </div>
                <div class="feature clearfix row-fluid dropfiles_tabcontainer">

                    <!-- Tab content -->
                    <div id="Filter" class="dropfiles_tabcontent active">
                        <?php
                        $date_class= 'date-filter';
                        if ((int) $layoutParams->get('creation_date', 1) === 0 && (int) $layoutParams->get('update_date', 1) === 0) {
                            $date_class= 'dropfiles-date-hidden';
                        }
                        ?>

                        <div class="<?php echo $date_class; ?>">
                            <?php if ($layoutParams->get('creation_date', 1)) : ?>
                                <div class="creation-date">
                                    <p class="date-info" style="text-transform: uppercase"><?php echo JText::_('COM_DROPFILES_SEARCH_CREATION_DATE'); ?></p>
                                    <div class="create-date-container">
                                        <div>
                                            <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_SEARCH_FROM'); ?> </span>
                                            <div class="input-icon-date">
                                                <input class="input-date" type="text" data-min="cfrom" name="cfrom"
                                                       value="<?php echo isset($filters['cfrom']) ? $filters['cfrom'] : ''; ?>"
                                                       id="cfrom"/>
                                                <i data-id="cfrom" class="icon-date icon-calendar"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_SEARCH_TO'); ?></span>
                                            <div class="input-icon-date">
                                                <input class="input-date" data-min="cfrom" type="text" name="cto" id="cto"
                                                       value="<?php echo isset($filters['cto']) ? $filters['cto'] : ''; ?>"/>
                                                <i data-id="cto" data-min="cfrom" class="icon-date icon-calendar"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($layoutParams->get('update_date', 1)) : ?>
                                <div class="update-date">
                                    <p class="date-info" style="text-transform: uppercase"><?php echo JText::_('COM_DROPFILES_SEARCH_UPDATE_DATE'); ?></p>
                                    <div class="update-date-container">
                                        <div>
                                            <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_SEARCH_FROM'); ?> </span>
                                            <div class="input-icon-date">
                                                <input class="input-date" type="text" data-min="ufrom"
                                                       value="<?php echo isset($filters['ufrom']) ? $filters['ufrom'] : ''; ?>"
                                                       name="ufrom" id="ufrom"/>
                                                <i data-id="ufrom" class="icon-date icon-calendar"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_SEARCH_TO'); ?> </span>
                                            <div class="input-icon-date">
                                                <input class="input-date" type="text" data-min="ufrom"
                                                       value="<?php echo isset($filters['uto']) ? $filters['uto'] : ''; ?>"
                                                       name="uto" id="uto"/>
                                                <i data-id="uto" data-min="ufrom" class="icon-date icon-calendar"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div id="Tags" class="dropfiles_tabcontent mobilecontent">
                        <?php if ($layoutParams->get('tag_filter', 1) &&
                            $layoutParams->get('display_tag', 'searchbox') === 'searchbox') : ?>
                            <div class="span12 tags-filtering">
                                <p class="tags-info" style="text-align:left; text-transform: uppercase"><?php echo JText::_('COM_DROPFILES_SEARCH_TAGS_FILTERING'); ?></p>
                                <input type="text" id="input_tags" name="ftags" class="tagit input_tags"
                                       value="<?php echo isset($filters['ftags']) ? $filters['ftags'] : ''; ?>"/>
                            </div>
                            <span class="error-message"><?php echo JText::_('COM_DROPFILES_SEARCH_NO_TAG_MATCHING'); ?></span>
                        <?php endif; ?>

                        <?php if ($layoutParams->get('tag_filter', 1) &&
                            $layoutParams->get('display_tag', 'searchbox') === 'checkboxes') : ?>
                            <div class="clearfix row-fluid">
                                <div class="span12 chk-tags-filtering">
                                    <p class="tags-info" style="text-align:left; text-transform: uppercase"><?php echo JText::_('COM_DROPFILES_SEARCH_TAGS_FILTERING'); ?></p>
                                    <input type="hidden" id="input_tags" name="ftags" class="input_tags"
                                           value="<?php echo isset($filters['ftags']) ? $filters['ftags'] : ''; ?>"/>
                                    <?php $allTags = str_replace(array('[', ']',     '"'), '', $this->allTagsFiles);
                                    if ($allTags !== '') {
                                        $arrTags = explode(',', $allTags);
                                        echo '<ul>';
                                        echo '<label class="labletags">' . JText::_('COM_DROPFILES_SEARCH_FILTER_BY_TAGS') . '</label>';
                                        foreach ($arrTags as $key => $tag) { ?>
                                            <li class="tags-item">
                                                <input type="checkbox" name="chk_ftags[]" class="chk_ftags"
                                                       id="ftags<?php echo $key; ?>" value="<?php echo $tag; ?>"/>
                                                <span><?php echo $tag; ?></span>
                                            </li>
                                        <?php }
                                        echo '</ul>';
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (intval($layoutParams->get('tag_filter', 1)) === 0) : ?>
                            <div class="no-tags"></div>
                        <?php endif; ?>
                    </div>
                    <div class="clearfix"></div>
                    <div class="box-btngroup-below">
                        <a href="<?php echo JRoute::_('index.php?option=com_dropfiles&view=frontsearch'); ?>"
                           class="btnsearchbelow" type="reset"
                           id="btnReset"><?php echo JText::_('COM_DROPFILES_SEARCH_BUTTON_RESET'); ?></a>
                        <button id="btnsearchbelow" class="btnsearchbelow" type="button">
                            <i class="dropfiles-icon-search"></i>
                            <?php echo JText::_('COM_DROPFILES_SEARCH_BUTTON_SEARCH'); ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div id="dropfiles-results" class="list-results">
            <?php echo $this->loadTemplate('results'); ?>
        </div>
    </div>
</form>

<script>
    var basejUrl = '<?php echo JURI::root();?>';
</script>
<style>

    .spinner svg {
        width: 28px;
        height: 28px;
    }

    svg:not(:root) {
        overflow: hidden;
    }
</style>
