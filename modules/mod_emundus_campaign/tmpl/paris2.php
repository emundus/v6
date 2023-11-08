<?php
defined('_JEXEC') or die;
header('Content-Type: text/html; charset=utf-8');

function filterByFormation($campaigns, $formation_type)
{
	if (!empty($formation_type)) {
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('dtfcrc.campaigns')
			->from('data_type_formation_campaign_repeat_campaigns AS dtfcrc')
			->leftJoin('data_type_formation_campaign AS dtf ON dtf.id = dtfcrc.parent_id')
			->where('dtf.id = ' . $db->quote($formation_type));

		$db->setQuery($query);

		try {
			$campaign_ids = $db->loadColumn();
		}
		catch (Exception $e) {
			JLog::add('Error getting campaigns for formation type: ' . $e->getMessage(), JLog::ERROR);
		}

		if (!empty($campaign_ids)) {
			foreach ($campaigns as $key => $campaign) {
				if (!in_array($campaign->id, $campaign_ids)) {
					unset($campaigns[$key]);
				}
			}
		}
	}

	return $campaigns;
}

function getListOfFormationTypes()
{
	$formation_types = array();
	$db              = JFactory::getDbo();
	$query           = $db->getQuery(true);

	$query->select('dtf.id, dtf.type_formation')
		->from('data_type_formation_campaign AS dtf');

	$db->setQuery($query);

	try {
		$formation_types = $db->loadObjectList();
	}
	catch (Exception $e) {
		JLog::add('Error getting formation types: ' . $e->getMessage(), JLog::ERROR);
	}

	return $formation_types;
}


$app                  = JFactory::getApplication();
$user                 = JFactory::getUser();
$searchword           = $app->input->getString('searchword', null);
$formation_type_input = $app->input->getString('formation_type', null);
$formation_types      = getListOfFormationTypes();

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

if (!empty($formation_type_input)) {
	if (!empty($currentCampaign)) {
		$currentCampaign   = filterByFormation($currentCampaign, $formation_type_input);
		$paginationCurrent = new JPagination(sizeof($currentCampaign), $session->get('limitstartCurrent'), $session->get('limit'));
	}

	if (!empty($futurCampaign)) {
		$futurCampaign   = filterByFormation($futurCampaign, $formation_type_input);
		$paginationFutur = new JPagination(sizeof($futurCampaign), $session->get('limitstartFutur'), $session->get('limit'));
	}

	if (!empty($pastCampaign)) {
		$pastCampaign   = filterByFormation($pastCampaign, $formation_type_input);
		$paginationPast = new JPagination(sizeof($pastCampaign), $session->get('limitstartPast'), $session->get('limit'));
	}

	$paginationTotal = new JPagination(sizeof($currentCampaign) + sizeof($futurCampaign) + sizeof($pastCampaign), $session->get('limitstart'), $session->get('limit'));
}

?>

<?= $mod_em_campaign_intro; ?>

<form action="index.php" method="post" id="search_program">
    <div class="em-w-100 em-flex-row" style="justify-content:end;">
        <select id="select_formation_types" name="formation_types" style="width:275px;">
            <option value="">-- <?= JText::_('MOD_EMUNDUS_CAMPAIGN_SELECT_FORMATION_TYPE'); ?> --</option>
			<?php
			foreach ($formation_types as $formation_type) {
				if ($formation_type_input == $formation_type->id) {
					echo '<option value="' . $formation_type->id . '" selected>' . $formation_type->type_formation . '</option>';
				}
				else {
					echo '<option value="' . $formation_type->id . '">' . $formation_type->type_formation . '</option>';
				}
			}
			?>
        </select>
    </div>

	<?php if ($mod_em_campaign_show_search && isset($searchword) && !empty($searchword)): ?>
        <div class="g-block size-100">
            <p>
                <b><?php echo JText::_("MOD_EM_CAMPAIGN_RESULT_FOR") . " : " . htmlspecialchars($searchword, ENT_QUOTES, 'UTF-8'); ?></b>
            </p>
        </div>
	<?php endif; ?>
	<?php if (sizeof($currentCampaign) == 0 && sizeof($futurCampaign) == 0 && sizeof($pastCampaign) == 0) : ?>
		<?php echo JText::_('MOD_EM_CAMPAIGN_NO_CAMPAIGN') ?>
	<?php else : ?>
        <div class="g-grid" id="navfilter">
            <div class="g-block size-30 navrowtabs">
				<?php if (!empty($pastCampaign) || !empty($currentCampaign) || !empty($futurCampaign)) : ?>
                    <ul id="tabslist" class="nav nav-tabs">
						<?php if ($mod_em_campaign_param_tab) : ?>

							<?php // Check if we have the "All" checkbox selected
							if (in_array('all', $mod_em_campaign_list_tab)) : ?>
								<?php $mod_em_campaign_list_tab = ['current', 'futur', 'past']; ?>
							<?php endif; ?>

							<?php foreach ($mod_em_campaign_list_tab as $tab) : ?>
								<?php if (($tab == 'current' && !empty($currentCampaign)) || ($tab == 'past' && !empty($pastCampaign)) || ($tab == 'futur' && !empty($futurCampaign))) : ?>
                                    <li role="presentation"><a data-toggle="tab"
                                                               href="#<?php echo $tab; ?>"><?php echo JText::_("MOD_EM_CAMPAIGN_LIST_" . strtoupper($tab)); ?></a>
                                    </li>
								<?php endif; ?>

							<?php endforeach; ?>
						<?php endif; ?>
                    </ul>

					<?php if (!$user->guest): ?>
                        <div class="em-flex-row em-pointer" onclick="history.go(-1)">
                            <span class="material-icons">arrow_back</span><span
                                    class="em-ml-8"><?php echo JText::_('MOD_EM_CAMPAIGN_BACK'); ?></span>
                        </div>
					<?php endif; ?>
				<?php endif; ?>
            </div>
			<?php if ($mod_em_campaign_show_nav_order): ?>
                <div class="g-block size-30 navorder">
                    <p><?php if ($order != "end_date") : ?>
							<?php if ($ordertime == "desc") : ?>
                                <a href="index.php?order_date=<?php echo $order; ?>&order_time=asc">
                                    <i class="icon-chevron-down" aria-hidden="true"></i>
                                    <b>
										<?php echo JText::_("MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE_ORDER"); ?>
                                    </b>
                                </a>
							<?php else : ?>
                                <a href="index.php?order_date=<?php echo $order; ?>&order_time=desc">
                                    <i class="icon-chevron-up" aria-hidden="true"></i>
                                    <b>
										<?php echo JText::_("MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE_ORDER"); ?>
                                    </b>
                                </a>
							<?php endif; ?>
                            |
                            <a href="index.php?order_date=end_date&order_time=<?php echo $ordertime ?>">
								<?php echo JText::_("MOD_EM_CAMPAIGN_LIST_DATE_END"); ?>
                            </a>
						<?php else : ?>
                            <a href="index.php?order_date=<?php echo $mod_em_campaign_order; ?>&order_time=<?php echo $ordertime ?>">
								<?php echo JText::_("MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE_ORDER"); ?>
                            </a>  |
							<?php if ($ordertime == "desc") : ?>
                                <a href="index.php?order_date=end_date&order_time=asc">
                                    <i class="icon-chevron-down" aria-hidden="true"></i>
                                    <b>
										<?php echo JText::_("MOD_EM_CAMPAIGN_LIST_DATE_END"); ?>
                                    </b>
                                </a>
							<?php else : ?>
                                <a href="index.php?order_date=end_date&order_time=desc">
                                    <i class="icon-chevron-up" aria-hidden="true"></i>
                                    <b>
										<?php echo JText::_("MOD_EM_CAMPAIGN_LIST_DATE_END"); ?>
                                    </b>
                                </a>
							<?php endif; ?>
						<?php endif; ?>
                    </p>
                </div>
			<?php endif; ?>
			<?php if ($mod_em_campaign_show_search): ?>
                <div class="g-block size-30 navsearch">
                    <div class="navsearch-content">
                        <div class="g-block size-100">
                            <div class="input-group">
                                <label for="searchword" style="display: inline-block">
                                    <input name="searchword" type="text" class="form-control"
                                           placeholder="<?php echo JText::_("MOD_EM_CAMPAIGN_SEARCH") . "..."; ?>" <?php if (isset($searchword) && !empty($searchword)) {
										echo "value=" . htmlspecialchars($searchword, ENT_QUOTES, 'UTF-8');
									}; ?>>
                                </label>
                                <span class="input-group-btn">
                                <button class="btn btn-default sch"
                                        type="submit"><?php echo JText::_("MOD_EM_CAMPAIGN_SEARCH"); ?></button>
                            </span>
                            </div><!-- /input-group -->
                        </div><!-- /.col-lg-6 -->
                    </div>
                </div>
			<?php endif; ?>
        </div>

		<?php
		$display_right_side = false;
		$left_side_class    = false;
		if ($mod_em_campaign_show_camp_start_date
			|| $mod_em_campaign_show_camp_end_date
			|| $mod_em_campaign_show_formation_start_date
			|| $mod_em_campaign_show_formation_end_date
			|| $mod_em_campaign_show_admission_start_date
			|| $mod_em_campaign_show_admission_end_date
			|| !empty($mod_em_campaign_show_timezone)) {
			$display_right_side = true;
			$left_side_class    = true;
		}
		?>

        <div class="tab-content">
		<?php if (in_array('current', $mod_em_campaign_list_tab) && !empty($currentCampaign)) : ?>

            <div id="current" class="tab-pane fade in active">
            <div class="campaigns-list">
			<?php if ($mod_em_campaign_show_results): ?><p
                    class="campaigns-list-result"><?php echo $paginationCurrent->getResultsCounter(); ?></p><?php endif; ?>
			<?php if (empty($currentCampaign)) { ?>
                <div class="alert alert-warning"><?php echo JText::_('MOD_EM_CAMPAIGN_NO_RESULT_FOUND') ?></div>
			<?php }
			else {
				$oldmonth = '';

				foreach ($currentCampaign as $result) {
					$dteStart = new DateTime($now);
					$dteEnd   = new DateTime($result->end_date);
					$dteDiff  = $dteStart->diff($dteEnd);
					$j        = $dteDiff->format("%a");
					$h        = $dteDiff->format("%H");

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

					if ($oldmonth != $month) {
						if (!empty($oldmonth)) {
							?>
                            </div> <!-- close campaign block (rt12 toclose) -->
                            </div> <!-- close campaignbymonth block -->
						<?php } // end !empty($oldmonth) ?>
                        <div class="g-block size-100 campaignbymonth">
                        <div class="campaign-month-label">
							<?php if ($mod_em_campaign_display_groupby): ?>
                                <div class="position-me">
                                    <div class="rotate-me <?php echo $mod_em_campaign_class; ?>">
                                        <p><?php echo ucfirst($month); ?></p>
                                    </div>
                                </div>
							<?php endif; ?>
                        </div>
                        <div class="campaign-month-campaigns"><!-- rt12 toclose -->
					<?php } ?>
                    <div class="campaign-content">
                        <div class="<?php if ($left_side_class) : ?>left-side<?php endif; ?> campaigntext <?php echo $mod_em_campaign_class; ?>">
                            <h4>
                                <a href="<?php echo !empty($result->link) ? $result->link : JURI::base() . "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>">
									<?php echo $result->label; ?>
                                </a>
                            </h4>
                            <p>
								<?php
								$text     = '';
								$textprog = '';
								$textcamp = '';
								if ($showcampaign) {
									$textcamp = $result->short_description;
								}
								echo $textcamp;
								?>
                            </p>
                        </div>
						<?php if ($display_right_side) : ?>
                            <div class="right-side campaingapply <?php echo $mod_em_campaign_class; ?>">
                                <div class="campaingapplycontent">
                                    <b><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?></b><br
                                            class="campaingapplycontent-breaker"/>

									<?php if ($mod_em_campaign_show_camp_start_date && $result->start_date != '0000-00-00 00:00:00') : ?>
                                        <strong><i class="icon-clock"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE'); ?>
                                        </strong>
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
                                        <strong><?php echo JText::_('MOD_EM_CAMPAIGN_FORMATION_START_DATE'); ?>
                                            :</strong>
                                        <span class="em-formation-start"><?php echo JFactory::getDate(new JDate($result->formation_start, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                        <br>
									<?php endif; ?>

									<?php if ($mod_em_campaign_show_formation_end_date && $result->formation_end !== '0000-00-00 00:00:00') : ?>
                                        <strong><?php echo JText::_('MOD_EM_CAMPAIGN_FORMATION_END_DATE'); ?>:</strong>
                                        <span class="em-formation-end"><?php echo JFactory::getDate(new JDate($result->formation_end, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                        <br/>
									<?php endif; ?>
									<?php if ($mod_em_campaign_show_admission_start_date && $result->admission_start_date !== '0000-00-00 00:00:00') : ?>
                                        <strong><?php echo JText::_('MOD_EM_CAMPAIGN_ADMISSION_START_DATE'); ?>
                                            :</strong>
                                        <span class="em-formation-start"><?php echo JFactory::getDate(new JDate($result->admission_start_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                        <br>
									<?php endif; ?>

									<?php if ($mod_em_campaign_show_admission_end_date && $result->admission_end_date !== '0000-00-00 00:00:00') : ?>
                                        <strong><?php echo JText::_('MOD_EM_CAMPAIGN_ADMISSION_END_DATE'); ?>:</strong>
                                        <span class="em-formation-end"><?php echo JFactory::getDate(new JDate($result->admission_end_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                        <br/>
									<?php endif; ?>
                                    <hr>
									<?= (!empty($mod_em_campaign_show_timezone)) ? JText::_('MOD_EM_CAMPAIGN_TIMEZONE') . $offset : ''; ?>
                                </div>
                            </div>
						<?php endif; ?>
                        <div class="below-content">
							<?php $formUrl = base64_encode('index.php?option=com_fabrik&view=form&formid=102&course=' . $result->code . '&cid=' . $result->id); ?>

							<?php if (($result->apply_online == 1 && !$result->is_limited) || ($result->apply_online == 1 && $result->is_limited && $m_campaign->isLimitObtained($result->id) !== true)) : ?>
								<?php if ($mod_em_campaign_get_link) : ?>
                                    <a class="btn btn-primary btn-creux btn-orange" role="button"
                                       href='<?php echo !empty($result->link) ? $result->link : "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                                       target="_blank"
                                       data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
								<?php else : ?>
                                    <a class="btn btn-primary btn-creux btn-orange" role="button"
                                       href='<?php echo "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                                       data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
								<?php endif; ?>
								<?php
								// The register URL does not work  with SEF, this workaround helps counter this.
								if ($sef == 0) {
									if (!isset($redirect_url) || empty($redirect_url)) {
										$redirect_url = "index.php?option=com_users&view=registration";
									}
									$register_url = $redirect_url . "&course=" . $result->code . "&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid;
								}
								else {
									$register_url = $redirect_url . "?course=" . $result->code . "&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid;
								}
								if (!$user->guest) {
									$register_url .= "&redirect=" . $formUrl;
								}
								?>
                                <a class="btn btn-primary btn-plein btn-blue" role="button"
                                   href='<?php echo $register_url; ?>'
                                   data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_APPLY_NOW'); ?></a>
							<?php else : ?>
								<?php if ($mod_em_campaign_get_link) : ?>
                                    <a class="btn btn-primary btn-plein btn-blue" role="button"
                                       href='<?php echo !empty($result->link) ? $result->link : "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                                       target="_blank"
                                       data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
								<?php else : ?>
                                    <a class="btn btn-primary btn-plein btn-blue" role="button"
                                       href='<?php echo "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                                       data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
								<?php endif; ?>
							<?php endif; ?>
                        </div>
                    </div><!-- Close campaign-content -->
					<?php
					$oldmonth = $month;
				} ?>
                </div> <!-- close last campaign block -->
                </div> <!-- close last campaignbymonth block -->
			<?php } ?>
            </div><!-- Close campaigns-list -->
            <div class="pagination"></div>
            </div><!-- Close current tab -->
		<?php endif; ?>

		<?php if (in_array('futur', $mod_em_campaign_list_tab) && !empty($futurCampaign)) : ?>
            <div id="futur" class="tab-pane fade in active">
            <div class="campaigns-list">
			<?php if ($mod_em_campaign_show_results): ?><p
                    class="campaigns-list-result"><?php echo $paginationFutur->getResultsCounter(); ?></p><?php endif; ?>
			<?php if (empty($futurCampaign)) { ?>
                <div class="alert alert-warning"><?php echo JText::_('MOD_EM_CAMPAIGN_NO_RESULT_FOUND') ?></div>
			<?php }
			else {
				$oldmonth = '';

				foreach ($futurCampaign as $result) {
					if ($order == "start_date") {
						$month = ($result->start_date !== '0000-00-00 00:00:00') ? JFactory::getDate(new JDate($result->start_date, $site_offset))->format("F Y") : "";
					}
					else {
						$month = ($result->end_date !== '0000-00-00 00:00:00') ? JFactory::getDate(new JDate($result->end_date, $site_offset))->format("F Y") : "";
					}
					if ($oldmonth != $month) {
						if (!empty($oldmonth)) { ?>
                            </div> <!-- close campaign block (rt12 toclose) -->
                            </div> <!-- close campaignbymonth block -->
						<?php } ?>
                        <div class="g-block size-100 campaignbymonth">
                        <div class="campaign-month-label">
							<?php if ($mod_em_campaign_display_groupby): ?>
                                <div class="position-me">
                                    <div class="rotate-me <?php echo $mod_em_campaign_class; ?>">
                                        <p><?php echo ucfirst($month); ?></p>
                                    </div>
                                </div>
							<?php endif; ?>
                        </div>
                        <div class="campaign-month-campaigns"><!-- rt12 toclose -->
					<?php } ?>
                    <div class="campaign-content">
                        <div class="left-side campaigntext <?php echo $mod_em_campaign_class; ?>">
                            <h4>
                                <a href="<?php echo !empty($result->link) ? $result->link : JURI::base() . "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>">
									<?php echo $result->label; ?>
                                </a>
                            </h4>
                            <p>
								<?php
								$text     = '';
								$textprog = '';
								$textcamp = '';
								if ($showcampaign) {
									$textcamp = $result->short_description;
								}
								echo $textcamp;
								?>
                            </p>
                        </div>
                        <div class="right-side campaingapply <?php echo $mod_em_campaign_class; ?>">
                            <div class="campaingapplycontent">
                                <b><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?></b><br/>

								<?php if ($mod_em_campaign_show_camp_start_date && $result->start_date != '0000-00-00 00:00:00') : ?>
                                    <strong><i class="icon-clock"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE'); ?>
                                    </strong>
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
                                <hr>
								<?= (!empty($mod_em_campaign_show_timezone)) ? JText::_('TIMEZONE') . $offset : ''; ?>
                            </div>
                        </div>
                        <div class="below-content">
							<?php if ($result->apply_online == 1) {
								$btn_class = "btn btn-primary btn-creux btn-orange";
							}
							else {
								$btn_class = "btn btn-primary btn-plein btn-blue";
							} ?>
							<?php if ($mod_em_campaign_get_link) : ?>
                                <a class="<?php echo $btn_class; ?>" role="button"
                                   href='<?php echo !empty($result->link) ? $result->link : "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                                   target="_blank"
                                   data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
							<?php else : ?>
                                <a class="btn btn-primary btn-creux btn-orange" role="button"
                                   href='<?php echo "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                                   data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
							<?php endif; ?>
                        </div>
                    </div><!-- Close campaign-content -->
					<?php
					$oldmonth = $month;
				} ?>
                </div> <!-- close last campaign block -->
                </div> <!-- close last campaignbymonth block -->
			<?php } ?>
            </div><!-- Close campaigns-list -->
            </div><!-- Close futur tab -->
		<?php endif; ?>

		<?php if (in_array('past', $mod_em_campaign_list_tab) && !empty($pastCampaign)) : ?>
            <div id="past" class="tab-pane fade in active">
            <div class="campaigns-list">
			<?php if ($mod_em_campaign_show_results): ?><p
                    class="campaigns-list-result"><?php echo $paginationPast->getResultsCounter(); ?></p><?php endif; ?>
			<?php if (empty($pastCampaign)) { ?>
                <div class="alert alert-warning"><?php echo JText::_('MOD_EM_CAMPAIGN_NO_RESULT_FOUND') ?></div>
			<?php }
			else {
				$oldmonth = '';

				foreach ($pastCampaign as $result) {
					if ($order == "start_date") {
						$month = ($result->start_date !== '0000-00-00 00:00:00') ? JFactory::getDate(new JDate($result->start_date, $site_offset))->format("F Y") : "";
					}
					else {
						$month = ($result->end_date !== '0000-00-00 00:00:00') ? JFactory::getDate(new JDate($result->end_date, $site_offset))->format("F Y") : "";
					}
					if ($oldmonth != $month) {
						if (!empty($oldmonth)) { ?>
                            </div> <!-- close campaign block (rt12 toclose) -->
                            </div> <!-- close campaignbymonth block -->
						<?php } ?>
                        <div class="g-block size-100 campaignbymonth">
                        <div class="campaign-month-label">
							<?php if ($mod_em_campaign_display_groupby): ?>
                                <div class="position-me">
                                    <div class="rotate-me <?php echo $mod_em_campaign_class; ?>">
                                        <p><?php echo ucfirst($month); ?></p>
                                    </div>
                                </div>
							<?php endif; ?>
                        </div>
                        <div class="campaign-month-campaigns"><!-- rt12 toclose -->
					<?php } ?>
                    <div class="campaign-content">
                        <div class="left-side campaigntext <?php echo $mod_em_campaign_class; ?>">
                            <h4>
                                <a href="<?php echo !empty($result->link) ? $result->link : JURI::base() . "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>">
									<?php echo $result->label; ?>
                                </a>
                            </h4>
                            <p>
								<?php
								$text     = '';
								$textprog = '';
								$textcamp = '';
								if ($showcampaign) {
									$textcamp = $result->short_description;
								}
								echo $textcamp;
								?>
                            </p>
                        </div>
                        <div class="right-side campaingapply <?php echo $mod_em_campaign_class; ?>">
                            <div class="campaingapplycontent">
                                <b><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?></b><br/>

								<?php if ($mod_em_campaign_show_camp_start_date && $result->start_date != '0000-00-00 00:00:00') : ?>
                                    <strong><i class="icon-clock"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE'); ?>
                                    </strong>
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
                                <hr>
								<?= (!empty($mod_em_campaign_show_timezone)) ? JText::_('TIMEZONE') . $offset : ''; ?>
                            </div>
                        </div>
                        <div class="below-content">
							<?php if ($result->apply_online == 1) {
								$btn_class = "btn btn-primary btn-creux btn-orange";
							}
							else {
								$btn_class = "btn btn-primary btn-plein btn-blue";
							} ?>
							<?php if ($mod_em_campaign_get_link) : ?>
                                <a class="<?php echo $btn_class; ?>" role="button"
                                   href='<?php echo !empty($result->link) ? $result->link : "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                                   target="_blank"
                                   data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
							<?php else : ?>
                                <a class="<?php echo $btn_class; ?>" role="button"
                                   href='<?php echo "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                                   data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
							<?php endif; ?>
                        </div>
                    </div><!-- Close campaign-content -->
					<?php
					$oldmonth = $month;
				} ?>
                </div> <!-- close last campaign block -->
                </div> <!-- close last campaignbymonth block -->
			<?php } ?>
            </div><!-- Close campaigns-list -->
            </div><!-- Close past tab -->
		<?php endif; ?>
        </div><!-- Close tab-content -->
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

    function changeFormationType(e) {
        const formation_type_id = e.target.selectedOptions[0].value;
        const newUrl = window.location.origin + window.location.pathname + '?formation_type=' + formation_type_id;

        window.location.href = newUrl;
    }

    var select = document.getElementById("select_formation_types");
    select.addEventListener("change", function (event) {
        changeFormationType(event);
    });
</script>
