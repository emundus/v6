/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){var a=this.G4PopupButtons=new Class({Implements:[Options,Events],options:{data:"g4"},initialize:function(b){this.setOptions(b);this.active=null;
this.attach();},attach:function(){var b={toggle:document.retrieve(this.options.data+":events:buttonstoggle",function(c,d){this.show.call(this,c,d);}.bind(this)),click:document.retrieve(this.options.data+":events:itemclick",function(c,d){this.hide.call(this,c,d);
}.bind(this))};document.addEvent("click:relay([data-"+this.options.data+"-toggle])",b.toggle);document.addEvent("click:relay([data-"+this.options.data+"-dropdown] >)",b.click);
},attachDocument:function(){if(document.retrieve(this.options.data+":attached:document")){return;}var b={document:document.retrieve(this.options.data+":events:hide",function(c,d){d=d||c.target;
this.hide.call(this);}.bind(this))};document.addEvent("click",b.document);document.store(this.options.data+":attached:document",true);},detachDocument:function(){if(!document.retrieve(this.options.data+":attached:document")){return;
}var b=document.retrieve(this.options.data+":events:hide");document.removeEvent("click",b);document.store(this.options.data+":attached:document",false);
},show:function(c,e){if(this.active==e){return;}this.hide();this.attachDocument();this.active=e;var d=this.active.get("data-"+this.options.data+"-toggle"),b=document.getElement("[data-"+this.options.data+'-dropdown="'+d+'"]');
this.active.addClass("rok-button-active");b.setStyle("display","block");},hide:function(){if(this.active){var c=this.active.get("data-"+this.options.data+"-toggle"),b=document.getElement("[data-"+this.options.data+'-dropdown="'+c+'"]');
this.active.removeClass("rok-button-active");b.setStyle("display","none");this.active=null;this.detachDocument();}}});this.g4PopupButtons=new a();})());
