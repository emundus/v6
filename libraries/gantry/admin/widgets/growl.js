/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){var a=new Class({Implements:[Options,Events,Chain],options:{duration:3000,position:"upperRight",container:null,bodyFx:null,itemFx:null,margin:{x:10,y:10},offset:10,className:"roar",onShow:function(){},onHide:function(){},onRender:function(){}},initialize:function(b){this.setOptions(b);
this.items=[];this.container=document.id(this.options.container)||document;},alert:function(f,d,c){var e=Array.from(arguments).link({title:Type.isString,message:Type.isString,options:Type.isObject});
var b=[new Element("h3",{html:e.title||""})];if(e.message){b.push(new Element("p",{html:e.message}));}return this.inject(b,e.options);},inject:function(b,j){if(!this.body){this.render();
}j=Object.merge(this.options,j||{});var d=[-this.options.offset,0];var h=this.items.getLast();if(h){d[0]=h.retrieve("roar:offset");d[1]=d[0]+h.offsetHeight+this.options.offset;
}var g={opacity:1};g[this.align.y]=d;var i=new Element("div",{"class":this.options.className,opacity:0}).adopt(new Element("div",{"class":"roar-bg",opacity:0.7}),b);
i.setStyle(this.align.x,0).store("roar:offset",d[1]).set("morph",Object.merge({link:"cancel",onStart:Chain.prototype.clearChain,transition:"back:out"},this.options.itemFx));
var e=this.remove.bind(this,i);this.items.push(i.addEvent("click",e));if(this.options.duration){var f=false;var c=(function(){c=null;if(!f){e();}}).delay(this.options.duration);
i.addEvents({mouseover:function(){f=true;},mouseout:function(){f=false;if(!c){e();}}});}i.inject(this.body).morph(g);return this.fireEvent("onShow",[i,this.items.length]);
},remove:function(c){var b=this.items.indexOf(c);if(b==-1){return this;}this.items.splice(b,1);c.removeEvents();var d={opacity:0};d[this.align.y]=c.getStyle(this.align.y).toInt()-c.offsetHeight-this.options.offset;
c.morph(d).get("morph").chain(c.destroy.bind(c));return this.fireEvent("onHide",[c,this.items.length]).callChain(c);},empty:function(){while(this.items.length){this.remove(this.items[0]);
}return this;},render:function(){this.position=this.options.position;if(typeof this.position=="string"){var b={x:"center",y:"center"};this.align={x:"left",y:"top"};
if((/left|west/i).test(this.position)){b.x="left";}else{if((/right|east/i).test(this.position)){this.align.x=b.x="right";}}if((/upper|top|north/i).test(this.position)){b.y="top";
}else{if((/bottom|lower|south/i).test(this.position)){this.align.y=b.y="bottom";}}this.position=b;}this.body=new Element("div",{"class":"roar-body"}).inject(document.body);
if(Browser.Engine.trident4){this.body.addClass("roar-body-ugly");}this.moveTo=this.body.setStyles.bind(this.body);this.reposition();if(this.options.bodyFx){var d=new Fx.Morph(this.body,Object.merge({chain:"cancel",transition:"circ:out"},this.options.bodyFx));
this.moveTo=d.start.bind(d);}var c=this.reposition.bind(this);window.addEvents({scroll:c,resize:c});this.fireEvent("onRender",this.body);},reposition:function(){var d=document.getCoordinates(),c=document.getScroll(),e=this.options.margin;
d.left+=c.x;d.right+=c.x;d.top+=c.y+30;d.bottom+=c.y+30;var b=(typeof this.container=="element")?this.container.getCoordinates():d;this.moveTo({left:(this.position.x=="right")?(Math.min(b.right,d.right)-e.x):(Math.max(b.left,d.left)+e.x),top:(this.position.y=="bottom")?(Math.min(b.bottom,d.bottom)-e.y):(Math.max(b.top,d.top)+e.y)});
}});window.addEvent("domready",function(){this.growl=new a();});})());