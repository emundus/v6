<?php
defined('_JEXEC') or die;

header('Content-Type: text/html; charset=utf-8');

$app        = JFactory::getApplication();
$searchword = $app->input->getString('searchword', null);

$lang      = JFactory::getLanguage();
$locallang = $lang->getTag();
if ($locallang == "fr-FR") {
	setlocale(LC_TIME, 'fr', 'fr_FR', 'french', 'fra', 'fra_FRA', 'fr_FR.ISO_8859-1', 'fra_FRA.ISO_8859-1', 'fr_FR.utf8', 'fr_FR.utf-8', 'fra_FRA.utf8', 'fra_FRA.utf-8');
}
else {
	setlocale(LC_ALL, 'en_GB');
}

// Check if user don't already have an opened fnum
$user = JFactory::getSession()->get('emundusUser');

// check if user is not connected as coordinator
$unallowed_menutypes = [
	"partnermenu",
	"coordinatormenu"
];

if (in_array($user->menutype, $unallowed_menutypes)) {
	return;
}

function userFormationLevelsAllowed()
{
	$user = JFactory::getSession()->get('emundusUser');

	// get all formation levels from data_formation_level table
	try {
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from('data_formation_level');
		$db->setQuery($query);

		$formationLevels = $db->loadColumn();

		// check if user has an opened fnum for each formation level
		foreach ($user->fnums as $fnum => $values) {
			// get fnum's formation level
			$query->clear();
			$query->select('data_formation_level.id');
			$query->from('data_formation_level')
				->leftJoin('data_formation ON data_formation_level.id = data_formation.level')
				->leftJoin('#__emundus_setup_campaigns ON data_formation.id = #__emundus_setup_campaigns.formation')
				->leftJoin('#__emundus_campaign_candidature ON #__emundus_setup_campaigns.id = #__emundus_campaign_candidature.campaign_id')
				->where('#__emundus_campaign_candidature.fnum = ' . $fnum)
				->andWhere('#__emundus_campaign_candidature.published = 1')
				->andWhere('#__emundus_setup_campaigns.end_date >= NOW()');


			$db->setQuery($query);
			$formationLevel = $db->loadResult();

			// check if user has an opened fnum for the formation level
			if (in_array($formationLevel, $formationLevels)) {
				// remove value from array
				$key = array_search($formationLevel, $formationLevels);
				unset($formationLevels[$key]);
			}
		}
	}
	catch (Exception $e) {
		JLog::add('Error getting formation levels from data_formation_level table : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');

		return [];
	}

	return $formationLevels;
}

$allowedFormationLevels = userFormationLevelsAllowed();

// remove campaigns that has a formation level that user does have an opened fnum for
function removeNotAllowedCampaigns($campaigns, $allowedFormationLevels)
{
	foreach ($campaigns as $key => $campaign) {
		// get campaign's formation level
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('data_formation_level.id');
		$query->from('data_formation_level')
			->leftJoin('data_formation ON data_formation_level.id = data_formation.level')
			->leftJoin('#__emundus_setup_campaigns ON data_formation.id = #__emundus_setup_campaigns.formation')
			->where('#__emundus_setup_campaigns.id = ' . $campaign->id);

		$db->setQuery($query);
		$formationLevel = $db->loadResult();

		// remove campaign if user has an opened fnum for the formation level
		if (!in_array($formationLevel, $allowedFormationLevels)) {
			unset($campaigns[$key]);
		}
	}

	return $campaigns;
}

$currentCampaign = removeNotAllowedCampaigns($currentCampaign, $allowedFormationLevels);
$allCampaign     = removeNotAllowedCampaigns($allCampaign, $allowedFormationLevels);
$pastCampaign    = removeNotAllowedCampaigns($pastCampaign, $allowedFormationLevels);
$futurCampaign   = removeNotAllowedCampaigns($futurCampaign, $allowedFormationLevels);

// sort arrays by label and not by date
usort($currentCampaign, function ($a, $b) {
	return $a->label <=> $b->label;
});

usort($allCampaign, function ($a, $b) {
	return $a->label <=> $b->label;
});

usort($pastCampaign, function ($a, $b) {
	return $a->label <=> $b->label;
});

usort($futurCampaign, function ($a, $b) {
	return $a->label <=> $b->label;
});

?>

<?php if (!empty($allowedFormationLevels)): ?>

	<?= $mod_em_campaign_intro; ?>

    <form action="index.php" method="post" id="search_program">
		<?php if ($mod_em_campaign_show_search && isset($searchword) && !empty($searchword)): ?>
            <div class="g-block size-100">
                <p>
                    <b><?php echo JText::_("RESULT_FOR") . " : " . htmlspecialchars($searchword, ENT_QUOTES, 'UTF-8'); ?></b>
                </p>
            </div>
		<?php endif; ?>
        <div class="g-grid" id="navfilter">
            <div class="g-block size-30 navrowtabs">
				<?php if ((count($currentCampaign) >= 1 && count($pastCampaign) >= 1 && count($futurCampaign) == 0) || (count($currentCampaign) == 0 && count($pastCampaign) >= 1 && count($futurCampaign) >= 1) || (count($currentCampaign) >= 1 && count($pastCampaign) == 0 && count($futurCampaign) >= 1)) : ?>

                    <ul id="tabslist" class="nav nav-tabs">
						<?php if ($mod_em_campaign_param_tab) : ?>
							<?php foreach ($mod_em_campaign_list_tab as $tab) : ?>
								<?php if (($tab == 'current' && !empty($currentCampaign)) || ($tab == 'past' && !empty($pastCampaign)) || ($tab == 'futur' && !empty($futurCampaign))) : ?>
                                    <li role="presentation"><a data-toggle="tab"
                                                               href="#<?php echo $tab; ?>"><?php echo JText::_("MOD_EM_CAMPAIGN_LIST_" . strtoupper($tab)); ?></a>
                                    </li>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>
                    </ul>
				<?php endif; ?>
				<?php if (!empty($formationTypes) && count($formationTypes) > 1): ?>
                    <div class="g-block size-30" id="navfilter">
                        <!-- <p>
                        <select name="formation_type" id="formation_type" onchange="filterBy('formation_type', this.value)">
                            <option value="all" selected>Tous type de formations</option>
                            <?php foreach ($formationTypes as $type): ?>
                                <option value="<?php echo $type->id; ?>"><?php echo $type->type; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p> -->
                        <p>
                            <select name="formation_level" id="formation_level"
                                    onchange="filterBy('formation_level', this.value)">
                                <option value="all" selected>Tous les niveaux de formation</option>
								<?php foreach ($formationLevels as $level): ?>
									<?php if (in_array($level->id, $allowedFormationLevels)): ?>
                                        <option value="<?php echo $level->id; ?>"><?php echo $level->label; ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
                            </select>
                        </p>
                        <p>
                            <select name="voie_d_acces" id="voie_d_acces"
                                    onchange="filterBy('voie_d_acces', this.value)">
                                <option value="all" selected>Toutes les voies d'acces</option>
								<?php foreach ($voiesDAcces as $acces): ?>
                                    <option value="<?php echo $acces->id; ?>"><?php echo $acces->libelle_fr; ?></option>
								<?php endforeach; ?>
                            </select>
                        </p>
                    </div>
				<?php endif; ?>
            </div>
			<?php if ($mod_em_campaign_show_nav_order): ?>
                <div class="g-block size-30 navorder">
                    <p><?php if ($order != "end_date") : ?>
							<?php if ($ordertime == "desc") : ?>
                                <a href="index.php?order_date=<?php echo $order; ?>&order_time=asc">
                                    <i class="icon-chevron-down" aria-hidden="true"></i>
                                    <b>
										<?php echo JText::_("CAMPAIGN_START_DATE_ORDER"); ?>
                                    </b>
                                </a>
							<?php else : ?>
                                <a href="index.php?order_date=<?php echo $order; ?>&order_time=desc">
                                    <i class="icon-chevron-up" aria-hidden="true"></i>
                                    <b>
										<?php echo JText::_("CAMPAIGN_START_DATE_ORDER"); ?>
                                    </b>
                                </a>
							<?php endif; ?>
                            |
                            <a href="index.php?order_date=end_date&order_time=<?php echo $ordertime ?>">
								<?php echo JText::_("LIST_DATE_END"); ?>
                            </a>
						<?php else : ?>
                            <a href="index.php?order_date=<?php echo $mod_em_campaign_order; ?>&order_time=<?php echo $ordertime ?>">
								<?php echo JText::_("CAMPAIGN_START_DATE_ORDER"); ?>
                            </a>  |
							<?php if ($ordertime == "desc") : ?>
                                <a href="index.php?order_date=end_date&order_time=asc">
                                    <i class="icon-chevron-down" aria-hidden="true"></i>
                                    <b>
										<?php echo JText::_("LIST_DATE_END"); ?>
                                    </b>
                                </a>
							<?php else : ?>
                                <a href="index.php?order_date=end_date&order_time=desc">
                                    <i class="icon-chevron-up" aria-hidden="true"></i>
                                    <b>
										<?php echo JText::_("LIST_DATE_END"); ?>
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
                                           placeholder="<?php echo JText::_("SEARCH") . "..."; ?>" <?php if (isset($searchword) && !empty($searchword)) {
										echo "value=" . htmlspecialchars($searchword, ENT_QUOTES, 'UTF-8');
									}; ?>>
                                </label>
                                <span class="input-group-btn">
                                <button class="btn btn-default sch"
                                        type="submit"><?php echo JText::_("SEARCH"); ?></button>
                            </span>
                            </div><!-- /input-group -->
                        </div><!-- /.col-lg-6 -->
                    </div>
                </div>
			<?php endif; ?>
        </div>

        <div class="tab-content">
			<?php if (in_array('current', $mod_em_campaign_list_tab) && !empty($currentCampaign)) : ?>

            <div id="current" class="tab-pane fade in active">
                <div class="campaigns-list">
					<?php if ($mod_em_campaign_show_results): ?><p
                            class="campaigns-list-result"><?php echo $paginationCurrent->getResultsCounter(); ?></p><?php endif; ?>
					<?php if (empty($currentCampaign)) { ?>
                        <div class="alert alert-warning"><?php echo JText::_('NO_RESULT_FOUND') ?></div>
					<?php } else {
					$oldmonth = '';

					foreach ($currentCampaign

					as $result) {
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
        <div class="campaign-content <?php echo $result->class ?>">
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
            <div class="below-content">
				<?php $formUrl = base64_encode('index.php?option=com_fabrik&view=form&formid=102&course=' . $result->code . '&cid=' . $result->id); ?>

				<?php if ($result->apply_online == 1 && $m_campaign->isLimitObtained($result->id) !== true) : ?>
					<?php if (!empty($result->formation_url)) :
						echo "<a class='btn btn-primary btn-creux btn-orange' role='button' href='#' onclick='goTo(\"" . $result->formation_url . "\", true)' data-toggle='sc-modal'>" . JText::_('MORE_INFO') . "</a>";
						?>
					<?php elseif ($mod_em_campaign_get_link) : ?>
                        <a class="btn btn-primary btn-creux btn-orange" role="button"
                           href='<?php echo !empty($result->link) ? $result->link : "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                           target="_blank" data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
					<?php else : ?>
                        <a class="btn btn-primary btn-creux btn-orange" role="button"
                           href='<?php echo "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                           data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
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
                    <a class="btn btn-primary btn-plein btn-blue" role="button" href='<?php echo $register_url; ?>'
                       data-toggle="sc-modal"><?php echo JText::_('APPLY_NOW'); ?></a>
				<?php else : ?>
					<?php if (!empty($result->formation_url)) :
						echo "<a class='btn btn-primary btn-plein btn-blue' role='button' href='#' onclick='goTo(\"" . $result->formation_url . "\", true)' data-toggle='sc-modal'>" . JText::_('MORE_INFO') . "</a>";
						?>
					<?php elseif ($mod_em_campaign_get_link) : ?>
                        <a class="btn btn-primary btn-plein btn-blue" role="button"
                           href='<?php echo !empty($result->link) ? $result->link : "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                           target="_blank" data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
					<?php else : ?>
                        <a class="btn btn-primary btn-plein btn-blue" role="button"
                           href='<?php echo "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                           data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
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
                <div class="alert alert-warning"><?php echo JText::_('NO_RESULT_FOUND') ?></div>
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
                    <div class="campaign-content <?php echo $result->class ?>">
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
                        <div class="below-content">
							<?php if ($result->apply_online == 1) {
								$btn_class = "btn btn-primary btn-creux btn-orange";
							}
							else {
								$btn_class = "btn btn-primary btn-plein btn-blue";
							} ?>

							<?php if (!empty($result->formation_url)) :
								echo "<a class='" . $btn_class . "' role='button' href='#' onclick='goTo(\"" . $result->formation_url . "\", true)' data-toggle='sc-modal'>" . JText::_('MORE_INFO') . "</a>";
								?>
							<?php elseif ($mod_em_campaign_get_link) : ?>
                                <a class="<?php echo $btn_class; ?>" role="button"
                                   href='<?php echo !empty($result->link) ? $result->link : "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                                   target="_blank" data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
							<?php else : ?>
                                <a class="btn btn-primary btn-creux btn-orange" role="button"
                                   href='<?php echo "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                                   data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
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
                <div class="alert alert-warning"><?php echo JText::_('NO_RESULT_FOUND') ?></div>
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
                    <div class="campaign-content <?php echo $result->class ?>">
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
                                   target="_blank" data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
							<?php else : ?>
                                <a class="<?php echo $btn_class; ?>" role="button"
                                   href='<?php echo "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                                   data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
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
    </form>
    <script type="text/javascript">
        const campaigns = document.querySelectorAll('.campaign-content');
        let filters = {};

        function filterBy(type, value) {
            campaigns.forEach(function (campaign) {
                let display = true;

                if (value == "all") {
                    campaign.style.display = "block";
                    delete filters[type];
                } else {
                    filters[type] = value;
                }

                // Loop through each filter
                Object.keys(filters).forEach(function (filter) {
                    const filterClass = filter + "-" + filters[filter];

                    if (!campaign.classList.contains(filterClass)) {
                        display = false;
                    }
                });

                campaign.style.display = display ? "block" : "none";
            });
        }

        function goTo(url, no_secure) {
            if (!url || url === '') {
                return;
            }

            // check format of url by regex
            const regex = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
            if (regex.test(url)) {
                if (no_secure === true) {
                    // replace https with http
                    url = url.replace(/^https:/, 'http:');
                }

                // open url in new tab
                window.open(url, '_blank');
            } else {
                console.warn('URL is not valid');
                return;
            }
        }

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
    </script>

<?php endif; ?>
