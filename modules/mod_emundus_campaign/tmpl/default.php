<?php
defined('_JEXEC') or die; 

$lang = JFactory::getLanguage();
$locallang = $lang->getTag(); 
if ($locallang == "fr-FR") {
    setlocale(LC_TIME, 'fr', 'fr_FR', 'french', 'fra', 'fra_FRA', 'fr_FR.ISO_8859-1', 'fra_FRA.ISO_8859-1', 'fr_FR.utf8', 'fr_FR.utf-8', 'fra_FRA.utf8', 'fra_FRA.utf-8');
} else {
    setlocale (LC_ALL, 'en_GB');
}

?>
<form action="<?php echo JRoute::_(JUri::getInstance()->toString(), true, $params->get('')); ?>" method="post" id="search_program">
    <?php if(isset($_POST['searchword']) && !empty($_POST['searchword'])) { ?>
        <div class="rt-grid-12">
            <p><b><?php echo JText::_("RESULT_FOR")." : ".$_POST['searchword']; ?></b></p>
        </div>
    <?php } ?>
    <div class="rt-grid-12" id="navfilter">
        <div class="rt-grid-5 navrowtabs">
            <ul id="tabslist" class="nav nav-tabs">
                <?php if($mod_em_campaign_param_tab) {?>
                    <?php foreach($mod_em_campaign_list_tab as $tab) {?>
                        <li role="presentation"><a data-toggle="tab" href="#<?php echo $tab ?>"><?php echo JText::_("MOD_EM_CAMPAIGN_LIST_".strtoupper($tab)); ?></a></li>
                    <?php } ?>
                <?php } ?>
            </ul>
        </div>
        <div class="rt-grid-3 navorder">
            <p><?php if ($order=="start_date") {?>
                <?php if ($ordertime=="desc") {?><a href="index.php?order_date=start_date&order_time=asc"><i class="icon-chevron-down" aria-hidden="true"></i> <?php } else { ?><a href="index.php?order_date=start_date&order_time=desc"><i class="icon-chevron-up" aria-hidden="true"></i> <?php }?> <b><?php echo JText::_("CAMPAIGN_START_DATE");?></b></a> |  <a href="index.php?order_date=end_date&ordertime=<?php echo $ordertime ?>"><?php echo JText::_("LIST_DATE_END");?></a>
                    <?php } else { ?>
                    <a href="index.php?order_date=start_date&order_time=<?php echo $ordertime ?>"><?php echo JText::_("CAMPAIGN_START_DATE");?></a>  |  <?php if ($ordertime=="desc") {?><a href="index.php?order_date=end_date&order_time=asc"><i class="icon-chevron-down" aria-hidden="true"></i> <?php } else { ?><a href="index.php?order_date=end_date&ordertime=desc"><i class="icon-chevron-up" aria-hidden="true"></i> <?php }?> <b><?php echo JText::_("LIST_DATE_END");?></b></a>
                        <?php }?> </p>
        </div>
        <div class="rt-grid-4 navsearch">
            <div class="navsearch-content">
                <div class="rt-grid-4">
                    <div class="input-group">
                        <input name="searchword" type="text" class="form-control" placeholder="<?php  echo JText::_("SEARCH")."..." ; ?>" <?php if (isset($_POST['searchword']) && !empty($_POST['searchword'])) { echo "value=".$_POST['searchword'];}; ?>>
                        <span class="input-group-btn">
                            <button class="btn btn-default sch" type="submit"><?php  echo JText::_("SEARCH"); ?></button>
                        </span>
                    </div><!-- /input-group -->
                </div><!-- /.col-lg-6 -->
            </div>
        </div>
        <div class="rt-grid-1"></div>
    </div>


    <div class="tab-content">
        <div id="current" class="tab-pane fade in active">
            <div class="rt-grid-12">
                <?php if (empty($currentCampaign)) { ?>
                    <div class="alert alert-warning"><?php echo JText::_('NO_RESULT_FOUND') ?></div>
                <?php } else {
                    $oldmonth = '';
                    
                    foreach($currentCampaign as $resul) {
                        if ($order == "start_date") {
                            $month = strftime("%B %Y", strtotime($resul->start_date));
                        } else {
                            $month = strftime("%B %Y", strtotime($resul->end_date));
                        }
                        if ($oldmonth != $month) {
                            if (!empty($oldmonth)) { ?>
                                    </div> <!-- close campaign block -->
                                </div> <!-- close campaignbymonth block -->
                            <?php } ?>
                            <div class="rt-grid-12 campaignbymonth">
                            <div class="rt-grid-12">
                                <div class="position-me">
                                    <div class="rotate-me">
                                        <p><?php echo ucfirst(utf8_encode($month)); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="rt-grid-12">
                        <?php } ?>
                        <div class="rt-grid-12 campaignright">
                            <div class="rt-grid-9 campaigntext">
                                <h4><a href="index.php?option=com_emundus&view=programme<?php if($resul->apply_online==1) {echo "&Itemid=".$mod_em_campaign_itemid;} ?>&id=<?php echo $resul->id ?>"><?php echo $resul->label; ?></a></h4>
                                <p>
                                    <?php
                                    $text = '';
                                    $textprog = '';
                                    $textcamp = '';
                                    if ($showcampaign) {
                                        $textcamp = strip_tags($resul->short_description);
                                    }
                                    //$text = $textprog."<br />".$textcamp;
                                    echo $textcamp;
                                    ?>
                                </p>
                            </div>
                            <div class="rt-grid-3 campaingapply">
                                <div class="campaingapplycontent">
                                    <fieldset class="apply-now-small">
                                        <legend>
                                            <?php if($resul->apply_online==1) {?>
                                                <a class="btn btn-primary" role="button" href='<?php echo ("index.php?option=com_emundus&view=programme&id=".$resul->id."&Itemid=".$mod_em_campaign_itemid); ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                                                <a class="btn btn-info" role="button" href='<?php echo ("index.php?option=com_users&view=registration&course=".$resul->code);?>' data-toggle="sc-modal"><?php echo JText::_('APPLY_NOW'); ?></a>
                                            <?php } else { ?>
                                                <a class="btn btn-primary" role="button" href='<?php echo ("index.php?option=com_emundus&view=programme&id=".$resul->id."&Itemid=".$mod_em_campaign_itemid2); ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                                            <?php } ?>
                                        </legend>
                                        <b><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?> :</b><br />
                                        <strong><i class="icon-time"></i> <?php echo JText::_('CAMPAIGN_START_DATE'); ?>:</strong>
                                        <?php echo date('d/m/Y H:i', strtotime($resul->start_date)); ?><br>
                                        <strong><i class="icon-time"></i> <?php echo JText::_('CAMPAIGN_END_DATE'); ?>:</strong>
                                        <?php echo date('d/m/Y H:i', strtotime($resul->end_date)); ?>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                        <?php
                        $oldmonth = $month;
                    } ?>
                            </div> <!-- close last campaign block -->
                    </div> <!-- close last campaignbymonth block -->
            <?php } ?>
            </div>
            <div class="pagination"><?php  // echo modEmundusCampaignHelper::getPaginationCurrent($condition)->getPagesLinks();
                                            //echo modEmundusCampaignHelper::getPaginationCurrent($condition)->getPagesCounter(); ?></div>
        </div>

        <div id="futur" class="tab-pane fade in active">
            <div class="rt-grid-12">
                <?php if (empty($futurCampaign)) { ?>
                    <div class="alert alert-warning"><?php echo JText::_('NO_RESULT_FOUND') ?></div>
                <?php } else {
                    $oldmonth = '';

                    foreach($futurCampaign as $resul) {
                        if ($order == "start_date") {
                            $month = strftime("%B %Y", strtotime($resul->start_date));
                        } else {
                            $month = strftime("%B %Y", strtotime($resul->end_date));
                        }
                        if ($oldmonth != $month) {
                            if (!empty($oldmonth)) { ?>
                                    </div> <!-- close campaign block -->
                                </div> <!-- close campaignbymonth block -->
                            <?php } ?>
                            <div class="rt-grid-12 campaignbymonth">
                            <div class="rt-grid-12">
                                <div class="position-me">
                                    <div class="rotate-me">
                                        <p><?php echo ucfirst(utf8_encode($month)); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="rt-grid-12">
                        <?php } ?>
                        <div class="rt-grid-12 campaignright">
                            <div class="rt-grid-9 campaigntext">
                                <h4><a href="index.php?option=com_emundus&view=programme<?php if($resul->apply_online==1) {echo "&Itemid=".$mod_em_campaign_itemid;} ?>&id=<?php echo $resul->id ?>"><?php echo $resul->label; ?></a></h4>
                                <p>
                                    <?php
                                    $text = '';
                                    $textprog = '';
                                    $textcamp = '';
                                    if ($showcampaign) {
                                        $textcamp = strip_tags($resul->short_description);
                                    }
                                    //$text = $textprog."<br />".$textcamp;
                                    echo $textcamp;
                                    ?>
                                </p>
                            </div>
                            <div class="rt-grid-3 campaingapply">
                                <div class="campaingapplycontent">
                                    <fieldset class="apply-now-small">
                                        <legend>
                                            <?php if($resul->apply_online==1) {?>
                                                <a class="btn btn-primary" role="button" href='<?php echo ("index.php?option=com_emundus&view=programme&id=".$resul->id); ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                                                <a class="btn btn-info" role="button" href='<?php echo ("index.php?option=com_users&view=registration&course=".$resul->code);?>' data-toggle="sc-modal"><?php echo JText::_('APPLY_NOW'); ?></a>
                                            <?php } else { ?>
                                                <a class="btn btn-primary" role="button" href='<?php echo ("index.php?option=com_emundus&view=programme&id=".$resul->id); ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                                            <?php } ?>
                                        </legend>
                                        <b><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?> :</b><br />
                                        <strong><i class="icon-time"></i> <?php echo JText::_('CAMPAIGN_START_DATE'); ?>:</strong>
                                        <?php echo date('d/m/Y H:i', strtotime($resul->start_date)); ?><br>
                                        <strong><i class="icon-time"></i> <?php echo JText::_('CAMPAIGN_END_DATE'); ?>:</strong>
                                        <?php echo date('d/m/Y H:i', strtotime($resul->end_date)); ?>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                        <?php
                        $oldmonth = $month;
                    } ?>
                            </div> <!-- close last campaign block -->
                    </div> <!-- close last campaignbymonth block -->
            <?php } ?>
            </div>
        </div>

        <div id="past" class="tab-pane fade in active">
            <div class="rt-grid-12">
                <?php if (empty($pastCampaign)) { ?>
                    <div class="alert alert-warning"><?php echo JText::_('NO_RESULT_FOUND') ?></div>
                <?php } else {
                    $oldmonth = '';

                    foreach($pastCampaign as $resul) {
                        if ($order == "start_date") {
                            $month = strftime("%B %Y", strtotime($resul->start_date));
                        } else {
                            $month = strftime("%B %Y", strtotime($resul->end_date));
                        }
                        if ($oldmonth != $month) {
                            if (!empty($oldmonth)) { ?>
                                    </div> <!-- close campaign block -->
                                </div> <!-- close campaignbymonth block -->
                            <?php } ?>
                            <div class="rt-grid-12 campaignbymonth">
                            <div class="rt-grid-12">
                                <div class="position-me">
                                    <div class="rotate-me">
                                        <p><?php echo ucfirst(utf8_encode($month)); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="rt-grid-12">
                        <?php } ?>
                        <div class="rt-grid-12 campaignright">
                            <div class="rt-grid-9 campaigntext">
                                <h4><a href="index.php?option=com_emundus&view=programme<?php if($resul->apply_online==1) {echo "&Itemid=".$mod_em_campaign_itemid;} ?>&id=<?php echo $resul->id ?>"><?php echo $resul->label; ?></a></h4>
                                <p>
                                    <?php
                                    $text = '';
                                    $textprog = '';
                                    $textcamp = '';
                                    if ($showcampaign) {
                                        $textcamp = strip_tags($resul->short_description);
                                    }
                                    //$text = $textprog."<br />".$textcamp;
                                    echo $textcamp;
                                    ?>
                                </p>
                            </div>
                            <div class="rt-grid-3 campaingapply">
                                <div class="campaingapplycontent">
                                    <fieldset class="apply-now-small">
                                        <legend>
                                            <?php if($resul->apply_online==1) {?>
                                                <a class="btn btn-primary" role="button" href='<?php echo ("index.php?option=com_emundus&view=programme&id=".$resul->id); ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                                                <a class="btn btn-info" role="button" href='<?php echo ("index.php?option=com_users&view=registration&course=".$resul->code);?>' data-toggle="sc-modal"><?php echo JText::_('APPLY_NOW'); ?></a>
                                            <?php } else { ?>
                                                <a class="btn btn-primary" role="button" href='<?php echo ("index.php?option=com_emundus&view=programme&id=".$resul->id); ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                                            <?php } ?>
                                        </legend>
                                        <b><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?> :</b><br />
                                        <strong><i class="icon-time"></i> <?php echo JText::_('CAMPAIGN_START_DATE'); ?>:</strong>
                                        <?php echo date('d/m/Y H:i', strtotime($resul->start_date)); ?><br>
                                        <strong><i class="icon-time"></i> <?php echo JText::_('CAMPAIGN_END_DATE'); ?>:</strong>
                                        <?php echo date('d/m/Y H:i', strtotime($resul->end_date)); ?>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                        <?php
                        $oldmonth = $month;
                    } ?>
                            </div> <!-- close last campaign block -->
                    </div> <!-- close last campaignbymonth block -->
            <?php } ?>
            </div>
        </div>

        <div id="all" class="tab-pane fade in active">
            <div class="rt-grid-12">
                <?php if (empty($allCampaign)) { ?>
                    <div class="alert alert-warning"><?php echo JText::_('NO_RESULT_FOUND') ?></div>
                <?php } else {
                    $oldmonth = '';

                    foreach($allCampaign as $resul) {
                        if ($order == "start_date") {
                            $month = strftime("%B %Y", strtotime($resul->start_date));
                        } else {
                            $month = strftime("%B %Y", strtotime($resul->end_date));
                        }
                        if ($oldmonth != $month) {
                            if (!empty($oldmonth)) { ?>
                                    </div> <!-- close campaign block -->
                                </div> <!-- close campaignbymonth block -->
                            <?php } ?>
                            <div class="rt-grid-12 campaignbymonth">
                            <div class="rt-grid-12">
                                <div class="position-me">
                                    <div class="rotate-me">
                                        <p><?php echo ucfirst(utf8_encode($month)); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="rt-grid-12">
                        <?php } ?>
                        <div class="rt-grid-12 campaignright">
                            <div class="rt-grid-9 campaigntext">
                                <h4><a href="index.php?option=com_emundus&view=programme<?php if($resul->apply_online==1) {echo "&Itemid=".$mod_em_campaign_itemid;} ?>&id=<?php echo $resul->id ?>"><?php echo $resul->label; ?></a></h4>
                                <p>
                                    <?php
                                    $text = '';
                                    $textprog = '';
                                    $textcamp = '';
                                    if ($showcampaign) {
                                        $textcamp = strip_tags($resul->short_description);
                                    }
                                    //$text = $textprog."<br />".$textcamp;
                                    echo $textcamp;
                                    ?>
                                </p>
                            </div>
                            <div class="rt-grid-3 campaingapply">
                                <div class="campaingapplycontent">
                                    <fieldset class="apply-now-small">
                                        <legend>
                                            <?php if($resul->apply_online==1) {?>
                                                <a class="btn btn-primary" role="button" href='<?php echo ("index.php?option=com_emundus&view=programme&id=".$resul->id."&Itemid=".$mod_em_campaign_itemid); ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                                                <a class="btn btn-info" role="button" href='<?php echo ("index.php?option=com_users&view=registration&course=".$resul->code);?>' data-toggle="sc-modal"><?php echo JText::_('APPLY_NOW'); ?></a>
                                            <?php } else { ?>
                                                <a class="btn btn-primary" role="button" href='<?php echo ("index.php?option=com_emundus&view=programme&id=".$resul->id."&Itemid=".$mod_em_campaign_itemid2); ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                                            <?php } ?>
                                        </legend>
                                        <b><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?> :</b><br />
                                        <strong><i class="icon-time"></i> <?php echo JText::_('CAMPAIGN_START_DATE'); ?>:</strong>
                                        <?php echo date('d/m/Y H:i', strtotime($resul->start_date)); ?><br />
                                        <strong><i class="icon-time"></i> <?php echo JText::_('CAMPAIGN_END_DATE'); ?>:</strong>
                                        <?php echo date('d/m/Y H:i', strtotime($resul->end_date)); ?>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                        <?php
                        $oldmonth = $month;
                    } ?>
                            </div> <!-- close last campaign block -->
                    </div> <!-- close last campaignbymonth block -->
            <?php } ?>
            </div>
        </div>
    </div>
    <div class="separator" style="height:100px"></div>
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
            if (jQuery(window).width() >= 767) {
                jQuery('.position-me').each(function () {
                    var h = jQuery(this).parent().parent().height()-20; 
                    jQuery(this).width(h);
                });
                jQuery('.campaingapply').each(function () {
                    var h = jQuery(this).parent().height()-2;
                    jQuery(this).height(h);
                });
            };
        });
        if (jQuery(window).width() >= 767) {
            jQuery('.position-me').each(function () {
                var h = jQuery(this).parent().parent().height()-20;
                jQuery(this).width(h);
            });
            jQuery('.campaingapply').each(function () {
                var h = jQuery(this).parent().height()-2;
                jQuery(this).height(h);
            });
        };
    });
</script>


