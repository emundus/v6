/*
 * @version   $Id: sfhover.js 4586 2012-10-27 01:50:24Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var sfHover=function(c,e){if(!e){e="sfHover";
}var a="sfActive";var b=$$("."+c).getElements("li");b[0].each(function(f){var g=f.getFirst();if(g){b[0].push(g);}});var d=$$("."+c).getElements("li.active");
if(d[0]&&d[0].length){d=d[0];d.each(function(h){var j=h.getFirst();if(j){j.addClass("active");var g=j.getProperty("class").split(" ");var f=[];for(i=1,l=g.length;
i<l;i++){f.push(g[0]+"-"+g[i]);}f.push(g.join("-"));f.each(function(k){j.addClass(k);});}});}if(!b.length){return false;}b.each(function(f){f.addEvents({mouseenter:function(){var h=this.getProperty("class").split(" ");
h=h.filter(function(j){return !j.test("-"+e)&&!j.test("-"+a);});h.each(function(j){if(this.hasClass(j)){this.addClass(j+"-"+e);}},this);var g=h.join("-")+"-"+e;
if(!this.hasClass(g)){this.addClass(g);}this.addClass(e);},mouseleave:function(){var h=this.getProperty("class").split(" ");h=h.filter(function(j){return j.test("-"+e);
});h.each(function(j){if(this.hasClass(j)){this.removeClass(j);}},this);var g=h.join("-")+"-"+e;if(!this.hasClass(g)){this.removeClass(g);}this.removeClass(e);
}});});};window.addEvent("domready",function(){sfHover("menutop");});