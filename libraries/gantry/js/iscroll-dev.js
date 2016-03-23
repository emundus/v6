/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){this.iScroll=function(c,a){this.element=typeof c=="object"?c:document.getElementById(c);this.wrapper=this.element.parentNode;this.wrapper.style.position="relative";
this.element.style.webkitTransitionProperty="-webkit-transform";this.element.style.webkitTransitionTimingFunction="cubic-bezier(0,0,0.25,1)";this.element.style.webkitTransitionDuration="0";
this.element.style.webkitTransform="translate3d(0,0,0)";this.options={bounce:true,hScrollBar:true,vScrollBar:true};if(typeof a=="object"){for(var b in a){this.options[b]=a[b];
}}this.refresh();this.element.addEventListener("touchstart",this);this.element.addEventListener("touchmove",this);this.element.addEventListener("touchend",this);
window.addEventListener("orientationchange",this);};this.iScroll.prototype={_x:0,_y:0,handleEvent:function(a){switch(a.type){case"touchstart":this.onTouchStart(a);
break;case"touchmove":this.onTouchMove(a);break;case"touchend":this.onTouchEnd(a);break;case"webkitTransitionEnd":this.onTransitionEnd(a);break;case"orientationchange":this.refresh();
this.scrollTo(0,0,"0");break;}},refresh:function(){this.element.style.webkitTransitionDuration="0";this.scrollWidth=this.wrapper.clientWidth;this.scrollHeight=this.wrapper.clientHeight;
this.maxScrollX=this.scrollWidth-this.element.offsetWidth;this.maxScrollY=this.scrollHeight-this.element.offsetHeight;this.scrollX=this.element.offsetWidth>this.scrollWidth?true:false;
this.scrollY=this.element.offsetHeight>this.scrollHeight?true:false;if(this.options.hScrollBar&&this.scrollX){this.scrollBarX=new scrollbar("horizontal",this.wrapper);
this.scrollBarX.init(this.scrollWidth,this.element.offsetWidth);}else{if(this.scrollBarX){this.scrollBarX=this.scrollBarX.remove();}}if(this.options.vScrollBar&&this.scrollY){this.scrollBarY=new scrollbar("vertical",this.wrapper);
this.scrollBarY.init(this.scrollHeight,this.element.offsetHeight);}else{if(this.scrollBarY){this.scrollBarY=this.scrollBarY.remove();}}},get xfunction(){return this._x;
},get yfunction(){return this._y;},setPosition:function(a,b){this._x=a!==null?a:this._x;this._y=b!==null?b:this._y;this.element.style.webkitTransform="translate3d("+this._x+"px,"+this._y+"px,0)";
if(this.scrollBarX){this.scrollBarX.setPosition(this.scrollBarX.maxScroll/this.maxScrollX*this._x);}if(this.scrollBarY){this.scrollBarY.setPosition(this.scrollBarY.maxScroll/this.maxScrollY*this._y);
}},onTouchStart:function(b){if(b.targetTouches.length!=1){return false;}b.preventDefault();b.stopPropagation();this.element.style.webkitTransitionDuration="0";
if(this.scrollBarX){this.scrollBarX.bar.style.webkitTransitionDuration="0, 250ms";}if(this.scrollBarY){this.scrollBarY.bar.style.webkitTransitionDuration="0, 250ms";
}var a=new WebKitCSSMatrix(window.getComputedStyle(this.element).webkitTransform);if(a.m41!=this.x||a.m42!=this.y){this.setPosition(a.m41,a.m42);}this.touchStartX=b.touches[0].pageX;
this.scrollStartX=this.x;this.touchStartY=b.touches[0].pageY;this.scrollStartY=this.y;this.scrollStartTime=b.timeStamp;this.moved=false;},onTouchMove:function(c){if(c.targetTouches.length!=1){return false;
}var b=this.scrollX===true?c.touches[0].pageX-this.touchStartX:0;var a=this.scrollY===true?c.touches[0].pageY-this.touchStartY:0;if(this.x>0||this.x<this.maxScrollX){b=Math.round(b/4);
}if(this.y>0||this.y<this.maxScrollY){a=Math.round(a/4);}if(this.scrollBarX&&!this.scrollBarX.visible){this.scrollBarX.show();}if(this.scrollBarY&&!this.scrollBarY.visible){this.scrollBarY.show();
}this.setPosition(this.x+b,this.y+a);this.touchStartX=c.touches[0].pageX;this.touchStartY=c.touches[0].pageY;this.moved=true;if(c.timeStamp-this.scrollStartTime>250){this.scrollStartX=this.x;
this.scrollStartY=this.y;this.scrollStartTime=c.timeStamp;}},onTouchEnd:function(f){if(f.targetTouches.length>0){return false;}if(!this.moved){var h=document.createEvent("MouseEvents");
h.initMouseEvent("click",true,true,document.defaultView,0,0,0,0,0,0,0,0,0,0,0,null);f.changedTouches[0].target.dispatchEvent(h);return false;}var d=f.timeStamp-this.scrollStartTime;
var i=this.scrollX===true?this.momentum(this.x-this.scrollStartX,d,-this.x+50,this.x+this.element.offsetWidth-this.scrollWidth+50):{dist:0,time:0};var g=this.scrollY===true?this.momentum(this.y-this.scrollStartY,d,-this.y+50,this.y+this.element.offsetHeight-this.scrollHeight+50):{dist:0,time:0};
if(!i.dist&&!g.dist){this.onTransitionEnd();return false;}var c=Math.max(i.time,g.time);var b=this.x+i.dist;var a=this.y+g.dist;this.element.addEventListener("webkitTransitionEnd",this);
this.scrollTo(b,a,c+"ms");if(this.scrollBarX){this.scrollBarX.scrollTo(this.scrollBarX.maxScroll/this.maxScrollX*b,c+"ms");}if(this.scrollBarY){this.scrollBarY.scrollTo(this.scrollBarY.maxScroll/this.maxScrollY*a,c+"ms");
}},onTransitionEnd:function(){this.element.removeEventListener("webkitTransitionEnd",this);this.resetPosition();if(this.scrollBarX){this.scrollBarX.hide();
}if(this.scrollBarY){this.scrollBarY.hide();}},resetPosition:function(){var b=null,a=null;if(this.x>0||this.x<this.maxScrollX){b=this.x>=0?0:this.maxScrollX;
}if(this.y>0||this.y<this.maxScrollY){a=this.y>=0?0:this.maxScrollY;}if(b!==null||a!==null){this.scrollTo(b,a,"500ms");if(this.scrollBarX){this.scrollBarX.scrollTo(this.scrollBarX.maxScroll/this.maxScrollX*(b||this.x),"500ms");
}if(this.scrollBarY){this.scrollBarY.scrollTo(this.scrollBarY.maxScroll/this.maxScrollY*(a||this.y),"500ms");}}},scrollTo:function(b,a,c){this.element.style.webkitTransitionDuration=c||"400ms";
this.setPosition(b,a);},momentum:function(g,f,e,d){friction=0.1;deceleration=1.5;var c=Math.abs(g)/f*1000;var b=c*c/(20*friction)/1000;if(g>0&&e!==undefined&&b>e){c=c*e/b;
b=e;}if(g<0&&d!==undefined&&b>d){c=c*d/b;b=d;}b=b*(g<0?-1:1);var a=-c/-deceleration;if(a<1){a=1;}return{dist:Math.round(b),time:Math.round(a)};}};this.scrollbar=function(a,b){this.dir=a;
this.bar=document.createElement("div");this.bar.className="scrollbar "+a;this.bar.style.webkitTransitionTimingFunction="cubic-bezier(0,0,0.25,1)";this.bar.style.webkitTransform="translate3d(0,0,0)";
this.bar.style.webkitTransitionProperty="-webkit-transform,opacity";this.bar.style.webkitTransitionDuration="0,250ms";this.bar.style.pointerEvents="none";
this.bar.style.opacity="0";b.appendChild(this.bar);};this.scrollbar.prototype={size:0,maxSize:0,maxScroll:0,visible:false,init:function(a,b){var c=this.dir=="horizontal"?this.bar.offsetWidth-this.bar.clientWidth:this.bar.offsetHeight-this.bar.clientHeight;
this.maxSize=a-8;this.size=Math.round(this.maxSize*this.maxSize/b)+c;this.maxScroll=this.maxSize-this.size;this.bar.style[this.dir=="horizontal"?"width":"height"]=(this.size-c)+"px";
},setPosition:function(a){if(a<0){a=0;}else{if(a>this.maxScroll){a=this.maxScroll;}}a=this.dir=="horizontal"?"translate3d("+Math.round(a)+"px,0,0)":"translate3d(0,"+Math.round(a)+"px,0)";
this.bar.style.webkitTransform=a;},scrollTo:function(b,a){this.bar.style.webkitTransitionDuration=(a||"400ms")+",250ms";this.setPosition(b);},show:function(){this.visible=true;
this.bar.style.opacity="1";},hide:function(){this.visible=false;this.bar.style.opacity="0";},remove:function(){this.bar.parentNode.removeChild(this.bar);
return null;}};})());