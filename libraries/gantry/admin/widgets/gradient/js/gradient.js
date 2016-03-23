/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var GantryGradient={init:function(){var a=$$(".gradient-preview");a.each(function(b){new Element("div").set("text","Sorry. Gradient previews can be seen only on WebKit and Gecko based browsers.").inject(b.addClass("error"));
});},add:function(g,a){var c=document.id(g);var f=new Element("div").set("text","Sorry. Gradient previews can only be seen on WebKit and Gecko based browsers.").inject(c);
c.getParent().addClass("error");if(Browser.Engine.webkit||Browser.Engine.gecko){var e=["from","fromopacity","toopacity","to","gradient","direction_start","direction_end"];
var d=window.moorainbow;var b=GantryGradient.updateGradient.pass([a,c],GantryGradient);e.each(function(h){var l=a+"_"+h;var k=l.replace(/-/g,"_");var i=GantryGradient.updateGradient.pass([a,c],GantryGradient);
if(typeof d["r_"+k]!="undefined"){var j=k;d["r_"+j].addEvent("onChange",i);}else{if(document.id(l)&&(h=="fromopacity"||h=="toopacity")){window.sliders[k].addEvent("onDrag",i);
}else{if(document.id(l)&&(h=="gradient"||h=="direction_start"||h=="direction_end")){document.id(l).addEvent("change",i);}}}});GantryGradient.updateGradient(a,c);
}},updateGradient:function(b,j){var e={from:document.id(b+"_from"),to:document.id(b+"_to"),fromOp:document.id(b+"_fromopacity"),toOp:document.id(b+"_toopacity"),type:document.id(b+"_gradient"),direction_start:document.id(b+"_direction_start"),direction_end:document.id(b+"_direction_end")};
var g=e.from.value.hexToRgb(true);var d=e.to.value.hexToRgb(true);g=g.join(", ")+", "+(e.fromOp?e.fromOp.value:1);d=d.join(", ")+", "+(e.toOp?e.toOp.value:1);
var h;if(Browser.Engine.webkit){h="-webkit-gradient("+(e.type?e.type.value:"linear")+", "+e.direction_start.value.replace("-"," ")+", "+e.direction_end.value.replace("-"," ")+", from(rgba("+g+")), to(rgba("+d+")))";
j.getParent().removeClass("error").getFirst().empty().style.background=h;}else{if(Browser.Engine.gecko){var c=e.direction_start.value.split("-");var f=e.direction_end.value.split("-");
var a,i;a=c[0];i=c[1];if(c[0]==f[0]){a="center";}if(c[1]==f[1]){i="center";}h="-moz-"+(e.type?e.type.value:"linear")+"-gradient("+a+" "+i+", rgba("+g+"), rgba("+d+"))";
j.getParent().removeClass("error").getFirst().empty().style.background=h;}}}};