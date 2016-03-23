/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var RokSlider=new Class({Implements:[Events,Options],options:{onTick:function(a){if(this.options.snap){a=this.toPosition(this.step);}if(isNaN(a)){a=this.options.offset;
}this.knob.setStyle(this.property,a);},snap:false,offset:0,range:false,wheel:false,steps:100,mode:"horizontal"},initialize:function(e,a,d){this.setOptions(d);
this.element=document.id(e);this.knob=document.id(a);this.previousChange=this.previousEnd=this.step=-1;if(this.options.initialize){this.options.initialize.call(this);
}this.element.addEvent("mousedown",this.clickedElement.bind(this));if(this.options.wheel){this.element.addEvent("mousewheel",this.scrolledElement.bindWithEvent(this));
}var f,b={},c={x:false,y:false};switch(this.options.mode){case"vertical":this.axis="y";this.property="top";f="offsetHeight";break;case"horizontal":this.axis="x";
this.property="left";f="offsetWidth";}this.half=this.knob[f]/2;this.full=this.element[f]-this.knob[f]+(this.options.offset*2);this.min=this.options.range[0]!=null?this.options.range[0]:0;
this.max=this.options.range[1]!=null?this.options.range[1]:this.options.steps;this.range=this.max-this.min;this.steps=this.options.steps||this.full;this.stepSize=Math.abs(this.range)/this.steps;
this.stepWidth=Number((this.stepSize*this.full/Math.abs(this.range)).toFixed(4));if(isNaN(this.stepWidth)){this.stepWidth=this.full;}this.knob.setStyle("position","relative").setStyle(this.property,-this.options.offset);
c[this.axis]=this.property;b[this.axis]=[-this.options.offset,this.full+this.options.offset];this.drag=new Drag(this.knob,{snap:0,limit:b,modifiers:c,onDrag:function(){this.draggedKnob();
this.fireEvent("onDrag",[this.drag.value.now.x]);}.bind(this),onComplete:function(){this.draggedKnob();this.end();}.bind(this)});if(this.options.snap){this.drag.options.grid=(this.stepWidth);
this.drag.options.limit[this.axis][1]=this.full;}},set:function(a){if(!((this.range>0)^(a<this.min))){a=this.min;}if(!((this.range>0)^(a>this.max))){a=this.max;
}this.step=(a);this.checkStep();this.end(true);this.fireEvent("onTick",this.toPosition(this.step));return this;},clickedElement:function(c){var b=this.range<0?-1:1;
var a=c.page[this.axis]-this.element.getPosition()[this.axis]-this.half;a=a.limit(-this.options.offset,this.full-this.options.offset);this.step=(this.min+b*this.toStep(a));
this.checkStep();this.end();this.fireEvent("onTick",a);},scrolledElement:function(a){var b=(this.options.mode=="horizontal")?(a.wheel<0):(a.wheel>0);this.set(b?this.step-this.stepSize:this.step+this.stepSize);
a.stop();},draggedKnob:function(){var b=this.range<0?-1:1;var a=this.drag.value.now[this.axis];a=a.limit(-this.options.offset,this.full-this.options.offset);
this.step=(this.min+b*this.toStep(a));this.checkStep();},checkStep:function(){this.previousChange=this.step;this.fireEvent("onChange",this.step);},end:function(a){if(this.previousEnd!==this.step){this.previousEnd=this.step;
}if(!a){this.fireEvent("onComplete",this.step+"");}},toStep:function(a){var b=(a+this.options.offset)*this.stepSize/this.full*this.steps;return this.options.steps?Math.round(b):b;
},toPosition:function(a){return(this.full*Math.abs(this.min-a))/(this.steps*this.stepSize)-this.options.offset;}});Drag.implement({drag:function(c){this.out=false;
this.mouse.now=c.page;for(var d in this.options.modifiers){if(!this.options.modifiers[d]){continue;}this.value.now[d]=this.mouse.now[d]-this.mouse.pos[d];
if(this.limit[d]){if(this.limit[d][1]!=null&&(this.value.now[d]>this.limit[d][1])){this.value.now[d]=this.limit[d][1];this.out=true;}else{if(this.limit[d][0]!=null&&(this.value.now[d]<this.limit[d][0])){this.value.now[d]=this.limit[d][0];
this.out=true;}}}var a=(this.value.now[d]-(this.limit[d][0]||0))%this.options.grid[d];var b=(a>this.options.grid[d]/2);if(this.options.grid[d]){this.value.now[d]=(b)?this.value.now[d]-a+this.options.grid[d]:this.value.now[d]-a;
}this.element.setStyle(this.options.modifiers[d],this.value.now[d]+this.options.unit);}this.fireEvent("onDrag",this.element);c.stop();}});