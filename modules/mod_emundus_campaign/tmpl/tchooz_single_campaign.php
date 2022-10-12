<?php
defined('_JEXEC') or die;

header('Content-Type: text/html; charset=utf-8');

$app = JFactory::getApplication();
$user = JFactory::getUser();
$lang = JFactory::getLanguage();
$locallang = $lang->getTag();

if ($locallang == "fr-FR") {
    setlocale(LC_TIME, 'fr', 'fr_FR', 'french', 'fra', 'fra_FRA', 'fr_FR.ISO_8859-1', 'fra_FRA.ISO_8859-1', 'fr_FR.utf8', 'fr_FR.utf-8', 'fra_FRA.utf8', 'fra_FRA.utf-8');
} else {
    setlocale (LC_ALL, 'en_GB');
}

$config = JFactory::getConfig();
$site_offset = $config->get('offset');

$currentCampaign = is_array($allCampaign) ? $allCampaign[0] : $allCampaign;
$dteStart = new DateTime($now);
$dteEnd   = new DateTime($currentCampaign->end_date);
$dteDiff  = $dteStart->diff($dteEnd);
$j = $dteDiff->format("%a");
$h = $dteDiff->format("%H");

switch ($order) {
    case "start_date":
        $month = ($currentCampaign->start_date !== '0000-00-00 00:00:00') ? JFactory::getDate(new JDate($currentCampaign->start_date, $site_offset))->format("F Y") : "";
        break;

    case "end_date":
        $month = ($currentCampaign->end_date !== '0000-00-00 00:00:00') ? JFactory::getDate(new JDate($currentCampaign->end_date, $site_offset))->format("F Y") : "";
        break;

    case "formation_start":
        $month = ($currentCampaign->formation_start !== '0000-00-00 00:00:00') ? JFactory::getDate(new JDate($currentCampaign->formation_start, $site_offset))->format("F Y") : "";
        break;

    case "formation_end":
        $month = ( $currentCampaign->formation_end !== '0000-00-00 00:00:00') ? JFactory::getDate(new JDate($currentCampaign->formation_end, $site_offset))->format("F Y") : "";
        break;
}
?>

<div class="em-grid-2-70-30" style="grid-gap: 64px">
    <div>
        <p class="mod_emundus_campaign__programme_tag"><?php  echo $currentCampaign->programme; ?></p>
        <p class="em-h3 mod_emundus_campaign__campaign_title em-mt-16"><?php echo $currentCampaign->label; ?></p>
        <div class="em-flex-row em-mt-16">
            <?php if ($mod_em_campaign_show_camp_start_date && $currentCampaign->start_date != '0000-00-00 00:00:00') :?>
            <div class="em-flex-row">
                <strong><i class="icon-clock"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE'); ?></strong>
                <span class="em-camp-start"><?php echo JFactory::getDate(new JDate($currentCampaign->start_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($mod_em_campaign_show_camp_end_date && $currentCampaign->end_date != '0000-00-00 00:00:00') :?>
            <div class="em-flex-row em-ml-24">
                <strong><i class="icon-clock <?php echo ($j<1 && $h<=1)?'red':'';?>"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_END_DATE'); ?></strong>
                <span class="em-camp-end"><?php echo JFactory::getDate(new JDate($currentCampaign->end_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($mod_em_campaign_show_formation_start_date && $currentCampaign->formation_start !== '0000-00-00 00:00:00') :?>
            <div class="em-flex-row">
                <strong><?php echo JText::_('MOD_EM_CAMPAIGN_FORMATION_START_DATE'); ?>:</strong>
                <span class="em-formation-start"><?php echo JFactory::getDate(new JDate($currentCampaign->formation_start, $site_offset))->format($mod_em_campaign_date_format); ?></span>
            </div>
            <?php endif;?>

            <?php if ($mod_em_campaign_show_formation_end_date && $currentCampaign->formation_end !== '0000-00-00 00:00:00') :?>
            <div class="em-flex-row em-ml-24">
                <strong><?php echo JText::_('MOD_EM_CAMPAIGN_FORMATION_END_DATE'); ?>:</strong>
                <span class="em-formation-end"><?php echo JFactory::getDate(new JDate($currentCampaign->formation_end, $site_offset))->format($mod_em_campaign_date_format); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($mod_em_campaign_show_admission_start_date && $currentCampaign->admission_start_date !== '0000-00-00 00:00:00') :?>
            <div class="em-flex-row">
                <strong><?php echo JText::_('MOD_EM_CAMPAIGN_ADMISSION_START_DATE'); ?>:</strong>
                <span class="em-formation-start"><?php echo JFactory::getDate(new JDate($currentCampaign->admission_start_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
            </div>
            <?php endif;?>

            <?php if ($mod_em_campaign_show_admission_end_date && $currentCampaign->admission_end_date !== '0000-00-00 00:00:00') :?>
            <div class="em-flex-row em-ml-24">
                <strong><?php echo JText::_('MOD_EM_CAMPAIGN_ADMISSION_END_DATE'); ?>:</strong>
                <span class="em-formation-end"><?php echo JFactory::getDate(new JDate($currentCampaign->admission_end_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
            </div>
            <?php endif; ?>
        </div>
        <div class="em-mt-8">
            <?= (!empty($mod_em_campaign_show_timezone)) ? JText::_('MOD_EM_CAMPAIGN_TIMEZONE').$offset : ''; ?>
        </div>

        <div class="mod_emundus_campaign__tabs em-flex-row">
            <?php if (!empty($faq_articles) || !empty($files)) : ?>
                <a class="em-text-neutral-900 current-tab em-mr-24" onclick="displayTab('campaign')" id="campaign_tab">
                    <span><?php echo JText::_('MOD_EM_CAMPAIGN_DETAILS') ?></span>
                </a>
            <?php endif; ?>
            <?php if (in_array('faq', $modules_tabs) && !empty($faq_articles)) : ?>
                <a class="em-text-neutral-900" onclick="displayTab('faq')" id="faq_tab">
                    <span><?php echo JText::_('MOD_EM_CAMPAIGN_FAQ') ?></span>
                </a>
            <?php endif; ?>
        </div>

        <div class="g-block size-100 tchooz-single-campaign">
            <div class="single-campaign" id="campaign">
                <?php if ($showprogramme) :?>
                    <div class="em-mt-16 em-w-100">
                        <span><?php echo $currentCampaign->notes ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($showcampaign) :?>
                    <div class="em-mt-16 em-w-100">
                        <span><?php echo $currentCampaign->description ?></span>
                    </div>
                <?php endif; ?>
            </div><!-- Close campaign-content -->

            <?php if ($mod_em_campaign_modules_tab) :?>
                <div id="faq">
                    <?php foreach ($faq_articles as $article) :?>
                        <h2> <?php echo $article->title ?></h2>
                        <p> <?php echo $article->introtext ?></p>
                        <hr>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <?php if ($mod_em_campaign_show_registration == 1) : ?>
        <!-- INFO BLOCK -->
        <div class="mod_emundus_campaign__details_content em-border-neutral-300 em-mb-24">
            <p class="em-h6">Candidater</p>
            <div class="em-mt-24">
                <?php $index = 1; ?>
                <?php foreach ($mod_em_campaign_show_registration_steps as $key => $step): ?>
                    <span class="em-text-neutral-600 em-flex-row em-font-size-14 em-mb-16"><span class="mod_emundus_campaign__details_step_count"><?php echo $index ?></span><?php echo $step->mod_em_campaign_show_registration_steps_text ?></span>
                    <?php $index++; ?>
                <?php endforeach;?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ATTACHMENTS BLOCK -->
        <div class="mod_emundus_campaign__details_content em-border-neutral-300 em-mb-24">
            <p class="em-h6">Téléchargements</p>
            <div class="em-mt-24">
                <?php foreach($files as $file) : ?>
                    <div class="em-flex-row em-mb-16 mod_emundus_campaign__details_file">
                        <span class="material-icons-outlined mod_emundus_campaign__details_file_icon">insert_drive_file</span>
                        <a href="files/<?php echo $file->catid."/".$file->title_category."/".$file->id."/".$file->title_file.".".$file->ext; ?>" target="_blank" rel="noopener noreferrer" >
                            <?php echo $file->title_file.".".$file->ext; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mod_emundus_campaign__details_content em-border-neutral-300">
            <p class="em-h6">Contact</p>
        </div>
    </div>
</div>

<script>
    var current_tab = 'campaign';

    window.onload = function() {
        document.getElementById('campaign_tab').classList.add('current-tab');
        <?php if (in_array('faq', $modules_tabs)) : ?>
        document.getElementById('faq').style.display = 'none';
        <?php endif; ?>

        <?php if (in_array('documents', $modules_tabs)) : ?>
        document.getElementById('documents').style.display = 'none';
        if(typeof document.getElementsByClassName('campaign-documents')[0] != 'undefined') {
            document.getElementsByClassName('campaign-documents')[0].parentElement.style.display = 'none';
        }
        <?php endif; ?>
    }

    function displayTab(tab){
        switch (tab) {
            case 'campaign':
                if(current_tab === 'faq'){
                    document.getElementById('faq').style.display = 'none';
                    document.getElementById('faq_tab').classList.remove('current-tab');
                } else if(current_tab === 'documents'){
                    document.getElementById('documents').style.display = 'none';
                    document.getElementById('documents_tab').classList.remove('current-tab');
                }
                break;
            case 'faq':
                if(current_tab === 'campaign'){
                    document.getElementById('campaign').style.display = 'none';
                    document.getElementById('campaign_tab').classList.remove('current-tab');
                } else if(current_tab === 'documents'){
                    document.getElementById('documents').style.display = 'none';
                    document.getElementById('documents_tab').classList.remove('current-tab');
                }
                break;
            default:
                break;
        }
        current_tab = tab;
        var section = document.getElementById(tab);
        var tab_div = document.getElementById(tab + '_tab');
        section.style.display === 'none' ? tab_div.classList.add('current-tab') : '';
        section.style.display === 'none' ? section.style.display = 'flex' : '';
    }
</script>
