/*
 * http://kevin.vanzonneveld.net
 * +     original by: Arpad Ray (mailto:arpad@php.net)
 * +     improved by: Pedro Tainha (http://www.pedrotainha.com)
 * +     bugfixed by: dptr1988
 * +      revised by: d3x
 * +     improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 * +        input by: Brett Zamir (http://brett-zamir.me)
 * +     improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 * +     improved by: Chris
 * +     improved by: James
 * +        input by: Martin (http://www.erlenwiese.de/)
 * +     bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 * +     improved by: Le Torbi
 * +     input by: kilops
 * +     bugfixed by: Brett Zamir (http://brett-zamir.me)
 * -      depends on: utf8_decode
 * %            note: We feel the main purpose of this function should be to ease the transport of data between php & js
 * %            note: Aiming for PHP-compatibility, we have to translate objects to arrays
 * *       example 1: unserialize('a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}');
 * *       returns 1: ['Kevin', 'van', 'Zonneveld']
 * *       example 2: unserialize('a:3:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";s:7:"surName";s:9:"Zonneveld";}');
 * *       returns 2: {firstName: 'Kevin', midName: 'van', surName: 'Zonneveld'}
 */
String.implement({unserialize:function(){var k=this;var n=this;var i=function(b){var a=b.charCodeAt(0);if(a<128){return 0;}if(a<2048){return 1;}return 2;
};var h=function(b,a,c,d){return;};var j=function(b,f,a){var e=[];var c=b.slice(f,f+1);var d=2;while(c!=a){if((d+f)>b.length){h("Error","Invalid");}e.push(c);
c=b.slice(f+(d-1),f+d);d+=1;}return[e.length,e.join("")];};var l=function(a,f,b){var e;e=[];for(var d=0;d<b;d++){var c=a.slice(f+(d-1),f+d);e.push(c);b-=i(c);
}return[e.length,e.join("")];};var m=function(a,J){var b;var L;var D=0;var I;var M;var e;var F;if(!J){J=0;}var c=(a.slice(J,J+1)).toLowerCase();var G=J+2;
var d=function(o){return o;};switch(c){case"i":d=function(o){return parseInt(o,10);};L=j(a,G,";");D=L[0];b=L[1];G+=D+1;break;case"b":d=function(o){return parseInt(o,10)!==0;
};L=j(a,G,";");D=L[0];b=L[1];G+=D+1;break;case"d":d=function(o){return parseFloat(o);};L=j(a,G,";");D=L[0];b=L[1];G+=D+1;break;case"n":b=null;break;case"s":I=j(a,G,":");
D=I[0];M=I[1];G+=D+2;L=l(a,G+1,parseInt(M,10));D=L[0];b=L[1];G+=D+2;if(D!=parseInt(M,10)&&D!=b.length){h("SyntaxError","String length mismatch");}b=utf8_decode(b);
break;case"a":b={};e=j(a,G,":");D=e[0];F=e[1];G+=D+2;for(var f=0;f<parseInt(F,10);f++){var K=m(a,G);var C=K[1];var E=K[2];G+=C;var g=m(a,G);var H=g[1];
var B=g[2];G+=H;b[E]=B;}G+=1;break;default:h("SyntaxError","Unknown / Unhandled data type(s): "+c);break;}return[c,G-J,d(b)];};return m((k+""),0)[2];}});
function serialize(p){var k=function(e){var f=typeof e,c;var a;if(f=="object"&&!e){return"null";}if(f=="object"){if(!e.constructor){return"object";}var d=e.constructor.toString();
c=d.match(/(\w+)\(/);if(c){d=c[1].toLowerCase();}var b=["boolean","number","string","array"];for(a in b){if(d==b[a]){f=b[a];break;}}}return f;};var n=k(p);
var r,q="";switch(n){case"function":r="";break;case"boolean":r="b:"+(p?"1":"0");break;case"number":r=(Math.round(p)==p?"i":"d")+":"+p;break;case"string":p=utf8_encode(p);
r="s:"+encodeURIComponent(p).replace(/%../g,"x").length+':"'+p+'"';break;case"array":case"object":r="a";var o=0;var m="";var j;var l;for(l in p){q=k(p[l]);
if(q=="function"){continue;}j=(l.match(/^[0-9]+$/)?parseInt(l,10):l);m+=this.serialize(j)+this.serialize(p[l]);o++;}r+=":"+o+":{"+m+"}";break;case"undefined":default:r="N";
break;}if(n!="object"&&n!="array"){r+=";";}return r;}function utf8_decode(i){var n=[],l=0,j=0,k=0,m=0,h=0;i+="";while(l<i.length){k=i.charCodeAt(l);if(k<128){n[j++]=String.fromCharCode(k);
l++;}else{if((k>191)&&(k<224)){m=i.charCodeAt(l+1);n[j++]=String.fromCharCode(((k&31)<<6)|(m&63));l+=2;}else{m=i.charCodeAt(l+1);h=i.charCodeAt(l+2);n[j++]=String.fromCharCode(((k&15)<<12)|((m&63)<<6)|(h&63));
l+=3;}}}return n.join("");}function utf8_encode(r){var k=(r+"");var j="";var q,n;var p=0;q=n=0;p=k.length;for(var o=0;o<p;o++){var l=k.charCodeAt(o);var m=null;
if(l<128){n++;}else{if(l>127&&l<2048){m=String.fromCharCode((l>>6)|192)+String.fromCharCode((l&63)|128);}else{m=String.fromCharCode((l>>12)|224)+String.fromCharCode(((l>>6)&63)|128)+String.fromCharCode((l&63)|128);
}}if(m!==null){if(n>q){j+=k.substring(q,n);}j+=m;q=n=o+1;}}if(n>q){j+=k.substring(q,k.length);}return j;}