<?php
defined('_JEXEC') or die;

header('Content-Type: text/html; charset=utf-8');

$user = JFactory::getUser();
$lang = JFactory::getLanguage();
$locallang = $lang->getTag();
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

?>

<div class="mod_emundus_campaign__intro">
    <?= $mod_em_campaign_intro; ?>
</div>


<form action="index.php" method="post" id="search_program">
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
                    <div class="mod_emundus_campaign__header_filter em-border-neutral-400 em-neutral-800-color em-pointer" onclick="displayFilters()">
                        <span class="material-icons-outlined">filter_list</span>
                        <span class="em-ml-8"><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER') ?></span>
                    </div>
                    <?php if ($mod_em_campaign_order == 'start_date' && $order == 'end_date') : ?>
                        <div class="mod_emundus_campaign__header_filter em-ml-8 em-border-neutral-400 em-neutral-800-color em-white-bg">
                            <span class="em-ml-8"><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_END_DATE_NEAR') ?></span>
                            <a class="em-flex-column em-ml-8 em-text-neutral-900" href="index.php?group_by=<?php echo $group_by ?>">
                                <span class="material-icons-outlined">close</span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if ($mod_em_campaign_order == 'end_date' && $order == 'start_date') : ?>
                        <div class="mod_emundus_campaign__header_filter em-ml-8 em-border-neutral-400 em-neutral-800-color em-white-bg">
                            <span class="em-ml-8"><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_START_DATE_NEAR') ?></span>
                            <a class="em-flex-column em-ml-8 em-text-neutral-900" href="index.php?group_by=<?php echo $group_by ?>">
                                <span class="material-icons-outlined">close</span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if ($group_by == 'program') : ?>
                        <div class="mod_emundus_campaign__header_filter em-ml-8 em-border-neutral-400 em-neutral-800-color em-white-bg">
                            <span><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_GROUP_BY_PROGRAM') ?></span>
                            <a class="em-flex-column em-ml-8 em-text-neutral-900" href="index.php?order_date=<?php echo $order ?>&order_time=<?php echo $ordertime ?>">
                                <span class="material-icons-outlined">close</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mod_emundus_campaign__header_filter__values em-border-neutral-400 em-neutral-800-color" id="filters_block" style="display: none">
                    <?php if($mod_em_campaign_order == 'start_date') : ?>
                        <a href="index.php?order_date=end_date&order_time=asc&group_by=<?php echo $group_by ?>" class="em-text-neutral-900">
                            <?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_END_DATE_NEAR') ?>
                        </a>
                    <?php endif; ?>
                    <?php if($mod_em_campaign_order == 'end_date') : ?>
                        <a href="index.php?order_date=start_date&order_time=asc&group_by=<?php echo $group_by ?>" class="em-text-neutral-900">
                            <?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_START_DATE_NEAR') ?>
                        </a>
                    <?php endif; ?>
                    <a href="index.php?order_date=<?php echo $order ?>&order_time=<?php echo $ordertime ?>&group_by=program" class="em-text-neutral-900">
                        <?php echo JText::_('MOD_EM_CAMPAIGN_LIST_FILTER_GROUP_BY_PROGRAM') ?>
                    </a>
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
                <p class="em-text-neutral-900 em-h5"><?php echo JText::_('MOD_EM_CAMPAIGN_LIST_CAMPAIGNS') ?></p>
                <hr style="margin-top: 8px">
            </div>

            <?php if (in_array('current', $mod_em_campaign_list_tab) && !empty($campaigns)) : ?>
                <div id="current" class="mod_emundus_campaign__list_items">
                        <?php
                        foreach ($campaigns as $result) {
                            $dteStart = new DateTime($now);
                            $dteEnd = new DateTime($result->end_date);
                            $dteDiff = $dteStart->diff($dteEnd);
                            $j = $dteDiff->format("%a");
                            $h = $dteDiff->format("%H");

                            switch ($order) {
                                case "start_date":
                                    $month = ($result->start_date !== '0000-00-00 00:00:00') ? JFactory::getDate(new JDate($result->start_date, $site_offset))->format("F Y") : "";
                                    break;

                                case "end_date":
                                    $month = ($result->end_date !== '0000-00-00 00:00:00') ? JFactory::getDate(new JDate($result->end_date, $site_offset))->format("F Y") : "";
                                    break;

                                case "formation_start":
                                    $month = ($result->formation_start !== '0000-00-00 00:00:00') ? JFactory::getDate(new JDate($result->formation_start, $site_offset))->format("F Y") : "";
                                    break;

                                case "formation_end":
                                    $month = ($result->formation_end !== '0000-00-00 00:00:00') ? JFactory::getDate(new JDate($result->formation_end, $site_offset))->format("F Y") : "";
                                    break;
                            }
                        ?>
                    <div class="mod_emundus_campaign__list_content em-border-neutral-300" onclick="window.location.href=<?php echo !empty($result->link) ? $result->link : JURI::base() . "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>">
                        <div class="mod_emundus_campaign__list_content_head <?php echo $mod_em_campaign_class; ?>">
                            <a href="<?php echo !empty($result->link) ? $result->link : JURI::base() . "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>">
                                <p class="em-text-neutral-900 em-h6"><?php echo $result->label; ?></p>
                            </a>
                            <div class="<?php echo $mod_em_campaign_class; ?>">
                                <div>
                                    <?php if ($mod_em_campaign_show_camp_start_date && $result->start_date != '0000-00-00 00:00:00') : ?>
                                        <strong><i class="icon-clock"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE'); ?></strong>
                                        <span class="em-camp-start"><?php echo JFactory::getDate(new JDate($result->start_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                        <br>
                                    <?php endif; ?>

                                    <?php if ($mod_em_campaign_show_camp_end_date && $result->end_date != '0000-00-00 00:00:00') : ?>
                                        <strong><i class="icon-clock <?php echo ($j < 1 && $h <= 1) ? 'red' : ''; ?>"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_END_DATE'); ?>
                                        </strong>
                                        <span class="em-camp-end"><?php echo JFactory::getDate(new JDate($result->end_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                        <br>
                                    <?php endif; ?>

                                    <?php if ($mod_em_campaign_show_formation_start_date && $result->formation_start !== '0000-00-00 00:00:00') : ?>
                                        <strong><?php echo JText::_('MOD_EM_CAMPAIGN_FORMATION_START_DATE'); ?>:</strong>
                                        <span class="em-formation-start"><?php echo JFactory::getDate(new JDate($result->formation_start, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                        <br>
                                    <?php endif; ?>

                                    <?php if ($mod_em_campaign_show_formation_end_date && $result->formation_end !== '0000-00-00 00:00:00') : ?>
                                        <strong><?php echo JText::_('MOD_EM_CAMPAIGN_FORMATION_END_DATE'); ?>:</strong>
                                        <span class="em-formation-end"><?php echo JFactory::getDate(new JDate($result->formation_end, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                        <br/>
                                    <?php endif; ?>
                                    <?php if ($mod_em_campaign_show_admission_start_date && $result->admission_start_date !== '0000-00-00 00:00:00') : ?>
                                        <strong><?php echo JText::_('MOD_EM_CAMPAIGN_ADMISSION_START_DATE'); ?>:</strong>
                                        <span class="em-formation-start"><?php echo JFactory::getDate(new JDate($result->admission_start_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                        <br>
                                    <?php endif; ?>

                                    <?php if ($mod_em_campaign_show_admission_end_date && $result->admission_end_date !== '0000-00-00 00:00:00') : ?>
                                        <strong><?php echo JText::_('MOD_EM_CAMPAIGN_ADMISSION_END_DATE'); ?>:</strong>
                                        <span class="em-formation-end"><?php echo JFactory::getDate(new JDate($result->admission_end_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                        <br/>
                                    <?php endif; ?>
                                    <?= (!empty($mod_em_campaign_show_timezone)) ? JText::_('MOD_EM_CAMPAIGN_TIMEZONE') . $offset : ''; ?>
                                </div>
                            </div>

                            <hr>

                            <p class="mod_emundus_campaign__list_content_resume em-text-neutral-600">
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
    });

    function displayFilters(){
        let filters = document.getElementById('filters_block');
        if(filters.style.display === 'none'){
            filters.style.display = 'flex';
        } else {
            filters.style.display = 'none';
        }
    }
</script>
