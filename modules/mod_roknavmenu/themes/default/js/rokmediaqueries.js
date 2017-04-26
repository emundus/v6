/*
 * @version    $Id: rokmediaqueries.js 4586 2012-10-27 01:50:24Z btowles $
 * @author        RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){window.matchMedia=window.matchMedia||window.msMatchMedia||(function(e,f){var c,a=e.documentElement,b=a.firstElementChild||a.firstChild,d=e.createElement("body"),g=e.createElement("div");
g.id="mq-test-1";g.style.cssText="position:absolute;top:-100em";d.style.background="none";d.appendChild(g);return function(h){g.innerHTML='&shy;<style media="'+h+'"> #mq-test-1 { width: 42px; }</style>';
a.insertBefore(d,b);c=g.offsetWidth==42;a.removeChild(d);return{matches:c,media:h,addListener:function(i){if(!Browser.ie9&&!window.opera){return"";}if(window.retrieve("rokmediaqueries:listener:"+h.replace(/[a-z]|[(|)|:|\s|-]/gi,""),false)){return;
}window.store("rokmediaqueries:listener:"+h.replace(/[a-z]|[(|)|:|\s|-]/gi,""),true);window[window.addListener?"addListener":"attachEvent"]("resize",function(){var l={},k=0,j,m=false;
h.replace(/(\w+-?\w+)\s?:\s?(\d+){1,}/g,function(o,s,q,p,r,n){l[s]=q;k++;});if(!k){return;}else{if(k==1){j=window.getSize();m=false;Object.each(l,function(n,o){if(o=="min-width"){m+=j.x>=n;
}else{if(o=="max-width"){m+=j.x<=n;}else{if(o=="width"){m+=j.x==n;}}}});}else{if(k>1){j=window.getSize();m=true;Object.each(l,function(n,o){if(o=="min-width"){m*=j.x>=n;
}else{if(o=="max-width"){m*=j.x<=n;}else{if(o=="width"){m*=j.x==n;}}}});}}}if(m){return i.call(i,h);}});}};};})(document);})());((function(c,b){if(typeof RokMediaQueries!="undefined"){return;
}var a=new Class({Implements:[Events,Options],options:{queries:["(min-width: 1200px)","(min-width: 960px) and (max-width: 1199px)","(min-width: 768px) and (max-width: 959px)","(min-width: 481px) and (max-width: 767px)","(max-width: 480px)"]},initialize:function(d){this.setOptions(d);
this.queries=this.options.queries;this.queriesEvents={};this.timers=[];for(var e=this.queries.length-1;e>=0;e--){var f=c.matchMedia(this.queries[e]);f.addListener(this._fireEvent.bind(this,this.queries[e]));
this.queriesEvents[this.queries[e]]=[];}},on:function(f,d){if(f=="every"){for(var e=this.queries.length-1;e>=0;e--){this._addOnMatch(this.queries[e],d);
}}else{this._addOnMatch(f,d);}},add:function(d){if(!this.queries.contains(d)){var e;this.queries.push(d);e=c.matchMedia(d);e.addListener(this._fireEvent.bind(this,d));
}if(!this.queriesEvents[d]){this.queriesEvents[d]=[];}},getQuery:function(){var e="";for(var d=this.queries.length-1;d>=0;d--){if(c.matchMedia(this.queries[d]).matches){e=this.queries[d];
break;}}return e;},_fireEvent:function(e){if(!c.matchMedia(e).matches||!Object.getLength(this.queriesEvents)||!this.queriesEvents[e]){return;}for(var d=this.queriesEvents[e].length-1;
d>=0;d--){this.queriesEvents[e][d].delay(5,this,e);}},_addOnMatch:function(e,d){this.add(e);this.queriesEvents[e].push(d);}});c.RokMediaQueries=new a();
})(window,document));