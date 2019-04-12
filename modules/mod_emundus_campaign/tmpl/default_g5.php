<?php
defined('_JEXEC') or die;

header('Content-Type: text/html; charset=utf-8');

$app = JFactory::getApplication();
$searchword = $app->input->getString('searchword', null);

$lang = JFactory::getLanguage();
$locallang = $lang->getTag();
if ($locallang == "fr-FR") {
	setlocale(LC_TIME, 'fr', 'fr_FR', 'french', 'fra', 'fra_FRA', 'fr_FR.ISO_8859-1', 'fra_FRA.ISO_8859-1', 'fr_FR.utf8', 'fr_FR.utf-8', 'fra_FRA.utf8', 'fra_FRA.utf-8');
} else {
	setlocale (LC_ALL, 'en_GB');
}
?>

<form action="<?php echo JRoute::_(JUri::getInstance()->toString(), true, $params->get('')); ?>" method="post" id="search_program">
	<?php if (isset($searchword) && !empty($searchword)) :?>
		<div class="g-block size-100">
			<p><b><?php echo JText::_("RESULT_FOR")." : ".htmlspecialchars($searchword, ENT_QUOTES, 'UTF-8'); ?></b></p>
		</div>
	<?php endif; ?>
	<div class="g-grid" id="navfilter">
		<div class="g-block size-30 navrowtabs">
			<ul id="tabslist" class="nav nav-tabs">
				<?php if ($mod_em_campaign_param_tab) :?>
					<?php foreach ($mod_em_campaign_list_tab as $tab) :?>
						<li role="presentation"><a data-toggle="tab" href="#<?php echo $tab ?>"><?php echo JText::_("MOD_EM_CAMPAIGN_LIST_".strtoupper($tab)); ?></a></li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
		</div>
		<div class="g-block size-30 navorder">
			<p><?php if ($order != "end_date") :?>
					<?php if ($ordertime == "desc") :?>
						<a href="index.php?order_date=<?php echo $order ;?>&order_time=asc"><i class="icon-chevron-down" aria-hidden="true"></i>
					<?php else :?>
							<a href="index.php?order_date=<?php echo $order ;?>&order_time=desc"><i class="icon-chevron-up" aria-hidden="true"></i>
					<?php endif; ?>
					<b><?php echo JText::_("CAMPAIGN_START_DATE");?></b></a> |  <a href="index.php?order_date=end_date&ordertime=<?php echo $ordertime ?>"><?php echo JText::_("LIST_DATE_END");?></a>
				<?php else :?>
					<a href="index.php?order_date=<?php echo $mod_em_campaign_order ;?>&order_time=<?php echo $ordertime ?>"><?php echo JText::_("CAMPAIGN_START_DATE");?></a>  |  <?php if ($ordertime=="desc") {?><a href="index.php?order_date=end_date&order_time=asc"><i class="icon-chevron-down" aria-hidden="true"></i> <?php } else { ?><a href="index.php?order_date=end_date&ordertime=desc"><i class="icon-chevron-up" aria-hidden="true"></i> <?php }?> <b><?php echo JText::_("LIST_DATE_END");?></b></a>
				<?php endif; ?>
			</p>
		</div>
		<div class="g-block size-30 navsearch">
			<div class="navsearch-content">
				<div class="g-block size-100">
					<div class="input-group">
                        <label for="searchword" style="display: inline-block">
                            <input name="searchword" type="text" class="form-control" placeholder="<?php  echo JText::_("SEARCH")."..." ; ?>" <?php if (isset($searchword) && !empty($searchword)) { echo "value=".htmlspecialchars($searchword, ENT_QUOTES, 'UTF-8');}; ?>>
                        </label>
						<span class="input-group-btn">
                            <button class="btn btn-default sch" type="submit"><?php  echo JText::_("SEARCH"); ?></button>
                        </span>
					</div><!-- /input-group -->
				</div><!-- /.col-lg-6 -->
			</div>
		</div>
	</div>

	<div class="tab-content">
		<div id="current" class="tab-pane fade in active">
			<div class="campaigns-list">
				<?php echo $paginationCurrent->getResultsCounter(); ?>
				<?php if (empty($currentCampaign)) {?>
					<div class="alert alert-warning"><?php echo JText::_('NO_RESULT_FOUND') ?></div>
				<?php } else {
					$oldmonth = '';

					foreach ($currentCampaign as $result) {
						$dteStart = new DateTime($now);
						$dteEnd   = new DateTime($result->end_date);
						$dteDiff  = $dteStart->diff($dteEnd);
						$j = $dteDiff->format("%a");
						$h = $dteDiff->format("%H");

						if ($order == "start_date") {
							$month = utf8_encode(strftime("%B %Y", strtotime($result->start_date)));
						} else {
							$month = utf8_encode(strftime("%B %Y", strtotime($result->end_date)));
						}

						if ($oldmonth != $month) {
							if (!empty($oldmonth)) {
						?>
					</div> <!-- close campaign block (rt12 toclose) -->
				</div> <!-- close campaignbymonth block -->
			<?php } // end !empty($oldmonth) ?>
			<div class="g-block size-100 campaignbymonth">
				<div class="campaign-month-label">
					<div class="position-me">
						<div class="rotate-me <?php echo $mod_em_campaign_class; ?>">
							<p><?php echo ucfirst($month); ?></p>
						</div>
					</div>
				</div>
				<div class="campaign-month-campaigns"><!-- rt12 toclose -->
				<?php } // end $oldmonth != $month ?>
				<div class="campaign-content">
					<div class="left-side campaigntext <?php echo $mod_em_campaign_class; ?>">
						<h4><a href="/index.php?option=com_emundus&view=programme&id=<?php echo $result->id ?>&Itemid="<?php echo $mod_em_campaign_itemid2; ?>"><?php echo $result->label; ?></a></h4>
						<p>
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
					<div class="right-side campaingapply <?php echo $mod_em_campaign_class; ?>">
						<div class="campaingapplycontent">
							<b><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?></b><br />

							<?php if ($mod_em_campaign_show_camp_start_date && $result->start_date != '0000-00-00 00:00:00') :?>
                                <strong><i class="icon-time"></i> <?php echo JText::_('CAMPAIGN_START_DATE'); ?>:</strong>
                                <?php echo JFactory::getDate(strtotime($result->start_date))->format($mod_em_campaign_date_format); ?>
                                <br>
                            <?php endif; ?>

                            <?php if ($mod_em_campaign_show_camp_end_date && $result->end_date != '0000-00-00 00:00:00') :?>
							    <strong><i class="icon-time <?php echo ($j<1 && $h<=1)?'red':'';?>"></i> <?php echo JText::_('CAMPAIGN_END_DATE'); ?>:</strong>
                                <?php echo JFactory::getDate(strtotime($result->end_date))->format($mod_em_campaign_date_format); ?>
                                <br>
                            <?php endif; ?>

                            <?php if ($mod_em_campaign_show_formation_start_date && $result->formation_start !== '0000-00-00 00:00:00') :?>
                                <strong><?php echo JText::_('FORMATION_START_DATE'); ?>:</strong>
                                <?php echo JFactory::getDate(strtotime($result->formation_start))->format($mod_em_campaign_date_format); ?>
                                <br>
                            <?php endif;?>

                            <?php if ($mod_em_campaign_show_formation_end_date && $result->formation_end !== '0000-00-00 00:00:00') :?>
                                <strong><?php echo JText::_('FORMATION_END_DATE'); ?>:</strong>
                                <?php echo JFactory::getDate(strtotime($result->formation_end))->format($mod_em_campaign_date_format); ?>
                                <br/>
                            <?php endif; ?>

                            <hr>
							<?php echo JText::_('TIMEZONE').$offset; ?>
						</div>
					</div>
					<div class="below-content">
                        <?php $formUrl = base64_encode('/index.php?option=com_fabrik&view=form&formid=102&course='.$result->code.'&cid='.$result->id); ?>

						<?php if ($result->apply_online == 1) :?>
                            <?php if ($mod_em_campaign_get_link) :?>
                                <a class="btn btn-primary btn-creux btn-orange" role="button" href='<?php echo !empty($result->link) ? $result->link : "index.php?option=com_emundus&view=programme&id=".$result->id."&Itemid=".$mod_em_campaign_itemid2; ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                            <?php else :?>
							    <a class="btn btn-primary btn-creux btn-orange" role="button" href='<?php echo "index.php?option=com_emundus&view=programme&id=".$result->id."&Itemid=".$mod_em_campaign_itemid2; ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                            <?php endif; ?>
							<?php
                                // The register URL does not work  with SEF, this workaround helps counter this.
                                if ($sef == 0) {
                                    $register_url = "index.php?option=com_users&view=".$redirect_url."&course=".$result->code."&cid=".$result->id."&Itemid=".$mod_em_campaign_itemid."&redirect=".$formUrl;
                                } else {
                                    $register_url = $redirect_url."?course=".$result->code."&cid=".$result->id."&Itemid=".$mod_em_campaign_itemid."&redirect=".$formUrl;
                                }
                            ?>
							<a class="btn btn-primary btn-plein btn-blue" role="button" href='<?php echo $register_url;?>' data-toggle="sc-modal"><?php echo JText::_('APPLY_NOW'); ?></a>
						<?php else :?>
                            <?php if ($mod_em_campaign_get_link) :?>
                                <a class="btn btn-primary btn-plein btn-blue" role="button" href='<?php echo !empty($result->link) ? $result->link : "index.php?option=com_emundus&view=programme&id=".$result->id."&Itemid=".$mod_em_campaign_itemid2; ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                            <?php else :?>
                                <a class="btn btn-primary btn-plein btn-blue" role="button" href='<?php echo "index.php?option=com_emundus&view=programme&id=".$result->id."&Itemid=".$mod_em_campaign_itemid2; ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
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

	<div id="futur" class="tab-pane fade in active">
		<div class="campaigns-list">
			<?php echo $paginationFutur->getResultsCounter(); ?>
			<?php if (empty($futurCampaign)) { ?>
				<div class="alert alert-warning"><?php echo JText::_('NO_RESULT_FOUND') ?></div>
			<?php } else {
			$oldmonth = '';

			foreach ($futurCampaign as $result) {
				if ($order == "start_date") {
					$month = utf8_encode(strftime("%B %Y", strtotime($result->start_date)));
				} else {
					$month = utf8_encode(strftime("%B %Y", strtotime($result->end_date)));
				}
				if ($oldmonth != $month) {
					if (!empty($oldmonth)) { ?>
							</div> <!-- close campaign block (rt12 toclose) -->
						</div> <!-- close campaignbymonth block -->
					<?php } ?>
				<div class="g-block size-100 campaignbymonth">
					<div class="campaign-month-label">
						<div class="position-me">
							<div class="rotate-me <?php echo $mod_em_campaign_class; ?>">
								<p><?php echo ucfirst($month); ?></p>
							</div>
						</div>
					</div>
					<div class="campaign-month-campaigns"><!-- rt12 toclose -->
				<?php } ?>
				<div class="campaign-content">
					<div class="left-side campaigntext <?php echo $mod_em_campaign_class; ?>">
						<h4><a href="/index.php?option=com_emundus&view=programme&id=<?php echo $result->id ?><?php if ($result->apply_online == 1) { echo "&Itemid=".$mod_em_campaign_itemid; } else { echo "&Itemid=".$mod_em_campaign_itemid2; } ?>"><?php echo $result->label; ?></a></h4>
						<p>
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
					<div class="right-side campaingapply <?php echo $mod_em_campaign_class; ?>">
						<div class="campaingapplycontent">
							<b><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?></b><br />
                            <?php if ($mod_em_campaign_show_camp_start_date && $result->start_date != '0000-00-00 00:00:00') :?>
                                <strong><i class="icon-time"></i> <?php echo JText::_('CAMPAIGN_START_DATE'); ?>:</strong>
                                <?php
                                echo JFactory::getDate(strtotime($result->start_date))->format($mod_em_campaign_date_format);
                                ?>
                                <br>
                            <?php endif; ?>

                            <?php if ($mod_em_campaign_show_camp_end_date && $result->end_date != '0000-00-00 00:00:00') :?>
                                <strong><i class="icon-time <?php echo ($j<1 && $h<=1)?'red':'';?>"></i> <?php echo JText::_('CAMPAIGN_END_DATE'); ?>:</strong>
                                <?php
                                    echo JFactory::getDate(strtotime($result->end_date))->format($mod_em_campaign_date_format);
                                ?>
                                <br>
                            <?php endif; ?>

                            <?php if ($mod_em_campaign_show_formation_start_date && $result->formation_start != '0000-00-00 00:00:00') :?>
                                <strong><?php echo JText::_('FORMATION_START_DATE'); ?>:</strong>
                                <?php
                                echo JFactory::getDate(strtotime($result->formation_start))->format($mod_em_campaign_date_format);
                                ?>
                                <br>
                            <?php endif;?>

                            <?php if ($mod_em_campaign_show_formation_end_date && $result->formation_end != '0000-00-00 00:00:00') :?>
                                <strong><?php echo JText::_('FORMATION_END_DATE'); ?>:</strong>
                                <?php
                                echo JFactory::getDate(strtotime($result->formation_end))->format($mod_em_campaign_date_format);
                                ?>
                                <br>
                            <?php endif; ?>
						</div>
					</div>
					<div class="below-content">
						<?php if ($result->apply_online == 1) {
						    $btn_class = "btn btn-primary btn-creux btn-orange";
						} else {
						    $btn_class = "btn btn-primary btn-plein btn-blue";
                        } ?>
                        <?php if ($mod_em_campaign_get_link) :?>
                            <a class="<?php echo $btn_class; ?>" role="button" href='<?php echo !empty($result->link) ? $result->link : "index.php?option=com_emundus&view=programme&id=".$result->id."&Itemid=".$mod_em_campaign_itemid2; ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                        <?php else :?>
                            <a class="btn btn-primary btn-creux btn-orange" role="button" href='<?php echo "index.php?option=com_emundus&view=programme&id=".$result->id."&Itemid=".$mod_em_campaign_itemid2; ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
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

	<div id="past" class="tab-pane fade in active">
		<div class="campaigns-list">
			<?php echo $paginationPast->getResultsCounter(); ?>
			<?php if (empty($pastCampaign)) { ?>
				<div class="alert alert-warning"><?php echo JText::_('NO_RESULT_FOUND') ?></div>
			<?php } else {
					$oldmonth = '';

					foreach ($pastCampaign as $result) {
					if ($order == "start_date") {
						$month = utf8_encode(strftime("%B %Y", strtotime($result->start_date)));
					} else {
						$month = utf8_encode(strftime("%B %Y", strtotime($result->end_date)));
					}
					if ($oldmonth != $month) {
						if (!empty($oldmonth)) { ?>
				</div> <!-- close campaign block (rt12 toclose) -->
			</div> <!-- close campaignbymonth block -->
		<?php } ?>
		<div class="g-block size-100 campaignbymonth">
			<div class="campaign-month-label">
				<div class="position-me">
					<div class="rotate-me <?php echo $mod_em_campaign_class; ?>">
						<p><?php echo ucfirst($month); ?></p>
					</div>
				</div>
			</div>
			<div class="campaign-month-campaigns"><!-- rt12 toclose -->
				<?php } ?>
			<div class="campaign-content">
				<div class="left-side campaigntext <?php echo $mod_em_campaign_class; ?>">
					<h4><a href="/index.php?option=com_emundus&view=programme&id=<?php echo $result->id ?><?php if($result->apply_online==1) {echo "&Itemid=".$mod_em_campaign_itemid;} else {echo "&Itemid=".$mod_em_campaign_itemid2;} ?>"><?php echo $result->label; ?></a></h4>
					<p>
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
				<div class="right-side campaingapply <?php echo $mod_em_campaign_class; ?>">
					<div class="campaingapplycontent">
						<b><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?></b><br />
                        <?php if ($mod_em_campaign_show_camp_start_date && $result->start_date != '0000-00-00 00:00:00') :?>
                            <strong><i class="icon-time"></i> <?php echo JText::_('CAMPAIGN_START_DATE'); ?>:</strong>
                            <?php echo JFactory::getDate(strtotime($result->start_date))->format($mod_em_campaign_date_format); ?>
                            <br>
                        <?php endif; ?>

                        <?php if ($mod_em_campaign_show_camp_end_date && $result->end_date != '0000-00-00 00:00:00') :?>
                            <strong><i class="icon-time <?php echo ($j<1 && $h<=1)?'red':'';?>"></i> <?php echo JText::_('CAMPAIGN_END_DATE'); ?>:</strong>
                            <?php echo JFactory::getDate(strtotime($result->end_date))->format($mod_em_campaign_date_format); ?>
                            <br>
                        <?php endif; ?>

                        <?php if ($mod_em_campaign_show_formation_start_date && $result->formation_start !== '0000-00-00 00:00:00') :?>
                            <strong><?php echo JText::_('FORMATION_START_DATE'); ?>:</strong>
                            <?php echo JFactory::getDate(strtotime($result->formation_start))->format($mod_em_campaign_date_format); ?>
                            <br>
                        <?php endif;?>

                        <?php if ($mod_em_campaign_show_formation_end_date && $result->formation_end !== '0000-00-00 00:00:00') :?>
                            <strong><?php echo JText::_('FORMATION_END_DATE'); ?>:</strong>
                            <?php echo JFactory::getDate(strtotime($result->formation_end))->format($mod_em_campaign_date_format); ?>
                            <br>
                        <?php endif; ?>
					</div>
				</div>
				<div class="below-content">
					<?php if ($result->apply_online == 1) {
						    $btn_class = "btn btn-primary btn-creux btn-orange";
						} else {
						    $btn_class = "btn btn-primary btn-plein btn-blue";
                        } ?>
                        <?php if ($mod_em_campaign_get_link) :?>
                            <a class="<?php echo $btn_class; ?>" role="button" href='<?php echo !empty($result->link) ? $result->link : "index.php?option=com_emundus&view=programme&id=".$result->id."&Itemid=".$mod_em_campaign_itemid2; ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                        <?php else :?>
                            <a class="<?php echo $btn_class; ?>" role="button" href='<?php echo "index.php?option=com_emundus&view=programme&id=".$result->id."&Itemid=".$mod_em_campaign_itemid2; ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
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
		</div><!-- Close tab-content -->
	</form>
<script type="text/javascript">
    jQuery(document).ready(function() {

        var tabsidshow = jQuery.cookie("tabactive");
        if (tabsidshow === undefined) {
            jQuery('#tabslist a[href="#current"]').tab('show');
            jQuery.cookie("tabactive", "current");
        } else {
            jQuery('#tabslist a[href="#' + tabsidshow + '"]').tab('show');
        }

        jQuery('#tabslist a').click(function (e) {
            e.preventDefault();
            var id = jQuery(this).attr("href").substr(1);
            jQuery.cookie("tabactive", id);
            jQuery(this).tab('show');

            // This timeout waits for the animation to complete before resizing the label.
            setTimeout(function() {
                if (jQuery(window).width() > 768) {
                    jQuery('.position-me').each(function () {
                        var h = jQuery(this).parent().parent().height()-23;
                        jQuery(this).width(h);
                    });
                }
                else if (jQuery(window).width() == 768) {
                    jQuery('.position-me').each(function () {
                        var h = jQuery(this).parent().parent().height()-38;
                        jQuery(this).width(h);
                    });
                }
            }, 200);

        });

        if (jQuery(window).width() > 768) {
            jQuery('.position-me').each(function () {
                var h = jQuery(this).parent().parent().height()-23;
                jQuery(this).width(h);
            });
        }
        else if (jQuery(window).width() == 768) {
            jQuery('.position-me').each(function () {
                var h = jQuery(this).parent().parent().height()-38;
                jQuery(this).width(h);
            });
        }
    });
</script>
