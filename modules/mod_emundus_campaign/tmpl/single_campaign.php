<?php
defined('_JEXEC') or die;

header('Content-Type: text/html; charset=utf-8');

$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$lang      = JFactory::getLanguage();
$locallang = $lang->getTag();

if ($locallang == "fr-FR") {
	setlocale(LC_TIME, 'fr', 'fr_FR', 'french', 'fra', 'fra_FRA', 'fr_FR.ISO_8859-1', 'fra_FRA.ISO_8859-1', 'fr_FR.utf8', 'fr_FR.utf-8', 'fra_FRA.utf8', 'fra_FRA.utf-8');
}
else {
	setlocale(LC_ALL, 'en_GB');
}

$config      = JFactory::getConfig();
$site_offset = $config->get('offset');

$currentCampaign = is_array($allCampaign) ? $allCampaign[0] : $allCampaign;

$dteStart = new DateTime($now);
$dteEnd   = new DateTime($currentCampaign->end_date);
$dteDiff  = $dteStart->diff($dteEnd);
$j        = $dteDiff->format("%a");
$h        = $dteDiff->format("%H");

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
		$month = ($currentCampaign->formation_end !== '0000-00-00 00:00:00') ? JFactory::getDate(new JDate($currentCampaign->formation_end, $site_offset))->format("F Y") : "";
		break;
}
?>

<div class="g-block size-100">
    <div class="single-campaign">
        <div class="right-side campaingapply <?php echo $mod_em_campaign_class; ?>">
            <div class="campaingapplycontent">
                <legend><?php echo JText::_('CAMPAIGN_PERIOD'); ?></legend>
				<?php if ($mod_em_campaign_show_camp_start_date && $currentCampaign->start_date != '0000-00-00 00:00:00') : ?>
                    <strong><i class="icon-clock"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE'); ?>
                    </strong>
                    <span class="em-camp-start"><?php echo JFactory::getDate(new JDate($currentCampaign->start_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                    <br>
				<?php endif; ?>

				<?php if ($mod_em_campaign_show_camp_end_date && $currentCampaign->end_date != '0000-00-00 00:00:00') : ?>
                    <strong><i class="icon-clock <?php echo ($j < 1 && $h <= 1) ? 'red' : ''; ?>"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_END_DATE'); ?>
                    </strong>
                    <span class="em-camp-end"><?php echo JFactory::getDate(new JDate($currentCampaign->end_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                    <br>
				<?php endif; ?>

				<?php if ($mod_em_campaign_show_formation_start_date && $currentCampaign->formation_start !== '0000-00-00 00:00:00') : ?>
                    <strong><?php echo JText::_('MOD_EM_CAMPAIGN_FORMATION_START_DATE'); ?>:</strong>
                    <span class="em-formation-start"><?php echo JFactory::getDate(new JDate($currentCampaign->formation_start, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                    <br>
				<?php endif; ?>

				<?php if ($mod_em_campaign_show_formation_end_date && $currentCampaign->formation_end !== '0000-00-00 00:00:00') : ?>
                    <strong><?php echo JText::_('MOD_EM_CAMPAIGN_FORMATION_END_DATE'); ?>:</strong>
                    <span class="em-formation-end"><?php echo JFactory::getDate(new JDate($currentCampaign->formation_end, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                    <br/>
				<?php endif; ?>
				<?php if ($mod_em_campaign_show_admission_start_date && $currentCampaign->admission_start_date !== '0000-00-00 00:00:00') : ?>
                    <strong><?php echo JText::_('MOD_EM_CAMPAIGN_ADMISSION_START_DATE'); ?>:</strong>
                    <span class="em-formation-start"><?php echo JFactory::getDate(new JDate($currentCampaign->admission_start_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                    <br>
				<?php endif; ?>

				<?php if ($mod_em_campaign_show_admission_end_date && $currentCampaign->admission_end_date !== '0000-00-00 00:00:00') : ?>
                    <strong><?php echo JText::_('MOD_EM_CAMPAIGN_ADMISSION_END_DATE'); ?>:</strong>
                    <span class="em-formation-end"><?php echo JFactory::getDate(new JDate($currentCampaign->admission_end_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                    <br/>
				<?php endif; ?>
                <hr>
				<?= (!empty($mod_em_campaign_show_timezone)) ? JText::_('MOD_EM_CAMPAIGN_TIMEZONE') . $offset : ''; ?>
            </div>
        </div>
        <div class="below-content">
			<?php $formUrl = base64_encode('index.php?option=com_fabrik&view=form&formid=102&course=' . $currentCampaign->code . '&cid=' . $currentCampaign->id); ?>

			<?php if ($currentCampaign->apply_online == 1) : ?>
                <a class="btn btn-primary btn-creux" role="button"
                   href="javascript:history.back()"><?= JText::_('MOD_EM_CAMPAIGN_GO_BACK'); ?></a>
				<?php
				// The register URL does not work  with SEF, this workaround helps counter this.
				if ($sef == 0) {
					if (!isset($redirect_url) || empty($redirect_url)) {
						$redirect_url = "index.php?option=com_users&view=registration";
					}
					$register_url = $redirect_url . "&course=" . $currentCampaign->code . "&cid=" . $currentCampaign->id . "&Itemid=" . $mod_em_campaign_itemid;
				}
				else {
					$register_url = $redirect_url . "?course=" . $currentCampaign->code . "&cid=" . $currentCampaign->id . "&Itemid=" . $mod_em_campaign_itemid;
				}
				if (!$user->guest) {
					$register_url .= "&redirect=" . $formUrl;
				}
				?>
				<?php if ($currentCampaign->start_date < $now && $currentCampaign->end_date > $now) : ?>
                    <a class="btn btn-primary btn-plein btn-blue" role="button" href='<?php echo $register_url; ?>'
                       data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_APPLY_NOW'); ?></a>
				<?php endif; ?>
			<?php else : ?>
				<?php if ($mod_em_campaign_get_link) : ?>
                    <a class="btn btn-primary btn-creux" role="button" href="javascript:history.back()"
                       data-toggle="sc-modal"><?= JText::_('MOD_EM_CAMPAIGN_GO_BACK'); ?></a>
				<?php else : ?>
                    <a class="btn btn-primary btn-plein btn-blue" role="button"
                       href='<?php echo "index.php?option=com_emundus&view=programme&cid=" . $currentCampaign->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                       target="_blank" data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
				<?php endif; ?>
			<?php endif; ?>
        </div>
    </div><!-- Close campaign-content -->
