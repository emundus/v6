/**
 * Facebook Display Element
 *
 * @copyright: Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */ define(["jquery","fab/element"],function(e,n){return window.FbPanel=new Class({Extends:n,initialize:function(e,n){this.setPlugin("panel"),this.parent(e,n)},update:function(e){this.getElement()&&(this.element.innerHTML=e)}}),window.FbPanel});