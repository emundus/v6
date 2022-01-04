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
.section-normal {
  overflow: hidden;
  padding-top: 80px;
  padding-bottom: 80px;
}

.fabrik_row.oddRow1 td {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
}

.filtertable.table.table-striped {
  margin-top: 50px;
}

.fabrik_row.oddRow1  .em-filter-label, .fabrik_row.oddRow0  .em-filter-label {
  margin-right: 10px !important;
}

.w-container {
  margin-left: auto;
  margin-right: auto;
  max-width: 940px;
}
.h3-lascala {
  margin-top: 0px;
  font-family: 'Clarikaprogeo md', sans-serif;
  font-size: 27px;
  line-height: 28px;
  font-weight: 400;
  text-transform: uppercase;
}

.h3-lascala.center {
  text-align: center;
}

#testimonies .testimonies-blocs h4{
margin-top: 20px;
}
.testimonies-blocs  {
display: flex;
flex-direction: row;
justify-content: space-between;
padding: 20px;
border-radius: 6px;
background-color: #fff;
margin-top: 50px;
align-items: flex-start;
}
.testimonies-image {
flex: 0 0 20%;
}

.testimonies-text {
flex: 0 0 75%;
}
.testimonies-image img{
border-radius: 6px;
height: 14rem;
}

.testimonies-text p strong{
font-weight: bold;
font-family: 'Clarikaprogeo md', sans-serif;
}


.testimonies-text h5{
color: #A51E25;
font-weight: 300;
font-family: 'Clarikaprogeo md', sans-serif;
font-style: italic;
}


.testimonies-text h4 {
color: #161616;
font-weight: 700;
font-family: 'Clarikaprogeo md', sans-serif;
margin-top: 0px;
}

.testimonies-text p {
font-size: 14.5px;
}

.testimonies-title img {
width: 15px;
height: 15px;
margin-left: 20px;
}


.testimonies-title {
display: flex;
flex-direction: row;
justify-content: flex-start;
align-items: center;
}

.fabrikNav{
  display:flex;
  flex-direction:row;
  justify-content:center;
  margin-bottom: 100px;
}

ul.pagination-list {
  display: inline-block;
  padding: 0;
  margin: 0;
}

ul.pagination-list li {
  display: inline;
  
}

ul.pagination-list li a {
  color: black;
  float: left;
  padding: 8px 16px;
  text-decoration: none;
  margin: 5px;
  border-radius:5px;
}

ul.pagination-list li.active a{
  background-color: #a51e25;
  color: white;
}

ul.pagination-list .pagination-prev.active a,
ul.pagination-list .pagination-start.active a,
ul.pagination-list .pagination-next.active a,
ul.pagination-list .pagination-end.active a {
  background-color: white;
  color:#a51e25;
}
ul.pagination-list .pagination-prev:not(.active) a,
ul.pagination-list .pagination-start:not(.active) a,
ul.pagination-list .pagination-next:not(.active) a,
ul.pagination-list .pagination-end:not(.active) a{
  background-color: #a51e25;
  color: white;
}

ul.pagination-list li:not(.active) a{
  background-color: white;
  color:#a51e25;
}

@media screen and (max-width: 479px) {
      form.form-search .filtertable.table.table-striped tbody {
        flex-direction: column;
        align-items: flex-start;
    }
} 

/* END - Your CSS styling ends here */
EOT;
