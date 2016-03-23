/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var GantrySliders={add:function(h,d,b,g){var c=h.replace(/-/,"_"),e=document.id(c+"-wrapper").getElement(".slider"),a=document.id(c+"-wrapper").getElement(".knob"),f=document.id(h);
if(!window.sliders){window.sliders={};}f.addEvents({set:function(k){var j=window.sliders[c];var i=j.list.indexOf(k);j.set(i).fireEvent("onComplete");}});
window.sliders[c]=new RokSlider(e,a,{steps:b,snap:true,initialize:function(){this.hiddenEl=f;},onComplete:function(){this.knob.removeClass("down");},onDrag:function(i){this.element.getFirst().setStyle("width",i+10);
},onChange:function(i){f.setProperty("value",this.list[i]);},onTick:function(i){if(this.options.snap){i=this.toPosition(this.step);}this.knob.setStyle(this.property,i);
this.fireEvent("onDrag",i);}});window.sliders[c].list=d;window.sliders[c].set(g);a.addEvents({mousedown:function(){this.addClass("down");},mouseup:function(){this.removeClass("down");
}});}};