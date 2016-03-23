/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var SelectBox=new Class({Implements:[Events],initialize:function(a){if(!a){a=document.body;}this.elements=document.id(a).getElements(".selectbox-wrapper");
this.elements.each(function(c,b){this.updateSizes(c);c.store("g:bound",this.mouseEnter.bind(this,c));c.addEvent("mouseenter",c.retrieve("g:bound"));},this);
},mouseEnter:function(a){var b=this.getObjects(a);this.init(a);b.real.store("gantry:objs",b);b.real.addEvent("detach",this.detach.bind(this,b.element));
b.real.addEvent("attach",this.attach.bind(this,b.element));b.real.addEvents({set:function(e){var d=b.opts.get("value");var c=d.indexOf(e);if(c!=-1){b.list[c].fireEvent("click");
}}});this.lisEvents(a);if(a.hasClass("disabled")){this.detach(a);}a.removeEvent("mouseenter",a.retrieve("g:bound"));},updateSizes:function(b){var e=this.getObjects(b);
var c={dropdown:e.dropdown.getSize().x,arrow:e.arrow.getSize().x,ul:e.ul.getSize().y};var a=e.ul.getStyle("max-height").toInt();var d=(c.ul>a)?10:0;e.top.setStyle("width",c.dropdown+d);
e.dropdown.setStyle("width",c.dropdown+c.arrow+d);if(d>0){e.ul.setStyle("overflow","auto");}},getObjects:function(a){return{element:a,selected:a.getElement(".selectbox-top .selected span"),top:a.getElement(".selectbox-top"),dropdown:a.getElement(".selectbox-dropdown"),arrow:a.getElement(".arrow"),ul:a.getElement("ul"),list:a.getElements("li"),real:a.getParent().getElement("select"),opts:a.getParent().getElement("select").getChildren()};
},init:function(a){a.addEvents({click:this.toggle.bind(this,a),disable:this.disable.bind(this,a),enable:this.enable.bind(this,a),mousedown:this.preventDefault.bind(this,a),onselectstart:this.preventDefault.bind(this,a),mouseenter:this.enter.bind(this,a),mouseleave:this.leave.bind(this,a)},this);
},lisEvents:function(c){var d=this.getObjects(c),a=this;var b=d.real.getChildren();d.list.each(function(f,e){f.addEvents({mouseenter:function(){if(b[e].getProperty("disabled")){return;
}d.list.removeClass("hover");this.addClass("hover");},mouseleave:function(){if(b[e].getProperty("disabled")){return;}this.removeClass("hover");},click:function(){if(b[e].getProperty("disabled")){return;
}d.list.removeClass("active");this.addClass("active");this.fireEvent("select",[d,e]);},select:a.select.bind(a)});});},attach:function(a){a.addEvent("click",this.toggle.bind(this,a));
a.stat="close";a.fireEvent("enable",a);},detach:function(a){a.removeEvents("click");a.fireEvent("disable",a);},toggle:function(a){var b=this.getObjects(a);
if(a.stat=="open"){return this.hide(b);}else{if(a.stat=="close"){return this.show(b);}}return this.show(b);},enter:function(a){var b=this.getObjects(a);
clearTimeout(a.timer);},leave:function(a){var b=this.getObjects(a);clearTimeout(a.timer);a.timer=this.hide.delay(500,this,b);},show:function(a){a.dropdown.setStyle("visibility","visible");
a.element.addClass("pushed");a.element.stat="open";},hide:function(a){a.dropdown.setStyle("visibility","hidden");a.element.removeClass("pushed");a.element.stat="close";
},select:function(b,a){if(a==-1){return;}b.selected.set("html",b.list[a].innerHTML);b.real.selectedIndex=a;b.real.fireEvent("change",a);},enable:function(a){a.removeClass("disabled");
},disable:function(a){clearTimeout(a.timer);this.hide(this.getObjects(a));a.addClass("disabled");},preventDefault:function(a,b){b.stop();return false;}});
window.addEvent("domready",function(){window.selectboxes=new SelectBox();});