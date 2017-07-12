// http://kevin.vanzonneveld.net
// +     original by: Arpad Ray (mailto:arpad@php.net)
// +     improved by: Pedro Tainha (http://www.pedrotainha.com)
// +     bugfixed by: dptr1988
// +      revised by: d3x
// +     improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +        input by: Brett Zamir (http://brett-zamir.me)
// +     improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +     improved by: Chris
// +     improved by: James
// +        input by: Martin (http://www.erlenwiese.de/)
// +     bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +     improved by: Le Torbi
// +     input by: kilops
// +     bugfixed by: Brett Zamir (http://brett-zamir.me)
// -      depends on: utf8_decode
// %            note: We feel the main purpose of this function should be to ease the transport of data between php & js
// %            note: Aiming for PHP-compatibility, we have to translate objects to arrays
// *       example 1: unserialize('a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}');
// *       returns 1: ['Kevin', 'van', 'Zonneveld']
// *       example 2: unserialize('a:3:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";s:7:"surName";s:9:"Zonneveld";}');
// *       returns 2: {firstName: 'Kevin', midName: 'van', surName: 'Zonneveld'}


String.implement({unserialize:function(){var f=this;var c=this;var a=function(h){var i=h.charCodeAt(0);if(i<128){return 0;}if(i<2048){return 1;}return 2;};var b=function(j,k,i,h){return;};var g=function(l,n,m){var h=[];var k=l.slice(n,n+1);var j=2;while(k!=m){if((j+n)>l.length){b("Error","Invalid");}h.push(k);k=l.slice(n+(j-1),n+j);j+=1;}return[h.length,h.join("")];};var e=function(m,n,l){var h;h=[];for(var j=0;j<l;j++){var k=m.slice(n+(j-1),n+j);h.push(k);l-=a(k);}return[h.length,h.join("")];};var d=function(z,l){var y;var j;var q=0;var m;var h;var v;var p;if(!l){l=0;}var x=(z.slice(l,l+1)).toLowerCase();var o=l+2;var w=function(i){return i;};switch(x){case"i":w=function(i){return parseInt(i,10);};j=g(z,o,";");q=j[0];y=j[1];o+=q+1;break;case"b":w=function(i){return parseInt(i,10)!==0;};j=g(z,o,";");q=j[0];y=j[1];o+=q+1;break;case"d":w=function(i){return parseFloat(i);};j=g(z,o,";");q=j[0];y=j[1];o+=q+1;break;case"n":y=null;break;case"s":m=g(z,o,":");q=m[0];h=m[1];o+=q+2;j=e(z,o+1,parseInt(h,10));q=j[0];y=j[1];o+=q+2;if(q!=parseInt(h,10)&&q!=y.length){b("SyntaxError","String length mismatch");}y=utf8_decode(y);break;case"a":y={};v=g(z,o,":");q=v[0];p=v[1];o+=q+2;for(var u=0;u<parseInt(p,10);u++){var k=d(z,o);var r=k[1];var A=k[2];o+=r;var t=d(z,o);var n=t[1];var s=t[2];o+=n;y[A]=s;}o+=1;break;default:b("SyntaxError","Unknown / Unhandled data type(s): "+x);break;}return[x,o-l,w(y)];};return d((f+""),0)[2];}});function serialize(c){var h=function(o){var n=typeof o,k;var m;if(n=="object"&&!o){return"null";}if(n=="object"){if(!o.constructor){return"object";}var j=o.constructor.toString();k=j.match(/(\w+)\(/);if(k){j=k[1].toLowerCase();}var l=["boolean","number","string","array"];for(m in l){if(j==l[m]){n=l[m];break;}}}return n;};var e=h(c);var a,b="";switch(e){case"function":a="";break;case"boolean":a="b:"+(c?"1":"0");break;case"number":a=(Math.round(c)==c?"i":"d")+":"+c;break;case"string":c=utf8_encode(c);a="s:"+encodeURIComponent(c).replace(/%../g,"x").length+':"'+c+'"';break;case"array":case"object":a="a";var d=0;var f="";var i;var g;for(g in c){b=h(c[g]);if(b=="function"){continue;}i=(g.match(/^[0-9]+$/)?parseInt(g,10):g);f+=this.serialize(i)+this.serialize(c[g]);d++;}a+=":"+d+":{"+f+"}";break;case"undefined":default:a="N";break;}if(e!="object"&&e!="array"){a+=";";}return a;}function utf8_decode(a){var c=[],e=0,g=0,f=0,d=0,b=0;a+="";while(e<a.length){f=a.charCodeAt(e);if(f<128){c[g++]=String.fromCharCode(f);e++;}else{if((f>191)&&(f<224)){d=a.charCodeAt(e+1);c[g++]=String.fromCharCode(((f&31)<<6)|(d&63));e+=2;}else{d=a.charCodeAt(e+1);b=a.charCodeAt(e+2);c[g++]=String.fromCharCode(((f&15)<<12)|((d&63)<<6)|(b&63));e+=3;}}}return c.join("");}function utf8_encode(a){var h=(a+"");var i="";var b,e;var c=0;b=e=0;c=h.length;for(var d=0;d<c;d++){var g=h.charCodeAt(d);var f=null;if(g<128){e++;}else{if(g>127&&g<2048){f=String.fromCharCode((g>>6)|192)+String.fromCharCode((g&63)|128);}else{f=String.fromCharCode((g>>12)|224)+String.fromCharCode(((g>>6)&63)|128)+String.fromCharCode((g&63)|128);}}if(f!==null){if(e>b){i+=h.substring(b,e);}i+=f;b=e=d+1;}}if(e>b){i+=h.substring(b,h.length);}return i;}