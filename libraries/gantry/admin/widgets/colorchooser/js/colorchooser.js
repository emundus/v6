/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var GantryColorChooser={add:function(c,b){var e=c.replace(/-/,"_"),a;if(!window.moorainbow){window.moorainbow={};}var d=function(){var f=document.id(c);
f.getParent().removeEvent("mouseenter",d);a=new MooRainbow("myRainbow_"+c+"_input",{id:"myRainbow_"+c,startColor:document.id(c).get("value").hexToRgb(true)||[255,255,255],imgPath:GantryURL+"/admin/widgets/colorchooser/images/",transparent:b,onChange:function(g){if(g=="transparent"){f.getNext().getFirst().addClass("overlay-transparent").setStyle("background-color","transparent");
f.value="transparent";}else{f.getNext().getFirst().removeClass("overlay-transparent").setStyle("background-color",g.hex);f.value=g.hex;}if(this.visible){this.okButton.focus();
}}});window.moorainbow["r_"+e]=a;a.okButton.setStyle("outline","none");document.id("myRainbow_"+c+"_input").addEvent("click",function(){(function(){a.okButton.focus();
}).delay(10);});f.addEvent("keyup",function(g){if(g){g=new Event(g);}if((this.value.length==4||this.value.length==7)&&this.value[0]=="#"){var j=new Color(this.value);
var k=this.value;var h=j.rgbToHsb();var i={hex:k,rgb:j,hsb:h};a.fireEvent("onChange",i);a.manualSet(i.rgb);}}).addEvent("set",function(g){this.value=g;
this.fireEvent("keyup");});f.getNext().getFirst().setStyle("background-color",a.sets.hex);GantryColorChooser.load("myRainbow_"+c);};if(e.contains("gradient")&&(e.contains("from")||e.contains("to"))){d();
}else{window.addEvent("domready",function(){document.id(c).getParent().addEvents({mouseenter:d,mouseleave:function(){this.removeEvent("mouseenter",d);}});
});}},load:function(a,b){if(b){document.id(a+"_input").getPrevious().value=b;document.id(a+"_input").getFirst().setStyle("background-color",b);}}};