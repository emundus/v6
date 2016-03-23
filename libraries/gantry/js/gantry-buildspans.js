/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var GantryBuildSpans=function(b,c,a){(b.length).times(function(e){var f="."+b[e];var d=function(j){j.setStyle("visibility","visible");var i=j.get("text");
var g=i.split(" ");first=g[0];rest=g.slice(1).join(" ");html=j.innerHTML;if(rest.length>0){var k=j.clone().set("text"," "+rest),h=new Element("span").set("text",first);
h.inject(k,"top");k.replaces(j);}};$$(f).each(function(g){c.each(function(h){g.getElements(h).each(function(j){var i=j.getFirst();if(i&&i.get("tag")=="a"){d(i);
}else{d(j);}});});});});};