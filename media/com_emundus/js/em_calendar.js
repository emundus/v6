function showCalendar() {


    $.ajax({
        type: 'GET',
        url: 'index.php?option=com_dpcalendar&view=calendar&tmpl=component',

        success: function (url) {
            var test = $('#calendar').html(url);
            $('#buttonCalendar').append('<div class = "col-md-2"><button type="button" class = "btn btn-success" id="addEvent">Add a disponibility</button></div>');
            $('#buttonCalendar').append('<div class = "col-md-2"><button type="button" class = "btn btn-success" id="addCalendar">Add a calendar</button></div');
            $('#dpcalendar_component_map').remove();
            $('.dpcal-fb-comments-box').remove();
            $('.clearfix').remove();
            $('.pull-left').remove();
            $('#editEventFormComponent').remove();
        },

        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        },

        complete: function () {
            setInterval(function () {
                var lien = document.getElementsByClassName('fc-event');
                //console.log(lien.attr('href'));
                for (var i = 0; i < lien.length; i++) {
                    lien[i].addEventListener('click', function () {
                        $.ajaxQ.abortAll();
                        var id = parseInt($(this).attr('id'));
                        var href = $(this).attr('href');
                        var hrefSplit = href.split('?');
                        var hrefSplited = hrefSplit[1];
                        var hrefSplitToId = hrefSplited.split('&');
                        var hrefSplitedToId = hrefSplitToId[2];
                        var idToRowId = hrefSplitedToId.split('=');
                        var rowId = idToRowId[1];
                        var url = "index.php?option=com_fabrik&view=form&formid=266&rowid=" + rowId + "&tmpl=component";

                        $('#em-modal-form').modal({
                            backdrop: 'static',
                            keyboard: false
                        }, 'toggle');
                        //  $('#em-modal-form .modal-content').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title">'+Joomla.JText._('LOADING')+'</h4></div><div class="modal-body"><img src="media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'></button></div>');

                        $('.modal-title').empty();
                        $('.modal-title').append($(this).children('a').text());
                        $('.modal-body').empty();
                        if ($('.modal-dialog').hasClass('modal-lg'))
                            $('.modal-dialog').removeClass('modal-lg');
                        $('.modal-body').attr('act-id', id);
                        $('.modal-footer').show();

                        /*$('.modal-footer').append('<div>' +
                        '<p>'+Joomla.JText._('SENT')+'</p>' +
                        '<img src="'+loadingLine+'" alt="loading"/>' +
                        '</div>');*/
                        // $('.modal-content').append('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title">'+Joomla.JText._('LOADING')+'</h4></div><div class="modal-body"><img src="media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'></button></div>');
                        $('.modal-footer').hide();

                        $('.modal-dialog').addClass('modal-lg');
                        $(".modal-body").empty();

                        $(".modal-body").append('<object id="formEventEdit" data="' + url + '" style="width:' + 100 + '%; height:' + window.getHeight() + 'px; border:none"></object>');


                        // $(".modal-body").append("<button type='submit' name='btnDelete' id='btnDelete' class='btn btn-danger button' data-dismiss='modal' value='delete'>DELETE</button>");


                    }, false);
                }

            }, 500);

            setInterval(function () {
                $('.tooltipster-base').remove();
            }, 10);

        }

    });

}





$(document).on('click', '#addEvent', function (e) {
    $.ajaxQ.abortAll();
    var id = parseInt($(this).attr('id'));
    var formID = $('#addEventForm').val();
    var url = "index.php?option=com_fabrik&view=form&formid=" + formID + "&tmpl=component";

    $('#em-modal-form').modal({
        backdrop: 'static',
        keyboard: false
    }, 'toggle');
    //  $('#em-modal-form .modal-content').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title">'+Joomla.JText._('LOADING')+'</h4></div><div class="modal-body"><img src="media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'></button></div>');

    $('.modal-title').empty();
    $('.modal-title').append($(this).children('a').text());
    $('.modal-body').empty();
    if ($('.modal-dialog').hasClass('modal-lg'))
        $('.modal-dialog').removeClass('modal-lg');
    $('.modal-body').attr('act-id', id);
    $('.modal-footer').show();

    /*$('.modal-footer').append('<div>' +
    '<p>'+Joomla.JText._('SENT')+'</p>' +
    '<img src="'+loadingLine+'" alt="loading"/>' +
    '</div>');*/
    // $('.modal-content').append('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title">'+Joomla.JText._('LOADING')+'</h4></div><div class="modal-body"><img src="media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'></button></div>');
    $('.modal-footer').hide();

    $('.modal-dialog').addClass('modal-lg');
    $(".modal-body").empty();

    $(".modal-body").append('<object data="' + url + '" style="width:' + 100 + '%; height:' + window.getHeight() + 'px; border:none"></object>');

    setTimeout(function () {
        var btnSave = document.getElementsByClassName('btn');
        //  btnSave[0].style.visibility ="hidden";   

    }, 10000)
});

$(document).on('click', '#addCalendar', function (e) {
    $.ajaxQ.abortAll();
    var id = parseInt($(this).attr('id'));
    var formID = $('#addCalendarForm').val();
    var url = "index.php?option=com_fabrik&view=form&formid=" + formID + "&tmpl=component";

    $('#em-modal-form').modal({
        backdrop: 'static',
        keyboard: false
    }, 'toggle');
    //  $('#em-modal-form .modal-content').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title">'+Joomla.JText._('LOADING')+'</h4></div><div class="modal-body"><img src="media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'></button></div>');

    $('.modal-title').empty();
    $('.modal-title').append($(this).children('a').text());
    $('.modal-body').empty();
    if ($('.modal-dialog').hasClass('modal-lg')) {
        $('.modal-dialog').removeClass('modal-lg');
    }
    $('.modal-body').attr('act-id', id);
    $('.modal-footer').show();

    /*$('.modal-footer').append('<div>' +
    '<p>'+Joomla.JText._('SENT')+'</p>' +
    '<img src="'+loadingLine+'" alt="loading"/>' +
    '</div>');*/
    // $('.modal-content').append('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title">'+Joomla.JText._('LOADING')+'</h4></div><div class="modal-body"><img src="media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'></button></div>');
    $('.modal-footer').hide();

    $('.modal-dialog').addClass('modal-lg');
    $(".modal-body").empty();

    $(".modal-body").append('<object data="' + url + '" style="width:' + 100 + '%; height:' + window.getHeight() + 'px; border:none"></object>');

    setTimeout(function () {
        var btnSave = document.getElementsByClassName('btn');
        console.log(btnSave);
    }, 10000);
    //  btnSave[0].style.visibility ="hidden";

    var tt = document.getElementsByClassName('modal-body');
    console.log(tt);

});





function listCalendar() {

    $.ajax({
        type: 'GET',
        url: 'index.php?option=com_fabrik&view=list&listid=276&tmpl=component',

        success: function (url) {

            //var data= 'index.php?option=com_dpcalendar&view=calendar&tmpl=component&Itemid=2667';
            var list = $('#listCalendar').html(url);

            //  var t = $('.fabrik_actions.fabrik_element').append('<object id="formEventEdit" data="'+data+'" style="width:'+100+'%; height:'+window.getHeight()+'px; border:none"></object>');

            $('.btn-group').hide();

            setInterval(function () {
                var lien = document.getElementsByClassName('fabrik_row');

                for (var i = 0; i < lien.length; i++) {
                    lien[i].addEventListener('click', function () {
                        $.ajaxQ.abortAll();
                        var id = $(this).attr('id');
                        var idSplit = id.split('_');
                        var calId = idSplit[6];

                        var url = "index.php?option=com_fabrik&view=form&formid=269&rowid=" + calId + "&tmpl=component";

                        $('#em-modal-form').modal({
                            backdrop: 'static',
                            keyboard: false
                        }, 'toggle');
                        //  $('#em-modal-form .modal-content').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title">'+Joomla.JText._('LOADING')+'</h4></div><div class="modal-body"><img src="media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'></button></div>');

                        $('.modal-title').empty();
                        $('.modal-title').append($(this).children('a').text());
                        $('.modal-body').empty();
                        if ($('.modal-dialog').hasClass('modal-lg'))
                            $('.modal-dialog').removeClass('modal-lg');
                        $('.modal-body').attr('act-id', id);
                        $('.modal-footer').show();

                        /*$('.modal-footer').append('<div>' +
                        '<p>'+Joomla.JText._('SENT')+'</p>' +
                        '<img src="'+loadingLine+'" alt="loading"/>' +
                        '</div>');*/
                        // $('.modal-content').append('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title">'+Joomla.JText._('LOADING')+'</h4></div><div class="modal-body"><img src="media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'></button></div>');
                        $('.modal-footer').hide();

                        $('.modal-dialog').addClass('modal-lg');
                        $(".modal-body").empty();

                        $(".modal-body").append('<object id="formEventEdit" data="' + url + '" style="width:' + 100 + '%; height:' + window.getHeight() + 'px; border:none"></object>');

                        $('.fb_el_jos_categories___title').remove();


                    }, false);
                }

            }, 500);


        },

        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        },

    });

}


function listCandidate() {


    $.ajax({
        type: 'GET',
        url: 'index.php?option=com_fabrik&view=list&listid=278&tmpl=component',

        success: function (url) {
            var list = $('#listCandidate').html(url);

            setInterval(function () {
                var lien = document.getElementsByClassName('fabrik_row');

                for (var i = 0; i < lien.length; i++) {
                    lien[i].addEventListener('click', function () {
                        $.ajaxQ.abortAll();
                        var id = $(this).attr('id');
                        var idSplit = id.split('_');
                        var calId = idSplit[6];

                        console.log(id);
                        console.log(calId);

                        var url = "index.php?option=com_fabrik&view=form&formid=271&rowid=" + calId + "&tmpl=component";

                        $('#em-modal-form').modal({
                            backdrop: 'static',
                            keyboard: false
                        }, 'toggle');
                        //  $('#em-modal-form .modal-content').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title">'+Joomla.JText._('LOADING')+'</h4></div><div class="modal-body"><img src="media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'></button></div>');

                        $('.modal-title').empty();
                        $('.modal-title').append($(this).children('a').text());
                        $('.modal-body').empty();
                        if ($('.modal-dialog').hasClass('modal-lg')) {
                            $('.modal-dialog').removeClass('modal-lg');
                        }
                        $('.modal-body').attr('act-id', id);
                        $('.modal-footer').show();

                        /*$('.modal-footer').append('<div>' +
                        '<p>'+Joomla.JText._('SENT')+'</p>' +
                        '<img src="'+loadingLine+'" alt="loading"/>' +
                        '</div>');*/
                        // $('.modal-content').append('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title">'+Joomla.JText._('LOADING')+'</h4></div><div class="modal-body"><img src="media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'></button></div>');
                        $('.modal-footer').hide();

                        $('.modal-dialog').addClass('modal-lg');
                        $(".modal-body").empty();

                        $(".modal-body").append('<object id="formEventEdit" data="' + url + '" style="width:' + 100 + '%; height:' + window.getHeight() + 'px; border:none"></object>');

                        $('.fb_el_jos_categories___title').remove();


                    }, false);
                }

            }, 500);


        },

        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        },





    });

}


function listTimesValidated() {


    $.ajax({
        type: 'GET',
        url: 'index.php?option=com_fabrik&view=list&listid=282&tmpl=component',

        success: function (url) {
            var list = $('#listTimesValidated').html(url);

            $('.btn-group').append('<a type="submit" class="btn btn-danger" id = "cancelInterview">Cancel the interview</a>');
            $('a').remove(":contains('View')");
            //$('').button().hide();


        },

        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        },





    });

}

function listInterviewCoordinator() {


    $.ajax({
        type: 'GET',
        url: 'index.php?option=com_fabrik&view=list&listid=280&tmpl=component',

        success: function (url) {
            var list = $('#listInterviews').html(url);

            $('.btn-group').append('<a type="submit" class="btn btn-danger" id = "cancelInterviews">Cancel the interview</a>');
            $('a').remove(":contains('View')");
            //$('').button().hide();


        },

        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        },





    });

}

function listInterviewManager() {


    $.ajax({
        type: 'GET',
        url: 'index.php?option=com_fabrik&view=list&listid=285&tmpl=component',

        success: function (url) {
            var list = $('#listInterviews').html(url);

            $('.btn-group').append('<a type="submit" class="btn btn-danger" id = "cancelInterviews">Cancel the interview</a>');
            $('a').remove(":contains('View')");
            //$('').button().hide();


        },

        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        },





    });

}


$(document).on('click', '#cancelInterview', function (e) {
    var id = $(this).parent().parent().parent().attr('id');

    var idSplit = id.split('_');
    var calId = idSplit[6];
    var url = "index.php?option=com_fabrik&view=form&formid=275&rowid=" + calId + "&tmpl=component"



    $('#em-modal-form').modal({
        backdrop: 'static',
        keyboard: false
    }, 'toggle');
    //  $('#em-modal-form .modal-content').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title">'+Joomla.JText._('LOADING')+'</h4></div><div class="modal-body"><img src="media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'></button></div>');

    $('.modal-title').empty();
    $('.modal-title').append($(this).children('a').text());
    $('.modal-body').empty();
    if ($('.modal-dialog').hasClass('modal-lg')) {
        $('.modal-dialog').removeClass('modal-lg');
    }
    $('.modal-body').attr('act-id', id);
    $('.modal-footer').show();

    /*$('.modal-footer').append('<div>' +
    '<p>'+Joomla.JText._('SENT')+'</p>' +
    '<img src="'+loadingLine+'" alt="loading"/>' +
    '</div>');*/
    // $('.modal-content').append('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title">'+Joomla.JText._('LOADING')+'</h4></div><div class="modal-body"><img src="media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'></button></div>');
    $('.modal-footer').hide();

    $('.modal-dialog').addClass('modal-lg');
    $(".modal-body").empty();

    $(".modal-body").append('<object id="formEventEdit" data="' + url + '" style="width:' + 100 + '%; height:' + window.getHeight() + 'px; border:none"></object>');

    $('.fb_el_jos_categories___title').remove();
})


$(document).on('click', '#cancelInterviews', function (e) {
    var id = $(this).parent().parent().parent().attr('id');

    var idSplit = id.split('_');
    var calId = idSplit[6];
    var url = "index.php?option=com_fabrik&view=form&formid=273&rowid=" + calId + "&tmpl=component"



    $('#em-modal-form').modal({
        backdrop: 'static',
        keyboard: false
    }, 'toggle');
    //  $('#em-modal-form .modal-content').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title">'+Joomla.JText._('LOADING')+'</h4></div><div class="modal-body"><img src="media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'></button></div>');

    $('.modal-title').empty();
    $('.modal-title').append($(this).children('a').text());
    $('.modal-body').empty();
    if ($('.modal-dialog').hasClass('modal-lg')) {
        $('.modal-dialog').removeClass('modal-lg');
    }
    $('.modal-body').attr('act-id', id);
    $('.modal-footer').show();

    /*$('.modal-footer').append('<div>' +
    '<p>'+Joomla.JText._('SENT')+'</p>' +
    '<img src="'+loadingLine+'" alt="loading"/>' +
    '</div>');*/
    // $('.modal-content').append('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title">'+Joomla.JText._('LOADING')+'</h4></div><div class="modal-body"><img src="media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'></button></div>');
    $('.modal-footer').hide();

    $('.modal-dialog').addClass('modal-lg');
    $(".modal-body").empty();

    $(".modal-body").append('<object id="formEventEdit" data="' + url + '" style="width:' + 100 + '%; height:' + window.getHeight() + 'px; border:none"></object>');

    $('.fb_el_jos_categories___title').remove();
})