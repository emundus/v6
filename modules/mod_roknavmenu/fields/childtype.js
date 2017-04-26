/*
 * @version   $Id: childtype.js 4758 2012-10-30 19:15:31Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
window.addEvent("domready",function(){var f=document.id("jform_params_dropdown_children_type1")||document.id("jform_params_fusion_children_type1"),e=document.id("jform_params_dropdown_children_type2")||document.id("jform_params_fusion_children_type2"),b=document.id("jform_params_dropdown_children_type0")||document.id("jform_params_fusion_children_type0");
var g=document.id("jform_params_dropdown_modules")||document.id("jform_params_fusion_modules"),d=document.id("jform_params_dropdown_module_positions")||document.id("jform_params_fusion_module_positions");
if(g){var c=g.getParent("li")||g.getParent(".control-group");}if(d){var a=d.getParent("li")||d.getParent(".control-group");}if(f&&g){f.addEvent("click",function(){if(a){a.setStyle("display","none");
}if(c){c.setStyle("display","block");}var h=c.getParent(".pane-slider");if(h){if(h.getStyle("height").toInt()>0){h.setStyle("height",c.getParent(".panelform").getSize().y);
}}});}if(e&&d){e.addEvent("click",function(){if(c){c.setStyle("display","none");}if(a){a.setStyle("display","block");}var h=a.getParent(".pane-slider");
if(h){if(h.getStyle("height").toInt()>0){h.setStyle("height",a.getParent(".panelform").getSize().y);}}});}if(b){b.addEvent("click",function(){if(c){c.setStyle("display","none");
}if(a){a.setStyle("display","none");}var h=c.getParent(".pane-slider");if(h){if(h.getStyle("height").toInt()>0){h.setStyle("height",c.getParent(".panelform").getSize().y);
}}});}if(b.checked){b.fireEvent("click");}if(f.checked){f.fireEvent("click");}if(e.checked){e.fireEvent("click");}});