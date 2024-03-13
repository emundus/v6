<?php
/**
 * Fabrik List Template: Bootstrap
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

header('Content-type: text/css');
$c           = $_REQUEST['c'];
$buttonCount = (int) $_REQUEST['buttoncount'];
$buttonTotal = $buttonCount === 0 ? '100%' : 30 * $buttonCount . "px";
echo "

.fabrikDataContainer {
	clear:both;
	/*
		dont use this as it stops dropdowns from showing correctly
		overflow: auto;*/
}

.fabrikDataContainer .pagination a{
	float: left;
}

ul.fabrikRepeatData {
	list-style: none;
	list-style-position:inside;
	margin: 0;
	padding-left: 0;
}
.fabrikRepeatData > li {
	white-space: nowrap;
	max-width:350px;
	overflow:hidden;
	text-overflow: ellipsis;
}
td.repeat-merge div, td.repeat-reduce div,
td.repeat-merge i, td.repeat-reduce i {
padding: 5px !important;
}

.nav li {
list-style: none;
}

.filtertable_horiz {
	display: inline-block;
	vertical-align: top;
}

.fabrikListFilterCheckbox {
	text-align: left;
}

.fabrikDateListFilterRange {
	text-align: left;
	display: inline-block;
}

.mod_emundus_campaign__list_content {
	display: flex;
    flex-direction: column;
}

.all-actions-container {
	position: relative;
}

.modal-actions {
	position: absolute;
	height: fit-content !important;
	right: -135px;
	top: 75px;
	z-index: 9;
	box-shadow: 0 12px 17px rgba(5, 47, 55, 0.07), 0 5px 22px rgba(5, 47, 55, 0.06), 0 7px 8px rgba(5, 47, 55, 0.1);
    border-radius: calc(var(--em-applicant-br-cards) / 2);
    padding-top: var(--p-8);
    padding-bottom: var(--p-8);
    position: absolute;
    background: var(--neutral-0);
    width: max-content;
}

.catalogue_tag {
	background: var(--neutral-400);
	color: var(--neutral-700);
	border-radius: 25px;
    padding: 4px 12px;
    width: min-content;
	text-align: center;
	font-size: 14px;
	margin: 16px 0;
}

.view-list.catalogue #g-container-main {
     padding-left: 0 !important; 
}

.view-list.catalogue #g-container-main .g-container {
    padding: 0 !important;
	width: 75rem !important;
    margin: auto;
}
    

.catalogue_content_container {
    overflow: auto;
    height: 100vh !important;
    display: flex;
    flex-direction: row !important;
	flex-wrap: wrap;
	align-items: flex-start !important;
    gap: 24px;
}

.catalogue_filters_container  {
  flex: 0 0 20%;
}

#catalogue_container.mod_emundus_campaign__content {
    flex: 0 0 77%;
    width: min-content !important;
    margin-top: 0;
}

#catalogue_container.cards .mod_emundus_campaign__list_items {
    gap: var(--p-24);
    grid-template-columns: repeat(3, minmax(200px, 1fr));
}

#catalogue_container.card .mod_emundus_campaign__list_content {
	padding: 32px 24px;
}

.fabrikFiltersBlock {
    padding: 24px;
    background: #FFFFFF;
    border: 1px solid #EDEDED;
    box-shadow: 0px 1px 1px rgba(5, 47, 55, 0.07), 0px 2px 1px rgba(5, 47, 55, 0.06), 0px 1px 3px rgba(5, 47, 55, 0.1);
    border-radius: 16px;
    height: auto;
}

.catalogue_filters_container .fabrik_filter_submit.button {
	background: var(--em-primary-color);
    border: 1px solid var(--em-primary-color);
    border-radius: var(--em-applicant-br) !important;
    padding: var(--em-spacing-vertical) var(--em-spacing-horizontal) !important;
    color: var(--neutral-0);
    line-height: normal !important;
    letter-spacing: normal;
    font-size: var(--em-applicant-font-size);
}

.view-list .catalogue_filters_container .fabrik_filter_submit.button {
    border-radius: var(--em-applicant-br) !important;
    padding: var(--em-spacing-vertical) var(--em-spacing-horizontal) !important;
    color: var(--neutral-0);
    line-height: normal !important;
    letter-spacing: normal;
    font-size: var(--em-applicant-font-size) !important;
}

.view-list .catalogue_filters_container .fabrik_filter_submit.button:hover, 
.view-list .catalogue_filters_container .fabrik_filter_submit.button:focus, 
.view-list .catalogue_filters_container .fabrik_filter_submit.button:active {
    color: var(--em-primary-color);
    background: var(--neutral-0) !important;
    border: 1px solid var(--em-primary-color);
}

.catalogue_filters_container .filtertable {
    margin-bottom: 0;
}

.catalogue_filters_container .filtertable .em-filter-body [data-filter-row]:last-child {
	margin-bottom: 0;
}

.view-list #catalogue_container.cards .container-actions .btn {
	height: auto;
	margin-top: 16px;
}

#catalogue_container.cards .mod_emundus_campaign__list_content {
    height: 300px;
}

#catalogue_container.cards .fabrik_element p {
	overflow: hidden;
	white-space: nowrap;
	text-overflow: ellipsis;
	width: 235px;
}

#catalogue_container.cards .mod_emundus_campaign__list_content #background-shapes {
    width: 126% !important;
    height: 76% !important;
    transform: scale(1.3) !important;
}

.catalogue.gantry.view-list #g-container-main .g-container .page-header {
    margin-top: 0;
}

#catalogue_container.cards h4 {
    -webkit-line-clamp: 2;
    overflow: hidden;
    -webkit-box-orient: vertical;
    max-height: 48px;
    display: -webkit-box;
    line-height: 140%;
    min-height: 46px;
}

#catalogue_container.cards .em-text-neutral-600 {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.catalogue_filters_container .em-filter-body select {
	color: var(--neutral-700);
}

.catalogue_filters_container .clearFilters {
	color: var(--red-600);
}

.catalogue_filters_container .clearFilters:hover, 
.catalogue_filters_container .clearFilters:focus,
.catalogue_filters_container .clearFilters:active{
	color: var(--red-700);
}

#catalogue_container.tabs .mod_emundus_campaign__list_items_tabs {
	display: flex;
	flex-direction: column;
    padding: 0;
    gap: var(--p-24);
}

#catalogue_container.tabs .mod_emundus_campaign__list_items_tabs .mod_emundus_campaign__list_content {
	display: flex;
	flex-direction: row; 
	height: auto;
	align-items: center;
}

#catalogue_container.tabs  .mod_emundus_campaign__list_items_tabs .mod_emundus_campaign__list_content_container  {
	flex: 0 0 84%;
}

#catalogue_container.tabs  .mod_emundus_campaign__list_items_tabs .mod_emundus_campaign__list_content_container .em-text-neutral-600 {
	display: flex;
	flex-direction: row; 
	justify-content: space-between;
	width: 100%;
}

#catalogue_container.tabs .container-actions a {
	height: auto;
	margin-top: 0;
}

#catalogue_container.tabs .catalogue_tag {
    margin-top: 0;
}

#catalogue_container.tabs .fabrik_element p {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    width: 214px;
}

#catalogue_container.tabs .mod_emundus_campaign__list_content #background-shapes {
    width: 27% !important;
    height: 200% !important;
    transform: scale(1.2) !important;
}

#catalogue_container.tabs .mod_emundus_campaign__list_items {
	display: none;
}

#catalogue_container.cards .mod_emundus_campaign__list_items_tabs {
	display: none;
}

.fabrik-switch-view-icon {
	color: var(--neutral-500);
	border: 1px solid var(--neutral-500);
	padding: 6px;
	border-radius: 4px;
	background-color: var(--neutral-0);
}

.fabrik-switch-view-icon.active {
	color: var(--em-primary-color);
	border: 1px solid var(--em-primary-color);
}

.catalogue_filters_container input:not(.fabrik_filter_submit), 
.catalogue_filters_container select {
	color: var(--neutral-900) !important;
}

#catalogue_container span.add-on {
	background: transparent;
}

#catalogue_container .inputbox  {
	color: var(--neutral-600);
}

#catalogue_container span.add-on small {
	color: var(--neutral-600);
	font-weight: 400;
	font-size: var(--em-applicant-font-size);
	font-family: var(--em-applicant-font);
}

"; ?>
