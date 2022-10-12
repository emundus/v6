<?php
defined('_JEXEC') or die;

header('Content-Type: text/html; charset=utf-8');

$user = JFactory::getUser();
$lang = JFactory::getLanguage();
$locallang = $lang->getTag();
$document->addStyleSheet("https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined" );

if ($locallang == "fr-FR") {
    setlocale(LC_TIME, 'fr', 'fr_FR', 'french', 'fra', 'fra_FRA', 'fr_FR.ISO_8859-1', 'fra_FRA.ISO_8859-1', 'fr_FR.utf8', 'fr_FR.utf-8', 'fra_FRA.utf8', 'fra_FRA.utf-8');
} else {
    setlocale(LC_ALL, 'en_GB');
}
$config = JFactory::getConfig();
$site_offset = $config->get('offset');

$campaigns = [];
if (in_array('current', $mod_em_campaign_list_tab) && !empty($currentCampaign)){
    $campaigns = array_merge($campaigns,$currentCampaign);
}
if (in_array('futur', $mod_em_campaign_list_tab) && !empty($futurCampaign)){
    $campaigns = array_merge($campaigns,$futurCampaign);
}
if (in_array('past', $mod_em_campaign_list_tab) && !empty($pastCampaign)){
    $campaigns = array_merge($campaigns,$pastCampaign);
}
if ($group_by == 'program') {
    usort($campaigns, function ($a, $b) {
        return strcmp($a->programme, $b->programme);
    });
}

require_once (JPATH_SITE.'/components/com_emundus/helpers/array.php');
$h_array  = new EmundusHelperArray();

$codes_filters = [];
if(!empty($codes)) {
    $codes_filters = explode(',', $codes);
}

$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$CurPageURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>

<div class="mod_emundus_campaign__intro">
    <?= $mod_em_campaign_intro; ?>
</div>


<form action="<?php echo $CurPageURL ?>" method="post" id="search_program">
    <?php if (sizeof($campaigns) == 0) : ?>
        <hr>
        <div class="em-card-white">
            <p class="em-text-neutral-900 em-h5"><?php echo JText::_('MOD_EM_CAMPAIGN_NO_CAMPAIGN') ?></p><br/>
            <p class="em-text-neutral-900 em-font-weight-500 em-mb-4"><?php echo JText::_('MOD_EM_CAMPAIGN_NO_CAMPAIGN_TEXT') ?></p>
            <p class="em-text-neutral-900"><?php echo JText::_('MOD_EM_CAMPAIGN_NO_CAMPAIGN_TEXT_2') ?></p><br/>
            <p class="em-text-neutral-900 em-font-weight-500 em-mb-4"><?php echo JText::_('MOD_EM_CAMPAIGN_NO_CAMPAIGN_TEXT_3') ?></p>
            <p class="em-text-neutral-900"><?php echo JText::_('MOD_EM_CAMPAIGN_NO_CAMPAIGN_TEXT_4') ?></p>
            <div class="em-flex-row-justify-end em-mt-32">
                <button class="em-secondary-button em-w-auto">
                    <?php echo JText::_('MOD_EM_CAMPAIGN_REGISTRATION_URL') ?>
                </button>
                <button class="em-primary-button em-w-auto em-ml-8">
                    <?php echo JText::_('MOD_EM_CAMPAIGN_LOGIN_URL') ?>
                </button>
            </div>
        </div>
    <?php else : ?>
    <div class="mod_emundus_campaign__content">
        <div class="mod_emundus_campaign__header">
            <div>
                <div class="em-flex-row">
                    <!-- BUTTONS -->
                    <div id="mod_emundus_campaign__header_sort" class="mod_emundus_campaign__header_filter em-border-neutral-400 em-neutral-800-color em-pointer" onclick="displaySort()">
                        <span class="material-icons-outlined">swap_vert</span>
                        <span class="em-ml-8"><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_SORT') ?></span>
                    </div>
                    <div id="mod_emundus_campaign__header_filter" class="mod_emundus_campaign__header_filter em-border-neutral-400 em-neutral-800-color em-pointer em-ml-8" onclick="displayFilters()">
                        <span class="material-icons-outlined">filter_list</span>
                        <span class="em-ml-8"><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER') ?></span>
                        <span id="mod_emundus_campaign__header_filter_count" class="mod_emundus_campaign__header_filter_count em-ml-8"></span>
                    </div>

                    <!-- TAGS ENABLED -->
                    <?php if ($mod_em_campaign_order == 'start_date' && $order == 'end_date') : ?>
                        <div class="mod_emundus_campaign__header_filter em-ml-8 em-border-neutral-400 em-neutral-800-color em-white-bg">
                            <span class="em-ml-8"><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_END_DATE_NEAR') ?></span>
                            <a class="em-flex-column em-ml-8 em-text-neutral-900 em-pointer" onclick="deleteSort(['order_date','order_time'])">
                                <span class="material-icons-outlined">close</span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if ($mod_em_campaign_order == 'end_date' && $order == 'start_date') : ?>
                        <div class="mod_emundus_campaign__header_filter em-ml-8 em-border-neutral-400 em-neutral-800-color em-white-bg">
                            <span class="em-ml-8"><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_START_DATE_NEAR') ?></span>
                            <a class="em-flex-column em-ml-8 em-text-neutral-900 em-pointer" onclick="deleteSort(['order_date','order_time'])">
                                <span class="material-icons-outlined">close</span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if ($group_by == 'program') : ?>
                        <div class="mod_emundus_campaign__header_filter em-ml-8 em-border-neutral-400 em-neutral-800-color em-white-bg">
                            <span><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_GROUP_BY_PROGRAM') ?></span>
                            <a class="em-flex-column em-ml-8 em-text-neutral-900 em-pointer" onclick="deleteSort(['group_by'])">
                                <span class="material-icons-outlined">close</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- SORT BLOCK -->
                <div class="mod_emundus_campaign__header_sort__values em-border-neutral-400 em-neutral-800-color" id="sort_block" style="display: none">
                    <?php if($mod_em_campaign_order == 'start_date') : ?>
                        <a onclick="filterCampaigns(['order_date','order_time'],['end_date','asc'])" class="em-text-neutral-900 em-pointer">
                            <?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_END_DATE_NEAR') ?>
                        </a>
                    <?php endif; ?>
                    <?php if($mod_em_campaign_order == 'end_date') : ?>
                        <a onclick="filterCampaigns(['order_date','order_time'],['start_date','asc'])" class="em-text-neutral-900 em-pointer">
                            <?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_START_DATE_NEAR') ?>
                        </a>
                    <?php endif; ?>
                    <a onclick="filterCampaigns('group_by','program')" class="em-text-neutral-900 em-pointer">
                        <?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_GROUP_BY_PROGRAM') ?>
                    </a>
                </div>

                <!-- FILTERS BLOCK -->
                <div class="mod_emundus_campaign__header_filter__values em-border-neutral-400 em-neutral-800-color" id="filters_block" style="display: none">
                    <a class="em-mb-8 em-flex-row em-font-size-14 em-blue-400-color em-pointer" onclick="addFilter()" title="<?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_ADD_FILTER') ?>">
                        <span class="material-icons-outlined em-font-size-14">add</span>
                        <?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_ADD_FILTER') ?>
                    </a>

                    <div id="filters_list">
                        <?php $i = 0; ?>
                        <?php foreach ($codes_filters as $key => $code) : ?>
                            <div class="mod_emundus_campaign__header_filter__grid" id="filter_<?php echo $i ?>">
                                <select onchange="setupFilter('<?php echo $i ?>')" id="select_filter_<?php echo $i ?>">
                                    <option value="0"><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_PLEASE_SELECT') ?></option>
                                    <option value="programme" selected><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_PROGRAMME') ?></option>
                                    <option value="category"><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_PROGRAMME_CATEGORY') ?></option>
                                    </select>
                                <select>
                                    <option value="="> <?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_IS') ?> </option>
                                </select>
                                <div id="filters_options_<?php echo $i ?>">
                                    <select id="filter_value_<?php echo $i ?>">
                                        <option value = 0></option>
                                        <?php foreach ($programs as $program) : ?>
                                            <option value=<?php echo $program['code'] ?> <?php if ($program['code'] == $code) : ?>selected<?php endif; ?>>
                                                <?php echo $program['label'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                        </select>
                                </div>
                                <div class="em-flex-row">
                                    <span class="material-icons-outlined em-font-size-16 em-red-500-color em-pointer" onclick="deleteFilter('<?php echo $i ?>')" title="<?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_DELETE') ?>">delete</span>
                                </div>
                            </div>
                            <?php $i++; ?>
                        <?php endforeach; ?>
                    </div>

                    <div>
                        <button class="btn btn-primary em-float-right" type="button" onclick="filterCampaigns()" title="<?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER') ?>">
                            <?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER') ?>
                        </button>
                    </div>
                </div>

            </div>

            <?php if ($mod_em_campaign_show_search): ?>
                <div class="mod_emundus_campaign__searchbar">
                    <label for="searchword" style="display: inline-block">
                        <input name="searchword" type="text" class="form-control"
                               placeholder="<?php echo JText::_('MOD_EM_CAMPAIGN_SEARCH') ?>" <?php if (isset($searchword) && !empty($searchword)) {
                                   echo "value=" . htmlspecialchars($searchword, ENT_QUOTES, 'UTF-8');
                               }; ?>>
                    </label>
                </div>
            <?php endif; ?>
        </div>

        <div class="mod_emundus_campaign__list em-mt-32">
            <div class="em-mb-24">
                <p class="mod_emundus_campaign__programme_cat_title"><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_CAMPAIGNS') ?></p>
                <hr style="margin-top: 8px">
            </div>

            <?php if (in_array('current', $mod_em_campaign_list_tab) && !empty($campaigns)) : ?>
                <div id="current" class="mod_emundus_campaign__list_items">
                        <?php
                        foreach ($campaigns as $result) {
                        ?>


                    <?php if(strtotime($now) > strtotime($result->end_date)) :  ?>
                    <div class="mod_emundus_campaign__list_content--closed mod_emundus_campaign__list_content em-border-neutral-300" onclick="window.location.href=<?php echo !empty($result->link) ? $result->link : JURI::base() . "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>">

                    <?php  else : ?>

                    <div class="mod_emundus_campaign__list_content em-border-neutral-300" onclick="window.location.href=<?php echo !empty($result->link) ? $result->link : JURI::base() . "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>">
                    <?php endif; ?>

                        <div class="mod_emundus_campaign__list_content_head <?php echo $mod_em_campaign_class; ?>">
                            <p class="mod_emundus_campaign__programme_tag"><?php  echo $result->programme; ?></p>
                            <a href="<?php echo !empty($result->link) ? $result->link : JURI::base() . "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>">
                                <p class="em-h6 mod_emundus_campaign__campaign_title"><?php echo $result->label; ?></p>
                            </a>

                            <div class="<?php echo $mod_em_campaign_class; ?> em-text-neutral-600 em-font-size-16">
                                <div>


                                    <?php if(strtotime($now) < strtotime($result->start_date)  ) : //pas commencé ?>

                                        <div class="mod_emundus_campaign__date">
                                            <span class="material-icons em-text-neutral-600 em-font-size-16">schedule</span>
                                            <p class="em-text-neutral-600 em-font-size-16"> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE'); ?></p>
                                            <span class="em-camp-start em-text-neutral-600"> <?php echo JFactory::getDate(new JDate($result->start_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(strtotime($now) > strtotime($result->end_date) ) :    //fini  ?>
                                       <div class="mod_emundus_campaign__date">
                                            <span class="material-icons em-text-neutral-600 em-font-size-16">alarm_off</span>
                                            <p class="em-text-neutral-600 em-font-size-16"><?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_CLOSED'); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if( strtotime($now) < strtotime($result->end_date)  && strtotime($now) > strtotime($result->start_date) ) : //en cours ?>
                                        <div class="mod_emundus_campaign__date">
                                            <span class="material-icons em-text-neutral-600 em-font-size-16">schedule</span>
                                            <p class="em-text-neutral-600 em-font-size-16"> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_END_DATE'); ?>
                                            </p>
                                            <span class="em-camp-end em-text-neutral-600"> <?php echo JFactory::getDate(new JDate($result->end_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                        </div>
                                    <?php endif; ?>


                                    <?php if ($mod_em_campaign_show_formation_start_date && $result->formation_start !== '0000-00-00 00:00:00') : ?>
                                        <div class="mod_emundus_campaign__date">
                                        <p class="em-text-neutral-600 em-font-size-16"><?php echo JText::_('MOD_EM_CAMPAIGN_FORMATION_START_DATE'); ?>:</p>
                                        <span class="em-formation-start em-text-neutral-600"><?php echo JFactory::getDate(new JDate($result->formation_start, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($mod_em_campaign_show_formation_end_date && $result->formation_end !== '0000-00-00 00:00:00') : ?>
                                        <div class="mod_emundus_campaign__date">
                                       <p class="em-text-neutral-600 em-font-size-16"><?php echo JText::_('MOD_EM_CAMPAIGN_FORMATION_END_DATE'); ?>:</p>
                                        <span class="em-formation-end em-text-neutral-600"><?php echo JFactory::getDate(new JDate($result->formation_end, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                       </div>
                                    <?php endif; ?>
                                    <?php if ($mod_em_campaign_show_admission_start_date && $result->admission_start_date !== '0000-00-00 00:00:00') : ?>
                                        <div class="mod_emundus_campaign__date">
                                        <p class="em-text-neutral-600 em-font-size-16"><?php echo JText::_('MOD_EM_CAMPAIGN_ADMISSION_START_DATE'); ?>:</p>
                                        <span class="em-formation-start em-text-neutral-600"><?php echo JFactory::getDate(new JDate($result->admission_start_date, $site_offset))->format($mod_em_campaign_date_format); ?></span></div>
                                    <?php endif; ?>

                                    <?php if ($mod_em_campaign_show_admission_end_date && $result->admission_end_date !== '0000-00-00 00:00:00') : ?>
                                        <div class="mod_emundus_campaign__date">
                                        <p class="em-text-neutral-600 em-font-size-16"><?php echo JText::_('MOD_EM_CAMPAIGN_ADMISSION_END_DATE'); ?>:</p>
                                        <span class="em-formation-end em-text-neutral-600"><?php echo JFactory::getDate(new JDate($result->admission_end_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?= (!empty($mod_em_campaign_show_timezone) && !(strtotime($now) > strtotime($dteEnd)) ) ? JText::_('MOD_EM_CAMPAIGN_TIMEZONE') . $offset : ''; ?>
                                </div>
                            </div>

                            <hr>

                            <p class="mod_emundus_campaign__list_content_resume em-text-neutral-600 em-font-size-16">
                                <?php
                                $text = '';
                                $textprog = '';
                                $textcamp = '';
                                if ($showcampaign) {
                                    $textcamp = $result->short_description;
                                }
                                echo $textcamp;
                                ?>
                            </p>
                        </div>
                    </div>
                <?php } ?>
                </div>
                <div class="pagination"></div>
            <?php endif; ?>

        </div><!-- Close tab-content -->
    </div>
    <?php endif; ?>
</form>
<script type="text/javascript">
    jQuery(document).ready(function () {
        var tabsidshow = jQuery.cookie("tabactive");
        if (tabsidshow === undefined) {
            jQuery('#tabslist a[href="#current"]').tab('show');
            jQuery('#current').addClass('in');
            jQuery.cookie("tabactive", "current");
        } else {
            jQuery('#tabslist a[href="#' + tabsidshow + '"]').tab('show');
            jQuery('#' + tabsidshow).addClass('in');
        }

        jQuery('#tabslist a').click(function (e) {
            e.preventDefault();
            var id = jQuery(this).attr("href").substr(1);
            jQuery.cookie("tabactive", id);
            jQuery(this).tab('show');

            // This timeout waits for the animation to complete before resizing the label.
            setTimeout(function () {
                if (jQuery(window).width() > 768) {
                    jQuery('.position-me').each(function () {
                        var h = jQuery(this).parent().parent().height() - 23;
                        jQuery(this).width(h);
                    });
                } else if (jQuery(window).width() == 768) {
                    jQuery('.position-me').each(function () {
                        var h = jQuery(this).parent().parent().height() - 38;
                        jQuery(this).width(h);
                    });
                }
            }, 200);

        });

        if (jQuery(window).width() > 768) {
            jQuery('.position-me').each(function () {
                var h = jQuery(this).parent().parent().height() - 23;
                jQuery(this).width(h);
            });
        } else if (jQuery(window).width() == 768) {
            jQuery('.position-me').each(function () {
                var h = jQuery(this).parent().parent().height() - 38;
                jQuery(this).width(h);
            });
        }

        setTimeout(() => {
            let sort_button = document.getElementById('mod_emundus_campaign__header_sort');
            if(typeof sort_button !== 'undefined'){
                document.getElementById('filters_block').style.marginLeft = (sort_button.offsetWidth + 8) +'px';
            }
        },1000);

        let filter_existing = document.querySelectorAll("div[id^='filter_']");
        document.getElementById('mod_emundus_campaign__header_filter_count').innerHTML = filter_existing.length;
    });

    function displaySort(){
        let sort = document.getElementById('sort_block');
        if(sort.style.display === 'none'){
            sort.style.display = 'flex';
        } else {
            sort.style.display = 'none';
        }
    }

    function displayFilters(){
        let filters = document.getElementById('filters_block');
        if(filters.style.display === 'none'){
            filters.style.display = 'flex';
        } else {
            filters.style.display = 'none';
        }
    }

    function setupFilter(index){
        let type = document.getElementById('select_filter_' + index).value
        let html = '';

        switch(type){
            case 'programme':
                html = '<select id="filter_value_'+index+'"> ' +
                    '<option value = 0></option>' +
                    <?php foreach ($programs as $program) : ?>
                    "<option value=\"<?php echo $program['code'] ?>\"><?php echo $program['label'] ?></option>" +
                    <?php endforeach; ?>
                    '</select>';
                break;
            default:
                break;
        }

        document.getElementById('filters_options_'+index).innerHTML = html;
    }

    function addFilter(){
        let index = 1;
        let filter_existing = document.querySelectorAll("div[id^='filter_']");
        let last_filter = filter_existing[filter_existing.length -1];
        if(typeof last_filter !== 'undefined'){
            index = last_filter.id.split('_');
            index = parseInt(index[index.length -1]) + 1;
        }

        let html = '<div class="mod_emundus_campaign__header_filter__grid" id="filter_'+index+'"> ' +
            '<select onchange="setupFilter('+index+')" id="select_filter_'+index+'"> ' +
            '<option value="0"><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_PLEASE_SELECT') ?></option> ' +
            '<option value="programme"><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_PROGRAMME') ?></option> ' +
            '<option value="category"><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_PROGRAMME_CATEGORY') ?></option> ' +
            '</select> ' +
            '<span><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_IS') ?></span> ' +
            '<div id="filters_options_'+index+'"></div>' +
            '<div class="em-flex-row">' +
                '<span class="material-icons-outlined em-font-size-16 em-red-500-color em-pointer" onclick="deleteFilter('+index+')">delete</span>' +
            '</div>' +
            '</div>';
        document.getElementById('filters_list').insertAdjacentHTML('beforeend',html);
    }

    function deleteFilter(index){
        document.getElementById('filter_' + index).remove();
    }

    function filterCampaigns(type = '', value = '') {
        let filters = document.querySelectorAll("select[id^='select_filter_']");
        let current_url = window.location.href;
        if(current_url.indexOf('?') === -1) {
            current_url += '?';
        }

        let existing_filters = current_url.split('&');
        let codes = [];


        existing_filters.forEach((filter,key) => {
            if(filter.indexOf('code') !== -1){
                existing_filters.splice(key,1);
            }
            if(type !== '' && !Array.isArray(type)){
                if(filter.indexOf(type) !== -1){
                    existing_filters.splice(key,1);
                }
            } else if(Array.isArray(type)){
                type.forEach((elt) => {
                    if(filter.indexOf(elt) !== -1){
                        existing_filters.splice(key,1);
                    }
                })
            }
        })

        let program_filter = '';
        let type_filter = '';
        filters.forEach((filter) => {
            let type = filter.value;
            let index = filter.id.split('_');
            index = parseInt(index[index.length -1]);

            switch (type){
                case 'programme':
                    let value = document.getElementById('filter_value_' + index).value;
                    if(value != 0 && value != ''){
                        codes.push(value);
                    }
                    break;
                default:
                    break;
            }
        })

        let new_url = existing_filters.join('&');
        if(codes.length > 0){
            program_filter = '&code=';
            program_filter += codes.join(',');
            new_url += program_filter;
        }
        if(type !== ''  && !Array.isArray(type)){
            type_filter = '&'+type+'=';
            type_filter += value;
            new_url += type_filter;
        } else if(Array.isArray(type)){
            type.forEach((elt,index) => {
                type_filter += '&'+elt+'=';
                type_filter += value[index];
            })
            new_url += type_filter;
        }

        window.location.href = new_url;
    }

    function deleteSort(sort){
        let current_url = window.location.href;
        let existing_filters = current_url.split('&');

        existing_filters.forEach((filter,key) => {
            sort.forEach((elt) => {
                if (filter.indexOf(elt) !== -1) {
                    existing_filters.splice(key, 1);
                }
            });
        });

        window.location.href = existing_filters.join('&');
    }

    document.addEventListener('click', function (e) {
        let sort = document.getElementById('sort_block');
        let filters = document.getElementById('filters_block');
        let clickInsideModule = false;

        if(sort.style.display === 'flex') {
            e.composedPath().forEach((pathElement) => {
                if (pathElement.id == "sort_block" || pathElement.id == "mod_emundus_campaign__header_sort") {
                    clickInsideModule = true;
                }
            });

            if (!clickInsideModule) {
                sort.style.display = 'none';
            }
        }

        clickInsideModule = false;
        if(filters.style.display === 'flex') {
            e.composedPath().forEach((pathElement) => {
                if (pathElement.id == "filters_block" || pathElement.id == "mod_emundus_campaign__header_filter") {
                    clickInsideModule = true;
                }
            });

            if (!clickInsideModule) {
                filters.style.display = 'none';
            }
        }
    });

</script>
