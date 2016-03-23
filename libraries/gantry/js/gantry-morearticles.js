/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var GantryMoreArticles=new Class({Implements:[Options],options:{leadings:2,moreText:"more articles",url:""},initialize:function(b){this.setOptions(b);this.wrapper=document.getElements(".rt-blog .items-row")||document.getElement(".rt-teaser-articles")||document.getElement(".rt-leading-articles");
if(!this.wrapper||!this.wrapper.length){return;}if(this.wrapper.length){this.wrapper=this.wrapper.getLast();}this.start=this.options.leadings;this.buildButton();
this.ajax=new Request({url:this.options.url,method:"get",onRequest:function(){this.button.addClass("spinner");}.bind(this),onSuccess:this.handle.bind(this)});
},buildButton:function(){this.button=new Element("a",{id:"more-articles",href:"#"}).adopt(new Element("span").set("text",this.options.moreText));var b=new Element("div",{"class":"rt-more-articles"}).inject(this.wrapper,"after");
this.button.inject(b).addEvent("click",function(a){a.stop();if(this.button.hasClass("disabled")){return;}this.ajax.get({limitstart:this.start});}.bind(this));
},handle:function(f){this.start+=this.options.leadings;this.button.removeClass("spinner");var i=new Element("div").set("html",f);var g=i.getElements(".rt-article");
if(!g.length){this.button.removeEvent("click");this.button.addClass("disabled");}else{if(g.length<this.options.leadings){this.button.removeEvent("click");
this.button.addClass("disabled");}g.inject(this.wrapper,this.wrapper.hasClass("rt-leading-articles")?"inside":"after");if(typeof GantryBuildSpans=="function"){var j=["rt-block"];
var h=["h3","h2","h1"];GantryBuildSpans(j,h);}if(typeof GantryArticleDetails!="undefined"){GantryArticleDetails.init();}}if(typeof rokbox!="undefined"&&rokbox.refresh){rokbox.refresh();
}}});