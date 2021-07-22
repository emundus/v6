/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2012 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var GantryColorChooser={add:function(h,i){var f=h.replace(/-/,"_"),j;if(!window.moorainbow){window.moorainbow={};}var g=function(){var a=document.id(h);
a.getParent().removeEvent("mouseenter",g);j=new MooRainbow("myRainbow_"+h+"_input",{id:"myRainbow_"+h,startColor:document.id(h).get("value").hexToRgb(true)||[255,255,255],imgPath:GantryURL+"/admin/widgets/colorchooser/images/",transparent:i,onChange:function(b){if(b=="transparent"){a.getNext().getFirst().addClass("overlay-transparent").setStyle("background-color","transparent");
a.value="transparent";}else{a.getNext().getFirst().removeClass("overlay-transparent").setStyle("background-color",b.hex);a.value=b.hex;}if(this.visible){this.okButton.focus();
}}});window.moorainbow["r_"+f]=j;j.okButton.setStyle("outline","none");document.id("myRainbow_"+h+"_input").addEvent("click",function(){(function(){j.okButton.focus();
}).delay(10);});a.addEvent("keyup",function(b){if(b){b=new Event(b);}if((this.value.length==4||this.value.length==7)&&this.value[0]=="#"){var d=new Color(this.value);
var c=this.value;var l=d.rgbToHsb();var e={hex:c,rgb:d,hsb:l};j.fireEvent("onChange",e);j.manualSet(e.rgb);}}).addEvent("set",function(b){this.value=b;
this.fireEvent("keyup");});a.getNext().getFirst().setStyle("background-color",j.sets.hex);GantryColorChooser.load("myRainbow_"+h);};if(f.contains("gradient")&&(f.contains("from")||f.contains("to"))){g();
}else{window.addEvent("domready",function(){document.id(h).getParent().addEvents({mouseenter:g,mouseleave:function(){this.removeEvent("mouseenter",g);}});
});}},load:function(d,c){if(c){document.id(d+"_input").getPrevious().value=c;document.id(d+"_input").getFirst().setStyle("background-color",c);}}};