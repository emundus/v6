/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */



function applyAutoSettings(int, pos)
{
        var selectoron = getSelector(int, "on");
        var selectoroff = getSelector(int, "off");
        
        
        if (jQuery(selectoron).length)
        {
                jQuery(selectoron).val("1");
        }

        if (jQuery(selectoroff).length)
        {
                jQuery(selectoroff).val("0");
        }

        submitJchSettings();
}
;

jQuery(document).ready(function () {
        var i = 1;
        for (i = 1; i <= 6; i++) {
                var flag = true;

                var selectoron = getSelector(i, "on");
                var selectoroff = getSelector(i, "off");

                jQuery(selectoron + "[value=1]").each(function () {
                        var attr = jQuery(this).attr("checked");

                        if (typeof attr === typeof undefined || attr === false) {
                                flag = false;
                                return false;
                        }
                });

                if (flag == true) {
                        jQuery(selectoroff + "[value=0]").each(function () {
                                var attr = jQuery(this).attr("checked");

                                if (typeof attr === typeof undefined || attr === false) {
                                        flag = false;
                                        return false;
                                }
                        })
                }

                if (flag == true) {
                        jQuery("div.icon.enabled.settings-" + i + " a i#toggle").addClass("on");
                        break;
                }
        }
});

function addJchOption(id)
{
        var input = jQuery("#" + id + " + .chzn-container > .chzn-choices > .search-field > input, #" + id + " + .chosen-container > .chosen-choices > .search-field > input");
        var txt = input.val();

        if (txt === input.prop("defaultValue")) {
                txt = null;
        }

        if (txt === null || txt === "") {
                alert("Please input an item in the box to add to the drop-down list");
                return false;
        }

        jQuery("#" + id).append(jQuery("<option/>", {
                value: txt.replace("...", ""),
                text: txt
        }).attr("selected", "selected"));

        jQuery("#" + id).trigger("liszt:updated");
        jQuery("#" + id).trigger("chosen:updated");
}
;

function ucFirst(string) {
    return string[0].toUpperCase() + string.slice(1);
};


function getTimeStamp() {
        return new Date().getTime();
};

