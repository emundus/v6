
        (function(){
var dojo = odojo;

var dijit = odijit;

var dojox = odojox;

dojo.declare("OfflajnSkin", null, {
	constructor: function(args) {
    dojo.mixin(this,args);
    this.init();
    if(this.hidden.changeSkin){
      this.hidden.changeSkin();
      this.hidden.changeSkin = null;
    }
    if(window[this.name+'delay'] == true){
      window[this.name+'delay'] == false;
      this.hidden.value = this.hidden.options[1].value;
      this.changeSkin();
    }
  },
  
  init: function() {
    this.hidden = dojo.byId(this.id);
    //this.span = dojo.create("span", {style: "margin-left: 10px; position: absolute;"}, this.hidden.parentNode.parentNode, "last");
    this.span = dojo.create("span", {style: "margin-left: 10px;"}, this.hidden.parentNode.parentNode, "last");
    this.c = dojo.connect(this.hidden, 'onchange', this, 'changeSkin');  
  },
  
  changeSkin: function() {
    if(this.hidden.value != 'custom'){
      this.changeSkinNext();
      this.hidden.value = 'custom';
      this.fireEvent(this.hidden, 'change');
    }
  },
  
  changeSkinNext: function() {
    var value = this.hidden.value;
    var def = this.data[value];
    for (var k in def) {
      var p = dojo.byId(this.control + k);
      
      if(!p) {
        var n = this.id.replace(this.name, '');
        p = dojo.byId(n + k);        
      }
      if(p) {
        var v = def[k];
        if(v.indexOf("**") >= 0){
            var newv = v.split('|*|');
            var oldv = p.value.split('|*|');
            for(var i = 0; i < oldv.length; i++){
                if(newv[i] != '**'){
                    oldv[i] = newv[i];
                }
            }
            v = oldv.join('|*|');
        }else if(v.length > 0 && v.indexOf("{") == 0){
          var orig = {};
          if(p.value.length > 0 && p.value.indexOf("{") == 0){
            orig = dojo.fromJson(p.value);
          }
          var newValues = dojo.fromJson(v);
          for(var key in newValues){
            if(!orig[key]) orig[key] = {};
            for(var key2 in newValues[key]){
              orig[key][key2] = newValues[key][key2];
            }
          }
          v = dojo.toJson(orig);
        }
        p.value = v;
        this.fireEvent(p, 'change');
      }
    } 
    this.span.innerHTML = "The <b>"+value.replace(/_/g," ").replace("default2","default")+" skin</b> parameters has been set.";
        
    if(this.dependency){
      window[this.dependency+'delay'] = true;
    }
  },
 
   fireEvent: function(element,event){
    if ((document.createEventObject && !dojo.isIE) || (document.createEventObject && dojo.isIE && dojo.isIE < 9)){
      var evt = document.createEventObject();
      return element.fireEvent('on'+event,evt);
    }else{
      var evt = document.createEvent("HTMLEvents");
      evt.initEvent(event, true, true );
      return !element.dispatchEvent(evt);
    }
  }
});


dojo.declare("OfflajnList", null, {
	constructor: function(args) {
    this.fireshow = 0;
    this.map = {};
    this.list = new Array;
	  dojo.mixin(this,args);
    this.showed = 0;
    this.focus = 0;
    this.zindex = 6;
    window.offlajnlistzindex = 10;
    if(this.height) this.height++;
    this.lineHeight = 20;
    this.init();
  },
  
  init: function() { 
    this.hidden = dojo.byId(this.name);
    this.active = this.hidden;
    
    this.hidden.listobj = this;
    this.hidden.options = this.options;
    this.hidden.selectedIndex = this.selectedIndex;

    dojo.connect(this.hidden, 'onchange', this, 'setValue');
    this.change = 0;
    
    this.container = dojo.byId('offlajnlistcontainer' + this.name);
    dojo.style(this.container, 'minWidth', Math.ceil(dojo.style(this.container, 'width')+1)+'px');
    if(dojo.isIE == 7) {
      var span = dojo.query('#offlajnlistcontainer' + this.name + ' span');
      dojo.style(this.container, 'width', dojo.style(span[0], 'width')+30+'px');
    }
    this.offlajnlist = dojo.query('.offlajnlist', this.container)[0];
    
    this.currentText = dojo.query('.offlajnlistcurrent', this.container)[0];
    dojo.connect(this.container, 'onclick', this, 'controller');
    this.options.forEach(function(o, i){
      this.map[o.value] = i;
    },this);
  },
  
  initSelectBox: function(){
    if(this.selectbox) return;
    
    this.selectbox = dojo.create('div', {'id': 'offlajnlistelements' + this.name, 'class': 'offlajnlistelements', 'innerHTML': this.elements }, this.container, "after");
    this.list = dojo.query('.listelement', this.selectbox);
    
    
    this.list.connect('onmouseenter', this, 'addActive');
    
    dojo.style(this.selectbox, {
      opacity: 0,
      display: 'block'
    });
    
    this.lineHeight = dojo.position(this.list[0]).h;
    dojo.style(this.selectbox, {
      height: (this.height) ? this.height * this.lineHeight + 'px' : 'auto'
    });
    
    if(this.height) {
      this.content = dojo.query('#offlajnlistelements' + this.name + ' .content')[0];
      dojo.style(this.content, 'height', this.list.length * this.lineHeight + 'px');
      this.scrollbar = new OfflajnScroller({
        'extraClass': 'single-select',
        'selectbox': this.selectbox,
        'content': this.content
      });
    }
    
    this.maxW = 0;
    this.list.forEach(function(el, i){
      if (this.options[i].value == 'optgroup') dojo.addClass(el, "optgroup");
      el.i = i;
    },this);
    
    this.list.connect('onclick', this, 'selected');
    
    this.selectbox.h = dojo.marginBox(this.selectbox).h;
    dojo.style(this.selectbox, {
      height: 0
    });
    dojo.connect(document, 'onclick', this, 'blur');
    dojo.connect(this.selectbox, 'onclick', this, 'focused');
    
    if(this.fireshow)
      this.fireEvent(this.hidden, 'click');
  },
  
  controller: function(){
    this.focused();
    this.initSelectBox();
    if(this.showed == 0){
      this.reposition();
      this.showList();
    }else{
      this.hideList();
    }
  },
  
  reposition: function(){
    var pos = dojo.coords(this.container, true);
    if(this.selectbox){
      dojo.style(this.selectbox, {
        left: pos.l + "px",
        top: pos.t + pos.h  + "px",
        width: pos.w -2 +"px" //-2px because of the side-borders
      });
      if(this.content) {
        dojo.style(this.content,{
        
         'width': pos.w - 12 + 'px',
         'float': 'left'
         });
      }
    }
  },
  
  showList: function(){
    if(this.anim) this.anim.stop();
    this.showed = 1;
    dojo.addClass(this.container,'openedlist');
    dojo.addClass(this.selectbox,'openedlist');
    dojo.removeClass(this.active,'active');
    dojo.addClass(this.list[this.hidden.selectedIndex],'selected active');
    if(this.height) {
      var p = this.hidden.selectedIndex * this.lineHeight;
      this.scrollbar.setPosition(p);
    }
    this.active = this.list[this.hidden.selectedIndex];
    
    dojo.style(this.offlajnlist, 'zIndex', ++window.offlajnlistzindex);
    dojo.style(this.selectbox, {
      display: 'block',
      zIndex: window.offlajnlistzindex-1
    });
    window.offlajnlistzindex++;
    
    this.anim = dojo.animateProperty({
      node: this.selectbox,
      properties: {
          opacity : 1,
          height: this.selectbox.h
      }
    }).play();
  },
  
  hideList: function(){
    if(this.anim) this.anim.stop();
    if(!this.selectbox) return;
    
    this.showed = 0;

    var h = dojo.marginBox(this.selectbox).h;
    dojo.removeClass(this.container,'openedlist');
    this.anim = dojo.animateProperty({
      node: this.selectbox,
      properties: {
          opacity : 0,
          height: 0
      },
      onEnd: dojo.hitch(this, function(el){
        dojo.style(el, {
          display: 'none',
          height: '0',
          zIndex: this.zindex-1
        });
        dojo.style(this.offlajnlist, 'zIndex', this.zindex);
        dojo.removeClass(this.selectbox,'openedlist');
      })
    }).play();
  },
  
  selected: function(e){
    if (dojo.hasClass(e.currentTarget, 'optgroup')) return;
    if(this.list[this.hidden.selectedIndex])
      dojo.removeClass(this.list[this.hidden.selectedIndex],'selected active');
    this.hidden.selectedIndex = e.target.i;
    this.hidden.value = this.hidden.options[this.hidden.selectedIndex].value;
    
    this.currentText.innerHTML = this.hidden.options[this.hidden.selectedIndex].text;
    if(this.list[this.hidden.selectedIndex])
      dojo.addClass(this.list[this.hidden.selectedIndex],'selected active');
    this.hideList();
    this.fireEvent(this.hidden, 'change');
    this.change = 0;
  },
  
  addActive: function(e){
    var el = e.target;
    if(el != this.active){
      dojo.removeClass(this.active,'active');
      dojo.addClass(el,'active');
      this.active = el;
    }
  },

  focused: function(){
    this.focus = 1;
  },

  blur: function(e){
    if(!this.focus){
      this.hideList();
    }
    this.focus = 0;
  },

  setValue: function(e) {
    if(!this.change && this.map[this.hidden.value] != this.hidden.selectedIndex) {
      this.change = 1;
      e.target.i = this.map[this.hidden.value] ? this.map[this.hidden.value] : 0;
      this.selected(e);
    }
  },

  fireEvent: function(element,event){
    if ((document.createEventObject && !dojo.isIE) || (document.createEventObject && dojo.isIE && dojo.isIE < 9)){
      var evt = document.createEventObject();
      return element.fireEvent('on'+event,evt);
    }else{
      var evt = document.createEvent("HTMLEvents");
      evt.initEvent(event, true, true );
      return !element.dispatchEvent(evt);
    }
  }
});

dojo.declare("OfflajnScroller", null, {
	constructor: function(args) {
   this.scrollspeed = 10;
   this.curr = 0;
	 dojo.mixin(this,args);
	 this.initScrollbar();
  },
  
  initScrollbar: function() {
    (!dojo.isMozilla) ? dojo.connect(this.selectbox, 'onmousewheel', this, 'scrollWheel') : dojo.connect(this.selectbox, 'DOMMouseScroll', this, 'scrollWheel');
    var right = dojo.create('div', {'class': 'gk_hack offlajnscrollerright'}, this.selectbox);
    this.sc = dojo.create('div', {'class': 'gk_hack offlajnscrollerbg'}, right);
    this.scrollbg = dojo.create('div', {'class': 'gk_hack offlajnscrollerscrollbg'}, this.sc);
    this.scrollbtn = dojo.create('div', {'class': 'gk_hack offlajnscrollerscrollbtn'} ,this.sc );
    if(this.extraClass) {
      dojo.addClass(right, this.extraClass);
      dojo.addClass(this.sc, this.extraClass);
      dojo.addClass(this.scrollbg, this.extraClass);
      dojo.addClass(this.scrollbtn, this.extraClass);
    }
    if(this.extraClass == 'multi-select') {
      this.scrollup = dojo.create('div', {'class': 'gk_hack offlajnscrollerarrowup'}, this.sc, 'first');
      this.scrolldown = dojo.create('div', {'class': 'gk_hack offlajnscrollerarrowdown' }, this.sc, 'last');     
      this.scrupc = dojo.connect(this.scrollup, 'onmousedown', this, 'upScroll');
      this.scrdownc = dojo.connect(this.scrolldown, 'onmousedown', this, 'downScroll');   
    }    
    dojo.connect(this.scrollbtn, 'onmousedown', this, 'onscrolldown');
    dojo.connect(this.scrollbg, 'onclick', this, 'scrollTo');
    this.scrbg = dojo.position(this.scrollbg, true);
    this.scrollbtnprop = dojo.position(this.scrollbtn, true);
    
    this.scrollReInit();
  },
  
  scrollReInit: function(){
    dojo.style(this.scrollbtn, 'display', 'block');
    this.maxHeight = parseInt(dojo.position(this.content).h);
    this.windowHeight = parseInt(dojo.style(this.selectbox, 'height'));
    this.scrollRatio = this.maxHeight/this.windowHeight;
    
    this.maxTop = -1 * (this.maxHeight-this.windowHeight);
    if(this.maxTop > 0) this.maxTop = 0;
    var scrollArrowHeight = 0;
    this.scrollHeight = 0;
    var marginVertical = dojo.marginBox(this.scrollbg).h-dojo.position(this.scrollbg).h;
    if(this.extraClass == 'multi-select') {
      scrollArrowHeight = dojo.marginBox(this.scrollup).h;
      this.scrollHeight = (this.windowHeight+(-2*scrollArrowHeight-marginVertical-2));
      this.scrollBtnmaxTop = (this.scrollHeight-this.scrollHeight/this.scrollRatio)-2;
    } else {
      this.scrollHeight = (this.windowHeight-10);
      this.scrollBtnmaxTop = (this.scrollHeight-this.scrollHeight/this.scrollRatio);
    }
    dojo.style(this.scrollbg, 'height', this.scrollHeight+'px');
    var scrollBtn = (this.scrollHeight/this.scrollRatio-2);
    if(scrollBtn<10){
      scrollBtn = 10;
      this.scrollBtnmaxTop = (this.scrollHeight-scrollBtn-2);
    }
    this.scrollBtnH = scrollBtn;
    dojo.style(this.scrollbtn, 'height', scrollBtn+'px');
    if(this.scrollBtnmaxTop < 0) this.scrollBtnmaxTop = 0; 
    if(this.windowHeight > this.maxHeight) this.hideScrollBtn();  
  },
  
  hideScrollBtn: function() {
    dojo.style(this.scrollbtn, 'display', 'none');
  },
  
  goToBottom: function(){
    this.scrolling(-1000,1000);
  },
  
  onscrolldown: function(e) {
    this.scrdown = 1;
    this.currentpos = e.clientY;
    this.scrbtnpos = dojo.style(this.scrollbtn, 'top');
    this.mousemove = dojo.connect(document, 'onmousemove', this, 'onscrollmove');
    this.mouseup = dojo.connect(document, 'onmouseup', this, 'mouseUp');
  },
  
  onscrollmove: function(e) {
    var diff = this.currentpos-e.clientY;
    if(diff == 0) return;
    var lastt = (dojo.style(this.scrollbtn, 'top'));
    var pos = dojo.style(this.content, 'top');
    this.scrolling(diff, 	(((lastt-diff)/this.scrollBtnmaxTop)*this.maxTop-pos)/diff);
    this.currentpos = e.clientY;
  },
  
  scrollTo: function(e) {
    var pos = e.clientY;
    var sc = dojo.position(this.scrollbg);
    var currpos = pos - sc.y;    
    if(currpos < this.maxTop) currpos = maxTop; 
    if(currpos > this.scrollBtnmaxTop) currpos = this.scrollBtnmaxTop;
    dojo.style(this.scrollbtn, 'top', currpos + 'px');
    var scroll = -1*currpos * this.scrollRatio;
    dojo.style(this.content, 'top', scroll + 'px');
  },
  
  setPosition: function(p) {
    var pos = -1*p;
    if(pos < this.maxTop) pos = this.maxTop;
    this.setScrollBtn(pos);
    dojo.style(this.content, 'top', pos + 'px');
  },
  
  onscrollup: function(e) {
    e.stopPropagation();
    this.scrdown = 0;
  },
  
  upScroll: function(e) {
    this.mouseup = dojo.connect(document, 'onmouseup', this, 'mouseUp');
    e.stopPropagation();
    this.btnScroll(1);
  },
  
  downScroll: function(e) {
    this.mouseup = dojo.connect(document, 'onmouseup', this, 'mouseUp');
    e.stopPropagation();
    this.btnScroll(-1);
  },
  
  btnScroll: function(direction){
    this.dscr = 1;
    var fn = dojo.hitch(this, 'scrolling', direction, this.scrollspeed/4);
    fn();
    this.inter = window.setInterval(fn, 50);
  },
    
  scrolling: function(p, ratio) {
    if(ratio == undefined) ratio = this.scrollspeed;
    var pos = dojo.style(this.content, 'top');
    var scr = pos + (p * ratio);

    
    if(scr < this.maxTop) scr = this.maxTop;
    if(scr > 0) scr = 0;
    dojo.style(this.content, 'top', scr + 'px');
   
    this.setScrollBtn(scr);
    this.curr = scr;
    this.onScroll();
  },
  
  onScroll: function(){
  
  },
    
  setScrollBtn: function(val) {
    var top = (this.scrollBtnmaxTop*(val/this.maxTop));
    dojo.style(this.scrollbtn, 'top', top+'px');
  },
  
  mouseUp: function(e) {
    if(this.mousemove)
      dojo.disconnect(this.mousemove);
    if(this.mouseup)
      dojo.disconnect(this.mouseup);
    e.stopPropagation();
    this.inter = window.clearInterval(this.inter);
    if( this.dscr == 1) {
      this.dscr = 0;
    }
  },
  
  scrollWheel: function(e) {
    var pos = 0;
    pos = (e.detail != "") ? e.detail : e.wheelDelta;  
    if(dojo.isMozilla || dojo.isOpera) {  
      if (pos < 0) {
        this.scrolling(1);
      } else {
        this.scrolling(-1);
      }
    } else {
      if (pos < 0) {
        this.scrolling(-1);
      } else {
        this.scrolling(1);
      }
    }
    dojo.stopEvent(e);
  }
  
});


dojo.declare("OfflajnCombine", null, {
	constructor: function(args) {
    dojo.mixin(this,args);
    this.fields = new Array();
    this.init();
  },
  
  
  init: function() {
    this.hidden = dojo.byId(this.id);
    //console.log(this.hidden.value);
    dojo.connect(this.hidden, 'onchange', this, 'reset');
    for(var i = 0;i < this.num; i++){
      this.fields[i] = dojo.byId(this.id+i);
      this.fields[i].combineobj = this;
      if(this.fields[i].loaded) this.fields[i].loaded();
      dojo.connect(this.fields[i], 'change', this, 'change');
    }
    this.reset();
    
    this.outer = dojo.byId('offlajncombine_outer' + this.id);
    this.items = dojo.query('.offlajncombinefieldcontainer', this.outer);
    if(this.switcherid) {
      this.switcher = dojo.byId(this.switcherid);
      dojo.connect(this.switcher, 'onchange', this, 'hider');
      this.hider();
    }
  },
  
  reset: function(){
    this.value = this.hidden.value;
    //console.log(this.hidden);
    var values = this.value.split('|*|');
    for(var i = 0;i < this.num; i++){
      if(this.fields[i].value != values[i]){
        this.fields[i].value = values[i];
        this.fireEvent(this.fields[i], 'change');
      }
    }
  },
  
  change: function(){
    var value = '';
    for(var i = 0;i < this.num; i++){
      value+= this.fields[i].value+'|*|';
    }
    this.hidden.value = value;
    this.fireEvent(this.hidden, 'change');
  },
  
  hider: function() {
    var w = dojo.position(this.outer).w;
    if(!this.hiderdiv) { 
      this.hiderdiv = dojo.query('.offlajncombine_hider', this.switcher.parentNode.parentNode.parentNode)[0];
      dojo.style(this.hiderdiv, 'width',  w - 38 + 'px');
    }
    if(this.switcher.value == 0) {
      this.items.forEach(function(item, i){
        if(i >= this.hideafter && item != this.switcher.parentNode.parentNode) dojo.style(item, 'opacity', '0.5');
      }, this);
      if(this.hideafter == 0)
        dojo.style(this.hiderdiv, 'display', 'block');
    } else {
      this.items.forEach(function(item, i){
        if(item != this.switcher.parentNode.parentNode) dojo.style(item, 'opacity', '1');
      }, this);
      if(this.hideafter == 0)
        dojo.style(this.hiderdiv, 'display', 'none');
    }
  },

  fireEvent: function(element,event){
    if ((document.createEventObject && !dojo.isIE) || (document.createEventObject && dojo.isIE && dojo.isIE < 9)){
      var evt = document.createEventObject();
      return element.fireEvent('on'+event,evt);
    }else{
      var evt = document.createEvent("HTMLEvents");
      evt.initEvent(event, true, true );
      return !element.dispatchEvent(evt);
    }
  }
});


dojo.declare("OfflajnText", null, {
	constructor: function(args) {
    dojo.mixin(this,args);
    this.init();
  },
  
  
  init: function() {
    this.hidden = dojo.byId(this.id);
    dojo.connect(this.hidden, 'change', this, 'reset');
    
    this.input = dojo.byId(this.id+'input');
    this.switcher = dojo.byId(this.id+'unit');
  
    
    if(this.validation == 'int'){
      dojo.connect(this.input, 'keyup', this, 'validateInt');
      this.validateInt();
    }else if(this.validation == 'float'){
      dojo.connect(this.input, 'keyup', this, 'validateFloat');
      this.validateFloat();
    }
    dojo.connect(this.input, 'onblur', this, 'change');
    if(this.switcher){
      dojo.connect(this.switcher, 'change', this, 'change');
    }else{
      if(this.attachunit != '')
        this.switcher = {'value': this.attachunit, 'noelement':true};
      
    }
    this.container = dojo.byId('offlajntextcontainer' + this.id);
    if(this.mode == 'increment') {
      this.arrows = dojo.query('.arrow', this.container);
      dojo.connect(this.arrows[0], 'onmousedown', dojo.hitch(this, 'mouseDown', 1));
      dojo.connect(this.arrows[1], 'onmousedown', dojo.hitch(this, 'mouseDown', -1));
    }
    dojo.connect(this.input, 'onfocus', this, dojo.hitch(this, 'setFocus', 1));
    dojo.connect(this.input, 'onblur', this, dojo.hitch(this, 'setFocus', 0));
  },
  
  reset: function(e){
    if(this.hidden.value != this.input.value+(this.switcher? '||'+this.switcher.value : '')){
      var v = this.hidden.value.split('||');
      this.input.value = v[0];
      if(this.switcher && this.switcher.noelement != true){
        this.switcher.value = v[1];
        this.fireEvent(this.switcher, 'change');
      }
      if(e) dojo.stopEvent(e);
      this.fireEvent(this.input, 'change');
    }
  },
  
  change: function(){
    this.hidden.value = this.input.value+(this.switcher? '||'+this.switcher.value : '');
    this.fireEvent(this.hidden, 'change');
    if(this.onoff) this.hider();
  },
  
  setFocus: function(mode) {
    if(mode){
      dojo.addClass(this.input.parentNode, 'focus');
    } else {
      dojo.removeClass(this.input.parentNode, 'focus');
    }
  },
  
  hider: function() {
    if(!this.hiderdiv) {
      this.hiderdiv = dojo.create('div', {'class': 'offlajntext_hider'}, this.container);
      dojo.style(this.hiderdiv, 'width', dojo.position(this.container).w + 'px');
    }
    if(parseInt(this.switcher.value)) {
      dojo.style(this.container, 'opacity', '1');
      dojo.style(this.hiderdiv, 'display', 'none');
    } else {
      dojo.style(this.container, 'opacity', '0.5');
      dojo.style(this.hiderdiv, 'display', 'block');
    }
  },
  
  validateInt: function(){
    var val = parseInt(this.input.value, 10);
    if(!val) val = 0;
    this.input.value = val;
  },
  
  validateFloat: function(){
    var val = parseFloat(this.input.value);
    if(!val) val = 0;
    this.input.value = val;
  },
  
  mouseDown: function(m){
    dojo.connect(document, 'onmouseup', this, 'mouseUp');
    var f = dojo.hitch(this, 'modifyValue', m);
    f();
    this.interval = setInterval(f, 200);
  },
  
  mouseUp: function(){
    clearInterval(this.interval);
  },

  modifyValue: function(m) {
    var val = 0;
    if(this.validation == 'int') {
      val = parseInt(this.input.value);
    } else if(this.validation == 'float') {
      val = parseFloat(this.input.value);
    }
    val = val + m*this.scale;
    if(val < 0 && this.minus == 0) val = 0; 
    this.input.value = val;
    this.change();
    this.fireEvent(this.input, 'change');
  },

  fireEvent: function(element,event){
    if ((document.createEventObject && !dojo.isIE) || (document.createEventObject && dojo.isIE && dojo.isIE < 9)){
      var evt = document.createEventObject();
      return element.fireEvent('on'+event,evt);
    }else{
      var evt = document.createEvent("HTMLEvents");
      evt.initEvent(event, true, true );
      return !element.dispatchEvent(evt);
    }
  }
});


dojo.declare("OfflajnImagemanager", null, {
	constructor: function(args) {
    this.dnd = false;
    dojo.mixin(this,args);
    this.map = {};
    var div = document.createElement('div');
    if(typeof(FileReader) != "undefined" && !!FileReader && (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div))){
      this.dnd = true;
    }
    this.init();
  },
  
  
  init: function() {
    this.btn = dojo.byId('offlajnimagemanager'+this.id);
    dojo.connect(this.btn, 'onclick', this, 'showWindow');

    this.selectedImage = "";
    this.hidden = dojo.byId(this.id);
    dojo.connect(this.hidden, 'change', this, 'reset');
    
    this.imgprev = dojo.query('.offlajnimagemanagerimg div', this.btn)[0];
    //if(this.hidden.value != "") dojo.style(this.imgprev,'backgroundImage','url("'+this.root+this.hidden.value+'")');
    if(this.hidden.value != "") dojo.style(this.imgprev,'backgroundImage','url("'+this.hidden.value+'")');
    this.images = new Array();
  },
  
  reset: function(){
    if(this.hidden.value != this.selectedImage){
      this.selectedImage = this.hidden.value;
      if(this.selectedImage == '') this.selectedImage = this.folder;
      this.saveImage();
      this.fireEvent(this.hidden, 'change');
    }
  },
  
  showOverlay: function(){
    if(!this.overlayBG){
      this.overlayBG = dojo.create('div',{'class': 'blackBg'}, dojo.body());
    }
    dojo.removeClass(this.overlayBG, 'hide');
    dojo.style(this.overlayBG,{
      'opacity': 0.3
    });
  },
  
  showWindow: function(){
    this.showOverlay();
    if(!this.window){
      this.window = dojo.create('div', {'class': 'OfflajnWindow'}, dojo.body());
      var closeBtn = dojo.create('div', {'class': 'OfflajnWindowClose'}, this.window);
      dojo.connect(closeBtn, 'onclick', this, 'closeWindow');
      var inner = dojo.create('div', {'class': 'OfflajnWindowInner'}, this.window);
      dojo.create('h3', {'innerHTML': 'Image Manager'}, inner);
      dojo.create('div', {'class': 'OfflajnWindowLine'}, inner);
      var imgAreaOuter = dojo.create('div', {'class': 'OfflajnWindowImgAreaOuter'}, inner);
      this.imgArea = dojo.create('div', {'class': 'OfflajnWindowImgArea'}, imgAreaOuter);
      
      dojo.place(this.createFrame(''), this.imgArea);
      
      for(var i in this.imgs){
        if(i >=0 )
          dojo.place(this.createFrame(this.imgs[i]), this.imgArea);
      }
      
      var left = dojo.create('div', {'class': 'OfflajnWindowLeftContainer'}, inner);
      var right = dojo.create('div', {'class': 'OfflajnWindowRightContainer'}, inner);
      
      dojo.create('h4', {'innerHTML': 'Upload Your Image'}, left);
      if (this.dnd) {
        this.uploadArea = dojo.create('div', { 'innerHTML': 'Drag images here or<br />', 'class': 'OfflajnWindowUploadarea'}, left);
        
        this.input = dojo.create('input', {'type': 'file'}, this.uploadArea);
        dojo.create('span', {innerHTML: 'Upload', 'class': 'upload'}, this.uploadArea);
        
        dojo.style(this.input, 'display', 'none');
        dojo.connect(this.uploadArea, 'onclick', this, 'openFilebrowser');
        dojo.connect(this.input, 'onchange', this, 'uploadInputFile');
      }else{
        this.uploadArea = dojo.create('form', {
          'action': 'index.php?option=offlajnupload&identifier='+this.identifier,
          'enctype': 'multipart/form-data',
          'method': 'post',
          'target': 'uploadiframe',
          'class': 'OfflajnWindowUploadareaForm'
        }, left);
        dojo.create('input', {'name': 'img', 'type': 'file'}, this.uploadArea);
        dojo.create('button', {'innerHTML': 'Upload', 'type': 'submit'}, this.uploadArea);
        var iframe = dojo.create('iframe', {'name': 'uploadiframe', 'style': 'display:none;'}, this.uploadArea);
        dojo.connect(iframe, 'onload', this, 'alterUpload');
      }
      
      dojo.create('h4', {'innerHTML': 'Currently Selected Image'}, right);
      
      this.selectedframe = dojo.create('div', {'class': 'OfflajnWindowImgFrame'}, right);
      this.selectedframe.img1 = dojo.create('div', {'class': 'OfflajnWindowImgFrameImg'}, this.selectedframe);
      this.selectedframe.img2 = dojo.create('img', {}, this.selectedframe);
      dojo.create('div', {'class': 'OfflajnWindowImgFrameSelected'}, this.selectedframe);
      
      dojo.connect(this.selectedframe, 'onmouseenter', dojo.hitch(this,function(img){dojo.addClass(img, 'show');}, this.selectedframe.img2));
      dojo.connect(this.selectedframe, 'onmouseleave', dojo.hitch(this,function(img){dojo.removeClass(img, 'show');}, this.selectedframe.img2));
      
      dojo.create('div', {'class': 'OfflajnWindowDescription', 'innerHTML': this.description}, right);
      
      var saveCont = dojo.create('div', {'class': 'OfflajnWindowSaveContainer'}, right);
      var savebtn = dojo.create('div', {'class': 'OfflajnWindowSave', 'innerHTML': 'SAVE'}, saveCont);
      dojo.connect(savebtn, 'onclick', this, 'saveImage');
      
      this.initUploadArea();
      
      this.scrollbar = new OfflajnScroller({
        'extraClass': 'multi-select',
        'selectbox': this.imgArea.parentNode,
        'content': this.imgArea,
        'scrollspeed' : 30
      });
    }
    dojo.removeClass(this.window, 'hide');
    this.exit = dojo.connect(document, "onkeypress", this, "keyPressed");
    this.loadSavedImage();
  },
  
  loadSavedImage: function() {
    var val = this.hidden.value;
    if(val == "") val = this.folder;
    val = val.replace(this.siteurl, "");
    if(val == '' || this.images[val] == undefined) return;
    var el = this.images[val];
    el.currentTarget = el.parentNode;
    this.select(el);
  },
  
  closeWindow: function(){
    dojo.addClass(this.window, 'hide');
    dojo.addClass(this.overlayBG, 'hide');
  },
  
  openFilebrowser: function(e){
    if(e.target == this.input) return;
    this.input.click();
  },
  
  createFrame: function(im, folder){
    if(!folder) folder = this.folder;
    if(this.map[im]){
      dojo.place(this.map[im], this.map[im].parentNode, 'last');
      return this.map[im];
    }
    var frame = dojo.create('div', {'class': 'OfflajnWindowImgFrame'});
    dojo.create('div', {'class': 'OfflajnWindowImgFrameImg', 'style': (im != '' ? {
      'backgroundImage': 'url("'+this.root+folder+im+'")'
    }:{}) }, frame);
    if(im != '')
      var img = dojo.create('img', {'src': this.root+folder+im}, frame);
    
    var caption = im != '' ? im.replace(/^.*[\\\/]/, '') : 'No image';
    dojo.create('div', {'class': 'OfflajnWindowImgFrameCaption', 'innerHTML': "<span>"+caption+"</span>"}, frame);
    
    frame.selected = dojo.create('div', {'class': 'OfflajnWindowImgFrameSelected'}, frame);
    
    frame.img = im;
    this.map[im] = frame;
    if(im != ''){
      dojo.connect(frame, 'onmouseenter', dojo.hitch(this,function(img){dojo.addClass(img, 'show');}, img));
      dojo.connect(frame, 'onmouseleave', dojo.hitch(this,function(img){dojo.removeClass(img, 'show');}, img));
      this.images[folder+im] = img;
    }
    dojo.connect(frame, 'onclick', this, 'select');
    return frame;
  },
  
  select: function(e){
    var el = e.currentTarget;
    if(el.img != this.active && this.map[this.active]){
      dojo.removeClass(this.map[this.active], 'active');
    }
    dojo.addClass(el, 'active');
    this.active = el.img;
    dojo.style(this.selectedframe.img1, 'backgroundImage', 'url("'+this.root+this.folder+this.active+'")');
    dojo.attr(this.selectedframe.img2, 'src', this.root+this.folder+this.active);
    this.selectedImage = this.folder+this.active;
    dojo.addClass(this.selectedframe, 'active');
  },
  
  initUploadArea: function(){
    dojo.connect(this.uploadArea, "ondragleave", this, function(e){
      var target = e.target;
    	if (target && target === this.uploadArea) {
    		dojo.removeClass(this.uploadArea, 'over');
    	}
      dojo.stopEvent(e);
    });
    dojo.connect(this.uploadArea, "ondragenter", this, function(e){
    	dojo.addClass(this.uploadArea, 'over');
      dojo.stopEvent(e);
    });
    dojo.connect(this.uploadArea, "ondragover", this, function(e){
      dojo.stopEvent(e);
    });
    dojo.connect(this.uploadArea, "ondrop", this, function(e){
    	this.filesAdded(e.dataTransfer.files);
    	dojo.removeClass(this.uploadArea, 'over');
      dojo.stopEvent(e);
    });
  },
  
  filesAdded: function(files){
    if (typeof files !== "undefined") {
  		for (var i=0, l=files.length; i<l; i++){
  			this.uploadFile(files[i]);
  		}
  	}
    this.scrollbar.scrollReInit();
    this.scrollbar.goToBottom();
  },
  
  uploadInputFile: function(){
    this.uploadFile(this.input.files[0]);
    this.scrollbar.scrollReInit();
    this.scrollbar.goToBottom();
  },
  
  uploadFile: function(file){
    var xhr = new XMLHttpRequest();
    xhr.open("post", this.uploadurl+"&name="+file.name+"&identifier="+this.identifier, true);
    
    // Set appropriate headers
    var boundary = "upload--"+(new Date).getTime();
    //xhr.setRequestHeader("Content-Type", "multipart/form-data; boundary=");
    xhr.setRequestHeader("X-File-Name", file.name);
    xhr.setRequestHeader("X-File-Size", file.fileSize);
    xhr.setRequestHeader("X-File-Type", file.type);

    dojo.connect(xhr, 'onload',dojo.hitch(this,'fileUploaded', file.name, xhr));
    
    if(xhr.upload)
      dojo.connect(xhr.upload, 'onprogress', dojo.hitch(this, 'fileProgress', xhr));
    else
      dojo.connect(xhr, 'onprogress', dojo.hitch(this, 'fileProgress', xhr));
      
    var frame = this.createFrame(file.name);
    this.changeFrameImg(frame, 'blank.png', '/media/system/images/');
    
    frame.span = dojo.query('span', frame)[0];
    frame.span.innerHTML = '0%';
    
    
    var caption = dojo.query('.OfflajnWindowImgFrameCaption', frame)[0];
    frame.progress = dojo.create('div', {'class':'progress'}, caption, 'first');
    dojo.place(frame, this.imgArea);
    this.captionW = dojo.position(caption).w-2;
    
    xhr.frame = frame;
    
    xhr.send(file);
  },
  
  changeFrameImg: function(frame, im, folder){
    if(!folder) folder = this.folder;
    dojo.attr(dojo.query("img", frame)[0], 'src', this.root+folder+im+"?"+new Date().getTime()
);
    dojo.style(dojo.query(".OfflajnWindowImgFrameImg", frame)[0], {
      'backgroundImage': 'url("'+this.root+folder+im+"?"+new Date().getTime()+'")'
    });
  },
  
  fileProgress: function(xhr, e){
    if (e.lengthComputable) {
      var ratio = (e.loaded / e.total);
  		xhr.frame.span.innerHTML = parseInt(ratio * 100) + "%";
      dojo.style(xhr.frame.progress, 'width', (ratio*this.captionW)+'px');
  	}
  },
  
  fileUploaded: function(name, xhr, e, data){
    
    var r = eval("(" + xhr.response+ ")");
    if(r.err){
      this.map[name] = null;
      dojo.destroy(xhr.frame);
      alert(r.err);
      return;
    }
    var img = dojo.query('.OfflajnWindowImgFrameImg',xhr.frame)[0];
    dojo.style(img, 'opacity', 0);
    this.changeFrameImg(xhr.frame, name);
    dojo.style(xhr.frame.progress, 'width', this.captionW+'px');
    xhr.frame.span.innerHTML = name;
    
    dojo.animateProperty({
      node: img,
      duration: 1000,
      properties: {
        opacity : 1
      }
    }).play();
    
    setTimeout(dojo.hitch(this,function(p){
      dojo.animateProperty({
        node: p,
        duration: 300,
        properties: {
          opacity : 0
        }
      }).play();
    },xhr.frame.progress),1000);
  },
  
  alterUpload: function(){
    var data = window["uploadiframe"].document.body.innerHTML;
    if(!data || data == '') return;
    var r = eval("(" + window["uploadiframe"].document.body.innerHTML + ")");
    if(r.err){
      alert(r.err);
      return;
    }else if(r.name){
      var frame = this.createFrame(r.name);
      var caption = dojo.query('.OfflajnWindowImgFrameCaption', frame)[0];
      frame.progress = dojo.create('div', {'class':'progress', 'style' : {'width':(dojo.position(caption).w-2)+'px'} }, caption, 'first');
      dojo.place(frame, this.imgArea);
      this.scrollbar.scrollReInit();
      this.scrollbar.goToBottom();
      setTimeout(dojo.hitch(this,function(p){
        dojo.animateProperty({
          node: p,
          duration: 300,
          properties: {
            opacity : 0
          }
        }).play();
      },frame.progress),1000);
    }
  },

  fireEvent: function(element,event){
    if ((document.createEventObject && !dojo.isIE) || (document.createEventObject && dojo.isIE && dojo.isIE < 9)){
      var evt = document.createEventObject();
      return element.fireEvent('on'+event,evt);
    }else{
      var evt = document.createEvent("HTMLEvents");
      evt.initEvent(event, true, true );
      return !element.dispatchEvent(evt);
    }
  },
  
  keyPressed: function(e) {
    if(e.keyCode == 27) { 
      this.closeWindow();
      dojo.disconnect(this.exit);
    }
  },
  
  saveImage: function() {
    //dojo.style(this.imgprev,'backgroundImage', 'url("'+this.root+this.selectedImage+'")');
    dojo.style(this.imgprev,'backgroundImage', 'url("'+this.selectedImage+'")');
    if(this.selectedImage != this.hidden.value) {
      this.closeWindow();
      if(this.folder == this.selectedImage) this.selectedImage = "";
      this.hidden.value = this.siteurl + this.selectedImage;
      this.fireEvent(this.hidden, 'change');
    }
  }
  
});


/*!
 * jQuery JavaScript Library v1.4.4
 * http://jquery.com/
 *
 * Copyright 2010, John Resig
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * Includes Sizzle.js
 * http://sizzlejs.com/
 * Copyright 2010, The Dojo Foundation
 * Released under the MIT, BSD, and GPL Licenses.
 *
 * Date: Thu Nov 11 19:04:53 2010 -0500
 */
(function(E,B){function ka(a,b,d){if(d===B&&a.nodeType===1){d=a.getAttribute("data-"+b);if(typeof d==="string"){try{d=d==="true"?true:d==="false"?false:d==="null"?null:!c.isNaN(d)?parseFloat(d):Ja.test(d)?c.parseJSON(d):d}catch(e){}c.data(a,b,d)}else d=B}return d}function U(){return false}function ca(){return true}function la(a,b,d){d[0].type=a;return c.event.handle.apply(b,d)}function Ka(a){var b,d,e,f,h,l,k,o,x,r,A,C=[];f=[];h=c.data(this,this.nodeType?"events":"__events__");if(typeof h==="function")h=
h.events;if(!(a.liveFired===this||!h||!h.live||a.button&&a.type==="click")){if(a.namespace)A=RegExp("(^|\\.)"+a.namespace.split(".").join("\\.(?:.*\\.)?")+"(\\.|$)");a.liveFired=this;var J=h.live.slice(0);for(k=0;k<J.length;k++){h=J[k];h.origType.replace(X,"")===a.type?f.push(h.selector):J.splice(k--,1)}f=c(a.target).closest(f,a.currentTarget);o=0;for(x=f.length;o<x;o++){r=f[o];for(k=0;k<J.length;k++){h=J[k];if(r.selector===h.selector&&(!A||A.test(h.namespace))){l=r.elem;e=null;if(h.preType==="mouseenter"||
h.preType==="mouseleave"){a.type=h.preType;e=c(a.relatedTarget).closest(h.selector)[0]}if(!e||e!==l)C.push({elem:l,handleObj:h,level:r.level})}}}o=0;for(x=C.length;o<x;o++){f=C[o];if(d&&f.level>d)break;a.currentTarget=f.elem;a.data=f.handleObj.data;a.handleObj=f.handleObj;A=f.handleObj.origHandler.apply(f.elem,arguments);if(A===false||a.isPropagationStopped()){d=f.level;if(A===false)b=false;if(a.isImmediatePropagationStopped())break}}return b}}function Y(a,b){return(a&&a!=="*"?a+".":"")+b.replace(La,
"`").replace(Ma,"&")}function ma(a,b,d){if(c.isFunction(b))return c.grep(a,function(f,h){return!!b.call(f,h,f)===d});else if(b.nodeType)return c.grep(a,function(f){return f===b===d});else if(typeof b==="string"){var e=c.grep(a,function(f){return f.nodeType===1});if(Na.test(b))return c.filter(b,e,!d);else b=c.filter(b,e)}return c.grep(a,function(f){return c.inArray(f,b)>=0===d})}function na(a,b){var d=0;b.each(function(){if(this.nodeName===(a[d]&&a[d].nodeName)){var e=c.data(a[d++]),f=c.data(this,
e);if(e=e&&e.events){delete f.handle;f.events={};for(var h in e)for(var l in e[h])c.event.add(this,h,e[h][l],e[h][l].data)}}})}function Oa(a,b){b.src?c.ajax({url:b.src,async:false,dataType:"script"}):c.globalEval(b.text||b.textContent||b.innerHTML||"");b.parentNode&&b.parentNode.removeChild(b)}function oa(a,b,d){var e=b==="width"?a.offsetWidth:a.offsetHeight;if(d==="border")return e;c.each(b==="width"?Pa:Qa,function(){d||(e-=parseFloat(c.css(a,"padding"+this))||0);if(d==="margin")e+=parseFloat(c.css(a,
"margin"+this))||0;else e-=parseFloat(c.css(a,"border"+this+"Width"))||0});return e}function da(a,b,d,e){if(c.isArray(b)&&b.length)c.each(b,function(f,h){d||Ra.test(a)?e(a,h):da(a+"["+(typeof h==="object"||c.isArray(h)?f:"")+"]",h,d,e)});else if(!d&&b!=null&&typeof b==="object")c.isEmptyObject(b)?e(a,""):c.each(b,function(f,h){da(a+"["+f+"]",h,d,e)});else e(a,b)}function S(a,b){var d={};c.each(pa.concat.apply([],pa.slice(0,b)),function(){d[this]=a});return d}function qa(a){if(!ea[a]){var b=c("<"+
a+">").appendTo("body"),d=b.css("display");b.remove();if(d==="none"||d==="")d="block";ea[a]=d}return ea[a]}function fa(a){return c.isWindow(a)?a:a.nodeType===9?a.defaultView||a.parentWindow:false}var t=E.document,c=function(){function a(){if(!b.isReady){try{t.documentElement.doScroll("left")}catch(j){setTimeout(a,1);return}b.ready()}}var b=function(j,s){return new b.fn.init(j,s)},d=E.jQuery,e=E.$,f,h=/^(?:[^<]*(<[\w\W]+>)[^>]*$|#([\w\-]+)$)/,l=/\S/,k=/^\s+/,o=/\s+$/,x=/\W/,r=/\d/,A=/^<(\w+)\s*\/?>(?:<\/\1>)?$/,
C=/^[\],:{}\s]*$/,J=/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,w=/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,I=/(?:^|:|,)(?:\s*\[)+/g,L=/(webkit)[ \/]([\w.]+)/,g=/(opera)(?:.*version)?[ \/]([\w.]+)/,i=/(msie) ([\w.]+)/,n=/(mozilla)(?:.*? rv:([\w.]+))?/,m=navigator.userAgent,p=false,q=[],u,y=Object.prototype.toString,F=Object.prototype.hasOwnProperty,M=Array.prototype.push,N=Array.prototype.slice,O=String.prototype.trim,D=Array.prototype.indexOf,R={};b.fn=b.prototype={init:function(j,
s){var v,z,H;if(!j)return this;if(j.nodeType){this.context=this[0]=j;this.length=1;return this}if(j==="body"&&!s&&t.body){this.context=t;this[0]=t.body;this.selector="body";this.length=1;return this}if(typeof j==="string")if((v=h.exec(j))&&(v[1]||!s))if(v[1]){H=s?s.ownerDocument||s:t;if(z=A.exec(j))if(b.isPlainObject(s)){j=[t.createElement(z[1])];b.fn.attr.call(j,s,true)}else j=[H.createElement(z[1])];else{z=b.buildFragment([v[1]],[H]);j=(z.cacheable?z.fragment.cloneNode(true):z.fragment).childNodes}return b.merge(this,
j)}else{if((z=t.getElementById(v[2]))&&z.parentNode){if(z.id!==v[2])return f.find(j);this.length=1;this[0]=z}this.context=t;this.selector=j;return this}else if(!s&&!x.test(j)){this.selector=j;this.context=t;j=t.getElementsByTagName(j);return b.merge(this,j)}else return!s||s.jquery?(s||f).find(j):b(s).find(j);else if(b.isFunction(j))return f.ready(j);if(j.selector!==B){this.selector=j.selector;this.context=j.context}return b.makeArray(j,this)},selector:"",jquery:"1.4.4",length:0,size:function(){return this.length},
toArray:function(){return N.call(this,0)},get:function(j){return j==null?this.toArray():j<0?this.slice(j)[0]:this[j]},pushStack:function(j,s,v){var z=b();b.isArray(j)?M.apply(z,j):b.merge(z,j);z.prevObject=this;z.context=this.context;if(s==="find")z.selector=this.selector+(this.selector?" ":"")+v;else if(s)z.selector=this.selector+"."+s+"("+v+")";return z},each:function(j,s){return b.each(this,j,s)},ready:function(j){b.bindReady();if(b.isReady)j.call(t,b);else q&&q.push(j);return this},eq:function(j){return j===
-1?this.slice(j):this.slice(j,+j+1)},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},slice:function(){return this.pushStack(N.apply(this,arguments),"slice",N.call(arguments).join(","))},map:function(j){return this.pushStack(b.map(this,function(s,v){return j.call(s,v,s)}))},end:function(){return this.prevObject||b(null)},push:M,sort:[].sort,splice:[].splice};b.fn.init.prototype=b.fn;b.extend=b.fn.extend=function(){var j,s,v,z,H,G=arguments[0]||{},K=1,Q=arguments.length,ga=false;
if(typeof G==="boolean"){ga=G;G=arguments[1]||{};K=2}if(typeof G!=="object"&&!b.isFunction(G))G={};if(Q===K){G=this;--K}for(;K<Q;K++)if((j=arguments[K])!=null)for(s in j){v=G[s];z=j[s];if(G!==z)if(ga&&z&&(b.isPlainObject(z)||(H=b.isArray(z)))){if(H){H=false;v=v&&b.isArray(v)?v:[]}else v=v&&b.isPlainObject(v)?v:{};G[s]=b.extend(ga,v,z)}else if(z!==B)G[s]=z}return G};b.extend({noConflict:function(j){E.$=e;if(j)E.jQuery=d;return b},isReady:false,readyWait:1,ready:function(j){j===true&&b.readyWait--;
if(!b.readyWait||j!==true&&!b.isReady){if(!t.body)return setTimeout(b.ready,1);b.isReady=true;if(!(j!==true&&--b.readyWait>0))if(q){var s=0,v=q;for(q=null;j=v[s++];)j.call(t,b);b.fn.trigger&&b(t).trigger("ready").unbind("ready")}}},bindReady:function(){if(!p){p=true;if(t.readyState==="complete")return setTimeout(b.ready,1);if(t.addEventListener){t.addEventListener("DOMContentLoaded",u,false);E.addEventListener("load",b.ready,false)}else if(t.attachEvent){t.attachEvent("onreadystatechange",u);E.attachEvent("onload",
b.ready);var j=false;try{j=E.frameElement==null}catch(s){}t.documentElement.doScroll&&j&&a()}}},isFunction:function(j){return b.type(j)==="function"},isArray:Array.isArray||function(j){return b.type(j)==="array"},isWindow:function(j){return j&&typeof j==="object"&&"setInterval"in j},isNaN:function(j){return j==null||!r.test(j)||isNaN(j)},type:function(j){return j==null?String(j):R[y.call(j)]||"object"},isPlainObject:function(j){if(!j||b.type(j)!=="object"||j.nodeType||b.isWindow(j))return false;if(j.constructor&&
!F.call(j,"constructor")&&!F.call(j.constructor.prototype,"isPrototypeOf"))return false;for(var s in j);return s===B||F.call(j,s)},isEmptyObject:function(j){for(var s in j)return false;return true},error:function(j){throw j;},parseJSON:function(j){if(typeof j!=="string"||!j)return null;j=b.trim(j);if(C.test(j.replace(J,"@").replace(w,"]").replace(I,"")))return E.JSON&&E.JSON.parse?E.JSON.parse(j):(new Function("return "+j))();else b.error("Invalid JSON: "+j)},noop:function(){},globalEval:function(j){if(j&&
l.test(j)){var s=t.getElementsByTagName("head")[0]||t.documentElement,v=t.createElement("script");v.type="text/javascript";if(b.support.scriptEval)v.appendChild(t.createTextNode(j));else v.text=j;s.insertBefore(v,s.firstChild);s.removeChild(v)}},nodeName:function(j,s){return j.nodeName&&j.nodeName.toUpperCase()===s.toUpperCase()},each:function(j,s,v){var z,H=0,G=j.length,K=G===B||b.isFunction(j);if(v)if(K)for(z in j){if(s.apply(j[z],v)===false)break}else for(;H<G;){if(s.apply(j[H++],v)===false)break}else if(K)for(z in j){if(s.call(j[z],
z,j[z])===false)break}else for(v=j[0];H<G&&s.call(v,H,v)!==false;v=j[++H]);return j},trim:O?function(j){return j==null?"":O.call(j)}:function(j){return j==null?"":j.toString().replace(k,"").replace(o,"")},makeArray:function(j,s){var v=s||[];if(j!=null){var z=b.type(j);j.length==null||z==="string"||z==="function"||z==="regexp"||b.isWindow(j)?M.call(v,j):b.merge(v,j)}return v},inArray:function(j,s){if(s.indexOf)return s.indexOf(j);for(var v=0,z=s.length;v<z;v++)if(s[v]===j)return v;return-1},merge:function(j,
s){var v=j.length,z=0;if(typeof s.length==="number")for(var H=s.length;z<H;z++)j[v++]=s[z];else for(;s[z]!==B;)j[v++]=s[z++];j.length=v;return j},grep:function(j,s,v){var z=[],H;v=!!v;for(var G=0,K=j.length;G<K;G++){H=!!s(j[G],G);v!==H&&z.push(j[G])}return z},map:function(j,s,v){for(var z=[],H,G=0,K=j.length;G<K;G++){H=s(j[G],G,v);if(H!=null)z[z.length]=H}return z.concat.apply([],z)},guid:1,proxy:function(j,s,v){if(arguments.length===2)if(typeof s==="string"){v=j;j=v[s];s=B}else if(s&&!b.isFunction(s)){v=
s;s=B}if(!s&&j)s=function(){return j.apply(v||this,arguments)};if(j)s.guid=j.guid=j.guid||s.guid||b.guid++;return s},access:function(j,s,v,z,H,G){var K=j.length;if(typeof s==="object"){for(var Q in s)b.access(j,Q,s[Q],z,H,v);return j}if(v!==B){z=!G&&z&&b.isFunction(v);for(Q=0;Q<K;Q++)H(j[Q],s,z?v.call(j[Q],Q,H(j[Q],s)):v,G);return j}return K?H(j[0],s):B},now:function(){return(new Date).getTime()},uaMatch:function(j){j=j.toLowerCase();j=L.exec(j)||g.exec(j)||i.exec(j)||j.indexOf("compatible")<0&&n.exec(j)||
[];return{browser:j[1]||"",version:j[2]||"0"}},browser:{}});b.each("Boolean Number String Function Array Date RegExp Object".split(" "),function(j,s){R["[object "+s+"]"]=s.toLowerCase()});m=b.uaMatch(m);if(m.browser){b.browser[m.browser]=true;b.browser.version=m.version}if(b.browser.webkit)b.browser.safari=true;if(D)b.inArray=function(j,s){return D.call(s,j)};if(!/\s/.test("\u00a0")){k=/^[\s\xA0]+/;o=/[\s\xA0]+$/}f=b(t);if(t.addEventListener)u=function(){t.removeEventListener("DOMContentLoaded",u,
false);b.ready()};else if(t.attachEvent)u=function(){if(t.readyState==="complete"){t.detachEvent("onreadystatechange",u);b.ready()}};return E.jQuery=E.$=b}();(function(){c.support={};var a=t.documentElement,b=t.createElement("script"),d=t.createElement("div"),e="script"+c.now();d.style.display="none";d.innerHTML="   <link/><table></table><a href='/a' style='color:red;float:left;opacity:.55;'>a</a><input type='checkbox'/>";var f=d.getElementsByTagName("*"),h=d.getElementsByTagName("a")[0],l=t.createElement("select"),
k=l.appendChild(t.createElement("option"));if(!(!f||!f.length||!h)){c.support={leadingWhitespace:d.firstChild.nodeType===3,tbody:!d.getElementsByTagName("tbody").length,htmlSerialize:!!d.getElementsByTagName("link").length,style:/red/.test(h.getAttribute("style")),hrefNormalized:h.getAttribute("href")==="/a",opacity:/^0.55$/.test(h.style.opacity),cssFloat:!!h.style.cssFloat,checkOn:d.getElementsByTagName("input")[0].value==="on",optSelected:k.selected,deleteExpando:true,optDisabled:false,checkClone:false,
scriptEval:false,noCloneEvent:true,boxModel:null,inlineBlockNeedsLayout:false,shrinkWrapBlocks:false,reliableHiddenOffsets:true};l.disabled=true;c.support.optDisabled=!k.disabled;b.type="text/javascript";try{b.appendChild(t.createTextNode("window."+e+"=1;"))}catch(o){}a.insertBefore(b,a.firstChild);if(E[e]){c.support.scriptEval=true;delete E[e]}try{delete b.test}catch(x){c.support.deleteExpando=false}a.removeChild(b);if(d.attachEvent&&d.fireEvent){d.attachEvent("onclick",function r(){c.support.noCloneEvent=
false;d.detachEvent("onclick",r)});d.cloneNode(true).fireEvent("onclick")}d=t.createElement("div");d.innerHTML="<input type='radio' name='radiotest' checked='checked'/>";a=t.createDocumentFragment();a.appendChild(d.firstChild);c.support.checkClone=a.cloneNode(true).cloneNode(true).lastChild.checked;c(function(){var r=t.createElement("div");r.style.width=r.style.paddingLeft="1px";t.body.appendChild(r);c.boxModel=c.support.boxModel=r.offsetWidth===2;if("zoom"in r.style){r.style.display="inline";r.style.zoom=
1;c.support.inlineBlockNeedsLayout=r.offsetWidth===2;r.style.display="";r.innerHTML="<div style='width:4px;'></div>";c.support.shrinkWrapBlocks=r.offsetWidth!==2}r.innerHTML="<table><tr><td style='padding:0;display:none'></td><td>t</td></tr></table>";var A=r.getElementsByTagName("td");c.support.reliableHiddenOffsets=A[0].offsetHeight===0;A[0].style.display="";A[1].style.display="none";c.support.reliableHiddenOffsets=c.support.reliableHiddenOffsets&&A[0].offsetHeight===0;r.innerHTML="";t.body.removeChild(r).style.display=
"none"});a=function(r){var A=t.createElement("div");r="on"+r;var C=r in A;if(!C){A.setAttribute(r,"return;");C=typeof A[r]==="function"}return C};c.support.submitBubbles=a("submit");c.support.changeBubbles=a("change");a=b=d=f=h=null}})();var ra={},Ja=/^(?:\{.*\}|\[.*\])$/;c.extend({cache:{},uuid:0,expando:"jQuery"+c.now(),noData:{embed:true,object:"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000",applet:true},data:function(a,b,d){if(c.acceptData(a)){a=a==E?ra:a;var e=a.nodeType,f=e?a[c.expando]:null,h=
c.cache;if(!(e&&!f&&typeof b==="string"&&d===B)){if(e)f||(a[c.expando]=f=++c.uuid);else h=a;if(typeof b==="object")if(e)h[f]=c.extend(h[f],b);else c.extend(h,b);else if(e&&!h[f])h[f]={};a=e?h[f]:h;if(d!==B)a[b]=d;return typeof b==="string"?a[b]:a}}},removeData:function(a,b){if(c.acceptData(a)){a=a==E?ra:a;var d=a.nodeType,e=d?a[c.expando]:a,f=c.cache,h=d?f[e]:e;if(b){if(h){delete h[b];d&&c.isEmptyObject(h)&&c.removeData(a)}}else if(d&&c.support.deleteExpando)delete a[c.expando];else if(a.removeAttribute)a.removeAttribute(c.expando);
else if(d)delete f[e];else for(var l in a)delete a[l]}},acceptData:function(a){if(a.nodeName){var b=c.noData[a.nodeName.toLowerCase()];if(b)return!(b===true||a.getAttribute("classid")!==b)}return true}});c.fn.extend({data:function(a,b){var d=null;if(typeof a==="undefined"){if(this.length){var e=this[0].attributes,f;d=c.data(this[0]);for(var h=0,l=e.length;h<l;h++){f=e[h].name;if(f.indexOf("data-")===0){f=f.substr(5);ka(this[0],f,d[f])}}}return d}else if(typeof a==="object")return this.each(function(){c.data(this,
a)});var k=a.split(".");k[1]=k[1]?"."+k[1]:"";if(b===B){d=this.triggerHandler("getData"+k[1]+"!",[k[0]]);if(d===B&&this.length){d=c.data(this[0],a);d=ka(this[0],a,d)}return d===B&&k[1]?this.data(k[0]):d}else return this.each(function(){var o=c(this),x=[k[0],b];o.triggerHandler("setData"+k[1]+"!",x);c.data(this,a,b);o.triggerHandler("changeData"+k[1]+"!",x)})},removeData:function(a){return this.each(function(){c.removeData(this,a)})}});c.extend({queue:function(a,b,d){if(a){b=(b||"fx")+"queue";var e=
c.data(a,b);if(!d)return e||[];if(!e||c.isArray(d))e=c.data(a,b,c.makeArray(d));else e.push(d);return e}},dequeue:function(a,b){b=b||"fx";var d=c.queue(a,b),e=d.shift();if(e==="inprogress")e=d.shift();if(e){b==="fx"&&d.unshift("inprogress");e.call(a,function(){c.dequeue(a,b)})}}});c.fn.extend({queue:function(a,b){if(typeof a!=="string"){b=a;a="fx"}if(b===B)return c.queue(this[0],a);return this.each(function(){var d=c.queue(this,a,b);a==="fx"&&d[0]!=="inprogress"&&c.dequeue(this,a)})},dequeue:function(a){return this.each(function(){c.dequeue(this,
a)})},delay:function(a,b){a=c.fx?c.fx.speeds[a]||a:a;b=b||"fx";return this.queue(b,function(){var d=this;setTimeout(function(){c.dequeue(d,b)},a)})},clearQueue:function(a){return this.queue(a||"fx",[])}});var sa=/[\n\t]/g,ha=/\s+/,Sa=/\r/g,Ta=/^(?:href|src|style)$/,Ua=/^(?:button|input)$/i,Va=/^(?:button|input|object|select|textarea)$/i,Wa=/^a(?:rea)?$/i,ta=/^(?:radio|checkbox)$/i;c.props={"for":"htmlFor","class":"className",readonly:"readOnly",maxlength:"maxLength",cellspacing:"cellSpacing",rowspan:"rowSpan",
colspan:"colSpan",tabindex:"tabIndex",usemap:"useMap",frameborder:"frameBorder"};c.fn.extend({attr:function(a,b){return c.access(this,a,b,true,c.attr)},removeAttr:function(a){return this.each(function(){c.attr(this,a,"");this.nodeType===1&&this.removeAttribute(a)})},addClass:function(a){if(c.isFunction(a))return this.each(function(x){var r=c(this);r.addClass(a.call(this,x,r.attr("class")))});if(a&&typeof a==="string")for(var b=(a||"").split(ha),d=0,e=this.length;d<e;d++){var f=this[d];if(f.nodeType===
1)if(f.className){for(var h=" "+f.className+" ",l=f.className,k=0,o=b.length;k<o;k++)if(h.indexOf(" "+b[k]+" ")<0)l+=" "+b[k];f.className=c.trim(l)}else f.className=a}return this},removeClass:function(a){if(c.isFunction(a))return this.each(function(o){var x=c(this);x.removeClass(a.call(this,o,x.attr("class")))});if(a&&typeof a==="string"||a===B)for(var b=(a||"").split(ha),d=0,e=this.length;d<e;d++){var f=this[d];if(f.nodeType===1&&f.className)if(a){for(var h=(" "+f.className+" ").replace(sa," "),
l=0,k=b.length;l<k;l++)h=h.replace(" "+b[l]+" "," ");f.className=c.trim(h)}else f.className=""}return this},toggleClass:function(a,b){var d=typeof a,e=typeof b==="boolean";if(c.isFunction(a))return this.each(function(f){var h=c(this);h.toggleClass(a.call(this,f,h.attr("class"),b),b)});return this.each(function(){if(d==="string")for(var f,h=0,l=c(this),k=b,o=a.split(ha);f=o[h++];){k=e?k:!l.hasClass(f);l[k?"addClass":"removeClass"](f)}else if(d==="undefined"||d==="boolean"){this.className&&c.data(this,
"__className__",this.className);this.className=this.className||a===false?"":c.data(this,"__className__")||""}})},hasClass:function(a){a=" "+a+" ";for(var b=0,d=this.length;b<d;b++)if((" "+this[b].className+" ").replace(sa," ").indexOf(a)>-1)return true;return false},val:function(a){if(!arguments.length){var b=this[0];if(b){if(c.nodeName(b,"option")){var d=b.attributes.value;return!d||d.specified?b.value:b.text}if(c.nodeName(b,"select")){var e=b.selectedIndex;d=[];var f=b.options;b=b.type==="select-one";
if(e<0)return null;var h=b?e:0;for(e=b?e+1:f.length;h<e;h++){var l=f[h];if(l.selected&&(c.support.optDisabled?!l.disabled:l.getAttribute("disabled")===null)&&(!l.parentNode.disabled||!c.nodeName(l.parentNode,"optgroup"))){a=c(l).val();if(b)return a;d.push(a)}}return d}if(ta.test(b.type)&&!c.support.checkOn)return b.getAttribute("value")===null?"on":b.value;return(b.value||"").replace(Sa,"")}return B}var k=c.isFunction(a);return this.each(function(o){var x=c(this),r=a;if(this.nodeType===1){if(k)r=
a.call(this,o,x.val());if(r==null)r="";else if(typeof r==="number")r+="";else if(c.isArray(r))r=c.map(r,function(C){return C==null?"":C+""});if(c.isArray(r)&&ta.test(this.type))this.checked=c.inArray(x.val(),r)>=0;else if(c.nodeName(this,"select")){var A=c.makeArray(r);c("option",this).each(function(){this.selected=c.inArray(c(this).val(),A)>=0});if(!A.length)this.selectedIndex=-1}else this.value=r}})}});c.extend({attrFn:{val:true,css:true,html:true,text:true,data:true,width:true,height:true,offset:true},
attr:function(a,b,d,e){if(!a||a.nodeType===3||a.nodeType===8)return B;if(e&&b in c.attrFn)return c(a)[b](d);e=a.nodeType!==1||!c.isXMLDoc(a);var f=d!==B;b=e&&c.props[b]||b;var h=Ta.test(b);if((b in a||a[b]!==B)&&e&&!h){if(f){b==="type"&&Ua.test(a.nodeName)&&a.parentNode&&c.error("type property can't be changed");if(d===null)a.nodeType===1&&a.removeAttribute(b);else a[b]=d}if(c.nodeName(a,"form")&&a.getAttributeNode(b))return a.getAttributeNode(b).nodeValue;if(b==="tabIndex")return(b=a.getAttributeNode("tabIndex"))&&
b.specified?b.value:Va.test(a.nodeName)||Wa.test(a.nodeName)&&a.href?0:B;return a[b]}if(!c.support.style&&e&&b==="style"){if(f)a.style.cssText=""+d;return a.style.cssText}f&&a.setAttribute(b,""+d);if(!a.attributes[b]&&a.hasAttribute&&!a.hasAttribute(b))return B;a=!c.support.hrefNormalized&&e&&h?a.getAttribute(b,2):a.getAttribute(b);return a===null?B:a}});var X=/\.(.*)$/,ia=/^(?:textarea|input|select)$/i,La=/\./g,Ma=/ /g,Xa=/[^\w\s.|`]/g,Ya=function(a){return a.replace(Xa,"\\$&")},ua={focusin:0,focusout:0};
c.event={add:function(a,b,d,e){if(!(a.nodeType===3||a.nodeType===8)){if(c.isWindow(a)&&a!==E&&!a.frameElement)a=E;if(d===false)d=U;else if(!d)return;var f,h;if(d.handler){f=d;d=f.handler}if(!d.guid)d.guid=c.guid++;if(h=c.data(a)){var l=a.nodeType?"events":"__events__",k=h[l],o=h.handle;if(typeof k==="function"){o=k.handle;k=k.events}else if(!k){a.nodeType||(h[l]=h=function(){});h.events=k={}}if(!o)h.handle=o=function(){return typeof c!=="undefined"&&!c.event.triggered?c.event.handle.apply(o.elem,
arguments):B};o.elem=a;b=b.split(" ");for(var x=0,r;l=b[x++];){h=f?c.extend({},f):{handler:d,data:e};if(l.indexOf(".")>-1){r=l.split(".");l=r.shift();h.namespace=r.slice(0).sort().join(".")}else{r=[];h.namespace=""}h.type=l;if(!h.guid)h.guid=d.guid;var A=k[l],C=c.event.special[l]||{};if(!A){A=k[l]=[];if(!C.setup||C.setup.call(a,e,r,o)===false)if(a.addEventListener)a.addEventListener(l,o,false);else a.attachEvent&&a.attachEvent("on"+l,o)}if(C.add){C.add.call(a,h);if(!h.handler.guid)h.handler.guid=
d.guid}A.push(h);c.event.global[l]=true}a=null}}},global:{},remove:function(a,b,d,e){if(!(a.nodeType===3||a.nodeType===8)){if(d===false)d=U;var f,h,l=0,k,o,x,r,A,C,J=a.nodeType?"events":"__events__",w=c.data(a),I=w&&w[J];if(w&&I){if(typeof I==="function"){w=I;I=I.events}if(b&&b.type){d=b.handler;b=b.type}if(!b||typeof b==="string"&&b.charAt(0)==="."){b=b||"";for(f in I)c.event.remove(a,f+b)}else{for(b=b.split(" ");f=b[l++];){r=f;k=f.indexOf(".")<0;o=[];if(!k){o=f.split(".");f=o.shift();x=RegExp("(^|\\.)"+
c.map(o.slice(0).sort(),Ya).join("\\.(?:.*\\.)?")+"(\\.|$)")}if(A=I[f])if(d){r=c.event.special[f]||{};for(h=e||0;h<A.length;h++){C=A[h];if(d.guid===C.guid){if(k||x.test(C.namespace)){e==null&&A.splice(h--,1);r.remove&&r.remove.call(a,C)}if(e!=null)break}}if(A.length===0||e!=null&&A.length===1){if(!r.teardown||r.teardown.call(a,o)===false)c.removeEvent(a,f,w.handle);delete I[f]}}else for(h=0;h<A.length;h++){C=A[h];if(k||x.test(C.namespace)){c.event.remove(a,r,C.handler,h);A.splice(h--,1)}}}if(c.isEmptyObject(I)){if(b=
w.handle)b.elem=null;delete w.events;delete w.handle;if(typeof w==="function")c.removeData(a,J);else c.isEmptyObject(w)&&c.removeData(a)}}}}},trigger:function(a,b,d,e){var f=a.type||a;if(!e){a=typeof a==="object"?a[c.expando]?a:c.extend(c.Event(f),a):c.Event(f);if(f.indexOf("!")>=0){a.type=f=f.slice(0,-1);a.exclusive=true}if(!d){a.stopPropagation();c.event.global[f]&&c.each(c.cache,function(){this.events&&this.events[f]&&c.event.trigger(a,b,this.handle.elem)})}if(!d||d.nodeType===3||d.nodeType===
8)return B;a.result=B;a.target=d;b=c.makeArray(b);b.unshift(a)}a.currentTarget=d;(e=d.nodeType?c.data(d,"handle"):(c.data(d,"__events__")||{}).handle)&&e.apply(d,b);e=d.parentNode||d.ownerDocument;try{if(!(d&&d.nodeName&&c.noData[d.nodeName.toLowerCase()]))if(d["on"+f]&&d["on"+f].apply(d,b)===false){a.result=false;a.preventDefault()}}catch(h){}if(!a.isPropagationStopped()&&e)c.event.trigger(a,b,e,true);else if(!a.isDefaultPrevented()){var l;e=a.target;var k=f.replace(X,""),o=c.nodeName(e,"a")&&k===
"click",x=c.event.special[k]||{};if((!x._default||x._default.call(d,a)===false)&&!o&&!(e&&e.nodeName&&c.noData[e.nodeName.toLowerCase()])){try{if(e[k]){if(l=e["on"+k])e["on"+k]=null;c.event.triggered=true;e[k]()}}catch(r){}if(l)e["on"+k]=l;c.event.triggered=false}}},handle:function(a){var b,d,e,f;d=[];var h=c.makeArray(arguments);a=h[0]=c.event.fix(a||E.event);a.currentTarget=this;b=a.type.indexOf(".")<0&&!a.exclusive;if(!b){e=a.type.split(".");a.type=e.shift();d=e.slice(0).sort();e=RegExp("(^|\\.)"+
d.join("\\.(?:.*\\.)?")+"(\\.|$)")}a.namespace=a.namespace||d.join(".");f=c.data(this,this.nodeType?"events":"__events__");if(typeof f==="function")f=f.events;d=(f||{})[a.type];if(f&&d){d=d.slice(0);f=0;for(var l=d.length;f<l;f++){var k=d[f];if(b||e.test(k.namespace)){a.handler=k.handler;a.data=k.data;a.handleObj=k;k=k.handler.apply(this,h);if(k!==B){a.result=k;if(k===false){a.preventDefault();a.stopPropagation()}}if(a.isImmediatePropagationStopped())break}}}return a.result},props:"altKey attrChange attrName bubbles button cancelable charCode clientX clientY ctrlKey currentTarget data detail eventPhase fromElement handler keyCode layerX layerY metaKey newValue offsetX offsetY pageX pageY prevValue relatedNode relatedTarget screenX screenY shiftKey srcElement target toElement view wheelDelta which".split(" "),
fix:function(a){if(a[c.expando])return a;var b=a;a=c.Event(b);for(var d=this.props.length,e;d;){e=this.props[--d];a[e]=b[e]}if(!a.target)a.target=a.srcElement||t;if(a.target.nodeType===3)a.target=a.target.parentNode;if(!a.relatedTarget&&a.fromElement)a.relatedTarget=a.fromElement===a.target?a.toElement:a.fromElement;if(a.pageX==null&&a.clientX!=null){b=t.documentElement;d=t.body;a.pageX=a.clientX+(b&&b.scrollLeft||d&&d.scrollLeft||0)-(b&&b.clientLeft||d&&d.clientLeft||0);a.pageY=a.clientY+(b&&b.scrollTop||
d&&d.scrollTop||0)-(b&&b.clientTop||d&&d.clientTop||0)}if(a.which==null&&(a.charCode!=null||a.keyCode!=null))a.which=a.charCode!=null?a.charCode:a.keyCode;if(!a.metaKey&&a.ctrlKey)a.metaKey=a.ctrlKey;if(!a.which&&a.button!==B)a.which=a.button&1?1:a.button&2?3:a.button&4?2:0;return a},guid:1E8,proxy:c.proxy,special:{ready:{setup:c.bindReady,teardown:c.noop},live:{add:function(a){c.event.add(this,Y(a.origType,a.selector),c.extend({},a,{handler:Ka,guid:a.handler.guid}))},remove:function(a){c.event.remove(this,
Y(a.origType,a.selector),a)}},beforeunload:{setup:function(a,b,d){if(c.isWindow(this))this.onbeforeunload=d},teardown:function(a,b){if(this.onbeforeunload===b)this.onbeforeunload=null}}}};c.removeEvent=t.removeEventListener?function(a,b,d){a.removeEventListener&&a.removeEventListener(b,d,false)}:function(a,b,d){a.detachEvent&&a.detachEvent("on"+b,d)};c.Event=function(a){if(!this.preventDefault)return new c.Event(a);if(a&&a.type){this.originalEvent=a;this.type=a.type}else this.type=a;this.timeStamp=
c.now();this[c.expando]=true};c.Event.prototype={preventDefault:function(){this.isDefaultPrevented=ca;var a=this.originalEvent;if(a)if(a.preventDefault)a.preventDefault();else a.returnValue=false},stopPropagation:function(){this.isPropagationStopped=ca;var a=this.originalEvent;if(a){a.stopPropagation&&a.stopPropagation();a.cancelBubble=true}},stopImmediatePropagation:function(){this.isImmediatePropagationStopped=ca;this.stopPropagation()},isDefaultPrevented:U,isPropagationStopped:U,isImmediatePropagationStopped:U};
var va=function(a){var b=a.relatedTarget;try{for(;b&&b!==this;)b=b.parentNode;if(b!==this){a.type=a.data;c.event.handle.apply(this,arguments)}}catch(d){}},wa=function(a){a.type=a.data;c.event.handle.apply(this,arguments)};c.each({mouseenter:"mouseover",mouseleave:"mouseout"},function(a,b){c.event.special[a]={setup:function(d){c.event.add(this,b,d&&d.selector?wa:va,a)},teardown:function(d){c.event.remove(this,b,d&&d.selector?wa:va)}}});if(!c.support.submitBubbles)c.event.special.submit={setup:function(){if(this.nodeName.toLowerCase()!==
"form"){c.event.add(this,"click.specialSubmit",function(a){var b=a.target,d=b.type;if((d==="submit"||d==="image")&&c(b).closest("form").length){a.liveFired=B;return la("submit",this,arguments)}});c.event.add(this,"keypress.specialSubmit",function(a){var b=a.target,d=b.type;if((d==="text"||d==="password")&&c(b).closest("form").length&&a.keyCode===13){a.liveFired=B;return la("submit",this,arguments)}})}else return false},teardown:function(){c.event.remove(this,".specialSubmit")}};if(!c.support.changeBubbles){var V,
xa=function(a){var b=a.type,d=a.value;if(b==="radio"||b==="checkbox")d=a.checked;else if(b==="select-multiple")d=a.selectedIndex>-1?c.map(a.options,function(e){return e.selected}).join("-"):"";else if(a.nodeName.toLowerCase()==="select")d=a.selectedIndex;return d},Z=function(a,b){var d=a.target,e,f;if(!(!ia.test(d.nodeName)||d.readOnly)){e=c.data(d,"_change_data");f=xa(d);if(a.type!=="focusout"||d.type!=="radio")c.data(d,"_change_data",f);if(!(e===B||f===e))if(e!=null||f){a.type="change";a.liveFired=
B;return c.event.trigger(a,b,d)}}};c.event.special.change={filters:{focusout:Z,beforedeactivate:Z,click:function(a){var b=a.target,d=b.type;if(d==="radio"||d==="checkbox"||b.nodeName.toLowerCase()==="select")return Z.call(this,a)},keydown:function(a){var b=a.target,d=b.type;if(a.keyCode===13&&b.nodeName.toLowerCase()!=="textarea"||a.keyCode===32&&(d==="checkbox"||d==="radio")||d==="select-multiple")return Z.call(this,a)},beforeactivate:function(a){a=a.target;c.data(a,"_change_data",xa(a))}},setup:function(){if(this.type===
"file")return false;for(var a in V)c.event.add(this,a+".specialChange",V[a]);return ia.test(this.nodeName)},teardown:function(){c.event.remove(this,".specialChange");return ia.test(this.nodeName)}};V=c.event.special.change.filters;V.focus=V.beforeactivate}t.addEventListener&&c.each({focus:"focusin",blur:"focusout"},function(a,b){function d(e){e=c.event.fix(e);e.type=b;return c.event.trigger(e,null,e.target)}c.event.special[b]={setup:function(){ua[b]++===0&&t.addEventListener(a,d,true)},teardown:function(){--ua[b]===
0&&t.removeEventListener(a,d,true)}}});c.each(["bind","one"],function(a,b){c.fn[b]=function(d,e,f){if(typeof d==="object"){for(var h in d)this[b](h,e,d[h],f);return this}if(c.isFunction(e)||e===false){f=e;e=B}var l=b==="one"?c.proxy(f,function(o){c(this).unbind(o,l);return f.apply(this,arguments)}):f;if(d==="unload"&&b!=="one")this.one(d,e,f);else{h=0;for(var k=this.length;h<k;h++)c.event.add(this[h],d,l,e)}return this}});c.fn.extend({unbind:function(a,b){if(typeof a==="object"&&!a.preventDefault)for(var d in a)this.unbind(d,
a[d]);else{d=0;for(var e=this.length;d<e;d++)c.event.remove(this[d],a,b)}return this},delegate:function(a,b,d,e){return this.live(b,d,e,a)},undelegate:function(a,b,d){return arguments.length===0?this.unbind("live"):this.die(b,null,d,a)},trigger:function(a,b){return this.each(function(){c.event.trigger(a,b,this)})},triggerHandler:function(a,b){if(this[0]){var d=c.Event(a);d.preventDefault();d.stopPropagation();c.event.trigger(d,b,this[0]);return d.result}},toggle:function(a){for(var b=arguments,d=
1;d<b.length;)c.proxy(a,b[d++]);return this.click(c.proxy(a,function(e){var f=(c.data(this,"lastToggle"+a.guid)||0)%d;c.data(this,"lastToggle"+a.guid,f+1);e.preventDefault();return b[f].apply(this,arguments)||false}))},hover:function(a,b){return this.mouseenter(a).mouseleave(b||a)}});var ya={focus:"focusin",blur:"focusout",mouseenter:"mouseover",mouseleave:"mouseout"};c.each(["live","die"],function(a,b){c.fn[b]=function(d,e,f,h){var l,k=0,o,x,r=h||this.selector;h=h?this:c(this.context);if(typeof d===
"object"&&!d.preventDefault){for(l in d)h[b](l,e,d[l],r);return this}if(c.isFunction(e)){f=e;e=B}for(d=(d||"").split(" ");(l=d[k++])!=null;){o=X.exec(l);x="";if(o){x=o[0];l=l.replace(X,"")}if(l==="hover")d.push("mouseenter"+x,"mouseleave"+x);else{o=l;if(l==="focus"||l==="blur"){d.push(ya[l]+x);l+=x}else l=(ya[l]||l)+x;if(b==="live"){x=0;for(var A=h.length;x<A;x++)c.event.add(h[x],"live."+Y(l,r),{data:e,selector:r,handler:f,origType:l,origHandler:f,preType:o})}else h.unbind("live."+Y(l,r),f)}}return this}});
c.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error".split(" "),function(a,b){c.fn[b]=function(d,e){if(e==null){e=d;d=null}return arguments.length>0?this.bind(b,d,e):this.trigger(b)};if(c.attrFn)c.attrFn[b]=true});E.attachEvent&&!E.addEventListener&&c(E).bind("unload",function(){for(var a in c.cache)if(c.cache[a].handle)try{c.event.remove(c.cache[a].handle.elem)}catch(b){}});
(function(){function a(g,i,n,m,p,q){p=0;for(var u=m.length;p<u;p++){var y=m[p];if(y){var F=false;for(y=y[g];y;){if(y.sizcache===n){F=m[y.sizset];break}if(y.nodeType===1&&!q){y.sizcache=n;y.sizset=p}if(y.nodeName.toLowerCase()===i){F=y;break}y=y[g]}m[p]=F}}}function b(g,i,n,m,p,q){p=0;for(var u=m.length;p<u;p++){var y=m[p];if(y){var F=false;for(y=y[g];y;){if(y.sizcache===n){F=m[y.sizset];break}if(y.nodeType===1){if(!q){y.sizcache=n;y.sizset=p}if(typeof i!=="string"){if(y===i){F=true;break}}else if(k.filter(i,
[y]).length>0){F=y;break}}y=y[g]}m[p]=F}}}var d=/((?:\((?:\([^()]+\)|[^()]+)+\)|\[(?:\[[^\[\]]*\]|['"][^'"]*['"]|[^\[\]'"]+)+\]|\\.|[^ >+~,(\[\\]+)+|[>+~])(\s*,\s*)?((?:.|\r|\n)*)/g,e=0,f=Object.prototype.toString,h=false,l=true;[0,0].sort(function(){l=false;return 0});var k=function(g,i,n,m){n=n||[];var p=i=i||t;if(i.nodeType!==1&&i.nodeType!==9)return[];if(!g||typeof g!=="string")return n;var q,u,y,F,M,N=true,O=k.isXML(i),D=[],R=g;do{d.exec("");if(q=d.exec(R)){R=q[3];D.push(q[1]);if(q[2]){F=q[3];
break}}}while(q);if(D.length>1&&x.exec(g))if(D.length===2&&o.relative[D[0]])u=L(D[0]+D[1],i);else for(u=o.relative[D[0]]?[i]:k(D.shift(),i);D.length;){g=D.shift();if(o.relative[g])g+=D.shift();u=L(g,u)}else{if(!m&&D.length>1&&i.nodeType===9&&!O&&o.match.ID.test(D[0])&&!o.match.ID.test(D[D.length-1])){q=k.find(D.shift(),i,O);i=q.expr?k.filter(q.expr,q.set)[0]:q.set[0]}if(i){q=m?{expr:D.pop(),set:C(m)}:k.find(D.pop(),D.length===1&&(D[0]==="~"||D[0]==="+")&&i.parentNode?i.parentNode:i,O);u=q.expr?k.filter(q.expr,
q.set):q.set;if(D.length>0)y=C(u);else N=false;for(;D.length;){q=M=D.pop();if(o.relative[M])q=D.pop();else M="";if(q==null)q=i;o.relative[M](y,q,O)}}else y=[]}y||(y=u);y||k.error(M||g);if(f.call(y)==="[object Array]")if(N)if(i&&i.nodeType===1)for(g=0;y[g]!=null;g++){if(y[g]&&(y[g]===true||y[g].nodeType===1&&k.contains(i,y[g])))n.push(u[g])}else for(g=0;y[g]!=null;g++)y[g]&&y[g].nodeType===1&&n.push(u[g]);else n.push.apply(n,y);else C(y,n);if(F){k(F,p,n,m);k.uniqueSort(n)}return n};k.uniqueSort=function(g){if(w){h=
l;g.sort(w);if(h)for(var i=1;i<g.length;i++)g[i]===g[i-1]&&g.splice(i--,1)}return g};k.matches=function(g,i){return k(g,null,null,i)};k.matchesSelector=function(g,i){return k(i,null,null,[g]).length>0};k.find=function(g,i,n){var m;if(!g)return[];for(var p=0,q=o.order.length;p<q;p++){var u,y=o.order[p];if(u=o.leftMatch[y].exec(g)){var F=u[1];u.splice(1,1);if(F.substr(F.length-1)!=="\\"){u[1]=(u[1]||"").replace(/\\/g,"");m=o.find[y](u,i,n);if(m!=null){g=g.replace(o.match[y],"");break}}}}m||(m=i.getElementsByTagName("*"));
return{set:m,expr:g}};k.filter=function(g,i,n,m){for(var p,q,u=g,y=[],F=i,M=i&&i[0]&&k.isXML(i[0]);g&&i.length;){for(var N in o.filter)if((p=o.leftMatch[N].exec(g))!=null&&p[2]){var O,D,R=o.filter[N];D=p[1];q=false;p.splice(1,1);if(D.substr(D.length-1)!=="\\"){if(F===y)y=[];if(o.preFilter[N])if(p=o.preFilter[N](p,F,n,y,m,M)){if(p===true)continue}else q=O=true;if(p)for(var j=0;(D=F[j])!=null;j++)if(D){O=R(D,p,j,F);var s=m^!!O;if(n&&O!=null)if(s)q=true;else F[j]=false;else if(s){y.push(D);q=true}}if(O!==
B){n||(F=y);g=g.replace(o.match[N],"");if(!q)return[];break}}}if(g===u)if(q==null)k.error(g);else break;u=g}return F};k.error=function(g){throw"Syntax error, unrecognized expression: "+g;};var o=k.selectors={order:["ID","NAME","TAG"],match:{ID:/#((?:[\w\u00c0-\uFFFF\-]|\\.)+)/,CLASS:/\.((?:[\w\u00c0-\uFFFF\-]|\\.)+)/,NAME:/\[name=['"]*((?:[\w\u00c0-\uFFFF\-]|\\.)+)['"]*\]/,ATTR:/\[\s*((?:[\w\u00c0-\uFFFF\-]|\\.)+)\s*(?:(\S?=)\s*(['"]*)(.*?)\3|)\s*\]/,TAG:/^((?:[\w\u00c0-\uFFFF\*\-]|\\.)+)/,CHILD:/:(only|nth|last|first)-child(?:\((even|odd|[\dn+\-]*)\))?/,
POS:/:(nth|eq|gt|lt|first|last|even|odd)(?:\((\d*)\))?(?=[^\-]|$)/,PSEUDO:/:((?:[\w\u00c0-\uFFFF\-]|\\.)+)(?:\((['"]?)((?:\([^\)]+\)|[^\(\)]*)+)\2\))?/},leftMatch:{},attrMap:{"class":"className","for":"htmlFor"},attrHandle:{href:function(g){return g.getAttribute("href")}},relative:{"+":function(g,i){var n=typeof i==="string",m=n&&!/\W/.test(i);n=n&&!m;if(m)i=i.toLowerCase();m=0;for(var p=g.length,q;m<p;m++)if(q=g[m]){for(;(q=q.previousSibling)&&q.nodeType!==1;);g[m]=n||q&&q.nodeName.toLowerCase()===
i?q||false:q===i}n&&k.filter(i,g,true)},">":function(g,i){var n,m=typeof i==="string",p=0,q=g.length;if(m&&!/\W/.test(i))for(i=i.toLowerCase();p<q;p++){if(n=g[p]){n=n.parentNode;g[p]=n.nodeName.toLowerCase()===i?n:false}}else{for(;p<q;p++)if(n=g[p])g[p]=m?n.parentNode:n.parentNode===i;m&&k.filter(i,g,true)}},"":function(g,i,n){var m,p=e++,q=b;if(typeof i==="string"&&!/\W/.test(i)){m=i=i.toLowerCase();q=a}q("parentNode",i,p,g,m,n)},"~":function(g,i,n){var m,p=e++,q=b;if(typeof i==="string"&&!/\W/.test(i)){m=
i=i.toLowerCase();q=a}q("previousSibling",i,p,g,m,n)}},find:{ID:function(g,i,n){if(typeof i.getElementById!=="undefined"&&!n)return(g=i.getElementById(g[1]))&&g.parentNode?[g]:[]},NAME:function(g,i){if(typeof i.getElementsByName!=="undefined"){for(var n=[],m=i.getElementsByName(g[1]),p=0,q=m.length;p<q;p++)m[p].getAttribute("name")===g[1]&&n.push(m[p]);return n.length===0?null:n}},TAG:function(g,i){return i.getElementsByTagName(g[1])}},preFilter:{CLASS:function(g,i,n,m,p,q){g=" "+g[1].replace(/\\/g,
"")+" ";if(q)return g;q=0;for(var u;(u=i[q])!=null;q++)if(u)if(p^(u.className&&(" "+u.className+" ").replace(/[\t\n]/g," ").indexOf(g)>=0))n||m.push(u);else if(n)i[q]=false;return false},ID:function(g){return g[1].replace(/\\/g,"")},TAG:function(g){return g[1].toLowerCase()},CHILD:function(g){if(g[1]==="nth"){var i=/(-?)(\d*)n((?:\+|-)?\d*)/.exec(g[2]==="even"&&"2n"||g[2]==="odd"&&"2n+1"||!/\D/.test(g[2])&&"0n+"+g[2]||g[2]);g[2]=i[1]+(i[2]||1)-0;g[3]=i[3]-0}g[0]=e++;return g},ATTR:function(g,i,n,
m,p,q){i=g[1].replace(/\\/g,"");if(!q&&o.attrMap[i])g[1]=o.attrMap[i];if(g[2]==="~=")g[4]=" "+g[4]+" ";return g},PSEUDO:function(g,i,n,m,p){if(g[1]==="not")if((d.exec(g[3])||"").length>1||/^\w/.test(g[3]))g[3]=k(g[3],null,null,i);else{g=k.filter(g[3],i,n,true^p);n||m.push.apply(m,g);return false}else if(o.match.POS.test(g[0])||o.match.CHILD.test(g[0]))return true;return g},POS:function(g){g.unshift(true);return g}},filters:{enabled:function(g){return g.disabled===false&&g.type!=="hidden"},disabled:function(g){return g.disabled===
true},checked:function(g){return g.checked===true},selected:function(g){return g.selected===true},parent:function(g){return!!g.firstChild},empty:function(g){return!g.firstChild},has:function(g,i,n){return!!k(n[3],g).length},header:function(g){return/h\d/i.test(g.nodeName)},text:function(g){return"text"===g.type},radio:function(g){return"radio"===g.type},checkbox:function(g){return"checkbox"===g.type},file:function(g){return"file"===g.type},password:function(g){return"password"===g.type},submit:function(g){return"submit"===
g.type},image:function(g){return"image"===g.type},reset:function(g){return"reset"===g.type},button:function(g){return"button"===g.type||g.nodeName.toLowerCase()==="button"},input:function(g){return/input|select|textarea|button/i.test(g.nodeName)}},setFilters:{first:function(g,i){return i===0},last:function(g,i,n,m){return i===m.length-1},even:function(g,i){return i%2===0},odd:function(g,i){return i%2===1},lt:function(g,i,n){return i<n[3]-0},gt:function(g,i,n){return i>n[3]-0},nth:function(g,i,n){return n[3]-
0===i},eq:function(g,i,n){return n[3]-0===i}},filter:{PSEUDO:function(g,i,n,m){var p=i[1],q=o.filters[p];if(q)return q(g,n,i,m);else if(p==="contains")return(g.textContent||g.innerText||k.getText([g])||"").indexOf(i[3])>=0;else if(p==="not"){i=i[3];n=0;for(m=i.length;n<m;n++)if(i[n]===g)return false;return true}else k.error("Syntax error, unrecognized expression: "+p)},CHILD:function(g,i){var n=i[1],m=g;switch(n){case "only":case "first":for(;m=m.previousSibling;)if(m.nodeType===1)return false;if(n===
"first")return true;m=g;case "last":for(;m=m.nextSibling;)if(m.nodeType===1)return false;return true;case "nth":n=i[2];var p=i[3];if(n===1&&p===0)return true;var q=i[0],u=g.parentNode;if(u&&(u.sizcache!==q||!g.nodeIndex)){var y=0;for(m=u.firstChild;m;m=m.nextSibling)if(m.nodeType===1)m.nodeIndex=++y;u.sizcache=q}m=g.nodeIndex-p;return n===0?m===0:m%n===0&&m/n>=0}},ID:function(g,i){return g.nodeType===1&&g.getAttribute("id")===i},TAG:function(g,i){return i==="*"&&g.nodeType===1||g.nodeName.toLowerCase()===
i},CLASS:function(g,i){return(" "+(g.className||g.getAttribute("class"))+" ").indexOf(i)>-1},ATTR:function(g,i){var n=i[1];n=o.attrHandle[n]?o.attrHandle[n](g):g[n]!=null?g[n]:g.getAttribute(n);var m=n+"",p=i[2],q=i[4];return n==null?p==="!=":p==="="?m===q:p==="*="?m.indexOf(q)>=0:p==="~="?(" "+m+" ").indexOf(q)>=0:!q?m&&n!==false:p==="!="?m!==q:p==="^="?m.indexOf(q)===0:p==="$="?m.substr(m.length-q.length)===q:p==="|="?m===q||m.substr(0,q.length+1)===q+"-":false},POS:function(g,i,n,m){var p=o.setFilters[i[2]];
if(p)return p(g,n,i,m)}}},x=o.match.POS,r=function(g,i){return"\\"+(i-0+1)},A;for(A in o.match){o.match[A]=RegExp(o.match[A].source+/(?![^\[]*\])(?![^\(]*\))/.source);o.leftMatch[A]=RegExp(/(^(?:.|\r|\n)*?)/.source+o.match[A].source.replace(/\\(\d+)/g,r))}var C=function(g,i){g=Array.prototype.slice.call(g,0);if(i){i.push.apply(i,g);return i}return g};try{Array.prototype.slice.call(t.documentElement.childNodes,0)}catch(J){C=function(g,i){var n=0,m=i||[];if(f.call(g)==="[object Array]")Array.prototype.push.apply(m,
g);else if(typeof g.length==="number")for(var p=g.length;n<p;n++)m.push(g[n]);else for(;g[n];n++)m.push(g[n]);return m}}var w,I;if(t.documentElement.compareDocumentPosition)w=function(g,i){if(g===i){h=true;return 0}if(!g.compareDocumentPosition||!i.compareDocumentPosition)return g.compareDocumentPosition?-1:1;return g.compareDocumentPosition(i)&4?-1:1};else{w=function(g,i){var n,m,p=[],q=[];n=g.parentNode;m=i.parentNode;var u=n;if(g===i){h=true;return 0}else if(n===m)return I(g,i);else if(n){if(!m)return 1}else return-1;
for(;u;){p.unshift(u);u=u.parentNode}for(u=m;u;){q.unshift(u);u=u.parentNode}n=p.length;m=q.length;for(u=0;u<n&&u<m;u++)if(p[u]!==q[u])return I(p[u],q[u]);return u===n?I(g,q[u],-1):I(p[u],i,1)};I=function(g,i,n){if(g===i)return n;for(g=g.nextSibling;g;){if(g===i)return-1;g=g.nextSibling}return 1}}k.getText=function(g){for(var i="",n,m=0;g[m];m++){n=g[m];if(n.nodeType===3||n.nodeType===4)i+=n.nodeValue;else if(n.nodeType!==8)i+=k.getText(n.childNodes)}return i};(function(){var g=t.createElement("div"),
i="script"+(new Date).getTime(),n=t.documentElement;g.innerHTML="<a name='"+i+"'/>";n.insertBefore(g,n.firstChild);if(t.getElementById(i)){o.find.ID=function(m,p,q){if(typeof p.getElementById!=="undefined"&&!q)return(p=p.getElementById(m[1]))?p.id===m[1]||typeof p.getAttributeNode!=="undefined"&&p.getAttributeNode("id").nodeValue===m[1]?[p]:B:[]};o.filter.ID=function(m,p){var q=typeof m.getAttributeNode!=="undefined"&&m.getAttributeNode("id");return m.nodeType===1&&q&&q.nodeValue===p}}n.removeChild(g);
n=g=null})();(function(){var g=t.createElement("div");g.appendChild(t.createComment(""));if(g.getElementsByTagName("*").length>0)o.find.TAG=function(i,n){var m=n.getElementsByTagName(i[1]);if(i[1]==="*"){for(var p=[],q=0;m[q];q++)m[q].nodeType===1&&p.push(m[q]);m=p}return m};g.innerHTML="<a href='#'></a>";if(g.firstChild&&typeof g.firstChild.getAttribute!=="undefined"&&g.firstChild.getAttribute("href")!=="#")o.attrHandle.href=function(i){return i.getAttribute("href",2)};g=null})();t.querySelectorAll&&
function(){var g=k,i=t.createElement("div");i.innerHTML="<p class='TEST'></p>";if(!(i.querySelectorAll&&i.querySelectorAll(".TEST").length===0)){k=function(m,p,q,u){p=p||t;m=m.replace(/\=\s*([^'"\]]*)\s*\]/g,"='$1']");if(!u&&!k.isXML(p))if(p.nodeType===9)try{return C(p.querySelectorAll(m),q)}catch(y){}else if(p.nodeType===1&&p.nodeName.toLowerCase()!=="object"){var F=p.getAttribute("id"),M=F||"__sizzle__";F||p.setAttribute("id",M);try{return C(p.querySelectorAll("#"+M+" "+m),q)}catch(N){}finally{F||
p.removeAttribute("id")}}return g(m,p,q,u)};for(var n in g)k[n]=g[n];i=null}}();(function(){var g=t.documentElement,i=g.matchesSelector||g.mozMatchesSelector||g.webkitMatchesSelector||g.msMatchesSelector,n=false;try{i.call(t.documentElement,"[test!='']:sizzle")}catch(m){n=true}if(i)k.matchesSelector=function(p,q){q=q.replace(/\=\s*([^'"\]]*)\s*\]/g,"='$1']");if(!k.isXML(p))try{if(n||!o.match.PSEUDO.test(q)&&!/!=/.test(q))return i.call(p,q)}catch(u){}return k(q,null,null,[p]).length>0}})();(function(){var g=
t.createElement("div");g.innerHTML="<div class='test e'></div><div class='test'></div>";if(!(!g.getElementsByClassName||g.getElementsByClassName("e").length===0)){g.lastChild.className="e";if(g.getElementsByClassName("e").length!==1){o.order.splice(1,0,"CLASS");o.find.CLASS=function(i,n,m){if(typeof n.getElementsByClassName!=="undefined"&&!m)return n.getElementsByClassName(i[1])};g=null}}})();k.contains=t.documentElement.contains?function(g,i){return g!==i&&(g.contains?g.contains(i):true)}:t.documentElement.compareDocumentPosition?
function(g,i){return!!(g.compareDocumentPosition(i)&16)}:function(){return false};k.isXML=function(g){return(g=(g?g.ownerDocument||g:0).documentElement)?g.nodeName!=="HTML":false};var L=function(g,i){for(var n,m=[],p="",q=i.nodeType?[i]:i;n=o.match.PSEUDO.exec(g);){p+=n[0];g=g.replace(o.match.PSEUDO,"")}g=o.relative[g]?g+"*":g;n=0;for(var u=q.length;n<u;n++)k(g,q[n],m);return k.filter(p,m)};c.find=k;c.expr=k.selectors;c.expr[":"]=c.expr.filters;c.unique=k.uniqueSort;c.text=k.getText;c.isXMLDoc=k.isXML;
c.contains=k.contains})();var Za=/Until$/,$a=/^(?:parents|prevUntil|prevAll)/,ab=/,/,Na=/^.[^:#\[\.,]*$/,bb=Array.prototype.slice,cb=c.expr.match.POS;c.fn.extend({find:function(a){for(var b=this.pushStack("","find",a),d=0,e=0,f=this.length;e<f;e++){d=b.length;c.find(a,this[e],b);if(e>0)for(var h=d;h<b.length;h++)for(var l=0;l<d;l++)if(b[l]===b[h]){b.splice(h--,1);break}}return b},has:function(a){var b=c(a);return this.filter(function(){for(var d=0,e=b.length;d<e;d++)if(c.contains(this,b[d]))return true})},
not:function(a){return this.pushStack(ma(this,a,false),"not",a)},filter:function(a){return this.pushStack(ma(this,a,true),"filter",a)},is:function(a){return!!a&&c.filter(a,this).length>0},closest:function(a,b){var d=[],e,f,h=this[0];if(c.isArray(a)){var l,k={},o=1;if(h&&a.length){e=0;for(f=a.length;e<f;e++){l=a[e];k[l]||(k[l]=c.expr.match.POS.test(l)?c(l,b||this.context):l)}for(;h&&h.ownerDocument&&h!==b;){for(l in k){e=k[l];if(e.jquery?e.index(h)>-1:c(h).is(e))d.push({selector:l,elem:h,level:o})}h=
h.parentNode;o++}}return d}l=cb.test(a)?c(a,b||this.context):null;e=0;for(f=this.length;e<f;e++)for(h=this[e];h;)if(l?l.index(h)>-1:c.find.matchesSelector(h,a)){d.push(h);break}else{h=h.parentNode;if(!h||!h.ownerDocument||h===b)break}d=d.length>1?c.unique(d):d;return this.pushStack(d,"closest",a)},index:function(a){if(!a||typeof a==="string")return c.inArray(this[0],a?c(a):this.parent().children());return c.inArray(a.jquery?a[0]:a,this)},add:function(a,b){var d=typeof a==="string"?c(a,b||this.context):
c.makeArray(a),e=c.merge(this.get(),d);return this.pushStack(!d[0]||!d[0].parentNode||d[0].parentNode.nodeType===11||!e[0]||!e[0].parentNode||e[0].parentNode.nodeType===11?e:c.unique(e))},andSelf:function(){return this.add(this.prevObject)}});c.each({parent:function(a){return(a=a.parentNode)&&a.nodeType!==11?a:null},parents:function(a){return c.dir(a,"parentNode")},parentsUntil:function(a,b,d){return c.dir(a,"parentNode",d)},next:function(a){return c.nth(a,2,"nextSibling")},prev:function(a){return c.nth(a,
2,"previousSibling")},nextAll:function(a){return c.dir(a,"nextSibling")},prevAll:function(a){return c.dir(a,"previousSibling")},nextUntil:function(a,b,d){return c.dir(a,"nextSibling",d)},prevUntil:function(a,b,d){return c.dir(a,"previousSibling",d)},siblings:function(a){return c.sibling(a.parentNode.firstChild,a)},children:function(a){return c.sibling(a.firstChild)},contents:function(a){return c.nodeName(a,"iframe")?a.contentDocument||a.contentWindow.document:c.makeArray(a.childNodes)}},function(a,
b){c.fn[a]=function(d,e){var f=c.map(this,b,d);Za.test(a)||(e=d);if(e&&typeof e==="string")f=c.filter(e,f);f=this.length>1?c.unique(f):f;if((this.length>1||ab.test(e))&&$a.test(a))f=f.reverse();return this.pushStack(f,a,bb.call(arguments).join(","))}});c.extend({filter:function(a,b,d){if(d)a=":not("+a+")";return b.length===1?c.find.matchesSelector(b[0],a)?[b[0]]:[]:c.find.matches(a,b)},dir:function(a,b,d){var e=[];for(a=a[b];a&&a.nodeType!==9&&(d===B||a.nodeType!==1||!c(a).is(d));){a.nodeType===1&&
e.push(a);a=a[b]}return e},nth:function(a,b,d){b=b||1;for(var e=0;a;a=a[d])if(a.nodeType===1&&++e===b)break;return a},sibling:function(a,b){for(var d=[];a;a=a.nextSibling)a.nodeType===1&&a!==b&&d.push(a);return d}});var za=/ jQuery\d+="(?:\d+|null)"/g,$=/^\s+/,Aa=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/ig,Ba=/<([\w:]+)/,db=/<tbody/i,eb=/<|&#?\w+;/,Ca=/<(?:script|object|embed|option|style)/i,Da=/checked\s*(?:[^=]|=\s*.checked.)/i,fb=/\=([^="'>\s]+\/)>/g,P={option:[1,
"<select multiple='multiple'>","</select>"],legend:[1,"<fieldset>","</fieldset>"],thead:[1,"<table>","</table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],col:[2,"<table><tbody></tbody><colgroup>","</colgroup></table>"],area:[1,"<map>","</map>"],_default:[0,"",""]};P.optgroup=P.option;P.tbody=P.tfoot=P.colgroup=P.caption=P.thead;P.th=P.td;if(!c.support.htmlSerialize)P._default=[1,"div<div>","</div>"];c.fn.extend({text:function(a){if(c.isFunction(a))return this.each(function(b){var d=
c(this);d.text(a.call(this,b,d.text()))});if(typeof a!=="object"&&a!==B)return this.empty().append((this[0]&&this[0].ownerDocument||t).createTextNode(a));return c.text(this)},wrapAll:function(a){if(c.isFunction(a))return this.each(function(d){c(this).wrapAll(a.call(this,d))});if(this[0]){var b=c(a,this[0].ownerDocument).eq(0).clone(true);this[0].parentNode&&b.insertBefore(this[0]);b.map(function(){for(var d=this;d.firstChild&&d.firstChild.nodeType===1;)d=d.firstChild;return d}).append(this)}return this},
wrapInner:function(a){if(c.isFunction(a))return this.each(function(b){c(this).wrapInner(a.call(this,b))});return this.each(function(){var b=c(this),d=b.contents();d.length?d.wrapAll(a):b.append(a)})},wrap:function(a){return this.each(function(){c(this).wrapAll(a)})},unwrap:function(){return this.parent().each(function(){c.nodeName(this,"body")||c(this).replaceWith(this.childNodes)}).end()},append:function(){return this.domManip(arguments,true,function(a){this.nodeType===1&&this.appendChild(a)})},
prepend:function(){return this.domManip(arguments,true,function(a){this.nodeType===1&&this.insertBefore(a,this.firstChild)})},before:function(){if(this[0]&&this[0].parentNode)return this.domManip(arguments,false,function(b){this.parentNode.insertBefore(b,this)});else if(arguments.length){var a=c(arguments[0]);a.push.apply(a,this.toArray());return this.pushStack(a,"before",arguments)}},after:function(){if(this[0]&&this[0].parentNode)return this.domManip(arguments,false,function(b){this.parentNode.insertBefore(b,
this.nextSibling)});else if(arguments.length){var a=this.pushStack(this,"after",arguments);a.push.apply(a,c(arguments[0]).toArray());return a}},remove:function(a,b){for(var d=0,e;(e=this[d])!=null;d++)if(!a||c.filter(a,[e]).length){if(!b&&e.nodeType===1){c.cleanData(e.getElementsByTagName("*"));c.cleanData([e])}e.parentNode&&e.parentNode.removeChild(e)}return this},empty:function(){for(var a=0,b;(b=this[a])!=null;a++)for(b.nodeType===1&&c.cleanData(b.getElementsByTagName("*"));b.firstChild;)b.removeChild(b.firstChild);
return this},clone:function(a){var b=this.map(function(){if(!c.support.noCloneEvent&&!c.isXMLDoc(this)){var d=this.outerHTML,e=this.ownerDocument;if(!d){d=e.createElement("div");d.appendChild(this.cloneNode(true));d=d.innerHTML}return c.clean([d.replace(za,"").replace(fb,'="$1">').replace($,"")],e)[0]}else return this.cloneNode(true)});if(a===true){na(this,b);na(this.find("*"),b.find("*"))}return b},html:function(a){if(a===B)return this[0]&&this[0].nodeType===1?this[0].innerHTML.replace(za,""):null;
else if(typeof a==="string"&&!Ca.test(a)&&(c.support.leadingWhitespace||!$.test(a))&&!P[(Ba.exec(a)||["",""])[1].toLowerCase()]){a=a.replace(Aa,"<$1></$2>");try{for(var b=0,d=this.length;b<d;b++)if(this[b].nodeType===1){c.cleanData(this[b].getElementsByTagName("*"));this[b].innerHTML=a}}catch(e){this.empty().append(a)}}else c.isFunction(a)?this.each(function(f){var h=c(this);h.html(a.call(this,f,h.html()))}):this.empty().append(a);return this},replaceWith:function(a){if(this[0]&&this[0].parentNode){if(c.isFunction(a))return this.each(function(b){var d=
c(this),e=d.html();d.replaceWith(a.call(this,b,e))});if(typeof a!=="string")a=c(a).detach();return this.each(function(){var b=this.nextSibling,d=this.parentNode;c(this).remove();b?c(b).before(a):c(d).append(a)})}else return this.pushStack(c(c.isFunction(a)?a():a),"replaceWith",a)},detach:function(a){return this.remove(a,true)},domManip:function(a,b,d){var e,f,h,l=a[0],k=[];if(!c.support.checkClone&&arguments.length===3&&typeof l==="string"&&Da.test(l))return this.each(function(){c(this).domManip(a,
b,d,true)});if(c.isFunction(l))return this.each(function(x){var r=c(this);a[0]=l.call(this,x,b?r.html():B);r.domManip(a,b,d)});if(this[0]){e=l&&l.parentNode;e=c.support.parentNode&&e&&e.nodeType===11&&e.childNodes.length===this.length?{fragment:e}:c.buildFragment(a,this,k);h=e.fragment;if(f=h.childNodes.length===1?h=h.firstChild:h.firstChild){b=b&&c.nodeName(f,"tr");f=0;for(var o=this.length;f<o;f++)d.call(b?c.nodeName(this[f],"table")?this[f].getElementsByTagName("tbody")[0]||this[f].appendChild(this[f].ownerDocument.createElement("tbody")):
this[f]:this[f],f>0||e.cacheable||this.length>1?h.cloneNode(true):h)}k.length&&c.each(k,Oa)}return this}});c.buildFragment=function(a,b,d){var e,f,h;b=b&&b[0]?b[0].ownerDocument||b[0]:t;if(a.length===1&&typeof a[0]==="string"&&a[0].length<512&&b===t&&!Ca.test(a[0])&&(c.support.checkClone||!Da.test(a[0]))){f=true;if(h=c.fragments[a[0]])if(h!==1)e=h}if(!e){e=b.createDocumentFragment();c.clean(a,b,e,d)}if(f)c.fragments[a[0]]=h?e:1;return{fragment:e,cacheable:f}};c.fragments={};c.each({appendTo:"append",
prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(a,b){c.fn[a]=function(d){var e=[];d=c(d);var f=this.length===1&&this[0].parentNode;if(f&&f.nodeType===11&&f.childNodes.length===1&&d.length===1){d[b](this[0]);return this}else{f=0;for(var h=d.length;f<h;f++){var l=(f>0?this.clone(true):this).get();c(d[f])[b](l);e=e.concat(l)}return this.pushStack(e,a,d.selector)}}});c.extend({clean:function(a,b,d,e){b=b||t;if(typeof b.createElement==="undefined")b=b.ownerDocument||
b[0]&&b[0].ownerDocument||t;for(var f=[],h=0,l;(l=a[h])!=null;h++){if(typeof l==="number")l+="";if(l){if(typeof l==="string"&&!eb.test(l))l=b.createTextNode(l);else if(typeof l==="string"){l=l.replace(Aa,"<$1></$2>");var k=(Ba.exec(l)||["",""])[1].toLowerCase(),o=P[k]||P._default,x=o[0],r=b.createElement("div");for(r.innerHTML=o[1]+l+o[2];x--;)r=r.lastChild;if(!c.support.tbody){x=db.test(l);k=k==="table"&&!x?r.firstChild&&r.firstChild.childNodes:o[1]==="<table>"&&!x?r.childNodes:[];for(o=k.length-
1;o>=0;--o)c.nodeName(k[o],"tbody")&&!k[o].childNodes.length&&k[o].parentNode.removeChild(k[o])}!c.support.leadingWhitespace&&$.test(l)&&r.insertBefore(b.createTextNode($.exec(l)[0]),r.firstChild);l=r.childNodes}if(l.nodeType)f.push(l);else f=c.merge(f,l)}}if(d)for(h=0;f[h];h++)if(e&&c.nodeName(f[h],"script")&&(!f[h].type||f[h].type.toLowerCase()==="text/javascript"))e.push(f[h].parentNode?f[h].parentNode.removeChild(f[h]):f[h]);else{f[h].nodeType===1&&f.splice.apply(f,[h+1,0].concat(c.makeArray(f[h].getElementsByTagName("script"))));
d.appendChild(f[h])}return f},cleanData:function(a){for(var b,d,e=c.cache,f=c.event.special,h=c.support.deleteExpando,l=0,k;(k=a[l])!=null;l++)if(!(k.nodeName&&c.noData[k.nodeName.toLowerCase()]))if(d=k[c.expando]){if((b=e[d])&&b.events)for(var o in b.events)f[o]?c.event.remove(k,o):c.removeEvent(k,o,b.handle);if(h)delete k[c.expando];else k.removeAttribute&&k.removeAttribute(c.expando);delete e[d]}}});var Ea=/alpha\([^)]*\)/i,gb=/opacity=([^)]*)/,hb=/-([a-z])/ig,ib=/([A-Z])/g,Fa=/^-?\d+(?:px)?$/i,
jb=/^-?\d/,kb={position:"absolute",visibility:"hidden",display:"block"},Pa=["Left","Right"],Qa=["Top","Bottom"],W,Ga,aa,lb=function(a,b){return b.toUpperCase()};c.fn.css=function(a,b){if(arguments.length===2&&b===B)return this;return c.access(this,a,b,true,function(d,e,f){return f!==B?c.style(d,e,f):c.css(d,e)})};c.extend({cssHooks:{opacity:{get:function(a,b){if(b){var d=W(a,"opacity","opacity");return d===""?"1":d}else return a.style.opacity}}},cssNumber:{zIndex:true,fontWeight:true,opacity:true,
zoom:true,lineHeight:true},cssProps:{"float":c.support.cssFloat?"cssFloat":"styleFloat"},style:function(a,b,d,e){if(!(!a||a.nodeType===3||a.nodeType===8||!a.style)){var f,h=c.camelCase(b),l=a.style,k=c.cssHooks[h];b=c.cssProps[h]||h;if(d!==B){if(!(typeof d==="number"&&isNaN(d)||d==null)){if(typeof d==="number"&&!c.cssNumber[h])d+="px";if(!k||!("set"in k)||(d=k.set(a,d))!==B)try{l[b]=d}catch(o){}}}else{if(k&&"get"in k&&(f=k.get(a,false,e))!==B)return f;return l[b]}}},css:function(a,b,d){var e,f=c.camelCase(b),
h=c.cssHooks[f];b=c.cssProps[f]||f;if(h&&"get"in h&&(e=h.get(a,true,d))!==B)return e;else if(W)return W(a,b,f)},swap:function(a,b,d){var e={},f;for(f in b){e[f]=a.style[f];a.style[f]=b[f]}d.call(a);for(f in b)a.style[f]=e[f]},camelCase:function(a){return a.replace(hb,lb)}});c.curCSS=c.css;c.each(["height","width"],function(a,b){c.cssHooks[b]={get:function(d,e,f){var h;if(e){if(d.offsetWidth!==0)h=oa(d,b,f);else c.swap(d,kb,function(){h=oa(d,b,f)});if(h<=0){h=W(d,b,b);if(h==="0px"&&aa)h=aa(d,b,b);
if(h!=null)return h===""||h==="auto"?"0px":h}if(h<0||h==null){h=d.style[b];return h===""||h==="auto"?"0px":h}return typeof h==="string"?h:h+"px"}},set:function(d,e){if(Fa.test(e)){e=parseFloat(e);if(e>=0)return e+"px"}else return e}}});if(!c.support.opacity)c.cssHooks.opacity={get:function(a,b){return gb.test((b&&a.currentStyle?a.currentStyle.filter:a.style.filter)||"")?parseFloat(RegExp.$1)/100+"":b?"1":""},set:function(a,b){var d=a.style;d.zoom=1;var e=c.isNaN(b)?"":"alpha(opacity="+b*100+")",f=
d.filter||"";d.filter=Ea.test(f)?f.replace(Ea,e):d.filter+" "+e}};if(t.defaultView&&t.defaultView.getComputedStyle)Ga=function(a,b,d){var e;d=d.replace(ib,"-$1").toLowerCase();if(!(b=a.ownerDocument.defaultView))return B;if(b=b.getComputedStyle(a,null)){e=b.getPropertyValue(d);if(e===""&&!c.contains(a.ownerDocument.documentElement,a))e=c.style(a,d)}return e};if(t.documentElement.currentStyle)aa=function(a,b){var d,e,f=a.currentStyle&&a.currentStyle[b],h=a.style;if(!Fa.test(f)&&jb.test(f)){d=h.left;
e=a.runtimeStyle.left;a.runtimeStyle.left=a.currentStyle.left;h.left=b==="fontSize"?"1em":f||0;f=h.pixelLeft+"px";h.left=d;a.runtimeStyle.left=e}return f===""?"auto":f};W=Ga||aa;if(c.expr&&c.expr.filters){c.expr.filters.hidden=function(a){var b=a.offsetHeight;return a.offsetWidth===0&&b===0||!c.support.reliableHiddenOffsets&&(a.style.display||c.css(a,"display"))==="none"};c.expr.filters.visible=function(a){return!c.expr.filters.hidden(a)}}var mb=c.now(),nb=/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
ob=/^(?:select|textarea)/i,pb=/^(?:color|date|datetime|email|hidden|month|number|password|range|search|tel|text|time|url|week)$/i,qb=/^(?:GET|HEAD)$/,Ra=/\[\]$/,T=/\=\?(&|$)/,ja=/\?/,rb=/([?&])_=[^&]*/,sb=/^(\w+:)?\/\/([^\/?#]+)/,tb=/%20/g,ub=/#.*$/,Ha=c.fn.load;c.fn.extend({load:function(a,b,d){if(typeof a!=="string"&&Ha)return Ha.apply(this,arguments);else if(!this.length)return this;var e=a.indexOf(" ");if(e>=0){var f=a.slice(e,a.length);a=a.slice(0,e)}e="GET";if(b)if(c.isFunction(b)){d=b;b=null}else if(typeof b===
"object"){b=c.param(b,c.ajaxSettings.traditional);e="POST"}var h=this;c.ajax({url:a,type:e,dataType:"html",data:b,complete:function(l,k){if(k==="success"||k==="notmodified")h.html(f?c("<div>").append(l.responseText.replace(nb,"")).find(f):l.responseText);d&&h.each(d,[l.responseText,k,l])}});return this},serialize:function(){return c.param(this.serializeArray())},serializeArray:function(){return this.map(function(){return this.elements?c.makeArray(this.elements):this}).filter(function(){return this.name&&
!this.disabled&&(this.checked||ob.test(this.nodeName)||pb.test(this.type))}).map(function(a,b){var d=c(this).val();return d==null?null:c.isArray(d)?c.map(d,function(e){return{name:b.name,value:e}}):{name:b.name,value:d}}).get()}});c.each("ajaxStart ajaxStop ajaxComplete ajaxError ajaxSuccess ajaxSend".split(" "),function(a,b){c.fn[b]=function(d){return this.bind(b,d)}});c.extend({get:function(a,b,d,e){if(c.isFunction(b)){e=e||d;d=b;b=null}return c.ajax({type:"GET",url:a,data:b,success:d,dataType:e})},
getScript:function(a,b){return c.get(a,null,b,"script")},getJSON:function(a,b,d){return c.get(a,b,d,"json")},post:function(a,b,d,e){if(c.isFunction(b)){e=e||d;d=b;b={}}return c.ajax({type:"POST",url:a,data:b,success:d,dataType:e})},ajaxSetup:function(a){c.extend(c.ajaxSettings,a)},ajaxSettings:{url:location.href,global:true,type:"GET",contentType:"application/x-www-form-urlencoded",processData:true,async:true,xhr:function(){return new E.XMLHttpRequest},accepts:{xml:"application/xml, text/xml",html:"text/html",
script:"text/javascript, application/javascript",json:"application/json, text/javascript",text:"text/plain",_default:"*/*"}},ajax:function(a){var b=c.extend(true,{},c.ajaxSettings,a),d,e,f,h=b.type.toUpperCase(),l=qb.test(h);b.url=b.url.replace(ub,"");b.context=a&&a.context!=null?a.context:b;if(b.data&&b.processData&&typeof b.data!=="string")b.data=c.param(b.data,b.traditional);if(b.dataType==="jsonp"){if(h==="GET")T.test(b.url)||(b.url+=(ja.test(b.url)?"&":"?")+(b.jsonp||"callback")+"=?");else if(!b.data||
!T.test(b.data))b.data=(b.data?b.data+"&":"")+(b.jsonp||"callback")+"=?";b.dataType="json"}if(b.dataType==="json"&&(b.data&&T.test(b.data)||T.test(b.url))){d=b.jsonpCallback||"jsonp"+mb++;if(b.data)b.data=(b.data+"").replace(T,"="+d+"$1");b.url=b.url.replace(T,"="+d+"$1");b.dataType="script";var k=E[d];E[d]=function(m){if(c.isFunction(k))k(m);else{E[d]=B;try{delete E[d]}catch(p){}}f=m;c.handleSuccess(b,w,e,f);c.handleComplete(b,w,e,f);r&&r.removeChild(A)}}if(b.dataType==="script"&&b.cache===null)b.cache=
false;if(b.cache===false&&l){var o=c.now(),x=b.url.replace(rb,"$1_="+o);b.url=x+(x===b.url?(ja.test(b.url)?"&":"?")+"_="+o:"")}if(b.data&&l)b.url+=(ja.test(b.url)?"&":"?")+b.data;b.global&&c.active++===0&&c.event.trigger("ajaxStart");o=(o=sb.exec(b.url))&&(o[1]&&o[1].toLowerCase()!==location.protocol||o[2].toLowerCase()!==location.host);if(b.dataType==="script"&&h==="GET"&&o){var r=t.getElementsByTagName("head")[0]||t.documentElement,A=t.createElement("script");if(b.scriptCharset)A.charset=b.scriptCharset;
A.src=b.url;if(!d){var C=false;A.onload=A.onreadystatechange=function(){if(!C&&(!this.readyState||this.readyState==="loaded"||this.readyState==="complete")){C=true;c.handleSuccess(b,w,e,f);c.handleComplete(b,w,e,f);A.onload=A.onreadystatechange=null;r&&A.parentNode&&r.removeChild(A)}}}r.insertBefore(A,r.firstChild);return B}var J=false,w=b.xhr();if(w){b.username?w.open(h,b.url,b.async,b.username,b.password):w.open(h,b.url,b.async);try{if(b.data!=null&&!l||a&&a.contentType)w.setRequestHeader("Content-Type",
b.contentType);if(b.ifModified){c.lastModified[b.url]&&w.setRequestHeader("If-Modified-Since",c.lastModified[b.url]);c.etag[b.url]&&w.setRequestHeader("If-None-Match",c.etag[b.url])}o||w.setRequestHeader("X-Requested-With","XMLHttpRequest");w.setRequestHeader("Accept",b.dataType&&b.accepts[b.dataType]?b.accepts[b.dataType]+", */*; q=0.01":b.accepts._default)}catch(I){}if(b.beforeSend&&b.beforeSend.call(b.context,w,b)===false){b.global&&c.active--===1&&c.event.trigger("ajaxStop");w.abort();return false}b.global&&
c.triggerGlobal(b,"ajaxSend",[w,b]);var L=w.onreadystatechange=function(m){if(!w||w.readyState===0||m==="abort"){J||c.handleComplete(b,w,e,f);J=true;if(w)w.onreadystatechange=c.noop}else if(!J&&w&&(w.readyState===4||m==="timeout")){J=true;w.onreadystatechange=c.noop;e=m==="timeout"?"timeout":!c.httpSuccess(w)?"error":b.ifModified&&c.httpNotModified(w,b.url)?"notmodified":"success";var p;if(e==="success")try{f=c.httpData(w,b.dataType,b)}catch(q){e="parsererror";p=q}if(e==="success"||e==="notmodified")d||
c.handleSuccess(b,w,e,f);else c.handleError(b,w,e,p);d||c.handleComplete(b,w,e,f);m==="timeout"&&w.abort();if(b.async)w=null}};try{var g=w.abort;w.abort=function(){w&&Function.prototype.call.call(g,w);L("abort")}}catch(i){}b.async&&b.timeout>0&&setTimeout(function(){w&&!J&&L("timeout")},b.timeout);try{w.send(l||b.data==null?null:b.data)}catch(n){c.handleError(b,w,null,n);c.handleComplete(b,w,e,f)}b.async||L();return w}},param:function(a,b){var d=[],e=function(h,l){l=c.isFunction(l)?l():l;d[d.length]=
encodeURIComponent(h)+"="+encodeURIComponent(l)};if(b===B)b=c.ajaxSettings.traditional;if(c.isArray(a)||a.jquery)c.each(a,function(){e(this.name,this.value)});else for(var f in a)da(f,a[f],b,e);return d.join("&").replace(tb,"+")}});c.extend({active:0,lastModified:{},etag:{},handleError:function(a,b,d,e){a.error&&a.error.call(a.context,b,d,e);a.global&&c.triggerGlobal(a,"ajaxError",[b,a,e])},handleSuccess:function(a,b,d,e){a.success&&a.success.call(a.context,e,d,b);a.global&&c.triggerGlobal(a,"ajaxSuccess",
[b,a])},handleComplete:function(a,b,d){a.complete&&a.complete.call(a.context,b,d);a.global&&c.triggerGlobal(a,"ajaxComplete",[b,a]);a.global&&c.active--===1&&c.event.trigger("ajaxStop")},triggerGlobal:function(a,b,d){(a.context&&a.context.url==null?c(a.context):c.event).trigger(b,d)},httpSuccess:function(a){try{return!a.status&&location.protocol==="file:"||a.status>=200&&a.status<300||a.status===304||a.status===1223}catch(b){}return false},httpNotModified:function(a,b){var d=a.getResponseHeader("Last-Modified"),
e=a.getResponseHeader("Etag");if(d)c.lastModified[b]=d;if(e)c.etag[b]=e;return a.status===304},httpData:function(a,b,d){var e=a.getResponseHeader("content-type")||"",f=b==="xml"||!b&&e.indexOf("xml")>=0;a=f?a.responseXML:a.responseText;f&&a.documentElement.nodeName==="parsererror"&&c.error("parsererror");if(d&&d.dataFilter)a=d.dataFilter(a,b);if(typeof a==="string")if(b==="json"||!b&&e.indexOf("json")>=0)a=c.parseJSON(a);else if(b==="script"||!b&&e.indexOf("javascript")>=0)c.globalEval(a);return a}});
if(E.ActiveXObject)c.ajaxSettings.xhr=function(){if(E.location.protocol!=="file:")try{return new E.XMLHttpRequest}catch(a){}try{return new E.ActiveXObject("Microsoft.XMLHTTP")}catch(b){}};c.support.ajax=!!c.ajaxSettings.xhr();var ea={},vb=/^(?:toggle|show|hide)$/,wb=/^([+\-]=)?([\d+.\-]+)(.*)$/,ba,pa=[["height","marginTop","marginBottom","paddingTop","paddingBottom"],["width","marginLeft","marginRight","paddingLeft","paddingRight"],["opacity"]];c.fn.extend({show:function(a,b,d){if(a||a===0)return this.animate(S("show",
3),a,b,d);else{d=0;for(var e=this.length;d<e;d++){a=this[d];b=a.style.display;if(!c.data(a,"olddisplay")&&b==="none")b=a.style.display="";b===""&&c.css(a,"display")==="none"&&c.data(a,"olddisplay",qa(a.nodeName))}for(d=0;d<e;d++){a=this[d];b=a.style.display;if(b===""||b==="none")a.style.display=c.data(a,"olddisplay")||""}return this}},hide:function(a,b,d){if(a||a===0)return this.animate(S("hide",3),a,b,d);else{a=0;for(b=this.length;a<b;a++){d=c.css(this[a],"display");d!=="none"&&c.data(this[a],"olddisplay",
d)}for(a=0;a<b;a++)this[a].style.display="none";return this}},_toggle:c.fn.toggle,toggle:function(a,b,d){var e=typeof a==="boolean";if(c.isFunction(a)&&c.isFunction(b))this._toggle.apply(this,arguments);else a==null||e?this.each(function(){var f=e?a:c(this).is(":hidden");c(this)[f?"show":"hide"]()}):this.animate(S("toggle",3),a,b,d);return this},fadeTo:function(a,b,d,e){return this.filter(":hidden").css("opacity",0).show().end().animate({opacity:b},a,d,e)},animate:function(a,b,d,e){var f=c.speed(b,
d,e);if(c.isEmptyObject(a))return this.each(f.complete);return this[f.queue===false?"each":"queue"](function(){var h=c.extend({},f),l,k=this.nodeType===1,o=k&&c(this).is(":hidden"),x=this;for(l in a){var r=c.camelCase(l);if(l!==r){a[r]=a[l];delete a[l];l=r}if(a[l]==="hide"&&o||a[l]==="show"&&!o)return h.complete.call(this);if(k&&(l==="height"||l==="width")){h.overflow=[this.style.overflow,this.style.overflowX,this.style.overflowY];if(c.css(this,"display")==="inline"&&c.css(this,"float")==="none")if(c.support.inlineBlockNeedsLayout)if(qa(this.nodeName)===
"inline")this.style.display="inline-block";else{this.style.display="inline";this.style.zoom=1}else this.style.display="inline-block"}if(c.isArray(a[l])){(h.specialEasing=h.specialEasing||{})[l]=a[l][1];a[l]=a[l][0]}}if(h.overflow!=null)this.style.overflow="hidden";h.curAnim=c.extend({},a);c.each(a,function(A,C){var J=new c.fx(x,h,A);if(vb.test(C))J[C==="toggle"?o?"show":"hide":C](a);else{var w=wb.exec(C),I=J.cur()||0;if(w){var L=parseFloat(w[2]),g=w[3]||"px";if(g!=="px"){c.style(x,A,(L||1)+g);I=(L||
1)/J.cur()*I;c.style(x,A,I+g)}if(w[1])L=(w[1]==="-="?-1:1)*L+I;J.custom(I,L,g)}else J.custom(I,C,"")}});return true})},stop:function(a,b){var d=c.timers;a&&this.queue([]);this.each(function(){for(var e=d.length-1;e>=0;e--)if(d[e].elem===this){b&&d[e](true);d.splice(e,1)}});b||this.dequeue();return this}});c.each({slideDown:S("show",1),slideUp:S("hide",1),slideToggle:S("toggle",1),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(a,b){c.fn[a]=function(d,e,f){return this.animate(b,
d,e,f)}});c.extend({speed:function(a,b,d){var e=a&&typeof a==="object"?c.extend({},a):{complete:d||!d&&b||c.isFunction(a)&&a,duration:a,easing:d&&b||b&&!c.isFunction(b)&&b};e.duration=c.fx.off?0:typeof e.duration==="number"?e.duration:e.duration in c.fx.speeds?c.fx.speeds[e.duration]:c.fx.speeds._default;e.old=e.complete;e.complete=function(){e.queue!==false&&c(this).dequeue();c.isFunction(e.old)&&e.old.call(this)};return e},easing:{linear:function(a,b,d,e){return d+e*a},swing:function(a,b,d,e){return(-Math.cos(a*
Math.PI)/2+0.5)*e+d}},timers:[],fx:function(a,b,d){this.options=b;this.elem=a;this.prop=d;if(!b.orig)b.orig={}}});c.fx.prototype={update:function(){this.options.step&&this.options.step.call(this.elem,this.now,this);(c.fx.step[this.prop]||c.fx.step._default)(this)},cur:function(){if(this.elem[this.prop]!=null&&(!this.elem.style||this.elem.style[this.prop]==null))return this.elem[this.prop];var a=parseFloat(c.css(this.elem,this.prop));return a&&a>-1E4?a:0},custom:function(a,b,d){function e(l){return f.step(l)}
var f=this,h=c.fx;this.startTime=c.now();this.start=a;this.end=b;this.unit=d||this.unit||"px";this.now=this.start;this.pos=this.state=0;e.elem=this.elem;if(e()&&c.timers.push(e)&&!ba)ba=setInterval(h.tick,h.interval)},show:function(){this.options.orig[this.prop]=c.style(this.elem,this.prop);this.options.show=true;this.custom(this.prop==="width"||this.prop==="height"?1:0,this.cur());c(this.elem).show()},hide:function(){this.options.orig[this.prop]=c.style(this.elem,this.prop);this.options.hide=true;
this.custom(this.cur(),0)},step:function(a){var b=c.now(),d=true;if(a||b>=this.options.duration+this.startTime){this.now=this.end;this.pos=this.state=1;this.update();this.options.curAnim[this.prop]=true;for(var e in this.options.curAnim)if(this.options.curAnim[e]!==true)d=false;if(d){if(this.options.overflow!=null&&!c.support.shrinkWrapBlocks){var f=this.elem,h=this.options;c.each(["","X","Y"],function(k,o){f.style["overflow"+o]=h.overflow[k]})}this.options.hide&&c(this.elem).hide();if(this.options.hide||
this.options.show)for(var l in this.options.curAnim)c.style(this.elem,l,this.options.orig[l]);this.options.complete.call(this.elem)}return false}else{a=b-this.startTime;this.state=a/this.options.duration;b=this.options.easing||(c.easing.swing?"swing":"linear");this.pos=c.easing[this.options.specialEasing&&this.options.specialEasing[this.prop]||b](this.state,a,0,1,this.options.duration);this.now=this.start+(this.end-this.start)*this.pos;this.update()}return true}};c.extend(c.fx,{tick:function(){for(var a=
c.timers,b=0;b<a.length;b++)a[b]()||a.splice(b--,1);a.length||c.fx.stop()},interval:13,stop:function(){clearInterval(ba);ba=null},speeds:{slow:600,fast:200,_default:400},step:{opacity:function(a){c.style(a.elem,"opacity",a.now)},_default:function(a){if(a.elem.style&&a.elem.style[a.prop]!=null)a.elem.style[a.prop]=(a.prop==="width"||a.prop==="height"?Math.max(0,a.now):a.now)+a.unit;else a.elem[a.prop]=a.now}}});if(c.expr&&c.expr.filters)c.expr.filters.animated=function(a){return c.grep(c.timers,function(b){return a===
b.elem}).length};var xb=/^t(?:able|d|h)$/i,Ia=/^(?:body|html)$/i;c.fn.offset="getBoundingClientRect"in t.documentElement?function(a){var b=this[0],d;if(a)return this.each(function(l){c.offset.setOffset(this,a,l)});if(!b||!b.ownerDocument)return null;if(b===b.ownerDocument.body)return c.offset.bodyOffset(b);try{d=b.getBoundingClientRect()}catch(e){}var f=b.ownerDocument,h=f.documentElement;if(!d||!c.contains(h,b))return d||{top:0,left:0};b=f.body;f=fa(f);return{top:d.top+(f.pageYOffset||c.support.boxModel&&
h.scrollTop||b.scrollTop)-(h.clientTop||b.clientTop||0),left:d.left+(f.pageXOffset||c.support.boxModel&&h.scrollLeft||b.scrollLeft)-(h.clientLeft||b.clientLeft||0)}}:function(a){var b=this[0];if(a)return this.each(function(x){c.offset.setOffset(this,a,x)});if(!b||!b.ownerDocument)return null;if(b===b.ownerDocument.body)return c.offset.bodyOffset(b);c.offset.initialize();var d,e=b.offsetParent,f=b.ownerDocument,h=f.documentElement,l=f.body;d=(f=f.defaultView)?f.getComputedStyle(b,null):b.currentStyle;
for(var k=b.offsetTop,o=b.offsetLeft;(b=b.parentNode)&&b!==l&&b!==h;){if(c.offset.supportsFixedPosition&&d.position==="fixed")break;d=f?f.getComputedStyle(b,null):b.currentStyle;k-=b.scrollTop;o-=b.scrollLeft;if(b===e){k+=b.offsetTop;o+=b.offsetLeft;if(c.offset.doesNotAddBorder&&!(c.offset.doesAddBorderForTableAndCells&&xb.test(b.nodeName))){k+=parseFloat(d.borderTopWidth)||0;o+=parseFloat(d.borderLeftWidth)||0}e=b.offsetParent}if(c.offset.subtractsBorderForOverflowNotVisible&&d.overflow!=="visible"){k+=
parseFloat(d.borderTopWidth)||0;o+=parseFloat(d.borderLeftWidth)||0}d=d}if(d.position==="relative"||d.position==="static"){k+=l.offsetTop;o+=l.offsetLeft}if(c.offset.supportsFixedPosition&&d.position==="fixed"){k+=Math.max(h.scrollTop,l.scrollTop);o+=Math.max(h.scrollLeft,l.scrollLeft)}return{top:k,left:o}};c.offset={initialize:function(){var a=t.body,b=t.createElement("div"),d,e,f,h=parseFloat(c.css(a,"marginTop"))||0;c.extend(b.style,{position:"absolute",top:0,left:0,margin:0,border:0,width:"1px",
height:"1px",visibility:"hidden"});b.innerHTML="<div style='position:absolute;top:0;left:0;margin:0;border:5px solid #000;padding:0;width:1px;height:1px;'><div></div></div><table style='position:absolute;top:0;left:0;margin:0;border:5px solid #000;padding:0;width:1px;height:1px;' cellpadding='0' cellspacing='0'><tr><td></td></tr></table>";a.insertBefore(b,a.firstChild);d=b.firstChild;e=d.firstChild;f=d.nextSibling.firstChild.firstChild;this.doesNotAddBorder=e.offsetTop!==5;this.doesAddBorderForTableAndCells=
f.offsetTop===5;e.style.position="fixed";e.style.top="20px";this.supportsFixedPosition=e.offsetTop===20||e.offsetTop===15;e.style.position=e.style.top="";d.style.overflow="hidden";d.style.position="relative";this.subtractsBorderForOverflowNotVisible=e.offsetTop===-5;this.doesNotIncludeMarginInBodyOffset=a.offsetTop!==h;a.removeChild(b);c.offset.initialize=c.noop},bodyOffset:function(a){var b=a.offsetTop,d=a.offsetLeft;c.offset.initialize();if(c.offset.doesNotIncludeMarginInBodyOffset){b+=parseFloat(c.css(a,
"marginTop"))||0;d+=parseFloat(c.css(a,"marginLeft"))||0}return{top:b,left:d}},setOffset:function(a,b,d){var e=c.css(a,"position");if(e==="static")a.style.position="relative";var f=c(a),h=f.offset(),l=c.css(a,"top"),k=c.css(a,"left"),o=e==="absolute"&&c.inArray("auto",[l,k])>-1;e={};var x={};if(o)x=f.position();l=o?x.top:parseInt(l,10)||0;k=o?x.left:parseInt(k,10)||0;if(c.isFunction(b))b=b.call(a,d,h);if(b.top!=null)e.top=b.top-h.top+l;if(b.left!=null)e.left=b.left-h.left+k;"using"in b?b.using.call(a,
e):f.css(e)}};c.fn.extend({position:function(){if(!this[0])return null;var a=this[0],b=this.offsetParent(),d=this.offset(),e=Ia.test(b[0].nodeName)?{top:0,left:0}:b.offset();d.top-=parseFloat(c.css(a,"marginTop"))||0;d.left-=parseFloat(c.css(a,"marginLeft"))||0;e.top+=parseFloat(c.css(b[0],"borderTopWidth"))||0;e.left+=parseFloat(c.css(b[0],"borderLeftWidth"))||0;return{top:d.top-e.top,left:d.left-e.left}},offsetParent:function(){return this.map(function(){for(var a=this.offsetParent||t.body;a&&!Ia.test(a.nodeName)&&
c.css(a,"position")==="static";)a=a.offsetParent;return a})}});c.each(["Left","Top"],function(a,b){var d="scroll"+b;c.fn[d]=function(e){var f=this[0],h;if(!f)return null;if(e!==B)return this.each(function(){if(h=fa(this))h.scrollTo(!a?e:c(h).scrollLeft(),a?e:c(h).scrollTop());else this[d]=e});else return(h=fa(f))?"pageXOffset"in h?h[a?"pageYOffset":"pageXOffset"]:c.support.boxModel&&h.document.documentElement[d]||h.document.body[d]:f[d]}});c.each(["Height","Width"],function(a,b){var d=b.toLowerCase();
c.fn["inner"+b]=function(){return this[0]?parseFloat(c.css(this[0],d,"padding")):null};c.fn["outer"+b]=function(e){return this[0]?parseFloat(c.css(this[0],d,e?"margin":"border")):null};c.fn[d]=function(e){var f=this[0];if(!f)return e==null?null:this;if(c.isFunction(e))return this.each(function(l){var k=c(this);k[d](e.call(this,l,k[d]()))});if(c.isWindow(f))return f.document.compatMode==="CSS1Compat"&&f.document.documentElement["client"+b]||f.document.body["client"+b];else if(f.nodeType===9)return Math.max(f.documentElement["client"+
b],f.body["scroll"+b],f.documentElement["scroll"+b],f.body["offset"+b],f.documentElement["offset"+b]);else if(e===B){f=c.css(f,d);var h=parseFloat(f);return c.isNaN(h)?f:h}else return this.css(d,typeof e==="string"?e:e+"px")}})})(window);

/*
 * jPicker 1.1.6
 *
 * jQuery Plugin for Photoshop style color picker
 *
 * Copyright (c) 2010 Christopher T. Tillman
 * Digital Magic Productions, Inc. (http://www.digitalmagicpro.com/)
 * MIT style license, FREE to use, alter, copy, sell, and especially ENHANCE
 *
 * Painstakingly ported from John Dyers' excellent work on his own color picker based on the Prototype framework.
 *
 * John Dyers' website: (http://johndyer.name)
 * Color Picker page:   (http://johndyer.name/post/2007/09/PhotoShop-like-JavaScript-Color-Picker.aspx)
 *
 */
(function($, version)
{
  Math.precision = function(value, precision)
    {
      if (precision === undefined) precision = 0;
      return Math.round(value * Math.pow(10, precision)) / Math.pow(10, precision);
    };
  var Slider = // encapsulate slider functionality for the ColorMap and ColorBar - could be useful to use a jQuery UI draggable for this with certain extensions
      function(bar, options)
      {
        var $this = this, // private properties, methods, and events - keep these variables and classes invisible to outside code
          arrow = bar.find('img:first'), // the arrow image to drag
          minX = 0,
          maxX = 100,
          rangeX = 100,
          minY = 0,
          maxY = 100,
          rangeY = 100,
          x = 0,
          y = 0,
          offset,
          timeout,
          changeEvents = new Array(),
          fireChangeEvents =
            function(context)
            {
              for (var i = 0; i < changeEvents.length; i++) changeEvents[i].call($this, $this, context);
            },
          mouseDown = // bind the mousedown to the bar not the arrow for quick snapping to the clicked location
            function(e)
            {
              var off = bar.offset();
              offset = { l: off.left | 0, t: off.top | 0 };
              clearTimeout(timeout);
              timeout = setTimeout( // using setTimeout for visual updates - once the style is updated the browser will re-render internally allowing the next Javascript to run
                function()
                {
                  setValuesFromMousePosition.call($this, e);
                }, 0);
              // Bind mousemove and mouseup event to the document so it responds when dragged of of the bar - we will unbind these when on mouseup to save processing
              $(document).bind('mousemove', mouseMove).bind('mouseup', mouseUp);
              e.preventDefault(); // don't try to select anything or drag the image to the desktop
            },
          mouseMove = // set the values as the mouse moves
            function(e)
            {
              clearTimeout(timeout);
              timeout = setTimeout(
                function()
                {
                  setValuesFromMousePosition.call($this, e);
                }, 0);
              e.stopPropagation();
              e.preventDefault();
              return false;
            },
          mouseUp = // unbind the document events - they aren't needed when not dragging
            function(e)
            {
              $(document).unbind('mouseup', mouseUp).unbind('mousemove', mouseMove);
              e.stopPropagation();
              e.preventDefault();
              return false;
            },
          setValuesFromMousePosition = // calculate mouse position and set value within the current range
            function(e)
            {
              var locX = e.pageX - offset.l,
                  locY = e.pageY - offset.t,
                  barW = bar.w, // local copies for YUI compressor
                  barH = bar.h;
              // keep the arrow within the bounds of the bar
              if (locX < 0) locX = 0;
              else if (locX > barW) locX = barW;
              if (locY < 0) locY = 0;
              else if (locY > barH) locY = barH;
              val.call($this, 'xy', { x: ((locX / barW) * rangeX) + minX, y: ((locY / barH) * rangeY) + minY });
            },
          draw =
            function()
            {
              var arrowOffsetX = 0,
                arrowOffsetY = 0,
                barW = bar.w,
                barH = bar.h,
                arrowW = arrow.w,
                arrowH = arrow.h;
              setTimeout(
                function()
                {
                  if (rangeX > 0) // range is greater than zero
                  {
                    // constrain to bounds
                    if (x == maxX) arrowOffsetX = barW;
                    else arrowOffsetX = ((x / rangeX) * barW) | 0;
                  }
                  if (rangeY > 0) // range is greater than zero
                  {
                    // constrain to bounds
                    if (y == maxY) arrowOffsetY = barH;
                    else arrowOffsetY = ((y / rangeY) * barH) | 0;
                  }
                  // if arrow width is greater than bar width, center arrow and prevent horizontal dragging
                  if (arrowW >= barW) arrowOffsetX = (barW >> 1) - (arrowW >> 1); // number >> 1 - superfast bitwise divide by two and truncate (move bits over one bit discarding lowest)
                  else arrowOffsetX -= arrowW >> 1;
                  // if arrow height is greater than bar height, center arrow and prevent vertical dragging
                  if (arrowH >= barH) arrowOffsetY = (barH >> 1) - (arrowH >> 1);
                  else arrowOffsetY -= arrowH >> 1;
                  // set the arrow position based on these offsets
                  arrow.css({ left: arrowOffsetX + 'px', top: arrowOffsetY + 'px' });
                }, 0);
            },
          val =
            function(name, value, context)
            {
              var set = value !== undefined;
              if (!set)
              {
                if (name === undefined || name == null) name = 'xy';
                switch (name.toLowerCase())
                {
                  case 'x': return x;
                  case 'y': return y;
                  case 'xy':
                  default: return { x: x, y: y };
                }
              }
              if (context != null && context == $this) return;
              var changed = false,
                  newX,
                  newY;
              if (name == null) name = 'xy';
              switch (name.toLowerCase())
              {
                case 'x':
                  newX = value && (value.x && value.x | 0 || value | 0) || 0;
                  break;
                case 'y':
                  newY = value && (value.y && value.y | 0 || value | 0) || 0;
                  break;
                case 'xy':
                default:
                  newX = value && value.x && value.x | 0 || 0;
                  newY = value && value.y && value.y | 0 || 0;
                  break;
              }
              if (newX != null)
              {
                if (newX < minX) newX = minX;
                else if (newX > maxX) newX = maxX;
                if (x != newX)
                {
                  x = newX;
                  changed = true;
                }
              }
              if (newY != null)
              {
                if (newY < minY) newY = minY;
                else if (newY > maxY) newY = maxY;
                if (y != newY)
                {
                  y = newY;
                  changed = true;
                }
              }
              changed && fireChangeEvents.call($this, context || $this);
            },
          range =
            function (name, value)
            {
              var set = value !== undefined;
              if (!set)
              {
                if (name === undefined || name == null) name = 'all';
                switch (name.toLowerCase())
                {
                  case 'minx': return minX;
                  case 'maxx': return maxX;
                  case 'rangex': return { minX: minX, maxX: maxX, rangeX: rangeX };
                  case 'miny': return minY;
                  case 'maxy': return maxY;
                  case 'rangey': return { minY: minY, maxY: maxY, rangeY: rangeY };
                  case 'all':
                  default: return { minX: minX, maxX: maxX, rangeX: rangeX, minY: minY, maxY: maxY, rangeY: rangeY };
                }
              }
              var changed = false,
                  newMinX,
                  newMaxX,
                  newMinY,
                  newMaxY;
              if (name == null) name = 'all';
              switch (name.toLowerCase())
              {
                case 'minx':
                  newMinX = value && (value.minX && value.minX | 0 || value | 0) || 0;
                  break;
                case 'maxx':
                  newMaxX = value && (value.maxX && value.maxX | 0 || value | 0) || 0;
                  break;
                case 'rangex':
                  newMinX = value && value.minX && value.minX | 0 || 0;
                  newMaxX = value && value.maxX && value.maxX | 0 || 0;
                  break;
                case 'miny':
                  newMinY = value && (value.minY && value.minY | 0 || value | 0) || 0;
                  break;
                case 'maxy':
                  newMaxY = value && (value.maxY && value.maxY | 0 || value | 0) || 0;
                  break;
                case 'rangey':
                  newMinY = value && value.minY && value.minY | 0 || 0;
                  newMaxY = value && value.maxY && value.maxY | 0 || 0;
                  break;
                case 'all':
                default:
                  newMinX = value && value.minX && value.minX | 0 || 0;
                  newMaxX = value && value.maxX && value.maxX | 0 || 0;
                  newMinY = value && value.minY && value.minY | 0 || 0;
                  newMaxY = value && value.maxY && value.maxY | 0 || 0;
                  break;
              }
              if (newMinX != null && minX != newMinX)
              {
                minX = newMinX;
                rangeX = maxX - minX;
              }
              if (newMaxX != null && maxX != newMaxX)
              {
                maxX = newMaxX;
                rangeX = maxX - minX;
              }
              if (newMinY != null && minY != newMinY)
              {
                minY = newMinY;
                rangeY = maxY - minY;
              }
              if (newMaxY != null && maxY != newMaxY)
              {
                maxY = newMaxY;
                rangeY = maxY - minY;
              }
            },
          bind =
            function (callback)
            {
              if ($.isFunction(callback)) changeEvents.push(callback);
            },
          unbind =
            function (callback)
            {
              if (!$.isFunction(callback)) return;
              var i;
              while ((i = $.inArray(callback, changeEvents)) != -1) changeEvents.splice(i, 1);
            },
          destroy =
            function()
            {
              // unbind all possible events and null objects
              $(document).unbind('mouseup', mouseUp).unbind('mousemove', mouseMove);
              bar.unbind('mousedown', mouseDown);
              bar = null;
              arrow = null;
              changeEvents = null;
            };
        $.extend(true, $this, // public properties, methods, and event bindings - these we need to access from other controls
          {
            val: val,
            range: range,
            bind: bind,
            unbind: unbind,
            destroy: destroy
          });
        // initialize this control
        arrow.src = options.arrow && options.arrow.image;
        arrow.w = options.arrow && options.arrow.width || arrow.width();
        arrow.h = options.arrow && options.arrow.height || arrow.height();
        bar.w = options.map && options.map.width || bar.width();
        bar.h = options.map && options.map.height || bar.height();
        // bind mousedown event
        bar.bind('mousedown', mouseDown);
        bind.call($this, draw);
      },
    ColorValuePicker = // controls for all the input elements for the typing in color values
      function(picker, color, bindedHex, alphaPrecision)
      {
        var $this = this, // private properties and methods
          inputs = picker.find('td.Text input'),
          red = inputs.eq(3),
          green = inputs.eq(4),
          blue = inputs.eq(5),
          alpha = inputs.length > 7 ? inputs.eq(6) : null,
          hue = inputs.eq(0),
          saturation = inputs.eq(1),
          value = inputs.eq(2),
          hex = inputs.eq(inputs.length > 7 ? 7 : 6),
          ahex = inputs.length > 7 ? inputs.eq(8) : null,
          keyDown = // input box key down - use arrows to alter color
            function(e)
            {
              if (e.target.value == '' && e.target != hex.get(0) && (bindedHex != null && e.target != bindedHex.get(0) || bindedHex == null)) return;
              if (!validateKey(e)) return e;
              switch (e.target)
              {
                case red.get(0):
                  switch (e.keyCode)
                  {
                    case 38:
                      red.val(setValueInRange.call($this, (red.val() << 0) + 1, 0, 255));
                      color.val('r', red.val(), e.target);
                      return false;
                    case 40:
                      red.val(setValueInRange.call($this, (red.val() << 0) - 1, 0, 255));
                      color.val('r', red.val(), e.target);
                      return false;
                  }
                  break;
                case green.get(0):
                  switch (e.keyCode)
                  {
                    case 38:
                      green.val(setValueInRange.call($this, (green.val() << 0) + 1, 0, 255));
                      color.val('g', green.val(), e.target);
                      return false;
                    case 40:
                      green.val(setValueInRange.call($this, (green.val() << 0) - 1, 0, 255));
                      color.val('g', green.val(), e.target);
                      return false;
                  }
                  break;
                case blue.get(0):
                  switch (e.keyCode)
                  {
                    case 38:
                      blue.val(setValueInRange.call($this, (blue.val() << 0) + 1, 0, 255));
                      color.val('b', blue.val(), e.target);
                      return false;
                    case 40:
                      blue.val(setValueInRange.call($this, (blue.val() << 0) - 1, 0, 255));
                      color.val('b', blue.val(), e.target);
                      return false;
                  }
                  break;
                case alpha && alpha.get(0):
                  switch (e.keyCode)
                  {
                    case 38:
                      alpha.val(setValueInRange.call($this, parseFloat(alpha.val()) + 1, 0, 100));
                      color.val('a', Math.precision((alpha.val() * 255) / 100, alphaPrecision), e.target);
                      return false;
                    case 40:
                      alpha.val(setValueInRange.call($this, parseFloat(alpha.val()) - 1, 0, 100));
                      color.val('a', Math.precision((alpha.val() * 255) / 100, alphaPrecision), e.target);
                      return false;
                  }
                  break;
                case hue.get(0):
                  switch (e.keyCode)
                  {
                    case 38:
                      hue.val(setValueInRange.call($this, (hue.val() << 0) + 1, 0, 360));
                      color.val('h', hue.val(), e.target);
                      return false;
                    case 40:
                      hue.val(setValueInRange.call($this, (hue.val() << 0) - 1, 0, 360));
                      color.val('h', hue.val(), e.target);
                      return false;
                  }
                  break;
                case saturation.get(0):
                  switch (e.keyCode)
                  {
                    case 38:
                      saturation.val(setValueInRange.call($this, (saturation.val() << 0) + 1, 0, 100));
                      color.val('s', saturation.val(), e.target);
                      return false;
                    case 40:
                      saturation.val(setValueInRange.call($this, (saturation.val() << 0) - 1, 0, 100));
                      color.val('s', saturation.val(), e.target);
                      return false;
                  }
                  break;
                case value.get(0):
                  switch (e.keyCode)
                  {
                    case 38:
                      value.val(setValueInRange.call($this, (value.val() << 0) + 1, 0, 100));
                      color.val('v', value.val(), e.target);
                      return false;
                    case 40:
                      value.val(setValueInRange.call($this, (value.val() << 0) - 1, 0, 100));
                      color.val('v', value.val(), e.target);
                      return false;
                  }
                  break;
              }
            },
          keyUp = // input box key up - validate value and set color
            function(e)
            {
              if (e.target.value == '' && e.target != hex.get(0) && (bindedHex != null && e.target != bindedHex.get(0) || bindedHex == null)) return;
              if (!validateKey(e)) return e;
              switch (e.target)
              {
                case red.get(0):
                  red.val(setValueInRange.call($this, red.val(), 0, 255));
                  color.val('r', red.val(), e.target);
                  break;
                case green.get(0):
                  green.val(setValueInRange.call($this, green.val(), 0, 255));
                  color.val('g', green.val(), e.target);
                  break;
                case blue.get(0):
                  blue.val(setValueInRange.call($this, blue.val(), 0, 255));
                  color.val('b', blue.val(), e.target);
                  break;
                case alpha && alpha.get(0):
                  alpha.val(setValueInRange.call($this, alpha.val(), 0, 100));
                  color.val('a', Math.precision((alpha.val() * 255) / 100, alphaPrecision), e.target);
                  break;
                case hue.get(0):
                  hue.val(setValueInRange.call($this, hue.val(), 0, 360));
                  color.val('h', hue.val(), e.target);
                  break;
                case saturation.get(0):
                  saturation.val(setValueInRange.call($this, saturation.val(), 0, 100));
                  color.val('s', saturation.val(), e.target);
                  break;
                case value.get(0):
                  value.val(setValueInRange.call($this, value.val(), 0, 100));
                  color.val('v', value.val(), e.target);
                  break;
                case hex.get(0):
                  hex.val(hex.val().replace(/[^a-fA-F0-9]/g, '').toLowerCase().substring(0, 6));
                  bindedHex && bindedHex.val(hex.val());
                  color.val('hex', hex.val() != '' ? hex.val() : null, e.target);
                  break;
                case bindedHex && bindedHex.get(0):
                  if(bindedHex[0].alphaSupport){
                    bindedHex.val(bindedHex.val().replace(/[^a-fA-F0-9]/g, '').toLowerCase().substring(0, 8));
                    hex.val(bindedHex.val());
                    color.val('ahex', bindedHex.val() != '' ? bindedHex.val() : null, e.target);
                  }else{
                    bindedHex.val(bindedHex.val().replace(/[^a-fA-F0-9]/g, '').toLowerCase().substring(0, 6));
                    hex.val(bindedHex.val());
                    color.val('hex', bindedHex.val() != '' ? bindedHex.val() : null, e.target);
                  }
                  break;
                case ahex && ahex.get(0):
                  ahex.val(ahex.val().replace(/[^a-fA-F0-9]/g, '').toLowerCase().substring(0, 2));
                  color.val('a', ahex.val() != null ? parseInt(ahex.val(), 16) : null, e.target);
                  break;
              }
            },
          blur = // input box blur - reset to original if value empty
            function(e)
            {
              if (color.val() != null)
              {
                switch (e.target)
                {
                  case red.get(0): red.val(color.val('r')); break;
                  case green.get(0): green.val(color.val('g')); break;
                  case blue.get(0): blue.val(color.val('b')); break;
                  case alpha && alpha.get(0): alpha.val(Math.precision((color.val('a') * 100) / 255, alphaPrecision)); break;
                  case hue.get(0): hue.val(color.val('h')); break;
                  case saturation.get(0): saturation.val(color.val('s')); break;
                  case value.get(0): value.val(color.val('v')); break;
                  case hex.get(0):
                  case bindedHex && bindedHex.get(0):
                    if(bindedHex[0].alphaSupport){
                      hex.val(color.val('ahex'));
                      bindedHex && bindedHex.val(color.val('ahex'));
                    }else{
                      hex.val(color.val('hex'));
                      bindedHex && bindedHex.val(color.val('hex'));
                    }
                    break;
                  case ahex && ahex.get(0): ahex.val(color.val('ahex').substring(6)); break;
                }
              }
            },
          validateKey = // validate key
            function(e)
            {
              switch(e.keyCode)
              {
                case 9:
                case 16:
                case 29:
                case 37:
                case 39:
                  return false;
                case 'c'.charCodeAt():
                case 'v'.charCodeAt():
                  if (e.ctrlKey) return false;
              }
              return true;
            },
          setValueInRange = // constrain value within range
            function(value, min, max)
            {
              if (value == '' || isNaN(value)) return min;
              if (value > max) return max;
              if (value < min) return min;
              return value;
            },
          colorChanged =
            function(ui, context)
            {
              var all = ui.val('all');
              if (context != red.get(0)) red.val(all != null ? all.r : '');
              if (context != green.get(0)) green.val(all != null ? all.g : '');
              if (context != blue.get(0)) blue.val(all != null ? all.b : '');
              if (alpha && context != alpha.get(0)) alpha.val(all != null ? Math.precision((all.a * 100) / 255, alphaPrecision) : '');
              if (context != hue.get(0)) hue.val(all != null ? all.h : '');
              if (context != saturation.get(0)) saturation.val(all != null ? all.s : '');
              if (context != value.get(0)) value.val(all != null ? all.v : '');
              if (context != hex.get(0) && (bindedHex && context != bindedHex.get(0) || !bindedHex)) hex.val(all != null ? all.hex : '');
              if(bindedHex[0] && bindedHex[0].alphaSupport){
                if (bindedHex && context != bindedHex.get(0) && context != hex.get(0)) bindedHex.val(all != null ? all.ahex : '');
              }else{
                if (bindedHex && context != bindedHex.get(0) && context != hex.get(0)) bindedHex.val(all != null ? all.hex : '');
              }
              if(bindedHex[0] && OfflajnfireEvent)
                OfflajnfireEvent(bindedHex[0], 'change');
              if (ahex && context != ahex.get(0)) ahex.val(all != null ? all.ahex.substring(6) : '');
            },
          destroy =
            function()
            {
              // unbind all events and null objects
              red.add(green).add(blue).add(alpha).add(hue).add(saturation).add(value).add(hex).add(bindedHex).add(ahex).unbind('keyup', keyUp).unbind('blur', blur);
              red.add(green).add(blue).add(alpha).add(hue).add(saturation).add(value).unbind('keydown', keyDown);
              color.unbind(colorChanged);
              red = null;
              green = null;
              blue = null;
              alpha = null;
              hue = null;
              saturation = null;
              value = null;
              hex = null;
              ahex = null;
            };
        $.extend(true, $this, // public properties and methods
          {
            destroy: destroy
          });
        if(bindedHex && bindedHex[0]){
          if(bindedHex[0].alphaSupport){
            bindedHex.val(bindedHex.val().replace(/[^a-fA-F0-9]/g, '').toLowerCase().substring(0, 8));
          }else{
            bindedHex.val(bindedHex.val().replace(/[^a-fA-F0-9]/g, '').toLowerCase().substring(0, 6));
          }
        }
        red.add(green).add(blue).add(alpha).add(hue).add(saturation).add(value).add(hex).add(bindedHex).add(ahex).bind('keyup', keyUp).bind('change', keyUp).bind('blur', blur);
        red.add(green).add(blue).add(alpha).add(hue).add(saturation).add(value).bind('keydown', keyDown);
        color.bind(colorChanged);
      };
  $.jPicker =
    {
      List: [], // array holding references to each active instance of the control
      Color: // color object - we will be able to assign by any color space type or retrieve any color space info
             // we want this public so we can optionally assign new color objects to initial values using inputs other than a string hex value (also supported)
        function(init)
        {
          var $this = this,
            r,
            g,
            b,
            a,
            h,
            s,
            v,
            changeEvents = new Array(),
            fireChangeEvents = 
              function(context)
              {
                for (var i = 0; i < changeEvents.length; i++) changeEvents[i].call($this, $this, context);
              },
            val =
              function(name, value, context)
              {
                var set = value !== undefined;
                if (!set)
                {
                  if (name === undefined || name == null || name == '') name = 'all';
                  if (r == null) return null;
                  switch (name.toLowerCase())
                  {
                    case 'ahex': return ColorMethods.rgbaToHex({ r: r, g: g, b: b, a: a });
                    case 'hex': return val('ahex').substring(0, 6);
                    case 'all': return { r: r, g: g, b: b, a: a, h: h, s: s, v: v, hex: val.call($this, 'hex'), ahex: val.call($this, 'ahex') };
                    default:
                      var ret={};
                      for (var i = 0; i < name.length; i++)
                      {
                        switch (name.charAt(i))
                        {
                          case 'r':
                            if (name.length == 1) ret = r;
                            else ret.r = r;
                            break;
                          case 'g':
                            if (name.length == 1) ret = g;
                            else ret.g = g;
                            break;
                          case 'b':
                            if (name.length == 1) ret = b;
                            else ret.b = b;
                            break;
                          case 'a':
                            if (name.length == 1) ret = a;
                            else ret.a = a;
                            break;
                          case 'h':
                            if (name.length == 1) ret = h;
                            else ret.h = h;
                            break;
                          case 's':
                            if (name.length == 1) ret = s;
                            else ret.s = s;
                            break;
                          case 'v':
                            if (name.length == 1) ret = v;
                            else ret.v = v;
                            break;
                        }
                      }
                      return ret == {} ? val.call($this, 'all') : ret;
                      break;
                  }
                }
                if (context != null && context == $this) return;
                var changed = false;
                if (name == null) name = '';
                if (value == null)
                {
                  if (r != null)
                  {
                    r = null;
                    changed = true;
                  }
                  if (g != null)
                  {
                    g = null;
                    changed = true;
                  }
                  if (b != null)
                  {
                    b = null;
                    changed = true;
                  }
                  if (a != null)
                  {
                    a = null;
                    changed = true;
                  }
                  if (h != null)
                  {
                    h = null;
                    changed = true;
                  }
                  if (s != null)
                  {
                    s = null;
                    changed = true;
                  }
                  if (v != null)
                  {
                    v = null;
                    changed = true;
                  }
                  changed && fireChangeEvents.call($this, context || $this);
                  return;
                }
                switch (name.toLowerCase())
                {
                  case 'ahex':
                  case 'hex':
                    var ret = ColorMethods.hexToRgba(value && (value.ahex || value.hex) || value || '00000000');
                    val.call($this, 'rgba', { r: ret.r, g: ret.g, b: ret.b, a: name == 'ahex' ? ret.a : a != null ? a : 255 }, context);
                    break;
                  default:
                    if (value && (value.ahex != null || value.hex != null))
                    {
                      val.call($this, 'ahex', value.ahex || value.hex || '00000000', context);
                      return;
                    }
                    var newV = {}, rgb = false, hsv = false;
                    if (value.r !== undefined && !name.indexOf('r') == -1) name += 'r';
                    if (value.g !== undefined && !name.indexOf('g') == -1) name += 'g';
                    if (value.b !== undefined && !name.indexOf('b') == -1) name += 'b';
                    if (value.a !== undefined && !name.indexOf('a') == -1) name += 'a';
                    if (value.h !== undefined && !name.indexOf('h') == -1) name += 'h';
                    if (value.s !== undefined && !name.indexOf('s') == -1) name += 's';
                    if (value.v !== undefined && !name.indexOf('v') == -1) name += 'v';
                    for (var i = 0; i < name.length; i++)
                    {
                      switch (name.charAt(i))
                      {
                        case 'r':
                          if (hsv) continue;
                          rgb = true;
                          newV.r = value && value.r && value.r | 0 || value && value | 0 || 0;
                          if (newV.r < 0) newV.r = 0;
                          else if (newV.r > 255) newV.r = 255;
                          if (r != newV.r)
                          {
                            r = newV.r;
                            changed = true;
                          }
                          break;
                        case 'g':
                          if (hsv) continue;
                          rgb = true;
                          newV.g = value && value.g && value.g | 0 || value && value | 0 || 0;
                          if (newV.g < 0) newV.g = 0;
                          else if (newV.g > 255) newV.g = 255;
                          if (g != newV.g)
                          {
                            g = newV.g;
                            changed = true;
                          }
                          break;
                        case 'b':
                          if (hsv) continue;
                          rgb = true;
                          newV.b = value && value.b && value.b | 0 || value && value | 0 || 0;
                          if (newV.b < 0) newV.b = 0;
                          else if (newV.b > 255) newV.b = 255;
                          if (b != newV.b)
                          {
                            b = newV.b;
                            changed = true;
                          }
                          break;
                        case 'a':
                          newV.a = value && value.a != null ? value.a | 0 : value != null ? value | 0 : 255;
                          if (newV.a < 0) newV.a = 0;
                          else if (newV.a > 255) newV.a = 255;
                          if (a != newV.a)
                          {
                            a = newV.a;
                            changed = true;
                          }
                          break;
                        case 'h':
                          if (rgb) continue;
                          hsv = true;
                          newV.h = value && value.h && value.h | 0 || value && value | 0 || 0;
                          if (newV.h < 0) newV.h = 0;
                          else if (newV.h > 360) newV.h = 360;
                          if (h != newV.h)
                          {
                            h = newV.h;
                            changed = true;
                          }
                          break;
                        case 's':
                          if (rgb) continue;
                          hsv = true;
                          newV.s = value && value.s != null ? value.s | 0 : value != null ? value | 0 : 100;
                          if (newV.s < 0) newV.s = 0;
                          else if (newV.s > 100) newV.s = 100;
                          if (s != newV.s)
                          {
                            s = newV.s;
                            changed = true;
                          }
                          break;
                        case 'v':
                          if (rgb) continue;
                          hsv = true;
                          newV.v = value && value.v != null ? value.v | 0 : value != null ? value | 0 : 100;
                          if (newV.v < 0) newV.v = 0;
                          else if (newV.v > 100) newV.v = 100;
                          if (v != newV.v)
                          {
                            v = newV.v;
                            changed = true;
                          }
                          break;
                      }
                    }
                    if (changed)
                    {
                      if (rgb)
                      {
                        r = r || 0;
                        g = g || 0;
                        b = b || 0;
                        var ret = ColorMethods.rgbToHsv({ r: r, g: g, b: b });
                        h = ret.h;
                        s = ret.s;
                        v = ret.v;
                      }
                      else if (hsv)
                      {
                        h = h || 0;
                        s = s != null ? s : 100;
                        v = v != null ? v : 100;
                        var ret = ColorMethods.hsvToRgb({ h: h, s: s, v: v });
                        r = ret.r;
                        g = ret.g;
                        b = ret.b;
                      }
                      a = a != null ? a : 255;
                      fireChangeEvents.call($this, context || $this);
                    }
                    break;
                }
              },
            bind =
              function(callback)
              {
                if ($.isFunction(callback)) changeEvents.push(callback);
              },
            unbind =
              function(callback)
              {
                if (!$.isFunction(callback)) return;
                var i;
                while ((i = $.inArray(callback, changeEvents)) != -1) changeEvents.splice(i, 1);
              },
            destroy =
              function()
              {
                changeEvents = null;
              }
          $.extend(true, $this, // public properties and methods
            {
              val: val,
              bind: bind,
              unbind: unbind,
              destroy: destroy
            });
          if (init)
          {
            if (init.ahex != null) val('ahex', init);
            else if (init.hex != null) val((init.a != null ? 'a' : '') + 'hex', init.a != null ? { ahex: init.hex + ColorMethods.intToHex(init.a) } : init);
            else if (init.r != null && init.g != null && init.b != null) val('rgb' + (init.a != null ? 'a' : ''), init);
            else if (init.h != null && init.s != null && init.v != null) val('hsv' + (init.a != null ? 'a' : ''), init);
          }
        },
      ColorMethods: // color conversion methods  - make public to give use to external scripts
        {
          hexToRgba:
            function(hex)
            {
              hex = this.validateHex(hex);
              if (hex == '') return { r: null, g: null, b: null, a: null };
              var r = '00', g = '00', b = '00', a = '255';
              if (hex.length == 6) hex += 'ff';
              if (hex.length > 6)
              {
                r = hex.substring(0, 2);
                g = hex.substring(2, 4);
                b = hex.substring(4, 6);
                a = hex.substring(6, hex.length);
              }
              else
              {
                if (hex.length > 4)
                {
                  r = hex.substring(4, hex.length);
                  hex = hex.substring(0, 4);
                }
                if (hex.length > 2)
                {
                  g = hex.substring(2, hex.length);
                  hex = hex.substring(0, 2);
                }
                if (hex.length > 0) b = hex.substring(0, hex.length);
              }
              return { r: this.hexToInt(r), g: this.hexToInt(g), b: this.hexToInt(b), a: this.hexToInt(a) };
            },
          validateHex:
            function(hex)
            {
              hex = hex.toLowerCase().replace(/[^a-f0-9]/g, '');
              if (hex.length > 8) hex = hex.substring(0, 8);
              return hex;
            },
          rgbaToHex:
            function(rgba)
            {
              return this.intToHex(rgba.r) + this.intToHex(rgba.g) + this.intToHex(rgba.b) + this.intToHex(rgba.a);
            },
          intToHex:
            function(dec)
            {
              var result = (dec | 0).toString(16);
              if (result.length == 1) result = ('0' + result);
              return result.toLowerCase();
            },
          hexToInt:
            function(hex)
            {
              return parseInt(hex, 16);
            },
          rgbToHsv:
            function(rgb)
            {
              var r = rgb.r / 255, g = rgb.g / 255, b = rgb.b / 255, hsv = { h: 0, s: 0, v: 0 }, min = 0, max = 0, delta;
              if (r >= g && r >= b)
              {
                max = r;
                min = g > b ? b : g;
              }
              else if (g >= b && g >= r)
              {
                max = g;
                min = r > b ? b : r;
              }
              else
              {
                max = b;
                min = g > r ? r : g;
              }
              hsv.v = max;
              hsv.s = max ? (max - min) / max : 0;
              if (!hsv.s) hsv.h = 0;
              else
              {
                delta = max - min;
                if (r == max) hsv.h = (g - b) / delta;
                else if (g == max) hsv.h = 2 + (b - r) / delta;
                else hsv.h = 4 + (r - g) / delta;
                hsv.h = parseInt(hsv.h * 60);
                if (hsv.h < 0) hsv.h += 360;
              }
              hsv.s = (hsv.s * 100) | 0;
              hsv.v = (hsv.v * 100) | 0;
              return hsv;
            },
          hsvToRgb:
            function(hsv)
            {
              var rgb = { r: 0, g: 0, b: 0, a: 100 }, h = hsv.h, s = hsv.s, v = hsv.v;
              if (s == 0)
              {
                if (v == 0) rgb.r = rgb.g = rgb.b = 0;
                else rgb.r = rgb.g = rgb.b = (v * 255 / 100) | 0;
              }
              else
              {
                if (h == 360) h = 0;
                h /= 60;
                s = s / 100;
                v = v / 100;
                var i = h | 0,
                    f = h - i,
                    p = v * (1 - s),
                    q = v * (1 - (s * f)),
                    t = v * (1 - (s * (1 - f)));
                switch (i)
                {
                  case 0:
                    rgb.r = v;
                    rgb.g = t;
                    rgb.b = p;
                    break;
                  case 1:
                    rgb.r = q;
                    rgb.g = v;
                    rgb.b = p;
                    break;
                  case 2:
                    rgb.r = p;
                    rgb.g = v;
                    rgb.b = t;
                    break;
                  case 3:
                    rgb.r = p;
                    rgb.g = q;
                    rgb.b = v;
                    break;
                  case 4:
                    rgb.r = t;
                    rgb.g = p;
                    rgb.b = v;
                    break;
                  case 5:
                    rgb.r = v;
                    rgb.g = p;
                    rgb.b = q;
                    break;
                }
                rgb.r = (rgb.r * 255) | 0;
                rgb.g = (rgb.g * 255) | 0;
                rgb.b = (rgb.b * 255) | 0;
              }
              return rgb;
            }
        }
    };
  var Color = $.jPicker.Color, List = $.jPicker.List, ColorMethods = $.jPicker.ColorMethods; // local copies for YUI compressor
  $.fn.jPicker =
    function(options)
    {
      var $arguments = arguments;
      return this.each(
        function()
        {
          var $this = this, settings = $.extend(true, {}, $.fn.jPicker.defaults, options); // local copies for YUI compressor
          if ($($this).get(0).nodeName.toLowerCase() == 'input') // Add color picker icon if binding to an input element and bind the events to the input
          {
            $.extend(true, settings,
              {
                window:
                {
                  bindToInput: true,
                  expandable: true,
                  input: $($this)
                }
              });
            if($($this).val()=='')
            {
              settings.color.active = new Color({ hex: null });
              settings.color.current = new Color({ hex: null });
            }
            else if (ColorMethods.validateHex($($this).val()))
            {
              settings.color.active = new Color({ hex: $($this).val(), a: settings.color.active.val('a') });
              settings.color.current = new Color({ hex: $($this).val(), a: settings.color.active.val('a') });
            }
          }
          if (settings.window.expandable)
            $($this).before('<span class="jPicker"><span class="Icon"><span class="Color">&nbsp;</span><span class="Alpha">&nbsp;</span><span class="Image" title="Click To Open Color Picker"><span class=ImageIcon>&nbsp;</span></span><span class="Container">&nbsp;</span></span></span>');
          else settings.window.liveUpdate = false; // Basic control binding for inline use - You will need to override the liveCallback or commitCallback function to retrieve results
          var isLessThanIE7 = parseFloat(navigator.appVersion.split('MSIE')[1]) < 7 && document.body.filters, // needed to run the AlphaImageLoader function for IE6
            container = null,
            colorMapDiv = null,
            colorBarDiv = null,
            colorMapL1 = null, // different layers of colorMap and colorBar
            colorMapL2 = null,
            colorMapL3 = null,
            colorBarL1 = null,
            colorBarL2 = null,
            colorBarL3 = null,
            colorBarL4 = null,
            colorBarL5 = null,
            colorBarL6 = null,
            colorMap = null, // color maps
            colorBar = null,
            colorPicker = null,
            elementStartX = null, // Used to record the starting css positions for dragging the control
            elementStartY = null,
            pageStartX = null, // Used to record the mousedown coordinates for dragging the control
            pageStartY = null,
            activePreview = null, // color boxes above the radio buttons
            currentPreview = null,
            okButton = null,
            cancelButton = null,
            grid = null, // preset colors grid
            iconColor = null, // iconColor for popup icon
            iconAlpha = null, // iconAlpha for popup icon
            iconImage = null, // iconImage popup icon
            moveBar = null, // drag bar
            setColorMode = // set color mode and update visuals for the new color mode
              function(colorMode)
              {
                var active = color.active, // local copies for YUI compressor
                  clientPath = images.clientPath,
                  hex = active.val('hex'),
                  rgbMap,
                  rgbBar;
                settings.color.mode = colorMode;
                switch (colorMode)
                {
                  case 'h':
                    setTimeout(
                      function()
                      {
                        setBG.call($this, colorMapDiv, 'transparent');
                        setImgLoc.call($this, colorMapL1, 0);
                        setAlpha.call($this, colorMapL1, 100);
                        setImgLoc.call($this, colorMapL2, 260);
                        setAlpha.call($this, colorMapL2, 100);
                        setBG.call($this, colorBarDiv, 'transparent');
                        setImgLoc.call($this, colorBarL1, 0);
                        setAlpha.call($this, colorBarL1, 100);
                        setImgLoc.call($this, colorBarL2, 260);
                        setAlpha.call($this, colorBarL2, 100);
                        setImgLoc.call($this, colorBarL3, 260);
                        setAlpha.call($this, colorBarL3, 100);
                        setImgLoc.call($this, colorBarL4, 260);
                        setAlpha.call($this, colorBarL4, 100);
                        setImgLoc.call($this, colorBarL6, 260);
                        setAlpha.call($this, colorBarL6, 100);
                      }, 0);
                    colorMap.range('all', { minX: 0, maxX: 100, minY: 0, maxY: 100 });
                    colorBar.range('rangeY', { minY: 0, maxY: 360 });
                    if (active.val('ahex') == null) break;
                    colorMap.val('xy', { x: active.val('s'), y: 100 - active.val('v') }, colorMap);
                    colorBar.val('y', 360 - active.val('h'), colorBar);
                    break;
                  case 's':
                    setTimeout(
                      function()
                      {
                        setBG.call($this, colorMapDiv, 'transparent');
                        setImgLoc.call($this, colorMapL1, -260);
                        setImgLoc.call($this, colorMapL2, -520);
                        setImgLoc.call($this, colorBarL1, -260);
                        setImgLoc.call($this, colorBarL2, -520);
                        setImgLoc.call($this, colorBarL6, 260);
                        setAlpha.call($this, colorBarL6, 100);
                      }, 0);
                    colorMap.range('all', { minX: 0, maxX: 360, minY: 0, maxY: 100 });
                    colorBar.range('rangeY', { minY: 0, maxY: 100 });
                    if (active.val('ahex') == null) break;
                    colorMap.val('xy', { x: active.val('h'), y: 100 - active.val('v') }, colorMap);
                    colorBar.val('y', 100 - active.val('s'), colorBar);
                    break;
                  case 'v':
                    setTimeout(
                      function()
                      {
                        setBG.call($this, colorMapDiv, '000000');
                        setImgLoc.call($this, colorMapL1, -780);
                        setImgLoc.call($this, colorMapL2, 260);
                        setBG.call($this, colorBarDiv, hex);
                        setImgLoc.call($this, colorBarL1, -520);
                        setImgLoc.call($this, colorBarL2, 260);
                        setAlpha.call($this, colorBarL2, 100);
                        setImgLoc.call($this, colorBarL6, 260);
                        setAlpha.call($this, colorBarL6, 100);
                      }, 0);
                    colorMap.range('all', { minX: 0, maxX: 360, minY: 0, maxY: 100 });
                    colorBar.range('rangeY', { minY: 0, maxY: 100 });
                    if (active.val('ahex') == null) break;
                    colorMap.val('xy', { x: active.val('h'), y: 100 - active.val('s') }, colorMap);
                    colorBar.val('y', 100 - active.val('v'), colorBar);
                    break;
                  case 'r':
                    rgbMap = -1040;
                    rgbBar = -780;
                    colorMap.range('all', { minX: 0, maxX: 255, minY: 0, maxY: 255 });
                    colorBar.range('rangeY', { minY: 0, maxY: 255 });
                    if (active.val('ahex') == null) break;
                    colorMap.val('xy', { x: active.val('b'), y: 255 - active.val('g') }, colorMap);
                    colorBar.val('y', 255 - active.val('r'), colorBar);
                    break;
                  case 'g':
                    rgbMap = -1560;
                    rgbBar = -1820;
                    colorMap.range('all', { minX: 0, maxX: 255, minY: 0, maxY: 255 });
                    colorBar.range('rangeY', { minY: 0, maxY: 255 });
                    if (active.val('ahex') == null) break;
                    colorMap.val('xy', { x: active.val('b'), y: 255 - active.val('r') }, colorMap);
                    colorBar.val('y', 255 - active.val('g'), colorBar);
                    break;
                  case 'b':
                    rgbMap = -2080;
                    rgbBar = -2860;
                    colorMap.range('all', { minX: 0, maxX: 255, minY: 0, maxY: 255 });
                    colorBar.range('rangeY', { minY: 0, maxY: 255 });
                    if (active.val('ahex') == null) break;
                    colorMap.val('xy', { x: active.val('r'), y: 255 - active.val('g') }, colorMap);
                    colorBar.val('y', 255 - active.val('b'), colorBar);
                    break;
                  case 'a':
                    setTimeout(
                      function()
                      {
                        setBG.call($this, colorMapDiv, 'transparent');
                        setImgLoc.call($this, colorMapL1, -260);
                        setImgLoc.call($this, colorMapL2, -520);
                        setImgLoc.call($this, colorBarL1, 260);
                        setImgLoc.call($this, colorBarL2, 260);
                        setAlpha.call($this, colorBarL2, 100);
                        setImgLoc.call($this, colorBarL6, 0);
                        setAlpha.call($this, colorBarL6, 100);
                      }, 0);
                    colorMap.range('all', { minX: 0, maxX: 360, minY: 0, maxY: 100 });
                    colorBar.range('rangeY', { minY: 0, maxY: 255 });
                    if (active.val('ahex') == null) break;
                    colorMap.val('xy', { x: active.val('h'), y: 100 - active.val('v') }, colorMap);
                    colorBar.val('y', 255 - active.val('a'), colorBar);
                    break;
                  default:
                    throw ('Invalid Mode');
                    break;
                }
                switch (colorMode)
                {
                  case 'h':
                    break;
                  case 's':
                  case 'v':
                  case 'a':
                    setTimeout(
                      function()
                      {
                        setAlpha.call($this, colorMapL1, 100);
                        setAlpha.call($this, colorBarL1, 100);
                        setImgLoc.call($this, colorBarL3, 260);
                        setAlpha.call($this, colorBarL3, 100);
                        setImgLoc.call($this, colorBarL4, 260);
                        setAlpha.call($this, colorBarL4, 100);
                      }, 0);
                    break;
                  case 'r':
                  case 'g':
                  case 'b':
                    setTimeout(
                      function()
                      {
                        setBG.call($this, colorMapDiv, 'transparent');
                        setBG.call($this, colorBarDiv, 'transparent');
                        setAlpha.call($this, colorBarL1, 100);
                        setAlpha.call($this, colorMapL1, 100);
                        setImgLoc.call($this, colorMapL1, rgbMap);
                        setImgLoc.call($this, colorMapL2, rgbMap - 260);
                        setImgLoc.call($this, colorBarL1, rgbBar - 780);
                        setImgLoc.call($this, colorBarL2, rgbBar - 520);
                        setImgLoc.call($this, colorBarL3, rgbBar);
                        setImgLoc.call($this, colorBarL4, rgbBar - 260);
                        setImgLoc.call($this, colorBarL6, 260);
                        setAlpha.call($this, colorBarL6, 100);
                      }, 0);
                    break;
                }
                if (active.val('ahex') == null) return;
                activeColorChanged.call($this, active);
              },
            activeColorChanged = // Update color when user changes text values
              function(ui, context)
              {
                if (context == null || (context != colorBar && context != colorMap)) positionMapAndBarArrows.call($this, ui, context);
                setTimeout(
                  function()
                  {
                    updatePreview.call($this, ui);
                    updateMapVisuals.call($this, ui);
                    updateBarVisuals.call($this, ui);
                  }, 0);
              },
            mapValueChanged = // user has dragged the ColorMap pointer
              function(ui, context)
              {
                var active = color.active;
                if (context != colorMap && active.val() == null) return;
                var xy = ui.val('all');
                switch (settings.color.mode)
                {
                  case 'h':
                    active.val('sv', { s: xy.x, v: 100 - xy.y }, context);
                    break;
                  case 's':
                  case 'a':
                    active.val('hv', { h: xy.x, v: 100 - xy.y }, context);
                    break;
                  case 'v':
                    active.val('hs', { h: xy.x, s: 100 - xy.y }, context);
                    break;
                  case 'r':
                    active.val('gb', { g: 255 - xy.y, b: xy.x }, context);
                    break;
                  case 'g':
                    active.val('rb', { r: 255 - xy.y, b: xy.x }, context);
                    break;
                  case 'b':
                    active.val('rg', { r: xy.x, g: 255 - xy.y }, context);
                    break;
                }
              },
            colorBarValueChanged = // user has dragged the ColorBar slider
              function(ui, context)
              {
                var active = color.active;
                if (context != colorBar && active.val() == null) return;
                switch (settings.color.mode)
                {
                  case 'h':
                    active.val('h', { h: 360 - ui.val('y') }, context);
                    break;
                  case 's':
                    active.val('s', { s: 100 - ui.val('y') }, context);
                    break;
                  case 'v':
                    active.val('v', { v: 100 - ui.val('y') }, context);
                    break;
                  case 'r':
                    active.val('r', { r: 255 - ui.val('y') }, context);
                    break;
                  case 'g':
                    active.val('g', { g: 255 - ui.val('y') }, context);
                    break;
                  case 'b':
                    active.val('b', { b: 255 - ui.val('y') }, context);
                    break;
                  case 'a':
                    active.val('a', 255 - ui.val('y'), context);
                    break;
                }
              },
            positionMapAndBarArrows = // position map and bar arrows to match current color
              function(ui, context)
              {
                if (context != colorMap)
                {
                  switch (settings.color.mode)
                  {
                    case 'h':
                      var sv = ui.val('sv');
                      colorMap.val('xy', { x: sv != null ? sv.s : 100, y: 100 - (sv != null ? sv.v : 100) }, context);
                      break;
                    case 's':
                    case 'a':
                      var hv = ui.val('hv');
                      colorMap.val('xy', { x: hv && hv.h || 0, y: 100 - (hv != null ? hv.v : 100) }, context);
                      break;
                    case 'v':
                      var hs = ui.val('hs');
                      colorMap.val('xy', { x: hs && hs.h || 0, y: 100 - (hs != null ? hs.s : 100) }, context);
                      break;
                    case 'r':
                      var bg = ui.val('bg');
                      colorMap.val('xy', { x: bg && bg.b || 0, y: 255 - (bg && bg.g || 0) }, context);
                      break;
                    case 'g':
                      var br = ui.val('br');
                      colorMap.val('xy', { x: br && br.b || 0, y: 255 - (br && br.r || 0) }, context);
                      break;
                    case 'b':
                      var rg = ui.val('rg');
                      colorMap.val('xy', { x: rg && rg.r || 0, y: 255 - (rg && rg.g || 0) }, context);
                      break;
                  }
                }
                if (context != colorBar)
                {
                  switch (settings.color.mode)
                  {
                    case 'h':
                      colorBar.val('y', 360 - (ui.val('h') || 0), context);
                      break;
                    case 's':
                      var s = ui.val('s');
                      colorBar.val('y', 100 - (s != null ? s : 100), context);
                      break;
                    case 'v':
                      var v = ui.val('v');
                      colorBar.val('y', 100 - (v != null ? v : 100), context);
                      break;
                    case 'r':
                      colorBar.val('y', 255 - (ui.val('r') || 0), context);
                      break;
                    case 'g':
                      colorBar.val('y', 255 - (ui.val('g') || 0), context);
                      break;
                    case 'b':
                      colorBar.val('y', 255 - (ui.val('b') || 0), context);
                      break;
                    case 'a':
                      var a = ui.val('a');
                      colorBar.val('y', 255 - (a != null ? a : 255), context);
                      break;
                  }
                }
              },
            updatePreview =
              function(ui)
              {
                try
                {
                  var all = ui.val('all');
                  activePreview.css({ backgroundColor: all && '#' + all.hex || 'transparent' });
                  setAlpha.call($this, activePreview, all && Math.precision((all.a * 100) / 255, 4) || 0);
                }
                catch (e) { }
              },
            updateMapVisuals =
              function(ui)
              {
                switch (settings.color.mode)
                {
                  case 'h':
                    setBG.call($this, colorMapDiv, new Color({ h: ui.val('h') || 0, s: 100, v: 100 }).val('hex'));
                    break;
                  case 's':
                  case 'a':
                    var s = ui.val('s');
                    setAlpha.call($this, colorMapL2, 100 - (s != null ? s : 100));
                    break;
                  case 'v':
                    var v = ui.val('v');
                    setAlpha.call($this, colorMapL1, v != null ? v : 100);
                    break;
                  case 'r':
                    setAlpha.call($this, colorMapL2, Math.precision((ui.val('r') || 0) / 255 * 100, 4));
                    break;
                  case 'g':
                    setAlpha.call($this, colorMapL2, Math.precision((ui.val('g') || 0) / 255 * 100, 4));
                    break;
                  case 'b':
                    setAlpha.call($this, colorMapL2, Math.precision((ui.val('b') || 0) / 255 * 100));
                    break;
                }
                var a = ui.val('a');
                setAlpha.call($this, colorMapL3, Math.precision(((255 - (a || 0)) * 100) / 255, 4));
              },
            updateBarVisuals =
              function(ui)
              {
                switch (settings.color.mode)
                {
                  case 'h':
                    var a = ui.val('a');
                    setAlpha.call($this, colorBarL5, Math.precision(((255 - (a || 0)) * 100) / 255, 4));
                    break;
                  case 's':
                    var hva = ui.val('hva'),
                        saturatedColor = new Color({ h: hva && hva.h || 0, s: 100, v: hva != null ? hva.v : 100 });
                    setBG.call($this, colorBarDiv, saturatedColor.val('hex'));
                    setAlpha.call($this, colorBarL2, 100 - (hva != null ? hva.v : 100));
                    setAlpha.call($this, colorBarL5, Math.precision(((255 - (hva && hva.a || 0)) * 100) / 255, 4));
                    break;
                  case 'v':
                    var hsa = ui.val('hsa'),
                        valueColor = new Color({ h: hsa && hsa.h || 0, s: hsa != null ? hsa.s : 100, v: 100 });
                    setBG.call($this, colorBarDiv, valueColor.val('hex'));
                    setAlpha.call($this, colorBarL5, Math.precision(((255 - (hsa && hsa.a || 0)) * 100) / 255, 4));
                    break;
                  case 'r':
                  case 'g':
                  case 'b':
                    var hValue = 0, vValue = 0, rgba = ui.val('rgba');
                    if (settings.color.mode == 'r')
                    {
                      hValue = rgba && rgba.b || 0;
                      vValue = rgba && rgba.g || 0;
                    }
                    else if (settings.color.mode == 'g')
                    {
                      hValue = rgba && rgba.b || 0;
                      vValue = rgba && rgba.r || 0;
                    }
                    else if (settings.color.mode == 'b')
                    {
                      hValue = rgba && rgba.r || 0;
                      vValue = rgba && rgba.g || 0;
                    }
                    var middle = vValue > hValue ? hValue : vValue;
                    setAlpha.call($this, colorBarL2, hValue > vValue ? Math.precision(((hValue - vValue) / (255 - vValue)) * 100, 4) : 0);
                    setAlpha.call($this, colorBarL3, vValue > hValue ? Math.precision(((vValue - hValue) / (255 - hValue)) * 100, 4) : 0);
                    setAlpha.call($this, colorBarL4, Math.precision((middle / 255) * 100, 4));
                    setAlpha.call($this, colorBarL5, Math.precision(((255 - (rgba && rgba.a || 0)) * 100) / 255, 4));
                    break;
                  case 'a':
                    var a = ui.val('a');
                    setBG.call($this, colorBarDiv, ui.val('hex') || '000000');
                    setAlpha.call($this, colorBarL5, a != null ? 0 : 100);
                    setAlpha.call($this, colorBarL6, a != null ? 100 : 0);
                    break;
                }
              },
            setBG =
              function(el, c)
              {
                el.css({ backgroundColor: c && c.length == 6 && '#' + c || 'transparent' });
              },
            setImg =
              function(img, src)
              {
                if (isLessThanIE7 && (src.indexOf('AlphaBar.png') != -1 || src.indexOf('Bars.png') != -1 || src.indexOf('Maps.png') != -1))
                {
                  img.attr('pngSrc', src);
                  img.css({ backgroundImage: 'none', filter: 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' + src + '\', sizingMethod=\'scale\')' });
                }
                else img.css({ backgroundImage: 'url(\'' + src + '\')' });
              },
            setImgLoc =
              function(img, y)
              {
                img.css({ top: y + 'px' });
              },
            setAlpha =
              function(obj, alpha)
              {
                obj.css({ visibility: alpha > 0 ? 'visible' : 'hidden' });
                if (alpha > 0 && alpha < 100)
                {
                  if (isLessThanIE7)
                  {
                    var src = obj.attr('pngSrc');
                    if (src != null && (src.indexOf('AlphaBar.png') != -1 || src.indexOf('Bars.png') != -1 || src.indexOf('Maps.png') != -1))
                      obj.css({ filter: 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' + src + '\', sizingMethod=\'scale\') progid:DXImageTransform.Microsoft.Alpha(opacity=' + alpha + ')' });
                    else obj.css({ opacity: Math.precision(alpha / 100, 4) });
                  }
                  else obj.css({ opacity: Math.precision(alpha / 100, 4) });
                }
                else if (alpha == 0 || alpha == 100)
                {
                  if (isLessThanIE7)
                  {
                    var src = obj.attr('pngSrc');
                    if (src != null && (src.indexOf('AlphaBar.png') != -1 || src.indexOf('Bars.png') != -1 || src.indexOf('Maps.png') != -1))
                      obj.css({ filter: 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' + src + '\', sizingMethod=\'scale\')' });
                    else obj.css({ opacity: '' });
                  }
                  else obj.css({ opacity: '' });
                }
              },
            revertColor = // revert color to original color when opened
              function()
              {
                color.active.val('ahex', color.current.val('ahex'));
              },
            commitColor = // commit the color changes
              function()
              {
                color.current.val('ahex', color.active.val('ahex'));
              },
            radioClicked =
              function(e)
              {
                $(this).parents('tbody:first').find('input:radio[value!="'+e.target.value+'"]').removeAttr('checked');
                setColorMode.call($this, e.target.value);
              },
            currentClicked =
              function()
              {
                revertColor.call($this);
              },
            cancelClicked =
              function()
              {
                revertColor.call($this);
                settings.window.expandable && hide.call($this);
                $.isFunction(cancelCallback) && cancelCallback.call($this, color.active, cancelButton);
              },
            okClicked =
              function()
              {
                commitColor.call($this);
                settings.window.expandable && hide.call($this);
                $.isFunction(commitCallback) && commitCallback.call($this, color.active, okButton);
              },
            iconImageClicked =
              function()
              {
                show.call($this);
              },
            currentColorChanged =
              function(ui, context)
              {
                var hex = ui.val('hex');
                currentPreview.css({ backgroundColor: hex && '#' + hex || 'transparent' });
                setAlpha.call($this, currentPreview, Math.precision(((ui.val('a') || 0) * 100) / 255, 4));
              },
            expandableColorChanged =
              function(ui, context)
              {
                var hex = ui.val('hex');
                var va = ui.val('va');
                iconColor.css({ backgroundColor: hex && '#' + hex || 'transparent' });
                setAlpha.call($this, iconAlpha, Math.precision(((255 - (va && va.a || 0)) * 100) / 255, 4));
                if (settings.window.bindToInput&&settings.window.updateInputColor)
                  settings.window.input.css(
                    {
                      backgroundColor: hex && '#' + hex || 'transparent',
                      color: va == null || va.v > 75 ? '#000000' : '#ffffff',
                      textShadow: va == null || va.v > 75 ? '1px 1px 1px rgba(255,255,255,0.22)' : '1px 1px 1px rgba(0,0,0,0.22)'
                    });
              },
            moveBarMouseDown =
              function(e)
              {
                var element = settings.window.element, // local copies for YUI compressor
                  page = settings.window.page;
                elementStartX = parseInt(container.css('left'));
                elementStartY = parseInt(container.css('top'));
                pageStartX = e.pageX;
                pageStartY = e.pageY;
                // bind events to document to move window - we will unbind these on mouseup
                $(document).bind('mousemove', documentMouseMove).bind('mouseup', documentMouseUp);
                e.preventDefault(); // prevent attempted dragging of the column
              },
            documentMouseMove =
              function(e)
              {
                container.css({ left: elementStartX - (pageStartX - e.pageX) + 'px', top: elementStartY - (pageStartY - e.pageY) + 'px' });
                if (settings.window.expandable && !$.support.boxModel) container.prev().css({ left: container.css("left"), top: container.css("top") });
                e.stopPropagation();
                e.preventDefault();
                return false;
              },
            documentMouseUp =
              function(e)
              {
                $(document).unbind('mousemove', documentMouseMove).unbind('mouseup', documentMouseUp);
                e.stopPropagation();
                e.preventDefault();
                return false;
              },
            quickPickClicked =
              function(e)
              {
                e.preventDefault();
                e.stopPropagation();
                color.active.val('ahex', $(this).attr('title') || null, e.target);
                return false;
              },
            commitCallback = $.isFunction($arguments[1]) && $arguments[1] || null,
            liveCallback = $.isFunction($arguments[2]) && $arguments[2] || null,
            cancelCallback = $.isFunction($arguments[3]) && $arguments[3] || null,
            show =
              function()
              {
                color.current.val('ahex', color.active.val('ahex'));
                var attachIFrame = function()
                  {
                    if (!settings.window.expandable || $.support.boxModel) return;
                    var table = container.find('table:first');
                    container.before('<iframe/>');
                    container.prev().css({ width: table.width(), height: container.height(), opacity: 0, position: 'relative', left: container.css("left"), top: container.css("top") });
                  };
                if (settings.window.expandable)
                {
                  $(document.body).children('div.jPicker.Container').css({zIndex:100000});
                  container.css({zIndex:200000});
                }
                switch (settings.window.effects.type)
                {
                  case 'fade':
                    container.fadeIn(settings.window.effects.speed.show, attachIFrame);
                    break;
                  case 'slide':
                    container.slideDown(settings.window.effects.speed.show, attachIFrame);
                    break;
                  case 'show':
                  default:
                    container.show(settings.window.effects.speed.show, attachIFrame);
                    break;
                }
              },
            hide =
              function()
              {
                var removeIFrame = function()
                  {
                    if (settings.window.expandable) container.css({ zIndex: 100000 });
                    if (!settings.window.expandable || $.support.boxModel) return;
                    container.prev().remove();
                  };
                switch (settings.window.effects.type)
                {
                  case 'fade':
                    container.fadeOut(settings.window.effects.speed.hide, removeIFrame);
                    break;
                  case 'slide':
                    container.slideUp(settings.window.effects.speed.hide, removeIFrame);
                    break;
                  case 'show':
                  default:
                    container.hide(settings.window.effects.speed.hide, removeIFrame);
                    break;
                }
              },
            initialize =
              function()
              {
                var win = settings.window,
                    popup = win.expandable ? $($this).prev().find('.Container:first') : null;
                container = win.expandable ? $('<div/>') : $($this);
                container.addClass('jPicker Container');
                if (win.expandable) container.hide();
                container.get(0).onselectstart = function(event){ if (event.target.nodeName.toLowerCase() !== 'input') return false; };
                // inject html source code - we are using a single table for this control - I know tables are considered bad, but it takes care of equal height columns and
                // this control really is tabular data, so I believe it is the right move
                var all = color.active.val('all');
                if (win.alphaPrecision < 0) win.alphaPrecision = 0;
                else if (win.alphaPrecision > 2) win.alphaPrecision = 2;
                var controlHtml='<table class="jPicker" cellpadding="0" cellspacing="0"><tbody>' + (win.expandable ? '<tr><td class="Move" colspan="5">&nbsp;</td></tr>' : '') + '<tr><td rowspan="9"><h2 class="Title">' + (win.title || localization.text.title) + '</h2><div class="Map"><span class="Map1">&nbsp;</span><span class="Map2">&nbsp;</span><span class="Map3">&nbsp;</span><img src="' + images.clientPath + images.colorMap.arrow.file + '" class="Arrow"/></div></td><td rowspan="9"><div class="Bar"><span class="Map1">&nbsp;</span><span class="Map2">&nbsp;</span><span class="Map3">&nbsp;</span><span class="Map4">&nbsp;</span><span class="Map5">&nbsp;</span><span class="Map6">&nbsp;</span><img src="' + images.clientPath + images.colorBar.arrow.file + '" class="Arrow"/></div></td><td colspan="2" class="Preview">' + localization.text.newColor + '<div><span class="Active" title="' + localization.tooltips.colors.newColor + '">&nbsp;</span><span class="Current" title="' + localization.tooltips.colors.currentColor + '">&nbsp;</span></div>' + localization.text.currentColor + '</td><td rowspan="9" class="Button"><input type="button" class="Ok" value="' + localization.text.ok + '" title="' + localization.tooltips.buttons.ok + '"/><input type="button" class="Cancel" value="' + localization.text.cancel + '" title="' + localization.tooltips.buttons.cancel + '"/><hr/><div class="Grid">&nbsp;</div></td></tr><tr class="Hue"><td class="Radio"><label title="' + localization.tooltips.hue.radio + '"><input type="radio" value="h"' + (settings.color.mode == 'h' ? ' checked="checked"' : '') + '/>H:</label></td><td class="Text"><input type="text" maxlength="3" value="' + (all != null ? all.h : '') + '" title="' + localization.tooltips.hue.textbox + '"/>&nbsp;&deg;</td></tr><tr class="Saturation"><td class="Radio"><label title="' + localization.tooltips.saturation.radio + '"><input type="radio" value="s"' + (settings.color.mode == 's' ? ' checked="checked"' : '') + '/>S:</label></td><td class="Text"><input type="text" maxlength="3" value="' + (all != null ? all.s : '') + '" title="' + localization.tooltips.saturation.textbox + '"/>&nbsp;%</td></tr><tr class="Value"><td class="Radio"><label title="' + localization.tooltips.value.radio + '"><input type="radio" value="v"' + (settings.color.mode == 'v' ? ' checked="checked"' : '') + '/>V:</label><br/><br/></td><td class="Text"><input type="text" maxlength="3" value="' + (all != null ? all.v : '') + '" title="' + localization.tooltips.value.textbox + '"/>&nbsp;%<br/><br/></td></tr><tr class="Red"><td class="Radio"><label title="' + localization.tooltips.red.radio + '"><input type="radio" value="r"' + (settings.color.mode == 'r' ? ' checked="checked"' : '') + '/>R:</label></td><td class="Text"><input type="text" maxlength="3" value="' + (all != null ? all.r : '') + '" title="' + localization.tooltips.red.textbox + '"/></td></tr><tr class="Green"><td class="Radio"><label title="' + localization.tooltips.green.radio + '"><input type="radio" value="g"' + (settings.color.mode == 'g' ? ' checked="checked"' : '') + '/>G:</label></td><td class="Text"><input type="text" maxlength="3" value="' + (all != null ? all.g : '') + '" title="' + localization.tooltips.green.textbox + '"/></td></tr><tr class="Blue"><td class="Radio"><label title="' + localization.tooltips.blue.radio + '"><input type="radio" value="b"' + (settings.color.mode == 'b' ? ' checked="checked"' : '') + '/>B:</label></td><td class="Text"><input type="text" maxlength="3" value="' + (all != null ? all.b : '') + '" title="' + localization.tooltips.blue.textbox + '"/></td></tr><tr class="Alpha"><td class="Radio">' + (win.alphaSupport ? '<label title="' + localization.tooltips.alpha.radio + '"><input type="radio" value="a"' + (settings.color.mode == 'a' ? ' checked="checked"' : '') + '/>A:</label>' : '&nbsp;') + '</td><td class="Text">' + (win.alphaSupport ? '<input type="text" maxlength="' + (3 + win.alphaPrecision) + '" value="' + (all != null ? Math.precision((all.a * 100) / 255, win.alphaPrecision) : '') + '" title="' + localization.tooltips.alpha.textbox + '"/>&nbsp;%' : '&nbsp;') + '</td></tr><tr class="Hex"><td colspan="2" class="Text"><label title="' + localization.tooltips.hex.textbox + '">#:<input type="text" maxlength="6" class="Hex" value="' + (all != null ? all.hex : '') + '"/></label>' + (win.alphaSupport ? '<input type="text" maxlength="2" class="AHex" value="' + (all != null ? all.ahex.substring(6) : '') + '" title="' + localization.tooltips.hex.alpha + '"/></td>' : '&nbsp;') + '</tr></tbody></table>';
                if (win.expandable)
                {
                  container.html(controlHtml);
                  if($(document.body).children('div.jPicker.Container').length==0)$(document.body).prepend(container);
                  else $(document.body).children('div.jPicker.Container:last').after(container);
                  container.mousedown(
                    function()
                    {
                      $(document.body).children('div.jPicker.Container').css({zIndex:100000});
                      container.css({zIndex:200000});
                    });
                  container.css( // positions must be set and display set to absolute before source code injection or IE will size the container to fit the window
                    {
                      left:
                        win.position.x == 'left' ? (popup.offset().left - 530 - (win.position.y == 'center' ? 25 : 0)) + 'px' :
                        win.position.x == 'center' ? (popup.offset().left - 260) + 'px' :
                        win.position.x == 'right' ? (popup.offset().left - 10 + (win.position.y == 'center' ? 25 : 0)) + 'px' :
                        win.position.x == 'screenCenter' ? (($(document).width() >> 1) - 260) + 'px' : (popup.offset().left + parseInt(win.position.x)) + 'px',
                      position: 'fixed',
                      top: win.position.y == 'top' ? 100 + 'px' :
                           win.position.y == 'center' ? (popup.offset().top - 156) + 'px' :
                           win.position.y == 'bottom' ? (popup.offset().top + 25) + 'px' : (popup.offset().top + parseInt(win.position.y)) + 'px'
                    });
                }
                else
                {
                  container = $($this);
                  container.html(controlHtml);
                }
                // initialize the objects to the source code just injected
                var tbody = container.find('tbody:first');
                colorMapDiv = tbody.find('div.Map:first');
                colorBarDiv = tbody.find('div.Bar:first');
                var MapMaps = colorMapDiv.find('span'),
                    BarMaps = colorBarDiv.find('span');
                colorMapL1 = MapMaps.filter('.Map1:first');
                colorMapL2 = MapMaps.filter('.Map2:first');
                colorMapL3 = MapMaps.filter('.Map3:first');
                colorBarL1 = BarMaps.filter('.Map1:first');
                colorBarL2 = BarMaps.filter('.Map2:first');
                colorBarL3 = BarMaps.filter('.Map3:first');
                colorBarL4 = BarMaps.filter('.Map4:first');
                colorBarL5 = BarMaps.filter('.Map5:first');
                colorBarL6 = BarMaps.filter('.Map6:first');
                // create color pickers and maps
                colorMap = new Slider(colorMapDiv,
                  {
                    map:
                    {
                      width: images.colorMap.width,
                      height: images.colorMap.height
                    },
                    arrow:
                    {
                      image: images.clientPath + images.colorMap.arrow.file,
                      width: images.colorMap.arrow.width,
                      height: images.colorMap.arrow.height
                    }
                  });
                colorMap.bind(mapValueChanged);
                colorBar = new Slider(colorBarDiv,
                  {
                    map:
                    {
                      width: images.colorBar.width,
                      height: images.colorBar.height
                    },
                    arrow:
                    {
                      image: images.clientPath + images.colorBar.arrow.file,
                      width: images.colorBar.arrow.width,
                      height: images.colorBar.arrow.height
                    }
                  });
                colorBar.bind(colorBarValueChanged);
                colorPicker = new ColorValuePicker(tbody, color.active, win.expandable && win.bindToInput ? win.input : null, win.alphaPrecision);
                var hex = all != null ? all.hex : null,
                    preview = tbody.find('.Preview'),
                    button = tbody.find('.Button');
                activePreview = preview.find('.Active:first').css({ backgroundColor: hex && '#' + hex || 'transparent' });
                currentPreview = preview.find('.Current:first').css({ backgroundColor: hex && '#' + hex || 'transparent' }).bind('click', currentClicked);
                setAlpha.call($this, currentPreview, Math.precision(color.current.val('a') * 100) / 255, 4);
                okButton = button.find('.Ok:first').bind('click', okClicked);
                cancelButton = button.find('.Cancel:first').bind('click', cancelClicked);
                grid = button.find('.Grid:first');
                setTimeout(
                  function()
                  {
                    setImg.call($this, colorMapL1, images.clientPath + 'Maps.png');
                    setImg.call($this, colorMapL2, images.clientPath + 'Maps.png');
                    setImg.call($this, colorMapL3, images.clientPath + 'map-opacity.png');
                    setImg.call($this, colorBarL1, images.clientPath + 'Bars.png');
                    setImg.call($this, colorBarL2, images.clientPath + 'Bars.png');
                    setImg.call($this, colorBarL3, images.clientPath + 'Bars.png');
                    setImg.call($this, colorBarL4, images.clientPath + 'Bars.png');
                    setImg.call($this, colorBarL5, images.clientPath + 'bar-opacity.png');
                    setImg.call($this, colorBarL6, images.clientPath + 'AlphaBar.png');
                    setImg.call($this, preview.find('div:first'), images.clientPath + 'preview-opacity.png');
                  }, 0);
                tbody.find('td.Radio input').bind('click', radioClicked);
                // initialize quick list
                if (color.quickList && color.quickList.length > 0)
                {
                  var html = '';
                  for (i = 0; i < color.quickList.length; i++)
                  {
                    /* if default colors are hex strings, change them to color objects */
                    if ((typeof (color.quickList[i])).toString().toLowerCase() == 'string') color.quickList[i] = new Color({ hex: color.quickList[i] });
                    var alpha = color.quickList[i].val('a');
                    var ahex = color.quickList[i].val('ahex');
                    if (!win.alphaSupport && ahex) ahex = ahex.substring(0, 6) + 'ff';
                    var quickHex = color.quickList[i].val('hex');
                    html+='<span class="QuickColor"' + (ahex && ' title="#' + ahex + '"' || '') + ' style="background-color:' + (quickHex && '#' + quickHex || '') + ';' + (quickHex ? '' : 'background-image:url(' + images.clientPath + 'NoColor.png)') + (win.alphaSupport && alpha && alpha < 255 ? ';opacity:' + Math.precision(alpha / 255, 4) + ';filter:Alpha(opacity=' + Math.precision(alpha / 2.55, 4) + ')' : '') + '">&nbsp;</span>';
                  }
                  setImg.call($this, grid, images.clientPath + 'bar-opacity.png');
                  grid.html(html);
                  grid.find('.QuickColor').click(quickPickClicked);
                }
                setColorMode.call($this, settings.color.mode);
                color.active.bind(activeColorChanged);
                $.isFunction(liveCallback) && color.active.bind(liveCallback);
                color.current.bind(currentColorChanged);
                // bind to input
                if (win.expandable)
                {
                  $this.icon = popup.parents('.Icon:first');
                  iconColor = $this.icon.find('.Color:first').css({ backgroundColor: hex && '#' + hex || 'transparent' });
                  iconAlpha = $this.icon.find('.Alpha:first');
                  setImg.call($this, iconAlpha, images.clientPath + 'bar-opacity.png');
                  setAlpha.call($this, iconAlpha, Math.precision(((255 - (all != null ? all.a : 0)) * 100) / 255, 4));
                  iconImage = $this.icon.find('.Image .ImageIcon:first').css(
                    {
                      background: 'url(\'' + images.clientPath + images.picker.file + '\') center center no-repeat',
                      width: '100%',
                      height: '100%',
                      position: 'absolute',
                      paddingLeft: '2px'
                    }).bind('click', iconImageClicked);
                  if (win.bindToInput&&win.updateInputColor)
                    win.input.css(
                      {
                        backgroundColor: hex && '#' + hex || 'transparent',
                        color: all == null || all.v > 75 ? '#000000' : '#ffffff',
                        textShadow: all == null || all.v > 75 ? '1px 1px 1px rgba(255,255,255,0.22)' : '1px 1px 1px rgba(0,0,0,0.22)'
                      });
                  moveBar = tbody.find('.Move:first').bind('mousedown', moveBarMouseDown);
                  color.active.bind(expandableColorChanged);
                }
                else show.call($this);
              },
            destroy =
              function()
              {
                container.find('td.Radio input').unbind('click', radioClicked);
                currentPreview.unbind('click', currentClicked);
                cancelButton.unbind('click', cancelClicked);
                okButton.unbind('click', okClicked);
                if (settings.window.expandable)
                {
                  iconImage.unbind('click', iconImageClicked);
                  moveBar.unbind('mousedown', moveBarMouseDown);
                  $this.icon = null;
                }
                container.find('.QuickColor').unbind('click', quickPickClicked);
                colorMapDiv = null;
                colorBarDiv = null;
                colorMapL1 = null;
                colorMapL2 = null;
                colorMapL3 = null;
                colorBarL1 = null;
                colorBarL2 = null;
                colorBarL3 = null;
                colorBarL4 = null;
                colorBarL5 = null;
                colorBarL6 = null;
                colorMap.destroy();
                colorMap = null;
                colorBar.destroy();
                colorBar = null;
                colorPicker.destroy();
                colorPicker = null;
                activePreview = null;
                currentPreview = null;
                okButton = null;
                cancelButton = null;
                grid = null;
                commitCallback = null;
                cancelCallback = null;
                liveCallback = null;
                container.html('');
                for (i = 0; i < List.length; i++) if (List[i] == $this) List.splice(i, 1);
              },
            images = settings.images, // local copies for YUI compressor
            localization = settings.localization,
            color =
              {
                active: (typeof(settings.color.active)).toString().toLowerCase() == 'string' ? new Color({ ahex: !settings.window.alphaSupport && settings.color.active ? settings.color.active.substring(0, 6) + 'ff' : settings.color.active }) : new Color({ ahex: !settings.window.alphaSupport && settings.color.active.val('ahex') ? settings.color.active.val('ahex').substring(0, 6) + 'ff' : settings.color.active.val('ahex') }),
                current: (typeof(settings.color.active)).toString().toLowerCase() == 'string' ? new Color({ ahex: !settings.window.alphaSupport && settings.color.active ? settings.color.active.substring(0, 6) + 'ff' : settings.color.active }) : new Color({ ahex: !settings.window.alphaSupport && settings.color.active.val('ahex') ? settings.color.active.val('ahex').substring(0, 6) + 'ff' : settings.color.active.val('ahex') }),
                quickList: settings.color.quickList
              };
          $.extend(true, $this, // public properties, methods, and callbacks
            {
              commitCallback: commitCallback, // commitCallback function can be overridden to return the selected color to a method you specify when the user clicks "OK"
              liveCallback: liveCallback, // liveCallback function can be overridden to return the selected color to a method you specify in live mode (continuous update)
              cancelCallback: cancelCallback, // cancelCallback function can be overridden to a method you specify when the user clicks "Cancel"
              color: color,
              show: show,
              hide: hide,
              destroy: destroy // destroys this control entirely, removing all events and objects, and removing itself from the List
            });
          List.push($this);
          setTimeout(
            function()
            {
              initialize.call($this);
            }, 0);
        });
    };
  $.fn.jPicker.defaults = /* jPicker defaults - you can change anything in this section (such as the clientPath to your images) without fear of breaking the program */
      {
      window:
        {
          title: null, /* any title for the jPicker window itself - displays "Drag Markers To Pick A Color" if left null */
          effects:
          {
            type: 'fade', /* effect used to show/hide an expandable picker. Acceptable values "slide", "show", "fade" */
            speed:
            {
              show: 'fast', /* duration of "show" effect. Acceptable values are "fast", "slow", or time in ms */
              hide: 'fast' /* duration of "hide" effect. Acceptable values are "fast", "slow", or time in ms */
            }
          },
          position:
          {
            x: 'screenCenter', /* acceptable values "left", "center", "right", "screenCenter", or relative px value */
            y: 'top' /* acceptable values "top", "bottom", "center", or relative px value */
          },
          expandable: false, /* default to large static picker - set to true to make an expandable picker (small icon with popup) - set automatically when binded to input element */
          liveUpdate: true, /* set false if you want the user to have to click "OK" before the binded input box updates values (always "true" for expandable picker) */
          alphaSupport: false, /* set to true to enable alpha picking */
          alphaPrecision: 0, /* set decimal precision for alpha percentage display - hex codes do not map directly to percentage integers - range 0-2 */
          updateInputColor: true /* set to false to prevent binded input colors from changing */
        },
      color:
        {
          mode: 'h', /* acceptabled values "h" (hue), "s" (saturation), "v" (value), "r" (red), "g" (green), "b" (blue), "a" (alpha) */
          active: new Color({ ahex: '#ffcc00ff' }), /* acceptable values are any declared $.jPicker.Color object or string HEX value (e.g. #ffc000) WITH OR WITHOUT the "#" prefix */
          quickList: /* the quick pick color list */
            [
              new Color({ h: 360, s: 33, v: 100 }), /* acceptable values are any declared $.jPicker.Color object or string HEX value (e.g. #ffc000) WITH OR WITHOUT the "#" prefix */
              new Color({ h: 360, s: 66, v: 100 }),
              new Color({ h: 360, s: 100, v: 100 }),
              new Color({ h: 360, s: 100, v: 75 }),
              new Color({ h: 360, s: 100, v: 50 }),
              new Color({ h: 180, s: 0, v: 100 }),
              new Color({ h: 30, s: 33, v: 100 }),
              new Color({ h: 30, s: 66, v: 100 }),
              new Color({ h: 30, s: 100, v: 100 }),
              new Color({ h: 30, s: 100, v: 75 }),
              new Color({ h: 30, s: 100, v: 50 }),
              new Color({ h: 180, s: 0, v: 90 }),
              new Color({ h: 60, s: 33, v: 100 }),
              new Color({ h: 60, s: 66, v: 100 }),
              new Color({ h: 60, s: 100, v: 100 }),
              new Color({ h: 60, s: 100, v: 75 }),
              new Color({ h: 60, s: 100, v: 50 }),
              new Color({ h: 180, s: 0, v: 80 }),
              new Color({ h: 90, s: 33, v: 100 }),
              new Color({ h: 90, s: 66, v: 100 }),
              new Color({ h: 90, s: 100, v: 100 }),
              new Color({ h: 90, s: 100, v: 75 }),
              new Color({ h: 90, s: 100, v: 50 }),
              new Color({ h: 180, s: 0, v: 70 }),
              new Color({ h: 120, s: 33, v: 100 }),
              new Color({ h: 120, s: 66, v: 100 }),
              new Color({ h: 120, s: 100, v: 100 }),
              new Color({ h: 120, s: 100, v: 75 }),
              new Color({ h: 120, s: 100, v: 50 }),
              new Color({ h: 180, s: 0, v: 60 }),
              new Color({ h: 150, s: 33, v: 100 }),
              new Color({ h: 150, s: 66, v: 100 }),
              new Color({ h: 150, s: 100, v: 100 }),
              new Color({ h: 150, s: 100, v: 75 }),
              new Color({ h: 150, s: 100, v: 50 }),
              new Color({ h: 180, s: 0, v: 50 }),
              new Color({ h: 180, s: 33, v: 100 }),
              new Color({ h: 180, s: 66, v: 100 }),
              new Color({ h: 180, s: 100, v: 100 }),
              new Color({ h: 180, s: 100, v: 75 }),
              new Color({ h: 180, s: 100, v: 50 }),
              new Color({ h: 180, s: 0, v: 40 }),
              new Color({ h: 210, s: 33, v: 100 }),
              new Color({ h: 210, s: 66, v: 100 }),
              new Color({ h: 210, s: 100, v: 100 }),
              new Color({ h: 210, s: 100, v: 75 }),
              new Color({ h: 210, s: 100, v: 50 }),
              new Color({ h: 180, s: 0, v: 30 }),
              new Color({ h: 240, s: 33, v: 100 }),
              new Color({ h: 240, s: 66, v: 100 }),
              new Color({ h: 240, s: 100, v: 100 }),
              new Color({ h: 240, s: 100, v: 75 }),
              new Color({ h: 240, s: 100, v: 50 }),
              new Color({ h: 180, s: 0, v: 20 }),
              new Color({ h: 270, s: 33, v: 100 }),
              new Color({ h: 270, s: 66, v: 100 }),
              new Color({ h: 270, s: 100, v: 100 }),
              new Color({ h: 270, s: 100, v: 75 }),
              new Color({ h: 270, s: 100, v: 50 }),
              new Color({ h: 180, s: 0, v: 10 }),
              new Color({ h: 300, s: 33, v: 100 }),
              new Color({ h: 300, s: 66, v: 100 }),
              new Color({ h: 300, s: 100, v: 100 }),
              new Color({ h: 300, s: 100, v: 75 }),
              new Color({ h: 300, s: 100, v: 50 }),
              new Color({ h: 180, s: 0, v: 0 }),
              new Color({ h: 330, s: 33, v: 100 }),
              new Color({ h: 330, s: 66, v: 100 }),
              new Color({ h: 330, s: 100, v: 100 }),
              new Color({ h: 330, s: 100, v: 75 }),
              new Color({ h: 330, s: 100, v: 50 }),
              new Color({ h: 180, s: 10, v: 0 })
            ]
        },
      images:
        {
          clientPath: '/jPicker/images/', /* Path to image files */
          colorMap:
          {
            width: 256,
            height: 256,
            arrow:
            {
              file: 'mappoint.gif', /* ColorMap arrow icon */
              width: 15,
              height: 15
            }
          },
          colorBar:
          {
            width: 20,
            height: 256,
            arrow:
            {
              file: 'rangearrows.gif', /* ColorBar arrow icon */
              width: 20,
              height: 7
            }
          },
          picker:
          {
            file: 'brush.png', /* Color Picker icon */
            width: 17,
            height: 16
          }
        },
      localization: /* alter these to change the text presented by the picker (e.g. different language) */
        {
          text:
          {
            title: 'Drag Markers To Pick A Color',
            newColor: 'new',
            currentColor: 'current',
            ok: 'OK',
            cancel: 'Cancel'
          },
          tooltips:
          {
            colors:
            {
              newColor: 'New Color - Press &ldquo;OK&rdquo; To Commit',
              currentColor: 'Click To Revert To Original Color'
            },
            buttons:
            {
              ok: 'Commit To This Color Selection',
              cancel: 'Cancel And Revert To Original Color'
            },
            hue:
            {
              radio: 'Set To &ldquo;Hue&rdquo; Color Mode',
              textbox: 'Enter A &ldquo;Hue&rdquo; Value (0-360&deg;)'
            },
            saturation:
            {
              radio: 'Set To &ldquo;Saturation&rdquo; Color Mode',
              textbox: 'Enter A &ldquo;Saturation&rdquo; Value (0-100%)'
            },
            value:
            {
              radio: 'Set To &ldquo;Value&rdquo; Color Mode',
              textbox: 'Enter A &ldquo;Value&rdquo; Value (0-100%)'
            },
            red:
            {
              radio: 'Set To &ldquo;Red&rdquo; Color Mode',
              textbox: 'Enter A &ldquo;Red&rdquo; Value (0-255)'
            },
            green:
            {
              radio: 'Set To &ldquo;Green&rdquo; Color Mode',
              textbox: 'Enter A &ldquo;Green&rdquo; Value (0-255)'
            },
            blue:
            {
              radio: 'Set To &ldquo;Blue&rdquo; Color Mode',
              textbox: 'Enter A &ldquo;Blue&rdquo; Value (0-255)'
            },
            alpha:
            {
              radio: 'Set To &ldquo;Alpha&rdquo; Color Mode',
              textbox: 'Enter A &ldquo;Alpha&rdquo; Value (0-100)'
            },
            hex:
            {
              textbox: 'Enter A &ldquo;Hex&rdquo; Color Value (#000000-#ffffff)',
              alpha: 'Enter A &ldquo;Alpha&rdquo; Value (#00-#ff)'
            }
          }
        }
    };
})(jQuery, '1.1.6');
jQuery.noConflict();


function OfflajnfireEvent(element,event){
    if ((document.createEventObject && !dojo.isIE) || (document.createEventObject && dojo.isIE && dojo.isIE < 9)){
      var evt = document.createEventObject();
      return element.fireEvent('on'+event,evt);
    }else{
      var evt = document.createEvent("HTMLEvents");
      evt.initEvent(event, true, true );
      return !element.dispatchEvent(evt);
    }
}


dojo.declare("OfflajnRadio", null, {
	constructor: function(args) {
	 dojo.mixin(this,args);
   this.selected = -1;
	 this.init();
  },
  
  init: function() {
    this.hidden = dojo.byId(this.id);
    this.hidden.radioobj = this;
    dojo.connect(this.hidden, 'change', this, 'reset');
    this.container = dojo.byId('offlajnradiocontainer' + this.id);
    this.items = dojo.query('.radioelement', this.container);
    if(this.mode == "image") this.imgitems = dojo.query('.radioelement_img', this.container);
    dojo.forEach(this.items, function(item, i){
      if(this.hidden.value == this.values[i]) this.selected = i;
      dojo.connect(item, 'onclick', dojo.hitch(this, 'selectItem', i));
    }, this);
    
    this.reset();
  },
  
  reset: function(){
    var i = this.map[this.hidden.value];
    if(!i) i = 0;
    this.selectItem(i);
  },
  
  selectItem: function(i) {
    if(this.selected == i) {
      if(this.mode == "image") this.changeImage(i);
     return;
    }
    if(this.selected >= 0) dojo.removeClass(this.items[this.selected], 'selected');
    if(this.mode == "image") this.changeImage(i);
    this.selected = i;
    dojo.addClass(this.items[this.selected], 'selected');
    if(this.hidden.value != this.values[this.selected]){
      this.hidden.value = this.values[this.selected];
      this.fireEvent(this.hidden, 'change');
    }
  },
  
  changeImage: function(i) {
    dojo.style(this.imgitems[this.selected], 'backgroundPosition', '0px 0px');
    dojo.style(this.imgitems[i], 'backgroundPosition', '0px -8px');
  },

  fireEvent: function(element,event){
    if ((document.createEventObject && !dojo.isIE) || (document.createEventObject && dojo.isIE && dojo.isIE < 9)){
      var evt = document.createEventObject();
      return element.fireEvent('on'+event,evt);
    }else{
      var evt = document.createEvent("HTMLEvents");
      evt.initEvent(event, true, true );
      return !element.dispatchEvent(evt);
    }
  }
});



dojo.declare("OfflajnSwitcher", null, {
	constructor: function(args) {
	 dojo.mixin(this,args);
   this.w = 11;
	 this.init();
  },
  
  
  init: function() {
    this.switcher = dojo.byId('offlajnswitcher_inner' + this.id);
    this.input = dojo.byId(this.id);
    this.state = this.map[this.input.value];
    this.click = dojo.connect(this.switcher, 'onclick', this, 'controller');
    dojo.connect(this.input, 'onchange', this, 'setValue');
    this.elements = new Array();
    this.getUnits();
    this.setSwitcher();
  },
  
  getUnits: function() {
    var units = dojo.create('div', {'class': 'offlajnswitcher_units' }, this.switcher.parentNode, "after");
    dojo.forEach(this.units, function(item, i){
      this.elements[i] = dojo.create('span', {'class': 'offlajnswitcher_unit', 'innerHTML': item }, units);
      if(this.mode) {
        this.elements[i].innerHTML = '';
        this.elements[i] = dojo.create('img', {'src': this.url + item }, this.elements[i]);
      }     
      this.elements[i].i = i;
      dojo.connect(this.elements[i], 'onclick', this, 'selectUnit');
    }, this);
  },
  
  getBgpos: function() {
    var pos = dojo.style(this.switcher, 'backgroundPosition');
    if(dojo.isIE <= 8){
      pos = dojo.style(this.switcher, 'backgroundPositionX')+' '+dojo.style(this.switcher, 'backgroundPositionY');
    }
    var bgp = pos.split(' ');
    bgp[1] = parseInt(bgp[1]);
    return !bgp[1] ? 0 : bgp[1];
  },
  
  selectUnit: function(e) {
    this.state = (e.target.i) ? 0 : 1;
    this.controller();
  },
  
  setSelected: function() {
    var s = (this.state) ? 0 : 1;
    dojo.removeClass(this.elements[s], 'selected');
    dojo.addClass(this.elements[this.state], 'selected');
  },
  
  controller: function() {
    if(this.anim) this.anim.stop();
    this.state ? this.setSecond() : this.setFirst();
  },
  
  
  setValue: function() {
    if(this.values[this.state] != this.input.value) {
      this.controller();
    }
  },
  
  setSwitcher: function() {
    (this.state) ? this.setFirst() : this.setSecond();
  },
  
  changeState: function(state){
    if(this.state != state){
      this.state = state;
      this.stateChanged();
    }
    this.setSelected();
  },  
  
  stateChanged: function(){
    this.input.value = this.values[this.state];
    this.fireEvent(this.input, 'change'); 
  },

  fireEvent: function(element,event){
    if ((document.createEventObject && !dojo.isIE) || (document.createEventObject && dojo.isIE && dojo.isIE < 9)){
      var evt = document.createEventObject();
      return element.fireEvent('on'+event,evt);
    }else{
      var evt = document.createEvent("HTMLEvents");
      evt.initEvent(event, true, true );
      return !element.dispatchEvent(evt);
    }
  },
  
  setFirst: function() {
    this.changeState(1);
    var bgp = this.getBgpos();
    this.anim = new dojo.Animation({
      curve: new dojo._Line(bgp, 0),
      node: this.switcher,
      duration: 200,
      onAnimate: function(){
				var str = "center " + Math.floor(arguments[0])+"px";
				dojo.style(this.node,"backgroundPosition",str);
			}
    }).play();
  },
  
  
  setSecond: function() {
    this.changeState(0);  
    var bgp = this.getBgpos();
    this.anim = new dojo.Animation({
      curve: new dojo._Line(bgp, -1*this.w),
      node: this.switcher,
      duration: 200,
      onAnimate: function(){
				var str =  "center " + Math.floor(arguments[0])+"px";
				dojo.style(this.node,"backgroundPosition",str);
			}
    }).play();
  }
  
});


dojo.declare("OfflajnOnOff", null, {
	constructor: function(args) {
	 dojo.mixin(this,args);
   this.w = 26;
	 this.init();
  },
  
  
  init: function() {
    this.switcher = dojo.byId('offlajnonoff' + this.id);
    this.input = dojo.byId(this.id);
    this.state = parseInt(this.input.value);
    this.click = dojo.connect(this.switcher, 'onclick', this, 'controller');
    if(this.mode == 'button') {
      this.img = dojo.query('.onoffbutton_img', this.switcher);
      if(dojo.hasClass(this.switcher, 'selected')) dojo.style(this.img[0], 'backgroundPosition', '0px -11px'); 
    } else {
      dojo.connect(this.switcher, 'onmousedown', this, 'mousedown');
    }
    dojo.connect(this.input, 'onchange', this, 'setValue');
  },
  
  controller: function() {
    if(!this.mode) {
      if(this.anim) this.anim.stop();
      this.state ? this.setOff() : this.setOn();
    } else if(this.mode == "button") {
      this.state ? this.setBtnOff() : this.setBtnOn();
    }
  },
    
  setBtnOn: function() {
    dojo.style(this.img[0], 'backgroundPosition', '0px -11px');
    dojo.addClass(this.switcher, 'selected');
    this.changeState(1);
  },
  
  setBtnOff: function() {
    dojo.style(this.img[0], 'backgroundPosition', '0px 0px');
    dojo.removeClass(this.switcher, 'selected');
    this.changeState(0);
  },
  
  setValue: function() {
    if(this.state != this.input.value) {
      this.controller();
    }
  },
  
  changeState: function(state){
    if(this.state != state){
      this.state = state;
      this.stateChanged();
    }
  },  
  
  stateChanged: function(){
    this.input.value = this.state;
    this.fireEvent(this.input, 'change'); 
  },
  
  mousedown: function(e){
    this.startState = this.state;
    this.move = dojo.connect(document, 'onmousemove', this, 'mousemove');
    this.up = dojo.connect(document, 'onmouseup', this, 'mouseup');
    this.startX = e.clientX;
  },
  
  mousemove: function(e){
    var x = e.clientX-this.startX;
    if(!this.startState) x-=this.w;
    if(x > 0){
      x = 0;
      this.changeState(1);
    }
    if(x < -1*this.w){
      x = -1*this.w;
      this.changeState(0);
    }
		var str = x+"px 0px";
    dojo.style(this.switcher,"backgroundPosition",str);
  },
  
  mouseup: function(e){
    dojo.disconnect(this.move);
    dojo.disconnect(this.up);
  },

  fireEvent: function(element,event){
    if ((document.createEventObject && !dojo.isIE) || (document.createEventObject && dojo.isIE && dojo.isIE < 9)){
      var evt = document.createEventObject();
      return element.fireEvent('on'+event,evt);
    }else{
      var evt = document.createEvent("HTMLEvents");
      evt.initEvent(event, true, true );
      return !element.dispatchEvent(evt);
    }
  },
  
  getBgpos: function() {
    var pos = dojo.style(this.switcher, 'backgroundPosition');
    if(dojo.isIE <= 8){
      pos = dojo.style(this.switcher, 'backgroundPositionX')+' '+dojo.style(this.switcher, 'backgroundPositionY');
    }
    var bgp = pos.split(' ');
    bgp[0] = parseInt(bgp[0]);
    return !bgp[0] ? 0 : bgp[0];
  },
  
  setOn: function() {
    this.changeState(1);
    
    this.anim = new dojo.Animation({
      curve: new dojo._Line(this.getBgpos(),0),
      node: this.switcher,
      onAnimate: function(){
				var str = Math.floor(arguments[0])+"px 0px";
				dojo.style(this.node,"backgroundPosition",str);
			}
    }).play();
  },
  
  
  setOff: function() {
    this.changeState(0);
      
    this.anim = new dojo.Animation({
      curve: new dojo._Line(this.getBgpos(), -1*this.w),
      node: this.switcher,
      onAnimate: function(){
				var str = Math.floor(arguments[0])+"px 0px";
				dojo.style(this.node,"backgroundPosition",str);
			}
    }).play();
  }
  
});

/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.window"]){
dojo._hasResource["dojo.window"]=true;
dojo.provide("dojo.window");
dojo.getObject("window",true,dojo);
dojo.window.getBox=function(){
var _1=(dojo.doc.compatMode=="BackCompat")?dojo.body():dojo.doc.documentElement;
var _2=dojo._docScroll();
return {w:_1.clientWidth,h:_1.clientHeight,l:_2.x,t:_2.y};
};
dojo.window.get=function(_3){
if(dojo.isIE&&window!==document.parentWindow){
_3.parentWindow.execScript("document._parentWindow = window;","Javascript");
var _4=_3._parentWindow;
_3._parentWindow=null;
return _4;
}
return _3.parentWindow||_3.defaultView;
};
dojo.window.scrollIntoView=function(_5,_6){
try{
_5=dojo.byId(_5);
var _7=_5.ownerDocument||dojo.doc,_8=_7.body||dojo.body(),_9=_7.documentElement||_8.parentNode,_a=dojo.isIE,_b=dojo.isWebKit;
if((!(dojo.isMoz||_a||_b||dojo.isOpera)||_5==_8||_5==_9)&&(typeof _5.scrollIntoView!="undefined")){
_5.scrollIntoView(false);
return;
}
var _c=_7.compatMode=="BackCompat",_d=(_a>=9&&_5.ownerDocument.parentWindow.frameElement)?((_9.clientHeight>0&&_9.clientWidth>0&&(_8.clientHeight==0||_8.clientWidth==0||_8.clientHeight>_9.clientHeight||_8.clientWidth>_9.clientWidth))?_9:_8):(_c?_8:_9),_e=_b?_8:_d,_f=_d.clientWidth,_10=_d.clientHeight,rtl=!dojo._isBodyLtr(),_11=_6||dojo.position(_5),el=_5.parentNode,_12=function(el){
return ((_a<=6||(_a&&_c))?false:(dojo.style(el,"position").toLowerCase()=="fixed"));
};
if(_12(_5)){
return;
}
while(el){
if(el==_8){
el=_e;
}
var _13=dojo.position(el),_14=_12(el);
if(el==_e){
_13.w=_f;
_13.h=_10;
if(_e==_9&&_a&&rtl){
_13.x+=_e.offsetWidth-_13.w;
}
if(_13.x<0||!_a){
_13.x=0;
}
if(_13.y<0||!_a){
_13.y=0;
}
}else{
var pb=dojo._getPadBorderExtents(el);
_13.w-=pb.w;
_13.h-=pb.h;
_13.x+=pb.l;
_13.y+=pb.t;
var _15=el.clientWidth,_16=_13.w-_15;
if(_15>0&&_16>0){
_13.w=_15;
_13.x+=(rtl&&(_a||el.clientLeft>pb.l))?_16:0;
}
_15=el.clientHeight;
_16=_13.h-_15;
if(_15>0&&_16>0){
_13.h=_15;
}
}
if(_14){
if(_13.y<0){
_13.h+=_13.y;
_13.y=0;
}
if(_13.x<0){
_13.w+=_13.x;
_13.x=0;
}
if(_13.y+_13.h>_10){
_13.h=_10-_13.y;
}
if(_13.x+_13.w>_f){
_13.w=_f-_13.x;
}
}
var l=_11.x-_13.x,t=_11.y-Math.max(_13.y,0),r=l+_11.w-_13.w,bot=t+_11.h-_13.h;
if(r*l>0){
var s=Math[l<0?"max":"min"](l,r);
if(rtl&&((_a==8&&!_c)||_a>=9)){
s=-s;
}
_11.x+=el.scrollLeft;
el.scrollLeft+=s;
_11.x-=el.scrollLeft;
}
if(bot*t>0){
_11.y+=el.scrollTop;
el.scrollTop+=Math[t<0?"max":"min"](t,bot);
_11.y-=el.scrollTop;
}
el=(el!=_e)&&!_14&&el.parentNode;
}
}
catch(error){
console.error("scrollIntoView: "+error);
_5.scrollIntoView(false);
}
};
}

dojo.require("dojo.window");

dojo.declare("FontConfigurator", null, {
	constructor: function(args) {  
    dojo.mixin(this,args);
    window.loadedFont = {};
    this.init();
  },
  
  
  init: function() {
    this.btn = dojo.byId(this.id+'change');
    dojo.connect(this.btn, 'onclick', this, 'showWindow');    
    this.settings = dojo.clone(this.origsettings);
    this.hidden = dojo.byId(this.id);
    dojo.connect(this.hidden, 'onchange', this, 'reset');
    this.reset();
  },
  
  reset: function(){  
    if(this.hidden.value == '') this.hidden.value = dojo.toJson(this.settings);        
    if(this.hidden.value != dojo.toJson(this.settings)){
      var newsettings = {};
      try{            
        newsettings = dojo.fromJson(this.hidden.value.replace(/\\"/g, '"'));
        if(dojo.isArray(newsettings)){
          newsettings = {};
        }                
      }catch(e){
        this.hidden.value = dojo.toJson(newsettings);
      }      
      for(var s in this.origsettings){
        if(!newsettings[s]){
          newsettings[s] = this.origsettings[s];
        }
      } 
      this.settings = this.origsettings = newsettings;
    }
  },
  
  showOverlay: function(){
    if(!this.overlayBG){
      this.overlayBG = dojo.create('div',{'class': 'blackBg'}, dojo.body());
    }
    dojo.removeClass(this.overlayBG, 'hide');
    dojo.style(this.overlayBG,{
      'opacity': 0.3
    });
  },
  
  showWindow: function(e){
    dojo.stopEvent(e);
    this.showOverlay();
    if(!this.window){
      this.window = dojo.create('div', {'class': 'OfflajnWindowFont'}, dojo.body());
      var closeBtn = dojo.create('div', {'class': 'OfflajnWindowClose'}, this.window);
      dojo.connect(closeBtn, 'onclick', this, 'closeWindow');
      var inner = dojo.create('div', {'class': 'OfflajnWindowInner'}, this.window);
      var h3 = dojo.create('h3', {'innerHTML': 'Font selector'+this.elements.tab['html']}, inner);
      
      this.reset = dojo.create('div', {'class': 'offlajnfont_reset hasOfflajnTip', 'tooltippos': 'T','title' : 'It will clear the settings on the current tab.', 'innerHTML': '<div class="offlajnfont_reset_img"></div>'}, h3);
      dojo.global.toolTips.connectToolTips(h3);
      dojo.connect(this.reset, 'onclick', this, 'resetValues');
      
      this.tab = dojo.byId(this.id+'tab');
      
      dojo.connect(this.tab, 'change', this, 'changeTab');

      dojo.create('div', {'class': 'OfflajnWindowLine'}, inner);
      var fields = dojo.create('div', {'class': 'OfflajnWindowFields'}, inner);
      
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Type<br />'+this.elements.type['html']}, fields);
      this.type = dojo.byId(this.elements.type.id);

      this.familyc = dojo.create('div', {'class': 'OfflajnWindowField'}, fields);
      
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Size<br />'+this.elements.size['html']}, fields);
      this.size = dojo.byId(this.elements.size['id']);
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Color<br />'+this.elements.color['html']}, fields);
      this.color = dojo.byId(this.elements.color['id']);
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Decoration<br />'+this.elements.bold['html']+this.elements.italic['html']+this.elements.underline['html']}, fields);
      this.bold = dojo.byId(this.elements.bold['id']);
      this.italic = dojo.byId(this.elements.italic['id']);
      this.underline = dojo.byId(this.elements.underline['id']);
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Align<br />'+this.elements.align['html']}, fields);
      this.align = dojo.byId(this.elements.align['id']);
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Alternative font<br />'+this.elements.afont['html']}, fields);
      this.afont = dojo.byId(this.elements.afont['id']);
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Text shadow<br />'+this.elements.tshadow['html']}, fields);
      this.tshadow = dojo.byId(this.elements.tshadow['id']);
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Line height<br />'+this.elements.lineheight['html']}, fields);
      this.lineheight = dojo.byId(this.elements.lineheight['id']);
      
      dojo.create('div', {'class': 'OfflajnWindowTester', 'innerHTML': '<span>Grumpy wizards make toxic brew for the evil Queen and Jack.</span>'}, inner);
      this.tester = dojo.query('.OfflajnWindowTester span', inner)[0];
      var saveCont = dojo.create('div', {'class': 'OfflajnWindowSaveContainer'}, inner);
      var savebtn = dojo.create('div', {'class': 'OfflajnWindowSave', 'innerHTML': 'SAVE'}, saveCont);
      dojo.connect(savebtn, 'onclick', this, 'save');
      eval(this.script);
      
      
      dojo.connect(this.type, 'change', this, 'changeType');
      dojo.connect(this.size, 'change', dojo.hitch(this, 'changeSet', 'size'));
      dojo.connect(this.size, 'change', this, 'changeSize');
      dojo.connect(this.color, 'change', dojo.hitch(this, 'changeSet', 'color'));
      dojo.connect(this.color, 'change', this, 'changeColor');
      dojo.connect(this.bold, 'change', dojo.hitch(this, 'changeSet', 'bold'));
      dojo.connect(this.bold, 'change', this, 'changeWeight');
      dojo.connect(this.italic, 'change', dojo.hitch(this, 'changeSet', 'italic'));
      dojo.connect(this.italic, 'change', this, 'changeItalic');
      dojo.connect(this.underline, 'change', dojo.hitch(this, 'changeSet', 'underline'));
      dojo.connect(this.underline, 'change', this, 'changeUnderline');
      dojo.connect(this.afont, 'change', dojo.hitch(this, 'changeSet', 'afont'));
      dojo.connect(this.afont, 'change', this, 'changeFamily');
      dojo.connect(this.align, 'change', dojo.hitch(this, 'changeSet', 'align'));
      dojo.connect(this.align, 'change', this, 'changeAlign');
      dojo.connect(this.tshadow, 'change', dojo.hitch(this, 'changeSet', 'tshadow'));
      dojo.connect(this.tshadow, 'change', this, 'changeTshadow');
      dojo.connect(this.lineheight, 'change', dojo.hitch(this, 'changeSet', 'lineheight'));
      dojo.connect(this.lineheight, 'change', this, 'changeLineheight');
      
      dojo.addOnLoad(this, function(){
        this.changeTab();
        this.changeType();
      });
    }else{
      this.settings = dojo.fromJson(this.hidden.value.replace(/\\"/g, '"'));
      this.loadSettings();
    }
    dojo.removeClass(this.window, 'hide');
    this.exit = dojo.connect(document, "onkeypress", this, "keyPressed");
  },
  
  closeWindow: function(){
    dojo.addClass(this.window, 'hide');
    dojo.addClass(this.overlayBG, 'hide');
  },
  
  save: function(){  
    this.hidden.value = dojo.toJson(this.settings);
    this.closeWindow();
  },
  
  loadSettings: function(){
    if(this.defaultTab!=this.t){
      this._loadSettings(this.defaultTab, true);
    }
    this._loadSettings(this.t, false);
    this.refreshFont();
  },
  
  _loadSettings: function(tab, def){
    var set = this.settings[tab];
    for(s in set){
      if(this[s] && (!def || def && !this.settings[this.t][s])){
        this.changeHidden(this[s], set[s]);
      }
    }
  },
  
  resetValues: function() {
    if(this.t != this.defaultTab) {
      this.settings[this.t] = {};
      this.loadSettings();
    }
  },
  
  loadFamily: function(e){
    dojo.stopEvent(e);
    var list = this.family.listobj;
    
    this.maxIteminWindow = parseInt(list.scrollbar.windowHeight/list.lineHeight)+1;
    this.loadFamilyScroll();
    list.scrollbar.onScroll = dojo.hitch(this, 'loadFamilyScroll');
  },
  
  loadFamilyScroll: function(){
    var set = this.settings[this.t];
    var list = this.family.listobj;
    var start = parseInt(list.scrollbar.curr*-1/list.lineHeight);
    for(var i = start; i <= start+this.maxIteminWindow && i < list.list.length; i++){
      var item = list.list[i];
      var option = list.options[i].value;
      this.loadGoogleFont(set.subset, option);
      dojo.style(item, 'fontFamily', "'"+option+"'");
    }
  },
  
  loadGoogleFont: function(subset, family, weight, italic){
    if(!weight) weight = 400;
    italic ? italic = 'italic' : italic = '';
    var hash = subset+family+weight+italic;
    if(!window.loadedFont[hash]){
      window.loadedFont[hash] = true; 
      setTimeout(function(){
        dojo.create('link', {rel:'stylesheet', type: 'text/css', href: 'http://fonts.googleapis.com/css?family='+family+':'+weight+italic+'&subset='+subset}, dojo.body())
      },500);
    } 
  },
  
  changeType: function(e){
    if(e){
      var obj = e.target.listobj;
      if(obj.map[obj.hidden.value] != obj.hidden.selectedIndex) return;
    }
    var set = this.settings[this.t];
    set.type = this.type.value;
    if(!this.elements.type[set.type]){
      if(!this.family){
        this.familyc.innerHTML = 'Family<br />'+this.elements.type['Latin']['html'];
        this.family = dojo.byId(this.elements.type['Latin']['id']);
        eval(this.elements.type['Latin']['script']);
      }
      dojo.addOnLoad(this, function(){
        dojo.style(this.family.listobj.container,'visibility', 'hidden');
      });
      set.family = '';
      this.changeFamily();
      return;
    }
    this.familyc.innerHTML = 'Family<br />'+this.elements.type[set.type]['html'];
    this.family = dojo.byId(this.elements.type[set.type]['id']);
    
    dojo.connect(this.family, 'change', dojo.hitch(this, 'changeSet', 'family'));
    dojo.connect(this.family, 'click', this, 'loadFamily');
    dojo.connect(this.family, 'change', this, 'refreshFont');
    eval(this.elements.type[set.type]['script']);
    if(set.family){
      dojo.addOnLoad(this, function(){
        var set = this.settings[this.t];
        this.changeHidden(this.family, set.family);
      });
    }
    var subset = this.type.value;
    if(subset == 'LatinExtended'){
      subset = 'latin,latin-ext';
    }else if(subset == 'CyrillicExtended'){
      subset = 'cyrillic,cyrillic-ext';
    }else if(subset == 'GreekExtended'){
      subset = 'greek,greek-ext';
    }
    set.subset = subset;
  },
  
  changeSet: function(name, e){
    var set = this.settings[this.t];
    set[name] = e.target.value;
  },
  
  refreshFont: function(){
    var set = this.settings[this.t];
    if(this.bold) this.changeWeight();
    if(this.italic) this.changeItalic();
    if(this.underline) this.changeUnderline();
    this.changeFamily();
    if(this.size) this.changeSize();
    if(this.color) this.changeColor();
    if(this.align) this.changeAlign();
    if(this.tshadow) this.changeTshadow();
    if(this.lineheight) this.changeLineheight();
  },
  
  changeWeight: function(){
    dojo.style(this.tester, 'fontWeight', (parseInt(this.bold.value) ? 'bold' : 'normal'));
  },
  
  changeItalic: function(){
    dojo.style(this.tester, 'fontStyle', (parseInt(this.italic.value) ? 'italic' : 'normal'));
  },
  
  changeUnderline: function(){
    dojo.style(this.tester, 'textDecoration', (parseInt(this.underline.value) ? 'underline' : 'none'));
  },
  
  changeFamily: function(){
    var set = this.settings[this.t];
    var f = '';
    if(this.family && set.type != '0'){
      f = "'"+this.family.value+"'";
      this.loadGoogleFont(set.subset, this.family.value, (this.bold && parseInt(this.bold.value) ? '700' : '400'), parseInt(this.italic.value));
    }
    if(this.afont){
      var afont = this.afont.value.split('||'); 
      if(afont[0] != '' && parseInt(afont[1])){
        if(f != '') f+=',';
        f+=afont[0];
      }
    }
    dojo.style(this.tester, 'fontFamily', f);
  },
  
  changeSize: function(){
    dojo.style(this.tester, 'fontSize', this.size.value.replace('||', '') );
  },
  
  changeColor: function(){
    dojo.style(this.tester, 'color', '#'+this.color.value );
  },
  
  changeAlign: function(){
    dojo.style(this.tester.parentNode, 'textAlign', this.align.value );
  },
  
  changeTshadow: function(){
    var s = this.tshadow.value.replace(/\|\|/g,'').split('|*|');
    var shadow = '';
    if(parseInt(s[4])){
      s[4] = '';
      if (s[3].length > 6) {
        var c = s[3].match(/(..)(..)(..)(..)/);
        s[3]='rgba('+Number('0x'+c[1])+','+Number('0x'+c[2])+','+Number('0x'+c[3])+','+Number('0x'+c[4])/255.+')';
      } else s[3] = '#'+s[3];
      shadow = s.join(' ');
    }
    dojo.style(this.tester, 'textShadow', shadow);
  },
  
  changeLineheight: function(){
    dojo.style(this.tester, 'lineHeight', this.lineheight.value);
  },
  
  changeTab: function(){
    var radio = this.tab.radioobj;
    this.t = this.tab.value;
    if(this.t != this.defaultTab){
      dojo.style(this.reset,'display','block');
    }else{
      dojo.style(this.reset,'display','none');
    }
    this.loadSettings();
  },
  
  changeHidden: function(el, value){
    if(el.value == value) return;
    el.value = value;
    this.fireEvent(el, 'change');
  },

  fireEvent: function(element,event){
    if ((document.createEventObject && !dojo.isIE) || (document.createEventObject && dojo.isIE && dojo.isIE < 9)){
      var evt = document.createEventObject();
      return element.fireEvent('on'+event,evt);
    }else{
      var evt = document.createEvent("HTMLEvents");
      evt.initEvent(event, true, true );
      return !element.dispatchEvent(evt);
    }
  },
  
  keyPressed: function(e) {
    if(e.keyCode == 27) { 
      this.closeWindow();
      dojo.disconnect(this.exit);
    }
  }
});



dojo.declare("OfflajnOnOff", null, {
	constructor: function(args) {
	 dojo.mixin(this,args);
   this.w = 26;
	 this.init();
  },
  
  
  init: function() {
    this.switcher = dojo.byId('offlajnonoff' + this.id);
    this.input = dojo.byId(this.id);
    this.state = parseInt(this.input.value);
    this.click = dojo.connect(this.switcher, 'onclick', this, 'controller');
    if(this.mode == 'button') {
      this.img = dojo.query('.onoffbutton_img', this.switcher);
      if(dojo.hasClass(this.switcher, 'selected')) dojo.style(this.img[0], 'backgroundPosition', '0px -11px'); 
    } else {
      dojo.connect(this.switcher, 'onmousedown', this, 'mousedown');
    }
    dojo.connect(this.input, 'onchange', this, 'setValue');
  },
  
  controller: function() {
    if(!this.mode) {
      if(this.anim) this.anim.stop();
      this.state ? this.setOff() : this.setOn();
    } else if(this.mode == "button") {
      this.state ? this.setBtnOff() : this.setBtnOn();
    }
  },
    
  setBtnOn: function() {
    dojo.style(this.img[0], 'backgroundPosition', '0px -11px');
    dojo.addClass(this.switcher, 'selected');
    this.changeState(1);
  },
  
  setBtnOff: function() {
    dojo.style(this.img[0], 'backgroundPosition', '0px 0px');
    dojo.removeClass(this.switcher, 'selected');
    this.changeState(0);
  },
  
  setValue: function() {
    if(this.state != this.input.value) {
      this.controller();
    }
  },
  
  changeState: function(state){
    if(this.state != state){
      this.state = state;
      this.stateChanged();
    }
  },  
  
  stateChanged: function(){
    this.input.value = this.state;
    this.fireEvent(this.input, 'change'); 
  },
  
  mousedown: function(e){
    this.startState = this.state;
    this.move = dojo.connect(document, 'onmousemove', this, 'mousemove');
    this.up = dojo.connect(document, 'onmouseup', this, 'mouseup');
    this.startX = e.clientX;
  },
  
  mousemove: function(e){
    var x = e.clientX-this.startX;
    if(!this.startState) x-=this.w;
    if(x > 0){
      x = 0;
      this.changeState(1);
    }
    if(x < -1*this.w){
      x = -1*this.w;
      this.changeState(0);
    }
		var str = x+"px 0px";
    dojo.style(this.switcher,"backgroundPosition",str);
  },
  
  mouseup: function(e){
    dojo.disconnect(this.move);
    dojo.disconnect(this.up);
  },

  fireEvent: function(element,event){
    if ((document.createEventObject && !dojo.isIE) || (document.createEventObject && dojo.isIE && dojo.isIE < 9)){
      var evt = document.createEventObject();
      return element.fireEvent('on'+event,evt);
    }else{
      var evt = document.createEvent("HTMLEvents");
      evt.initEvent(event, true, true );
      return !element.dispatchEvent(evt);
    }
  },
  
  getBgpos: function() {
    var pos = dojo.style(this.switcher, 'backgroundPosition');
    if(dojo.isIE <= 8){
      pos = dojo.style(this.switcher, 'backgroundPositionX')+' '+dojo.style(this.switcher, 'backgroundPositionY');
    }
    var bgp = pos.split(' ');
    bgp[0] = parseInt(bgp[0]);
    return !bgp[0] ? 0 : bgp[0];
  },
  
  setOn: function() {
    this.changeState(1);
    
    this.anim = new dojo.Animation({
      curve: new dojo._Line(this.getBgpos(),0),
      node: this.switcher,
      onAnimate: function(){
				var str = Math.floor(arguments[0])+"px 0px";
				dojo.style(this.node,"backgroundPosition",str);
			}
    }).play();
  },
  
  
  setOff: function() {
    this.changeState(0);
      
    this.anim = new dojo.Animation({
      curve: new dojo._Line(this.getBgpos(), -1*this.w),
      node: this.switcher,
      onAnimate: function(){
				var str = Math.floor(arguments[0])+"px 0px";
				dojo.style(this.node,"backgroundPosition",str);
			}
    }).play();
  }
  
});


dojo.declare("OfflajnGradient", null, {
	constructor: function(args) {
    dojo.mixin(this,args);
    this.init();
  },
  
  init: function() {
    this.start.alphaSupport=false; 
    this.startc = jQuery(this.start).jPicker({
      window:{
        expandable: true,
        alphaSupport: false
      }
    });
    
    this.end.alphaSupport=false; 
    this.endc = jQuery(this.end).jPicker({
      window:{
        expandable: true,
        alphaSupport: false
      }
    });
    
    if(dojo.isIE){
      dojo.style(this.start.parentNode.parentNode, 'zoom', '1');
    }
    //this.changeGradient();
    
    this.container = this.start.parentNode.parentNode.parentNode;
    if (!this.onoff) {
      this.container.style.marginLeft = 0;
      dojo.byId("offlajnonoff"+this.switcher.id).style.display = 'none';
    }
    
    this.hider = dojo.create("div", { "class": "gradient_hider" }, this.container, "last");
    
    dojo.style(this.hider, 'position', 'absolute');
    dojo.style(this.hider, "display", "none");
    
    if(!parseInt(this.switcher.value)){
      dojo.style(this.container, 'opacity', 0.15);
      dojo.style(this.hider, "display", "block");
    }
    this.changeValues();
  
    dojo.connect(this.switcher, 'onchange', this, 'onSwitch');
    dojo.connect(this.start, 'onchange', this, 'changeGradient');
    dojo.connect(this.end, 'onchange', this, 'changeGradient');
    dojo.connect(this.hidden, 'onchange', this, 'changeValues');
    this.onResize();
    dojo.connect(window, 'onresize', this, 'onResize');
  },
     
  onResize: function(){ 
    var j15 = 0;
    if(this.container.parentNode.tagName == 'TD') j15 = 1;
    var w = dojo.coords(j15 ? this.container.parentNode.parentNode:this.container.parentNode).w-30;
    var c = this.container.parentNode.children;
    for(var i = 0; i < c.length-1 && c[i] != this.container; i++){
      w-=dojo.marginBox(c[i]).w;
    }
    if(j15) w-=160;
    dojo.style(this.container, 'width', w+'px');
    dojo.style(this.hider, "width", w+"px");
  },
  
  onSwitch: function(){
    if(this.anim) this.anim.stop();
    if(parseInt(this.switcher.value)){
      this.anim = dojo.animateProperty({
        node: this.container,
        properties: {
            opacity : 1
        },
        onEnd : dojo.hitch(this,function() {
                  dojo.style(this.hider, "display", "none");
                })
      }).play();
    }else{
      this.anim = dojo.animateProperty({
        node: this.container,
        properties: {
            opacity : 0.15
        },
        onBegin : dojo.hitch(this,function() {
                  dojo.style(this.hider, "display", "block");
                })
      }).play();
    }
    this.changeGradient();
  },
  
  changeGradient: function() {
      if(dojo.isIE){
        dojo.style(this.start.parentNode.parentNode, 'filter', 'progid:DXImageTransform.Microsoft.Gradient(GradientType=1,StartColorStr=#'+this.start.value+',EndColorStr=#'+this.end.value+')');
      }else if (dojo.isFF ) {
        dojo.style(this.start.parentNode.parentNode, 'background', '-moz-linear-gradient( left, #'+this.start.value+', #'+this.end.value+')');
      } else if (dojo.isMozilla ) {
        dojo.style(this.start.parentNode.parentNode, 'background', '-moz-linear-gradient( left, #'+this.start.value+', #'+this.end.value+')');
      } else if (dojo.isOpera ) {
        dojo.style(this.start.parentNode.parentNode, 'background-image', '-o-linear-gradient(right, #'+this.start.value+', #'+this.end.value+')');
      } else {
        dojo.style(this.start.parentNode.parentNode, 'background', '-webkit-gradient( linear, left top, right top, from(#'+this.start.value+'), to(#'+this.end.value+'))');
      }
      this.hidden.value = this.switcher.value+'-'+this.start.value+'-'+this.end.value;
  },
  
  changeValues: function() {
    var val = this.hidden.value.split("-");
    //console.log(val);
    this.switcher.value = val[0];
    this.fireEvent(this.switcher, 'change');
    this.onSwitch();
    if(val[1] && val[2]) {
      this.start.value = val[1];
      this.startc[0].color.active.val('hex', val[1]);
      //this.fireEvent(this.start, 'change');
      this.end.value = val[2];
      this.endc[0].color.active.val('hex', val[2]);
      //this.fireEvent(this.end, 'change');
      this.changeGradient();
    }
  },
  
  fireEvent: function(element,event){
    if ((document.createEventObject && !dojo.isIE) || (document.createEventObject && dojo.isIE && dojo.isIE < 9)){
      var evt = document.createEventObject();
      return element.fireEvent('on'+event,evt);
    }else{
      var evt = document.createEvent("HTMLEvents");
      evt.initEvent(event, true, true );
      return !element.dispatchEvent(evt);
    }
  }
});
dojo.addOnLoad(function(){
      new OfflajnList({
        name: "jformparamsmoduleparametersTabthemethemeskin",
        elements: "<div class=\"content\"><div class=\"listelement\">Custom<\/div><div class=\"listelement\">Blue<\/div><div class=\"listelement\">Orange<\/div><div class=\"listelement\">Red<\/div><div class=\"listelement\">Green<\/div><div class=\"listelement\">Grey<\/div><div class=\"listelement\">Darkblue<\/div><div class=\"listelement\">Darkorange<\/div><div class=\"listelement\">Darkred<\/div><div class=\"listelement\">Darkgreen<\/div><\/div>",
        options: [{"value":"custom","text":"Custom"},{"value":"elegant_blue","text":"Blue"},{"value":"elegant_orange","text":"Orange"},{"value":"elegant_red","text":"Red"},{"value":"elegant_green","text":"Green"},{"value":"elegant_grey","text":"Grey"},{"value":"elegant_darkblue","text":"Darkblue"},{"value":"elegant_darkorange","text":"Darkorange"},{"value":"elegant_darkred","text":"Darkred"},{"value":"elegant_darkgreen","text":"Darkgreen"}],
        selectedIndex: 0,
        height: 0,
        fireshow: 0
      });
    

      window.themeskin = new OfflajnSkin({
        name: "themeskin",
        id: "jformparamsmoduleparametersTabthemethemeskin",
        data: {"elegant_blue":{"blackoutcomb":"40|*|\/modules\/mod_improved_ajax_login\/themes\/elegant\/images\/samples\/ptrn1.png","popupcomb":"f5f5f5|*|3|*|c4c4c4|*|7","titlefont":"{\"Text\":{\"color\":\"5d5c5c\"}}","btnfont":"{\"Text\":{\"color\":\"ffffff\", \"lineheight\":\"normal\"}}","btngrad":"1-309dff-1186bb","hovergrad":"1-297dc9-104f88","buttoncomb":"4|*|0d70cb|*|3","txtcomb":"eeeeee|*|ffffff","textfont":"{\"Text\":{\"color\":\"5d5c5c\"},\"Hover\":{\"color\":\"1186bb\"}}","errorgrad":"1-e0401d-b73016","errorcolor":"ffffff","hintgrad":"1-FFFFFF-F5F5F5","hintcolor":"5E5E5E"},"elegant_orange":{"blackoutcomb":"40|*|\/modules\/mod_improved_ajax_login\/themes\/elegant\/images\/samples\/ptrn1.png","popupcomb":"f5f5f5|*|3|*|c4c4c4|*|7","titlefont":"{\"Text\":{\"color\":\"5d5c5c\"}}","btnfont":"{\"Text\":{\"color\":\"ffffff\", \"lineheight\":\"normal\"}}","btngrad":"1-ef8c00-e05a00","hovergrad":"1-e27b2f-cf4e1e","buttoncomb":"4|*|b24400|*|3","txtcomb":"eeeeee|*|ffffff","textfont":"{\"Text\":{\"color\":\"5d5c5c\"},\"Hover\":{\"color\":\"e05a00\"}}","errorgrad":"1-e0401d-b73016","errorcolor":"ffffff","hintgrad":"1-FFFFFF-F5F5F5","hintcolor":"5E5E5E"},"elegant_red":{"blackoutcomb":"40|*|\/modules\/mod_improved_ajax_login\/themes\/elegant\/images\/samples\/ptrn1.png","popupcomb":"f5f5f5|*|3|*|c4c4c4|*|7","titlefont":"{\"Text\":{\"color\":\"5d5c5c\"}}","btnfont":"{\"Text\":{\"color\":\"ffffff\", \"lineheight\":\"normal\"}}","btngrad":"1-d44f47-aa2b0d","hovergrad":"1-b6443d-87220a","buttoncomb":"4|*|87220a|*|3","txtcomb":"eeeeee|*|ffffff","textfont":"{\"Text\":{\"color\":\"5d5c5c\"},\"Hover\":{\"color\":\"aa2b0d\"}}","errorgrad":"1-e0401d-b73016","errorcolor":"ffffff","hintgrad":"1-FFFFFF-F5F5F5","hintcolor":"5E5E5E"},"elegant_green":{"blackoutcomb":"40|*|\/modules\/mod_improved_ajax_login\/themes\/elegant\/images\/samples\/ptrn1.png","popupcomb":"f5f5f5|*|3|*|c4c4c4|*|7","titlefont":"{\"Text\":{\"color\":\"5d5c5c\"}}","btnfont":"{\"Text\":{\"color\":\"ffffff\", \"lineheight\":\"normal\"}}","btngrad":"1-86bc35-559222","hovergrad":"1-729e31-3a611a","buttoncomb":"4|*|3a611a|*|3","txtcomb":"eeeeee|*|ffffff","textfont":"{\"Text\":{\"color\":\"5d5c5c\"},\"Hover\":{\"color\":\"559222\"}}","errorgrad":"1-e0401d-b73016","errorcolor":"ffffff","hintgrad":"1-FFFFFF-F5F5F5","hintcolor":"5E5E5E"},"elegant_grey":{"blackoutcomb":"40|*|\/modules\/mod_improved_ajax_login\/themes\/elegant\/images\/samples\/ptrn1.png","popupcomb":"f5f5f5|*|3|*|c4c4c4|*|7","titlefont":"{\"Text\":{\"color\":\"5d5c5c\"}}","btnfont":"{\"Text\":{\"color\":\"ffffff\", \"lineheight\":\"normal\"}}","btngrad":"1-8b8b8b-5e5e5e","hovergrad":"1-646464-3b3b3b","buttoncomb":"4|*|3b3b3b|*|3","txtcomb":"eeeeee|*|ffffff","textfont":"{\"Text\":{\"color\":\"5d5c5c\"},\"Hover\":{\"color\":\"5e5e5e\"}}","errorgrad":"1-e0401d-b73016","errorcolor":"ffffff","hintgrad":"1-FFFFFF-F5F5F5","hintcolor":"5E5E5E"},"elegant_darkblue":{"blackoutcomb":"60|*|\/modules\/mod_improved_ajax_login\/themes\/elegant\/images\/samples\/ptrn6.png","popupcomb":"282828|*|3|*|474747|*|7","titlefont":"{\"Text\":{\"color\":\"7e7e7e\"}}","btnfont":"{\"Text\":{\"color\":\"ffffff\", \"lineheight\":\"normal\"}}","btngrad":"1-309dff-0d70cb","hovergrad":"1-2684d6-0a65af","buttoncomb":"4|*|0d5f95|*|3","txtcomb":"f4f4f4|*|f4f4f4","textfont":"{\"Text\":{\"color\":\"7e7e7e\"},\"Hover\":{\"color\":\"0d70cb\"}}","errorgrad":"1-e0401d-b73016","errorcolor":"ffffff","hintgrad":"1-f4f4f4-e4e4e4","hintcolor":"474747"},"elegant_darkorange":{"blackoutcomb":"60|*|\/modules\/mod_improved_ajax_login\/themes\/elegant\/images\/samples\/ptrn6.png","popupcomb":"282828|*|3|*|474747|*|7","titlefont":"{\"Text\":{\"color\":\"7e7e7e\"}}","btnfont":"{\"Text\":{\"color\":\"ffffff\", \"lineheight\":\"normal\"}}","btngrad":"1-e27b2f-cf4e1e","hovergrad":"1-e0650d-cc3702","buttoncomb":"4|*|e04800|*|3","txtcomb":"f4f4f4|*|f4f4f4","textfont":"{\"Text\":{\"color\":\"7e7e7e\"},\"Hover\":{\"color\":\"cf4e1e\"}}","errorgrad":"1-e0401d-b73016","errorcolor":"ffffff","hintgrad":"1-f4f4f4-e4e4e4","hintcolor":"474747"},"elegant_darkred":{"blackoutcomb":"60|*|\/modules\/mod_improved_ajax_login\/themes\/elegant\/images\/samples\/ptrn6.png","popupcomb":"282828|*|3|*|474747|*|7","titlefont":"{\"Text\":{\"color\":\"7e7e7e\"}}","btnfont":"{\"Text\":{\"color\":\"ffffff\", \"lineheight\":\"normal\"}}","btngrad":"1-d44f47-aa2b0d","hovergrad":"1-ce1610-961900","buttoncomb":"4|*|7f1d07|*|3","txtcomb":"f4f4f4|*|f4f4f4","textfont":"{\"Text\":{\"color\":\"7e7e7e\"},\"Hover\":{\"color\":\"aa2b0d\"}}","errorgrad":"1-e0401d-b73016","errorcolor":"ffffff","hintgrad":"1-f4f4f4-e4e4e4","hintcolor":"474747"},"elegant_darkgreen":{"blackoutcomb":"60|*|\/modules\/mod_improved_ajax_login\/themes\/elegant\/images\/samples\/ptrn6.png","popupcomb":"282828|*|3|*|474747|*|7","titlefont":"{\"Text\":{\"color\":\"7e7e7e\"}}","btnfont":"{\"Text\":{\"color\":\"ffffff\", \"lineheight\":\"normal\"}}","btngrad":"1-77ba01-569801","hovergrad":"1-5e9100-3f7000","buttoncomb":"4|*|3f7000|*|3","txtcomb":"f4f4f4|*|f4f4f4","textfont":"{\"Text\":{\"color\":\"7e7e7e\"},\"Hover\":{\"color\":\"569801\"}}","errorgrad":"1-e0401d-b73016","errorcolor":"ffffff","hintgrad":"1-f4f4f4-e4e4e4","hintcolor":"474747"}},
        control: "jform[params][moduleparametersTab][theme]",
        dependency: ''
      });
    

      new OfflajnList({
        name: "jformparamsmoduleparametersTabthemefontskin",
        elements: "<div class=\"content\"><div class=\"listelement\">Custom<\/div><div class=\"listelement\">Helvetica<\/div><div class=\"listelement\">Arial<\/div><div class=\"listelement\">Arimo<\/div><div class=\"listelement\">Cabin<\/div><div class=\"listelement\">Carme<\/div><div class=\"listelement\">Magra<\/div><div class=\"listelement\">Mako<\/div><div class=\"listelement\">Opensans<\/div><div class=\"listelement\">Ptsans<\/div><div class=\"listelement\">Rosario<\/div><div class=\"listelement\">Shanti<\/div><div class=\"listelement\">Viga<\/div><\/div>",
        options: [{"value":"custom","text":"Custom"},{"value":"elegant_helvetica","text":"Helvetica"},{"value":"elegant_arial","text":"Arial"},{"value":"elegant_arimo","text":"Arimo"},{"value":"elegant_cabin","text":"Cabin"},{"value":"elegant_carme","text":"Carme"},{"value":"elegant_magra","text":"Magra"},{"value":"elegant_mako","text":"Mako"},{"value":"elegant_opensans","text":"Opensans"},{"value":"elegant_ptsans","text":"Ptsans"},{"value":"elegant_rosario","text":"Rosario"},{"value":"elegant_shanti","text":"Shanti"},{"value":"elegant_viga","text":"Viga"}],
        selectedIndex: 0,
        height: 10,
        fireshow: 0
      });
    

      window.fontskin = new OfflajnSkin({
        name: "fontskin",
        id: "jformparamsmoduleparametersTabthemefontskin",
        data: {"elegant_helvetica":{"titlefont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"0\",\"subset\":\"\",\"family\":\"\",\"size\":\"14||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","textfont":"{\"Text\":{\"lineheight\":\"normal\",\"underline\":\"0\",\"type\":\"0\",\"subset\":\"\",\"family\":\"\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","btnfont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"0\",\"subset\":\"\",\"family\":\"\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"1||px|*|1||px|*|0|*|000000b3|*|1\"}}","smalltext":"11"},"elegant_arial":{"titlefont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"0\",\"subset\":\"\",\"family\":\"\",\"size\":\"14||px\",\"afont\":\"Arial||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","textfont":"{\"Text\":{\"lineheight\":\"normal\",\"underline\":\"0\",\"type\":\"0\",\"subset\":\"\",\"family\":\"\",\"size\":\"12||px\",\"afont\":\"Arial||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","btnfont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"0\",\"subset\":\"\",\"family\":\"\",\"size\":\"12||px\",\"afont\":\"Arial||1\",\"bold\":\"0\",\"tshadow\":\"1||px|*|1||px|*|0|*|000000b3|*|1\"}}","smalltext":"11"},"elegant_arimo":{"titlefont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Arimo\",\"size\":\"15||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","textfont":"{\"Text\":{\"lineheight\":\"normal\",\"underline\":\"0\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Arimo\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","btnfont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Arimo\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"1||px|*|1||px|*|0|*|000000b3|*|1|*|\"}}","smalltext":"11"},"elegant_cabin":{"titlefont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Cabin\",\"size\":\"15||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","textfont":"{\"Text\":{\"lineheight\":\"normal\",\"underline\":\"0\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Cabin\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","btnfont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Cabin\",\"size\":\"13||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"1||px|*|1||px|*|0|*|000000b3|*|1\"}}","smalltext":"11"},"elegant_carme":{"titlefont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Carme\",\"size\":\"15||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","textfont":"{\"Text\":{\"lineheight\":\"normal\",\"underline\":\"0\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Carme\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","btnfont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Carme\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"1||px|*|1||px|*|0|*|000000b3|*|1\"}}","smalltext":"11"},"elegant_magra":{"titlefont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Magra\",\"size\":\"15||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","textfont":"{\"Text\":{\"lineheight\":\"normal\",\"underline\":\"0\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Magra\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","btnfont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Magra\",\"size\":\"13||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"1||px|*|1||px|*|0|*|000000b3|*|1\"}}","smalltext":"11"},"elegant_mako":{"titlefont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Mako\",\"size\":\"14||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","textfont":"{\"Text\":{\"lineheight\":\"normal\",\"underline\":\"0\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Mako\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","btnfont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Mako\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"1||px|*|1||px|*|0|*|000000b3|*|1|*|\"}}","smalltext":"11"},"elegant_opensans":{"titlefont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Open Sans\",\"size\":\"14||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","textfont":"{\"Text\":{\"lineheight\":\"normal\",\"underline\":\"0\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":Open Sans\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","btnfont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Open Sans\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"1||px|*|1||px|*|0|*|000000b3|*|1\"}}","smalltext":"11"},"elegant_ptsans":{"titlefont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"PT Sans\",\"size\":\"14||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","textfont":"{\"Text\":{\"lineheight\":\"normal\",\"underline\":\"0\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"PT Sans\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","btnfont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"PT Sans\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"1||px|*|1||px|*|0|*|000000b3|*|1|*|\"}}","smalltext":"11"},"elegant_rosario":{"titlefont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Rosario\",\"size\":\"14||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","textfont":"{\"Text\":{\"lineheight\":\"normal\",\"underline\":\"0\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Rosario\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","btnfont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Rosario\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"1||px|*|1||px|*|0|*|000000b3|*|1\"}}","smalltext":"11"},"elegant_shanti":{"titlefont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Shanti\",\"size\":\"14||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","textfont":"{\"Text\":{\"lineheight\":\"normal\",\"underline\":\"0\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Shanti\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","btnfont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Shanti\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"1||px|*|1||px|*|0|*|000000b3|*|1\"}}","smalltext":"11"},"elegant_viga":{"titlefont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Viga\",\"size\":\"14||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","textfont":"{\"Text\":{\"lineheight\":\"normal\",\"underline\":\"0\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Viga\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"0|*|0|*|0|*|000000b3|*|0\"}}","btnfont":"{\"Text\":{\"lineheight\":\"normal\",\"type\":\"Latin\",\"subset\":\"Latin\",\"family\":\"Viga\",\"size\":\"12||px\",\"afont\":\"Helvetica||1\",\"bold\":\"0\",\"tshadow\":\"1||px|*|1||px|*|0|*|000000b3|*|1\"}}","smalltext":"11"}},
        control: "jform[params][moduleparametersTab][theme]",
        dependency: ''
      });
    

      new OfflajnText({
        id: "jformparamsmoduleparametersTabthemeblackoutcomb0",
        validation: "int",
        attachunit: "%",
        mode: "",
        scale: "",
        minus: 0,
        onoff: ""
      }); 
    

        new OfflajnImagemanager({
          id: "jformparamsmoduleparametersTabthemeblackoutcomb1",
          folder: "/modules/mod_improved_ajax_login/themes/elegant/images/samples/",
          root: "",
          uploadurl: "index.php?option=offlajnupload",
          imgs: ["ptrn1.png","ptrn2.png","ptrn3.png","ptrn4.png","ptrn5.png","ptrn6.png"],
          active: "/modules/mod_improved_ajax_login/themes/elegant/images/samples/ptrn1.png",
          identifier: "fa4c4e3d46e3600898f64b3e5f9b5927",
          description: "",
          siteurl: "http://emundus.local/"
        });
    

      new OfflajnCombine({
        id: "jformparamsmoduleparametersTabthemeblackoutcomb",
        num: 2,
        switcherid: "",
        hideafter: "0"
      }); 
    

    var el = dojo.byId("jformparamsmoduleparametersTabthemepopupcomb0");
    jQuery.fn.jPicker.defaults.images.clientPath="/modules/mod_improved_ajax_login/params/offlajndashboard/../offlajncolor/offlajncolor/jpicker/images/";
    el.alphaSupport=false; 
    el.c = jQuery("#jformparamsmoduleparametersTabthemepopupcomb0").jPicker({
        window:{
          expandable: true,
          alphaSupport: false}
        });
    dojo.connect(el, "change", function(){
      this.c[0].color.active.val("hex", this.value);
    });
    

      new OfflajnText({
        id: "jformparamsmoduleparametersTabthemepopupcomb1",
        validation: "int",
        attachunit: "px",
        mode: "",
        scale: "",
        minus: 0,
        onoff: ""
      }); 
    

    var el = dojo.byId("jformparamsmoduleparametersTabthemepopupcomb2");
    jQuery.fn.jPicker.defaults.images.clientPath="/modules/mod_improved_ajax_login/params/offlajndashboard/../offlajncolor/offlajncolor/jpicker/images/";
    el.alphaSupport=false; 
    el.c = jQuery("#jformparamsmoduleparametersTabthemepopupcomb2").jPicker({
        window:{
          expandable: true,
          alphaSupport: false}
        });
    dojo.connect(el, "change", function(){
      this.c[0].color.active.val("hex", this.value);
    });
    

      new OfflajnText({
        id: "jformparamsmoduleparametersTabthemepopupcomb3",
        validation: "int",
        attachunit: "px",
        mode: "",
        scale: "",
        minus: 0,
        onoff: ""
      }); 
    

      new OfflajnCombine({
        id: "jformparamsmoduleparametersTabthemepopupcomb",
        num: 4,
        switcherid: "",
        hideafter: "2"
      }); 
    

        new FontConfigurator({
          id: "jformparamsmoduleparametersTabthemetitlefont",
          defaultTab: "Text",
          origsettings: {"Text":{"lineheight":"normal","type":"Latin","subset":"Latin","family":"Carme","color":"5d5c5c","lineheight":"21px","size":"14||px","align":"left","afont":"Helvetica||1","bold":"0"}},
          elements: {"tab":{"name":"jform[params][moduleparametersTab][theme][titlefont]tab","id":"jformparamsmoduleparametersTabthemetitlefonttab","html":"<div class=\"offlajnradiocontainerbutton\" id=\"offlajnradiocontainerjformparamsmoduleparametersTabthemetitlefonttab\"><div class=\"radioelement first last selected\">Text<\/div><div class=\"clear\"><\/div><\/div><input type=\"hidden\" id=\"jformparamsmoduleparametersTabthemetitlefonttab\" name=\"jform[params][moduleparametersTab][theme][titlefont]tab\" value=\"Text\"\/>"},"type":{"name":"jform[params][moduleparametersTab][theme][titlefont]type","id":"jformparamsmoduleparametersTabthemetitlefonttype","Cyrillic":{"name":"jform[params][moduleparametersTab][theme][titlefont]family","id":"jformparamsmoduleparametersTabthemetitlefontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetitlefontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Andika<br \/>Andika<br \/>Anonymous Pro<br \/>Cuprum<br \/>Didact Gothic<br \/>EB Garamond<br \/>Istok Web<br \/>Jura<br \/>Forum<br \/>Kelly Slab<br \/>Lobster<br \/>Neucha<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Philosopher<br \/>Play<br \/>PT Sans<br \/>PT Sans Caption<br \/>PT Sans Narrow<br \/>PT Serif<br \/>PT Serif Caption<br \/>Ruslan Display<br \/>Tenor Sans<br \/>Ubuntu<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]family\" id=\"jformparamsmoduleparametersTabthemetitlefontfamily\" value=\"Andika\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetitlefontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Andika<\\\/div><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Cuprum<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">EB Garamond<\\\/div><div class=\\\"listelement\\\">Istok Web<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">Forum<\\\/div><div class=\\\"listelement\\\">Kelly Slab<\\\/div><div class=\\\"listelement\\\">Lobster<\\\/div><div class=\\\"listelement\\\">Neucha<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Philosopher<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">PT Sans<\\\/div><div class=\\\"listelement\\\">PT Sans Caption<\\\/div><div class=\\\"listelement\\\">PT Sans Narrow<\\\/div><div class=\\\"listelement\\\">PT Serif<\\\/div><div class=\\\"listelement\\\">PT Serif Caption<\\\/div><div class=\\\"listelement\\\">Ruslan Display<\\\/div><div class=\\\"listelement\\\">Tenor Sans<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Andika\",\"text\":\"Andika\"},{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Cuprum\",\"text\":\"Cuprum\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"EB Garamond\",\"text\":\"EB Garamond\"},{\"value\":\"Istok Web\",\"text\":\"Istok Web\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"Forum\",\"text\":\"Forum\"},{\"value\":\"Kelly Slab\",\"text\":\"Kelly Slab\"},{\"value\":\"Lobster\",\"text\":\"Lobster\"},{\"value\":\"Neucha\",\"text\":\"Neucha\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Philosopher\",\"text\":\"Philosopher\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"PT Sans\",\"text\":\"PT Sans\"},{\"value\":\"PT Sans Caption\",\"text\":\"PT Sans Caption\"},{\"value\":\"PT Sans Narrow\",\"text\":\"PT Sans Narrow\"},{\"value\":\"PT Serif\",\"text\":\"PT Serif\"},{\"value\":\"PT Serif Caption\",\"text\":\"PT Serif Caption\"},{\"value\":\"Ruslan Display\",\"text\":\"Ruslan Display\"},{\"value\":\"Tenor Sans\",\"text\":\"Tenor Sans\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"CyrillicExtended":{"name":"jform[params][moduleparametersTab][theme][titlefont]family","id":"jformparamsmoduleparametersTabthemetitlefontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetitlefontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Andika<br \/>Andika<br \/>Anonymous Pro<br \/>Didact Gothic<br \/>EB Garamond<br \/>Istok Web<br \/>Jura<br \/>Forum<br \/>Lobster<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Play<br \/>Ruslan Display<br \/>Tenor Sans<br \/>Ubuntu<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]family\" id=\"jformparamsmoduleparametersTabthemetitlefontfamily\" value=\"Andika\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetitlefontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Andika<\\\/div><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">EB Garamond<\\\/div><div class=\\\"listelement\\\">Istok Web<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">Forum<\\\/div><div class=\\\"listelement\\\">Lobster<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">Ruslan Display<\\\/div><div class=\\\"listelement\\\">Tenor Sans<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Andika\",\"text\":\"Andika\"},{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"EB Garamond\",\"text\":\"EB Garamond\"},{\"value\":\"Istok Web\",\"text\":\"Istok Web\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"Forum\",\"text\":\"Forum\"},{\"value\":\"Lobster\",\"text\":\"Lobster\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"Ruslan Display\",\"text\":\"Ruslan Display\"},{\"value\":\"Tenor Sans\",\"text\":\"Tenor Sans\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"Greek":{"name":"jform[params][moduleparametersTab][theme][titlefont]family","id":"jformparamsmoduleparametersTabthemetitlefontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetitlefontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Anonymous Pro<br \/>Anonymous Pro<br \/>Caudex<br \/>Didact Gothic<br \/>Jura<br \/>GFS Didot<br \/>GFS Neohellenic<br \/>Nova Mono<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Play<br \/>Ubuntu<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]family\" id=\"jformparamsmoduleparametersTabthemetitlefontfamily\" value=\"Anonymous Pro\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetitlefontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Caudex<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">GFS Didot<\\\/div><div class=\\\"listelement\\\">GFS Neohellenic<\\\/div><div class=\\\"listelement\\\">Nova Mono<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Caudex\",\"text\":\"Caudex\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"GFS Didot\",\"text\":\"GFS Didot\"},{\"value\":\"GFS Neohellenic\",\"text\":\"GFS Neohellenic\"},{\"value\":\"Nova Mono\",\"text\":\"Nova Mono\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"GreekExtended":{"name":"jform[params][moduleparametersTab][theme][titlefont]family","id":"jformparamsmoduleparametersTabthemetitlefontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetitlefontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Anonymous Pro<br \/>Anonymous Pro<br \/>Caudex<br \/>Didact Gothic<br \/>Jura<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Play<br \/>Ubuntu<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]family\" id=\"jformparamsmoduleparametersTabthemetitlefontfamily\" value=\"Anonymous Pro\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetitlefontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Caudex<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Caudex\",\"text\":\"Caudex\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"Khmer":{"name":"jform[params][moduleparametersTab][theme][titlefont]family","id":"jformparamsmoduleparametersTabthemetitlefontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetitlefontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Angkor<br \/>Angkor<br \/>Battambang<br \/>Bayon<br \/>Bokor<br \/>Chenla<br \/>Content<br \/>Dangrek<br \/>Freehand<br \/>Hanuman<br \/>Khmer<br \/>Koulen<br \/>Metal<br \/>Moul<br \/>Moulpali<br \/>Odor Mean Chey<br \/>Preahvihear<br \/>Siemreap<br \/>Suwannaphum<br \/>Taprom<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]family\" id=\"jformparamsmoduleparametersTabthemetitlefontfamily\" value=\"Angkor\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetitlefontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Angkor<\\\/div><div class=\\\"listelement\\\">Battambang<\\\/div><div class=\\\"listelement\\\">Bayon<\\\/div><div class=\\\"listelement\\\">Bokor<\\\/div><div class=\\\"listelement\\\">Chenla<\\\/div><div class=\\\"listelement\\\">Content<\\\/div><div class=\\\"listelement\\\">Dangrek<\\\/div><div class=\\\"listelement\\\">Freehand<\\\/div><div class=\\\"listelement\\\">Hanuman<\\\/div><div class=\\\"listelement\\\">Khmer<\\\/div><div class=\\\"listelement\\\">Koulen<\\\/div><div class=\\\"listelement\\\">Metal<\\\/div><div class=\\\"listelement\\\">Moul<\\\/div><div class=\\\"listelement\\\">Moulpali<\\\/div><div class=\\\"listelement\\\">Odor Mean Chey<\\\/div><div class=\\\"listelement\\\">Preahvihear<\\\/div><div class=\\\"listelement\\\">Siemreap<\\\/div><div class=\\\"listelement\\\">Suwannaphum<\\\/div><div class=\\\"listelement\\\">Taprom<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Angkor\",\"text\":\"Angkor\"},{\"value\":\"Battambang\",\"text\":\"Battambang\"},{\"value\":\"Bayon\",\"text\":\"Bayon\"},{\"value\":\"Bokor\",\"text\":\"Bokor\"},{\"value\":\"Chenla\",\"text\":\"Chenla\"},{\"value\":\"Content\",\"text\":\"Content\"},{\"value\":\"Dangrek\",\"text\":\"Dangrek\"},{\"value\":\"Freehand\",\"text\":\"Freehand\"},{\"value\":\"Hanuman\",\"text\":\"Hanuman\"},{\"value\":\"Khmer\",\"text\":\"Khmer\"},{\"value\":\"Koulen\",\"text\":\"Koulen\"},{\"value\":\"Metal\",\"text\":\"Metal\"},{\"value\":\"Moul\",\"text\":\"Moul\"},{\"value\":\"Moulpali\",\"text\":\"Moulpali\"},{\"value\":\"Odor Mean Chey\",\"text\":\"Odor Mean Chey\"},{\"value\":\"Preahvihear\",\"text\":\"Preahvihear\"},{\"value\":\"Siemreap\",\"text\":\"Siemreap\"},{\"value\":\"Suwannaphum\",\"text\":\"Suwannaphum\"},{\"value\":\"Taprom\",\"text\":\"Taprom\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"Latin":{"name":"jform[params][moduleparametersTab][theme][titlefont]family","id":"jformparamsmoduleparametersTabthemetitlefontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetitlefontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Carme<br \/>Abel<br \/>Abril Fatface<br \/>Aclonica<br \/>Actor<br \/>Aldrich<br \/>Alice<br \/>Alike<br \/>Allan<br \/>Allerta<br \/>Allerta Stencil<br \/>Amaranth<br \/>Andika<br \/>Annie Use Your Telescope<br \/>Anonymous Pro<br \/>Antic<br \/>Anton<br \/>Architects Daughter<br \/>Arimo<br \/>Artifika<br \/>Arvo<br \/>Asap<br \/>Asul<br \/>Asset<br \/>Astloch<br \/>Aubrey<br \/>Bangers<br \/>Bentham<br \/>Bevan<br \/>Bigshot One<br \/>Black Ops One<br \/>Bowlby One<br \/>Bowlby One SC<br \/>Brawler<br \/>Buda<br \/>Cabin<br \/>Cabin Sketch<br \/>Calligraffitti<br \/>Candal<br \/>Cantarell<br \/>Cardo<br \/>Carme<br \/>Carter One<br \/>Caudex<br \/>Cedarville Cursive<br \/>Cherry Cream Soda<br \/>Chewy<br \/>Chivo<br \/>Coda<br \/>Coda Caption<br \/>Comfortaa<br \/>Coming Soon<br \/>Convergence<br \/>Copse<br \/>Corben<br \/>Cousine<br \/>Coustard<br \/>Covered By Your Grace<br \/>Crafty Girls<br \/>Crimson Text<br \/>Crushed<br \/>Cuprum<br \/>Damion<br \/>Dancing Script<br \/>Dawning of a New Day<br \/>Days One<br \/>Delius<br \/>Delius Swash Caps<br \/>Delius Unicase<br \/>Didact Gothic<br \/>Dorsa<br \/>Droid Sans<br \/>Droid Sans Mono<br \/>Droid Serif<br \/>EB Garamond<br \/>Exo<br \/>Expletus Sans<br \/>Fanwood Text<br \/>Federo<br \/>Fontdiner Swanky<br \/>Forum<br \/>Francois One<br \/>Gentium Basic<br \/>Gentium Book Basic<br \/>Geo<br \/>Geostar<br \/>Geostar Fill<br \/>Give You Glory<br \/>Gloria Hallelujah<br \/>Goblin One<br \/>Goudy Bookletter 1911<br \/>Gravitas One<br \/>Gruppo<br \/>Hammersmith One<br \/>Holtwood One SC<br \/>Homemade Apple<br \/>IM Fell DW Pica<br \/>IM Fell DW Pica SC<br \/>IM Fell Double Pica<br \/>IM Fell Double Pica SC<br \/>IM Fell English<br \/>IM Fell English SC<br \/>IM Fell French Canon<br \/>IM Fell French Canon SC<br \/>IM Fell Great Primer<br \/>IM Fell Great Primer SC<br \/>Inconsolata<br \/>Inder<br \/>Indie Flower<br \/>Irish Grover<br \/>Istok Web<br \/>Josefin Sans<br \/>Josefin Slab<br \/>Judson<br \/>Jura<br \/>Just Another Hand<br \/>Just Me Again Down Here<br \/>Kameron<br \/>Kelly Slab<br \/>Kenia<br \/>Kranky<br \/>Kreon<br \/>Kristi<br \/>La Belle Aurore<br \/>Lato<br \/>League Script<br \/>Leckerli One<br \/>Lekton<br \/>Limelight<br \/>Lobster<br \/>Lobster Two<br \/>Lora<br \/>Love Ya Like A Sister<br \/>Loved by the King<br \/>Luckiest Guy<br \/>Magra<br \/>Maiden Orange<br \/>Mako<br \/>Marvel<br \/>Maven Pro<br \/>Meddon<br \/>MedievalSharp<br \/>Megrim<br \/>Merriweather<br \/>Metrophobic<br \/>Michroma<br \/>Miltonian<br \/>Miltonian Tattoo<br \/>Modern Antiqua<br \/>Molengo<br \/>Monofett<br \/>Monoton<br \/>Montez<br \/>Mountains of Christmas<br \/>Muli<br \/>Neucha<br \/>Neuton<br \/>News Cycle<br \/>Nixie One<br \/>Nobile<br \/>Nothing You Could Do<br \/>Nova Cut<br \/>Nova Flat<br \/>Nova Mono<br \/>Nova Oval<br \/>Nova Round<br \/>Nova Script<br \/>Nova Slim<br \/>Nova Square<br \/>Numans<br \/>Nunito<br \/>OFL Sorts Mill Goudy TT<br \/>Old Standard TT<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Orbitron<br \/>Oswald<br \/>Over the Rainbow<br \/>Ovo<br \/>PT Sans<br \/>PT Sans Caption<br \/>PT Sans Narrow<br \/>PT Serif<br \/>PT Serif Caption<br \/>Pacifico<br \/>Passero One<br \/>Patrick Hand<br \/>Paytone One<br \/>Permanent Marker<br \/>Philosopher<br \/>Play<br \/>Playfair Display<br \/>Podkova<br \/>Pompiere<br \/>Prociono<br \/>Puritan<br \/>Quattrocento<br \/>Quattrocento Sans<br \/>Questrial<br \/>Quicksand<br \/>Radley<br \/>Raleway<br \/>Rationale<br \/>Redressed<br \/>Reenie Beanie<br \/>Rochester<br \/>Rock Salt<br \/>Rokkitt<br \/>Ropa Sans<br \/>Rosario<br \/>Ruslan Display<br \/>Schoolbell<br \/>Shadows Into Light<br \/>Shanti<br \/>Short Stack<br \/>Sigmar One<br \/>Signika<br \/>Signika Negative<br \/>Six Caps<br \/>Slackey<br \/>Smokum<br \/>Smythe<br \/>Sniglet<br \/>Snippet<br \/>Special Elite<br \/>Stardos Stencil<br \/>Sue Ellen Francisco<br \/>Sunshiney<br \/>Swanky and Moo Moo<br \/>Syncopate<br \/>Tangerine<br \/>Telex<br \/>Tenor Sans<br \/>Terminal Dosis Light<br \/>The Girl Next Door<br \/>Tienne<br \/>Tinos<br \/>Tulpen One<br \/>Ubuntu<br \/>Ultra<br \/>UnifrakturCook<br \/>UnifrakturMaguntia<br \/>Unkempt<br \/>Unna<br \/>VT323<br \/>Varela<br \/>Varela Round<br \/>Vibur<br \/>Viga<br \/>Vidaloka<br \/>Volkhov<br \/>Vollkorn<br \/>Voltaire<br \/>Waiting for the Sunrise<br \/>Wallpoet<br \/>Walter Turncoat<br \/>Wire One<br \/>Yanone Kaffeesatz<br \/>Yellowtail<br \/>Yeseva One<br \/>Zeyada<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]family\" id=\"jformparamsmoduleparametersTabthemetitlefontfamily\" value=\"Carme\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetitlefontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Abel<\\\/div><div class=\\\"listelement\\\">Abril Fatface<\\\/div><div class=\\\"listelement\\\">Aclonica<\\\/div><div class=\\\"listelement\\\">Actor<\\\/div><div class=\\\"listelement\\\">Aldrich<\\\/div><div class=\\\"listelement\\\">Alice<\\\/div><div class=\\\"listelement\\\">Alike<\\\/div><div class=\\\"listelement\\\">Allan<\\\/div><div class=\\\"listelement\\\">Allerta<\\\/div><div class=\\\"listelement\\\">Allerta Stencil<\\\/div><div class=\\\"listelement\\\">Amaranth<\\\/div><div class=\\\"listelement\\\">Andika<\\\/div><div class=\\\"listelement\\\">Annie Use Your Telescope<\\\/div><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Antic<\\\/div><div class=\\\"listelement\\\">Anton<\\\/div><div class=\\\"listelement\\\">Architects Daughter<\\\/div><div class=\\\"listelement\\\">Arimo<\\\/div><div class=\\\"listelement\\\">Artifika<\\\/div><div class=\\\"listelement\\\">Arvo<\\\/div><div class=\\\"listelement\\\">Asap<\\\/div><div class=\\\"listelement\\\">Asul<\\\/div><div class=\\\"listelement\\\">Asset<\\\/div><div class=\\\"listelement\\\">Astloch<\\\/div><div class=\\\"listelement\\\">Aubrey<\\\/div><div class=\\\"listelement\\\">Bangers<\\\/div><div class=\\\"listelement\\\">Bentham<\\\/div><div class=\\\"listelement\\\">Bevan<\\\/div><div class=\\\"listelement\\\">Bigshot One<\\\/div><div class=\\\"listelement\\\">Black Ops One<\\\/div><div class=\\\"listelement\\\">Bowlby One<\\\/div><div class=\\\"listelement\\\">Bowlby One SC<\\\/div><div class=\\\"listelement\\\">Brawler<\\\/div><div class=\\\"listelement\\\">Buda<\\\/div><div class=\\\"listelement\\\">Cabin<\\\/div><div class=\\\"listelement\\\">Cabin Sketch<\\\/div><div class=\\\"listelement\\\">Calligraffitti<\\\/div><div class=\\\"listelement\\\">Candal<\\\/div><div class=\\\"listelement\\\">Cantarell<\\\/div><div class=\\\"listelement\\\">Cardo<\\\/div><div class=\\\"listelement\\\">Carme<\\\/div><div class=\\\"listelement\\\">Carter One<\\\/div><div class=\\\"listelement\\\">Caudex<\\\/div><div class=\\\"listelement\\\">Cedarville Cursive<\\\/div><div class=\\\"listelement\\\">Cherry Cream Soda<\\\/div><div class=\\\"listelement\\\">Chewy<\\\/div><div class=\\\"listelement\\\">Chivo<\\\/div><div class=\\\"listelement\\\">Coda<\\\/div><div class=\\\"listelement\\\">Coda Caption<\\\/div><div class=\\\"listelement\\\">Comfortaa<\\\/div><div class=\\\"listelement\\\">Coming Soon<\\\/div><div class=\\\"listelement\\\">Convergence<\\\/div><div class=\\\"listelement\\\">Copse<\\\/div><div class=\\\"listelement\\\">Corben<\\\/div><div class=\\\"listelement\\\">Cousine<\\\/div><div class=\\\"listelement\\\">Coustard<\\\/div><div class=\\\"listelement\\\">Covered By Your Grace<\\\/div><div class=\\\"listelement\\\">Crafty Girls<\\\/div><div class=\\\"listelement\\\">Crimson Text<\\\/div><div class=\\\"listelement\\\">Crushed<\\\/div><div class=\\\"listelement\\\">Cuprum<\\\/div><div class=\\\"listelement\\\">Damion<\\\/div><div class=\\\"listelement\\\">Dancing Script<\\\/div><div class=\\\"listelement\\\">Dawning of a New Day<\\\/div><div class=\\\"listelement\\\">Days One<\\\/div><div class=\\\"listelement\\\">Delius<\\\/div><div class=\\\"listelement\\\">Delius Swash Caps<\\\/div><div class=\\\"listelement\\\">Delius Unicase<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">Dorsa<\\\/div><div class=\\\"listelement\\\">Droid Sans<\\\/div><div class=\\\"listelement\\\">Droid Sans Mono<\\\/div><div class=\\\"listelement\\\">Droid Serif<\\\/div><div class=\\\"listelement\\\">EB Garamond<\\\/div><div class=\\\"listelement\\\">Exo<\\\/div><div class=\\\"listelement\\\">Expletus Sans<\\\/div><div class=\\\"listelement\\\">Fanwood Text<\\\/div><div class=\\\"listelement\\\">Federo<\\\/div><div class=\\\"listelement\\\">Fontdiner Swanky<\\\/div><div class=\\\"listelement\\\">Forum<\\\/div><div class=\\\"listelement\\\">Francois One<\\\/div><div class=\\\"listelement\\\">Gentium Basic<\\\/div><div class=\\\"listelement\\\">Gentium Book Basic<\\\/div><div class=\\\"listelement\\\">Geo<\\\/div><div class=\\\"listelement\\\">Geostar<\\\/div><div class=\\\"listelement\\\">Geostar Fill<\\\/div><div class=\\\"listelement\\\">Give You Glory<\\\/div><div class=\\\"listelement\\\">Gloria Hallelujah<\\\/div><div class=\\\"listelement\\\">Goblin One<\\\/div><div class=\\\"listelement\\\">Goudy Bookletter 1911<\\\/div><div class=\\\"listelement\\\">Gravitas One<\\\/div><div class=\\\"listelement\\\">Gruppo<\\\/div><div class=\\\"listelement\\\">Hammersmith One<\\\/div><div class=\\\"listelement\\\">Holtwood One SC<\\\/div><div class=\\\"listelement\\\">Homemade Apple<\\\/div><div class=\\\"listelement\\\">IM Fell DW Pica<\\\/div><div class=\\\"listelement\\\">IM Fell DW Pica SC<\\\/div><div class=\\\"listelement\\\">IM Fell Double Pica<\\\/div><div class=\\\"listelement\\\">IM Fell Double Pica SC<\\\/div><div class=\\\"listelement\\\">IM Fell English<\\\/div><div class=\\\"listelement\\\">IM Fell English SC<\\\/div><div class=\\\"listelement\\\">IM Fell French Canon<\\\/div><div class=\\\"listelement\\\">IM Fell French Canon SC<\\\/div><div class=\\\"listelement\\\">IM Fell Great Primer<\\\/div><div class=\\\"listelement\\\">IM Fell Great Primer SC<\\\/div><div class=\\\"listelement\\\">Inconsolata<\\\/div><div class=\\\"listelement\\\">Inder<\\\/div><div class=\\\"listelement\\\">Indie Flower<\\\/div><div class=\\\"listelement\\\">Irish Grover<\\\/div><div class=\\\"listelement\\\">Istok Web<\\\/div><div class=\\\"listelement\\\">Josefin Sans<\\\/div><div class=\\\"listelement\\\">Josefin Slab<\\\/div><div class=\\\"listelement\\\">Judson<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">Just Another Hand<\\\/div><div class=\\\"listelement\\\">Just Me Again Down Here<\\\/div><div class=\\\"listelement\\\">Kameron<\\\/div><div class=\\\"listelement\\\">Kelly Slab<\\\/div><div class=\\\"listelement\\\">Kenia<\\\/div><div class=\\\"listelement\\\">Kranky<\\\/div><div class=\\\"listelement\\\">Kreon<\\\/div><div class=\\\"listelement\\\">Kristi<\\\/div><div class=\\\"listelement\\\">La Belle Aurore<\\\/div><div class=\\\"listelement\\\">Lato<\\\/div><div class=\\\"listelement\\\">League Script<\\\/div><div class=\\\"listelement\\\">Leckerli One<\\\/div><div class=\\\"listelement\\\">Lekton<\\\/div><div class=\\\"listelement\\\">Limelight<\\\/div><div class=\\\"listelement\\\">Lobster<\\\/div><div class=\\\"listelement\\\">Lobster Two<\\\/div><div class=\\\"listelement\\\">Lora<\\\/div><div class=\\\"listelement\\\">Love Ya Like A Sister<\\\/div><div class=\\\"listelement\\\">Loved by the King<\\\/div><div class=\\\"listelement\\\">Luckiest Guy<\\\/div><div class=\\\"listelement\\\">Magra<\\\/div><div class=\\\"listelement\\\">Maiden Orange<\\\/div><div class=\\\"listelement\\\">Mako<\\\/div><div class=\\\"listelement\\\">Marvel<\\\/div><div class=\\\"listelement\\\">Maven Pro<\\\/div><div class=\\\"listelement\\\">Meddon<\\\/div><div class=\\\"listelement\\\">MedievalSharp<\\\/div><div class=\\\"listelement\\\">Megrim<\\\/div><div class=\\\"listelement\\\">Merriweather<\\\/div><div class=\\\"listelement\\\">Metrophobic<\\\/div><div class=\\\"listelement\\\">Michroma<\\\/div><div class=\\\"listelement\\\">Miltonian<\\\/div><div class=\\\"listelement\\\">Miltonian Tattoo<\\\/div><div class=\\\"listelement\\\">Modern Antiqua<\\\/div><div class=\\\"listelement\\\">Molengo<\\\/div><div class=\\\"listelement\\\">Monofett<\\\/div><div class=\\\"listelement\\\">Monoton<\\\/div><div class=\\\"listelement\\\">Montez<\\\/div><div class=\\\"listelement\\\">Mountains of Christmas<\\\/div><div class=\\\"listelement\\\">Muli<\\\/div><div class=\\\"listelement\\\">Neucha<\\\/div><div class=\\\"listelement\\\">Neuton<\\\/div><div class=\\\"listelement\\\">News Cycle<\\\/div><div class=\\\"listelement\\\">Nixie One<\\\/div><div class=\\\"listelement\\\">Nobile<\\\/div><div class=\\\"listelement\\\">Nothing You Could Do<\\\/div><div class=\\\"listelement\\\">Nova Cut<\\\/div><div class=\\\"listelement\\\">Nova Flat<\\\/div><div class=\\\"listelement\\\">Nova Mono<\\\/div><div class=\\\"listelement\\\">Nova Oval<\\\/div><div class=\\\"listelement\\\">Nova Round<\\\/div><div class=\\\"listelement\\\">Nova Script<\\\/div><div class=\\\"listelement\\\">Nova Slim<\\\/div><div class=\\\"listelement\\\">Nova Square<\\\/div><div class=\\\"listelement\\\">Numans<\\\/div><div class=\\\"listelement\\\">Nunito<\\\/div><div class=\\\"listelement\\\">OFL Sorts Mill Goudy TT<\\\/div><div class=\\\"listelement\\\">Old Standard TT<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Orbitron<\\\/div><div class=\\\"listelement\\\">Oswald<\\\/div><div class=\\\"listelement\\\">Over the Rainbow<\\\/div><div class=\\\"listelement\\\">Ovo<\\\/div><div class=\\\"listelement\\\">PT Sans<\\\/div><div class=\\\"listelement\\\">PT Sans Caption<\\\/div><div class=\\\"listelement\\\">PT Sans Narrow<\\\/div><div class=\\\"listelement\\\">PT Serif<\\\/div><div class=\\\"listelement\\\">PT Serif Caption<\\\/div><div class=\\\"listelement\\\">Pacifico<\\\/div><div class=\\\"listelement\\\">Passero One<\\\/div><div class=\\\"listelement\\\">Patrick Hand<\\\/div><div class=\\\"listelement\\\">Paytone One<\\\/div><div class=\\\"listelement\\\">Permanent Marker<\\\/div><div class=\\\"listelement\\\">Philosopher<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">Playfair Display<\\\/div><div class=\\\"listelement\\\">Podkova<\\\/div><div class=\\\"listelement\\\">Pompiere<\\\/div><div class=\\\"listelement\\\">Prociono<\\\/div><div class=\\\"listelement\\\">Puritan<\\\/div><div class=\\\"listelement\\\">Quattrocento<\\\/div><div class=\\\"listelement\\\">Quattrocento Sans<\\\/div><div class=\\\"listelement\\\">Questrial<\\\/div><div class=\\\"listelement\\\">Quicksand<\\\/div><div class=\\\"listelement\\\">Radley<\\\/div><div class=\\\"listelement\\\">Raleway<\\\/div><div class=\\\"listelement\\\">Rationale<\\\/div><div class=\\\"listelement\\\">Redressed<\\\/div><div class=\\\"listelement\\\">Reenie Beanie<\\\/div><div class=\\\"listelement\\\">Rochester<\\\/div><div class=\\\"listelement\\\">Rock Salt<\\\/div><div class=\\\"listelement\\\">Rokkitt<\\\/div><div class=\\\"listelement\\\">Ropa Sans<\\\/div><div class=\\\"listelement\\\">Rosario<\\\/div><div class=\\\"listelement\\\">Ruslan Display<\\\/div><div class=\\\"listelement\\\">Schoolbell<\\\/div><div class=\\\"listelement\\\">Shadows Into Light<\\\/div><div class=\\\"listelement\\\">Shanti<\\\/div><div class=\\\"listelement\\\">Short Stack<\\\/div><div class=\\\"listelement\\\">Sigmar One<\\\/div><div class=\\\"listelement\\\">Signika<\\\/div><div class=\\\"listelement\\\">Signika Negative<\\\/div><div class=\\\"listelement\\\">Six Caps<\\\/div><div class=\\\"listelement\\\">Slackey<\\\/div><div class=\\\"listelement\\\">Smokum<\\\/div><div class=\\\"listelement\\\">Smythe<\\\/div><div class=\\\"listelement\\\">Sniglet<\\\/div><div class=\\\"listelement\\\">Snippet<\\\/div><div class=\\\"listelement\\\">Special Elite<\\\/div><div class=\\\"listelement\\\">Stardos Stencil<\\\/div><div class=\\\"listelement\\\">Sue Ellen Francisco<\\\/div><div class=\\\"listelement\\\">Sunshiney<\\\/div><div class=\\\"listelement\\\">Swanky and Moo Moo<\\\/div><div class=\\\"listelement\\\">Syncopate<\\\/div><div class=\\\"listelement\\\">Tangerine<\\\/div><div class=\\\"listelement\\\">Telex<\\\/div><div class=\\\"listelement\\\">Tenor Sans<\\\/div><div class=\\\"listelement\\\">Terminal Dosis Light<\\\/div><div class=\\\"listelement\\\">The Girl Next Door<\\\/div><div class=\\\"listelement\\\">Tienne<\\\/div><div class=\\\"listelement\\\">Tinos<\\\/div><div class=\\\"listelement\\\">Tulpen One<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><div class=\\\"listelement\\\">Ultra<\\\/div><div class=\\\"listelement\\\">UnifrakturCook<\\\/div><div class=\\\"listelement\\\">UnifrakturMaguntia<\\\/div><div class=\\\"listelement\\\">Unkempt<\\\/div><div class=\\\"listelement\\\">Unna<\\\/div><div class=\\\"listelement\\\">VT323<\\\/div><div class=\\\"listelement\\\">Varela<\\\/div><div class=\\\"listelement\\\">Varela Round<\\\/div><div class=\\\"listelement\\\">Vibur<\\\/div><div class=\\\"listelement\\\">Viga<\\\/div><div class=\\\"listelement\\\">Vidaloka<\\\/div><div class=\\\"listelement\\\">Volkhov<\\\/div><div class=\\\"listelement\\\">Vollkorn<\\\/div><div class=\\\"listelement\\\">Voltaire<\\\/div><div class=\\\"listelement\\\">Waiting for the Sunrise<\\\/div><div class=\\\"listelement\\\">Wallpoet<\\\/div><div class=\\\"listelement\\\">Walter Turncoat<\\\/div><div class=\\\"listelement\\\">Wire One<\\\/div><div class=\\\"listelement\\\">Yanone Kaffeesatz<\\\/div><div class=\\\"listelement\\\">Yellowtail<\\\/div><div class=\\\"listelement\\\">Yeseva One<\\\/div><div class=\\\"listelement\\\">Zeyada<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Abel\",\"text\":\"Abel\"},{\"value\":\"Abril Fatface\",\"text\":\"Abril Fatface\"},{\"value\":\"Aclonica\",\"text\":\"Aclonica\"},{\"value\":\"Actor\",\"text\":\"Actor\"},{\"value\":\"Aldrich\",\"text\":\"Aldrich\"},{\"value\":\"Alice\",\"text\":\"Alice\"},{\"value\":\"Alike\",\"text\":\"Alike\"},{\"value\":\"Allan\",\"text\":\"Allan\"},{\"value\":\"Allerta\",\"text\":\"Allerta\"},{\"value\":\"Allerta Stencil\",\"text\":\"Allerta Stencil\"},{\"value\":\"Amaranth\",\"text\":\"Amaranth\"},{\"value\":\"Andika\",\"text\":\"Andika\"},{\"value\":\"Annie Use Your Telescope\",\"text\":\"Annie Use Your Telescope\"},{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Antic\",\"text\":\"Antic\"},{\"value\":\"Anton\",\"text\":\"Anton\"},{\"value\":\"Architects Daughter\",\"text\":\"Architects Daughter\"},{\"value\":\"Arimo\",\"text\":\"Arimo\"},{\"value\":\"Artifika\",\"text\":\"Artifika\"},{\"value\":\"Arvo\",\"text\":\"Arvo\"},{\"value\":\"Asap\",\"text\":\"Asap\"},{\"value\":\"Asul\",\"text\":\"Asul\"},{\"value\":\"Asset\",\"text\":\"Asset\"},{\"value\":\"Astloch\",\"text\":\"Astloch\"},{\"value\":\"Aubrey\",\"text\":\"Aubrey\"},{\"value\":\"Bangers\",\"text\":\"Bangers\"},{\"value\":\"Bentham\",\"text\":\"Bentham\"},{\"value\":\"Bevan\",\"text\":\"Bevan\"},{\"value\":\"Bigshot One\",\"text\":\"Bigshot One\"},{\"value\":\"Black Ops One\",\"text\":\"Black Ops One\"},{\"value\":\"Bowlby One\",\"text\":\"Bowlby One\"},{\"value\":\"Bowlby One SC\",\"text\":\"Bowlby One SC\"},{\"value\":\"Brawler\",\"text\":\"Brawler\"},{\"value\":\"Buda\",\"text\":\"Buda\"},{\"value\":\"Cabin\",\"text\":\"Cabin\"},{\"value\":\"Cabin Sketch\",\"text\":\"Cabin Sketch\"},{\"value\":\"Calligraffitti\",\"text\":\"Calligraffitti\"},{\"value\":\"Candal\",\"text\":\"Candal\"},{\"value\":\"Cantarell\",\"text\":\"Cantarell\"},{\"value\":\"Cardo\",\"text\":\"Cardo\"},{\"value\":\"Carme\",\"text\":\"Carme\"},{\"value\":\"Carter One\",\"text\":\"Carter One\"},{\"value\":\"Caudex\",\"text\":\"Caudex\"},{\"value\":\"Cedarville Cursive\",\"text\":\"Cedarville Cursive\"},{\"value\":\"Cherry Cream Soda\",\"text\":\"Cherry Cream Soda\"},{\"value\":\"Chewy\",\"text\":\"Chewy\"},{\"value\":\"Chivo\",\"text\":\"Chivo\"},{\"value\":\"Coda\",\"text\":\"Coda\"},{\"value\":\"Coda Caption\",\"text\":\"Coda Caption\"},{\"value\":\"Comfortaa\",\"text\":\"Comfortaa\"},{\"value\":\"Coming Soon\",\"text\":\"Coming Soon\"},{\"value\":\"Convergence\",\"text\":\"Convergence\"},{\"value\":\"Copse\",\"text\":\"Copse\"},{\"value\":\"Corben\",\"text\":\"Corben\"},{\"value\":\"Cousine\",\"text\":\"Cousine\"},{\"value\":\"Coustard\",\"text\":\"Coustard\"},{\"value\":\"Covered By Your Grace\",\"text\":\"Covered By Your Grace\"},{\"value\":\"Crafty Girls\",\"text\":\"Crafty Girls\"},{\"value\":\"Crimson Text\",\"text\":\"Crimson Text\"},{\"value\":\"Crushed\",\"text\":\"Crushed\"},{\"value\":\"Cuprum\",\"text\":\"Cuprum\"},{\"value\":\"Damion\",\"text\":\"Damion\"},{\"value\":\"Dancing Script\",\"text\":\"Dancing Script\"},{\"value\":\"Dawning of a New Day\",\"text\":\"Dawning of a New Day\"},{\"value\":\"Days One\",\"text\":\"Days One\"},{\"value\":\"Delius\",\"text\":\"Delius\"},{\"value\":\"Delius Swash Caps\",\"text\":\"Delius Swash Caps\"},{\"value\":\"Delius Unicase\",\"text\":\"Delius Unicase\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"Dorsa\",\"text\":\"Dorsa\"},{\"value\":\"Droid Sans\",\"text\":\"Droid Sans\"},{\"value\":\"Droid Sans Mono\",\"text\":\"Droid Sans Mono\"},{\"value\":\"Droid Serif\",\"text\":\"Droid Serif\"},{\"value\":\"EB Garamond\",\"text\":\"EB Garamond\"},{\"value\":\"Exo\",\"text\":\"Exo\"},{\"value\":\"Expletus Sans\",\"text\":\"Expletus Sans\"},{\"value\":\"Fanwood Text\",\"text\":\"Fanwood Text\"},{\"value\":\"Federo\",\"text\":\"Federo\"},{\"value\":\"Fontdiner Swanky\",\"text\":\"Fontdiner Swanky\"},{\"value\":\"Forum\",\"text\":\"Forum\"},{\"value\":\"Francois One\",\"text\":\"Francois One\"},{\"value\":\"Gentium Basic\",\"text\":\"Gentium Basic\"},{\"value\":\"Gentium Book Basic\",\"text\":\"Gentium Book Basic\"},{\"value\":\"Geo\",\"text\":\"Geo\"},{\"value\":\"Geostar\",\"text\":\"Geostar\"},{\"value\":\"Geostar Fill\",\"text\":\"Geostar Fill\"},{\"value\":\"Give You Glory\",\"text\":\"Give You Glory\"},{\"value\":\"Gloria Hallelujah\",\"text\":\"Gloria Hallelujah\"},{\"value\":\"Goblin One\",\"text\":\"Goblin One\"},{\"value\":\"Goudy Bookletter 1911\",\"text\":\"Goudy Bookletter 1911\"},{\"value\":\"Gravitas One\",\"text\":\"Gravitas One\"},{\"value\":\"Gruppo\",\"text\":\"Gruppo\"},{\"value\":\"Hammersmith One\",\"text\":\"Hammersmith One\"},{\"value\":\"Holtwood One SC\",\"text\":\"Holtwood One SC\"},{\"value\":\"Homemade Apple\",\"text\":\"Homemade Apple\"},{\"value\":\"IM Fell DW Pica\",\"text\":\"IM Fell DW Pica\"},{\"value\":\"IM Fell DW Pica SC\",\"text\":\"IM Fell DW Pica SC\"},{\"value\":\"IM Fell Double Pica\",\"text\":\"IM Fell Double Pica\"},{\"value\":\"IM Fell Double Pica SC\",\"text\":\"IM Fell Double Pica SC\"},{\"value\":\"IM Fell English\",\"text\":\"IM Fell English\"},{\"value\":\"IM Fell English SC\",\"text\":\"IM Fell English SC\"},{\"value\":\"IM Fell French Canon\",\"text\":\"IM Fell French Canon\"},{\"value\":\"IM Fell French Canon SC\",\"text\":\"IM Fell French Canon SC\"},{\"value\":\"IM Fell Great Primer\",\"text\":\"IM Fell Great Primer\"},{\"value\":\"IM Fell Great Primer SC\",\"text\":\"IM Fell Great Primer SC\"},{\"value\":\"Inconsolata\",\"text\":\"Inconsolata\"},{\"value\":\"Inder\",\"text\":\"Inder\"},{\"value\":\"Indie Flower\",\"text\":\"Indie Flower\"},{\"value\":\"Irish Grover\",\"text\":\"Irish Grover\"},{\"value\":\"Istok Web\",\"text\":\"Istok Web\"},{\"value\":\"Josefin Sans\",\"text\":\"Josefin Sans\"},{\"value\":\"Josefin Slab\",\"text\":\"Josefin Slab\"},{\"value\":\"Judson\",\"text\":\"Judson\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"Just Another Hand\",\"text\":\"Just Another Hand\"},{\"value\":\"Just Me Again Down Here\",\"text\":\"Just Me Again Down Here\"},{\"value\":\"Kameron\",\"text\":\"Kameron\"},{\"value\":\"Kelly Slab\",\"text\":\"Kelly Slab\"},{\"value\":\"Kenia\",\"text\":\"Kenia\"},{\"value\":\"Kranky\",\"text\":\"Kranky\"},{\"value\":\"Kreon\",\"text\":\"Kreon\"},{\"value\":\"Kristi\",\"text\":\"Kristi\"},{\"value\":\"La Belle Aurore\",\"text\":\"La Belle Aurore\"},{\"value\":\"Lato\",\"text\":\"Lato\"},{\"value\":\"League Script\",\"text\":\"League Script\"},{\"value\":\"Leckerli One\",\"text\":\"Leckerli One\"},{\"value\":\"Lekton\",\"text\":\"Lekton\"},{\"value\":\"Limelight\",\"text\":\"Limelight\"},{\"value\":\"Lobster\",\"text\":\"Lobster\"},{\"value\":\"Lobster Two\",\"text\":\"Lobster Two\"},{\"value\":\"Lora\",\"text\":\"Lora\"},{\"value\":\"Love Ya Like A Sister\",\"text\":\"Love Ya Like A Sister\"},{\"value\":\"Loved by the King\",\"text\":\"Loved by the King\"},{\"value\":\"Luckiest Guy\",\"text\":\"Luckiest Guy\"},{\"value\":\"Magra\",\"text\":\"Magra\"},{\"value\":\"Maiden Orange\",\"text\":\"Maiden Orange\"},{\"value\":\"Mako\",\"text\":\"Mako\"},{\"value\":\"Marvel\",\"text\":\"Marvel\"},{\"value\":\"Maven Pro\",\"text\":\"Maven Pro\"},{\"value\":\"Meddon\",\"text\":\"Meddon\"},{\"value\":\"MedievalSharp\",\"text\":\"MedievalSharp\"},{\"value\":\"Megrim\",\"text\":\"Megrim\"},{\"value\":\"Merriweather\",\"text\":\"Merriweather\"},{\"value\":\"Metrophobic\",\"text\":\"Metrophobic\"},{\"value\":\"Michroma\",\"text\":\"Michroma\"},{\"value\":\"Miltonian\",\"text\":\"Miltonian\"},{\"value\":\"Miltonian Tattoo\",\"text\":\"Miltonian Tattoo\"},{\"value\":\"Modern Antiqua\",\"text\":\"Modern Antiqua\"},{\"value\":\"Molengo\",\"text\":\"Molengo\"},{\"value\":\"Monofett\",\"text\":\"Monofett\"},{\"value\":\"Monoton\",\"text\":\"Monoton\"},{\"value\":\"Montez\",\"text\":\"Montez\"},{\"value\":\"Mountains of Christmas\",\"text\":\"Mountains of Christmas\"},{\"value\":\"Muli\",\"text\":\"Muli\"},{\"value\":\"Neucha\",\"text\":\"Neucha\"},{\"value\":\"Neuton\",\"text\":\"Neuton\"},{\"value\":\"News Cycle\",\"text\":\"News Cycle\"},{\"value\":\"Nixie One\",\"text\":\"Nixie One\"},{\"value\":\"Nobile\",\"text\":\"Nobile\"},{\"value\":\"Nothing You Could Do\",\"text\":\"Nothing You Could Do\"},{\"value\":\"Nova Cut\",\"text\":\"Nova Cut\"},{\"value\":\"Nova Flat\",\"text\":\"Nova Flat\"},{\"value\":\"Nova Mono\",\"text\":\"Nova Mono\"},{\"value\":\"Nova Oval\",\"text\":\"Nova Oval\"},{\"value\":\"Nova Round\",\"text\":\"Nova Round\"},{\"value\":\"Nova Script\",\"text\":\"Nova Script\"},{\"value\":\"Nova Slim\",\"text\":\"Nova Slim\"},{\"value\":\"Nova Square\",\"text\":\"Nova Square\"},{\"value\":\"Numans\",\"text\":\"Numans\"},{\"value\":\"Nunito\",\"text\":\"Nunito\"},{\"value\":\"OFL Sorts Mill Goudy TT\",\"text\":\"OFL Sorts Mill Goudy TT\"},{\"value\":\"Old Standard TT\",\"text\":\"Old Standard TT\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Orbitron\",\"text\":\"Orbitron\"},{\"value\":\"Oswald\",\"text\":\"Oswald\"},{\"value\":\"Over the Rainbow\",\"text\":\"Over the Rainbow\"},{\"value\":\"Ovo\",\"text\":\"Ovo\"},{\"value\":\"PT Sans\",\"text\":\"PT Sans\"},{\"value\":\"PT Sans Caption\",\"text\":\"PT Sans Caption\"},{\"value\":\"PT Sans Narrow\",\"text\":\"PT Sans Narrow\"},{\"value\":\"PT Serif\",\"text\":\"PT Serif\"},{\"value\":\"PT Serif Caption\",\"text\":\"PT Serif Caption\"},{\"value\":\"Pacifico\",\"text\":\"Pacifico\"},{\"value\":\"Passero One\",\"text\":\"Passero One\"},{\"value\":\"Patrick Hand\",\"text\":\"Patrick Hand\"},{\"value\":\"Paytone One\",\"text\":\"Paytone One\"},{\"value\":\"Permanent Marker\",\"text\":\"Permanent Marker\"},{\"value\":\"Philosopher\",\"text\":\"Philosopher\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"Playfair Display\",\"text\":\"Playfair Display\"},{\"value\":\"Podkova\",\"text\":\"Podkova\"},{\"value\":\"Pompiere\",\"text\":\"Pompiere\"},{\"value\":\"Prociono\",\"text\":\"Prociono\"},{\"value\":\"Puritan\",\"text\":\"Puritan\"},{\"value\":\"Quattrocento\",\"text\":\"Quattrocento\"},{\"value\":\"Quattrocento Sans\",\"text\":\"Quattrocento Sans\"},{\"value\":\"Questrial\",\"text\":\"Questrial\"},{\"value\":\"Quicksand\",\"text\":\"Quicksand\"},{\"value\":\"Radley\",\"text\":\"Radley\"},{\"value\":\"Raleway\",\"text\":\"Raleway\"},{\"value\":\"Rationale\",\"text\":\"Rationale\"},{\"value\":\"Redressed\",\"text\":\"Redressed\"},{\"value\":\"Reenie Beanie\",\"text\":\"Reenie Beanie\"},{\"value\":\"Rochester\",\"text\":\"Rochester\"},{\"value\":\"Rock Salt\",\"text\":\"Rock Salt\"},{\"value\":\"Rokkitt\",\"text\":\"Rokkitt\"},{\"value\":\"Ropa Sans\",\"text\":\"Ropa Sans\"},{\"value\":\"Rosario\",\"text\":\"Rosario\"},{\"value\":\"Ruslan Display\",\"text\":\"Ruslan Display\"},{\"value\":\"Schoolbell\",\"text\":\"Schoolbell\"},{\"value\":\"Shadows Into Light\",\"text\":\"Shadows Into Light\"},{\"value\":\"Shanti\",\"text\":\"Shanti\"},{\"value\":\"Short Stack\",\"text\":\"Short Stack\"},{\"value\":\"Sigmar One\",\"text\":\"Sigmar One\"},{\"value\":\"Signika\",\"text\":\"Signika\"},{\"value\":\"Signika Negative\",\"text\":\"Signika Negative\"},{\"value\":\"Six Caps\",\"text\":\"Six Caps\"},{\"value\":\"Slackey\",\"text\":\"Slackey\"},{\"value\":\"Smokum\",\"text\":\"Smokum\"},{\"value\":\"Smythe\",\"text\":\"Smythe\"},{\"value\":\"Sniglet\",\"text\":\"Sniglet\"},{\"value\":\"Snippet\",\"text\":\"Snippet\"},{\"value\":\"Special Elite\",\"text\":\"Special Elite\"},{\"value\":\"Stardos Stencil\",\"text\":\"Stardos Stencil\"},{\"value\":\"Sue Ellen Francisco\",\"text\":\"Sue Ellen Francisco\"},{\"value\":\"Sunshiney\",\"text\":\"Sunshiney\"},{\"value\":\"Swanky and Moo Moo\",\"text\":\"Swanky and Moo Moo\"},{\"value\":\"Syncopate\",\"text\":\"Syncopate\"},{\"value\":\"Tangerine\",\"text\":\"Tangerine\"},{\"value\":\"Telex\",\"text\":\"Telex\"},{\"value\":\"Tenor Sans\",\"text\":\"Tenor Sans\"},{\"value\":\"Terminal Dosis Light\",\"text\":\"Terminal Dosis Light\"},{\"value\":\"The Girl Next Door\",\"text\":\"The Girl Next Door\"},{\"value\":\"Tienne\",\"text\":\"Tienne\"},{\"value\":\"Tinos\",\"text\":\"Tinos\"},{\"value\":\"Tulpen One\",\"text\":\"Tulpen One\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"},{\"value\":\"Ultra\",\"text\":\"Ultra\"},{\"value\":\"UnifrakturCook\",\"text\":\"UnifrakturCook\"},{\"value\":\"UnifrakturMaguntia\",\"text\":\"UnifrakturMaguntia\"},{\"value\":\"Unkempt\",\"text\":\"Unkempt\"},{\"value\":\"Unna\",\"text\":\"Unna\"},{\"value\":\"VT323\",\"text\":\"VT323\"},{\"value\":\"Varela\",\"text\":\"Varela\"},{\"value\":\"Varela Round\",\"text\":\"Varela Round\"},{\"value\":\"Vibur\",\"text\":\"Vibur\"},{\"value\":\"Viga\",\"text\":\"Viga\"},{\"value\":\"Vidaloka\",\"text\":\"Vidaloka\"},{\"value\":\"Volkhov\",\"text\":\"Volkhov\"},{\"value\":\"Vollkorn\",\"text\":\"Vollkorn\"},{\"value\":\"Voltaire\",\"text\":\"Voltaire\"},{\"value\":\"Waiting for the Sunrise\",\"text\":\"Waiting for the Sunrise\"},{\"value\":\"Wallpoet\",\"text\":\"Wallpoet\"},{\"value\":\"Walter Turncoat\",\"text\":\"Walter Turncoat\"},{\"value\":\"Wire One\",\"text\":\"Wire One\"},{\"value\":\"Yanone Kaffeesatz\",\"text\":\"Yanone Kaffeesatz\"},{\"value\":\"Yellowtail\",\"text\":\"Yellowtail\"},{\"value\":\"Yeseva One\",\"text\":\"Yeseva One\"},{\"value\":\"Zeyada\",\"text\":\"Zeyada\"}],\n        selectedIndex: 40,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"LatinExtended":{"name":"jform[params][moduleparametersTab][theme][titlefont]family","id":"jformparamsmoduleparametersTabthemetitlefontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetitlefontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Andika<br \/>Andika<br \/>Anonymous Pro<br \/>Anton<br \/>Caudex<br \/>Didact Gothic<br \/>EB Garamond<br \/>Forum<br \/>Francois One<br \/>Gentium Basic<br \/>Gentium Book Basic<br \/>Istok Web<br \/>Jura<br \/>Kelly Slab<br \/>Lobster<br \/>MedievalSharp<br \/>Modern Antiqua<br \/>Neuton<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Patrick Hand<br \/>Play<br \/>Ruslan Display<br \/>Tenor Sans<br \/>Ubuntu<br \/>Varela<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]family\" id=\"jformparamsmoduleparametersTabthemetitlefontfamily\" value=\"Andika\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetitlefontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Andika<\\\/div><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Anton<\\\/div><div class=\\\"listelement\\\">Caudex<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">EB Garamond<\\\/div><div class=\\\"listelement\\\">Forum<\\\/div><div class=\\\"listelement\\\">Francois One<\\\/div><div class=\\\"listelement\\\">Gentium Basic<\\\/div><div class=\\\"listelement\\\">Gentium Book Basic<\\\/div><div class=\\\"listelement\\\">Istok Web<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">Kelly Slab<\\\/div><div class=\\\"listelement\\\">Lobster<\\\/div><div class=\\\"listelement\\\">MedievalSharp<\\\/div><div class=\\\"listelement\\\">Modern Antiqua<\\\/div><div class=\\\"listelement\\\">Neuton<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Patrick Hand<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">Ruslan Display<\\\/div><div class=\\\"listelement\\\">Tenor Sans<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><div class=\\\"listelement\\\">Varela<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Andika\",\"text\":\"Andika\"},{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Anton\",\"text\":\"Anton\"},{\"value\":\"Caudex\",\"text\":\"Caudex\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"EB Garamond\",\"text\":\"EB Garamond\"},{\"value\":\"Forum\",\"text\":\"Forum\"},{\"value\":\"Francois One\",\"text\":\"Francois One\"},{\"value\":\"Gentium Basic\",\"text\":\"Gentium Basic\"},{\"value\":\"Gentium Book Basic\",\"text\":\"Gentium Book Basic\"},{\"value\":\"Istok Web\",\"text\":\"Istok Web\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"Kelly Slab\",\"text\":\"Kelly Slab\"},{\"value\":\"Lobster\",\"text\":\"Lobster\"},{\"value\":\"MedievalSharp\",\"text\":\"MedievalSharp\"},{\"value\":\"Modern Antiqua\",\"text\":\"Modern Antiqua\"},{\"value\":\"Neuton\",\"text\":\"Neuton\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Patrick Hand\",\"text\":\"Patrick Hand\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"Ruslan Display\",\"text\":\"Ruslan Display\"},{\"value\":\"Tenor Sans\",\"text\":\"Tenor Sans\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"},{\"value\":\"Varela\",\"text\":\"Varela\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"Vietnamese":{"name":"jform[params][moduleparametersTab][theme][titlefont]family","id":"jformparamsmoduleparametersTabthemetitlefontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetitlefontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">EB Garamond<br \/>EB Garamond<br \/>Open Sans<br \/>Open Sans Condensed<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]family\" id=\"jformparamsmoduleparametersTabthemetitlefontfamily\" value=\"EB Garamond\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetitlefontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">EB Garamond<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><\\\/div>\",\n        options: [{\"value\":\"EB Garamond\",\"text\":\"EB Garamond\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetitlefonttype\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Latin<br \/>Alternative fonts<br \/>Cyrillic<br \/>CyrillicExtended<br \/>Greek<br \/>GreekExtended<br \/>Khmer<br \/>Latin<br \/>LatinExtended<br \/>Vietnamese<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]type\" id=\"jformparamsmoduleparametersTabthemetitlefonttype\" value=\"Latin\"\/><\/div><\/div>"},"size":{"name":"jform[params][moduleparametersTab][theme][titlefont]size","id":"jformparamsmoduleparametersTabthemetitlefontsize","html":"<div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemetitlefontsize\"><input  size=\"1\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemetitlefontsizeinput\" value=\"14\"><div class=\"offlajntext_increment\">\n                <div class=\"offlajntext_increment_up arrow\"><\/div>\n                <div class=\"offlajntext_increment_down arrow\"><\/div>\n      <\/div><\/div><div class=\"offlajnswitcher\">\r\n            <div class=\"offlajnswitcher_inner\" id=\"offlajnswitcher_innerjformparamsmoduleparametersTabthemetitlefontsizeunit\"><\/div>\r\n    <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]size[unit]\" id=\"jformparamsmoduleparametersTabthemetitlefontsizeunit\" value=\"px\" \/><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]size\" id=\"jformparamsmoduleparametersTabthemetitlefontsize\" value=\"14||px\">"},"color":{"name":"jform[params][moduleparametersTab][theme][titlefont]color","id":"jformparamsmoduleparametersTabthemetitlefontcolor","html":"<div class=\"offlajncolor\"><input type=\"text\" name=\"jform[params][moduleparametersTab][theme][titlefont]color\" id=\"jformparamsmoduleparametersTabthemetitlefontcolor\" value=\"5d5c5c\" class=\"color wa\" size=\"12\" \/><\/div>"},"bold":{"name":"jform[params][moduleparametersTab][theme][titlefont]bold","id":"jformparamsmoduleparametersTabthemetitlefontbold","html":"<div id=\"offlajnonoffjformparamsmoduleparametersTabthemetitlefontbold\" class=\"gk_hack onoffbutton\">\n                <div class=\"gk_hack onoffbutton_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnonoff\/images\/bold.png);\"><\/div>\n      <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]bold\" id=\"jformparamsmoduleparametersTabthemetitlefontbold\" value=\"0\" \/>"},"italic":{"name":"jform[params][moduleparametersTab][theme][titlefont]italic","id":"jformparamsmoduleparametersTabthemetitlefontitalic","html":"<div id=\"offlajnonoffjformparamsmoduleparametersTabthemetitlefontitalic\" class=\"gk_hack onoffbutton\">\n                <div class=\"gk_hack onoffbutton_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnonoff\/images\/italic.png);\"><\/div>\n      <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]italic\" id=\"jformparamsmoduleparametersTabthemetitlefontitalic\" value=\"0\" \/>"},"underline":{"name":"jform[params][moduleparametersTab][theme][titlefont]underline","id":"jformparamsmoduleparametersTabthemetitlefontunderline","html":"<div id=\"offlajnonoffjformparamsmoduleparametersTabthemetitlefontunderline\" class=\"gk_hack onoffbutton\">\n                <div class=\"gk_hack onoffbutton_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnonoff\/images\/underline.png);\"><\/div>\n      <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]underline\" id=\"jformparamsmoduleparametersTabthemetitlefontunderline\" value=\"0\" \/>"},"align":{"name":"jform[params][moduleparametersTab][theme][titlefont]align","id":"jformparamsmoduleparametersTabthemetitlefontalign","html":"<div class=\"offlajnradiocontainerimage\" id=\"offlajnradiocontainerjformparamsmoduleparametersTabthemetitlefontalign\"><div class=\"radioelement first selected\"><div class=\"radioelement_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnradio\/images\/left_align.png);\"><\/div><\/div><div class=\"radioelement \"><div class=\"radioelement_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnradio\/images\/center_align.png);\"><\/div><\/div><div class=\"radioelement  last\"><div class=\"radioelement_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnradio\/images\/right_align.png);\"><\/div><\/div><div class=\"clear\"><\/div><\/div><input type=\"hidden\" id=\"jformparamsmoduleparametersTabthemetitlefontalign\" name=\"jform[params][moduleparametersTab][theme][titlefont]align\" value=\"left\"\/>"},"afont":{"name":"jform[params][moduleparametersTab][theme][titlefont]afont","id":"jformparamsmoduleparametersTabthemetitlefontafont","html":"<div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemetitlefontafont\"><input  size=\"10\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemetitlefontafontinput\" value=\"Helvetica\"><\/div><div class=\"offlajnswitcher\">\r\n            <div class=\"offlajnswitcher_inner\" id=\"offlajnswitcher_innerjformparamsmoduleparametersTabthemetitlefontafontunit\"><\/div>\r\n    <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]afont[unit]\" id=\"jformparamsmoduleparametersTabthemetitlefontafontunit\" value=\"1\" \/><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]afont\" id=\"jformparamsmoduleparametersTabthemetitlefontafont\" value=\"Helvetica||1\">"},"tshadow":{"name":"jform[params][moduleparametersTab][theme][titlefont]tshadow","id":"jformparamsmoduleparametersTabthemetitlefonttshadow","html":"<div id=\"offlajncombine_outerjformparamsmoduleparametersTabthemetitlefonttshadow\" class=\"offlajncombine_outer\"><div class=\"offlajncombinefieldcontainer\"><div class=\"offlajncombinefield\"><div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemetitlefonttshadow0\"><input  size=\"1\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemetitlefonttshadow0input\" value=\"0\"><div class=\"unit\">px<\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]tshadow0\" id=\"jformparamsmoduleparametersTabthemetitlefonttshadow0\" value=\"0\"><\/div><\/div><div class=\"offlajncombinefieldcontainer\"><div class=\"offlajncombinefield\"><div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemetitlefonttshadow1\"><input  size=\"1\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemetitlefonttshadow1input\" value=\"0\"><div class=\"unit\">px<\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]tshadow1\" id=\"jformparamsmoduleparametersTabthemetitlefonttshadow1\" value=\"0\"><\/div><\/div><div class=\"offlajncombinefieldcontainer\"><div class=\"offlajncombinefield\"><div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemetitlefonttshadow2\"><input  size=\"1\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemetitlefonttshadow2input\" value=\"0\"><div class=\"unit\">px<\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]tshadow2\" id=\"jformparamsmoduleparametersTabthemetitlefonttshadow2\" value=\"0\"><\/div><\/div><div class=\"offlajncombinefieldcontainer\"><div class=\"offlajncombinefield\"><div class=\"offlajncolor\"><input type=\"text\" name=\"jform[params][moduleparametersTab][theme][titlefont]tshadow3\" id=\"jformparamsmoduleparametersTabthemetitlefonttshadow3\" value=\"000000\" class=\"color \" size=\"12\" \/><\/div><\/div><\/div><div class=\"offlajncombinefieldcontainer\"><div class=\"offlajncombinefield\"><div class=\"offlajnswitcher\">\r\n            <div class=\"offlajnswitcher_inner\" id=\"offlajnswitcher_innerjformparamsmoduleparametersTabthemetitlefonttshadow4\"><\/div>\r\n    <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]tshadow4\" id=\"jformparamsmoduleparametersTabthemetitlefonttshadow4\" value=\"0\" \/><\/div><\/div><div class=\"offlajncombine_hider\"><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]tshadow\" id=\"jformparamsmoduleparametersTabthemetitlefonttshadow\" value='0|*|0|*|0|*|000000|*|0'>"},"lineheight":{"name":"jform[params][moduleparametersTab][theme][titlefont]lineheight","id":"jformparamsmoduleparametersTabthemetitlefontlineheight","html":"<div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemetitlefontlineheight\"><input  size=\"5\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemetitlefontlineheightinput\" value=\"21px\"><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][titlefont]lineheight\" id=\"jformparamsmoduleparametersTabthemetitlefontlineheight\" value=\"21px\">"}},
          script: "dojo.addOnLoad(function(){\r\n      new OfflajnRadio({\r\n        id: \"jformparamsmoduleparametersTabthemetitlefonttab\",\r\n        values: [\"Text\"],\r\n        map: {\"Text\":0},\r\n        mode: \"\"\r\n      });\r\n    \n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetitlefonttype\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Alternative fonts<\\\/div><div class=\\\"listelement\\\">Cyrillic<\\\/div><div class=\\\"listelement\\\">CyrillicExtended<\\\/div><div class=\\\"listelement\\\">Greek<\\\/div><div class=\\\"listelement\\\">GreekExtended<\\\/div><div class=\\\"listelement\\\">Khmer<\\\/div><div class=\\\"listelement\\\">Latin<\\\/div><div class=\\\"listelement\\\">LatinExtended<\\\/div><div class=\\\"listelement\\\">Vietnamese<\\\/div><\\\/div>\",\n        options: [{\"value\":\"0\",\"text\":\"Alternative fonts\"},{\"value\":\"Cyrillic\",\"text\":\"Cyrillic\"},{\"value\":\"CyrillicExtended\",\"text\":\"CyrillicExtended\"},{\"value\":\"Greek\",\"text\":\"Greek\"},{\"value\":\"GreekExtended\",\"text\":\"GreekExtended\"},{\"value\":\"Khmer\",\"text\":\"Khmer\"},{\"value\":\"Latin\",\"text\":\"Latin\"},{\"value\":\"LatinExtended\",\"text\":\"LatinExtended\"},{\"value\":\"Vietnamese\",\"text\":\"Vietnamese\"}],\n        selectedIndex: 6,\n        height: 0,\n        fireshow: 0\n      });\n    dojo.addOnLoad(function(){ \r\n      new OfflajnSwitcher({\r\n        id: \"jformparamsmoduleparametersTabthemetitlefontsizeunit\",\r\n        units: [\"px\",\"em\"],\r\n        values: [\"px\",\"em\"],\r\n        map: {\"px\":0,\"em\":1},\r\n        mode: 0,\r\n        url: \"http:\\\/\\\/emundus.local\\\/administrator\\\/..\\\/modules\\\/mod_improved_ajax_login\\\/params\\\/offlajnswitcher\\\/images\\\/\"\r\n      }); \r\n    });\n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemetitlefontsize\",\n        validation: \"int\",\n        attachunit: \"\",\n        mode: \"increment\",\n        scale: \"1\",\n        minus: 0,\n        onoff: \"\"\n      }); \n    \n    var el = dojo.byId(\"jformparamsmoduleparametersTabthemetitlefontcolor\");\n    jQuery.fn.jPicker.defaults.images.clientPath=\"\/modules\/mod_improved_ajax_login\/params\/offlajndashboard\/..\/offlajncolor\/offlajncolor\/jpicker\/images\/\";\n    el.alphaSupport=false; \n    el.c = jQuery(\"#jformparamsmoduleparametersTabthemetitlefontcolor\").jPicker({\n        window:{\n          expandable: true,\n          alphaSupport: false}\n        });\n    dojo.connect(el, \"change\", function(){\n      this.c[0].color.active.val(\"hex\", this.value);\n    });\n    \n      new OfflajnOnOff({\n        id: \"jformparamsmoduleparametersTabthemetitlefontbold\",\n        mode: \"button\",\n        imgs: \"\"\n      }); \n    \n      new OfflajnOnOff({\n        id: \"jformparamsmoduleparametersTabthemetitlefontitalic\",\n        mode: \"button\",\n        imgs: \"\"\n      }); \n    \n      new OfflajnOnOff({\n        id: \"jformparamsmoduleparametersTabthemetitlefontunderline\",\n        mode: \"button\",\n        imgs: \"\"\n      }); \n    \r\n      new OfflajnRadio({\r\n        id: \"jformparamsmoduleparametersTabthemetitlefontalign\",\r\n        values: [\"left\",\"center\",\"right\"],\r\n        map: {\"left\":0,\"center\":1,\"right\":2},\r\n        mode: \"image\"\r\n      });\r\n    dojo.addOnLoad(function(){ \r\n      new OfflajnSwitcher({\r\n        id: \"jformparamsmoduleparametersTabthemetitlefontafontunit\",\r\n        units: [\"ON\",\"OFF\"],\r\n        values: [\"1\",\"0\"],\r\n        map: {\"1\":0,\"0\":1},\r\n        mode: 0,\r\n        url: \"http:\\\/\\\/emundus.local\\\/administrator\\\/..\\\/modules\\\/mod_improved_ajax_login\\\/params\\\/offlajnswitcher\\\/images\\\/\"\r\n      }); \r\n    });\n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemetitlefontafont\",\n        validation: \"\",\n        attachunit: \"\",\n        mode: \"\",\n        scale: \"\",\n        minus: 0,\n        onoff: \"1\"\n      }); \n    \n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemetitlefonttshadow0\",\n        validation: \"float\",\n        attachunit: \"px\",\n        mode: \"\",\n        scale: \"\",\n        minus: 0,\n        onoff: \"\"\n      }); \n    \n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemetitlefonttshadow1\",\n        validation: \"float\",\n        attachunit: \"px\",\n        mode: \"\",\n        scale: \"\",\n        minus: 0,\n        onoff: \"\"\n      }); \n    \n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemetitlefonttshadow2\",\n        validation: \"float\",\n        attachunit: \"px\",\n        mode: \"\",\n        scale: \"\",\n        minus: 0,\n        onoff: \"\"\n      }); \n    \n    var el = dojo.byId(\"jformparamsmoduleparametersTabthemetitlefonttshadow3\");\n    jQuery.fn.jPicker.defaults.images.clientPath=\"\/modules\/mod_improved_ajax_login\/params\/offlajndashboard\/..\/offlajncolor\/offlajncolor\/jpicker\/images\/\";\n    el.alphaSupport=true; \n    el.c = jQuery(\"#jformparamsmoduleparametersTabthemetitlefonttshadow3\").jPicker({\n        window:{\n          expandable: true,\n          alphaSupport: true}\n        });\n    dojo.connect(el, \"change\", function(){\n      this.c[0].color.active.val(\"hex\", this.value);\n    });\n    dojo.addOnLoad(function(){ \r\n      new OfflajnSwitcher({\r\n        id: \"jformparamsmoduleparametersTabthemetitlefonttshadow4\",\r\n        units: [\"ON\",\"OFF\"],\r\n        values: [\"1\",\"0\"],\r\n        map: {\"1\":0,\"0\":1},\r\n        mode: 0,\r\n        url: \"http:\\\/\\\/emundus.local\\\/administrator\\\/..\\\/modules\\\/mod_improved_ajax_login\\\/params\\\/offlajnswitcher\\\/images\\\/\"\r\n      }); \r\n    });\r\n      new OfflajnCombine({\r\n        id: \"jformparamsmoduleparametersTabthemetitlefonttshadow\",\r\n        num: 5,\r\n        switcherid: \"jformparamsmoduleparametersTabthemetitlefonttshadow4\",\r\n        hideafter: \"0\"\r\n      }); \r\n    \n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemetitlefontlineheight\",\n        validation: \"\",\n        attachunit: \"\",\n        mode: \"\",\n        scale: \"\",\n        minus: 0,\n        onoff: \"\"\n      }); \n    });"
        });
    

        new FontConfigurator({
          id: "jformparamsmoduleparametersTabthemebtnfont",
          defaultTab: "Text",
          origsettings: {"Text":{"lineheight":"normal","type":"Latin","family":"Carme","subset":"Latin","size":"12||px","color":"ffffff","tshadow":"1||px|*|1||px|*|0|*|000000b3|*|1|*|","afont":"Helvetica||1","bold":"0","lineheight":"normal"}},
          elements: {"tab":{"name":"jform[params][moduleparametersTab][theme][btnfont]tab","id":"jformparamsmoduleparametersTabthemebtnfonttab","html":"<div class=\"offlajnradiocontainerbutton\" id=\"offlajnradiocontainerjformparamsmoduleparametersTabthemebtnfonttab\"><div class=\"radioelement first last selected\">Text<\/div><div class=\"clear\"><\/div><\/div><input type=\"hidden\" id=\"jformparamsmoduleparametersTabthemebtnfonttab\" name=\"jform[params][moduleparametersTab][theme][btnfont]tab\" value=\"Text\"\/>"},"type":{"name":"jform[params][moduleparametersTab][theme][btnfont]type","id":"jformparamsmoduleparametersTabthemebtnfonttype","Cyrillic":{"name":"jform[params][moduleparametersTab][theme][btnfont]family","id":"jformparamsmoduleparametersTabthemebtnfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemebtnfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Andika<br \/>Andika<br \/>Anonymous Pro<br \/>Cuprum<br \/>Didact Gothic<br \/>EB Garamond<br \/>Istok Web<br \/>Jura<br \/>Forum<br \/>Kelly Slab<br \/>Lobster<br \/>Neucha<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Philosopher<br \/>Play<br \/>PT Sans<br \/>PT Sans Caption<br \/>PT Sans Narrow<br \/>PT Serif<br \/>PT Serif Caption<br \/>Ruslan Display<br \/>Tenor Sans<br \/>Ubuntu<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]family\" id=\"jformparamsmoduleparametersTabthemebtnfontfamily\" value=\"Andika\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemebtnfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Andika<\\\/div><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Cuprum<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">EB Garamond<\\\/div><div class=\\\"listelement\\\">Istok Web<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">Forum<\\\/div><div class=\\\"listelement\\\">Kelly Slab<\\\/div><div class=\\\"listelement\\\">Lobster<\\\/div><div class=\\\"listelement\\\">Neucha<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Philosopher<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">PT Sans<\\\/div><div class=\\\"listelement\\\">PT Sans Caption<\\\/div><div class=\\\"listelement\\\">PT Sans Narrow<\\\/div><div class=\\\"listelement\\\">PT Serif<\\\/div><div class=\\\"listelement\\\">PT Serif Caption<\\\/div><div class=\\\"listelement\\\">Ruslan Display<\\\/div><div class=\\\"listelement\\\">Tenor Sans<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Andika\",\"text\":\"Andika\"},{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Cuprum\",\"text\":\"Cuprum\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"EB Garamond\",\"text\":\"EB Garamond\"},{\"value\":\"Istok Web\",\"text\":\"Istok Web\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"Forum\",\"text\":\"Forum\"},{\"value\":\"Kelly Slab\",\"text\":\"Kelly Slab\"},{\"value\":\"Lobster\",\"text\":\"Lobster\"},{\"value\":\"Neucha\",\"text\":\"Neucha\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Philosopher\",\"text\":\"Philosopher\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"PT Sans\",\"text\":\"PT Sans\"},{\"value\":\"PT Sans Caption\",\"text\":\"PT Sans Caption\"},{\"value\":\"PT Sans Narrow\",\"text\":\"PT Sans Narrow\"},{\"value\":\"PT Serif\",\"text\":\"PT Serif\"},{\"value\":\"PT Serif Caption\",\"text\":\"PT Serif Caption\"},{\"value\":\"Ruslan Display\",\"text\":\"Ruslan Display\"},{\"value\":\"Tenor Sans\",\"text\":\"Tenor Sans\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"CyrillicExtended":{"name":"jform[params][moduleparametersTab][theme][btnfont]family","id":"jformparamsmoduleparametersTabthemebtnfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemebtnfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Andika<br \/>Andika<br \/>Anonymous Pro<br \/>Didact Gothic<br \/>EB Garamond<br \/>Istok Web<br \/>Jura<br \/>Forum<br \/>Lobster<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Play<br \/>Ruslan Display<br \/>Tenor Sans<br \/>Ubuntu<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]family\" id=\"jformparamsmoduleparametersTabthemebtnfontfamily\" value=\"Andika\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemebtnfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Andika<\\\/div><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">EB Garamond<\\\/div><div class=\\\"listelement\\\">Istok Web<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">Forum<\\\/div><div class=\\\"listelement\\\">Lobster<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">Ruslan Display<\\\/div><div class=\\\"listelement\\\">Tenor Sans<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Andika\",\"text\":\"Andika\"},{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"EB Garamond\",\"text\":\"EB Garamond\"},{\"value\":\"Istok Web\",\"text\":\"Istok Web\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"Forum\",\"text\":\"Forum\"},{\"value\":\"Lobster\",\"text\":\"Lobster\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"Ruslan Display\",\"text\":\"Ruslan Display\"},{\"value\":\"Tenor Sans\",\"text\":\"Tenor Sans\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"Greek":{"name":"jform[params][moduleparametersTab][theme][btnfont]family","id":"jformparamsmoduleparametersTabthemebtnfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemebtnfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Anonymous Pro<br \/>Anonymous Pro<br \/>Caudex<br \/>Didact Gothic<br \/>Jura<br \/>GFS Didot<br \/>GFS Neohellenic<br \/>Nova Mono<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Play<br \/>Ubuntu<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]family\" id=\"jformparamsmoduleparametersTabthemebtnfontfamily\" value=\"Anonymous Pro\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemebtnfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Caudex<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">GFS Didot<\\\/div><div class=\\\"listelement\\\">GFS Neohellenic<\\\/div><div class=\\\"listelement\\\">Nova Mono<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Caudex\",\"text\":\"Caudex\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"GFS Didot\",\"text\":\"GFS Didot\"},{\"value\":\"GFS Neohellenic\",\"text\":\"GFS Neohellenic\"},{\"value\":\"Nova Mono\",\"text\":\"Nova Mono\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"GreekExtended":{"name":"jform[params][moduleparametersTab][theme][btnfont]family","id":"jformparamsmoduleparametersTabthemebtnfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemebtnfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Anonymous Pro<br \/>Anonymous Pro<br \/>Caudex<br \/>Didact Gothic<br \/>Jura<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Play<br \/>Ubuntu<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]family\" id=\"jformparamsmoduleparametersTabthemebtnfontfamily\" value=\"Anonymous Pro\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemebtnfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Caudex<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Caudex\",\"text\":\"Caudex\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"Khmer":{"name":"jform[params][moduleparametersTab][theme][btnfont]family","id":"jformparamsmoduleparametersTabthemebtnfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemebtnfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Angkor<br \/>Angkor<br \/>Battambang<br \/>Bayon<br \/>Bokor<br \/>Chenla<br \/>Content<br \/>Dangrek<br \/>Freehand<br \/>Hanuman<br \/>Khmer<br \/>Koulen<br \/>Metal<br \/>Moul<br \/>Moulpali<br \/>Odor Mean Chey<br \/>Preahvihear<br \/>Siemreap<br \/>Suwannaphum<br \/>Taprom<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]family\" id=\"jformparamsmoduleparametersTabthemebtnfontfamily\" value=\"Angkor\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemebtnfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Angkor<\\\/div><div class=\\\"listelement\\\">Battambang<\\\/div><div class=\\\"listelement\\\">Bayon<\\\/div><div class=\\\"listelement\\\">Bokor<\\\/div><div class=\\\"listelement\\\">Chenla<\\\/div><div class=\\\"listelement\\\">Content<\\\/div><div class=\\\"listelement\\\">Dangrek<\\\/div><div class=\\\"listelement\\\">Freehand<\\\/div><div class=\\\"listelement\\\">Hanuman<\\\/div><div class=\\\"listelement\\\">Khmer<\\\/div><div class=\\\"listelement\\\">Koulen<\\\/div><div class=\\\"listelement\\\">Metal<\\\/div><div class=\\\"listelement\\\">Moul<\\\/div><div class=\\\"listelement\\\">Moulpali<\\\/div><div class=\\\"listelement\\\">Odor Mean Chey<\\\/div><div class=\\\"listelement\\\">Preahvihear<\\\/div><div class=\\\"listelement\\\">Siemreap<\\\/div><div class=\\\"listelement\\\">Suwannaphum<\\\/div><div class=\\\"listelement\\\">Taprom<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Angkor\",\"text\":\"Angkor\"},{\"value\":\"Battambang\",\"text\":\"Battambang\"},{\"value\":\"Bayon\",\"text\":\"Bayon\"},{\"value\":\"Bokor\",\"text\":\"Bokor\"},{\"value\":\"Chenla\",\"text\":\"Chenla\"},{\"value\":\"Content\",\"text\":\"Content\"},{\"value\":\"Dangrek\",\"text\":\"Dangrek\"},{\"value\":\"Freehand\",\"text\":\"Freehand\"},{\"value\":\"Hanuman\",\"text\":\"Hanuman\"},{\"value\":\"Khmer\",\"text\":\"Khmer\"},{\"value\":\"Koulen\",\"text\":\"Koulen\"},{\"value\":\"Metal\",\"text\":\"Metal\"},{\"value\":\"Moul\",\"text\":\"Moul\"},{\"value\":\"Moulpali\",\"text\":\"Moulpali\"},{\"value\":\"Odor Mean Chey\",\"text\":\"Odor Mean Chey\"},{\"value\":\"Preahvihear\",\"text\":\"Preahvihear\"},{\"value\":\"Siemreap\",\"text\":\"Siemreap\"},{\"value\":\"Suwannaphum\",\"text\":\"Suwannaphum\"},{\"value\":\"Taprom\",\"text\":\"Taprom\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"Latin":{"name":"jform[params][moduleparametersTab][theme][btnfont]family","id":"jformparamsmoduleparametersTabthemebtnfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemebtnfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Carme<br \/>Abel<br \/>Abril Fatface<br \/>Aclonica<br \/>Actor<br \/>Aldrich<br \/>Alice<br \/>Alike<br \/>Allan<br \/>Allerta<br \/>Allerta Stencil<br \/>Amaranth<br \/>Andika<br \/>Annie Use Your Telescope<br \/>Anonymous Pro<br \/>Antic<br \/>Anton<br \/>Architects Daughter<br \/>Arimo<br \/>Artifika<br \/>Arvo<br \/>Asap<br \/>Asul<br \/>Asset<br \/>Astloch<br \/>Aubrey<br \/>Bangers<br \/>Bentham<br \/>Bevan<br \/>Bigshot One<br \/>Black Ops One<br \/>Bowlby One<br \/>Bowlby One SC<br \/>Brawler<br \/>Buda<br \/>Cabin<br \/>Cabin Sketch<br \/>Calligraffitti<br \/>Candal<br \/>Cantarell<br \/>Cardo<br \/>Carme<br \/>Carter One<br \/>Caudex<br \/>Cedarville Cursive<br \/>Cherry Cream Soda<br \/>Chewy<br \/>Chivo<br \/>Coda<br \/>Coda Caption<br \/>Comfortaa<br \/>Coming Soon<br \/>Convergence<br \/>Copse<br \/>Corben<br \/>Cousine<br \/>Coustard<br \/>Covered By Your Grace<br \/>Crafty Girls<br \/>Crimson Text<br \/>Crushed<br \/>Cuprum<br \/>Damion<br \/>Dancing Script<br \/>Dawning of a New Day<br \/>Days One<br \/>Delius<br \/>Delius Swash Caps<br \/>Delius Unicase<br \/>Didact Gothic<br \/>Dorsa<br \/>Droid Sans<br \/>Droid Sans Mono<br \/>Droid Serif<br \/>EB Garamond<br \/>Exo<br \/>Expletus Sans<br \/>Fanwood Text<br \/>Federo<br \/>Fontdiner Swanky<br \/>Forum<br \/>Francois One<br \/>Gentium Basic<br \/>Gentium Book Basic<br \/>Geo<br \/>Geostar<br \/>Geostar Fill<br \/>Give You Glory<br \/>Gloria Hallelujah<br \/>Goblin One<br \/>Goudy Bookletter 1911<br \/>Gravitas One<br \/>Gruppo<br \/>Hammersmith One<br \/>Holtwood One SC<br \/>Homemade Apple<br \/>IM Fell DW Pica<br \/>IM Fell DW Pica SC<br \/>IM Fell Double Pica<br \/>IM Fell Double Pica SC<br \/>IM Fell English<br \/>IM Fell English SC<br \/>IM Fell French Canon<br \/>IM Fell French Canon SC<br \/>IM Fell Great Primer<br \/>IM Fell Great Primer SC<br \/>Inconsolata<br \/>Inder<br \/>Indie Flower<br \/>Irish Grover<br \/>Istok Web<br \/>Josefin Sans<br \/>Josefin Slab<br \/>Judson<br \/>Jura<br \/>Just Another Hand<br \/>Just Me Again Down Here<br \/>Kameron<br \/>Kelly Slab<br \/>Kenia<br \/>Kranky<br \/>Kreon<br \/>Kristi<br \/>La Belle Aurore<br \/>Lato<br \/>League Script<br \/>Leckerli One<br \/>Lekton<br \/>Limelight<br \/>Lobster<br \/>Lobster Two<br \/>Lora<br \/>Love Ya Like A Sister<br \/>Loved by the King<br \/>Luckiest Guy<br \/>Magra<br \/>Maiden Orange<br \/>Mako<br \/>Marvel<br \/>Maven Pro<br \/>Meddon<br \/>MedievalSharp<br \/>Megrim<br \/>Merriweather<br \/>Metrophobic<br \/>Michroma<br \/>Miltonian<br \/>Miltonian Tattoo<br \/>Modern Antiqua<br \/>Molengo<br \/>Monofett<br \/>Monoton<br \/>Montez<br \/>Mountains of Christmas<br \/>Muli<br \/>Neucha<br \/>Neuton<br \/>News Cycle<br \/>Nixie One<br \/>Nobile<br \/>Nothing You Could Do<br \/>Nova Cut<br \/>Nova Flat<br \/>Nova Mono<br \/>Nova Oval<br \/>Nova Round<br \/>Nova Script<br \/>Nova Slim<br \/>Nova Square<br \/>Numans<br \/>Nunito<br \/>OFL Sorts Mill Goudy TT<br \/>Old Standard TT<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Orbitron<br \/>Oswald<br \/>Over the Rainbow<br \/>Ovo<br \/>PT Sans<br \/>PT Sans Caption<br \/>PT Sans Narrow<br \/>PT Serif<br \/>PT Serif Caption<br \/>Pacifico<br \/>Passero One<br \/>Patrick Hand<br \/>Paytone One<br \/>Permanent Marker<br \/>Philosopher<br \/>Play<br \/>Playfair Display<br \/>Podkova<br \/>Pompiere<br \/>Prociono<br \/>Puritan<br \/>Quattrocento<br \/>Quattrocento Sans<br \/>Questrial<br \/>Quicksand<br \/>Radley<br \/>Raleway<br \/>Rationale<br \/>Redressed<br \/>Reenie Beanie<br \/>Rochester<br \/>Rock Salt<br \/>Rokkitt<br \/>Ropa Sans<br \/>Rosario<br \/>Ruslan Display<br \/>Schoolbell<br \/>Shadows Into Light<br \/>Shanti<br \/>Short Stack<br \/>Sigmar One<br \/>Signika<br \/>Signika Negative<br \/>Six Caps<br \/>Slackey<br \/>Smokum<br \/>Smythe<br \/>Sniglet<br \/>Snippet<br \/>Special Elite<br \/>Stardos Stencil<br \/>Sue Ellen Francisco<br \/>Sunshiney<br \/>Swanky and Moo Moo<br \/>Syncopate<br \/>Tangerine<br \/>Telex<br \/>Tenor Sans<br \/>Terminal Dosis Light<br \/>The Girl Next Door<br \/>Tienne<br \/>Tinos<br \/>Tulpen One<br \/>Ubuntu<br \/>Ultra<br \/>UnifrakturCook<br \/>UnifrakturMaguntia<br \/>Unkempt<br \/>Unna<br \/>VT323<br \/>Varela<br \/>Varela Round<br \/>Vibur<br \/>Viga<br \/>Vidaloka<br \/>Volkhov<br \/>Vollkorn<br \/>Voltaire<br \/>Waiting for the Sunrise<br \/>Wallpoet<br \/>Walter Turncoat<br \/>Wire One<br \/>Yanone Kaffeesatz<br \/>Yellowtail<br \/>Yeseva One<br \/>Zeyada<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]family\" id=\"jformparamsmoduleparametersTabthemebtnfontfamily\" value=\"Carme\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemebtnfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Abel<\\\/div><div class=\\\"listelement\\\">Abril Fatface<\\\/div><div class=\\\"listelement\\\">Aclonica<\\\/div><div class=\\\"listelement\\\">Actor<\\\/div><div class=\\\"listelement\\\">Aldrich<\\\/div><div class=\\\"listelement\\\">Alice<\\\/div><div class=\\\"listelement\\\">Alike<\\\/div><div class=\\\"listelement\\\">Allan<\\\/div><div class=\\\"listelement\\\">Allerta<\\\/div><div class=\\\"listelement\\\">Allerta Stencil<\\\/div><div class=\\\"listelement\\\">Amaranth<\\\/div><div class=\\\"listelement\\\">Andika<\\\/div><div class=\\\"listelement\\\">Annie Use Your Telescope<\\\/div><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Antic<\\\/div><div class=\\\"listelement\\\">Anton<\\\/div><div class=\\\"listelement\\\">Architects Daughter<\\\/div><div class=\\\"listelement\\\">Arimo<\\\/div><div class=\\\"listelement\\\">Artifika<\\\/div><div class=\\\"listelement\\\">Arvo<\\\/div><div class=\\\"listelement\\\">Asap<\\\/div><div class=\\\"listelement\\\">Asul<\\\/div><div class=\\\"listelement\\\">Asset<\\\/div><div class=\\\"listelement\\\">Astloch<\\\/div><div class=\\\"listelement\\\">Aubrey<\\\/div><div class=\\\"listelement\\\">Bangers<\\\/div><div class=\\\"listelement\\\">Bentham<\\\/div><div class=\\\"listelement\\\">Bevan<\\\/div><div class=\\\"listelement\\\">Bigshot One<\\\/div><div class=\\\"listelement\\\">Black Ops One<\\\/div><div class=\\\"listelement\\\">Bowlby One<\\\/div><div class=\\\"listelement\\\">Bowlby One SC<\\\/div><div class=\\\"listelement\\\">Brawler<\\\/div><div class=\\\"listelement\\\">Buda<\\\/div><div class=\\\"listelement\\\">Cabin<\\\/div><div class=\\\"listelement\\\">Cabin Sketch<\\\/div><div class=\\\"listelement\\\">Calligraffitti<\\\/div><div class=\\\"listelement\\\">Candal<\\\/div><div class=\\\"listelement\\\">Cantarell<\\\/div><div class=\\\"listelement\\\">Cardo<\\\/div><div class=\\\"listelement\\\">Carme<\\\/div><div class=\\\"listelement\\\">Carter One<\\\/div><div class=\\\"listelement\\\">Caudex<\\\/div><div class=\\\"listelement\\\">Cedarville Cursive<\\\/div><div class=\\\"listelement\\\">Cherry Cream Soda<\\\/div><div class=\\\"listelement\\\">Chewy<\\\/div><div class=\\\"listelement\\\">Chivo<\\\/div><div class=\\\"listelement\\\">Coda<\\\/div><div class=\\\"listelement\\\">Coda Caption<\\\/div><div class=\\\"listelement\\\">Comfortaa<\\\/div><div class=\\\"listelement\\\">Coming Soon<\\\/div><div class=\\\"listelement\\\">Convergence<\\\/div><div class=\\\"listelement\\\">Copse<\\\/div><div class=\\\"listelement\\\">Corben<\\\/div><div class=\\\"listelement\\\">Cousine<\\\/div><div class=\\\"listelement\\\">Coustard<\\\/div><div class=\\\"listelement\\\">Covered By Your Grace<\\\/div><div class=\\\"listelement\\\">Crafty Girls<\\\/div><div class=\\\"listelement\\\">Crimson Text<\\\/div><div class=\\\"listelement\\\">Crushed<\\\/div><div class=\\\"listelement\\\">Cuprum<\\\/div><div class=\\\"listelement\\\">Damion<\\\/div><div class=\\\"listelement\\\">Dancing Script<\\\/div><div class=\\\"listelement\\\">Dawning of a New Day<\\\/div><div class=\\\"listelement\\\">Days One<\\\/div><div class=\\\"listelement\\\">Delius<\\\/div><div class=\\\"listelement\\\">Delius Swash Caps<\\\/div><div class=\\\"listelement\\\">Delius Unicase<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">Dorsa<\\\/div><div class=\\\"listelement\\\">Droid Sans<\\\/div><div class=\\\"listelement\\\">Droid Sans Mono<\\\/div><div class=\\\"listelement\\\">Droid Serif<\\\/div><div class=\\\"listelement\\\">EB Garamond<\\\/div><div class=\\\"listelement\\\">Exo<\\\/div><div class=\\\"listelement\\\">Expletus Sans<\\\/div><div class=\\\"listelement\\\">Fanwood Text<\\\/div><div class=\\\"listelement\\\">Federo<\\\/div><div class=\\\"listelement\\\">Fontdiner Swanky<\\\/div><div class=\\\"listelement\\\">Forum<\\\/div><div class=\\\"listelement\\\">Francois One<\\\/div><div class=\\\"listelement\\\">Gentium Basic<\\\/div><div class=\\\"listelement\\\">Gentium Book Basic<\\\/div><div class=\\\"listelement\\\">Geo<\\\/div><div class=\\\"listelement\\\">Geostar<\\\/div><div class=\\\"listelement\\\">Geostar Fill<\\\/div><div class=\\\"listelement\\\">Give You Glory<\\\/div><div class=\\\"listelement\\\">Gloria Hallelujah<\\\/div><div class=\\\"listelement\\\">Goblin One<\\\/div><div class=\\\"listelement\\\">Goudy Bookletter 1911<\\\/div><div class=\\\"listelement\\\">Gravitas One<\\\/div><div class=\\\"listelement\\\">Gruppo<\\\/div><div class=\\\"listelement\\\">Hammersmith One<\\\/div><div class=\\\"listelement\\\">Holtwood One SC<\\\/div><div class=\\\"listelement\\\">Homemade Apple<\\\/div><div class=\\\"listelement\\\">IM Fell DW Pica<\\\/div><div class=\\\"listelement\\\">IM Fell DW Pica SC<\\\/div><div class=\\\"listelement\\\">IM Fell Double Pica<\\\/div><div class=\\\"listelement\\\">IM Fell Double Pica SC<\\\/div><div class=\\\"listelement\\\">IM Fell English<\\\/div><div class=\\\"listelement\\\">IM Fell English SC<\\\/div><div class=\\\"listelement\\\">IM Fell French Canon<\\\/div><div class=\\\"listelement\\\">IM Fell French Canon SC<\\\/div><div class=\\\"listelement\\\">IM Fell Great Primer<\\\/div><div class=\\\"listelement\\\">IM Fell Great Primer SC<\\\/div><div class=\\\"listelement\\\">Inconsolata<\\\/div><div class=\\\"listelement\\\">Inder<\\\/div><div class=\\\"listelement\\\">Indie Flower<\\\/div><div class=\\\"listelement\\\">Irish Grover<\\\/div><div class=\\\"listelement\\\">Istok Web<\\\/div><div class=\\\"listelement\\\">Josefin Sans<\\\/div><div class=\\\"listelement\\\">Josefin Slab<\\\/div><div class=\\\"listelement\\\">Judson<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">Just Another Hand<\\\/div><div class=\\\"listelement\\\">Just Me Again Down Here<\\\/div><div class=\\\"listelement\\\">Kameron<\\\/div><div class=\\\"listelement\\\">Kelly Slab<\\\/div><div class=\\\"listelement\\\">Kenia<\\\/div><div class=\\\"listelement\\\">Kranky<\\\/div><div class=\\\"listelement\\\">Kreon<\\\/div><div class=\\\"listelement\\\">Kristi<\\\/div><div class=\\\"listelement\\\">La Belle Aurore<\\\/div><div class=\\\"listelement\\\">Lato<\\\/div><div class=\\\"listelement\\\">League Script<\\\/div><div class=\\\"listelement\\\">Leckerli One<\\\/div><div class=\\\"listelement\\\">Lekton<\\\/div><div class=\\\"listelement\\\">Limelight<\\\/div><div class=\\\"listelement\\\">Lobster<\\\/div><div class=\\\"listelement\\\">Lobster Two<\\\/div><div class=\\\"listelement\\\">Lora<\\\/div><div class=\\\"listelement\\\">Love Ya Like A Sister<\\\/div><div class=\\\"listelement\\\">Loved by the King<\\\/div><div class=\\\"listelement\\\">Luckiest Guy<\\\/div><div class=\\\"listelement\\\">Magra<\\\/div><div class=\\\"listelement\\\">Maiden Orange<\\\/div><div class=\\\"listelement\\\">Mako<\\\/div><div class=\\\"listelement\\\">Marvel<\\\/div><div class=\\\"listelement\\\">Maven Pro<\\\/div><div class=\\\"listelement\\\">Meddon<\\\/div><div class=\\\"listelement\\\">MedievalSharp<\\\/div><div class=\\\"listelement\\\">Megrim<\\\/div><div class=\\\"listelement\\\">Merriweather<\\\/div><div class=\\\"listelement\\\">Metrophobic<\\\/div><div class=\\\"listelement\\\">Michroma<\\\/div><div class=\\\"listelement\\\">Miltonian<\\\/div><div class=\\\"listelement\\\">Miltonian Tattoo<\\\/div><div class=\\\"listelement\\\">Modern Antiqua<\\\/div><div class=\\\"listelement\\\">Molengo<\\\/div><div class=\\\"listelement\\\">Monofett<\\\/div><div class=\\\"listelement\\\">Monoton<\\\/div><div class=\\\"listelement\\\">Montez<\\\/div><div class=\\\"listelement\\\">Mountains of Christmas<\\\/div><div class=\\\"listelement\\\">Muli<\\\/div><div class=\\\"listelement\\\">Neucha<\\\/div><div class=\\\"listelement\\\">Neuton<\\\/div><div class=\\\"listelement\\\">News Cycle<\\\/div><div class=\\\"listelement\\\">Nixie One<\\\/div><div class=\\\"listelement\\\">Nobile<\\\/div><div class=\\\"listelement\\\">Nothing You Could Do<\\\/div><div class=\\\"listelement\\\">Nova Cut<\\\/div><div class=\\\"listelement\\\">Nova Flat<\\\/div><div class=\\\"listelement\\\">Nova Mono<\\\/div><div class=\\\"listelement\\\">Nova Oval<\\\/div><div class=\\\"listelement\\\">Nova Round<\\\/div><div class=\\\"listelement\\\">Nova Script<\\\/div><div class=\\\"listelement\\\">Nova Slim<\\\/div><div class=\\\"listelement\\\">Nova Square<\\\/div><div class=\\\"listelement\\\">Numans<\\\/div><div class=\\\"listelement\\\">Nunito<\\\/div><div class=\\\"listelement\\\">OFL Sorts Mill Goudy TT<\\\/div><div class=\\\"listelement\\\">Old Standard TT<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Orbitron<\\\/div><div class=\\\"listelement\\\">Oswald<\\\/div><div class=\\\"listelement\\\">Over the Rainbow<\\\/div><div class=\\\"listelement\\\">Ovo<\\\/div><div class=\\\"listelement\\\">PT Sans<\\\/div><div class=\\\"listelement\\\">PT Sans Caption<\\\/div><div class=\\\"listelement\\\">PT Sans Narrow<\\\/div><div class=\\\"listelement\\\">PT Serif<\\\/div><div class=\\\"listelement\\\">PT Serif Caption<\\\/div><div class=\\\"listelement\\\">Pacifico<\\\/div><div class=\\\"listelement\\\">Passero One<\\\/div><div class=\\\"listelement\\\">Patrick Hand<\\\/div><div class=\\\"listelement\\\">Paytone One<\\\/div><div class=\\\"listelement\\\">Permanent Marker<\\\/div><div class=\\\"listelement\\\">Philosopher<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">Playfair Display<\\\/div><div class=\\\"listelement\\\">Podkova<\\\/div><div class=\\\"listelement\\\">Pompiere<\\\/div><div class=\\\"listelement\\\">Prociono<\\\/div><div class=\\\"listelement\\\">Puritan<\\\/div><div class=\\\"listelement\\\">Quattrocento<\\\/div><div class=\\\"listelement\\\">Quattrocento Sans<\\\/div><div class=\\\"listelement\\\">Questrial<\\\/div><div class=\\\"listelement\\\">Quicksand<\\\/div><div class=\\\"listelement\\\">Radley<\\\/div><div class=\\\"listelement\\\">Raleway<\\\/div><div class=\\\"listelement\\\">Rationale<\\\/div><div class=\\\"listelement\\\">Redressed<\\\/div><div class=\\\"listelement\\\">Reenie Beanie<\\\/div><div class=\\\"listelement\\\">Rochester<\\\/div><div class=\\\"listelement\\\">Rock Salt<\\\/div><div class=\\\"listelement\\\">Rokkitt<\\\/div><div class=\\\"listelement\\\">Ropa Sans<\\\/div><div class=\\\"listelement\\\">Rosario<\\\/div><div class=\\\"listelement\\\">Ruslan Display<\\\/div><div class=\\\"listelement\\\">Schoolbell<\\\/div><div class=\\\"listelement\\\">Shadows Into Light<\\\/div><div class=\\\"listelement\\\">Shanti<\\\/div><div class=\\\"listelement\\\">Short Stack<\\\/div><div class=\\\"listelement\\\">Sigmar One<\\\/div><div class=\\\"listelement\\\">Signika<\\\/div><div class=\\\"listelement\\\">Signika Negative<\\\/div><div class=\\\"listelement\\\">Six Caps<\\\/div><div class=\\\"listelement\\\">Slackey<\\\/div><div class=\\\"listelement\\\">Smokum<\\\/div><div class=\\\"listelement\\\">Smythe<\\\/div><div class=\\\"listelement\\\">Sniglet<\\\/div><div class=\\\"listelement\\\">Snippet<\\\/div><div class=\\\"listelement\\\">Special Elite<\\\/div><div class=\\\"listelement\\\">Stardos Stencil<\\\/div><div class=\\\"listelement\\\">Sue Ellen Francisco<\\\/div><div class=\\\"listelement\\\">Sunshiney<\\\/div><div class=\\\"listelement\\\">Swanky and Moo Moo<\\\/div><div class=\\\"listelement\\\">Syncopate<\\\/div><div class=\\\"listelement\\\">Tangerine<\\\/div><div class=\\\"listelement\\\">Telex<\\\/div><div class=\\\"listelement\\\">Tenor Sans<\\\/div><div class=\\\"listelement\\\">Terminal Dosis Light<\\\/div><div class=\\\"listelement\\\">The Girl Next Door<\\\/div><div class=\\\"listelement\\\">Tienne<\\\/div><div class=\\\"listelement\\\">Tinos<\\\/div><div class=\\\"listelement\\\">Tulpen One<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><div class=\\\"listelement\\\">Ultra<\\\/div><div class=\\\"listelement\\\">UnifrakturCook<\\\/div><div class=\\\"listelement\\\">UnifrakturMaguntia<\\\/div><div class=\\\"listelement\\\">Unkempt<\\\/div><div class=\\\"listelement\\\">Unna<\\\/div><div class=\\\"listelement\\\">VT323<\\\/div><div class=\\\"listelement\\\">Varela<\\\/div><div class=\\\"listelement\\\">Varela Round<\\\/div><div class=\\\"listelement\\\">Vibur<\\\/div><div class=\\\"listelement\\\">Viga<\\\/div><div class=\\\"listelement\\\">Vidaloka<\\\/div><div class=\\\"listelement\\\">Volkhov<\\\/div><div class=\\\"listelement\\\">Vollkorn<\\\/div><div class=\\\"listelement\\\">Voltaire<\\\/div><div class=\\\"listelement\\\">Waiting for the Sunrise<\\\/div><div class=\\\"listelement\\\">Wallpoet<\\\/div><div class=\\\"listelement\\\">Walter Turncoat<\\\/div><div class=\\\"listelement\\\">Wire One<\\\/div><div class=\\\"listelement\\\">Yanone Kaffeesatz<\\\/div><div class=\\\"listelement\\\">Yellowtail<\\\/div><div class=\\\"listelement\\\">Yeseva One<\\\/div><div class=\\\"listelement\\\">Zeyada<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Abel\",\"text\":\"Abel\"},{\"value\":\"Abril Fatface\",\"text\":\"Abril Fatface\"},{\"value\":\"Aclonica\",\"text\":\"Aclonica\"},{\"value\":\"Actor\",\"text\":\"Actor\"},{\"value\":\"Aldrich\",\"text\":\"Aldrich\"},{\"value\":\"Alice\",\"text\":\"Alice\"},{\"value\":\"Alike\",\"text\":\"Alike\"},{\"value\":\"Allan\",\"text\":\"Allan\"},{\"value\":\"Allerta\",\"text\":\"Allerta\"},{\"value\":\"Allerta Stencil\",\"text\":\"Allerta Stencil\"},{\"value\":\"Amaranth\",\"text\":\"Amaranth\"},{\"value\":\"Andika\",\"text\":\"Andika\"},{\"value\":\"Annie Use Your Telescope\",\"text\":\"Annie Use Your Telescope\"},{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Antic\",\"text\":\"Antic\"},{\"value\":\"Anton\",\"text\":\"Anton\"},{\"value\":\"Architects Daughter\",\"text\":\"Architects Daughter\"},{\"value\":\"Arimo\",\"text\":\"Arimo\"},{\"value\":\"Artifika\",\"text\":\"Artifika\"},{\"value\":\"Arvo\",\"text\":\"Arvo\"},{\"value\":\"Asap\",\"text\":\"Asap\"},{\"value\":\"Asul\",\"text\":\"Asul\"},{\"value\":\"Asset\",\"text\":\"Asset\"},{\"value\":\"Astloch\",\"text\":\"Astloch\"},{\"value\":\"Aubrey\",\"text\":\"Aubrey\"},{\"value\":\"Bangers\",\"text\":\"Bangers\"},{\"value\":\"Bentham\",\"text\":\"Bentham\"},{\"value\":\"Bevan\",\"text\":\"Bevan\"},{\"value\":\"Bigshot One\",\"text\":\"Bigshot One\"},{\"value\":\"Black Ops One\",\"text\":\"Black Ops One\"},{\"value\":\"Bowlby One\",\"text\":\"Bowlby One\"},{\"value\":\"Bowlby One SC\",\"text\":\"Bowlby One SC\"},{\"value\":\"Brawler\",\"text\":\"Brawler\"},{\"value\":\"Buda\",\"text\":\"Buda\"},{\"value\":\"Cabin\",\"text\":\"Cabin\"},{\"value\":\"Cabin Sketch\",\"text\":\"Cabin Sketch\"},{\"value\":\"Calligraffitti\",\"text\":\"Calligraffitti\"},{\"value\":\"Candal\",\"text\":\"Candal\"},{\"value\":\"Cantarell\",\"text\":\"Cantarell\"},{\"value\":\"Cardo\",\"text\":\"Cardo\"},{\"value\":\"Carme\",\"text\":\"Carme\"},{\"value\":\"Carter One\",\"text\":\"Carter One\"},{\"value\":\"Caudex\",\"text\":\"Caudex\"},{\"value\":\"Cedarville Cursive\",\"text\":\"Cedarville Cursive\"},{\"value\":\"Cherry Cream Soda\",\"text\":\"Cherry Cream Soda\"},{\"value\":\"Chewy\",\"text\":\"Chewy\"},{\"value\":\"Chivo\",\"text\":\"Chivo\"},{\"value\":\"Coda\",\"text\":\"Coda\"},{\"value\":\"Coda Caption\",\"text\":\"Coda Caption\"},{\"value\":\"Comfortaa\",\"text\":\"Comfortaa\"},{\"value\":\"Coming Soon\",\"text\":\"Coming Soon\"},{\"value\":\"Convergence\",\"text\":\"Convergence\"},{\"value\":\"Copse\",\"text\":\"Copse\"},{\"value\":\"Corben\",\"text\":\"Corben\"},{\"value\":\"Cousine\",\"text\":\"Cousine\"},{\"value\":\"Coustard\",\"text\":\"Coustard\"},{\"value\":\"Covered By Your Grace\",\"text\":\"Covered By Your Grace\"},{\"value\":\"Crafty Girls\",\"text\":\"Crafty Girls\"},{\"value\":\"Crimson Text\",\"text\":\"Crimson Text\"},{\"value\":\"Crushed\",\"text\":\"Crushed\"},{\"value\":\"Cuprum\",\"text\":\"Cuprum\"},{\"value\":\"Damion\",\"text\":\"Damion\"},{\"value\":\"Dancing Script\",\"text\":\"Dancing Script\"},{\"value\":\"Dawning of a New Day\",\"text\":\"Dawning of a New Day\"},{\"value\":\"Days One\",\"text\":\"Days One\"},{\"value\":\"Delius\",\"text\":\"Delius\"},{\"value\":\"Delius Swash Caps\",\"text\":\"Delius Swash Caps\"},{\"value\":\"Delius Unicase\",\"text\":\"Delius Unicase\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"Dorsa\",\"text\":\"Dorsa\"},{\"value\":\"Droid Sans\",\"text\":\"Droid Sans\"},{\"value\":\"Droid Sans Mono\",\"text\":\"Droid Sans Mono\"},{\"value\":\"Droid Serif\",\"text\":\"Droid Serif\"},{\"value\":\"EB Garamond\",\"text\":\"EB Garamond\"},{\"value\":\"Exo\",\"text\":\"Exo\"},{\"value\":\"Expletus Sans\",\"text\":\"Expletus Sans\"},{\"value\":\"Fanwood Text\",\"text\":\"Fanwood Text\"},{\"value\":\"Federo\",\"text\":\"Federo\"},{\"value\":\"Fontdiner Swanky\",\"text\":\"Fontdiner Swanky\"},{\"value\":\"Forum\",\"text\":\"Forum\"},{\"value\":\"Francois One\",\"text\":\"Francois One\"},{\"value\":\"Gentium Basic\",\"text\":\"Gentium Basic\"},{\"value\":\"Gentium Book Basic\",\"text\":\"Gentium Book Basic\"},{\"value\":\"Geo\",\"text\":\"Geo\"},{\"value\":\"Geostar\",\"text\":\"Geostar\"},{\"value\":\"Geostar Fill\",\"text\":\"Geostar Fill\"},{\"value\":\"Give You Glory\",\"text\":\"Give You Glory\"},{\"value\":\"Gloria Hallelujah\",\"text\":\"Gloria Hallelujah\"},{\"value\":\"Goblin One\",\"text\":\"Goblin One\"},{\"value\":\"Goudy Bookletter 1911\",\"text\":\"Goudy Bookletter 1911\"},{\"value\":\"Gravitas One\",\"text\":\"Gravitas One\"},{\"value\":\"Gruppo\",\"text\":\"Gruppo\"},{\"value\":\"Hammersmith One\",\"text\":\"Hammersmith One\"},{\"value\":\"Holtwood One SC\",\"text\":\"Holtwood One SC\"},{\"value\":\"Homemade Apple\",\"text\":\"Homemade Apple\"},{\"value\":\"IM Fell DW Pica\",\"text\":\"IM Fell DW Pica\"},{\"value\":\"IM Fell DW Pica SC\",\"text\":\"IM Fell DW Pica SC\"},{\"value\":\"IM Fell Double Pica\",\"text\":\"IM Fell Double Pica\"},{\"value\":\"IM Fell Double Pica SC\",\"text\":\"IM Fell Double Pica SC\"},{\"value\":\"IM Fell English\",\"text\":\"IM Fell English\"},{\"value\":\"IM Fell English SC\",\"text\":\"IM Fell English SC\"},{\"value\":\"IM Fell French Canon\",\"text\":\"IM Fell French Canon\"},{\"value\":\"IM Fell French Canon SC\",\"text\":\"IM Fell French Canon SC\"},{\"value\":\"IM Fell Great Primer\",\"text\":\"IM Fell Great Primer\"},{\"value\":\"IM Fell Great Primer SC\",\"text\":\"IM Fell Great Primer SC\"},{\"value\":\"Inconsolata\",\"text\":\"Inconsolata\"},{\"value\":\"Inder\",\"text\":\"Inder\"},{\"value\":\"Indie Flower\",\"text\":\"Indie Flower\"},{\"value\":\"Irish Grover\",\"text\":\"Irish Grover\"},{\"value\":\"Istok Web\",\"text\":\"Istok Web\"},{\"value\":\"Josefin Sans\",\"text\":\"Josefin Sans\"},{\"value\":\"Josefin Slab\",\"text\":\"Josefin Slab\"},{\"value\":\"Judson\",\"text\":\"Judson\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"Just Another Hand\",\"text\":\"Just Another Hand\"},{\"value\":\"Just Me Again Down Here\",\"text\":\"Just Me Again Down Here\"},{\"value\":\"Kameron\",\"text\":\"Kameron\"},{\"value\":\"Kelly Slab\",\"text\":\"Kelly Slab\"},{\"value\":\"Kenia\",\"text\":\"Kenia\"},{\"value\":\"Kranky\",\"text\":\"Kranky\"},{\"value\":\"Kreon\",\"text\":\"Kreon\"},{\"value\":\"Kristi\",\"text\":\"Kristi\"},{\"value\":\"La Belle Aurore\",\"text\":\"La Belle Aurore\"},{\"value\":\"Lato\",\"text\":\"Lato\"},{\"value\":\"League Script\",\"text\":\"League Script\"},{\"value\":\"Leckerli One\",\"text\":\"Leckerli One\"},{\"value\":\"Lekton\",\"text\":\"Lekton\"},{\"value\":\"Limelight\",\"text\":\"Limelight\"},{\"value\":\"Lobster\",\"text\":\"Lobster\"},{\"value\":\"Lobster Two\",\"text\":\"Lobster Two\"},{\"value\":\"Lora\",\"text\":\"Lora\"},{\"value\":\"Love Ya Like A Sister\",\"text\":\"Love Ya Like A Sister\"},{\"value\":\"Loved by the King\",\"text\":\"Loved by the King\"},{\"value\":\"Luckiest Guy\",\"text\":\"Luckiest Guy\"},{\"value\":\"Magra\",\"text\":\"Magra\"},{\"value\":\"Maiden Orange\",\"text\":\"Maiden Orange\"},{\"value\":\"Mako\",\"text\":\"Mako\"},{\"value\":\"Marvel\",\"text\":\"Marvel\"},{\"value\":\"Maven Pro\",\"text\":\"Maven Pro\"},{\"value\":\"Meddon\",\"text\":\"Meddon\"},{\"value\":\"MedievalSharp\",\"text\":\"MedievalSharp\"},{\"value\":\"Megrim\",\"text\":\"Megrim\"},{\"value\":\"Merriweather\",\"text\":\"Merriweather\"},{\"value\":\"Metrophobic\",\"text\":\"Metrophobic\"},{\"value\":\"Michroma\",\"text\":\"Michroma\"},{\"value\":\"Miltonian\",\"text\":\"Miltonian\"},{\"value\":\"Miltonian Tattoo\",\"text\":\"Miltonian Tattoo\"},{\"value\":\"Modern Antiqua\",\"text\":\"Modern Antiqua\"},{\"value\":\"Molengo\",\"text\":\"Molengo\"},{\"value\":\"Monofett\",\"text\":\"Monofett\"},{\"value\":\"Monoton\",\"text\":\"Monoton\"},{\"value\":\"Montez\",\"text\":\"Montez\"},{\"value\":\"Mountains of Christmas\",\"text\":\"Mountains of Christmas\"},{\"value\":\"Muli\",\"text\":\"Muli\"},{\"value\":\"Neucha\",\"text\":\"Neucha\"},{\"value\":\"Neuton\",\"text\":\"Neuton\"},{\"value\":\"News Cycle\",\"text\":\"News Cycle\"},{\"value\":\"Nixie One\",\"text\":\"Nixie One\"},{\"value\":\"Nobile\",\"text\":\"Nobile\"},{\"value\":\"Nothing You Could Do\",\"text\":\"Nothing You Could Do\"},{\"value\":\"Nova Cut\",\"text\":\"Nova Cut\"},{\"value\":\"Nova Flat\",\"text\":\"Nova Flat\"},{\"value\":\"Nova Mono\",\"text\":\"Nova Mono\"},{\"value\":\"Nova Oval\",\"text\":\"Nova Oval\"},{\"value\":\"Nova Round\",\"text\":\"Nova Round\"},{\"value\":\"Nova Script\",\"text\":\"Nova Script\"},{\"value\":\"Nova Slim\",\"text\":\"Nova Slim\"},{\"value\":\"Nova Square\",\"text\":\"Nova Square\"},{\"value\":\"Numans\",\"text\":\"Numans\"},{\"value\":\"Nunito\",\"text\":\"Nunito\"},{\"value\":\"OFL Sorts Mill Goudy TT\",\"text\":\"OFL Sorts Mill Goudy TT\"},{\"value\":\"Old Standard TT\",\"text\":\"Old Standard TT\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Orbitron\",\"text\":\"Orbitron\"},{\"value\":\"Oswald\",\"text\":\"Oswald\"},{\"value\":\"Over the Rainbow\",\"text\":\"Over the Rainbow\"},{\"value\":\"Ovo\",\"text\":\"Ovo\"},{\"value\":\"PT Sans\",\"text\":\"PT Sans\"},{\"value\":\"PT Sans Caption\",\"text\":\"PT Sans Caption\"},{\"value\":\"PT Sans Narrow\",\"text\":\"PT Sans Narrow\"},{\"value\":\"PT Serif\",\"text\":\"PT Serif\"},{\"value\":\"PT Serif Caption\",\"text\":\"PT Serif Caption\"},{\"value\":\"Pacifico\",\"text\":\"Pacifico\"},{\"value\":\"Passero One\",\"text\":\"Passero One\"},{\"value\":\"Patrick Hand\",\"text\":\"Patrick Hand\"},{\"value\":\"Paytone One\",\"text\":\"Paytone One\"},{\"value\":\"Permanent Marker\",\"text\":\"Permanent Marker\"},{\"value\":\"Philosopher\",\"text\":\"Philosopher\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"Playfair Display\",\"text\":\"Playfair Display\"},{\"value\":\"Podkova\",\"text\":\"Podkova\"},{\"value\":\"Pompiere\",\"text\":\"Pompiere\"},{\"value\":\"Prociono\",\"text\":\"Prociono\"},{\"value\":\"Puritan\",\"text\":\"Puritan\"},{\"value\":\"Quattrocento\",\"text\":\"Quattrocento\"},{\"value\":\"Quattrocento Sans\",\"text\":\"Quattrocento Sans\"},{\"value\":\"Questrial\",\"text\":\"Questrial\"},{\"value\":\"Quicksand\",\"text\":\"Quicksand\"},{\"value\":\"Radley\",\"text\":\"Radley\"},{\"value\":\"Raleway\",\"text\":\"Raleway\"},{\"value\":\"Rationale\",\"text\":\"Rationale\"},{\"value\":\"Redressed\",\"text\":\"Redressed\"},{\"value\":\"Reenie Beanie\",\"text\":\"Reenie Beanie\"},{\"value\":\"Rochester\",\"text\":\"Rochester\"},{\"value\":\"Rock Salt\",\"text\":\"Rock Salt\"},{\"value\":\"Rokkitt\",\"text\":\"Rokkitt\"},{\"value\":\"Ropa Sans\",\"text\":\"Ropa Sans\"},{\"value\":\"Rosario\",\"text\":\"Rosario\"},{\"value\":\"Ruslan Display\",\"text\":\"Ruslan Display\"},{\"value\":\"Schoolbell\",\"text\":\"Schoolbell\"},{\"value\":\"Shadows Into Light\",\"text\":\"Shadows Into Light\"},{\"value\":\"Shanti\",\"text\":\"Shanti\"},{\"value\":\"Short Stack\",\"text\":\"Short Stack\"},{\"value\":\"Sigmar One\",\"text\":\"Sigmar One\"},{\"value\":\"Signika\",\"text\":\"Signika\"},{\"value\":\"Signika Negative\",\"text\":\"Signika Negative\"},{\"value\":\"Six Caps\",\"text\":\"Six Caps\"},{\"value\":\"Slackey\",\"text\":\"Slackey\"},{\"value\":\"Smokum\",\"text\":\"Smokum\"},{\"value\":\"Smythe\",\"text\":\"Smythe\"},{\"value\":\"Sniglet\",\"text\":\"Sniglet\"},{\"value\":\"Snippet\",\"text\":\"Snippet\"},{\"value\":\"Special Elite\",\"text\":\"Special Elite\"},{\"value\":\"Stardos Stencil\",\"text\":\"Stardos Stencil\"},{\"value\":\"Sue Ellen Francisco\",\"text\":\"Sue Ellen Francisco\"},{\"value\":\"Sunshiney\",\"text\":\"Sunshiney\"},{\"value\":\"Swanky and Moo Moo\",\"text\":\"Swanky and Moo Moo\"},{\"value\":\"Syncopate\",\"text\":\"Syncopate\"},{\"value\":\"Tangerine\",\"text\":\"Tangerine\"},{\"value\":\"Telex\",\"text\":\"Telex\"},{\"value\":\"Tenor Sans\",\"text\":\"Tenor Sans\"},{\"value\":\"Terminal Dosis Light\",\"text\":\"Terminal Dosis Light\"},{\"value\":\"The Girl Next Door\",\"text\":\"The Girl Next Door\"},{\"value\":\"Tienne\",\"text\":\"Tienne\"},{\"value\":\"Tinos\",\"text\":\"Tinos\"},{\"value\":\"Tulpen One\",\"text\":\"Tulpen One\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"},{\"value\":\"Ultra\",\"text\":\"Ultra\"},{\"value\":\"UnifrakturCook\",\"text\":\"UnifrakturCook\"},{\"value\":\"UnifrakturMaguntia\",\"text\":\"UnifrakturMaguntia\"},{\"value\":\"Unkempt\",\"text\":\"Unkempt\"},{\"value\":\"Unna\",\"text\":\"Unna\"},{\"value\":\"VT323\",\"text\":\"VT323\"},{\"value\":\"Varela\",\"text\":\"Varela\"},{\"value\":\"Varela Round\",\"text\":\"Varela Round\"},{\"value\":\"Vibur\",\"text\":\"Vibur\"},{\"value\":\"Viga\",\"text\":\"Viga\"},{\"value\":\"Vidaloka\",\"text\":\"Vidaloka\"},{\"value\":\"Volkhov\",\"text\":\"Volkhov\"},{\"value\":\"Vollkorn\",\"text\":\"Vollkorn\"},{\"value\":\"Voltaire\",\"text\":\"Voltaire\"},{\"value\":\"Waiting for the Sunrise\",\"text\":\"Waiting for the Sunrise\"},{\"value\":\"Wallpoet\",\"text\":\"Wallpoet\"},{\"value\":\"Walter Turncoat\",\"text\":\"Walter Turncoat\"},{\"value\":\"Wire One\",\"text\":\"Wire One\"},{\"value\":\"Yanone Kaffeesatz\",\"text\":\"Yanone Kaffeesatz\"},{\"value\":\"Yellowtail\",\"text\":\"Yellowtail\"},{\"value\":\"Yeseva One\",\"text\":\"Yeseva One\"},{\"value\":\"Zeyada\",\"text\":\"Zeyada\"}],\n        selectedIndex: 40,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"LatinExtended":{"name":"jform[params][moduleparametersTab][theme][btnfont]family","id":"jformparamsmoduleparametersTabthemebtnfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemebtnfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Andika<br \/>Andika<br \/>Anonymous Pro<br \/>Anton<br \/>Caudex<br \/>Didact Gothic<br \/>EB Garamond<br \/>Forum<br \/>Francois One<br \/>Gentium Basic<br \/>Gentium Book Basic<br \/>Istok Web<br \/>Jura<br \/>Kelly Slab<br \/>Lobster<br \/>MedievalSharp<br \/>Modern Antiqua<br \/>Neuton<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Patrick Hand<br \/>Play<br \/>Ruslan Display<br \/>Tenor Sans<br \/>Ubuntu<br \/>Varela<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]family\" id=\"jformparamsmoduleparametersTabthemebtnfontfamily\" value=\"Andika\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemebtnfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Andika<\\\/div><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Anton<\\\/div><div class=\\\"listelement\\\">Caudex<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">EB Garamond<\\\/div><div class=\\\"listelement\\\">Forum<\\\/div><div class=\\\"listelement\\\">Francois One<\\\/div><div class=\\\"listelement\\\">Gentium Basic<\\\/div><div class=\\\"listelement\\\">Gentium Book Basic<\\\/div><div class=\\\"listelement\\\">Istok Web<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">Kelly Slab<\\\/div><div class=\\\"listelement\\\">Lobster<\\\/div><div class=\\\"listelement\\\">MedievalSharp<\\\/div><div class=\\\"listelement\\\">Modern Antiqua<\\\/div><div class=\\\"listelement\\\">Neuton<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Patrick Hand<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">Ruslan Display<\\\/div><div class=\\\"listelement\\\">Tenor Sans<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><div class=\\\"listelement\\\">Varela<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Andika\",\"text\":\"Andika\"},{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Anton\",\"text\":\"Anton\"},{\"value\":\"Caudex\",\"text\":\"Caudex\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"EB Garamond\",\"text\":\"EB Garamond\"},{\"value\":\"Forum\",\"text\":\"Forum\"},{\"value\":\"Francois One\",\"text\":\"Francois One\"},{\"value\":\"Gentium Basic\",\"text\":\"Gentium Basic\"},{\"value\":\"Gentium Book Basic\",\"text\":\"Gentium Book Basic\"},{\"value\":\"Istok Web\",\"text\":\"Istok Web\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"Kelly Slab\",\"text\":\"Kelly Slab\"},{\"value\":\"Lobster\",\"text\":\"Lobster\"},{\"value\":\"MedievalSharp\",\"text\":\"MedievalSharp\"},{\"value\":\"Modern Antiqua\",\"text\":\"Modern Antiqua\"},{\"value\":\"Neuton\",\"text\":\"Neuton\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Patrick Hand\",\"text\":\"Patrick Hand\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"Ruslan Display\",\"text\":\"Ruslan Display\"},{\"value\":\"Tenor Sans\",\"text\":\"Tenor Sans\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"},{\"value\":\"Varela\",\"text\":\"Varela\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"Vietnamese":{"name":"jform[params][moduleparametersTab][theme][btnfont]family","id":"jformparamsmoduleparametersTabthemebtnfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemebtnfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">EB Garamond<br \/>EB Garamond<br \/>Open Sans<br \/>Open Sans Condensed<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]family\" id=\"jformparamsmoduleparametersTabthemebtnfontfamily\" value=\"EB Garamond\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemebtnfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">EB Garamond<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><\\\/div>\",\n        options: [{\"value\":\"EB Garamond\",\"text\":\"EB Garamond\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemebtnfonttype\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Latin<br \/>Alternative fonts<br \/>Cyrillic<br \/>CyrillicExtended<br \/>Greek<br \/>GreekExtended<br \/>Khmer<br \/>Latin<br \/>LatinExtended<br \/>Vietnamese<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]type\" id=\"jformparamsmoduleparametersTabthemebtnfonttype\" value=\"Latin\"\/><\/div><\/div>"},"size":{"name":"jform[params][moduleparametersTab][theme][btnfont]size","id":"jformparamsmoduleparametersTabthemebtnfontsize","html":"<div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemebtnfontsize\"><input  size=\"1\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemebtnfontsizeinput\" value=\"12\"><div class=\"offlajntext_increment\">\n                <div class=\"offlajntext_increment_up arrow\"><\/div>\n                <div class=\"offlajntext_increment_down arrow\"><\/div>\n      <\/div><\/div><div class=\"offlajnswitcher\">\r\n            <div class=\"offlajnswitcher_inner\" id=\"offlajnswitcher_innerjformparamsmoduleparametersTabthemebtnfontsizeunit\"><\/div>\r\n    <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]size[unit]\" id=\"jformparamsmoduleparametersTabthemebtnfontsizeunit\" value=\"px\" \/><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]size\" id=\"jformparamsmoduleparametersTabthemebtnfontsize\" value=\"12||px\">"},"color":{"name":"jform[params][moduleparametersTab][theme][btnfont]color","id":"jformparamsmoduleparametersTabthemebtnfontcolor","html":"<div class=\"offlajncolor\"><input type=\"text\" name=\"jform[params][moduleparametersTab][theme][btnfont]color\" id=\"jformparamsmoduleparametersTabthemebtnfontcolor\" value=\"ffffff\" class=\"color wa\" size=\"12\" \/><\/div>"},"bold":{"name":"jform[params][moduleparametersTab][theme][btnfont]bold","id":"jformparamsmoduleparametersTabthemebtnfontbold","html":"<div id=\"offlajnonoffjformparamsmoduleparametersTabthemebtnfontbold\" class=\"gk_hack onoffbutton\">\n                <div class=\"gk_hack onoffbutton_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnonoff\/images\/bold.png);\"><\/div>\n      <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]bold\" id=\"jformparamsmoduleparametersTabthemebtnfontbold\" value=\"0\" \/>"},"italic":{"name":"jform[params][moduleparametersTab][theme][btnfont]italic","id":"jformparamsmoduleparametersTabthemebtnfontitalic","html":"<div id=\"offlajnonoffjformparamsmoduleparametersTabthemebtnfontitalic\" class=\"gk_hack onoffbutton\">\n                <div class=\"gk_hack onoffbutton_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnonoff\/images\/italic.png);\"><\/div>\n      <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]italic\" id=\"jformparamsmoduleparametersTabthemebtnfontitalic\" value=\"0\" \/>"},"underline":{"name":"jform[params][moduleparametersTab][theme][btnfont]underline","id":"jformparamsmoduleparametersTabthemebtnfontunderline","html":"<div id=\"offlajnonoffjformparamsmoduleparametersTabthemebtnfontunderline\" class=\"gk_hack onoffbutton\">\n                <div class=\"gk_hack onoffbutton_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnonoff\/images\/underline.png);\"><\/div>\n      <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]underline\" id=\"jformparamsmoduleparametersTabthemebtnfontunderline\" value=\"0\" \/>"},"align":{"name":"jform[params][moduleparametersTab][theme][btnfont]align","id":"jformparamsmoduleparametersTabthemebtnfontalign","html":"<div class=\"offlajnradiocontainerimage\" id=\"offlajnradiocontainerjformparamsmoduleparametersTabthemebtnfontalign\"><div class=\"radioelement first selected\"><div class=\"radioelement_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnradio\/images\/left_align.png);\"><\/div><\/div><div class=\"radioelement \"><div class=\"radioelement_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnradio\/images\/center_align.png);\"><\/div><\/div><div class=\"radioelement  last\"><div class=\"radioelement_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnradio\/images\/right_align.png);\"><\/div><\/div><div class=\"clear\"><\/div><\/div><input type=\"hidden\" id=\"jformparamsmoduleparametersTabthemebtnfontalign\" name=\"jform[params][moduleparametersTab][theme][btnfont]align\" value=\"left\"\/>"},"afont":{"name":"jform[params][moduleparametersTab][theme][btnfont]afont","id":"jformparamsmoduleparametersTabthemebtnfontafont","html":"<div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemebtnfontafont\"><input  size=\"10\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemebtnfontafontinput\" value=\"Helvetica\"><\/div><div class=\"offlajnswitcher\">\r\n            <div class=\"offlajnswitcher_inner\" id=\"offlajnswitcher_innerjformparamsmoduleparametersTabthemebtnfontafontunit\"><\/div>\r\n    <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]afont[unit]\" id=\"jformparamsmoduleparametersTabthemebtnfontafontunit\" value=\"1\" \/><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]afont\" id=\"jformparamsmoduleparametersTabthemebtnfontafont\" value=\"Helvetica||1\">"},"tshadow":{"name":"jform[params][moduleparametersTab][theme][btnfont]tshadow","id":"jformparamsmoduleparametersTabthemebtnfonttshadow","html":"<div id=\"offlajncombine_outerjformparamsmoduleparametersTabthemebtnfonttshadow\" class=\"offlajncombine_outer\"><div class=\"offlajncombinefieldcontainer\"><div class=\"offlajncombinefield\"><div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemebtnfonttshadow0\"><input  size=\"1\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemebtnfonttshadow0input\" value=\"1\"><div class=\"unit\">px<\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]tshadow0\" id=\"jformparamsmoduleparametersTabthemebtnfonttshadow0\" value=\"1||px\"><\/div><\/div><div class=\"offlajncombinefieldcontainer\"><div class=\"offlajncombinefield\"><div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemebtnfonttshadow1\"><input  size=\"1\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemebtnfonttshadow1input\" value=\"1\"><div class=\"unit\">px<\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]tshadow1\" id=\"jformparamsmoduleparametersTabthemebtnfonttshadow1\" value=\"1||px\"><\/div><\/div><div class=\"offlajncombinefieldcontainer\"><div class=\"offlajncombinefield\"><div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemebtnfonttshadow2\"><input  size=\"1\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemebtnfonttshadow2input\" value=\"0\"><div class=\"unit\">px<\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]tshadow2\" id=\"jformparamsmoduleparametersTabthemebtnfonttshadow2\" value=\"0\"><\/div><\/div><div class=\"offlajncombinefieldcontainer\"><div class=\"offlajncombinefield\"><div class=\"offlajncolor\"><input type=\"text\" name=\"jform[params][moduleparametersTab][theme][btnfont]tshadow3\" id=\"jformparamsmoduleparametersTabthemebtnfonttshadow3\" value=\"000000b3\" class=\"color \" size=\"12\" \/><\/div><\/div><\/div><div class=\"offlajncombinefieldcontainer\"><div class=\"offlajncombinefield\"><div class=\"offlajnswitcher\">\r\n            <div class=\"offlajnswitcher_inner\" id=\"offlajnswitcher_innerjformparamsmoduleparametersTabthemebtnfonttshadow4\"><\/div>\r\n    <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]tshadow4\" id=\"jformparamsmoduleparametersTabthemebtnfonttshadow4\" value=\"1\" \/><\/div><\/div><div class=\"offlajncombine_hider\"><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]tshadow\" id=\"jformparamsmoduleparametersTabthemebtnfonttshadow\" value='1||px|*|1||px|*|0|*|000000b3|*|1|*|'>"},"lineheight":{"name":"jform[params][moduleparametersTab][theme][btnfont]lineheight","id":"jformparamsmoduleparametersTabthemebtnfontlineheight","html":"<div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemebtnfontlineheight\"><input  size=\"5\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemebtnfontlineheightinput\" value=\"normal\"><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][btnfont]lineheight\" id=\"jformparamsmoduleparametersTabthemebtnfontlineheight\" value=\"normal\">"}},
          script: "dojo.addOnLoad(function(){\r\n      new OfflajnRadio({\r\n        id: \"jformparamsmoduleparametersTabthemebtnfonttab\",\r\n        values: [\"Text\"],\r\n        map: {\"Text\":0},\r\n        mode: \"\"\r\n      });\r\n    \n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemebtnfonttype\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Alternative fonts<\\\/div><div class=\\\"listelement\\\">Cyrillic<\\\/div><div class=\\\"listelement\\\">CyrillicExtended<\\\/div><div class=\\\"listelement\\\">Greek<\\\/div><div class=\\\"listelement\\\">GreekExtended<\\\/div><div class=\\\"listelement\\\">Khmer<\\\/div><div class=\\\"listelement\\\">Latin<\\\/div><div class=\\\"listelement\\\">LatinExtended<\\\/div><div class=\\\"listelement\\\">Vietnamese<\\\/div><\\\/div>\",\n        options: [{\"value\":\"0\",\"text\":\"Alternative fonts\"},{\"value\":\"Cyrillic\",\"text\":\"Cyrillic\"},{\"value\":\"CyrillicExtended\",\"text\":\"CyrillicExtended\"},{\"value\":\"Greek\",\"text\":\"Greek\"},{\"value\":\"GreekExtended\",\"text\":\"GreekExtended\"},{\"value\":\"Khmer\",\"text\":\"Khmer\"},{\"value\":\"Latin\",\"text\":\"Latin\"},{\"value\":\"LatinExtended\",\"text\":\"LatinExtended\"},{\"value\":\"Vietnamese\",\"text\":\"Vietnamese\"}],\n        selectedIndex: 6,\n        height: 0,\n        fireshow: 0\n      });\n    dojo.addOnLoad(function(){ \r\n      new OfflajnSwitcher({\r\n        id: \"jformparamsmoduleparametersTabthemebtnfontsizeunit\",\r\n        units: [\"px\",\"em\"],\r\n        values: [\"px\",\"em\"],\r\n        map: {\"px\":0,\"em\":1},\r\n        mode: 0,\r\n        url: \"http:\\\/\\\/emundus.local\\\/administrator\\\/..\\\/modules\\\/mod_improved_ajax_login\\\/params\\\/offlajnswitcher\\\/images\\\/\"\r\n      }); \r\n    });\n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemebtnfontsize\",\n        validation: \"int\",\n        attachunit: \"\",\n        mode: \"increment\",\n        scale: \"1\",\n        minus: 0,\n        onoff: \"\"\n      }); \n    \n    var el = dojo.byId(\"jformparamsmoduleparametersTabthemebtnfontcolor\");\n    jQuery.fn.jPicker.defaults.images.clientPath=\"\/modules\/mod_improved_ajax_login\/params\/offlajndashboard\/..\/offlajncolor\/offlajncolor\/jpicker\/images\/\";\n    el.alphaSupport=false; \n    el.c = jQuery(\"#jformparamsmoduleparametersTabthemebtnfontcolor\").jPicker({\n        window:{\n          expandable: true,\n          alphaSupport: false}\n        });\n    dojo.connect(el, \"change\", function(){\n      this.c[0].color.active.val(\"hex\", this.value);\n    });\n    \n      new OfflajnOnOff({\n        id: \"jformparamsmoduleparametersTabthemebtnfontbold\",\n        mode: \"button\",\n        imgs: \"\"\n      }); \n    \n      new OfflajnOnOff({\n        id: \"jformparamsmoduleparametersTabthemebtnfontitalic\",\n        mode: \"button\",\n        imgs: \"\"\n      }); \n    \n      new OfflajnOnOff({\n        id: \"jformparamsmoduleparametersTabthemebtnfontunderline\",\n        mode: \"button\",\n        imgs: \"\"\n      }); \n    \r\n      new OfflajnRadio({\r\n        id: \"jformparamsmoduleparametersTabthemebtnfontalign\",\r\n        values: [\"left\",\"center\",\"right\"],\r\n        map: {\"left\":0,\"center\":1,\"right\":2},\r\n        mode: \"image\"\r\n      });\r\n    dojo.addOnLoad(function(){ \r\n      new OfflajnSwitcher({\r\n        id: \"jformparamsmoduleparametersTabthemebtnfontafontunit\",\r\n        units: [\"ON\",\"OFF\"],\r\n        values: [\"1\",\"0\"],\r\n        map: {\"1\":0,\"0\":1},\r\n        mode: 0,\r\n        url: \"http:\\\/\\\/emundus.local\\\/administrator\\\/..\\\/modules\\\/mod_improved_ajax_login\\\/params\\\/offlajnswitcher\\\/images\\\/\"\r\n      }); \r\n    });\n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemebtnfontafont\",\n        validation: \"\",\n        attachunit: \"\",\n        mode: \"\",\n        scale: \"\",\n        minus: 0,\n        onoff: \"1\"\n      }); \n    \n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemebtnfonttshadow0\",\n        validation: \"float\",\n        attachunit: \"px\",\n        mode: \"\",\n        scale: \"\",\n        minus: 0,\n        onoff: \"\"\n      }); \n    \n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemebtnfonttshadow1\",\n        validation: \"float\",\n        attachunit: \"px\",\n        mode: \"\",\n        scale: \"\",\n        minus: 0,\n        onoff: \"\"\n      }); \n    \n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemebtnfonttshadow2\",\n        validation: \"float\",\n        attachunit: \"px\",\n        mode: \"\",\n        scale: \"\",\n        minus: 0,\n        onoff: \"\"\n      }); \n    \n    var el = dojo.byId(\"jformparamsmoduleparametersTabthemebtnfonttshadow3\");\n    jQuery.fn.jPicker.defaults.images.clientPath=\"\/modules\/mod_improved_ajax_login\/params\/offlajndashboard\/..\/offlajncolor\/offlajncolor\/jpicker\/images\/\";\n    el.alphaSupport=true; \n    el.c = jQuery(\"#jformparamsmoduleparametersTabthemebtnfonttshadow3\").jPicker({\n        window:{\n          expandable: true,\n          alphaSupport: true}\n        });\n    dojo.connect(el, \"change\", function(){\n      this.c[0].color.active.val(\"hex\", this.value);\n    });\n    dojo.addOnLoad(function(){ \r\n      new OfflajnSwitcher({\r\n        id: \"jformparamsmoduleparametersTabthemebtnfonttshadow4\",\r\n        units: [\"ON\",\"OFF\"],\r\n        values: [\"1\",\"0\"],\r\n        map: {\"1\":0,\"0\":1},\r\n        mode: 0,\r\n        url: \"http:\\\/\\\/emundus.local\\\/administrator\\\/..\\\/modules\\\/mod_improved_ajax_login\\\/params\\\/offlajnswitcher\\\/images\\\/\"\r\n      }); \r\n    });\r\n      new OfflajnCombine({\r\n        id: \"jformparamsmoduleparametersTabthemebtnfonttshadow\",\r\n        num: 5,\r\n        switcherid: \"jformparamsmoduleparametersTabthemebtnfonttshadow4\",\r\n        hideafter: \"0\"\r\n      }); \r\n    \n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemebtnfontlineheight\",\n        validation: \"\",\n        attachunit: \"\",\n        mode: \"\",\n        scale: \"\",\n        minus: 0,\n        onoff: \"\"\n      }); \n    });"
        });
    
jQuery.fn.jPicker.defaults.images.clientPath="http://emundus.local/administrator/../modules/mod_improved_ajax_login/params/offlajncolor/offlajncolor/jpicker/images/";

      new OfflajnOnOff({
        id: "jformparamsmoduleparametersTabthemebtngradonoff",
        mode: "",
        imgs: ""
      }); 
    

      new OfflajnGradient({
        hidden: dojo.byId("jformparamsmoduleparametersTabthemebtngrad"),
        switcher: dojo.byId("jformparamsmoduleparametersTabthemebtngradonoff"),
        onoff: 0,
        start: dojo.byId("jformparamsmoduleparametersTabthemebtngradstart"),
        end: dojo.byId("jformparamsmoduleparametersTabthemebtngradstop")
      });
    
jQuery.fn.jPicker.defaults.images.clientPath="http://emundus.local/administrator/../modules/mod_improved_ajax_login/params/offlajncolor/offlajncolor/jpicker/images/";

      new OfflajnOnOff({
        id: "jformparamsmoduleparametersTabthemehovergradonoff",
        mode: "",
        imgs: ""
      }); 
    

      new OfflajnGradient({
        hidden: dojo.byId("jformparamsmoduleparametersTabthemehovergrad"),
        switcher: dojo.byId("jformparamsmoduleparametersTabthemehovergradonoff"),
        onoff: 0,
        start: dojo.byId("jformparamsmoduleparametersTabthemehovergradstart"),
        end: dojo.byId("jformparamsmoduleparametersTabthemehovergradstop")
      });
    

      new OfflajnText({
        id: "jformparamsmoduleparametersTabthemebuttoncomb0",
        validation: "int",
        attachunit: "px",
        mode: "",
        scale: "",
        minus: 0,
        onoff: ""
      }); 
    

    var el = dojo.byId("jformparamsmoduleparametersTabthemebuttoncomb1");
    jQuery.fn.jPicker.defaults.images.clientPath="/modules/mod_improved_ajax_login/params/offlajndashboard/../offlajncolor/offlajncolor/jpicker/images/";
    el.alphaSupport=false; 
    el.c = jQuery("#jformparamsmoduleparametersTabthemebuttoncomb1").jPicker({
        window:{
          expandable: true,
          alphaSupport: false}
        });
    dojo.connect(el, "change", function(){
      this.c[0].color.active.val("hex", this.value);
    });
    

      new OfflajnText({
        id: "jformparamsmoduleparametersTabthemebuttoncomb2",
        validation: "int",
        attachunit: "px",
        mode: "",
        scale: "",
        minus: 0,
        onoff: ""
      }); 
    

      new OfflajnCombine({
        id: "jformparamsmoduleparametersTabthemebuttoncomb",
        num: 3,
        switcherid: "",
        hideafter: "0"
      }); 
    

    var el = dojo.byId("jformparamsmoduleparametersTabthemetxtcomb0");
    jQuery.fn.jPicker.defaults.images.clientPath="/modules/mod_improved_ajax_login/params/offlajndashboard/../offlajncolor/offlajncolor/jpicker/images/";
    el.alphaSupport=false; 
    el.c = jQuery("#jformparamsmoduleparametersTabthemetxtcomb0").jPicker({
        window:{
          expandable: true,
          alphaSupport: false}
        });
    dojo.connect(el, "change", function(){
      this.c[0].color.active.val("hex", this.value);
    });
    

    var el = dojo.byId("jformparamsmoduleparametersTabthemetxtcomb1");
    jQuery.fn.jPicker.defaults.images.clientPath="/modules/mod_improved_ajax_login/params/offlajndashboard/../offlajncolor/offlajncolor/jpicker/images/";
    el.alphaSupport=false; 
    el.c = jQuery("#jformparamsmoduleparametersTabthemetxtcomb1").jPicker({
        window:{
          expandable: true,
          alphaSupport: false}
        });
    dojo.connect(el, "change", function(){
      this.c[0].color.active.val("hex", this.value);
    });
    

      new OfflajnCombine({
        id: "jformparamsmoduleparametersTabthemetxtcomb",
        num: 2,
        switcherid: "",
        hideafter: "0"
      }); 
    

        new FontConfigurator({
          id: "jformparamsmoduleparametersTabthemetextfont",
          defaultTab: "Text",
          origsettings: {"Text":{"lineheight":"normal","type":"Latin","subset":"Latin","family":"Carme","size":"12||px","color":"5d5c5c","afont":"Helvetica||1","underline":"0"},"Link":{"color":"1685d7","underline":"0"},"Hover":{"color":"1685d7","underline":"0"}},
          elements: {"tab":{"name":"jform[params][moduleparametersTab][theme][textfont]tab","id":"jformparamsmoduleparametersTabthemetextfonttab","html":"<div class=\"offlajnradiocontainerbutton\" id=\"offlajnradiocontainerjformparamsmoduleparametersTabthemetextfonttab\"><div class=\"radioelement first selected\">Text<\/div><div class=\"radioelement  last\">Hover<\/div><div class=\"clear\"><\/div><\/div><input type=\"hidden\" id=\"jformparamsmoduleparametersTabthemetextfonttab\" name=\"jform[params][moduleparametersTab][theme][textfont]tab\" value=\"Text\"\/>"},"type":{"name":"jform[params][moduleparametersTab][theme][textfont]type","id":"jformparamsmoduleparametersTabthemetextfonttype","Cyrillic":{"name":"jform[params][moduleparametersTab][theme][textfont]family","id":"jformparamsmoduleparametersTabthemetextfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetextfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Andika<br \/>Andika<br \/>Anonymous Pro<br \/>Cuprum<br \/>Didact Gothic<br \/>EB Garamond<br \/>Istok Web<br \/>Jura<br \/>Forum<br \/>Kelly Slab<br \/>Lobster<br \/>Neucha<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Philosopher<br \/>Play<br \/>PT Sans<br \/>PT Sans Caption<br \/>PT Sans Narrow<br \/>PT Serif<br \/>PT Serif Caption<br \/>Ruslan Display<br \/>Tenor Sans<br \/>Ubuntu<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]family\" id=\"jformparamsmoduleparametersTabthemetextfontfamily\" value=\"Andika\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetextfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Andika<\\\/div><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Cuprum<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">EB Garamond<\\\/div><div class=\\\"listelement\\\">Istok Web<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">Forum<\\\/div><div class=\\\"listelement\\\">Kelly Slab<\\\/div><div class=\\\"listelement\\\">Lobster<\\\/div><div class=\\\"listelement\\\">Neucha<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Philosopher<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">PT Sans<\\\/div><div class=\\\"listelement\\\">PT Sans Caption<\\\/div><div class=\\\"listelement\\\">PT Sans Narrow<\\\/div><div class=\\\"listelement\\\">PT Serif<\\\/div><div class=\\\"listelement\\\">PT Serif Caption<\\\/div><div class=\\\"listelement\\\">Ruslan Display<\\\/div><div class=\\\"listelement\\\">Tenor Sans<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Andika\",\"text\":\"Andika\"},{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Cuprum\",\"text\":\"Cuprum\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"EB Garamond\",\"text\":\"EB Garamond\"},{\"value\":\"Istok Web\",\"text\":\"Istok Web\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"Forum\",\"text\":\"Forum\"},{\"value\":\"Kelly Slab\",\"text\":\"Kelly Slab\"},{\"value\":\"Lobster\",\"text\":\"Lobster\"},{\"value\":\"Neucha\",\"text\":\"Neucha\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Philosopher\",\"text\":\"Philosopher\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"PT Sans\",\"text\":\"PT Sans\"},{\"value\":\"PT Sans Caption\",\"text\":\"PT Sans Caption\"},{\"value\":\"PT Sans Narrow\",\"text\":\"PT Sans Narrow\"},{\"value\":\"PT Serif\",\"text\":\"PT Serif\"},{\"value\":\"PT Serif Caption\",\"text\":\"PT Serif Caption\"},{\"value\":\"Ruslan Display\",\"text\":\"Ruslan Display\"},{\"value\":\"Tenor Sans\",\"text\":\"Tenor Sans\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"CyrillicExtended":{"name":"jform[params][moduleparametersTab][theme][textfont]family","id":"jformparamsmoduleparametersTabthemetextfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetextfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Andika<br \/>Andika<br \/>Anonymous Pro<br \/>Didact Gothic<br \/>EB Garamond<br \/>Istok Web<br \/>Jura<br \/>Forum<br \/>Lobster<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Play<br \/>Ruslan Display<br \/>Tenor Sans<br \/>Ubuntu<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]family\" id=\"jformparamsmoduleparametersTabthemetextfontfamily\" value=\"Andika\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetextfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Andika<\\\/div><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">EB Garamond<\\\/div><div class=\\\"listelement\\\">Istok Web<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">Forum<\\\/div><div class=\\\"listelement\\\">Lobster<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">Ruslan Display<\\\/div><div class=\\\"listelement\\\">Tenor Sans<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Andika\",\"text\":\"Andika\"},{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"EB Garamond\",\"text\":\"EB Garamond\"},{\"value\":\"Istok Web\",\"text\":\"Istok Web\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"Forum\",\"text\":\"Forum\"},{\"value\":\"Lobster\",\"text\":\"Lobster\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"Ruslan Display\",\"text\":\"Ruslan Display\"},{\"value\":\"Tenor Sans\",\"text\":\"Tenor Sans\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"Greek":{"name":"jform[params][moduleparametersTab][theme][textfont]family","id":"jformparamsmoduleparametersTabthemetextfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetextfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Anonymous Pro<br \/>Anonymous Pro<br \/>Caudex<br \/>Didact Gothic<br \/>Jura<br \/>GFS Didot<br \/>GFS Neohellenic<br \/>Nova Mono<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Play<br \/>Ubuntu<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]family\" id=\"jformparamsmoduleparametersTabthemetextfontfamily\" value=\"Anonymous Pro\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetextfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Caudex<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">GFS Didot<\\\/div><div class=\\\"listelement\\\">GFS Neohellenic<\\\/div><div class=\\\"listelement\\\">Nova Mono<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Caudex\",\"text\":\"Caudex\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"GFS Didot\",\"text\":\"GFS Didot\"},{\"value\":\"GFS Neohellenic\",\"text\":\"GFS Neohellenic\"},{\"value\":\"Nova Mono\",\"text\":\"Nova Mono\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"GreekExtended":{"name":"jform[params][moduleparametersTab][theme][textfont]family","id":"jformparamsmoduleparametersTabthemetextfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetextfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Anonymous Pro<br \/>Anonymous Pro<br \/>Caudex<br \/>Didact Gothic<br \/>Jura<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Play<br \/>Ubuntu<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]family\" id=\"jformparamsmoduleparametersTabthemetextfontfamily\" value=\"Anonymous Pro\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetextfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Caudex<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Caudex\",\"text\":\"Caudex\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"Khmer":{"name":"jform[params][moduleparametersTab][theme][textfont]family","id":"jformparamsmoduleparametersTabthemetextfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetextfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Angkor<br \/>Angkor<br \/>Battambang<br \/>Bayon<br \/>Bokor<br \/>Chenla<br \/>Content<br \/>Dangrek<br \/>Freehand<br \/>Hanuman<br \/>Khmer<br \/>Koulen<br \/>Metal<br \/>Moul<br \/>Moulpali<br \/>Odor Mean Chey<br \/>Preahvihear<br \/>Siemreap<br \/>Suwannaphum<br \/>Taprom<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]family\" id=\"jformparamsmoduleparametersTabthemetextfontfamily\" value=\"Angkor\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetextfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Angkor<\\\/div><div class=\\\"listelement\\\">Battambang<\\\/div><div class=\\\"listelement\\\">Bayon<\\\/div><div class=\\\"listelement\\\">Bokor<\\\/div><div class=\\\"listelement\\\">Chenla<\\\/div><div class=\\\"listelement\\\">Content<\\\/div><div class=\\\"listelement\\\">Dangrek<\\\/div><div class=\\\"listelement\\\">Freehand<\\\/div><div class=\\\"listelement\\\">Hanuman<\\\/div><div class=\\\"listelement\\\">Khmer<\\\/div><div class=\\\"listelement\\\">Koulen<\\\/div><div class=\\\"listelement\\\">Metal<\\\/div><div class=\\\"listelement\\\">Moul<\\\/div><div class=\\\"listelement\\\">Moulpali<\\\/div><div class=\\\"listelement\\\">Odor Mean Chey<\\\/div><div class=\\\"listelement\\\">Preahvihear<\\\/div><div class=\\\"listelement\\\">Siemreap<\\\/div><div class=\\\"listelement\\\">Suwannaphum<\\\/div><div class=\\\"listelement\\\">Taprom<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Angkor\",\"text\":\"Angkor\"},{\"value\":\"Battambang\",\"text\":\"Battambang\"},{\"value\":\"Bayon\",\"text\":\"Bayon\"},{\"value\":\"Bokor\",\"text\":\"Bokor\"},{\"value\":\"Chenla\",\"text\":\"Chenla\"},{\"value\":\"Content\",\"text\":\"Content\"},{\"value\":\"Dangrek\",\"text\":\"Dangrek\"},{\"value\":\"Freehand\",\"text\":\"Freehand\"},{\"value\":\"Hanuman\",\"text\":\"Hanuman\"},{\"value\":\"Khmer\",\"text\":\"Khmer\"},{\"value\":\"Koulen\",\"text\":\"Koulen\"},{\"value\":\"Metal\",\"text\":\"Metal\"},{\"value\":\"Moul\",\"text\":\"Moul\"},{\"value\":\"Moulpali\",\"text\":\"Moulpali\"},{\"value\":\"Odor Mean Chey\",\"text\":\"Odor Mean Chey\"},{\"value\":\"Preahvihear\",\"text\":\"Preahvihear\"},{\"value\":\"Siemreap\",\"text\":\"Siemreap\"},{\"value\":\"Suwannaphum\",\"text\":\"Suwannaphum\"},{\"value\":\"Taprom\",\"text\":\"Taprom\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"Latin":{"name":"jform[params][moduleparametersTab][theme][textfont]family","id":"jformparamsmoduleparametersTabthemetextfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetextfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Carme<br \/>Abel<br \/>Abril Fatface<br \/>Aclonica<br \/>Actor<br \/>Aldrich<br \/>Alice<br \/>Alike<br \/>Allan<br \/>Allerta<br \/>Allerta Stencil<br \/>Amaranth<br \/>Andika<br \/>Annie Use Your Telescope<br \/>Anonymous Pro<br \/>Antic<br \/>Anton<br \/>Architects Daughter<br \/>Arimo<br \/>Artifika<br \/>Arvo<br \/>Asap<br \/>Asul<br \/>Asset<br \/>Astloch<br \/>Aubrey<br \/>Bangers<br \/>Bentham<br \/>Bevan<br \/>Bigshot One<br \/>Black Ops One<br \/>Bowlby One<br \/>Bowlby One SC<br \/>Brawler<br \/>Buda<br \/>Cabin<br \/>Cabin Sketch<br \/>Calligraffitti<br \/>Candal<br \/>Cantarell<br \/>Cardo<br \/>Carme<br \/>Carter One<br \/>Caudex<br \/>Cedarville Cursive<br \/>Cherry Cream Soda<br \/>Chewy<br \/>Chivo<br \/>Coda<br \/>Coda Caption<br \/>Comfortaa<br \/>Coming Soon<br \/>Convergence<br \/>Copse<br \/>Corben<br \/>Cousine<br \/>Coustard<br \/>Covered By Your Grace<br \/>Crafty Girls<br \/>Crimson Text<br \/>Crushed<br \/>Cuprum<br \/>Damion<br \/>Dancing Script<br \/>Dawning of a New Day<br \/>Days One<br \/>Delius<br \/>Delius Swash Caps<br \/>Delius Unicase<br \/>Didact Gothic<br \/>Dorsa<br \/>Droid Sans<br \/>Droid Sans Mono<br \/>Droid Serif<br \/>EB Garamond<br \/>Exo<br \/>Expletus Sans<br \/>Fanwood Text<br \/>Federo<br \/>Fontdiner Swanky<br \/>Forum<br \/>Francois One<br \/>Gentium Basic<br \/>Gentium Book Basic<br \/>Geo<br \/>Geostar<br \/>Geostar Fill<br \/>Give You Glory<br \/>Gloria Hallelujah<br \/>Goblin One<br \/>Goudy Bookletter 1911<br \/>Gravitas One<br \/>Gruppo<br \/>Hammersmith One<br \/>Holtwood One SC<br \/>Homemade Apple<br \/>IM Fell DW Pica<br \/>IM Fell DW Pica SC<br \/>IM Fell Double Pica<br \/>IM Fell Double Pica SC<br \/>IM Fell English<br \/>IM Fell English SC<br \/>IM Fell French Canon<br \/>IM Fell French Canon SC<br \/>IM Fell Great Primer<br \/>IM Fell Great Primer SC<br \/>Inconsolata<br \/>Inder<br \/>Indie Flower<br \/>Irish Grover<br \/>Istok Web<br \/>Josefin Sans<br \/>Josefin Slab<br \/>Judson<br \/>Jura<br \/>Just Another Hand<br \/>Just Me Again Down Here<br \/>Kameron<br \/>Kelly Slab<br \/>Kenia<br \/>Kranky<br \/>Kreon<br \/>Kristi<br \/>La Belle Aurore<br \/>Lato<br \/>League Script<br \/>Leckerli One<br \/>Lekton<br \/>Limelight<br \/>Lobster<br \/>Lobster Two<br \/>Lora<br \/>Love Ya Like A Sister<br \/>Loved by the King<br \/>Luckiest Guy<br \/>Magra<br \/>Maiden Orange<br \/>Mako<br \/>Marvel<br \/>Maven Pro<br \/>Meddon<br \/>MedievalSharp<br \/>Megrim<br \/>Merriweather<br \/>Metrophobic<br \/>Michroma<br \/>Miltonian<br \/>Miltonian Tattoo<br \/>Modern Antiqua<br \/>Molengo<br \/>Monofett<br \/>Monoton<br \/>Montez<br \/>Mountains of Christmas<br \/>Muli<br \/>Neucha<br \/>Neuton<br \/>News Cycle<br \/>Nixie One<br \/>Nobile<br \/>Nothing You Could Do<br \/>Nova Cut<br \/>Nova Flat<br \/>Nova Mono<br \/>Nova Oval<br \/>Nova Round<br \/>Nova Script<br \/>Nova Slim<br \/>Nova Square<br \/>Numans<br \/>Nunito<br \/>OFL Sorts Mill Goudy TT<br \/>Old Standard TT<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Orbitron<br \/>Oswald<br \/>Over the Rainbow<br \/>Ovo<br \/>PT Sans<br \/>PT Sans Caption<br \/>PT Sans Narrow<br \/>PT Serif<br \/>PT Serif Caption<br \/>Pacifico<br \/>Passero One<br \/>Patrick Hand<br \/>Paytone One<br \/>Permanent Marker<br \/>Philosopher<br \/>Play<br \/>Playfair Display<br \/>Podkova<br \/>Pompiere<br \/>Prociono<br \/>Puritan<br \/>Quattrocento<br \/>Quattrocento Sans<br \/>Questrial<br \/>Quicksand<br \/>Radley<br \/>Raleway<br \/>Rationale<br \/>Redressed<br \/>Reenie Beanie<br \/>Rochester<br \/>Rock Salt<br \/>Rokkitt<br \/>Ropa Sans<br \/>Rosario<br \/>Ruslan Display<br \/>Schoolbell<br \/>Shadows Into Light<br \/>Shanti<br \/>Short Stack<br \/>Sigmar One<br \/>Signika<br \/>Signika Negative<br \/>Six Caps<br \/>Slackey<br \/>Smokum<br \/>Smythe<br \/>Sniglet<br \/>Snippet<br \/>Special Elite<br \/>Stardos Stencil<br \/>Sue Ellen Francisco<br \/>Sunshiney<br \/>Swanky and Moo Moo<br \/>Syncopate<br \/>Tangerine<br \/>Telex<br \/>Tenor Sans<br \/>Terminal Dosis Light<br \/>The Girl Next Door<br \/>Tienne<br \/>Tinos<br \/>Tulpen One<br \/>Ubuntu<br \/>Ultra<br \/>UnifrakturCook<br \/>UnifrakturMaguntia<br \/>Unkempt<br \/>Unna<br \/>VT323<br \/>Varela<br \/>Varela Round<br \/>Vibur<br \/>Viga<br \/>Vidaloka<br \/>Volkhov<br \/>Vollkorn<br \/>Voltaire<br \/>Waiting for the Sunrise<br \/>Wallpoet<br \/>Walter Turncoat<br \/>Wire One<br \/>Yanone Kaffeesatz<br \/>Yellowtail<br \/>Yeseva One<br \/>Zeyada<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]family\" id=\"jformparamsmoduleparametersTabthemetextfontfamily\" value=\"Carme\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetextfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Abel<\\\/div><div class=\\\"listelement\\\">Abril Fatface<\\\/div><div class=\\\"listelement\\\">Aclonica<\\\/div><div class=\\\"listelement\\\">Actor<\\\/div><div class=\\\"listelement\\\">Aldrich<\\\/div><div class=\\\"listelement\\\">Alice<\\\/div><div class=\\\"listelement\\\">Alike<\\\/div><div class=\\\"listelement\\\">Allan<\\\/div><div class=\\\"listelement\\\">Allerta<\\\/div><div class=\\\"listelement\\\">Allerta Stencil<\\\/div><div class=\\\"listelement\\\">Amaranth<\\\/div><div class=\\\"listelement\\\">Andika<\\\/div><div class=\\\"listelement\\\">Annie Use Your Telescope<\\\/div><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Antic<\\\/div><div class=\\\"listelement\\\">Anton<\\\/div><div class=\\\"listelement\\\">Architects Daughter<\\\/div><div class=\\\"listelement\\\">Arimo<\\\/div><div class=\\\"listelement\\\">Artifika<\\\/div><div class=\\\"listelement\\\">Arvo<\\\/div><div class=\\\"listelement\\\">Asap<\\\/div><div class=\\\"listelement\\\">Asul<\\\/div><div class=\\\"listelement\\\">Asset<\\\/div><div class=\\\"listelement\\\">Astloch<\\\/div><div class=\\\"listelement\\\">Aubrey<\\\/div><div class=\\\"listelement\\\">Bangers<\\\/div><div class=\\\"listelement\\\">Bentham<\\\/div><div class=\\\"listelement\\\">Bevan<\\\/div><div class=\\\"listelement\\\">Bigshot One<\\\/div><div class=\\\"listelement\\\">Black Ops One<\\\/div><div class=\\\"listelement\\\">Bowlby One<\\\/div><div class=\\\"listelement\\\">Bowlby One SC<\\\/div><div class=\\\"listelement\\\">Brawler<\\\/div><div class=\\\"listelement\\\">Buda<\\\/div><div class=\\\"listelement\\\">Cabin<\\\/div><div class=\\\"listelement\\\">Cabin Sketch<\\\/div><div class=\\\"listelement\\\">Calligraffitti<\\\/div><div class=\\\"listelement\\\">Candal<\\\/div><div class=\\\"listelement\\\">Cantarell<\\\/div><div class=\\\"listelement\\\">Cardo<\\\/div><div class=\\\"listelement\\\">Carme<\\\/div><div class=\\\"listelement\\\">Carter One<\\\/div><div class=\\\"listelement\\\">Caudex<\\\/div><div class=\\\"listelement\\\">Cedarville Cursive<\\\/div><div class=\\\"listelement\\\">Cherry Cream Soda<\\\/div><div class=\\\"listelement\\\">Chewy<\\\/div><div class=\\\"listelement\\\">Chivo<\\\/div><div class=\\\"listelement\\\">Coda<\\\/div><div class=\\\"listelement\\\">Coda Caption<\\\/div><div class=\\\"listelement\\\">Comfortaa<\\\/div><div class=\\\"listelement\\\">Coming Soon<\\\/div><div class=\\\"listelement\\\">Convergence<\\\/div><div class=\\\"listelement\\\">Copse<\\\/div><div class=\\\"listelement\\\">Corben<\\\/div><div class=\\\"listelement\\\">Cousine<\\\/div><div class=\\\"listelement\\\">Coustard<\\\/div><div class=\\\"listelement\\\">Covered By Your Grace<\\\/div><div class=\\\"listelement\\\">Crafty Girls<\\\/div><div class=\\\"listelement\\\">Crimson Text<\\\/div><div class=\\\"listelement\\\">Crushed<\\\/div><div class=\\\"listelement\\\">Cuprum<\\\/div><div class=\\\"listelement\\\">Damion<\\\/div><div class=\\\"listelement\\\">Dancing Script<\\\/div><div class=\\\"listelement\\\">Dawning of a New Day<\\\/div><div class=\\\"listelement\\\">Days One<\\\/div><div class=\\\"listelement\\\">Delius<\\\/div><div class=\\\"listelement\\\">Delius Swash Caps<\\\/div><div class=\\\"listelement\\\">Delius Unicase<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">Dorsa<\\\/div><div class=\\\"listelement\\\">Droid Sans<\\\/div><div class=\\\"listelement\\\">Droid Sans Mono<\\\/div><div class=\\\"listelement\\\">Droid Serif<\\\/div><div class=\\\"listelement\\\">EB Garamond<\\\/div><div class=\\\"listelement\\\">Exo<\\\/div><div class=\\\"listelement\\\">Expletus Sans<\\\/div><div class=\\\"listelement\\\">Fanwood Text<\\\/div><div class=\\\"listelement\\\">Federo<\\\/div><div class=\\\"listelement\\\">Fontdiner Swanky<\\\/div><div class=\\\"listelement\\\">Forum<\\\/div><div class=\\\"listelement\\\">Francois One<\\\/div><div class=\\\"listelement\\\">Gentium Basic<\\\/div><div class=\\\"listelement\\\">Gentium Book Basic<\\\/div><div class=\\\"listelement\\\">Geo<\\\/div><div class=\\\"listelement\\\">Geostar<\\\/div><div class=\\\"listelement\\\">Geostar Fill<\\\/div><div class=\\\"listelement\\\">Give You Glory<\\\/div><div class=\\\"listelement\\\">Gloria Hallelujah<\\\/div><div class=\\\"listelement\\\">Goblin One<\\\/div><div class=\\\"listelement\\\">Goudy Bookletter 1911<\\\/div><div class=\\\"listelement\\\">Gravitas One<\\\/div><div class=\\\"listelement\\\">Gruppo<\\\/div><div class=\\\"listelement\\\">Hammersmith One<\\\/div><div class=\\\"listelement\\\">Holtwood One SC<\\\/div><div class=\\\"listelement\\\">Homemade Apple<\\\/div><div class=\\\"listelement\\\">IM Fell DW Pica<\\\/div><div class=\\\"listelement\\\">IM Fell DW Pica SC<\\\/div><div class=\\\"listelement\\\">IM Fell Double Pica<\\\/div><div class=\\\"listelement\\\">IM Fell Double Pica SC<\\\/div><div class=\\\"listelement\\\">IM Fell English<\\\/div><div class=\\\"listelement\\\">IM Fell English SC<\\\/div><div class=\\\"listelement\\\">IM Fell French Canon<\\\/div><div class=\\\"listelement\\\">IM Fell French Canon SC<\\\/div><div class=\\\"listelement\\\">IM Fell Great Primer<\\\/div><div class=\\\"listelement\\\">IM Fell Great Primer SC<\\\/div><div class=\\\"listelement\\\">Inconsolata<\\\/div><div class=\\\"listelement\\\">Inder<\\\/div><div class=\\\"listelement\\\">Indie Flower<\\\/div><div class=\\\"listelement\\\">Irish Grover<\\\/div><div class=\\\"listelement\\\">Istok Web<\\\/div><div class=\\\"listelement\\\">Josefin Sans<\\\/div><div class=\\\"listelement\\\">Josefin Slab<\\\/div><div class=\\\"listelement\\\">Judson<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">Just Another Hand<\\\/div><div class=\\\"listelement\\\">Just Me Again Down Here<\\\/div><div class=\\\"listelement\\\">Kameron<\\\/div><div class=\\\"listelement\\\">Kelly Slab<\\\/div><div class=\\\"listelement\\\">Kenia<\\\/div><div class=\\\"listelement\\\">Kranky<\\\/div><div class=\\\"listelement\\\">Kreon<\\\/div><div class=\\\"listelement\\\">Kristi<\\\/div><div class=\\\"listelement\\\">La Belle Aurore<\\\/div><div class=\\\"listelement\\\">Lato<\\\/div><div class=\\\"listelement\\\">League Script<\\\/div><div class=\\\"listelement\\\">Leckerli One<\\\/div><div class=\\\"listelement\\\">Lekton<\\\/div><div class=\\\"listelement\\\">Limelight<\\\/div><div class=\\\"listelement\\\">Lobster<\\\/div><div class=\\\"listelement\\\">Lobster Two<\\\/div><div class=\\\"listelement\\\">Lora<\\\/div><div class=\\\"listelement\\\">Love Ya Like A Sister<\\\/div><div class=\\\"listelement\\\">Loved by the King<\\\/div><div class=\\\"listelement\\\">Luckiest Guy<\\\/div><div class=\\\"listelement\\\">Magra<\\\/div><div class=\\\"listelement\\\">Maiden Orange<\\\/div><div class=\\\"listelement\\\">Mako<\\\/div><div class=\\\"listelement\\\">Marvel<\\\/div><div class=\\\"listelement\\\">Maven Pro<\\\/div><div class=\\\"listelement\\\">Meddon<\\\/div><div class=\\\"listelement\\\">MedievalSharp<\\\/div><div class=\\\"listelement\\\">Megrim<\\\/div><div class=\\\"listelement\\\">Merriweather<\\\/div><div class=\\\"listelement\\\">Metrophobic<\\\/div><div class=\\\"listelement\\\">Michroma<\\\/div><div class=\\\"listelement\\\">Miltonian<\\\/div><div class=\\\"listelement\\\">Miltonian Tattoo<\\\/div><div class=\\\"listelement\\\">Modern Antiqua<\\\/div><div class=\\\"listelement\\\">Molengo<\\\/div><div class=\\\"listelement\\\">Monofett<\\\/div><div class=\\\"listelement\\\">Monoton<\\\/div><div class=\\\"listelement\\\">Montez<\\\/div><div class=\\\"listelement\\\">Mountains of Christmas<\\\/div><div class=\\\"listelement\\\">Muli<\\\/div><div class=\\\"listelement\\\">Neucha<\\\/div><div class=\\\"listelement\\\">Neuton<\\\/div><div class=\\\"listelement\\\">News Cycle<\\\/div><div class=\\\"listelement\\\">Nixie One<\\\/div><div class=\\\"listelement\\\">Nobile<\\\/div><div class=\\\"listelement\\\">Nothing You Could Do<\\\/div><div class=\\\"listelement\\\">Nova Cut<\\\/div><div class=\\\"listelement\\\">Nova Flat<\\\/div><div class=\\\"listelement\\\">Nova Mono<\\\/div><div class=\\\"listelement\\\">Nova Oval<\\\/div><div class=\\\"listelement\\\">Nova Round<\\\/div><div class=\\\"listelement\\\">Nova Script<\\\/div><div class=\\\"listelement\\\">Nova Slim<\\\/div><div class=\\\"listelement\\\">Nova Square<\\\/div><div class=\\\"listelement\\\">Numans<\\\/div><div class=\\\"listelement\\\">Nunito<\\\/div><div class=\\\"listelement\\\">OFL Sorts Mill Goudy TT<\\\/div><div class=\\\"listelement\\\">Old Standard TT<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Orbitron<\\\/div><div class=\\\"listelement\\\">Oswald<\\\/div><div class=\\\"listelement\\\">Over the Rainbow<\\\/div><div class=\\\"listelement\\\">Ovo<\\\/div><div class=\\\"listelement\\\">PT Sans<\\\/div><div class=\\\"listelement\\\">PT Sans Caption<\\\/div><div class=\\\"listelement\\\">PT Sans Narrow<\\\/div><div class=\\\"listelement\\\">PT Serif<\\\/div><div class=\\\"listelement\\\">PT Serif Caption<\\\/div><div class=\\\"listelement\\\">Pacifico<\\\/div><div class=\\\"listelement\\\">Passero One<\\\/div><div class=\\\"listelement\\\">Patrick Hand<\\\/div><div class=\\\"listelement\\\">Paytone One<\\\/div><div class=\\\"listelement\\\">Permanent Marker<\\\/div><div class=\\\"listelement\\\">Philosopher<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">Playfair Display<\\\/div><div class=\\\"listelement\\\">Podkova<\\\/div><div class=\\\"listelement\\\">Pompiere<\\\/div><div class=\\\"listelement\\\">Prociono<\\\/div><div class=\\\"listelement\\\">Puritan<\\\/div><div class=\\\"listelement\\\">Quattrocento<\\\/div><div class=\\\"listelement\\\">Quattrocento Sans<\\\/div><div class=\\\"listelement\\\">Questrial<\\\/div><div class=\\\"listelement\\\">Quicksand<\\\/div><div class=\\\"listelement\\\">Radley<\\\/div><div class=\\\"listelement\\\">Raleway<\\\/div><div class=\\\"listelement\\\">Rationale<\\\/div><div class=\\\"listelement\\\">Redressed<\\\/div><div class=\\\"listelement\\\">Reenie Beanie<\\\/div><div class=\\\"listelement\\\">Rochester<\\\/div><div class=\\\"listelement\\\">Rock Salt<\\\/div><div class=\\\"listelement\\\">Rokkitt<\\\/div><div class=\\\"listelement\\\">Ropa Sans<\\\/div><div class=\\\"listelement\\\">Rosario<\\\/div><div class=\\\"listelement\\\">Ruslan Display<\\\/div><div class=\\\"listelement\\\">Schoolbell<\\\/div><div class=\\\"listelement\\\">Shadows Into Light<\\\/div><div class=\\\"listelement\\\">Shanti<\\\/div><div class=\\\"listelement\\\">Short Stack<\\\/div><div class=\\\"listelement\\\">Sigmar One<\\\/div><div class=\\\"listelement\\\">Signika<\\\/div><div class=\\\"listelement\\\">Signika Negative<\\\/div><div class=\\\"listelement\\\">Six Caps<\\\/div><div class=\\\"listelement\\\">Slackey<\\\/div><div class=\\\"listelement\\\">Smokum<\\\/div><div class=\\\"listelement\\\">Smythe<\\\/div><div class=\\\"listelement\\\">Sniglet<\\\/div><div class=\\\"listelement\\\">Snippet<\\\/div><div class=\\\"listelement\\\">Special Elite<\\\/div><div class=\\\"listelement\\\">Stardos Stencil<\\\/div><div class=\\\"listelement\\\">Sue Ellen Francisco<\\\/div><div class=\\\"listelement\\\">Sunshiney<\\\/div><div class=\\\"listelement\\\">Swanky and Moo Moo<\\\/div><div class=\\\"listelement\\\">Syncopate<\\\/div><div class=\\\"listelement\\\">Tangerine<\\\/div><div class=\\\"listelement\\\">Telex<\\\/div><div class=\\\"listelement\\\">Tenor Sans<\\\/div><div class=\\\"listelement\\\">Terminal Dosis Light<\\\/div><div class=\\\"listelement\\\">The Girl Next Door<\\\/div><div class=\\\"listelement\\\">Tienne<\\\/div><div class=\\\"listelement\\\">Tinos<\\\/div><div class=\\\"listelement\\\">Tulpen One<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><div class=\\\"listelement\\\">Ultra<\\\/div><div class=\\\"listelement\\\">UnifrakturCook<\\\/div><div class=\\\"listelement\\\">UnifrakturMaguntia<\\\/div><div class=\\\"listelement\\\">Unkempt<\\\/div><div class=\\\"listelement\\\">Unna<\\\/div><div class=\\\"listelement\\\">VT323<\\\/div><div class=\\\"listelement\\\">Varela<\\\/div><div class=\\\"listelement\\\">Varela Round<\\\/div><div class=\\\"listelement\\\">Vibur<\\\/div><div class=\\\"listelement\\\">Viga<\\\/div><div class=\\\"listelement\\\">Vidaloka<\\\/div><div class=\\\"listelement\\\">Volkhov<\\\/div><div class=\\\"listelement\\\">Vollkorn<\\\/div><div class=\\\"listelement\\\">Voltaire<\\\/div><div class=\\\"listelement\\\">Waiting for the Sunrise<\\\/div><div class=\\\"listelement\\\">Wallpoet<\\\/div><div class=\\\"listelement\\\">Walter Turncoat<\\\/div><div class=\\\"listelement\\\">Wire One<\\\/div><div class=\\\"listelement\\\">Yanone Kaffeesatz<\\\/div><div class=\\\"listelement\\\">Yellowtail<\\\/div><div class=\\\"listelement\\\">Yeseva One<\\\/div><div class=\\\"listelement\\\">Zeyada<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Abel\",\"text\":\"Abel\"},{\"value\":\"Abril Fatface\",\"text\":\"Abril Fatface\"},{\"value\":\"Aclonica\",\"text\":\"Aclonica\"},{\"value\":\"Actor\",\"text\":\"Actor\"},{\"value\":\"Aldrich\",\"text\":\"Aldrich\"},{\"value\":\"Alice\",\"text\":\"Alice\"},{\"value\":\"Alike\",\"text\":\"Alike\"},{\"value\":\"Allan\",\"text\":\"Allan\"},{\"value\":\"Allerta\",\"text\":\"Allerta\"},{\"value\":\"Allerta Stencil\",\"text\":\"Allerta Stencil\"},{\"value\":\"Amaranth\",\"text\":\"Amaranth\"},{\"value\":\"Andika\",\"text\":\"Andika\"},{\"value\":\"Annie Use Your Telescope\",\"text\":\"Annie Use Your Telescope\"},{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Antic\",\"text\":\"Antic\"},{\"value\":\"Anton\",\"text\":\"Anton\"},{\"value\":\"Architects Daughter\",\"text\":\"Architects Daughter\"},{\"value\":\"Arimo\",\"text\":\"Arimo\"},{\"value\":\"Artifika\",\"text\":\"Artifika\"},{\"value\":\"Arvo\",\"text\":\"Arvo\"},{\"value\":\"Asap\",\"text\":\"Asap\"},{\"value\":\"Asul\",\"text\":\"Asul\"},{\"value\":\"Asset\",\"text\":\"Asset\"},{\"value\":\"Astloch\",\"text\":\"Astloch\"},{\"value\":\"Aubrey\",\"text\":\"Aubrey\"},{\"value\":\"Bangers\",\"text\":\"Bangers\"},{\"value\":\"Bentham\",\"text\":\"Bentham\"},{\"value\":\"Bevan\",\"text\":\"Bevan\"},{\"value\":\"Bigshot One\",\"text\":\"Bigshot One\"},{\"value\":\"Black Ops One\",\"text\":\"Black Ops One\"},{\"value\":\"Bowlby One\",\"text\":\"Bowlby One\"},{\"value\":\"Bowlby One SC\",\"text\":\"Bowlby One SC\"},{\"value\":\"Brawler\",\"text\":\"Brawler\"},{\"value\":\"Buda\",\"text\":\"Buda\"},{\"value\":\"Cabin\",\"text\":\"Cabin\"},{\"value\":\"Cabin Sketch\",\"text\":\"Cabin Sketch\"},{\"value\":\"Calligraffitti\",\"text\":\"Calligraffitti\"},{\"value\":\"Candal\",\"text\":\"Candal\"},{\"value\":\"Cantarell\",\"text\":\"Cantarell\"},{\"value\":\"Cardo\",\"text\":\"Cardo\"},{\"value\":\"Carme\",\"text\":\"Carme\"},{\"value\":\"Carter One\",\"text\":\"Carter One\"},{\"value\":\"Caudex\",\"text\":\"Caudex\"},{\"value\":\"Cedarville Cursive\",\"text\":\"Cedarville Cursive\"},{\"value\":\"Cherry Cream Soda\",\"text\":\"Cherry Cream Soda\"},{\"value\":\"Chewy\",\"text\":\"Chewy\"},{\"value\":\"Chivo\",\"text\":\"Chivo\"},{\"value\":\"Coda\",\"text\":\"Coda\"},{\"value\":\"Coda Caption\",\"text\":\"Coda Caption\"},{\"value\":\"Comfortaa\",\"text\":\"Comfortaa\"},{\"value\":\"Coming Soon\",\"text\":\"Coming Soon\"},{\"value\":\"Convergence\",\"text\":\"Convergence\"},{\"value\":\"Copse\",\"text\":\"Copse\"},{\"value\":\"Corben\",\"text\":\"Corben\"},{\"value\":\"Cousine\",\"text\":\"Cousine\"},{\"value\":\"Coustard\",\"text\":\"Coustard\"},{\"value\":\"Covered By Your Grace\",\"text\":\"Covered By Your Grace\"},{\"value\":\"Crafty Girls\",\"text\":\"Crafty Girls\"},{\"value\":\"Crimson Text\",\"text\":\"Crimson Text\"},{\"value\":\"Crushed\",\"text\":\"Crushed\"},{\"value\":\"Cuprum\",\"text\":\"Cuprum\"},{\"value\":\"Damion\",\"text\":\"Damion\"},{\"value\":\"Dancing Script\",\"text\":\"Dancing Script\"},{\"value\":\"Dawning of a New Day\",\"text\":\"Dawning of a New Day\"},{\"value\":\"Days One\",\"text\":\"Days One\"},{\"value\":\"Delius\",\"text\":\"Delius\"},{\"value\":\"Delius Swash Caps\",\"text\":\"Delius Swash Caps\"},{\"value\":\"Delius Unicase\",\"text\":\"Delius Unicase\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"Dorsa\",\"text\":\"Dorsa\"},{\"value\":\"Droid Sans\",\"text\":\"Droid Sans\"},{\"value\":\"Droid Sans Mono\",\"text\":\"Droid Sans Mono\"},{\"value\":\"Droid Serif\",\"text\":\"Droid Serif\"},{\"value\":\"EB Garamond\",\"text\":\"EB Garamond\"},{\"value\":\"Exo\",\"text\":\"Exo\"},{\"value\":\"Expletus Sans\",\"text\":\"Expletus Sans\"},{\"value\":\"Fanwood Text\",\"text\":\"Fanwood Text\"},{\"value\":\"Federo\",\"text\":\"Federo\"},{\"value\":\"Fontdiner Swanky\",\"text\":\"Fontdiner Swanky\"},{\"value\":\"Forum\",\"text\":\"Forum\"},{\"value\":\"Francois One\",\"text\":\"Francois One\"},{\"value\":\"Gentium Basic\",\"text\":\"Gentium Basic\"},{\"value\":\"Gentium Book Basic\",\"text\":\"Gentium Book Basic\"},{\"value\":\"Geo\",\"text\":\"Geo\"},{\"value\":\"Geostar\",\"text\":\"Geostar\"},{\"value\":\"Geostar Fill\",\"text\":\"Geostar Fill\"},{\"value\":\"Give You Glory\",\"text\":\"Give You Glory\"},{\"value\":\"Gloria Hallelujah\",\"text\":\"Gloria Hallelujah\"},{\"value\":\"Goblin One\",\"text\":\"Goblin One\"},{\"value\":\"Goudy Bookletter 1911\",\"text\":\"Goudy Bookletter 1911\"},{\"value\":\"Gravitas One\",\"text\":\"Gravitas One\"},{\"value\":\"Gruppo\",\"text\":\"Gruppo\"},{\"value\":\"Hammersmith One\",\"text\":\"Hammersmith One\"},{\"value\":\"Holtwood One SC\",\"text\":\"Holtwood One SC\"},{\"value\":\"Homemade Apple\",\"text\":\"Homemade Apple\"},{\"value\":\"IM Fell DW Pica\",\"text\":\"IM Fell DW Pica\"},{\"value\":\"IM Fell DW Pica SC\",\"text\":\"IM Fell DW Pica SC\"},{\"value\":\"IM Fell Double Pica\",\"text\":\"IM Fell Double Pica\"},{\"value\":\"IM Fell Double Pica SC\",\"text\":\"IM Fell Double Pica SC\"},{\"value\":\"IM Fell English\",\"text\":\"IM Fell English\"},{\"value\":\"IM Fell English SC\",\"text\":\"IM Fell English SC\"},{\"value\":\"IM Fell French Canon\",\"text\":\"IM Fell French Canon\"},{\"value\":\"IM Fell French Canon SC\",\"text\":\"IM Fell French Canon SC\"},{\"value\":\"IM Fell Great Primer\",\"text\":\"IM Fell Great Primer\"},{\"value\":\"IM Fell Great Primer SC\",\"text\":\"IM Fell Great Primer SC\"},{\"value\":\"Inconsolata\",\"text\":\"Inconsolata\"},{\"value\":\"Inder\",\"text\":\"Inder\"},{\"value\":\"Indie Flower\",\"text\":\"Indie Flower\"},{\"value\":\"Irish Grover\",\"text\":\"Irish Grover\"},{\"value\":\"Istok Web\",\"text\":\"Istok Web\"},{\"value\":\"Josefin Sans\",\"text\":\"Josefin Sans\"},{\"value\":\"Josefin Slab\",\"text\":\"Josefin Slab\"},{\"value\":\"Judson\",\"text\":\"Judson\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"Just Another Hand\",\"text\":\"Just Another Hand\"},{\"value\":\"Just Me Again Down Here\",\"text\":\"Just Me Again Down Here\"},{\"value\":\"Kameron\",\"text\":\"Kameron\"},{\"value\":\"Kelly Slab\",\"text\":\"Kelly Slab\"},{\"value\":\"Kenia\",\"text\":\"Kenia\"},{\"value\":\"Kranky\",\"text\":\"Kranky\"},{\"value\":\"Kreon\",\"text\":\"Kreon\"},{\"value\":\"Kristi\",\"text\":\"Kristi\"},{\"value\":\"La Belle Aurore\",\"text\":\"La Belle Aurore\"},{\"value\":\"Lato\",\"text\":\"Lato\"},{\"value\":\"League Script\",\"text\":\"League Script\"},{\"value\":\"Leckerli One\",\"text\":\"Leckerli One\"},{\"value\":\"Lekton\",\"text\":\"Lekton\"},{\"value\":\"Limelight\",\"text\":\"Limelight\"},{\"value\":\"Lobster\",\"text\":\"Lobster\"},{\"value\":\"Lobster Two\",\"text\":\"Lobster Two\"},{\"value\":\"Lora\",\"text\":\"Lora\"},{\"value\":\"Love Ya Like A Sister\",\"text\":\"Love Ya Like A Sister\"},{\"value\":\"Loved by the King\",\"text\":\"Loved by the King\"},{\"value\":\"Luckiest Guy\",\"text\":\"Luckiest Guy\"},{\"value\":\"Magra\",\"text\":\"Magra\"},{\"value\":\"Maiden Orange\",\"text\":\"Maiden Orange\"},{\"value\":\"Mako\",\"text\":\"Mako\"},{\"value\":\"Marvel\",\"text\":\"Marvel\"},{\"value\":\"Maven Pro\",\"text\":\"Maven Pro\"},{\"value\":\"Meddon\",\"text\":\"Meddon\"},{\"value\":\"MedievalSharp\",\"text\":\"MedievalSharp\"},{\"value\":\"Megrim\",\"text\":\"Megrim\"},{\"value\":\"Merriweather\",\"text\":\"Merriweather\"},{\"value\":\"Metrophobic\",\"text\":\"Metrophobic\"},{\"value\":\"Michroma\",\"text\":\"Michroma\"},{\"value\":\"Miltonian\",\"text\":\"Miltonian\"},{\"value\":\"Miltonian Tattoo\",\"text\":\"Miltonian Tattoo\"},{\"value\":\"Modern Antiqua\",\"text\":\"Modern Antiqua\"},{\"value\":\"Molengo\",\"text\":\"Molengo\"},{\"value\":\"Monofett\",\"text\":\"Monofett\"},{\"value\":\"Monoton\",\"text\":\"Monoton\"},{\"value\":\"Montez\",\"text\":\"Montez\"},{\"value\":\"Mountains of Christmas\",\"text\":\"Mountains of Christmas\"},{\"value\":\"Muli\",\"text\":\"Muli\"},{\"value\":\"Neucha\",\"text\":\"Neucha\"},{\"value\":\"Neuton\",\"text\":\"Neuton\"},{\"value\":\"News Cycle\",\"text\":\"News Cycle\"},{\"value\":\"Nixie One\",\"text\":\"Nixie One\"},{\"value\":\"Nobile\",\"text\":\"Nobile\"},{\"value\":\"Nothing You Could Do\",\"text\":\"Nothing You Could Do\"},{\"value\":\"Nova Cut\",\"text\":\"Nova Cut\"},{\"value\":\"Nova Flat\",\"text\":\"Nova Flat\"},{\"value\":\"Nova Mono\",\"text\":\"Nova Mono\"},{\"value\":\"Nova Oval\",\"text\":\"Nova Oval\"},{\"value\":\"Nova Round\",\"text\":\"Nova Round\"},{\"value\":\"Nova Script\",\"text\":\"Nova Script\"},{\"value\":\"Nova Slim\",\"text\":\"Nova Slim\"},{\"value\":\"Nova Square\",\"text\":\"Nova Square\"},{\"value\":\"Numans\",\"text\":\"Numans\"},{\"value\":\"Nunito\",\"text\":\"Nunito\"},{\"value\":\"OFL Sorts Mill Goudy TT\",\"text\":\"OFL Sorts Mill Goudy TT\"},{\"value\":\"Old Standard TT\",\"text\":\"Old Standard TT\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Orbitron\",\"text\":\"Orbitron\"},{\"value\":\"Oswald\",\"text\":\"Oswald\"},{\"value\":\"Over the Rainbow\",\"text\":\"Over the Rainbow\"},{\"value\":\"Ovo\",\"text\":\"Ovo\"},{\"value\":\"PT Sans\",\"text\":\"PT Sans\"},{\"value\":\"PT Sans Caption\",\"text\":\"PT Sans Caption\"},{\"value\":\"PT Sans Narrow\",\"text\":\"PT Sans Narrow\"},{\"value\":\"PT Serif\",\"text\":\"PT Serif\"},{\"value\":\"PT Serif Caption\",\"text\":\"PT Serif Caption\"},{\"value\":\"Pacifico\",\"text\":\"Pacifico\"},{\"value\":\"Passero One\",\"text\":\"Passero One\"},{\"value\":\"Patrick Hand\",\"text\":\"Patrick Hand\"},{\"value\":\"Paytone One\",\"text\":\"Paytone One\"},{\"value\":\"Permanent Marker\",\"text\":\"Permanent Marker\"},{\"value\":\"Philosopher\",\"text\":\"Philosopher\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"Playfair Display\",\"text\":\"Playfair Display\"},{\"value\":\"Podkova\",\"text\":\"Podkova\"},{\"value\":\"Pompiere\",\"text\":\"Pompiere\"},{\"value\":\"Prociono\",\"text\":\"Prociono\"},{\"value\":\"Puritan\",\"text\":\"Puritan\"},{\"value\":\"Quattrocento\",\"text\":\"Quattrocento\"},{\"value\":\"Quattrocento Sans\",\"text\":\"Quattrocento Sans\"},{\"value\":\"Questrial\",\"text\":\"Questrial\"},{\"value\":\"Quicksand\",\"text\":\"Quicksand\"},{\"value\":\"Radley\",\"text\":\"Radley\"},{\"value\":\"Raleway\",\"text\":\"Raleway\"},{\"value\":\"Rationale\",\"text\":\"Rationale\"},{\"value\":\"Redressed\",\"text\":\"Redressed\"},{\"value\":\"Reenie Beanie\",\"text\":\"Reenie Beanie\"},{\"value\":\"Rochester\",\"text\":\"Rochester\"},{\"value\":\"Rock Salt\",\"text\":\"Rock Salt\"},{\"value\":\"Rokkitt\",\"text\":\"Rokkitt\"},{\"value\":\"Ropa Sans\",\"text\":\"Ropa Sans\"},{\"value\":\"Rosario\",\"text\":\"Rosario\"},{\"value\":\"Ruslan Display\",\"text\":\"Ruslan Display\"},{\"value\":\"Schoolbell\",\"text\":\"Schoolbell\"},{\"value\":\"Shadows Into Light\",\"text\":\"Shadows Into Light\"},{\"value\":\"Shanti\",\"text\":\"Shanti\"},{\"value\":\"Short Stack\",\"text\":\"Short Stack\"},{\"value\":\"Sigmar One\",\"text\":\"Sigmar One\"},{\"value\":\"Signika\",\"text\":\"Signika\"},{\"value\":\"Signika Negative\",\"text\":\"Signika Negative\"},{\"value\":\"Six Caps\",\"text\":\"Six Caps\"},{\"value\":\"Slackey\",\"text\":\"Slackey\"},{\"value\":\"Smokum\",\"text\":\"Smokum\"},{\"value\":\"Smythe\",\"text\":\"Smythe\"},{\"value\":\"Sniglet\",\"text\":\"Sniglet\"},{\"value\":\"Snippet\",\"text\":\"Snippet\"},{\"value\":\"Special Elite\",\"text\":\"Special Elite\"},{\"value\":\"Stardos Stencil\",\"text\":\"Stardos Stencil\"},{\"value\":\"Sue Ellen Francisco\",\"text\":\"Sue Ellen Francisco\"},{\"value\":\"Sunshiney\",\"text\":\"Sunshiney\"},{\"value\":\"Swanky and Moo Moo\",\"text\":\"Swanky and Moo Moo\"},{\"value\":\"Syncopate\",\"text\":\"Syncopate\"},{\"value\":\"Tangerine\",\"text\":\"Tangerine\"},{\"value\":\"Telex\",\"text\":\"Telex\"},{\"value\":\"Tenor Sans\",\"text\":\"Tenor Sans\"},{\"value\":\"Terminal Dosis Light\",\"text\":\"Terminal Dosis Light\"},{\"value\":\"The Girl Next Door\",\"text\":\"The Girl Next Door\"},{\"value\":\"Tienne\",\"text\":\"Tienne\"},{\"value\":\"Tinos\",\"text\":\"Tinos\"},{\"value\":\"Tulpen One\",\"text\":\"Tulpen One\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"},{\"value\":\"Ultra\",\"text\":\"Ultra\"},{\"value\":\"UnifrakturCook\",\"text\":\"UnifrakturCook\"},{\"value\":\"UnifrakturMaguntia\",\"text\":\"UnifrakturMaguntia\"},{\"value\":\"Unkempt\",\"text\":\"Unkempt\"},{\"value\":\"Unna\",\"text\":\"Unna\"},{\"value\":\"VT323\",\"text\":\"VT323\"},{\"value\":\"Varela\",\"text\":\"Varela\"},{\"value\":\"Varela Round\",\"text\":\"Varela Round\"},{\"value\":\"Vibur\",\"text\":\"Vibur\"},{\"value\":\"Viga\",\"text\":\"Viga\"},{\"value\":\"Vidaloka\",\"text\":\"Vidaloka\"},{\"value\":\"Volkhov\",\"text\":\"Volkhov\"},{\"value\":\"Vollkorn\",\"text\":\"Vollkorn\"},{\"value\":\"Voltaire\",\"text\":\"Voltaire\"},{\"value\":\"Waiting for the Sunrise\",\"text\":\"Waiting for the Sunrise\"},{\"value\":\"Wallpoet\",\"text\":\"Wallpoet\"},{\"value\":\"Walter Turncoat\",\"text\":\"Walter Turncoat\"},{\"value\":\"Wire One\",\"text\":\"Wire One\"},{\"value\":\"Yanone Kaffeesatz\",\"text\":\"Yanone Kaffeesatz\"},{\"value\":\"Yellowtail\",\"text\":\"Yellowtail\"},{\"value\":\"Yeseva One\",\"text\":\"Yeseva One\"},{\"value\":\"Zeyada\",\"text\":\"Zeyada\"}],\n        selectedIndex: 40,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"LatinExtended":{"name":"jform[params][moduleparametersTab][theme][textfont]family","id":"jformparamsmoduleparametersTabthemetextfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetextfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Andika<br \/>Andika<br \/>Anonymous Pro<br \/>Anton<br \/>Caudex<br \/>Didact Gothic<br \/>EB Garamond<br \/>Forum<br \/>Francois One<br \/>Gentium Basic<br \/>Gentium Book Basic<br \/>Istok Web<br \/>Jura<br \/>Kelly Slab<br \/>Lobster<br \/>MedievalSharp<br \/>Modern Antiqua<br \/>Neuton<br \/>Open Sans<br \/>Open Sans Condensed<br \/>Patrick Hand<br \/>Play<br \/>Ruslan Display<br \/>Tenor Sans<br \/>Ubuntu<br \/>Varela<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]family\" id=\"jformparamsmoduleparametersTabthemetextfontfamily\" value=\"Andika\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetextfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Andika<\\\/div><div class=\\\"listelement\\\">Anonymous Pro<\\\/div><div class=\\\"listelement\\\">Anton<\\\/div><div class=\\\"listelement\\\">Caudex<\\\/div><div class=\\\"listelement\\\">Didact Gothic<\\\/div><div class=\\\"listelement\\\">EB Garamond<\\\/div><div class=\\\"listelement\\\">Forum<\\\/div><div class=\\\"listelement\\\">Francois One<\\\/div><div class=\\\"listelement\\\">Gentium Basic<\\\/div><div class=\\\"listelement\\\">Gentium Book Basic<\\\/div><div class=\\\"listelement\\\">Istok Web<\\\/div><div class=\\\"listelement\\\">Jura<\\\/div><div class=\\\"listelement\\\">Kelly Slab<\\\/div><div class=\\\"listelement\\\">Lobster<\\\/div><div class=\\\"listelement\\\">MedievalSharp<\\\/div><div class=\\\"listelement\\\">Modern Antiqua<\\\/div><div class=\\\"listelement\\\">Neuton<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><div class=\\\"listelement\\\">Patrick Hand<\\\/div><div class=\\\"listelement\\\">Play<\\\/div><div class=\\\"listelement\\\">Ruslan Display<\\\/div><div class=\\\"listelement\\\">Tenor Sans<\\\/div><div class=\\\"listelement\\\">Ubuntu<\\\/div><div class=\\\"listelement\\\">Varela<\\\/div><\\\/div>\",\n        options: [{\"value\":\"Andika\",\"text\":\"Andika\"},{\"value\":\"Anonymous Pro\",\"text\":\"Anonymous Pro\"},{\"value\":\"Anton\",\"text\":\"Anton\"},{\"value\":\"Caudex\",\"text\":\"Caudex\"},{\"value\":\"Didact Gothic\",\"text\":\"Didact Gothic\"},{\"value\":\"EB Garamond\",\"text\":\"EB Garamond\"},{\"value\":\"Forum\",\"text\":\"Forum\"},{\"value\":\"Francois One\",\"text\":\"Francois One\"},{\"value\":\"Gentium Basic\",\"text\":\"Gentium Basic\"},{\"value\":\"Gentium Book Basic\",\"text\":\"Gentium Book Basic\"},{\"value\":\"Istok Web\",\"text\":\"Istok Web\"},{\"value\":\"Jura\",\"text\":\"Jura\"},{\"value\":\"Kelly Slab\",\"text\":\"Kelly Slab\"},{\"value\":\"Lobster\",\"text\":\"Lobster\"},{\"value\":\"MedievalSharp\",\"text\":\"MedievalSharp\"},{\"value\":\"Modern Antiqua\",\"text\":\"Modern Antiqua\"},{\"value\":\"Neuton\",\"text\":\"Neuton\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"},{\"value\":\"Patrick Hand\",\"text\":\"Patrick Hand\"},{\"value\":\"Play\",\"text\":\"Play\"},{\"value\":\"Ruslan Display\",\"text\":\"Ruslan Display\"},{\"value\":\"Tenor Sans\",\"text\":\"Tenor Sans\"},{\"value\":\"Ubuntu\",\"text\":\"Ubuntu\"},{\"value\":\"Varela\",\"text\":\"Varela\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"Vietnamese":{"name":"jform[params][moduleparametersTab][theme][textfont]family","id":"jformparamsmoduleparametersTabthemetextfontfamily","html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetextfontfamily\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">EB Garamond<br \/>EB Garamond<br \/>Open Sans<br \/>Open Sans Condensed<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]family\" id=\"jformparamsmoduleparametersTabthemetextfontfamily\" value=\"EB Garamond\"\/><\/div><\/div>","script":"dojo.addOnLoad(function(){\n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetextfontfamily\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">EB Garamond<\\\/div><div class=\\\"listelement\\\">Open Sans<\\\/div><div class=\\\"listelement\\\">Open Sans Condensed<\\\/div><\\\/div>\",\n        options: [{\"value\":\"EB Garamond\",\"text\":\"EB Garamond\"},{\"value\":\"Open Sans\",\"text\":\"Open Sans\"},{\"value\":\"Open Sans Condensed\",\"text\":\"Open Sans Condensed\"}],\n        selectedIndex: 0,\n        height: \"10\",\n        fireshow: 1\n      });\n    });"},"html":"<div style='position:relative;'><div id=\"offlajnlistcontainerjformparamsmoduleparametersTabthemetextfonttype\" class=\"gk_hack offlajnlistcontainer\"><div class=\"gk_hack offlajnlist\"><span class=\"offlajnlistcurrent\">Latin<br \/>Alternative fonts<br \/>Cyrillic<br \/>CyrillicExtended<br \/>Greek<br \/>GreekExtended<br \/>Khmer<br \/>Latin<br \/>LatinExtended<br \/>Vietnamese<br \/><\/span><div class=\"offlajnlistbtn\"><span><\/span><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]type\" id=\"jformparamsmoduleparametersTabthemetextfonttype\" value=\"Latin\"\/><\/div><\/div>"},"size":{"name":"jform[params][moduleparametersTab][theme][textfont]size","id":"jformparamsmoduleparametersTabthemetextfontsize","html":"<div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemetextfontsize\"><input  size=\"1\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemetextfontsizeinput\" value=\"12\"><div class=\"offlajntext_increment\">\n                <div class=\"offlajntext_increment_up arrow\"><\/div>\n                <div class=\"offlajntext_increment_down arrow\"><\/div>\n      <\/div><\/div><div class=\"offlajnswitcher\">\r\n            <div class=\"offlajnswitcher_inner\" id=\"offlajnswitcher_innerjformparamsmoduleparametersTabthemetextfontsizeunit\"><\/div>\r\n    <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]size[unit]\" id=\"jformparamsmoduleparametersTabthemetextfontsizeunit\" value=\"px\" \/><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]size\" id=\"jformparamsmoduleparametersTabthemetextfontsize\" value=\"12||px\">"},"color":{"name":"jform[params][moduleparametersTab][theme][textfont]color","id":"jformparamsmoduleparametersTabthemetextfontcolor","html":"<div class=\"offlajncolor\"><input type=\"text\" name=\"jform[params][moduleparametersTab][theme][textfont]color\" id=\"jformparamsmoduleparametersTabthemetextfontcolor\" value=\"5d5c5c\" class=\"color wa\" size=\"12\" \/><\/div>"},"bold":{"name":"jform[params][moduleparametersTab][theme][textfont]bold","id":"jformparamsmoduleparametersTabthemetextfontbold","html":"<div id=\"offlajnonoffjformparamsmoduleparametersTabthemetextfontbold\" class=\"gk_hack onoffbutton\">\n                <div class=\"gk_hack onoffbutton_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnonoff\/images\/bold.png);\"><\/div>\n      <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]bold\" id=\"jformparamsmoduleparametersTabthemetextfontbold\" value=\"0\" \/>"},"italic":{"name":"jform[params][moduleparametersTab][theme][textfont]italic","id":"jformparamsmoduleparametersTabthemetextfontitalic","html":"<div id=\"offlajnonoffjformparamsmoduleparametersTabthemetextfontitalic\" class=\"gk_hack onoffbutton\">\n                <div class=\"gk_hack onoffbutton_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnonoff\/images\/italic.png);\"><\/div>\n      <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]italic\" id=\"jformparamsmoduleparametersTabthemetextfontitalic\" value=\"0\" \/>"},"underline":{"name":"jform[params][moduleparametersTab][theme][textfont]underline","id":"jformparamsmoduleparametersTabthemetextfontunderline","html":"<div id=\"offlajnonoffjformparamsmoduleparametersTabthemetextfontunderline\" class=\"gk_hack onoffbutton\">\n                <div class=\"gk_hack onoffbutton_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnonoff\/images\/underline.png);\"><\/div>\n      <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]underline\" id=\"jformparamsmoduleparametersTabthemetextfontunderline\" value=\"0\" \/>"},"align":{"name":"jform[params][moduleparametersTab][theme][textfont]align","id":"jformparamsmoduleparametersTabthemetextfontalign","html":"<div class=\"offlajnradiocontainerimage\" id=\"offlajnradiocontainerjformparamsmoduleparametersTabthemetextfontalign\"><div class=\"radioelement first selected\"><div class=\"radioelement_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnradio\/images\/left_align.png);\"><\/div><\/div><div class=\"radioelement \"><div class=\"radioelement_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnradio\/images\/center_align.png);\"><\/div><\/div><div class=\"radioelement  last\"><div class=\"radioelement_img\" style=\"background-image: url(http:\/\/emundus.local\/administrator\/..\/modules\/mod_improved_ajax_login\/params\/offlajnradio\/images\/right_align.png);\"><\/div><\/div><div class=\"clear\"><\/div><\/div><input type=\"hidden\" id=\"jformparamsmoduleparametersTabthemetextfontalign\" name=\"jform[params][moduleparametersTab][theme][textfont]align\" value=\"left\"\/>"},"afont":{"name":"jform[params][moduleparametersTab][theme][textfont]afont","id":"jformparamsmoduleparametersTabthemetextfontafont","html":"<div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemetextfontafont\"><input  size=\"10\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemetextfontafontinput\" value=\"Helvetica\"><\/div><div class=\"offlajnswitcher\">\r\n            <div class=\"offlajnswitcher_inner\" id=\"offlajnswitcher_innerjformparamsmoduleparametersTabthemetextfontafontunit\"><\/div>\r\n    <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]afont[unit]\" id=\"jformparamsmoduleparametersTabthemetextfontafontunit\" value=\"1\" \/><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]afont\" id=\"jformparamsmoduleparametersTabthemetextfontafont\" value=\"Helvetica||1\">"},"tshadow":{"name":"jform[params][moduleparametersTab][theme][textfont]tshadow","id":"jformparamsmoduleparametersTabthemetextfonttshadow","html":"<div id=\"offlajncombine_outerjformparamsmoduleparametersTabthemetextfonttshadow\" class=\"offlajncombine_outer\"><div class=\"offlajncombinefieldcontainer\"><div class=\"offlajncombinefield\"><div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemetextfonttshadow0\"><input  size=\"1\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemetextfonttshadow0input\" value=\"0\"><div class=\"unit\">px<\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]tshadow0\" id=\"jformparamsmoduleparametersTabthemetextfonttshadow0\" value=\"0\"><\/div><\/div><div class=\"offlajncombinefieldcontainer\"><div class=\"offlajncombinefield\"><div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemetextfonttshadow1\"><input  size=\"1\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemetextfonttshadow1input\" value=\"0\"><div class=\"unit\">px<\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]tshadow1\" id=\"jformparamsmoduleparametersTabthemetextfonttshadow1\" value=\"0\"><\/div><\/div><div class=\"offlajncombinefieldcontainer\"><div class=\"offlajncombinefield\"><div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemetextfonttshadow2\"><input  size=\"1\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemetextfonttshadow2input\" value=\"0\"><div class=\"unit\">px<\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]tshadow2\" id=\"jformparamsmoduleparametersTabthemetextfonttshadow2\" value=\"0\"><\/div><\/div><div class=\"offlajncombinefieldcontainer\"><div class=\"offlajncombinefield\"><div class=\"offlajncolor\"><input type=\"text\" name=\"jform[params][moduleparametersTab][theme][textfont]tshadow3\" id=\"jformparamsmoduleparametersTabthemetextfonttshadow3\" value=\"000000\" class=\"color \" size=\"12\" \/><\/div><\/div><\/div><div class=\"offlajncombinefieldcontainer\"><div class=\"offlajncombinefield\"><div class=\"offlajnswitcher\">\r\n            <div class=\"offlajnswitcher_inner\" id=\"offlajnswitcher_innerjformparamsmoduleparametersTabthemetextfonttshadow4\"><\/div>\r\n    <\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]tshadow4\" id=\"jformparamsmoduleparametersTabthemetextfonttshadow4\" value=\"0\" \/><\/div><\/div><div class=\"offlajncombine_hider\"><\/div><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]tshadow\" id=\"jformparamsmoduleparametersTabthemetextfonttshadow\" value='0|*|0|*|0|*|000000|*|0'>"},"lineheight":{"name":"jform[params][moduleparametersTab][theme][textfont]lineheight","id":"jformparamsmoduleparametersTabthemetextfontlineheight","html":"<div class=\"offlajntextcontainer\" id=\"offlajntextcontainerjformparamsmoduleparametersTabthemetextfontlineheight\"><input  size=\"5\" class=\"offlajntext\" type=\"text\" id=\"jformparamsmoduleparametersTabthemetextfontlineheightinput\" value=\"normal\"><\/div><input type=\"hidden\" name=\"jform[params][moduleparametersTab][theme][textfont]lineheight\" id=\"jformparamsmoduleparametersTabthemetextfontlineheight\" value=\"normal\">"}},
          script: "dojo.addOnLoad(function(){\r\n      new OfflajnRadio({\r\n        id: \"jformparamsmoduleparametersTabthemetextfonttab\",\r\n        values: [\"Text\",\"Hover\"],\r\n        map: {\"Text\":0,\"Hover\":1},\r\n        mode: \"\"\r\n      });\r\n    \n      new OfflajnList({\n        name: \"jformparamsmoduleparametersTabthemetextfonttype\",\n        elements: \"<div class=\\\"content\\\"><div class=\\\"listelement\\\">Alternative fonts<\\\/div><div class=\\\"listelement\\\">Cyrillic<\\\/div><div class=\\\"listelement\\\">CyrillicExtended<\\\/div><div class=\\\"listelement\\\">Greek<\\\/div><div class=\\\"listelement\\\">GreekExtended<\\\/div><div class=\\\"listelement\\\">Khmer<\\\/div><div class=\\\"listelement\\\">Latin<\\\/div><div class=\\\"listelement\\\">LatinExtended<\\\/div><div class=\\\"listelement\\\">Vietnamese<\\\/div><\\\/div>\",\n        options: [{\"value\":\"0\",\"text\":\"Alternative fonts\"},{\"value\":\"Cyrillic\",\"text\":\"Cyrillic\"},{\"value\":\"CyrillicExtended\",\"text\":\"CyrillicExtended\"},{\"value\":\"Greek\",\"text\":\"Greek\"},{\"value\":\"GreekExtended\",\"text\":\"GreekExtended\"},{\"value\":\"Khmer\",\"text\":\"Khmer\"},{\"value\":\"Latin\",\"text\":\"Latin\"},{\"value\":\"LatinExtended\",\"text\":\"LatinExtended\"},{\"value\":\"Vietnamese\",\"text\":\"Vietnamese\"}],\n        selectedIndex: 6,\n        height: 0,\n        fireshow: 0\n      });\n    dojo.addOnLoad(function(){ \r\n      new OfflajnSwitcher({\r\n        id: \"jformparamsmoduleparametersTabthemetextfontsizeunit\",\r\n        units: [\"px\",\"em\"],\r\n        values: [\"px\",\"em\"],\r\n        map: {\"px\":0,\"em\":1},\r\n        mode: 0,\r\n        url: \"http:\\\/\\\/emundus.local\\\/administrator\\\/..\\\/modules\\\/mod_improved_ajax_login\\\/params\\\/offlajnswitcher\\\/images\\\/\"\r\n      }); \r\n    });\n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemetextfontsize\",\n        validation: \"int\",\n        attachunit: \"\",\n        mode: \"increment\",\n        scale: \"1\",\n        minus: 0,\n        onoff: \"\"\n      }); \n    \n    var el = dojo.byId(\"jformparamsmoduleparametersTabthemetextfontcolor\");\n    jQuery.fn.jPicker.defaults.images.clientPath=\"\/modules\/mod_improved_ajax_login\/params\/offlajndashboard\/..\/offlajncolor\/offlajncolor\/jpicker\/images\/\";\n    el.alphaSupport=false; \n    el.c = jQuery(\"#jformparamsmoduleparametersTabthemetextfontcolor\").jPicker({\n        window:{\n          expandable: true,\n          alphaSupport: false}\n        });\n    dojo.connect(el, \"change\", function(){\n      this.c[0].color.active.val(\"hex\", this.value);\n    });\n    \n      new OfflajnOnOff({\n        id: \"jformparamsmoduleparametersTabthemetextfontbold\",\n        mode: \"button\",\n        imgs: \"\"\n      }); \n    \n      new OfflajnOnOff({\n        id: \"jformparamsmoduleparametersTabthemetextfontitalic\",\n        mode: \"button\",\n        imgs: \"\"\n      }); \n    \n      new OfflajnOnOff({\n        id: \"jformparamsmoduleparametersTabthemetextfontunderline\",\n        mode: \"button\",\n        imgs: \"\"\n      }); \n    \r\n      new OfflajnRadio({\r\n        id: \"jformparamsmoduleparametersTabthemetextfontalign\",\r\n        values: [\"left\",\"center\",\"right\"],\r\n        map: {\"left\":0,\"center\":1,\"right\":2},\r\n        mode: \"image\"\r\n      });\r\n    dojo.addOnLoad(function(){ \r\n      new OfflajnSwitcher({\r\n        id: \"jformparamsmoduleparametersTabthemetextfontafontunit\",\r\n        units: [\"ON\",\"OFF\"],\r\n        values: [\"1\",\"0\"],\r\n        map: {\"1\":0,\"0\":1},\r\n        mode: 0,\r\n        url: \"http:\\\/\\\/emundus.local\\\/administrator\\\/..\\\/modules\\\/mod_improved_ajax_login\\\/params\\\/offlajnswitcher\\\/images\\\/\"\r\n      }); \r\n    });\n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemetextfontafont\",\n        validation: \"\",\n        attachunit: \"\",\n        mode: \"\",\n        scale: \"\",\n        minus: 0,\n        onoff: \"1\"\n      }); \n    \n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemetextfonttshadow0\",\n        validation: \"float\",\n        attachunit: \"px\",\n        mode: \"\",\n        scale: \"\",\n        minus: 0,\n        onoff: \"\"\n      }); \n    \n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemetextfonttshadow1\",\n        validation: \"float\",\n        attachunit: \"px\",\n        mode: \"\",\n        scale: \"\",\n        minus: 0,\n        onoff: \"\"\n      }); \n    \n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemetextfonttshadow2\",\n        validation: \"float\",\n        attachunit: \"px\",\n        mode: \"\",\n        scale: \"\",\n        minus: 0,\n        onoff: \"\"\n      }); \n    \n    var el = dojo.byId(\"jformparamsmoduleparametersTabthemetextfonttshadow3\");\n    jQuery.fn.jPicker.defaults.images.clientPath=\"\/modules\/mod_improved_ajax_login\/params\/offlajndashboard\/..\/offlajncolor\/offlajncolor\/jpicker\/images\/\";\n    el.alphaSupport=true; \n    el.c = jQuery(\"#jformparamsmoduleparametersTabthemetextfonttshadow3\").jPicker({\n        window:{\n          expandable: true,\n          alphaSupport: true}\n        });\n    dojo.connect(el, \"change\", function(){\n      this.c[0].color.active.val(\"hex\", this.value);\n    });\n    dojo.addOnLoad(function(){ \r\n      new OfflajnSwitcher({\r\n        id: \"jformparamsmoduleparametersTabthemetextfonttshadow4\",\r\n        units: [\"ON\",\"OFF\"],\r\n        values: [\"1\",\"0\"],\r\n        map: {\"1\":0,\"0\":1},\r\n        mode: 0,\r\n        url: \"http:\\\/\\\/emundus.local\\\/administrator\\\/..\\\/modules\\\/mod_improved_ajax_login\\\/params\\\/offlajnswitcher\\\/images\\\/\"\r\n      }); \r\n    });\r\n      new OfflajnCombine({\r\n        id: \"jformparamsmoduleparametersTabthemetextfonttshadow\",\r\n        num: 5,\r\n        switcherid: \"jformparamsmoduleparametersTabthemetextfonttshadow4\",\r\n        hideafter: \"0\"\r\n      }); \r\n    \n      new OfflajnText({\n        id: \"jformparamsmoduleparametersTabthemetextfontlineheight\",\n        validation: \"\",\n        attachunit: \"\",\n        mode: \"\",\n        scale: \"\",\n        minus: 0,\n        onoff: \"\"\n      }); \n    });"
        });
    

      new OfflajnText({
        id: "jformparamsmoduleparametersTabthemesmalltext",
        validation: "int",
        attachunit: "px",
        mode: "",
        scale: "",
        minus: 0,
        onoff: ""
      }); 
    

    var el = dojo.byId("jformparamsmoduleparametersTabthemeerrorcolor");
    jQuery.fn.jPicker.defaults.images.clientPath="/modules/mod_improved_ajax_login/params/offlajndashboard/../offlajncolor/offlajncolor/jpicker/images/";
    el.alphaSupport=false; 
    el.c = jQuery("#jformparamsmoduleparametersTabthemeerrorcolor").jPicker({
        window:{
          expandable: true,
          alphaSupport: false}
        });
    dojo.connect(el, "change", function(){
      this.c[0].color.active.val("hex", this.value);
    });
    
jQuery.fn.jPicker.defaults.images.clientPath="http://emundus.local/administrator/../modules/mod_improved_ajax_login/params/offlajncolor/offlajncolor/jpicker/images/";

      new OfflajnOnOff({
        id: "jformparamsmoduleparametersTabthemeerrorgradonoff",
        mode: "",
        imgs: ""
      }); 
    

      new OfflajnGradient({
        hidden: dojo.byId("jformparamsmoduleparametersTabthemeerrorgrad"),
        switcher: dojo.byId("jformparamsmoduleparametersTabthemeerrorgradonoff"),
        onoff: 0,
        start: dojo.byId("jformparamsmoduleparametersTabthemeerrorgradstart"),
        end: dojo.byId("jformparamsmoduleparametersTabthemeerrorgradstop")
      });
    

    var el = dojo.byId("jformparamsmoduleparametersTabthemehintcolor");
    jQuery.fn.jPicker.defaults.images.clientPath="/modules/mod_improved_ajax_login/params/offlajndashboard/../offlajncolor/offlajncolor/jpicker/images/";
    el.alphaSupport=false; 
    el.c = jQuery("#jformparamsmoduleparametersTabthemehintcolor").jPicker({
        window:{
          expandable: true,
          alphaSupport: false}
        });
    dojo.connect(el, "change", function(){
      this.c[0].color.active.val("hex", this.value);
    });
    
jQuery.fn.jPicker.defaults.images.clientPath="http://emundus.local/administrator/../modules/mod_improved_ajax_login/params/offlajncolor/offlajncolor/jpicker/images/";

      new OfflajnOnOff({
        id: "jformparamsmoduleparametersTabthemehintgradonoff",
        mode: "",
        imgs: ""
      }); 
    

      new OfflajnGradient({
        hidden: dojo.byId("jformparamsmoduleparametersTabthemehintgrad"),
        switcher: dojo.byId("jformparamsmoduleparametersTabthemehintgradonoff"),
        onoff: 0,
        start: dojo.byId("jformparamsmoduleparametersTabthemehintgradstart"),
        end: dojo.byId("jformparamsmoduleparametersTabthemehintgradstop")
      });
    });
      djConfig = {};})();