<?php
defined('_JEXEC') or die;

header('Content-Type: text/html; charset=utf-8');

$app = JFactory::getApplication();
$user = JFactory::getUser();
$searchword = $app->input->getString('searchword', null);

$lang = JFactory::getLanguage();
$locallang = $lang->getTag();
if ($locallang == "fr-FR") {
	setlocale(LC_TIME, 'fr', 'fr_FR', 'french', 'fra', 'fra_FRA', 'fr_FR.ISO_8859-1', 'fra_FRA.ISO_8859-1', 'fr_FR.utf8', 'fr_FR.utf-8', 'fra_FRA.utf8', 'fra_FRA.utf-8');
} else {
	setlocale (LC_ALL, 'en_GB');
}
$config = JFactory::getConfig();
$site_offset = $config->get('offset');

?>

<!-- <div class = "navfilter">
    <div class = "depositor">
        <select id = "depositor_select">
        <option value=""><?php echo JText::_('SELECT_DEPOSITOR');?></option>
            <option value="2"><?php echo JText::_('HORS_RESEAUX');?></option>
            <option value="1"><?php echo JText::_('RESEAUX');?></option>
        </select>
    </div>

    <div class = "result-counter">
        <span><?php echo (sizeof($currentCampaign) == 1) ? sizeof($currentCampaign) . " " . JText::_('CURRENT_CAMPAIGN') : sizeof($currentCampaign) . " " . JText::_('CURRENT_CAMPAIGNS'); ?></span>
    </div>
    <div class = "type">
        <select id= "program_type">
            <option value=""><?php echo JText::_('SELECT_PROG_TYPE');?></option>

            <?php
            $programs = array_unique(array_column($programs, 'programmes'));
            foreach($programs as $program => $value) :?>
                <option value = "<?=$value;?>"><?= ucfirst(strtolower($value)); ?></option>
            <?php endforeach ;?>
        </select>
    </div>
</div> -->
<hr>


<div class="tab-content">
    <div id="current" class="tab-pane fade in active">
        <div class="campaigns-list">
            <?php if (empty($currentCampaign)) :?>
                <div class="alert alert-warning"><?php echo JText::_('NO_RESULT_FOUND') ?></div>
            <?php else :?>
                <?php foreach ($currentCampaign as $result) :?>
                    <div class="campaign-content" style="height: 170px">
                        <div class="left-side campaigntext <?php echo $mod_em_campaign_class; ?>">
                            <h4>
                                <?php echo $result->label; ?>
                            </h4>
                            <p>
                                <?php echo $result->short_description;?>
                            </p>
                        </div>
                        <div class="right-side campaingapply <?php echo $mod_em_campaign_class; ?>">
                            <div class="campaingapplycontent">
                                <b class="campaingapplycontent-bold"><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?></b><br class="campaingapplycontent-breaker"/>

                                <?php if ($mod_em_campaign_show_camp_start_date && $result->start_date != '0000-00-00 00:00:00') :?>
                                    <strong><i class="icon-time"></i> <?php echo JText::_('CAMPAIGN_START_DATE'); ?>:</strong>
                                    <span class="em-camp-start"><?php echo JFactory::getDate(new JDate($result->start_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                    <br>
                                <?php endif; ?>

                                <?php if ($mod_em_campaign_show_camp_end_date && $result->end_date != '0000-00-00 00:00:00') :?>
                                    <strong><i class="icon-time <?php echo ($j<1 && $h<=1)?'red':'';?>"></i> <?php echo JText::_('CAMPAIGN_END_DATE'); ?>:</strong>
                                    <span class="em-camp-end"><?php echo JFactory::getDate(new JDate($result->end_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                    <br>
                                <?php endif; ?>
                                <hr>
	                            <?= JText::_('TIMEZONE').$offset; ?>
                            </div>
                            <div class="below-content">
                            <?php $formUrl = base64_encode('index.php?option=com_fabrik&view=form&formid=102&course='.$result->code.'&cid='.$result->id); ?>

                            <?php if ($result->apply_online == 1) :?>
                                <?php if ($mod_em_campaign_get_link) :?>
                                    <a class="btn btn-primary btn-creux btn-orange" role="button" href='<?php echo !empty($result->link) ? $result->link : "index.php?option=com_emundus&view=programme&id=".$result->id."&Itemid=".$mod_em_campaign_itemid2; ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                                <?php else :?>
                                    <a class="btn btn-primary btn-creux btn-orange" role="button" href='<?php echo "index.php?option=com_emundus&view=programme&id=".$result->id."&Itemid=".$mod_em_campaign_itemid2; ?>' data-toggle="sc-modal"><?php echo JText::_('MORE_INFO'); ?></a>
                                <?php endif; ?>
                                <?php
                                    // The register URL does not work  with SEF, this workaround helps counter this.
                                    if ($sef == 0) {
                                        $register_url = "index.php?option=com_users&view=".$redirect_url."&course=".$result->code."&cid=".$result->id."&Itemid=".$mod_em_campaign_itemid;
                                    } else {
                                        $register_url = $redirect_url."?course=".$result->code."&cid=".$result->id."&Itemid=".$mod_em_campaign_itemid;
                                    }

                                    if(!$user->guest) {
                                        $register_url .= "&redirect=" . $formUrl;
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
                        </div>

                    </div>
                <?php endforeach ;?>
            <?php endif;?>
        </div>
    </div>
</div>

<script>
</script>
