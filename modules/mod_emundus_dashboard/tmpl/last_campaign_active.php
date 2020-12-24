<?php
if(isset($campaigns[$cpt_last_campaigns])){
    $campaign = $campaigns[$cpt_last_campaigns];
} else {
    $campaign = $campaigns[0];
}
if(isset($campaigns[$cpt_last_campaigns])) {
    echo "<div class='col-md-5 col-sm-6 col-md-offset-2 tchooz-widget' id='last_campaign_active'>
        <div class='section-sub-menu' style='margin-bottom: 10px'>
        <div class='d-flex'>
            <h1>" . $campaign->label . "</h1>
            <div class='publishedTag'>
            " . JText::_("CAMPAIGN_PUBLISHED") . "
            </div>
        </div>
        <div class='date-menu'>
              " . JText::_("CAMPAIGN_FROM") . " " .  date('d/m/Y', strtotime($campaign->start_date)) . " " . JText::_("CAMPAIGN_TO") . " " . date('d/m/Y', strtotime($campaign->end_date)) . "
        </div>
        <p class='description-block'>" . $campaign->short_description . "</p>
        <div class='stats-block'>
        <div class='nb-dossier'>
            <div>" . $campaign->files . " Dossiers</div>
        </div>
        </div>
      </div>
      </div>";
    $cpt_last_campaigns = $cpt_last_campaigns + 1;
} else {
    echo "<div class='col-md-5 col-sm-6 col-md-offset-2 tchooz-widget' id='last_campaign_active'>
        <div class='section-sub-menu' style='margin-bottom: 10px'>
            <h3>Pas de campagnes actives</h3>
        </div>
        </div>";
}
?>
