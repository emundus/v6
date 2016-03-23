/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var iDropdowns=function(d,p){var i=new Hash({}),h,k,l,n=false;var a=new Element("div",{id:"idrops"}).inject("rt-menu");var g=$$("#rt-menu ul")[0];var b=0;
g.getChildren().each(function(q){b+=q.offsetWidth;});g.setStyle("width",b+document.getElementById("rt-right-menu").offsetWidth);var o=$$("#rt-menu li.root");
var e=$$("#rt-menu ul.menu li.parent ul");var j=[];e.each(function(q){var r=q.getLast().getProperty("parent_id").replace("idrops-","");if(!i.get(r)){i.set(r,[]);
}i.get(r).push(q);new Element("div",{id:"idown-"+r,"class":"idown"}).adopt(q).inject(a);j.push(q.getElements("li").filter(function(s){return s.getProperty("parent_id");
}));});i=Array.from(o).combine(j).flatten();i.each(function(q){if(q.hasClass("parent")){var r=q.getLast();if(r.get("tag")=="a"){r.storedLink=r.href;r.setProperty("href","#"+q.id.replace("idrops-","idown-"));
}q.addEvent("click",function(v){v.preventDefault();if(n){return false;}var u=q.get("parent_id").replace("idrops-","");var t=q.get("id").replace("idrops-","");
k=document.id("idown-"+u);l=document.id("idown-"+t);l.addEventListener("webkitAnimationEnd",c,false);l.addClass("selected");if(u==1&&!k){var s=l.getSize().y;
if(h){k=h;s=Math.max(s,h.getSize().y);h.addClass("slidedown").addClass("out");}document.id("idrops").setStyles({overflow:"hidden",height:s});l.addClass("slidedown").addClass("in");
}else{k.addClass(animation).addClass("out");l.addClass(animation).addClass("in");}n=true;h=l;});}});var m=$$("#rt-menu #idrops .backmenu");var f=$$("#rt-menu #idrops .closemenu");
m.each(function(q){q.addEvent("click",function(s){s.preventDefault();if(n){return false;}var r=q.getProperty("parent_id").replace("idrops-","idown-");l.addEventListener("webkitAnimationEnd",c,false);
l=document.id(r).addClass("selected");k=h;h=l;k.addClass(animation).addClass("reverse").addClass("out");l.addClass(animation).addClass("reverse").addClass("in");
n=true;});});f.each(function(q){q.addEvent("click",function(s){s.preventDefault();if(n){return false;}var r=h.getSize().y;var u=h;var t=h;h=null;document.id("idrops").setStyles({overflow:"hidden",height:r});
t.addEventListener("webkitAnimationEnd",c,false);t.addClass("slidedown").addClass("out");n=true;});});var c=function(){document.id("idrops").setStyle("overflow","");
if(k){k.className="idown";}if(h==l&&h==k){l.className="idown";h=null;}else{l.className="idown selected";}if(!h){document.id("idrops").setStyle("height",0);
l.removeClass("selected");}l.removeEventListener("webkitAnimationEnd",c,false);n=false;};};window.addEvent("domready",iDropdowns);