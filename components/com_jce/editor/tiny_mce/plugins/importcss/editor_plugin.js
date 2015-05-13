/* JCE Editor - 2.4.6 | 19 January 2015 | http://www.joomlacontenteditor.net | Copyright (C) 2006 - 2014 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
(function(){var each=tinymce.each,PreviewCss=tinymce.util.PreviewCss,DOM=tinymce.DOM;function toAbsolute(u,p){return u.replace(/url\(["']?(.+?)["']?\)/gi,function(a,b){if(b.indexOf('://')<0){return'url("'+p+b+'")';}
return a;});}
tinymce.create('tinymce.plugins.ImportCSS',{populateStyleSelect:function(){var ed=this.editor;var self=this,styleselect=ed.controlManager.get('styleselect');if(!styleselect||styleselect.hasClasses){return;}
var counter=styleselect.getLength(),cls=this._import();if(cls.length===0){return;}
each(cls,function(o,idx){var name='style_'+(counter+idx),fmt;fmt={inline:'span',classes:o['class'],selector:'*'};ed.formatter.register(name,fmt);styleselect.add(o['class'],name,{style:function(){return PreviewCss(ed,fmt);}});});styleselect.hasClasses=true;},init:function(ed,url){this.editor=ed;var self=this;this.classes=[];this.fontface=[];ed.onPreInit.add(function(editor){var styleselect=ed.controlManager.get('styleselect');if(styleselect&&!styleselect.hasClasses&&ed.getParam('styleselect_stylesheet',true)){styleselect.onPostRender.add(function(ed,n){if(!styleselect.NativeListBox){DOM.bind(DOM.get(n.id+'_text'),'focus mousedown',self.populateStyleSelect,self);DOM.bind(DOM.get(n.id+'_open'),'focus mousedown',self.populateStyleSelect,self);}else{DOM.bind(DOM.get(n.id,'focus'),self.populateStyleSelect,self);}});}
var fontselect=ed.controlManager.get('fontselect');if(fontselect){fontselect.onPostRender.add(function(){if(!self.fontface){self._import();}});}});ed.onNodeChange.add(function(){var styleselect=ed.controlManager.get('styleselect');if(styleselect&&!styleselect.hasClasses&&ed.getParam('styleselect_stylesheet',true)){return self.populateStyleSelect();}});},_import:function(){var self=this,ed=this.editor,doc=ed.getDoc(),i,lo={},f=ed.settings.class_filter,ov,href='',rules=[],fontface=false,classes=false;function parseCSS(stylesheet){each(stylesheet.imports,function(r){if(r.href.indexOf('://fonts.googleapis.com')>0){var v='@import url('+r.href+');';if(tinymce.inArray(self.fontface,v)===-1){self.fontface.unshift(v);}
return;}
parseCSS(r);});try{rules=stylesheet.cssRules||stylesheet.rules;if(stylesheet.href){href=stylesheet.href.substr(0,stylesheet.href.lastIndexOf('/')+1);}}catch(e){}
each(rules,function(r){switch(r.type||1){case 1:if(!r.type){}
if(r.selectorText){each(r.selectorText.split(','),function(v){v=v.replace(/^\s*|\s*$|^\s\./g,"");if(/\.mce/.test(v)||!/\.[\w\-]+$/.test(v))
return;ov=v;v=tinymce._replace(/.*\.([a-z0-9_\-]+).*/i,'$1',v);if(f&&!(v=f(v,ov)))
return;if(!lo[v]){self.classes.push({'class':v});lo[v]=1;}});}
break;case 3:if(r.href.indexOf('//fonts.googleapis.com')>0){var v='@import url('+r.href+');';if(tinymce.inArray(self.fontface,v)===-1){self.fontface.unshift(v);}}
if(tinymce.isGecko&&r.href.indexOf('//')!=-1){return;}
parseCSS(r.styleSheet);break;case 5:if(r.cssText&&/(fontawesome|glyphicons|icomoon)/i.test(r.cssText)===false){var v=toAbsolute(r.cssText,href);if(tinymce.inArray(self.fontface,v)===-1){self.fontface.push(v);}}
break;}});classes=true;}
if((self.classes.length===0&&classes===false)||(self.fontface.length===0&&fontface===false)){try{each(doc.styleSheets,function(styleSheet){parseCSS(styleSheet);});}catch(ex){}}
if(self.fontface.length&&fontface===false){try{var head=DOM.doc.getElementsByTagName('head')[0];var style=DOM.create('style',{type:'text/css'});var css=self.fontface.join("\n");if(style.styleSheet){var setCss=function(){try{style.styleSheet.cssText=css;}catch(e){}};if(style.styleSheet.disabled){setTimeout(setCss,10);}else{setCss();}}else{style.appendChild(DOM.doc.createTextNode(css));}
head.appendChild(style);fontface=true;}catch(e){}}
return self.classes;}});tinymce.PluginManager.add('importcss',tinymce.plugins.ImportCSS);})();