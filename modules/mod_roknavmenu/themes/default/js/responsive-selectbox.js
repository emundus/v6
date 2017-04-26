/*
 * @version   $Id: responsive-selectbox.js 8885 2013-03-28 17:38:51Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){var a={cache:[],build:function(){var d=document.getElement("ul.gf-menu"),c=document.getElement(".gf-menu-device-container");
if(!d||!c||d.retrieve("roknavmenu:dropdown:select")){return;}d.store("roknavmenu:dropdown:select",true);var b=new Element("select").inject(c,"top");a.getChildren(d,b,0);
a.attachEvent(b);},getChildren:function(d,n,h){var e=d.getChildren().flatten(),c,o,r,p,b,k,m,j,q;for(var g=0,f=e.length;g<f;g++){r=e[g].getElement(".item");
if(!r){continue;}b=e[g].className.replace(/\s/g,"-");if(a.cache.contains(b)){continue;}a.cache.push(b);o=r.getElement("em")||r.getElement("i");c=e[g].getElement("ul");
p=e[g].getElement("ol");q=e[g].hasClass("active");k=r.get("text").clean();m=o?o.get("text").clean():"";if(k.length!=m.length){k=k.substr(0,(k.length-1)-(m.length-1));
}j=new Element("option",{value:r.get("href"),text:"-".repeat(h)+" "+k}).inject(n);if(q){j.set("selected","selected");}if(c){if(c.getParent(".column")){a.getChildren(c.getParent(".dropdown").getElements(" > .column > ul"),n,h+1);
}else{a.getChildren(c,n,h+1);}}if(p){a.getChildren(p,n,h+1);}}},attachEvent:function(b){b.addEvent("change",function(){window.location.href=this.value;
});}};window.addEvent("domready",a.build);if(typeof ResponsiveMenu!="undefined"){ResponsiveMenu.implement({mediaQuery:function(d){var e=document.getElement(".gf-menu"),c=document.getElement(".gf-menu-device-container"),b=this.toggler.retrieve("roknavmenu:slide");
if(!e&&!c){return;}if(d=="(min-width: 768px)"){e.setStyle("display","inherit");this.slide.wrapper.setStyle("display","none");this.toggler.setStyle("display","none");
}else{e.setStyle("display","none");this.slide.wrapper.setStyle("display","inherit");this.toggler.setStyle("display","block");}b.hide();this.toggler.removeClass("active");
}});}})());