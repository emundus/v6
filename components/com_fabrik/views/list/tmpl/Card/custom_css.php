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
#g-page-surround {
  background: #f5f5f5 !important;
}
.span12{
width:100%!important;
}
.g-container-main{
background-image:none!important;
}
a {
  -webkit-transition: color 200ms cubic-bezier(.55, .085, .68, .53);
  transition: color 200ms cubic-bezier(.55, .085, .68, .53);
  color: #482683;
  text-decoration: underline;
}

.em-containerbottomfooter {
 z-index: -1 !important;
}

#g-container-main {

  padding: 0px !important;
 }

 .fabrikNav .list-footer {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 80px;
    flex-direction: column;
 }


 .fabrikNav .list-footer .limit .input-prepend.input-append  span.add-on  {
   height: 2rem;
   background: #F5F5F5;
   border-color: #DDDDDD;
   color: #82208B !important;
   font-size: 14px !important;
   font-weight: normal !important;
   padding: 6px;
 }

 .fabrikNav .list-footer .limit .input-prepend.input-append  span.add-on small  {

    color: #82208B !important;
    font-size: 14px !important;
    font-weight: normal !important;

  }

 .fabrikNav .list-footer .limit .input-prepend.input-append select#limit349  {
    height: 2rem;
    background: #ffffff;
    border-color: #DDDDDD;
    color: #999999 !important;
    font-size: 14px !important;
    font-weight: normal !important;
    padding: 6px;
  }

 .fabrikNav .list-footer div.pagination ul.pagination-list {
    -webkit-box-shadow: none;
    -moz-box-shadow: none ;
     box-shadow: none;
   
}

.fabrikNav .list-footer div.pagination ul.pagination-list li a {
          color: #82208B !important;
}


 select.inputbox.fabrik_filter.input-large  {
  width: 50%;
}

.filtertable.table.table-striped .inputbox.fabrik_filter.input-large {
  padding-left : 30px;
  padding-right : 23px;
}

a:hover {
  color: #82358b !important;
}

.g-content {
    margin: 0rem !important; 
    padding: 0rem !important;
}

#g-navigation {
   
   border-bottom : none !important;
   background: #F5F5F5!important;
}

.g-container {
    width: 100% !important;
}

form {
    margin: 0px !important;
}

.em-paragrapheprojet-explain {
 
  margin-top: 10px;
  margin-bottom: 50px;
  margin-right: auto;
  margin-left: auto;
  color: #636363;
  font-size: 18px;
  line-height: 24px;
  text-align: center;
  font-weight: 500;
}
.em-ulprojet-explain {
  width: 40%;
  margin-top: 10px;
  margin-bottom: 50px;
  margin-right: auto;
  margin-left: auto;
  color: #636363;
  font-size: 18px;
  line-height: 24px;
  text-align: left;
  font-weight: 500;
}
.em-divprojet-explain {
    width: 50%;
    margin-top: 10px;
    margin-bottom: 50px;
    margin-right: auto;
    margin-left: auto;
    color: #636363;
    font-size: 18px;
    line-height: 24px;
    text-align: center;
    font-weight: 500;
  }

p.em-paragrapheprojet-explain {
width: 100%; 
}

.em-wrappernavbar {
  position: fixed;
  left: 0%;
  top: 0%;
  right: 0%;
  bottom: auto;
  z-index: 500;
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
  line-height: 28px !important;

}

.em-itemmenu:hover {
  background-color: #482683;
  color: #fff !important;
  line-height: 28px !important;
}

.view-list {
    background-color: #f5f5f5;
    font-family: Wigrum, sans-serif;
    color: #333;
  }

  .view-list h1, .view-list h2, .view-list h3, .view-list h4 {
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

.em-filter-label {
  font-size: 18px;
  font-weight: 500;
  color: #636363;
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

.em-sectionmain {
  position: relative;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  overflow: hidden;
  height: 100vh;
  padding-top: 180px;
  padding-bottom: 50px;
  -webkit-box-pack: center;
  -webkit-justify-content: center;
  -ms-flex-pack: center;
  justify-content: center;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
}

.em-h1, h1 {
  width: 60%;
  margin-right: auto;
  color: #482683;
  font-size: 60px;
  line-height: 55px;
  text-align: left;
}

.em-h2, h2 {
  width: 84%;
  margin-top: 0px;
  margin-bottom: 0px;
  font-family: Ariatextg1, sans-serif;
  color: #482683;
  font-size: 46px;
  line-height: 46px;
  text-align: right;
}

.em-imagecategory {
  width: 100%;
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

.em-containcercategoryphase1 {
  position: absolute;
  left: 0%;
  top: 0%;
  right: 0%;
  bottom: 0%;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  overflow: hidden;
  -webkit-box-pack: center;
  -webkit-justify-content: center;
  -ms-flex-pack: center;
  justify-content: center;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
}

.em-categorymore {
  position: absolute;
  left: 0%;
  top: auto;
  right: 0%;
  bottom: 10px;
  z-index: 20;
  display: block;
  width: 40px;
  height: 40px;
  margin-right: auto;
  margin-left: auto;
  border-radius: 50%;
  background-color: #482683;
  background-image: url('../../../../../../images/custom/Plus.svg');
  background-position: 50% 50%;
  background-size: 40%;
  background-repeat: no-repeat;
}

.div-block {
  background-color: transparent;
  background-image: none;
  -webkit-transform: translate(0px, 100%);
  -ms-transform: translate(0px, 100%);
  transform: translate(0px, 100%);
}

.em-paragraphecategory {
  position: relative;
  z-index: 5;
}

.em-overlaycategory {
  position: absolute;
  left: 0%;
  top: 0%;
  right: 0%;
  bottom: 0%;
  background-color: rgba(72, 38, 131, 0.9);
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

.em-imagecategory2 {
  width: 100%;
}

.em-imagecategory3 {
  width: 100%;
}

.em-imagecategory4 {
  width: 100%;
}

.em-imagecategory5 {
  width: 100%;
}

.em-imagecategory6 {
  width: 100%;
}

.em-containeremundus {
  width: 90%;
  margin-right: auto;
  margin-left: auto;
}

.em-homepurple {
  position: absolute;
  left: 1%;
  top: 15%;
  right: 0%;
  bottom: auto;
  width: 100%;
}

.em-homepicture {
  position: absolute;
  left: 0%;
  top: 0%;
  right: 0%;
  bottom: 0%;
  width: 100%;
  height: 100%;
  -o-object-fit: cover;
  object-fit: cover;
}

.em-homeorange {
  position: absolute;
  left: auto;
  top: auto;
  right: 8%;
  bottom: -52%;
  width: 100%;
}

.em-colhero {
  position: relative;
  z-index: 15;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 90%;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
}

.em-divider {
  width: 15%;
  height: 8px;
  margin-top: 40px;
  margin-bottom: 40px;
  background-color: #2cbfdc;
}

.em-paragraphehero {
  width: 70%;
  color: #636363;
  font-size: 18px;
  line-height: 24px;
}

.em-button {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 260px;
  margin-top: 30px;
  padding: 17px 20px;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
  background-color: #482683;
  -webkit-transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53);
  transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53);
  transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), transform 200ms cubic-bezier(.55, .085, .68, .53);
  transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), transform 200ms cubic-bezier(.55, .085, .68, .53), -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53);
  color: #fff;
  text-decoration: none;
  text-transform: uppercase;
}

.em-button:hover {
  background-color: #ee7937;
  -webkit-transform: translate(0px, -3px);
  -ms-transform: translate(0px, -3px);
  transform: translate(0px, -3px);
}

.em-arrowcta {
  z-index: 50;
  width: 15px;
  height: 15px;
  margin-right: 5px;
}

.em-lechallenge {
  position: relative;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  min-height: 70vh;
  padding-top: 90px;
  padding-bottom: 90px;
  -webkit-box-pack: center;
  -webkit-justify-content: center;
  -ms-flex-pack: center;
  justify-content: center;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
  background-color: #fff;
}

.em-h2center {
  width: 50%;
  margin-right: auto;
  margin-bottom: 20px;
  margin-left: auto;
  color: #482683;
  font-size: 46px;
  line-height: 46px;
  text-align: center;
}

.em-parentflexright {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
  -webkit-flex-direction: column;
  -ms-flex-direction: column;
  flex-direction: column;
  -webkit-box-align: end;
  -webkit-align-items: flex-end;
  -ms-flex-align: end;
  align-items: flex-end;
}

.em-dividerbleu {
  width: 15%;
  height: 8px;
  margin-top: 30px;
  margin-bottom: 30px;
  background-color: #2cbfdc;
}

.em-paragrapheright {
  width: 84%;
  margin-bottom: 15px;
  color: #636363;
  font-size: 18px;
  line-height: 24px;
  text-align: right;
}

.em-challengecontainer {
  position: relative;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 100%;
  height: auto;
  padding-top: 60px;
  padding-right: 60px;
  padding-bottom: 60px;
  float: right;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
  background-color: #fff;
  box-shadow: 0 11px 50px 0 rgba(99, 99, 99, 0.2);
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
  
}

.em-textcta2 {
    position: relative;
    z-index: 50;
    padding-top: 3px;
    line-height: 14px;
    padding-left: 19px;
    font-weight: 500;
  }
  

.em-h3calendrier {
  margin-bottom: 7px;
  font-family: Ariatextg1, sans-serif;
  color: #eb5395;
  font-size: 37px;
  line-height: 37px;
  text-align: right;
}

.em-paragraphe-calendrier {
  color: #636363;
  font-size: 18px;
  line-height: 24px;
  text-align: right;
}

.em-datecalendrier {
  margin-top: 15px;
  margin-bottom: 15px;
  color: #636363;
  font-size: 18px;
  line-height: 20px;
  font-weight: 500;
  text-align: right;
}

.em-calendrier {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  margin-bottom: 7%;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
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

.em-h3calendrierright {
  margin-bottom: 7px;
  font-family: Ariatextg1, sans-serif;
  color: #2da4d0;
  font-size: 37px;
  line-height: 37px;
  text-align: left;
}

.em-datecalendrierright {
  margin-top: 15px;
  margin-bottom: 15px;
  color: #636363;
  font-size: 18px;
  line-height: 20px;
  font-weight: 500;
  text-align: left;
}

.em-paragraphe-calendrierright {
  color: #636363;
  font-size: 18px;
  line-height: 24px;
  text-align: left;
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

.em-h3calendrier2 {
  margin-bottom: 7px;
  font-family: Ariatextg1, sans-serif;
  color: #f4c60d;
  font-size: 37px;
  line-height: 37px;
  text-align: right;
}

.em-h3calendrierright2 {
  width: 90%;
  margin-bottom: 7px;
  font-family: Ariatextg1, sans-serif;
  color: #9fc766;
  font-size: 37px;
  line-height: 37px;
  text-align: left;
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

.em-colcentercalendrier {
  height: 100%;
  -webkit-align-self: center;
  -ms-flex-item-align: center;
  -ms-grid-row-align: center;
  align-self: center;
  -webkit-box-flex: 0;
  -webkit-flex: 0 0 auto;
  -ms-flex: 0 0 auto;
  flex: 0 0 auto;
}

.em-recompenses {
  display: none;
  min-height: 90vh;
  padding-top: 100px;
  padding-bottom: 100px;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
  background-image: url('../../../../../../images/custom/orangeshape.png'), -webkit-gradient(linear, left top, left bottom, from(#482683), to(#482683));
  background-image: url('../../../../../../images/custom/orangeshape.png'), linear-gradient(180deg, #482683, #482683);
  background-position: 50% 50%, 0px 0px;
  background-size: cover, auto;
  background-repeat: no-repeat, repeat;
  background-attachment: fixed, scroll;
}

.em-imagecalendrierleft {
  position: relative;
  height: 350px;
  float: right;
}

.em-imagecalendrierright {
  height: 350px;
  float: left;
  -o-object-fit: fill;
  object-fit: fill;
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

.em-containerbarrerond {
  position: absolute;
  left: 10px;
  top: 50%;
  overflow: hidden;
  width: 50%;
  height: 25px;
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

.em-containerbarrerond-2 {
  position: absolute;
  top: 50%;
  right: 10px;
  overflow: hidden;
  width: 50%;
  height: 25px;
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

.em-parentflexleft {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
  -webkit-flex-direction: column;
  -ms-flex-direction: column;
  flex-direction: column;
  -webkit-box-align: start;
  -webkit-align-items: flex-start;
  -ms-flex-align: start;
  align-items: flex-start;
}

.em-challengecontainer-left {
  position: relative;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 100%;
  height: auto;
  padding-top: 60px;
  padding-bottom: 60px;
  padding-left: 60px;
  float: right;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
  background-color: #fff;
  box-shadow: 0 11px 50px 0 rgba(99, 99, 99, 0.2);
}

.em-dividerpurple {
  width: 15%;
  height: 8px;
  margin-top: 30px;
  margin-bottom: 30px;
  background-color: #482683;
}

.em-paragrapheleft {
  width: 90%;
  color: #636363;
  font-size: 1.2vw;
  line-height: 1.7vw;
  text-align: left;
}

.em-sectionfooter {
  padding-top: 25px !important;
  padding-bottom: 25px !important;
  background-color: #fff !important;
  box-shadow: 0 -1px 6px 1px rgba(99, 99, 99, 0.15);
}

.em-rowthematiques {
  margin-top: 25px;
}

.em-h2-2 {
  width: 80%;
  margin-top: 0px;
  margin-bottom: 0px;
  font-family: Ariatextg1, sans-serif;
  color: #482683;
  font-size: 3vw;
  line-height: 3.5vw;
  text-align: left;
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
  width: 70% !important;
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
  font-weight: 500;
  text-align: center;
  text-decoration: none;
  text-transform: uppercase;
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

.em-courreur {
  position: absolute;
  left: 0%;
  top: auto;
  right: auto;
  bottom: 0%;
  width: 100%;
  height: 100%;
  background-image: url('../../../../../../images/custom/5e0f6d7684880a258c55cff1_5e04d6a54c88a6740a582b50_blue-shape.png');
  background-position: 0% 100%;
  background-size: 44%;
  background-repeat: no-repeat;
  background-attachment: fixed;
}

.em-sectionreglement {
  min-height: 100vh;
  padding-top: 130px;
  padding-bottom: 110px;
  background-image: url('../../../../../../images/custom/5e021faa8d3c51660ee2747a_Jaune.png');
  background-position: 0% 100%;
  background-size: auto;
  background-repeat: no-repeat;
  background-attachment: fixed;
}

.em-sectionapropos {
  min-height: 100vh;
  padding-top: 170px;
  padding-bottom: 80px;
  background-color: #fff;
  background-image: -webkit-gradient(linear, left top, left bottom, from(hsla(0, 0%, 100%, 0.4)), to(hsla(0, 0%, 100%, 0.4))), url('../../../../../../images/custom/vyv_wave_home_header_full--white-near.png');
  background-image: linear-gradient(180deg, hsla(0, 0%, 100%, 0.4), hsla(0, 0%, 100%, 0.4)), url('../../../../../../images/custom/vyv_wave_home_header_full--white-near.png');
  background-position: 0px 0px, 50% 50%;
  background-size: auto, contain;
  background-repeat: repeat, no-repeat;
  background-attachment: scroll, fixed;
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

.em-wrapperr-glement {
  width: 90%;
  margin-right: auto;
  margin-left: auto;
  padding-top: 25px;
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

.em-sectionchallenge {
  padding-top: 90px;
  padding-bottom: 90px;
  background-color: #fff;
}

.em-colhero1 {
  position: relative;
  z-index: 15;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 90%;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
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

.em-menureglement {
  -webkit-transition: color 200ms cubic-bezier(.55, .085, .68, .53), -webkit-transform 200ms ease;
  transition: color 200ms cubic-bezier(.55, .085, .68, .53), -webkit-transform 200ms ease;
  transition: transform 200ms ease, color 200ms cubic-bezier(.55, .085, .68, .53);
  transition: transform 200ms ease, color 200ms cubic-bezier(.55, .085, .68, .53), -webkit-transform 200ms ease;
  font-family: Ariatextg1, sans-serif;
  color: #482683;
  font-size: 23px;
  line-height: 25px;
  font-weight: 700;
  text-decoration: none;
}

.em-menureglement:hover {
  -webkit-transform: translate(0px, -3px);
  -ms-transform: translate(0px, -3px);
  transform: translate(0px, -3px);
  color: #ee7937;
}

.em-menureglement.w--current {
  color: #ee7937;
}

.em-submenureglement {
  font-size: 16px;
}

.em-wrappermenureglememnt {
  margin-bottom: 20px;
}

.em-articletitle {
  margin-top: 0px;
  font-family: Ariatextg1, sans-serif;
  color: #482683;
  font-size: 25px;
  line-height: 25px;
  font-weight: 700;
}

.em-paragraphe-title {
  margin-bottom: 0px;
  color: #636363;
  font-size: 18px;
  line-height: 24px;
}

.em-wrapperarticle {
  padding-top: 40px;
}

.em-colarticle {
  position: -webkit-sticky;
  position: sticky;
  top: 90px;
  padding-top: 48px;
  padding-left: 100px;
}

.em-containerarticle {
  padding-top: 0px;
  padding-left: 46px;
}

.em-wrapperapropos {
  width: 90%;
  margin-right: auto;
  margin-left: auto;
  padding-top: 25px;
}

.em-rowabout {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 100%;
  margin-right: auto;
  margin-bottom: 110px;
  margin-left: auto;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
}

.em-paragraphewhite {
  width: 90%;
  color: #636363;
  font-size: 18px;
  line-height: 24px;
}

.em-divider-blue {
  width: 15%;
  height: 8px;
  margin-top: 40px;
  margin-bottom: 40px;
  background-color: #2cbfdc;
}

.em-h2-white {
  width: 100%;
  margin-right: auto;
  color: #482683;
  font-size: 45px;
  line-height: 45px;
  text-align: left;
}

.em-divider-orange {
  width: 15%;
  height: 8px;
  margin-top: 40px;
  margin-bottom: 40px;
  margin-left: auto;
  background-color: #2cbfdc;
}

.em-paragraphewhite-left {
  width: 90%;
  margin-left: auto;
  color: #636363;
  font-size: 18px;
  line-height: 24px;
  text-align: right;
}

.em-h2-white-left {
  width: 100%;
  margin-right: 0px;
  color: #482683;
  font-size: 45px;
  line-height: 45px;
  text-align: right;
}

.em-imageapropos {
  height: 250px;
  padding-right: 25px;
  padding-left: 25px;
  -o-object-fit: contain;
  object-fit: contain;
}


.em-button-vyv-projet {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex !important;
    width: 255px;
    margin-top: 20px;
    padding: 17px 30px;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    background-color: #82358b;
    -webkit-transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53);
    transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53);
    transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), transform 200ms cubic-bezier(.55, .085, .68, .53);
    transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), transform 200ms cubic-bezier(.55, .085, .68, .53), -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53);
    color: #fff;
    text-decoration: none;
    text-transform: uppercase;
    font-weight: 500;
  }
  
  .em-button-vyv-projet:hover {
    background-color: #482683;
    -webkit-transform: translate(0px, -3px);
    -ms-transform: translate(0px, -3px);
    transform: translate(0px, -3px);
    color: #fff !important;
  }

.em-button-vyv {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 295px;
  margin-top: 30px;
  padding: 17px 30px;
  -webkit-box-pack: center;
  -webkit-justify-content: center;
  -ms-flex-pack: center;
  justify-content: center;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
  background-color: #2cbfdc;
  -webkit-transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53);
  transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53);
  transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), transform 200ms cubic-bezier(.55, .085, .68, .53);
  transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), transform 200ms cubic-bezier(.55, .085, .68, .53), -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53);
  color: #fff;
  text-decoration: none;
  text-transform: uppercase;
}

.em-button-vyv:hover {
  background-color: #482683;
  -webkit-transform: translate(0px, -3px);
  -ms-transform: translate(0px, -3px);
  transform: translate(0px, -3px);
  color: #fff;
}

.em-button-festival {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 270px;
  margin-top: 30px;
  margin-left: auto;
  padding: 17px 30px;
  -webkit-box-pack: center;
  -webkit-justify-content: center;
  -ms-flex-pack: center;
  justify-content: center;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
  background-color: #2cbfdc;
  -webkit-transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53);
  transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53);
  transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), transform 200ms cubic-bezier(.55, .085, .68, .53);
  transition: background-color 200ms cubic-bezier(.55, .085, .68, .53), transform 200ms cubic-bezier(.55, .085, .68, .53), -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53);
  color: #fff;
  text-decoration: none;
  text-transform: uppercase;
}

.em-button-festival:hover {
  background-color: #482683;
  -webkit-transform: translate(0px, -3px);
  -ms-transform: translate(0px, -3px);
  transform: translate(0px, -3px);
  color: #fff;
}

.em-imageapropos-float {
  height: 300px;
  padding-right: 25px;
  padding-left: 25px;
  float: right;
  -o-object-fit: contain;
  object-fit: contain;
}

.em-columngvyv {
  padding-left: 70px;
}

.em-divcontainerfooter {
  width: 90%;
  margin-right: auto;
  margin-left: auto;
}

.em-rowabout2 {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 100%;
  margin-right: auto;
  margin-bottom: 110px;
  margin-left: auto;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
}

.vyv-logo-top-white {
  display: none;
}

.em-h3category-2 {
  position: static;
  display: block;
  width: 100%;
  padding-right: 41px;
  padding-left: 41px;
  font-family: Ariatextg1, sans-serif;
  color: #fff;
  font-size: 21px;
  line-height: 24px;
  font-weight: 700;
  text-align: center;
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

.em-paragraphecentersubtitle-2 {
  width: 40%;
  margin-right: auto;
  margin-bottom: 50px;
  margin-left: auto;
  color: #636363;
  font-size: 18px;
  line-height: 24px;
  text-align: center;
}

.em-h2center-2 {
  width: 30%;
  margin-right: auto;
  margin-bottom: 20px;
  margin-left: auto;
  color: #482683;
  font-size: 46px;
  line-height: 46px;
  text-align: center;
}

.em-sectionthematiques-2 {
  padding-top: 90px;
  padding-bottom: 90px;
  background-color: #f5f5f5;
  background-image: -webkit-gradient(linear, left top, left bottom, from(hsla(0, 0%, 96%, 0.49)), to(hsla(0, 0%, 96%, 0.49))), url('../../../../../../images/custom/2vyv_wave_home_key-figures_center--white-near-1.png');
  background-image: linear-gradient(180deg, hsla(0, 0%, 96%, 0.49), hsla(0, 0%, 96%, 0.49)), url('../../../../../../images/custom/2vyv_wave_home_key-figures_center--white-near-1.png');
  background-position: 0px 0px, 50% 50%;
  background-size: auto, 80%;
  background-repeat: repeat, no-repeat;
}

.em-containercategory-2 {
  position: relative;
  overflow: hidden;
  width: 300px;
  height: 300px;
  margin-bottom: 20px;
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

.em-exposant {
  position: relative;
  bottom: 5px;
  font-size: 15px;
}

.em-button-nav-hero {
  position: relative;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  overflow: hidden;
  width: 260px;
  height: 55px;
  margin-top: 30px;
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

.em-button-nav-hero:hover {
  background-color: transparent;
  color: #82358b;
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

.em-parentflexmentions {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  padding-top: 70px;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
  -webkit-flex-direction: column;
  -ms-flex-direction: column;
  flex-direction: column;
  -webkit-box-align: start;
  -webkit-align-items: flex-start;
  -ms-flex-align: start;
  align-items: flex-start;
}

.em-h2-mentions {
  width: 100%;
  margin-top: 0px;
  margin-bottom: 0px;
  font-family: Ariatextg1, sans-serif;
  color: #482683;
  font-size: 46px;
  line-height: 46px;
  text-align: left;
}

.em-paragrapheright-mentions {
  width: 100%;
  color: #636363;
  font-size: 18px;
  line-height: 24px;
  text-align: left;
}

.em-subtitlementiosn {
  color: #482683;
  font-size: 30px;
  font-weight: 700;
}

.em-sectionmentionslegales {
  min-height: 100vh;
  padding-top: 130px;
  padding-bottom: 110px;
  background-image: url('../../../../../../images/custom/blue-shape.png');
  background-position: 100% 100%;
  background-size: 400px;
  background-repeat: no-repeat;
  background-attachment: fixed;
}

.utility-page-wrap {
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
}

.utility-page-content {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 260px;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
  -webkit-flex-direction: column;
  -ms-flex-direction: column;
  flex-direction: column;
  text-align: center;
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

.em-subtitilechallenge {
  display: block;
  margin-bottom: 8px;
}

.em-project-section {
  min-height: 100vh;
  padding-top: 80px;
}

.em-wrapper-project-row {
  width: 90%;
  margin-right: auto;
  margin-bottom: 90px;
  margin-left: auto;
  position:relative;
}
.em-containerimage {
  position: relative;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 80% !important;
  height: 350px !important;
  float: right;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
  background-color: #82358b;
  background-image: url('../../../../../../images/custom/VYV-Shape-1.svg');
  background-position: 50% 50%;
  background-size: contain;
  background-repeat: no-repeat;
  box-shadow: 0 11px 50px 0 rgba(99, 99, 99, 0.2);
}
.em-containerimage-bloque {
  position: relative;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 80% !important;
  height: 350px !important;
  float: right;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
  background-color: #82358b;
  background-image: url('../../../../../../images/custom/VYV-Shape-1.svg');
  background-position: 50% 50%;
  background-size: contain;
  background-repeat: no-repeat;
}
.em-containerimage2 {
  position: relative;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 80%;
  height: 350px;
  float: right;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
  background-color: #622D6E;
  background-image: url('../../../../../../images/custom/VYV-Shape-2.svg');
  background-position: 50% 50%;
  background-size: contain;
  background-repeat: no-repeat;
  box-shadow: 0 11px 50px 0 rgba(99, 99, 99, 0.2);
}
.em-containerimage2-bloque {
  position: relative;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 80%;
  height: 350px;
  float: right;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
  background-color: #622D6E;
  background-image: url('../../../../../../images/custom/VYV-Shape-2.svg');
  background-position: 50% 50%;
  background-size: contain;
  background-repeat: no-repeat;
}
.em-containerimage3 {
  position: relative;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 80%;
  height: 350px;
  float: right;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
  background-color: #482683;
  background-image: url('../../../../../../images/custom/VYV-Shape-3.svg');
  background-position: 50% 50%;
  background-size: contain;
  background-repeat: no-repeat;
  box-shadow: 0 11px 50px 0 rgba(99, 99, 99, 0.2);
}
.em-containerimage3-bloque {
  position: relative;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 80%;
  height: 350px;
  float: right;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
  background-color: #482683;
  background-image: url('../../../../../../images/custom/VYV-Shape-3.svg');
  background-position: 50% 50%;
  background-size: contain;
  background-repeat: no-repeat;
}

.em-titleproject {
  margin-top: 0px;
  margin-bottom: 0px;
  color: #482683;
  font-size: 36px;
  line-height: 36px;
  font-weight: 800;
  text-transform: lowercase;
}

.em-titleproject::first-letter {
 
  text-transform: capitalize;
}

.em-thematiqueproject {
  margin-bottom: 20px;
  margin-top: 6px;
  color: #82358B;
  font-size: 21px;
  line-height: 26px;
  font-weight: 500;
}

.em-wrappertextproject {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  padding-right: 60px;
  padding-left: 60px;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
  -webkit-flex-direction: column;
  -ms-flex-direction: column;
  flex-direction: column;
  -webkit-box-align: start;
  -webkit-align-items: flex-start;
  -ms-flex-align: start;
  align-items: flex-start;
  text-align: left;
  width: 100%;
}

.em-wrappertextproject.textinvert {
  -webkit-box-align: end;
  -webkit-align-items: flex-end;
  -ms-flex-align: end;
  align-items: flex-end;
  text-align: right;
}

.em-paragrapheprojet {
  color: #636363;
  font-weight: 500;
  font-size: 18px;
  line-height: 24px;
  margin-bottom: 10px;
  width: 70%;
}

.em-rowproject {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
}

.em-rowproject.invert {
  -webkit-box-orient: horizontal;
  -webkit-box-direction: reverse;
  -webkit-flex-direction: row-reverse;
  -ms-flex-direction: row-reverse;
  flex-direction: row-reverse;
}

.em-rowproject._2 {
  -webkit-box-orient: horizontal;
  -webkit-box-direction: reverse;
  -webkit-flex-direction: row-reverse;
  -ms-flex-direction: row-reverse;
  flex-direction: row-reverse;
}

.em-rowproject.rowinvert {
  -webkit-box-orient: horizontal;
  -webkit-box-direction: reverse;
  -webkit-flex-direction: row-reverse;
  -ms-flex-direction: row-reverse;
  flex-direction: row-reverse;
}

.em-button-project {
  position: relative;
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex !important;
  overflow: hidden;
  width: 220px;
  height: 55px;
  margin-top: 25px;
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

.em-button-project:hover {
  background-color: transparent;
  color: #82358b;
}

.em-wrappertextproject2 {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
  -webkit-flex-direction: column;
  -ms-flex-direction: column;
  flex-direction: column;
  -webkit-box-pack: end;
  -webkit-justify-content: flex-end;
  -ms-flex-pack: end;
  justify-content: flex-end;
  -webkit-box-align: end;
  -webkit-align-items: flex-end;
  -ms-flex-align: end;
  align-items: flex-end;
  text-align: right;
}

.em-wrappercontainerimage {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-pack: end;
  -webkit-justify-content: flex-end;
  -ms-flex-pack: end;
  justify-content: flex-end;
}

.em-wrappercontainerimage.imageinvert {
  -webkit-box-pack: start;
  -webkit-justify-content: flex-start;
  -ms-flex-pack: start;
  justify-content: flex-start;
}

.em-imageproject {
  width: 100%;
  height: 100%;
  -o-object-fit: cover;
  object-fit: cover;
}

.div-block-3 {
  position: absolute;
  left: auto;
  top: 0%;
  right: 0%;
  bottom: auto;
  width: 100px;
  height: 100px;
}

.em-protections-des-donnees {
  margin-top: 10px;
  color: #636363;
  text-decoration: underline;
  font-weight: 400;
}

.em-mentionslegales-2 {
  margin-top: 43px;
  color: #636363;
  font-weight: 400;
}

.em-menufooter-2 {
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
  font-weight: 500;
  text-align: center;
  text-decoration: none;
  text-transform: uppercase;
}

.em-menufooter-2:hover {
  background-color: #482683;
  color: #fff;
}

.em-menufooter-2.w--current {
  background-color: #82358b;
}

.em-menufooter-2.w--current:hover {
  background-color: #482683;
}

.em-sectionfooter-2 {
  padding-top: 25px;
  padding-bottom: 25px;
  background-color: #fff;
  box-shadow: 0 -1px 6px 1px rgba(99, 99, 99, 0.15);
}

.em-sectionfooter-2-copy {
  padding-top: 25px;
  padding-bottom: 25px;
  background-color: #fff;
  box-shadow: 0 -1px 6px 1px rgba(99, 99, 99, 0.15);
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

@media (max-width: 991px) {
  .em-logonavbar.w--current {
    width: 15%;
    height: 60px;
    margin-top: 7px;
    margin-bottom: 7px;
  }

  .em-filter-label {
    width: 100%;
    text-align: center;
}
.em-paragrapheprojet-explain {
    width: 80% !important;
  }


  .filtertable.table.table-striped .inputbox.fabrik_filter.input-large {
    padding-left : 10px;
    padding-right : 23px;
  }

  .nav-link {
    margin-right: 8px;
    margin-left: 8px;
    font-size: 9px !important;
    line-height: 9px !important;
    font-weight: 400;
  }


  #g-container-main {
      padding: 0px !important;
  }

  .em-itemmenu {
    margin-right: 4px;
    margin-left: 4px;
   
    font-size: 13px;
    line-height: 13vw;
  }
  .em-wrapperitemmenu {
    padding-right: 0px;
  }
  .em-wrappermenu {
    padding-right: 30px;
    padding-left: 30px;
  }
  .em-sectionmain {
    height: auto;
    padding-bottom: 100px;
  }
  .em-h1, h1 {
    width: 100%;
    font-size: 47px;
    line-height: 42px;
  }
  .em-h2, h2 {
    width: 90%;
    font-size: 40px;
    line-height: 40px;
  }
  .em-categorymore {
    width: 30px;
    height: 30px;
  }
  .div-block {
    padding: 35px 20px;
  }
  .em-paragraphecategory {
    font-size: 11px;
    line-height: 15px;
  }
  .em-homepurple {
    left: auto;
    top: auto;
    right: 0%;
    bottom: 0%;
    width: 50%;
    height: 100%;
    -o-object-fit: cover;
    object-fit: cover;
  }
  .em-homepicture {
    left: auto;
    top: auto;
    right: 0%;
    bottom: 0%;
    width: 50%;
    -o-object-fit: cover;
    object-fit: cover;
  }
  .em-homeorange {
    left: auto;
    top: auto;
    right: 0%;
    bottom: 0%;
    display: none;
    width: 70%;
  }
  .em-colhero {
    -webkit-box-orient: vertical;
    -webkit-box-direction: reverse;
    -webkit-flex-direction: column-reverse;
    -ms-flex-direction: column-reverse;
    flex-direction: column-reverse;
  }
  .em-divider {
    width: 100px;
    height: 6px;
    margin-top: 30px;
    margin-bottom: 30px;
  }
  .em-paragraphehero {
    width: 100%;
    font-size: 15px;
    line-height: 20px;
  }
  .em-arrowcta {
    width: 13px;
    height: 13px;
  }
  .em-lechallenge {
    min-height: 0px;
    padding-bottom: 90px;
  }
  .em-h2center {
    width: 60%;
    margin-bottom: 35px;
    font-size: 36px;
    line-height: 37px;
  }
  .em-dividerbleu {
    width: 100px;
    height: 6px;
    margin-top: 30px;
    margin-bottom: 30px;
  }
  .em-paragrapheright {
    width: 90%;
    font-size: 15px;
    line-height: 20px;
  }
  .em-challengecontainer {
    padding-top: 45px;
    padding-bottom: 45px;
  }
  .em-h3calendrier {
    font-size: 27px;
    line-height: 30px;
  }
  .em-paragraphe-calendrier {
    font-size: 15px;
    line-height: 20px;
  }
  .em-etapecalendrier {
    width: 40px;
    height: 40px;
  }
  .em-h3calendrierright {
    font-size: 27px;
    line-height: 30px;
  }
  .em-paragraphe-calendrierright {
    font-size: 15px;
    line-height: 20px;
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
  .em-h3calendrier2 {
    font-size: 27px;
    line-height: 30px;
  }
  .em-h3calendrierright2 {
    font-size: 26px;
    line-height: 30px;
  }
  .em-imagecalendrierleft {
    height: 270px;
    -o-object-fit: contain;
    object-fit: contain;
  }
  .em-imagecalendrierright {
    height: 270px;
    -o-object-fit: contain;
    object-fit: contain;
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
    font-weight: 500;
  }
  .em-rowfooter {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
  }
  .em-courreur {
    background-position: 0px 100%;
    background-size: auto 50%;
  }
  .em-sectionreglement {
    padding-top: 130px;
    background-position: -10% 100%;
  }
  .em-sectionapropos {
    min-height: auto;
    padding-top: 110px;
    padding-bottom: 5px;
  }
  .em-wrappermenufooter {
    padding-left: 0px;
  }
  .em-button-nav {
    width: 86px;
    font-size: 10px;
  }
  .em-sectionchallenge {
    padding-top: 50px;
    padding-bottom: 50px;
  }
  .column {
    -webkit-align-self: auto;
    -ms-flex-item-align: auto;
    -ms-grid-row-align: auto;
    align-self: auto;
  }
  .em-colhero1 {
    -webkit-box-orient: horizontal;
    -webkit-box-direction: normal;
    -webkit-flex-direction: row;
    -ms-flex-direction: row;
    flex-direction: row;
  }
  .em-menureglement {
    font-size: 21px;
  }
  .em-articletitle {
    font-size: 21px;
    line-height: 25px;
  }
  .em-paragraphe-title {
    font-size: 15px;
    line-height: 20px;
  }
  .em-colarticle {
    top: 110px;
    padding-left: 10px;
  }
  .em-rowabout {
    margin-bottom: 60px;
  }
  .em-paragraphewhite {
    width: 100%;
    font-size: 15px;
    line-height: 20px;
  }
  .em-divider-blue {
    width: 100px;
    height: 6px;
    margin-top: 30px;
    margin-bottom: 30px;
  }
  .em-h2-white {
    width: 100%;
    font-size: 36px;
    line-height: 37px;
  }
  .em-divider-orange {
    width: 100px;
    height: 6px;
    margin-top: 30px;
    margin-bottom: 30px;
  }
  .em-paragraphewhite-left {
    width: 100%;
    font-size: 15px;
    line-height: 20px;
  }
  .em-h2-white-left {
    width: 100%;
    font-size: 36px;
    line-height: 37px;
  }
  .em-imageapropos {
    width: 80%;
    height: auto;
    margin-left: 40px;
  }
  .em-imageapropos-float {
    width: 90%;
    height: auto;
    margin-right: 40px;
  }
  .em-columngvyv {
    padding-right: 0px;
    padding-left: 0px;
  }
  .em-rowabout2 {
    margin-bottom: 60px;
  }
  .em-h3category-2 {
    padding-right: 46px;
    padding-left: 46px;
    font-size: 17px;
    line-height: 19px;
  }
  .em-paragraphecentersubtitle-2 {
    font-size: 15px;
    line-height: 20px;
  }
  .em-h2center-2 {
    width: 50%;
    font-size: 38px;
    line-height: 37px;
  }
  .em-containercategory-2 {
    width: 220px;
    height: 220px;
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
  .em-exposant {
    font-size: 13px;
  }
  .em-button-nav-hero {
    width: 210px;
    font-size: 11px;
  }
  .em-containerarrow2 {
    margin-right: 0px;
    margin-left: 10px;
  }
  .em-parentflexmentions {
    padding-top: 45px;
  }
  .em-h2-mentions {
    width: 90%;
    font-size: 40px;
    line-height: 40px;
  }
  .em-paragrapheright-mentions {
    width: 100%;
    font-size: 15px;
    line-height: 20px;
  }
  .em-containermentions {
    padding-right: 43px;
    padding-left: 43px;
  }
  .em-sectionmentionslegales {
    padding-top: 130px;
    background-position: 100% 100%;
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
  .em-containerimage {
    width: 100%;
    height: 290px;
    padding-top: 0px;
    padding-bottom: 0px;
  }
  .em-titleproject {
    width: 100%;
    font-size: 32px;
    line-height: 31px;
  }
  .em-wrappertextproject {
    padding-right: 10px;
    padding-left: 10px;
  }
  .em-paragrapheprojet {
    width: 100%;
    font-size: 15px;
    line-height: 20px;
  }
  .em-button-project {
    width: 210px;
    margin-top: 5px;
    font-size: 11px;
  }
  .em-menufooter-2 {
    margin-right: 10px;
    margin-left: 10px;
    font-size: 14px;
    line-height: 24px;
  }
  .em-sectionfooter-2 {
    padding-right: 40px;
    padding-left: 40px;
  }
  .em-sectionfooter-2-copy {
    padding-right: 40px;
    padding-left: 40px;
  }
}
/* Favoris */
.starActive{
color:#e39809;
}
@media (max-width: 767px) {
      .em-wrappermenu {
        display: none;
      }
      .em-sectionmain {
        padding-top: 105px;
        padding-bottom: 80px;
        background-image: url('../../../../../../images/custom/5e04d6a54c88a6740a582b50_blue-shape.png');
        background-position: 100% 100%;
        background-size: 30%;
        background-repeat: no-repeat;
      }

      #listform_349_com_fabrik_349 .em-project-section .fabrikNav .list-footer .pagination ul.pagination-list  {
        display: flex;
        flex-direction: row;
        padding-left: 0px !important;
    }

    #listform_349_com_fabrik_349 .em-project-section .fabrikNav .list-footer .pagination ul.pagination-list li   {
      margin: 0px !important;
    }

  .em-h1, h1 {
    font-size: 43px;
    line-height: 42px;
  }
  .em-h2, h2 {
    width: 100%;
    padding-top: 30px;
    text-align: right;
  }
  .div-block {
    -webkit-transform: translate(0px, 100%);
    -ms-transform: translate(0px, 100%);
    transform: translate(0px, 100%);
  }
  .em-paragraphecategory {
    font-size: 11px;
  }
  .em-homepurple {
    display: none;
  }
  .em-homepicture {
    display: none;
  }
  .em-paragraphehero {
    width: 82%;
  }
  .em-button {
    width: 245px;
  }
  .em-lechallenge {
    padding-top: 40px;
    padding-bottom: 50px;
  }
  .em-h2center {
    margin-bottom: 40px;
  }
  .em-dividerbleu {
    text-align: right;
  }
  .em-paragrapheright {
    width: 100%;
    text-align: right;
  }
  .em-challengecontainer {
    padding-right: 40px;
    padding-left: 40px;
  }
  .em-calendrier {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-orient: horizontal;
    -webkit-box-direction: normal;
    -webkit-flex-direction: row;
    -ms-flex-direction: row;
    flex-direction: row;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -ms-flex-pack: center;
    justify-content: center;
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
  .em-colcentercalendrier {
    -webkit-box-flex: 1;
    -webkit-flex: 1;
    -ms-flex: 1;
    flex: 1;
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
  .em-sectionreglement {
    padding-top: 107px;
  }
  .em-sectionapropos {
    padding-top: 145px;
    padding-bottom: 25px;
    background-image: url('../../../../../../images/custom/vyv_wave_home_header_full--white-near.png');
    background-position: 50% 50%;
    background-size: cover;
    background-repeat: repeat;
    background-attachment: fixed;
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
  .em-wrapperr-glement {
    width: 95%;
  }
  .em-sectionchallenge {
    padding-bottom: 40px;
  }
  .em-colhero1 {
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
  }
  .column-shape-calendar {
    display: none;
  }
  .column-2 {
    -webkit-box-flex: 1;
    -webkit-flex: 1;
    -ms-flex: 1;
    flex: 1;
  }
  .column-3 {
    -webkit-box-flex: 1;
    -webkit-flex: 1;
    -ms-flex: 1;
    flex: 1;
  }
  .column-4 {
    -webkit-box-flex: 1;
    -webkit-flex: 1;
    -ms-flex: 1;
    flex: 1;
  }
  .column-5 {
    height: 0px;
  }
  .line1.orange {
    background-color: #482683;
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
  a.nav-link:hover {
    color: #ee7937;
  }
  a.em-itemmenu:hover {
    color: #ee7937 !important;
    background-color:transparent;
    padding: 0px !important;
    
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
  .em-menureglement {
    font-size: 19px;
    text-decoration: underline;
  }
  .em-menureglement.w--current {
    color: #ee7937;
  }
  .em-submenureglement {
    display: none;
    font-size: 14px;
    line-height: 17px;
  }
  .em-wrappermenureglememnt {
    text-align: left;
  }
  .em-articletitle {
    font-size: 19px;
    line-height: 25px;
  }
  .em-wrapperarticle {
    margin-bottom: 25px;
  }
  .em-colarticle {
    z-index: 10;
    display: block;
    margin-top: 28px;
    padding: 16px 13px 0px;
    -webkit-box-orient: horizontal;
    -webkit-box-direction: normal;
    -webkit-flex-direction: row;
    -ms-flex-direction: row;
    flex-direction: row;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
  }
  .em-containerarticle {
    padding-top: 0px;
    padding-right: 30px;
    padding-left: 30px;
  }
  .em-wrapperapropos {
    width: 95%;
    padding-right: 20px;
    padding-left: 20px;
  }
  .em-rowabout {
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
  }
  .em-paragraphewhite {
    width: 82%;
  }
  .em-h2-white {
    font-size: 40px;
    line-height: 40px;
  }
  .em-paragraphewhite-left {
    width: 82%;
  }
  .em-h2-white-left {
    font-size: 40px;
    line-height: 40px;
  }
  .em-imageapropos {
    width: 150px;
    margin-bottom: 9px;
    margin-left: 0px;
    padding-right: 0px;
    padding-left: 0px;
    float: right;
  }
  .em-imageapropos-float {
    width: 210px;
    margin-right: 0px;
    padding-right: 0px;
    padding-left: 0px;
    float: left;
    -webkit-transform: translate(-20px, 0px);
    -ms-transform: translate(-20px, 0px);
    transform: translate(-20px, 0px);
  }
  .em-rowgvyvpropos {
    padding-right: 0px;
    padding-left: 0px;
  }
  .em-rowabout2 {
    -webkit-box-orient: vertical;
    -webkit-box-direction: reverse;
    -webkit-flex-direction: column-reverse;
    -ms-flex-direction: column-reverse;
    flex-direction: column-reverse;
  }
  .vyv-logo-top-white {
    position: absolute;
    left: 0%;
    top: 0%;
    right: auto;
    bottom: auto;
    display: none;
    width: 120px;
    margin-top: 15px;
    margin-left: 5%;
  }
  .em-paragraphecentersubtitle-2 {
    width: 60%;
  }
  .em-h2center-2 {
    width: 70%;
  }
  .em-sectionthematiques-2 {
    background-image: -webkit-gradient(linear, left top, left bottom, from(null), to(null)), url('../../../../../../images/custom/2vyv_wave_home_key-figures_center--white-near-1.png');
    background-image: linear-gradient(180deg, null, null), url('../../../../../../images/custom/2vyv_wave_home_key-figures_center--white-near-1.png');
    background-size: auto, cover;
  }
  .em-containercategory-2 {
    margin-right: auto;
    margin-left: auto;
  }
  .em-button-nav-hero {
    height: 42px;
  }
  .em-parentflexmentions {
    padding-top: 10px;
  }
  .em-h2-mentions {
    width: 100%;
    padding-top: 30px;
    text-align: left;
  }
  .em-paragrapheright-mentions {
    width: 100%;
    text-align: left;
  }
  .em-subtitlementiosn {
    font-size: 24px;
    line-height: 24px;
  }
  .em-containermentions {
    padding-top: 30px;
  }
  .em-sectionmentionslegales {
    padding-top: 107px;
  }
  .em-button-404 {
    height: 42px;
    margin-top: 14px;
  }
  .em-project-section {
    padding-top: 145px;
  }
  .em-containerimage {
    height: 310px;
    padding-right: 0px;
    padding-left: 0px;
  }
  .em-titleproject {
    width: 90%;
    margin-top: 35px;
    font-size: 31px;
    line-height: 29px;
  }
  .em-wrappertextproject {
    padding-right: 0px;
    padding-left: 0px;
  }
  .em-paragrapheprojet {
    width: 100%;
    font-size: 18px;
    line-height: 17px;
  }
  .em-rowproject {
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
  }
  .em-rowproject.rowinvert {
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
  }
  .em-button-project {
    height: 42px;
  }
  .em-menufooter-2 {
    margin-right: 10px;
    margin-left: 10px;
    font-size: 12px;
  }
  .em-sectionfooter-2 {
    padding-top: 25px;
    padding-bottom: 25px;
  }
  .em-sectionfooter-2-copy {
    padding-top: 25px;
    padding-bottom: 25px;
  }
}

@media (max-width: 479px) {
  .em-sectionmain {
    height: 600px;
    padding: 120px 16px 50px;
    -webkit-box-pack: start;
    -webkit-justify-content: flex-start;
    -ms-flex-pack: start;
    justify-content: flex-start;
    background-image: url('../../../../../../images/custom/5e04d6a54c88a6740a582b50_blue-shape.png');
    background-position: 100% 100%;
    background-size: 40%;
    background-repeat: no-repeat;
    background-attachment: scroll;
  }


  .em-itemmenu {
    margin-right: 4px;
    margin-left: 4px;
   
    font-size: 9px;
    line-height: 9px;
  }
  
  .em-logofooter {
    width: 100% !important;
     margin-top: 0px;
     margin-bottom: 0px;
     margin-top: 20px;
   }

.em-containerimage {

  width: 100% !important;

}
.em-containerimage2 {
  width: 100% !important;
}

.em-containerimage3 {
  width: 100% !important;
}

.em-containerimage-bloque{

  width: 100% !important;

}
.em-containerimage2-bloque {
  width: 100% !important;
}

.em-containerimage3-bloque {
  width: 100% !important;
}

.em-wrappertextproject { 

  width: 100% !important;

}

  .em-button-vyv-projet {

    padding-left: 8px !important;

  }

  .em-filter-label {
    width: 80%;
    text-align: center;
}




  }

  .em-mentionslegales {
    margin-top: 20px;
    color: #636363;
    text-decoration: underline;
    font-weight: 400;
  }
  .em-h1, h1 {
    font-size: 42px;
    line-height: 39px;
  }
  .em-h2, h2 {
    padding-top: 9px;
    font-size: 30px;
    line-height: 30px;
  }

  .em-paragrapheprojet-explain {
    width: 50%;
  }

  .em-project-section .em-paragrapheprojet-explain p.em-thematique-deja-votee, .em-project-section .em-divprojet-explain p.em-thematique-deja-votee {
            color: #82208B;
            font-weight: 600;
            margin: 0px;
}

.em-project-section .em-paragrapheprojet-explain p.em-thematique-deja-votee:first-child {
  margin-top: 10px !important;
}


  .em-paragraphecategory {
    font-size: 12px;
  }

  .em-homepurple {
    display: none;
    width: 60%;
    height: 60%;
    -o-object-fit: contain;
    object-fit: contain;
  }
  .em-homepicture {
    right: -11%;
    display: none;
    height: 100%;
  }
  .em-colhero {
    width: 95%;
  }
  .em-lechallenge {
    padding-top: 70px;
  }
  .em-h2center {
    width: 80%;
    font-size: 30px;
    line-height: 30px;
  }
  .em-dividerbleu {
    margin-top: 24px;
    margin-bottom: 24px;
  }
  .em-paragrapheright {
    font-size: 13px;
    line-height: 18px;
  }
  .em-challengecontainer {
    width: 90%;
    margin-right: auto;
    margin-left: auto;
    padding: 25px;
  }
  .em-h3calendrier {
    font-size: 24px;
    line-height: 27px;
  }
  .em-paragraphe-calendrier {
    font-size: 13px;
    line-height: 18px;
  }
  .em-datecalendrier {
    font-size: 15px;
    line-height: 18px;
  }
  .em-calendrier {
    margin-bottom: 40px;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
  }
  .em-calendrier.inverted {
    margin-bottom: 40px;
    -webkit-box-orient: vertical;
    -webkit-box-direction: reverse;
    -webkit-flex-direction: column-reverse;
    -ms-flex-direction: column-reverse;
    flex-direction: column-reverse;
  }
  .em-etapecalendrier {
    width: 60px;
    height: 60px;
    margin-right: 0px;
  }
  .em-h3calendrierright {
    font-size: 24px;
    line-height: 27px;
  }
  .em-datecalendrierright {
    font-size: 15px;
    line-height: 18px;
  }
  .em-paragraphe-calendrierright {
    font-size: 13px;
    line-height: 18px;
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
  .em-h3calendrier2 {
    font-size: 24px;
    line-height: 27px;
  }
  .em-h3calendrierright2 {
    font-size: 24px;
    line-height: 27px;
  }
  .em-containerbarrerond {
    display: none;
  }
  .em-containerbarrerond-2 {
    display: none;
  }
  .em-sectionfooter {
    padding: 30px 15px;
  }
 
  .em-menufooter {
     /* width: 100%; */
    margin-right: 0px;
    margin-bottom: 10px;
    margin-left: 0px;
  }
  .em-courreur {
    background-position: 0px 100%;
    background-size: auto 60%;
  }
  .em-sectionreglement {
    padding-top: 70px;
  }
  .em-sectionapropos {
    padding-top: 90px;
    background-size: cover;
    background-repeat: no-repeat;
    background-attachment: scroll;
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
  .column {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    padding-right: 0px;
    padding-left: 0px;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -ms-flex-pack: center;
    justify-content: center;
  }
  .em-colhero1 {
    width: 99%;
  }
  .column-3 {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
  }
  .column-5 {
    height: 0px;
  }
  .navbar {
    margin-top: 15px;
    margin-right: 15px;
    margin-left: 5px;
    padding-right: 23px;
    padding-left: 23px;
  }
  .nav-link {
    margin-right: 8px;
    margin-left: 8px;
    font-size: 14px;
    line-height: 14px;
    font-weight: 400;
  }
  .nav-link.margin {
    margin-right: 25px;
  }
  .em-menureglement {
    font-size: 12px;
    line-height: 19px;
  }
  .em-menureglement.w--current {
    color: #ee7937;
    font-size: 12px;
  }
  .em-wrappermenureglememnt {
    margin-bottom: 10px;
    font-size: 14px;
  }
  .em-paragraphe-title {
    font-size: 13px;
    line-height: 18px;
  }
  .em-wrapperarticle {
    margin-bottom: 0px;
    padding-top: 40px;
  }
  .em-containerarticle {
    padding-right: 13px;
    padding-left: 11px;
  }
  .em-rowabout {
    margin-bottom: 75px;
  }
  .em-paragraphewhite {
    width: 100%;
  }
  .em-h2-white {
    margin-top: 5px;
  }
  .em-paragraphewhite-left {
    width: 100%;
  }
  .em-imageapropos {
    width: 130px;
  }

 


  .em-button-vyv {
    width: 100%;
    padding-right: 15px;
    padding-left: 15px;
  }
  .em-button-festival {
    width: 250px;
    padding-right: 15px;
    padding-left: 15px;
  }
  .em-imageapropos-float {
    width: 150px;
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
  .em-paragraphecentersubtitle-2 {
    width: 80%;
    font-size: 13px;
    line-height: 18px;
  }
  .em-h2center-2 {
    font-size: 30px;
    line-height: 30px;
  }
  .em-containercategory-2 {
    width: 250px;
    height: 250px;
  }
  .em-exposant {
    position: relative;
    bottom: 4px;
    font-size: 11px;
  }
  .em-button-nav-hero {
    height: 40px;
  }
  .em-h2-mentions {
    padding-top: 9px;
    font-size: 30px;
    line-height: 30px;
  }
  .em-paragrapheright-mentions {
    font-size: 13px;
    line-height: 18px;
  }
  .em-subtitlementiosn {
    line-height: 28px;
  }
  .em-containermentions {
    padding-top: 60px;
    padding-right: 23px;
    padding-left: 23px;
  }
  .em-sectionmentionslegales {
    padding-top: 70px;
  }
  .em-button-404 {
    height: 40px;
  }
  .em-project-section {
    padding-top: 120px;
  }
  .em-wrapper-project-row {
    margin-bottom: 60px;
  }
  .em-containerimage {
    width: 100%;
    height: 180px;
   
    padding: 0px;
  }
  .em-titleproject {
    font-size: 30px;
    line-height: 30px;
  }
  .em-button-project {
    height: 40px;
  }
  .em-menufooter-2 {
    width: 100%;
    margin-right: 0px;
    margin-bottom: 10px;
    margin-left: 0px;
  }
  .em-sectionfooter-2 {
    padding: 30px 15px;
  }
  .em-sectionfooter-2-copy {
    padding: 30px 15px;
  }


@font-face {
  font-family: 'Wigrum';
  src: url('../fonts/Wigrum-Bold.otf') format('opentype');
  font-weight: 700;
  font-style: normal;
}
@font-face {
  font-family: 'Wigrum';
  src: url('../fonts/Wigrum-Light.otf') format('opentype');
  font-weight: 300;
  font-style: normal;
}
@font-face {
  font-family: 'Wigrum';
  src: url('../fonts/Wigrum-Medium.otf') format('opentype');
  font-weight: 500;
  font-style: normal;
}
@font-face {
  font-family: 'Wigrum';
  src: url('../fonts/Wigrum-MediumItalic.otf') format('opentype');
  font-weight: 500;
  font-style: italic;
}
@font-face {
  font-family: 'Wigrum';
  src: url('../fonts/Wigrum-Regular.otf') format('opentype');
  font-weight: 400;
  font-style: normal;
}
@font-face {
  font-family: 'Wigrum';
  src: url('../fonts/Wigrum-BlackItalic.otf') format('opentype');
  font-weight: 900;
  font-style: italic;
}
@font-face {
  font-family: 'Wigrum';
  src: url('../fonts/Wigrum-BoldItalic.otf') format('opentype');
  font-weight: 700;
  font-style: italic;
}
@font-face {
  font-family: 'Wigrum';
  src: url('../fonts/Wigrum-Italic.otf') format('opentype');
  font-weight: 400;
  font-style: italic;
}
@font-face {
  font-family: 'Wigrum';
  src: url('../fonts/Wigrum-LightItalic.otf') format('opentype');
  font-weight: 300;
  font-style: italic;
}
@font-face {
  font-family: 'Wigrum';
  src: url('../fonts/Wigrum-Black.otf') format('opentype');
  font-weight: 900;
  font-style: normal;
}
@font-face {
  font-family: 'Ariatextg1';
  src: url('../fonts/AriaTextG1-Bold.otf') format('opentype');
  font-weight: 700;
  font-style: normal;
}
@font-face {
  font-family: 'Ariatextg1';
  src: url('../fonts/AriaTextG1.otf') format('opentype');
  font-weight: 400;
  font-style: normal;
}
@font-face {
  font-family: 'Ariatextg1';
  src: url('../fonts/AriaTextG1-BoldItalic.otf') format('opentype');
  font-weight: 700;
  font-style: italic;
}
@font-face {
  font-family: 'Ariatextg1';
  src: url('../fonts/AriaTextG1-SemiBold.otf') format('opentype');
  font-weight: 600;
  font-style: normal;
}
@font-face {
  font-family: 'Ariatextg1';
  src: url('../fonts/AriaTextG1-SemiBoldItalic.otf') format('opentype');
  font-weight: 600;
  font-style: italic;
}
@font-face {
  font-family: 'Ariatextg1';
  src: url('../fonts/AriaTextG1-Italic.otf') format('opentype');
  font-weight: 400;
  font-style: italic;
}

@font-face {
  font-family: 'webflow-icons';
  src: url("data:application/x-font-ttf;charset=utf-8;base64,AAEAAAALAIAAAwAwT1MvMg8SBiUAAAC8AAAAYGNtYXDpP+a4AAABHAAAAFxnYXNwAAAAEAAAAXgAAAAIZ2x5ZmhS2XEAAAGAAAADHGhlYWQTFw3HAAAEnAAAADZoaGVhCXYFgQAABNQAAAAkaG10eCe4A1oAAAT4AAAAMGxvY2EDtALGAAAFKAAAABptYXhwABAAPgAABUQAAAAgbmFtZSoCsMsAAAVkAAABznBvc3QAAwAAAAAHNAAAACAAAwP4AZAABQAAApkCzAAAAI8CmQLMAAAB6wAzAQkAAAAAAAAAAAAAAAAAAAABEAAAAAAAAAAAAAAAAAAAAABAAADpAwPA/8AAQAPAAEAAAAABAAAAAAAAAAAAAAAgAAAAAAADAAAAAwAAABwAAQADAAAAHAADAAEAAAAcAAQAQAAAAAwACAACAAQAAQAg5gPpA//9//8AAAAAACDmAOkA//3//wAB/+MaBBcIAAMAAQAAAAAAAAAAAAAAAAABAAH//wAPAAEAAAAAAAAAAAACAAA3OQEAAAAAAQAAAAAAAAAAAAIAADc5AQAAAAABAAAAAAAAAAAAAgAANzkBAAAAAAEBIAAAAyADgAAFAAAJAQcJARcDIP5AQAGA/oBAAcABwED+gP6AQAABAOAAAALgA4AABQAAEwEXCQEH4AHAQP6AAYBAAcABwED+gP6AQAAAAwDAAOADQALAAA8AHwAvAAABISIGHQEUFjMhMjY9ATQmByEiBh0BFBYzITI2PQE0JgchIgYdARQWMyEyNj0BNCYDIP3ADRMTDQJADRMTDf3ADRMTDQJADRMTDf3ADRMTDQJADRMTAsATDSANExMNIA0TwBMNIA0TEw0gDRPAEw0gDRMTDSANEwAAAAABAJ0AtAOBApUABQAACQIHCQEDJP7r/upcAXEBcgKU/usBFVz+fAGEAAAAAAL//f+9BAMDwwAEAAkAABcBJwEXAwE3AQdpA5ps/GZsbAOabPxmbEMDmmz8ZmwDmvxmbAOabAAAAgAA/8AEAAPAAB0AOwAABSInLgEnJjU0Nz4BNzYzMTIXHgEXFhUUBw4BBwYjNTI3PgE3NjU0Jy4BJyYjMSIHDgEHBhUUFx4BFxYzAgBqXV6LKCgoKIteXWpqXV6LKCgoKIteXWpVSktvICEhIG9LSlVVSktvICEhIG9LSlVAKCiLXl1qal1eiygoKCiLXl1qal1eiygoZiEgb0tKVVVKS28gISEgb0tKVVVKS28gIQABAAABwAIAA8AAEgAAEzQ3PgE3NjMxFSIHDgEHBhUxIwAoKIteXWpVSktvICFmAcBqXV6LKChmISBvS0pVAAAAAgAA/8AFtgPAADIAOgAAARYXHgEXFhUUBw4BBwYHIxUhIicuAScmNTQ3PgE3NjMxOAExNDc+ATc2MzIXHgEXFhcVATMJATMVMzUEjD83NlAXFxYXTjU1PQL8kz01Nk8XFxcXTzY1PSIjd1BQWlJJSXInJw3+mdv+2/7c25MCUQYcHFg5OUA/ODlXHBwIAhcXTzY1PTw1Nk8XF1tQUHcjIhwcYUNDTgL+3QFt/pOTkwABAAAAAQAAmM7nP18PPPUACwQAAAAAANciZKUAAAAA1yJkpf/9/70FtgPDAAAACAACAAAAAAAAAAEAAAPA/8AAAAW3//3//QW2AAEAAAAAAAAAAAAAAAAAAAAMBAAAAAAAAAAAAAAAAgAAAAQAASAEAADgBAAAwAQAAJ0EAP/9BAAAAAQAAAAFtwAAAAAAAAAKABQAHgAyAEYAjACiAL4BFgE2AY4AAAABAAAADAA8AAMAAAAAAAIAAAAAAAAAAAAAAAAAAAAAAAAADgCuAAEAAAAAAAEADQAAAAEAAAAAAAIABwCWAAEAAAAAAAMADQBIAAEAAAAAAAQADQCrAAEAAAAAAAUACwAnAAEAAAAAAAYADQBvAAEAAAAAAAoAGgDSAAMAAQQJAAEAGgANAAMAAQQJAAIADgCdAAMAAQQJAAMAGgBVAAMAAQQJAAQAGgC4AAMAAQQJAAUAFgAyAAMAAQQJAAYAGgB8AAMAAQQJAAoANADsd2ViZmxvdy1pY29ucwB3AGUAYgBmAGwAbwB3AC0AaQBjAG8AbgBzVmVyc2lvbiAxLjAAVgBlAHIAcwBpAG8AbgAgADEALgAwd2ViZmxvdy1pY29ucwB3AGUAYgBmAGwAbwB3AC0AaQBjAG8AbgBzd2ViZmxvdy1pY29ucwB3AGUAYgBmAGwAbwB3AC0AaQBjAG8AbgBzUmVndWxhcgBSAGUAZwB1AGwAYQByd2ViZmxvdy1pY29ucwB3AGUAYgBmAGwAbwB3AC0AaQBjAG8AbgBzRm9udCBnZW5lcmF0ZWQgYnkgSWNvTW9vbi4ARgBvAG4AdAAgAGcAZQBuAGUAcgBhAHQAZQBkACAAYgB5ACAASQBjAG8ATQBvAG8AbgAuAAAAAwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA==") format('truetype');
  font-weight: normal;
  font-style: normal;
}



.em-share{
    display:flex;
    flex-direction: row;
    justify-content:center;
    align-items:center;
}
.em-share a {
    margin-right:10px;
}
.em-share a i{
    font-size: 2rem;
}




[class^="w-icon-"],
[class*=" w-icon-"] {
  /* use !important to prevent issues with browser extensions that change fonts */
  font-family: 'webflow-icons' !important;
  speak: none;
  font-style: normal;
  font-weight: normal;
  font-variant: normal;
  text-transform: none;
  line-height: 1;
  /* Better Font Rendering =========== */
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.w-icon-slider-right:before {
  content: "\e600";
}
.w-icon-slider-left:before {
  content: "\e601";
}
.w-icon-nav-menu:before {
  content: "\e602";
}
.w-icon-arrow-down:before,
.w-icon-dropdown-toggle:before {
  content: "\e603";
}
.w-icon-file-upload-remove:before {
  content: "\e900";
}
.w-icon-file-upload-icon:before {
  content: "\e903";
}
* {
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}
html {
  height: 100%;
}
body {
  margin: 0;
  min-height: 100%;
  background-color: #fff;
  font-family: Arial, sans-serif;
  font-size: 14px;
  line-height: 20px;
  color: #333;
}
img {
  max-width: 100%;
  vertical-align: middle;
  display: inline-block;
}
html.w-mod-touch * {
  background-attachment: scroll !important;
}
.w-block {
  display: block;
}
.w-inline-block {
  max-width: 100%;
  display: inline-block;
}
.w-clearfix:before,
.w-clearfix:after {
  content: " ";
  display: table;
  grid-column-start: 1;
  grid-row-start: 1;
  grid-column-end: 2;
  grid-row-end: 2;
}
.w-clearfix:after {
  clear: both;
}
.w-hidden {
  display: none;
}
.w-button {
  display: inline-block;
  padding: 9px 15px;
  background-color: #3898EC;
  color: white;
  border: 0;
  line-height: inherit;
  text-decoration: none;
  cursor: pointer;
  border-radius: 0;
}
input.w-button {
  -webkit-appearance: button;
}
html[data-w-dynpage] [data-w-cloak] {
  color: transparent !important;
}
.w-webflow-badge,
.w-webflow-badge * {
  position: static;
  left: auto;
  top: auto;
  right: auto;
  bottom: auto;
  z-index: auto;
  display: block;
  visibility: visible;
  overflow: visible;
  overflow-x: visible;
  overflow-y: visible;
  box-sizing: border-box;
  width: auto;
  height: auto;
  max-height: none;
  max-width: none;
  min-height: 0;
  min-width: 0;
  margin: 0;
  padding: 0;
  float: none;
  clear: none;
  border: 0 none transparent;
  border-radius: 0;
  background: none;
  background-image: none;
  background-position: 0% 0%;
  background-size: auto auto;
  background-repeat: repeat;
  background-origin: padding-box;
  background-clip: border-box;
  background-attachment: scroll;
  background-color: transparent;
  box-shadow: none;
  opacity: 1.0;
  transform: none;
  transition: none;
  direction: ltr;
  font-family: inherit;
  font-weight: inherit;
  color: inherit;
  font-size: inherit;
  line-height: inherit;
  font-style: inherit;
  font-variant: inherit;
  text-align: inherit;
  letter-spacing: inherit;
  text-decoration: inherit;
  text-indent: 0;
  text-transform: inherit;
  list-style-type: disc;
  text-shadow: none;
  font-smoothing: auto;
  vertical-align: baseline;
  cursor: inherit;
  white-space: inherit;
  word-break: normal;
  word-spacing: normal;
  word-wrap: normal;
}
.w-webflow-badge {
  position: fixed !important;
  display: inline-block !important;
  visibility: visible !important;
  opacity: 1 !important;
  z-index: 2147483647 !important;
  top: auto !important;
  right: 12px !important;
  bottom: 12px !important;
  left: auto !important;
  color: #AAADB0 !important;
  background-color: #fff !important;
  border-radius: 3px !important;
  padding: 6px 8px 6px 6px !important;
  font-size: 12px !important;
  opacity: 1.0 !important;
  line-height: 14px !important;
  text-decoration: none !important;
  transform: none !important;
  margin: 0 !important;
  width: auto !important;
  height: auto !important;
  overflow: visible !important;
  white-space: nowrap;
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1), 0px 1px 3px rgba(0, 0, 0, 0.1);
  cursor: pointer;
}
.w-webflow-badge > img {
  display: inline-block !important;
  visibility: visible !important;
  opacity: 1 !important;
  vertical-align: middle !important;
}
h1,
h2,
h3,
h4,
h5,
h6 {
  font-weight: bold;
  margin-bottom: 10px;
}
h1 {
  font-size: 38px;
  line-height: 44px;
  margin-top: 20px;
}
h2 {
  font-size: 32px;
  line-height: 36px;
  margin-top: 20px;
}
h3 {
  font-size: 24px;
  line-height: 30px;
  margin-top: 20px;
}
h4 {
  font-size: 18px;
  line-height: 24px;
  margin-top: 10px;
}
h5 {
  font-size: 14px;
  line-height: 20px;
  margin-top: 10px;
}
h6 {
  font-size: 12px;
  line-height: 18px;
  margin-top: 10px;
}
p {
  margin-top: 0;
  margin-bottom: 10px;
}
blockquote {
  margin: 0 0 10px 0;
  padding: 10px 20px;
  border-left: 5px solid #E2E2E2;
  font-size: 18px;
  line-height: 22px;
}
figure {
  margin: 0;
  margin-bottom: 10px;
}
figcaption {
  margin-top: 5px;
  text-align: center;
}
ul,
ol {
  margin-top: 0px;
  margin-bottom: 10px;
  padding-left: 40px;
}
.w-list-unstyled {
  padding-left: 0;
  list-style: none;
}
.w-embed:before,
.w-embed:after {
  content: " ";
  display: table;
  grid-column-start: 1;
  grid-row-start: 1;
  grid-column-end: 2;
  grid-row-end: 2;
}
.w-embed:after {
  clear: both;
}
.w-video {
  width: 100%;
  position: relative;
  padding: 0;
}
.w-video iframe,
.w-video object,
.w-video embed {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
fieldset {
  padding: 0;
  margin: 0;
  border: 0;
}
button,
html input[type="button"],
input[type="reset"] {
  border: 0;
  cursor: pointer;
  -webkit-appearance: button;
}
.w-form {
  margin: 0 0 15px;
}
.w-form-done {
  display: none;
  padding: 20px;
  text-align: center;
  background-color: #dddddd;
}
.w-form-fail {
  display: none;
  margin-top: 10px;
  padding: 10px;
  background-color: #ffdede;
}
label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}
.w-input,
.w-select {
  display: block;
  width: 100%;
  height: 38px;
  padding: 8px 12px;
  margin-bottom: 10px;
  font-size: 14px;
  line-height: 1.428571429;
  color: #333333;
  vertical-align: middle;
  background-color: #ffffff;
  border: 1px solid #cccccc;
}
.w-input:-moz-placeholder,
.w-select:-moz-placeholder {
  color: #999;
}
.w-input::-moz-placeholder,
.w-select::-moz-placeholder {
  color: #999;
  opacity: 1;
}
.w-input:-ms-input-placeholder,
.w-select:-ms-input-placeholder {
  color: #999;
}
.w-input::-webkit-input-placeholder,
.w-select::-webkit-input-placeholder {
  color: #999;
}
.w-input:focus,
.w-select:focus {
  border-color: #3898EC;
  outline: 0;
}
.w-input[disabled],
.w-select[disabled],
.w-input[readonly],
.w-select[readonly],
fieldset[disabled] .w-input,
fieldset[disabled] .w-select {
  cursor: not-allowed;
  background-color: #eeeeee;
}
textarea.w-input,
textarea.w-select {
  height: auto;
}
.w-select {
  background-color: #f3f3f3;
}
.w-select[multiple] {
  height: auto;
}
.w-form-label {
  display: inline-block;
  cursor: pointer;
  font-weight: normal;
  margin-bottom: 0px;
}
.w-radio {
  display: block;
  margin-bottom: 5px;
  padding-left: 20px;
}
.w-radio:before,
.w-radio:after {
  content: " ";
  display: table;
  grid-column-start: 1;
  grid-row-start: 1;
  grid-column-end: 2;
  grid-row-end: 2;
}
.w-radio:after {
  clear: both;
}
.w-radio-input {
  margin: 4px 0 0;
  margin-top: 1px \9;
  line-height: normal;
  float: left;
  margin-left: -20px;
}
.w-radio-input {
  margin-top: 3px;
}
.w-file-upload {
  display: block;
  margin-bottom: 10px;
}
.w-file-upload-input {
  width: 0.1px;
  height: 0.1px;
  opacity: 0;
  overflow: hidden;
  position: absolute;
  z-index: -100;
}
.w-file-upload-default,
.w-file-upload-uploading,
.w-file-upload-success {
  display: inline-block;
  color: #333333;
}
.w-file-upload-error {
  display: block;
  margin-top: 10px;
}
.w-file-upload-default.w-hidden,
.w-file-upload-uploading.w-hidden,
.w-file-upload-error.w-hidden,
.w-file-upload-success.w-hidden {
  display: none;
}
.w-file-upload-uploading-btn {
  display: flex;
  font-size: 14px;
  font-weight: normal;
  cursor: pointer;
  margin: 0;
  padding: 8px 12px;
  border: 1px solid #cccccc;
  background-color: #fafafa;
}
.w-file-upload-file {
  display: flex;
  flex-grow: 1;
  justify-content: space-between;
  margin: 0;
  padding: 8px 9px 8px 11px;
  border: 1px solid #cccccc;
  background-color: #fafafa;
}
.w-file-upload-file-name {
  font-size: 14px;
  font-weight: normal;
  display: block;
}
.w-file-remove-link {
  margin-top: 3px;
  margin-left: 10px;
  width: auto;
  height: auto;
  padding: 3px;
  display: block;
  cursor: pointer;
}
.w-icon-file-upload-remove {
  margin: auto;
  font-size: 10px;
}
.w-file-upload-error-msg {
  display: inline-block;
  color: #ea384c;
  padding: 2px 0;
}
.w-file-upload-info {
  display: inline-block;
  line-height: 38px;
  padding: 0 12px;
}
.w-file-upload-label {
  display: inline-block;
  font-size: 14px;
  font-weight: normal;
  cursor: pointer;
  margin: 0;
  padding: 8px 12px;
  border: 1px solid #cccccc;
  background-color: #fafafa;
}
.w-icon-file-upload-icon,
.w-icon-file-upload-uploading {
  display: inline-block;
  margin-right: 8px;
  width: 20px;
}
.w-icon-file-upload-uploading {
  height: 20px;
}
.w-container {
  margin-left: auto;
  margin-right: auto;
  max-width: 940px;
}
.w-container:before,
.w-container:after {
  content: " ";
  display: table;
  grid-column-start: 1;
  grid-row-start: 1;
  grid-column-end: 2;
  grid-row-end: 2;
}
.w-container:after {
  clear: both;
}
.w-container .w-row {
  margin-left: -10px;
  margin-right: -10px;
}
.w-row:before,
.w-row:after {
  content: " ";
  display: table;
  grid-column-start: 1;
  grid-row-start: 1;
  grid-column-end: 2;
  grid-row-end: 2;
}
.w-row:after {
  clear: both;
}
.w-row .w-row {
  margin-left: 0;
  margin-right: 0;
}
.w-col {
  position: relative;
  float: left;
  width: 100%;
  min-height: 1px;
  padding-left: 10px;
  padding-right: 10px;
}
.w-col .w-col {
  padding-left: 0;
  padding-right: 0;
}
.w-col-1 {
  width: 8.33333333%;
}
.w-col-2 {
  width: 16.66666667%;
}
.w-col-3 {
  width: 25%;
}
.w-col-4 {
  width: 33.33333333%;
}
.w-col-5 {
  width: 41.66666667%;
}
.w-col-6 {
  width: 50%;
}
.w-col-7 {
  width: 58.33333333%;
}
.w-col-8 {
  width: 66.66666667%;
}
.w-col-9 {
  width: 75%;
}
.w-col-10 {
  width: 83.33333333%;
}
.w-col-11 {
  width: 91.66666667%;
}
.w-col-12 {
  width: 100%;
}
.w-hidden-main {
  display: none !important;
}
@media screen and (max-width: 991px) {
  .w-container {
    max-width: 728px;
  }
  .w-hidden-main {
    display: inherit !important;
  }
  .w-hidden-medium {
    display: none !important;
  }
  .w-col-medium-1 {
    width: 8.33333333%;
  }
  .w-col-medium-2 {
    width: 16.66666667%;
  }
  .w-col-medium-3 {
    width: 25%;
  }
  .w-col-medium-4 {
    width: 33.33333333%;
  }
  .w-col-medium-5 {
    width: 41.66666667%;
  }
  .w-col-medium-6 {
    width: 50%;
  }
  .w-col-medium-7 {
    width: 58.33333333%;
  }
  .w-col-medium-8 {
    width: 66.66666667%;
  }
  .w-col-medium-9 {
    width: 75%;
  }
  .w-col-medium-10 {
    width: 83.33333333%;
  }
  .w-col-medium-11 {
    width: 91.66666667%;
  }
  .w-col-medium-12 {
    width: 100%;
  }
  .w-col-stack {
    width: 100%;
    left: auto;
    right: auto;
  }
}
@media screen and (max-width: 767px) {

  .em-paragrapheprojet-explain {
    width: 80% !important;
  }

  .w-hidden-main {
    display: inherit !important;
  }
  .w-hidden-medium {
    display: inherit !important;
  }
  .w-hidden-small {
    display: none !important;
  }
  .w-row,
  .w-container .w-row {
    margin-left: 0;
    margin-right: 0;
  }
  .w-col {
    width: 100%;
    left: auto;
    right: auto;
  }
  .w-col-small-1 {
    width: 8.33333333%;
  }
  .w-col-small-2 {
    width: 16.66666667%;
  }
  .w-col-small-3 {
    width: 25%;
  }
  .w-col-small-4 {
    width: 33.33333333%;
  }
  .w-col-small-5 {
    width: 41.66666667%;
  }
  .w-col-small-6 {
    width: 50%;
  }
  .w-col-small-7 {
    width: 58.33333333%;
  }
  .w-col-small-8 {
    width: 66.66666667%;
  }
  .w-col-small-9 {
    width: 75%;
  }
  .w-col-small-10 {
    width: 83.33333333%;
  }
  .w-col-small-11 {
    width: 91.66666667%;
  }
  .w-col-small-12 {
    width: 100%;
  }

  .em-containerimage {

    width: 100% !important;
  
  }
  
  .em-containerimage2 {
    width: 100% !important;
  }
  
  .em-containerimage3 {
    width: 100% !important;
  }

  .em-containerimage-bloque {

    width: 100% !important;
  
  }
  
  .em-containerimage2-bloque {
    width: 100% !important;
  }
  
  .em-containerimage3-bloque {
    width: 100% !important;
  }



}
@media screen and (max-width: 479px) {
  .w-container {
    max-width: none;
  }

  .em-menufooter {
    width: 100%; 
 }

  .w-hidden-main {
    display: inherit !important;
  }
  .w-hidden-medium {
    display: inherit !important;
  }
  .w-hidden-small {
    display: inherit !important;
  }
  .w-hidden-tiny {
    display: none !important;
  }
  .w-col {
    width: 100%;
  }
  .w-col-tiny-1 {
    width: 8.33333333%;
  }
  .w-col-tiny-2 {
    width: 16.66666667%;
  }
  .w-col-tiny-3 {
    width: 25%;
  }
  .w-col-tiny-4 {
    width: 33.33333333%;
  }
  .w-col-tiny-5 {
    width: 41.66666667%;
  }
  .w-col-tiny-6 {
    width: 50%;
  }
  .w-col-tiny-7 {
    width: 58.33333333%;
  }
  .w-col-tiny-8 {
    width: 66.66666667%;
  }
  .w-col-tiny-9 {
    width: 75%;
  }
  .w-col-tiny-10 {
    width: 83.33333333%;
  }
  .w-col-tiny-11 {
    width: 91.66666667%;
  }
  .w-col-tiny-12 {
    width: 100%;
  }
}
.w-widget {
  position: relative;
}
.w-widget-map {
  width: 100%;
  height: 400px;
}
.w-widget-map label {
  width: auto;
  display: inline;
}
.w-widget-map img {
  max-width: inherit;
}
.w-widget-map .gm-style-iw {
  text-align: center;
}
.w-widget-map .gm-style-iw > button {
  display: none !important;
}
.w-widget-twitter {
  overflow: hidden;
}
.w-widget-twitter-count-shim {
  display: inline-block;
  vertical-align: top;
  position: relative;
  width: 28px;
  height: 20px;
  text-align: center;
  background: white;
  border: #758696 solid 1px;
  border-radius: 3px;
}
.w-widget-twitter-count-shim * {
  pointer-events: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
.w-widget-twitter-count-shim .w-widget-twitter-count-inner {
  position: relative;
  font-size: 15px;
  line-height: 12px;
  text-align: center;
  color: #999;
  font-family: serif;
}
.w-widget-twitter-count-shim .w-widget-twitter-count-clear {
  position: relative;
  display: block;
}
.w-widget-twitter-count-shim.w--large {
  width: 36px;
  height: 28px;
  margin-left: 7px;
}
.w-widget-twitter-count-shim.w--large .w-widget-twitter-count-inner {
  font-size: 18px;
  line-height: 18px;
}
.w-widget-twitter-count-shim:not(.w--vertical) {
  margin-left: 5px;
  margin-right: 8px;
}
.w-widget-twitter-count-shim:not(.w--vertical).w--large {
  margin-left: 6px;
}
.w-widget-twitter-count-shim:not(.w--vertical):before,
.w-widget-twitter-count-shim:not(.w--vertical):after {
  top: 50%;
  left: 0;
  border: solid transparent;
  content: " ";
  height: 0;
  width: 0;
  position: absolute;
  pointer-events: none;
}
.w-widget-twitter-count-shim:not(.w--vertical):before {
  border-color: rgba(117, 134, 150, 0);
  border-right-color: #5d6c7b;
  border-width: 4px;
  margin-left: -9px;
  margin-top: -4px;
}
.w-widget-twitter-count-shim:not(.w--vertical).w--large:before {
  border-width: 5px;
  margin-left: -10px;
  margin-top: -5px;
}
.w-widget-twitter-count-shim:not(.w--vertical):after {
  border-color: rgba(255, 255, 255, 0);
  border-right-color: white;
  border-width: 4px;
  margin-left: -8px;
  margin-top: -4px;
}
.w-widget-twitter-count-shim:not(.w--vertical).w--large:after {
  border-width: 5px;
  margin-left: -9px;
  margin-top: -5px;
}
.w-widget-twitter-count-shim.w--vertical {
  width: 61px;
  height: 33px;
  margin-bottom: 8px;
}
.w-widget-twitter-count-shim.w--vertical:before,
.w-widget-twitter-count-shim.w--vertical:after {
  top: 100%;
  left: 50%;
  border: solid transparent;
  content: " ";
  height: 0;
  width: 0;
  position: absolute;
  pointer-events: none;
}
.w-widget-twitter-count-shim.w--vertical:before {
  border-color: rgba(117, 134, 150, 0);
  border-top-color: #5d6c7b;
  border-width: 5px;
  margin-left: -5px;
}
.w-widget-twitter-count-shim.w--vertical:after {
  border-color: rgba(255, 255, 255, 0);
  border-top-color: white;
  border-width: 4px;
  margin-left: -4px;
}
.w-widget-twitter-count-shim.w--vertical .w-widget-twitter-count-inner {
  font-size: 18px;
  line-height: 22px;
}
.w-widget-twitter-count-shim.w--vertical.w--large {
  width: 76px;
}
.w-widget-gplus {
  overflow: hidden;
}
.w-background-video {
  position: relative;
  overflow: hidden;
  height: 500px;
  color: white;
}
.w-background-video > video {
  background-size: cover;
  background-position: 50% 50%;
  position: absolute;
  margin: auto;
  width: 100%;
  height: 100%;
  right: -100%;
  bottom: -100%;
  top: -100%;
  left: -100%;
  object-fit: cover;
  z-index: -100;
}
.w-background-video > video::-webkit-media-controls-start-playback-button {
  display: none !important;
  -webkit-appearance: none;
}
.w-slider {
  position: relative;
  height: 300px;
  text-align: center;
  background: #dddddd;
  clear: both;
  -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
  tap-highlight-color: rgba(0, 0, 0, 0);
}
.w-slider-mask {
  position: relative;
  display: block;
  overflow: hidden;
  z-index: 1;
  left: 0;
  right: 0;
  height: 100%;
  white-space: nowrap;
}
.w-slide {
  position: relative;
  display: inline-block;
  vertical-align: top;
  width: 100%;
  height: 100%;
  white-space: normal;
  text-align: left;
}
.w-slider-nav {
  position: absolute;
  z-index: 2;
  top: auto;
  right: 0;
  bottom: 0;
  left: 0;
  margin: auto;
  padding-top: 10px;
  height: 40px;
  text-align: center;
  -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
  tap-highlight-color: rgba(0, 0, 0, 0);
}
.w-slider-nav.w-round > div {
  border-radius: 100%;
}
.w-slider-nav.w-num > div {
  width: auto;
  height: auto;
  padding: 0.2em 0.5em;
  font-size: inherit;
  line-height: inherit;
}
.w-slider-nav.w-shadow > div {
  box-shadow: 0 0 3px rgba(51, 51, 51, 0.4);
}
.w-slider-nav-invert {
  color: #fff;
}
.w-slider-nav-invert > div {
  background-color: rgba(34, 34, 34, 0.4);
}
.w-slider-nav-invert > div.w-active {
  background-color: #222;
}
.w-slider-dot {
  position: relative;
  display: inline-block;
  width: 1em;
  height: 1em;
  background-color: rgba(255, 255, 255, 0.4);
  cursor: pointer;
  margin: 0 3px 0.5em;
  transition: background-color 100ms, color 100ms;
}
.w-slider-dot.w-active {
  background-color: #fff;
}
.w-slider-arrow-left,
.w-slider-arrow-right {
  position: absolute;
  width: 80px;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  margin: auto;
  cursor: pointer;
  overflow: hidden;
  color: white;
  font-size: 40px;
  -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
  tap-highlight-color: rgba(0, 0, 0, 0);
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
.w-slider-arrow-left [class^="w-icon-"],
.w-slider-arrow-right [class^="w-icon-"],
.w-slider-arrow-left [class*=" w-icon-"],
.w-slider-arrow-right [class*=" w-icon-"] {
  position: absolute;
}
.w-slider-arrow-left {
  z-index: 3;
  right: auto;
}
.w-slider-arrow-right {
  z-index: 4;
  left: auto;
}
.w-icon-slider-left,
.w-icon-slider-right {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  margin: auto;
  width: 1em;
  height: 1em;
}
.w-dropdown {
  display: inline-block;
  position: relative;
  text-align: left;
  margin-left: auto;
  margin-right: auto;
  z-index: 900;
}
.w-dropdown-btn,
.w-dropdown-toggle,
.w-dropdown-link {
  position: relative;
  vertical-align: top;
  text-decoration: none;
  color: #222222;
  padding: 20px;
  text-align: left;
  margin-left: auto;
  margin-right: auto;
  white-space: nowrap;
}
.w-dropdown-toggle {
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  display: inline-block;
  cursor: pointer;
  padding-right: 40px;
}
.w-icon-dropdown-toggle {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  margin: auto;
  margin-right: 20px;
  width: 1em;
  height: 1em;
}
.w-dropdown-list {
  position: absolute;
  background: #dddddd;
  display: none;
  min-width: 100%;
}
.w-dropdown-list.w--open {
  display: block;
}
.w-dropdown-link {
  padding: 10px 20px;
  display: block;
  color: #222222;
}
.w-dropdown-link.w--current {
  color: #0082f3;
}
@media screen and (max-width: 767px) {
  .w-nav-brand {
    padding-left: 10px;
  }
}
/**
 * ## Note
 * Safari (on both iOS and OS X) does not handle viewport units (vh, vw) well.
 * For example percentage units do not work on descendants of elements that
 * have any dimensions expressed in viewport units. It also doesn’t handle them at
 * all in `calc()`.
 */
/**
 * Wrapper around all lightbox elements
 *
 * 1. Since the lightbox can receive focus, IE also gives it an outline.
 * 2. Fixes flickering on Chrome when a transition is in progress
 *    underneath the lightbox.
 */
.w-lightbox-backdrop {
  color: #000;
  cursor: auto;
  font-family: serif;
  font-size: medium;
  font-style: normal;
  font-variant: normal;
  font-weight: normal;
  letter-spacing: normal;
  line-height: normal;
  list-style: disc;
  text-align: start;
  text-indent: 0;
  text-shadow: none;
  text-transform: none;
  visibility: visible;
  white-space: normal;
  word-break: normal;
  word-spacing: normal;
  word-wrap: normal;
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  color: #fff;
  font-family: "Helvetica Neue", Helvetica, Ubuntu, "Segoe UI", Verdana, sans-serif;
  font-size: 17px;
  line-height: 1.2;
  font-weight: 300;
  text-align: center;
  background: rgba(0, 0, 0, 0.9);
  z-index: 2000;
  outline: 0;
  /* 1 */
  opacity: 0;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  -webkit-tap-highlight-color: transparent;
  -webkit-transform: translate(0, 0);
  /* 2 */
}
/**
 * Neat trick to bind the rubberband effect to our canvas instead of the whole
 * document on iOS. It also prevents a bug that causes the document underneath to scroll.
 */
.w-lightbox-backdrop,
.w-lightbox-container {
  height: 100%;
  overflow: auto;
  -webkit-overflow-scrolling: touch;
}
.w-lightbox-content {
  position: relative;
  height: 100vh;
  overflow: hidden;
}
.w-lightbox-view {
  position: absolute;
  width: 100vw;
  height: 100vh;
  opacity: 0;
}
.w-lightbox-view:before {
  content: "";
  height: 100vh;
}
/* .w-lightbox-content */
.w-lightbox-group,
.w-lightbox-group .w-lightbox-view,
.w-lightbox-group .w-lightbox-view:before {
  height: 86vh;
}
.w-lightbox-frame,
.w-lightbox-view:before {
  display: inline-block;
  vertical-align: middle;
}
/*
 * 1. Remove default margin set by user-agent on the <figure> element.
 */
.w-lightbox-figure {
  position: relative;
  margin: 0;
  /* 1 */
}
.w-lightbox-group .w-lightbox-figure {
  cursor: pointer;
}
/**
 * IE adds image dimensions as width and height attributes on the IMG tag,
 * but we need both width and height to be set to auto to enable scaling.
 */
.w-lightbox-img {
  width: auto;
  height: auto;
  max-width: none;
}
/**
 * 1. Reset if style is set by user on "All Images"
 */
.w-lightbox-image {
  display: block;
  float: none;
  /* 1 */
  max-width: 100vw;
  max-height: 100vh;
}
.w-lightbox-group .w-lightbox-image {
  max-height: 86vh;
}
.w-lightbox-caption {
  position: absolute;
  right: 0;
  bottom: 0;
  left: 0;
  padding: .5em 1em;
  background: rgba(0, 0, 0, 0.4);
  text-align: left;
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;
}
.w-lightbox-embed {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
.w-lightbox-control {
  position: absolute;
  top: 0;
  width: 4em;
  background-size: 24px;
  background-repeat: no-repeat;
  background-position: center;
  cursor: pointer;
  -webkit-transition: all .3s;
  transition: all .3s;
}
.w-lightbox-left {
  display: none;
  bottom: 0;
  left: 0;
  /* <svg xmlns="http://www.w3.org/2000/svg" viewBox="-20 0 24 40" width="24" height="40"><g transform="rotate(45)"><path d="m0 0h5v23h23v5h-28z" opacity=".4"/><path d="m1 1h3v23h23v3h-26z" fill="#fff"/></g></svg> */
  background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9Ii0yMCAwIDI0IDQwIiB3aWR0aD0iMjQiIGhlaWdodD0iNDAiPjxnIHRyYW5zZm9ybT0icm90YXRlKDQ1KSI+PHBhdGggZD0ibTAgMGg1djIzaDIzdjVoLTI4eiIgb3BhY2l0eT0iLjQiLz48cGF0aCBkPSJtMSAxaDN2MjNoMjN2M2gtMjZ6IiBmaWxsPSIjZmZmIi8+PC9nPjwvc3ZnPg==");
}
.w-lightbox-right {
  display: none;
  right: 0;
  bottom: 0;
  /* <svg xmlns="http://www.w3.org/2000/svg" viewBox="-4 0 24 40" width="24" height="40"><g transform="rotate(45)"><path d="m0-0h28v28h-5v-23h-23z" opacity=".4"/><path d="m1 1h26v26h-3v-23h-23z" fill="#fff"/></g></svg> */
  background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9Ii00IDAgMjQgNDAiIHdpZHRoPSIyNCIgaGVpZ2h0PSI0MCI+PGcgdHJhbnNmb3JtPSJyb3RhdGUoNDUpIj48cGF0aCBkPSJtMC0waDI4djI4aC01di0yM2gtMjN6IiBvcGFjaXR5PSIuNCIvPjxwYXRoIGQ9Im0xIDFoMjZ2MjZoLTN2LTIzaC0yM3oiIGZpbGw9IiNmZmYiLz48L2c+PC9zdmc+");
}
/*
 * Without specifying the with and height inside the SVG, all versions of IE render the icon too small.
 * The bug does not seem to manifest itself if the elements are tall enough such as the above arrows.
 * (http://stackoverflow.com/questions/16092114/background-size-differs-in-internet-explorer)
 */
.w-lightbox-close {
  right: 0;
  height: 2.6em;
  /* <svg xmlns="http://www.w3.org/2000/svg" viewBox="-4 0 18 17" width="18" height="17"><g transform="rotate(45)"><path d="m0 0h7v-7h5v7h7v5h-7v7h-5v-7h-7z" opacity=".4"/><path d="m1 1h7v-7h3v7h7v3h-7v7h-3v-7h-7z" fill="#fff"/></g></svg> */
  background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9Ii00IDAgMTggMTciIHdpZHRoPSIxOCIgaGVpZ2h0PSIxNyI+PGcgdHJhbnNmb3JtPSJyb3RhdGUoNDUpIj48cGF0aCBkPSJtMCAwaDd2LTdoNXY3aDd2NWgtN3Y3aC01di03aC03eiIgb3BhY2l0eT0iLjQiLz48cGF0aCBkPSJtMSAxaDd2LTdoM3Y3aDd2M2gtN3Y3aC0zdi03aC03eiIgZmlsbD0iI2ZmZiIvPjwvZz48L3N2Zz4=");
  background-size: 18px;
}
/**
 * 1. All IE versions add extra space at the bottom without this.
 */
.w-lightbox-strip {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 0 1vh;
  line-height: 0;
  /* 1 */
  white-space: nowrap;
  overflow-x: auto;
  overflow-y: hidden;
}
/*
 * 1. We use content-box to avoid having to do `width: calc(10vh + 2vw)`
 *    which doesn’t work in Safari anyway.
 * 2. Chrome renders images pixelated when switching to GPU. Making sure
 *    the parent is also rendered on the GPU (by setting translate3d for
 *    example) fixes this behavior.
 */
.w-lightbox-item {
  display: inline-block;
  width: 10vh;
  padding: 2vh 1vh;
  box-sizing: content-box;
  /* 1 */
  cursor: pointer;
  -webkit-transform: translate3d(0, 0, 0);
  /* 2 */
}
.w-lightbox-active {
  opacity: .3;
}
.w-lightbox-thumbnail {
  position: relative;
  height: 10vh;
  background: #222;
  overflow: hidden;
}
.w-lightbox-thumbnail-image {
  position: absolute;
  top: 0;
  left: 0;
}
.w-lightbox-thumbnail .w-lightbox-tall {
  top: 50%;
  width: 100%;
  -webkit-transform: translate(0, -50%);
  -ms-transform: translate(0, -50%);
  transform: translate(0, -50%);
}
.w-lightbox-thumbnail .w-lightbox-wide {
  left: 50%;
  height: 100%;
  -webkit-transform: translate(-50%, 0);
  -ms-transform: translate(-50%, 0);
  transform: translate(-50%, 0);
}
/*
 * Spinner
 *
 * Absolute pixel values are used to avoid rounding errors that would cause
 * the white spinning element to be misaligned with the track.
 */
.w-lightbox-spinner {
  position: absolute;
  top: 50%;
  left: 50%;
  box-sizing: border-box;
  width: 40px;
  height: 40px;
  margin-top: -20px;
  margin-left: -20px;
  border: 5px solid rgba(0, 0, 0, 0.4);
  border-radius: 50%;
  -webkit-animation: spin .8s infinite linear;
  animation: spin .8s infinite linear;
}
.w-lightbox-spinner:after {
  content: "";
  position: absolute;
  top: -4px;
  right: -4px;
  bottom: -4px;
  left: -4px;
  border: 3px solid transparent;
  border-bottom-color: #fff;
  border-radius: 50%;
}
/*
 * Utility classes
 */
.w-lightbox-hide {
  display: none;
}
.w-lightbox-noscroll {
  overflow: hidden;
}
@media (min-width: 768px) {
  .w-lightbox-content {
    height: 96vh;
    margin-top: 2vh;
  }
  .w-lightbox-view,
  .w-lightbox-view:before {
    height: 96vh;
  }
  /* .w-lightbox-content */
  .w-lightbox-group,
  .w-lightbox-group .w-lightbox-view,
  .w-lightbox-group .w-lightbox-view:before {
    height: 84vh;
  }
  .w-lightbox-image {
    max-width: 96vw;
    max-height: 96vh;
  }
  .w-lightbox-group .w-lightbox-image {
    max-width: 82.3vw;
    max-height: 84vh;
  }
  .w-lightbox-left,
  .w-lightbox-right {
    display: block;
    opacity: .5;
  }
  .w-lightbox-close {
    opacity: .8;
  }
  .w-lightbox-control:hover {
    opacity: 1;
  }
}
.w-lightbox-inactive,
.w-lightbox-inactive:hover {
  opacity: 0;
}
.w-richtext:before,
.w-richtext:after {
  content: " ";
  display: table;
  grid-column-start: 1;
  grid-row-start: 1;
  grid-column-end: 2;
  grid-row-end: 2;
}
.w-richtext:after {
  clear: both;
}
.w-richtext[contenteditable="true"]:before,
.w-richtext[contenteditable="true"]:after {
  white-space: initial;
}
.w-richtext ol,
.w-richtext ul {
  overflow: hidden;
}
.w-richtext .w-richtext-figure-selected.w-richtext-figure-type-video div:after,
.w-richtext .w-richtext-figure-selected[data-rt-type="video"] div:after {
  outline: 2px solid #2895f7;
}
.w-richtext .w-richtext-figure-selected.w-richtext-figure-type-image div,
.w-richtext .w-richtext-figure-selected[data-rt-type="image"] div {
  outline: 2px solid #2895f7;
}
.w-richtext figure.w-richtext-figure-type-video > div:after,
.w-richtext figure[data-rt-type="video"] > div:after {
  content: '';
  position: absolute;
  display: none;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
}
.w-richtext figure {
  position: relative;
  max-width: 60%;
}
.w-richtext figure > div:before {
  cursor: default!important;
}
.w-richtext figure img {
  width: 100%;
}
.w-richtext figure figcaption.w-richtext-figcaption-placeholder {
  opacity: 0.6;
}
.w-richtext figure div {
  /* fix incorrectly sized selection border in the data manager */
  font-size: 0px;
  color: transparent;
}
.w-richtext figure.w-richtext-figure-type-image,
.w-richtext figure[data-rt-type="image"] {
  display: table;
}
.w-richtext figure.w-richtext-figure-type-image > div,
.w-richtext figure[data-rt-type="image"] > div {
  display: inline-block;
}
.w-richtext figure.w-richtext-figure-type-image > figcaption,
.w-richtext figure[data-rt-type="image"] > figcaption {
  display: table-caption;
  caption-side: bottom;
}
.w-richtext figure.w-richtext-figure-type-video,
.w-richtext figure[data-rt-type="video"] {
  width: 60%;
  height: 0;
}
.w-richtext figure.w-richtext-figure-type-video iframe,
.w-richtext figure[data-rt-type="video"] iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
.w-richtext figure.w-richtext-figure-type-video > div,
.w-richtext figure[data-rt-type="video"] > div {
  width: 100%;
}
.w-richtext figure.w-richtext-align-center {
  margin-right: auto;
  margin-left: auto;
  clear: both;
}
.w-richtext figure.w-richtext-align-center.w-richtext-figure-type-image > div,
.w-richtext figure.w-richtext-align-center[data-rt-type="image"] > div {
  max-width: 100%;
}
.w-richtext figure.w-richtext-align-normal {
  clear: both;
}
.w-richtext figure.w-richtext-align-fullwidth {
  width: 100%;
  max-width: 100%;
  text-align: center;
  clear: both;
  display: block;
  margin-right: auto;
  margin-left: auto;
}
.w-richtext figure.w-richtext-align-fullwidth > div {
  display: inline-block;
  /* padding-bottom is used for aspect ratios in video figures
      we want the div to inherit that so hover/selection borders in the designer-canvas
      fit right*/
  padding-bottom: inherit;
}
.w-richtext figure.w-richtext-align-fullwidth > figcaption {
  display: block;
}
.w-richtext figure.w-richtext-align-floatleft {
  float: left;
  margin-right: 15px;
  clear: none;
}
.w-richtext figure.w-richtext-align-floatright {
  float: right;
  margin-left: 15px;
  clear: none;
}
.w-nav {
  position: relative;
  background: #dddddd;
  z-index: 1000;
}
.w-nav:before,
.w-nav:after {
  content: " ";
  display: table;
  grid-column-start: 1;
  grid-row-start: 1;
  grid-column-end: 2;
  grid-row-end: 2;
}
.w-nav:after {
  clear: both;
}
.w-nav-brand {
  position: relative;
  float: left;
  text-decoration: none;
  color: #333333;
}
.w-nav-link {
  position: relative;
  display: inline-block;
  vertical-align: top;
  text-decoration: none;
  color: #222222;
  padding: 20px;
  text-align: left;
  margin-left: auto;
  margin-right: auto;
}
.w-nav-link.w--current {
  color: #0082f3;
}
.w-nav-menu {
  position: relative;
  float: right;
}
.w--nav-menu-open {
  display: block !important;
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #C8C8C8;
  text-align: center;
  overflow: visible;
  min-width: 200px;
}
.w--nav-link-open {
  display: block;
  position: relative;
}
.w-nav-overlay {
  position: absolute;
  overflow: hidden;
  display: none;
  top: 100%;
  left: 0;
  right: 0;
  width: 100%;
}
.w-nav-overlay .w--nav-menu-open {
  top: 0;
}
.w-nav[data-animation="over-left"] .w-nav-overlay {
  width: auto;
}
.w-nav[data-animation="over-left"] .w-nav-overlay,
.w-nav[data-animation="over-left"] .w--nav-menu-open {
  right: auto;
  z-index: 1;
  top: 0;
}
.w-nav[data-animation="over-right"] .w-nav-overlay {
  width: auto;
}
.w-nav[data-animation="over-right"] .w-nav-overlay,
.w-nav[data-animation="over-right"] .w--nav-menu-open {
  left: auto;
  z-index: 1;
  top: 0;
}
.w-nav-button {
  position: relative;
  float: right;
  padding: 18px;
  font-size: 24px;
  display: none;
  cursor: pointer;
  -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
  tap-highlight-color: rgba(0, 0, 0, 0);
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
.w-nav-button.w--open {
  background-color: #C8C8C8;
  color: white;
}
.w-nav[data-collapse="all"] .w-nav-menu {
  display: none;
}
.w-nav[data-collapse="all"] .w-nav-button {
  display: block;
}
.w--nav-dropdown-open {
  display: block;
}
.w--nav-dropdown-toggle-open {
  display: block;
}
.w--nav-dropdown-list-open {
  position: static;
}
@media screen and (max-width: 991px) {
  .w-nav[data-collapse="medium"] .w-nav-menu {
    display: none;
  }
  .w-nav[data-collapse="medium"] .w-nav-button {
    display: block;
  }
}
@media screen and (max-width: 767px) {
  .w-nav[data-collapse="small"] .w-nav-menu {
    display: none;
  }
  .w-nav[data-collapse="small"] .w-nav-button {
    display: block;
  }
  .w-nav-brand {
    padding-left: 10px;
  }
}
@media screen and (max-width: 479px) {
  .w-nav[data-collapse="tiny"] .w-nav-menu {
    display: none;
  }
  .w-nav[data-collapse="tiny"] .w-nav-button {
    display: block;
  }
}
.w-tabs {
  position: relative;
}
.w-tabs:before,
.w-tabs:after {
  content: " ";
  display: table;
  grid-column-start: 1;
  grid-row-start: 1;
  grid-column-end: 2;
  grid-row-end: 2;
}
.w-tabs:after {
  clear: both;
}
.w-tab-menu {
  position: relative;
}
.w-tab-link {
  position: relative;
  display: inline-block;
  vertical-align: top;
  text-decoration: none;
  padding: 9px 30px;
  text-align: left;
  cursor: pointer;
  color: #222222;
  background-color: #dddddd;
}
.w-tab-link.w--current {
  background-color: #C8C8C8;
}
.w-tab-content {
  position: relative;
  display: block;
  overflow: hidden;
}
.w-tab-pane {
  position: relative;
  display: none;
}
.w--tab-active {
  display: block;
}
@media screen and (max-width: 479px) {
  .w-tab-link {
    display: block;
  }
}
.w-ix-emptyfix:after {
  content: "";
}
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
.w-dyn-empty {
  padding: 10px;
  background-color: #dddddd;
}
.w-dyn-hide {
  display: none !important;
}
.w-dyn-bind-empty {
  display: none !important;
}
.w-condition-invisible {
  display: none !important;
}

.filtertable td{
 background:transparent !important;
 border:none!important;
}
select.fabrik_filter {
-moz-appearance: none;
-webkit-appearance: none;
border-radius: 0;
margin: 0;
width: 50%!important;
font-family: Wigrum, sans-serif;
background:url("/projet/images/custom/arrow_down.svg") no-repeat scroll 97.5% center;
color: white;
margin-top: 25px;
padding-right: 47px;
background-color:#82358b;
color:#f5f5f5;
font-size: 18px;
line-height: 22px;
height: 62px!important;
background-size: 18px;
}
select.fabrik_filter option{
  display: block;
  list-style-type: none;
  padding: 10px 15px;
  background-color:#82358b;
  font-size: 16px;
  line-height: 1.4;
  cursor: pointer;
  color:#f5f5f5;
  -webkit-transition: all ease-in-out .3s;
  transition: all ease-in-out .3s;
  height:40px;
}
select.fabrik_filter option:hover{
background:#482683;
}
.fabrikFilterContainer .row-fluid{
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
.fabrik-filter-element{
display: flex;
flex-direction: column;
justify-content: center;
align-items: center;
}

.overlay {
  position: absolute;
  z-index: 100;
  width: 100%;
  height: 100%;
  background-color: 
  hsla(0, 0%, 96%, 0.8);
}


/*Share*/
.em-share{
    display:flex;
    flex-direction: row;
    justify-content:center;
    align-items:center;
}
.em-share a {
    margin-right:10px;
}
.em-share a i{
    font-size: 2rem;
}
/* Favoris */
.starActive{
color:#e39809;
}

/* END - Your CSS styling ends here */
EOT;
