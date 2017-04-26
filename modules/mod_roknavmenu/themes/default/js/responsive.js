/*
 * @version   $Id: responsive.js 4586 2012-10-27 01:50:24Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){var a=this.ResponsiveMenu=new Class({initialize:function(){this.build();
this.attachEvents();this.mediaQuery(RokMediaQueries.getQuery());},build:function(){if(this.toggler){return this.toggler;}this.toggler=new Element("div.gf-menu-toggle").inject(document.body);
(3).times(function(b){new Element("span.icon-bar").inject(this.toggler);},this);return this.toggler;},attachEvents:function(){var c=this.toggler.retrieve("roknavmenu:click",function(d){this.toggle.call(this,d,this.toggler);
}.bind(this));this.toggler.addEvent("click",c);this.slide=this.toggler.retrieve("roknavmenu:slide",new Fx.Slide(document.getElement(".gf-menu-device-container"),{duration:350,hideOverflow:true,resetHeight:true,link:"cancel",onStart:function(){if(!this.open){this.wrapper.addClass("gf-menu-device-wrapper");
}},onComplete:function(){if(this.open){this.wrapper.removeClass("gf-menu-device-wrapper");}}}).hide());try{RokMediaQueries.on("(max-width: 767px)",this.mediaQuery.bind(this));
RokMediaQueries.on("(min-width: 768px)",this.mediaQuery.bind(this));}catch(b){if(typeof console!="undefined"){console.error('Error [Responsive Menu] while trying to add a RokMediaQuery "match" event',b);
}}},toggle:function(c,d){var b=d.retrieve("roknavmenu:slide");d[b.open?"removeClass":"addClass"]("active");b[b.open?"slideOut":"slideIn"]();},mediaQuery:function(d){var e=document.getElement(".gf-menu"),c=document.getElement(".gf-menu-device-container"),b=this.toggler.retrieve("roknavmenu:slide");
if(!e&&!c){return;}if(d=="(min-width: 768px)"){e.inject(b.wrapper,"after");this.slide.wrapper.setStyle("display","none");this.toggler.setStyle("display","none");
}else{e.inject(c);this.slide.wrapper.setStyle("display","inherit");this.toggler.setStyle("display","block");}b.hide();this.toggler.removeClass("active");
}});window.addEvent("domready",function(){this.RokNavMenu=new a();});})());