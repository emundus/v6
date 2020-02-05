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
#g-container-main .span12{
width:100%;
}
hr{
    color: rgba(181,181,181,1);
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
    flex-direction: row;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
}
.em-cardContainer-card{
    display: flex;
    flex-direction: column;
    margin: 10px;
}
.em-cardContainer-card .em-cardContainer-card-image{
    height: 50vh;
}

.em-cardContainer-card .em-cardContainer-card-image img{
    height: 100%;
    width: 100%;
    object-fit: cover;
    object-position: center;
}
.em-cardContainer-card-content{
    margin: 15px;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.em-cardContainer-card-content h1{
    font-size: 1.5rem;
    margin-bottom: 40px;
}
.em-cardContainer-card-galerie{
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}
.em-cardContainer-card-galerie img{
    width:33%;
    object-fit:contain;
}


.em-cardContainer-card-vote{
    display:flex;
    flex-direction:column;
    margin: 15px;
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
}
.em-cardContainer-card-vote-button a:hover, .em-cardContainer-card-vote-button a.active {
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
}
@media screen and (min-width:1270px){
    .em-cardContainer-card-content h1{
        font-size: 1.2rem;
    }
}
@media screen and (min-width:1700px){
    .em-cardContainer-card-content h1{
        font-size: 1.5rem;
    }
}
/* END - Your CSS styling ends here */
EOT;