<?php
defined('_JEXEC') or die;

header('Content-Type: text/html; charset=utf-8');

$app = JFactory::getApplication();

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

<div class="g-block size-100 tchooz-single-campaign">
    <div class="single-campaign" id="campaign">
        <div class="right-side-tchooz col-md-4">
            <div class="right-side campaingapply <?php echo $mod_em_campaign_class; ?>">
                <div class="campaingapplycontent">
                    <legend><?php echo JText::_('CAMPAIGN_PERIOD'); ?></legend>
					<?php if ($mod_em_campaign_show_camp_start_date && $currentCampaign->start_date != '0000-00-00 00:00:00') : ?>
                        <strong><i class="icon-clock"></i> <?php echo JText::_('CAMPAIGN_START_DATE'); ?></strong>
                        <span class="em-camp-start"><?php echo JFactory::getDate(new JDate($currentCampaign->start_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                        <br>
					<?php endif; ?>

					<?php if ($mod_em_campaign_show_camp_end_date && $currentCampaign->end_date != '0000-00-00 00:00:00') : ?>
                        <strong><i class="icon-clock <?php echo ($j < 1 && $h <= 1) ? 'red' : ''; ?>"></i> <?php echo JText::_('CAMPAIGN_END_DATE'); ?>
                        </strong>
                        <span class="em-camp-end"><?php echo JFactory::getDate(new JDate($currentCampaign->end_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                        <br>
					<?php endif; ?>

					<?php if ($mod_em_campaign_show_formation_start_date && $currentCampaign->formation_start !== '0000-00-00 00:00:00') : ?>
                        <strong><?php echo JText::_('FORMATION_START_DATE'); ?>:</strong>
                        <span class="em-formation-start"><?php echo JFactory::getDate(new JDate($currentCampaign->formation_start, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                        <br>
					<?php endif; ?>

					<?php if ($mod_em_campaign_show_formation_end_date && $currentCampaign->formation_end !== '0000-00-00 00:00:00') : ?>
                        <strong><?php echo JText::_('FORMATION_END_DATE'); ?>:</strong>
                        <span class="em-formation-end"><?php echo JFactory::getDate(new JDate($currentCampaign->formation_end, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                        <br/>
					<?php endif; ?>
					<?php if ($mod_em_campaign_show_admission_start_date && $currentCampaign->admission_start_date !== '0000-00-00 00:00:00') : ?>
                        <strong><?php echo JText::_('ADMISSION_START_DATE'); ?>:</strong>
                        <span class="em-formation-start"><?php echo JFactory::getDate(new JDate($currentCampaign->admission_start_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                        <br>
					<?php endif; ?>

					<?php if ($mod_em_campaign_show_admission_end_date && $currentCampaign->admission_end_date !== '0000-00-00 00:00:00') : ?>
                        <strong><?php echo JText::_('ADMISSION_END_DATE'); ?>:</strong>
                        <span class="em-formation-end"><?php echo JFactory::getDate(new JDate($currentCampaign->admission_end_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                        <br/>
					<?php endif; ?>
                    <hr>
					<?= (!empty($mod_em_campaign_show_timezone)) ? JText::_('TIMEZONE') . $offset : ''; ?>
                </div>
            </div>
        </div>
        <div class="single-campaign col-md-12">
            <div class="below-content">
				<?php $formUrl = base64_encode('index.php?option=com_fabrik&view=form&formid=102&course=' . $currentCampaign->code . '&cid=' . $currentCampaign->id); ?>

				<?php if ($currentCampaign->apply_online == 1) : ?>
                    <a class="btn btn-primary btn-creux" role="button" href="index.php"><?= JText::_('GO_BACK'); ?></a>
					<?php
					// The register URL does not work  with SEF, this workaround helps counter this.
					if ($sef == 0) {
						if (!isset($redirect_url) || empty($redirect_url)) {
							$redirect_url = "index.php?option=com_users&view=registration";
						}
						$register_url = $redirect_url . "&course=" . $currentCampaign->code . "&cid=" . $currentCampaign->id . "&Itemid=" . $mod_em_campaign_itemid . "&redirect=" . $formUrl;
					}
					else {
						$register_url = $redirect_url . "?course=" . $currentCampaign->code . "&cid=" . $currentCampaign->id . "&Itemid=" . $mod_em_campaign_itemid . "&redirect=" . $formUrl;
					}
					?>
                    <a class="btn btn-primary btn-plein btn-blue" role="button" href='<?php echo $register_url; ?>'
                       data-toggle="sc-modal"><?php echo JText::_('APPLY_NOW'); ?></a>
				<?php else : ?>
					<?php if ($mod_em_campaign_get_link) : ?>
                        <a class="btn btn-primary btn-creux" role="button" href="index.php"
                           data-toggle="sc-modal"><?= JText::_('GO_BACK'); ?></a>
					<?php endif; ?>
				<?php endif; ?>
            </div>
        </div>
		<?php if ($showprogramme) : ?>
            <div class="col-md-12">
                <span><?php echo $currentCampaign->notes ?></span>
            </div>
		<?php endif; ?>
		<?php if ($showcampaign) : ?>
            <div class="col-md-12">
				<?php
				if ($now < $currentCampaign->end_date) {
					// Get number of files compared to limit if limit is enabled
					if ($currentCampaign->is_limited == '1') {
						$db    = JFactory::getDbo();
						$query = $db->getQuery(true);

						$query->clear()
							->select($db->quoteName('limit_status'))
							->from($db->quoteName('jos_emundus_setup_campaigns_repeat_limit_status'))
							->where($db->quoteName('parent_id') . ' = ' . $db->quote($currentCampaign->id));
						$db->setQuery($query);
						$limit_status = $db->loadColumn();

						$query->clear()
							->select($db->quoteName('limit'))
							->from($db->quoteName('jos_emundus_setup_campaigns'))
							->where($db->quoteName('id') . ' = ' . $db->quote($currentCampaign->id));
						$db->setQuery($query);
						$file_limit = $db->loadResult();

						$files_sent = 0;
						if (!empty($limit_status)) {
							$query->clear()
								->select('COUNT(id)')
								->from($db->quoteName('jos_emundus_campaign_candidature'))
								->where($db->quoteName('campaign_id') . ' = ' . $db->quote($currentCampaign->id))
								->andWhere($db->quoteName('status') . ' IN (' . implode(',', $limit_status) . ')');
							$db->setQuery($query);
							$files_sent = $db->loadResult();
						}

						if ($files_sent == 1) {
							$files_sent_tag = 'MOD_EM_CAMPAIGN_CAMPAIGN_SENT_NUMBER_SINGULAR';
						}
						else {
							$files_sent_tag = 'MOD_EM_CAMPAIGN_CAMPAIGN_SENT_NUMBER_PLURAL';
						}
						echo '<div style="width:100%;display:flex;justify-content:center;"><p style="display:inline-block;padding:10px;border:1px solid red;border-radius:4px;font-weight:bold;color:red;">' . $files_sent . ' ' . JText::_($files_sent_tag) . ' ' . $file_limit . '</p></div>';
					}
				}
				?>
                <span><?php echo $currentCampaign->description ?></span>
            </div>
		<?php endif; ?>
    </div><!-- Close campaign-content -->
	<?php if ($mod_em_campaign_modules_tab) : ?>
        <div id="faq">
			<?php foreach ($faq_articles as $article) : ?>
                <h2> <?php echo $article->title ?></h2>
                <p> <?php echo $article->introtext ?></p>
                <hr>
			<?php endforeach; ?>
        </div>
        <div id="documents">
            <div class="em-campaign-dropfiles">
                <ul>
					<?php foreach ($files as $file) { ?>
                        <a href="files/<?php echo $file->catid . "/" . $file->title_category . "/" . $file->id . "/" . $file->title_file . "." . $file->ext; ?>"
                           target="_blank" rel="noopener noreferrer">
                            <li class="em-campaign-dropfiles__btn">
								<?php echo $file->title_file . "." . $file->ext; ?><span><i
                                            class="fas fa-arrow-circle-down"></i></span>
                            </li>
                        </a>
					<?php } ?>
                </ul>
            </div>
        </div>
	<?php endif; ?>

</div>