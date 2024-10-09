/**
 * Panel Element
 *
 * @copyright: Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */ define(["jquery","fab/element"],function(e,t){return window.FbPanel=new Class({Extends:t,initialize:function(e,t){this.setPlugin("panel"),this.parent(e,t)},update:function(e){if(this.getElement()){let t=this.element.querySelector('span[id*="-value"]');t&&(t.innerHTML=e)}},cloneUpdateIds:function(e){this.element=document.id(e),this.options.element=e;let t=document.querySelector("#"+e+' div[id*="-content"]');t&&(t.id=e+"-content");let n=document.querySelector("#"+e+' span[id*="-value"]');n&&(n.id=e+"-value")}}),window.FbPanel});