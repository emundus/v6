<?php
header('Content-type: text/css');
$c = (int) $_REQUEST['c'];
$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : 'form';
$rowid = isset($_REQUEST['rowid']) ? $_REQUEST['rowid'] : '';
$form = $view . '_' . $c;

if ($rowid !== '')
{
	$form .= '_' . $rowid;
}

echo <<<EOT
/* missing from some bootstrap templates (like JoomlArt) */

.row-fluid:before,
.row-fluid:after {
	display: table;
	content: "";
	line-height: 0;
}

.row-fluid:after {
	clear: both;
}

/* Override BS2 form horizontal labels for details view when fabrik form module */
.fabrikDetails.fabrikIsMambot .form-horizontal .control-label {
	width: auto;
	text-align: left;
}

.mod_emundus_campaign__tabs {
    margin-top: var(--m-24);
    border-bottom: solid 1px var(--neutral-300);
    height: 35px;
}

.mod_emundus_campaign__details_content {
    box-shadow: var(--em-box-shadow-x-1) var(--em-box-shadow-y-1) var(--em-box-shadow-blur-1) var(--em-box-shadow-color-1), var(--em-box-shadow-x-2) var(--em-box-shadow-y-2) var(--em-box-shadow-blur-2) var(--em-box-shadow-color-2), var(--em-box-shadow-x-3) var(--em-box-shadow-y-3) var(--em-box-shadow-blur-3) var(--em-box-shadow-color-3);
    border-radius: var(--em-applicant-br-cards) !important;
    padding: var(--p-32);
    background: var(--neutral-0);
    color: var(--em-default-title-color-1);
}

.mod_emundus_campaign__grid {
    display: grid;
    grid-gap: 0;
    grid-template-columns: 64% 30%;
}

.mod_emundus_campaign__tabs a.current-tab {
    border-bottom: solid 1px var(--em-profile-color);
}

.mod_emundus_campaign__tabs a {
    height: 35px;
    cursor: pointer;
}

.mod_emundus_campaign__tabs a.current-tab span {
    color: var(--neutral-900);
    font-weight: 500;
    font-family: var(--em-applicant-font);
}

.tchooz-single-campaign #campaign * {
    line-height: 160%;
    color: var(--neutral-900);
    font-family: var(--em-applicant-font);
}

.mod_emundus_campaign__details_content {
    position: relative;
    overflow: hidden;
}

.em-programme-tag.catalogue_tag  {
    background: var(--neutral-400);
    color: var(--neutral-700);
    border-radius: 25px;
    padding: 4px 12px;
    width: min-content;
    text-align: center;
    font-size: 14px;
    margin: 16px 0;
    font-weight: 400;
}

.mod_emundus_campaign__grid a.btn.btn-default {
    background-color: var(--neutral-0);
    color: var(--em-primary-color);
    border: 1px solid var(--em-primary-color);
    display: flex;
    flex-direction: row;
    align-items: center;
}

a.btn.btn-default .icon-print {
	margin-right: 8px;
}

.mod_emundus_campaign__grid a.btn.btn-default:hover, 
 .mod_emundus_campaign__grid a.btn.btn-default:focus, 
 .mod_emundus_campaign__grid a.btn.btn-default:active{
    background-color: var(--em-primary-color);
    color: var(--neutral-0);
    border: 1px solid var(--em-primary-color);
}

.mod_emundus_campaign__grid .fabrikElementReadOnly {
	margin-top: 0; 
	color: var(--neutral-700);
}


EOT;
