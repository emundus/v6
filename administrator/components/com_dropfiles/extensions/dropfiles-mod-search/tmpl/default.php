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
?>
<div class="mod_dropfiles_search">
    <form action="<?php echo JRoute::_('index.php?option=com_dropfiles'); ?>" class="" name="mod_dropfiles_search"
          method="post">
        <input type="hidden" value='frontsearch.search' name="task"/>
        <input class="dropfiles_cat_type" type="hidden" name="cat_type" value="<?php echo isset($filters['cattype']) ? $filters['cattype'] : ''; ?>"/>
        <?php if (isset($mitemid)) : ?>
        <input type="hidden" name="Itemid" value="<?php echo $mitemid; ?>" />
        <?php endif; ?>
        <div class="box-search-filter">
            <div class="only-file input-group clearfix">
                <input type="text" class="pull-left required" name="q" id="dropfiles_q"
                       placeholder="<?php echo JText::_('COM_DROPFILES_SEARCH_SEARCH_KEYWORD'); ?>"
                       value="<?php echo isset($filters['q']) ? $filters['q'] : ''; ?>"/>
                <button id="mod_btnsearch" class="pull-left"><i class="dropfiles-icon-search"></i></button>
            </div>
            <?php if ($params->get('show_filters', 1)) : ?>
                <div class="by-feature feature-border">
                    <div class="top clearfix">
                        <div class="pull-left"><strong><?php echo JText::_('COM_DROPFILES_SEARCH_FILTERS') ?></strong>
                        </div>
                        <div class="pull-right"><i class="feature-toggle" class="feature-toggle-up"></i></div>
                    </div>
                    <div class="feature clearfix row-fluid">
                        <?php if ($params->get('cat_filter', 1)) : ?>
                            <div class="categories-filtering">
                                <h4><?php echo JText::_('COM_DROPFILES_SEARCH_CATEGORIES_FILTERING'); ?></h4>
                                <div class="ui-widget">
                                    <select id="search_catid" class="chzn-select" name="catid">
                                        <option value="">
                                            <?php echo ' ' . JText::_('COM_DROPFILES_SEARCH_SELECT_ONE'); ?>
                                        </option>
                                        <?php
                                        if (count($categories) > 0) {
                                            foreach ($categories as $key => $category) {
                                                if ((int) $filters['catid'] === (int) $category->id) {
                                                    echo '<option selected="selected" data-type="'
                                                        . $category->type . '" value="' . $category->id . '">'
                                                        . str_repeat('-', $category->level - 1)
                                                        . ' ' . $category->title . '</option>';
                                                } else {
                                                    echo '<option data-type="' . $category->type . '" value="'
                                                        . $category->id . '">'
                                                        . str_repeat('-', $category->level - 1)
                                                        . ' ' . $category->title . '</option>';
                                                }
                                            }
                                        }
                                        ?>

                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($params->get('tag_filter', 1) &&
                            $params->get('display_tag', 'searchbox') === 'searchbox') : ?>
                            <div class="tags-filtering">
                                <h4><?php echo JText::_('COM_DROPFILES_SEARCH_TAGS_FILTERING'); ?></h4>
                                <input type="text" id="filter_tags" name="ftags" class="tagit"
                                       value="<?php echo isset($filters['ftags']) ? $filters['ftags'] : ''; ?>"/>
                            </div>
                        <?php endif; ?>

                        <?php if ($params->get('creation_date', 1)) : ?>
                            <div class="creation-date">
                                <h4><?php echo JText::_('COM_DROPFILES_SEARCH_CREATION_DATE'); ?></h4>
                                <div><span class="lbl-date">
                                        <?php echo JText::_('COM_DROPFILES_SEARCH_FROM'); ?> </span>
                                    <input class="input-date" type="text" data-min="cfrom" name="cfrom"
                                           value="<?php echo isset($filters['cfrom']) ? $filters['cfrom'] : ''; ?>"
                                           id="mod_cfrom"/>
                                    <i data-id="mod_cfrom" class="icon-date icon-calendar"></i></div>
                                <div><span class="lbl-date">
                                        <?php echo JText::_('COM_DROPFILES_SEARCH_TO'); ?></span>
                                    <input class="input-date" data-min="cfrom" type="text" name="cto" id="mod_cto"
                                           value="<?php echo isset($filters['cto']) ? $filters['cto'] : ''; ?>"/>
                                    <i data-id="mod_cto" data-min="cfrom" class="icon-date icon-calendar"></i>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($params->get('update_date', 1)) : ?>
                            <div class="update-date">
                                <h4><?php echo JText::_('COM_DROPFILES_SEARCH_UPDATE_DATE'); ?></h4>
                                <div><span class="lbl-date">
                                        <?php echo JText::_('COM_DROPFILES_SEARCH_FROM'); ?>
                                    </span><input
                                        class="input-date" type="text" data-min="ufrom"
                                        value="<?php
                                        if (isset($filters['ufrom'])) {
                                            echo $filters['ufrom'];
                                        } ?>"
                                        name="ufrom" id="mod_ufrom"/>
                                    <i data-id="mod_ufrom" class="icon-date icon-calendar"></i></div>
                                <div><span class="lbl-date">
                                        <?php echo JText::_('COM_DROPFILES_SEARCH_TO'); ?>
                                    </span><input
                                        class="input-date" type="text" data-min="ufrom"
                                        value="<?php
                                        if (isset($filters['uto'])) {
                                            echo $filters['uto'];
                                        } ?>"
                                        name="uto" id="mod_uto"/>
                                    <i data-id="mod_uto" data-min="ufrom" class="icon-date icon-calendar"></i>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="clearfix"></div>

                        <?php if ($params->get('tag_filter', 1) &&
                            $params->get('display_tag', 'searchbox') === 'checkboxes') : ?>
                            <div class="clearfix row-fluid">
                                <div class="span11 chk-tags-filtering">
                                    <h4 style="text-align:left">
                                        <?php echo JText::_('COM_DROPFILES_SEARCH_TAGS_FILTERING'); ?>
                                    </h4>
                                    <input type="hidden" id="input_tags" name="ftags" class="tagit input_tags"
                                           value="<?php echo isset($filters['ftags']) ? $filters['ftags'] : ''; ?>"/>
                                    <?php $allTags = str_replace(array('[', ']', '"'), '', $allTagsFiles);
                                    if ($allTags !== '') {
                                        $arrTags = explode(',', $allTags);
                                        echo '<ul>';
                                        foreach ($arrTags as $key => $tag) { ?>
                                            <li>
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
            allowSpaces: true
        });

        $("#mod_btnReset").click(function (e) {
            e.preventDefault();
            resetFilters($(this).parents(".mod_dropfiles_search"));
        });

    });
</script>
