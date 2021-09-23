var dropfilesColorboxInit;
jQuery(document).ready(function($) {
    var videoTypes = ['m4a','mp4','webm','ogg','ogv','flv'];
    var audioTypes = ['mp3','wav','wma'];
    var imageTypes = ['jpg','png','gif','jpeg','jpe','bmp','ico','tiff','tif','svg','svgz'];
    (dropfilesColorboxInit = function(){

        $('#dropfiles-results .dropfile-file-link').each(function() {
            $(this).unbind('click').click(function (e) {
                e.preventDefault();
                if($(this).data('remoteurl') ) {
                    window.open( $(this).attr('href'),'_blank');
                }else {
                    window.location.href = $(this).attr('href');
                }
            })
        });

        $('.dropfileslightbox').each(function(){
             var filetype= $(this).data('file-type');
             sW  = $(window).width();
             sH  = $(window).height();
             sR = sW/sH;
             $(this).unbind('click').click(function(e){
                   e.preventDefault();
                    fileid = $(this).data('id');
                    catid = $(this).data('catid')   ;
                    downloadLink = dropfilesBaseUrl+'index.php?option=com_dropfiles&task=frontfile.download&&id='+fileid+'&catid='+catid+'&preview=1';

                    html ='<div class="dropblock">';
                    html +=' <a href="#" id="dropblock-close"><i class="modal-close-icon"></i></a>';
                    if(audioTypes.indexOf(filetype) > -1) { //is audio
                        html +='<audio src="'+downloadLink+'"  class="video-js vjs-default-skin" id="player-'+fileid+'" controls="controls" preload="auto" autoplay="true"> ' ;
                        html +=' <p class="vjs-no-js">Your browser does not support the <code>audio</code> element.</p></audio>';
                    }else if(imageTypes.indexOf(filetype)>-1) { //is image
                        html +='<img src="'+downloadLink+'" class="video-js vjs-default-skin" id="player-'+fileid+'" /> ' ;
                   }else if(videoTypes.indexOf(filetype)>-1) {
                        html +='<video width="1000" height="1000" src="'+downloadLink+'"  class="video-js vjs-default-skin" id="player-'+fileid+'" controls="controls" preload="auto" autoplay="true"> ' ;
                        html +=' <p class="vjs-no-js">Your browser does not support the <code>video</code> element.</p></video>';
                    }else { //other type
                        viewlink = $(this).attr('href');
                        //googleViewer = 'https://docs.google.com/viewer?url='+ encodeURIComponent(encodeURI(viewlink))+'&embedded=true';
                        html +='<iframe mozallowfullscreen="true" webkitallowfullscreen="true" allowfullscreen="true" class="cboxIframe"  src="'+viewlink+'" frameborder="0"></iframe>';
                    }
                    html +='</div>';
                    //loader init
                    loader =  $("#dropfiles-box-loading");
                    if(loader.length===0){
                        $('body').append('<div id="dropfiles-box-loading" style="display: none;"><div class="loading"></div></div>');
                        loader = $("#dropfiles-box-loading");
                    }
                    loader.show();

                    $(document).unbind('click', '#dropfiles-box-loading, .dropfiles-loading-close').on('click', '#dropfiles-box-loading, .dropfiles-loading-close', function () {
                     $("#dropfiles-box-loading").remove();
                    });

                     var timeout = 5000; // After 5s display waiting notify
                     // Set time out to display close notification
                     loading = setTimeout(function() {
                         var currentLoading = $('#dropfiles-box-loading');
                         if (currentLoading.length > 0) {
                             $('.dropfiles-loading-status', currentLoading).remove();
                             var status = $('<div class="dropfiles-loading-status" style="text-align:center;">' + Joomla.JText._('COM_DROPFILES_LIGHT_BOX_LOADING_STATUS', 'The preview is still loading, you can <span class="dropfiles-loading-close">cancel</span> it at any time...') + '</div>');
                             currentLoading.append(status);
                         }
                     }, timeout);

                    //player box init
                    pBox = $("#dropfiles-box-player");
                    if(pBox.length===0){
                        $('body').append('<div id="dropfiles-box-player" style="display: none;"></div>');
                        pBox = $("#dropfiles-box-player");
                    }
                    pBox.hide();
                    pBox.empty();
                    pBox.prepend(html);

                     $('#dropblock-close').click(function(e) {
                         e.preventDefault();
                         pBox.hide();
                         if($("#player-"+fileid).length) {
                             myPlayer = videojs("player-"+fileid);
                             myPlayer.dispose();
                         }
                     });

                    pBox.click(function(e){
                        if($(e.target).is('#dropfiles-box-player')){
                            pBox.hide();
                            if($("#player-"+fileid).length) {
                                myPlayer = videojs("player-"+fileid);
                                myPlayer.dispose();
                            }
                        }
                        $('#dropfiles-box-player').unbind('click.box').bind('click.box',function(e){
                            if($(e.target).is('#dropfiles-box-player')){
                                pBox.hide();
                            }
                        });
                    });

                   //player
                    if(imageTypes.indexOf(filetype)>-1) { //is image
                        new_img = new Image();
                        new_img.onload = function() {
                            var img_width  = this.width,
                                img_heigth = this.height;
                            vR = img_width/img_heigth;
                                  if(vR > sR) {
                                        new_vW = parseInt(sW * 0.9);
                                        new_vH = parseInt(new_vW/vR);
                                  } else {
                                        new_vH =  parseInt(sH * 0.9);
                                        new_vW = parseInt(new_vH * vR);
                                  }
                            var imgEl = document.getElementById("player-"+fileid);
                            $(imgEl).css('width',new_vW);
                            $(imgEl).css('height',new_vH) ;
                            centerDropblock(fileid,new_vH/2,new_vW/2);
                            loader.hide();
                            pBox.show();

                        }
                        new_img.src = downloadLink;

                    } else if(videoTypes.indexOf(filetype) > -1 || (audioTypes.indexOf(filetype)>-1) ){ // video or audio
                        videojs("player-"+fileid, {}, function() {
                             // Player (this) is initialized and ready.
                            var myPlayer = this;
                            if(audioTypes.indexOf(filetype) > -1) { //is audio
                                new_vW = 350;
                                new_vH = 60;
                                myPlayer.dimensions(new_vW,new_vH);
                                centerDropblock(fileid,new_vH/2,new_vW/2);
                                loader.hide();
                                pBox.show();

                            } else { //is video

                                myPlayer.on('loadedmetadata', function(){

                                  var v = document.getElementById('player-'+fileid+'_html5_api');
                                  vW  = v.videoWidth;
                                  vH = v.videoHeight;
                                  vR = vW/vH;
                                  if(vR > sR) {
                                        new_vW = parseInt(sW * 0.9);
                                        new_vH = parseInt(new_vW/vR);
                                  } else {
                                        new_vH =  parseInt(sH * 0.9);
                                        new_vW = parseInt(new_vH * vR);
                                  }
                                   myPlayer.dimensions(new_vW,new_vH);
                                   centerDropblock(fileid,new_vH/2,new_vW/2);
                                    loader.hide();
                                    pBox.show();
                                });

                            }

                            //error handling
                            myPlayer.on('error', function() { // error event listener
                                // dispose the old player and its HTML
                                error= myPlayer.error();

                                myPlayer.dispose();
                                pBox.empty();
                                pBox.prepend('<div class="dropblock">'+error.message+'</div>');

                                new_vW= 300; new_vH = 200;
                                var dropblock = pBox.find('.dropblock');
                                dropblock.css('width',new_vW).css('height',new_vH);
                                dropblock.css('margin-top',(-new_vH/2)+'px').css('margin-left',(-new_vW/2)+'px');;

                                loader.hide();
                                pBox.show();
                            })
                        });

                    }else { //other type => use googler viewer

                        new_vW = sW * 0.9;
                        new_vH = sH * 0.9;
                        var dropblock = pBox.find('.dropblock');
                        dropblock.css('width',new_vW).css('height',new_vH);
                        dropblock.css('margin-top',(-new_vH/2)+'px').css('margin-left',(-new_vW/2)+'px');;
                        $('.dropblock iframe').load(function() {
                            loader.hide();
                            pBox.show();
                        })
                    }
                });
         });
    })();

    centerDropblock = function(fileid,margin_top, margin_left) {
        //re-position dropblock
        var imgEl = document.getElementById("player-"+fileid);
        var dropblock = $(imgEl).parent('.dropblock') ;

        dropblock.css('margin-top',(-margin_top)+'px');
        dropblock.css('margin-left',(-margin_left)+'px');
        dropblock.css('height','');
        dropblock.css('width','');
        dropblock.css('top','');
        dropblock.css('left','');
    }
});

jQuery(document).keyup(function(e) {
     if (e.keyCode == 27) { // escape key maps to keycode `27`
        pBox = jQuery("#dropfiles-box-player");
        if(pBox.length){
            pBox.hide();
            if( pBox.find(".video-js").length) {
                var playerId = pBox.find(".video-js").first().attr("id");
                myPlayer = videojs(playerId);
                myPlayer.dispose();
            }
        }
    }
});