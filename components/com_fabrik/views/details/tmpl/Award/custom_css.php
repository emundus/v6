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
.span12{
width:100%!important;
}
#g-page-surround {
  background: #f5f5f5 !important;
}
.w-inline-block {
    display: flex !important;
}

.em-wrapper-project-row2 .em-colprojet.w-row  .em-col0.w-col.em-enjeuxadroite.w-col-6{
   width: 50% !important;
}

.em-wrapper-project-row2 .em-colprojet.w-row .w-col.w-col-6 {
    width: 50% !important;
 }
 
 .em-containerbottomfooter {
    z-index: -1 !important;
 }
   

.em-center.w-radio  .em-labelyesno.w-form-label {
     margin-top: 8px;
     font-weight: 500;
 }

 .em-textcta2 {
    position: relative;
    z-index: 50;
    padding-top: 3px;
    line-height: 14px;
    padding-left: 19px;
    font-weight: 500;
  }
  
.em-sectionenjeux {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex !important;
    min-height: 50vh;
    padding-top: 100px;
    padding-bottom: 100px;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    background-color: #439064;
    background-image: -webkit-gradient(linear, left top, left bottom, from(rgba(67, 144, 100, 0.45)), to(rgba(67, 144, 100, 0.45))), url('../../../../../../images/custom/5e1c916fe402b63b1b8eaaf5_2vyv_wave_home_key-figures_center--white-near-1.png');
    background-image: linear-gradient(180deg, rgba(67, 144, 100, 0.45), rgba(67, 144, 100, 0.45)), url('../../../../../../images/custom/5e1c916fe402b63b1b8eaaf5_2vyv_wave_home_key-figures_center--white-near-1.png');
    background-position: 0px 0px, 0% 100%;
    background-size: auto, cover;
    background-repeat: repeat, no-repeat;
    background-attachment: scroll, fixed;
  }


  .em-cardContainer-card-vote {
      display: flex;
      justify-content: center;
      
  }
  .em-cardContainer-card-vote p{
   color: #fff;
    font-size: 18px;
}


.g-content {
    margin: 0rem !important;
    padding: 0rem !important;
}

#g-navigation,#g-header {
   
    border-bottom : none !important;
    background-color: #F5F5F5 !important;
 }
 
 .g-container {
     width: 100% !important;
 }
 
 form {
     margin: 0px !important;
 }

 
.w-form-formradioinput--inputType-custom {
    border-top-width: 1px;
    border-bottom-width: 1px;
    border-left-width: 1px;
    border-right-width: 1px;
    border-top-color: #ccc;
    border-bottom-color: #ccc;
    border-left-color: #ccc;
    border-right-color: #ccc;
    border-top-style: solid;
    border-bottom-style: solid;
    border-left-style: solid;
    border-right-style: solid;
    width: 12px;
    height: 12px;
    border-bottom-left-radius: 50%;
    border-bottom-right-radius: 50%;
    border-top-left-radius: 50%;
    border-top-right-radius: 50%;
  }
  
  .w-form-formradioinput--inputType-custom.w--redirected-focus {
    box-shadow: 0px 0px 3px 1px #3898ec;
  }
  
  .w-form-formradioinput--inputType-custom.w--redirected-checked {
    border-top-width: 4px;
    border-bottom-width: 4px;
    border-left-width: 4px;
    border-right-width: 4px;
    border-top-color: #3898ec;
    border-bottom-color: #3898ec;
    border-left-color: #3898ec;
    border-right-color: #3898ec;
  }
  
  a {
    -webkit-transition: color 200ms cubic-bezier(.55, .085, .68, .53);
    transition: color 200ms cubic-bezier(.55, .085, .68, .53);
    color: #482683;
    text-decoration: underline;
  }
  
  a:hover {
    color: #82358b !important;
  }
  
  .em-wrappernavbar {
    position: fixed;
    left: 0%;
    top: 0%;
    right: 0%;
    bottom: auto;
    z-index: 6000;
    margin-right: auto;
    margin-left: auto;
  }
  
  .em-logonavbar {
    width: 18%;
    -o-object-fit: cover;
    object-fit: cover;
  }
  
  .em-itemmenu {
    display: inline-block;
    margin-right: 4px;
    margin-left: 4px;
    padding: 0px 12px;
    -webkit-transition: color 200ms cubic-bezier(.55, .085, .68, .53), background-color 200ms cubic-bezier(.55, .085, .68, .53);
    transition: color 200ms cubic-bezier(.55, .085, .68, .53), background-color 200ms cubic-bezier(.55, .085, .68, .53);
    font-family: Wigrum, sans-serif;
    color: #636363;
    font-size: 16px;
    line-height: 28px;
    font-weight: 400;
    text-decoration: none;
    text-transform: uppercase;
    font-weight: 400;
  }
  
  .em-itemmenu:hover {
    background-color: #482683;
    color: #fff !important;
    line-height: 28px;
  }
  
  .view-details {
    background-color: #f5f5f5;
    font-family: Wigrum, sans-serif;
    color: #333;
  }

  .view-details h1, .view-details h2, .view-details h3, .view-details h4 {
    font-family: Wigrum, sans-serif;
  }
  
  .em-log {
    position: absolute;
    z-index: 1;
    width: 40px;
    height: 40px;
    background-image: url('../../../../../../images/custom/loggin.svg');
    background-position: 50% 50%;
    background-size: contain;
    background-repeat: no-repeat;
  }
  
  .em-wrapperitemmenu {
    position: relative;
    padding-right: 25px;
  }
  
  .em-wrappermenu {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    padding: 2px 45px;
    -webkit-box-pack: justify;
    -webkit-justify-content: space-between;
    -ms-flex-pack: justify;
    justify-content: space-between;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    border-style: solid;
    border-width: 1px;
    border-color: rgba(99, 99, 99, 0.25);
    background-color: #fff;
    box-shadow: 0 11px 50px 0 rgba(99, 99, 99, 0.2);
  }
  
  .em-wrappertextcategory {
    position: absolute;
    z-index: 10;
    width: 100%;
    -webkit-box-flex: 0;
    -webkit-flex: 0 0 auto;
    -ms-flex: 0 0 auto;
    flex: 0 0 auto;
  }
  
  .em-wrappertextcategory2 {
    position: absolute;
    z-index: 10;
    width: 100%;
    -webkit-box-flex: 0;
    -webkit-flex: 0 0 auto;
    -ms-flex: 0 0 auto;
    flex: 0 0 auto;
  }
  
  .em-wrappertextcategory3 {
    position: absolute;
    z-index: 10;
    width: 100%;
    -webkit-box-flex: 0;
    -webkit-flex: 0 0 auto;
    -ms-flex: 0 0 auto;
    flex: 0 0 auto;
  }
  
  .em-wrappertextcategory4 {
    position: absolute;
    z-index: 10;
    width: 100%;
    -webkit-box-flex: 0;
    -webkit-flex: 0 0 auto;
    -ms-flex: 0 0 auto;
    flex: 0 0 auto;
  }
  
  .em-wrappertextcategory5 {
    position: absolute;
    z-index: 10;
    width: 100%;
    -webkit-box-flex: 0;
    -webkit-flex: 0 0 auto;
    -ms-flex: 0 0 auto;
    flex: 0 0 auto;
  }
  
  .em-wrappertextcategory6 {
    position: absolute;
    z-index: 10;
    width: 100%;
    -webkit-box-flex: 0;
    -webkit-flex: 0 0 auto;
    -ms-flex: 0 0 auto;
    flex: 0 0 auto;
  }
  
  .em-arrowcta {
    z-index: 50;
    width: 15px;
    height: 15px;
    margin-right: 5px;
  }
  
  .em-log-hover {
    position: relative;
    width: 40px;
    height: 40px;
    background-image: url('../../../../../../images/custom/5e010047e5da7730b1877de8_2.svg');
    background-position: 50% 50%;
    background-size: contain;
    background-repeat: no-repeat;
    opacity: 0;
  }
  
  .em-textcta {
    position: relative;
    z-index: 50;
    padding-top: 3px;
    line-height: 14px;
    font-weight: 500;
  }
  
  .em-etapecalendrier {
    position: relative;
    z-index: 2;
    width: 80px;
    height: 80px;
    margin-right: auto;
    margin-left: auto;
    border-radius: 50%;
    background-color: #eb5395;
    background-image: url('../../../../../../images/custom/5e04ee200aa529dcfe7a95fc_Projet-solidaire_1.svg');
    background-position: 50% 50%;
    background-size: 50%;
    background-repeat: no-repeat;
  }
  
  .em-etapecalendrier2 {
    position: relative;
    z-index: 5;
    width: 80px;
    height: 80px;
    margin-right: auto;
    margin-left: auto;
    border-radius: 50%;
    background-color: #2da4d0;
    background-image: url('../../../../../../images/custom/5e04ee200aa529dcfe7a95fc_Projet-solidaire_2.svg');
    background-position: 50% 50%;
    background-size: 60%;
    background-repeat: no-repeat;
  }
  
  .em-etapecalendrier3 {
    position: relative;
    z-index: 5;
    width: 80px;
    height: 80px;
    margin-right: auto;
    margin-left: auto;
    border-radius: 50%;
    background-color: #f4c60d;
    background-image: url('../../../../../../images/custom/5e04ee200aa529dcfe7a95fc_Projet-solidaire.svg');
    background-position: 50% 50%;
    background-size: 57%;
    background-repeat: no-repeat;
  }
  
  .em-etapecalendrier4 {
    position: relative;
    z-index: 5;
    width: 80px;
    height: 80px;
    margin-right: auto;
    margin-left: auto;
    border-radius: 50%;
    background-color: #9fc766;
    background-image: url('../../../../../../images/custom/Projet-solidaire.svg');
    background-position: 50% 50%;
    background-size: 59%;
    background-repeat: no-repeat;
  }
  
  .em-etapecalendrier5 {
    position: relative;
    z-index: 5;
    width: 80px;
    height: 80px;
    margin-right: auto;
    margin-left: auto;
    border-radius: 50%;
    background-color: #ee7937;
    background-image: url('../../../../../../images/custom/Vyv-festival.svg');
    background-position: 50% 50%;
    background-size: 58%;
    background-repeat: no-repeat;
  }
  
  .em-barrecalendrier {
    position: absolute;
    top: 50%;
    width: 100%;
    height: 4px;
    background-color: #eb5395;
    -webkit-transform: translate(0px, -50%);
    -ms-transform: translate(0px, -50%);
    transform: translate(0px, -50%);
  }
  
  .em-rondcalendrier {
    position: absolute;
    top: 50%;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background-color: #eb5395;
    -webkit-transform: translate(0px, -50%);
    -ms-transform: translate(0px, -50%);
    transform: translate(0px, -50%);
  }
  
  .em-barrecalendrier2 {
    position: absolute;
    top: 50%;
    width: 100%;
    height: 4px;
    background-color: #f4c60d;
    -webkit-transform: translate(0px, -50%);
    -ms-transform: translate(0px, -50%);
    transform: translate(0px, -50%);
  }
  
  .em-rondcalendrier2 {
    position: absolute;
    top: 50%;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background-color: #f4c60d;
    -webkit-transform: translate(0px, -50%);
    -ms-transform: translate(0px, -50%);
    transform: translate(0px, -50%);
  }
  
  .em-barrecalendrier3 {
    position: absolute;
    top: 50%;
    width: 100%;
    height: 4px;
    background-color: #ee7937;
    -webkit-transform: translate(0px, -50%);
    -ms-transform: translate(0px, -50%);
    transform: translate(0px, -50%);
  }
  
  .em-rondcalendrier3 {
    position: absolute;
    top: 50%;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background-color: #ee7937;
    -webkit-transform: translate(0px, -50%);
    -ms-transform: translate(0px, -50%);
    transform: translate(0px, -50%);
  }
  
  .em-rondcalendrier4 {
    position: absolute;
    left: auto;
    top: 50%;
    right: 0%;
    bottom: 0%;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background-color: #2da4d0;
    -webkit-transform: translate(0px, -50%);
    -ms-transform: translate(0px, -50%);
    transform: translate(0px, -50%);
  }
  
  .em-barrecalendrier4 {
    position: absolute;
    top: 50%;
    width: 100%;
    height: 4px;
    background-color: #2da4d0;
    -webkit-transform: translate(0px, -50%);
    -ms-transform: translate(0px, -50%);
    transform: translate(0px, -50%);
  }
  
  .em-barrecalendrier5 {
    position: absolute;
    top: 50%;
    width: 100%;
    height: 4px;
    background-color: #9fc766;
    -webkit-transform: translate(0px, -50%);
    -ms-transform: translate(0px, -50%);
    transform: translate(0px, -50%);
  }
  
  .em-rondcalendrier5 {
    position: absolute;
    left: auto;
    top: 50%;
    right: 0%;
    bottom: 0%;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background-color: #9fc766;
    -webkit-transform: translate(0px, -50%);
    -ms-transform: translate(0px, -50%);
    transform: translate(0px, -50%);
  }
  
  .em-sectionfooter {
    padding-top: 25px;
    padding-bottom: 25px;
    background-color: #fff !important;
    box-shadow: 0 -1px 6px 1px rgba(99, 99, 99, 0.15);
  }
  
  .em-miniwrappermenu {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
  }
  
  .em-logofooter {
    display: block;
    width: 70%!important;
    margin-right: auto;
    margin-bottom: 20px;
    margin-left: auto;
  }
  
  .em-menufooter {
    width: 150px;
    margin-right: 14px;
    margin-left: 14px;
    padding: 9px 10px;
    background-color: #82358b;
    -webkit-transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), color 200ms cubic-bezier(.55, .085, .68, .53);
    transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), color 200ms cubic-bezier(.55, .085, .68, .53);
    color: #fff;
    font-size: 14px;
    line-height: 25px;
    font-weight: 400;
    text-align: center;
    text-decoration: none;
    text-transform: uppercase;
    font-weight: 500;
  }
  
  .em-menufooter:hover {
    background-color: #482683;
    color: #fff !important;
  }
  
  .em-menufooter.w--current {
    background-color: #82358b;
  }
  
  .em-menufooter.w--current:hover {
    background-color: #482683;
  }
  
  .em-rowfooter {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    margin-top: 15px;
    margin-bottom: 15px;
    -webkit-box-orient: vertical;
    -webkit-box-direction: reverse;
    -webkit-flex-direction: column-reverse;
    -ms-flex-direction: column-reverse;
    flex-direction: column-reverse;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
  }
  
  .em-wrappermenufooter {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    margin-top: 35px;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -ms-flex-pack: center;
    justify-content: center;
  }
  
  .em-button-nav {
    position: relative;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex !important;
    overflow: hidden;
    width: 100px;
    height: 50px;
    -webkit-box-orient: horizontal;
    -webkit-box-direction: reverse;
    -webkit-flex-direction: row-reverse;
    -ms-flex-direction: row-reverse;
    flex-direction: row-reverse;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    border-style: solid;
    border-width: 2px;
    border-color: #82358b;
    background-color: transparent;
    -webkit-transition: background-color 200ms cubic-bezier(.55, .085, .68, .53);
    transition: background-color 200ms cubic-bezier(.55, .085, .68, .53);
    color: #fff;
    font-size: 11px;
    line-height: 16px;
    text-decoration: none;
    text-transform: uppercase;
  }
  
  .em-button-nav:hover {
    background-color: transparent;
    color: #82358b;
  }
  
  .line1 {
    position: absolute;
    left: 50%;
    top: 0px;
    right: 0px;
    width: 100%;
    height: 15px;
    background-color: #fff;
    -webkit-transform: translate(-50%, 0px);
    -ms-transform: translate(-50%, 0px);
    transform: translate(-50%, 0px);
    -webkit-transform-origin: 50% 50%;
    -ms-transform-origin: 50% 50%;
    transform-origin: 50% 50%;
  }
  
  .line1.orange {
    z-index: 1;
    height: 4px;
    background-color: #592b7b;
  }
  
  .line1.orange {
    z-index: 1;
    height: 4px;
    background-color: #592b7b;
  }
  
  .line2 {
    position: absolute;
    left: 50%;
    top: 0px;
    right: 0px;
    width: 100%;
    height: 15px;
    margin-top: 30px;
    background-color: #fff;
    -webkit-transform: translate(-50%, 0px);
    -ms-transform: translate(-50%, 0px);
    transform: translate(-50%, 0px);
    -webkit-transform-origin: 50% 50%;
    -ms-transform-origin: 50% 50%;
    transform-origin: 50% 50%;
  }
  
  .line2.orange {
    height: 4px;
    margin-top: 8px;
    background-color: #c53364;
  }
  
  .line2.orange {
    height: 4px;
    margin-top: 8px;
    background-color: #c53364;
  }
  
  .dropdown-list-2 {
    background-color: transparent;
  }
  
  .dropdown-list-2.w--open {
    top: 55px;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
  }
  
  .navbar {
    position: fixed;
    left: auto;
    top: 0%;
    right: 0%;
    bottom: auto;
    z-index: 9007199254740991;
    display: none;
    height: 74px;
    margin: 25px;
    padding-right: 25px;
    padding-left: 25px;
    -webkit-box-orient: horizontal;
    -webkit-box-direction: normal;
    -webkit-flex-direction: row;
    -ms-flex-direction: row;
    flex-direction: row;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    border-radius: 90px;
    background-color: #fdfdfd;
    box-shadow: 1px 1px 3px 0 hsla(0, 0%, 41.3%, 0.32);
    opacity: 1;
  }
  
  .line3 {
    position: absolute;
    left: 50%;
    top: 0px;
    right: 0px;
    width: 100%;
    height: 15px;
    margin-top: 60px;
    background-color: #fff;
    -webkit-transform: translate(-50%, 0px);
    -ms-transform: translate(-50%, 0px);
    transform: translate(-50%, 0px);
    -webkit-transform-origin: 50% 50%;
    -ms-transform-origin: 50% 50%;
    transform-origin: 50% 50%;
  }
  
  .line3.orange {
    height: 4px;
    margin-top: 16px;
    background-color: #fd8263;
  }
  
  .navbar-content {
    position: relative;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    padding: 27px 0px;
  }
  
  .nav-link {
    display: inline-block;
    margin-right: 15px;
    margin-left: 15px;
    -webkit-transition: color 200ms cubic-bezier(.55, .085, .68, .53);
    transition: color 200ms cubic-bezier(.55, .085, .68, .53);
    color: #1e1e1e;
    font-weight: 400;
    text-decoration: none;
  }
  
  .nav-link:hover {
    color: #c53364;
  }
  
  .nav-link.w--current {
    color: #c53364;
  }
  
  .nav-link.w--current:hover {
    color: #1e1e1e;
  }
  
  .wrapper-menu-item {
    display: block;
    overflow: hidden;
    width: auto;
    height: 150px;
  }
  
  .burger {
    position: relative;
    display: block;
    overflow: hidden;
    width: 26.6px;
    height: 20px;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
    -webkit-box-pack: justify;
    -webkit-justify-content: space-between;
    -ms-flex-pack: justify;
    justify-content: space-between;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
  }
  
  .vyv-logo-top {
    display: none;
  }
  
  .em-divcontainerfooter {
    width: 90%;
    margin-right: auto;
    margin-left: auto;
  }
  
  .em-containcercategoryphase2-2 {
    position: absolute;
    left: 0%;
    top: 0%;
    right: 0%;
    bottom: 0%;
    z-index: 15;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    padding: 30px;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    background-color: #482683;
    background-image: -webkit-gradient(linear, left top, left bottom, from(rgba(72, 38, 131, 0.8)), to(rgba(72, 38, 131, 0.8))), url('../../../../../../images/custom/Gradiant.png');
    background-image: linear-gradient(180deg, rgba(72, 38, 131, 0.8), rgba(72, 38, 131, 0.8)), url('../../../../../../images/custom/Gradiant.png');
    background-position: 0px 0px, 0px 0px;
    background-size: auto, cover;
    -webkit-transform: translate(0px, 100%);
    -ms-transform: translate(0px, 100%);
    transform: translate(0px, 100%);
    color: #fff;
    text-align: center;
  }
  
  .em-overlay {
    position: absolute;
    width: 100%;
    height: 100%;
    background-color: #82358b;
  }
  
  .em-containerarrow {
    position: relative;
    z-index: 50;
    width: 15px;
    height: 15px;
    margin-right: 14px;
    margin-left: 10px;
  }
  
  .em-arrowcta-purple {
    position: absolute;
    left: 0%;
    top: 0%;
    right: 0%;
    bottom: 0%;
    z-index: 50;
    width: 15px;
    height: 15px;
  }
  
  .em-arrowcta-white {
    position: absolute;
    left: 0%;
    top: 0%;
    right: 0%;
    bottom: 0%;
    z-index: 50;
    width: 15px;
    height: 15px;
  }
  
  .em-containerarrow2 {
    position: relative;
    z-index: 50;
    width: 15px;
    height: 15px;
    margin-right: 0px;
    margin-left: 10px;
  }
  
  .em-mentionslegales {
    margin-top: 43px;
    color: #636363;
    text-decoration: underline;
    font-weight: 400;
  }
  
  .em--utility-page-wrap {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    width: 100vw;
    height: 100vh;
    max-height: 100%;
    max-width: 100%;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    background-image: url('../../../../../../images/custom/5e04d6a54c88a6740a582b50_blue-shape.png');
    background-position: 100% 100%;
    background-size: 40%;
    background-repeat: no-repeat;
    color: #482683;
  }
  
  .em-button-404 {
    position: relative;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    overflow: hidden;
    width: 310px;
    height: 55px;
    margin-top: 10px;
    -webkit-box-orient: horizontal;
    -webkit-box-direction: reverse;
    -webkit-flex-direction: row-reverse;
    -ms-flex-direction: row-reverse;
    flex-direction: row-reverse;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    border-style: solid;
    border-width: 2px;
    border-color: #82358b;
    background-color: transparent;
    -webkit-transition: background-color 200ms cubic-bezier(.55, .085, .68, .53);
    transition: background-color 200ms cubic-bezier(.55, .085, .68, .53);
    color: #fff;
    font-size: 13px;
    line-height: 16px;
    text-decoration: none;
    text-transform: uppercase;
  }
  
  .em-button-404:hover {
    background-color: transparent;
    color: #82358b;
  }
  
  .em-utility-page-content {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    width: 280px;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
    text-align: center;
  }
  
  .em-logotop {
    position: absolute;
    left: 0%;
    top: 0%;
    right: 0%;
    bottom: auto;
    width: 150px;
    margin-top: 60px;
    margin-right: auto;
    margin-left: auto;
  }
  
  .em-img404 {
    width: 30%;
    margin-right: auto;
    margin-left: auto;
  }
  
  .em-arrowcta-white2 {
    position: absolute;
    left: 0%;
    top: 0%;
    right: 0%;
    bottom: 0%;
    z-index: 50;
    width: 15px;
    height: 15px;
  }
  
  .em-arrowcta-purple2 {
    position: absolute;
    left: 0%;
    top: 0%;
    right: 0%;
    bottom: 0%;
    z-index: 50;
    width: 15px;
    height: 15px;
  }
  
  .em-protections-des-donnees {
    margin-top: 10px;
    color: #636363;
    text-decoration: underline;
    font-weight: 400;
  }
  
  .em-imageproject {
    position: absolute;
    z-index: 5;
    width: 100%;
    height: 100%;
    object-fit: contain;
    background: #F5F5F5;
  }
  
  .em-projectcategory {
    margin-top: 6px;
    margin-bottom: 20px;
    color: #82358b;
    font-size: 21px;
    line-height: 26px;
    font-weight: 500;
  }
  
  .em-paragrapheprojet {
    color: #636363;
    font-size: 18px;
    line-height: 20px;
    font-weight: 400;
    margin-bottom:10px;
    margin-top:0px !important;
  }
  
  .em-colprojecttitle {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-pack: end;
    -webkit-justify-content: flex-end;
    -ms-flex-pack: end;
    justify-content: flex-end;
  }
  
  .em-titleprojet {
    margin-top: 0px;
    margin-bottom: 0px;
    color: #482683;
    font-size: 36px;
    line-height: 36px;
    font-weight: 800;
    text-transform: lowercase;
  }


  .em-titleprojet::first-letter {

    text-transform: capitalize;
  }
  
  .em-flexproject {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
  }
  
  .em-sectionprojet {
    position: relative;
    padding-top: 80px;
    padding-bottom: 40px;
  }
  
  .em-backgroudhero {
    position: relative;
    left: 0%;
    top: 0%;
    right: 0%;
    bottom: auto;
    height: 50vh;
  }
  
  .em-wrapperprojecttext {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    width: 80%;
    padding-right: 60px;
    padding-left: 60px;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
    -webkit-box-align: end;
    -webkit-align-items: flex-end;
    -ms-flex-align: end;
    align-items: flex-end;
    text-align: right;
  }
  
  .em-containerimageproject {
    position: relative;
    z-index: 1;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    width: 100%;
    height: 100%;
    float: right;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    background-color: transparent;
    background-image: url('../../../../../../images/custom/5e0f71de6a42a4ca854ae362_Groupe-VYV_RVB.svg');
    background-position: 50% 50%;
    background-repeat: no-repeat;
    box-shadow: none;
  }
  
  .em-sectionenjeux {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    min-height: 50vh;
    padding-top: 100px;
    padding-bottom: 100px;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    background-color: #439064;
    background-image: -webkit-gradient(linear, left top, left bottom, from(rgba(67, 144, 100, 0.45)), to(rgba(67, 144, 100, 0.45))), url('../../../../../../images/custom/5e1c916fe402b63b1b8eaaf5_2vyv_wave_home_key-figures_center--white-near-1.png');
    background-image: linear-gradient(180deg, rgba(67, 144, 100, 0.45), rgba(67, 144, 100, 0.45)), url('../../../../../../images/custom/5e1c916fe402b63b1b8eaaf5_2vyv_wave_home_key-figures_center--white-near-1.png');
    background-position: 0px 0px, 0% 100%;
    background-size: auto, cover;
    background-repeat: repeat, no-repeat;
    background-attachment: scroll, fixed;
  }
  
  .em-wrapper-project-row2 {
    position: relative;
    overflow: hidden;
    width: 70%;
    margin-right: auto;
    margin-left: auto;
  }
  
  .em-wrapperwhitetext {
    padding-left: 60px;
  }
  
  .em-titlewhitecolor {
    margin-bottom: 25px;
    color: #fff;
    font-size: 36px;
    line-height: 36px;
    font-weight: 800;
  }
  
  .em-paragraphewhitecolor {
    color: #fff;
    font-size: 18px;
    line-height: 24px;
    font-weight: 400;
  }
  
  .em-overlay2 {
    position: absolute;
    width: 100%;
    height: 100%;
    background-color: #3b173f;
  }
  
  .em-soutenir-le-projet {
    width: 60%;
    margin-right: auto;
    margin-left: auto;
    color: #fff;
    font-size: 18px;
    line-height: 24px;
    text-align: center;
    font-weight: 500;
  }
  
  .em-h2vote {
    color: #fff;
    font-size: 36px;
    text-align: center;
    font-weight: 800;
  }
  
  .em-containercheckbox {
    position: absolute;
    left: 0%;
    top: 0%;
    right: 0%;
    bottom: auto;
    -webkit-transform: translate(0%, 150%);
    -ms-transform: translate(0%, 150%);
    transform: translate(0%, 150%);
  }
  
  .em-vote {
    padding-top: 110px;
    padding-bottom: 110px;
    background-color: #82358b;
  }
  
  .em-button-vote {
    position: relative;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    overflow: hidden;
    width: 210px;
    height: 55px;
    margin-top: 25px;
    margin-right: auto;
    margin-left: auto;
    -webkit-box-orient: horizontal;
    -webkit-box-direction: reverse;
    -webkit-flex-direction: row-reverse;
    -ms-flex-direction: row-reverse;
    flex-direction: row-reverse;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    border: 2px solid #331336;
    background-color: #331336;
    -webkit-transition: background-color 200ms cubic-bezier(.55, .085, .68, .53);
    transition: background-color 200ms cubic-bezier(.55, .085, .68, .53);
    color: #fff;
    font-size: 13px;
    line-height: 16px;
    text-decoration: none;
    text-transform: uppercase;
  }
  
  .em-button-vote:hover {
    border-color: #fff;
    background-color: transparent;
    color: #fff !important;
  }
  
  .em-question {
    width: 60%;
    margin-top: 15px;
    margin-right: auto;
    margin-left: auto;
    color: #fff;
    font-size: 18px;
    line-height: 24px;
    text-align: center;
    font-weight: 500;
  }
  
  .em-button-finalvote {
    position: relative;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    overflow: hidden;
    width: 170px;
    height: 55px;
    margin-top: 0px;
    margin-right: auto;
    margin-left: auto;
    -webkit-box-orient: horizontal;
    -webkit-box-direction: reverse;
    -webkit-flex-direction: row-reverse;
    -ms-flex-direction: row-reverse;
    flex-direction: row-reverse;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    border: 2px solid #331336;
    background-color: #331336;
    -webkit-transition: background-color 200ms cubic-bezier(.55, .085, .68, .53);
    transition: background-color 200ms cubic-bezier(.55, .085, .68, .53);
    color: #fff;
    font-size: 13px;
    line-height: 16px;
    text-decoration: none;
    text-transform: uppercase;
  }
  
  .em-button-finalvote:hover {
    border-color: #fff;
    background-color: transparent;
    color: #fff !important;
  }
  
  .em-center {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex !important;
    margin-right: 10px;
    margin-left: 10px;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
  }
  
  .em-formyesno {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    margin-top: 15px;
    margin-bottom: 20px;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -ms-flex-pack: center;
    justify-content: center;
  }
  
  .em-labelyesno {
    margin-bottom: 0px;
    margin-left: 8px;
    color: #fff;
    font-size: 16px;
    line-height: 16px;
  }
  
  .em-buttonradioyesno {
    width: 30px;
    height: 30px;
    margin-top: 0px;
    border: 1px solid #b6b6b6;
    background-color: #fff;
  }
  
  .em-buttonradioyesno.w--redirected-checked {
    border-width: 1px;
    border-color: #482683;
    background-color: #3b173f;
    background-image: url('../../../../../../images/custom/Retenu.svg');
    background-position: 50% 50%;
    background-size: 50%;
    background-repeat: no-repeat;
  }
  
  .em-containervotetext {
    position: relative;
  }
  
  html.w-mod-js *[data-ix="homepurple"] {
    opacity: 0;
    -webkit-transform: translate(5%, 0px);
    -ms-transform: translate(5%, 0px);
    transform: translate(5%, 0px);
  }
  
  html.w-mod-js *[data-ix="homeorange"] {
    opacity: 0;
    -webkit-transform: translate(-5%, 0px);
    -ms-transform: translate(-5%, 0px);
    transform: translate(-5%, 0px);
  }
  
  html.w-mod-js *[data-ix="ia-low-opa"] {
    opacity: 0;
  }
  
  html.w-mod-js *[data-ix="ia-low-opa-transform-down"] {
    opacity: 0;
    -webkit-transform: translate(0px, 6px);
    -ms-transform: translate(0px, 6px);
    transform: translate(0px, 6px);
  }
  
  html.w-mod-js *[data-ix="ia-rond-calendrier"] {
    opacity: 0;
    -webkit-transform: translate(0px, -50%) scale(0.01, 0.01);
    -ms-transform: translate(0px, -50%) scale(0.01, 0.01);
    transform: translate(0px, -50%) scale(0.01, 0.01);
  }
  
  html.w-mod-js *[data-ix="ia-barre"] {
    -webkit-transform: translate(100%, 0px);
    -ms-transform: translate(100%, 0px);
    transform: translate(100%, 0px);
  }
  
  html.w-mod-js *[data-ix="ia-barre-2"] {
    -webkit-transform: translate(-100%, 0px);
    -ms-transform: translate(-100%, 0px);
    transform: translate(-100%, 0px);
  }
  
  html.w-mod-js *[data-ix="formealendrier"] {
    opacity: 0;
  }
  
  html.w-mod-js *[data-ix="text-appear"] {
    opacity: 0;
    -webkit-transform: translate(0px, 6px);
    -ms-transform: translate(0px, 6px);
    transform: translate(0px, 6px);
  }
  
  html.w-mod-js *[data-ix="menu-item-wrapper"] {
    width: 0px;
  }
  
  html.w-mod-js *[data-ix="ia-navlink"] {
    opacity: 0;
  }
  
  html.w-mod-js *[data-ix="ia-submenu"] {
    opacity: 0;
    -webkit-transform: translate(0px, -4px);
    -ms-transform: translate(0px, -4px);
    transform: translate(0px, -4px);
  }
  
  html.w-mod-js *[data-ix="center"] {
    -webkit-transform: translate(-50%, 0px);
    -ms-transform: translate(-50%, 0px);
    transform: translate(-50%, 0px);
  }
  
  @media screen and (max-width: 991px) {
    .em-logonavbar.w--current {
      width: 15%;
      height: 60px;
      margin-top: 7px;
      margin-bottom: 7px;
    }
    .em-itemmenu {
      margin-right: 5px;
      margin-left: 5px;
      padding-right: 10px;
      padding-left: 10px;
      font-size: 13px;
      line-height: 13px;
    }
    .em-wrapperitemmenu {
      padding-right: 0px;
    }
    .em-wrappermenu {
      padding-right: 30px;
      padding-left: 30px;
    }
    .em-arrowcta {
      width: 13px;
      height: 13px;
    }
    .em-etapecalendrier {
      width: 40px;
      height: 40px;
    }
    .em-etapecalendrier2 {
      width: 40px;
      height: 40px;
    }
    .em-etapecalendrier3 {
      width: 40px;
      height: 40px;
    }
    .em-etapecalendrier4 {
      width: 40px;
      height: 40px;
    }
    .em-sectionfooter {
      padding-right: 40px;
      padding-left: 40px;
    }
    .em-logofooter {
      margin-bottom: 5px;
    }
    .em-menufooter {
      margin-right: 10px;
      margin-left: 10px;
      font-size: 14px;
      line-height: 24px;
    }
    .em-rowfooter {
      display: -webkit-box;
      display: -webkit-flex;
      display: -ms-flexbox;
      display: flex;
    }
    .em-wrappermenufooter {
      padding-left: 0px;
    }
    .em-button-nav {
      width: 90px;
      font-size: 10px;
    }
    .em-containerarrow {
      margin-right: 10px;
      margin-left: 10px;
    }
    .em-arrowcta-purple {
      width: 13px;
      height: 13px;
    }
    .em-arrowcta-white {
      width: 13px;
      height: 13px;
    }
    .em-containerarrow2 {
      margin-right: 0px;
      margin-left: 10px;
    }
    .em-button-404 {
      width: 100%;
      font-size: 11px;
    }
    .em-arrowcta-white2 {
      width: 13px;
      height: 13px;
    }
    .em-arrowcta-purple2 {
      width: 13px;
      height: 13px;
    }

    .nav-link {
        margin-right: 4px;
        margin-left: 4px;
        font-size: 13px;
        line-height: 13px;
      }

    .em-paragrapheprojet {
      width: 100%;
      font-size: 15px;
      line-height: 20px;
    }
    .em-titleprojet {
      width: 100%;
      font-size: 32px;
      line-height: 31px;
    }
    .em-backgroudhero {
      height: auto;
      min-height: 40vh;
    }
    .em-wrapperprojecttext {
      padding-right: 10px;
      padding-left: 10px;
    }
    .em-containerimageproject {
      width: 100%;
      height: 290px;
      padding-top: 0px;
      padding-bottom: 0px;
    }
    .em-sectionenjeux {
      min-height: auto;
      padding-top: 50px;
      padding-bottom: 50px;
    }
    .em-wrapper-project-row2 {
      width: 80%;
    }
    .em-titlewhitecolor {
      width: 100%;
      font-size: 32px;
      line-height: 31px;
      font-weight: 800;
    }
    .em-paragraphewhitecolor {
      width: 100%;
      font-size: 15px;
      line-height: 20px;
      font-weight: 400;
    }
    .em-soutenir-le-projet {
      width: 100%;
      font-size: 15px;
      line-height: 20px;
      font-weight: 500;
    }
    .em-vote {
      padding-top: 35px;
      padding-bottom: 50px;
    }
    .em-button-vote {
      width: 210px;
      margin-top: 5px;
      font-size: 11px;
    }
    .em-colprojet {
      display: -webkit-box;
      display: -webkit-flex;
      display: -ms-flexbox;
      display: flex;
      -webkit-box-orient: vertical;
      -webkit-box-direction: normal;
      -webkit-flex-direction: column;
      -ms-flex-direction: column;
      flex-direction: column;
    }
    .em-col0 {
      height: 0px;
    }
    .em-columnrightproject {
      padding-right: 0px;
    }
    .em-question {
      width: 100%;
      font-size: 15px;
      line-height: 20px;
      font-weight: 500;
    }
    .em-button-finalvote {
      width: 210px;
      margin-top: 5px;
      font-size: 11px;
    }
  }

  @media (max-width: 1239px) {
    #g-container-main {
         padding: 0px !important;
    }
}
  
  @media screen and (max-width: 767px) {
    .em-wrappermenu {
      display: none;
    }

    .em-mentionslegales {
      margin-top: 26px;
      color: #636363;
      text-decoration: underline;
      font-weight: 400;
    }

    .em-wrapper-project-row2 .em-colprojet.w-row .w-col.w-col-6 {
      width: 100% !important; 
     } 

    .em-etapecalendrier {
      width: 120px;
      height: 120px;
    }
    .em-etapecalendrier2 {
      width: 120px;
      height: 120px;
    }
    .em-etapecalendrier3 {
      width: 120px;
      height: 120px;
    }
    .em-etapecalendrier4 {
      width: 120px;
      height: 120px;
    }
    .em-containertextcal1 {
      -webkit-box-flex: 1;
      -webkit-flex: 1;
      -ms-flex: 1;
      flex: 1;
    }
    .em-containertextcal2 {
      -webkit-align-self: stretch;
      -ms-flex-item-align: stretch;
      -ms-grid-row-align: stretch;
      align-self: stretch;
      -webkit-box-flex: 1;
      -webkit-flex: 1;
      -ms-flex: 1;
      flex: 1;
    }
    .em-containertextcal3 {
      -webkit-box-flex: 1;
      -webkit-flex: 1;
      -ms-flex: 1;
      flex: 1;
    }
    .em-containertextcal4 {
      -webkit-box-flex: 1;
      -webkit-flex: 1;
      -ms-flex: 1;
      flex: 1;
    }
    .em-sectionfooter {
      padding-top: 25px;
      padding-bottom: 25px;
    }
    .em-logofooter {
      display: block;
      width: 100% !important;
      height: auto;
      margin-top: 15px;
      margin-right: auto;
      margin-left: auto;
    }
    .em-menufooter {
      width: 110px;
      margin-right: 10px !important;
      margin-left: 10px !important;
      font-size: 12px !important;
    }
    .em-rowfooter {
      -webkit-box-orient: vertical;
      -webkit-box-direction: reverse;
      -webkit-flex-direction: column-reverse;
      -ms-flex-direction: column-reverse;
      flex-direction: column-reverse !important;
    }
    .em-wrappermenufooter {
      display: -webkit-box;
      display: -webkit-flex;
      display: -ms-flexbox;
      display: flex;
      padding-left: 0px;
      -webkit-box-pack: center;
      -webkit-justify-content: center;
      -ms-flex-pack: center;
      justify-content: center;
    }
    .line1.orange {
      background-color: #482683;
    }
    .line1.orange {
      background-color: #482683;
    }
    .line2.orange {
      background-color: #ee7937;
    }
    .line2.orange {
      background-color: #ee7937;
    }
    .navbar {
      display: block;
      margin-right: 5%;
    }
    .line3.orange {
      background-color: #ffd428;
    }
    .nav-link {
      margin-right: 8px;
      margin-left: 8px;
      color: #636363;
    }
    .nav-link:hover {
      color: #ee7937;
    }
    .nav-link.margin {
      margin-right: 25px;
    }
    .wrapper-menu-item {
      padding-right: 0px;
    }
    .vyv-logo-top {
      position: absolute;
      left: 0%;
      top: 0%;
      right: auto;
      bottom: auto;
      display: block;
      width: 120px;
      margin-top: 15px;
      margin-left: 5%;
    }
    .em-button-404 {
      height: 42px;
      margin-top: 14px;
    }
    .em-paragrapheprojet {
      width: 100%;
      font-size: 15px;
      line-height: 20px;
    }
    .em-titleprojet {
      width: 100%;
      margin-top: 35px;
      font-size: 31px;
      line-height: 29px;
    }
    .em-flexproject {
      -webkit-box-orient: vertical;
      -webkit-box-direction: reverse;
      -webkit-flex-direction: column-reverse;
      -ms-flex-direction: column-reverse;
      flex-direction: column-reverse;
    }
    .em-sectionprojet {
      padding-top: 0px;
      padding-bottom: 40px;
    }
    .em-backgroudhero {
      height: 200px;
      min-height: auto;
    }
    .em-wrapperprojecttext {
      width: 85%;
      margin-right: auto;
      margin-left: auto;
      padding-top: 10px;
      padding-right: 0px;
      padding-left: 0px;
    }
    .em-containerimageproject {
      height: 100%;
      padding-right: 0px;
      padding-left: 0px;
    }
    .em-sectionenjeux {
      padding-top: 20px;
      padding-bottom: 40px;
    }
    .em-wrapper-project-row2 {
      width: 85%;
    }
    .em-wrapperwhitetext {
      padding-left: 0px;
    }
    .em-titlewhitecolor {
      width: 90%;
      margin-top: 35px;
      font-size: 31px;
      line-height: 29px;
      font-weight: 800;
    }
    .em-paragraphewhitecolor {
      width: 100%;
    }
    .em-soutenir-le-projet {
      width: 82%;
    }
    .em-h2vote {
      font-size: 31px;
      line-height: 29px;
    }
    .em-button-vote {
      height: 55px;
    }
    .em-columnrightproject {
      padding-right: 0px;
      padding-left: 0px;
    }
    .em-question {
      width: 82%;
    }
    .em-button-finalvote {
      height: 55px;
    }
  }
  
  @media screen and (max-width: 479px) {
    .em-etapecalendrier {
      width: 60px;
      height: 60px;
      margin-right: 0px;
    }
    .em-etapecalendrier2 {
      width: 60px;
      height: 60px;
      margin-left: 0px;
    }
    .em-etapecalendrier3 {
      width: 60px;
      height: 60px;
      margin-right: 0px;
    }
    .em-etapecalendrier4 {
      width: 60px;
      height: 60px;
      margin-right: 0px;
      margin-left: 0px;
    }
    .em-sectionfooter {
      padding: 30px 15px;
    }
    .em-logofooter {
      width: 100% !important;
      margin-top: 20px !important;
      margin-bottom: 0px;
    }
    .em-menufooter {
      width: 100%;
      margin-right: 0px;
      margin-bottom: 10px;
      margin-left: 0px;
    }
    .em-wrappermenufooter {
      padding-left: 0px;
      -webkit-box-orient: vertical;
      -webkit-box-direction: normal;
      -webkit-flex-direction: column;
      -ms-flex-direction: column;
      flex-direction: column;
      -webkit-box-align: center;
      -webkit-align-items: center;
      -ms-flex-align: center;
      align-items: center;
    }
    .navbar {
      margin-top: 15px;
      margin-right: 15px;
      margin-left: 5px;
      padding-right: 25px;
      padding-left: 25px;
    }
    .nav-link {
      margin-right: 4px;
      margin-left: 4px;
      font-size: 9px;
      line-height: 9px;
    }

    .nav-link.margin {
      margin-right: 15px;
    }
    .wrapper-menu-item {
      -webkit-box-orient: horizontal;
      -webkit-box-direction: normal;
      -webkit-flex-direction: row;
      -ms-flex-direction: row;
      flex-direction: row;
      -webkit-flex-wrap: wrap;
      -ms-flex-wrap: wrap;
      flex-wrap: wrap;
    }
    .em-divcontainerfooter {
      width: 100%;
    }
    .div-block-2 {
      -webkit-align-self: stretch;
      -ms-flex-item-align: stretch;
      -ms-grid-row-align: stretch;
      align-self: stretch;
    }
    .em-button-404 {
      height: 40px;
    }
    .em-titleprojet {
      font-size: 30px;
      line-height: 30px;
    }
    .em-wrapperprojecttext {
      padding-right: 0px;
    }
    .em-containerimageproject {
      width: 100%;
      height: 180px;
      margin-right: auto;
      margin-left: auto;
      padding: 0px;
    }
    .em-wrapper-project-row2 {
      margin-bottom: 0px;
    }
    .em-titlewhitecolor {
      font-size: 30px;
      line-height: 30px;
    }
    .em-paragraphewhitecolor {
      width: 100%;
    }
    .em-soutenir-le-projet {
      width: 100%;
    }
    .em-button-vote {
      width: 90%;
      height: 55px;
    }
    .em-question {
      width: 100%;
    }

    .em-itemmenu {
        margin-right: 4px;
        margin-left: 4px;
        font-size:9px;
        line-height: 9px;
      }


    .em-button-finalvote {
      width: 90%;
      height: 55px;
    }
  }
  
  @font-face {
    font-family: 'Wigrum';
    src: url('../../../../../../templates/emundus_vanilla/fonts/Wigrum-Bold.otf') format('opentype');
    font-weight: 700;
    font-style: normal;
  }
  @font-face {
    font-family: 'Wigrum';
    src: url('../../../../../../templates/emundus_vanilla/fonts/Wigrum-Light.otf') format('opentype');
    font-weight: 300;
    font-style: normal;
  }
  @font-face {
    font-family: 'Wigrum';
    src: url('../../../../../../templates/emundus_vanilla/fonts/Wigrum-Medium.otf') format('opentype');
    font-weight: 500;
    font-style: normal;
  }
  @font-face {
    font-family: 'Wigrum';
    src: url('../../../../../../templates/emundus_vanilla/fonts/Wigrum-MediumItalic.otf') format('opentype');
    font-weight: 500;
    font-style: italic;
  }
  @font-face {
    font-family: 'Wigrum';
    src: url('../../../../../../templates/emundus_vanilla/fonts/Wigrum-Regular.otf') format('opentype');
    font-weight: 400;
    font-style: normal;
  }
  @font-face {
    font-family: 'Wigrum';
    src: url('../../../../../../templates/emundus_vanilla/fonts/Wigrum-BlackItalic.otf') format('opentype');
    font-weight: 900;
    font-style: italic;
  }
  @font-face {
    font-family: 'Wigrum';
    src: url('../../../../../../templates/emundus_vanilla/fonts/Wigrum-BoldItalic.otf') format('opentype');
    font-weight: 700;
    font-style: italic;
  }
  @font-face {
    font-family: 'Wigrum';
    src: url('../../../../../../templates/emundus_vanilla/fonts/Wigrum-Italic.otf') format('opentype');
    font-weight: 400;
    font-style: italic;
  }
  @font-face {
    font-family: 'Wigrum';
    src: url('../../../../../../templates/emundus_vanilla/fonts/Wigrum-LightItalic.otf') format('opentype');
    font-weight: 300;
    font-style: italic;
  }
  @font-face {
    font-family: 'Wigrum';
    src: url('../../../../../../templates/emundus_vanilla/fonts/Wigrum-Black.otf') format('opentype');
    font-weight: 900;
    font-style: normal;
  }
  @font-face {
    font-family: 'Ariatextg1';
    src: url('../../../../../../templates/emundus_vanilla/fonts/AriaTextG1-Bold.otf') format('opentype');
    font-weight: 700;
    font-style: normal;
  }
  @font-face {
    font-family: 'Ariatextg1';
    src: url('../../../../../../templates/emundus_vanilla/fonts/AriaTextG1.otf') format('opentype');
    font-weight: 400;
    font-style: normal;
  }
  @font-face {
    font-family: 'Ariatextg1';
    src: url('../../../../../../templates/emundus_vanilla/fonts/AriaTextG1-BoldItalic.otf') format('opentype');
    font-weight: 700;
    font-style: italic;
  }
  @font-face {
    font-family: 'Ariatextg1';
    src: url('../../../../../../templates/emundus_vanilla/fonts//AriaTextG1-SemiBold.otf') format('opentype');
    font-weight: 600;
    font-style: normal;
  }
  @font-face {
    font-family: 'Ariatextg1';
    src: url('../../../../../../templates/emundus_vanilla/fonts/AriaTextG1-SemiBoldItalic.otf') format('opentype');
    font-weight: 600;
    font-style: italic;
  }
  @font-face {
    font-family: 'Ariatextg1';
    src: url('../../../../../../templates/emundus_vanilla/fonts/AriaTextG1-Italic.otf') format('opentype');
    font-weight: 400;
    font-style: italic;
  }
/* END - Your CSS styling ends here */
EOT;