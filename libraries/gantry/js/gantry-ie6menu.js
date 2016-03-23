/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var sfHover=function(c,b){if(!b){b="sfHover";}var a=$$("."+c).getElements("li");if(!a.length){return false;}a.each(function(d){d.addEvents({mouseenter:function(){var e=this.getProperty("class").split(" ");
e=e.filter(function(g){return !g.test("-"+b);});e.each(function(g){if(this.hasClass(g)){this.addClass(g+"-"+b);}},this);var f=e.join("-")+"-"+b;if(!this.hasClass(f)){this.addClass(f);
}this.addClass(b);},mouseleave:function(){var e=this.getProperty("class").split(" ");e=e.filter(function(g){return g.test("-"+b);});e.each(function(g){if(this.hasClass(g)){this.removeClass(g);
}},this);var f=e.join("-")+"-"+b;if(!this.hasClass(f)){this.removeClass(f);}this.removeClass(b);}});});};window.addEvent("domready",function(){sfHover("menutop");
});