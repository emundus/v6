/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
Gantry.PresetsSaver={init:function(){var a=document.id("toolbar-save-preset");Gantry.PresetsSaver.bounds={show:Gantry.PresetsSaver.build.bind(Gantry.PresetsSaver)};
a.addEvent("click",function(b){b.stop();Gantry.Overlay.addEvent("show",Gantry.PresetsSaver.bounds.show);Gantry.Overlay.show();});Gantry.PresetsSaver.Template=$$("input[name=id]");
if(Gantry.PresetsSaver.Template.length){Gantry.PresetsSaver.Template=Gantry.PresetsSaver.Template[0].value;}else{Gantry.PresetsSaver.Template=false;}},build:function(){var i=new Element("div.presets-wrapper-table").inject(document.body);
var j=new Element("div.presets-wrapper-row").inject(i);var h=new Element("div.presets-wrapper-cell").inject(j);var b=new Element("div",{id:"presets-namer","class":"gantry-layer-wrapper"}).inject(h);
var f=new Element("h2").set("text",GantryLang.preset_title).inject(b);var d=new Element("div",{"class":"preset-namer-inner"}).inject(b);Gantry.PresetsSaver.wrapper=b;
Gantry.PresetsSaver.innerWrapper=d;var e=new Element("p").set("text",GantryLang.preset_select).inject(d),g;var c=new Hash(Presets);c.each(function(s,u){var q=new Element("div",{"class":"preset-namer valid-preset-"+u}).inject(d);
var r=new Element("h3",{"class":"preset-namer-title"}).set("html",GantryLang.preset_naming+' "<span>'+u+'</span>"').inject(q);if(c.length>1){g=new Element("span",{"class":"skip"}).set("text",GantryLang.preset_skip).inject(r);
}var m=new Element("div").set("html","<label><span>"+GantryLang.preset_name+'</span><input type="text" class="text-long input-name" id="'+u+'_namer_name" /></label>').inject(q);
var n=new Element("div").set("html","<label><span>"+GantryLang.key_name+'</span><input type="text" class="text-long input-key example" tabindex="-1" id="'+u+'_namer_key" /></label>').inject(q);
var k=m.getElement("input"),p=n.getElement("input");Gantry.PresetsSaver.valExample="ex, Preset 1";Gantry.PresetsSaver.keyExample="(optional) ex, preset1";
var t=Gantry.PresetsSaver.valExample,o=Gantry.PresetsSaver.keyExample;k.addClass("example").value=t;p.value=o;k.addEvents({focus:function(){if(this.value==t){this.value="";
}this.removeClass("example");},blur:function(){if(this.value==""){this.addClass("example").value=t;}Gantry.PresetsSaver.checkInputs();},keyup:function(){this.value=this.value.replace(/[^a-z0-9\s]/gi,"");
if(this.value.length){p.value=this.value.toLowerCase().clean().replace(/\s/g,"");}Gantry.PresetsSaver.checkInputs();}});p.addEvents({focus:function(){if(this.value==o){this.value="";
}this.removeClass("example");},blur:function(){if(this.value==""&&(k.value!=""&&k.value!=t)){this.value=k.value.toLowerCase().clean().replace(/\s/g,"");
}else{if(this.value==""){this.value=o;}}this.addClass("example");Gantry.PresetsSaver.checkInputs();},keyup:function(v){this.value=this.value.replace(/[^a-z0-9\s]/gi,"");
this.value=this.value.toLowerCase().clean().replace(/\s/g,"");Gantry.PresetsSaver.checkInputs();}});if(c.getLength()>1){var l=new Fx.Morph(q,{duration:200,onComplete:function(){q.empty().dispose();
Gantry.PresetsSaver.checkInputs();}}).set({opacity:1});g.addEvent("click",function(){k.removeEvents("focus").removeEvents("blur").removeEvents("keyup");
p.removeEvents("focus").removeEvents("blur").removeEvents("keyup");q.setStyle("overflow","hidden");l.start({opacity:0,height:0});});}});GantryLang.save=GantryLang.save.toLowerCase().capitalize();
GantryLang.cancel=GantryLang.cancel.toLowerCase().capitalize();GantryLang.close=GantryLang.close.toLowerCase().capitalize();GantryLang.retry=GantryLang.retry.toLowerCase().capitalize();
var a=new Element("div",{"class":"preset-bottom"}).inject(b);Gantry.PresetsSaver.savePreset=new Element("div",{"class":"rok-button rok-button-primary rok-button-disabled"}).set("text",GantryLang.save).inject(a);
Gantry.PresetsSaver.cancel=new Element("div",{"class":"rok-button"}).set("text",GantryLang.cancel).inject(a);Gantry.PresetsSaver.savePreset.addClass("rok-button-primary");
Gantry.PresetsSaver.cancel.addEvent("click",function(k){Gantry.Overlay.removeEvent("show",Gantry.PresetsSaver.bounds.show);i.empty().dispose();Gantry.Overlay.hide();
});Gantry.PresetsSaver.savePreset.addEvent("click",Gantry.PresetsSaver.save);},checkInputs:function(){var c=[],b=Gantry.PresetsSaver.wrapper.getElements("input");
b.each(function(d,e){if(d.value!=""&&d.value!=Gantry.PresetsSaver[(!e%2)?"valExample":"keyExample"]){c[e]=true;}else{c[e]=false;}});var a=c.contains(false);
if(a||!b.length){Gantry.PresetsSaver.savePreset.addClass("rok-button-disabled");}else{Gantry.PresetsSaver.savePreset.removeClass("rok-button-disabled");
}return a;},save:function(){if(!Gantry.PresetsSaver.checkInputs||Gantry.PresetsSaver.savePreset.hasClass("rok-button-disabled")){return;}var a=[];Gantry.PresetsSaver.wrapper.getElements(".preset-namer").each(function(c){a.push(c.getElements("input"));
});Gantry.PresetsSaver.data=Gantry.PresetsSaver.getPresets(a);var b=Gantry.PresetsSaver.data;new Request.HTML({url:GantryAjaxURL,onSuccess:Gantry.PresetsSaver.handleResponse}).post({model:"presets-saver",action:"add","presets-data":JSON.encode(b)});
},handleResponse:function(j,c,e){var b=Gantry.PresetsSaver.wrapper,i=Gantry.PresetsSaver.innerWrapper;var g,h,d;if(e.clean()=="success"){$H(Gantry.PresetsSaver.data).each(function(l,k){$H(l).each(function(m,o){var n=m.name;
PresetDropdown.newItem(k,o,n);delete m.name;Presets[k].set(n,m);});});Gantry.PresetsSaver.cancel.fireEvent("click");growl.alert("Gantry",GantryLang.success_msg,{duration:5000});
}else{i.setStyle("display","none");var f=new Element("div",{"class":"preset-error"}).inject(i,"after");g=new Element("div",{"class":"error-icon"}).inject(f);
h=new Element("div").set("html","<h3>"+GantryLang.fail_save+"</h3>").inject(f);d=new Element("div").set("html",GantryLang.fail_msg).inject(f);var a=Gantry.PresetsSaver.savePreset.clone();
Gantry.PresetsSaver.savePreset.setStyle("display","none");a.inject(Gantry.PresetsSaver.savePreset,"before").set("html",GantryLang.retry).addEvent("click",function(){f.empty().dispose();
i.setStyle("display","block");Gantry.PresetsSaver.savePreset.setStyle("display","");a.dispose();});}},center:function(d){var c=window.getSize();var a=d.getSize();
var b={left:(c.x/2)+window.getScroll().x-a.x/2,top:(c.y/2)+window.getScroll().y-a.y/2};d.setStyles(b);},getPresets:function(c){var b=new Hash(Presets);
var e=1,d=0;var a={};b.each(function(g,f){if(!Gantry.PresetsSaver.wrapper.getElement(".valid-preset-"+f)){return;}var h=b.get(f);a[f]={};a[f][c[d][1].value]={};
a[f][c[d][1].value].name=c[d][0].value;e=1;h.each(function(i,j){i=new Hash(i);if(e>1){return;}else{i.each(function(n,m){var l=m.replace(/-/g,"_"),k=m.replace(/_/g,"-");
if(document.id(GantryParamsPrefix+l)){a[f][c[d][1].value][k]=document.id(GantryParamsPrefix+l).get("value")||"";}});}e++;});d++;});return a;}};window.addEvent("domready",Gantry.PresetsSaver.init);
