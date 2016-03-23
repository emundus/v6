/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){var a=new Class({Implements:[Options,Events],options:{url:null},initialize:function(b){this.setOptions(b);this.request=new Request({url:this.options.url});
this.attach();},attach:function(){var b=document.retrieve("gantry:ajaxbutton",function(d,c){this.click.call(this,d,c);}.bind(this));document.addEvent("click:relay([data-ajaxbutton])",b);
},detach:function(){var b=document.retrieve("gantry:ajaxbutton");document.removeEvent("click:relay([data-ajaxbutton])",b);},click:function(d,c){if(d){d.preventDefault();
}var e=JSON.decode(c.get("data-ajaxbutton")),g={model:e.model,action:e.action},f=function(h){growl.alert("Gantry",h,{duration:6000});this.request.removeEvents(b);
}.bind(this),b={onSuccess:f};this.request.addEvents(b).post(g);}});window.addEvent("domready",function(){(typeof Gantry!="undefined"?Gantry:this)["AjaxButtons"]=new a({url:GantryAjaxURL});
});})());