/**
 * @package    Joomla
 * @subpackage emundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     EMUNDUS SAS
 */

if ('undefined' === typeof jQuery) throw new Error('eMundus\'s JavaScript requires jQuery');

var loading;
var moduleFilters = null;
var refreshModuleFiltersEvent = new Event('refresh-emundus-module-filters');

if (typeof $ === 'undefined') {
    var $ = jQuery.noConflict();
}

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

function search() {
    const controller = $('#view').val();

    if (controller !== null && typeof controller !== 'undefined') {
        addLoader();
        var quick = [];

        $('#quick div[data-value]').each(function () {
            quick.push($(this).attr('data-value')) ;
        });

        var inputs = [{
            name: 's',
            value: quick,
            adv_fil: false
        }];

        $('[id^=em-adv-fil-]').each(function(){
            inputs.push({
                name: $(this).attr('name'),
                value: $(this).val(),
                adv_fil: true,
                select: this.nodeName.toLowerCase() === 'select'
            });
        });

        $('.em_filters_filedset .testSelAll').each(function () {
            inputs.push({
                name: $(this).attr('name'),
                value: $(this).val(),
                adv_fil: false
            });
        });

        $('.em_filters_filedset .search_test').each(function () {
            inputs.push({
                name: $(this).attr('name'),
                value: $(this).val(),
                adv_fil: false
            });
        });

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?option=com_emundus&controller='+controller+'&task=setfilters',
            data: ({
                val: JSON.stringify(($.extend({}, inputs))),
                multi: false,
                elements: true
            }),
            success: function(result) {
                if (result.status) {
                    refreshFilter(controller);
                }
            },
            error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        });
    }
}

function clearchosen(target){
    $(target)[0].sumo.unSelectAll();
}

function reloadData(view) {
    view = (typeof view === 'undefined') ? 'files' : view;

    addLoader();

    $.ajax({
        type: 'GET',
        url: 'index.php?option=com_emundus&view='+view+'&layout=data&format=raw&Itemid=' + itemId + '&cfnum=' + cfnum,
        async: false,
        dataType: 'html',
        success: function(data) {
            removeLoader();

            let col9 = $('.col-md-9 .panel.panel-default');
            if(col9.length > 0) {
                col9.remove();
                if($('.col-md-9')) {
                    $('.col-md-9').append(data);
                }
            }

            let col12 = $('.col-md-12 .panel.panel-default');
            if(col12.length > 0) {
                col12.remove();
                if($('.col-md-12')) {
                    $('.col-md-12').append(data);
                }
            }
        },
        error: function(jqXHR) {
            console.log(jqXHR.responseText);
        }
    });
}

function reloadActions(view, fnum, onCheck, async, display = 'none') {

    fnum = (typeof fnum === 'undefined') ? 0 : fnum;
    async = (typeof async === 'undefined') ? false : async;

    var multi = $('.em-check:checked').length;
    if (multi === 0 && fnum != 0 ) {
        multi = 1;
    }

    $.ajax({
        type: 'GET',
        async: async,
        url: 'index.php?option=com_emundus&view=files&layout=menuactions&format=raw&Itemid=' + itemId + '&display=' + display + '&fnum=' + fnum + '&multi=' + multi,
        dataType: 'html',
        success: function(data) {
            let navbar = $('.navbar.navbar-inverse');
            navbar.empty();
            navbar.append(data);

            if (onCheck === true) {
                menuBar1();
            }
        },
        error: function(jqXHR) {
            console.log(jqXHR.responseText);
        }
    });
}

function addElement() {
    $.ajax({
        type: 'GET',
        url: 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=getadvfilters&Itemid=' + itemId,
        dataType: 'json',
        success: function(result) {
            if (result.status) {
                var ni = $('#advanced-filters');
                var num = ($('#nb-adv-filter').val() - 1) + 2;
                $('#nb-adv-filter').val(num);
                var newId = 'em-adv-father-' + num;
                ni.append('<fieldset id="' + newId + '" class="em-nopadding">' +
                    '<a id="suppr-filt" class="em-mb-4 em-flex-start">' +
                    '<span class="em-font-size-14 em-red-500-color em-pointer">' + Joomla.JText._('COM_EMUNDUS_DELETE_ADVANCED_FILTERS') + '</span>' +
                    '</a>' +
                    '<select class="chzn-select em-filt-select em-mb-4" name="elements" id="elements-'+num+'">' +
                    '<option value="">' + result.default +'</option>' +
                    '</select> ' +
                    '</fieldset>');

                var options = '';
                var menu = null;
                var groupe = null;
                var menuTmp = null;
                var groupeTmp = null;

                for (var i = 0; i < result.options.length; i++) {

                    if (Joomla.JText._(result.options[i].title) == 'undefined' || Joomla.JText._(result.options[i].title) == '') {
                        menuTmp = result.options[i].title;
                    } else {
                        menuTmp = Joomla.JText._(result.options[i].title);
                    }

                    if (Joomla.JText._(result.options[i].group_label) == 'undefined' || Joomla.JText._(result.options[i].group_label) == '') {
                        groupeTmp = result.options[i].group_label;
                    } else {
                        groupeTmp = Joomla.JText._(result.options[i].group_label);
                    }

                    if (menu != menuTmp) {
                        options += '<optgroup label="________________________________">' +
                            '<option disabled class="emundus_search_elm" value="-">' +
                            menuTmp.toUpperCase() +
                            '</option>' +
                            '</optgroup>';
                        menu = menuTmp;
                    }

                    if (groupe != null && (groupe != groupeTmp)) {
                        options += '</optgroup>';
                    }

                    if (groupe != groupeTmp) {
                        options += '<optgroup label=">> ' + groupeTmp + '">';
                        groupe = groupeTmp;
                    }

                    var eltLabel = null;
                    if (Joomla.JText._(result.options[i].element_label) == 'undefined' || Joomla.JText._(result.options[i].element_label) == '') {
                        eltLabel = result.options[i].element_label;
                    } else {
                        eltLabel = Joomla.JText._(result.options[i].element_label);
                    }

                    options += '<option class="emundus_search_elm" value="' + result.options[i].id + '">'+eltLabel+'</option>';
                }
                $('#' + newId + ' #elements-'+num).append(options);
                $('.chzn-select').chosen({width:'75%'});

            }

        },
        error: function(jqXHR) {
            console.log(jqXHR.responseText);
        }
    });

}

function usingModuleFilters()
{
    itemId = (typeof itemId === 'undefined') ? 0 : itemId;

    if (itemId > 0) {
        if (moduleFilters === null) {
            fetch('index.php?option=com_emundus&controller=files&task=checkmenufilterparams&Itemid=' + itemId, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            }).then(function(response) {
                if (response.ok) {
                    return response.json();
                } else {
                    console.log('Network response was not ok.');
                    moduleFilters = false;
                    return moduleFilters;
                }
            }).then(function(data) {
                if (data.status) {
                    moduleFilters = data.use_module_filters;
                } else {
                    moduleFilters = false;
                }

                return moduleFilters;
            }).catch(function(error) {
                console.log('There has been a problem with your fetch operation: ' + error.message);
                moduleFilters = false;
                return moduleFilters;
            });
        }
    }

    return moduleFilters;
}
usingModuleFilters();

function refreshFilter(view) {
    usingModuleFilters();

    if (moduleFilters === false || moduleFilters === null) {
        view = (typeof view === 'undefined') ? 'files' : view;
        $.ajax({
            type: 'GET',
            url: 'index.php?option=com_emundus&view='+view+'&layout=filters&format=raw&Itemid=' + itemId,
            dataType: 'html',
            success: function(data) {
                let panelBody = $('#em-files-filters .panel-body');
                panelBody.empty();
                panelBody.append(data);
                $('.chzn-select').chosen();
                reloadData($('#view').val());
            },
            error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        });
    } else {
        reloadData($('#view').val(), false);
    }
}

function tableOrder(order) {
    addLoader();
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
            } else {
                removeLoader();
            }
        },
        error: function(jqXHR) {
            console.log(jqXHR.responseText);
        }
    });
}

function doesSomeoneElseEditFile(fnum) {
    if (fnum !== undefined && fnum !== null && fnum !== '') {
        return new Promise(function(resolve, reject) {
            var xhr = new XMLHttpRequest();

            xhr.open('GET', 'index.php?option=com_emundus&controller=files&task=checkIfSomeoneElseIsEditing&format=json&fnum=' + fnum, true);

            xhr.onload = function() {
                if (this.status === 200) {
                    resolve(JSON.parse(this.response));
                } else {
                    reject(false);
                }
            };

            xhr.onerror = function() {
                reject(false);
            };

            xhr.send();
        });
    }

    return false;
}

async function checkIfSomeoneIsEditing(fnum) {
    const response = await doesSomeoneElseEditFile(fnum);

    if (response.status && response.data) {
        var text = '';

        response.data.forEach(function(user) {
            text += user.name +  ' ';
        });

        if (response.data.length > 1) {
            text += ' ' + Joomla.JText._('COM_EMUNDUS_FILES_ARE_EDITED_BY_OTHER_USERS');
        } else {
            text += ' ' + Joomla.JText._('COM_EMUNDUS_FILES_IS_EDITED_BY_OTHER_USER');
        }

        Swal.fire({
            title: Joomla.JText._('COM_EMUNDUS_FILE_EDITED_BY_ANOTHER_USER'),
            text: text,
            customClass: {
                title: 'em-swal-title',
                confirmButton: 'em-swal-confirm-button',
                actions: 'em-swal-single-action'
            },
        });
    }
}

function hideItems(selectors){
    if (selectors.length > 0) {
        selectors.forEach(function (selector) {
            const selectedEl = document.querySelector(selector);

            if (selectedEl) {
                selectedEl.classList.add('em-hide');
                selectedEl.style.display = 'none';
            }
        });
    }
}

function openFiles(fnum, page = 0, vue = false) {
    checkIfSomeoneIsEditing(fnum.fnum);

    jQuery("html, body").animate({ scrollTop: 0 }, 300);
    // Run the reload actions function without waiting for return.
    setTimeout(function () {
        if (vue === true) {
            reloadActions(undefined, fnum.fnum, false, true, 'block');
        } else {
            reloadActions(undefined, fnum.fnum, false, true);
        }
    }, 0);

    var cid = parseInt(fnum.fnum.substr(14, 7));
    var sid = parseInt(fnum.fnum.substr(21, 7));

    $('#em-assoc-files .panel-body').empty();

    $.ajax({
        type: 'get',
        url: 'index.php?option=com_emundus&view=application&fnum=' + fnum.fnum + '&Itemid=' + itemId + '&format=raw&layout=assoc_files',
        dataType: 'html',
        success: function (result) {
            if (result) {
                $('#em-assoc-files .panel-body').append(result);
                document.getElementById('em-assoc-files').show();
            } else {
                document.getElementById('em-assoc-files').hide();
            }

        },
        error: function (jqXHR) {
            console.log(jqXHR.responseText);
        }
    });

    $.ajax({
        type: 'get',
        url: 'index.php?option=com_emundus&view=application&fnum=' + fnum.fnum + '&Itemid=' + itemId + '&format=raw&layout=synthesis&action=0',
        dataType: 'html',
        success: function(result) {
            $('#em-last-open .list-group .active').removeClass('active');
            if ($('#'+fnum.fnum+'_ls_op').is(':visible')) {
                $('#'+fnum.fnum+'_ls_op' ).addClass('active');
            } else {
                if (fnum.hasOwnProperty('name')) {
                    $('#em-last-open .list-group').append('<a href="#'+fnum.fnum+'|open" class="list-group-item active" title="'+fnum.fnum+'" id="'+fnum.fnum+'_ls_op"><strong>'+fnum.name+'</strong><span> - '+fnum.label+'</span></a>');
                } else {
                    $('#em-last-open .list-group').append('<a href="#'+fnum.fnum+'|open" class="list-group-item active" id="'+fnum.fnum+'_ls_op">'+fnum.fnum+'</a>');
                }
            }

            $('.em-open-files').remove();

            var panel = result;
            //.main-panel
            $('.main-panel').append('<div class="clearfix"></div><div class="col-md-12" id="em-appli-block"></div>');
            if (result) {
                let panelBody = $('#em-synthesis .panel-body');
                panelBody.empty();
                panelBody.append(panel);
                $('#em-synthesis').show();
            } else {
                $('#em-synthesis').hide();
            }


            $.ajax({
                type:'get',
                url:'index.php?option=com_emundus&controller=application&task=getapplicationmenu&fnum='+fnum.fnum,
                dataType:'json',
                success: function(result) {

                    String.prototype.fmt = function (hash) {
                        var string = this, key;
                        for (key in hash) {
                            string = string.replace(new RegExp('\\{' + key + '\\}', 'gm'), hash[key]);
                        }
                        return string;
                    };

                    let menuListGroup = $('#em-appli-menu .list-group');
                    menuListGroup.empty();
                    if (result.status) {
                        var menus = result.menus;
                        var numMenu = 0;

                        while (numMenu <= menus.length) {
                            if (menus[numMenu] && menus[numMenu].link && menus[numMenu].link.indexOf('layout=' + page) != -1) {
                                break;
                            }
                            numMenu++;
                            if(numMenu >= menus.length){
                                numMenu = 0;
                                break;
                            }
                        }

                        var firstMenu = menus[numMenu].link;
                        var menuList = '';

                        if (menus.length > 0) {
                            for (var m in menus) {
                                if (isNaN(parseInt(m)) || isNaN(menus[m].id) || typeof(menus[m].title) == 'undefined') {
                                    break;
                                }

                                url = menus[m].link.fmt({ fnum: fnum.fnum, applicant_id: sid, campaign_id: cid });
                                url += '&fnum='+fnum.fnum;
                                url += '&Itemid='+itemId;

                                if(typeof menus[m].notifications != 'undefined'){
                                    menuList += '<a href="'+url+'" class="list-group-item list-item-notifications" title="'+menus[m].title+'" id="'+menus[m].id+'">';
                                } else {
                                    menuList += '<a href="' + url + '" class="list-group-item" title="' + menus[m].title + '" id="' + menus[m].id + '">';
                                }

                                if (menus[m].hasSons) {
                                    menuList += '<span class="glyphicon glyphicon-plus" id="'+menus[m].id+'"></span>';
                                }

                                if(typeof menus[m].notifications != 'undefined'){
                                    menuList +=  '<strong>'+menus[m].title+'</strong><span class="notifications-counter">'+menus[m].notifications+'</span></a>';
                                } else {
                                    menuList +=  '<strong>'+menus[m].title+'</strong></a>';
                                }
                            }
                            menuListGroup.append(menuList);
                            $('#em-appli-menu').show();
                        } else {
                            $('#em-appli-menu').hide();
                        }

                        if (vue === true) {
                            // stop here
                            return;
                        }

                        $.ajax({
                            type:'get',
                            url:firstMenu,
                            dataType:'html',
                            data:({fnum:fnum.fnum}),
                            success: function(result) {
                                removeLoader();
                                $('#em-files-filters').hide();
                                $('.main-panel .panel.panel-default').hide();

                                const appBlock = $('#em-appli-block');
                                appBlock.empty();
                                appBlock.append(result);
                                $('#accordion .panel.panel-default').show();
                                $('#em-last-open, .em-open-files > div[id="'+fnum.fnum+'"]').show();
                                menuBar1();

                                $('#em-close-multi-file').hide();
                                $('#em-close-multi-file button').hide();
                            },
                            error: function (jqXHR) {
                                console.log(jqXHR.responseText);
                                if (jqXHR.status === 302) {
                                    window.location.replace('/user');
                                }
                            }
                        });

                    } else {
                        menuListGroup.append(result.msg);
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR.responseText);
                }
            });
        },
        error: function(jqXHR) {
            console.log(jqXHR.responseText);
        }
    });

}

function menuBar1() {
    $('.nav.navbar-nav').show();
    $('.em-actions[multi="0"]').show();
    $('.em-actions[multi="0"]').removeClass('em-hidden');
    $('.em-actions[multi="1"]').show();
    $('.em-actions[multi="1"]').removeClass('em-hidden');

    $('.em-dropdown[nba="0"]').parent('li').show();
    $('.em-dropdown').each(function() {
        var dpId = $(this).attr('id');
        var nonHidden = $('ul[aria-labelledby="' + dpId + '"] .em-actions').length;
        $(this).attr('nba', nonHidden);
    });
}

function exist(fnum) {
    var exist = false;
    $('.main-panel.col-xs-16 .panel.panel-default.em-hide').each(function() {
        if (parseInt($(this).attr('id')) == parseInt(fnum)) {
            exist = true;
        }
    });

    return exist;
}

// Looks up checked items and adds them to a JSON object or return all if the "check all" box is ticked
function getUserCheck() {
    var checkInput = null;

    if ($('#em-check-all-all').is(':checked')) {
        checkInput = 'all';
    } else {
        var i = 0;

        let checkedEm = $('.em-check:checked');

        if(checkedEm.length == 0) {
            var hash = $(location).attr('hash');
            var fnum = hash.replace('#', '');
            fnum = fnum.replace('|open', '');
            if(fnum != '') {
                checkInput = '{"0":'+fnum+'}';
                return checkInput;
            } else {
                return null;
            }
        }

        var myJSONObject = '{';
        checkedEm.each(function(){
            i = i + 1;
            myJSONObject += '"'+i+'"'+':"'+$(this).attr('id').split('_')[0]+'",';
        });

        myJSONObject = myJSONObject.substr(0, myJSONObject.length-1);
        myJSONObject += '}';

        if (myJSONObject.length <= 2) {
            alert('SELECT_FILES');
            return null;
        } else {
            checkInput = myJSONObject;
        }
    }

    return checkInput;
}

// Looks up checked items and adds them to a array or return all if the "check all" box is ticked
function getUserCheckArray() {
    var fnums = [];

    if ($('#em-check-all-all').is(':checked')) {
        return 'all';
    } else {
        if ($('.em-check:checked').length === 0) {
            var hash = $(location).attr('hash');
            var fnum = hash.replace('#', '');
            fnum = fnum.replace('|open', '');

            if (fnum == '') {
                return null;
            } else {
                var cid = parseInt(fnum.substr(14, 7));
                var sid = parseInt(fnum.substr(21, 7));
                fnums.push({fnum: fnum, cid: cid, sid:sid});
            }
        } else {
            var cid = '';
            var sid = '';
            $('.em-check:checked').each(function() {
                fnum = $(this).attr('id').split('_')[0];
                cid = parseInt(fnum.substr(14, 7));
                sid = parseInt(fnum.substr(21, 7));
                fnums.push({fnum: fnum, cid: cid, sid:sid});
            });
        }
    }

    return JSON.stringify(fnums);
}


function showelts(elt, idcodeyear) {
    var attachments_block = document.getElementById(idcodeyear);

    if (attachments_block.style.display === 'none') {
        $('#'+idcodeyear).toggle(400);
        $('#'+idcodeyear+'-icon').css('transform','rotate(0deg)');
    } else {
        $('#'+idcodeyear).toggle(400);
        $('#'+idcodeyear+'-icon').css('transform','rotate(-90deg)');
    }
}

function showoptions(opt) {
    if ($(opt).hasClass('btn btn-info')) {
        $('#options').toggle(400);
        $(opt).removeClass('btn btn-info').addClass('btn btn-elements-success');
        $(opt).empty();
        $(opt).append('<span class="glyphicon glyphicon-minus"></span>');
    } else {
        $('#options').toggle(400);
        $(opt).removeClass('btn btn-elements-success').addClass('btn btn-info');
        $(opt).empty();
        $(opt).append('<span class="glyphicon glyphicon-plus"></span>');
    }
}

function back() {
    $('div').remove('#chargement');
    $('#data').show();
    $('#can-val').show();
}

function getAllLetters() {
    return new Promise(function(resolve, reject) {
        const xhr = new XMLHttpRequest();

        xhr.open('POST', 'index.php?option=com_emundus&controller=files&task=getAllLetters');

        xhr.onload = function() {
            if (xhr.status === 200) {
                const result = JSON.parse(xhr.responseText);
                var letters = result.letters;

                resolve(letters);
            } else {
                reject(this.statusText);
            }
        };

        xhr.onerror = function() {
            reject(this.statusText);
        };
        xhr.send();
    });
}

function getProgramCampaigns(code) {
    return new Promise(function(resolve, reject) {
        const xhr = new XMLHttpRequest();

        xhr.open('GET', 'index.php?option=com_emundus&controller=files&task=getProgramCampaigns&code=' + code);

        xhr.onload = function() {
            if (xhr.status === 200) {
                const result = JSON.parse(xhr.responseText);

                resolve(result);
            } else {
                reject(this.statusText);
            }
        };

        xhr.onerror = function() {
            reject(this.statusText);
        };
        xhr.send();
    });
}

function setFiltersSumo(event){
    $.ajaxQ.abortAll();
    if (event.handle !== true) {
        event.handle = true;

        var id = event.currentTarget.id;
        const my_element = $('#' + id);
        console.log(my_element);
        if (!id.includes('elements-')) {
            var multi = false;
            if (typeof my_element.attr('multiple') !== 'undefined') {
                multi = true;
            }

            var test = id.split('-');
            test.pop();
            var elements_son = false;
            if (test.join('-') === 'em-adv-fil') {
                elements_son = true;
            }

            if (multi) {
                var value = my_element.val();
                if (value != null && value.length > 1 && value[0] === '%') {
                    if ((lastVal.hasOwnProperty(id) && lastVal[id][0] !== '%')) {
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
            if ($('#select_multiple_programmes').val() != null || $('#select_multiple_campaigns').val() != null) {
                $('#em_adv_filters').show();
            } else {
                $('#em_adv_filters').hide();
            }

            search();
        } else {
            var father = my_element.parent('fieldset').attr('id');
            console.log(my_element);
            console.log(father);
            getSearchBox(my_element.val(), father);
        }
    }
}

function runAction(action, url = '', option = '') {
    $.ajaxQ.abortAll();

    // act-id represents the action to carry out (ex: export)
    var id = action;

    // See which files have been selected for action
    var checkInput = getUserCheck();

    var state = $('#em-action-state').val();
    var tag = $('#em-action-tag').val();

    switch (id) {
        // Export Excel
        case 6:
            export_excel(checkInput, option);
            break;

        // Export ZIP
        case 7:
            export_zip(checkInput);
            break;

        // Export PDF;
        case 8:
            // Get fnum from URL parameter
            var fnum = getUrlParameter(url, 'fnum');
            var fnums = getUserCheck();

            // If there is one we add it to a JSON, if there is none then they are defined by the em_checked id
            if (fnum !== '' && typeof(fnum) != 'undefined') {
                fnums = '{"1":"' + fnum + '"}';
            }

            var ids = getUrlParameter(url, 'ids');
            export_pdf(fnums,ids);
            break;

        // Send an email
        case 9:
            var fnums = getUserCheckArray();

            sendMailQueue(fnums);
            break;

        // Add comments
        case 10:
            var comment = $('#comment-body').val();
            var title = $('#comment-title').val();

            removeLoader();

            Swal.fire({
                position: 'center',
                title: Joomla.JText._('COM_EMUNDUS_ACCESS_COMMENT_FILE_CREATE'),
                html: '<div class="em-flex-column"><div class="em-loader em-mt-8"></div></div>',
                showCancelButton: false,
                showConfirmButton: false,
                customClass: {
                    title: 'em-swal-title',
                },
            });

            url = 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=addcomment';
            $.ajax({
                type:'POST',
                url:url,
                dataType:'json',
                data:({id:id, fnums:checkInput, title: title, comment:comment}),
                success: function(result) {
                    removeLoader();

                    if (result.status) {
                        Swal.fire({
                            position: 'center',
                            type: 'success',
                            html: '<p class="em-main-500-color">' + result.msg + '</p>',
                            showCancelButton: false,
                            showConfirmButton: false,
                            customClass: {
                                title: 'em-swal-title',
                            },
                            timer: 1500
                        });
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR.responseText);
                }
            });
            break;

        // Adding rights to the selected files
        case 11:
            var groupeEval = $('#em-access-groups-eval').val();
            var evaluators = $('#em-access-evals').val();

            if ((groupeEval != undefined &&  groupeEval.length > 0 )) {
                groupeEval = JSON.stringify(groupeEval);
            }

            if (evaluators != undefined && evaluators.length > 0) {
                evaluators = JSON.stringify(evaluators);
            }

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
                            actLine.c = 0;
                        else if ($(this).hasClass('no-action-r'))
                            actLine.r = 0;
                        else if ($(this).hasClass('no-action-u'))
                            actLine.u = 0;
                        else
                            actLine.d = 0;
                    } else {
                        actLine.id = $(this).attr('id');
                    }
                });
                actionsCheck.push(actLine);
                if (actionsCheck.length == tableSize)
                    return false;
            });

            var notifyEval = document.querySelector('#evaluator-email').checked;
            actionsCheck = JSON.stringify(actionsCheck);
            removeLoader();

            Swal.fire({
                position: 'center',
                title: Joomla.JText._('COM_EMUNDUS_ACCESS_SHARE_PROGRESS'),
                html: '<div class="em-flex-column"><div class="em-loader em-mt-8"></div></div>',
                showCancelButton: false,
                showConfirmButton: false,
                customClass: {
                    title: 'em-swal-title',
                },
            });

            url = 'index.php?option=com_emundus&controller=files&task=share';

            $.ajax({
                type:'POST',
                url:url,
                dataType:'json',
                data:({
                    fnums: checkInput,
                    actions: actionsCheck,
                    groups: groupeEval,
                    evals: evaluators,
                    notify: notifyEval
                }),
                success: function(result) {

                    if (result.status) {
                        Swal.fire({
                            position: 'center',
                            type: 'success',
                            html: '<p class="em-main-500-color">' + result.msg + '</p>',
                            showCancelButton: false,
                            showConfirmButton: false,
                            customClass: {
                                title: 'em-swal-title',
                            },
                            timer: 1500
                        });
                        reloadData($('#view').val());

                    } else {
                        Swal.fire({
                            position: 'center',
                            type: 'warning',
                            title: result.msg
                        });
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR.responseText);
                }
            });
            break;

        // Validating status changes for files
        case 13:
            var sel = document.getElementById("em-action-state");
            var newState = document.getElementById("em-action-state").options[sel.selectedIndex].text;
            $("#can-val").css('display','none');

            url = 'index.php?option=com_emundus&controller=files&task=getExistEmailTrigger';
            $.ajax({
                type:'POST',
                url:url,
                dataType:'json',
                data:({
                    fnums: checkInput,
                    state: state,
                    to_applicant: "1"
                }),
                success: function(result) {
                    $('.modal-body').empty();

                    if(result.status) {
                        Swal.fire({
                            title: Joomla.JText._('WARNING_CHANGE_STATUS'),
                            text: result.msg,
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonText: Joomla.JText._('COM_EMUNDUS_APPLICATION_VALIDATE_CHANGE_STATUT'),
                            cancelButtonText: Joomla.JText._('COM_EMUNDUS_APPLICATION_CANCEL_CHANGE_STATUT'),
                            reverseButtons: true,
                            customClass: {
                                title: 'em-swal-title',
                                cancelButton: 'em-swal-cancel-button',
                                confirmButton: 'em-swal-confirm-button',
                            },
                        }).then(function(result) {
                            if (result.value) {
                                addLoader();
                                updateState(checkInput, state);
                            } else {
                                $('.modal-body').empty();
                                removeLoader();
                                $('#em-modal-actions').modal('hide');
                                $('.modal-backdrop, .modal-backdrop.fade.in').css('display','none');
                                $('body').removeClass('modal-open');
                            }
                        })
                    } else {
                        addLoader();
                        const nbFiles = countFilesBeforeAction(checkInput, 13, 'u');

                        // wait for nbFiles promise to resolve
                        nbFiles.then(function(nbFiles) {
                            if (nbFiles > 0) {
                                removeLoader();

                                Swal.fire({
                                    title: Joomla.JText._('COM_EMUNDUS_APPLICATION_WARNING_CHANGE_STATUS'),
                                    text: Joomla.JText._('COM_EMUNDUS_APPLICATION_WARNING_CHANGE_STATUS_OF_NB_FILES') + ' ' + nbFiles + ' ' + Joomla.JText._('COM_EMUNDUS_APPLICATION_WARNING_CHANGE_STATUS_OF_NB_FILES_2'),
                                    type: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: Joomla.JText._('COM_EMUNDUS_APPLICATION_VALIDATE_CHANGE_STATUT'),
                                    cancelButtonText: Joomla.JText._('COM_EMUNDUS_APPLICATION_CANCEL_CHANGE_STATUT'),
                                    reverseButtons: true,
                                    customClass: {
                                        title: 'em-swal-title',
                                        cancelButton: 'em-swal-cancel-button',
                                        confirmButton: 'em-swal-confirm-button',
                                    },
                                }).then(function(result) {
                                    if (result.value) {
                                        updateState(checkInput, state);
                                    } else {
                                        removeLoader();
                                        $('#em-modal-actions').modal('hide');
                                        $('.modal-backdrop, .modal-backdrop.fade.in').css('display','none');
                                        $('body').removeClass('modal-open');
                                    }
                                });
                            } else {
                                removeLoader();
                                Swal.fire({
                                    title: Joomla.JText._('COM_EMUNDUS_ONBOARD_ERROR_MESSAGE'),
                                    text: '',
                                    type: 'error',
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    reverseButtons: true,
                                    customClass: {
                                        title: 'em-swal-title'
                                    },
                                });
                            }
                        });
                    }

                },
                error: function (jqXHR) {
                    document.getElementsByClassName('modal-body')[0].innerHTML =jqXHR.responseText;
                }
            });

            break;

        // Validating tags
        case 14:
            addLoader();
            var url="";
            if (option == 1)
                url = 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=deletetags';
            else
                url = 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=tagfile';

            $.ajax({
                type:'POST',
                url:url,
                dataType:'json',
                data:({fnums:checkInput, tag: tag}),
                success: function(result) {
                    if (result.status) {
                        $('.modal-body').empty();
                        removeLoader();
                        Swal.fire({
                            position: 'center',
                            type: 'success',
                            title: result.msg,
                            showConfirmButton: false,
                            timer: 1500
                        });

                        for (var i in result.tagged) {
                            $('#'+result.tagged[i].fnum).parents('td').addClass(result.tagged[i].class);
                            $('#'+result.tagged[i].fnum+'_check').parents('td').addClass(result.tagged[i].class);
                        }
                    } else {
                        $('.modal-body').empty();
                        removeLoader();

                        Swal.fire({
                            title: Joomla.JText._('COM_EMUNDUS_ONBOARD_ERROR_MESSAGE'),
                            text: '',
                            type: 'error',
                            showCancelButton: false,
                            showConfirmButton: false,
                            reverseButtons: true,
                            customClass: {
                                title: 'em-swal-title'
                            },
                        });
                    }

                    $('#em-modal-actions').modal('hide');

                    reloadData($('#view').val());
                    reloadActions($('#view').val(), undefined, false);
                    $('.modal-backdrop, .modal-backdrop.fade.in').css('display','none');
                    $('body').removeClass('modal-open');
                },
                error: function (jqXHR) {
                    console.log(jqXHR.responseText);
                }
            });
            break;

        case 15:
        case 16:
            // TODO : make this work

            const iframeFastMails = document.querySelector('#iframe-fast-emails');
            const fastMailForm = iframeFastMails.contentWindow.document.querySelector('#adminForm');
            fastMailForm.submit();
            break;

        case 27:
            generate_letter();
            break;

        // Validating publication change
        case 28:
            var publish = $("#em-action-publish").val();
            $('.modal-body').empty();
            addLoader();
            url = 'index.php?option=com_emundus&controller=files&task=updatepublish';
            $.ajax({
                type:'POST',
                url:url,
                dataType:'json',
                data:({fnums:checkInput, publish: publish}),
                success: function(result) {
                    if (result.status) {
                        $('.modal-body').empty();
                        removeLoader();
                        Swal.fire({
                            position: 'center',
                            type: 'success',
                            title: result.msg,
                            showConfirmButton: false,
                            timer: 1500
                        });

                        if (moduleFilters) {
                            window.dispatchEvent(refreshModuleFiltersEvent);
                        }

                        reloadData($('#view').val());
                        reloadActions($('#view').val(), undefined, false);
                        $('.modal-backdrop, .modal-backdrop.fade.in').css('display','none');
                        $('body').removeClass('modal-open');
                    } else {
                        $('.modal-body').empty();
                        removeLoader();
                        Swal.fire({
                            position: 'center',
                            type: 'warning',
                            title: result.msg
                        });
                    }

                    $('#em-modal-actions').modal('hide');

                },
                error: function (jqXHR) {
                    console.log(jqXHR.responseText);
                }
            });
            break;

        // Generate trombinoscope
        case 31:
            addLoader();
            const iframe = document.querySelector('#iframe-trombinoscope');
            const gridWidthSelect = iframe.contentWindow.document.querySelector('#trombi_grid_width');
            const gridHeightSelect = iframe.contentWindow.document.querySelector('#trombi_grid_height');

            const trombinoscopeData = {
                selected_grid_width: gridWidthSelect.options[gridWidthSelect.selectedIndex].value,
                selected_grid_height: gridHeightSelect.options[gridHeightSelect.selectedIndex].value,
                selected_margin: iframe.contentWindow.document.querySelector('#trombi_margin').value,
                selected_tmpl: decodeEntity(iframe.contentWindow.document.querySelector('#trombi_tmpl').innerHTML),
                header: decodeEntity(iframe.contentWindow.document.querySelector('#trombi_head').innerHTML),
                header_height: iframe.contentWindow.document.querySelector('#trombi_header_height').value,
                footer: iframe.contentWindow.document.querySelector('#trombi_foot').value,
                format:  iframe.contentWindow.document.querySelector('#selected_format').value,
                selected_check:  iframe.contentWindow.document.querySelector('#trombi_check').value,
                selected_border: iframe.contentWindow.document.querySelector('#trombi_border').value,
                string_generate:  iframe.contentWindow.document.querySelector('#string_generate').value
            };

            generate_trombinoscope(iframe.contentWindow.document.querySelector('#string_fnums').innerText, trombinoscopeData);
            break;

        // Export to Aurion
        case 33:
            var type = option.type;
            var state = option.state;
            var tag = option.tag;
            addLoader();

            url = 'index.php?option=com_emundus&controller=files&task=exportfile';
            $.ajax({
                type:'POST',
                url:url,
                dataType:'json',
                data:({fnums:checkInput, type: type}),
                success: function(result) {
                    var msg = result.msg;

                    if (result.status) {
                        if (state) {
                            url = 'index.php?option=com_emundus&controller=files&task=updatestate';
                            $.ajax({
                                type:'POST',
                                url:url,
                                dataType:'json',
                                data:({fnums:checkInput, state: state}),
                                success: function(result) {
                                    if (result.status) {
                                        removeLoader();
                                        Swal.fire({
                                            position: 'center',
                                            type: 'success',
                                            title: msg,
                                            text: result.msg,
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                    }
                                    else {
                                        removeLoader();
                                        Swal.fire({
                                            position: 'center',
                                            type: 'warning',
                                            title: msg,
                                            text: result.msg
                                        });
                                    }
                                },
                                error: function (jqXHR) {
                                    console.log(jqXHR.responseText);
                                }
                            });
                        }

                        if (tag) {
                            url = 'index.php?option=com_emundus&controller=files&task=tagfile';
                            $.ajax({
                                type:'POST',
                                url:url,
                                dataType:'json',
                                data:({fnums:checkInput, tag: tag}),
                                success: function(result) {
                                    if (result.status) {
                                        removeLoader();
                                        Swal.fire({
                                            position: 'center',
                                            type: 'success',
                                            title: msg,
                                            text: result.msg,
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                    }
                                    else {
                                        removeLoader();
                                        Swal.fire({
                                            position: 'center',
                                            type: 'warning',
                                            title: msg,
                                            text: result.msg
                                        });
                                    }
                                },
                                error: function (jqXHR) {
                                    console.log(jqXHR.responseText);
                                }
                            });
                        }

                        removeLoader();
                        Swal.fire({
                            position: 'center',
                            type: 'success',
                            title: result.msg,
                            showConfirmButton: false,
                            timer: 1500
                        });

                    }
                    else {
                        removeLoader();
                        Swal.fire({
                            position: 'center',
                            type: 'warning',
                            title: result.msg
                        });
                    }

                    reloadData($('#view').val());
                    reloadActions($('#view').val(), undefined, false);
                },
                error: function (jqXHR) {
                    console.log(jqXHR.responseText);
                }
            });
            break;

        default:
            break;
    }
}

function setModel(json) {
    var progCode = json.pdffilter.code;
    var campCode = json.pdffilter.camp;

    setProgram(progCode);
    setDocuments(json);
}

function setProgram(progCode) {
    $('#em-export-prg').val(progCode);
    $('#em-export-prg').trigger("chosen:updated");
}

/**
 * Function to be executed before updating the state of the files
 * It will show how many files will be impacted by the action
 * @param fnums
 */
async function countFilesBeforeAction(fnums, action, verb) {
    let form = new FormData();
    form.append('fnums', fnums);
    form.append('action_id', action);
    form.append('verb', verb);

    return fetch('index.php?option=com_emundus&controller=files&task=countfilesbeforeaction',
        {
            body: form,
            method: 'POST'
        }).then((response) => {
        return response.json();
    }).then((json) => {
        return json.data;
    });
}

function updateState(fnums, state)
{
    $.ajax({
        type:'POST',
        url: 'index.php?option=com_emundus&controller=files&task=updatestate',
        dataType:'json',
        data:({
            fnums: fnums,
            state: state
        }),
        success: function(result) {
            $('.modal-footer').hide();
            if (result.status) {
                $('.modal-body').empty();
                removeLoader();
                Swal.fire({
                    position: 'center',
                    type: 'success',
                    title: result.msg,
                    showConfirmButton: false,
                    timer: 1500
                });
            } else {
                $('.modal-body').empty();
                removeLoader();
                Swal.fire({
                    position: 'center',
                    type: 'warning',
                    title: result.msg,
                    showConfirmButton: true,
                    reverseButtons: true,
                    customClass: {
                        title: 'em-swal-title',
                        confirmButton: 'em-swal-confirm-button',
                    },
                });
            }

            $('#em-modal-actions').modal('hide');

            reloadData($('#view').val());
            reloadActions($('#view').val(), undefined, false);
            $('.modal-backdrop, .modal-backdrop.fade.in').css('display','none');
            $('body').removeClass('modal-open');
        },
        error: function (jqXHR) {
            console.log(jqXHR.responseText);
        }
    });
}

async function setCampaign(progCode,campCode, campLabel, headers) {
    await setProgram(progCode);

    getProgramCampaigns(progCode).then(function(data) {
        if(data.status) {
            // $('#em-export-camp').empty();

            $('#em-export-camp').append(data.html);

            $('#em-export-camp').empty();

            // just keep exactly the val
            $('#em-export-camp').append('<option value="'+ campCode +'" data-value="' + campCode + '">' + campLabel + '</option>');
            $('#em-export-camp').trigger("chosen:updated");
            $('#em-export-camp').trigger("change");
            $('#camp').show();

            $('#loadingimg-campaign').remove();

            $('#em-export-opt').val(headers);
            $('#em-export-opt').trigger("chosen:updated");
            $('#em-export-opt').trigger("change");

            $('.modal-header').before('<div id="loadingimg-campaign"><img src="'+loading+'" alt="loading"/></div>');
        }
    }).catch((error) => {
        console.log(error);
    });
}

async function setProfiles(json) {
    var progCode = json.pdffilter.code;
    var campCode = json.pdffilter.camp;

    var checkAllGroups = json.pdffilter.checkAllGroups;
    var checkAllTables = json.pdffilter.checkAllTables;
    var elements = json.pdffilter.elements;
    var headers = json.pdffilter.headers;

    var campLabel = json.pdffilter.camplabel;

    await setCampaign(progCode, campCode, campLabel, headers);

    if (elements[0] !== "") {
        $.ajax({
            type: 'post',
            url: 'index.php?option=com_emundus&controller=files&task=getfabrikdatabyelements',
            dataType: 'JSON',
            data: {elts: elements.toString()},
            async: false,
            success: function (returnData) {
                // build profile(s)
                var profiles = returnData.fabrik_data.profiles;

                profiles.forEach(prf => {
                    checkElement('#felts'+prf.id).then((selector) => {
                        $('#' + selector.id).show();        // show felts
                        $('#loadingimg-campaign').remove();

                        // uncheck all checkbox of each felts
                        if($('#form-exists input:checked').length > 0) {
                            $('#form-exists input:checked').prop('checked', false);
                        }

                        // render tables
                        if (checkAllTables !== null || checkAllTables !== undefined || checkAllTables[0] !== "") {
                            checkAllTables.forEach(tbl => {
                                $('#emundus_checkall_tbl_' + tbl).attr('checked', true);
                            })
                        }

                        if (checkAllGroups !== null || checkAllGroups !== undefined || checkAllGroups[0] !== "") {
                            checkAllGroups.forEach(grp => {
                                $('#emundus_checkall_grp_' + grp).attr('checked', true);
                            })
                        }

                        if (elements !== null || elements !== undefined || elements[0] !== "") {
                            elements.forEach(elt => {
                                $('#emundus_elm_' + elt).attr('checked', true);
                            })
                        }
                    });
                })
            }
        })
    }
}

async function setDocuments(json) {
    var progCode = json.pdffilter.code;
    var campCode = json.pdffilter.camp;
    var attachments = json.pdffilter.attachments;

    await setProfiles(json);
    checkElement('#aelts-' + progCode + campCode).then((selector) => {
        /// show #aelts
        $('#' + selector.id).show();

        /// check to selected elements
        attachments.forEach((doc) => {
            $('[id="' + doc + '"]').prop('checked', true);
        })
    })
}

const checkElement = async selector => {
    while (document.querySelector(selector) === null && document.querySelector('.em-export')) {
        await new Promise( resolve =>  requestAnimationFrame(resolve) )
    }

    if (!document.querySelector('.em-export')) {
        return false;
    }

    return document.querySelector(selector);
};

$(document).ready(function() {
    $('#check').removeClass('em-check-all-all');

    const headerNav = document.querySelector('#g-navigation .g-container');

    // Fix actions and filters to sticky
    if (headerNav) {
        $('.em-menuaction').css('top',headerNav.offsetHeight + 'px');
        $('.side-panel').css('top', headerNav.offsetHeight + 'px');
    }

    var lastVal = {};

    /**
     * Prepare action modal
     */
    $(document).on('click', '.em-actions', async function(e) {
        $.ajaxQ.abortAll();
        e.preventDefault();

        // Get action id
        var id = parseInt($(this).attr('id'));

        // Prepare SweetAlert variables
        var title = '';
        var html = '';
        var swal_container_class = '';
        var swal_popup_class = '';
        var swal_actions_class = '';
        var swal_confirm_button = 'COM_EMUNDUS_ONBOARD_OK';
        var preconfirm = '';
        var preconfirm_value
        var multipleSteps = false;

        removeLoader();

        // Get fnums
        var fnums = getUserCheckArray();
        if (fnums !== 'all') {
            var fnums_list = [];
            var fnums_json = JSON.parse(fnums);

            if (fnums_json.length === 1) {
                var fnum = fnums_json[0].fnum;
                var cid = fnums_json[0].cid;
                var sid = fnums_json[0].sid;
                fnums_list.push(fnums_json[0].fnum);
                fnums_list = fnums_list.join(',');
            } else {
                for(const value of fnums_json){
                    if(value.fnum !== 'em-check-all'){
                        fnums_list.push(value.fnum);
                    }
                }
                fnums_list = fnums_list.join(',');
            }
        }
        fnums = encodeURIComponent(fnums);

        var view = $('#view').val();
        var url = $(this).children('a').attr('href');
        var formid = 29;

        switch (id) {
            case 5 :
                // get formid by fnum
                $.ajax({
                    type:'post',
                    url:'index.php?option=com_emundus&controller=files&task=getformid&Itemid='+itemId,
                    data: {
                        fnum: fnum
                    },
                    dataType:'json',
                    async: false,
                    success: function(result) {
                        if (result.status)
                            formid = result.formid;
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                });
                break;
            case 29 :
                // get formid by fnum
                $.ajax({
                    type:'post',
                    url:'index.php?option=com_emundus&controller=files&task=getdecisionformid&Itemid='+itemId,
                    data: {
                        fnum: fnum
                    },
                    dataType:'json',
                    async: false,
                    success: function(result) {
                        if (result.status)
                            formid = result.formid;
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                });
                break;
            default:
                break;
        }

        String.prototype.fmt = function (hash) {
            var string = this, key;
            for (key in hash) string = string.replace(new RegExp('\\{' + key + '\\}', 'gm'), hash[key]); return string;
        };

        url = url.fmt({ fnums: fnums, fnum: fnum,fnums_joins: fnums_list, applicant_id: sid, campaign_id: cid, view: view, controller: view, Itemid: itemId, formid: formid });
        url +='&action_id='+id;

        var checkInput = getUserCheck();
        var prghtml = '';
        var atthtml = '';
        var tags = null;

        switch (id) {
            /**
             * Open modal for action selected
             * [IFRAME]
             * 1  : Create an application file
             * 4  : Add attachments to file
             * 5  : Add an evaluation
             * 29 : Add a decision
             * 32 : Add an admission
             * [BASIC]
             * 6  : Export Excel
             * 7  : Export ZIP
             * 8  : Export PDF
             * 33 : Export to external application
             * 9  : Send an email
             * 10 : Add comments
             * 11 : Define access on file(s)
             * 13 : Update status of file(s)
             * 14 : Add tag(s) to file(s)
             * 27 : Generate letter(s) for file(s)
             * 28 : Update publication of file (publish, archive or trash)
             * 31 : Trombinoscope (Letter for a group of files)
             * 35 : Fast PDF export
             * 18 : Send email to experts
             */

            // IFRAME
            case 1 :
            case 4 :
            case 5 :
            case 29 :
            case 32 :
                addLoader();
                swal_popup_class = 'em-w-auto'
                swal_actions_class = 'em-actions-none'

                html = '<iframe src="'+url+'" style="width:'+window.getWidth()*0.8+'px; height:'+window.getHeight()*0.8+'px; border:none"></iframe>';

                removeLoader();
                break;

            // Export Excel
            case 6:
                addLoader();

                title = 'COM_EMUNDUS_EXCEL_GENERATION';
                html = '<div id="data" class="em-mt-32"></div>';
                swal_container_class = 'em-export'
                swal_popup_class = 'em-w-100 em-h-100'
                swal_actions_class = 'em-actions-fixed'
                swal_confirm_button = 'COM_EMUNDUS_EXPORTS_EXPORT';

                preconfirm = "return $('#em-export-letter').val();"

                $.ajax({
                    type:'get',
                    url: url,
                    dataType:'json',

                    success: function(result) {
                        if (result.status) {
                            var nbprg = 0;
                            removeLoader();
                            addLoader('.swal2-popup');

                            //**export excel filter */

                            $('#data').append(
                                '<div>' +
                                '<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8">' +
                                '<select class="modal-chzn-select" id="filt_save" name="filt_save" >'+
                                '<option value="0">'+Joomla.JText._('COM_EMUNDUS_FILTERS_PLEASE_SELECT_FILTER')+'</option>' +
                                '</select>'+
                                '<div class="em-flex-row em-flex-row-justify-end em-mt-8">' +
                                '<button class="em-tertiary-button em-w-auto" id="delfilter" style="border-radius: 4px;" title="'+Joomla.JText._('COM_EMUNDUS_ACTIONS_DELETE')+'">'+Joomla.JText._('COM_EMUNDUS_ACTIONS_DELETE')+'</button>' +
                                '<button class="em-primary-button em-w-auto" id="savefilter" title="'+Joomla.JText._('COM_EMUNDUS_FILES_SAVE_FILTER')+'">'+Joomla.JText._('COM_EMUNDUS_FILES_SAVE_FILTER')+'</button>'+
                                '</div>' +
                                '</div>' +
                                '</div>'+

                                '<div class="alert alert-dismissable alert-success em-alert-filter" id="sav-filter">'+
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'+
                                '<strong>'+Joomla.JText._('COM_EMUNDUS_FILTERS_FILTER_SAVED')+'</strong>'+
                                '</div>'+

                                '<div class="alert alert-dismissable alert-success em-alert-filter" id="del-filter">'+
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'+
                                '<strong>'+Joomla.JText._('COM_EMUNDUS_FILTERS_FILTER_DELETED')+'</strong>'+
                                '</div>'+

                                '<div class="alert alert-dismissable alert-danger em-alert-filter" id="err-filter">'+
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'+
                                '<strong>'+Joomla.JText._('COM_EMUNDUS_ERROR_SQL_ERROR')+'</strong>'+
                                '</div>');

                            $('#data').append('' +
                                '<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8 em-mt-16">' +

                                '<div>' +
                                '<h5>'+Joomla.JText._('COM_EMUNDUS_CHOOSE_PRG')+'<span class="material-icons text-xxs text-red-500 mr-0" style="top: -5px;position: relative">emergency</span></h5>' +
                                '</div>' +

                                '<div class="em-mt-8">' +
                                '<select class="chzn-select" name="em-export-prg" id="em-export-prg">' +
                                '<option value="0" >-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_PRG')+' --</option>' +
                                '</select>' +

                                '<div id="camp" class="em-mt-4" style="display:none;">' +
                                '<select name="em-export-camp" id="em-export-camp" style="display: none;" class="chzn-select">' +
                                '<option value="0" data-value="0">-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_CAMP')+' --</option>' +
                                '</select>' +
                                '</div>' +
                                '<div id="letter" class="em-mt-4" style="display:none;">' +
                                '<select name="em-export-letter" id="em-export-letter" style="display:none;" class="chzn-select">' +
                                '<option value="0" data-value="0">-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_LETTER')+' --</option>' +
                                '</select>' +
                                '</div>' +
                                '</div>');

                            $('#data').append('<div id="elements_detail" style="display: none">' +
                                '<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8 em-mt-16">' +
                                '<div>'+
                                '<table style="width:100%;"><tr>'+
                                '<th class="em-bg-transparent"><div class="em-flex-row em-pointer" id="showelements">' +
                                '<span title="'+Joomla.JText._('COM_EMUNDUS_SHOW_ELEMENTS')+'" id="showelements_icon" class="material-icons em-mr-4" style="transform: rotate(-90deg)">expand_more</span>' +
                                '<p>'+Joomla.JText._('COM_EMUNDUS_CHOOSE_FORM_ELEM')+'</p>'+
                                '</div></th>' +

                                '<th class="em-bg-transparent" id="th-eval"><div class="em-flex-row em-pointer" id="showevalelements">' +
                                '<span title="'+Joomla.JText._('COM_EMUNDUS_SHOW_ELEMENTS')+'" class="material-icons em-mr-4" id="showevalelements_icon" style="transform: rotate(-90deg)">expand_more</span>' +
                                '<p>'+Joomla.JText._('COM_EMUNDUS_CHOOSE_EVAL_FORM_ELEM')+'</p>'+
                                '</div></th>' +

                                '<th class="em-bg-transparent" id="th-dec" style="display: none;"><div class="em-flex-row em-pointer" id="showdecisionelements">' +
                                '<span title="'+Joomla.JText._('COM_EMUNDUS_SHOW_ELEMENTS')+'" class="material-icons em-mr-4" id="showdecisionelements_icon" style="transform: rotate(-90deg)">expand_more</span>' +
                                '<p>'+Joomla.JText._('COM_EMUNDUS_CHOOSE_DECISION_FORM_ELEM')+'</p>'+
                                '</div></th>' +

                                '<th class="em-bg-transparent" id="th-adm" style="display: none;"><div class="em-flex-row em-pointer" id="showadmissionelements">' +
                                '<span title="'+Joomla.JText._('COM_EMUNDUS_SHOW_ELEMENTS')+'" class="material-icons em-mr-4" id="showadmissionelements_icon" style="transform: rotate(-90deg)">expand_more</span>' +
                                '<p>'+Joomla.JText._('COM_EMUNDUS_CHOOSE_ADMISSION_FORM_ELEM')+'</p>'+
                                '</div></th>' +

                                '</tr></table>' +

                                '</div>' +
                                '<div class="em-p-16">' +
                                // '<select name="em-export-form" id="em-export-form" class="chzn-select"></select><br/>' +
                                '<div id="appelement">'+
                                '<div id="elements-popup" style="display: none; ">' +
                                '</div>' +
                                '</div>'+
                                '<div id="evalelement" style="display: none;">' +
                                '<div id="eval-elements-popup" style="display: none;">' +
                                '</div>' +
                                '</div>' +
                                '<div id="decelement" style="display: none;">' +
                                '<div id="decision-elements-popup" style="display: none;">' +
                                '</div>' +
                                '</div>' +
                                '<div id="admelement" style="display: none;">' +
                                '<div id="admission-elements-popup" style="display: none;">' +
                                '</div>' +
                                '</div>' +
                                '</div>' +
                                '</div>' +
                                '</div>');

                            checkInput = getUserCheck();

                            document.getElementsByClassName('em-swal-confirm-button')[0].style.opacity = 0;

                            $.ajax({
                                type:'post',
                                url: 'index.php?option=com_emundus&controller=files&task=getPDFProgrammes',
                                data: {checkInput : checkInput},
                                dataType:'json',

                                success: function(result) {
                                    if (result.status) {
                                        $('#em-export-prg').append(result.html);
                                        $('#em-export-prg').chosen('destroy').chosen({width: "100%"});

                                        removeLoader();
                                    }
                                },
                                error: function (jqXHR) {
                                    console.log(jqXHR.responseText);
                                }
                            });


                            $('#em-export-prg').on('change', function() {
                                var code = $(this).val();
                                if (code != 0) {
                                    $.ajax({
                                        type:'get',
                                        url: 'index.php?option=com_emundus&controller=files&task=checkforms&code='+code,
                                        dataType:'json',
                                        success: function(result) {
                                            if (result.status) {
                                                if (result.eval == 1) {
                                                    $('#th-eval').show();
                                                    $('#evalelement').show();
                                                }
                                                if (result.dec == 1) {
                                                    $('#th-dec').show();
                                                    $('#decelement').show();
                                                }
                                                if (result.adm == 1) {
                                                    $('#th-adm').show();
                                                    $('#admelement').show();
                                                }

                                                /// add loading
                                                $('.modal-header').before('<div id="loadingimg-campaign"><img src="'+loading+'" alt="loading"/></div>');

                                                getProgramCampaigns(code).then((result) => {
                                                    if (result.status) {
                                                        $('#em-export-camp').empty();
                                                        $('#em-export-camp').append('<option value="0" data-value="0">-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_CAMP')+' --</option>');
                                                        $('#em-export-camp').append(result.html);
                                                        $("#em-export-camp").val(0);
                                                        $('#em-export-camp').chosen('destroy').chosen({width: "100%"});

                                                        $('#loadingimg-campaign').remove();
                                                        $('#camp').show();
                                                    }
                                                }).catch((error) => {
                                                    console.log(error);
                                                });
                                            }
                                        },
                                        error: function (jqXHR) {
                                            console.log(jqXHR.responseText);
                                        }
                                    });

                                } else {
                                    $('.btn-success').hide();
                                    $('#camp').hide();
                                    $('#em-export').empty();
                                    $('#elements_detail').hide();
                                    $('#elements-popup').hide();

                                    document.getElementById('list-element-export').style.display = 'none';
                                    document.getElementById('oelts').style.display = 'none';
                                    document.getElementById('methode').style.display = 'none';
                                }
                            });

                            $('#em-export-camp').on('change', function() {

                                var code = $('#em-export-prg').val();
                                var camp = $("#em-export-camp").val();

                                if (code != 0 && camp != 0) {


                                    $.ajax({
                                        type:'get',
                                        url: 'index.php?option=com_emundus&controller=files&task=checkforms&code='+code,
                                        dataType:'json',

                                        success: function(result) {
                                            if (result.status) {

                                                if (result.eval == 1) {
                                                    $('#th-eval').show();
                                                    $('#evalelement').show();
                                                }
                                                if (result.dec == 1) {
                                                    $('#th-dec').show();
                                                    $('#decelement').show();
                                                }
                                                if (result.adm == 1) {
                                                    $('#th-adm').show();
                                                    $('#admelement').show();
                                                }

                                                document.getElementById('list-element-export').style.display = 'block';
                                                document.getElementById('oelts').style.display = 'block';
                                                document.getElementById('methode').style.display = 'block';

                                                document.getElementsByClassName('em-swal-confirm-button')[0].style.opacity = 1;

                                                /*** application form elements */
                                                $.ajax({
                                                    type: 'get',
                                                    url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&viewcall=files&camp='+camp+'&code=' + code,

                                                    success: function(data) {
                                                        $('.btn-success').show();
                                                        $('#em-export-form').empty();
                                                        $('#elements-popup').empty();
                                                        $('#elements-popup').append(data);
                                                        $.ajax({
                                                            type:'get',
                                                            url: 'index.php?option=com_emundus&controller=files&task=getformelem&code='+code+'&camp='+camp+'&Itemid='+itemId,
                                                            dataType:'json',
                                                            success: function(result) {

                                                                var item='<option value="0" selected>' + Joomla.JText._('PLEASE_SELECT') + '</option>';

                                                                for (var d in result.elts) {

                                                                    if (isNaN(parseInt(d)))
                                                                        break;

                                                                    var menu_tmp = result.elts[d].title;

                                                                    if (menu != menu_tmp) {
                                                                        item += '<optgroup label="________________________________"><option disabled class="emundus_search_elm" value="-">' + menu_tmp.toUpperCase() + '</option></optgroup>';
                                                                        menu = menu_tmp;
                                                                    }

                                                                    if (grId != null || grId != result.elts[d].group_id)
                                                                        item += '</optgroup>';

                                                                    if (grId != result.elts[d].group_id) {

                                                                        if (Joomla.JText._(result.elts[d].group_id) == "undefined" || Joomla.JText._(result.elts[d].group_id) == "")
                                                                            item += '<optgroup label=">> '+result.elts[d].group_label+'">';
                                                                        else
                                                                            item += '<optgroup label=">> '+Joomla.JText._(result.elts[d].group_label)+'">';
                                                                    }

                                                                    grId = result.elts[d].group_id;

                                                                    var label = result.elts[d].element_label.replace(/(<([^>]+)>)/ig, "");
                                                                    var elt_label = Joomla.JText._(label);

                                                                    if (elt_label == "undefined" || elt_label == "")
                                                                        elt_label = label;

                                                                    item += '<option value="'+result.elts[d].id+'" data-value="'+label+'">'+elt_label+'</option>';
                                                                }

                                                                $('#em-export-form').append(item);
                                                                $('#em-export-form').trigger("chosen:updated");

                                                                item = "";
                                                                if (view == "files") {
                                                                    for (var d in result.defaults) {
                                                                        if (isNaN(parseInt(d)))
                                                                            break;

                                                                        if ($('#em-export #'+result.defaults[d].id+'-item').length == 0)
                                                                            item += '<li class="em-export-item" id="' + result.defaults[d].id + '-item"><span class="em-excel_elts em-flex-row"><span id="' + result.defaults[d].id + '-itembtn" class="em-pointer fabrik-elt-delete material-icons em-red-500-color em-mr-4">delete_outline</span><p>' + result.defaults[d].element_label + '</p></span></li>';
                                                                    }
                                                                    $('#em-export').append(item);
                                                                }

                                                                /*** evaluation elements */
                                                                $.ajax({
                                                                    type: 'get',
                                                                    url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=evaluation',

                                                                    success: function(data) {

                                                                        $('#eval-elements-popup').empty();
                                                                        $.ajax({
                                                                            type:'get',
                                                                            url: 'index.php?option=com_emundus&controller=evaluation&task=getformelem',
                                                                            data: {code:code},
                                                                            dataType:'json',
                                                                            success: function(result) {

                                                                                var item='';

                                                                                $('#eval-elements-popup').append(data);

                                                                                if (view == "evaluation") {
                                                                                    for (var d in result.defaults) {
                                                                                        if (isNaN(parseInt(d)))
                                                                                            break;
                                                                                        item += '<li class="em-export-item" id="' + result.defaults[d].element_id + '-item"><span class="em-excel_elts em-flex-row"><span id="' + result.defaults[d].element_id + '-itembtn" class="em-pointer fabrik-elt-delete material-icons em-red-500-color em-mr-4">delete_outline</span><p>' + result.defaults[d].element_label + '</p></span></li>';
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

                                                                                                                if (view == "admission") {
                                                                                                                    for (var d in result.defaults) {
                                                                                                                        if (isNaN(parseInt(d)))
                                                                                                                            break;
                                                                                                                        item += '<li class="em-export-item" id="' + result.defaults[d].element_id + '-item"><span class="em-excel_elts em-flex-row"><span id="' + result.defaults[d].element_id + '-itembtn" class="em-pointer fabrik-elt-delete material-icons em-red-500-color em-mr-4">delete_outline</span><p>' + result.defaults[d].element_label + '</p></span></li>';
                                                                                                                        $('#emundus_elm_'+ result.defaults[d].element_id).prop("checked", true);
                                                                                                                    }
                                                                                                                    $('#em-export').append(item);
                                                                                                                }
                                                                                                            },
                                                                                                            error: function (jqXHR) {
                                                                                                                console.log(jqXHR.responseText);
                                                                                                            }
                                                                                                        });
                                                                                                    },
                                                                                                    error: function (jqXHR) {
                                                                                                        console.log(jqXHR.responseText);
                                                                                                    }
                                                                                                });
                                                                                            },
                                                                                            error: function (jqXHR) {
                                                                                                console.log(jqXHR.responseText);
                                                                                            }
                                                                                        });

                                                                                    },
                                                                                    error: function (jqXHR) {
                                                                                        console.log(jqXHR.responseText);
                                                                                    }
                                                                                });
                                                                            },
                                                                            error: function (jqXHR) {
                                                                                console.log(jqXHR.responseText);
                                                                            }
                                                                        });
                                                                    },
                                                                    error: function (jqXHR) {
                                                                        console.log(jqXHR.responseText);
                                                                    }
                                                                });
                                                            },
                                                            error: function (jqXHR) {
                                                                console.log(jqXHR.responseText);
                                                            }
                                                        });
                                                    },
                                                    error: function (jqXHR) {
                                                        console.log(jqXHR.responseText);
                                                    }
                                                });
                                            }
                                        },
                                        error: function (jqXHR) {
                                            console.log(jqXHR.responseText);
                                        }
                                    });

                                    $('.btn-success').show();
                                    $('#elements_detail').show();

                                } else {
                                    $('.btn-success').hide();
                                    $('#elements_detail').hide();
                                    $('#elements-popup').hide();

                                    document.getElementById('list-element-export').style.display = 'none';
                                    document.getElementById('oelts').style.display = 'none';
                                    document.getElementById('methode').style.display = 'none';
                                }
                            });

                            $('#data').append('<div id="main" class="em-grid-2 em-mt-16"><div id="list-element-export" style="display: none"></div><div id="oelts" style="display:none;"></div></div>');

                            var defaults = '<div class="em-flex-row em-pointer em-mb-8" id="list-element-export-button"><p>'+Joomla.JText._('COM_EMUNDUS_CHOOSEN_FORM_ELEM')+'</p></div>' +
                                '<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8 em-mt-16" id="em-export-elts">' +
                                '<ul id="em-export" class="em-m-8"></ul>' +
                                '</div>';

                            $('#list-element-export').append(defaults);

                            var grId = null;
                            var menu = null;

                            $('#oelts').append('<div>' +
                                '<p>  '+Joomla.JText._('COM_EMUNDUS_CHOOSE_OTHER_COL')+'</p>'+
                                '<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8 em-mt-16">'+
                                '<div class="em-flex-row em-mb-4"><input class="em-ex-check" type="checkbox" value="photo" name="em-ex-photo" id="em-ex-photo"/>' +
                                '<label for="em-ex-photo" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_PHOTO')+'</label></div>' +
                                '<div class="em-flex-row em-mb-4"><input class="em-ex-check" type="checkbox" value="forms" name="em-ex-forms" id="em-ex-forms"/>' +
                                '<label for="em-ex-forms" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_FORMS')+'</label></div>' +
                                '<div class="em-flex-row em-mb-4"><input class="em-ex-check" type="checkbox" value="attachment" name="em-ex-attachment" id="em-ex-attachment"/>' +
                                '<label for="em-ex-attachment" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_ATTACHMENT')+'</label></div>' +
                                '<div class="em-flex-row em-mb-4"><input class="em-ex-check" type="checkbox" value="comment" name="em-ex-comment" id="em-ex-comment"/>' +
                                '<label for="em-ex-comment" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_COMMENT')+'</label></div>' +
                                '<div class="em-flex-row em-mb-4"><input class="em-ex-check" type="checkbox" value="tags" name="em-ex-tags" id="em-ex-tags"/>' +
                                '<label for="em-ex-tags" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_TAGS')+'</label></div>' +
                                '<div class="em-flex-row em-mb-4"><input class="em-ex-check" type="checkbox" value="group-assoc" name="em-ex-group" id="em-ex-group"/>' +
                                '<label for="em-ex-group" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_ASSOCIATED_GROUPS')+'</label></div>' +
                                '<div class="em-flex-row em-mb-4"><input class="em-ex-check" type="checkbox" value="user-assoc" name="em-ex-user" id="em-ex-user"/>' +
                                '<label for="em-ex-user" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_ASSOCIATED_USERS')+'</label></div>' +
                                '<div class="em-flex-row em-mb-4"><input class="em-ex-check" type="checkbox" value="overall" name="em-ex-overall" id="em-ex-overall"/>' +
                                '<label for="em-ex-overall" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_EVALUATIONS_OVERALL')+'</label></div>' +
                                '</div></div></div>')

                            // TODO: fix upper-case options
                            // '<div class="em-flex-row em-mb-4"><input class="em-ex-check0" type="checkbox" value="upper-case" name="upper-case" id="upper-case" style="max-height: 20px;"/>' +
                            // '<label for="upper-case" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_TO_UPPER_CASE')+'</label></div>' +
                            $('#data').append( '<div id="methode" style="display: none" class="em-grid-2 em-mt-16">'+
                                '<div><p>' +Joomla.JText._('COM_EMUNDUS_CHOOSE_EXTRACTION_OPTION')+ '</p>' +
                                '<div id="exp" class="em-p-12-16 em-bg-neutral-200 em-border-radius-8 em-mt-16">'+
                                '<div id="exp1"><form style="margin-left:15px; margin-bottom:6px">'+
                                '<input type="radio" name="em-export-methode" id="em-export-methode" value="0" checked>' +Joomla.JText._('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE_AGGREGATE_DISTINCT')+
                                '<br/><input type="radio" name="em-export-methode" id="em-export-methode" value="2">' +Joomla.JText._('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE_AGGREGATE')+
                                '<br/><input type="radio" name="em-export-methode" id="em-export-methode" value="1">' +Joomla.JText._('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE_LEFTJOIN')+ '<br>'+
                                '</form></div></div></div>'+
                                '<div><p>'+Joomla.JText._('COM_EMUNDUS_CHOOSE_OTHER_OPTION')+'</p>' +
                                '<div id="forms" class="em-p-12-16 em-bg-neutral-200 em-border-radius-8 em-mt-16">'+
                                '<div id="forms1">'+
                                '<div class="em-flex-row em-mb-4"><input class="em-ex-check0" type="checkbox" value="form-title" name="form-title" id="form-title" style="max-height: 20px;"/>' +
                                '<label for="form-title" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_FORM_TITLE')+'</label></div>' +
                                '<div class="em-flex-row em-mb-4"><input class="em-ex-check0" type="checkbox" value="form-group" name="form-group" id="form-group" style="max-height: 20px;"/>' +
                                '<label for="form-group" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_FORM_GROUP')+'</label></div>' +
                                '</div>'+
                                '</div>'+
                                '</div></div>' );

                            //*** on export excel filter change ******************************/
                            $('#filt_save').on('change', function(e) {
                                $('#model-err').empty();
                                $('#em-export').remove();                           // reset #em-export
                                $('#oelts :input').prop('checked', false);          // reset #oelts
                                $('#forms :input').prop('checked', false);          // reset #forms
                                $('#em-export-elts').append('<ul id="em-export"></ul></div>');
                                var id = $(this).val();
                                if (id != 0) {
                                    //$('.modal-header').before('<div id="loadingimg-campaign"><img src="'+loading+'" alt="loading"/></div>');
                                    $.ajax({
                                        type:'get',
                                        url: 'index.php?option=com_emundus&controller=files&task=getExportExcelFilter',
                                        dataType:'json',
                                        success: function(result) {
                                            if (result.status) {
                                                $.ajax({
                                                    type: 'post',
                                                    url: 'index.php?option=com_emundus&controller=files&task=getExportExcelFilterById',
                                                    dataType: 'JSON',
                                                    data: { id : id},
                                                    success: function(excelFilter) {
                                                        try {
                                                            var constraints = jQuery.parseJSON(excelFilter.filter.constraints);
                                                            var filter = jQuery.parseJSON(constraints.excelfilter);

                                                            if(!jQuery.isEmptyObject(filter.objects)) {
                                                                var otherElements = Object.values(filter.objects);

                                                                /// iterate
                                                                otherElements.forEach(elems => {
                                                                    $('#' + elems).prop('checked', true);
                                                                })
                                                            }

                                                            if (filter.baseElements !== undefined) {
                                                                var baseElements = filter.baseElements;

                                                                $.ajax({
                                                                    type: 'post',
                                                                    url: 'index.php?option=com_emundus&controller=files&task=getselectedelements',
                                                                    dataType: 'JSON',
                                                                    data: {elts: baseElements.split(',')},
                                                                    success: function (selectedElements) {
                                                                        var selectedElts = selectedElements.elements.selected_elements;

                                                                        selectedElts.forEach(elts => {
                                                                            $('#em-export').append('<li class="em-export-item" id="' + elts.id + '-item"><span class="em-excel_elts em-flex-row"><span id="' + elts.id + '-itembtn" class="em-pointer fabrik-elt-delete material-icons em-red-500-color em-mr-4">delete_outline</span><p>' + elts.label + '</p></span></li>');
                                                                        });
                                                                        $('#em-export').trigger("chosen:updated");
                                                                    }
                                                                })
                                                            }
                                                        } catch(e) {
                                                            $('#filt_save_chosen').append('<div id="model-err" style="color: red">' + Joomla.JText._('COM_EMUNDUS_MODEL_ERR') + '</div>');
                                                        }
                                                    }
                                                })

                                                for (var d in result.filter) {
                                                    $('#model-err-data').empty();
                                                    if (isNaN(parseInt(d)))
                                                        break;
                                                    if (result.filter[d].id == id) {
                                                        var constraints = result.filter[d].constraints;
                                                        try {
                                                            constraints = jQuery.parseJSON(constraints);
                                                            var filter = jQuery.parseJSON(constraints.excelfilter);
                                                            var proglabel = filter.programmelabel;
                                                            var camplabel = filter.campaignlabel;
                                                            var code = filter.code;
                                                            var camp = filter.camp;
                                                            var letters = filter.letters;

                                                            if (code != 0) { //for programmes

                                                                /// check if letters != 0 -> [yes] --> select it, [no] --> do nothing
                                                                if(letters) {
                                                                    $('#em-export-letter  option[value="' + letters + '"]').prop("selected", true);
                                                                    $('#em-export-letter').trigger("chosen:updated");
                                                                    $('#em-export-letter').trigger("change");
                                                                }

                                                                html = '<option value="'+code+'">'+proglabel+'</option>';
                                                                if ($("#em-export-prg option[value="+code+"]").length == 0) {
                                                                    $('#em-export-prg').append(html);// add option to list
                                                                }
                                                                $('#em-export-prg').val(code);
                                                                $('#em-export-prg').trigger("chosen:updated");


                                                                getProgramCampaigns(code).then(function(result) {
                                                                    if (result.status) {

                                                                        if ($("#em-export-camp option[value="+camp+"]").length == 0) {
                                                                            $('#em-export-camp').append('<option value="'+camp+'">'+camplabel+'</option>');// add option to list
                                                                        }
                                                                        $('#em-export-camp').val(camp);
                                                                        $('#em-export-camp').trigger("chosen:updated");

                                                                        $('#camp').show();
                                                                    }
                                                                }).catch((error) => {
                                                                    console.log(jqXHR.responseText);
                                                                });

                                                                $.ajax({
                                                                    type:'get',
                                                                    url: 'index.php?option=com_emundus&controller=files&task=checkforms&code='+code,
                                                                    dataType:'json',
                                                                    success: function(result) {
                                                                        if (result.status) {
                                                                            if (result.eval == 1) {
                                                                                $('#th-eval').show();
                                                                                $('#evalelement').show();
                                                                            }
                                                                            if (result.dec == 1) {
                                                                                $('#th-dec').show();
                                                                                $('#decelement').show();
                                                                            }
                                                                            if (result.adm == 1) {
                                                                                $('#th-adm').show();
                                                                                $('#admelement').show();
                                                                            }
                                                                        }
                                                                    },
                                                                    error: function(jqXHR) {
                                                                        console.log(jqXHR.responseText);
                                                                    }
                                                                });
                                                                /*** application form elements */
                                                                $.ajax({
                                                                    type: 'get',
                                                                    url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&viewcall=files&camp='+camp+'&code=' + code,
                                                                    success: function (data) {
                                                                        //$('#em-export').empty();
                                                                        $.ajax({
                                                                            type:'get',
                                                                            url: 'index.php?option=com_emundus&controller=files&task=getformelem&code='+code+'&camp='+camp+'&Itemid='+itemId,
                                                                            dataType:'json',
                                                                            success: function(result) {
                                                                                var item='<option value="0" selected>'+Joomla.JText._('JGLOBAL_SELECT_AN_OPTION')+'</option>';

                                                                                for (var d in result.elts) {

                                                                                    if (isNaN(parseInt(d)))
                                                                                        break;

                                                                                    var menu_tmp = result.elts[d].title;

                                                                                    if (menu != menu_tmp) {
                                                                                        item += '<optgroup label="________________________________"><option disabled class="emundus_search_elm" value="-">' + menu_tmp.toUpperCase() + '</option></optgroup>';
                                                                                        menu = menu_tmp;
                                                                                    }

                                                                                    if (grId != null || grId != result.elts[d].group_id)
                                                                                        item += '</optgroup>';

                                                                                    if (grId != result.elts[d].group_id) {

                                                                                        if (Joomla.JText._(result.elts[d].group_id) == "undefined" || Joomla.JText._(result.elts[d].group_id) == "")
                                                                                            item += '<optgroup label=">> '+result.elts[d].group_label+'">';
                                                                                        else
                                                                                            item += '<optgroup label=">> '+Joomla.JText._(result.elts[d].group_label)+'">'
                                                                                    }

                                                                                    grId = result.elts[d].group_id;

                                                                                    var label = result.elts[d].element_label.replace(/(<([^>]+)>)/ig, "");
                                                                                    var elt_label = Joomla.JText._(label);

                                                                                    if (elt_label == "undefined" || elt_label == "") {
                                                                                        elt_label = label;
                                                                                    }

                                                                                    item += '<option value="'+result.elts[d].id+'" data-value="'+label+'">'+elt_label+'</option>';
                                                                                }

                                                                                $('#em-export-form').append(item);
                                                                                $('#em-export-form').trigger("chosen:updated");
                                                                            }
                                                                        });

                                                                        /*** evaluation elements */
                                                                        $.ajax({
                                                                            type: 'get',
                                                                            url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=evaluation',
                                                                            success: function(data) {
                                                                                $('#eval-elements-popup').empty();
                                                                                $('#eval-elements-popup').append(data);

                                                                                /*** decision elements */
                                                                                $.ajax({
                                                                                    type: 'get',
                                                                                    url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=decision',
                                                                                    success: function(data) {
                                                                                        $('#decision-elements-popup').empty();
                                                                                        $('#decision-elements-popup').append(data);

                                                                                        /*** admission elements */
                                                                                        $.ajax({
                                                                                            type: 'get',
                                                                                            url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&code=' + code + '&form=admission',
                                                                                            success: function(data) {

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
                                                                                                    }
                                                                                                }

                                                                                                $('input[name=em-export-methode][value="'+methode+'"]').prop("checked",true);

                                                                                                if (options != "") {

                                                                                                    $('#forms').find('input[type=checkbox]:checked').removeAttr('checked');

                                                                                                    for (var d in options) {
                                                                                                        if (isNaN(parseInt(d)))
                                                                                                            break;

                                                                                                        $('#'+ options[d]).prop("checked", true);
                                                                                                    }
                                                                                                }

                                                                                            },
                                                                                            error: function (jqXHR) {
                                                                                                console.log(jqXHR.responseText);
                                                                                            }
                                                                                        });
                                                                                    },
                                                                                    error: function (jqXHR) {
                                                                                        console.log(jqXHR.responseText);
                                                                                    }
                                                                                });
                                                                            },
                                                                            error: function (jqXHR) {
                                                                                console.log(jqXHR.responseText);
                                                                            }
                                                                        });

                                                                        $('#elements-popup').empty();
                                                                        $('#elements-popup').append(data);
                                                                    },
                                                                    error: function (jqXHR) {
                                                                        console.log(jqXHR.responseText);
                                                                    }
                                                                });

                                                            } else {
                                                                $('.btn-success').hide();
                                                                $('#elements_detail').hide();
                                                                $('#elements-popup').hide();
                                                            }
                                                        }
                                                        catch(e) {
                                                            $('#data').append('<br> <div id="model-err-data" style="color: red">' + Joomla.JText._('COM_EMUNDUS_MODEL_ERR') + '</div></br>');
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
                                        error: function(jqXHR) {
                                            console.log(jqXHR.responseText);
                                        }
                                    });
                                }
                            });


                            $('#em-export-prg').chosen({width: "100%"});
                            $('#em-export-camp').chosen({width: "100%"});
                            $('#em-export-form').chosen({width: "100%"});

                            getAllLetters().then(function(letters) {
                                letters.forEach(letter => {
                                    if (letter.template_type == '4') {
                                        $('#em-export-letter').append('<option value="' + letter.id + '">' + letter.title + '</option>');
                                        $('#em-export-letter').chosen({width: "100%"});
                                        $('#letter').show();
                                    }
                                });
                            }).catch(function(error) {
                                console.log(error);
                            });

                            // get export excel saved filter
                            $.ajax({
                                type:'get',
                                url: 'index.php?option=com_emundus&controller=files&task=getExportExcelFilter',
                                dataType:'json',
                                success: function (result) {
                                    if (result.status) {
                                        result.filter.forEach(filter => {
                                            if (!isNaN(parseInt(filter.id))) {
                                                $('#filt_save').append('<option value="' + filter.id + '">' + filter.name + '</option>');
                                            }
                                        });

                                        $('#filt_save').chosen({width: "100%"});
                                    } else {
                                        const errorFilterElement = document.getElementById('err-filter');

                                        if (errorFilterElement) {
                                            errorFilterElement.style.display = 'block';

                                            setTimeout(function() {
                                                errorFilterElement.style.display = 'none';
                                            }, 2000);
                                        }

                                        console.warn('Error: failed to get export excel filters of current user');
                                    }
                                },
                                error: function(jqXHR) {
                                    console.log(jqXHR.responseText);
                                }
                            });
                        }
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                });
                break;

            // Export ZIP
            case 7:
                addLoader();

                title = 'COM_EMUNDUS_EXPORTS_CREATE_ZIP';
                html = '<div id="data" class="em-mt-32"></div>';
                swal_container_class = 'em-export';
                swal_popup_class = 'em-w-100 em-h-100';
                swal_actions_class = 'em-actions-fixed';
                swal_confirm_button = 'COM_EMUNDUS_EXPORTS_EXPORT';

                setTimeout(() => {
                    removeLoader();
                    addLoader('.swal2-popup');

                    $('#data').append('<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8">' +
                        '<div>' +
                        '<h5>'+Joomla.JText._('COM_EMUNDUS_CHOOSE_PRG')+'</h5>' +
                        '</div>' +
                        '<div>' +
                        '<select class="chzn-select" name="em-export-prg" id="em-export-prg">' +
                        '<option value="0" >-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_PRG')+' --</option>' +
                        '</select>' +
                        '<div id="camp" class="em-mt-8" style="display:none;">' +
                        '<select name="em-export-camp" id="em-export-camp" style="display: none;" class="chzn-select">' +
                        '<option value="0" data-value="0">-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_CAMP')+' --</option>' +
                        '</select>' +
                        '</div>' +
                        '</div>' +
                        '</div>');

                    $('#data').append('<div class=" em-mt-8 em-p-12-16 em-bg-neutral-200 em-border-radius-8" id="form-exists" style="display:none;">'+
                        '<div>'+
                        '<div class="em-flex-row">' +
                        '<input class="em-ex-check" type="checkbox" value="forms" name="forms" id="em-ex-forms" checked />' +
                        '<label for="em-ex-forms" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_EXPORTS_FORMS_PDF')+'</label>'+
                        '</div>' +
                        '</div>'+
                        '<div id="felts" style="overflow:auto;display:none;"></div>'+
                        '</div>'+
                        '<div class="em-mt-8 em-p-12-16 em-bg-neutral-200 em-border-radius-8" id="att-exists" style="display:none;">'+
                        '<div>'+
                        '<div class="em-flex-row">' +
                        '<input class="em-ex-check" type="checkbox" value="attachment" name="attachment" id="em-ex-attachment" checked/>' +
                        '<label for="em-ex-attachment" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_EXPORTS_ATTACHMENT_PDF')+'</label>'+
                        '</div>' +
                        '</div>'+
                        '<div id="aelts" style="overflow:auto;display:none;"></div>'+
                        '</div>'+

                        '<div class="em-mt-8 em-p-12-16 em-bg-neutral-200 em-border-radius-8" id="eval-exists" style="display:none;">'+
                        '<div>'+
                        '<div class="em-flex-row">' +
                        '<input class="em-ex-check" type="checkbox"  value="assessment" name="assessment" id="em-ex-assessment"/>' +
                        '<label for="em-ex-assessment" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_EXPORTS_ASSESSMENT_PDF')+'</label>'+
                        '</div>' +
                        '</div>'+
                        '</div>'+

                        '<div class="em-mt-8" id="dec-exists" style="display:none;">'+
                        '<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8">'+
                        '<div class="em-flex-row">' +
                        '<input class="em-ex-check" type="checkbox"  value="decision" name="decision" id="em-ex-decision"/>' +
                        '<label for="em-ex-decision" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_EXPORTS_DECISION_PDF')+'</label>'+
                        '</div>' +
                        '</div>'+
                        '</div>'+

                        '<div class="em-mt-8" id="adm-exists" style="display:none;">'+
                        '<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8">'+
                        '<div class="em-flex-row">' +
                        '<input class="em-ex-check" type="checkbox"  value="admission" name="admission" id="em-ex-admission"/>' +
                        '<label for="em-ex-admission" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_EXPORTS_ADMISSION_PDF')+'</label>'+
                        '</div>' +
                        '</div>'+
                        '</div>');

                    $('#data').append('<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8 em-mt-16" id="exp-options">'+
                        '<div class="em-flex-row">' +
                        '<input class="em-ex-check" type="checkbox"  value="header" name="em-add-header" id="em-add-header" checked/>&ensp;' +
                        '<label for="em-add-header">'+Joomla.JText._('COM_EMUNDUS_EXPORTS_ADD_HEADER')+'</label>'+
                        '</div>' +
                        '<div id="exp-opt">'+
                        '<label >'+Joomla.JText._('COM_EMUNDUS_EXPORTS_PDF_OPTIONS')+'</label>'+
                        '<select class="chzn-select" name="em-export-opt" id="em-export-opt" multiple>'+
                        '<option  value="aid" selected>'+Joomla.JText._('COM_EMUNDUS_APPLICATION_APPLICANT')+'</option>' +
                        '<option  value="afnum" selected>'+Joomla.JText._('COM_EMUNDUS_FNUM')+'</option>' +
                        '<option  value="aemail" selected>'+Joomla.JText._('COM_EMUNDUS_EMAIL')+'</option>' +
                        '<option  value="aapp-sent" selected>'+Joomla.JText._('COM_EMUNDUS_APPLICATION_SENT_ON')+'</option>' +
                        '<option  value="adoc-print" selected>'+Joomla.JText._('COM_EMUNDUS_APPLICATION_DOCUMENT_PRINTED_ON')+'</option>' +
                        '<option  value="tags"  disabled>'+Joomla.JText._('COM_EMUNDUS_EXPORTS_PDF_TAGS')+'</option>' +
                        '<option  value="status" selected>'+Joomla.JText._('COM_EMUNDUS_EXPORTS_PDF_STATUS')+'</option>' +
                        '<option  value="upload" selected>'+Joomla.JText._('COM_EMUNDUS_ATTACHMENTS_FILES_UPLOADED')+'</option>' +
                        '</select>'+
                        '</div></div>');

                    checkInput = getUserCheck();

                    prghtml = '';
                    atthtml = '';

                    $.ajax({
                        type:'post',
                        url: 'index.php?option=com_emundus&controller=files&task=getPDFProgrammes',
                        data: {checkInput : checkInput},
                        dataType:'json',

                        success: function(result) {
                            if (result.status) {
                                removeLoader();
                                addLoader('.swal2-popup');

                                $('#em-export-prg').append(result.html);
                                $('#em-export-prg').chosen('destroy').chosen({width: "100%"});

                                nbprg = $('#em-export-prg option').size();

                                if (nbprg == 2) {
                                    $('#em-export-prg option:eq(1)').attr('selected', true);
                                    $('#em-export-prg').trigger("chosen:updated");

                                    var code = $('#em-export-prg').val();

                                    $.ajax({
                                        type:'get',
                                        url: 'index.php?option=com_emundus&controller=files&task=checkforms&code='+code,
                                        dataType:'json',
                                        async: false,
                                        success: function(result) {
                                            if (result.status) {
                                                if (result.form == 1)
                                                    $('#form-exists').show();
                                                if (result.att == 1)
                                                    $('#att-exists').show();
                                                if (result.eval == 1)
                                                    $('#eval-exists').show();
                                                if (result.dec == 1)
                                                    $('#dec-exists').show();
                                                if (result.adm == 1)
                                                    $('#adm-exists').show();

                                                if (result.tag == 1) {
                                                    $('#em-export-opt option:disabled').removeAttr("disabled").attr("selected", "selected");
                                                    $('#em-export-opt').trigger("chosen:updated");
                                                }

                                            }
                                        },
                                        error: function(jqXHR) {
                                            console.log(jqXHR.responseText);
                                        }
                                    });

                                    $.ajax({
                                        type:'get',
                                        url: 'index.php?option=com_emundus&controller=files&task=getPDFCampaigns&code=' + code,
                                        data: {checkInput : checkInput},
                                        async: false,
                                        dataType:'json',
                                        success: function(result) {

                                            if (result.status) {
                                                $('#em-export-camp').append(result.html);
                                                $('#em-export-camp').chosen('destroy').chosen({width: "100%"});
                                                $('#camp').show();

                                                var camp = $("#em-export-camp").val();

                                                $.ajax({
                                                    type:'get',
                                                    url: 'index.php?option=com_emundus&controller=files&task=getformslist&code=' + code +'&camp=' + camp,
                                                    async: false,
                                                    dataType:'json',

                                                    success: function(result) {
                                                        if (result.status) {
                                                            prghtml = result.html;
                                                            $('#felts').append(result.html);
                                                            $('#felts').toggle(400);

                                                            $.ajax({
                                                                type:'get',
                                                                url: 'index.php?option=com_emundus&controller=files&task=getdoctype&code=' + code +'&camp=' + camp,
                                                                dataType:'json',
                                                                success: function(result) {

                                                                    if (result.status) {
                                                                        atthtml = result.html;
                                                                        $('#aelts').append(result.html);
                                                                        $('#aelts').toggle(400);
                                                                    }
                                                                },
                                                                error: function (jqXHR) {
                                                                    console.log(jqXHR.responseText);
                                                                }
                                                            });
                                                        }
                                                    },
                                                    error: function (jqXHR) {
                                                        console.log(jqXHR.responseText);
                                                    }
                                                });
                                            }
                                        },
                                        error: function (jqXHR) {
                                            console.log(jqXHR.responseText);
                                        }
                                    });
                                }
                                removeLoader();
                            }
                        },
                        error: function (jqXHR) {
                            console.log(jqXHR.responseText);
                        }
                    });

                    $('#em-export-prg').on('change', function() {
                        var code = $(this).val();

                        if (code != 0) {
                            $.ajax({
                                type:'get',
                                url: 'index.php?option=com_emundus&controller=files&task=checkforms&code='+code,
                                dataType:'json',

                                success: function(result) {
                                    if (result.status) {
                                        if (result.form == 1)
                                            $('#form-exists').show();
                                        if (result.att == 1)
                                            $('#att-exists').show();
                                        if (result.eval == 1)
                                            $('#eval-exists').show();
                                        if (result.dec == 1)
                                            $('#dec-exists').show();
                                        if (result.adm == 1)
                                            $('#adm-exists').show();

                                        if (result.tag == 1) {
                                            $('#em-export-opt option:disabled').removeAttr("disabled").attr("selected", "selected");
                                            $('#em-export-opt').trigger("chosen:updated");
                                        }

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
                                                    $('#em-export-camp').chosen('destroy').chosen({width: "100%"});
                                                    $('#camp').show();

                                                    var camp = $("#em-export-camp").val();

                                                    $.ajax({
                                                        type:'get',
                                                        url: 'index.php?option=com_emundus&controller=files&task=getformslist&code=' + code +'&camp=' + camp,
                                                        dataType:'json',
                                                        success: function(result) {
                                                            if (result.status) {

                                                                prghtml = result.html;
                                                                $('#felts').empty();
                                                                $('#felts').append(result.html);
                                                                $('#felts').show();

                                                                removeLoader();

                                                                $.ajax({
                                                                    type:'get',
                                                                    url: 'index.php?option=com_emundus&controller=files&task=getdoctype&code=' + code +'&camp=' + camp,
                                                                    dataType:'json',
                                                                    success: function(result) {
                                                                        if (result.status) {
                                                                            atthtml = result.html;
                                                                            $('#aelts').empty();
                                                                            $('#aelts').append(result.html);
                                                                            $('#aelts').show();
                                                                        }
                                                                    },
                                                                    error: function (jqXHR) {
                                                                        console.log(jqXHR.responseText);
                                                                    }
                                                                });
                                                            }
                                                        },
                                                        error: function (jqXHR) {
                                                            console.log(jqXHR.responseText);
                                                        }
                                                    });
                                                }
                                            },
                                            error: function (jqXHR) {
                                                console.log(jqXHR.responseText);
                                            }
                                        });
                                    }
                                },
                                error: function (jqXHR) {
                                    console.log(jqXHR.responseText);
                                }
                            });

                        } else {
                            $('#camp').hide();
                            $('#felts').hide();
                            $('#aelts').hide();
                            $('#felts').empty();
                            $('#aelts').empty();
                            removeLoader();
                        }
                    });

                    $('#em-export-camp').on('change', function() {

                        var code = $('#em-export-prg').val();

                        $.ajax({
                            type:'get',
                            url: 'index.php?option=com_emundus&controller=files&task=checkforms&code='+code,
                            dataType:'json',
                            success: function(result) {
                                if (result.status) {
                                    if (result.form == 1)
                                        $('#form-exists').show();
                                    if (result.att == 1)
                                        $('#att-exists').show();
                                    if (result.eval == 1)
                                        $('#eval-exists').show();
                                    if (result.dec == 1)
                                        $('#dec-exists').show();
                                    if (result.adm == 1)
                                        $('#adm-exists').show();

                                    if (result.tag == 1) {
                                        $('#em-export-opt option:disabled').removeAttr("disabled").attr("selected", "selected");
                                        $('#em-export-opt').trigger("chosen:updated");
                                    }

                                    if (code != 0) {
                                        var camp = $("#em-export-camp").val();

                                        if (camp != 0) {
                                            $.ajax({
                                                type:'get',
                                                url: 'index.php?option=com_emundus&controller=files&task=getformslist&code=' + code +'&camp=' + camp,
                                                dataType:'json',
                                                async: false,
                                                success: function(result) {
                                                    if (result.status) {

                                                        $('#felts-'+code+camp).parent('div').remove();
                                                        $('#felts-'+code+'0').parent('div').remove();
                                                        $('#felts').empty();
                                                        $('#felts').append(result.html);
                                                        $('#felts').show();

                                                        $.ajax({
                                                            type:'get',
                                                            url: 'index.php?option=com_emundus&controller=files&task=getdoctype&code=' + code +'&camp=' + camp,
                                                            dataType:'json',
                                                            success: function(result) {
                                                                if (result.status) {
                                                                    $('#aelts-'+code+camp).parent('div').remove();
                                                                    $('#aelts-'+code+'0').parent('div').remove();
                                                                    $('#aelts').append(result.html);
                                                                    $('#aelts').show();
                                                                }
                                                            },
                                                            error: function (jqXHR) {
                                                                console.log(jqXHR.responseText);
                                                            }
                                                        });
                                                    }
                                                },
                                                error: function (jqXHR) {
                                                    console.log(jqXHR.responseText);
                                                }
                                            });
                                        } else {
                                            $('[id^=felts-'+code+']').parent('div').remove();
                                            $('[id^=aelts-'+code+']').parent('div').remove();

                                            $('#felts').append(prghtml);
                                            $('#aelts').append(atthtml);
                                        }
                                    }
                                }
                            },
                            error: function (jqXHR) {
                                console.log(jqXHR.responseText);
                            }
                        });
                    });

                    $('#em-ex-forms').click(function(e){
                        if ($('#em-ex-forms').is(":checked")) {
                            $('[id^=felts-]:not([id*=-icon])').hide();
                            $('#felts [id$=-icon]').css('transform','rotate(-90deg)')
                            $('#felts input').attr('checked', false);
                        } else {
                            $('[id^=felts-]:not([id*=-icon])').show();
                            $('#felts [id$=-icon]').css('transform','rotate(0deg)')
                        }
                    });

                    $('#em-ex-attachment').click(function(e) {
                        if ($('#em-ex-attachment').is(":checked")) {
                            $('[id^=aelts-]:not([id*=-icon])').hide();
                            $('#aelts [id$=-icon]').css('transform','rotate(-90deg)')
                            $('#aelts input').attr('checked', false);
                        } else {
                            $('[id^=aelts-]').show();
                            $('#aelts [id$=-icon]').css('transform','rotate(0deg)')
                        }
                    });

                    $('#em-ex-assessment').click(function(e) {
                        if ($("#felts input[type=checkbox]").is(":checked") || $('#em-ex-forms').is(":checked") || $('#em-ex-assessment').is(":checked") || $('#em-ex-decision').is(":checked") || $('#em-ex-admission').is(":checked")) {
                            $('#exp-options').show();
                        } else {
                            $('#exp-options').hide();
                        }
                    });
                    $('#em-ex-decision').click(function(e) {
                        if ($("#felts input[type=checkbox]").is(":checked") || $('#em-ex-forms').is(":checked") || $('#em-ex-assessment').is(":checked") || $('#em-ex-decision').is(":checked") || $('#em-ex-admission').is(":checked")) {
                            $('#exp-options').show();
                        } else {
                            $('#exp-options').hide();
                        }
                    });
                    $('#em-ex-admission').click(function(e) {
                        if ($("#felts input[type=checkbox]").is(":checked") || $('#em-ex-forms').is(":checked") || $('#em-ex-assessment').is(":checked") || $('#em-ex-decision').is(":checked") || $('#em-ex-admission').is(":checked")) {
                            $('#exp-options').show();
                        } else {
                            $('#exp-options').hide();
                        }
                    });

                    $('#felts').click(function(e) {
                        if ($(".em-ex-check").is(":checked")) {
                            $('#em-ex-forms').attr('checked', false);
                        }
                    });

                    $('#aelts').click(function(e) {
                        if ($(".em-ex-check").is(":checked")) {
                            $('#em-ex-attachment').attr('checked', false);
                        }

                    });

                    $('#em-add-header').click(function(e) {
                        if ($("#em-add-header").is(":checked"))
                            $('#exp-opt').show();
                        else
                            $('#exp-opt').hide();
                    });

                    $('#em-export-prg').chosen({width: "100%"});
                    $('#em-export-camp').chosen({width: "100%"});
                    $('#em-export-opt').chosen({width: "100%"});
                    $('.pdform').css({width: "95%", 'margin': "auto", 'margin-top': "15px", 'border-radius':"4px"});
                },1000);


                //*** end zip view */
                break;

            // Export PDF;
            case 8 :
                title = 'COM_EMUNDUS_EXPORTS_CREATE_PDF';
                swal_container_class = 'em-export'
                swal_popup_class = 'em-w-100 em-h-100'
                swal_actions_class = 'em-actions-fixed'
                swal_confirm_button = 'COM_EMUNDUS_EXPORTS_EXPORT';

                html = '<div id="data" class="em-mt-32"></div>' +
                    '<div>' +
                    '<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8"> ' +
                    '<select class="modal-chzn-select" id="filt_save_pdf" name="filt_save_pdf" >'+
                    '<option value="0">'+Joomla.JText._('COM_EMUNDUS_FILTERS_PLEASE_SELECT_FILTER')+'</option>' +
                    '</select>'+
                    '<div class="em-flex-row em-flex-row-justify-end em-mt-8">' +
                    '<button class="em-tertiary-button em-w-auto" id="delPDFfilter" style="border-radius: 4px;" title="'+Joomla.JText._('COM_EMUNDUS_ACTIONS_DELETE')+'">'+Joomla.JText._('COM_EMUNDUS_ACTIONS_DELETE')+'</button>'+
                    '<button class="em-primary-button em-w-auto" id="savePDFfilter" title="'+Joomla.JText._('COM_EMUNDUS_FILES_SAVE_FILTER')+'">'+Joomla.JText._('COM_EMUNDUS_FILES_SAVE_FILTER')+'</button>'+
                    '</div>' +
                    '</div>' +
                    '</div>'+

                    '<div class="alert alert-dismissable alert-success em-alert-filter" id="sav-filter">'+
                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'+
                    '<strong>'+Joomla.JText._('COM_EMUNDUS_FILTERS_FILTER_SAVED')+'</strong>'+
                    '</div>'+

                    '<div class="alert alert-dismissable alert-success em-alert-filter" id="del-filter">'+
                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'+
                    '<strong>'+Joomla.JText._('COM_EMUNDUS_FILTERS_FILTER_DELETED')+'</strong>'+
                    '</div>'+

                    '<div class="alert alert-dismissable alert-danger em-alert-filter" id="err-filter">'+
                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'+
                    '<strong>'+Joomla.JText._('COM_EMUNDUS_ERROR_SQL_ERROR')+'</strong>'+
                    '</div>'+
                    '</div>';
                html += '<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8 em-mt-16">' +

                    '<div>' +
                    '<h5>'+Joomla.JText._('COM_EMUNDUS_CHOOSE_PRG')+'</h5>' +
                    '</div>' +

                    '<div class="em-mt-8">' +
                    '<select class="modal-chzn-select" name="em-export-prg" id="em-export-prg">' +
                    '<option value="0" >-- '+Joomla.JText._('COM_EMUNDUS_CHOOSE_PRG')+' --</option>' +
                    '</select>' +
                    '<br/><br/>' +

                    '<div id="camp" class="em-mt-4" style="display:none;">' +
                    '<select name="em-export-camp" id="em-export-camp" style="display: none;" class="modal-chzn-select">' +

                    '</select>' +
                    '</div>' +
                    '</div>' +
                    '</div>';

                html += '<div id="form-exists" style="display:none;" class="em-p-12-16 em-bg-neutral-200 em-border-radius-8 em-mt-16">'+
                    '<div>'+
                    '<div class="em-flex-row">' +
                    '<input class="em-ex-check" type="checkbox" value="forms" name="forms" id="em-ex-forms"/>' +
                    '<label for="em-ex-forms" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_EXPORTS_FORMS_PDF')+'</label>'+
                    '</div>' +
                    '</div>'+

                    '<div id="form-element" style="overflow:auto;display: none" class="em-mt-12"></div>' +
                    '</div>'+

                    '<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8 em-mt-16" id="att-exists" style="display:none;">'+
                    '<div>'+
                    '<div class="em-flex-row">' +
                    '<input class="em-ex-check" type="checkbox" value="attachment" name="attachment" id="em-ex-attachment"/>' +
                    '<label for="em-ex-attachment" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_EXPORTS_ATTACHMENT_PDF')+'</label>'+
                    '</div>' +
                    '</div>'+
                    '<div id="aelts" style="overflow:auto;display:none;"></div>'+
                    '</div>'+

                    '<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8 em-mt-16" id="eval-exists" style="display:none;">'+
                    '<div>'+
                    '<div class="em-flex-row">' +
                    '<input class="em-ex-check" type="checkbox"  value="assessment" name="assessment" id="em-ex-assessment"/>' +
                    '<label for="em-ex-assessment" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_EXPORTS_ASSESSMENT_PDF')+'</label>'+
                    '</div>'+
                    '</div>'+
                    '</div>'+

                    '<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8 em-mt-16" id="dec-exists" style="display:none;">'+
                    '<div>'+
                    '<div class="em-flex-row">' +
                    '<input class="em-ex-check" type="checkbox"  value="decision" name="decision" id="em-ex-decision"/>' +
                    '<label for="em-ex-decision" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_EXPORTS_DECISION_PDF')+'</label>'+
                    '</div>' +
                    '</div>'+
                    '</div>'+

                    '<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8 em-mt-16" id="adm-exists" style="display:none;">'+
                    '<div>'+
                    '<div class="em-flex-row">' +
                    '<input class="em-ex-check" type="checkbox"  value="admission" name="admission" id="em-ex-admission"/>' +
                    '<label for="em-ex-admission" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_EXPORTS_ADMISSION_PDF')+'</label>'+
                    '</div>' +
                    '</div>'+
                    '</div>';

                html += '<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8 em-mt-16" id="adm-exists" style="display:none;">'+
                    '<div>'+
                    '<div class="em-flex-row">' +
                    '<input class="em-ex-check" type="checkbox"  value="admission" name="admission" id="em-ex-admission"/>' +
                    '<label for="em-ex-admission" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_EXPORTS_ADMISSION_PDF')+'</label>'+
                    '</div>' +
                    '</div>'+
                    '</div>';

                html += '<div class="em-p-12-16 em-bg-neutral-200 em-border-radius-8 em-mt-16" id="em-options">'+
                    '<div class="em-flex-row">' +
                    '<input class="em-ex-check" type="checkbox"  value="header" name="em-add-header" id="em-add-header" checked />' +
                    '<label for="em-add-header" class="em-mb-0-important">'+Joomla.JText._('COM_EMUNDUS_EXPORTS_ADD_HEADER')+'</label>'+
                    '</div>' +
                    '<div id="exp-opt">'+
                    '<label ><font color="black">'+Joomla.JText._('COM_EMUNDUS_EXPORTS_PDF_OPTIONS')+'</font></label>'+
                    '<select class="modal-chzn-select" name="em-export-opt" id="em-export-opt" multiple >'+
                    '<option  value="aid" selected>'+Joomla.JText._('COM_EMUNDUS_APPLICATION_APPLICANT')+'</option>' +
                    '<option  value="afnum" selected>'+Joomla.JText._('COM_EMUNDUS_FNUM')+'</option>' +
                    '<option  value="aemail" selected>'+Joomla.JText._('COM_EMUNDUS_EMAIL')+'</option>' +
                    '<option  value="aapp-sent" selected>'+Joomla.JText._('COM_EMUNDUS_APPLICATION_SENT_ON')+'</option>' +
                    '<option  value="adoc-print" selected>'+Joomla.JText._('COM_EMUNDUS_APPLICATION_DOCUMENT_PRINTED_ON')+'</option>' +
                    '<option  value="tags" disabled>'+Joomla.JText._('COM_EMUNDUS_EXPORTS_PDF_TAGS')+'</option>' +
                    '<option  value="status" selected>'+Joomla.JText._('COM_EMUNDUS_EXPORTS_PDF_STATUS')+'</option>' +
                    '<option  value="upload" selected>'+Joomla.JText._('COM_EMUNDUS_ATTACHMENTS_FILES_UPLOADED')+'</option>' +
                    '</select>'+
                    '</div></div>';

                $.ajax({
                    type:'post',
                    url: 'index.php?option=com_emundus&controller=files&task=getPDFProgrammes',
                    data: {checkInput : checkInput},
                    dataType:'json',
                    success: function(result) {
                        addLoader('.swal2-popup');
                        $('#felts').empty(); /// error here
                        if (result.status) {
                            /// get all pdf models by user_id
                            $.ajax({
                                type: 'get',
                                url: 'index.php?option=com_emundus&controller=files&task=getAllExportPdfFilter',
                                dataType: 'json',
                                success: function(result) {
                                    if(result.status) {
                                        if(result.filter !== null && typeof result.filter !== 'undefined') {
                                            var models = result.filter;
                                            const filterSavePdf = $('#filt_save_pdf');

                                            models.forEach(model => {
                                                filterSavePdf.append('<option value="' + model.id + '">' + model.name + '</option>');
                                            });

                                            filterSavePdf.chosen('destroy').chosen({width: "100%"});
                                            filterSavePdf.trigger('chosen:updated');
                                            filterSavePdf.trigger('change');
                                        }
                                    }

                                    removeLoader();
                                }, error: function(jqXHR) {
                                    console.log(jqXHR.responseText);
                                    removeLoader();
                                }
                            });

                            $('#em-export-prg').append(result.html);
                            $('#em-export-prg').chosen('destroy').chosen({width: "100%"});

                            let filtSavePdf = $('#filt_save_pdf');

                            filtSavePdf.on('change', async function() {
                                addLoader('.swal2-popup');
                                var model = filtSavePdf.val();

                                $('#model-err-pdf').remove();
                                $('.modal-header').before('<div id="loadingimg-campaign"><img src="'+loading+'" alt="loading"/></div>');

                                if(model != 0) {
                                    let programSelector = $('#em-export-prg');
                                    const selectedProgram = programSelector.val();

                                    if (selectedProgram == 0) {
                                        removeLoader();
                                        // select id="filt_save_pdf" and add sibling saying to select a program
                                        programSelector.after('<span id="model-err-pdf" class="error em-red-500-color">Please select a program</span>');
                                        filtSavePdf.val(0);
                                        filtSavePdf.trigger('chosen:updated');
                                        filtSavePdf.trigger('liszt:updated');

                                        setTimeout(() => {
                                            const errorTxt = document.getElementById('model-err-pdf');

                                            if (errorTxt) {
                                                errorTxt.remove();
                                            }
                                        }, 5000);

                                        return;
                                    }

                                    // show #form-div
                                    $('#form-exists').show();

                                    // show #document-div
                                    $('#att-exists').show();

                                    // show #eval-div
                                    $('#eval-exists').show();

                                    // show #document-div
                                    $('#dec-exists').show();

                                    // show #admission-div
                                    $('#adm-exists').show();

                                    let result = {status: false};


                                    result = await getExportPDFModel(model);
                                    if (result.status) {
                                        var constraints = result.filter.constraints;
                                        var json = JSON.parse(constraints);
                                        var progCode = json.pdffilter.code;
                                        var campCode = json.pdffilter.camp;

                                        /// case 1 :: one program
                                        if($("#em-export-prg option").length == 2) {
                                            // if only program is preselected --> check the camp
                                            if($("#em-export-camp option[value='" + campCode + "']").length > 0 === true) {
                                                var elements = json.pdffilter.elements;
                                                var checkAllGroups = json.pdffilter.checkAllGroups;
                                                var checkAllTables = json.pdffilter.checkAllTables;
                                                var attachments = json.pdffilter.attachments;


                                                let emExportCamp = $('#em-export-camp');
                                                emExportCamp.val(campCode);
                                                emExportCamp.trigger("chosen:updated");
                                                emExportCamp.trigger("change");

                                                if (elements[0] !== "") {
                                                    $.ajax({
                                                        type: 'post',
                                                        url: 'index.php?option=com_emundus&controller=files&task=getfabrikdatabyelements',
                                                        dataType: 'JSON',
                                                        data: {elts: elements.toString()},
                                                        async: false,
                                                        success: function (returnData) {
                                                            // build profile(s)
                                                            var profiles = returnData.fabrik_data.profiles;
                                                            profiles.forEach((profile) => {
                                                                checkElement('#felts'+profile.id).then((selector) => {
                                                                    $('#' + selector.id).show();        // show felts
                                                                    removeLoader();

                                                                    // uncheck all checkbox of each felts
                                                                    if($('#form-exists input:checked').length > 0) {
                                                                        $('#form-exists input:checked').prop('checked', false);
                                                                    }

                                                                    // render tables
                                                                    if (checkAllTables !== null || checkAllTables !== undefined || checkAllTables[0] !== "") {
                                                                        checkAllTables.forEach(tbl => {
                                                                            $('#emundus_checkall_tbl_' + tbl).attr('checked', true);
                                                                        })
                                                                    }

                                                                    if (checkAllGroups !== null || checkAllGroups !== undefined || checkAllGroups[0] !== "") {
                                                                        checkAllGroups.forEach(grp => {
                                                                            $('#emundus_checkall_grp_' + grp).attr('checked', true);
                                                                        })
                                                                    }

                                                                    if (elements !== null || elements !== undefined || elements[0] !== "") {
                                                                        elements.forEach(elt => {
                                                                            $('#emundus_elm_' + elt).attr('checked', true);
                                                                        })
                                                                    }
                                                                });
                                                            })
                                                        }
                                                    })
                                                }

                                                /// render attachments
                                                checkElement('#aelts-' + progCode + campCode).then((selector) => {
                                                    /// show #aelts
                                                    $('#' + selector.id).show();

                                                    /// check to selected elements
                                                    attachments.forEach((attachmentToCheck) => {
                                                        $('[id="' + attachmentToCheck + '"]').prop('checked', true);
                                                    });
                                                });

                                                if (json.pdffilter.assessment === '1') {
                                                    document.getElementById('em-ex-assessment').checked = true;
                                                } else {
                                                    document.getElementById('em-ex-assessment').checked = false;
                                                }

                                                if (json.pdffilter.decision === '1') {
                                                    document.getElementById('em-ex-decision').checked = true;
                                                } else {
                                                    document.getElementById('em-ex-assessment').checked = false;
                                                }

                                                if (json.pdffilter.admission === '1') {
                                                    document.getElementById('em-ex-admission').checked = true;
                                                } else {
                                                    document.getElementById('em-ex-assessment').checked = false;
                                                }

                                                if (json.pdffilter.checkAllAttachments === '1') {
                                                    document.getElementById('em-ex-attachment').checked = true;
                                                } else {
                                                    document.getElementById('em-ex-attachment').checked = false;
                                                }
                                            } else {
                                                $('#loadingimg-campaign').remove();
                                                $('#filt_save_pdf_chosen').append('<div id="model-err-pdf" style="color: red">' + Joomla.JText._('COM_EMUNDUS_MODEL_ERR') + '</div>');
                                            }
                                        }
                                        /// case 2 :: many programs
                                        else if($("#em-export-prg option").length > 2)
                                        {
                                            if ($("#em-export-prg option[value='" + progCode + "']").length > 0 === true) {
                                                setModel(json);      /// if prog is found --> keep going
                                            } else {
                                                $('#loadingimg-campaign').remove();
                                                $('#filt_save_pdf_chosen').append('<div id="model-err-pdf" style="color: red">' + Joomla.JText._('COM_EMUNDUS_MODEL_ERR') + '</div>');
                                            }
                                        }

                                        removeLoader();
                                    }
                                } else {
                                    // set "unselect" program
                                    $('#loadingimg-campaign').remove();
                                    $('#em-export-prg option:selected').removeAttr("selected");
                                    $('#em-export-prg').trigger('chosen:updated');

                                    // hide #camp --> set "unselect" campaign
                                    $('#camp').hide();
                                    $('#em-export-camp option:selected').removeAttr("selected");
                                    $('#em-export-camp').trigger('chosen:updated');

                                    // hide #form-div and reset #form-element
                                    $('#form-exists').hide();
                                    $('#form-element').hide();

                                    // hide #att-div and reset #aelts (documents)
                                    $('#att-exists').hide();
                                    $('#aelts').empty();

                                    // hide #eval-div
                                    $('#eval-exists').hide();

                                    // hide #decision-div
                                    $('#dec-exists').hide();

                                    // hide #admission-div
                                    $('#adm-exists').hide();

                                    removeLoader();
                                }
                            });

                            $('#em-export-prg').on('change', function() {
                                addLoader('.swal2-popup');
                                $('#form-element').hide();
                                $('#em-ex-forms').prop('checked', false);
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

                                                $('#em-export-camp').append(result.html);
                                                $('#em-export-camp').chosen('destroy').chosen({width: "100%"});
                                                $('#camp').show();

                                                var camp = $("#em-export-camp").val();

                                                var firstOption = $("#em-export-camp").prop("selectedIndex", 0).val();

                                                /// check this firstOption by default
                                                $('#em-export-camp option[value="' + firstOption + '"]').prop("selected", true);
                                                $('#em-export-camp').trigger("chosen:updated");
                                                $('#em-export-camp').trigger("change");

                                                if (access.form == 1) {
                                                    checkElement('[id^=felts]').then((selector) => {
                                                        $('#emundus_checkall').trigger('click');
                                                        $('#em-ex-forms').trigger('click');
                                                    })
                                                }
                                            }
                                        },
                                        error: function (jqXHR) {
                                            console.log(jqXHR.responseText);
                                        }
                                    });

                                } else {
                                    $('#camp').hide();
                                    $('#felts').hide();
                                    $('#aelts').hide();
                                    $('#felts').empty();
                                    $('#aelts').empty();
                                }
                            });


                            $('#em-export-camp').on('change', function() {
                                addLoader('.swal2-popup');
                                /// when a new campaign is loaded, clean the existing formelement
                                $('[id^=form-element]').empty();
                                $('[id^=aelts]').empty();
                                var code = $('#em-export-prg').val();           // get value of chosen program
                                var camp = $("#em-export-camp").val();          // get value of chosen campaign

                                if (code != 0 && camp != 0) {
                                    $('.modal-header').before('<div id="loadingimg-campaign"><img src="'+loading+'" alt="loading"/></div>');
                                    // step 1 --> grab all profiles by campaigns
                                    $.ajax({
                                        type: 'get',
                                        url: 'index.php?option=com_emundus&controller=export&task=getprofiles',
                                        dataType: 'json',
                                        data: {
                                            camp: camp,
                                            code: code,
                                        },
                                        async: false,
                                        success: function(result) {
                                            if (result.scalar !== undefined && result.scalar === false) {
                                                $('#loadingimg-campaign').remove();
                                                return;
                                            }

                                            var profile_labels = Object.values(result.profile_label);
                                            var profile_ids = Object.values(result.profile_id);
                                            var profile_menus = Object.values(result.profile_menu_type);

                                            for (index = 0; index < profile_labels.length; index++) {
                                                var menu = profile_menus[index];
                                                var id = profile_ids[index];
                                                var labels = profile_labels[index];
                                                $.ajax({
                                                    type: 'get',
                                                    url: 'index.php?option=com_emundus&controller=files&task=getformelem',
                                                    data: {
                                                        camp: camp,
                                                        code: code,
                                                        profile: menu,
                                                    },
                                                    dataType: 'json',
                                                    async: false,
                                                    success: function (data) {
                                                        $.ajax({
                                                            type: 'get',
                                                            url: 'index.php?option=com_emundus&view=export_select_columns&format=raw&camp=' + camp + '&code=' + code + '&profile=' + menu,
                                                            async: false,
                                                            success: function (answer) {
                                                                /// using Virtual DOM to render DOM --> append #formelement and #felts
                                                                $('#form-element').show();
                                                                $('#form-element').append(
                                                                    '<div class="em-flex-row em-pointer"  id="showelements_'+id+'">' +
                                                                    '<span title="'+Joomla.JText._('COM_EMUNDUS_SHOW_ELEMENTS')+'" id="showelements_'+id+'_icon" class="material-icons em-mr-4" style="transform: rotate(-90deg)">expand_more</span>' +
                                                                    '<p>'+labels+'</p>'+
                                                                    '</div>' +
                                                                    '<div id="felts' + id + '" class="em-p-16" style="overflow:auto; display: none"/>' +
                                                                    '</div>'
                                                                );
                                                                $('#felts' + id).append(answer);
                                                                removeLoader();
                                                            }
                                                        })

                                                    }
                                                })
                                            }

                                            $.ajax({
                                                type:'get',
                                                url: 'index.php?option=com_emundus&controller=files&task=checkforms&code='+code,
                                                dataType:'json',
                                                async: false,
                                                success: function(result) {
                                                    if (result.status) {
                                                        if (result.form == 1)
                                                            $('#form-exists').show();
                                                        if (result.att == 1)
                                                            $('#att-exists').show();
                                                        if (result.eval == 1)
                                                            $('#eval-exists').show();
                                                        if (result.dec == 1)
                                                            $('#dec-exists').show();
                                                        if (result.adm == 1)
                                                            $('#adm-exists').show();

                                                        if (result.tag == 1) {
                                                            $('#em-export-opt option:disabled').removeAttr("disabled").attr("selected", "selected");
                                                            $('#em-export-opt').trigger("chosen:updated");
                                                        }

                                                        var camp = $("#em-export-camp").val();

                                                        var profiles = [];
                                                        var felts = $('[id^=felts]');

                                                        felts.each(function(e) {
                                                            profiles.push($(this).attr('id').split('felts')[1]);
                                                        })

                                                        if (camp != 0) {
                                                            $.ajax({
                                                                type:'post',
                                                                url: 'index.php?option=com_emundus&controller=files&task=getdoctype&code=' + code +'&camp=' + camp,
                                                                dataType:'json',
                                                                async: false,
                                                                data: {
                                                                    profiles: profiles
                                                                },
                                                                success: function(result) {
                                                                    if (result.status) {
                                                                        $('#aelts-'+code+camp).parent('div').remove();
                                                                        $('#aelts-'+code+'0').parent('div').remove();
                                                                        $('#aelts').append(result.html);
                                                                        $('#aelts').show();
                                                                    }
                                                                },
                                                                error: function(jqXHR) {
                                                                    console.log(jqXHR.responseText);
                                                                }
                                                            });
                                                        } else {
                                                            $('[id^=aelts-'+code+']').parent('div').remove();
                                                            $('#aelts').append(atthtml);
                                                        }

                                                        removeLoader();
                                                    }
                                                },
                                                error: function (jqXHR) {
                                                    console.log(jqXHR.responseText);
                                                }
                                            });
                                        }
                                    })
                                } else {
                                    /// if one of two conditions as above is not correct --> hide the div "felts"
                                    $('[id^=form-element]').hide();
                                    $('[id^=aelts]').hide();
                                    removeLoader();
                                }
                            });

                            $('#em-ex-forms').click(function(e) {
                                var feltsObj = $('[id^=felts]');
                                var feltsArr = Array.prototype.slice.call(feltsObj);

                                if ($('#em-ex-forms').is(":checked")){
                                    // check all inputs of all felts
                                    feltsArr.forEach(flt => {
                                        var id = flt.id;
                                        $('#'+id+" :input").attr('checked', true);
                                    })
                                } else {
                                    // uncheck all inputs of all felts
                                    feltsArr.forEach(flt => {
                                        var id = flt.id;
                                        $('#'+id+" :input").attr('checked', false);
                                    })
                                }
                            });

                            $('#em-add-header').click(function(e) {
                                if ($("#em-add-header").is(":checked"))
                                    $('#exp-opt').show();
                                else
                                    $('#exp-opt').hide();
                            });

                            var id = ($('#felts').find('input').checked = true);


                            /// save pdf filter
                            $('#savePDFfilter').on('click', function() {
                                /// find all childs of #felts which has the name 'emundus_elm'

                                var code = $('#em-export-prg').val();
                                var camp = $('#em-export-camp').val();
                                var proglabel = $("#em-export-prg option:selected").text();
                                var camplabel = $("#em-export-camp option:selected").text();

                                // save check all tables
                                var checkAllTables = [];
                                var tblElementsObject = $('[id^=emundus_checkall_tbl_]');
                                var tblElementsArray = Array.prototype.slice.call(tblElementsObject);
                                tblElementsArray.forEach(tbl => {
                                    if(tbl.checked == true) {
                                        var id = tbl.id.split('emundus_checkall_tbl_')[1];
                                        checkAllTables.push(id);
                                    }
                                });

                                /// save check all groups
                                var checkAllGroups = [];
                                var grpElementsObject = $('[id^=emundus_checkall_grp_]');
                                var grpElementsArray = Array.prototype.slice.call(grpElementsObject);

                                grpElementsArray.forEach(grp => {
                                    if(grp.checked == true) {
                                        var id = grp.id.split('emundus_checkall_grp_')[1];
                                        checkAllGroups.push(id);
                                    }
                                });


                                /// save all elements
                                var elements = [];
                                var eltsObject = $('[id^=emundus_elm_]');
                                var eltsArray = Array.prototype.slice.call(eltsObject);

                                eltsArray.forEach(elt => {
                                    if (elt.checked == true) {
                                        elements.push(elt.value);
                                    }
                                })

                                ///////////////
                                /// save all profiles
                                var profiles = [];
                                $('[id^=felts]').each(function (flt) {
                                    if($(this).find($('[id^=emundus_elm_]')).is(':checked') == true) {
                                        var id = $(this).attr('id').split('felts')[1];
                                        profiles.push(id);
                                    }
                                })

                                /// save all tables
                                var tables = [];
                                $('[id^=emundus_table_]').each(function (flt) {
                                    if($(this).find($('[id^=emundus_elm_]')).is(':checked') == true) {
                                        var id = $(this).attr('id').split('emundus_table_')[1];
                                        tables.push(id);
                                    }
                                })

                                /// save all groups
                                var groups = [];
                                $('[id^=emundus_grp_]').each(function (flt) {
                                    if($(this).find($('[id^=emundus_elm_]')).is(':checked') == true) {
                                        var id = $(this).attr('id').split('emundus_grp_')[1];
                                        groups.push(id);
                                    }
                                });


                                var headers = [];
                                var headersObject = document.getElementById('em-export-opt');
                                var headersArray = Array.prototype.slice.call(headersObject);

                                if($('#em-add-header').is(":checked") == false || $('#em-export-opt option:selected').length == 0) {
                                    headers = "0";
                                } else {
                                    headersArray.forEach(header => {
                                        if (header.selected == true)
                                            headers.push(header.value);
                                    });
                                }

                                // save all attachments id
                                var attachments = [];
                                $('#aelts input:checked').each(function() {
                                    attachments.push($(this).val());
                                });

                                var is_assessment = 0;
                                var is_decision = 0;
                                var is_admission = 0;

                                if ($('#em-ex-assessment').is(":checked"))
                                    is_assessment = 1;
                                if ($('#em-ex-decision').is(":checked"))
                                    is_decision = 1;
                                if ($('#em-ex-admission').is(":checked"))
                                    is_admission = 1;

                                var params = {
                                    'code':code,
                                    'camp': camp,
                                    'proglabel': proglabel,
                                    'camplabel': camplabel,

                                    'profiles': profiles.length > 0 ? profiles : [""],
                                    'tables': tables.length > 0 ? tables : [""],
                                    'groups': groups.length > 0 ? groups : [""],
                                    'elements': elements.length > 0 ? elements : [""],

                                    'checkAllTables': checkAllTables.length > 0 ? checkAllTables : [""],
                                    'checkAllGroups': checkAllGroups.length > 0 ? checkAllGroups : [""],
                                    'headers': headers.length > 0 ? headers : [""],

                                    'checkAllAttachments': $('#em-ex-attachment').is(":checked") ? 1 : 0,
                                    'attachments': attachments.length > 0 ? attachments : [""],
                                    'assessment': is_assessment,
                                    'admission': is_admission,
                                    'decision': is_decision,
                                };

                                /// jquery remove all empty data before sending
                                var filName = prompt(filterName);
                                if (filName != null && camp != 0 & code != 0) {
                                    $.ajax({
                                        type: 'post',
                                        url: 'index.php?option=com_emundus&controller=files&task=savePdfFilter&Itemid=' + itemId,
                                        dataType: 'JSON',
                                        data: ({
                                            params: params,
                                            filt_name: filName,
                                            mode: 'pdf',
                                        }),
                                        success: function (result) {
                                            if(result.status) {
                                                $('#filt_save_pdf').append('<option value="' + result.filter.id + '" selected="">' + result.filter.name + '</option>');
                                                $('#filt_save_pdf').trigger("chosen:updated");
                                                $('#sav-filter').show();

                                                setTimeout(function(e) {
                                                    $('#sav-filter').hide();
                                                }, 600);

                                            }
                                            else {
                                                $('#err-filter').show();
                                                setTimeout(function(e) {
                                                    $('#err-filter').hide();
                                                }, 600);

                                            }
                                        }, error: function(jqXHR) {
                                            console.log(jqXHR.responseText);
                                        }
                                    })
                                } else {
                                    alert(filterEmpty);
                                    $('#err-filter').show();
                                    setTimeout(function(e) {
                                        $('#err-filter').hide();
                                    }, 600);

                                    filName = prompt(filterName, "name");
                                }
                            });

                            /// delete pdf filter
                            $('#delPDFfilter').on("click", function() {
                                var filter = $('#filt_save_pdf').val();
                                $.ajax({
                                    type: 'post',
                                    url: 'index.php?option=com_emundus&controller=files&task=deletePdfFilter',
                                    dataType: 'JSON',
                                    data: { fid : filter },
                                    success: function(result) {
                                        if(result.status) {
                                            $("#filt_save_pdf option:selected").remove();
                                            $("#filt_save_pdf option:selected").trigger("chosen:updated");
                                            $('#del-filter').show();
                                            setTimeout(function (e) {
                                                $('#del-filter').hide();
                                            }, 600);

                                            //change class of button "showelements_"
                                            var showFeltsObj = $('[id^=showelements_]');
                                            var showFeltsArr = Array.prototype.slice.call(showFeltsObj);

                                            showFeltsArr.forEach(sftl => {
                                                var id = $(sftl).attr('id').split('showelements_')[1];

                                                // set class of button
                                                $('#' + sftl.id + ' > span').attr('class', 'glyphicon glyphicon-plus');
                                                $('#' + sftl.id).attr('class','btn-xs btn btn-info');

                                                // hide felts
                                                $('#felts' + id + " :input").attr('checked', false);
                                                $('#felts' + id).hide();
                                            })

                                        } else {
                                            $('#err-filter').show();
                                            setTimeout(function(e) {
                                                $('#err-filter').hide();
                                            }, 600);
                                        }
                                    }, error: function(jqXHR) {
                                        console.log(jqXHR.responseText);
                                    }
                                })
                            });
                        }
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                });


                break;

            // Export to Aurion
            case 33 :
                title = 'COM_EMUNDUS_AURION_EXPORT';

                var regex = /type=\w+/gi;

                var exportType = url.match(regex)[0].split('=')[1];
                preconfirm = "return {state: $('#em-action-state').val(), tag: $('#em-action-tag').val(), type: $('#data').attr('data-export-type')}";

                html = '<div id="data" data-export-type="'+exportType+'" class="em-mt-32">';
                html += '<div class="select-export-status">' +
                    '<label>'+Joomla.JText._('EXPORT_CHANGE_STATUS')+'</label>' +
                    '<div id="change-status">' +
                    '<div class="em-flex-row em-mb-4"><input type="radio" name="export-status" id="ex-yes" value="yes"> <label class="em-mb-0-important" for="ex-yes">' + Joomla.JText._('JYES') + '</label></div>' +
                    '<div class="em-flex-row"><input type="radio" name="export-status" id="ex-no" value="no"> <label class="em-mb-0-important" for="ex-no">' + Joomla.JText._('JNO') + '</label></div>' +
                    '</div></div></div>';

                $.ajax({
                    type:'get',
                    url: 'index.php?option=com_emundus&controller=files&task=getstate',
                    dataType:'json',
                    success: function(result) {

                        var status = '<div id="em-action-export-state" class="em-mt-16"><label>'+result.state+'</label><select class="col-lg-12 modal-chzn-select" data-placeholder="'+result.select_state+'" name="em-action-state" id="em-action-state" value=""><option value="">' + Joomla.JText._('PLEASE_SELECT') + '</option>';

                        for (var i in result.states) {
                            if (isNaN(parseInt(i)))
                                break;
                            status += '<option value="'+result.states[i].step+'" >'+result.states[i].value+'</option>';
                        }
                        status += '</select></div>';
                        $('#data').append(status);
                        $("#em-action-export-state").hide();


                        $('#data').append(
                            '<div class="select-export-tag em-mt-16">' +
                            '<label>'+Joomla.JText._('EXPORT_SET_TAG')+'</label>' +
                            '<div id="set-export-tag">' +
                            '<div class="em-flex-row em-mb-4"><input type="radio" name="export-tag" id="tag-yes" value="yes"> <label class="em-mb-0-important" for="tag-yes">' + Joomla.JText._('JYES') + '</label></div>' +
                            '<div class="em-flex-row"><input type="radio" name="export-tag" id="tag-no" value="no"> <label class="em-mb-0-important" for="tag-no">' + Joomla.JText._('JNO') + '</label></div>' +
                            '</div></div>');


                        $.ajax({
                            type:'get',
                            url:'index.php?option=com_emundus&controller=files&task=gettags',
                            dataType:'json',
                            success: function(result) {

                                var tags = '<div id="em-action-export-tag" class="em-mt-16"><label>'+result.tag+'</label><select class="col-lg-12 modal-chzn-select" name="em-action-tag" id="em-action-tag" multiple="multiple" >';

                                for (var i in result.tags) {
                                    if (isNaN(parseInt(i)))
                                        break;
                                    tags += '<option value="'+result.tags[i].id+'" >'+result.tags[i].label+'</option>';
                                }
                                tags += '</select></div>';
                                $('#data').append(tags);
                                $('.modal-chzn-select').chosen({width:'100%',search_contains: true});
                                $("#em-action-export-tag").hide();

                                $('#change-status input[name=export-status]').on('change', function(){
                                    $('#em-action-state').val('');
                                    $("#em-action-export-state .modal-chzn-select").val('').trigger("chosen:updated");
                                    if(this.value == "yes") {
                                        $("#em-action-export-state").show();
                                    }
                                    else {
                                        $("#em-action-export-state").hide();
                                    }
                                });

                                $('#set-export-tag input[name=export-tag]').on('change', function(){
                                    $("#em-action-export-tag .modal-chzn-select").val('').trigger("chosen:updated");
                                    if(this.value == "yes") {
                                        $("#em-action-export-tag").show();
                                    }
                                    else {
                                        $("#em-action-export-tag").hide();
                                    }
                                });

                                $('#em-action-tag').on('change', function() {
                                    if ($(this).val() != null)
                                        $('#success-ok').removeAttr("disabled");
                                    else
                                        $('#success-ok').attr("disabled", "disabled");
                                });
                            },
                            error: function(jqXHR) {
                                console.log(jqXHR.responseText);
                            }
                        });



                    },
                    error: function (jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                });
                break;

            // Send an email
            case 9:
                multipleSteps = true;
                break;

            // Add comments
            case 10:
                title = 'COM_EMUNDUS_COMMENTS_ADD_COMMENT';
                html = '<form>' +
                    '<input placeholder="'+Joomla.JText._('TITLE')+'" class="form-control" id="comment-title" type="text" value="" name="comment-title"/>' +
                    '<textarea placeholder="'+Joomla.JText._('ENTER_COMMENT')+'" class="form-control" style="height:250px !important; margin-left:0px !important;"  id="comment-body"></textarea>' +
                    '</form>';

                preconfirm = "var comment = $('#comment-body').val();if (comment.length == 0) {Swal.showValidationMessage(Joomla.JText._('COM_EMUNDUS_COMMENTS_ERROR_PLEASE_COMPLETE'))}"
                break;

            // Define access on file(s)
            case 11:
                addLoader();

                fnums = getUserCheckArray();

                title = 'COM_EMUNDUS_ACCESS_ACCESS_FILE';

                preconfirm = "var groupeEval = $('#em-access-groups-eval').val();var evaluators = $('#em-access-evals').val();if ((groupeEval == undefined ||  groupeEval.length == 0 ) && (evaluators == undefined || evaluators.length == 0)) {Swal.showValidationMessage(Joomla.JText._('COM_EMUNDUS_ACCESS_ERROR_REQUIRED'))}"

                await $.ajax({
                    type:'POST',
                    url:'index.php?option=com_emundus&view=files&format=raw&layout=access',
                    data: {
                        fnums: fnums
                    },
                    dataType:'html',
                    success: function(result) {
                        html = result;

                        removeLoader();
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                });
                break;

            // Update status of file(s)
            case 13:
                addLoader();

                await $.ajax({
                    type:'get',
                    url: url,
                    dataType:'json',
                    success: function(result) {
                        title = 'COM_EMUNDUS_APPLICATION_VALIDATE_CHANGE_STATUT'

                        // Build HTML for SweetAlert
                        html = '<div class="em-flex-column em-flex-align-start"><label>'+result.state+'</label><select class="modal-chzn-select" data-placeholder="'+result.select_state+'" name="em-action-state" id="em-action-state" value="">';

                        for (var i in result.states) {
                            if (isNaN(parseInt(i)))
                                break;
                            html += '<option value="'+result.states[i].step+'" >'+result.states[i].value+'</option>';
                        }
                        html += '</select></div>';

                        removeLoader();
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                });
                break;

            // Add/delete tags of file(s)
            case 14:
                addLoader();

                title = 'COM_EMUNDUS_APPLICATION_ADD_TAGS';

                await $.ajax({
                    type:'get',
                    url:url,
                    dataType:'json',
                    success: function(result) {
                        tags = result;

                        html = '<form>'+
                            '<div class="em-flex-row"><input type="radio" name="em-tags" id="em-tags-add" value="0" checked><label for="em-tags-add" class="em-mb-0-important">' +Joomla.JText._('COM_EMUNDUS_APPLICATION_ADD_TAGS')+'</label></div>' +
                            '<div class="em-flex-row"><input type="radio" name="em-tags" id="em-tags-delete" value="1"><label for="em-tags-delete" class="em-mb-0-important">' +Joomla.JText._('COM_EMUNDUS_TAGS_DELETE_TAGS')+'</label></div>' +
                            '</form>';

                        if(result.show_tags_category == 1) {
                            html += '<div><label>' +Joomla.JText._('COM_EMUNDUS_TAGS_CATEGORIES')+ '</label>' +
                                '<select class="modal-chzn-select" name="em-action-tag-category" id="em-action-tag-category">';

                            var tag_categories = [...new Set(result.tags.filter(tag => {
                                return (typeof tag.category == 'string' && tag.category !== '')
                            }).map(cat => cat.category))];

                            html += '<option value="">'+Joomla.JText._('PLEASE_SELECT')+'</option>';

                            tag_categories.forEach((tag_category) => {
                                html += '<option value="'+tag_category+'">'+tag_category+'</option>';
                            });

                            html += '</select></div>';
                        }

                        html += '<div class="em-mt-16"><label>'+Joomla.JText._('COM_EMUNDUS_APPLICATION_TAG')+'</label>' +
                            '<select class="modal-chzn-select" name="em-action-tag" id="em-action-tag" multiple="multiple">';

                        /** Create tags dropdown **/
                        result.tags.forEach((tag) => {
                            html += '<option value="'+tag.id+'">'+tag.label+'</option>';
                        });
                        html += '</select></div>';

                        preconfirm = "return document.querySelector('input[name=em-tags]:checked').value ";

                        removeLoader();

                        /***
                         * On Category change
                         */
                        $('#em-action-tag-category').chosen({search_contains: true}).change(function() {

                            var cat = $(this).val();
                            if (cat) {
                                var allowed_cats = result.tags.filter((tag) => {
                                    if(tag.category != cat) {
                                        return tag.id;
                                    }
                                }).map((item) => {
                                    return item.id;
                                });

                                document.querySelectorAll('#em-action-tag option').forEach((option) => {
                                    if (!allowed_cats.contains(option.value)) {
                                        option.disabled = false;
                                        option.show();
                                    } else {
                                        option.disabled = true;
                                        option.hide();
                                    }
                                })
                            } else {
                                document.querySelectorAll('#em-action-tag option').forEach((option) => {
                                    option.disabled = false;
                                    option.show();
                                })
                            }
                            $("#em-action-tag").val('').trigger("chosen:updated");
                        });

                        /***
                         * On Tag change
                         */
                        $('#em-action-tag').on('change', function() {
                            if ($(this).val() != null)
                                $('#success-ok').removeAttr("disabled");
                            else
                                $('#success-ok').attr("disabled", "disabled");
                        });
                    },
                    error: function(jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                });
                break;

            case 27:
                title = 'COM_EMUNDUS_ACCESS_LETTERS';
                swal_confirm_button = 'GENERATE_DOCUMENT';
                swal_popup_class = 'em-w-auto';
                addLoader();

                html = '<div id="data"></div>';

                $.ajax({
                    type:'get',
                    url:url,
                    dataType:'html',
                    success: function(result) {
                        $('#data').append(result);
                        removeLoader();
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                });
                break;

            // Update publication of file(s)
            case 28:
                addLoader();

                await $.ajax({
                    type:'get',
                    url:url,
                    dataType:'json',
                    success: function(result) {
                        title = 'COM_EMUNDUS_PUBLISH_UPDATE';

                        html = '<div class="em-flex-column em-flex-align-start"><label>'+result.state+'</label><select class="modal-chzn-select" data-placeholder="'+result.select_state+'" name="em-action-publish" id="em-action-publish" value="">';

                        for (var i in result.states) {
                            if(isNaN(parseInt(i)))
                                break;
                            html += '<option value="'+result.states[i].step+'" >'+result.states[i].value+'</option>';
                        }
                        html += '</select></div>';

                        removeLoader();
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                });
                break;

            // Trombinoscope (letter for a group of files)
            case 31:
                swal_popup_class = 'em-w-auto';
                swal_confirm_button = 'COM_EMUNDUS_TROMBI_GENERATE';
                title = 'COM_EMUNDUS_TROMBINOSCOPE';
                html = '<iframe id="iframe-trombinoscope" src="'+url+'" style="width:'+window.getWidth()*0.8+'px;height:'+window.getHeight()*0.8+'px;border:none;"></iframe>';
                break;

            // TODO: Synthesis (fast pdf generation from a model)
            case 35:
                /// first --> get fnums

                $('.modal-body').append('<div id="chargement" style="padding:15px">' +
                    '<h5>'+Joomla.JText._('COM_EMUNDUS_PDF_GENERATION')+'</h5>'+
                    '<div id="loadingimg"><img src="'+loadingLine+'" alt="loading"/></div>' +
                    '<div id="extractstep"><p>'+Joomla.JText._('COM_EMUNDUS_CREATE_PDF')+'</p></div>'+
                    '</div>');

                checkInput = getUserCheck();
                var start = 0;
                var limit = 2;

                $.ajaxQ.abortAll();

                $.ajax({
                    type: 'post',
                    url: 'index.php?option=com_emundus&controller=files&task=create_file_pdf&format=raw',
                    dataType: 'JSON',
                    success: function (response) {
                        var file = response.file;
                        var model = $('[id^=l_35]').attr('href').split('model=')[1];
                        $.ajax({
                            type: 'post',
                            url: 'index.php?option=com_emundus&task=export_fiche_synthese',
                            dataType: 'JSON',
                            data: {checkInput: checkInput, file: file, model: model},
                            success: function (data) {
                                /// show download url
                                $('#extractstep').replaceWith('<div id="extractstep"><p>' + Joomla.JText._('COM_EMUNDUS_PDF_GENERATION') + '</p></div>');
                                $('#loadingimg').empty();
                                $('#extractstep').replaceWith('<div class="alert alert-success" role="alert">' + Joomla.JText._('COM_EMUNDUS_EXPORT_FINISHED') + '</div>');
                                //$('#chargement').append('<button type="button" class="btn btn-default" id="back" onclick="back();"><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;&nbsp;' + Joomla.JText._('BACK') + '</button>&nbsp;&nbsp;&nbsp;');
                                $('#chargement').append('<a class="btn btn-link" title="' + Joomla.JText._('COM_EMUNDUS_EXPORTS_DOWNLOAD_PDF') + '" href="' +data.path+ '/tmp/' + data.file + '" target="_blank"><span class="glyphicon glyphicon-download-alt"></span>  <span>' + Joomla.JText._('COM_EMUNDUS_EXPORTS_DOWNLOAD_PDF') + '</span></a>');
                            }, error: function (jqXHR) {
                                $('#loadingimg').empty();
                                $('#extractstep').replaceWith('<div class="alert alert-danger" role="alert">!!' + jqXHR.responseText + '</div>');
                                //$('#chargement').append('<button type="button" class="btn btn-default" id="back" onclick="back();"><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;&nbsp;' + Joomla.JText._('BACK') + '</button>&nbsp;&nbsp;&nbsp;');
                            }
                        })

                    }, error: function (jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                })

                break;
            case 18:
                fnums = getUserCheckArray();

                swal_popup_class = 'em-w-auto';
                title = 'COM_EMUNDUS_ACCESS_MAIL_EXPERT';
                html = '<div id="data" class="em-mt-32 em-w-100"><div id="email-loader" class="em-loader" style="margin: auto;"></div></div>';

                $.ajax({
                    type:'POST',
                    url:url,
                    data: {
                        fnums: fnums
                    },
                    success: function(result) {
                        const dataWrapper = document.getElementById('data');

                        if (dataWrapper) {
                            dataWrapper.innerHTML = result;
                            document.querySelector('.em-swal-confirm-button').style.opacity = '0';
                            $('#email-loader').remove();
                            dataWrapper.classList.remove('em-loader');
                        }
                    },
                    error: function (jqXHR) {
                        const dataWrapper = document.getElementById('data');
                        if (dataWrapper) {
                            dataWrapper.classList.remove('em-loader');
                            dataWrapper.innerHTML = '<p class="alert alert-error">' + Joomla.JText._('COM_EMUNDUS_ONBOARD_ERROR_MESSAGE') +'</p>';
                        }
                        console.warn(jqXHR.responseText);
                    }
                });
                break;

            default:
                break;
        }

        if (!multipleSteps) {
            Swal.fire({
                title: Joomla.JText._(title),
                html: html,
                allowOutsideClick: false,
                showCancelButton: true,
                showCloseButton: true,
                reverseButtons: true,
                confirmButtonText: Joomla.JText._(swal_confirm_button),
                cancelButtonText: Joomla.JText._('COM_EMUNDUS_ONBOARD_CANCEL'),
                customClass: {
                    container: 'em-modal-actions ' + swal_container_class,
                    popup: swal_popup_class,
                    title: 'em-swal-title',
                    cancelButton: 'em-swal-cancel-button',
                    confirmButton: 'em-swal-confirm-button btn btn-success',
                    actions: swal_actions_class
                },
                preConfirm: () => {
                    if(preconfirm !== '') {
                        preconfirm_value = new Function(preconfirm)();
                    }
                },
            }).then((result) => {
                if (result.value) {
                    runAction(id, url,preconfirm_value);
                }
            });

        } else {
            runAction(id);
        }

        $('.modal-chzn-select').chosen({width:'100%', search_contains: true});

        /***
         * On Category change
         */
        $('#em-action-tag-category').chosen().change(function() {
            var cat = $(this).val();

            if (cat) {
                var allowed_cats = tags.tags.filter((tag) => {
                    if(tag.category != cat) {
                        return tag.id;
                    }
                }).map((item) => {
                    return item.id;
                });

                document.querySelectorAll('#em-action-tag option').forEach((option) => {
                    if (!allowed_cats.contains(option.value)) {
                        option.disabled = false;
                        option.show();
                    } else {
                        option.disabled = true;
                        option.hide();
                    }
                })
            } else {
                document.querySelectorAll('#em-action-tag option').forEach((option) => {
                    option.disabled = false;
                    option.show();
                })
            }
            $("#em-action-tag").val('').trigger("liszt:updated");
        });
    });

    $(document).on('click', function() {
        if (!$('ul.dropdown-menu.open').hasClass('just-open')) {
            $('ul.dropdown-menu.open').hide();
            $('ul.dropdown-menu.open').removeClass('open');
        }
    });

    $(document).on('change', '#elements.em-filt-select', function(event) {
        setFiltersSumo(event);
    });
    $(document).on('change', 'select[id^="elements-"].em-filt-select', function(event) {
        setFiltersSumo(event);
    });
    $(document).on('change', 'select[id^="em-adv"].em-filt-select', function(event) {
        setFiltersSumo(event);
    });
    $(document).on('change', '#select_published', function(event) {
        setFiltersSumo(event);
    });
    $(document).on('change', '#select_newsletter', function(event) {
        setFiltersSumo(event);
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
            var name = $(this).attr('id');
            switch (name) {
                case 'clear-search':
                    if(moduleFilters) {
                        document.querySelector('#emundus-filters #clear-filters').click();
                    } else {
                        lastVal = {};
                        addLoader();
                        $.ajax({
                            type: 'POST',
                            url: 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=clear',
                            dataType: 'json',
                            success: function(result) {
                                if (result.status) {
                                    refreshFilter();
                                }
                            },
                            error: function(jqXHR) {
                                console.log(jqXHR.responseText);
                            }
                        });
                    }
                    break;

                case 'search':
                    search();
                    break;

                case 'save-filter':
                    if(moduleFilters) {
                        document.querySelector('#emundus-filters #save-filters').click();
                    } else {
                        $.ajaxQ.abortAll();
                        var filName = prompt(filterName);
                        if (filName != null) {
                            $.ajax({
                                type: 'POST',
                                url: 'index.php?option=com_emundus&controller=' + $('#view').val() + '&task=savefilters&Itemid=' + itemId,
                                dataType: 'json',
                                data: ({
                                    name: filName
                                }),
                                success: function (result) {

                                    if (result.status) {
                                        document.getElementById('em_select_filter').style.display = 'block'
                                        $('#select_filter').append('<option id="' + result.filter.id + '" selected="">' + result.filter.name + '<option>');
                                        $("#select_filter").trigger("chosen:updated");
                                        $('#saved-filter').show();
                                        setTimeout(function (e) {
                                            $('#saved-filter').hide();
                                        }, 600);

                                    } else {
                                        $('#error-filter').show();
                                        setTimeout(function (e) {
                                            $('#error-filter').hide();
                                        }, 600);
                                    }

                                },
                                error: function (jqXHR) {
                                    console.log(jqXHR.responseText);
                                }
                            })
                        } else {
                            alert(filterEmpty);
                            filName = prompt(filterName, "name");
                        }
                    }
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
                type: 'POST',
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

    $(document).on('click', '#em-last-open .list-group-item', function(e) {
        $.ajaxQ.abortAll();

        if (e.handle !== true) {
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
                data:({
                    fnum:fnum.fnum
                }),
                success: function(result) {
                    if (result.status) {
                        var fnumInfos = result.fnumInfos;
                        fnum.name = fnumInfos.name;
                        fnum.label = fnumInfos.label;
                        openFiles(fnum);
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR.responseText);
                }
            })
        }
    });

    const handledIds = ['del-filter', 'em-close-file', 'em-mini-file', 'em-next-file', 'em-prev-file', 'em-see-files', 'em-delete-files', 'add-filter'];
    $(document).on('click', 'button', function(e) {
        if (e.handle != true && handledIds.indexOf(this.id) != -1) {
            e.handle = true;
            var id = this.id;
            var cfnum = '';
            var fnumsOnPage = '';
            var fnum = {};
            switch (id) {
                case 'del-filter':
                    $.ajaxQ.abortAll();
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
                            error: function(jqXHR) {
                                console.log(jqXHR.responseText);
                            }
                        })
                    } else {
                        alert(nodelete);
                    }
                    break;

                case 'add-filter':
                    $.ajaxQ.abortAll();
                    addElement();
                    break;

                case 'em-close-file':
                    $.ajaxQ.abortAll();
                    document.location.hash = "close";
                    $('.em-check:checked').prop('checked', false);
                    $('.nav.navbar-nav').hide();
                    reloadActions('files', undefined, false);

                case 'em-mini-file':
                    hideItems(['#em-appli-menu', '#em-synthesis', '#em-assoc-files', '.em-hide', '#em-last-open']);

                    $.ajaxQ.abortAll();
                    $('#em-appli-block').remove();
                    $('.em-close-minimise').remove();
                    $('.em-open-files').remove();
                    $('#em-last-open .list-group .list-group-item').removeClass('active');
                    $('#em-files-filters').show();
                    $('.em-check:checked').prop('checked', false);
                    $(".main-panel .panel.panel-default").show();
                    break;

                case 'em-next-file':
                    $.ajaxQ.abortAll();

                    cfnum = document.querySelector('.em-check:checked').id;
                    if (typeof cfnum !== 'undefined') {
                        cfnum = cfnum.split('_')[0];
                    }

                    fnumsOnPage = document.getElementsByClassName('em_file_open');
                    for (var fop = 0; fop < fnumsOnPage.length; fop++) {
                        if (fnumsOnPage[fop].id === cfnum) {
                            // In case we are on the last fnum of the page, we loop around to -1 so the i+1 value is 0.
                            if (fop === fnumsOnPage.length-1) {
                                fop = -1;
                            }
                            break;
                        }
                    }
                    fop++;

                    fnum.fnum = fnumsOnPage[fop].id;
                    $('.em-check:checked').prop('checked', false);
                    $('#'+fnum.fnum+'_check').prop('checked', true);

                    addLoader();
                    fnum.sid = parseInt(fnum.fnum.substr(21, 7));
                    fnum.cid = parseInt(fnum.fnum.substr(14, 7));

                    page = Array.from(document.querySelector('#em-appli-block .panel[class*="em-container-"]').classList).filter(
                        function x (p) {
                            return p.startsWith('em-container');
                        }
                    )[0].split('-')[2];

                    var url = window.location.href;
                    url = url.split('#');
                    window.history.pushState('','', url[0]+'#'+fnum.fnum+'|open');

                    $.ajax({
                        type: 'get',
                        url: 'index.php?option=com_emundus&controller=' + $('#view').val() + '&task=getfnuminfos',
                        dataType: "json",
                        data: ({
                            fnum: fnum.fnum
                        }),
                        success: function (result) {
                            if (result.status) {
                                var fnumInfos = result.fnumInfos;
                                fnum.name = fnumInfos.name;
                                fnum.label = fnumInfos.label;
                                openFiles(fnum, page);
                            }
                        },
                        error: function (jqXHR) {
                            console.log(jqXHR.responseText);
                        }
                    });
                    break;

                case 'em-prev-file':
                    $.ajaxQ.abortAll();

                    cfnum = document.querySelector('.em-check:checked').id;
                    if (typeof cfnum !== 'undefined') {
                        cfnum = cfnum.split('_')[0];
                    }

                    fnumsOnPage = document.getElementsByClassName('em_file_open');
                    for (var fop = 0; fop < fnumsOnPage.length; fop++) {
                        if (fnumsOnPage[fop].id === cfnum) {
                            // In case we are on the first fnum of the page, we loop around to the length so the i-1 value is equal to the last fnum index.
                            if (fop === 0) {
                                fop = fnumsOnPage.length;
                            }
                            break;
                        }
                    }
                    fop--;

                    fnum.fnum = fnumsOnPage[fop].id;
                    $('.em-check:checked').prop('checked', false);
                    $('#'+fnum.fnum+'_check').prop('checked', true);

                    addLoader();
                    fnum.sid = parseInt(fnum.fnum.substr(21, 7));
                    fnum.cid = parseInt(fnum.fnum.substr(14, 7));

                    if (document.querySelector('#em-appli-block .panel[class*="em-container-"]')) {
                        var page = Array.from(document.querySelector('#em-appli-block .panel[class*="em-container-"]').classList).filter(
                            function x (p) {
                                return p.startsWith('em-container');
                            }
                        )[0].split('-')[2];
                    }

                    var url = window.location.href;
                    url = url.split('#');
                    window.history.pushState('','', url[0]+'#'+fnum.fnum+'|open');

                    $.ajax({
                        type: 'get',
                        url: 'index.php?option=com_emundus&controller=' + $('#view').val() + '&task=getfnuminfos',
                        dataType: "json",
                        data: ({
                            fnum: fnum.fnum
                        }),
                        success: function (result) {
                            if (result.status) {
                                var fnumInfos = result.fnumInfos;
                                fnum.name = fnumInfos.name;
                                fnum.label = fnumInfos.label;
                                openFiles(fnum, page);
                            }
                        },
                        error: function (jqXHR) {
                            console.log(jqXHR.responseText);
                        }
                    });
                    break;

                case 'em-see-files':
                    $.ajaxQ.abortAll();
                    var fnum = new Object();
                    fnum.fnum = $(this).parents('a').attr('href').split('-')[0];
                    fnum.fnum = fnum.fnum.substr(1, fnum.fnum.length);
                    fnum.sid = parseInt(fnum.fnum.substr(21, 7));
                    fnum.cid = parseInt(fnum.fnum.substr(14, 7));
                    $('.em-check:checked').prop('checked', false);
                    $('#'+fnum.fnum+'_check').prop('checked', true);

                    var url = window.location.href;
                    url = url.split('#');
                    window.history.pushState('','', url[0]+'#'+fnum.fnum+'|open');

                    $.ajax({
                        type:'get',
                        url:'index.php?option=com_emundus&controller='+$('#view').val()+'&task=getfnuminfos',
                        dataType:"json",
                        data:({
                            fnum:fnum.fnum
                        }),
                        success: function(result) {
                            if (result.status) {
                                var fnumInfos = result.fnumInfos;
                                fnum.name = fnumInfos.name;
                                fnum.label = fnumInfos.label;
                                openFiles(fnum);
                            }
                        },
                        error: function (jqXHR) {
                            console.log(jqXHR.responseText);
                        }
                    });
                    break;

                case 'em-delete-files':
                    $.ajaxQ.abortAll();
                    var r = confirm(Joomla.JText._('COM_EMUNDUS_CONFIRM_DELETE_FILE'));
                    if (r == true) {

                        var fnum = $(this).parents('a').attr('href').split('-')[0];
                        fnum = fnum.substr(1, fnum.length);
                        $.ajax({
                            type:'POST',
                            url:'index.php?option=com_emundus&controller=files&task=deletefile',
                            dataType:'json',
                            data:{
                                fnum: fnum
                            },
                            success: function(result) {
                                if (result.status) {
                                    if ($("#"+fnum+"-collapse").parent('div').hasClass('panel-primary')) {
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
                            error: function (jqXHR) {
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

    $(document).on('click', '.em_file_open', function(e) {
        $.ajaxQ.abortAll();
        if (e.handle !== true) {
            addLoader();
            e.handle = true;
            var fnum = {};
            fnum.fnum = $(this).attr('id');

            var sid = parseInt(fnum.fnum.substr(21, 7));
            var cid = parseInt(fnum.fnum.substr(14, 7));

            $('.em-check:checked').prop('checked', false);
            $('#em-check-all:checked').prop('checked', false);
            $('#em-check-all-all:checked').prop('checked', false);
            $('#'+fnum.fnum+'_check').prop('checked', true);

            $.ajax({
                type:'get',
                url:'index.php?option=com_emundus&controller='+$('#view').val()+'&task=getfnuminfos',
                dataType:"json",
                data:({
                    fnum:fnum.fnum
                }),
                success: function(result) {
                    if (result.status) {
                        var fnumInfos = result.fnumInfos;
                        fnum.name = fnumInfos.name;
                        fnum.label = fnumInfos.label;
                        openFiles(fnum);
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR.responseText);
                }
            })
        }
    });

    $(document).on('click', '#em-appli-menu .list-group-item', function(e) {
        $.ajaxQ.abortAll();
        e.preventDefault();
        var id = $(this).attr('id');
        var url = $(this).attr('href');

        $.ajax({
            type: "get",
            url: url,
            dataType: 'html',
            data: ({id: id}),
            success: function (result) {
                const urlUsed = new URL(window.location.origin + '/' + url);

                var fnumUsed = urlUsed.searchParams.get('fnum');

                checkIfSomeoneIsEditing(fnumUsed);
                const appBlock = $('#em-appli-block');

                if (appBlock) {
                    appBlock.empty();
                    appBlock.append(result);
                }
            },
            error: function (jqXHR) {
                console.log(jqXHR.responseText);
            }
        });

    });

    $(document).on('change', '#pager-select', function(e) {
        if (e.handle !== true) {
            e.handle = true;
            addLoader();
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
                    } else {
                        removeLoader();
                    }
                }
            });
        }
    });

    $(document).on('keyup', '#em_filters input:text, #filters input:text', function(e) {
        if ($(this).closest('.modal').length === 0 && $(this).closest('#em-message').length === 0 && e.keyCode == 13 ) {
            search();
        }
    });

    $(document).on('change', '#select_filter', function(e) {
        var id = $(this).attr('id');
        var val = $('#' + id).val();
        $.ajax({
            type: 'POST',
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
                            }
                        },
                        error: function(jqXHR) {
                            console.log(jqXHR.responseText);
                        }
                    });
                }
            },
            error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        });
    });

    $(document).on('click', '#suppr-filt', function(e) {
        $.ajaxQ.abortAll();
        var fId = $(this).parent('fieldset').attr('id');
        var index = fId.split('-');

        var sonName = $('#em-adv-fil-' + index[index.length - 1]).attr('name');

        $('#' + fId).remove();
        $.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=files&task=deladvfilter',
            dataType: 'json',
            data: ({
                elem: sonName,
                id: index[index.length - 1]
            }),
            success: function(result) {
                if (result.status) {
                    reloadData($('#view').val());
                }
            },
            error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        })
    });

    $(document).on('change', '.em-check', function(e) {
        if ($(this).attr('id') == 'em-check-all') {
            $('.em-actions[multi="1"]').show();
            $('.em-actions[multi="1"]').removeClass('em-hidden');

            if ($(this).is(':checked')) {
                $(this).prop('checked', true);
                $('.em-check').prop('checked', true);
                $('.em-actions[multi="0"]').hide();
                $('.em-actions[multi="0"]').addClass('em-hidden');
                $('.nav.navbar-nav').show();

                $('.em-dropdown').each(function() {
                    var dpId = $(this).attr('id');
                    var nbHidden =  $('ul[aria-labelledby="' + dpId + '"] .em-actions.em-hidden').length;
                    var nonHidden = $('ul[aria-labelledby="' + dpId + '"] .em-actions').length;
                    $(this).attr('nba', nonHidden - nbHidden);
                });
                $('.em-dropdown[nba="0"]').parent('li').hide();
                reloadActions('files', undefined, true);

            } else {
                console.log('here');
                $(this).prop('checked', false);
                $('.em-check').prop('checked', false);
                $('.em-actions[multi="0"]').show();
                $('.em-actions[multi="0"]').removeClass('em-hidden');
                $('.em-actions[multi="1"]').show();
                $('.em-actions[multi="1"]').removeClass('em-hidden');
                $('.nav.navbar-nav').hide();

                $('.em-dropdown[nba="0"]').parent('li').show();
                $('.em-dropdown').each(function() {
                    var dpId = $(this).attr('id');
                    var nonHidden = $('ul[aria-labelledby="' + dpId + '"] .em-actions').length;
                    $(this).attr('nba', nonHidden);
                });
                reloadActions('files', undefined, false);
            }

        } else {

            $('#em-check-all').prop('checked', false);
            $('#em-check-all-all').prop('checked', false);

            if ($('.em-check:checked').length == 0) {
                $('.nav.navbar-nav').hide();
                reloadActions('files', undefined, false);
            } else if($('.em-check:checked').length == 1) {
                reloadActions('files', $(this).attr('id').split('_')[0], true);
            } else {
                reloadActions('files', undefined, true);

                $('.em-actions[multi="0"]').hide();
                $('.em-actions[multi="0"]').addClass('em-hidden');

                $('.em-dropdown').each(function() {
                    var dpId = $(this).attr('id');
                    var nbHidden =  $('ul[aria-labelledby="' + dpId + '"] .em-actions.em-hidden').length;
                    var nonHidden = $('ul[aria-labelledby="' + dpId + '"] .em-actions').length;
                    $(this).attr('nba', nonHidden - nbHidden);
                });
                $('.em-dropdown[nba="0"]').parent('li').hide();
            }
        }
    });

    $(document).on('mouseover', '.em-dropdown', function(e) {
        var id = $(this).attr('id');

        $('ul.dropdown-menu.open').hide();
        $('ul.dropdown-menu.open').removeClass('open');

        if ($('ul[aria-labelledby="' + id + '"]').hasClass('open')) {
            $('ul[aria-labelledby="' + id + '"]').hide();
            $('ul[aria-labelledby="' + id + '"]').removeClass('open');
        } else {
            $('ul[aria-labelledby="' + id + '"]').show();
            $('ul[aria-labelledby="' + id + '"]').addClass('open just-open');
        }

        setTimeout(function() {
            $('ul[aria-labelledby="' + id + '"]').removeClass('just-open')
        }, 300);
    });

    $(document).on('click', '.em-actions-form', function(e) {
        console.log('here');
        $.ajaxQ.abortAll();
        var id = parseInt($(this).attr('id'));
        var url = $(this).attr('url');
        $('#em-modal-form').modal({backdrop:true},'toggle');

        $('.modal-title').empty();
        $('.modal-title').append($(this).children('a').text());
        $('.modal-body').empty();
        removeLoader();

        if ($('.modal-dialog').hasClass('modal-lg')) {
            $('.modal-dialog').removeClass('modal-lg');
        }

        $('.modal-body').attr('act-id', id);
        $('.modal-footer').show();
        $('.modal-footer').hide();
        $('.modal-dialog').addClass('modal-lg');
        $(".modal-body").empty();

        $(".modal-body").append('<iframe src="'+url+'" style="width:'+window.getWidth()*0.8+'px; height:'+window.getHeight()*0.8+'px; border:none"></iframe>');
    });


    // show tooltips when hovering the button --> using JText
    $(document).on('mouseover', '[id^=candidat_]', function(e){ $(this).css('cursor', 'pointer').attr('title', Joomla.JText._('SEND_EMAIL_TOOLTIPS'));})

    $(document).on('click', '[id^=candidat_]', function(e){
        // e.preventDefault();
        // $.ajaxQ.abortAll();
        tinymce.remove();
        var fnum = $(this).attr('id').split('candidat_')[1];

        $('#em-modal-actions').modal({backdrop:false,keyboard:true},'toggle');
        $('.modal-title').empty();
        $('.modal-title').append($(this).children('a').text());
        $('.modal-body').empty();
        removeLoader();

        if ($('.modal-dialog').hasClass('modal-lg')) {
            $('.modal-dialog').removeClass('modal-lg');
        }

        $('.modal-body').attr('act-id', 37);
        $('.modal-footer').show();
        $('.modal-lg').css({ width: '80%' });
        $('.modal-dialog').css({ width: '80%' });

        $('.modal-dialog').append('<div class="em-modal-sending-emails" id="em-modal-sending-emails">' +
            '<div id="em-sending-email-caption" class="em-sending-email-caption">' + Joomla.JText._('SENDING_EMAILS') + '</div>' +
            '<img class="em-sending-email-img" id="em-sending-email-img" src="/media/com_emundus/images/sending-email.gif"/>' +
            '</div>');

        $('#can-val').empty();
        $('#can-val').append('<a id="send-email" class="btn btn-success" name="applicant_email">'+Joomla.JText._('COM_EMUNDUS_EMAILS_SEND_CUSTOM_EMAIL').replace(/\\/g, '')+'</a>');
        $('#can-val').show();

        $('.modal-body').css('display', 'flex');
        $('.modal-body').css('align-items', 'center');
        $('.modal-body').css('justify-content', 'center');

        $('.modal-body').append('<div id="email-candidat-preview" class="email___candidat"></div>');
        $('#email-candidat-preview').append('<div id="email-candidat-panel-preview" class="email___candidat_panel" style="display: none"></div>');
        $('#email-candidat-preview').append('<div id="email-candidat-message-preview" class="email___message_body" style="display: none"></div>');


        $('#email-candidat-preview').prepend('<div id="loadingimg-candidat"><img src="'+loadingLine+'" alt="loading"/></div>');

        $('#loadingimg-candidat').show();


        $.ajax({
            type: 'post',
            url: 'index.php?option=com_emundus&controller=messages&task=getrecapbyfnum',
            dataType: 'JSON',
            data: { fnum : fnum.toString() },
            success: function(result) {

                $.ajax({
                    type: 'post',
                    url: 'index.php?option=com_emundus&controller=messages&task=getmessagerecapbyfnum',
                    dataType: 'JSON',
                    data: { fnum : fnum },
                    success: function(data) {
                        var email_recap = data.email_recap.message_recap[0];
                        var letter_recap = data.email_recap.attached_letter;

                        if(data.status == true) {
                            var recap = result.recap;
                            var color = result.color;

                            $('#loadingimg-candidat').hide();

                            $('#email-candidat-panel-preview').show();
                            $('#email-candidat-message-preview').show();
                            $('#email-candidat-panel-preview').append(
                                '<div id="email-candidat-panel-preview" class="email___candidat_panel_item">' +
                                '<label for="candidat-name-label">' + Joomla.JText._('CANDIDATE') + '</label>' +
                                '<div id="candidat-name"></div>' +
                                '<div id="candidat-email"></div>' +
                                '</div>'
                            );

                            // candidate program
                            $('#email-candidat-panel-preview').append(
                                '<div id="email-candidat-panel-preview" class="email___candidat_panel_item">' +
                                '<label for="candidat-program-label">' + Joomla.JText._('PROGRAM_NAME') + '</label>' +
                                '<div id="candidat-program"></div>' +
                                '<div id="candidat-program-year"></div>' +
                                '</div>'
                            );

                            // candidate status
                            $('#email-candidat-panel-preview').append(
                                '<div id="email-candidat-panel-preview" class="email___candidat_panel_item">' +
                                '<label for="candidat-status-label">' + Joomla.JText._('CANDIDAT_STATUS') + '</label>' +
                                '<div id="candidat-status" style="margin-bottom: 8px !important">' +
                                '<span id="status-class"></span>' +
                                '</div>' +
                                '</div>'
                            );

                            // attachment letters
                            $('#email-candidat-panel-preview').append(
                                '<div id="email-candidat-panel-preview" class="email___candidat_panel_item">' +
                                '<label for="candidat-attachment-label">' + Joomla.JText._('ATTACHMENT_LETTER') + '</label>' +
                                '<div id="candidat-letters"></div>' +
                                '</div>'
                            );

                            $('#candidat-name').append(recap.name);
                            $('#candidat-email').append(recap.email);
                            $('#candidat-program').append(recap.label);
                            $('#candidat-program-year').append(recap.year);

                            $('#status-class').addClass('label label-' + recap.class);
                            $('#status-class').append(recap.value);

                            // message preview
                            $('#email-candidat-message-preview').append(
                                '<div id="email-preview" class="email___message_body_item">' +
                                '<div class="form-group em-form-subject">' +
                                '<span class="label label-grey" for="mail_from" >' + Joomla.JText._('EMAIL_SUBJECT') + ':' + '</span>' +
                                '<input type="text" id="email-preview-label" style="height:35px; font-weight: bold; width: 100%">'+
                                '</div>'+
                                '</div>' +

                                '<div id="message-subject"></div>' +
                                '<div id="message-body" contenteditable="true"></div>' +
                                '</div>'
                            );

                            $('#message-body').append(email_recap.message);

                            tinymce.init({
                                selector: '#message-body',
                                menubar: false,
                                font_formats: "Sans Serif = arial, helvetica, sans-serif;Serif = times new roman, serif;Fixed Width = monospace;Wide = arial black, sans-serif;Narrow = arial narrow, sans-serif;Comic Sans MS = comic sans ms, sans-serif;Garamond = garamond, serif;Georgia = georgia, serif;Tahoma = tahoma, sans-serif;Trebuchet MS = trebuchet ms, sans-serif;Verdana = verdana, sans-serif",
                                toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | outdent indent | removeformat",
                                fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt",
                                height: 350
                            });

                            /// render attachment letters to candidat preview
                            letter_recap.forEach(letter => {
                                $('#candidat-letters').append(
                                    "<li>" +
                                    "<a id='em_letter_preview' value='" + letter.value + "' target='_blank' href='" + letter.dest + "'>" +
                                    "<span style='font-size: medium; padding: 10px 0px; color:" + color + "'>" +
                                    "<span class='glyphicon glyphicon-paperclip' style='padding-right: 10px;'></span>" + letter.value +
                                    "</span>" +
                                    "</a>" +
                                    "</li>"
                                );
                            })

                            $("#email-preview-label").val(email_recap.subject);

                        } else {
                            console.log(false);
                        }

                        /// send email
                        $('#send-email').on('click', function(e) {
                            $('#em-modal-sending-emails').css('display', 'block');
                            var tmpl = email_recap;

                            var files_tbl = $('#candidat-letters').find('[id^=em_letter_preview]');
                            var files = [];
                            var types = [];

                            files_tbl.each(function() {
                                var href = $(this).attr('href').split('/');
                                files.push(href[href.length - 1]);
                                types.push($(this).attr('value'));
                            })

                            var raw = {
                                title : $('#email-preview-label').val(),
                                content : tinyMCE.activeEditor.getContent(),
                                template: email_recap.email_tmpl,
                                files : files,
                                types: types,
                            };

                            $.ajax({
                                type: 'POST',
                                url: 'index.php?option=com_emundus&controller=messages&task=sendemailtocandidat',
                                async: false,
                                dataType: 'JSON',
                                data: { fnum: fnum, raw: raw, tmpl: email_recap.id},
                                success: function(result) {
                                    var dest = '<p>' + Joomla.JText._('SEND_TO') + '</p><ul class="list-group" id="em-mails-sent" style="overflow-y: unset"><i>' + result.email + '</i></ul>';
                                    $.ajax({
                                        type: 'POST',
                                        url: 'index.php?option=com_emundus&controller=messages&task=addtagsbyfnum',
                                        async: false,
                                        dataType: 'JSON',
                                        data: { fnum: fnum , tmpl: email_recap.id },
                                        success: function(value) {
                                            if(value.status) {
                                                addLoader();
                                                $('#em-modal-sending-emails').css('display', 'none');
                                                $('#em-modal-actions').modal('hide');

                                                reloadData($('#view').val());
                                                reloadActions($('#view').val(), undefined, false);
                                                $('.modal-backdrop, .modal-backdrop.fade.in').css('display', 'none');
                                                $('body').removeClass('modal-open');

                                                Swal.fire({
                                                    type: 'success',
                                                    title: Joomla.JText._('COM_EMUNDUS_EMAILS_EMAILS_SENT'),
                                                    html: dest,
                                                    customClass: {
                                                        title: 'em-swal-title',
                                                        confirmButton: 'em-swal-confirm-button',
                                                        actions: "em-swal-single-action",
                                                    },
                                                });
                                            } else {
                                                $('#em-modal-sending-emails').css('display', 'none');
                                                Swal.fire({
                                                    type: 'error',
                                                    title: Joomla.JText._('COM_EMUNDUS_EMAILS_NO_EMAILS_SENT'),
                                                    customClass: {
                                                        title: 'em-swal-title',
                                                        confirmButton: 'em-swal-confirm-button',
                                                        actions: "em-swal-single-action",
                                                    },
                                                })
                                            }

                                        }, error: function(jqXHR) {
                                            console.log(jqXHR.responseText);
                                        }}
                                    )
                                }, error: function(jqXHR, textStatus) {
                                    $.ajax({
                                        type: 'POST',
                                        url: 'index.php?option=com_emundus&controller=messages&task=addtagsbyfnum',
                                        async: false,
                                        dataType: 'JSON',
                                        data: { fnum: fnum , tmpl: email_recap.id },
                                        success: function(value) {
                                            if(value.status) {
                                                addLoader();
                                                $('#em-modal-sending-emails').css('display', 'none');
                                                $('#em-modal-actions').modal('hide');

                                                reloadData($('#view').val());
                                                reloadActions($('#view').val(), undefined, false);
                                                $('.modal-backdrop, .modal-backdrop.fade.in').css('display', 'none');
                                                $('body').removeClass('modal-open');

                                                Swal.fire({
                                                    type: 'success',
                                                    title: Joomla.JText._('COM_EMUNDUS_EMAILS_EMAILS_SENT'),
                                                    html: dest
                                                });
                                            } else {
                                                $('#em-modal-sending-emails').css('display', 'none');
                                                Swal.fire({
                                                    type: 'error',
                                                    title: Joomla.JText._('COM_EMUNDUS_EMAILS_NO_EMAILS_SENT')
                                                })
                                            }

                                        }, error: function(jqXHR) {
                                            console.log(jqXHR.responseText);
                                        }}
                                    )
                                }
                            })
                        })


                    }, error: function(jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                })

            }, error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        })
    })

    $(document).on('click', '#savefilter', function(e) {
        var code = $('#em-export-prg').val();
        var camp = $('#em-export-camp').val();
        var letters = $('#em-export-letter').val();

        var proglabel = $("#em-export-prg option:selected").text();
        var camplabel = $("#em-export-camp option:selected").text();
        var exp_methode = $('#em-export-methode:checked').val();

        var baseElements = [];
        var baseEltNodes = document.getElementById("em-export").querySelectorAll('li');
        baseEltNodes.forEach(baseElt => {
            var id = baseElt.id.split('-')[0];
            baseElements.push(id);
        })

        var params = '{' +
            '"programmelabel":"'+proglabel+
            '","code":"'+code+
            '","camp":"'+camp+
            '","letters":"'+letters+
            '","baseElements":"'+baseElements+
            '","campaignlabel":"'+camplabel+
            '","elements":';

        var eltJson = "{";
        var i = 0;

        $(".em-export-item").each(function() {
            eltJson += '"'+i+'":"'+$(this).attr('id').split('-')[0]+'",';
            i++;
        });

        eltJson = eltJson.substr(0, eltJson.length - 1);
        eltJson += '}';

        var objJson = '{';
        i = 0;
        $('.em-ex-check:checked').each(function() {
            objJson += '"'+i +'":"'+$(this).attr('name')+'",';
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
        if (objJson === '}')
            params += ',"objects":""';
        else
            params += ',"objects":'+objJson;

        if (options === '}')
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
                error: function(jqXHR) {
                    console.log(jqXHR.responseText);
                }
            })
        } else {
            alert(filterEmpty);
            filName = prompt(filterName, "name");
        }
    });

    $(document).on('click', '#delfilter', function(e) {
        if (confirm(Joomla.JText._('CONFIRM_DELETE_FILTER'))) {
            var id = $('#filt_save').val();
            if (id != 0) {
                /// clean all elements de base
                $('#em-export').empty();

                /// uncheck all elements du formulaire
                if($('#appelement :input').is(":checked") == true) {
                    $('#appelement :input').attr('checked',false);
                } else { }

                /// uncheck all criteres d'evaluation --> evalelement
                if($('#evalelement :input').is(":checked") == true) {
                    $('#evalelement :input').attr('checked',false);
                } else { }

                /// uncheck all elements de decision --> decelement
                if($('#decelement :input').is(":checked") == true) {
                    $('#decelement :input').attr('checked',false);
                } else { }

                // uncheck all autres colonnes --> oelts
                if($('#oelts :input').is(":checked") == true) {
                    $('#oelts :input').attr('checked',false);
                } else { }

                /// uncheck all autres options --> forms
                if($('#forms :input').is(":checked") == true) {
                    $('#forms :input').attr('checked',false);
                } else { }

                /// set option d'extraction to default checked value
                $('#em-export-methode').attr('checked', true)

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
                    error: function(jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                })
            } else {
                alert(nodelete);
            }
        }

    });

    $(document).on('change', '#em-modal-actions #em-export-form', function(e) {
        if (e.handle !== true) {
            e.handle = true;
            var id = $(this).val();
            var text = $('#em-modal-actions #em-export-form option:selected').attr('data-value');
            $('#emundus_elm_'+id).prop("checked", true);
            $('#em-export').append('<li class="em-export-item" id="' + id + '-item"><span class="em-excel_elts em-flex-row"><span id="' + id + '-itembtn" class="em-pointer fabrik-elt-delete material-icons em-red-500-color em-mr-4">delete_outline</span><p>' + text + '</p></span></li>');
        }
    });

    $(document).on('change', '#em-admission-export-form', function(e) {
        if (e.handle !== true) {
            e.handle = true;
            var id = $(this).val();
            var text = $('#em-admission-export-form option:selected').attr('data-value');
            $('#emundus_elm_'+id).prop("checked", true);
            $('#em-export').append('<li class="em-export-item" id="' + id + '-item"><span class="em-excel_elts em-flex-row"><span id="' + id + '-itembtn" class="em-pointer fabrik-elt-delete material-icons em-red-500-color em-mr-4">delete_outline</span><p>' + text + '</p></span></li>');
        }
    });

    $(document).on('change', '#em-decision-export-form', function(e) {
        if (e.handle !== true) {
            e.handle = true;
            var id = $(this).val();
            var text = $('#em-decision-export-form option:selected').attr('data-value');
            $('#emundus_elm_'+id).prop("checked", true);
            $('#em-export').append('<li class="em-export-item" id="' + id + '-item"><span class="em-excel_elts em-flex-row"><span id="' + id + '-itembtn" class="em-pointer fabrik-elt-delete material-icons em-red-500-color em-mr-4">delete_outline</span><p>' + text + '</p></span></li>');
        }
    });

    $(document).on('click', '.emundusraw', function(e) {
        $.ajaxQ.abortAll();
        if (e.handle !== true) {
            e.handle = true;
            var id = $(this).val();
            if ($(this).is(':checked')) {
                var text = $("label[for='" + $(this).attr('id') + "']").text();
                $('#em-export').append('<li class="em-export-item" id="' + id + '-item"><span class="em-excel_elts em-flex-row"><span id="' + id + '-itembtn" class="em-pointer fabrik-elt-delete material-icons em-red-500-color em-mr-4">delete_outline</span><p>' + text + '</p></span></li>');
            } else {
                $('#'+id+'-item').remove();
            }
        }
    });

    $(document).on('click', '#em-export .em-export-item .fabrik-elt-delete', function(e) {
        $.ajaxQ.abortAll();
        var id = $(this).attr('id').split('-');
        id.pop();
        $('#emundus_elm_'+id).removeAttr("checked");
        console.log(id);
        $('#'+id+'-item').remove();
        //$(this).parent('li').remove();
    });

    $(document).on('change', '.em-modal-check', function() {

        if ($(this).hasClass('em-check-all')) {

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
        $.ajax({
            type:'post',
            url:'index.php?option=com_emundus&controller='+$('#view').val()+'&task=unlinkevaluators',
            dataType:'json',
            data:({fnum:id[0], id:id[1], group: gr}),
            success: function(result) {
                if (result.status)
                    $("#"+id.join('-')).parent('li').remove()
            },
            error: function (jqXHR) {
                console.log(jqXHR.responseText);
            }
        })
    });

    $(document).on('click', '#em-hide-filters', function() {
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
        var elements_block = document.getElementById('elements-popup');
        if (elements_block.style.display == 'none') {

            $('#eval-elements-popup').hide();
            $('#showevalelements_icon').css('transform','rotate(-90deg)');

            $('#decision-elements-popup').hide();
            $('#showdecisionelements_icon').css('transform','rotate(-90deg)');

            $('#admission-elements-popup').hide();
            $('#showadmissionelements_icon').css('transform','rotate(-90deg)');

            $('#elements-popup').toggle(300);
            $('#showelements_icon').css('transform','rotate(0deg)');

        } else {
            $('#elements-popup').toggle(300);
            $('#showelements_icon').css('transform','rotate(-90deg)');
        }
    });

    $(document).on('click', '#showevalelements', function() {
        var eval_elements_block = document.getElementById('eval-elements-popup');
        if (eval_elements_block.style.display == 'none') {

            $('#elements-popup').hide();
            $('#showelements_icon').css('transform','rotate(-90deg)');

            $('#decision-elements-popup').hide();
            $('#showdecisionelements_icon').css('transform','rotate(-90deg)');

            $('#admission-elements-popup').hide();
            $('#showadmissionelements_icon').css('transform','rotate(-90deg)');

            $('#eval-elements-popup').toggle(300);
            $('#showevalelements_icon').css('transform','rotate(0deg)');

        } else {
            $('#eval-elements-popup').toggle(300);
            $('#showevalelements_icon').css('transform','rotate(-90deg)');
        }
    });

    $(document).on('click', '#showdecisionelements', function() {
        var decision_elements_block = document.getElementById('decision-elements-popup');
        if (decision_elements_block.style.display == 'none') {

            $('#elements-popup').hide();
            $('#showelements_icon').css('transform','rotate(-90deg)');

            $('#eval-elements-popup').hide();
            $('#showevalelements_icon').css('transform','rotate(-90deg)');

            $('#admission-elements-popup').hide();
            $('#showadmissionelements_icon').css('transform','rotate(-90deg)');

            $('#decision-elements-popup').toggle(300);
            $('#showdecisionelements_icon').css('transform','rotate(0deg)');

        } else {
            $('#decision-elements-popup').toggle(300);
            $('#showdecisionelements_icon').css('transform','rotate(-90deg)');
        }
    });

    $(document).on('click', '#showadmissionelements', function() {
        var admission_elements_block = document.getElementById('admission-elements-popup');
        if (admission_elements_block.style.display == 'none') {

            $('#elements-popup').hide();
            $('#showelements_icon').css('transform','rotate(-90deg)');

            $('#eval-elements-popup').hide();
            $('#showevalelements_icon').css('transform','rotate(-90deg)');

            $('#decision-elements-popup').hide();
            $('#showdecisionelements_icon').css('transform','rotate(-90deg)');

            $('#admission-elements-popup').toggle(300);
            $('#showadmissionelements_icon').css('transform','rotate(0deg)');

        } else {
            $('#admission-elements-popup').toggle(300);
            $('#showadmissionelements_icon').css('transform','rotate(-90deg)');
        }
    });

    $(document).on('change', '#em-doc-export-mode', function() {
        var showMode = $('#em-doc-export-mode').val();
        $('#export-tooltips').empty();

        if(showMode == 2) { $('#merge-div').hide(); }

        else {
            $('#merge-div').show();
            $("label[for='em-combine-pdf']").css('text-decoration', 'none');
            if(showMode == 0) {
                $('#export-tooltips').append('<div id="candidat-export-tooltip" style="font-size: .8rem; color: #406AFF">' + Joomla.JText._('COM_EMUNDUS_CANDIDAT_EXPORT_TOOLTIP') + '</div>');
                $('#em-doc-pdf-merge').prop('checked', false);

                if($('#em-doc-pdf-merge').is(':checked')) {
                    setTimeout(function() {$('#merge-tooltips').append('<div id="candidat-merge-tooltip" style="font-size: .8rem; color: #406AFF">' + Joomla.JText._('COM_EMUNDUS_CANDIDAT_MERGE_TOOLTIP') + '</div>');}, 100);
                } else {
                    $('#merge-tooltips').empty();
                }
            } else if(showMode == 1) {
                $('#export-tooltips').append('<div id="document-export-tooltip" style="font-size: .8rem; color: #406AFF">' + Joomla.JText._('COM_EMUNDUS_DOCUMENT_EXPORT_TOOLTIP') + '</div>');
                $('#em-doc-pdf-merge').prop('checked', false);

                if($('#em-doc-pdf-merge').is(':checked')) {
                    setTimeout(function() {$('#merge-tooltips').append('<div id="document-merge-tooltip" style="font-size: 1rem; color: #406AFF">' + Joomla.JText._('COM_EMUNDUS_DOCUMENT_MERGE_TOOLTIP') + '</div>');}, 100);
                } else {
                    $('#merge-tooltips').empty();
                }
            }
        }
    })

    $(document).on('change', '#em-doc-pdf-merge', function() {
        if ($('#em-doc-pdf-merge').is(':checked')) {
            setTimeout(function() {$('#merge-tooltips').empty();}, 100);
            if ($('#em-doc-export-mode').val() == 0) {
                setTimeout(function(){$('#merge-tooltips').append('<div id="candidat-merge-tooltip" style="font-size: .8rem; color: #406AFF">' + Joomla.JText._('COM_EMUNDUS_CANDIDAT_MERGE_TOOLTIP') + '</div>');}, 100);
                $('#merge-tooltips').fadeIn();
            } else if ($('#em-doc-export-mode').val() == 1) {
                setTimeout(function(){$('#merge-tooltips').append('<div id="document-merge-tooltip" style="font-size: .8rem; color: #406AFF">' + Joomla.JText._('COM_EMUNDUS_DOCUMENT_MERGE_TOOLTIP') + '</div>');}, 100);
                $('#merge-tooltips').fadeIn();
            }
        } else {
            setTimeout(function() {$('#merge-tooltips').empty();}, 100);
        }
    })


    $(document).on('change', '#select_multiple_campaigns', function() {
        if ($("#select_multiple_campaigns :selected").length > 0) {
            $("#add-filter").prop("disabled", false);
        }
        else {
            $("#add-filter").prop("disabled", true);
        }
    });

    $(document).on('click', 'div[id^=showelements_]', function() {
        var id = $(this).attr('id').split('_')[1];

        var elements_block = document.getElementById('felts' + id);
        if (elements_block != null && elements_block.style.display == 'none') {
            $('#showelements_'+id+'_icon').css('transform', 'rotate(0deg)');
            $('#felts'+ id).toggle(300);
        } else {
            $('#showelements_'+id+'_icon').css('transform', 'rotate(-90deg)');
            $('#felts'+ id).toggle(300);
        }
    });

    $(document).on('click', '[id^=emundus_elm_]', function(e) {
        var eid = $(this).attr('id').split('emundus_elm_')[1];
        var elabel = $('label[for="emundus_elm_' + eid + '"]').text();
        var eclass = $(this).attr('class').split('_')[0];

        if($(this).is(":checked")) {
            if(eclass == 'emundusitem') {
                if($('#' + eid + '-item').length == 0) {
                    $('#em-export').append('<li class="em-export-item" id="' + eid + '-item"><span class="em-excel_elts em-flex-row"><span id="' + eid + '-itembtn" class="em-pointer fabrik-elt-delete material-icons em-red-500-color em-mr-4">delete_outline</span><p>' + elabel + '</p></span></li>');
                }
            }
        } else {
            $('#' + eid + '-item').remove();
        }
    })

    $(document).on('click', '[id^=emundus_checkall]', function() {
        var dataType = $(this).attr('data-check');
        var elements = $('#appelement').find('[id^=emundus_elm_]');

        if(dataType == '.emunduspage') {
            var profile_id = $(this).attr('id').split('emundus_checkall')[1];
            // if excel found
            if($('#appelement').length > 0) {
                if ($('#emundus_checkall' + profile_id).is(":checked")) {
                    $('#emundus_elements :input').prop('checked', true);

                    elements.each(function(e) {
                        var eclass = $(this).attr('class').split('_')[0];
                        if(eclass == 'emundusitem') {
                            var eid = $(this).attr('id').split('emundus_elm_')[1];
                            var elabel = $('label[for="emundus_elm_' + eid + '"]').text();

                            // check exist
                            if($('#' + eid + '-item').length == 0) {
                                $('#em-export').append('<li class="em-export-item" id="' + eid + '-item"><span class="em-excel_elts em-flex-row"><span id="' + eid + '-itembtn" class="em-pointer fabrik-elt-delete material-icons em-red-500-color em-mr-4">delete_outline</span><p>' + elabel + '</p></span></li>');
                            }
                        }
                    })
                } else {
                    $('#emundus_elements :input').prop('checked', false);

                    elements.each(function(e) {
                        var eclass = $(this).attr('class').split('_')[0];
                        if(eclass == 'emundusitem') {
                            var eid = $(this).attr('id').split('emundus_elm_')[1];

                            // remove all <li>
                            $('#' + eid + '-item').remove();
                        }
                    })
                }
            }

            /// if pdf found
            if($('#felts' + profile_id).length) {
                if ($('#emundus_checkall' + profile_id).is(":checked")) {
                    $('#felts' + profile_id + ' :input').attr('checked', true);
                } else {
                    $('#felts' + profile_id + ' :input').attr('checked', false);
                }
            }
        }
    });

    $(document).on('click', '[id^=emundus_checkall_tbl_]', function() {
        var id = $(this).attr('id').split('emundus_checkall_tbl_')[1];
        var is_checked = $('#emundus_checkall_tbl_' + id).is(":checked");

        /// find all sub-elements
        var groups = document.querySelectorAll('#emundus_table_' + id + ' [id^=emundus_checkall_grp_]')
        var elements = document.querySelectorAll('#emundus_table_' + id + ' [id^=emundus_elm_]')

        for(const group of groups){
            if(is_checked) {
                group.checked = true;
            } else {
                group.checked = false;
            }
        }

        for(const element of elements){
            var eclass = element.className.split('_')[0];

            if(is_checked) {
                element.checked = true;
            } else {
                element.checked = false;
            }

            if(eclass === 'emundusitem') {
                var eid = element.id.split('emundus_elm_')[1];
                var elabel = $('label[for="emundus_elm_' + eid + '"]').text();

                if($('#emundus_checkall_tbl_' + id).is(":checked")){
                    if($('#' + eid + '-item').length === 0) {
                        $('#em-export').append('<li class="em-export-item" id="' + eid + '-item"><span class="em-excel_elts em-flex-row"><span id="' + eid + '-itembtn" class="em-pointer fabrik-elt-delete material-icons em-red-500-color em-mr-4">delete_outline</span><p>' + elabel + '</p></span></li>');
                    }
                } else {
                    $('#' + eid + '-item').remove();
                }
            }
        }
    });

    $(document).on('click', '[id^=emundus_checkall_grp_]', function(){
        var id = $(this).attr('id').split('emundus_checkall_grp_')[1];

        var elements = $('#emundus_grp_' + id).find('[id^=emundus_elm_]');

        if($('#emundus_checkall_grp_' + id).is(":checked")) {
            $('#emundus_grp_' + id + " :input").attr('checked', true);

            elements.each(function(e) {
                var eclass = $(this).attr('class').split('_')[0];
                if(eclass == 'emundusitem') {
                    var eid = $(this).attr('id').split('emundus_elm_')[1];
                    var elabel = $('label[for="emundus_elm_' + eid + '"]').text();

                    // check exist
                    if($('#' + eid + '-item').length == 0) {
                        $('#em-export').append('<li class="em-export-item" id="' + eid + '-item"><span class="em-excel_elts em-flex-row"><span id="' + eid + '-itembtn" class="em-pointer fabrik-elt-delete material-icons em-red-500-color em-mr-4">delete_outline</span><p>' + elabel + '</p></span></li>');
                    }
                }
            })
        } else {
            $('#emundus_grp_' + id + " :input").attr('checked', false);

            elements.each(function(e) {
                var eclass = $(this).attr('class').split('_')[0];
                if(eclass == 'emundusitem') {
                    var eid = $(this).attr('id').split('emundus_elm_')[1];

                    // remove all <li>
                    $('#' + eid + '-item').remove();
                }
            })
        }
    });
});

function updateProfileForm(profile){
    document.querySelector('.em-light-selected-tab p').classList.remove('em-neutral-900-color');
    document.querySelector('.em-light-selected-tab p').classList.add('em-neutral-600-color');
    document.querySelector('.em-light-selected-tab').classList.remove('em-light-selected-tab');

    document.querySelector('#tab_link_'+profile).classList.add('em-light-selected-tab');
    document.querySelector('#tab_link_'+profile+' p').classList.remove('em-neutral-600-color');
    document.querySelector('#tab_link_'+profile+' p').classList.add('em-neutral-900-color');

    $('#show_profile').empty();
    $('#show_profile').before('<div id="loading"><img src="'+loading+'" alt="loading"/></div>');

    /* call to ajax */
    $.ajax({
        type: 'post',
        url: 'index.php?option=com_emundus&controller=application&task=getform',
        dataType: 'json',
        data: { profile: profile, user: $('#user_hidden').attr('value'), fnum: $('#fnum_hidden').attr('value') },
        success: function(result) {
            var form = result.data;

            $('#loading').remove();

            if(form) {
                $('#show_profile').append(form.toString());
                $('#download-pdf').attr('href', 'index.php?option=com_emundus&task=pdf&user=' + $('#user_hidden').attr('value') + '&fnum=' + $('#fnum_hidden').attr('value') + '&profile=' + profile);
            }

        }, error: function(jqXHR) {
            console.log(jqXHR.responseText);
        }
    })
}


async function sendMailQueue(fnums) {
    const steps = [1, 2];
    let currentStep;
    let body = '';
    let data = {};

    for (currentStep = 0; currentStep < 2;) {
        let title = '';
        let html = '';
        let type = '';
        let swal_confirm_button = 'COM_EMUNDUS_EMAILS_SEND_CUSTOM_EMAIL';
        let swal_container_class = 'em-export';
        let swal_popup_class = 'em-w-100 em-h-100';
        let swal_actions_class = 'em-actions-fixed';


        switch(currentStep) {
            case 0:
                title = 'COM_EMUNDUS_EMAILS_SEND_CUSTOM_EMAIL';
                html = '<div id="data" class="em-w-100"><div id="email-loader" class="em-loader" style="margin: auto;"></div></div>';
                swal_confirm_button = 'COM_EMUNDUS_EMAILS_EMAIL_PREVIEW_BEFORE_SEND';

                $.ajax({
                    type: 'POST',
                    url: 'index.php?option=com_emundus&view=message&format=raw',
                    data: {
                        fnums: fnums,
                        body: body,
                        data: data
                    },
                    success: function(result) {
                        $('#data').append(result);
                        $('#email-loader').remove();
                        $('#data').removeClass('em-loader');
                    },
                    error: function (jqXHR) {
                        $('#email-loader').remove();
                        console.log(jqXHR.responseText);
                    }
                });

                break;
            case 1:
                title = 'COM_EMUNDUS_EMAILS_EMAIL_PREVIEW';
                html = '<div id="email-recap"></div>';

                // update the textarea with the WYSIWYG content.
                tinymce.triggerSave();

                body = $('#mail_body').val();

                // Get all form elements.
                data = {
                    recipients      : $('#fnums').val(),
                    template        : $('#message_template :selected').val(),
                    mail_from_name  : $('#mail_from_name').text(),
                    mail_from       : $('#mail_from').text(),
                    reply_to_from   : $('#reply_to_from').text(),
                    mail_subject    : $('#mail_subject').text(),
                    message         : body,
                    bcc             : [],
                    cc              : [],
                    tags            : $('#tags').val(),
                };

                // cc emails
                $('#cc-box div[data-value]').each(function () {
                    var val = $(this).attr('data-value');
                    var REGEX_EMAIL = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

                    if(val.split(':')[0] === 'CC') {
                        val = $(this).attr('data-value').split('CC: ')[1];
                    }

                    if (REGEX_EMAIL.test(val)) { data.cc.push(val); }
                });

                // bcc emails
                $('#bcc-box div[data-value]').each(function () {
                    // var val = $(this).attr('data-value').split('BCC: ')[1];
                    var val = $(this).attr('data-value');
                    var REGEX_EMAIL = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

                    if(val.split(':')[0] === 'BCC') {
                        val = $(this).attr('data-value').split('BCC: ')[1];
                    }

                    if (REGEX_EMAIL.test(val)) { data.bcc.push(val); }
                });

                // Attachments object used for sorting the different attachment types.
                var attachments = {
                    upload : [],
                    candidate_file : [],
                    setup_letters : []
                };

                // Looping through the list and sorting attachments based on their type.
                var listItems = $("#em-attachment-list li");
                listItems.each(function(idx, li) {
                    var attachment = $(li);

                    if (attachment.hasClass('upload')) {
                        attachments.upload.push(attachment.find('.value').text());
                    } else if (attachment.hasClass('candidate_file')) {
                        attachments.candidate_file.push(attachment.find('.value').text());
                    } else if (attachment.hasClass('setup_letters')) {
                        attachments.setup_letters.push(attachment.find('.value').text());
                    }
                });

                data.attachments = attachments;

                $.ajax({
                    type: 'POST',
                    url: 'index.php?option=com_emundus&controller=messages&task=previewemail',
                    data: data,
                    success: function(result) {
                        result = JSON.parse(result);

                        if (result.status) {
                            $('#email-recap').append(result.html);
                        } else {
                            $('#email-recap').append( Joomla.JText._('ERROR_GETTING_PREVIEW'));
                        }
                    },
                    error: function() {
                        Swal.fire({
                            type: 'error',
                            title: Joomla.JText._('ERROR_GETTING_PREVIEW')
                        });
                    }
                });

                break;
            default:
                break;
        }

        const swalOptions = {
            position: 'center',
            title: Joomla.JText._(title),
            html: html,
            showCancelButton: currentStep > 0,
            currentProgressStep: currentStep,
            progressSteps: steps,
            confirmButtonText: Joomla.JText._(swal_confirm_button),
            cancelButtonText: Joomla.JText._('COM_EMUNDUS_ONBOARD_CANCEL'),
            showCloseButton: true,
            reverseButtons: true,
            customClass: {
                container: 'em-modal-actions ' + swal_container_class,
                popup: swal_popup_class,
                title: 'em-swal-title',
                cancelButton: 'em-swal-cancel-button',
                confirmButton: 'em-swal-confirm-button btn btn-success',
                actions: swal_actions_class
            },
        };

        if (type != '') {
            swalOptions.type = type;
        }

        const result = await Swal.fire(swalOptions);

        if (result.value) {
            currentStep++;
            if (currentStep === 2) {
                sendMail(data);

                break;
            }
        } else if (result.dismiss === 'cancel') {
            if (currentStep == 0) {
                removeLoader();
                Swal.close();
                break;
            } else {
                currentStep--;
            }
        } else if (result.dismiss === 'close') {
            removeLoader();
            Swal.close();
            break;
        }
    }
}

function sendMail(data)
{
    Swal.fire({
        position: 'center',
        title: Joomla.JText._('COM_EMUNDUS_EMAILS_SENDING_EMAILS'),
        html: '<div id="em-modal-sending-emails">' +
            '<img class="em-sending-email-img" id="em-sending-email-img" src="/media/com_emundus/images/sending-email.gif"/>' +
            '</div>',
        showCancelButton: false,
        showConfirmButton: false,
        customClass: {
            title: 'em-swal-title',
        },
    });

    $.ajax({
        type: 'POST',
        url: "index.php?option=com_emundus&controller=messages&task=applicantemail",
        data: data,
        success: function (result) {
            result = JSON.parse(result);

            if (result.status) {
                if (result.sent.length > 0) {
                    var sent_to = '<p>' + Joomla.JText._('SEND_TO') + '</p><ul class="list-group" id="em-mails-sent">';
                    result.sent.forEach(function (element) {
                        sent_to += '<li class="list-group-item alert-success">' + element + '</li>';
                    });

                    addLoader();

                    reloadData($('#view').val());
                    reloadActions($('#view').val(), undefined, false);

                    Swal.fire({
                        type: 'success',
                        title: Joomla.JText._('COM_EMUNDUS_EMAILS_EMAILS_SENT') + result.sent.length,
                        html: sent_to + '</ul>',
                        customClass: {
                            title: 'em-swal-title',
                            confirmButton: 'em-swal-confirm-button',
                            actions: "em-swal-single-action",
                        },
                    });

                } else {
                    Swal.fire({
                        type: 'error',
                        title: Joomla.JText._('COM_EMUNDUS_EMAILS_NO_EMAILS_SENT')
                    })
                }

                if (result.failed.length > 0) {
                    // Block containing the email adresses of the failed emails.
                    $("#em-email-messages").append('<div class="alert alert-danger">' + Joomla.JText._('COM_EMUNDUS_EMAILS_FAILED') + '<span class="badge">' + result.failed.length + '</span>' +
                        '<ul class="list-group" id="em-mails-failed"></ul>');

                    result.failed.forEach(function (element) {
                        $('#em-mails-sent').append('<li class="list-group-item alert-danger">' + element + '</li>');
                    });

                    $('#em-email-messages').append('</div>');
                }

            } else {
                $("#em-email-messages").append('<span class="alert alert-danger">' + Joomla.JText._('SEND_FAILED') + '</span>')
            }
        },
        error: function (jqXHR, textStatus) {
            if(textStatus == 'timeout') {

                var sent_to = '<p>' + Joomla.JText._('COM_EMUNDUS_MAILS_EMAIL_SENDING') + '</p>';

                $.ajax({
                    type: 'post',
                    url: 'index.php?option=com_emundus&controller=messages&task=addtagsbyfnums',
                    dataType: 'json',

                    data: { data: data },
                    success: function(tags) {
                        addLoader();

                        reloadData($('#view').val());
                        reloadActions($('#view').val(), undefined, false);

                        Swal.fire({
                            type: 'success',
                            title: Joomla.JText._('COM_EMUNDUS_EMAILS_EMAILS_SENT'),
                            html: sent_to,
                            customClass: {
                                title: 'em-swal-title',
                                confirmButton: 'em-swal-confirm-button',
                                actions: "em-swal-single-action",
                            },
                        });
                    }, error: function(jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                })
            } else {
                $("#em-email-messages").append('<span class="alert alert-danger">' + Joomla.JText._('SEND_FAILED') + '</span>')
            }
        },
        timeout: 5000
    });
}

function DoubleScroll(element) {
    const id = Math.random();
    if (element.scrollWidth > element.offsetWidth) {
        createScrollbarForElement(element, id);
    }

    window.addEventListener('resize', function () {
       let scrollbar = document.getElementById(id);
       if (scrollbar) {
           if (element.scrollWidth > element.offsetWidth) {
               scrollbar.firstChild.style.width = element.scrollWidth + 'px';
           } else {
               scrollbar.remove();
           }
       } else {
           if (element.scrollWidth > element.offsetWidth) {
               createScrollbarForElement(element, id);
           }
       }
    });
}

function createScrollbarForElement(element, id) {
    let new_scrollbar = document.createElement('div');
    new_scrollbar.appendChild(document.createElement('div'));
    new_scrollbar.style.overflowX = 'auto';
    new_scrollbar.style.overflowY = 'hidden';
    new_scrollbar.firstChild.style.height = '1px';
    new_scrollbar.firstChild.style.width = element.scrollWidth + 'px';
    new_scrollbar.firstChild.appendChild(document.createTextNode('\xA0'));
    new_scrollbar.id = id;
    new_scrollbar.classList.add('em-double-scroll-bar')
    let running = false;
    new_scrollbar.onscroll = function () {
        if (running) {
            running = false;
            return;
        }
        running = true;
        element.scrollLeft = new_scrollbar.scrollLeft;
    };
    element.onscroll = function () {
        if (running) {
            running = false;
            return;
        }
        running = true;
        new_scrollbar.scrollLeft = element.scrollLeft;
    };
    element.parentNode.insertBefore(new_scrollbar, element);
}

async function getExportPDFModel(model) {
    if (model) {
        return fetch('index.php?option=com_emundus&controller=files&task=getExportPdfFilterById&id=' + model)
            .then((response) => {
                if (response.ok) {
                    return response.json();
                }
            }).then((result) => {
                return result;
            }).catch((error) => {
                return {
                    status: false,
                    msg: error
                }
            });
    } else {
        return {
            status: false,
            msg: 'Missing parameters'
        }
    }
}

window.addEventListener('emundus-start-apply-filters', () => {
    addLoader();
});

window.addEventListener('emundus-apply-filters-success', () => {
     reloadData(document.getElementById('view').getAttribute('value'), false);
});