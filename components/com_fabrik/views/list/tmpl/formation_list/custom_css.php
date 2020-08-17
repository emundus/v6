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

.em-formation #g-container-main .g-container{
padding:0!important;
width:100vw;
}
.em-formation .fabrikForm.form-search{
	background: #f5f5f5;
	margin:0!important;
	padding-bottom:100px;
}
.em-search{
	display: flex;
	flex-direction: column;
	background: #f5f5f5;
	min-height: 100vh;
	padding-bottom:100px;
}
.em-searchContainer{
	display: flex;
	flex-direction: row;
}
.view-list .em-filter{ 
	box-shadow: 3px 4px 5px 0px rgba(222, 222, 222, 1);
	width: 300px;
	height: 0;
	position: relative;
	background: white;
	left:20px;
	min-width: 300px;
	margin:0 45px;
}
.em-search .header{
	padding: 10px;
	background: #a10006;
	width:100%;
	margin-bottom: 30px;
	margin:0;
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	align-items: center;
}
.em-search .header h2{
	padding: 10px;
	color: white;
	margin:0;
	text-align:center;
}
.em-search .header img{
height: 30px;
}
.em-formation .em-select{
	background: white;
	padding: 20px;
	display: flex;
	flex-direction: column;
	justify-content: flex-start;
	align-items: flex-start;
	height:auto;
	box-shadow: 3px 4px 5px 0px rgba(222, 222, 222, 1);
}
.em-formation .em-select .em-filter-label{
	color: #a10006;
	margin-bottom:5px;
}

.select2-container{
margin-bottom: 30px !important;
}
.em-filter-label{
margin-bottom:10px!important;
}
.select2-selection__choice__remove{
color:white!important;
}
.em-select .select2 .select2-selection--multiple .select2-selection__choice{
	color: white;
padding: 5px;
}
.em-card{
margin:0 45px;
}
.em-result h4{
margin-left:33px;
}
.em-cardContainer{
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
}
.card{
	width: 400px;
	background: white;
	height: 450px;
	position: relative;
	display: flex;
	flex-direction: column;
	margin:0 22px 45px 22px;
}
.card:hover{
	box-shadow: 3px 4px 5px 0px rgba(222, 222, 222, 1);
}
.card .intitule{
font-weight: 700;
color: #002d72;
width: 150px;
display: inline-block;
}
.cardContainerHeader{
	background:#002d72;
	padding: 20px;
	height:100px;
	margin-bottom:15px;
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	align-items:center;
}
.cardContainerHeader h4{
color:white;
font-size:1.1rem;
margin-bottom:0;
width: 85%; 
}
.cardContainerHeader img{
height:30px;
}
.cardContainerContent{
padding: 20px;
}
.em-formation .btn-connexion{
	border-radius: 0 !important;
	padding: 8px 12px !important;
	text-transform: uppercase !important;
	font-weight: 700 !important;
	background: #a10006 !important;
	border: 1px solid #a10006 !important;
	text-shadow: none;
	color: white;
	position: absolute;
	bottom: -17px;
	align-self: center;
	width:80%;
}
.em-formation .btn-connexion:hover{
    background: #a10006 !important;
    color:white!important;
    
  }
  .em-formation .fabrikNav{
  	display:flex;
  	flex-direction:row;
  	justify-content:center;
  }
  .em-formation .fabrikNav .add-on{
  	height:35px;
  	background:#002d72;
  	color:white;
  	text-shadow:none;
  }
  .em-formation .fabrikNav .add-on label{
  	color:white!important;
  }
  .em-formation .fabrikNav .inputbox{
  	height:35px;
  }
  
@media screen and (max-width:768px){
    .em-searchContainer{
	flex-direction:column;
	justify-content:center;
	align-items:center;
    }
    .view-list .em-filter{
        width: 80%;
        height: 90%;
        left:0;
        margin-bottom:50px;
    }
    
}
@media screen and (max-width:480px){
.em-card{
width: 80%;
}
.card{
 margin:0 0 45px 0;
}

/* END - Your CSS styling ends here */
EOT;
