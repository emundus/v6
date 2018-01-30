/**
 * Created by yoan on 16/09/14.
 */

// load Menu action
function reloadActions(view)
{
	//addDimmer();
    var mutli = 0;
    multi = $('.em-check:checked').length;
    $.ajax({
        type: "GET",
        url: 'index.php?option=com_emundus&view=files&layout=menuactions&format=raw&Itemid=' + itemId + '&display=inline&multi='+multi,
        dataType: 'html',
        success: function(data)
        {
            //$('.em-dimmer').remove();
            //$(".col-md-9 .panel.panel-default").remove();

            $(".navbar.navbar-inverse").empty();
            $(".navbar.navbar-inverse").append(data);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
        }
    })
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

function getUserCheck()
{
	var id = parseInt($('.modal-body').attr('act-id'));
	if ($('#em-check-all-all').is(':checked'))
	{
		var checkInput = 'all';
	}
	else
	{
		var i = 0;
		var myJSONObject = '{';
		$('.em-check:checked').each(function()
		                            { i = i + 1;
			                            myJSONObject += '"'+i+'"'+':"'+$(this).attr('id').split('_')[0]+'",';
		                            });
		myJSONObject = myJSONObject.substr(0, myJSONObject.length-1);
		myJSONObject += '}';
		if(myJSONObject.length == 2)
		{
			alert('SELECT_FILES');
			return;
		}
		else
		{
			checkInput = myJSONObject;
		}

	}
	return checkInput;
}

function formCheck(id)
{
	if($('#'+id).val().trim().length == 0)
	{
		$('#'+id).parent('.form-group').addClass('has-error');
		$('#'+id+' help-block').remove();
		$('#'+id).after('<span class="help-block">'+Joomla.JText._('REQUIRED')+'</span>');
		return false;
	}
	else
	{
		var re = /^[0-9a-zA-Z\_\@\-\.]+$/;
		if(id == 'login' && !re.test($('#'+id).val()))
		{
			if(!$(this).parent('.form-group').hasClass('has-error'))
			{
				$(this).parent('.form-group').addClass('has-error');
				$('#'+id+' help-block').remove();
				$(this).after('<span class="help-block">'+Joomla.JText._('NOT_A_VALID_LOGIN_MUST_NOT_CONTAIN_SPECIAL_CHARACTER')+'</span>');
			}
			return false;
		}
		var remail = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-z\-0-9]+\.)+[a-z]{2,}))$/;
		if(id == 'mail' && !remail.test($('#'+id).val()))
		{
			if(!$(this).parent('.form-group').hasClass('has-error'))
			{
				$(this).parent('.form-group').addClass('has-error');
				$('#'+id+' help-block').remove();
				$(this).after('<span class="help-block">'+Joomla.JText._('NOT_A_VALID_EMAIL')+'</span>');
			}
			return false;
		}
		if($('#'+id).parent('.form-group').hasClass('has-error'))
		{
			$('#'+id).parent('.form-group').removeClass('has-error');
			$('#'+id+' help-block').remove();
		}
		return true;

	}


}

function reloadData() {
	addDimmer();
	$.ajax({
		       type: "GET",
		       url:'index.php?option=com_emundus&view=users&format=raw&layout=user&Itemid=' + itemId,
		       dataType: 'html',
		       success: function(data) {
			       $('.em-dimmer').remove();
			       $(".col-md-9 .panel.panel-default").empty();
			       $(".col-md-9 .panel.panel-default").append(data);
		       },
		       error: function(jqXHR, textStatus, errorThrown) {
			       console.log(jqXHR.responseText);
		       }
	       })
}

function addDimmer() {
    $('.row').before('<div class="em-dimmer"><img src="' + loading + '" alt=""/></div>');
}

function refreshFilter() {
	$.ajax({
		       type: "GET",
		       url:'index.php?option=com_emundus&view=users&format=raw&layout=filter&Itemid=' + itemId,
		       dataType: 'html',
		       success: function(data) {
			       $("#em-user-filters .panel-body").empty();
			       $("#em-user-filters .panel-body").append(data);
			       $('.chzn-select').chosen();

		       },
		       error: function(jqXHR, textStatus, errorThrown) {
			       console.log(jqXHR.responseText);
		       }
	       });
}

function tableOrder(order) {
	$.ajax({
		       type: 'POST',
		       url: 'index.php?option=com_emundus&controller=users&task=order',
		       dataType: 'json',
		       data:
		       {
			       filter_order: order
		       },
		       success: function(result) {
			       if (result.status) {
				       reloadData();
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
	$('.col-md-9.col-xs-16 .panel.panel-default.em-hide').each(function()
	                                                           {
		                                                           if(parseInt($(this).attr('id')) == parseInt(fnum))
		                                                           {
			                                                           exist = true;
			                                                           return;
		                                                           }
	                                                           })

	return exist;
}

function search(){
	var inputs = [{
		              name: 's',
		              value: $('#text_s').val(),
		              adv_fil : false
	              }];
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
		       url: 'index.php?option=com_emundus&controller=users&task=setfilters&1',
		       data: ({
			       val: JSON.stringify(inputs),
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

$(document).ready(function()
                  {
						reloadData();
	                    refreshFilter();
	                  var lastVal = new Object();
	                  $(document).on('click', function()
	                  {
		                  if (!$('ul.dropdown-menu.open').hasClass('just-open'))
		                  {
			                  $('ul.dropdown-menu.open').hide();
			                  $('ul.dropdown-menu.open').removeClass('open');
		                  }
	                  });

	                  $(document).on('change', '.em-filt-select', function(event)
	                  {
		                  if (event.handle !== true)
		                  {
			                  event.handle = true;
			                    search();
		                  }
	                  });
	                  /*$(document).on('click', '#em-data thead th strong', function(e) {
		                  if (e.handle !== true)
		                  {
			                  var id = $(this).parent('th').attr('id');
			                  e.handle = true;
                              alert(id);
                              if (id != 'check') {
                                  tableOrder(id);
                              }
		                  }
	                  });*/
	                  $(document).on('click', 'input:button', function(e) {
		                  if (e.event !== true) {
			                  e.handle = true;
			                  var name = $(this).attr('name');
			                  switch (name) {
				                  case 'clear-search':
					                  lastVal = new Object();
					                  $.ajax({
						                         type: 'POST',
						                         url: 'index.php?option=com_emundus&controller=users&task=clear',
						                         dataType: 'json',
						                         success: function(result) {
							                         if (result.status) {
								                         refreshFilter();
								                         reloadData();
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
		                  if (e.handle !== true) {
			                  e.handle = true;
			                  var id = $(this).attr('id');
			                  $.ajax({
				                         type: "POST",
				                         url: 'index.php?option=com_emundus&controller=users&task=setlimitstart',
				                         dataType: 'json',
				                         data: ({
					                         limitstart: id
				                         }),
				                         success: function(result) {
					                         if (result.status) {
						                         reloadData();
					                         }
				                         }
			                         });
		                  }
	                  });
	                  $(document).on('click', '#em-last-open .list-group-item', function(e)
	                  {
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
				                         url:'index.php?option=com_emundus&controller=users&task=getfnuminfos',
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
	                  $(document).on('click', 'button', function(e) {
		                  if (e.handle != true) {
			                  e.handle = true;
			                  var id = $(this).attr('id');
			                  switch (id) {
				                  case 'save-filter':
					                  var filName = prompt(filterName);
					                  if (filName != "")
					                  {
						                  $.ajax(
							                  {
						                         type: 'POST',
						                         url: 'index.php?option=com_emundus&controller=users&task=savefilters&Itemid=' + itemId,
						                         dataType: 'json',
						                         data: ({
							                         name: filName
						                         }),
						                         success: function(result)
						                         {
							                         if (result.status)
							                         {
								                         $('#select_filter').append('<option id="' + result.filter.id + '" selected="">' + result.filter.name + '<option>');
								                         $("#select_filter").trigger("chosen:updated");
								                         $('#saved-filter').show();
								                         setTimeout(function(e)
								                                    {
									                                    $('#saved-filter').hide();
								                                    }, 600);

							                         }
							                         else
							                         {
								                         $('#error-filter').show();
								                         setTimeout(function(e)
								                                    {
									                                    $('#error-filter').hide();
								                                    }, 600);
							                         }
						                         },
						                         error: function(jqXHR, textStatus, errorThrown) {
							                         console.log(jqXHR.responseText);
						                         }
					                         })
					                  }
					                  else
					                  {
						                  alert(filterEmpty);
						                  filName = prompt(filterName, "name");
					                  }
					                  break;
				                  case 'del-filter':
					                  var id = $('#select_filter').val();

					                  if (id != 0) {
						                  $.ajax({
							                         type: 'POST',
							                         url: 'index.php?option=com_emundus&controller=users&task=deletefilters&Itemid=' + itemId,
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
				                  case 'em-close-file':
				                  case 'em-mini-file':
					                  $('.em-open-files').remove();
					                  $('.em-hide').hide();
					                  $('#em-last-open').show();
					                  $('#em-last-open .list-group .list-group-item').removeClass('active');
					                  $('#em-user-filters').show();
					                  $('.em-check:checked').prop('checked', false);
					                  $(".col-md-9 .panel.panel-default").show();
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
						                         url:'index.php?option=com_emundus&controller=users&task=getfnuminfos',
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
							                         url:'index.php?option=com_emundus&controller=users&task=deletefile',
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
										                         $('#em-user-filters').show();
										                         $('.em-check:checked').prop('checked', false);
										                         $(".col-md-9.col-xs-16 .panel.panel-default").show();
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

	                  $(document).on('change', '#pager-select', function(e) {
		                  if (e.handle !== true) {
			                  e.handle = true;
			                  $.ajax({
				                         type: 'POST',
				                         url: 'index.php?option=com_emundus&controller=users&task=setlimit',
				                         dataType: 'json',
				                         data: ({
					                         limit: $(this).val()
				                         }),
				                         success: function(result)
				                         {
					                         if (result.status) {
						                         reloadData();
					                         }
				                         }
			                         });
		                  }
	                  });
	                 /* $(document).on('keyup', 'input:text', function(e) {
		                  if (e.keyCode == 13) {
			                  var id = $(this).attr('id');
			                  var test = id.split('-');
			                  test.pop();
			                  if (test.join('-') == 'em-adv-fil') {
				                  var elements_son = true;
			                  } else {
				                  var elements_son = false;
			                  }
			                  $.ajax({
				                         type: "POST",
				                         dataType: 'json',
				                         url: 'index.php?option=com_emundus&controller=users&task=setfilters&2',
				                         data: ({
					                         id: $('#' + id).attr('name'),
					                         val: $('#' + id).val(),
					                         multi: false,
					                         elements: elements_son
				                         }),
				                         success: function(result) {
					                         if (result.status) {
						                         reloadData();
					                         }
				                         },
				                         error: function(jqXHR, textStatus, errorThrown) {
					                         console.log(jqXHR.responseText);
				                         }
			                         });
		                  }
	                  });*/
	                  $(document).on('change', '#select_filter', function(e) {
		                  var id = $(this).attr('id');
		                  var val = $('#' + id).val();
		                  $.ajax({
			                         type: "POST",
			                         dataType: 'json',
			                         url: 'index.php?option=com_emundus&controller=users&task=setfilters&3',
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
						                                url: 'index.php?option=com_emundus&controller=users&task=loadfilters',
						                                data: {
							                                id: val
						                                },
						                                success: function(result) {
							                                if (result.status) {
								                                refreshFilter();

								                                reloadData();
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
		                  var fId = $(this).parent('fieldset').attr('id');
		                  var index = fId.split('-');

		                  var sonName = $('#em-adv-fil-' + index[index.length - 1]).attr('name');

		                  $('#' + fId).remove();
		                  $.ajax(
			                  {
				                  type: 'POST',
				                  url: 'index.php?option=com_emundus&controller=users&task=deladvfilter',
				                  dataType: 'json',
				                  data: ({
					                  elem: sonName,
					                  id: index[index.length - 1]
				                  }),
				                  success: function(result)
				                  {
					                  if (result.status)
					                  {
						                  reloadData();
					                  }
				                  },
				                  error: function(jqXHR, textStatus, errorThrown)
				                  {
					                  console.log(jqXHR.responseText);
				                  }
			                  })

	                  });
	                  $(document).on('click', '.em-dropdown', function(e)
	                  {
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
		                  var id = parseInt($(this).attr('id'));
		                  var url = $(this).attr('url');
		                  $('#em-modal-form').modal({backdrop:true},'toggle');


		                  $('.modal-title').empty();
		                  $('.modal-title').append($(this).children('a').text());
		                  $('.modal-body').empty();
		                  if($('.modal-dialog').hasClass('modal-lg'))
		                  {
			                  $('.modal-dialog').removeClass('modal-lg');
		                  }
		                  $('.modal-body').attr('act-id', id);
		                  $('.modal-footer').show();


		                  $('.modal-footer').append('<div>' +
			                                            '<p>'+jtextArray[2]+'</p>' +
			                                            '<img src="'+loadingLine+'" alt="loading"/>' +
			                                            '</div>');

		                  $('.modal-footer').hide();

		                  $('.modal-dialog').addClass('modal-lg');
		                  $(".modal-body").empty();

		                  $(".modal-body").append('<iframe src="'+url+'" style="width:100%; height:720px; border:none"></iframe>');
	                  });
//
// Menu action
//
	                  $(document).on('click', '.em-actions', function(e)
	                  { 
	                  	  e.preventDefault();
		                  var id = parseInt($(this).attr('id').split('|')[3]); 
		                  $('#em-modal-actions').modal({backdrop:false},'toggle');
		                  $('.modal-title').empty();
		                  $('.modal-title').append($(this).children('a').text());
		                  $('.modal-body').empty();
		                  if($('.modal-dialog').hasClass('modal-lg'))
		                  {
			                  $('.modal-dialog').removeClass('modal-lg');
		                  }
		                  $('.modal-body').attr('act-id', id);
		                  $('.modal-footer').show();

		                  var view = $('#view').val();
		                  var sid = 0;
		                  if ($('.em-check:checked').length != 0)
       					  	sid = $('.em-check:checked').attr('id').split('_')[0];
						  	
				       	  var url = $(this).children('a').attr('href'); 
				       	  var view = $('#view').val();

				          String.prototype.fmt = function (hash) {
				            var string = this, key; 
				            for (key in hash) string = string.replace(new RegExp('\\{' + key + '\\}', 'gm'), hash[key]); return string
				          }

				          url = url.fmt({ applicant_id: sid, view: view, controller: view, Itemid: itemId });

		                  switch (id)
		                  {
			                  
			                  case 19:
				                  //create group
			                  case 20:
				                  //create user
				              case 23:
				                  //affect
								   $.ajax(
					                  {
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
					                  })
				                  break;
				              case 24:
			                  	// edit user
				                  $.ajax(
					                  {
						                  type:'get',
						                  url:url,
						                  dataType:'html',
						                  data:{user:sid},
						                  success: function(result)
						                  {
						                  	  $('.modal-body').empty();
							                  $('.modal-body').append(result);
						                  },
						                  error: function (jqXHR, textStatus, errorThrown)
						                  {
							                  console.log(jqXHR.responseText);
						                  }
					                  })
			                    break;
							 
								/*case 29:
								// change current profile
								$.ajax(
									{
										type:'get',
										url:url,
										dataType:'html',
										data:{user:sid},
										success: function(result)
										{
											  $('.modal-body').empty();
											$('.modal-body').append(result);
										},
										error: function (jqXHR, textStatus, errorThrown)
										{
											console.log(jqXHR.responseText);
										}
									})
							  break;*/
								
			                  case 21:
				                  //activate
								   $('#em-modal-actions').modal('hide');
				                  var checkInput = getUserCheck();
				                  $.ajax(
					                  {
						                  type:'POST',
						                  url:url,
						                  dataType:'json',
						                  data:{users:checkInput, state: 0},
						                  success: function(result)
						                  {
							                  reloadData();
						                  },
						                  error: function (jqXHR, textStatus, errorThrown)
						                  {
							                  console.log(jqXHR.responseText);
							                  if (jqXHR.status === 302)
							                  {
								                  window.location.replace('/user');
							                  }
						                  }
					                  });
				                  break;
								  
			                  case 22:
				                  //desactivate
				                  $('#em-modal-actions').modal('hide');
				                  var checkInput = getUserCheck();
				                  $.ajax(
					                  {
						                  type:'POST',
						                  url:url,
						                  dataType:'json',
						                  data:{users:checkInput, state: 1},
						                  success: function(result)
						                  {
							                  reloadData();
						                  },
						                  error: function (jqXHR, textStatus, errorThrown)
						                  {
							                  console.log(jqXHR.responseText);
							                  if (jqXHR.status === 302)
							                  {
								                  window.location.replace('/user');
							                  }
						                  }
					                  });
				                  break;
			                  
			                  case 25:
			                  		// Show user rights
				                  $('.modal-dialog').addClass('modal-lg');
				                  $.ajax(
					                  {
						                  type:'get',
						                  url:url,
						                  dataType:'html',
						                  data:{user:sid},
						                  success: function(result)
						                  {
						                  	  $('.modal-body').empty();
							                  $('.modal-body').append(result);
						                  },
						                  error: function (jqXHR, textStatus, errorThrown)
						                  {
							                  console.log(jqXHR.responseText);
						                  }
					                  })
				                  break;
			                  
			                  case 26:
			                  		// delete user
									$('.modal-body').empty();
									$('.modal-body').append('<strong>'+Joomla.JText._('ARE_YOU_SURE_TO_DELETE_USERS')+'</strong>');
				                  break;
		                  }

	                  });
	                  
// Button on Actions
	                  $(document).on('click', '#em-modal-actions .btn.btn-success', function(e)
	                  {
		                  var id = parseInt($('.modal-body').attr('act-id'));
		                  if ($('#em-check-all-all').is(':checked'))
		                  {
			                  var checkInput = 'all';
		                  }
		                  else
		                  {
			                  var i = 0;
			                  var myJSONObject = '{';
			                  $('.em-check:checked').each(function()
			                                              { i = i + 1;
				                                              myJSONObject += '"'+i+'"'+':"'+$(this).attr('id').split('_')[0]+'",';
			                                              });
			                  myJSONObject = myJSONObject.substr(0, myJSONObject.length-1);
			                  myJSONObject += '}';
			                  if(myJSONObject.length == 2)
			                  {
				                  alert('SELECT_FILES');
				                  return;
			                  }
			                  else
			                  {
				                  checkInput = myJSONObject;
			                  }

		                  }
		                  if($('.modal-body .em-dimmer').is(':visible'))
		                  {
			                  return false;
		                  }

		                  switch (id)
		                  {
			                  case 19:

				                  if(!formCheck('gname') || !formCheck('gdescription'))
				                  {
					                  return false;
				                  }

				                  if($('#gprogs') == null)
				                  {
					                  $('#gprogs').parent('.form-group').addClass('has-error');
					                  $('#gprogs').after('<span class="help-block">'+Joomla.JText._('SELECT_A_VALUE')+'</span>');
					                  return false;
				                  }
				                  else
				                  {
					                  var progs = "";
					                  for(var i = 0; i < $("#gprogs").val().length ; i++)
					                  {
						                  progs += $("#gprogs").val()[i];
						                  progs += ',';
					                  }
				                  }


				                  var actionsCheck = [];
				                  var tableSize = parseInt($('.em-actions-table-line').parent('tbody').attr('size'));
				                  $('.em-actions-table-line').each(function()
				                                                   {
					                                                   var actLine = new Object();
					                                                   $(this).children('td').each(function()
					                                                                               {
						                                                                               if($(this).hasClass('em-has-checkbox'))
						                                                                               {
							                                                                               var id = $(this).attr('id').split('-');
							                                                                               switch(id[0])
							                                                                               {
								                                                                               case 'c':
									                                                                               id = id.join('-');
									                                                                               if($(this).children('input[name="'+id+'"]').is(':checked'))
									                                                                               {
										                                                                               actLine.c = 1;
									                                                                               }
									                                                                               else
									                                                                               {
										                                                                               actLine.c = 0;
									                                                                               }
									                                                                               break;
								                                                                               case 'r':
									                                                                               id = id.join('-');
									                                                                               if($(this).children('input[name="'+id+'"]').is(':checked'))
									                                                                               {
										                                                                               actLine.r = 1;
									                                                                               }
									                                                                               else
									                                                                               {
										                                                                               actLine.r = 0;
									                                                                               }
									                                                                               break;
								                                                                               case 'u':
									                                                                               id = id.join('-');
									                                                                               if($(this).children('input[name="'+id+'"]').is(':checked'))
									                                                                               {
										                                                                               actLine.u = 1;
									                                                                               }
									                                                                               else
									                                                                               {
										                                                                               actLine.u = 0;
									                                                                               }
									                                                                               break;
								                                                                               case 'd':
									                                                                               id = id.join('-');
									                                                                               if($(this).children('input[name="'+id+'"]').is(':checked'))
									                                                                               {
										                                                                               actLine.d = 1;
									                                                                               }
									                                                                               else
									                                                                               {
										                                                                               actLine.d = 0;
									                                                                               }
									                                                                               break;
							                                                                               }
						                                                                               }
						                                                                               else if($(this).hasClass('em-no'))
						                                                                               {
							                                                                               if($(this).hasClass('no-action-c'))
								                                                                               actLine.c = 0
							                                                                               else if($(this).hasClass('no-action-r'))
								                                                                               actLine.r = 0;
							                                                                               else if($(this).hasClass('no-action-u'))
								                                                                               actLine.u = 0;
							                                                                               else
								                                                                               actLine.d = 0;
						                                                                               }
						                                                                               else
						                                                                               {
							                                                                               actLine.id = $(this).attr('id');
						                                                                               }
					                                                                               })
					                                                   actionsCheck.push(actLine);
					                                                   if(actionsCheck.length == tableSize)
					                                                   {
						                                                  return false;
					                                                   }
				                                                   });


				                  actionsCheck = JSON.stringify(actionsCheck);
				                  $('.modal-body').prepend('<div class="em-dimmer"><img src="' + loading + '" alt=""/></div>');

				                 $.ajax(
					                  {
						                  type:'POST',
						                  url:$('#em-add-group').attr('action'),
						                  data:
						                  {
							                  gname:$('#gname').val(),
							                  gdesc:$('#gdescription').val(),
							                  actions:actionsCheck,
							                  gprog:progs.substr(0 , progs.length - 1)
						                  },
						                  dataType:'json',
						                  success: function(result)
						                  {
							                  $('.modal-body .em-dimmer').remove();
							                  if(result.status)
							                  {
								                  $('#em-add-group').before('<div class="alert alert-dismissable alert-success">' +
									                                           '<button type="button" class="close" data-dismiss="alert">×</button>' +
									                                           '<strong>'+result.msg+'</strong> ' +
									                                           '</div>');
								                  setTimeout(function(){$('#em-modal-actions').modal('hide');},500);

							                  }
							                  else
							                  {
								                  $('#em-add-group').before('<div class="alert alert-dismissable alert-danger">' +
									                                           '<button type="button" class="close" data-dismiss="alert">×</button>' +
									                                           '<strong>'+result.msg+'</strong> ' +
									                                           '</div>');
							                  }
						                  },
						                  error: function (jqXHR, textStatus, errorThrown)
						                  {
							                  console.log(jqXHR.responseText);
						                  }
					                  });
				                  break;
			                  case 20:
								  var groups = "";
								  var campaigns = "";
								  var oprofiles = "";

				                  if($("#groups").val() != null && $("#groups").val().length > 0)
				                  {
					                  for(var i = 0; i < $("#groups").val().length ; i++)
					                  {
						                  groups += $("#groups").val()[i];
						                  groups += ',';
					                  }
				                  }
				                  if($("#campaigns").val() && $("#campaigns").val().length > 0)
				                  {
					                  for(var i = 0; i < $("#campaigns").val().length ; i++)
					                  {
						                  campaigns+= $("#campaigns").val()[i];
						                  campaigns+= ',';
					                  }
								  }
								  if($("#oprofiles").val() && $("#oprofiles").val().length > 0)
				                  {
					                  for(var i = 0; i < $("#oprofiles").val().length ; i++)
					                  {
						                  oprofiles+= $("#oprofiles").val()[i];
										  oprofiles+= ',';
										  //alert($("#oprofiles").val()[i]);
					                  }
				                  }
							      var login = $('#login').val();
							      var fn = $('#fname').val();
							      var ln = $('#lname').val();
							      var email = $('#mail').val();
							      var profile = $('#profiles').val();
								  
				                 if(!formCheck('fname') || !formCheck('lname') || !formCheck('login') || !formCheck('mail'))
				                 {
					                 return false;
				                 }
								 if(profile == "0")
								 {
									 $('#profiles').parent('.form-group').addClass('has-error');
									 $('#profiles').after('<span class="help-block">'+Joomla.JText._('SELECT_A_VALUE')+'</span>');
									 return false;
								 }
				                  $('.modal-body').prepend('<div class="em-dimmer"><img src="' + loading + '" alt=""/></div>');

				                  $.ajax(
					                  {
						                 type:'POST',
						                 url:$('#em-add-user').attr('action'),
						                 data:{
							                 login:login,
							                 firstname:fn,
							                 lastname:ln,
											 campaigns:campaigns.substr(0 , campaigns.length - 1),
											 oprofiles:oprofiles.substr(0 , oprofiles.length - 1),											 
							                 groups:groups.substr(0, groups.length - 1),
							                 profile:profile,
							                 jgr:$('#profiles option:selected').attr('id'),
							                 email:email,
							                 newsletter:$('#news').is(':checked')?1:0,
							                 university_id:$('#univ').val()
						                 },
						                 dataType:'json',
						                 success: function(result)
						                 {
							                 $('.modal-body .em-dimmer').remove();

							                 if(result.status)
							                 {
								                 $('.modal-body').prepend('<div class="alert alert-dismissable alert-success">' +
									                                          '<button type="button" class="close" data-dismiss="alert">×</button>' +
									                                          '<strong>'+result.msg+'</strong> ' +
																			  '</div>');												 
								                 setTimeout(function(){$('#em-modal-actions').modal('hide');},500);
												 reloadData();
							                 }
							                 else
							                 {
								                 $('.modal-body').prepend('<div class="alert alert-dismissable alert-danger">' +
									                                          '<button type="button" class="close" data-dismiss="alert">×</button>' +
									                                          '<strong>'+result.msg+'</strong> ' +
									                                          '</div>');
							                 }

						                 },
						                  error: function (jqXHR, textStatus, errorThrown)
						                  {
						                      console.log(jqXHR.responseText);
						                  }
					                  });

				                  break;

			                  case 23: 
			                  	// button action affect user to group(s)
				                  var checkInput = getUserCheck();
				                  if($('#agroups') == null)
				                  {
					                  $('#agroups').parent('.form-group').addClass('has-error');
					                  $('#agroups').after('<span class="help-block">'+Joomla.JText._('SELECT_A_GROUP')+'</span>');
					                  return false;
				                  }
				                  else
				                  {
					                  var groups = "";
					                  for(var i = 0; i < $("#agroups").val().length ; i++)
					                  {
						                  groups += $("#agroups").val()[i];
						                  groups += ',';
					                  }
				                  }
				                  //$('.modal-body').prepend('<div class="em-dimmer"><img src="' + loading + '" alt=""/></div>').hide();

				                  $.ajax(
					                  {
						                  type:'POST',
						                  url:$('#em-affect-groups').attr('action'),
						                  data:{users:checkInput, groups: groups.substr(0, groups.length - 1)},
						                  dataType:'json',
						                  success: function(result)
						                  {
							                  $('.modal-body .em-dimmer').remove();

							                  if(result.status)
							                  {
								                  $('.modal-body').prepend('<div class="alert alert-dismissable alert-success">' +
									                                           '<button type="button" class="close" data-dismiss="alert">×</button>' +
									                                           '<strong>'+result.msg+'</strong> ' +
									                                           '</div>');
								                  setTimeout(function(){$('#em-modal-actions').modal('hide');},500);

							                  }
							                  else
							                  {
								                  $('.modal-body').prepend('<div class="alert alert-dismissable alert-danger">' +
									                                           '<button type="button" class="close" data-dismiss="alert">×</button>' +
									                                           '<strong>'+result.msg+'</strong> ' +
									                                           '</div>');
							                  }

						                  },
						                  error: function (jqXHR, textStatus, errorThrown)
						                  {
							                  console.log(jqXHR.responseText);
						                  }
					                  });
				                  break;
			                  case 24:
				                  var groups = "";
								  var campaigns = "";
								  var oprofiles = "";								  
								
				                  if($("#groups").val() != null && $("#groups").val().length > 0)
				                  {
					                  for(var i = 0; i < $("#groups").val().length ; i++)
					                  {
						                  groups += $("#groups").val()[i];
						                  groups += ',';
					                  }
				                  }
				                  if($("#campaigns").val() && $("#campaigns").val().length > 0)
				                  {
					                  for(var i = 0; i < $("#campaigns").val().length ; i++)
					                  {
						                  campaigns+= $("#campaigns").val()[i];
						                  campaigns+= ',';
					                  }
								  }
								  if($("#oprofiles").val() && $("#oprofiles").val().length > 0)
				                  {
					                  for(var i = 0; i < $("#oprofiles").val().length ; i++)
					                  {
						                  oprofiles+= $("#oprofiles").val()[i];
										  oprofiles+= ',';
										  //alert($("#oprofiles").val()[i]);
					                  }
				                  }
				                  var login = $('#login').val();
				                  var fn = $('#fname').val();
				                  var ln = $('#lname').val();
				                  var email = $('#mail').val();
				                  var profile = $('#profiles').val();

				                  if(!formCheck('fname') || !formCheck('lname') || !formCheck('login') || !formCheck('mail'))
				                  {
					                  return false;
				                  }
				                  if(profile == "0")
				                  {
					                  $('#profiles').parent('.form-group').addClass('has-error');
					                  $('#profiles').after('<span class="help-block">'+Joomla.JText._('SELECT_A_VALUE')+'</span>');
					                  return false;
				                  }
				                  $('.modal-body').prepend('<div class="em-dimmer"><img src="' + loading + '" alt=""/></div>');

				                  $.ajax(
					                  {
						                  type:'POST',
						                  url:$('#em-add-user').attr('action'),
						                  data:{
							                  id:$('.em-check:checked').attr('id').split('_')[0],
							                  login:login,
							                  firstname:fn,
							                  lastname:ln,
											  campaigns:campaigns.substr(0 , campaigns.length - 1),
											  oprofiles:oprofiles.substr(0 , oprofiles.length - 1),											  
							                  groups:groups.substr(0, groups.length - 1),
							                  profile:profile,
							                  jgr:$('#profiles option:selected').attr('id'),
							                  email:email,
							                  newsletter:$('#news').is(':checked')?1:0,
							                  university_id:$('#univ').val()},
						                  dataType:'json',
						                  success: function(result)
						                  {
							                  $('.modal-body .em-dimmer').remove();

							                  if(result.status)
							                  {
								                  $('.modal-body').prepend('<div class="alert alert-dismissable alert-success">' +
									                                           '<button type="button" class="close" data-dismiss="alert">×</button>' +
									                                           '<strong>'+result.msg+'</strong> ' +
									                                           '</div>');
								                  setTimeout(function(){$('#em-modal-actions').modal('hide');},500);
								                  reloadData();

							                  }
							                  else
							                  {
								                  $('.modal-body').prepend('<div class="alert alert-dismissable alert-danger">' +
									                                           '<button type="button" class="close" data-dismiss="alert">×</button>' +
									                                           '<strong>'+result.msg+'</strong> ' +
									                                           '</div>');
							                  }

						                  },
						                  error: function (jqXHR, textStatus, errorThrown)
						                  {
							                  console.log(jqXHR.responseText);
						                  }
					                  });

				                  break;
			                  case 26:
				                  var checkInput = getUserCheck();
				                  $('.modal-body').prepend('<div class="em-dimmer"><img src="' + loading + '" alt=""/></div>');
				                  $.ajax(
					                  {
						                  type:'POST',
						                  url:'index.php?option=com_emundus&controller=users&task=deleteusers&Itemid='+itemId,
						                  data:{users:checkInput},
						                  dataType:'json',
						                  success: function(result)
						                  {
							                  $('.modal-body .em-dimmer').remove();

							                  if(result.status)
							                  {
								                  $('.modal-body').prepend('<div class="alert alert-dismissable alert-success">' +
								                                           '<button type="button" class="close" data-dismiss="alert">×</button>' +
								                                           '<strong>'+result.msg+'</strong> ' +
								                                           '</div>');
								                  reloadData();
								                  setTimeout(function(){$('#em-modal-actions').modal('hide');},500);

							                  }
							                  else
							                  {
								                  $('.modal-body').prepend('<div class="alert alert-dismissable alert-danger">' +
								                                           '<button type="button" class="close" data-dismiss="alert">×</button>' +
								                                           '<strong>'+result.msg+'</strong> ' +
								                                           '</div>');
							                  }

						                  },
						                  error: function (jqXHR, textStatus, errorThrown)
						                  {
							                  console.log(jqXHR.responseText);
						                  }
					                  });
				                  break;
		                  }
	                  });

//action fin

	                  $(document).on('change', '#em-modal-actions #em-export-form', function(e)
	                  {
		                  if (e.handle !== true)
		                  {
			                  e.handle = true;
			                  var id = $(this).val();
			                  var text = $('#em-modal-actions #em-export-form option:selected').attr('data-value');
			                  $('#em-export').append('<li class="em-export-item" id="'+id+'-item"><strong>'+text+'</strong><button class="btn btn-danger btn-xs pull-right"><span class="glyphicon glyphicon-trash"></span></button></li>');
		                  }
	                  });

	                  $(document).on('click', '#em-export .em-export-item .btn.btn-danger', function(e)
	                  {
		                  $(this).parent('li').remove();
	                  });

	                  $(document).on('change', '.em-modal-check', function()
	                  {
		                  if($(this).hasClass('em-check-all'))
		                  {
			                  var id = $(this).attr('name').split('-');
			                  id.pop();
			                  id = id.join('-');
			                  if($(this).is(':checked'))
			                  {
				                  $(this).prop('checked', true);
				                  $('.'+id).prop('checked', true);
			                  }
			                  else
			                  {
				                  $(this).prop('checked', false);
				                  $('.'+id).prop('checked', false);
			                  }
		                  }
	                  });

	                  $(document).on('click', '#em-hide-filters', function()
	                  {
		                  if($('.col-md-3').is(':visible'))
		                  {
			                  $('.col-md-3').hide();
			                  $(this).children('span').addClass('glyphicon-chevron-right');
			                  $(this).children('span').removeClass('glyphicon-chevron-left');
			                  $('.col-md-9').addClass('col-md-12');
			                  $('.col-md-9').removeClass('col-md-9');

		                  }
		                  else
		                  {
			                  $('.col-md-3').show();
			                  $(this).children('span').removeClass('glyphicon-chevron-right');
			                  $(this).children('span').addClass('glyphicon-chevron-left');
			                  $('.col-md-12').addClass('col-md-9');
			                  $('.col-md-12').removeClass('col-md-12');
		                  }

	                  })


                  })