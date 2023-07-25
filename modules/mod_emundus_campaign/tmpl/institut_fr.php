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
$config = JFactory::getConfig();
$site_offset = $config->get('offset');

?>

<style>
    .hide {
        display: none !important;
    }
</style>

<div class="navfilter">
    <div class="depositor">
        <select id="depositor_select" onchange="searchCampaign()">
            <option value=""><?php echo JText::_('MOD_EM_CAMPAIGN_SELECT_DEPOSITOR');?></option>
            <option value="2"><?php echo JText::_('MOD_EM_CAMPAIGN_HORS_RESEAUX');?></option>
            <option value="1"><?php echo JText::_('MOD_EM_CAMPAIGN_RESEAUX');?></option>
        </select>
    </div>
    <!--
    <div class = "result-counter">
        <span><?php echo (sizeof($currentCampaign) == 1) ? sizeof($currentCampaign) . " " . JText::_('MOD_EM_CAMPAIGN_CURRENT_CAMPAIGN') : sizeof($currentCampaign) . " " . JText::_('MOD_EM_CAMPAIGN_CURRENT_CAMPAIGNS'); ?></span>
    </div>
    -->
    <div class = "type">
        <select id="program_type" onchange="searchCampaign()">
            <option value=""><?php echo JText::_('MOD_EM_CAMPAIGN_SELECT_PROG_TYPE');?></option>

            <?php
            $programs = array_unique(array_column($programs, 'programmes'));
            foreach($programs as $program => $value) :?>
                <option value="<?=$value;?>"><?= ucfirst(strtolower($value)); ?></option>
            <?php endforeach ;?>
        </select>
    </div>
</div>
<hr>


<div class="tab-content">
    <div id="current" class="tab-pane fade in active">
        <div class="campaigns-list">
            <?php if (empty($currentCampaign)) :?>
                <div class="alert alert-warning"><?php echo JText::_('MOD_EM_CAMPAIGN_NO_RESULT_FOUND') ?></div>
            <?php else :?>
                <?php foreach ($currentCampaign as $result) :?>
                    <?php
                    $resaux = $helper->getReseaux($result->id);

                    // Get number of files compared to limit if limit is enabled
                    if ($result->is_limited == 1) {
                        $db = JFactory::getDbo();
                        $query = $db->getQuery(true);

                        $query->clear()
                            ->select($db->quoteName('limit_status'))
                            ->from($db->quoteName('jos_emundus_setup_campaigns_repeat_limit_status'))
                            ->where($db->quoteName('parent_id') . ' = ' . $db->quote($result->id));
                        $db->setQuery($query);
                        $limit_status = $db->loadColumn();

                        $query->clear()
                            ->select($db->quoteName('limit'))
                            ->from($db->quoteName('jos_emundus_setup_campaigns'))
                            ->where($db->quoteName('id') . ' = ' . $db->quote($result->id));
                        $db->setQuery($query);
                        $file_limit = $db->loadResult();

                        $files_sent = 0;
                        if (!empty($limit_status)) {
                            $query->clear()
                                ->select('COUNT(id)')
                                ->from($db->quoteName('jos_emundus_campaign_candidature'))
                                ->where($db->quoteName('campaign_id') . ' = ' . $db->quote($result->id))
                                ->andWhere($db->quoteName('status') . ' IN (' . implode(',', $limit_status) . ')');
                            $db->setQuery($query);
                            $files_sent = $db->loadResult();
                        }
                    }

                    ?>
                    <div class="campaign-content" data-row="<?php echo $result->prog_type;?>" data-reseaux1="<?php echo $resaux->reseaux_cult; ?>" data-reseaux2="<?php echo $resaux->hors_reseaux; ?>">
                        <div class="left-side campaigntext <?php echo $mod_em_campaign_class; ?>">
                            <h4>
                                <?php echo $result->label; ?>
                            </h4>
                            <div class = "em-disciplines">
                                <?php echo '<div class = "em-discipline label">'.implode('</div><div class = "em-discipline label">', $helper->getCampaignTags($result->id)).'</div>';?>
                            </div>
                            <p>
                                <?php echo $result->short_description;?>
                            </p>
                            <?php
                            if ($result->is_limited == 1) {
                                if ($files_sent == 1) {
                                    $files_sent_tag = 'MOD_EM_CAMPAIGN_CAMPAIGN_SENT_NUMBER_SINGULAR';
                                } else {
                                    $files_sent_tag = 'MOD_EM_CAMPAIGN_CAMPAIGN_SENT_NUMBER_PLURAL';
                                }
                                echo '<p style="display:inline-block;padding:10px;border:1px solid red;border-radius:4px;font-weight:bold;color:red;">' . $files_sent . ' ' . JText::_($files_sent_tag) . ' ' . $file_limit . '</p>';
                            }
                            ?>
                        </div>
                        <div class="right-side campaingapply <?php echo $mod_em_campaign_class; ?>">
                            <div class="campaingapplycontent">
                                <b class="campaingapplycontent-bold"><?php echo JText::_('MOD_EM_CAMPAIGN_PERIOD'); ?></b><br class="campaingapplycontent-breaker"/>

                                <?php if ($mod_em_campaign_show_camp_start_date && $result->start_date != '0000-00-00 00:00:00') :?>
                                    <strong><i class="icon-time"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_START_DATE'); ?>:</strong>
                                    <span class="em-camp-start"><?php echo JFactory::getDate(new JDate($result->start_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                    <br>
                                <?php endif; ?>

                                <?php if ($mod_em_campaign_show_camp_end_date && $result->end_date != '0000-00-00 00:00:00') :?>
                                    <strong><i class="icon-time <?php echo ($j<1 && $h<=1)?'red':'';?>"></i> <?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_END_DATE'); ?></strong>
                                    <span class="em-camp-end"><?php echo JFactory::getDate(new JDate($result->end_date, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                    <br>
                                <?php endif; ?>

                                <?php if ($mod_em_campaign_show_formation_start_date && $result->formation_start !== '0000-00-00 00:00:00') :?>
                                    <strong><?php echo JText::_('MOD_EM_CAMPAIGN_FORMATION_START_DATE'); ?>:</strong>
                                    <span class="em-formation-start"><?php echo JFactory::getDate(new JDate($result->formation_start, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                    <br>
                                <?php endif;?>

                                <?php if ($mod_em_campaign_show_formation_end_date && $result->formation_end !== '0000-00-00 00:00:00') :?>
                                    <strong><?php echo JText::_('MOD_EM_CAMPAIGN_FORMATION_END_DATE'); ?>:</strong>
                                    <span class="em-formation-end"><?php echo JFactory::getDate(new JDate($result->formation_end, $site_offset))->format($mod_em_campaign_date_format); ?></span>
                                    <br/>
                                <?php endif; ?>

                                <hr>
                                <?= JText::_('MOD_EM_CAMPAIGN_TIMEZONE').$offset; ?>
                            </div>
                            <div class="below-content">
                                <?php $formUrl = base64_encode('index.php?option=com_fabrik&view=form&formid=102&course='.$result->code.'&cid='.$result->id); ?>

                                <?php if ($result->apply_online == 1 && $m_campaign->isLimitObtained($result->id) !== true) : ?>
                                    <?php if ($mod_em_campaign_get_link) : ?>
                                        <a class="btn btn-primary btn-creux btn-orange" role="button"
                                           href='<?php echo !empty($result->link) ? $result->link : "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                                           target="_blank" data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
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
                                        $register_url = $redirect_url . "&course=" . $result->code . "&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid . "&redirect=" . $formUrl;
                                    } else {
                                        $register_url = $redirect_url . "?course=" . $result->code . "&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid . "&redirect=" . $formUrl;
                                    }
                                    ?>
                                    <a class="btn btn-primary btn-plein btn-blue" role="button" href='<?php echo $register_url; ?>'
                                       data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_APPLY_NOW'); ?></a>
                                <?php else : ?>
                                    <?php if ($mod_em_campaign_get_link) : ?>
                                        <a class="btn btn-primary btn-plein btn-blue" role="button"
                                           href='<?php echo !empty($result->link) ? $result->link : "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                                           target="_blank" data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
                                    <?php else : ?>
                                        <a class="btn btn-primary btn-plein btn-blue" role="button"
                                           href='<?php echo "index.php?option=com_emundus&view=programme&cid=" . $result->id . "&Itemid=" . $mod_em_campaign_itemid2; ?>'
                                           data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_MORE_INFO'); ?></a>
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

    /*  Program Type Select */
    // document.querySelector('#program_type').addEventListener('change',function(){
    //     var campaigns = document.querySelectorAll(".campaign-content");
    //     campaigns.forEach((camp) => {
    //         if (this.value === '') {
    //             camp.classList.remove("hide");
    //         } else {
    //             if (camp.dataset.row !== this.value) {
    //                 camp.classList.add("hide");
    //             } else {
    //                 camp.classList.remove("hide");
    //             }
    //         }
    //     });
    // });

    function searchCampaign() {
        const deposit = document.querySelector('#depositor_select').value,
            type = document.querySelector('#program_type').value,
            campaigns = document.querySelectorAll(".campaign-content");

        campaigns.forEach((camp) => {
            if (deposit === '') {
                if (camp.dataset.row !== type && type !== '') {
                    camp.classList.add("hide");
                } else {
                    camp.classList.remove("hide");
                }
            } else {
                if((deposit == 1 && camp.dataset.reseaux1 == 1)) {
                    if (camp.dataset.row !== type && type !== '' || camp.dataset.reseaux2 == 1) {
                        camp.classList.add("hide");
                    } else {
                        camp.classList.remove("hide");
                    }

                } else if (deposit == 2 && camp.dataset.reseaux2 == 1) {

                    if (camp.dataset.row !== type && type !== '' || camp.dataset.reseaux1 == 1) {
                        camp.classList.add("hide");
                    } else {
                        camp.classList.remove("hide");
                    }
                } else {
                    camp.classList.add("hide");
                }
            }
        });
    }
</script>
