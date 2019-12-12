<?php
defined('_JEXEC') || die;
//JHTML::_('behavior.calendar');
JHTML::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', '.chzn-select');
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

?>
<script>
    window.catDatas = <?php echo json_encode($this->categories);?>;
    window.catTags = <?php echo json_encode($this->catTags);?> ;
    var filterData = null;
    jQuery(document).ready(function () {
        jQuery('#filter_catid_chzn').removeAttr('style');
        jQuery('.chzn-search input').removeAttr('readonly');
        <?php if ($layoutParams->get('tag_filter', 1) &&
            $layoutParams->get('display_tag', 'searchbox') === 'searchbox' ) { ?>
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
            initialTags: defaultTags
        });
        <?php } ?>

        <?php if (!empty($filters)) { ?>
        filterData = <?php echo json_encode($filters);?>;
        <?php } ?>

        window.history.pushState(filterData, "", window.location);
    });
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" id="adminForm" name="adminForm" class="dropfiles_search" method="post">
    <input type="hidden" value='com_dropfiles' name="option"/>
    <input type="hidden" value='frontsearch.search' name="task"/>
    <?php if ($this->Itemid) { ?>
        <input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>"/>
    <?php } ?>

    <input class="dropfiles_cat_type" type="hidden" name="cat_type" value="<?php echo $filters['cattype']; ?>"/>

    <div id="loader" style="display:none"><img
            src="<?php echo JURI::root(); ?>components/com_dropfiles/assets/images/spinner.gif"/></div>

    <div class="box-search-filter">
        <div class="only-file input-group clearfix">
            <input type="text" class="pull-left required" name="q" id="txtfilename"
                   placeholder="<?php echo JText::_('COM_DROPFILES_SEARCH_SEARCH_KEYWORD'); ?>"
                   placeholder="<?php echo JText::_('COM_DROPFILES_SEARCH_SEARCH_KEYWORD'); ?>"
                   value="<?php echo isset($filters['q']) ? $filters['q'] : ''; ?>"/>
            <button id="btnsearch" class="pull-left"><i class="dropfiles-icon-search"></i></button>
        </div>
        <?php if ($layoutParams->get('show_filters', 1)) : ?>
            <div class="by-feature feature-border">
                <div class="top clearfix">
                    <div class="pull-left"><strong><?php echo JText::_('COM_DROPFILES_SEARCH_FILTERS') ?></strong>
                    </div>
                    <div class="pull-right"><i class="feature-toggle" class="feature-toggle-up"></i></div>
                </div>
                <div class="feature clearfix row-fluid">
                    <?php if ($layoutParams->get('cat_filter', 1)) : ?>
                        <div class="span3 categories-filtering">
                            <h4><?php echo JText::_('COM_DROPFILES_SEARCH_CATEGORIES_FILTERING'); ?></h4>
                            <div class="ui-widget">
                                <select id="filter_catid" class="chzn-select" name="catid">
                                    <option value="">
                                        <?php echo ' ' . JText::_('COM_DROPFILES_SEARCH_SELECT_ONE'); ?>
                                    </option>
                                    <?php
                                    if (count($this->categories) > 0) {
                                        foreach ($this->categories as $key => $category) {
                                            if ((int) $filters['catid'] === (int) $category->id) {
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
                                    ?>

                                </select>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($layoutParams->get('tag_filter', 1) &&
                        $layoutParams->get('display_tag', 'searchbox') === 'searchbox') : ?>
                        <div class="span3 tags-filtering">
                            <h4><?php echo JText::_('COM_DROPFILES_SEARCH_TAGS_FILTERING'); ?></h4>
                            <input type="text" id="input_tags" name="ftags" class="tagit"
                                   value="<?php echo isset($filters['ftags']) ? $filters['ftags'] : ''; ?>"/>
                        </div>
                    <?php endif; ?>

                    <?php if ($layoutParams->get('creation_date', 1)) : ?>
                        <div class="span3 creation-date">
                            <h4><?php echo JText::_('COM_DROPFILES_SEARCH_CREATION_DATE'); ?></h4>
                            <div>
                                <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_SEARCH_FROM'); ?> </span>
                                <input class="input-date" type="text" data-min="cfrom" name="cfrom"
                                       value="<?php echo isset($filters['cfrom']) ? $filters['cfrom'] : ''; ?>"
                                       id="cfrom"/>
                                <i data-id="cfrom" class="icon-date icon-calendar"></i></div>
                            <div>
                                <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_SEARCH_TO'); ?></span>
                                <input class="input-date" data-min="cfrom" type="text" name="cto" id="cto"
                                       value="<?php echo isset($filters['cto']) ? $filters['cto'] : ''; ?>"/>
                                <i data-id="cto" data-min="cfrom" class="icon-date icon-calendar"></i>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($layoutParams->get('update_date', 1)) : ?>
                        <div class="span3 update-date">
                            <h4><?php echo JText::_('COM_DROPFILES_SEARCH_UPDATE_DATE'); ?></h4>
                            <div>
                                <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_SEARCH_FROM'); ?> </span>
                                <input class="input-date" type="text" data-min="ufrom"
                                       value="<?php echo isset($filters['ufrom']) ? $filters['ufrom'] : ''; ?>"
                                       name="ufrom" id="ufrom"/>
                                <i data-id="ufrom" class="icon-date icon-calendar"></i></div>
                            <div>
                                <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_SEARCH_TO'); ?> </span>
                                <input class="input-date" type="text" data-min="ufrom"
                                       value="<?php echo isset($filters['uto']) ? $filters['uto'] : ''; ?>"
                                       name="uto" id="uto"/>
                                <i data-id="uto" data-min="ufrom" class="icon-date icon-calendar"></i></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($layoutParams->get('tag_filter', 1) &&
                        $layoutParams->get('display_tag', 'searchbox') === 'checkboxes') : ?>
                        <div class="clearfix row-fluid">
                            <div class="span11 chk-tags-filtering">
                                <h4 style="text-align:left">
                                    <?php echo JText::_('COM_DROPFILES_SEARCH_TAGS_FILTERING'); ?>
                                </h4>
                                <input type="hidden" id="input_tags" name="ftags" class="input_tags"
                                       value="<?php isset($filters['ftags']) ? $filters['ftags'] : ''; ?>"/>
                                <?php $allTags = str_replace(array('[', ']', '"'), '', $this->allTagsFiles);
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
