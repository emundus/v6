/**
 * Block Until Fabrik is Ready
 * 
 * This code blocks all user input until the Fabrik susbsystem is ready.
 *
 * @copyright: Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

var onFabrikReadyBody = false;
var blockDiv = '<div id="blockDiv" style="position:absolute; left:0; right:0; height:100%; width:100%; z-index:9999999;"></div>';

function onFabrikReadyBlock(e) {
    if (blockDiv.length == 0) return false;
    e.stopPropagation();
    e.preventDefault();
    alert(Joomla.JText._("COM_FABRIK_STILL_LOADING"));
    blockDiv = '';
    return false;
}

function onFabrikReady() {
    if (typeof Fabrik === "undefined") {
        if (onFabrikReadyBody === false && typeof document.getElementsByTagName("BODY")[0] !== "undefined") {
            onFabrikReadyBody = document.getElementsByTagName("BODY")[0];
            onFabrikReadyBody.insertAdjacentHTML('afterbegin', blockDiv);
            jQuery("#blockDiv").click(function(e) {
                return onFabrikReadyBlock(e);
            });
            jQuery("#blockDiv").mousedown(function(e) {
                return onFabrikReadyBlock(e);
            });
        }    
        setTimeout(onFabrikReady, 50);
    } else {
        jQuery("#blockDiv").remove();
    }
}


if (document.readyState !== 'loading') {
    onFabrikReady();
} else {
    document.addEventListener('DOMContentLoaded', onFabrikReady()); 
}

