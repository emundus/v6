/**
 * Created by yoan on 23/05/14.
 */
// to abort all AJAX query at once
$.ajaxQ = (function(){
    var id = 0, Q = {};

    $(document).ajaxSend(function(e, jqx){
        jqx._id = ++id;
        Q[jqx._id] = jqx;
    });
    $(document).ajaxComplete(function(e, jqx){
        delete Q[jqx._id];
    });

    return {
        abortAll: function(){
            var r = [];
            $.each(Q, function(i, jqx){
                r.push(jqx._id);
                jqx.abort();
            });
            return r;
        }
    };
})();

// to resize iframe
function UpdateIframeSize(id) {
    var iFrames = document.getElementById(id);
    var height = parseInt($($("#"+id).contents()).height())+10;
    iFrames.style.height =  height + 'px';
}

// get url param value
function getUrlParameter(url, sParam) {
    var sPageURL = url;
    var sURLVariables = sPageURL.split('&');

    for (var i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) {
            if ( typeof sParameterName[1] !== 'undefined')
                return sParameterName[1];
            else
                return '';
        }
    }
}

function search() {
    var inputs = [{
        name: 's',
        value: $('#text_s').val(),
        adv_fil : false
    }];

    $('[id^=em-adv-fil-]').each(function(){
        var name = $(this).attr('name');
        inputs.push({
            name: $(this).attr('name'),
            value: $(this).val(),
            adv_fil : true
        });
    });

    $('.em_filters_filedset .chzn-select').each(function () {
        inputs.push({
            name: $(this).attr('name'),
            value: $(this).val(),
            adv_fil : false
        });
    });

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=setfilters',
        data: ({
            val: JSON.stringify(($.extend({}, inputs))),
            multi: false,
            elements: true
        }),
        success: function(result) {
            if (result.status) {
                reloadData($('#view').val());
            }

        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        }

    });
}
function clearchosen(cible){
    $(cible).val("%");
    //$('#select_multiple_programmes option[value="%"]').attr('selected',true);
    $(cible).trigger('chosen:updated');
    // $("#select_multiple_programmes").trigger("chosen:updated");
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i].trim();
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}

var lastIndex = 0;
var loading;

// load applicant list
function reloadData(view)
{
    view = (typeof view === "undefined") ? "files" : view;

    addDimmer();
    $.ajax({
        type: "GET",
        url: 'index.php?option=com_emundus&view='+view+'&layout=data&format=raw&Itemid=' + itemId + '&cfnum=' + cfnum,
        dataType: 'html',
        success: function(data)
        {
            $('.em-dimmer').remove();
            $(".col-md-9 .panel.panel-default").remove();
            $(".col-md-9").append(data);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        }
    })
}

/*fixed head js*/
function fixedHead(){
    var falseTable = document.getElementById("false-table");
    var mainTable = document.getElementById("em-data");
    var clonedElement = mainTable.cloneNode(true);
    clonedElement.id = "";
    falseTable.appendChild(clonedElement);
}

/*
// load Filter
function reloadFilter(view)
{
    view = (typeof view === "undefined") ? "files" : view;

    $.ajax({
        type: "GET",
        url: 'index.php?option=com_emundus&view='+view+'&layout=filters&format=raw&Itemid=' + itemId,
        dataType: 'html',
        success: function(data)
        {
            //$("#em_filters").remove();
            $("#em_filters").append(data);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        }
    })
}
*/
// load Menu action
function reloadActions(view, fnum, onCheck)
{
    view = (typeof view === "undefined") ? "files" : view;
    fnum = (typeof fnum === "undefined") ? 0 : fnum;
    //addDimmer();
    var mutli = 0;
    multi = $('.em-check:checked').length;
    $.ajax({
        type: "GET",
        async: false,
        url: 'index.php?option=com_emundus&view='+view+'&layout=menuactions&format=raw&Itemid=' + itemId+ '&display=none&fnum='+fnum+'&multi='+multi,
        dataType: 'html',
        success: function(data)
        {
            //$('.em-dimmer').remove();
            //$(".col-md-9 .panel.panel-default").remove();
            $(".navbar.navbar-inverse").empty();
            $(".navbar.navbar-inverse").append(data);
            if(onCheck === true)
            {
                menuBar1();
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        }
    })
}

function addDimmer() {
    $('.row').before('<div class="em-dimmer"><img src="' + loading + '" alt=""/></div>');
}

function addElement() {
    $.ajax({
        type: 'GET',
        url: 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=getadvfilters&Itemid=' + itemId,
        dataType: 'json',
        success: function(result) {
            if (result.status)
            {
                var ni = $('#advanced-filters');
                var num = ($("#nb-adv-filter").val() - 1) + 2;
                $("#nb-adv-filter").val(num);
                var newId = 'em-adv-father-' + num;
                ni.append('<fieldset id="' + newId + '"><select class="chzn-select em-filt-select" name="elements" id="elements"><option value="">' + result.
                    default +'</option></select> <button class="btn btn-danger btn-xs" id="suppr-filt"><span class="glyphicon glyphicon-trash" ></span></button></fieldset>');

                var options = '';
                var menu = null;
                var groupe = null;

                for (var i=0 ; i<result.options.length ; i++) {

                    if (Joomla.JText._(result.options[i].title) == "undefined" || Joomla.JText._(result.options[i].title) == "")
                        var menu_tmp = result.options[i].title;
                    else
                        var menu_tmp = Joomla.JText._(result.options[i].title);

                    if (Joomla.JText._(result.options[i].group_label) == "undefined" || Joomla.JText._(result.options[i].group_label) == "")
                        var groupe_tmp = result.options[i].group_label;
                    else
                        var groupe_tmp = Joomla.JText._(result.options[i].group_label);

                    if (menu != menu_tmp) {
                        options += '<optgroup label="________________________________"><option disabled class="emundus_search_elm" value="-">' + menu_tmp.toUpperCase() + '</option></optgroup>';
                        menu = menu_tmp;
                    }

                    if (groupe != null && (groupe != groupe_tmp))
                        options += '</optgroup>';

                    if (groupe != groupe_tmp) {
                        options += '<optgroup label=">> ' + groupe_tmp + '">';
                        groupe = groupe_tmp;
                    }

                    if (Joomla.JText._(result.options[i].element_label) == "undefined" || Joomla.JText._(result.options[i].element_label) == "")
                        var elt_label = result.options[i].element_label;
                    else
                        var elt_label = Joomla.JText._(result.options[i].element_label);


                    options += '<option class="emundus_search_elm" value="' + result.options[i].id + '">' + elt_label + '</option>';
                }
                $('#' + newId + ' #elements').append(options);
                $(".chzn-select").chosen({
                    width: "75%"
                });
            }

        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        }
    })

}

function refreshFilter(view) {
    view = (typeof view === "undefined") ? "files" : view;
    $.ajax({
        type: "GET",
        url: 'index.php?option=com_emundus&view='+view+'&layout=filters&format=raw&Itemid=' + itemId,
        dataType: 'html',
        success: function(data) {
            $("#em-files-filters .panel-body").empty();
            $("#em-files-filters .panel-body").append(data);
            $('.chzn-select').chosen();
            reloadData($('#view').val());
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        }
    });
}

function tableOrder(order) {
    $.ajax({
        type: 'POST',
        url: 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=order',
        dataType: 'json',
        data: {
            filter_order: order
        },
        success: function(result) {
            if (result.status) {
                reloadData($('#view').val());
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        }
    })
}

// Open Application file
function openFiles(fnum) {
    reloadActions(undefined, fnum.fnum);
    //var fnum = fnum.fnum;
    var cid = parseInt(fnum.fnum.substr(14, 7));
    var sid = parseInt(fnum.fnum.substr(21, 7));

    $.ajax({
        type:'get',
        url:'index.php?option=com_emundus&controller=application&task=getactionmenu&fnum='+fnum.fnum,
        dataType:'json',
        success: function(result) {

            String.prototype.fmt = function (hash) {
                var string = this, key;
                for (key in hash) string = string.replace(new RegExp('\\{' + key + '\\}', 'gm'), hash[key]); return string;
            }

            $('#em-appli-menu .list-group').empty();
            if (result.status) {
                var menus = result.menus;
                var firstMenu = menus[0].link;
                var menuList = '';

                for (var m in menus) {
                    if (isNaN(parseInt(m)) || isNaN(menus[m].id) || typeof(menus[m].title) == "undefined")
                        break;

                    url = menus[m].link.fmt({ fnum: fnum.fnum, applicant_id: sid, campaign_id: cid });
                    url += '&fnum='+fnum.fnum;
                    url += '&Itemid='+itemId;
                    menuList += '<a href="'+url+'" class="list-group-item" title="'+menus[m].title+'" id="'+menus[m].id+'">';

                    if (menus[m].hasSons)
                        menuList += '<span class="glyphicon glyphicon-plus" id="'+menus[m].id+'"></span>';

                    menuList +=  '<strong>'+menus[m].title+'</strong></a>';
                }
                $('#em-appli-menu .list-group').append(menuList);

                $.ajax({
                    type:'get',
                    url:firstMenu,
                    dataType:'html',
                    data:({fnum:fnum.fnum}),
                    success: function(result)
                    {
                        $('.em-dimmer').remove();
                        $('#em-files-filters').hide();
                        $(".main-panel .panel.panel-default").hide();
                        $('#em-appli-block').empty();
                        $('#em-appli-block').append(result);
                        $('#accordion .panel.panel-default').show();
                        $('#em-appli-menu, #em-last-open, #em-assoc-files, #em-synthesis, .em-open-files > div[id="'+fnum.fnum+'"]').show();
                        menuBar1();
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        console.log(jqXHR.responseText);
                        if (jqXHR.status === 302)
                            window.location.replace('/user');
                    }
                })

            } else $('#em-appli-menu .list-group').append(result.msg);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        }
    });
    $("#em-assoc-files .panel-body").empty();
    $.ajax({
        type:'get',
        url:'index.php?option=com_emundus&view=application&fnum=' + fnum.fnum + '&Itemid=' + itemId + '&format=raw&layout=assoc_files',
        dataType:'html',
        success: function(result)
        {
            $("#em-assoc-files .panel-body").append(result);
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            console.log(jqXHR.responseText);
        }

    })
    /* if(!exist(fnum.fnum))
     {*/
    $.ajax({
        type: 'get',
        url: 'index.php?option=com_emundus&view=application&fnum=' + fnum.fnum + '&Itemid=' + itemId + '&format=raw&layout=synthesis&action=0',
        dataType: 'html',
        success: function(result)
        {
            //$('#em-hide-filters, #em-last-open, #em-appli-menu').show();
            $('#em-last-open .list-group .active').removeClass('active');
            if($('#'+fnum.fnum+'_ls_op').is(':visible')) {
                $('#'+fnum.fnum+'_ls_op' ).addClass('active');
            } else {
                if(fnum.hasOwnProperty('name'))
                    $("#em-last-open .list-group").append('<a href="#" class="list-group-item active" title="'+fnum.fnum+'" id="'+fnum.fnum+'_ls_op"><strong>'+fnum.name+'</strong><span> - '+fnum.label+'</span></a>');
                else
                    $("#em-last-open .list-group").append('<a href="#" class="list-group-item active" id="'+fnum.fnum+'_ls_op">'+fnum.fnum+'</a>');
            }

            $('.em-open-files').remove();
            var panel = result;
            $(".main-panel").append('<div class="em-close-minimise"><div class="btn-group pull-right"><button id="em-close-file" class="btn btn-danger btn-xxl"><strong>X</strong></button></div></div><div class="clearfix"></div><div class="col-md-12" id="em-appli-block"></div>');
            $("#em-synthesis .panel-body").empty();
            $("#em-synthesis .panel-body").append(panel);
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            console.log(jqXHR.responseText);
        }
    })

    // }
}

function getApplicationMenu()
{
    $.ajax({
        type:'get',
        url:'index.php?option=com_emundus&controller=application&task=getactionmenu&Itemid='.itemId,
        dataType:'json',
        success: function(result)
        {
            if (result.status)
            {
                var menus = result.menus;
                var menuList = '';
                for (var m in menus)
                {
                    if (isNaN(parseInt(m)) || isNaN(menus[m].id) || typeof(menus[m].title) == "undefined")
                        break;

                    menuList += '<a href="'+menus[m].link+'" class="list-group-item active" title="'+menus[m].title+'" id="'+menus[m].id+'">';

                    if (menuList[m].hasSon)
                        menuList += '<span class="glyphicon glyphicon-plus" id="'+menus[m].id+'"></span>';

                    menuList += '<strong>'+fnum.title+'</strong></a>';
                }
            }
            $('#em-appli-menu .list-group').append(menuList);
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            console.log(jqXHR.responseText);
        }
    })

}

function menuBar1()
{
    $('.nav.navbar-nav').show();
    $('.em-actions[multi="0"]').show();
    $('.em-actions[multi="0"]').removeClass('em-hidden');
    $('.em-actions[multi="1"]').show();
    $('.em-actions[multi="1"]').removeClass('em-hidden');

    $('.em-dropdown[nba="0"]').parent('li').show();
    $('.em-dropdown').each(function()
    {
        var dpId = $(this).attr('id');
        var nonHidden = $('ul[aria-labelledby="' + dpId + '"] .em-actions').length;
        $(this).attr('nba', nonHidden);
    });
}

function getSearchBox(id, father_id) {
    var index = father_id.split('-');
    $.ajax({
        type: 'get',
        url: 'index.php?option=com_emundus&controller=files&task=getbox&ItemId=' + itemId,
        dataType: 'json',
        data: ({
            id: id,
            index: index[index.length - 1]
        }),
        success: function(result)
        {
            if (result.status)
            {
                $('#em-adv-fil-' + index[index.length - 1]).remove();
                $('#em_adv_fil_' + index[index.length - 1] + '_chosen').remove();
                $('#' + father_id).append(result.html);
                $('.chzn-select').chosen();
                reloadData($('#view').val());
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        }
    })
}



function exist(fnum)
{
    var exist = false;
    $('.main-panel.col-xs-16 .panel.panel-default.em-hide').each(function(){
        if(parseInt($(this).attr('id')) == parseInt(fnum))
        {
            exist = true;
            return;
        }
    })

    return exist;
}

// Looks up checked items and adds them to a JSON object or return all if the "check all" box is ticked
function getUserCheck() {
    var id = parseInt($('.modal-body').attr('act-id'));
    if ($('#em-check-all-all').is(':checked')) {
        var checkInput = 'all';
    } else {
        var i = 0;
        var myJSONObject = '{';
        $('.em-check:checked').each(function(){
            i = i + 1;
            myJSONObject += '"'+i+'"'+':"'+$(this).attr('id').split('_')[0]+'",';
        });
        myJSONObject = myJSONObject.substr(0, myJSONObject.length-1);
        myJSONObject += '}';
        if(myJSONObject.length == 2) {
            alert('SELECT_FILES');
            return;
        } else {
            checkInput = myJSONObject;
        }
    }
    return checkInput;
}

maxcsv = 65000;
maxxls = 65000;
function generate_csv(json, eltJson, objJson, options, objclass) {
    var start = json.start;
    var limit = json.limit;
    var totalfile = json.totalfile;
    var file = json.file;
    var nbcol = json.nbcol;
    var methode = json.methode;
    var objclass = objclass;
    $.ajaxQ.abortAll();
    if (start+limit <= maxcsv) {
        $.ajax(
            {
                type: 'post',
                url: 'index.php?option=com_emundus&controller=files&task=generate_array',
                dataType: 'JSON',
                data: {
                    file: file,
                    totalfile: totalfile,
                    start: start,
                    limit: limit,
                    nbcol: nbcol,
                    methode: methode,
                    elts: eltJson,
                    objs: objJson,
                    opts: options,
                    objclass: objclass
                },
                success: function (result) {
                    var json = result.json;
                    if (result.status) {
                        if ((methode == 0) && ($('#view').val()!="evaluation")) {
                            $('#datasbs').replaceWith('<div id="datasbs" data-start="' + result.json.start + '"><p>' + result.json.start + ' / ' + result.json.totalfile + '</p></div>');
                        } else {
                            $('#datasbs').replaceWith('<div id="datasbs" data-start="' + result.json.start + '"><p>' + result.json.start+'</p></div>');

                        }
                        if (start!= json.start) {
                            generate_csv(json, eltJson, objJson, options, objclass);
                        } else {
                            $('#extractstep').replaceWith('<div id="extractstep"><p>'+Joomla.JText._('COM_EMUNDUS_XLS_GENERATION')+'</p></div>');
                            $.ajax(
                                {
                                    type: 'post',
                                    url: 'index.php?option=com_emundus&controller=files&task=export_xls_from_csv',
                                    dataType: 'JSON',
                                    data: {csv: file, nbcol: nbcol, start: start},
                                    success: function (result) {
                                        if (result.status) {
                                            $('#loadingimg').empty();
                                            $('#extractstep').replaceWith('<div class="alert alert-success" role="alert">'+Joomla.JText._('COM_EMUNDUS_EXPORT_FINISHED')+'</div>' );
                                            $('#chargement').append('<button type="button" class="btn btn-default" id="back" onclick="back();"><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;&nbsp;'+Joomla.JText._('BACK')+'</button>&nbsp;&nbsp;&nbsp;');
                                            $('#chargement').append('<a class="btn .btn-link" title="' + Joomla.JText._('COM_EMUNDUS_DOWNLOAD_EXTRACTION') + '" href="index.php?option=com_emundus&controller=' + $('#view').val() + '&task=download&format=xls&name=' + result.link + '"><span class="glyphicon glyphicon-download-alt"></span>  <span>' + Joomla.JText._('COM_EMUNDUS_DOWNLOAD_EXTRACTION') + '</span></a>');
                                        }
                                    },
                                    error: function (jqXHR, textStatus, errorThrown) {
                                        $('#loadingimg').empty();
                                        $('#extractstep').replaceWith('<div class="alert alert-danger" role="alert">'+Joomla.JText._('COM_EMUNDUS_ERROR_XLS')+'</div>');
                                        $('#chargement').append('<button type="button" class="btn btn-default" id="back" onclick="back();"><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;&nbsp;'+Joomla.JText._('BACK')+'</button>&nbsp;&nbsp;&nbsp;');
                                        console.log(jqXHR.responseText);
                                    }
                                });
                        }
                        /* $.ajax(
                         {
                         type: 'post',
                         url: 'index.php?option=com_emundus&controller=' + $('#view').val() + '&task=export_xls_from_csv',
                         dataType: 'JSON',
                         data: {csv: file, nbcol: nbcol, totalfile: totalfile},
                         success: function (result) {
                         if (result.status) {
                         $('#loadingimg').empty();
                         $('#extractstep').replaceWith('<div class="alert alert-warning" role="alert">' + Joomla.JText._('COM_EMUNDUS_LIMIT_POST_SERVER') + '</div>');
                         $('.modal-body').append('<a class="btn .btn-link" title="' + Joomla.JText._('COM_EMUNDUS_DOWNLOAD_EXTRACTION') + '" href="index.php?option=com_emundus&controller=' + $('#view').val() + '&task=download&format=xls&name=' + result.link + '"><span class="glyphicon glyphicon-download-alt"></span>  <span>' + Joomla.JText._('COM_EMUNDUS_DOWNLOAD_EXTRACTION') + '</span></a>');
                         }
                         },
                         error: function (jqXHR, textStatus, errorThrown) {
                         $('#loadingimg').empty();
                         $('#extractstep').replaceWith('<div class="alert alert-danger" role="alert">' + Joomla.JText._('COM_EMUNDUS_ERROR_XLS') + '</div>');
                         console.log(jqXHR.responseText);
                         }
                         });*/
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#loadingimg').empty();
                    $('#extractstep').replaceWith('<div class="alert alert-danger" role="alert">' + jqXHR.responseText + '</div>');
                }
            });
    } else if (start+limit> maxcsv) {
        $('#loadingimg').empty();
        $('#extractstep').replaceWith('<div class="alert alert-danger" role="alert">'+Joomla.JText._('COM_EMUNDUS_ERROR_CSV_CAPACITY')+'</div>');
        exit();
    } else if((start < maxxls) && (start+limit < maxcsv) ) {
        $('#extractstep').replaceWith('<div id="extractstep"><p>'+Joomla.JText._('COM_EMUNDUS_XLS_GENERATION')+'</p></div>');
        $.ajax(
            {
                type: 'post',
                url: 'index.php?option=com_emundus&controller=files&task=export_xls_from_csv',
                dataType: 'JSON',
                data: {csv: file, nbcol: nbcol, start: start},
                success: function (result) {
                    if (result.status) {
                        $('#loadingimg').empty();
                        $('#extractstep').replaceWith('<div class="alert alert-success" role="alert">'+Joomla.JText._('COM_EMUNDUS_EXPORT_FINISHED')+'</div>' );
                        $('.modal-body').append('<a class="btn .btn-link" title="' + Joomla.JText._('COM_EMUNDUS_DOWNLOAD_EXTRACTION') + '" href="index.php?option=com_emundus&controller=' + $('#view').val() + '&task=download&format=xls&name=' + result.link + '"><span class="glyphicon glyphicon-download-alt"></span>  <span>' + Joomla.JText._('COM_EMUNDUS_DOWNLOAD_EXTRACTION') + '</span></a>');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#loadingimg').empty();
                    $('#extractstep').replaceWith('<div class="alert alert-danger" role="alert">'+Joomla.JText._('COM_EMUNDUS_ERROR_XLS')+'</div>');
                    console.log(jqXHR.responseText);
                }
            });
    } else {
        $('#loadingimg').empty();
        $('#extractstep').replaceWith('<div class="alert alert-info" role="alert">'+Joomla.JText._('COM_EMUNDUS_ERROR_CAPACITY_XLS')+'</div><a class="btn .btn-link" title="'+Joomla.JText._('COM_EMUNDUS_DOWNLOAD_EXTRACTION')+'" href="index.php?option=com_emundus&controller='+$('#view').val()+'&task=download&format=xls&name='+file+'"><span class="glyphicon glyphicon-download-alt"></span>  <span>'+Joomla.JText._('COM_EMUNDUS_DOWNLOAD_EXTRACTION')+'</span></a>');

    }
}

maxfiles = 5000;
function generate_pdf(json) {

    var start       = json.start;
    var limit       = json.limit;
    var totalfile   = json.totalfile;
    var file        = json.file;
    var forms       = json.forms;
    var attachment  = json.attachment;
    var assessment  = json.assessment;
    var decision    = json.decision;
    var admission   = json.admission;
    var ids         = json.ids;
    var formids     = json.formids;
    var attachids   = json.attachids;
    var options     = json.options;
   //console.log(formids);
    $.ajaxQ.abortAll();

    if (start+limit < maxfiles) {
        $.ajax(
            {
                type: 'post',
                url: 'index.php?option=com_emundus&controller=files&task=generate_pdf&format=raw',
                dataType: 'JSON',
                data: {
                    file: file,
                    totalfile: totalfile,
                    start: start,
                    limit: limit,
                    forms: forms,
                    attachment: attachment,
                    assessment: assessment,
                    decision: decision,
                    admission: admission,
                    ids: ids,
                    formids: formids,
                    attachids:attachids,
                    options:options
                },
                success: function (result) {
                    var json = result.json;

                    if (result.status) {

                        $('#datasbs').replaceWith('<div id="datasbs" data-start="' + result.json.start + '"><p>' + result.json.start + ' / ' + result.json.totalfile + '</p></div>');

                        if (start != json.start) {
                            //$('#extractstep').replaceWith('<div class="alert alert-success" role="alert">'+json.msg+'</div>' );
                            generate_pdf(json);
                        } else {
                            $('#extractstep').replaceWith('<div id="extractstep"><p>'+Joomla.JText._('COM_EMUNDUS_PDF_GENERATION')+'</p></div>');
                            $('#loadingimg').empty();
                            $('#extractstep').replaceWith('<div class="alert alert-success" role="alert">'+Joomla.JText._('COM_EMUNDUS_EXPORT_FINISHED')+'</div>' );
                            $('#chargement').append('<button type="button" class="btn btn-default" id="back" onclick="back();"><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;&nbsp;'+Joomla.JText._('BACK')+'</button>&nbsp;&nbsp;&nbsp;');
                            $('#chargement').append('<a class="btn .btn-link" title="' + Joomla.JText._('DOWNLOAD_PDF') + '" href="tmp/' + file + '" target="_blank"><span class="glyphicon glyphicon-download-alt"></span>  <span>' + Joomla.JText._('DOWNLOAD_PDF') + '</span></a>');
                        }
                            
                    } else {

                        var json = result.json;
                        if (start != json.start) {
                            generate_pdf(json);
                        } else {
                            $('#loadingimg').empty();//.append(json.msg);
                            $('#addatatext').empty()
                            $('#datasbs p').replaceWith('<span class="alert alert-danger"> '+json.msg+'</span><br/><br/>');
                            $('#chargement').append('<button type="button" class="btn btn-default" id="back" onclick="back();"><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;&nbsp;'+Joomla.JText._('BACK')+'</button>&nbsp;&nbsp;&nbsp;');

                        }

                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#loadingimg').empty();
                    $('#extractstep').replaceWith('<div class="alert alert-danger" role="alert">!!' + jqXHR.responseText + '</div>');
                    $('#chargement').append('<button type="button" class="btn btn-default" id="back" onclick="back();"><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;&nbsp;'+Joomla.JText._('BACK')+'</button>&nbsp;&nbsp;&nbsp;');
                }
            });

        } else if (start+limit> maxfiles) {
        $('#loadingimg').empty();
        $('#extractstep').replaceWith('<div class="alert alert-danger" role="alert">'+Joomla.JText._('COM_EMUNDUS_ERROR_NBFILES_CAPACITY')+'</div>');
        $('#chargement').append('<button type="button" class="btn btn-default" id="back" onclick="back();"><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;&nbsp;'+Joomla.JText._('BACK')+'</button>&nbsp;&nbsp;&nbsp;');
        exit();

    } else if( (start+limit <= maxfiles) ) {
        $('#extractstep').replaceWith('<div id="extractstep"><p>'+Joomla.JText._('COM_EMUNDUS_PDF_GENERATION')+'</p></div>');
        $('#loadingimg').empty();
        $('#extractstep').replaceWith('<div class="alert alert-success" role="alert">'+Joomla.JText._('COM_EMUNDUS_EXPORT_FINISHED')+'</div>' );
        $('#chargement').append('<button type="button" class="btn btn-default" id="back" onclick="back();"><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;&nbsp;'+Joomla.JText._('BACK')+'</button>&nbsp;&nbsp;&nbsp;');
        $('#chargement').append('<a class="btn .btn-link" title="' + Joomla.JText._('DOWNLOAD_PDF') + '" href="tmp/' + file + '" target="_blank"><span class="glyphicon glyphicon-download-alt"></span>  <span>' + Joomla.JText._('DOWNLOAD_PDF') + '</span></a>');

    } else {
        $('#loadingimg').empty();
        $('#extractstep').replaceWith('<div class="alert alert-info" role="alert">'+Joomla.JText._('COM_EMUNDUS_ERROR_CAPACITY_PDF')+'</div><a class="btn .btn-link" title="'+Joomla.JText._('DOWNLOAD_PDF')+'" href="tmp/'+file+'" target="_blank"><span class="glyphicon glyphicon-download-alt"></span>  <span>'+Joomla.JText._('DOWNLOAD_PDF')+'</span></a>');
        $('#chargement').append('<button type="button" class="btn btn-default" id="back" onclick="back();"><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;&nbsp;'+Joomla.JText._('BACK')+'</button>&nbsp;&nbsp;&nbsp;');
    }
}

function showelts(elt,idcodeyear ){
    if ($(elt).hasClass("btn btn-info")) {
        $('#'+idcodeyear).toggle(400);
        $(elt).removeClass("btn btn-info").addClass("btn btn-elements-success");
        $(elt).empty();
        $(elt).append('<span class="glyphicon glyphicon-minus"></span>');
    }else{
        $('#'+idcodeyear).toggle(400);
        $(elt).removeClass("btn btn-elements-success").addClass("btn btn-info");
        $(elt).empty();
        $(elt).append('<span class="glyphicon glyphicon-plus"></span>');

    }
}

function showoptions(opt){
    if ($(opt).hasClass("btn btn-info")) {
        $('#options').toggle(400);
        $(opt).removeClass("btn btn-info").addClass("btn btn-elements-success");
        $(opt).empty();
        $(opt).append('<span class="glyphicon glyphicon-minus"></span>');
    }else{
        $('#options').toggle(400);
        $(opt).removeClass("btn btn-elements-success").addClass("btn btn-info");
        $(opt).empty();
        $(opt).append('<span class="glyphicon glyphicon-plus"></span>');

    }
}

function back(){
    $('div').remove('#chargement');
    $('#data').show();
    $('#can-val').show();
}

$(document).ready(function()
{
    var lastVal = new Object();
    $(document).on('click', function() {
        if (!$('ul.dropdown-menu.open').hasClass('just-open')) {
            $('ul.dropdown-menu.open').hide();
            $('ul.dropdown-menu.open').removeClass('open');
        }
    });
    $(document).on('change', '.em-filt-select', function(event) {
        $.ajaxQ.abortAll();
        if (event.handle !== true) {
            event.handle = true;
            var id = $(this).attr('id');

            if (id != 'elements') {
                if ($('#' + id).attr('multiple') != undefined)
                    var multi = true;
                else
                    var multi = false;

                var test = id.split('-');
                test.pop();
                if (test.join('-') == 'em-adv-fil')
                    var elements_son = true;
                else
                    var elements_son = false;

                if (multi) {
                    var value = $('#' + id).val();
                    if (value != null && value.length > 1 && value[0] == '%') {
                        if ((lastVal.hasOwnProperty(id) && lastVal[id][0] != '%')) {
                            $('#' + id + ' option:selected').removeAttr('selected');
                            $('#' + id + ' option')[0].selected = true;
                            $('.chzn-select').trigger('chosen:updated');
                        } else {
                            $('#' + id + ' option')[0].selected = false;
                            $('#' + id + ' option')[0].removeAttribute('selected');
                            $('#' + id + '_chosen .chosen-choices .search-choice a[data-option-array-index="0"]').click();
                        }
                        lastVal[id] = $('#' + id).val();
                    }
                }
                search();
            } else {
                var father = $(this).parent('fieldset').attr('id');
                getSearchBox($(this).val(), father);
            }
        }
    });
    $(document).on('click', '#em-data thead th', function(e) {
        $.ajaxQ.abortAll();
        if (e.handle !== true) {
            var id = $(this).attr('id');
            e.handle = true;
            if (id != 'check' && id != 'checkuser' && id != 'access' ) {
                tableOrder(id);
            }

        }
    });
    $(document).on('click', 'input:button', function(e) {
        $.ajaxQ.abortAll();
        if (e.event !== true) {
            e.handle = true;
            var name = $(this).attr('name');
            switch (name) {
                case 'clear-search':
                    lastVal = new Object();
                    $.ajax({
                        type: 'POST',
                        url: 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=clear',
                        dataType: 'json',
                        success: function(result) {
                            if (result.status) {
                                refreshFilter();
                                //reloadData($('#view').val());
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR.responseText);
                        }
                    });
                    break;
                case 'search':
                    search();
                    break;
                default:
                    break;
            }
        }
    });
    $(document).on('click', '.pagination.pagination-sm li a', function(e) {
        $.ajaxQ.abortAll();
        if (e.handle !== true) {
            e.handle = true;
            var id = $(this).attr('id');
            $.ajax({
                type: "POST",
                url: 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=setlimitstart',
                dataType: 'json',
                data: ({
                    limitstart: id
                }),
                success: function(result) {
                    if (result.status) {
                        reloadData($('#view').val());
                    }
                }
            });
        }
    });
    $(document).on('click', '#em-last-open .list-group-item', function(e)
    {
        $.ajaxQ.abortAll();
        if(e.handle !== true)
        {
            e.handle = true;
            var fnum = new Object();
            fnum.fnum = $(this).attr('title');
            fnum.sid = parseInt(fnum.fnum.substr(21, 7));
            fnum.cid = parseInt(fnum.fnum.substr(14, 7));
            $('.em-check:checked').prop('checked', false);

            $('#'+fnum.fnum+'_check').prop('checked', true);

            $.ajax({
                type:'get',
                url:'index.php?option=com_emundus&controller='+$('#view').val()+'&task=getfnuminfos',
                dataType:"json",
                data:({fnum:fnum.fnum}),
                success: function(result)
                {
                    if (result.status)
                    {
                        var fnumInfos = result.fnumInfos;
                        fnum.name = fnumInfos.name;
                        fnum.label = fnumInfos.label;
                        openFiles(fnum);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    console.log(jqXHR.responseText);
                }
            })
        }
    })
//
// Filter buttons action (search, clear filter, save filter)
//
    $(document).on('click', 'button', function(e) {
        $.ajaxQ.abortAll();
        if (e.handle != true) {
            e.handle = true;
            var id = $(this).attr('id');
            switch (id) {
                case 'save-filter':
                    var filName = prompt(filterName);
                    if (filName != null) {
                        $.ajax({
                            type: 'POST',
                            url: 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=savefilters&Itemid=' + itemId,
                            dataType: 'json',
                            data: ({
                                name: filName
                            }),
                            success: function(result) {
                                if (result.status) {
                                    $('#select_filter').append('<option id="' + result.filter.id + '" selected="">' + result.filter.name + '<option>');
                                    $("#select_filter").trigger("chosen:updated");
                                    $('#saved-filter').show();
                                    setTimeout(function(e) {
                                        $('#saved-filter').hide();
                                    }, 600);

                                } else {
                                    $('#error-filter').show();
                                    setTimeout(function(e) {
                                        $('#error-filter').hide();
                                    }, 600);
                                }

                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                console.log(jqXHR.responseText);
                            }
                        })
                    } else {
                        alert(filterEmpty);

                        filName = prompt(filterName, "name");
                    }
                    break;
                case 'del-filter':
                    var id = $('#select_filter').val();

                    if (id != 0) {
                        $.ajax({
                            type: 'POST',
                            url: 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=deletefilters&Itemid=' + itemId,
                            dataType: 'json',
                            data: ({
                                id: id
                            }),
                            success: function(result) {
                                if (result.status) {
                                    $('#select_filter option:selected').remove();
                                    $("#select_filter").trigger("chosen:updated");
                                    $('#deleted-filter').show();
                                    setTimeout(function(e) {
                                        $('#deleted-filter').hide();
                                    }, 600);
                                } else {
                                    $('#error-filter').show();
                                    setTimeout(function(e) {
                                        $('#error-filter').hide();
                                    }, 600);
                                }

                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                console.log(jqXHR.responseText);
                            }
                        })
                    } else {
                        alert(nodelete);
                    }
                    break;
                case 'add-filter':
                    addElement();
                    break;
                case 'em-close-file': document.location.hash = "close";
                    $('.alert.alert-warning').remove();
                case 'em-mini-file':
                    $('#em-appli-block').remove();
                    $('.em-close-minimise').remove();
                    $('.em-open-files').remove();
                    $('.em-hide').hide();
                    $('#em-last-open').show();
                    $('#em-last-open .list-group .list-group-item').removeClass('active');
                    $('#em-files-filters').show();
                    $('.em-check:checked').prop('checked', false);
                    $(".main-panel .panel.panel-default").show();
                    break;
                case 'em-see-files':
                    var fnum = new Object();
                    fnum.fnum = $(this).parents('a').attr('href').split('-')[0];
                    fnum.fnum = fnum.fnum.substr(1, fnum.fnum.length);
                    fnum.sid = parseInt(fnum.fnum.substr(21, 7));
                    fnum.cid = parseInt(fnum.fnum.substr(14, 7));
                    $('.em-check:checked').prop('checked', false);
                    $('#'+fnum.fnum+'_check').prop('checked', true);

                    $.ajax({
                        type:'get',
                        url:'index.php?option=com_emundus&controller='+$('#view').val()+'&task=getfnuminfos',
                        dataType:"json",
                        data:({fnum:fnum.fnum}),
                        success: function(result)
                        {
                            if (result.status)
                            {
                                var fnumInfos = result.fnumInfos;
                                fnum.name = fnumInfos.name;
                                fnum.label = fnumInfos.label;
                                openFiles(fnum);
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown)
                        {
                            console.log(jqXHR.responseText);
                        }
                    })

                    break;
                case 'em-delete-files':
                    var r = confirm(Joomla.JText._('COM_EMUNDUS_CONFIRM_DELETE_FILE'));
                    if (r == true)
                    {
                        var fnum = $(this).parents('a').attr('href').split('-')[0];
                        fnum = fnum.substr(1, fnum.length);
                        $.ajax({
                            type:'POST',
                            url:'index.php?option=com_emundus&controller=files&task=deletefile',
                            dataType:'json',
                            data:{fnum: fnum},
                            success: function(result)
                            {
                                if(result.status)
                                {
                                    if($("#"+fnum+"-collapse").parent('div').hasClass('panel-primary'))
                                    {
                                        $('.em-open-files').remove();
                                        $('.em-hide').hide();
                                        $('#em-last-open').show();
                                        $('#em-last-open .list-group .list-group-item').removeClass('active');
                                        $('#em-files-filters').show();
                                        $('.em-check:checked').prop('checked', false);
                                        $(".main-panel.col-xs-16 .panel.panel-default").show();
                                    }
                                    $("#em-last-open #"+fnum+"_ls_op").remove();
                                    $("#"+fnum+"-collapse").parent('div').remove();

                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown)
                            {
                                console.log(jqXHR.responseText);
                            }
                        })
                    }

                    break;

                default:
                    break;
            }

        }
    });
//
// Open Application File (FNUM)
//
    $(document).on('click', '.em_file_open', function(e)
    {
        $.ajaxQ.abortAll();
        if(e.handle !== true)
        {
           addDimmer();
            e.handle = true;
            var fnum = new Object();
            fnum.fnum = $(this).attr('id');
            var sid = parseInt(fnum.fnum.substr(21, 7));
            var cid = parseInt(fnum.fnum.substr(14, 7));
            $('.em-check:checked').prop('checked', false);

            $('#'+fnum.fnum+'_check').prop('checked', true);

            $.ajax({
                type:'get',
                url:'index.php?option=com_emundus&controller='+$('#view').val()+'&task=getfnuminfos',
                dataType:"json",
                data:({fnum:fnum.fnum}),
                success: function(result)
                {
                    if (result.status)
                    {
                        var fnumInfos = result.fnumInfos;
                        fnum.name = fnumInfos.name;
                        fnum.label = fnumInfos.label;
                        openFiles(fnum);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    console.log(jqXHR.responseText);
                }
            })
        }
    });
//
// Menu Form actions
//
    $(document).on('click', '#em-appli-menu .list-group-item', function(e) {
        $.ajaxQ.abortAll();
        e.preventDefault();
        var id = $(this).attr('id');
        var url = $(this).attr('href');
        //var currenturl = $(location).attr('href');
       // currenturl = currenturl.split("#");
        //if (currenturl[1] != null) {
           // currenturl = currenturl[1].split("|");
           // var fnum = currenturl[0];
           // if (fnum != null) {
                $.ajax({
                    type: "get",
                    url: url,
                    dataType: 'html',
                    data: ({id: id}),
                    success: function (result) {
                        $('#em-appli-block').empty();
                        $('#em-appli-block').append(result);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR.responseText);
                    }
                });
            //}
           // ;
        //};
    });

//
// Lien Edition / Ajout de données de formulaire de candidature
//
    /*   $(document).on('click', '#em-appli-block .active.content a.btn-primary', function(e)
     {
     e.preventDefault();
     // var id=$(this).attr("rel");
     var url = $(this).attr('href');
     var fnum = $('.em-check:checked').attr('id').split('_')[0];
     //$(".modal-body").fadeIn(1000).html('<div style="text-align:center; margin-right:auto; margin-left:auto">Patientez...</div>');
     $(".modal-body").empty();
     $.ajax({
     type:"GET",
     url:url,
     dataType:'html',
     data:({fnum:fnum}),
     error:function(msg){
     $(".modal-body").addClass("tableau_msg_erreur").fadeOut(800).fadeIn(800).fadeOut(400).fadeIn(400).html('<div style="margin-right:auto; margin-left:auto; text-align:center">Impossible de charger cette page</div>');
     },
     success:function(data){
     $(".modal-body").empty();
     $(".modal-body").append(data);
     }
     });
     });
     */
    $(document).on('change', '#pager-select', function(e) {
        if (e.handle !== true) {
            e.handle = true;
            $.ajax({
                type: 'POST',
                url: 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=setlimit',
                dataType: 'json',
                data: ({
                    limit: $(this).val()
                }),
                success: function(result) {
                    if (result.status) {
                        reloadData($('#view').val());
                    }
                }
            });
        }
    });
    $(document).on('keyup', 'input:text', function(e) {
        if (e.keyCode == 13) {
            var id = $(this).attr('id');
            if(id != 'undefined') {
                var test = id.split('-');
                test.pop();
                if (test.join('-') == 'em-adv-fil') {
                    var elements_son = true;
                } else {
                    var elements_son = false;
                }
            }
            search();
        }
    });

    $(document).on('change', '#select_filter', function(e) {
        var id = $(this).attr('id');
        var val = $('#' + id).val();
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=setfilters',
            data: ({
                id: $('#' + id).attr('name'),
                val: val,
                multi: false
            }),
            success: function(result) {
                if (result.status) {
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=loadfilters',
                        data: {
                            id: val
                        },
                        success: function(result) {
                            if (result.status) {
                                refreshFilter();

                                reloadData($('#view').val());
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR.responseText);
                        }

                    });

                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    });

    $(document).on('click', '#suppr-filt', function(e)
    {
        $.ajaxQ.abortAll();
        var fId = $(this).parent('fieldset').attr('id');
        var index = fId.split('-');

        var sonName = $('#em-adv-fil-' + index[index.length - 1]).attr('name');

        $('#' + fId).remove();
        $.ajax(
            {
                type: 'POST',
                url: 'index.php?option=com_emundus&controller=files&task=deladvfilter',
                dataType: 'json',
                data: ({
                    elem: sonName,
                    id: index[index.length - 1]
                }),
                success: function(result)
                {
                    if (result.status)
                    {
                        reloadData($('#view').val());
                    }
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    console.log(jqXHR.responseText);
                }
            })

    });
    $(document).on('change', '.em-check', function(e)
    {
        if(($(this).attr('id') == 'em-check-all') || ($(this).attr('id') == 'em-check-all-all'))
        {
            $('.em-check-all-all').show();
            $('.em-actions[multi="1"]').show();
            $('.em-actions[multi="1"]').removeClass('em-hidden');
            if($(this).is(':checked'))
            {
                $(this).prop('checked', true);
                $('.em-check').prop('checked', true);
                $('.em-actions[multi="0"]').hide();
                $('.em-actions[multi="0"]').addClass('em-hidden');
                $('.nav.navbar-nav').show();
                $('.em-dropdown').each(function()
                {
                    var dpId = $(this).attr('id');
                    var nbHidden =  $('ul[aria-labelledby="' + dpId + '"] .em-actions.em-hidden').length;
                    var nonHidden = $('ul[aria-labelledby="' + dpId + '"] .em-actions').length;
                    $(this).attr('nba', nonHidden - nbHidden);
                });
                $('.em-dropdown[nba="0"]').parent('li').hide();
                reloadActions('files', undefined, true);
            }
            else
            {
                $('.em-check-all-all').hide();
                $(this).prop('checked', false);
                $('.em-check').prop('checked', false);
                $('.em-actions[multi="0"]').show();
                $('.em-actions[multi="0"]').removeClass('em-hidden');
                $('.em-actions[multi="1"]').show();
                $('.em-actions[multi="1"]').removeClass('em-hidden');
                $('.nav.navbar-nav').hide();

                $('.em-dropdown[nba="0"]').parent('li').show();
                $('.em-dropdown').each(function()
                {
                    var dpId = $(this).attr('id');
                    var nonHidden = $('ul[aria-labelledby="' + dpId + '"] .em-actions').length;
                    $(this).attr('nba', nonHidden);
                });
                reloadActions('files', undefined, false);
            }
        }
        else
        {
            $('#em-check-all').prop('checked', false);
            $('#em-check-all-all').prop('checked', false);

            if($('.em-check:checked').length == 0)
            {
                $('.nav.navbar-nav').hide();
                reloadActions('files', undefined, false);
            }
            else if($('.em-check:checked').length == 1)
            {
                reloadActions('files', $(this).attr('id').split('_')[0], true);
            }
            else
            {
                reloadActions('files', undefined, true);

                $('.em-actions[multi="0"]').hide();
                $('.em-actions[multi="0"]').addClass('em-hidden');

                $('.em-dropdown').each(function()
                {
                    var dpId = $(this).attr('id');
                    var nbHidden =  $('ul[aria-labelledby="' + dpId + '"] .em-actions.em-hidden').length;
                    var nonHidden = $('ul[aria-labelledby="' + dpId + '"] .em-actions').length;
                    $(this).attr('nba', nonHidden - nbHidden);
                });
                $('.em-dropdown[nba="0"]').parent('li').hide();
            }
        }
    });
    $(document).on('click', '.em-dropdown', function(e)
    {
        $.ajaxQ.abortAll();
        var id = $(this).attr('id');

        $('ul.dropdown-menu.open').hide();
        $('ul.dropdown-menu.open').removeClass('open');
        if ($('ul[aria-labelledby="' + id + '"]').hasClass('open'))
        {
            $('ul[aria-labelledby="' + id + '"]').hide();
            $('ul[aria-labelledby="' + id + '"]').removeClass('open');
        }
        else
        {
            $('ul[aria-labelledby="' + id + '"]').show();
            $('ul[aria-labelledby="' + id + '"]').addClass('open just-open');
        }


        setTimeout(function()
        {
            $('ul[aria-labelledby="' + id + '"]').removeClass('just-open')
        }, 300);
    });
//
// Button Form actions
//
    $(document).on('click', '.em-actions-form', function(e)
    {
        $.ajaxQ.abortAll();
        var id = parseInt($(this).attr('id'));
        var url = $(this).attr('url');
        $('#em-modal-form').modal({backdrop:true},'toggle');
        //  $('#em-modal-form .modal-content').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title">'+Joomla.JText._('LOADING')+'</h4></div><div class="modal-body"><img src="media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'></button></div>');

        $('.modal-title').empty();
        $('.modal-title').append($(this).children('a').text());
        $('.modal-body').empty();

        if($('.modal-dialog').hasClass('modal-lg'))
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

        $(".modal-body").append('<iframe src="'+url+'" style="width:'+window.getWidth()*0.8+'px; height:'+window.getHeight()*0.8+'px; border:none"></iframe>');
    });
//
// Menu action
//
    $(document).on('click', '.em-actions', function(e) {

        $.ajaxQ.abortAll();
        e.preventDefault();

        var id = parseInt($(this).attr('id'));

        $('#em-modal-actions').modal({backdrop:true,keyboard:true},'toggle');
        $('.modal-title').empty();
        $('.modal-title').append($(this).children('a').text());
        $('.modal-body').empty();

        if ($('.modal-dialog').hasClass('modal-lg'))
            $('.modal-dialog').removeClass('modal-lg');

        $('.modal-body').attr('act-id', id);
        $('.modal-footer').show();
        $('.modal-lg').css({ width: '80%' });
        $('.modal-dialog').css({ width: '80%' });

        var fnum = $(this).attr('id').split('_')[0];
        var cid = parseInt(fnum.substr(14, 7));
        var sid = parseInt(fnum.substr(21, 7));

        if ($('#em-check-all-all').is(':checked')) {
            var fnums = 'all';
        } else {

            var fnums = [];
            $('.em-check:checked').each(function() {
                fnum = $(this).attr('id').split('_')[0];
                cid = parseInt(fnum.substr(14, 7));
                sid = parseInt(fnum.substr(21, 7));
                fnums.push({fnum: fnum, cid: cid, sid:sid});
            });

        }

        fnums = JSON.stringify(fnums);
        fnums = encodeURIComponent(fnums);

        var view = $('#view').val();
        var url = $(this).children('a').attr('href');
        var formid = 29;
        // get formid by fnum
        $.ajax({
            type:'post',
            url:'index.php?option=com_emundus&controller=files&task=getformid&Itemid='+itemId,
            data: {fnum: fnum},
            dataType:'json',
            async: false,
            success: function(result)
            {
                if (result.status)
                    formid = result.formid;
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                console.log(jqXHR.responseText);
            }
        });


        String.prototype.fmt = function (hash) {
            var string = this, key;
            for (key in hash) string = string.replace(new RegExp('\\{' + key + '\\}', 'gm'), hash[key]); return string;
        }

        url = url.fmt({ fnums: fnums, fnum: fnum, applicant_id: sid, campaign_id: cid, view: view, controller: view, Itemid: itemId, formid: formid });
        url +='&action_id='+id;
        switch (id) {
            // 1:new application file
            // 4:attachments
            // 5:evaluation
            // 32: Admission
            // Export PDF
            case 1 : $('#can-val').empty();
            case 4 : $('#can-val').empty();
            case 5 : $('#can-val').empty();
            case 32 :
                $('#can-val').empty();
                $('.modal-body').append('<div><img src="'+loadingLine+'" alt="'+Joomla.JText._('LOADING')+'"/></div>');
                $('.modal-footer').hide();
                $('.modal-dialog').addClass('modal-lg');
                $(".modal-body").empty();
                $(".modal-body").append('<iframe src="'+url+'" style="width:'+window.getWidth()*0.8+'px; height:'+window.getHeight()*0.8+'px; border:none"></iframe>');
                break;
            //export excel
            case 6:
            $('#can-val').empty();
            $('#can-val').append('<button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'</button>'+
                                 '<button style="margin-left:5px;" type="button" class="btn btn-success">'+Joomla.JText._('OK')+'</button>');
            $('#can-val').show();
            
                $('.modal-body').append('<div>' +'<img src="'+loadingLine+'" alt="loading"/>' +'</div>');
                //var url = 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=getformelem&Itemid='+itemId;
                $.ajax({
                    type:'get',
                    url:url,
                    dataType:'json',

                    success: function(result) {
                        if (result.status) {
                            var nbprg = 0;
                            $('.modal-body').empty();

                             //**export excel filter */
                             $('.modal-body').append('<div id="data"></div>');
                             $('#data').append('<div class="panel panel-default xclsform"><div class="panel-body"> <select class="chzn-select" id="filt_save" name="filt_save" >'+
                                '<option value="0">'+Joomla.JText._('PLEASE_SELECT_FILTER')+'</option></select>'+

                             '<button class="w3-button w3-tiny btn-warning" id="savefilter" style="margin-left:5%; margin-right:1%; border-radius: 4px;"><i class="icon-star"></i></button>'+
                             '<button class="w3-button w3-tiny" id="delfilter" style="border-radius: 4px;" title="'+Joomla.JText._('DELETE')+'"><i class="icon-trash"></i></button></div></div>'+

                             '<div class="alert alert-dismissable alert-success em-alert-filter" id="sav-filter">'+
                                 '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'+
                                 '<strong>'+Joomla.JText._('FILTER_SAVED')+'</strong>'+
                             '</div>'+
                             '<div class="alert alert-dismissable alert-success em-alert-filter" id="del-filter">'+
                                 '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'+
                                 '<strong>'+Joomla.JText._('FILTER_DELETED')+'</strong>'+
                             '</div>'+
                             '<div class="alert alert-dismissable alert-danger em-alert-filter" id="err-filter">'+
                                 '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'+
                                 '<strong>'+Joomla.JText._('SQL_ERROR')+'</strong>'+
                             '</div>');
                             
 
                            $('#data').append('<div class="panel panel-default xclsform"><div class="panel-heading"><h5>'+Joomla.JText._('COM_EMUNDUS_CHOOSE_PRG')+'</h5></div><div class="panel-body"><select class="chzn-select" name="em-export-prg" id="em-export-prg"><option value="0" >-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_PRG')+' --</option></select><br/><br/><div id="camp" style="display:none;"><select name="em-export-camp" id="em-export-camp" style="display: none;" class="chzn-select"><option value="0" data-value="0">-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_CAMP')+' --</option></select></div></div></div>');
                
                            $('#data').append('<div id="elements_detail" style="display: none">' +
                                                        '<div class="panel panel-default xclsform">' +
                                                            '<div class="panel-heading">'+
                                                            '<table style="width:100%;"><tr>'+
                                                                '<th><h5><button type="button" id="showelements" class="btn btn-info btn-xs" title="'+Joomla.JText._('COM_EMUNDUS_SHOW_ELEMENTS')+'">' +
                                                                '<span class="glyphicon glyphicon-plus"></span>' +
                                                                 '</button> &ensp;' +Joomla.JText._('COM_EMUNDUS_CHOOSE_FORM_ELEM')+
                                                                '</h5></th>' +

                                                                '<th><h5><button type="button" id="showevalelements" class="btn btn-info btn-xs" title="'+Joomla.JText._('COM_EMUNDUS_SHOW_ELEMENTS')+'">' +
                                                                '<span class="glyphicon glyphicon-plus"></span>' +
                                                                '</button> &ensp;' +Joomla.JText._('COM_EMUNDUS_CHOOSE_EVAL_FORM_ELEM')+
                                                                '</h5></th>'+

                                                                '<th><h5><button type="button" id="showdecisionelements" class="btn btn-info btn-xs" title="'+Joomla.JText._('COM_EMUNDUS_SHOW_ELEMENTS')+'">' +
                                                                '<span class="glyphicon glyphicon-plus"></span>' +
                                                                '</button> &ensp; ' +Joomla.JText._('COM_EMUNDUS_CHOOSE_DECISION_FORM_ELEM')+
                                                                ' </h5></th>'+

                                                                '<th><h5> <button type="button" id="showadmissionelements" class="btn btn-info btn-xs" title="'+Joomla.JText._('COM_EMUNDUS_SHOW_ELEMENTS')+'">' +
                                                                '<span class="glyphicon glyphicon-plus"></span>' +
                                                                '</button> &ensp;' +Joomla.JText._('COM_EMUNDUS_CHOOSE_ADMISSION_FORM_ELEM')+
                                                                ' </h5></th>'+
                                                            '</tr></table>'+

                                                            '</div>' +
                                                            '<div class="panel-body">' +
                                                                '<div id="appelement">'+
                                                                    '<div id="elements-popup" style="width : 95%;margin : auto; display: none; ">' +
                                                                    '</div>' +
                                                                '</div>'+
                                                                '<div id="evalelement">' +
                                                                    '<div id="eval-elements-popup" style="width : 95%;margin : auto; display: none;">' +
                                                                    '</div>' +
                                                                '</div>' +
                                                                '<div id="decelement">' +
                                                                    '<div id="decision-elements-popup" style="width : 95%;margin : auto; display: none;">' +
                                                                    '</div>' +
                                                                '</div>' +
                                                                '<div id="admelement">' +
                                                                    '<div id="admission-elements-popup" style="width : 95%;margin : auto; display: none;">' +
                                                                    '</div>' +
                                                                '</div>' +
                                                            '</div>' +
                                                        '</div>' +
                                                    '</div>');
                                                    
                            $.ajax({
                                type:'get',
                                url: 'index.php?option=com_emundus&controller=files&task=getProgrammes',
                                dataType:'json',

                                success: function(result) {
                                    if (result.status) {
                                        // get export excel saved filter
                                        $.ajax({
                                            type:'get',
                                            url: 'index.php?option=com_emundus&controller=files&task=getExportExcelFilter',
                                            dataType:'json',
                                            success: function (result) {
                                                if (result.status) {
                                                    for(var d in result.filter){
                                                        if (isNaN(parseInt(d)))
                                                            break;
                                                        $('#filt_save').append('<option value="' + result.filter[d].id + '">' + result.filter[d].name + '</option>');
                                                        $('#filt_save').trigger("chosen:updated");
                                                    }
                                                } else {
                                                    $('#err-filter').show();
                                                    setTimeout(function(e) {
                                                        $('#err-filter').hide();
                                                    }, 600);
                                                }

                                            },
                                            error: function(jqXHR, textStatus, errorThrown) {
                                                console.log(jqXHR.responseText);
                                            }
                                        });

                                        $('#em-export-prg').append(result.html);
                                        $('#em-export-prg').trigger("chosen:updated");
                                        var code = $('#em-export-prg').val();

                                        nbprg = result.nbprg;
                                        if (nbprg == 1) {

                                            $.ajax({
                                                type:'get',
                                                url: 'index.php?option=com_emundus&controller=files&task=getProgramCampaigns&code=' + code,
                                                dataType:'json',

                                                success: function(result) {
                                                    if (result.status) {
                                                        $('#em-export-camp').append(result.html);
                                                        $('#em-export-camp').trigger("chosen:updated");
                                                        $('#camp').show();

                                                        nbcamp = result.nbcamp;

                                                        var year = $("#em-export-camp").val();

                                                        $.ajax({
                                                            type: 'get',
                                                            url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&year='+year+'&code=' + code,

                                                            success: function (data) {
                                                                $('#elements-popup').empty();
                                                                $('#em-export').empty();

                                                                $.ajax({
                                                                    type:'get',
                                                                    url: 'index.php?option=com_emundus&controller=files&task=getformelem&Itemid='+itemId,
                                                                    dataType:'json',

                                                                    success: function(result) {

                                                                        var item='';
                                                                        /*item+='<option value="0" selected>Select an option</option>';

                                                                        for (var d in result.elts) {

                                                                            if (isNaN(parseInt(d)))
                                                                                break;

                                                                            var menu_tmp = result.elts[d].title;

                                                                            if (menu != menu_tmp) {
                                                                                item += '<optgroup label="________________________________"><option disabled class="emundus_search_elm" value="-">' + menu_tmp.toUpperCase() + '</option></optgroup>';
                                                                                menu = menu_tmp;
                                                                            }

                                                                            if (grId != null || grId != result.elts[d].group_id)
                                                                                item += '</optgroup>'

                                                                            if (grId != result.elts[d].group_id)
                                                                                item += '<optgroup label=">> '+Joomla.JText._(result.elts[d].group_label)+'">'

                                                                            grId = result.elts[d].group_id

                                                                            if (Joomla.JText._(result.elts[d].element_label) == "undefined")
                                                                                var elt_label = result.elts[d].element_label;
                                                                            else
                                                                                var elt_label = Joomla.JText._(result.elts[d].element_label);

                                                                            item += '<option value="'+result.elts[d].id+'" data-value="'+result.elts[d].element_label+'">'+elt_label+'</option>';
                                                                        }*/

                                                                        $('#elements-popup').append(data);
                                                                        item ='';
                                                                        
                                                                        if(view == "files"){
                                                                            for (var d in result.defaults) {
                                                                                if (isNaN(parseInt(d)))
                                                                                    break;
                                                                                item += '<li class="em-export-item" id="' + result.defaults[d].id + '-item"><button class="btn btn-danger btn-xs" id="' + result.defaults[d].id + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + result.defaults[d].element_label + '</strong></span></li>';
                                                                            }
                                                                            $('#em-export').append(item)
                                                                        }

                                                                        ;
                                                                       

                                                                        /***evaluation elements */
                                                                        $.ajax({
                                                                            type: 'get',
                                                                            url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=evaluation',

                                                                            success: function (data) {
                                                                                $('#eval-elements-popup').empty();
                                                                                //$('#em-eval-export').empty();

                                                                                $.ajax({
                                                                                    type:'get',
                                                                                    url: 'index.php?option=com_emundus&controller=evaluation&task=getformelem',
                                                                                    data: {code:code},
                                                                                    dataType:'json',

                                                                                    success: function(result) {

                                                                                        var item='';
                                                                                        /*item+='<option value="0" selected>Select an option</option>';

                                                                                        for (var d in result.elts) {

                                                                                            if (isNaN(parseInt(d)))
                                                                                                break;

                                                                                            if (Joomla.JText._(result.elts[d].element_label) == "")
                                                                                                var elt_label = result.elts[d].element_label;
                                                                                            else
                                                                                                var elt_label = Joomla.JText._(result.elts[d].element_label);

                                                                                            item += '<option value="'+result.elts[d].element_id+'" data-value="'+result.elts[d].element_label+'">'+elt_label+'</option>';
                                                                                        }*/
                                                                                

                                                                                        $('#eval-elements-popup').append(data);
                                                                                        item ="";

                                                                                        if(view == "evaluation"){
                                                                                            for (var d in result.defaults) {
                                                                                                if (isNaN(parseInt(d)))
                                                                                                    break;
                                                                                                item += '<li class="em-export-item" id="' + result.defaults[d].element_id + '-item"><button class="btn btn-danger btn-xs" id="' + result.defaults[d].element_id + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + result.defaults[d].element_label + '</strong></span></li>';
                                                                                                $('#emundus_elm_'+ result.defaults[d].element_id).prop("checked", true);
                                                                                            }
                                                                                            $('#em-export').append(item);
                                                                                        }
                                                                                        

                                                                                        //***decision elements */
                                                                                        $.ajax({
                                                                                            type: 'get',
                                                                                            url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=decision',

                                                                                            success: function (data) {
                                                                                                $('#decision-elements-popup').empty();
                                                                                                //$('#em-decision-export').empty();

                                                                                                $.ajax({
                                                                                                    type:'get',
                                                                                                    url: 'index.php?option=com_emundus&controller=admission&task=getformelem&form=decision',
                                                                                                    data: {code:code},
                                                                                                    dataType:'json',
                                        
                                                                                                    success: function(result) {
                                        
                                                                                                        var item='';
                                                                                                       
                                                                                                        $('#decision-elements-popup').append(data);
                                                                                                        item ="";
                                        
                                                                                                        
                                                                                                        /***admission elements */
                                                                                                        $.ajax({
                                                                                                            type: 'get',
                                                                                                            url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=admission',
                                                            
                                                                                                            success: function (data) {
                                                                                                                $('#admission-elements-popup').empty();
                                                                                                                
                                                                                                                $.ajax({
                                                                                                                    type:'get',
                                                                                                                    url: 'index.php?option=com_emundus&controller=admission&task=getformelem&form=admission',
                                                                                                                    data: {code:code},
                                                                                                                    dataType:'json',
                                                        
                                                                                                                    success: function(result) {
                                                        
                                                                                                                        var item='';
                                                                                                                        
                                                                                                                        $('#admission-elements-popup').append(data);
                                                                                                                        item ="";
                                                                                                                        
                                                                                                                        if(view == "admission"){
                                                                                                                            for (var d in result.defaults) {
                                                                                                                                if (isNaN(parseInt(d)))
                                                                                                                                    break;
                                                                                                                                item += '<li class="em-export-item" id="' + result.defaults[d].element_id + '-item"><button class="btn btn-danger btn-xs" id="' + result.defaults[d].element_id + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + result.defaults[d].element_label + '</strong></span></li>';
                                                                                                                                $('#emundus_elm_'+ result.defaults[d].element_id).prop("checked", true);
                                                                                                                            }
                                                                                                                            $('#em-export').append(item);
                                                                                                                        }
                                                                                                                    },
                                                        
                                                                                                                    error: function (jqXHR, textStatus, errorThrown) {
                                                                                                                        console.log(jqXHR.responseText);
                                                                                                                    }
                                                        
                                                                                                                });
                                                                                                    
                                                                                                            },
                                                                                                            error: function (jqXHR, textStatus, errorThrown) {
                                                                                                                console.log(jqXHR.responseText);
                                                                                                            }
                                                                                                        });
                                                                                                    },
                                                                                                    error: function (jqXHR, textStatus, errorThrown) {
                                                                                                        console.log(jqXHR.responseText);
                                                                                                    }

                                                                                                });
                                                                                            },
                                                                                            error: function (jqXHR, textStatus, errorThrown) {
                                                                                                console.log(jqXHR.responseText);
                                                                                            }
                                                                                        });
                                                                                    },
                                                                                    error: function (jqXHR, textStatus, errorThrown) {
                                                                                        console.log(jqXHR.responseText);
                                                                                    }
                                                                                });
                                                                            },
                                                                            error: function(jqXHR, textStatus, errorThrown) {
                                                                                console.log(jqXHR.responseText);
                                                                            }
                                                                        });
                                                                        $('.btn-success').show();
                                                                        $('#elements_detail').show();
                                                                    },
                                                                    error: function (jqXHR, textStatus, errorThrown) {
                                                                        console.log(jqXHR.responseText);
                                                                    }
                                                                });
                                                            },
                                                            error: function (jqXHR, textStatus, errorThrown) {
                                                                console.log(jqXHR.responseText);
                                                            }
                                                        });
                                                    }
                                                },
                                                error: function (jqXHR, textStatus, errorThrown)
                                                {
                                                    console.log(jqXHR.responseText);
                                                }
                                            });
                                        }
                                    }
                                },
                                error: function (jqXHR, textStatus, errorThrown)
                                {
                                    console.log(jqXHR.responseText);
                                }
                            });



                           //*** on export excel filter change ******************************/
                            $('#filt_save').on('change', function() {
                                var id = $(this).val();
                                if(id != 0){
                                    $.ajax({
                                        type:'get',
                                        url: 'index.php?option=com_emundus&controller=files&task=getExportExcelFilter',
                                        dataType:'json',
                                        success: function (result) {
                                            if (result.status) {
                                                for(var d in result.filter){
                                                    if (isNaN(parseInt(d)))
                                                        break;
                                                    if(result.filter[d].id == id){
                                                        var constraints = result.filter[d].constraints;
                                                        constraints = jQuery.parseJSON(constraints);
                                                        var filter = jQuery.parseJSON(constraints.excelfilter);
                                                        var label = filter.programme;
                                                        var code = filter.code;
                                                        //var year = label.split('(')[1];
                                                        var year = filter.year;
                                                        //year = year.substr(0, year.length - 1);

                                                        if (code != 0) { //for programmes
                                                            html = '<option value="'+code+'">'+label+'</option>';
                                                            if($("#em-export-prg option[value="+code+"]").length == 0){
                                                                $('#em-export-prg').append(html);// add option to list
                                                            }
                                                            $('#em-export-prg').val(code);
                                                            $('#em-export-prg').trigger("chosen:updated");

                                                            $.ajax({
                                                                type:'get',
                                                                url: 'index.php?option=com_emundus&controller=files&task=getProgramCampaigns&code=' + code,
                                                                dataType:'json',

                                                                success: function(result) {
                                                                    if (result.status) {

                                                                        if($("#em-export-camp option[value="+year+"]").length == 0){
                                                                            $('#em-export-camp').append('<option value="'+year+'">'+label+"("+year+')</option>');// add option to list
                                                                        }
                                                                        $('#em-export-camp').val(year);
                                                                        $('#em-export-camp').trigger("chosen:updated");
                                                                        $('#camp').show();

                                                                    }
                                                                },
                                                                error: function (jqXHR, textStatus, errorThrown)
                                                                {
                                                                    console.log(jqXHR.responseText);
                                                                }
                                                            });

                                                            /*** application form elements */
                                                            $.ajax({
                                                                type: 'get',
                                                                url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&viewcall=files&year='+year+'&code=' + code,

                                                                success: function (data) {

                                                                    $('#em-export').empty();

                                                                    /*** evaluation elements */
                                                                    $.ajax({
                                                                        type: 'get',
                                                                        url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=evaluation',

                                                                        success: function (data) {
                                                                            $('#eval-elements-popup').empty();
                                                                            $('#eval-elements-popup').append(data);

                                                                                /*** decision elements */
                                                                            $.ajax({
                                                                                type: 'get',
                                                                                url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=decision',

                                                                                success: function (data) {
                                                                                    $('#decision-elements-popup').empty();

                                                                                    $('#decision-elements-popup').append(data);

                                                                                    /*** admission elements */
                                                                                    $.ajax({
                                                                                        type: 'get',
                                                                                        url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=admission',

                                                                                        success: function (data) {
                                                                                            $('#admission-elements-popup').empty();

                                                                                            $('#admission-elements-popup').append(data);

                                                                                            $('#elements_detail').show();

                                                                                            //**** on change, check the elements and add it to selected elements */
                                                                                            var elements = filter.elements;
                                                                                            var others = filter.objects;
                                                                                            var methode = filter.methode;
                                                                                            var options = filter.options;
                                                                                           

                                                                                            for (var d in elements) {
                                                                                                if (isNaN(parseInt(d)))
                                                                                                    break;

                                                                                                $('#emundus_elm_' + elements[d]).prop("checked", true);
                                                                                                var checked = $('#emundus_elm_' + elements[d]).is(':checked');
                                                                                                if (checked == true) {
                                                                                                    var text =  $("label[for='emundus_elm_" + elements[d] + "']").text();
                                                                                                    $('#em-export').append('<li class="em-export-item" id="' + elements[d] + '-item"><button class="btn btn-danger btn-xs" id="' + elements[d] + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + text + '</strong></span></li>');
                                                                                                } else {
                                                                                                    $('#' + elements[d] + '-item').remove();
                                                                                                }
                                                                                            }

                                                                                            if(others != ""){

                                                                                                $('#oelts').find('input[type=checkbox]:checked').removeAttr('checked');

                                                                                                for (var d in others) {
                                                                                                    if (isNaN(parseInt(d)))
                                                                                                        break;

                                                                                                    $('#em-ex-' + others[d]).prop("checked", true);

                                                                                                }
                                                                                            }

                                                                                            $('input[name=em-export-methode][value="'+methode+'"]').prop("checked",true);

                                                                                            if(options != ""){

                                                                                                $('#forms').find('input[type=checkbox]:checked').removeAttr('checked');

                                                                                                for (var d in options) {
                                                                                                    if (isNaN(parseInt(d)))
                                                                                                        break;

                                                                                                    $('#'+ options[d]).prop("checked", true);

                                                                                                }
                                                                                            }

                                                                                        },
                                                                                        error: function (jqXHR, textStatus, errorThrown) {
                                                                                            console.log(jqXHR.responseText);
                                                                                        }
                                                                                    });

                                                                                },
                                                                                error: function (jqXHR, textStatus, errorThrown) {
                                                                                    console.log(jqXHR.responseText);
                                                                                }
                                                                            });

                                                                        },
                                                                        error: function (jqXHR, textStatus, errorThrown) {
                                                                            console.log(jqXHR.responseText);
                                                                        }
                                                                    });

                                                                    $('#elements-popup').empty();
                                                                    $('#elements-popup').append(data);
                                                                },
                                                                error: function (jqXHR, textStatus, errorThrown) {
                                                                    console.log(jqXHR.responseText);
                                                                }
                                                            });

                                                        } else {
                                                            $('.btn-success').hide();
                                                            $('#elements_detail').hide();
                                                            $('#elements-popup').hide();
                                                        }

                                                    }
                                                }
                                            } else {
                                                $('#err-filter').show();
                                                setTimeout(function(e) {
                                                    $('#err-filter').hide();
                                                }, 600);
                                            }

                                        },
                                        error: function(jqXHR, textStatus, errorThrown) {
                                            console.log(jqXHR.responseText);
                                        }
                                    });
                                }else{
                                    $('.btn-success').hide();
                                    $('#elements_detail').hide();
                                    $('#elements-popup').hide();
                                }
                            });

                            $('#em-export-prg').on('change', function() {

                                var code = $(this).val();
                                if (code != 0) {

                                    $.ajax({
                                        type:'get',
                                        url: 'index.php?option=com_emundus&controller=files&task=getProgramCampaigns&code=' + code,
                                        dataType:'json',

                                        success: function(result) {
                                            if (result.status) {
                                                $('#em-export-camp').empty();
                                                $('#em-export-camp').append('<option value="0" data-value="0">-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_CAMP')+' --</option>');
                                                $('#em-export-camp').append(result.html);
                                                $('#em-export-camp').trigger("chosen:updated");
                                                $('#camp').show();

                                                var year = $("#em-export-camp").val();

                                                    /*** application form elements */
                                                $.ajax({
                                                    type: 'get',
                                                    url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&viewcall=files&year='+year+'&code=' + code,

                                                    success: function (data) {
                                                        $('#em-export').empty();
                                                        $.ajax({
                                                            type:'get',
                                                            url: 'index.php?option=com_emundus&controller=files&task=getformelem&Itemid='+itemId,
                                                            dataType:'json',
                                                            success: function(result)
                                                            {
                                                                var item='';
                                                              
                                                                item ="";
                                                                if(view == "files"){
                                                                    for (var d in result.defaults) {
                                                                        if (isNaN(parseInt(d)))
                                                                            break;
                                                                        
                                                                        item += '<li class="em-export-item" id="' + result.defaults[d].id + '-item"><button class="btn btn-danger btn-xs" id="' + result.defaults[d].id + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + result.defaults[d].element_label + '</strong></span></li>';
                                                                    }
                                                                    $('#em-export').append(item);
                                                                }
            
                                                                /*** evaluation elements */
                                                                $.ajax({
                                                                    type: 'get',
                                                                    url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=evaluation',

                                                                    success: function (data) {
                                                                        $('#eval-elements-popup').empty();
                                                                        
                                                                            $.ajax({
                                                                                type:'get',
                                                                                url: 'index.php?option=com_emundus&controller=evaluation&task=getformelem',
                                                                                data: {code:code},
                                                                                dataType:'json',

                                                                                success: function(result) {

                                                                                    var item='';
                                                                                   
                                                                                    $('#eval-elements-popup').append(data);
                                                                                    item ="";
                                                                                    
                                                                                    if(view == "evaluation"){
                                                                                        for (var d in result.defaults) {
                                                                                            if (isNaN(parseInt(d)))
                                                                                                break;
                                                                                            item += '<li class="em-export-item" id="' + result.defaults[d].element_id + '-item"><button class="btn btn-danger btn-xs" id="' + result.defaults[d].element_id + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + result.defaults[d].element_label + '</strong></span></li>';
                                                                                            $('#emundus_elm_'+ result.defaults[d].element_id).prop("checked", true);
                                                                                        }
                                                                                        $('#em-export').append(item);
                                                                                    }
                                                                                    /*** decision elements */
                                                                                    $.ajax({
                                                                                        type: 'get',
                                                                                        url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=decision',

                                                                                        success: function (data) {
                                                                                            $('#decision-elements-popup').empty();
                                                                                            
                                                                                                $.ajax({
                                                                                                    type:'get',
                                                                                                    url: 'index.php?option=com_emundus&controller=admission&task=getformelem&form=decision',
                                                                                                    data: {code:code},
                                                                                                    dataType:'json',

                                                                                                    success: function(result) {            
                                                                                                      
                                                                                                
                                                                                                        $('#decision-elements-popup').append(data);
                                                                                                        
                                                                                                        $('.btn-success').show();

                                                                                                        /*** admission elements */
                                                                                                        $.ajax({
                                                                                                            type: 'get',
                                                                                                            url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=admission',

                                                                                                            success: function (data) {
                                                                                                                $('#admission-elements-popup').empty();
                                                                                                                
                                                                                                                    $.ajax({
                                                                                                                        type:'get',
                                                                                                                        url: 'index.php?option=com_emundus&controller=admission&task=getformelem&form=admission',
                                                                                                                        data: {code:code},
                                                                                                                        dataType:'json',

                                                                                                                        success: function(result) {

                                                                                                                            var item='';
                                                                                                                           
                                                                                                                            $('#admission-elements-popup').append(data);
                                                                                                                            item ="";
                                                                                                                            
                                                                                                                            if(view == "admission"){
                                                                                                                                for (var d in result.defaults) {
                                                                                                                                    if (isNaN(parseInt(d)))
                                                                                                                                        break;
                                                                                                                                    item += '<li class="em-export-item" id="' + result.defaults[d].element_id + '-item"><button class="btn btn-danger btn-xs" id="' + result.defaults[d].element_id + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + result.defaults[d].element_label + '</strong></span></li>';
                                                                                                                                    $('#emundus_elm_'+ result.defaults[d].element_id).prop("checked", true);
                                                                                                                                }
                                                                                                                                $('#em-export').append(item);
                                                                                                                            }
                                                                                                                           
                                                                                                                        },

                                                                                                                        error: function (jqXHR, textStatus, errorThrown) {
                                                                                                                            console.log(jqXHR.responseText);
                                                                                                                        }

                                                                                                                    });

                                                                                                            },
                                                                                                            error: function (jqXHR, textStatus, errorThrown) {
                                                                                                                console.log(jqXHR.responseText);
                                                                                                            }
                                                                                                        });
                                                                                                    },

                                                                                                    error: function (jqXHR, textStatus, errorThrown) {
                                                                                                        console.log(jqXHR.responseText);
                                                                                                    }

                                                                                                });

                                                                                        },
                                                                                        error: function (jqXHR, textStatus, errorThrown) {
                                                                                            console.log(jqXHR.responseText);
                                                                                        }
                                                                                    });
                                                                                },

                                                                                error: function (jqXHR, textStatus, errorThrown) {
                                                                                    console.log(jqXHR.responseText);
                                                                                }

                                                                            });

                                                                    },
                                                                    error: function (jqXHR, textStatus, errorThrown) {
                                                                        console.log(jqXHR.responseText);
                                                                    }
                                                                });

                                                            },
                                                            error: function (jqXHR, textStatus, errorThrown)
                                                            {
                                                                console.log(jqXHR.responseText);
                                                            }
                                                        });
                                                        // $('#elements_detail').show();
                                                        $('#elements-popup').empty();
                                                        $('#elements-popup').append(data);
                                                    },
                                                    error: function (jqXHR, textStatus, errorThrown) {
                                                        console.log(jqXHR.responseText);
                                                    }
                                                });

                                                $('.btn-success').show();
                                                $('#elements_detail').show();

                                            }

                                        },
                                        error: function(jqXHR, textStatus, errorThrown) {
                                            console.log(jqXHR.responseText);
                                        }
                                    });
                                }else{
                                    $('.btn-success').hide();
                                    $('#camp').hide();
                                    $('#elements_detail').hide();
                                    $('#elements-popup').hide();
                                }
                            });

                            $('#em-export-camp').on('change', function() {

                                var code = $('#em-export-prg').val();

                                if (code != 0) {

                                    var year = $("#em-export-camp").val();
                                     /*** application form elements */
                                    $.ajax({
                                        type: 'get',
                                        url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&viewcall=files&year='+year+'&code=' + code,

                                        success: function (data) {
                                            $('.btn-success').show();
                                            $('#em-export').empty();
                                            $.ajax({
                                                type:'get',
                                                url: 'index.php?option=com_emundus&controller=files&task=getformelem&Itemid='+itemId,
                                                dataType:'json',
                                                success: function(result)
                                                {
                                                    var item='';
                                                   
                                                    item ="";
                                                    if(view == "files"){
                                                        for (var d in result.defaults) {
                                                            if (isNaN(parseInt(d)))
                                                                break;

                                                            item += '<li class="em-export-item" id="' + result.defaults[d].id + '-item"><button class="btn btn-danger btn-xs" id="' + result.defaults[d].id + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + result.defaults[d].element_label + '</strong></span></li>';
                                                        }
                                                        $('#em-export').append(item);
                                                    }
                                                  
                                                     /*** evaluation elements */
                                                    $.ajax({
                                                        type: 'get',
                                                        url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=evaluation',

                                                        success: function (data) {
                                                            $('#eval-elements-popup').empty();
                                                            
                                                                $.ajax({
                                                                    type:'get',
                                                                    url: 'index.php?option=com_emundus&controller=evaluation&task=getformelem',
                                                                    data: {code:code},
                                                                    dataType:'json',

                                                                    success: function(result) {

                                                                        var item='';
                                                                        
                                                                        $('#eval-elements-popup').append(data);
                                                                        item ="";

                                                                        if(view == "evaluation"){
                                                                            for (var d in result.defaults) {
                                                                                if (isNaN(parseInt(d)))
                                                                                    break;
                                                                                item += '<li class="em-export-item" id="' + result.defaults[d].element_id + '-item"><button class="btn btn-danger btn-xs" id="' + result.defaults[d].element_id + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + result.defaults[d].element_label + '</strong></span></li>';
                                                                                $('#emundus_elm_'+ result.defaults[d].element_id).prop("checked", true);
                                                                            }
                                                                            $('#em-export').append(item);
                                                                        }
                                                                        /*** decision elements */
                                                                        $.ajax({
                                                                            type: 'get',
                                                                            url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=decision',

                                                                            success: function (data) {
                                                                                $('#decision-elements-popup').empty();
                                                                                
                                                                                    $.ajax({
                                                                                        type:'get',
                                                                                        url: 'index.php?option=com_emundus&controller=admission&task=getformelem&form=decision',
                                                                                        data: {code:code},
                                                                                        dataType:'json',

                                                                                        success: function(result) {

                                                                                            var item='';
                                                                                           
                                                                                            $('#decision-elements-popup').append(data);
                                                                                           
                                                                                            $('.btn-success').show();

                                                                                            /*** admission elements */
                                                                                            $.ajax({
                                                                                                type: 'get',
                                                                                                url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=admission',

                                                                                                success: function (data) {
                                                                                                    $('#admission-elements-popup').empty();
                                                                                                   
                                                                                                        $.ajax({
                                                                                                            type:'get',
                                                                                                            url: 'index.php?option=com_emundus&controller=admission&task=getformelem&form=admission',
                                                                                                            data: {code:code},
                                                                                                            dataType:'json',

                                                                                                            success: function(result) {

                                                                                                                var item='';
                                                                                                                
                                                                                                                $('#admission-elements-popup').append(data);
                                                                                                                item ="";
                                                                                                                if(view == "admission"){
                                                                                                                    for (var d in result.defaults) {
                                                                                                                        if (isNaN(parseInt(d)))
                                                                                                                            break;
                                                                                                                        item += '<li class="em-export-item" id="' + result.defaults[d].element_id + '-item"><button class="btn btn-danger btn-xs" id="' + result.defaults[d].element_id + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + result.defaults[d].element_label + '</strong></span></li>';
                                                                                                                        $('#emundus_elm_'+ result.defaults[d].element_id).prop("checked", true);
                                                                                                                    }
                                                                                                                    $('#em-export').append(item);
                                                                                                                }
                                                                                                                
                                                                                                            },

                                                                                                            error: function (jqXHR, textStatus, errorThrown) {
                                                                                                                console.log(jqXHR.responseText);
                                                                                                            }

                                                                                                        });

                                                                                                },
                                                                                                error: function (jqXHR, textStatus, errorThrown) {
                                                                                                    console.log(jqXHR.responseText);
                                                                                                }
                                                                                            });
                                                                                        },

                                                                                        error: function (jqXHR, textStatus, errorThrown) {
                                                                                            console.log(jqXHR.responseText);
                                                                                        }

                                                                                    });

                                                                            },
                                                                            error: function (jqXHR, textStatus, errorThrown) {
                                                                                console.log(jqXHR.responseText);
                                                                            }
                                                                        });
                                                                    },

                                                                    error: function (jqXHR, textStatus, errorThrown) {
                                                                        console.log(jqXHR.responseText);
                                                                    }

                                                                });

                                                        },
                                                        error: function (jqXHR, textStatus, errorThrown) {
                                                            console.log(jqXHR.responseText);
                                                        }
                                                    });
                                                },
                                                error: function (jqXHR, textStatus, errorThrown)
                                                {
                                                    console.log(jqXHR.responseText);
                                                }
                                            });
                                            $('#elements-popup').empty();
                                            $('#elements-popup').append(data);
                                        },
                                        error: function (jqXHR, textStatus, errorThrown) {
                                            console.log(jqXHR.responseText);
                                        }
                                    });

                                    $('.btn-success').show();
                                    $('#elements_detail').show();


                                } else {
                                    $('.btn-success').hide();
                                    $('#elements_detail').hide();
                                    $('#elements-popup').hide();
                                }
                            });
                            $('#data').append('<div id="main"><div id="list-element-export" class="panel panel-default xclsform"></div><div id="oelts"></div></div>');

                            var defaults = '<h5 style="padding-left:15px;">  '+Joomla.JText._('COM_EMUNDUS_CHOOSEN_FORM_ELEM')+'</h5><div class="well" id="em-export-elts" style="height:73%;overflow:auto;"  ><ul id="em-export"></ul></div>';


                            $('#list-element-export').append(defaults);

                            var grId = null;
                            var menu = null;

                            $('#oelts').append('<div style="height:150px; width:65%;" class="panel panel-default xclsform">' +
                            '<h5 style="padding-left:15px;">  '+Joomla.JText._('COM_EMUNDUS_CHOOSE_OTHER_COL')+'</h5>'+
                            '<div class="well" style="height:73%; overflow:auto;">'+
                            '<input class="em-ex-check" type="checkbox" value="photo" name="em-ex-photo" id="em-ex-photo" style="max-height:20px"/>' +
                            '<label for="em-ex-photo">'+Joomla.JText._('COM_EMUNDUS_PHOTO')+'</label> <br/>' +
                            '<input class="em-ex-check" type="checkbox" value="forms" name="em-ex-forms" id="em-ex-forms" style="max-height:20px"/>' +
                            '<label for="em-ex-forms">'+Joomla.JText._('COM_EMUNDUS_FORMS')+'</label> <br/>' +
                            '<input class="em-ex-check" type="checkbox" value="attachment" name="em-ex-attachment" id="em-ex-attachment" style="max-height:20px"/>' +
                            '<label for="em-ex-attachment">'+Joomla.JText._('COM_EMUNDUS_ATTACHMENT')+'</label> <br/>' +
                            //'<input class="em-ex-check" type="checkbox" value="assessment" name="em-ex-assessment" id="em-ex-assessment"/>' +
                           // '<label for="em-ex-assessment">'+Joomla.JText._('COM_EMUNDUS_ASSESSMENT')+'</label> <br/>' +
                            '<input class="em-ex-check" type="checkbox" value="comment" name="em-ex-comment" id="em-ex-comment" style="max-height:20px"/>' +
                            '<label for="em-ex-comment">'+Joomla.JText._('COM_EMUNDUS_COMMENT')+'</label> <br/>' +
                            '<input class="em-ex-check" type="checkbox" value="tags" name="em-ex-tags" id="em-ex-tags" style="max-height:20px"/>' +
                            '<label for="em-ex-tags">'+Joomla.JText._('JTAG')+'</label> <br/>' +
                            

                            '</div></div></div>');

                          
                            
                            $('#data').append( '<div id="methode">'+
                                '<div id="exp" class="panel panel-default">'+
                                '<b style="margin-left:15px; color:#32373D; text-transform:uppercase;">' +Joomla.JText._('COM_EMUNDUS_CHOOSE_EXTRACTION_OPTION')+ '</b>'+
                                '<div id="exp1"><form style="margin-left:15px; margin-bottom:6px">'+
                                    '<input type="radio" name="em-export-methode" id="em-export-methode" value="0" checked>' +Joomla.JText._('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE_AGGREGATE')+
                                    '<br/><input type="radio" name="em-export-methode" id="em-export-methode" value="1">' +Joomla.JText._('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE_LEFTJOIN')+ '<br>'+
                                '</form></div></div>'+
                                '<div id="forms" class="panel panel-default">'+
                                    '<b style="margin-left:15px; color:#32373D; text-transform:uppercase;">'+Joomla.JText._('COM_EMUNDUS_CHOOSE_OTHER_OPTION')+'</b>'+
                                    '<div id="forms1">'+
                                        '<input class="em-ex-check0" type="checkbox" value="form-title" name="form-title" id="form-title" max-height: 20px;/>' +
                                        '<label for="form-title">'+Joomla.JText._('COM_EMUNDUS_FORM_TITLE')+'</label> <br/>' +
                                        '<input class="em-ex-check0" type="checkbox" value="form-group" name="form-group" id="form-group" max-height: 20px;/>' +
                                        '<label for="form-group">'+Joomla.JText._('COM_EMUNDUS_FORM_GROUP')+'</label> <br/>' +
                                    '</div>'+
                                '</div>'+
                            '</div>' );

                            $('#em-export-prg').chosen({width: "95%"});
                            $('#em-export-camp').chosen({width: "95%"});
                            $('#filt_save').chosen({width: "85%"});
                            $('.xclsform').css({width: "95%", 'margin': "auto", 'margin-top': "15px"});
                            $('th').css({'padding-right':"40px"});
                            $('#main').css({width: "95%",'margin': "auto" ,'margin-bottom': "180px", 'position':"relative"});
                            $('#list-element-export').css({'float': "left",'height':"150px", 'width':"70%",'position':"absolute"});
                            $('#oelts').css({'float': "left", 'width':"30.7%", 'margin-left':"70%", 'position':"absolute"});

                            $('#methode').css({width: "95%",'margin': "auto", 'padding-bottom': "90px", 'position':"relative"});
                            $('#exp').css({'float': "left", 'width':"50%", 'position':"absolute"});
                            $('#forms').css({'width':"49%", 'margin-left':"51%", 'position':"absolute"});
                            $('#exp1').css({'background-color':"#f5f4f4", 'border-color':"#dddddd", 'border-style': "solid",'border-width': "0.5px", 'border-radius':"3px"});
                            $('#forms1').css({'background-color':"#f5f4f4", 'border-color':"#dddddd", 'border-style': "solid",'border-width': "0.5px", 'border-radius':"3px", 'padding-left':"15px"});

                            $('head').append('<link rel="stylesheet" href="media/com_emundus/css/emundus.css" type="text/css" />');




                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        console.log(jqXHR.responseText);
                    }
                });
                break;
            //export zip
            case 7:
                //*** zip export */
                $('#em-modal-actions .modal-body').empty();
                $('#em-modal-actions .modal-body').append('<div id="data"></div>');
                $('#data').append('<div class="panel panel-default pdform"><div class="panel-heading"><h5>'+Joomla.JText._('COM_EMUNDUS_CHOOSE_PRG')+'</h5></div><div class="panel-body"><select class="chzn-select" name="em-export-prg" id="em-export-prg"><option value="0" >-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_PRG')+' --</option></select><br/><br/><div id="camp" style="display:none;"><select name="em-export-camp" id="em-export-camp" style="display: none;" class="chzn-select"><option value="0" data-value="0">-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_CAMP')+' --</option></select></div></div></div>');
                $('#data').append(
                        '<div class="panel panel-default pdform">'+
                            '<div class="panel-heading">'+
                                '<input class="em-ex-check" type="checkbox" value="forms" name="forms" id="em-ex-forms" checked />' +
                                '<label for="em-ex-forms"><font color="black">'+Joomla.JText._('FORMS_PDF').toUpperCase()+'</font></label>'+
                            '</div>'+
                            '<div class="panel-body" id="felts" style="overflow:auto; display:none;"></div>'+
                        '</div>'+
                        '<div class="panel panel-default pdform">'+
                            '<div class="panel-heading">'+
                                '<input class="em-ex-check" type="checkbox" value="attachment" name="attachment" id="em-ex-attachment"/>' +
                                '<label for="em-ex-attachment"><font color="black">'+Joomla.JText._('ATTACHMENT_PDF').toUpperCase()+'</font></label>'+
                            '</div>'+
                            '<div class="panel-body" id="aelts" style="overflow:auto;display:none;"></div>'+
                       '</div>'+

                       '<div class="panel panel-default pdform">'+
                            '<div class="panel-heading">'+
                                '<input class="em-ex-check" type="checkbox"  value="assessment" name="assessment" id="em-ex-assessment"/>' +
                                '<label for="em-ex-assessment"><font color="black">'+Joomla.JText._('ASSESSMENT_PDF').toUpperCase()+'</font></label>'+
                            '</div>'+
                        '</div>'+

                        '<div class="panel panel-default pdform">'+
                            '<div class="panel-heading">'+
                                '<input class="em-ex-check" type="checkbox"  value="decision" name="decision" id="em-ex-decision"/>' +
                                '<label for="em-ex-decision"><font color="black">'+Joomla.JText._('DECISION_PDF').toUpperCase()+'</font></label>'+ 
                            '</div>'+
                        '</div>'+

                        '<div class="panel panel-default pdform" id="adm-exists" style="display:none;">'+
                            '<div class="panel-heading">'+
                                '<input class="em-ex-check" type="checkbox"  value="admission" name="admission" id="em-ex-admission"/>' +
                                '<label for="em-ex-admission"><font color="black">'+Joomla.JText._('ADMISSION_PDF').toUpperCase()+'</font></label>'+
                            '</div>'+
                        '</div><br/>');
                
                    $('#data').append('<div style="padding-left:30px" id="exp-opt">'+
                        '<label ><font color="black">'+Joomla.JText._('PDF_OPTIONS')+'</font></label>&ensp;&ensp;'+ 
                        '<select class="chzn-select" name="em-export-opt" id="em-export-opt" multiple>'+
                            '<option  value="aid" selected>'+Joomla.JText._('ID_CANDIDAT')+'</option>' +
                            '<option  value="afnum" selected>'+Joomla.JText._('FNUM')+'</option>' +
                            '<option  value="aemail" selected>'+Joomla.JText._('EMAIL')+'</option>' +
                            '<option  value="tags" selected>'+Joomla.JText._('PDF_TAGS')+'</option>' +
                            '<option  value="aapp-sent" selected>'+Joomla.JText._('APPLICATION_SENT_ON')+'</option>' +
                            '<option  value="adoc-print" selected>'+Joomla.JText._('DOCUMENT_PRINTED_ON')+'</option>' +
                            '<option  value="upload" selected>'+Joomla.JText._('FILES_UPLOADED')+'</option>' +
                        '</select>'+
                        '</div><br/>' );
                    
                    $('#em-export-opt').chosen({width:'89.8%'});
            
                
                $.ajax({
                    type:'get',
                    url: 'index.php?option=com_emundus&controller=files&task=checkadmission',
                    dataType:'json',

                    success: function(result) {
                        if(result.status){
                            $('#adm-exists').show();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        console.log(jqXHR.responseText);
                    }
                });

                var checkInput = getUserCheck();
                //alert(checkInput);
                var prghtml = "";
                var atthtml = "";
                //alert(url);
                $.ajax({
                    type:'post',
                    url: 'index.php?option=com_emundus&controller=files&task=getPDFProgrammes',
                    data: {checkInput : checkInput},
                    dataType:'json',

                    success: function(result) {
                        if (result.status) {
                            
                            $('#em-export-prg').append(result.html);
                            $('#em-export-prg').trigger("chosen:updated");
                            
                          
                            nbprg = $('#em-export-prg option').size();
                           
                            if (nbprg == 2) {
                                $('#em-export-prg option:eq(1)').attr('selected', true);
                                $('#em-export-prg').trigger("chosen:updated");

                                var code = $('#em-export-prg').val();
                                $.ajax({
                                    type:'get',
                                    url: 'index.php?option=com_emundus&controller=files&task=getPDFCampaigns&code=' + code,
                                    data: {checkInput : checkInput},
                                    dataType:'json',
    
                                    success: function(result) {
                                        if (result.status) {
                                            $('#em-export-camp').append(result.html);
                                            $('#em-export-camp').trigger("chosen:updated");
                                            $('#camp').show();

                                           

                                            var year = $("#em-export-camp").val();

                                            $.ajax({
                                                type:'get',
                                                url: 'index.php?option=com_emundus&controller=files&task=getformslist&code=' + code +'&year=' + year,
                                                dataType:'json',
                            
                                                success: function(result) {
                                                    if(result.status){
                                                        prghtml = result.html;
                                                        $('#felts').append(result.html);
                                                        $('#felts').toggle(400);
                                                        
                                                        $.ajax({
                                                            type:'get',
                                                            url: 'index.php?option=com_emundus&controller=files&task=getdoctype&code=' + code +'&year=' + year,
                                                            dataType:'json',
                                        
                                                            success: function(result) {
                                                                if(result.status){
                                                                    atthtml = result.html;
                                                                    $('#aelts').append(result.html);
                                                                    $('#aelts').toggle(400);
                                                                }
                                                            },
                                                            error: function (jqXHR, textStatus, errorThrown)
                                                            {
                                                                console.log(jqXHR.responseText);
                                                            }
                                                        });
                                                    }
                                                },
                                                error: function (jqXHR, textStatus, errorThrown)
                                                {
                                                    console.log(jqXHR.responseText);
                                                }
                                            });
                                           
                                        }
                                    },
                                    error: function (jqXHR, textStatus, errorThrown)
                                    {
                                        console.log(jqXHR.responseText);
                                    }
                                });
                            }
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        console.log(jqXHR.responseText);
                    }
                });

                
                $('#em-export-prg').on('change', function() {

                    var code = $(this).val();

                    if (code != 0) {
                      
                        $.ajax({
                            type:'get',
                            url: 'index.php?option=com_emundus&controller=files&task=getPDFCampaigns&code=' + code,
                            data: {checkInput : checkInput},
                            dataType:'json',

                            success: function(result) {
                                if (result.status) {
                                    $('#em-export-camp').empty();
                                    $('#em-export-camp').append('<option value="0" data-value="0">-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_CAMP')+' --</option>');
                                    $('#em-export-camp').append(result.html);
                                    $('#em-export-camp').trigger("chosen:updated");
                                    $('#camp').show();

                                    var year = $("#em-export-camp").val();

                                    $.ajax({
                                        type:'get',
                                        url: 'index.php?option=com_emundus&controller=files&task=getformslist&code=' + code +'&year=' + year,
                                        dataType:'json',
                    
                                        success: function(result) {
                                            if(result.status){
                                                
                                                prghtml = result.html;
                                                $('#felts-'+code+year).parent('div').remove();
                                                $('#felts').append(result.html);
                                                $('#felts').show();
                                                
                                                $.ajax({
                                                    type:'get',
                                                    url: 'index.php?option=com_emundus&controller=files&task=getdoctype&code=' + code +'&year=' + year,
                                                    dataType:'json',
                                
                                                    success: function(result) {
                                                        if(result.status){
                                                            atthtml = result.html;
                                                            $('#aelts-'+code+year).parent('div').remove();
                                                            $('#aelts').append(result.html);
                                                            $('#aelts').show();
                                                           
                                                        }
                                                    },
                                                    error: function (jqXHR, textStatus, errorThrown)
                                                    {
                                                        console.log(jqXHR.responseText);
                                                    }
                                                });
                                            }
                                        },
                                        error: function (jqXHR, textStatus, errorThrown)
                                        {
                                            console.log(jqXHR.responseText);
                                        }
                                    });
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown)
                            {
                                console.log(jqXHR.responseText);
                            }
                        });
                    }else{
                        $('#camp').hide();
                        $('#felts').hide();
                        $('#aelts').hide();
                        $('#felts').empty();
                        $('#aelts').empty();
                    }
                        
                });


                $('#em-export-camp').on('change', function() {
                              
                    var code = $('#em-export-prg').val();

                    if (code != 0) {
                        var year = $("#em-export-camp").val();
                        if (year != 0) {
                            $.ajax({
                                type:'get',
                                url: 'index.php?option=com_emundus&controller=files&task=getformslist&code=' + code +'&year=' + year,
                                dataType:'json',
            
                                success: function(result) {
                                    if(result.status){
                                        
                                        $('#felts-'+code+year).parent('div').remove();
                                        $('#felts-'+code+'0').parent('div').remove();
                                        $('#felts').append(result.html);
                                        $('#felts').show();

                                        $.ajax({
                                            type:'get',
                                            url: 'index.php?option=com_emundus&controller=files&task=getdoctype&code=' + code +'&year=' + year,
                                            dataType:'json',
                        
                                            success: function(result) {
                                                if(result.status){
                                                    $('#aelts-'+code+year).parent('div').remove();
                                                    $('#aelts-'+code+'0').parent('div').remove();
                                                    $('#aelts').append(result.html);
                                                    $('#aelts').show();
                                                }
                                            },
                                            error: function (jqXHR, textStatus, errorThrown)
                                            {
                                                console.log(jqXHR.responseText);
                                            }
                                        });
                                    }
                                },
                                error: function (jqXHR, textStatus, errorThrown)
                                {
                                    console.log(jqXHR.responseText);
                                }
                            });
                        }else{
                            $('[id^=felts-'+code+']').parent('div').remove();
                            $('[id^=aelts-'+code+']').parent('div').remove();
                            
                            $('#felts').append(prghtml);
                            $('#aelts').append(atthtml);
                        }
                    }
                });

                    
                $('#em-ex-forms').click(function(e){
                    if ($('#em-ex-forms').is(":checked")){
                        $('[id^=felts-]').hide();
                        $('#felts input').attr('checked', false);
                        $('#felts button').removeClass("btn btn-elements-success").addClass("btn btn-info");
                        $('#felts span').removeClass("glyphicon-minus").addClass("glyphicon-plus");
                    }else{
                        $('[id^=felts-]').show();
                        $('#felts button').removeClass("btn btn-info").addClass("btn btn-elements-success");
                        $('#felts span').removeClass("glyphicon-plus").addClass("glyphicon-minus");
                    }
                        
                });

                $('#em-ex-attachment').click(function(e){
                    if ($('#em-ex-attachment').is(":checked")){
                        $('[id^=aelts-]').hide();
                        $('#aelts input').attr('checked', false);
                        $('#aelts button').removeClass("btn btn-elements-success").addClass("btn btn-info");
                        $('#aelts span').removeClass("glyphicon-minus").addClass("glyphicon-plus");
                    }else{
                        $('[id^=aelts-]').show();
                        $('#aelts button').removeClass("btn btn-info").addClass("btn btn-elements-success");
                        $('#aelts span').removeClass("glyphicon-plus").addClass("glyphicon-minus");
                    }
                       
                });

                $('#felts').click(function(e){
                    if ($(".em-ex-check").is(":checked"))
                        $('#em-ex-forms').attr('checked', false);
                    
                });

                $('#aelts').click(function(e){
                    if ($(".em-ex-check").is(":checked"))
                        $('#em-ex-attachment').attr('checked', false);
                    
                });


                $('#data').click(function(e){
                    if ($(".em-ex-check").is(":checked"))
                        $('#exp-opt').show();
                    else
                        $('#exp-opt').hide();
                });

                $('#felts').click(function(e){
                    if ($(".em-ex-check").is(":checked"))
                        $('#exp-opt').show();
                    else
                        $('#exp-opt').hide();
                });

                $('#aelts').click(function(e){
                    if ($(".em-ex-check").is(":checked"))
                        $('#exp-opt').show();
                    else
                        $('#exp-opt').hide();
                });
                
                   
                   
                $('#em-export-prg').chosen({width: "95%"});
                $('#em-export-camp').chosen({width: "95%"});
                $('.pdform').css({width: "95%", 'margin': "auto", 'margin-top': "15px", 'border-radius':"4px"});
                
                //$('#em-modal-actions .modal-footer ').hide();
                $('#can-val').empty();
                $('#can-val').append('<a class="btn btn-default btn-attach" id="em_zip" href="'+url+'">'+Joomla.JText._('GENERATE_ZIP')+'</a><div id="attachement_res"></div>'); 
                $('#can-val').show();

                $('#em-modal-actions .modal-dialog').addClass('modal-lg');
                $('#em-modal-actions .modal').show();
                $('#em-modal-actions').modal({backdrop:false, keyboard:true},'toggle');
                
                //*** end zip view */

               
                break;
            //export PDF;
            case 8 :
                
                $('#em-modal-actions .modal-body').empty();
                $('#em-modal-actions .modal-body').append('<div id="data"></div>');
                $('#data').append('<div class="panel panel-default pdform"><div class="panel-heading"><h5>'+Joomla.JText._('COM_EMUNDUS_CHOOSE_PRG')+'</h5></div><div class="panel-body"><select class="chzn-select" name="em-export-prg" id="em-export-prg"><option value="0" >-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_PRG')+' --</option></select><br/><br/><div id="camp" style="display:none;"><select name="em-export-camp" id="em-export-camp" style="display: none;" class="chzn-select"><option value="0" data-value="0">-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_CAMP')+' --</option></select></div></div></div>');
                $('#data').append(
                        '<div class="panel panel-default pdform">'+
                            '<div class="panel-heading">'+
                                '<input class="em-ex-check" type="checkbox" value="forms" name="forms" id="em-ex-forms" checked />' +
                                '<label for="em-ex-forms"><font color="black">'+Joomla.JText._('FORMS_PDF').toUpperCase()+'</font></label>'+
                            '</div>'+
                            '<div class="panel-body" id="felts" style="overflow:auto;display:none;"></div>'+
                        '</div>'+
                        '<div class="panel panel-default pdform">'+
                            '<div class="panel-heading">'+
                                '<input class="em-ex-check" type="checkbox" value="attachment" name="attachment" id="em-ex-attachment"/>' +
                                '<label for="em-ex-attachment"><font color="black">'+Joomla.JText._('ATTACHMENT_PDF').toUpperCase()+'</font></label>'+
                            '</div>'+
                            '<div class="panel-body" id="aelts" style="overflow:auto;display:none;"></div>'+
                       '</div>'+

                       '<div class="panel panel-default pdform">'+
                            '<div class="panel-heading">'+
                                '<input class="em-ex-check" type="checkbox"  value="assessment" name="assessment" id="em-ex-assessment"/>' +
                                '<label for="em-ex-assessment"><font color="black">'+Joomla.JText._('ASSESSMENT_PDF').toUpperCase()+'</font></label>'+
                            '</div>'+
                        '</div>'+

                        '<div class="panel panel-default pdform">'+
                            '<div class="panel-heading">'+
                                '<input class="em-ex-check" type="checkbox"  value="decision" name="decision" id="em-ex-decision"/>' +
                                '<label for="em-ex-decision"><font color="black">'+Joomla.JText._('DECISION_PDF').toUpperCase()+'</font></label>'+ 
                            '</div>'+
                        '</div>'+

                        '<div class="panel panel-default pdform" id="adm-exists" style="display:none;">'+
                            '<div class="panel-heading">'+
                                '<input class="em-ex-check" type="checkbox"  value="admission" name="admission" id="em-ex-admission"/>' +
                                '<label for="em-ex-admission"><font color="black">'+Joomla.JText._('ADMISSION_PDF').toUpperCase()+'</font></label>'+
                            '</div>'+
                        '</div><br/>');

                        $('#data').append('<div style="padding-left:30px" id="exp-opt">'+
                            '<label ><font color="black">'+Joomla.JText._('PDF_OPTIONS')+'</font></label>&ensp;&ensp;'+ 
                            '<select class="chzn-select" name="em-export-opt" id="em-export-opt" multiple>'+
                                '<option  value="aid" selected>'+Joomla.JText._('ID_CANDIDAT')+'</option>' +
                                '<option  value="afnum" selected>'+Joomla.JText._('FNUM')+'</option>' +
                                '<option  value="aemail" selected>'+Joomla.JText._('EMAIL')+'</option>' +
                                '<option  value="tags" selected>'+Joomla.JText._('PDF_TAGS')+'</option>' +
                                '<option  value="aapp-sent" selected>'+Joomla.JText._('APPLICATION_SENT_ON')+'</option>' +
                                '<option  value="adoc-print" selected>'+Joomla.JText._('DOCUMENT_PRINTED_ON')+'</option>' +
                                '<option  value="upload" selected>'+Joomla.JText._('FILES_UPLOADED')+'</option>' +
                            '</select>'+
                        '</div><br/>' );
                        
                        $('#em-export-opt').chosen({width:'89.8%'});

                        $.ajax({
                            type:'get',
                            url: 'index.php?option=com_emundus&controller=files&task=checkadmission',
                            dataType:'json',
        
                            success: function(result) {
                                if(result.status){
                                    $('#adm-exists').show();
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown)
                            {
                                console.log(jqXHR.responseText);
                            }
                        });
                
                var checkInput = getUserCheck();
                //alert(checkInput);
                var prghtml = "";
                var atthtml = "";

                $.ajax({
                    type:'post',
                    url: 'index.php?option=com_emundus&controller=files&task=getPDFProgrammes',
                    data: {checkInput : checkInput},
                    dataType:'json',

                    success: function(result) {
                        if (result.status) {
                            
                            $('#em-export-prg').append(result.html);
                            $('#em-export-prg').trigger("chosen:updated");
                            
                          
                            nbprg = $('#em-export-prg option').size();
                           
                            if (nbprg == 2) {
                                $('#em-export-prg option:eq(1)').attr('selected', true);
                                $('#em-export-prg').trigger("chosen:updated");

                                var code = $('#em-export-prg').val();
                                $.ajax({
                                    type:'get',
                                    url: 'index.php?option=com_emundus&controller=files&task=getPDFCampaigns&code=' + code,
                                    data: {checkInput : checkInput},
                                    dataType:'json',
    
                                    success: function(result) {
                                        if (result.status) {
                                            $('#em-export-camp').append(result.html);
                                            $('#em-export-camp').trigger("chosen:updated");
                                            $('#camp').show();

                                           
                                            var year = $("#em-export-camp").val();

                                            $.ajax({
                                                type:'get',
                                                url: 'index.php?option=com_emundus&controller=files&task=getformslist&code=' + code +'&year=' + year,
                                                dataType:'json',
                            
                                                success: function(result) {
                                                    if(result.status){
                                                        prghtml = result.html;
                                                        $('#felts').append(result.html);
                                                        $('#felts').toggle(400);

                                                        $.ajax({
                                                            type:'get',
                                                            url: 'index.php?option=com_emundus&controller=files&task=getdoctype&code=' + code +'&year=' + year,
                                                            dataType:'json',
                                        
                                                            success: function(result) {
                                                                if(result.status){
                                                                    atthtml = result.html;
                                                                    $('#aelts').append(result.html);
                                                                    $('#aelts').toggle(400);
                                                                }
                                                            },
                                                            error: function (jqXHR, textStatus, errorThrown)
                                                            {
                                                                console.log(jqXHR.responseText);
                                                            }
                                                        });
                                                    }
                                                },
                                                error: function (jqXHR, textStatus, errorThrown)
                                                {
                                                    console.log(jqXHR.responseText);
                                                }
                                            });
                                           
                                        }
                                    },
                                    error: function (jqXHR, textStatus, errorThrown)
                                    {
                                        console.log(jqXHR.responseText);
                                    }
                                });
                            }
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        console.log(jqXHR.responseText);
                    }
                });

                
                $('#em-export-prg').on('change', function() {

                    var code = $(this).val();

                    if (code != 0) {
                      
                        $.ajax({
                            type:'get',
                            url: 'index.php?option=com_emundus&controller=files&task=getPDFCampaigns&code=' + code,
                            data: {checkInput : checkInput},
                            dataType:'json',

                            success: function(result) {
                                if (result.status) {
                                    $('#em-export-camp').empty();
                                    $('#em-export-camp').append('<option value="0" data-value="0">-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_CAMP')+' --</option>');
                                    $('#em-export-camp').append(result.html);
                                    $('#em-export-camp').trigger("chosen:updated");
                                    $('#camp').show();

                                    var year = $("#em-export-camp").val();

                                    $.ajax({
                                        type:'get',
                                        url: 'index.php?option=com_emundus&controller=files&task=getformslist&code=' + code +'&year=' + year,
                                        dataType:'json',
                    
                                        success: function(result) {
                                            if(result.status){
                                                
                                                prghtml = result.html;
                                                $('#felts-'+code+year).parent('div').remove();
                                                $('#felts').append(result.html);
                                                $('#felts').show();
                                                
                                                $.ajax({
                                                    type:'get',
                                                    url: 'index.php?option=com_emundus&controller=files&task=getdoctype&code=' + code +'&year=' + year,
                                                    dataType:'json',
                                
                                                    success: function(result) {
                                                        if(result.status){
                                                            atthtml = result.html;
                                                            $('#aelts-'+code+year).parent('div').remove();
                                                            $('#aelts').append(result.html);
                                                            $('#aelts').show();
                                                        }
                                                    },
                                                    error: function (jqXHR, textStatus, errorThrown)
                                                    {
                                                        console.log(jqXHR.responseText);
                                                    }
                                                });
                                            }
                                        },
                                        error: function (jqXHR, textStatus, errorThrown)
                                        {
                                            console.log(jqXHR.responseText);
                                        }
                                    });
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown)
                            {
                                console.log(jqXHR.responseText);
                            }
                        });
                    }else{
                        $('#camp').hide();
                        $('#felts').hide();
                        $('#aelts').hide();
                        $('#felts').empty();
                        $('#aelts').empty();
                    }
                        
                });


                $('#em-export-camp').on('change', function() {
                              
                    var code = $('#em-export-prg').val();

                    if (code != 0) {
                        var year = $("#em-export-camp").val();
                        if (year != 0) {
                            $.ajax({
                                type:'get',
                                url: 'index.php?option=com_emundus&controller=files&task=getformslist&code=' + code +'&year=' + year,
                                dataType:'json',
            
                                success: function(result) {
                                    if(result.status){
                                        
                                        $('#felts-'+code+year).parent('div').remove();
                                        $('#felts-'+code+'0').parent('div').remove();
                                        $('#felts').append(result.html);
                                        $('#felts').show();
                                       
                                        $.ajax({
                                            type:'get',
                                            url: 'index.php?option=com_emundus&controller=files&task=getdoctype&code=' + code +'&year=' + year,
                                            dataType:'json',
                        
                                            success: function(result) {
                                                if(result.status){
                                                    $('#aelts-'+code+year).parent('div').remove();
                                                    $('#aelts-'+code+'0').parent('div').remove();
                                                    $('#aelts').append(result.html);
                                                    $('#aelts').show();
                                                }
                                            },
                                            error: function (jqXHR, textStatus, errorThrown)
                                            {
                                                console.log(jqXHR.responseText);
                                            }
                                        });
                                    }
                                },
                                error: function (jqXHR, textStatus, errorThrown)
                                {
                                    console.log(jqXHR.responseText);
                                }
                            });
                        }else{
                            $('[id^=felts-'+code+']').parent('div').remove();
                            $('[id^=aelts-'+code+']').parent('div').remove();
                           
                            $('#felts').append(prghtml);
                            $('#aelts').append(atthtml);
                        }
                    }
                });

                    
                $('#em-ex-forms').click(function(e){
                    if ($('#em-ex-forms').is(":checked")){
                        $('[id^=felts-]').hide();
                        $('#felts input').attr('checked', false);
                        $('#felts button').removeClass("btn btn-elements-success").addClass("btn btn-info");
                        $('#felts span').removeClass("glyphicon-minus").addClass("glyphicon-plus");
                    }else{
                        $('[id^=felts-]').show();
                        $('#felts button').removeClass("btn btn-info").addClass("btn btn-elements-success");
                        $('#felts span').removeClass("glyphicon-plus").addClass("glyphicon-minus");
                    }
                        
                });

                $('#em-ex-attachment').click(function(e){
                    if ($('#em-ex-attachment').is(":checked")){
                        $('[id^=aelts-]').hide();
                        $('#aelts input').attr('checked', false);
                        $('#aelts button').removeClass("btn btn-elements-success").addClass("btn btn-info");
                        $('#aelts span').removeClass("glyphicon-minus").addClass("glyphicon-plus");
                    }else{
                        $('[id^=aelts-]').show();
                        $('#aelts button').removeClass("btn btn-info").addClass("btn btn-elements-success");
                        $('#aelts span').removeClass("glyphicon-plus").addClass("glyphicon-minus");
                    }
                       
                });
                
                
                $('#felts').click(function(e){
                    if ($(".em-ex-check").is(":checked"))
                        $('#em-ex-forms').attr('checked', false);
                    
                });

                $('#aelts').click(function(e){
                    if ($(".em-ex-check").is(":checked"))
                        $('#em-ex-attachment').attr('checked', false);
                    
                });

                $('#data').click(function(e){
                    if ($(".em-ex-check").is(":checked"))
                        $('#exp-opt').show();
                    else
                        $('#exp-opt').hide();
                });

                $('#felts').click(function(e){
                    if ($(".em-ex-check").is(":checked"))
                        $('#exp-opt').show();
                    else
                        $('#exp-opt').hide();
                });

                $('#aelts').click(function(e){
                    if ($(".em-ex-check").is(":checked"))
                        $('#exp-opt').show();
                    else
                        $('#exp-opt').hide();
                });
                
                  
                   
                $('#em-export-prg').chosen({width: "95%"});
                $('#em-export-camp').chosen({width: "95%"});
                $('.pdform').css({width: "95%", 'margin': "auto", 'margin-top': "15px", 'border-radius':"4px"});
               
                $('#can-val').empty();
                $('#can-val').append('<a class="btn btn-default btn-attach" id="em_generate" href="'+url+'">'+Joomla.JText._('GENERATE_PDF')+'</a><div id="attachement_res"></div>');
                $('#can-val').show();
                $('#em-modal-actions .modal-dialog').addClass('modal-lg');
                $('#em-modal-actions .modal').show();
                $('#em-modal-actions').modal({backdrop:false, keyboard:true},'toggle');
                break;
            // Mail applicants
            case 9:
                 $('#can-val').empty();
                if($('#em-check-all-all').is(':checked'))
                {
                    var fnums = 'all';
                }
                else
                {
                    var fnums = [];
                    $('.em-check:checked').each(function()
                    {
                        var id = $(this).attr('id').split('_')[0];
                        var cid = parseInt(id.substr(14, 7));
                        var sid = parseInt(id.substr(21, 7));
                        fnums.push({fnum: id, cid: cid, sid:sid});
                    });
                }
                fnums = JSON.stringify(fnums);
                $('.modal-body').append('<div>' +'<img src="'+loadingLine+'" alt="'+Joomla.JText._('LOADING')+'"/>' +'</div>');
                $('.modal-footer').hide();
                $('.modal-dialog').addClass('modal-lg');
                $('.modal-body').empty();
                //var url = 'index.php?option=com_emundus&view=email&tmpl=component&Itemid='+itemId+'&fnums='+encodeURIComponent(fnums)+'&desc=0';
                $('.modal-body').append('<iframe src="'+url+'" style="width:'+window.getWidth()*0.8+'px;height:'+window.getHeight()*0.8+'px;border:none;"></iframe>');
                break;
            // Comment
            case 10:
                $('#can-val').empty();
                $('#can-val').append('<button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'</button>'+
                                    '<button style="margin-left:5px;" type="button" class="btn btn-success">'+Joomla.JText._('OK')+'</button>');
                $('#can-val').show();
                var textArea = '<form>' +
                    '<input placeholder="'+Joomla.JText._('TITLE')+'" class="form-control" id="comment-title" type="text" value="" name="comment-title"/>' +
                    '<textarea placeholder="'+Joomla.JText._('ENTER_COMMENT')+'" class="form-control" style="height:250px !important; margin-left:0px !important;"  id="comment-body"></textarea>' +
                    '</form>';


                $('.modal-body').append(textArea);
                break;
            // Access
            case 11:
                var checkInput = getUserCheck();
                $('#can-val').empty();
                $('#can-val').append('<button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'</button>'+
                                    '<button style="margin-left:5px;" type="button" class="btn btn-success">'+Joomla.JText._('OK')+'</button>');
                $('#can-val').show();
                $('.modal-dialog').addClass('modal-lg');
                $('.modal-body').append('<div>' +'<img src="'+loadingLine+'" alt="'+Joomla.JText._('LOADING')+'"/>' +'</div>');
                //url = 'index.php?option=com_emundus&view='+$('#view').val()+'&format=raw&layout=access';
                $.ajax({
                    type:'GET',
                    url:url,
                    data:{users:checkInput},
                    dataType:'html',
                    success: function(result)
                    {
                        $('.modal-body').empty();
                        $('.modal-body').append(result);
                        $('.modal-chzn-select').chosen({width:'75%'});
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        console.log(jqXHR.responseText);
                    }
                });

                break;
            // Status
            case 13:
                $('#can-val').empty();
                $('#can-val').append('<button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'</button>'+
                                    '<button style="margin-left:5px;" type="button" class="btn btn-success">'+Joomla.JText._('OK')+'</button>');
                $('#can-val').show();

                $('.modal-body').append('<div>' +
                '<img src="'+loadingLine+'" alt="loading"/>' +
                '</div>');
                //var url = 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=getstate';
                $.ajax({
                    type:'get',
                    url:url,
                    dataType:'json',
                    success: function(result)
                    {
                        $('.modal-body').empty();
                        
                        var status = '<br/><div class="form-group" style="color:black !important"><label class="col-lg-2 control-label">'+result.state+'</label><select class="col-lg-7 modal-chzn-select data-placeholder="'+result.select_state+'" name="em-action-state" id="em-action-state" value="">';

                        for (var i in result.states)
                        {
                            if(isNaN(parseInt(i)))
                                break;
                            status += '<option value="'+result.states[i].step+'" >'+result.states[i].value+'</option>';
                        }
                        '</select></div>';
                        $('.modal-body').append(status);

                        $('.modal-chzn-select').chosen({width:'75%'});
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        console.log(jqXHR.responseText);
                    }
                });

                break;
            // tags
            case 14:
                
                $('#can-val').empty();
                $('#can-val').append('<button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'</button>'+
                                    '<button style="margin-left:5px;" type="button" class="btn btn-success">'+Joomla.JText._('OK')+'</button>');
                $('#can-val').show();

                $('.modal-body').append('<div>' +
                '<img src="'+loadingLine+'" alt="loading"/>' +
                '</div>');
                //var url = 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=gettags';
                $.ajax({
                    type:'get',
                    url:url,
                    dataType:'json',
                    success: function(result)
                    {
                        $('.modal-body').empty();
                        
                        var tags = '<br/><div class="form-group" style="color:black !important"><label class="col-lg-2 control-label">'+result.tag+'</label><select class="col-lg-7 modal-chzn-select data-placeholder="'+result.select_tag+'" name="em-action-tag" id="em-action-tag" value="">';


                        for (var i in result.tags)
                        {
                            if(isNaN(parseInt(i)))
                                break;
                            tags += '<option value="'+result.tags[i].id+'" >'+result.tags[i].label+'</option>';
                        }
                        '</select></div>';
                        $('.modal-body').append(tags);
                        $('.modal-chzn-select').chosen({width:'75%'});
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        console.log(jqXHR.responseText);
                    }
                });

                break;
            // email evaluator
            case 15:
                $('#can-val').empty();
                /*if($('#em-check-all-all').is(':checked'))
                 {
                 var fnums = 'all';
                 }
                 else
                 {
                 var fnums = [];
                 $('.em-check:checked').each(function()
                 {
                 var id = $(this).attr('id').split('_')[0];
                 var cid = parseInt(id.substr(14, 7));
                 var sid = parseInt(id.substr(21, 7));
                 fnums.push({fnum: id, cid: cid, sid:sid});
                 });
                 }
                 fnums = JSON.stringify(fnums);*/
                $('.modal-body').append('<div>' +
                '<img src="'+loadingLine+'" alt="loading"/>' +
                '</div>');
                $('.modal-footer').hide();
                $('.modal-dialog').addClass('modal-lg');
                $('.modal-body').empty();
                //var url = 'index.php?option=com_emundus&view=email&tmpl=component&Itemid='+itemId+'&fnums='+encodeURIComponent(fnums)+'&desc=1';
                $('.modal-body').append('<iframe src="'+url+'" style="width:'+window.getWidth()*0.8+'px;height:'+window.getHeight()*0.8+'px;border:none;"></iframe>');

                break;

            // email groups
            case 16:
                //  if($('#em-check-all-all').is(':checked'))
                //    {
                //       var fnums = 'all';
                //   }
                //   else
                //   {
                //       var fnums = [];
                //       $('.em-check:checked').each(function()
                //                                  {
                //                                      var id = $(this).attr('id').split('_')[0];
                //                                      var cid = parseInt(id.substr(14, 7));
                //                                      var sid = parseInt(id.substr(21, 7));
                //                                      fnums.push({fnum: id, cid: cid, sid:sid});
                //                                  });
                //  }
                //  fnums = JSON.stringify(fnums);
                $('#can-val').empty();
                $('.modal-body').append('<div>' +
                '<img src="'+loadingLine+'" alt="loading"/>' +
                '</div>');
                $('.modal-footer').hide();
                $('.modal-dialog').addClass('modal-lg');
                //var url = 'index.php?option=com_emundus&view=email&tmpl=component&Itemid='+itemId+'&fnums='+encodeURIComponent(fnums)+'&desc=2';
                $('.modal-body').empty();
                $('.modal-body').append('<iframe src="'+url+'" style="width:'+window.getWidth()*0.8+'px;height:'+window.getHeight()*0.8+'px;border:none;"></iframe>');

            // email custom
            case 17:
            case 18:
                //  if($('#em-check-all-all').is(':checked'))
                //   {
                //       var fnums = 'all';
                //  }
                //   else
                //   {
                //      var fnums = [];
                //      $('.em-check:checked').each(function()
                //                                  {
                //                                      var id = $(this).attr('id').split('_')[0];
                //                                      var cid = parseInt(id.substr(14, 7));
                //                                      var sid = parseInt(id.substr(21, 7));
                //                                      fnums.push({fnum: id, cid: cid, sid:sid});
                //                                 });
                //  }
                //  fnums = JSON.stringify(fnums);
                $('#can-val').empty();
                $('.modal-body').append('<div>' +
                '<img src="'+loadingLine+'" alt="loading"/>' +
                '</div>');
                $('.modal-footer').hide();
                $('.modal-dialog').addClass('modal-lg');
                //var url = 'index.php?option=com_emundus&view=email&tmpl=component&Itemid='+itemId+'&fnums='+encodeURIComponent(fnums)+'&desc=3';
                $('.modal-body').empty();
                $('.modal-body').append('<iframe src="'+url+'" style="width:'+window.getWidth()*0.8+'px;height:'+window.getHeight()*0.8+'px;border:none;"></iframe>');

                break;
            // generate DOCX
            case 27:
                $('#can-val').empty();
                $('#can-val').append('<button type="button" class="btn btn-danger" data-dismiss="modal">'+Joomla.JText._('CANCEL')+'</button>'+
                                    '<button style="margin-left:5px;" type="button" class="btn btn-success">'+Joomla.JText._('OK')+'</button>');
                $('#can-val').show();

                $('#em-modal-actions .modal-body').empty();
                $('.modal-body').append('<div>' +
                '<img src="'+loadingLine+'" alt="loading"/>' +
                '</div>');
                $('#em-modal-actions .modal-dialog').addClass('modal-lg');
                $('#em-modal-actions .modal').show();
                $('#em-modal-actions').modal({backdrop:false, keyboard:true},'toggle');
                $.ajax({
                    type:'get',
                    url:url,
                    dataType:'html',
                    success: function(result)
                    {
                        $('.modal-body').empty();
                        $('.modal-body').append(result);
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        console.log(jqXHR.responseText);
                    }
                });

                break;
            // publication status od the application file
            case 28:
                
                $('.modal-body').append('<div>' +
                '<img src="'+loadingLine+'" alt="loading"/>' +
                '</div>');
                //var url = 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=getpublish';
                $.ajax({
                    type:'get',
                    url:url,
                    dataType:'json',
                    success: function(result)
                    {
                        $('.modal-body').empty();
                        var status = '<br/><div class="form-group" style="color:black !important"><label class="col-lg-2 control-label">'+result.state+'</label><select class="col-lg-7 modal-chzn-select data-placeholder="'+result.select_state+'" name="em-action-publish" id="em-action-publish" value="">';

                        for (var i in result.states)
                        {
                            if(isNaN(parseInt(i)))
                                break;
                            status += '<option value="'+result.states[i].step+'" >'+result.states[i].value+'</option>';
                        }
                        '</select></div>';
                        $('.modal-body').append(status);

                        $('.modal-chzn-select').chosen({width:'75%'});
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        console.log(jqXHR.responseText);
                    }
                });

                break;

                // decision
                case 29:
                break;

                // trombinoscope
                case 31:
                $('#can-val').empty();
                $('.modal-body').append('<div>' +
                '<img src="'+loadingLine+'" alt="loading"/>' +
                '</div>');
                $('.modal-footer').hide();
                $('.modal-dialog').addClass('modal-lg');
                //var url = 'index.php?option=com_emundus&view=email&tmpl=component&Itemid='+itemId+'&fnums='+encodeURIComponent(fnums)+'&desc=2';
                $('.modal-body').empty();
                $('.modal-body').append('<iframe src="'+url+'" style="width:'+window.getWidth()*0.8+'px;height:'+window.getHeight()*0.8+'px;border:none;"></iframe>');

                break;

            default:
                break;
        }

    });

    // zip file generation
    $(document).on('click', '#em_zip', function(e) {
        e.preventDefault();
        $.ajaxQ.abortAll();

        var url = $(this).attr('href');
        
        var forms = 0;
        var attachment  = 0;
        var assessment  = 0;
        var decision    = 0;
        var admission   = 0;
        var form_checked = [];
        var attach_checked = [];
        var options = [];
        
        if ($('#em-check-all-all').is(':checked')) {
            var fnums = 'all';
        } else {
            fnums = [];
            var myJSONObject = '{';
            var i = 0;
            $('.em-check:checked').each(function()
            {
                myJSONObject += '"'+i+'"'+':"'+$(this).attr('id').split('_')[0]+'",';
                i++;
            });
            myJSONObject = myJSONObject.substr(0, myJSONObject.length-1);
            myJSONObject += '}';

            if (myJSONObject.length == 2) {
                alert('SELECT_FILES');
                return;
            }
        }

        $('#felts input:checked').each(function() {
            form_checked.push($(this).val());
            forms       = 1;
        });
        
        $('#aelts input:checked').each(function() {
            attach_checked.push($(this).val());
            attachment       = 1;
        });
        
        if ($('#em-ex-forms').is(":checked"))
            forms       = 1;
        if ($('#em-ex-attachment').is(":checked"))
            attachment  = 1;
        if ($('#em-ex-assessment').is(":checked"))
            assessment  = 1;
        if ($('#em-ex-decision').is(":checked"))
            decision    = 1;
        if ($('#em-ex-admission').is(":checked"))
            admission   = 1;

        $('#em-export-opt option:selected').each(function() {
            options.push($(this).val());
        });

        //$('.modal-footer').hide();
        //$('.modal-body').append('<div>' +'<img src="'+loadingLine+'" alt="'+Joomla.JText._('LOADING')+'"/>' +'</div>');
        $('#data').hide();
        $('div').remove('#chargement');
        $('.modal-body').append('<div id="chargement" style="padding:15px">' +
        '<h5>'+Joomla.JText._('COM_EMUNDUS_ZIP_GENERATION')+'</h5>'+
        '<div id="loadingimg"><img src="'+loadingLine+'" alt="loading"/></div>' +
        '<div id="extractstep"><p>'+Joomla.JText._('COM_EMUNDUS_CREATE_ZIP')+'</p></div>'+
        '</div>');

        $('#can-val').hide();

        url = 'index.php?option=com_emundus&controller=files&task=zip&Itemid='+itemId;
        $.ajax({
            type:'get',
            url:url,
            data:{
                fnums: myJSONObject,
                forms: forms,
                attachment: attachment,
                assessment: assessment,
                decision: decision,
                admission: admission,
                formids: form_checked,
                attachids:attach_checked,
                options:options
            },
            dataType:'json',
            success: function(result)
            {
                if(result.status && result.name!=0)
                {
                    $('#extractstep').replaceWith('<div id="extractstep"><p>'+Joomla.JText._('COM_EMUNDUS_ZIP_GENERATION')+'</p></div>');
                    $('#loadingimg').empty();
                    $('#extractstep').replaceWith('<div class="alert alert-success" role="alert">'+Joomla.JText._('COM_EMUNDUS_EXPORT_FINISHED')+'</div>' );
                    $('#chargement').append('<button type="button" class="btn btn-default" id="back" onclick="back();"><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;&nbsp;'+Joomla.JText._('BACK')+'</button>&nbsp;&nbsp;&nbsp;');
                    $('#chargement').append('<a class="btn .btn-link" title="'+Joomla.JText._('COM_EMUNDUS_DOWNLOAD_ZIP')+'" href="index.php?option=com_emundus&controller='+$('#view').val()+'&task=download&format=zip&name='+result.name+'"><span class="glyphicon glyphicon-download-alt"></span>  <span>'+Joomla.JText._('COM_EMUNDUS_DOWNLOAD_ZIP')+'</span></a>');
                } else {
                    $('#extractstep').replaceWith('<div id="extractstep"><p>'+Joomla.JText._('COM_EMUNDUS_ZIP_GENERATION')+'</p></div>');
                    $('#loadingimg').empty();
                    $('#chargement').append('<button type="button" class="btn btn-default" id="back" onclick="back();"><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;&nbsp;'+Joomla.JText._('BACK')+'</button>&nbsp;&nbsp;&nbsp;');
                    $('#chargement').append('<div class="alert alert-warning"><!-- Joomla.JText._(\'NO_ATTACHMENT_ZIP\')+-->Erreur, pas de document dans ce dossier </div>');
                }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                console.log(jqXHR.responseText);
            }
        });
    });

    // PDF file generation
    // this function is called on click of #em-generate and not in the above switch case for the likely reason that the same button is used elsewhere
    $(document).on('click', '#em_generate', function(e) {
        e.preventDefault();
        $.ajaxQ.abortAll();

        //var checkInput = getUserCheck();
        var fnum = '';
        var fnums = '';
        var url = $(this).attr('href');

        // Get fnum from URL parameter
        var fnum = getUrlParameter(url, 'fnum');

        // If there is one we add it to a JSON, if there is none then they are defined by the em_checked id
        if (fnum != '' && typeof(fnum) != "undefined")
            fnums = '{"1":"'+fnum+'"}';
        else
            fnums = getUserCheck();

        var ids = getUrlParameter(url, 'ids');


        var start = 0;
        var limit = 2;
        var forms = 0;
        var attachment  = 0;
        var assessment  = 0;
        var decision    = 0;
        var admission   = 0;

        var form_checked = [];
        var attach_checked = [];
        var options = [];

        $('#felts input:checked').each(function() {
            form_checked.push($(this).val());
            forms       = 1;
        });
        
        $('#aelts input:checked').each(function() {
            attach_checked.push($(this).val());
            attachment       = 1;
        });
        
        if ($('#em-ex-forms').is(":checked"))
            forms       = 1;
        if ($('#em-ex-attachment').is(":checked"))
            attachment  = 1;
        if ($('#em-ex-assessment').is(":checked"))
            assessment  = 1;
        if ($('#em-ex-decision').is(":checked"))
            decision    = 1;
        if ($('#em-ex-admission').is(":checked"))
            admission   = 1;

        $('#em-export-opt option:selected').each(function() {
            options.push($(this).val());
        });
        //console.log(options);
        $('#data').hide();
        $('div').remove('#chargement');
        $('.modal-body').append('<div id="chargement" style="padding:15px">' +
        '<h5>'+Joomla.JText._('COM_EMUNDUS_PDF_GENERATION')+'</h5>'+
        '<div id="loadingimg"><img src="'+loadingLine+'" alt="loading"/></div>' +
        '<div id="extractstep"><p>'+Joomla.JText._('COM_EMUNDUS_CREATE_PDF')+'</p></div>'+
        '</div>');
        $('#can-val').hide();
       

        //console.log(ids);
        //console.log(fnums);

            $.ajax(
            {
                type: 'post',
                url: 'index.php?option=com_emundus&controller=files&task=getfnums',
                dataType: 'JSON',
                data: {fnums: fnums, ids: ids, action_id:8, crud:'c'},

                success: function (result) {
                    var totalfile = result.totalfile;
                    ids = result.ids;

                    if (result.status) {
                        $.ajax(
                            {
                                type: 'post',
                                url: 'index.php?option=com_emundus&controller=files&task=create_file_pdf&format=raw',
                                dataType: 'JSON',
                                success: function (result) {
                                    if (result.status) {

                                        $('#extractstep').replaceWith('<div id="extractstep"><div id="addatatext"><p>' +
                                        Joomla.JText._('COM_EMUNDUS_ADD_FILES_TO_PDF') +
                                        '</p></div><div id="datasbs"</div>');

                                        var json = jQuery.parseJSON('{"start":"' + start + '","limit":"' + limit +
                                        '","totalfile":"' + totalfile + '","forms":"' + forms + '","formids":"' + form_checked +
                                        '","attachment":"' + attachment + '", "attachids":"' + attach_checked + '", "options":"' + options + '", "assessment":"' + assessment +
                                        '","decision":"' + decision + '","admission":"' + admission + '","file":"' + result.file + '","ids":"' + ids + '"}');

                                        $('#datasbs').replaceWith('<div id="datasbs" data-start="0"><p>...</p></div>');

                                        //console.log(json);
                                        //console.log(json.ids);
                                        generate_pdf(json);

                                    } else {

                                        $('#loadingimg').empty();
                                        $('#extractstep').replaceWith('<div class="alert alert-danger" role="alert">' +
                                        result.msg + '</div>');

                                    }
                                },
                                error: function (jqXHR, textStatus, errorThrown) {
                                    $('#loadingimg').empty();
                                    $('#extractstep').replaceWith('<div class="alert alert-danger" role="alert">' +
                                    jqXHR.responseText + '</div>');
                                }
                            });
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#loadingimg').empty();
                    $('#extractstep').replaceWith('<div class="alert alert-danger" role="alert">' + jqXHR.responseText +
                    '</div>');
                }

            });
        /*
        var link = $(this).attr('href');
        $('#attachement_res').empty();
        $('#attachement_res').append('<img src="'+loadingLine+'" alt="'+Joomla.JText._('LOADING')+'"/>');
        $.ajax(
            {
                type:'post',
                url:link,
                dataType:'json',
                data:{forms:$('input[name="forms"]:checked').val(), attachment:$('input[name="attachment"]:checked').val(), assessment:$('input[name="assessment"]:checked').val()},
                success: function(result)
                {
                    if(result.status)
                    {
                        $('#attachement_res').empty();
                        $('#attachement_res').append('<a class="btn btn-success btn-attach"  href="'+result.link+'" target="_blank">'+Joomla.JText._('DOWNLOAD_PDF')+'</a>');
                    }
                    else
                    {
                        $('#attachement_res').empty();
                        $('#attachement_res').append('<div class="alert alert-danger">'+result.msg+'</div>');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    console.log(jqXHR.responseText);
                    if (jqXHR.status === 302)
                    {
                        window.location.replace('/user');
                    }
                }
            });*/
    //}
    });

    /*** onclick save export excel filter button */

    $(document).on('click', '#savefilter', function(e) {
        var code = $('#em-export-prg').val();
        var year = $('#em-export-camp').val();
        var label = $("#em-export-prg option:selected").text();
        var exp_methode = $('#em-export-methode:checked').val();
        var params = '{"programme":"'+label+'","code":"'+code+'","year":"'+year+'","elements":'
        var eltJson = "{";
        var i = 0;

        //var defaultElements = '{"1904":"Application created on","2535":"Programme","3913":"Envoye le"}';

        $(".em-export-item").each(function() {
            eltJson += '"'+i+'":"'+$(this).attr('id').split('-')[0]+'",';
            i++;
        });

        eltJson = eltJson.substr(0, eltJson.length - 1);
        eltJson += '}';

        var objJson = '{';
        i = 0;
        $('.em-ex-check:checked').each(function() {
            objJson += '"'+i +'":"'+$(this).attr('value')+'",';
            i++;
        });
        objJson = objJson.substr(0, objJson.length - 1);
        objJson += '}';

        var options = '{';
        i = 0;
        $('.em-ex-check0:checked').each(function() {
            options += '"'+i +'":"'+$(this).attr('value')+'",';
            i++;
        });
        options = options.substr(0, options.length - 1);
        options += '}';

        params += eltJson;
        if(objJson == '}')
            params += ',"objects":""';
        else
            params += ',"objects":'+objJson;

        if(options == '}')
            params += ',"options":""';
        else
            params += ',"options":'+options;

        params += ',"methode":"'+exp_methode+'"';
       // params += ',"defaultselem":'+defaultElements+'"';
        params += '}';

        var filName = prompt(filterName);
        if (filName != null) {
            $.ajax({
                type: 'post',
                url: 'index.php?option=com_emundus&controller=files&task=saveExcelFilter&Itemid=' + itemId,
                dataType: 'JSON',
                data: ({
                    params: params,
                    filt_name: filName
                }),
                success: function(result) {
                    if (result.status) {
                        $('#filt_save').append('<option value="' + result.filter.id + '" selected="">' + result.filter.name + '</option>');
                        $('#filt_save').trigger("chosen:updated");
                        $('#sav-filter').show();

                        setTimeout(function(e) {
                            $('#sav-filter').hide();
                        }, 600);

                    } else {
                        $('#err-filter').show();
                        setTimeout(function(e) {
                            $('#err-filter').hide();
                        }, 600);
                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR.responseText);
                }
            })
        } else {
            alert(filterEmpty);
            filName = prompt(filterName, "name");
        }
    });

    $(document).on('click', '#delfilter', function(e) {
        if (confirm('Are you sure to delete the filter ?')) {
            var id = $('#filt_save').val();
            if (id != 0) {
                $.ajax({
                    type: 'POST',
                    url: 'index.php?option=com_emundus&controller=files&task=deletefilters&Itemid=' + itemId,
                    dataType: 'json',
                    data: ({
                        id: id
                    }),
                    success: function(result) {
                        if (result.status) {
                            $('#filt_save option:selected').remove();
                            $("#filt_save").trigger("chosen:updated");
                            $('#del-filter').show();
                            setTimeout(function(e) {
                                $('#del-filter').hide();
                            }, 600);
                        } else {
                            $('#err-filter').show();
                            setTimeout(function(e) {
                                $('#err-filter').hide();
                            }, 600);
                        }

                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR.responseText);
                    }
                })
            } else {
                alert(nodelete);
            }
        }

    });


    /********************************* */
    // Modals for actions such as exporting documents to pdf
    $(document).on('click', '#em-modal-actions .btn.btn-success', function(e) {
        $.ajaxQ.abortAll();

        // act-id represents the action to carry out (ex: export)
        var id = parseInt($('.modal-body').attr('act-id'));

        // See which files have been selected for action
        var checkInput = getUserCheck();
        
        switch (id){
            //export to excel
            case 6:
                var eltJson = "{";
                var i = 0;
                var objclass = [];

                $('[class^="emundusitem"]:checkbox:checked').each(function() {
                    if($(this).attr('class') == "emundusitem_evaluation otherForm"){
                        objclass.push($(this).attr('class'));
                    }
                });
                 objclass = $.unique(objclass);

                $(".em-export-item").each(function() {
                    eltJson += '"'+i+'":"'+$(this).attr('id').split('-')[0]+'",';
                    i++;
                });

                eltJson = eltJson.substr(0, eltJson.length - 1);
                eltJson += '}';
                var objJson = '{';

                i = 0;
                $('.em-ex-check:checked').each(function() {
                    objJson += '"'+i +'":"'+$(this).attr('value')+'",';
                    i++;
                });

                objJson = objJson.substr(0, objJson.length - 1);
                objJson += '}';


                var methode = $('#em-export-methode:checked').val();

                var options = "{";
                i = 0;
                $('.em-ex-check0:checked').each(function() {
                    options += '"'+i +'":"'+$(this).attr('value')+'",';
                    i++;
                });
                options = options.substr(0, options.length - 1);
                options += '}';



                if ($('#view').val() == "evaluation")
                    methode = 0;

                $('#data').hide();
                $('div').remove('#chargement');
                $('.modal-body').append('<div id="chargement" style="padding:15px">' +
                '<h5>'+Joomla.JText._('COM_EMUNDUS_EXCEL_GENERATION')+'</h5>'+
                '<div id="loadingimg"><img src="'+loadingLine+'" alt="loading"/></div>' +
                '<div id="extractstep"><p>'+Joomla.JText._('COM_EMUNDUS_CREATE_CSV')+'</p></div>'+
                '</div>');
                
                $('#can-val').hide();
               
                $.ajax(
                    {
                        type: 'post',
                        url: 'index.php?option=com_emundus&controller=files&task=getfnums_csv',
                        dataType: 'JSON',
                        data: {fnums: checkInput},
                        success: function (result) {
                            var totalfile = result.totalfile;
                            if (result.status) {
                                $.ajax(
                                    {
                                        type: 'post',
                                        url: 'index.php?option=com_emundus&controller=files&task=create_file_csv',
                                        dataType: 'JSON',
                                        success: function (result) {
                                            if (result.status) {
                                                $('#extractstep').replaceWith('<div id="extractstep"><div id="addatatext"><p>'+Joomla.JText._('COM_EMUNDUS_ADD_DATA_TO_CSV')+'</p></div><div id="datasbs"</div>' );
                                                var start = 0;
                                                var limit = 100;
                                                var file = result.file;
                                                var json= jQuery.parseJSON('{"start":"'+start+'","limit":"'+limit+'","totalfile":"'+totalfile+'","nbcol":"0","methode":"'+methode+'","file":"'+file+'"}');

                                                if ((methode == 0) && ($('#view').val()!="evaluation"))
                                                    $('#datasbs').replaceWith('<div id="datasbs" data-start="0"><p>0 / ' + totalfile + '</p></div>');
                                                else
                                                    $('#datasbs').replaceWith('<div id="datasbs" data-start="0"><p>0</p></div>');
                                                generate_csv(json, eltJson, objJson, options, objclass);
                                            }
                                        },
                                        error: function (jqXHR, textStatus, errorThrown) {
                                            $('#loadingimg').empty();
                                            $('#extractstep').replaceWith('<div class="alert alert-danger" role="alert">' + jqXHR.responseText + '</div>' );
                                        }
                                    });
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            $('#loadingimg').empty();
                            $('#extractstep').replaceWith('<div class="alert alert-danger" role="alert">' + jqXHR.responseText + '</div>' );
                        }
                    });
                break;

            // Add a comment
            case 10:
                var comment = $('#comment-body').val();
                var title = $('#comment-title').val();
                if (comment.length == 0) {
                    $('#comment-body').attr('style', 'height:250px !important; border-color: red !important; background-color:pink !important;');
                    return;
                }
                $('.modal-body').empty();
                $('.modal-body').append('<div>' +'<p>'+Joomla.JText._('COMMENT_SENT')+'</p>' +'<img src="'+loadingLine+'" alt="loading"/>' +'</div>');
                url = 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=addcomment';
                $.ajax({
                    type:'POST',
                    url:url,
                    dataType:'json',
                    data:({id:id, fnums:checkInput, title: title, comment:comment}),
                    success: function(result) {
                        $('.modal-body').empty();

                        if (result.status)
                            $('.modal-body').append('<p class="text-success"><strong>'+result.msg+'</strong></p>');
                        else
                            $('.modal-body').append('<p class="text-danger"><strong>'+result.msg+'</strong></p>');

                        setTimeout(function(){$('#em-modal-actions').modal('hide');}, 800);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR.responseText);
                    }
                });
                break;

            // Adding rights to the selected files
            case 11:
                var groupeEval = $('#em-access-groups-eval').val();
                var evaluators = $('#em-access-evals').val();

                if ((groupeEval == undefined ||  groupeEval.length == 0 ) && (evaluators == undefined || evaluators.length == 0)) {
                    $('.modal-body').prepend('<div class="alert alert-dismissable alert-danger">' +
                    '<button type="button" class="close" data-dismiss="alert">×</button>' +
                    '<p>'+Joomla.JText._('ERROR_REQUIRED')+'</p>' +
                    '</div>');

                    return;
                }

                if ((groupeEval != undefined &&  groupeEval.length > 0 ))
                    groupeEval = JSON.stringify(groupeEval);

                if (evaluators != undefined && evaluators.length > 0)
                    evaluators = JSON.stringify(evaluators);

                var actionsCheck = [];
                var tableSize = parseInt($('.em-actions-table-line').parent('tbody').attr('size'));

                $('.em-actions-table-line').each(function() {
                    var actLine = new Object();
                    $(this).children('td').each(function() {
                        if ($(this).hasClass('em-has-checkbox')) {
                            var id = $(this).attr('id').split('-');

                            switch(id[0]) {
                                case 'c':
                                    id = id.join('-');
                                    if ($(this).children('input[name="'+id+'"]').is(':checked'))
                                        actLine.c = 1;
                                    else
                                        actLine.c = 0;
                                    break;

                                case 'r':
                                    id = id.join('-');
                                    if ($(this).children('input[name="'+id+'"]').is(':checked'))
                                        actLine.r = 1;
                                    else
                                        actLine.r = 0;
                                    break;

                                case 'u':
                                    id = id.join('-');
                                    if ($(this).children('input[name="'+id+'"]').is(':checked'))
                                        actLine.u = 1;
                                    else
                                        actLine.u = 0;
                                    break;

                                case 'd':
                                    id = id.join('-');
                                    if ($(this).children('input[name="'+id+'"]').is(':checked'))
                                        actLine.d = 1;
                                    else
                                        actLine.d = 0;
                                    break;
                            }
                        } else if ($(this).hasClass('em-no')) {
                            if ($(this).hasClass('no-action-c'))
                                actLine.c = 0
                            else if ($(this).hasClass('no-action-r'))
                                actLine.r = 0;
                            else if ($(this).hasClass('no-action-u'))
                                actLine.u = 0;
                            else
                                actLine.d = 0;
                        } else {
                            actLine.id = $(this).attr('id');
                        }
                    })
                    actionsCheck.push(actLine);
                    if (actionsCheck.length == tableSize)
                        return false;
                });

                actionsCheck = JSON.stringify(actionsCheck);
                $('.modal-body').empty();
                $('.modal-body').append('<div>' +
                '<p style="color:#000000;">'+Joomla.JText._('SHARE_PROGRESS')+'</p>' +
                '<img src="'+loadingLine+'" alt="loading"/>' +
                '</div>');
                url = 'index.php?option=com_emundus&controller=files&task=share';
                $.ajax(
                    {
                        type:'POST',
                        url:url,
                        dataType:'json',
                        data:({fnums: checkInput, actions:actionsCheck, groups:groupeEval, evals:evaluators}),
                        success: function(result) {
                            if (result.status) {
                                $('.modal-body').empty();
                                $('.modal-body').append('<div class="alert alert-dismissable alert-success">' +
                                '<button type="button" class="close" data-dismiss="alert">×</button>' +
                                '<strong>'+result.msg+'</strong> ' +
                                '</div>');
                            } else {
                                $('.modal-body').empty();
                                $('.modal-body').append('<div class="alert alert-dismissable alert-danger">' +
                                '<button type="button" class="close" data-dismiss="alert">×</button>' +
                                '<strong>'+result.msg+'</strong> ' +
                                '</div>');
                            }
                            setTimeout(function(){$('#em-modal-actions').modal('hide');}, 800);

                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR.responseText);
                        }
                    });
                break;

            // Validating status changes for files
            case 13:
                var state = $("#em-action-state").val();
                $('.modal-body').empty();
                $('.modal-body').append('<div>' +
                '<img src="'+loadingLine+'" alt="loading"/>' +
                '</div>');
                url = 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=updatestate';
                $.ajax(
                    {
                        type:'POST',
                        url:url,
                        dataType:'json',
                        data:({fnums:checkInput, state: state}),
                        success: function(result) {
                            $('.modal-footer').hide();
                            if (result.status) {
                                $('.modal-body').empty();
                                $('.modal-body').append('<div class="alert alert-dismissable alert-success">' +
                                '<button type="button" class="close" data-dismiss="alert">×</button>' +
                                '<strong>'+result.msg+'</strong> ' +
                                '</div>');
                            } else {
                                $('.modal-body').empty();
                                $('.modal-body').append('<div class="alert alert-dismissable alert-danger">' +
                                '<button type="button" class="close" data-dismiss="alert">×</button>' +
                                '<strong>'+result.msg+'</strong> ' +
                                '</div>');
                            }
                            setTimeout(function(){$('#em-modal-actions').modal('hide');}, 380000);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR.responseText);
                        }
                    });
                break;
            // Validating tags
            case 14:
                var tag = $("#em-action-tag").val();
                $('.modal-body').empty();
                $('.modal-body').append('<div>' +
                '<img src="'+loadingLine+'" alt="loading"/>' +
                '</div>');
                url = 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=tagfile';
                $.ajax(
                    {
                        type:'POST',
                        url:url,
                        dataType:'json',
                        data:({fnums:checkInput, tag: tag}),
                        success: function(result) {
                            if (result.status) {
                                $('.modal-body').empty();
                                $('.modal-body').append('<div class="alert alert-dismissable alert-success">' +
                                '<button type="button" class="close" data-dismiss="alert">×</button>' +
                                '<strong>'+result.msg+'</strong> ' +
                                '</div>');
                                for (var i in result.tagged) {
                                    $('#'+result.tagged[i].fnum).parents('td').addClass(result.tagged[i].class);
                                    $('#'+result.tagged[i].fnum+'_check').parents('td').addClass(result.tagged[i].class);
                                }
                            } else {
                                $('.modal-body').empty();
                                $('.modal-body').append('<div class="alert alert-dismissable alert-danger">' +
                                '<button type="button" class="close" data-dismiss="alert">×</button>' +
                                '<strong>'+result.msg+'</strong> ' +
                                '</div>');
                            }
                            setTimeout(function(){$('#em-modal-actions').modal('hide');}, 800);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR.responseText);
                        }
                    });
                break;

            case 27:
                if ($(this).hasClass('em-doc-dl'))
                    return;

                var fnums = $('input:hidden[name="em-doc-fnums"]').val();
                var code = $('#em-doc-trainings').val();
                var idTmpl = $('#em-doc-tmpl').val();
                $('.modal-body').empty();
                $('.modal-body').append('<div>' +
                '<img src="'+loadingLine+'" alt="loading"/>' +
                '</div>');
                $.ajax(
                    {
                        type:'post',
                        url:'/index.php?option=com_emundus&controller=files&task=generatedoc',
                        dataType:'json',
                        data:{fnums: fnums, code:code, id_tmpl: idTmpl},
                        success: function(result) {
                            $('.modal-body').empty();
                            if (result.status) {
                                var zipUrl = 'index.php?option=com_emundus&controller=files&task=exportzipdoc&ids='
                                var oneUrl = 'index.php?option=com_emundus&controller=files&task=exportonedoc&ids='
                                var table = "<h3>" +
                                    Joomla.JText._('FILES_GENERATED')+
                                    "</h3>" +
                                    "<table class='table table-striped' id='em-generated-docs'>" +
                                    "<thead>" +
                                    "<tr>" +
                                    "<th>"+Joomla.JText._('FILE_NAME')+" <a class='btn btn-small pull-right' id='em-doc-zip' href=''>"+Joomla.JText._('COM_EMUNDUS_ACCESS_EXPORT_ZIP')+"</a> <a href='' class='btn btn-small pull-right' target='_blank'  id='em-doc-one'>"+Joomla.JText._('ALL_IN_ONE_DOC')+"</a></th>" +
                                    "</tr>" +
                                    "</thead>" +
                                    "<tbody>";
                                for (var i = 0; i < result.files.length; i++ ) {
                                    table += "<tr id='"+result.files[i].upload+"'>" +
                                    "<td>"+result.files[i].filename+" <a class='btn btn-success btn-xs pull-right em-doc-dl'  href='"+result.files[i].url+result.files[i].filename+"'><span class='glyphicon glyphicon-save'></span></a></td>" +
                                    "</tr>";
                                    if (i == 0) {
                                        zipUrl += result.files[i].upload;
                                        oneUrl += result.files[i].upload;
                                    } else {
                                        zipUrl += ','+result.files[i].upload;
                                        oneUrl += ','+result.files[i].upload;
                                    }
                                }
                                table += "</tbody></table>";
                                $('.modal-body').append(table);
                                $('#em-doc-zip').attr('href', zipUrl);
                                $('#em-doc-one').attr('href', oneUrl);
                            } else {
                                $('.modal-body').append('<div class="alert alert-danger"><h4>'+result.msg+'</h4></div>');
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR.responseText);
                            if (jqXHR.status === 302)
                                window.location.replace('/user');
                        }
                    })

                break;

            // Validating publication change
            case 28:
                var publish = $("#em-action-publish").val();
                $('.modal-body').empty();
                $('.modal-body').append('<div>' +
                '<img src="'+loadingLine+'" alt="loading"/>' +
                '</div>');
                url = 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=updatepublish';
                $.ajax(
                    {
                        type:'POST',
                        url:url,
                        dataType:'json',
                        data:({fnums:checkInput, publish: publish}),
                        success: function(result) {
                            if (result.status) {
                                $('.modal-body').empty();
                                $('.modal-body').append('<div class="alert alert-dismissable alert-success">' +
                                '<button type="button" class="close" data-dismiss="alert">×</button>' +
                                '<strong>'+result.msg+'</strong> ' +
                                '</div>');
                                reloadData($('#view').val());
                            } else {
                                $('.modal-body').empty();
                                $('.modal-body').append('<div class="alert alert-dismissable alert-danger">' +
                                '<button type="button" class="close" data-dismiss="alert">×</button>' +
                                '<strong>'+result.msg+'</strong> ' +
                                '</div>');
                            }
                            setTimeout(function(){$('#em-modal-actions').modal('hide');}, 800);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR.responseText);
                        }
                    });
                break;
        }
    });
//
//action fin
//
    $(document).on('change', '#em-modal-actions #em-export-form', function(e)
    {
        if (e.handle !== true)
        {
            e.handle = true;
            var id = $(this).val();
            var text = $('#em-modal-actions #em-export-form option:selected').attr('data-value');
            $('#emundus_elm_'+id).prop("checked", true);
            $('#em-export').append('<li class="em-export-item" id="'+id+'-item"><button class="btn btn-danger btn-xs" id="' + id + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button><span class="em-excel_elts"><strong>'+text+'</strong></span></li>');
        }
    });

    $(document).on('change', '#em-admission-export-form', function(e)
    {
        if (e.handle !== true)
        {
            e.handle = true;
            var id = $(this).val();
            var text = $('#em-admission-export-form option:selected').attr('data-value');
            $('#emundus_elm_'+id).prop("checked", true);
            $('#em-export').append('<li class="em-export-item" id="'+id+'-item"><button class="btn btn-danger btn-xs" id="' + id + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button><span class="em-excel_elts"><strong>'+text+'</strong></span></li>');
        }
    });

    $(document).on('change', '#em-decision-export-form', function(e)
    {
        if (e.handle !== true)
        {
            e.handle = true;
            var id = $(this).val();
            var text = $('#em-decision-export-form option:selected').attr('data-value');
            $('#emundus_elm_'+id).prop("checked", true);
            $('#em-export').append('<li class="em-export-item" id="'+id+'-item"><button class="btn btn-danger btn-xs" id="' + id + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button><span class="em-excel_elts"><strong>'+text+'</strong></span></li>');
        }
    });

    $(document).on('click', '.emundusraw', function(e)
    {
        $.ajaxQ.abortAll();
        if (e.handle !== true)
        {
            e.handle = true;
            var id = $(this).val();
            if ($(this).is(':checked')) {
                var text = $("label[for='" + $(this).attr('id') + "']").text();
                $('#em-export').append('<li class="em-export-item" id="' + id + '-item"><button class="btn btn-danger btn-xs" id="' + id + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button><span class="em-excel_elts"><strong>' + text + '</strong></span></li>');
            } else {
                $('#'+id+'-item').remove();
            }

        }
    });

    $(document).on('click', '#em-export .em-export-item .btn.btn-danger', function(e)
    {
        $.ajaxQ.abortAll();
        var id = $(this).attr('id').split('-');
        id.pop();
        $('#emundus_elm_'+id).removeAttr("checked");
        $(this).parent('li').remove();
    });

    $(document).on('change', '.em-modal-check', function() {

        if($(this).hasClass('em-check-all')) {

            var id = $(this).attr('name').split('-');
            id.pop();
            id = id.join('-');

            if ($(this).is(':checked')) {
                $(this).prop('checked', true);
                $('.'+id).prop('checked', true);
            } else {
                $(this).prop('checked', false);
                $('.'+id).prop('checked', false);
            }

        }
    });
    $(document).on('click', '.em-list-evaluator-item .btn-danger', function() {
        $.ajaxQ.abortAll();
        var gr = false;
        if ($(this).hasClass('group'))
            gr = true;

        var id = $(this).attr('id').split('-');
        $.ajax(
            {
                type:'post',
                url:'index.php?option=com_emundus&controller='+$('#view').val()+'&task=unlinkevaluators',
                dataType:'json',
                data:({fnum:id[0], id:id[1], group: gr}),
                success: function(result)
                {
                    if (result.status)
                        $("#"+id.join('-')).parent('li').remove()
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    console.log(jqXHR.responseText);
                }
            })
    });
    $(document).on('click', '#em-hide-filters', function()
    {
        $.ajaxQ.abortAll();
        if ($('.side-panel').is(':visible')) {

            $('.side-panel').hide();
            $(this).children('span').addClass('glyphicon-chevron-right');
            $(this).children('span').removeClass('glyphicon-chevron-left');
            $('.main-panel').addClass('col-md-12');
            $('.main-panel').removeClass('col-md-9');

        } else {

            $('.side-panel').show();
            $(this).children('span').removeClass('glyphicon-chevron-right');
            $(this).children('span').addClass('glyphicon-chevron-left');
            $('.main-panel').addClass('col-md-9');
            $('.main-panel').removeClass('col-md-12');

        }
    });
    $(document).on('click', '#showelements', function() {
        $.ajaxQ.abortAll();
        if ($(this).hasClass("btn btn-info")) {
            $('#eval-elements-popup').hide();
            $('#decision-elements-popup').hide();
            $('#admission-elements-popup').hide();

            $('#elements-popup').toggle(400);
            $(this).removeClass("btn btn-info").addClass("btn btn-elements-success");
            $(this).empty();
            $(this).append('<span class="glyphicon glyphicon-minus"></span>');

        } else {
            $('#elements-popup').hide();
            $(this).removeClass("btn btn-elements-success").addClass("btn btn-info");
            $(this).empty();
            $(this).append('<span class="glyphicon glyphicon-plus"></span>');

        }
    });
    $(document).on('click', '#showevalelements', function() {
        $.ajaxQ.abortAll();
        if ($(this).hasClass("btn btn-info")) {
            $('#elements-popup').hide();
            $('#decision-elements-popup').hide();
            $('#admission-elements-popup').hide();

            $('#eval-elements-popup').toggle(400);
            $(this).removeClass("btn btn-info").addClass("btn btn-elements-success");
            $(this).empty();
            $(this).append('<span class="glyphicon glyphicon-minus"></span>');

        } else {
            $('#eval-elements-popup').hide();
            $(this).removeClass("btn btn-elements-success").addClass("btn btn-info");
            $(this).empty();
            $(this).append('<span class="glyphicon glyphicon-plus"></span>');

        }
    });
    $(document).on('click', '#showdecisionelements', function() {
        $.ajaxQ.abortAll();
        if ($(this).hasClass("btn btn-info")) {
            $('#elements-popup').hide();
            $('#eval-elements-popup').hide();
            $('#admission-elements-popup').hide();

            $('#decision-elements-popup').toggle(400);
            $(this).removeClass("btn btn-info").addClass("btn btn-elements-success");
            $(this).empty();
            $(this).append('<span class="glyphicon glyphicon-minus"></span>');

        } else {
            $('#decision-elements-popup').hide();
            $(this).removeClass("btn btn-elements-success").addClass("btn btn-info");
            $(this).empty();
            $(this).append('<span class="glyphicon glyphicon-plus"></span>');

        }
    });
    $(document).on('click', '#showadmissionelements', function() {
        $.ajaxQ.abortAll();
        if ($(this).hasClass("btn btn-info")) {
            $('#elements-popup').hide();
            $('#eval-elements-popup').hide();
            $('#decision-elements-popup').hide();

            $('#admission-elements-popup').toggle(400);
            $(this).removeClass("btn btn-info").addClass("btn btn-elements-success");
            $(this).empty();
            $(this).append('<span class="glyphicon glyphicon-minus"></span>');

        } else {
            $('#admission-elements-popup').hide();
            $(this).removeClass("btn btn-elements-success").addClass("btn btn-info");
            $(this).empty();
            $(this).append('<span class="glyphicon glyphicon-plus"></span>');

        }

    });



})
