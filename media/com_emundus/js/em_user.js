/**
 * Created by yoan on 16/09/14.
 */

var lastIndex = 0;
var loading;

function reloadActions(view) {
	var multi = $('.em-check:checked').length;
	$.ajax({
		type: 'GET',
		url: 'index.php?option=com_emundus&view=files&layout=menuactions&format=raw&Itemid=' + itemId + '&display=inline&multi=' + multi,
		dataType: 'html',
		success: function (data) {
			let navbar = $('.navbar.navbar-inverse');
			navbar.empty();
			navbar.append(data);
		},
		error: function (jqXHR) {
			console.log(jqXHR.responseText);
		}
	});
}

/*function clearchosen(cible) {
	$(cible).val("%");
	$(cible).trigger('chosen:updated');
}*/

function clearchosen(target){
	$(target)[0].sumo.unSelectAll();
}

function getUserCheck() {
	var id = parseInt($('.modal-body').attr('act-id'));
	if ($('#em-check-all').is(':checked')) {
		var checkInput = 'all';
	} else {
		var i = 0;
		var myJSONObject = '{';
		$('.em-check:checked').each(function () {
			i = i + 1;
			myJSONObject += '"' + i + '"' + ':"' + $(this).attr('id').split('_')[0] + '",';
		});
		myJSONObject = myJSONObject.substr(0, myJSONObject.length - 1);
		myJSONObject += '}';
		if (myJSONObject.length == 2) {
			alert('SELECT_FILES');
			return;
		} else {
			checkInput = myJSONObject;
		}

	}
	return checkInput;
}



function formCheck(id) {
	let check = true;
	let field = document.querySelector('#' + id);
	let form_group = field.parentElement;
	let help_block = document.querySelector('.em-addUser-detail-info-'+id+' .help-block');

	field.style.border = null;

	if (id === 'login') {
		let same_as_email = document.querySelector('#same_login_email');
		if (same_as_email && same_as_email.checked) {
			check = false;
		}
	}

	if (field.value.trim().length === 0 && check) {
		if(form_group) {
			form_group.classList.add('has-error');
		}
		field.style.border = '1px solid var(--red-500)';

		if(help_block) {
			help_block.remove();
			field.insertAdjacentHTML('afterend', '<span class="help-block">' + Joomla.JText._('NOT_A_VALID_LOGIN_MUST_NOT_CONTAIN_SPECIAL_CHARACTER') + '</span>');
		}

		return false;
	}
	else
	{
		let remail = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-z\-0-9]+\.)+[a-z]{2,}))$/;
		let re = /^[0-9a-zA-Z\_\@\-\.\+]+$/;

		if (id === 'login' && check)
		{
			if(!re.test(field.value)) {
				if(form_group) {
					form_group.classList.add('has-error');
				}
				field.style.border = '1px solid var(--red-500)';

				if(help_block) {
					help_block.remove();
				}

				field.insertAdjacentHTML('afterend', '<span class="help-block">' + Joomla.JText._('NOT_A_VALID_LOGIN_MUST_NOT_CONTAIN_SPECIAL_CHARACTER') + '</span>');

				return false;
			}
		}

		if (id === 'mail' && !remail.test(field.value))
		{
			if(form_group) {
				form_group.classList.add('has-error');
			}
			field.style.border = '1px solid var(--red-500)';

			if(help_block) {
				help_block.remove();
			}

			field.insertAdjacentHTML('afterend', '<span class="help-block">' + Joomla.JText._('COM_EMUNDUS_USERS_ERROR_NOT_A_VALID_EMAIL') + '</span>');

			return false;
		}

		if(form_group && form_group.classList.contains('has-error')) {
			form_group.classList.remove('has-error');
		}

		return true;
	}
}

function reloadData(loader = true) {
	loader ? addLoader() : '';

	$.ajax({
		type: 'GET',
		url: 'index.php?option=com_emundus&view=users&format=raw&layout=user&Itemid=' + itemId,
		dataType: 'html',
		success: function (data) {
			loader ? removeLoader() : '';
			$('.col-md-9 .panel.panel-default').empty();
			$('.col-md-9 .panel.panel-default').append(data);

			reloadActions($('#view').val(), undefined, false);
		},
		error: function (jqXHR) {
			removeLoader();
			console.log(jqXHR.responseText);
		}
	});
}

function refreshFilter() {
	$.ajax({
		type: 'GET',
		url: 'index.php?option=com_emundus&view=users&format=raw&layout=filter&Itemid=' + itemId,
		dataType: 'html',
		success: function (data) {
			$("#em-user-filters .panel-body").empty();
			$("#em-user-filters .panel-body").append(data);
			$('.chzn-select').chosen();
			reloadData();
		},
		error: function (jqXHR) {
			console.log(jqXHR.responseText);
		}
	});
}

function tableOrder(order) {
	$.ajax({
		type: 'POST',
		url: 'index.php?option=com_emundus&controller=users&task=order',
		dataType: 'json',
		data: {
			filter_order: order
		},
		success: function (result) {
			if (result.status) {
				reloadData();
			}
		},
		error: function (jqXHR) {
			console.log(jqXHR.responseText);
		}
	});
}

function exist(fnum) {
	var exist = false;
	$('.col-md-9.col-xs-16 .panel.panel-default.em-hide').each(function () {
		if (parseInt($(this).attr('id')) == parseInt(fnum)) {
			exist = true;
			return;
		}
	});

	return exist;
}

function search() {

	var quick = [];
	$('div[data-value]').each( function () {
		quick.push($(this).attr('data-value'));
	});
	var inputs = [{
		name: 's',
		value: quick,
		adv_fil: false
	}];

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
		url: 'index.php?option=com_emundus&controller=users&task=setfilters&1',
		data: ({
			val: JSON.stringify(inputs),
			multi: false,
			elements: true
		}),
		success: function (result) {
			if (result.status) {
				reloadData($('#view').val());
			}
		},
		error: function (jqXHR) {
			console.log(jqXHR.responseText);
		}
	});
}

$(document).ready(function () {
	reloadData();
	refreshFilter();
	var lastVal = new Object();
	$(document).on('click', function () {
		if (!$('ul.dropdown-menu.open').hasClass('just-open')) {
			$('ul.dropdown-menu.open').hide();
			$('ul.dropdown-menu.open').removeClass('open');
		}
	});

	$(document).on('change', '.em-filt-select', function (event) {
		if (event.handle !== true) {
			event.handle = true;
			search();
		}
	});

	$(document).on('click', 'input:button', function (e) {
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
						success: function (result) {
							if (result.status) {
								refreshFilter();
							}
						},
						error: function (jqXHR) {
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
	$(document).on('click', '.pagination.pagination-sm li a', function (e) {
		if (e.handle !== true) {
			e.handle = true;
			var id = $(this).attr('id');
			$.ajax({
				type: 'POST',
				url: 'index.php?option=com_emundus&controller=users&task=setlimitstart',
				dataType: 'json',
				data: ({
					limitstart: id
				}),
				success: function (result) {
					if (result.status) {
						reloadData();
					}
				}
			});
		}
	});
	$(document).on('click', '#em-last-open .list-group-item', function (e) {
		if (e.handle !== true) {
			e.handle = true;
			var fnum = new Object();
			fnum.fnum = $(this).attr('title');
			fnum.sid = parseInt(fnum.fnum.substr(21, 7));
			fnum.cid = parseInt(fnum.fnum.substr(14, 7));
			$('.em-check:checked').prop('checked', false);

			$('#' + fnum.fnum + '_check').prop('checked', true);

			$.ajax({
				type: 'get',
				url: 'index.php?option=com_emundus&controller=users&task=getfnuminfos',
				dataType: 'json',
				data: ({
					fnum: fnum.fnum
				}),
				success: function (result) {
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
		}
	});
	$(document).on('click', 'button', function (e) {
		if (e.handle != true) {
			e.handle = true;
			var id = $(this).attr('id');
			switch (id) {
				case 'save-filter':
					var filName = prompt(filterName);
					if (filName != '') {
						$.ajax({
							type: 'POST',
							url: 'index.php?option=com_emundus&controller=users&task=savefilters&Itemid=' + itemId,
							dataType: 'json',
							data: ({
								name: filName
							}),
							success: function (result) {
								if (result.status) {
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
						});
					} else {
						alert(filterEmpty);
						filName = prompt(filterName, 'name');
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
							success: function (result) {
								if (result.status) {
									$('#select_filter option:selected').remove();
									$("#select_filter").trigger("chosen:updated");
									$('#deleted-filter').show();
									setTimeout(function () {
										$('#deleted-filter').hide();
									}, 600);
								} else {
									$('#error-filter').show();
									setTimeout(function () {
										$('#error-filter').hide();
									}, 600);
								}

							},
							error: function (jqXHR) {
								console.log(jqXHR.responseText);
							}
						});
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
					$('.col-md-9 .panel.panel-default').show();
					break;
				case 'em-see-files':
					var fnum = new Object();
					fnum.fnum = $(this).parents('a').attr('href').split('-')[0];
					fnum.fnum = fnum.fnum.substr(1, fnum.fnum.length);
					fnum.sid = parseInt(fnum.fnum.substr(21, 7));
					fnum.cid = parseInt(fnum.fnum.substr(14, 7));
					$('.em-check:checked').prop('checked', false);
					$('#' + fnum.fnum + '_check').prop('checked', true);

					$.ajax({
						type: 'get',
						url: 'index.php?option=com_emundus&controller=users&task=getfnuminfos',
						dataType: 'json',
						data: ({
							fnum: fnum.fnum
						}),
						success: function (result) {
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
					var r = confirm(Joomla.JText._('COM_EMUNDUS_CONFIRM_DELETE_FILE'));
					if (r == true) {
						var fnum = $(this).parents('a').attr('href').split('-')[0];
						fnum = fnum.substr(1, fnum.length);
						$.ajax({
							type: 'POST',
							url: 'index.php?option=com_emundus&controller=users&task=deletefile',
							dataType: 'json',
							data: {
								fnum: fnum
							},
							success: function (result) {
								if (result.status) {
									if ($('#' + fnum + '-collapse').parent('div').hasClass('panel-primary')) {
										$('.em-open-files').remove();
										$('.em-hide').hide();
										$('#em-last-open').show();
										$('#em-last-open .list-group .list-group-item').removeClass('active');
										$('#em-user-filters').show();
										$('.em-check:checked').prop('checked', false);
										$('.col-md-9.col-xs-16 .panel.panel-default').show();
									}
									$('#em-last-open #' + fnum + '_ls_op').remove();
									$('#' + fnum + '-collapse').parent('div').remove();

								}
							},
							error: function (jqXHR) {
								console.log(jqXHR.responseText);
							}
						});
					}

					break;

				default:
					break;
			}

		}
	});

	$(document).on('change', '#pager-select', function (e) {
		if (e.handle !== true) {
			e.handle = true;
			$.ajax({
				type: 'POST',
				url: 'index.php?option=com_emundus&controller=users&task=setlimit',
				dataType: 'json',
				data: ({
					limit: $(this).val()
				}),
				success: function (result) {
					if (result.status) {
						reloadData();
					}
				}
			});
		}
	});

	$(document).on('change', '#select_filter', function (e) {
		var id = $(this).attr('id');
		var val = $('#' + id).val();
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'index.php?option=com_emundus&controller=users&task=setfilters&3',
			data: ({
				id: $('#' + id).attr('name'),
				val: val,
				multi: false
			}),
			success: function (result) {
				if (result.status) {
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: 'index.php?option=com_emundus&controller=users&task=loadfilters',
						data: {
							id: val
						},
						success: function (result) {
							if (result.status) {
								refreshFilter();

								reloadData();
							}
						},
						error: function (jqXHR, textStatus, errorThrown) {
							console.log(jqXHR.responseText);
						}

					});

				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.responseText);
			}
		});

	});
	$(document).on('click', '#suppr-filt', function (e) {
		var fId = $(this).parent('fieldset').attr('id');
		var index = fId.split('-');

		var sonName = $('#em-adv-fil-' + index[index.length - 1]).attr('name');

		$('#' + fId).remove();
		$.ajax({
			type: 'POST',
			url: 'index.php?option=com_emundus&controller=users&task=deladvfilter',
			dataType: 'json',
			data: ({
				elem: sonName,
				id: index[index.length - 1]
			}),
			success: function (result) {
				if (result.status) {
					reloadData();
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.responseText);
			}
		});
	});
	$(document).on('click', '.em-dropdown', function (e) {
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

		setTimeout(function () {
			$('ul[aria-labelledby="' + id + '"]').removeClass('just-open');
		}, 300);
	});

	/* Button Form actions*/
	$(document).on('click', '.em-actions-form', function (e) {
		var id = parseInt($(this).attr('id'));
		var url = $(this).attr('url');
		console.log(id);
		$('#em-modal-form').modal({
			backdrop: true
		}, 'toggle');



		$('.modal-title').empty();
		$('.modal-title').append($(this).children('a').text());
		$('.modal-body').empty();
		if ($('.modal-dialog').hasClass('modal-lg')) {
			$('.modal-dialog').removeClass('modal-lg');
		}
		$('.modal-body').attr('act-id', id);
		$('.modal-footer').show();


		$('.modal-footer').append('<div>' +
			'<p>' + jtextArray[2] + '</p>' +
			'<img src="' + loadingLine + '" alt="loading"/>' +
			'</div>');

		$('.modal-footer').hide();

		$('.modal-dialog').addClass('modal-lg');
		$(".modal-body").empty();

		$(".modal-body").append('<iframe src="' + url + '" style="width:100%; height:720px; border:none"></iframe>');

	});

	/* Menu action */
	$(document).off('click', '.em-actions');
	$(document).on('click', '.em-actions',async function (e) {

		e.preventDefault();
		var id = parseInt($(this).attr('id').split('|')[3]);

		// Prepare SweetAlert variables
		var title = '';
		var html = '';
		var swal_container_class = '';
		var swal_popup_class = '';
		var swal_actions_class = '';
		var swal_confirm_button = 'COM_EMUNDUS_ONBOARD_OK';
		var preconfirm = '';
		var preconfirm_value
		var swalForm = false;


		var view = $('#view').val();
		var sid = 0;
		if ($('.em-check:checked').length != 0) {
			sid = $('.em-check:checked').attr('id').split('_')[0];
		}

		var url = $(this).children('a').attr('href');

		String.prototype.fmt = function (hash) {
			var string = this,
				key;
			for (key in hash) {
				string = string.replace(new RegExp('\\{' + key + '\\}', 'gm'), hash[key]);
			}
			return string;
		};

		url = url.fmt({
			applicant_id: sid,
			view: view,
			controller: view,
			Itemid: itemId
		});

		const checkInput = getUserCheck();

		/**
		 * 19: create group
		 * 20: create user
		 * 21: activate
		 * 22: desactivate
		 * 23: affect
		 * 24: edit user
		 * 25: show user rights
		 * 26: delete user
		 * 33: regenerate password
		 * 34: send email
		 */
		switch (id) {
			case 19:
				title = 'COM_EMUNDUS_USERS_CREATE_GROUP';
				preconfirm = "if (!formCheck('gname')) {Swal.showValidationMessage(Joomla.JText._('COM_EMUNDUS_USERS_ERROR_PLEASE_COMPLETE'))}"
				break;
			case 20:
				title = 'COM_EMUNDUS_ONBOARD_PROGRAM_ADDUSER';
				preconfirm = "let checklanme =formCheck('lname');let checkfname =formCheck('fname');let checkmail =formCheck('mail');let checklogin =formCheck('login'); if (!checklanme || !checkfname || !checkmail || !checklogin) {Swal.showValidationMessage(Joomla.JText._('COM_EMUNDUS_USERS_ERROR_PLEASE_COMPLETE'))}";
				swal_confirm_button = 'COM_EMUNDUS_USERS_CREATE_USER_CONFIRM';
				break;
			case 23:
				title = 'COM_EMUNDUS_USERS_AFFECT_USER';
				swal_confirm_button = 'COM_EMUNDUS_USERS_AFFECT_USER_CONFIRM';
				preconfirm = "if ($('#agroups').val() == null) {Swal.showValidationMessage(Joomla.JText._('COM_EMUNDUS_USERS_AFFECT_GROUP_ERROR'))}"
				break;
		}

		switch (id) {
			case 19:
			case 20:
			case 23:
				swalForm = true;
				html = '<div id="data"></div>';
				addLoader();

				$.ajax({
					type: 'get',
					url: url,
					dataType: 'html',
					success: function (result) {
						$('#data').append(result);

						removeLoader();
					},
					error: function (jqXHR) {
						removeLoader();
						console.log(jqXHR.responseText);
					}
				});
				break;
			case 24:
				swalForm = true;
				title = 'COM_EMUNDUS_ACTIONS_EDIT_USER';
				swal_confirm_button = '	COM_EMUNDUS_USERS_EDIT_USER_CONFIRM';
				preconfirm = "let checklanme =formCheck('lname');let checkfname =formCheck('fname');let checkmail =formCheck('mail');let checklogin =formCheck('login'); if (!checklanme || !checkfname || !checkmail || !checklogin) {Swal.showValidationMessage(Joomla.JText._('COM_EMUNDUS_USERS_ERROR_PLEASE_COMPLETE'))}";				html = '<div id="data"></div>';

				addLoader();

				$.ajax({
					type: 'get',
					url: url,
					dataType: 'html',
					data: {
						user: sid
					},
					success: function (result) {
						$('#data').append(result);

						removeLoader();
					},
					error: function (jqXHR, textStatus, errorThrown) {
						removeLoader();
						console.log(jqXHR.responseText);
					}
				});
				break;

			case 21:
				$.ajax({
					type: 'POST',
					url: url,
					dataType: 'json',
					data: {
						users: checkInput,
						state: 0
					},
					success: (result) => {
						if (result.status){
							Swal.fire({
								position: 'center',
								type: 'success',
								title: result.msg,
								showConfirmButton: false,
								timer: 1500,
								customClass: {
									title: 'w-full justify-center',
								}
							}).then(() => {
								reloadData(false);
							});
						}

					},
					error: function (jqXHR) {
						if (jqXHR.status === 302) {
							Swal.fire({
								position: 'center',
								type: 'warning',
								title: result.msg,
								customClass: {
									title: 'em-swal-title',
									confirmButton: 'em-swal-confirm-button',
									actions: "em-swal-single-action",
								},
							}).then(function() {
								window.location.replace('/user');
							});
						}
					}
				});
				break;

			case 22:
				$.ajax({
					type: 'POST',
					url: url,
					dataType: 'json',
					data: {
						users: checkInput,
						state: 1
					},
					success: (result) => {
						if (result.status){
							Swal.fire({
								position: 'center',
								type: 'success',
								title: result.msg,
								showConfirmButton: false,
								timer: 1500,
								customClass: {
									title: 'w-full justify-center',
								}
							}).then(() => {
								reloadData(false);
							});
						}

					},
					error: function (jqXHR) {
						if (jqXHR.status === 302) {
							Swal.fire({
								position: 'center',
								type: 'success',
								title: result.msg,
								showConfirmButton: false,
								timer: 1500
							});
							window.location.replace('/user');
						}
					}
				});
				break;

			case 25:
				addLoader();
				await $.ajax({
					type: 'get',
					url: url,
					dataType: 'html',
					data: {
						user: sid
					},
					success: function (result) {
						removeLoader();

						swalForm = true;
						title = 'COM_EMUNDUS_USERS_SHOW_USER_RIGHTS';
						swal_popup_class = 'em-w-auto';
						html = result
					},
					error: function (jqXHR) {
						removeLoader();
						console.log(jqXHR.responseText);
					}
				});
				break;

			case 6:
				addLoader();
				title = 'COM_EMUNDUS_CREATE_CSV';
				swal_confirm_button = 'COM_EMUNDUS_EXPORTS_GENERATE_EXCEL';
				preconfirm = "var atLeastOneChecked = false; $('.form-group input[type=\"checkbox\"]').each(function() { if ($(this).is(':checked')) { atLeastOneChecked = true; return false; } }); if (!atLeastOneChecked) { Swal.showValidationMessage(Joomla.JText._('COM_EMUNDUS_EXPORTS_SELECT_AT_LEAST_ONE_INFORMATION')); }";
				await $.ajax({
					type: 'get',
					url: url,
					dataType: 'html',
					data: {
						user: sid
					},
					success: function (result) {
						removeLoader();

						swalForm = true;
						title = 'COM_EMUNDUS_CREATE_CSV';
						swal_confirm_button = 'COM_EMUNDUS_EXPORTS_GENERATE_EXCEL';
						html = result;
					},
					error: function (jqXHR) {
						removeLoader();
						console.log(jqXHR.responseText);
					}
				});
				break;
			case 26:
				Swal.fire({
					title: $(this).children('a').text(),
					text: Joomla.JText._('COM_EMUNDUS_USERS_ARE_YOU_SURE_TO_DELETE_USERS'),
					showCancelButton: true,
					showCloseButton: true,
					confirmButtonText: Joomla.JText._('JACTION_DELETE'),
					cancelButtonText: Joomla.JText._('JCANCEL'),
					reverseButtons: true,
					customClass: {
						title: 'em-swal-title',
						cancelButton: 'em-swal-cancel-button',
						confirmButton: 'em-swal-confirm-button',
					},
				}).then((result) => {
					if (result.value) {
						addLoader();

						$.ajax({
							type: 'POST',
							url: 'index.php?option=com_emundus&controller=users&task=deleteusers&Itemid=' + itemId,
							data: {
								users: checkInput
							},
							dataType: 'json',
							success: (result) => {
								removeLoader();

								if (result.status) {
									Swal.fire({
										position: 'center',
										type: 'success',
										title: result.msg,
										showConfirmButton: false,
										timer: 1500,
										customClass: {
											title: 'w-full justify-center',
										}
									});

									reloadData();
								} else {
									Swal.fire({
										position: 'center',
										type: 'warning',
										title: result.msg,
										customClass: {
											title: 'em-swal-title',
											confirmButton: 'em-swal-confirm-button',
											actions: "em-swal-single-action",
										},
									});
								}

							},
							error: function (jqXHR) {
								removeLoader();
								console.log(jqXHR.responseText);
							}
						});
					}
				});

				break;

			case 33:
				Swal.fire({
					title: $(this).children('a').text(),
					text: Joomla.JText._('COM_EMUNDUS_WANT_RESET_PASSWORD'),
					showCancelButton: true,
					showCloseButton: true,
					confirmButtonText: Joomla.JText._('COM_EMUNDUS_MAIL_SEND_NEW'),
					cancelButtonText: Joomla.JText._('JCANCEL'),
					reverseButtons: true,
					customClass: {
						title: 'em-swal-title',
						cancelButton: 'em-swal-cancel-button',
						confirmButton: 'em-swal-confirm-button',
					},
				}).then((result) => {
					if (result.value) {
						addLoader();

						const formData = new FormData();
						formData.append('users', checkInput);

						fetch('index.php?option=com_emundus&controller=users&task=passrequest&Itemid=' + itemId, {
							method: 'POST',
							body: formData
						}).then((response) => {
							if (response.ok) {
								return response.json();
							}
							throw new Error(Joomla.JText._('COM_EMUNDUS_ERROR_OCCURED'));
						}).then((result) => {
							removeLoader();

							if (result.status) {
								Swal.fire({
									position: 'center',
									type: 'success',
									title: result.msg,
									showConfirmButton: false,
									timer: 1500,
									customClass: {
										title: 'w-full justify-center',
									}
								});

								reloadData();
							} else {
								Swal.fire({
									position: 'center',
									type: 'error',
									title: result.msg,
									customClass: {
										title: 'w-full justify-center',
										confirmButton: 'em-swal-confirm-button',
										actions: "em-swal-single-action",
									},
								});
							}
						}).catch(function(error) {
							removeLoader();
							Swal.fire({
								position: 'center',
								type: 'error',
								title: Joomla.JText._('COM_EMUNDUS_ERROR_OCCURED'),
								customClass: {
									title: 'w-full justify-center',
									confirmButton: 'em-swal-confirm-button',
									actions: "em-swal-single-action",
								},
							});
						});
					}
				});
				break;

			case 34:
				addLoader();

				swalForm = true;
				title = 'COM_EMUNDUS_MAILS_SEND_EMAIL';
				swal_popup_class = 'em-w-100 em-h-100';
				swal_confirm_button = 'COM_EMUNDUS_MAIL_SEND_NEW';
				html = '<div id="data"></div>';

				$.ajax({
					type: 'POST',
					url: url,
					dataType: 'html',
					data: {
						users: checkInput,
					},
					success: (result) => {
						removeLoader();

						$('#data').append(result);
					},
					error: jqXHR => {
						removeLoader();
						console.log(jqXHR.responseText);
					}
				});
				break;
		}

		if(swalForm) {
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
					if (preconfirm !== '') {
						preconfirm_value = new Function(preconfirm)();
					}
				},
			}).then((result) => {
				if (result.value) {
					runAction(id, url, preconfirm_value);
				}
			});

			$('.em-chosen').chosen({width: '100%'});
		}
	});

	function runAction(id, url = '', option = '') {

		if ($('#em-check-all').is(':checked')) {
			var checkInput = 'all';
		} else {
			var i = 0;
			var myJSONObject = '{';
			$('.em-check:checked').each(function () {
				i = i + 1;
				myJSONObject += '"' + i + '"' + ':"' + $(this).attr('id').split('_')[0] + '",';
			});
			myJSONObject = myJSONObject.substr(0, myJSONObject.length - 1);
			myJSONObject += '}';
			if (myJSONObject.length == 2) {
				alert('SELECT_FILES');
				return;
			} else {
				checkInput = myJSONObject;
			}
		}

		/**
		 * 19: create group
		 * 20: create user
		 * 21: activate
		 * 22: desactivate
		 * 23: affect
		 * 24: edit user
		 * 25: show user rights
		 * 26: delete user
		 * 33: regenerate password
		 * 34: send email
		 */
		switch (id) {

			case 6:
				addLoader();

				var checkBoxesProps = {};

				$('input[type="checkbox"]').each(function() {
					var checkboxValue = $(this).attr('value');
					checkBoxesProps[checkboxValue] = $(this).prop('checked');
				});

				var checkedBoxes = {};
				for (var key in checkBoxesProps) {
					if (checkBoxesProps.hasOwnProperty(key) && checkBoxesProps[key]) {
						checkedBoxes[key] = true;
					}
				}


				$.ajax({
					type: 'POST',
					url: 'index.php?option=com_emundus&controller=users&task=exportusers&Itemid=' + itemId,
					data: {
						users: checkInput,
						checkboxes: checkedBoxes,
					},
					success: function(result) {
						removeLoader();

						var response = JSON.parse(result);
						var fileName = response.fileName;

						var downloadButton = $('<a>').attr('href', "/tmp/" + fileName)
							.attr('download', fileName)
							.text('Télécharger le fichier CSV')
							.css({
								'display': 'block',
								'margin': '0 auto',
								'text-align': 'center',
								'max-width': '80%'
							});

						var swalInstance = Swal.fire({
							position: 'center',
							type: 'success',
							title: 'Téléchargement prêt',
							html: downloadButton,
							showConfirmButton: false,
							customClass: {
								title: 'w-full justify-center'
							}
						});

						swalInstance.then(() => {
							$.ajax({
								type: 'POST',
								url: 'index.php?option=com_emundus&controller=users&task=deleteusersfile&Itemid=' + itemId,
								data: { fileName: fileName },
								success: function(response) {
									// console.log(response);
								},
								error: function(jqXHR, textStatus, errorThrown) {
									console.error(textStatus, errorThrown);
								}
							});
						});
					},

					error: function(jqXHR) {
						removeLoader();
						console.log(jqXHR.responseText);
					}
				});
				break;



			case 19:
				var programs = $('#gprogs');
				var progs = "";
				if (programs.val() != null) {
					for (var i = 0; i < programs.val().length; i++) {
						progs += programs.val()[i];
						progs += ',';
					}
					progs = progs.substr(0, progs.length - 1);
				}

				addLoader();

				$.ajax({
					type: 'POST',
					url: $('#em-add-group').attr('action'),
					data: {
						gname: $('#gname').val(),
						gdesc: $('#gdescription').val(),
						gprog: progs
					},
					dataType: 'json',
					success: function (result) {
						removeLoader();
						if (result.status) {

							Swal.fire({
								position: 'center',
								type: 'success',
								title: result.msg,
								showConfirmButton: false,
								timer: 1500
							}).then(function() {
								window.location.replace('index.php?option=com_emundus&view=users&layout=showgrouprights&Itemid=1169&rowid='+result.status);
							});

						} else {
							Swal.fire({
								position: 'center',
								type: 'warning',
								title: result.msg,
								customClass: {
									title: 'em-swal-title',
									confirmButton: 'em-swal-confirm-button',
									actions: "em-swal-single-action",
								},
							});
						}
					},
					error: function (jqXHR) {
						console.log(jqXHR.responseText);
					}
				});
				break;

			case 20:
				var groups = "";
				var campaigns = "";
				var oprofiles = "";

				if ($("#groups").val() != null && $("#groups").val().length > 0) {
					for (var i = 0; i < $("#groups").val().length; i++) {
						groups += $("#groups").val()[i];
						groups += ',';
					}
				}
				if ($("#campaigns").val() && $("#campaigns").val().length > 0) {
					for (var i = 0; i < $("#campaigns").val().length; i++) {
						campaigns += $("#campaigns").val()[i];
						campaigns += ',';
					}
				}
				if ($("#oprofiles").val() && $("#oprofiles").val().length > 0) {
					for (var i = 0; i < $("#oprofiles").val().length; i++) {
						oprofiles += $("#oprofiles").val()[i];
						oprofiles += ',';
					}
				}
				if($('#same_login_email').is(':checked')){
					$('#login').val($('#mail').val());
				}

				var login = $('#login').val();
				var fn = $('#fname').val();
				var ln = $('#lname').val();
				var email = $('#mail').val();
				var profile = $('#profiles').val();

				if (profile == "0") {
					$('#profiles').parent('.form-group').addClass('has-error');
					$('#profiles').after('<span class="help-block">' + Joomla.JText._('SELECT_A_VALUE') + '</span>');
					return false;
				}

				addLoader();
				$.ajax({
					type: 'POST',
					url: $('#em-add-user').attr('action'),
					data: {
						login: login,
						firstname: fn,
						lastname: ln,
						campaigns: campaigns.substr(0, campaigns.length - 1),
						oprofiles: oprofiles.substr(0, oprofiles.length - 1),
						groups: groups.substr(0, groups.length - 1),
						profile: profile,
						jgr: $('#profiles option:selected').attr('id'),
						email: email,
						newsletter: $('#news').is(':checked') ? 1 : 0,
						university_id: $('#univ').val(),
						ldap: $('#ldap').is(':checked') ? 1 : 0
					},
					dataType: 'json',
					success: function (result) {
						removeLoader();

						if (result.status) {
							Swal.fire({
								position: 'center',
								type: 'success',
								title: result.msg,
								showConfirmButton: false,
								timer: 1500,
							});

							$('#em-modal-actions').modal('hide');

							reloadData();

							reloadActions($('#view').val(), undefined, false);
							$('.modal-backdrop, .modal-backdrop.fade.in').css('display','none');
						} else {
							Swal.fire({
								position: 'center',
								type: 'warning',
								title: result.msg,
								customClass: {
									title: 'em-swal-title',
									confirmButton: 'em-swal-confirm-button',
									actions: "em-swal-single-action",
								},
							});
						}

					},
					error: function (jqXHR) {
						removeLoader();
						console.log(jqXHR.responseText);
					}
				});
				break;
			case 23:
				var checkInput = getUserCheck();

				if ($('#agroups').val() == null) {
					$('#agroups').parent('.form-group').addClass('has-error');
					$('#agroups').after('<span class="help-block">' + Joomla.JText._('SELECT_A_GROUP') + '</span>');
					return false;
				} else {
					var groups = '';
					for (var i = 0; i < $("#agroups").val().length; i++) {
						groups += $("#agroups").val()[i];
						groups += ',';
					}
				}

				$.ajax({
					type: 'POST',
					url: $('#em-affect-groups').attr('action'),
					data: {
						users: checkInput,
						groups: groups.substr(0, groups.length - 1)
					},
					dataType: 'json',
					success: function (result) {
						removeLoader();

						if (result.status) {
							Swal.fire({
								position: 'center',
								type: 'success',
								title: result.msg,
								showConfirmButton: false,
								timer: 1500
							});
						} else {
							Swal.fire({
								position: 'center',
								type: 'warning',
								title: result.msg,
								customClass: {
									title: 'em-swal-title',
									confirmButton: 'em-swal-confirm-button',
									actions: "em-swal-single-action",
								},
							});
						}

					},
					error: function (jqXHR, textStatus, errorThrown) {
						console.log(jqXHR.responseText);
					}
				});
				break;

			case 24:
				var groups = '';
				var campaigns = '';
				var oprofiles = '';

				if ($("#groups").val() != null && $("#groups").val().length > 0) {
					for (var i = 0; i < $("#groups").val().length; i++) {
						groups += $("#groups").val()[i];
						groups += ',';
					}
				}
				if ($("#campaigns").val() && $("#campaigns").val().length > 0) {
					for (var i = 0; i < $("#campaigns").val().length; i++) {
						campaigns += $("#campaigns").val()[i];
						campaigns += ',';
					}
				}
				if ($("#oprofiles").val() && $("#oprofiles").val().length > 0) {
					for (var i = 0; i < $("#oprofiles").val().length; i++) {
						oprofiles += $("#oprofiles").val()[i];
						oprofiles += ',';
					}
				}
				var login = $('#login').val();
				var fn = $('#fname').val();
				var ln = $('#lname').val();
				var email = $('#mail').val();
				var profile = $('#profiles').val();
				let sameLoginEmail = document.getElementById('same_login_email').checked ? 1 : 0;

				if (profile == "0") {
					$('#profiles').parent('.form-group').addClass('has-error');
					$('#profiles').after('<span class="help-block">' + Joomla.JText._('SELECT_A_VALUE') + '</span>');
					return false;
				}

				addLoader();

				let addUserData = {
					login: login,
					email: email,
					sameLoginEmail: sameLoginEmail,
					firstname: fn,
					lastname: ln,
					campaigns: campaigns.substr(0, campaigns.length - 1),
					oprofiles: oprofiles.substr(0, oprofiles.length - 1),
					groups: groups.substr(0, groups.length - 1),
					profile: profile,
					jgr: $('#profiles option:selected').attr('id'),
					newsletter: $('#news').is(':checked') ? 1 : 0,
					university_id: $('#univ').val()
				}

				const action = document.getElementById('em-add-user').getAttribute('action');
				if(action.indexOf('edituser') !== -1) {
					addUserData.id =  $('.em-check:checked').attr('id').split('_')[0];
				}

				$.ajax({
					type: 'POST',
					url: action,
					data: addUserData,
					dataType: 'json',
					success: function (result) {
						removeLoader();

						if (result.status) {
							Swal.fire({
								position: 'center',
								type: 'success',
								title: result.msg,
								showConfirmButton: false,
								timer: 1500
							});

							reloadData();
						} else {
							Swal.fire({
								position: 'center',
								type: 'warning',
								title: result.msg,
								customClass: {
									title: 'em-swal-title',
									confirmButton: 'em-swal-confirm-button',
									actions: "em-swal-single-action",
								},
							});
						}
					},
					error: function (jqXHR) {
						removeLoader();
						console.log(jqXHR.responseText);
						displayErrorMessage(jqXHR.responseText);
					}
				});
				break;

			/* Send an email to a user.*/
			case 34:
				addLoader();

				/* Get all form elements.*/
				let data = {
					recipients 		: $('#uids').val(),
					template		: $('#message_template :selected').val(),
					Bcc 			: $('#sendUserACopy').prop('checked'),
					mail_from_name 	: $('#mail_from_name').text(),
					mail_from 		: $('#mail_from').text(),
					mail_subject 	: $('#mail_subject').text(),
					message			: $('#mail_body').val(),
					attachments		: []
				};

				$("#em-attachment-list li").each((idx, li) => {
					let attachment = $(li);
					data.attachments.push(attachment.find('.value').text());
				});

				$.ajax({
					type: 'POST',
					url: "index.php?option=com_emundus&controller=messages&task=useremail",
					data: data,
					success: result => {
						removeLoader();

						result = JSON.parse(result);

						if (result.status) {
							if (result.sent.length > 0) {

								var sent_to = '<p>' + Joomla.JText._('SEND_TO') + '</p><ul class="list-group" id="em-mails-sent">';
								result.sent.forEach(element => {
									sent_to += '<li class="list-group-item alert-success">'+element+'</li>';
								});

								Swal.fire({
									position: 'center',
									type: 'success',
									title: Joomla.JText._('COM_EMUNDUS_EMAILS_EMAILS_SENT') + result.sent.length,
									html:  sent_to + '</ul>',
									customClass: {
										title: 'w-full justify-center',
										confirmButton: 'em-swal-confirm-button',
										actions: "em-swal-single-action",
									},
								});

							} else {
								Swal.fire({
									type: 'error',
									title: Joomla.JText._('COM_EMUNDUS_EMAILS_NO_EMAILS_SENT'),
									customClass: {
										title: 'em-swal-title',
										confirmButton: 'em-swal-confirm-button',
										actions: "em-swal-single-action",
									},
								});
							}

							if (result.failed.length > 0) {

								// add sibling to #em-mails-sent
								const emailNotSentMessage = document.createElement('p');
								emailNotSentMessage.classList.add('em-mt-16');
								emailNotSentMessage.innerText = "Certains utilisateurs n'ont pas reçu l'email";

								const emailNotSent = document.createElement('div');
								emailNotSent.classList.add('alert', 'alert-danger', 'em-mt-16');
								emailNotSent.innerHTML = '<span class="badge">'+result.failed.length+'</span>';
								emailNotSent.appendChild(document.createElement('ul'));
								result.failed.forEach(element => {
									const emailNotSentItem = document.createElement('li');
									emailNotSentItem.innerHTML = element;
									emailNotSent.querySelector('ul').appendChild(emailNotSentItem);
								});

								$('#em-mails-sent').after(emailNotSent);
								$('#em-mails-sent').after(emailNotSentMessage);
							}

						} else {
							$("#em-email-messages").append('<span class="alert alert-danger">'+Joomla.JText._('SEND_FAILED')+'</span>')
						}
					},
					error : () => {
						$("#em-email-messages").append('<span class="alert alert-danger">'+Joomla.JText._('SEND_FAILED')+'</span>')
					}
				});

				break;
		}
	}

	/* Button on Actions*/
	$(document).off('click', '#em-modal-actions .btn.btn-success');

	$(document).on('click', '#em-modal-actions .btn.btn-success', function (e) {

	});

	/*action fin*/
	$(document).on('change', '#em-modal-actions #em-export-form', function (e) {
		if (e.handle !== true) {
			e.handle = true;
			var id = $(this).val();
			var text = $('#em-modal-actions #em-export-form option:selected').attr('data-value');
			$('#em-export').append('<li class="em-export-item" id="' + id + '-item"><strong>' + text + '</strong><button class="btn btn-danger btn-xs pull-right"><span class="material-icons">delete_outline</span></button></li>');
		}
	});

	$(document).on('click', '#em-export .em-export-item .btn.btn-danger', function (e) {
		$(this).parent('li').remove();
	});

	$(document).on('change', '.em-modal-check', function () {
		if ($(this).hasClass('em-check-all')) {
			var id = $(this).attr('name').split('-');
			id.pop();
			id = id.join('-');
			if ($(this).is(':checked')) {
				$(this).prop('checked', true);
				$('.' + id).prop('checked', true);
			} else {
				$(this).prop('checked', false);
				$('.' + id).prop('checked', false);
			}
		}
	});

	function displayErrorMessage(msg)
	{
		Swal.fire({
			type: 'error',
			title: msg,
			customClass: {
				title: 'em-swal-title',
				confirmButton: 'em-swal-confirm-button',
				actions: "em-swal-single-action",
			},
		});
	}
})

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
