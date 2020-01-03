<?php
/**
 * Fabrik List Template: Default Custom CSS
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

/**
* If you need to make small adjustments or additions to the CSS for a Fabrik
* list template, you can create a custom_css.php file, which will be loaded after
* the main template_css.php for the template.
*
* This file will be invoked as a PHP file, so the list ID
* can be used in order to narrow the scope of any style changes.  You do
* this by prepending #listform_$c to any selectors you use.  This will become
* (say) #listform_12, owhich will be the HTML ID of your list on the page.
*
* See examples below, which you should remove if you copy this file.
*
* Don't edit anything outside of the BEGIN and END comments.
*
* For more on custom CSS, see the Wiki at:
*
* http://www.fabrikar.com/forums/index.php?wiki/form-and-details-templates/#the-custom-css-file
*
* NOTE - for backward compatibility with Fabrik 2.1, and in case you
* just prefer a simpler CSS file, without the added PHP parsing that
* allows you to be be more specific in your selectors, we will also include
* a custom.css we find in the same location as this file.
*
*/

header('Content-type: text/css');
$c = $_REQUEST['c'];
echo <<<EOT
/* BEGIN - Your CSS styling starts here */

/* Add a padding on list program-year-page for example */
.program-year-page #g-main-mainbody, .campaign-page #g-main-mainbody, .users-profile-setup-page #g-main-mainbody, .emails-setup-page #g-main-mainbody,
.emails-declancheur-setup-page #g-main-mainbody, .courriers-setup-page #g-main-mainbody, .documents-type-candidacy-page #g-main-mainbody,
.tags-setup-page #g-main-mainbody, .groups-setup-page #g-main-mainbody, .files-status-page #g-main-mainbody,
.layout-showgrouprights #g-main-mainbody{
    padding: 0 20px;
}
/* Add margin to list footer */
.campaign-page #g-footer, .files-status-page #g-footer, .emails-setup-page #g-footer, .documents-type-candidacy-page #g-footer,
.tags-setup-page #g-footer{
  margin-top:20px!important;
}

.fabrikForm {
	margin-top: 25px !important;
}
/* Tags */
.ui.horizontal.label, .ui.horizontal.labels .label, .em-cell .label-default {
    margin: 4px !important;
    display: inline-flex !important;
}

.label-lightpurple {
    background-color: #DCC6E0 !important;
    text-shadow: none;
}

.label-purple {
    background-color: #947CB0 !important;
    text-shadow: none;
}

.label-darkpurple {
    background-color: #663399 !important;
    text-shadow: none;
}

.label-lightblue {
    background-color: #6bb9F0 !important;
    text-shadow: none;
}

.label-blue {
    background-color: #19B5FE !important;
    text-shadow: none;
}

.label-darkblue {
    background-color: #013243 !important;
    text-shadow: none;
}

.label-lightgreen {
    background-color: #7befb2 !important; 
    text-shadow: none;
}

.label-green {
    background-color: #3FC380 !important;
    text-shadow: none;
}

.label-darkgreen {
    background-color: #1E824C !important;
    text-shadow: none;
}

.label-lightyellow {
    background-color: #FFFD7E !important;
    text-shadow: none;
}

.label-yellow {
    background-color: #FFFD54 !important;
    text-shadow: none;
}

.label-darkyellow {
    background-color: #F7CA18 !important;
    text-shadow: none;
}

.label-lightorange {
    background-color: #FABE58 !important;
    text-shadow: none;
}

.label-orange {
    background-color: #E87E04 !important;
    text-shadow: none;
}

.label-darkorange {
    background-color: #D35400 !important;
    text-shadow: none;
}

.label-lightred {
    background-color: #EC644B !important;
    text-shadow: none;
}

.label-red {
    background-color: #CF000F !important;
    text-shadow: none;
}

.label-darkred {
    background-color: #e5283b !important;
    text-shadow: none;
}

.label-lightpink {
    background-color: #e08283;
    text-shadow: none;
}

.label-pink {
    background-color: #d2527f;
    text-shadow: none;
}

.label-darkpink {
    background-color: #db0a5b;
    text-shadow: none;
}

/*Programms*/
.programmes-page #g-container-main{
    padding: 0 20px;
}

.programmes-page .fabrik_view{
    margin: 0 5px!important;
}

.programmes-page .fabrik__rowlink{
    padding: 3px 5px;
}
.g-back-office-emundus-tableau table{
    background-color:#ddd;
}
.g-back-office-emundus-tableau h6 {
    padding: 10px 0 0 0;   
}
.g-back-office-emundus-tableau #g-main-mainbody .platform-content .container-fluid{
    padding:0;
}
.g-back-office-emundus-tableau .header-c .open > .dropdown-menu {
    right: 0!important;
}
hr{
    color: rgba(181,181,181,1);
    margin:10px!important;
}
.award{
    display: flex;
    flex-direction: column;
    justify-content: center;
	align-items: center;
}
.em-search{
	display: flex;
	flex-direction: row;
	justify-content: flex-end;
	width: 90vw;
}
.em-search-button{
	margin-left: -5px !important;
	height: 34px !important;
	background-color: #e87f2e;
	background: #e87f2e;
	margin-bottom: 0;
	border-top-left-radius: 0;
	border-bottom-left-radius: 0;
	border-top-right-radius: 5px;
	border-bottom-right-radius: 5px;
	border: none;
	color: white;
}
.em-search-button i{
	margin-right: 5px;
}
.em-cardContainer{
    display:flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    width: 90vw;
}
.em-cardContainer-card{
    display: flex;
    flex-direction: row;
    border-radius: 10px;
    border: 1px solid rgba(181,181,181,1);
    box-shadow: 10px 10px 13px -4px rgba(181,181,181,1);
    margin: 10px;
    margin-bottom:100px;
    overflow:hidden;
    position: initial;
    z-index: 1;
}
.em-bulle{
position:absolute;
z-index:-1;
}
.left{
left:-42px;
}
.right{
right:-42px;
}
.em-bulle img{
width:400px;
}
.em-cardContainer-card .em-cardContainer-card-image{
    height: 300px;
}

.em-cardContainer-card .em-cardContainer-card-image img{
    height: 100%;
    width: 100%;
    object-fit: cover;
    object-position: center;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}
.em-cardContainer-card-content{
    margin: 10px;
    height: 100%;
    width:80%;
    display: flex;
    flex-direction: column;
}
.em-cardContainer-card.rouge{
background:#e5e5e5;
flex-direction:row-reverse;
}
.em-cardContainer-card-content h1{
	font-size: 1.5rem;
	margin-bottom: 40px;
}
.em-cardContainer-card-content-txt{
    height:155px;
    overflow:hidden;
 }

/* Voir plus */
.wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
}
.em-cardContainer-card-content-btn {
    cursor: pointer;
    display: flex;
    flex-direction: row;
    justify-content: space-around;
    align-items: center;
    align-self: flex-end;
}
.em-cardContainer-card-content .btn-txt{
    margin-left: 15px;
}
.em-cardContainer-card-content-btn:hover .btn-icons {
    width: 220px;
}
.em-cardContainer-card-content-btn:hover .btn-txt {
    color: white;
    position: absolute;
}
.em-cardContainer-card-content-btn:hover #angle {
    opacity: 0;
    left: 120%;
}
.em-cardContainer-card-content-btn:hover #arrow {
    opacity: 1;
    left: 15%;
}

.btn-icons {
    position: relative;
    width: 50px;
    height: 50px;
    border-radius: 40px;
    color: white;
    background-color: rgb(52, 152, 219);
}
.btn-icons.green{
background:green;
}
.btn-icons.red{
background:red;
}
.btn-icons.yellow{
background:yellow;
}
.btn-icons i {
    font-size: 35px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
.btn-icons #arrow {
    opacity: 0;
    left: -40%;
    transition: all 0.6s ease;
}
.btn-icons #angle {
    transition: all 0.4s ease;
}

.btn-txt {
    color: black;
}


.em-cardContainer-card-vote{
    display:flex;
    flex-direction:column;
    margin: 5px;
}
.em-cardContainer-card-vote-button{
    display: flex;
    flex-direction: row;
    justify-content: space-around;

}
.em-cardContainer-card-vote-button a{
    height: 30px;
    width: 150px;
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    vertical-align: center;
    border: 2px solid #1e3799;
    border-radius: 100px;
    cursor: pointer;
	margin-right: 5px;
	color:#1e3799;
}
.em-cardContainer-card-vote-button a:hover, .em-cardContainer-card-vote-button a.active {
color:#1e3799;
    background-color: rgba(52, 152, 219, 0.2);
}
.em-cardContainer-card-vote-button a i{
    height: 16px;
    width: 16px;
    color: #1e3799;
    padding-left: 10px
}
.em-button-clicked{
	animation: clicked 2s ease;
}

@keyframes clicked{
	0%{
		transform: scale(1);
	}
	30%{
		transform: scale(1.5);
	}
	50%,70%,90%{
		transform: rotate(20deg);
	}
	60%,80%,100%{
		transform: rotate(-20deg);
		transform: scale(1);
	}
	
	
}
@media screen and (min-width:780px){
	.em-cardContainer-card-content h1{
		font-size: 1rem;
	}
	.em-search{
		width: 80vw;
	}
    .em-cardContainer{
        width: 80vw;
    }
    .em-cardContainer-card{
        flex:0 1 41%;
    }

}
@media screen and (min-width:1270px){
	.em-cardContainer-card-content h1{
		font-size: 1.2rem;
	}
	.em-search{
		width: 70vw;
	}
     .em-cardContainer{
        width: 70vw;
    }
    .em-cardContainer-card{
        flex:0 1 31%;
    }

}
@media screen and (min-width:1700px){
	.em-cardContainer-card-content h1{
		font-size: 1.5rem;
	}
}
/* END - Your CSS styling ends here */
EOT;
