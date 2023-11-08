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

?>

<?= $mod_em_campaign_intro; ?>

<form action="index.php" method="post" id="search_program">
	<?php if (isset($searchword) && !empty($searchword)) : ?>
        <div class="rt-grid-12">
            <p>
                <b><?php echo JText::_("MOD_EM_CAMPAIGN_RESULT_FOR") . " : " . htmlspecialchars($searchword, ENT_QUOTES, 'UTF-8'); ?></b>
            </p>
        </div>
	<?php endif; ?>
    <div class="rt-grid-12" id="navfilter">
        <div class="rt-grid-4 navrowtabs">
            <ul id="tabslist" class="nav nav-tabs">
				<?php if ($mod_em_campaign_param_tab) : ?>
					<?php foreach ($mod_em_campaign_list_tab as $tab) : ?>
                        <li role="presentation"><a data-toggle="tab"
                                                   href="#<?php echo $tab ?>"><?php echo JText::_("MOD_EM_CAMPAIGN_LIST_" . strtoupper($tab)); ?></a>
                        </li>
					<?php endforeach; ?>
				<?php endif; ?>
            </ul>
        </div>
        <div class="rt-grid-4 navorder">
            <p>
				<?php if ($order == "start_date") : ?>
				<?php if ($ordertime == "desc") : ?>
                <a href="index.php?order_date=start_date&order_time=asc"><i class="icon-chevron-down"
                                                                            aria-hidden="true"></i>
					<?php else : ?>
                    <a href="index.php?order_date=start_date&order_time=desc"><i class="icon-chevron-up"
                                                                                 aria-hidden="true"></i>
						<?php endif; ?>
                        <b><?php echo JText::_("MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE"); ?></b></a> | <a
                            href="index.php?order_date=end_date&ordertime=<?php echo $ordertime ?>"><?php echo JText::_("MOD_EM_CAMPAIGN_LIST_DATE_END"); ?></a>
					<?php else : ?>
                    <a href="index.php?order_date=start_date&order_time=<?php echo $ordertime ?>"><?php echo JText::_("MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE"); ?></a>
                    | <?php if ($ordertime == "desc") : ?> <a href="index.php?order_date=end_date&order_time=asc"><i
                                class="icon-chevron-down" aria-hidden="true"></i> <?php else : ?><a
                                href="index.php?order_date=end_date&ordertime=desc"><i class="icon-chevron-up"
                                                                                       aria-hidden="true"></i> <?php endif; ?>
                            <b><?php echo JText::_("MOD_EM_CAMPAIGN_LIST_DATE_END"); ?></b></a>
						<?php endif; ?></p>
        </div>
        <div class="rt-grid-4 navsearch">
            <div class="navsearch-content">
                <div class="rt-grid-4">
                    <div class="input-group">
                        <input name="searchword" type="text" class="form-control"
                               placeholder=" <?php echo JText::_("MOD_EM_CAMPAIGN_SEARCH") . "..."; ?>" <?php if (isset($searchword) && !empty($searchword)) {
							echo "value=" . htmlspecialchars($searchword, ENT_QUOTES, 'UTF-8');
						}; ?>>
                        <span class="input-group-btn">
                            <button class="btn btn-default sch"
                                    type="submit"><?php echo JText::_("MOD_EM_CAMPAIGN_SEARCH"); ?></button>
                        </span>
                    </div><!-- /input-group -->
                </div><!-- /.col-lg-6 -->
            </div>
        </div>
        <div class="rt-grid-1"></div>
    </div>

    <div class="tab-content">
		<?php if (in_array('current', $mod_em_campaign_list_tab)) : ?>
        <div id="current" class="tab-pane fade in active">
            <div class="campaigns-list">
				<?php echo $paginationCurrent->getResultsCounter(); ?>
				<?php if (empty($currentCampaign)) { ?>
                    <div class="alert alert-warning"><?php echo JText::_('MOD_EM_CAMPAIGN_NO_RESULT_FOUND') ?></div>
				<?php } else {
				$oldmonth = '';

				foreach ($currentCampaign

				as $result) {
				$dteStart = new DateTime($now);
				$dteEnd   = new DateTime($result->end_date);
				$dteDiff  = $dteStart->diff($dteEnd);
				$j        = $dteDiff->format("%a");
				$h        = $dteDiff->format("%H");

				if ($order == "start_date")
					$month = utf8_encode(strftime("%B %Y", strtotime($result->start_date)));
				else
					$month = utf8_encode(strftime("%B %Y", strtotime($result->end_date)));

				if ($oldmonth != $month) {
				if (!empty($oldmonth)) { ?>
            </div> <!-- close campaign block (rt12 toclose) -->
        </div> <!-- close campaignbymonth block -->
	<?php } ?>
    <div class="rt-grid-12 campaignbymonth">
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
            <h4>
                <a href="index.php?option=com_emundus&view=programme&cid=<?php echo $result->id ?><?php if ($result->apply_online == 1) {
					echo "&Itemid=" . $mod_em_campaign_itemid;
				} else {
					echo "&Itemid=" . $mod_em_campaign_itemid2;
				} ?>"><?php echo $result->label; ?></a></h4>
            <p>
				<?php
				$text     = '';
				$textprog = '';
				$textcamp = '';
				if ($showcampaign)
					$textcamp = strip_tags($result->short_description);
				//$text = $textprog."<br />".$textcamp;
				echo $textcamp;
				?>
            </p>
        </div>
        <div class="right-side campaingapply <?php echo $mod_em_campaign_class; ?>">
            <div class="campaingapplycontent">
                <b><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?></b><br/>
                <strong><i class="icon-clock"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE'); ?>
                </strong>
				<?php echo date($mod_em_campaign_date_format, strtotime($result->start_date)); ?><br>
                <strong><i class="icon-clock <?php echo ($j < 1 && $h <= 1) ? 'red' : ''; ?>"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_END_DATE'); ?>
                </strong>
				<?php echo date($mod_em_campaign_date_format, strtotime($result->end_date)); ?>
				<?php echo (!empty($mod_em_campaign_show_timezone)) ? '<hr>' . JText::_('MOD_EM_CAMPAIGN_TIMEZONE') . $offset : ''; ?>
				<?php echo (!empty($mod_em_campaign_show_localedate)) ? '<hr>' . JText::_('MOD_EM_CAMPAIGN_LOCALDATE') . date($mod_em_campaign_date_format, strtotime($now)) : ''; ?>
            </div>
        </div>
        <div class="below-content">
			<?php if ($result->apply_online == 1) : ?>
                <a class="btn btn-primary btn-creux btn-orange" role="button"
                   href='<?php echo("index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid); ?>'
                   data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
				<?php
				// The register URL does not
				if ($sef == 0)
					$register_url = "index.php?option=com_users&view=registration&course=" . $result->code . "&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid;
				else
					$register_url = "registration?course=" . $result->code . "&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid;
				?>
                <a class="btn btn-primary btn-plein btn-blue" role="button" href='<?php echo $register_url; ?>'
                   data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_APPLY_NOW'); ?></a>
			<?php else : ?>
                <a class="btn btn-primary btn-plein btn-blue" role="button"
                   href='<?php echo("index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2); ?>'
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
    <div class="pagination"><?php // echo modEmundusCampaignHelper::getPaginationCurrent($condition)->getPagesLinks();
		//echo modEmundusCampaignHelper::getPaginationCurrent($condition)->getPagesCounter(); ?></div>
    </div><!-- Close current tab -->
<?php endif; ?>

	<?php if (in_array('futur', $mod_em_campaign_list_tab)) : ?>
        <div id="futur" class="tab-pane fade in active">
        <div class="campaigns-list">
		<?php echo $paginationFutur->getResultsCounter(); ?>
		<?php if (empty($futurCampaign)) { ?>
            <div class="alert alert-warning"><?php echo JText::_('MOD_EM_CAMPAIGN_NO_RESULT_FOUND') ?></div>
		<?php }
		else {
			$oldmonth = '';

			foreach ($futurCampaign as $result) {
				if ($order == "start_date")
					$month = strftime("%B %Y", strtotime($result->start_date));
				else
					$month = strftime("%B %Y", strtotime($result->end_date));
				if ($oldmonth != $month) {
					if (!empty($oldmonth)) { ?>
                        </div> <!-- close campaign block (rt12 toclose) -->
                        </div> <!-- close campaignbymonth block -->
					<?php } ?>
                    <div class="rt-grid-12 campaignbymonth">
                    <div class="campaign-month-label">
                        <div class="position-me">
                            <div class="rotate-me <?php echo $mod_em_campaign_class; ?>">
                                <p><?php echo ucfirst(utf8_encode($month)); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="campaign-month-campaigns"><!-- rt12 toclose -->
				<?php } ?>
                <div class="campaign-content">
                    <div class="left-side campaigntext <?php echo $mod_em_campaign_class; ?>">
                        <h4>
                            <a href="index.php?option=com_emundus&view=programme&cid=<?php echo $result->id ?><?php if ($result->apply_online == 1) {
								echo "&Itemid=" . $mod_em_campaign_itemid;
							} else {
								echo "&Itemid=" . $mod_em_campaign_itemid2;
							} ?>"><?php echo $result->label; ?></a></h4>
                        <p>
							<?php
							$text     = '';
							$textprog = '';
							$textcamp = '';
							if ($showcampaign)
								$textcamp = strip_tags($result->short_description);
							//$text = $textprog."<br />".$textcamp;
							echo $textcamp;
							?>
                        </p>
                    </div>
                    <div class="right-side campaingapply <?php echo $mod_em_campaign_class; ?>">
                        <div class="campaingapplycontent">
                            <b><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?></b><br/>
                            <strong><i class="icon-clock"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE'); ?>
                            </strong>
							<?php echo date($mod_em_campaign_date_format, strtotime($result->start_date)); ?><br>
                            <strong><i class="icon-clock <?php echo ($j < 1 && $h <= 1) ? 'red' : ''; ?>"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_END_DATE'); ?>
                            </strong>
							<?= date($mod_em_campaign_date_format, strtotime($result->end_date)); ?>
                        </div>
                    </div>
                    <div class="below-content">
						<?php if ($result->apply_online == 1) : ?>
                            <a class="btn btn-primary btn-creux btn-orange" role="button"
                               href='<?php echo("index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid); ?>'
                               data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
						<?php else : ?>
                            <a class="btn btn-primary btn-plein btn-blue" role="button"
                               href='<?php echo("index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2); ?>'
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

	<?php if (in_array('past', $mod_em_campaign_list_tab)) : ?>
        <div id="past" class="tab-pane fade in active">
        <div class="campaigns-list">
		<?php echo $paginationPast->getResultsCounter(); ?>
		<?php if (empty($pastCampaign)) { ?>
            <div class="alert alert-warning"><?php echo JText::_('MOD_EM_CAMPAIGN_NO_RESULT_FOUND') ?></div>
		<?php }
		else {
			$oldmonth = '';

			foreach ($pastCampaign as $result) {

				if ($order == "start_date")
					$month = strftime("%B %Y", strtotime($result->start_date));
				else
					$month = strftime("%B %Y", strtotime($result->end_date));

				if ($oldmonth != $month) {
					if (!empty($oldmonth)) { ?>
                        </div> <!-- close campaign block (rt12 toclose) -->
                        </div> <!-- close campaignbymonth block -->
					<?php } ?>
                    <div class="rt-grid-12 campaignbymonth">
                    <div class="campaign-month-label">
                        <div class="position-me">
                            <div class="rotate-me <?php echo $mod_em_campaign_class; ?>">
                                <p><?php echo ucfirst(utf8_encode($month)); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="campaign-month-campaigns"><!-- rt12 toclose -->
				<?php } ?>
                <div class="campaign-content">
                    <div class="left-side campaigntext <?php echo $mod_em_campaign_class; ?>">
                        <h4>
                            <a href="index.php?option=com_emundus&view=programme&cid=<?php echo $result->id ?><?php if ($result->apply_online == 1) {
								echo "&Itemid=" . $mod_em_campaign_itemid;
							} else {
								echo "&Itemid=" . $mod_em_campaign_itemid2;
							} ?>"><?php echo $result->label; ?></a></h4>
                        <p>
							<?php
							$text     = '';
							$textprog = '';
							$textcamp = '';
							if ($showcampaign)
								$textcamp = strip_tags($result->short_description);
							//$text = $textprog."<br />".$textcamp;
							echo $textcamp;
							?>
                        </p>
                    </div>
                    <div class="right-side campaingapply <?php echo $mod_em_campaign_class; ?>">
                        <div class="campaingapplycontent">
                            <b><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?></b><br/>
                            <strong><i class="icon-clock"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE'); ?>
                            </strong>
							<?php echo date($mod_em_campaign_date_format, strtotime($result->start_date)); ?><br>
                            <strong><i class="icon-clock <?php echo ($j < 1 && $h <= 1) ? 'red' : ''; ?>"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_END_DATE'); ?>
                            </strong>
							<?php echo date($mod_em_campaign_date_format, strtotime($result->end_date)); ?>
                        </div>
                    </div>
                    <div class="below-content">
						<?php if ($result->apply_online == 1) : ?>
                            <a class="btn btn-primary btn-creux btn-orange" role="button"
                               href='<?php echo("index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid); ?>'
                               data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
						<?php else : ?>
                            <a class="btn btn-primary btn-plein btn-blue" role="button"
                               href='<?php echo("index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2); ?>'
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

	<?php if (in_array('all', $mod_em_campaign_list_tab)) : ?>
        <div id="all" class="tab-pane fade in active">
        <div class="campaigns-list">
		<?php echo $paginationTotal->getResultsCounter(); ?>
		<?php if (empty($allCampaign)) { ?>
            <div class="alert alert-warning"><?php echo JText::_('MOD_EM_CAMPAIGN_NO_RESULT_FOUND') ?></div>
		<?php }
		else {
			$oldmonth = '';

			foreach ($allCampaign as $result) {

				if ($order == "start_date")
					$month = strftime("%B %Y", strtotime($result->start_date));
				else
					$month = strftime("%B %Y", strtotime($result->end_date));

				if ($oldmonth != $month) {
					if (!empty($oldmonth)) { ?>
                        </div> <!-- close campaign block (rt12 toclose) -->
                        </div> <!-- close campaignbymonth block -->
					<?php } ?>
                    <div class="rt-grid-12 campaignbymonth">
                    <div class="campaign-month-label">
                        <div class="position-me">
                            <div class="rotate-me <?php echo $mod_em_campaign_class; ?>">
                                <p><?php echo ucfirst(utf8_encode($month)); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="campaign-month-campaigns"><!-- rt12 toclose -->
				<?php } ?>
                <div class="campaign-content">
                    <div class="left-side campaigntext <?php echo $mod_em_campaign_class; ?>">
                        <h4>
                            <a href="index.php?option=com_emundus&view=programme&cid=<?php echo $result->id ?><?php if ($result->apply_online == 1) {
								echo "&Itemid=" . $mod_em_campaign_itemid;
							} else {
								echo "&Itemid=" . $mod_em_campaign_itemid2;
							} ?>"><?php echo $result->label; ?></a></h4>
                        <p>
							<?php
							$text     = '';
							$textprog = '';
							$textcamp = '';
							if ($showcampaign)
								$textcamp = strip_tags($result->short_description);
							//$text = $textprog."<br />".$textcamp;
							echo $textcamp;
							?>
                        </p>
                    </div>
                    <div class="right-side campaingapply <?php echo $mod_em_campaign_class; ?>">
                        <div class="campaingapplycontent">
                            <b><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?></b><br/>
                            <strong><i class="icon-clock"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE'); ?>
                            </strong>
							<?php echo date($mod_em_campaign_date_format, strtotime($result->start_date)); ?><br>
                            <strong><i class="icon-clock <?php echo ($j < 1 && $h <= 1) ? 'red' : ''; ?>"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_END_DATE'); ?>
                            </strong>
							<?php echo date($mod_em_campaign_date_format, strtotime($result->end_date)); ?>
                        </div>
                    </div>
                    <div class="below-content">
						<?php if ($result->apply_online == 1) : ?>
                            <a class="btn btn-primary btn-creux btn-orange" role="button"
                               href='<?php echo("index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid); ?>'
                               data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
							<?php if (date('Y/m/d H:i', strtotime($result->start_date)) <= date('Y/m/d H:i') && date('Y/m/d H:i', strtotime($result->end_date)) >= date('Y/m/d H:i')) { ?>
                                <a class="btn btn-primary btn-plein btn-blue" role="button"
                                   href='<?php echo("index.php?option=com_users&view=registration&course=" . $result->code . "&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid); ?>'
                                   data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_APPLY_NOW'); ?></a>
							<?php } ?>
						<?php else : ?>
                            <a class="btn btn-primary btn-plein btn-blue" role="button"
                               href='<?php echo("index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2); ?>'
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
        </div><!-- Close all tab -->
	<?php endif; ?>
    </div><!-- Close tab-content -->
	<?php /*?>
    <div class="separator" style="height:100px"></div>
    <?php */ ?>
</form>
<script type="text/javascript">
    jQuery(document).ready(function () {
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
            //if (jQuery(window).width() >= 767) {
            setTimeout(function () {
                if (jQuery(window).width() > 768) {
                    jQuery('.position-me').each(function () {
                        var h = jQuery(this).parent().parent().height() - 23;
                        jQuery(this).width(h);
                    });
                    /*jQuery('.campaingapply').each(function () {
                        var h = jQuery(this).parent().height()-2;
                        jQuery(this).height(h);
                    });
                    jQuery('.campaigntext').each(function () {
                        var h = jQuery(this).parent().height()-2;
                        jQuery(this).height(h);
                    });*/
                } else if (jQuery(window).width() == 768) {
                    jQuery('.position-me').each(function () {
                        var h = jQuery(this).parent().parent().height() - 38;
                        jQuery(this).width(h);
                    });
                }
            }, 200);
        });
        //if (jQuery(window).width() >= 767) {
        if (jQuery(window).width() > 768) {
            jQuery('.position-me').each(function () {
                var h = jQuery(this).parent().parent().height() - 23;
                jQuery(this).width(h);
            });
            /*jQuery('.campaingapply').each(function () {
			   var h = jQuery(this).parent().height()-2;
			   jQuery(this).height(h);
		   });
		   jQuery('.campaigntext').each(function () {
			   var h = jQuery(this).parent().height()-2;
			   jQuery(this).height(h);
		   });*/
        }
        ;
        if (jQuery(window).width() == 768) {
            jQuery('.position-me').each(function () {
                var h = jQuery(this).parent().parent().height() - 38;
                jQuery(this).width(h);
            });
        }
    });
</script>


