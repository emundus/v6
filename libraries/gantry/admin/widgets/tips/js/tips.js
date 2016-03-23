/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var GantryTips={init:function(){var a=document.getElements(".gantrytips");if(!a){return;}a.each(function(c,e){var d=c.getElements(".gantrytips-controller .gantrytips-left, .gantrytips-controller .gantrytips-right");
var g=c.getElement(".current-tip");var f=g.get("html").toInt();var b=c.getElements(".gantrytips-tip");b.each(function(j,h){j.set("display",(h==f-1)?"block":"none");
});d.addEvents({click:function(){var i=this.hasClass("gantrytips-left");var h=f;if(i){f-=1;if(f<=0){f=b.length;}}else{f+=1;if(f>b.length){f=1;}}this.fireEvent("jumpTo",[f,h]);
},jumpTo:function(i,h){if(!h){h=f;}f=i;if(!b[f-1]||!b[h-1]){return;}b.setStyle("display","none");b[f-1].setStyle("display","block");g.set("text",f);},jumpById:function(i,h){if(!h){h=f;
}f=b.indexOf(document.id(i))||0;if(f==-1){return;}b.setStyle("display","none");b[f].setStyle("display","block");f+=1;g.set("text",f);},selectstart:function(h){h.stop();
}});d[0].fireEvent("jumpTo",1);d[1].fireEvent("jumpTo",1);});},pins:function(a){a.each(function(c,d){var b=c.getParent(".gantry-panel").getElements(".gantry-panel-left, .gantry-panel-right");
var e={left:0,right:0};b.each(function(f,h){var g=f.getSize().y;e[(!h)?"left":"right"]=g;});c.store("surround",{panels:b,sizes:e,parent:c.getParent(".tips-field")});
if(e.left<=e.right+50){c.setStyle("display","none");}else{GantryTips.attachPin(c);}});},attachPin:function(a){if(!window.retrieve("pinAttached")){window.store("pinAttached",true);
}a.addEvents({click:function(){var b=a.retrieve("surround").parent;a.toggleClass("active");if(a.hasClass("active")){b.setStyles({top:b.getPosition().y-window.getScroll().y});
}b.toggleClass("fixed");},dbclick:function(b){b.stop();},selectstart:function(b){b.stop();}});}};window.addEvent("domready",GantryTips.init);