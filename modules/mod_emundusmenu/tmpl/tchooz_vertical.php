<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
?>
<style type='text/css'>
    hr{
        z-index: 999;
    }
    .edge .g-active{
        position: absolute !important;
        left: -100% !important;
        z-index: 1 !important ;
        margin-left: -50px !important;
        top: -9px !important;
        /* right: 100%; */
        margin-top: 2px !important;
    }

    /*** Navbar ***/
    #header-a{
        position: relative;
        left: 5%;
        opacity: 0;
    }

    @media all and (max-width: 479px) {
        #header-a{
            opacity: 1;
        }
    }

    #header-b{
        width: auto;
        position: fixed;
        background: white;
        left: 0;
        top: 0;
        padding: 10px;
        height: 100%;
        align-items: baseline;
        overflow-y: auto;
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
    #header-b::-webkit-scrollbar {
        display: none;
    }
    #header-b #em_user_menu li.parent-active:hover a{
        color: black;
    }

    #header-b #em_user_menu li:hover a, #header-b #em_user_menu li:active a, #header-b #em_user_menu li:focus a{
        color: black;
    }
    .g-sublevel .g-menu-item-title span:hover,.g-sublevel .g-menu-item-title span:focus,.g-sublevel .g-menu-item-title span:active {
        color: black;
    }
    .g-sublevel-list{
        margin-top: 10px;
    }
    .g-sublevel-list::before{
        position: absolute;
        height: 50%;
        border: solid 3px #16AFE1;
        border-radius: 25px;
    }
    /*** END ***/

    /*** Sublevel parent ***/
    ul.tchooz-vertical-toplevel > li.active.tchooz-vertical-item > a.item::before{
        background: var(--em-coordinator-primary-color);
        width: 5px;
        height: 100%;
        content: "";
        position: absolute;
        left: -20px;
    }
    .active .item .image-title{
        color: var(--em-coordinator-primary-color);
    }
    .g-menu-parent-indicator{
        margin-left: 20px;
        align-self: center;
    }
    .parent-indicator-close::after{
        opacity: 1 !important;
        content: "\f054" !important;
    }
    /*** END ***/

    /*** List style ***/
    #g-navigation .g-main-nav .tchooz-vertical-toplevel > li{
        margin: 5px 10px !important;
        font-family: var(--em-default-font);
    }

    .g-menu-item.g-standard.tchooz-vertical-item.tchooz-vertical-logo.tchooz-vertical-item.tchooz-vertical-logo > a {
        margin-bottom: 16px;
    }
    .g-menu-item.g-standard.tchooz-vertical-item.tchooz-vertical-logo.tchooz-vertical-item.tchooz-vertical-logo img {
        width: 30px;
    }

    #g-navigation .g-main-nav .g-sublevel > li:not(:last-child) > .g-menu-item-container{
        border-bottom: unset !important;
    }

    .tchooz-vertical-item a{
        display: flex;
        align-items: center;
        white-space: nowrap;
        position: relative;
        z-index: 2;
        transition: all 0.3s ease-in-out;
    }
    .tchooz-vertical-item a img,
    .tchooz-vertical-item a span[class*="material-icons"] {
        width: 30px;
        height: 30px;
        padding: 3px !important;
    }

    .image-title{
        margin-left: 30px;
        transition: opacity 0.2s ease-in-out;
    }

    /*.g-sublevel-list{
        margin-left: 40px !important;
        margin-top: 10px !important;
        position: fixed;
        top: 90px;
        max-width: 200px;
    }*/
    /*** END ***/

    /*** Back button ***/
    .g-main-nav .g-standard .g-go-back{
        display: block;
        border-bottom: solid 1px #e0e0e5;
        margin-bottom: 15px !important;
    }
    .g-go-back a span{
        display: block;
        margin-left: 10px;
    }
    .burger-button{
        margin: 0 10px 10px 10px;
        background: transparent;
        padding: 0;width: 30px
    }
    /*** END ***/
    .message-tooltip{
        width: auto;
        height: auto;
        position: fixed;
        margin-left: 0;
        color: black;
        align-items: center;
        font-weight: 600;
        display: none;
        margin-top: -38px;
        z-index: 1;
    }
    .message-tooltip-block p {
        font-weight: 600;
        border-bottom: 1px solid #e0e0e5;
        padding-bottom: 10px;
        color: var(--neutral-900);
    }

    .message-tooltip-block{
        margin-left: 60px;
        box-shadow: 0 5px 10px rgb(0 0 0 / 10%);
        padding: 15px;
        border-radius: 5px;
        background: #fff;
    }
    .message-tooltip-block::after{
        content: "";
        position: absolute;
        height: 0;
        width: 0;
        right: 100%;
        top: 10px;
        border: 10px solid transparent;
        border-right-color: transparent;
        border-right-style: solid;
        border-right-width: 10px;
        border-right: 10px solid #fff;
    }

    .g-main-nav .g-standard .g-sublevel .g-menu-item a.g-menu-item-container:hover   {
        background: #EBECF0 !important;
        border-radius: var(--em-default-br) !important;
    }

    .g-sublevel{
        margin-top: 10px !important;
    }
    .g-sublevel .tchooz-vertical-item::before {
        height: 110%;
        position: absolute;
        display: block;
       border: solid 2px #fff;
        border-radius: 5px;
        content: "";
    }

    #g-container-main,#g-footer,#footer-rgpd {
        padding-left: 76px;
    }
</style>
<nav class="g-main-nav <?php echo $class_sfx;?>" data-g-hover-expand="true"
    <?php
    $tag = '';
    if ($params->get('tag_id')!=NULL) {
        $tag = $params->get('tag_id').'';
        echo ' id="'.$tag.'"';
    }
    ?>>
    <div style="opacity: 0" class="grey-navbar-icons"></div>
    <ul class="g-toplevel tchooz-vertical-toplevel">
        <button class="g-menu-item g-standard burger-button" onclick="enableTitles()"><img src="<?php echo JURI::base()?>images/emundus/menus/menu.png" style="width: 30px"></button>
        <?php

        $target_dir = "images/custom/";
        $filename = 'favicon';
        $favicon = glob("{$target_dir}{$filename}.*");

        if(file_exists(JPATH_SITE . '/' . $favicon[0])){
            $favicon = JURI::base().'/' . $favicon[0];
        } else {
            $favicon = JURI::base().'/images/emundus/tchooz_favicon.png';
        }
        echo '<li class="g-menu-item g-standard tchooz-vertical-item tchooz-vertical-logo" style="height: auto"><a class="item" href="'.$favicon_link.'"><img src="'.$favicon.'" alt="Accueil"></a>
        </li>';

        if ($display_tchooz) :
            foreach ($tchooz_list as $i => &$item) :
                $item->anchor_css="item";
                $class = 'item-'.$item->id.' g-standard';
                if ($item->id == $active_id) {
                    $class .= ' current';
                }

                if (in_array($item->id, $path)) {
                    $class .= ' active';
                }
                elseif ($item->type == 'alias') {
                    $aliasToId = $item->params->get('aliasoptions');
                    if (count($path) > 0 && $aliasToId == $path[count($path)-1]) {
                        $class .= ' active';
                    }
                    elseif (in_array($aliasToId, $path)) {
                        $class .= ' alias-parent-active';
                    }
                }

                if ($item->parent) {
                    $class .= ' g-parent';
                }

                if (!empty($class)) {
                    $class = ' class="tchooz-vertical-item g-menu-item g-menu-'.trim($class) .'"';
                }

                echo '<li' . $class . 'onmouseenter="enableTooltip(' . $item->id . ')" onmouseleave="disableTooltip(' . $item->id . ')">';

                // Render the menu item.
                switch ($item->type) :
                    case 'separator':
                    case 'url':
                    case 'component':
                        require JModuleHelper::getLayoutPath('mod_emundusmenu', 'tchooz_'.$item->type);
                        break;

                    default:
                        require JModuleHelper::getLayoutPath('mod_emundusmenu', 'tchooz_url');
                        break;
                endswitch;

                // The next item is deeper.
                if ($item->deeper) {
                    echo '<ul class="g-sublevel-list" id="sublevel_list_' . $item->id . '" style="display: none">';
                    echo '<li class="g-dropdown-column">';
                    echo '<div class="g-grid"><div class="g-block size-100"><ul class="g-sublevel"><li class="g-level-'.($item->level).' g-go-back"><a class="g-menu-item-container" href="#" onclick="backToParentMenu(' . $item->id . ')" data-g-menuparent=""><span class="g-menu-item-content"><span class="g-menu-item-title">' . JText::_("COM_EMUNDUS_BACK") . '</span></span></a></li>';
                }
                // The next item is shallower.
                elseif ($item->shallower) {
                    echo '<div class="message-tooltip" id="tooltip-'.$item->id.'"><div class="message-tooltip-block"> '.$item->title.'</div>';
                    echo '</li>';
                    echo str_repeat('</ul></div></div></li></ul>', $item->level_diff);
                }
                // The next item is on the same level.
                else {
                    echo '<div class="message-tooltip" id="tooltip-'.$item->id.'"><div class="message-tooltip-block"><a class class="'.$class.'" href="'. $item->flink . '"> '.$item->title.'</a></div></div>';
                    echo '</li>';
                }
            endforeach;

            if(sizeof($tchooz_list) > 0) :
                echo '<hr id="menu_separator" class="mb-4 mt-4">';
            endif;
        endif;

        foreach ($list as $i => &$item) :
            if($item->alias != 'homepage' && $item->params->get('menu_show') != 0) :
                $item->anchor_css="item";
                $class = 'item-'.$item->id.' g-standard';
                if ($item->id == $active_id) {
                    $class .= ' current';
                }

                if (in_array($item->id, $path)) {
                    $class .= ' active';
                }
                elseif ($item->type == 'alias') {
                    $aliasToId = $item->params->get('aliasoptions');
                    if (count($path) > 0 && $aliasToId == $path[count($path)-1]) {
                        $class .= ' active';
                    }
                    elseif (in_array($aliasToId, $path)) {
                        $class .= ' alias-parent-active';
                    }
                }

                if ($item->parent) {
                    $class .= ' g-parent';
                }

                if (!empty($class)) {
                    $class = ' class="tchooz-vertical-item g-menu-item g-menu-'.trim($class) .'"';
                }

                if($item->level == 1) {
                    echo '<li' . $class . 'onmouseenter="enableTooltip(' . $item->id . ')" onmouseleave="disableTooltip(' . $item->id . ')">';
                } else {
                    echo '<li' . $class.'>';
                }

                // Render the menu item.
                switch ($item->type) :
                    case 'separator':
                    case 'url':
                    case 'component':
                        require JModuleHelper::getLayoutPath('mod_emundusmenu', 'tchooz_'.$item->type);
                        break;

                    default:
                        require JModuleHelper::getLayoutPath('mod_emundusmenu', 'tchooz_url');
                        break;
                endswitch;

                // The next item is deeper.
                if ($item->deeper) {
                    echo '<div class="message-tooltip" id="tooltip-'.$item->id.'" style="height: auto"><div class="message-tooltip-block"><p>'.$item->title.'</p>';
                    echo '<ul class="g-sublevel-list" id="sublevel_list_' . $item->id . '">';
                    echo '<li class="g-dropdown-column">';
                    echo '<div class="g-grid"><div class="g-block size-100"><ul class="g-sublevel">';
                }
                // The next item is shallower.
                elseif ($item->shallower) {
                    //echo '<div class="message-tooltip" id="tooltip-'.$item->id.'">'.$item->title.'</div>';
                    echo '</li>';
                    echo str_repeat('</ul></div></div></li></ul>', $item->level_diff);
                }
                // The next item is on the same level.
                else {
                    echo '<div class="message-tooltip" id="tooltip-'.$item->id.'"><div class="message-tooltip-block"> <a class class="'.$class.'" href="'. $item->flink . '"> '.$item->title.'</a></div></div>';
                    echo '</li>';
                }
            endif;
        endforeach;



        foreach ($help_list as $i => &$item) :
            if($item->params->get('menu_show') != 0) :
                $item->anchor_css="item";
                $class = 'item-'.$item->id.' g-standard';
                if ($item->id == $active_id) {
                    $class .= ' current';
                }

                if (in_array($item->id, $path)) {
                    $class .= ' active';
                }
                elseif ($item->type == 'alias') {
                    $aliasToId = $item->params->get('aliasoptions');
                    if (count($path) > 0 && $aliasToId == $path[count($path)-1]) {
                        $class .= ' active';
                    }
                    elseif (in_array($aliasToId, $path)) {
                        $class .= ' alias-parent-active';
                    }
                }

                if ($item->parent) {
                    $class .= ' g-parent';
                }

                if (!empty($class)) {
                    $class = ' class="tchooz-vertical-item g-menu-item g-menu-'.trim($class) .'"';
                }

                echo '<li'.$class.'onmouseenter="enableTooltip('.$item->id.')" onmouseleave="disableTooltip('.$item->id.')">';

                // Render the menu item.
                switch ($item->type) :
                    case 'separator':
                    case 'url':
                    case 'component':
                        require JModuleHelper::getLayoutPath('mod_emundusmenu', 'tchooz_'.$item->type);
                        break;

                    default:
                        require JModuleHelper::getLayoutPath('mod_emundusmenu', 'tchooz_url');
                        break;
                endswitch;

                // The next item is deeper.
                if ($item->deeper) {
                    echo '<ul class="g-sublevel-list" id="sublevel_list_' . $item->id . '" style="display: none">';
                    echo '<li class="g-dropdown-column">';
                    echo '<div class="g-grid"><div class="g-block size-100"><ul class="g-sublevel"><li class="g-level-'.($item->level).' g-go-back"><a class="g-menu-item-container" href="#" onclick="backToParentMenu(' . $item->id . ')" data-g-menuparent=""><span class="g-menu-item-content"><span class="g-menu-item-title">' . JText::_("COM_EMUNDUS_BACK") . '</span></span></a></li>';
                }
                // The next item is shallower.
                elseif ($item->shallower) {
                    echo '</li>';
                    echo str_repeat('</ul></div></div></li></ul>', $item->level_diff);
                }
                // The next item is on the same level.
                else {
                    echo '<div class="message-tooltip" id="tooltip-'.$item->id.'"><div class="message-tooltip-block">'.$item->title.'</div></div>';

                    //'<a'.$class.' href="'. $item->flink . '"'. $item->title .'></a>

                    echo '</li>';
                }
            endif;
        endforeach;
        ?>
    </ul>
</nav>


<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery(".g-sublevel > li").on('mouseenter', function (e) {
            if (jQuery('ul', this).length) {
                var elm = jQuery('ul:first', this);
                var off = elm.offset();
                var l = off.left;
                var w = elm.width();
                var docH = jQuery("#g-page-surround").height();
                var docW = jQuery("#g-page-surround").width();
                var isEntirelyVisible = (l + w <= docW);

                if (!isEntirelyVisible) {
                    jQuery(this).addClass('edge');
                } else {
                    jQuery(this).removeClass('edge');
                }
            }
        });
    });


    //Keep original tooltip margin to reposition after mouse out (usefull in case of window resizing)
    const originalMargin = parseInt(jQuery("[id^=tooltip-]:first").css('margin-top'),10);

    function enableTooltip(menu){
        if(jQuery(".image-title").css("display") != 'none') {
            if(typeof jQuery("#sublevel_list_" + menu)[0] != 'undefined'){
                jQuery("#tooltip-" + menu).css('margin-left', '200px');
                jQuery("#tooltip-" + menu).css('display', 'block');
            }
        } else {
            jQuery("#tooltip-" + menu).css('margin-left', '0');
            jQuery("#tooltip-" + menu).css('display', 'block');

            //Reposition tooltip if out of viewport or scroll happened
            var tooltipBox = document.querySelector("#tooltip-" + menu);
            var tooltipRect = tooltipBox.getBoundingClientRect();
            const menuBox = document.querySelector("li.g-menu-item-"+menu);
            const menuRect = menuBox.getBoundingClientRect();
            const viewportHeight = window.innerHeight || document.documentElement.clientHeight;

            //reposition after scrolling
            if(tooltipRect.top - menuRect.top > 10){
                jQuery("#tooltip-" + menu).css('margin-top', -(tooltipRect.top - menuRect.top - originalMargin)+'px');
                //get new position of tooltip
                tooltipBox = document.querySelector("#tooltip-" + menu);
                tooltipRect = tooltipBox.getBoundingClientRect();
            }

            //reposition out of viewport
            if(tooltipRect.bottom > viewportHeight){
                jQuery("#tooltip-" + menu).css('margin-top', -(tooltipRect.bottom - viewportHeight - parseInt(jQuery("#tooltip-" + menu).css('margin-top'),10))+'px');
            }
        }
    }

    function disableTooltip(menu){
        jQuery("#tooltip-" + menu).css('display', 'none');
        jQuery("#tooltip-" + menu).css('margin-top', originalMargin);
    }

    function enableTitles(state = null){
        if(jQuery(".image-title").css("display") == 'none' && state == null){
            localStorage.setItem('menu', 'true');
            jQuery(".tchooz-vertical-toplevel").css("width","250px");
            jQuery(".tchooz-vertical-item").css("width","auto");
            jQuery(".grey-navbar-icons").css("opacity","1");
            jQuery(".sidebar-formbuilder").css("opacity","0");
            if(window.innerWidth >= 1280) {
                jQuery("#g-footer").css("padding-left", "280px");
                jQuery("#footer-rgpd").css("padding-left", "280px");
                jQuery("#g-container-main").css("padding-left", "280px");
                jQuery("#header-a").css("opacity", "1");
            }
            setTimeout(() =>{
                jQuery(".image-title").css("display","block");
                jQuery(".image-title").css("opacity","1");
                jQuery(".sidebar-formbuilder").css("display","none");
                setTimeout(() => {
                    jQuery(".g-menu-parent-indicator").css("display","block");
                },50);
            },250)
        } else if(state == 'true'){
            jQuery(".tchooz-vertical-toplevel").css("width","250px");
            jQuery(".tchooz-vertical-item").css("width","auto");
            jQuery(".grey-navbar-icons").css("opacity","1");
            jQuery(".sidebar-formbuilder").css("opacity","0");
            if(window.innerWidth >= 1280) {
                jQuery("#g-footer").css("padding-left", "280px");
                jQuery("#footer-rgpd").css("padding-left", "280px");
                jQuery("#g-container-main").css("padding-left", "280px");
                jQuery("#header-a").css("opacity", "1");
            }
            setTimeout(() =>{
                jQuery(".image-title").css("display","block");
                jQuery(".image-title").css("opacity","1");
                jQuery(".sidebar-formbuilder").css("display","none");
                setTimeout(() => {
                    jQuery(".g-menu-parent-indicator").css("display","block");
                },50);
            },250)
        } else {
            localStorage.setItem('menu', 'false');
            jQuery(".tchooz-vertical-toplevel").css("width","55px");
            jQuery(".image-title").css("opacity","0");
            jQuery(".g-menu-parent-indicator").css("display","none");
            jQuery(".sidebar-formbuilder").css("display","block");
            jQuery(".sidebar-formbuilder").css("opacity","1");
            if(window.innerWidth >= 1280) {
                jQuery("#g-container-main").css("padding-left", "76px");
                jQuery("#g-footer").css("padding-left", "76px");
                jQuery("#footer-rgpd").css("padding-left", "76px");
                jQuery("#header-a").css("opacity", "0");
            }
            setTimeout(() =>{
                jQuery(".image-title").css("display","none");
                jQuery(".grey-navbar-icons").css("opacity","0");
            },50)
        }
    }

    function backToParentMenu(id){
        jQuery(".tchooz-vertical-toplevel").css("width","250px");
        jQuery(".tchooz-vertical-item").css("width","auto");
        jQuery("#menu_separator").css('width','auto');
        jQuery(".g-menu-item-" + id).removeClass('parent-active');
        jQuery("#sublevel_list_" + id).css("display", "none");
        jQuery(".g-menu-parent-indicator").css("display","block");
        jQuery(".image-title").css("display","block");
        jQuery(".image-title").css("opacity","1");
    }

    document.addEventListener('click', function (e) {
        e.stopPropagation();
        if(jQuery(".image-title").css("display") == 'block') {
            enableTitles();
        }
    });

    window.onload = function () {
        this.enableTitles(localStorage.getItem('menu'));
    }
</script>
