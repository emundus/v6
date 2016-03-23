/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
(function(){var a=this.Toggle=new Class({Implements:[Options,Events],initialize:function(b){this.setOptions(b);this.container=document.id("g4-panels");
if(!this.container){return false;}this.attach();},attach:function(){var c=this.container.retrieve("g4:toggle:click",function(f){this._clearSelection.call(this);
this.click.call(this,f,f.target);}.bind(this)),e=this.container.retrieve("g4:toggle:dblclick",function(f){this._clearSelection.call(this,f);}.bind(this)),b=this.container.retrieve("g4:toggle:attach",function(f){this.enable.call(this,f.target);
}.bind(this)),d=this.container.retrieve("g4:toggle:detach",function(f){this.disable.call(this,f.target);}.bind(this));this.container.addEvents({"click:relay(.toggle)":c,"attach:relay(.toggle-input)":b,"detach:relay(.toggle-input)":d});
},detach:function(c){var b=this.container.retrieve("g4:toggle:click"),e=this.container.retrieve("g4:toggle:dblclick"),d=this.container.retrieve("g4:toggle:detach");
this.container.removeEvents({"click:relay(.toggle)":b,"detach:relay(.toggle-input)":d});},click:function(d,c){if(c.retrieve("g4:toggle:disabled")){return;
}var b=c.getElement("input.toggle-input"),e=b.get("value");if(!b.get("value")||b.get("value")=="0"){c.removeClass("toggle-off").addClass("toggle-on");b.set("value",1);
}else{c.removeClass("toggle-on").addClass("toggle-off");b.set("value",0);}b.fireEvent("change",e);},set:function(c,b){console.log("set",c.target,b);},enable:function(c){var b=c.getParent(".toggle");
b.removeClass("disabled");b.store("g4:toggle:disabled",false);},disable:function(c){var b=c.getParent(".toggle");b.addClass("disabled");b.store("g4:toggle:disabled",true);
},_chainSwitch:function(c,e){var b=c.getParent(".chain");if(!b||c.getParent(".wrapper .chain")!=b){return false;}var d=b.getAllNext(".chain input, .chain select");
d.each(function(f){if(f.hasClass("toggle-input")){this.container.fireEvent((e=="0"?"detach":"attach")+":relay(.toggle-input)",{target:f});}},this);},_clearSelection:function(){if(document.selection&&document.selection.empty){document.selection.empty();
}else{if(window.getSelection){var b=window.getSelection();b.removeAllRanges();}}}});window.addEvent("domready",function(){new this.Toggle();});})();