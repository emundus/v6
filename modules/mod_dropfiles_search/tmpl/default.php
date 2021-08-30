<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

//-- No direct access
defined('_JEXEC') || die;

if (!isset($filters)) {
    $filters = array();
}
if (!isset($categories)) {
    $categories = null;
}
if (!isset($comParams)) {
    $comParams = null;
}

if (!isset($params)) {
    $params = null;
}

$excludeCategory = $comParams->get('ref_exclude_category_id');
$excludeCategory = (isset($excludeCategory)) ? $excludeCategory : array();
?>
<div class="mod_dropfiles_search">
    <form action="<?php echo JRoute::_('index.php?option=com_dropfiles'); ?>" class="" name="mod_dropfiles_search"
          method="post">
        <input type="hidden" value='frontsearch.search' name="task"/>
        <input class="dropfiles_cat_type" type="hidden" name="cat_type" value="<?php echo isset($filters['cattype']) ? $filters['cattype'] : ''; ?>"/>
        <?php if (isset($mitemid)) : ?>
        <input type="hidden" name="Itemid" value="<?php echo $mitemid; ?>" />
        <?php endif; ?>
        <?php
        $search_class = '';
        if (intval($params->get('show_filters', 1)) === 1) {
            if (intval($params->get('cat_filter', 1)) === 0) {
                $search_class = 'fullwidth';
            }
        } else {
            $search_class = 'fullwidth';
        }
        ?>
        <div class="box-search-filter module-box-search-filter">
            <div class="searchSection">
                <?php if ($params->get('show_filters', 1) && $params->get('cat_filter', 1)) : ?>
                    <div class="categories-filtering">
                        <img src="<?php echo JURI::root(); ?>components/com_dropfiles/assets/images/menu.svg" class="material-icons cateicon"/>
                        <div class="cate-lab" style="text-transform: uppercase"><?php echo JText::_('COM_DROPFILES_SEARCH_FILES_CATEGORY_FILTERING'); ?></div>
                        <div class="ui-widget" id="dropfiles-listCate" style="display: none">
                            <select id="search_catid" class="chzn-select" name="catid">
                                <option value="">
                                    <?php echo ' ' . JText::_('COM_DROPFILES_SEARCH_SELECT_ONE'); ?>
                                </option>
                                <?php
                                if (count($categories) > 0) {
                                    foreach ($categories as $key => $category) {
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
                                            if (isset($filters['catid']) && (int) $filters['catid'] === $category->id) {
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
                                            if (isset($filters['catid']) && (int) $filters['catid'] === $category->id) {
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
                                if (count($categories) > 0) { ?>
                                    <li class="search-cate">
                                        <input class="qCatesearch" id="dropfilesCategorySearch" data-id="" placeholder="<?php echo JText::_('COM_DROPFILES_SEARCH_QUICK_CATEGORY_SEARCH'); ?>">
                                    </li>
                                    <li class="cate-item" data-catid="">
                                        <span class="dropfiles-toggle-expand"></span>
                                        <span class="dropfiles-folder-search"></span>
                                        <label><?php echo JText::_('COM_DROPFILES_SEARCH_CATEGORY_ALL');?></label>
                                    </li>
                                    <?php
                                    foreach ($categories as $key => $category) {
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
                                            if (isset($filters['catid']) && (int) $filters['catid'] === $category->id) {
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
                                            if (isset($filters['catid']) && (int) $filters['catid'] === $category->id) {
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
                    <input type="text" class="pull-left required" name="q" id="dropfiles_q"
                           placeholder="<?php echo JText::_('COM_DROPFILES_SEARCH_SEARCH_KEYWORD'); ?>"
                           value="<?php echo isset($filters['q']) ? $filters['q'] : ''; ?>"/>
                    <button id="mod_btnsearch" class="pull-left"><i class="dropfiles-icon-search"></i></button>
                </div>
            </div>

            <?php if ($params->get('show_filters', 1)) : ?>
                <div class="by-feature feature-border" id="Category_container">
                    <?php if (intval($params->get('tag_filter', 1)) === 1 &&
                        intval($params->get('creation_date', 1)) === 1 &&
                        intval($params->get('update_date', 1)) === 1) : ?>
                        <div class="dropfiles_tab">
                            <button class="tablinks active" onclick="openSearchfilter(event, 'Filter')"><?php echo JText::_('COM_DROPFILES_SEARCH_FILTER_TAB'); ?></button>
                            <button class="tablinks" onclick="openSearchfilter(event, 'Tags')"><?php echo JText::_('COM_DROPFILES_SEARCH_TAGS_TAB'); ?></button>
                            <?php if (intval($params->get('creation_date', 1)) === 1 ||
                            intval($params->get('update_date', 1)) === 1 ||
                            ($params->get('tag_filter', 1) && $params->get('display_tag', 'searchbox') === 'searchbox') ||
                            ($params->get('tag_filter', 1) && $params->get('display_tag', 'searchbox') === 'checkboxes')) : ?>
                            <span class="feature-toggle toggle-arrow-up-alt"></span>
                            <?php endif;?>
                        </div>
                    <?php endif;?>
                    <?php if (intval($params->get('creation_date', 1)) === 1 ||
                        intval($params->get('update_date', 1)) === 1 ||
                        ($params->get('tag_filter', 1) && $params->get('display_tag', 'searchbox') === 'searchbox') ||
                        ($params->get('tag_filter', 1) && $params->get('display_tag', 'searchbox') === 'checkboxes')) : ?>
                    <div class="top clearfix">
                        <div class="pull-left">
                            <p class="filter-lab" style="text-transform: uppercase"><?php echo JText::_('COM_DROPFILES_SEARCH_FILTERS') ?></p>
                        </div>
                        <div class="pull-right"><span class="feature-toggle toggle-arrow-up-alt"></span></div>
                    </div>
                    <?php endif;?>
                    <div class="feature clearfix row-fluid dropfiles_tabcontainer">

                        <!-- Tab content -->
                        <div id="Filter" class="dropfiles_tabcontent active">
                            <?php
                            $date_class= 'date-filter';
                            if ((int) $params->get('creation_date', 1) === 0 && (int) $params->get('update_date', 1) === 0) {
                                $date_class= 'dropfiles-date-hidden';
                            }
                            ?>

                            <div class="<?php echo $date_class; ?>">
                                <?php if ($params->get('creation_date', 1)) : ?>
                                    <div class="creation-date">
                                        <p class="date-info" style="text-transform: uppercase"><?php echo JText::_('COM_DROPFILES_SEARCH_CREATION_DATE'); ?></p>
                                        <div class="create-date-container">
                                            <div>
                                                <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_SEARCH_FROM'); ?> </span>
                                                <div class="input-icon-date">
                                                    <input class="input-date" type="text" data-min="cfrom" name="cfrom"
                                                           value="<?php echo isset($filters['cfrom']) ? $filters['cfrom'] : ''; ?>"
                                                           id="mod_cfrom"/>
                                                    <i data-id="mod_cfrom" class="icon-date icon-calendar"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_SEARCH_TO'); ?></span>
                                                <div class="input-icon-date">
                                                    <input class="input-date" data-min="cfrom" type="text" name="cto" id="mod_cto"
                                                           value="<?php echo isset($filters['cto']) ? $filters['cto'] : ''; ?>"/>
                                                    <i data-id="mod_cto" data-min="cfrom" class="icon-date icon-calendar"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($params->get('update_date', 1)) : ?>
                                    <div class="update-date">
                                        <p class="date-info" style="text-transform: uppercase"><?php echo JText::_('COM_DROPFILES_SEARCH_UPDATE_DATE'); ?></p>
                                        <div class="update-date-container">
                                            <div>
                                                <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_SEARCH_FROM'); ?> </span>
                                                <div class="input-icon-date">
                                                    <input
                                                        class="input-date" type="text" data-min="ufrom"
                                                        value="<?php
                                                        if (isset($filters['ufrom'])) {
                                                            echo $filters['ufrom'];
                                                        } ?>"
                                                        name="ufrom" id="mod_ufrom"/>
                                                    <i data-id="mod_ufrom" class="icon-date icon-calendar"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_SEARCH_TO'); ?> </span>
                                                <div class="input-icon-date">
                                                    <input
                                                        class="input-date" type="text" data-min="ufrom"
                                                        value="<?php
                                                        if (isset($filters['uto'])) {
                                                            echo $filters['uto'];
                                                        } ?>"
                                                        name="uto" id="mod_uto"/>
                                                    <i data-id="mod_uto" data-min="ufrom" class="icon-date icon-calendar"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>

                        <div id="Tags" class="dropfiles_tabcontent mobilecontent">
                            <?php if ($params->get('tag_filter', 1) &&
                                $params->get('display_tag', 'searchbox') === 'searchbox') : ?>
                                <div class="tags-filtering">
                                    <p class="tags-info" style="text-align:left; text-transform: uppercase"><?php echo JText::_('COM_DROPFILES_SEARCH_TAGS_FILTERING'); ?></p>
                                    <input type="text" id="filter_tags" name="ftags" class="tagit input_tags"
                                           value="<?php echo isset($filters['ftags']) ? $filters['ftags'] : ''; ?>"/>
                                </div>
                                <span class="error-message"><?php echo JText::_('COM_DROPFILES_SEARCH_NO_TAG_MATCHING'); ?></span>
                            <?php endif; ?>

                            <?php if ($params->get('tag_filter', 1) &&
                                $params->get('display_tag', 'searchbox') === 'checkboxes') : ?>
                                <div class="clearfix row-fluid">
                                    <div class="span12 chk-tags-filtering">
                                        <p class="tags-info" style="text-align:left; text-transform: uppercase"><?php echo JText::_('COM_DROPFILES_SEARCH_TAGS_FILTERING'); ?></p>
                                        <input type="hidden" id="input_tags" name="ftags" class="tagit input_tags"
                                               value="<?php echo isset($filters['ftags']) ? $filters['ftags'] : ''; ?>"/>
                                        <?php $allTags = str_replace(array('[', ']', '"'), '', $allTagsFiles);
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

                            <?php if (intval($params->get('tag_filter', 1)) === 0) : ?>
                                <div class="no-tags"></div>
                            <?php endif; ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="box-btngroup-below">
                            <a class="btnsearchbelow" type="reset"
                               id="mod_btnReset"><?php echo JText::_('COM_DROPFILES_SEARCH_BUTTON_RESET'); ?></a>
                            <button id="mod_btnsearchbelow" class="btnsearchbelow" type="submit"><i
                                    class="dropfiles-icon-search"></i>
                                <?php echo JText::_('COM_DROPFILES_SEARCH_BUTTON_SEARCH'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
    var search_date_format = "<?php echo $comParams->get('date_format', 'Y-m-d');?>";
    window.catDatas = <?php echo json_encode($categories);?>;
    window.catTags = <?php echo json_encode($catTags);?> ;
    var availTags = [];
    <?php if (!empty($allTagsFiles)) : ?>
    availTags = <?php echo $allTagsFiles; ?>;
    <?php endif; ?>
    jQuery(document).ready(function ($) {

        $("#filter_tags").tagit({
            availableTags: availTags,
            allowSpaces: true,
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

        $("#mod_btnReset").click(function (e) {
            e.preventDefault();
            resetFilters($(this).parents(".mod_dropfiles_search"));
        });

    });
</script>
