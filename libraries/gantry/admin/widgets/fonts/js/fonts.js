/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){var a=this.GantryFonts={init:function(b){this.data=b;this.element=document.id(b.param);this.element.store("g4:fonts:value",this.element.get("value"));
Object.each(b.paths,function(c,d){this.load(d,c.delim,b.baseurl+c.json);},this);},load:function(i,g,e){var b=new Element("optgroup",{label:i}).inject(this.element),h=new Element("option",{value:"-1",text:"Loading..."}).inject(b,"top"),f,c,d;
new Request.JSON({url:e,method:"get",onSuccess:function(o){for(var q=0,m=o.items.length;q<m;q++){f=o.items[q].family;c=":";for(var p=0,n=o.items[q].variants.length;
p<n;p++){if(p<n-1){c=c+o.items[q].variants[p]+",";}else{c=c+o.items[q].variants[p];}}d=new Element("option",{text:f,value:g+f+c}).inject(b);}h.dispose();
this.validate();if(typeof jQuery!="undefined"){jQuery("#"+this.data.param).trigger("liszt:updated");}}.bind(this),onError:function(k,j){h.set("text","Error("+i+"): "+j);
}}).send();},validate:function(){var c=this.element.get("data-value");if(c.contains(":")){return this.element.set("value",c);}var b=this.element.getElement("[value$=:"+c+"]");
this.element.set("value",(b?b:this.element.getElement("option")).get("value"));}};})());