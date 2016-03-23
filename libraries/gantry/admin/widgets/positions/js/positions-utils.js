/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var tip;window.sliders={};window.slidersTips={};Array.prototype.compareArrays=function(a){if(!a){return false;}if(this.length!=a.length){return false;}for(var b=0;
b<a.length;b++){if(this[b].compareArrays){if(!this[b].compareArrays(a[b])){return false;}else{continue;}}if(this[b]!=a[b]){return false;}}return true;};
String.implement({baseConversion:function(c,b){var a=this;if(isNaN(c)||c<2||c>36||isNaN(b)||b<2||b>36){throw (new RangeError("Illegal radix. Radices must be integers between 2 and 36, inclusive."));
}a=parseInt(a,c);a=a.toString(b);return a;},hex2dec:function(){if(!isNaN(this.toInt())){return this;}return this.baseConversion(24,10);},dec2hex:function(){return this.baseConversion(10,24);
}});var tip;var createTip=function(b){var a=document.id(b);if(a){return a;}a=new Element("div",{id:b}).inject(document.body).set("text","2 | 2 | 2 | 2 | 2 | 2");
a.set("tween",{duration:200,link:"cancel"}).fade("out");return a;};var updateTip=function(b){var c=b.RT.blocks,a="";c.each(function(f,e){if(f.style.display!="none"){var d=f.className.split(" ")[1].replace("mini-grid-","");
a+=d.hex2dec()+" | ";}});a=a.substring(0,a.length-2);return a;};var updateSlider=function(d,b){var a=d;b=b;a.min=0;a.max=d.RT.list[b].length-1;a.range=a.max-a.min;
a.steps=a.max;a.stepSize=Math.abs(a.range)/a.steps;a.stepWidth=Number((a.stepSize*a.full/Math.abs(a.range)).toFixed(4));var c=(a.stepWidth==Infinity)?a.full:a.stepWidth;
a.drag.options.grid=c;if(!a.steps){a.drag.detach();}else{a.drag.attach();}d.RT.current=b;};var updateBlocks=function(c,a,b){if(!b){b=0;}var e=c.RT.blocks;
var d=c.RT.list[c.RT.current];a=a;e.removeClass("main");e.each(function(k,g){if(g<c.RT.current){e[g].setStyle("display","block");}else{e[g].setStyle("display","none");
}var f=c.RT.list[a][Math.round(b,0)].toString();e[g].className="";var h=(a==1)?c.RT.gridSize:f.charAt(g).hex2dec();e[g].addClass("mini-grid").addClass("mini-grid-"+h);
var j=e[g].innerHTML;if(j==c.RT.keyName&&(j!="")){e[g].addClass("main");}});};var serializeSettings=function(c,b){var a="";a+="a:1:{i:"+c.RT.gridSize+";";
a+="a:"+b.getLength()+":{";b.each(function(f,e){a+="i:"+e+";a:"+f.length+":{";for(i=0,l=f.length;i<l;i++){if(c.RT.type=="custom"){var d=c.RT.store[e][i];
a+="s:"+d.length+':"'+d+'";i:'+f[i].hex2dec()+";";}else{a+="i:"+i+";i:"+f[i].hex2dec()+";";}}a+="}";});a+="}}";return a;};var GantryPositions={add:function(e,a,f,g,m,h,j,n,k){e=document.id(e);
var d=a.replace(/-/,"_"),c=document.id(a+"-grp").getElement(".position"),b=document.id(a+"-grp").getElement(".knob");if(!window.sliders){window.sliders={};
}GantryPositionsTools.setEvent(e,d);window.sliders[d]=new RokSlider(c,b,{offset:5,snap:true,initialize:function(){this.hiddenEl=e;this.RT={};this.RT.current=$$("#"+a+"-grp .list .active a")[0].getFirst().innerHTML.toInt();
this.RT.list=j;this.RT.keys=n;this.RT.navigation=document.id(a+"-grp").getElement(".list").getChildren();this.RT.blocks=document.id(a+"-grp").getElements(".mini-grid");
this.RT.settings={};this.RT.gridSize=f;this.RT.defaults=g;this.RT.keyName=m||"";this.RT.type=h;this.RT.store={};GantryPositionsTools.init.bind(this,[d])();
},onComplete:function(){if(MooTools.lang){GantryPositionsTools.complete.bind(this,[a,e])();}else{GantryPositionsTools.complete.bind(this,a,e)();}},onDrag:function(o){if(MooTools.lang){GantryPositionsTools.drag.bind(this,[o,d])();
}else{GantryPositionsTools.drag.bind(this,o,d)();}},onChange:function(o){GantryPositionsTools.change.bind(this,o)();}});window.sliders[d].RT.navigation[k].fireEvent("click");
b.addEvents({mousedown:function(){this.addClass("down");},mouseup:function(){this.removeClass("down");}});GantryPositionsTools.wrapperTip(a,d);}};var GantryPositionsTools={init:function(b){this.options.steps=this.RT.list[this.RT.current].length-1;
this.setOptions(this.options);var d=this.RT.current,a=this.RT.navigation,e=this.RT.blocks;var c=this.RT.settings;a.each(function(g,f){c[d]=[];g.addEvent("click",function(k){if(k){k.stop();
}a.removeClass("active");this.addClass("active");updateSlider(window.sliders[b],this.getFirst().getFirst().innerHTML.toInt());var n=window.sliders[b].RT.defaults[window.sliders[b].RT.current][0];
if(window.sliders[b].RT.type=="custom"){var o=window.sliders[b].RT.defaults[window.sliders[b].RT.current];var j=window.sliders[b].RT.keys[window.sliders[b].RT.current];
var h=[];j.each(function(q,p){if(q.compareArrays(o.keys)){h.push(p);}});var m=window.sliders[b].RT.list[window.sliders[b].RT.current];h.each(function(q,p){if(m[q]==o.values[0]){window.sliders[b].set(q);
}});}else{window.sliders[b].set(window.sliders[b].RT.list[window.sliders[b].RT.current].indexOf(n));}});});updateBlocks(this,d);},complete:function(a,e){this.knob.removeClass("down");
e.set("value",serializeSettings(this,new Hash(this.RT.settings)));var b="";var d=Math.round(this.step);for(i=0,len=this.RT.current;i<len;i++){b+=this.RT.list[this.RT.current][(isNaN(d)||d<0)?0:d].toString().charAt(i);
}if(this.RT.type!="custom"){this.RT.defaults[this.RT.current]=[b];}else{this.RT.defaults[this.RT.current].values=[b];var c=[];for(i=0,l=this.RT.current;
i<l;i++){c.push(this.RT.blocks[i].innerHTML);}this.RT.defaults[this.RT.current].keys=c;}},drag:function(c,b){if(typeOf(c)=="array"){b=c[1];c=c[0];}this.element.getFirst().setStyle("width",c+10);
var f=this.step;var e=this.RT.list[this.RT.current][Math.round(f,0)],a="";if(!e){return;}e=e.toString();this.RT.settings[this.RT.current]=[];this.RT.store[this.RT.current]=[];
for(i=0,len=this.RT.current;i<len;i++){a+=e.charAt(i).hex2dec()+((i==len-1)?"":" | ");if(this.RT.type=="custom"){this.RT.settings[this.RT.current].push(e.charAt(i));
this.RT.store[this.RT.current].push(this.RT.keys[this.RT.current][Math.round(f,0)][i]);}else{this.RT.settings[this.RT.current].push(e.charAt(i));}if(this.RT.keys){var d=this.RT.keys[this.RT.current][Math.round(f,0)][i];
if(this.RT.type=="custom"){this.RT.blocks[i].set("text",d);}}}if(!tip){tip=createTip("positions-tip");}tip.set("html",a);updateBlocks(window.sliders[b],this.RT.current,f);
},change:function(a){if(this.options.snap){a=this.toPosition(this.step);}a=a||0;this.knob.setStyle(this.property,a);this.fireEvent("onDrag",a);},wrapperTip:function(a,b){document.id(a+"-wrapper").addEvents({mouseenter:function(){var c=this.getElement(".mini-container");
var d=c.getCoordinates();tip.setStyles({left:d.left+d.width+5,top:d.top-5});tip.set("html",updateTip(window.sliders[b]));tip.fade("in");},mouseleave:function(){tip.fade("out");
}});},showMax:function(b,d){var e=document.id(b+"-grp").getParent(".chain");if(!e){return;}e=e.getParent();var a=e.getElement(".chain-showmax select");
if(!a){return;}var c=document.id(b+"-grp").getElements("ul.list li");a.addEvent("change",function(g){if(!g||typeof g=="object"){g=this.get("value").toInt();
}else{g+=1;}var h=document.id(b+"-grp").getElement("li.active"),f=c.filter(function(k,j){return j+1>g;});if(c.indexOf(h)>g-1){c[g-1].fireEvent("click");
}c.setStyle("display","inline-block");f.setStyle("display","none");}).fireEvent("change");},setEvent:function(b,a){b.addEvent("set",function(k){var d=window.sliders[a].RT,o=k;
if(k.contains(",")){var j=o.split(",");k={};k[d.gridSize]={};k[d.gridSize][j.length]=j;k=serialize(k);}if(!k.contains("{")){k=serialize(k.replace(/\s/g,"").split(","));
}k=k.unserialize();if(!k[d.gridSize]){return;}else{k=new Hash(k[d.gridSize]);}var m={};var c={};k.each(function(q,r){m[r]=[];c[r]=[];if(d.type=="custom"){m[r]={};
c[r]={};m[r].keys=[];m[r].values=[];c[r].keys=[];c[r].values=[];}$H(q).each(function(s,t){var u=s.toString().dec2hex();if(d.type!="custom"){m[r].push(u);
c[r].push(u);}else{m[r].keys.push(t);c[r].keys.push(t);m[r].values.push(u);c[r].values.push(u);}});if(d.type!="custom"){c[r]=[c[r].join("")];}else{c[r].values=[c[r].values.join("")];
}});d.defaults=Object.merge(d.defaults,c);if(d.type!="custom"){var n=c[d.current];if(n){var h=d.list[d.current].indexOf(n[0])||0;window.sliders[a].set(h).fireEvent("onComplete");
}}else{var f=d.defaults[d.current];var p=d.keys[d.current];var e=[];p.each(function(r,q){if(r.compareArrays(f.keys)){e.push(q);}});var g=d.list[d.current];
e.each(function(r,q){if(g[r]==f.values[0]){window.sliders[a].set(r);}});}});}};Array.implement({diff:function(a,c){var b=(c)?Array.clone(this):this;for(i=0;
i<a.length;i++){if(b.contains(a[i])){b.erase(a[i]);}}return b;}});