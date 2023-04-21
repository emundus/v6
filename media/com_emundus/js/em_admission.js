// to abort all AJAX query at once
$.ajaxQ = (function () {
    var id = 0,
        Q = {};

    $(document).ajaxSend(function (e, jqx) {
        jqx._id = ++id;
        Q[jqx._id] = jqx;
    });
    $(document).ajaxComplete(function (e, jqx) {
        delete Q[jqx._id];
    });

    return {
        abortAll: function () {
            var r = [];
            $.each(Q, function (i, jqx) {
                r.push(jqx._id);
                jqx.abort();
            });
            return r;
        }
    };
})();

function updateText(id, text) {
    // Use ID of .em-textarea or .em-field to get fabrik_id and fnum
    // Written as: [fnum]-[fabrik_id]
    var tmp = id.split("-");
    var fnum = tmp[0],
        fabrik_id = tmp[1];

    console.log('text');


    $('span#' + tmp[0]+'-'+tmp[1] + '-span').addClass('glyphicon-refresh').removeClass('glyphicon-share-alt');

    $.ajax({
        type: "POST",
        url: 'index.php?option=com_emundus&controller=' + $('#view').val() + '&task=changeadmissionvalue',
        dataType: 'json',
        data: ({
            fnum: fnum,
            fabrik_id: fabrik_id,
            value: text
        }),
        success: function (res) {
            $('span#' + tmp[0]+'-'+tmp[1] + '-span').addClass('glyphicon-share-alt').removeClass('glyphicon-refresh').css('color', 'black');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('span#' + tmp[0]+'-'+tmp[1] + '-span').addClass('glyphicon-warning-sign').removeClass('glyphicon-refresh').css('color', 'orange');
        }
    })
}

function toggleRadio(id) {
    // Use ID of .em-radio to get fabrik_id and fnum
    // Written as: [fnum]-[fabrik_id]-[value]
    var tmp = id.split("-");
    
    var fnum = tmp[0],
        fabrik_id = tmp[1],
        newVal = $('#' + id).val();

    $.ajax({
        type: "POST",
        url: 'index.php?option=com_emundus&controller=' + $('#view').val() + '&task=changeadmissionvalue',
        dataType: 'json',
        data: ({
            fnum: fnum,
            fabrik_id: fabrik_id,
            value: newVal
        }),
        success: function (res) {
            $('#' + id).css("border", "solid 1px green");
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#' + id).css("border", "solid 1px red");
        }
    })
}


$(document).ready(function () {

    $(document).on('change', '.em-radio', function (e) {
        $.ajaxQ.abortAll();
        if (e.handle !== true) {
            e.handle = true;

            var id = $(this).attr('id');

            toggleRadio(id);
        }
    });

    $(document).on('click', '.em-textarea', function (e) {
        $.ajaxQ.abortAll();
        if (e.handle !== true) {
            e.handle = true;

            var id = $(this).attr('id');
            var tmp = id.split('-');
            var text = $('textarea#' + tmp[0]+'-'+tmp[1]).val();

            updateText(id, text);
        }
    });

    $(document).on('click', '.em-field', function (e) {
        $.ajaxQ.abortAll();
        if (e.handle !== true) {
            e.handle = true;

            var id = $(this).attr('id');
            var tmp = id.split('-');
            var text = $('input#' + tmp[0]+'-'+tmp[1]).val();

            updateText(id, text);
        }
    });

    // Press enter on input
    $(document).on('keypress', '.admission_input', function (e) {
        $.ajaxQ.abortAll();
        if (e.keyCode == 13) {

            var id = $(this).attr('id');
            var text = $(this).val();

            updateText(id, text);
        }
    });

    // Hover effect for buttons
    $(document).on({
        
        mouseenter: function() {
        
            if ($(this).hasClass('glyphicon-ok'))
                $(this).addClass('glyphicon-ok-circle').removeClass('glyphicon-ok');
            if ($(this).hasClass('glyphicon-remove'))
                $(this).addClass('glyphicon-remove-circle').removeClass('glyphicon-remove');
            if ($(this).hasClass('glyphicon-warning-sign'))
                $(this).css('color', 'orangered');
        
        },
        
        mouseleave: function() {
        
            if ($(this).hasClass('glyphicon-ok-circle'))
                $(this).addClass('glyphicon-ok').removeClass('glyphicon-ok-circle');
            if ($(this).hasClass('glyphicon-remove-circle'))
                $(this).addClass('glyphicon-remove').removeClass('glyphicon-remove-circle');
            if ($(this).hasClass('glyphicon-warning-sign'))
                $(this).css('color', 'orange');

        }

    }, '.em-radio');


    $(document).on({
        
        mouseenter: function() {
            $(this).css('color', 'green');
        },
        
        mouseleave: function() {
            $(this).css('color', 'black');
        }

    }, '.em-textarea');


    $(document).on({
        
        mouseenter: function() {
            $(this).css('color', 'green');
        },
        
        mouseleave: function() {
            $(this).css('color', 'black');
        }

    }, '.em-field');

});
