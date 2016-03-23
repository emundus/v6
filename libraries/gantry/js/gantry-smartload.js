/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var GantrySmartLoad=new Class({Implements:[Events,Options],options:{placeholder:"blank.gif",container:window,cssrule:"img",offset:{x:200,y:200},exclusion:[]},initialize:function(b){this.setOptions(b);
this.container=document.id(this.options.container);this.images=$$(this.options.cssrule);this.dimensions={size:this.container.getSize(),scroll:this.container.getScroll(),scrollSize:this.container.getScrollSize()};
var c=this.options.exclusion[0].split(",");if(c.length&&(c.length!=1&&c[0]!="")){c.each(function(e){var d=$$(e+" "+this.options.cssrule);d.each(function(f){this.images.erase(f);
},this);},this);}this.init=0;this.storage=new Hash({});this.images.each(function(d,f){if(typeof d=="undefined"){return;}if(!d.get("width")&&!d.get("height")){this.storage.erase(d.get("smartload"));
this.images.erase(d);return;}var e=d.getSize();if(d.getProperty("width")){e.x=d.getProperty("width");e.y=d.getProperty("height");}if(!d.getProperty("width")&&e.x&&e.y){d.setProperty("width",e.x).setProperty("height",e.y);
}d.setProperty("smartload",f);this.storage.set(f,{src:d.src,width:e.x,height:e.y,fx:new Fx.Tween(d,{duration:250,transition:Fx.Transitions.Sine.easeIn})});
if(!this.checkPosition(d)){d.setProperty("src",this.options.placeholder).addClass("spinner");}else{this.storage.erase(d.getProperty("smartload"));this.images.erase(d);
}},this);if(this.images.length){document.id(this.container).addEvent("scroll",this.scrolling.bind(this));}var a=this.container;},checkPosition:function(b){var a=b.getPosition(),d=this.options.offset;
var c={size:this.container.getSize(),scroll:this.container.getScroll(),scrollSize:this.container.getScrollSize()};return((a.y>=c.scroll.y-d.y)&&(a.y<=c.scroll.y+this.dimensions.size.y+d.y));
},scrolling:function(b){var a=this;if(!this.images||!this.init){this.init=1;return;}this.images.each(function(c){if(typeof c=="undefined"){return;}if(this.checkPosition(c)&&this.storage.get(c.getProperty("smartload"))){var d=this.storage.get(c.getProperty("smartload"));
new Asset.image(d.src,{onload:function(){var e={width:d.width,height:d.height};if(e.width&&!e.height){e.height=e.width;}if(!e.width&&e.height){e.width=e.height;
}if(!e.width&&!e.height){e.width=this.width;e.height=this.height;}if(e.width!=this.width&&e.height==this.height){e.width=this.width;}else{if(e.width==this.width&&e.height!=this.height){e.height=this.height;
}}d.fx.start("opacity",0).chain(function(){c.setProperty("width",e.width).setProperty("height",e.height);c.setProperty("src",d.src).removeClass("spinner");
this.start("opacity",1);});a.images.erase(c);a.storage.erase(c.getProperty("smartload"));}});}},this);}});