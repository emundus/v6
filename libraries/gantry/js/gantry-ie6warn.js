/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var RokIEWarn=new Class({site:"sitename",initialize:function(f){var d=f;this.box=new Element("div",{id:"iewarn"}).inject(document.body,"top");var g=new Element("div").inject(this.box).set("html",d);
var e=this.toggle.bind(this);var c=new Element("a",{id:"iewarn_close"}).addEvents({mouseover:function(){this.addClass("cHover");},mouseout:function(){this.removeClass("cHover");
},click:function(){e();}}).inject(g,"top");this.height=document.id("iewarn").getSize().y;this.fx=new Fx.Morph(this.box,{duration:1000}).set({top:document.id("iewarn").getStyle("top").toInt()});
this.open=false;var b=Cookie.read("rokIEWarn"),a=this.height;if(!b||b=="open"){this.show();}else{this.fx.set({top:-a});}return;},show:function(){this.fx.start({top:0});
this.open=true;Cookie.write("rokIEWarn","open",{duration:7});},close:function(){var a=this.height;this.fx.start({top:-a});this.open=false;Cookie.write("rokIEWarn","close",{duration:7});
},status:function(){return this.open;},toggle:function(){if(this.open){this.close();}else{this.show();}}});