<?php
defined('_JEXEC') or die;

header('Content-Type: text/html; charset=utf-8');

$app = JFactory::getApplication();

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

<div class="single-campaign-tabs">
    <?php if (in_array('campaign', $modules_tabs)) : ?>
        <button class="btn btn-primary current-tab" onclick="displayTab('campaign')" id="campaign_tab">
            <span>Détails</span>
        </button>
    <?php endif; ?>
    <?php if (in_array('faq', $modules_tabs)) : ?>
        <button class="btn btn-primary" onclick="displayTab('faq')" id="faq_tab">
            <span>FAQ</span>
        </button>
    <?php endif; ?>
    <?php if (in_array('documents', $modules_tabs)) : ?>
        <button class="btn btn-primary" onclick="displayTab('documents')" id="documents_tab">
            <span>Documents</span>
        </button>
    <?php endif; ?>
</div>
<div class="g-block size-100 tchooz-single-campaign">
    <div class="single-campaign" id="campaign">
        <?php if ($showprogramme) :?>
            <div class="col-md-7 left-side campaingapply <?php echo $mod_em_campaign_class; ?>">
                <span><?php echo $currentCampaign->notes ?></span>
            </div>
        <?php endif; ?>
        <div class="right-side-tchooz col-md-4">
            <div class="right-side campaingapply <?php echo $mod_em_campaign_class; ?>">
                <div class="campaingapplycontent">
                    <legend><?php echo JText::_('CAMPAIGN_PERIOD'); ?></legend>
                    <?php if ($mod_em_campaign_show_camp_start_date && $currentCampaign->start_date != '0000-00-00 00:00:00') :?>
                        <strong><i class="icon-clock"></i> <?php echo JText::_('CAMPAIGN_START_DATE'); ?></strong>
                        <span class="em-camp-start"><?php echo JFactory::getDate(new JDate($currentCampaign->start_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                        <br>
                    <?php endif; ?>

                    <?php if ($mod_em_campaign_show_camp_end_date && $currentCampaign->end_date != '0000-00-00 00:00:00') :?>
                        <strong><i class="icon-clock <?php echo ($j<1 && $h<=1)?'red':'';?>"></i> <?php echo JText::_('CAMPAIGN_END_DATE'); ?></strong>
                        <span class="em-camp-end"><?php echo JFactory::getDate(new JDate($currentCampaign->end_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                        <br>
                    <?php endif; ?>

                    <?php if ($mod_em_campaign_show_formation_start_date && $currentCampaign->formation_start !== '0000-00-00 00:00:00') :?>
                        <strong><?php echo JText::_('FORMATION_START_DATE'); ?>:</strong>
                        <span class="em-formation-start"><?php echo JFactory::getDate(new JDate($currentCampaign->formation_start, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                        <br>
                    <?php endif;?>

                    <?php if ($mod_em_campaign_show_formation_end_date && $currentCampaign->formation_end !== '0000-00-00 00:00:00') :?>
                        <strong><?php echo JText::_('FORMATION_END_DATE'); ?>:</strong>
                        <span class="em-formation-end"><?php echo JFactory::getDate(new JDate($currentCampaign->formation_end, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                        <br/>
                    <?php endif; ?>
                    <?php if ($mod_em_campaign_show_admission_start_date && $currentCampaign->admission_start_date !== '0000-00-00 00:00:00') :?>
                        <strong><?php echo JText::_('ADMISSION_START_DATE'); ?>:</strong>
                        <span class="em-formation-start"><?php echo JFactory::getDate(new JDate($currentCampaign->admission_start_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                        <br>
                    <?php endif;?>

                    <?php if ($mod_em_campaign_show_admission_end_date && $currentCampaign->admission_end_date !== '0000-00-00 00:00:00') :?>
                        <strong><?php echo JText::_('ADMISSION_END_DATE'); ?>:</strong>
                        <span class="em-formation-end"><?php echo JFactory::getDate(new JDate($currentCampaign->admission_end_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                        <br/>
                    <?php endif; ?>
                    <hr>
                    <?= (!empty($mod_em_campaign_show_timezone)) ? JText::_('TIMEZONE').$offset : ''; ?>
                </div>
            </div>
        </div>
    </div><!-- Close campaign-content -->
    <div id="faq">
        <?php foreach ($faq_articles as $article) :?>
            <h2> <?php echo $article->title ?></h2>
            <p> <?php echo $article->introtext ?></p>
            <hr>
        <?php endforeach; ?>
    </div>
    <div id="documents"></div>
    <div class="single-campaign">
        <div class="below-content">
            <?php $formUrl = base64_encode('index.php?option=com_fabrik&view=form&formid=102&course='.$currentCampaign->code.'&cid='.$currentCampaign->id); ?>

            <?php if ($currentCampaign->apply_online == 1) :?>
                <a class="btn btn-primary btn-creux"  role="button" href="index.php"><?= JText::_('GO_BACK');?></a>
                <?php
                // The register URL does not work  with SEF, this workaround helps counter this.
                if ($sef == 0) {
                    if(!isset($redirect_url) || empty($redirect_url)) {
                        $redirect_url = "index.php?option=com_users&view=registration";
                    }
                    $register_url = $redirect_url."&course=".$currentCampaign->code."&cid=".$currentCampaign->id."&Itemid=".$mod_em_campaign_itemid."&redirect=".$formUrl;
                } else {
                    $register_url = $redirect_url."?course=".$currentCampaign->code."&cid=".$currentCampaign->id."&Itemid=".$mod_em_campaign_itemid."&redirect=".$formUrl;
                }
                ?>
                <a class="btn btn-primary btn-plein btn-blue" role="button" href='<?php echo $register_url;?>' data-toggle="sc-modal"><?php echo JText::_('APPLY_NOW'); ?></a>
            <?php else :?>
                <?php if ($mod_em_campaign_get_link) :?>
                    <a class="btn btn-primary btn-creux" role="button" href="index.php" data-toggle="sc-modal" ><?= JText::_('GO_BACK');?></a>
                <?php else :?>
                    <a class="btn btn-primary btn-plein btn-blue" role="button" href='<?php echo "index.php?option=com_emundus&view=programme&cid=".$currentCampaign->id."&Itemid=".$mod_em_campaign_itemid2; ?>' target="_blank" data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

    <script>
        window.onload = function() {
            <?php if (in_array('campaign', $modules_tabs)) : ?>
                document.getElementById('campaign_tab').classList.add('current-tab');
            <?php else :?>
                document.getElementById('campaign').style.display = 'none';
            <?php endif; ?>
            <?php if (in_array('faq', $modules_tabs)) : ?>
                <?php if (!in_array('campaign', $modules_tabs)) : ?>
                    document.getElementById('faq_tab').classList.add('current-tab');
                <?php else :?>
                    document.getElementById('faq').style.display = 'none';
                <?php endif; ?>
            <?php endif; ?>
            <?php if (in_array('documents', $modules_tabs)) : ?>
                <?php if (!in_array('campaign', $modules_tabs) && !in_array('faq', $modules_tabs)) : ?>
                    document.getElementById('documents_tab').classList.add('current-tab');
                    document.getElementById('documents').appendChild(document.getElementsByClassName('campaign-documents')[0].parentElement);
                <?php else :?>
                    document.getElementById('documents').appendChild(document.getElementsByClassName('campaign-documents')[0].parentElement);
                    document.getElementById('documents').style.display = 'none';
                <?php endif; ?>
            <?php endif; ?>
        }

        function displayTab(tab){
            switch (tab) {
                case 'campaign':
                    <?php if (in_array('faq', $modules_tabs)) : ?>
                    document.getElementById('faq').style.display = 'none';
                    document.getElementById('faq_tab').classList.remove('current-tab');
                    <?php endif; ?>
                    <?php if (in_array('documents', $modules_tabs)) : ?>
                    document.getElementById('documents').style.display = 'none';
                    document.getElementById('documents_tab').classList.remove('current-tab');
                    <?php endif; ?>
                    break;
                case 'faq':
                    <?php if (in_array('campaign', $modules_tabs)) : ?>
                    document.getElementById('campaign').style.display = 'none';
                    document.getElementById('campaign_tab').classList.remove('current-tab');
                    <?php endif; ?>
                    <?php if (in_array('documents', $modules_tabs)) : ?>
                    document.getElementById('documents').style.display = 'none';
                    document.getElementById('documents_tab').classList.remove('current-tab');
                    <?php endif; ?>
                    break;
                case 'documents':
                    <?php if (in_array('faq', $modules_tabs)) : ?>
                    document.getElementById('faq').style.display = 'none';
                    document.getElementById('faq_tab').classList.remove('current-tab');
                    <?php endif; ?>
                    <?php if (in_array('campaign', $modules_tabs)) : ?>
                    document.getElementById('campaign').style.display = 'none';
                    document.getElementById('campaign_tab').classList.remove('current-tab');
                    <?php endif; ?>
                    break;
                default:
                    break;
            }
            var section = document.getElementById(tab);
            var tab_div = document.getElementById(tab + '_tab');
            section.style.display === 'none' ? tab_div.classList.add('current-tab') : '';
            section.style.display === 'none' ? section.style.display = 'flex' : '';
        }
    </script>
