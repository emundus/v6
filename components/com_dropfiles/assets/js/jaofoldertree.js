// jQuery File Tree Plugin
//
// Version 1.0
//
// Base on the work of Cory S.N. LaViska  A Beautiful Site (http://abeautifulsite.net/)
// Dual-licensed under the GNU General Public License and the MIT License
// Icons from famfamfam silk icon set thanks to http://www.famfamfam.com/lab/icons/silk/
//
// Usage : $('#jao').jaofoldertree(options);
//
// Author: Damien Barrère
// Website: http://www.crac-design.com

(function( $ ) {
  
    var options =  {
      'root'            : '/',
      'script'         : 'connectors/jaoconnector.php',
      'showroot'        : 'root',
      'onclick'         : function(elem,type,file){},
      'oncheck'         : function(elem,checked,type,file){},
      'usecheckboxes'   : true, //can be true files dirs or false
      'expandSpeed'     : 500,
      'collapseSpeed'   : 500,
      'expandEasing'    : null,
      'collapseEasing'  : null,
      'canselect'       : true
    };

    var methods = {
        init : function( o ) {
            if($(this).length==0){
                return;
            }
            $this = $(this);
            $.extend(options,o);
            $(this).data('jaofoldertree', $.extend({}, options));
            if(options.showroot!=''){

                $this.html('<ul class="jaofoldertree"><li class="drive directory collapsed selected">' +
                    '<div class="icon-open-close" data-id="'+options.root+'" data-parent_id="0" data-file="'+options.root+'"></div>'
                    +'<i class="zmdi zmdi-folder dropfiles-folder"></i><a class="catlinks" href="#" data-file="'+options.root+'" data-type="dir">'+options.showroot+'</a></li></ul>');
            }else {
                $this.html('<ul class="jaofoldertree"><li class="drive directory collapsed selected">' +
                    '<div class="icon-open-close" ></div>'
                    +'<a class="catlinks" href="#" data-file="'+options.root+'" data-type="dir"><i class="zmdi zmdi-folder dropfiles-folder"></i></a></li></ul>');
            }
            openfolder(options.root, $this);
        },
        open : function(dir, $this){
            openfolder(dir, $this);
        },
        close : function(dir, $this){
            closedir(dir, $this);
        },
        getchecked : function(){
            var list = new Array();            
            var ik = 0;
            $this.find('input:checked + a').each(function(){
                list[ik] = {
                    type : $(this).attr('data-type'),
                    file : $(this).attr('data-file')
                }                
                ik++;
            });
	    return list;
        },
        getselected : function(){
            var list = new Array();            
            var ik = 0;
            $this.find('li.selected > a').each(function(){
                list[ik] = {
                    type : $(this).attr('data-type'),
                    file : $(this).attr('data-file')
                }                
                ik++;
            });
	    return list;
        }
    };

    openfolder = function(dir, $this) {
        if (typeof ($this) === "undefined") {
            return false;
        }
	    if($this.find('a[data-file="'+dir+'"]').parent().hasClass('expanded')) {
		    return;
	    }
            var ret;
            ret = $.ajax({
                url : options.script,
                data : {id : dir},
                context : $this,
		        dataType: 'json',
                beforeSend : function(){this.find('a[data-file="'+dir+'"]').parent().addClass('wait');}
            }).done(function(datas) {
                ret = '<ul class="jaofoldertree" style="display: none">';
                for(ij=0; ij<datas.length; ij++){
                   
                    classe = 'directory collapsed';                                         
                    ret += '<li class="'+classe+'">';
                    if(datas[ij].count_child > 0){
                        ret += '<div class="icon-open-close" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' +datas[ij].id + '" ></div>';
                    }else{
                        ret += '<div class="icon-open-close no-child" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' +datas[ij].id + '" ></div>';
                    }
                    selectedId = dir;
                    if(datas[ij].id === selectedId.toString()) {
                            ret += '<i class="zmdi zmdi-folder zmdi-folder"></i></i>';
                    }else {
                            ret += '<i class="zmdi zmdi-folder dropfiles-folder"></i>';
                    }
                    
                    ret += '<a href="#" data-file="'+datas[ij].id+'" >'+datas[ij].title+'</a>';
                    ret += '</li>';
                }
                ret += '</ul>';

                this.find('a[data-file="'+dir+'"]').parent().removeClass('wait').removeClass('collapsed').addClass('expanded');
                this.find('a[data-file="'+dir+'"]').after(ret);
                this.find('a[data-file="'+dir+'"]').next().slideDown(options.expandSpeed,options.expandEasing);

                setevents($this);
            }).done(function(){
                //Trigger custom event
                $this.trigger('afteropen');
                $this.trigger('afterupdate');
            });
    }

    closedir = function(dir, $this) {
        if (typeof ($this) === "undefined") {
            return false;
        }

            $this.find('a[data-file="'+dir+'"]').next().slideUp(options.collapseSpeed,options.collapseEasing,function(){$(this).remove();});
            $this.find('a[data-file="'+dir+'"]').parent().removeClass('expanded').addClass('collapsed');
            setevents($this);
            
            //Trigger custom event
            $this.trigger('afterclose');
            $this.trigger('afterupdate');
            
    }

    setevents = function($this){
        var options = $this.data('jaofoldertree');
        $this.find('li a, li .icon-open-close').unbind('click');
        //Bind userdefined function on click an element
        $this.find('li.directory a').bind('click', function(e) {
                                  
            $this.find('li').removeClass('selected');
            $this.find('i.zmdi').removeClass('zmdi-folder').addClass("zmdi-folder");
            $(this).parent().addClass('selected');
            $(this).parent().find(' > i.zmdi').addClass("zmdi-folder");
            var $el = $(this);
            if($el.data('clicked')){
                // Previously clicked, stop actions
                e.preventDefault();
                e.stopPropagation();
            }else{
                // Mark to ignore next click
                $el.data('clicked', true);
                options.onclick(this, $(this).attr('data-file'));
                // Unmark after 1 second
                window.setTimeout(function(){
                    $el.removeData('clicked');
                }, 1000)
            }
            return false;
        });
      
        //Bind for collapse or expand elements
        //$this.find('li.directory.collapsed a').bind('click', function() {methods.open($(this).attr('data-file'));return false;});
       // $this.find('li.directory.expanded a').bind('click', function() {methods.close($(this).attr('data-file'));return false;});        

        $this.find('li.directory.collapsed .icon-open-close').bind('click', function (e) {
            e.preventDefault;

            var $el = $(this);
            if($el.data('clicked')){
                // Previously clicked, stop actions
                e.preventDefault();
                e.stopPropagation();
            }else{
                // Mark to ignore next click
                $el.data('clicked', true);
                methods.open($(this).attr('data-file'), $this);
                // Unmark after 1 second
                window.setTimeout(function(){
                    $el.removeData('clicked');
                }, 1000)
            }
        });

        $this.find('li.directory.expanded .icon-open-close').bind('click', function (e) {
            e.preventDefault;
            var $el = $(this);
            if($el.data('clicked')){
                // Previously clicked, stop actions
                e.preventDefault();
                e.stopPropagation();
            }else{
                // Mark to ignore next click
                $el.data('clicked', true);
                methods.close($(this).attr('data-file'), $this);
                // Unmark after 1 second
                window.setTimeout(function(){
                    $el.removeData('clicked');
                }, 1000)
            }
        });
    }

    $.fn.jaofoldertree = function( method ) {
        // Method calling logic
        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            //error
        }    
  };
})( jQuery );
