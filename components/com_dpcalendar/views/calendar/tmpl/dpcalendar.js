jQuery(document).ready(
		function() {
			// Parsing the hash
			var today = new Date();
			dpcalendarOptions['year'] = today.getFullYear();
			dpcalendarOptions['month'] = today.getMonth();
			dpcalendarOptions['day'] = today.getDate();
			var vars = window.location.hash.replace(/&amp;/gi, "&").split("&");
			for (var i = 0; i < vars.length; i++) {
				if (vars[i].match("^#year"))
					dpcalendarOptions['year'] = vars[i].substring(6);
				if (vars[i].match("^month"))
					dpcalendarOptions['month'] = vars[i].substring(6) - 1;
				if (vars[i].match("^day"))
					dpcalendarOptions['date'] = vars[i].substring(4);
				if (vars[i].match("^view"))
					dpcalendarOptions['defaultView'] = vars[i].substring(5);
			}

			// Loading the list view when we have a small screen
			if (jQuery(document).width() < 500) {
				dpcalendarOptions['defaultView'] = 'list';
			}

			// Some default options
			if (!('header' in dpcalendarOptions)) {
				dpcalendarOptions['header'] = {
					left : 'prev,next ',
					center : 'title',
					right : 'month,agendaWeek,agendaDay,list'
				};
			}
			dpcalendarOptions['weekNumberTitle'] = '';
			dpcalendarOptions['theme'] = false;
			dpcalendarOptions['startParam'] = 'date-start';
			dpcalendarOptions['endParam'] = 'date-end';
			dpcalendarOptions['columnFormat'] = {
				month : 'ddd',
				week : 'ddd d',
				day : 'dddd d'
			};
			dpcalendarOptions['listSections'] = 'smart';

			// Translations
			dpcalendarOptions['allDayText'] = Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_ALL_DAY', true);
			dpcalendarOptions['buttonText'] = {
				today : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_TODAY', true),
				month : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_MONTH', true),
				week : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_WEEK', true),
				day : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_DAY', true),
				list : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_LIST', true)
			};

			dpcalendarOptions['listTexts'] = {
				until : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_UNTIL', true),
				past : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_PAST', true),
				today : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_TODAY', true),
				tomorrow : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_TOMORROW', true),
				thisWeek : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_THIS_WEEK', true),
				nextWeek : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_NEXT_WEEK', true),
				thisMonth : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_THIS_MONTH', true),
				nextMonth : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_NEXT_MONTH', true),
				future : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_FUTURE', true),
				week : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_VIEW_TEXTS_WEEK', true)
			};

			var hasMap = jQuery('#dpcalendar_component_map').length > 0;

			dpcalendarOptions['viewRender'] = function(view) {
				// Setting the hash based on the actual view
				var d = jQuery('#dpcalendar_component').fullCalendar('getDate');
				var newHash = 'year=' + d.getFullYear() + '&month=' + (d.getMonth() + 1) + '&day=' + d.getDate() + '&view=' + view.name;
				if (window.location.hash.replace(/&amp;/gi, "&") != newHash) {
					window.location.hash = newHash;
				}
				if (hasMap) {
					// Clearing the map
					if (dpcalendarMapMarkers != null) {
						for (var i = 0; i < dpcalendarMapMarkers.length; i++) {
							dpcalendarMapMarkers[i].setMap(null);
						}
					}
					dpcalendarMapMarkers = [];
				}
			};
			dpcalendarOptions['eventRender'] = function(event, element) {
				if (event.description && typeof (element.tooltipster) == "function") {
					var desc = event.description;

					// Adding the hash to the url for proper return
					desc = desc.replace('&task=event.delete', '&task=event.delete&urlhash=' + encodeURIComponent(window.location.hash));
					desc = desc.replace('&task=event.edit', '&task=event.edit&urlhash=' + encodeURIComponent(window.location.hash));

					// Adding the tooltip
					element.tooltipster({
						contentAsHTML : true,
						content : dpEncode(desc),
						delay : 100,
						interactive : true
					});
					if (event.fgcolor) {
						element.css('color', event.fgcolor).find('.fc-event-inner').css('color', event.fgcolor);
					}
				}

				if (hasMap) {
					var chartUrl = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|';
					if (document.location.protocol == 'https:') {
						chartUrl = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|';
					}

					// Adding the locations to the map
					jQuery.each(event.location, function(i, loc) {
						if (loc.location == undefined || loc.location == '' || loc.location == null)
							return;
						var l = new google.maps.LatLng(loc.latitude, loc.longitude);

						var marker = new google.maps.Marker({
							position : l,
							map : dpcalendarMap,
							title : loc.location,
							icon : {
								url : chartUrl + event.color.toString().replace('#', ''),
								size : new google.maps.Size(21, 34),
								origin : new google.maps.Point(0, 0),
								anchor : new google.maps.Point(10, 34)
							}
						});
						dpcalendarMapMarkers.push(marker);

						var desc = event.description;
						if (event.url) {
							desc = desc.replace(event.title, '<a href="' + event.url + '"">' + event.title + "</a>");
						}
						var infowindow = new google.maps.InfoWindow({
							content : desc
						});
						google.maps.event.addListener(marker, 'click', function() {
							infowindow.open(dpcalendarMap, marker);
						});
						dpcalendarMapBounds.extend(l);
						dpcalendarMap.setCenter(dpcalendarMapBounds.getCenter());
					});
				}
			};

			// Handling the messages in the returned data
			dpcalendarOptions['eventDataTransform'] = function(events) {
				if (events.messages != null && jQuery('#system-message-container').length) {
					Joomla.renderMessages(events.messages);
				}
				if (events.data != null) {
					return events.data;
				}
				return events;
			};

			// Drag and drop support
			dpcalendarOptions['eventDrop'] = function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) {
				jQuery('#dpcalendar_component_loading').show();
				jQuery(jsEvent.target).tooltip('hide');
				jQuery.ajax({
					type : 'POST',
					url : 'index.php?option=com_dpcalendar&task=event.move',
					data : {
						id : event.id,
						days : dayDelta,
						minutes : minuteDelta,
						allDay : allDay
					},
					success : function(data) {
						jQuery('#dpcalendar_component_loading').hide();
						var json = jQuery.parseJSON(data);
						if (json.data.url)
							event.url = json.data.url;
						if (json.messages != null && jQuery('#system-message-container').length) {
							Joomla.renderMessages(json.messages);
						}
					}
				});
			};

			// Resize support
			dpcalendarOptions['eventResize'] = function(event, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view) {
				jQuery('#dpcalendar_component_loading').show();
				jQuery(jsEvent.target).tooltip('hide');
				jQuery.ajax({
					type : 'POST',
					url : 'index.php?option=com_dpcalendar&task=event.move',
					data : {
						id : event.id,
						days : dayDelta,
						minutes : minuteDelta,
						allDay : false,
						onlyEnd : true
					},
					success : function(data) {
						jQuery('#dpcalendar_component_loading').hide();
						var json = jQuery.parseJSON(data);
						if (json.data.url)
							event.url = json.data.url;
						if (json.messages != null && jQuery('#system-message-container').length) {
							Joomla.renderMessages(json.messages);
						}
					}
				});
			};

			// Handling clicking on an event
			dpcalendarOptions['eventClick'] = function(event, jsEvent, view) {
				jsEvent.stopPropagation();

				if (dpcalendarOptions['show_event_as_popup'] == 4) {
					return false;
				}

				// If we are on a small screen navigate to the page
				if (jQuery(window).width() < 600) {
					window.location = dpEncode(event.url);
					return false;
				}

				if (dpcalendarOptions['show_event_as_popup'] == 1) {
					// Opening a BS modal dialog
					jQuery('#dpc-event-view').on('show', function() {
						var url = new Url(event.url);
						url.query.tmpl = 'component';
						jQuery('#dpc-event-view iframe').attr('src', url.toString());
					});
					jQuery('#dpc-event-view').on('hide', function() {
						if (jQuery('#dpc-event-view iframe').contents().find('#system-message').children().length > 0) {
							jQuery('#dpcalendar_component').fullCalendar('refetchEvents');
						}
						jQuery('#dpc-event-view iframe').removeAttr('src');
					});
					var modal = jQuery('#dpc-event-view').modal();
					if (jQuery(window).width() < modal.width()) {
						modal.css({
							width : jQuery(window).width() - 100 + 'px'
						});
					} else {
						modal.css({
							'margin-left' : '-' + modal.width() / 2 + 'px'
						});
					}
				} else if (dpcalendarOptions['show_event_as_popup'] == 3) {
					// Opening the Joomal modal box
					var modal = jQuery('#dpc-event-view');
					var width = jQuery(window).width();
					var url = new Url(event.url);
					url.query.tmpl = 'component';
					SqueezeBox.open(url.toString(), {
						handler : 'iframe',
						size : {
							x : (width < 650 ? width - (width * 0.10) : modal.width()),
							y : modal.height()
						}
					});
				} else {
					// Just navigate to the event
					window.location = dpEncode(event.url);
				}
				return false;
			};

			dpcalendarOptions['dayClick'] = function(date, allDay, jsEvent, view) {
				if (jQuery('#editEventFormComponent').length > 0) {
					// On small screens open the edit page directly
					if (jQuery(window).width() < 600) {
						jQuery('#editEventFormComponent #task').val('');
						jQuery('#editEventFormComponent').submit();
						return false;
					}
					jsEvent.stopPropagation();

					// Setting some defaults on the quick add popup form
					if (view.name == 'month')
						date.setHours(8);

					jQuery('#editEventFormComponent #jform_start_date').datepicker('setDate', date);

					// Setting the actual date without hours to prevent shifting
					var actualDate = new Date(date);
					actualDate.setHours(0);
					jQuery('#editEventFormComponent #jform_start_date').data('actualDate', actualDate);
					jQuery('#editEventFormComponent #jform_start_date_time').timepicker('setTime', date);
					jQuery('#editEventFormComponent #jform_end_date').datepicker('setDate', date);
					date.setHours(date.getHours() + 1);
					jQuery('#editEventFormComponent #jform_end_date_time').timepicker('setTime', date);
					var p = jQuery('#dpcalendar_component').parents().filter(function() {
						var parent = jQuery(this);
						return parent.is('body') || parent.css('position') == 'relative';
					}).slice(0, 1).offset();

					if (dpcalendarOptions['event_edit_popup'] == 1) {
						// Show the quick add popup
						jQuery('#editEventFormComponent').css({
							top : jsEvent.pageY - p.top,
							left : jsEvent.pageX - 160 - p.left
						}).show();
						jQuery('#editEventFormComponent #jform_title').focus();
					} else {
						// Open the edit page
						jQuery('#editEventFormComponent #task').val('');
						jQuery('#editEventFormComponent').submit();
					}
				} else {
					// The edit form is not loaded, navigate to the day
					jQuery('#dpcalendar_component').fullCalendar('gotoDate', date).fullCalendar('changeView', 'agendaDay');
				}
			};

			// Spinner handling
			dpcalendarOptions['loading'] = function(bool) {
				if (bool) {
					jQuery('#dpcalendar_component_loading').show();
				} else {
					jQuery('#dpcalendar_component_loading').hide();
				}
			};
			jQuery('#dpcalendar_component_loading').hide();

			jQuery('#dpcalendar_component').data('eventSources', dpcalendarOptions['eventSources']);

			// Initializing local storage of event sources
			var hash = md5(JSON.stringify(dpcalendarOptions['eventSources']));
			if (isLocalStorageNameSupported()) {
				if (localStorage.getItem('dpcalendarComponentEventSources-' + hash) == null) {
					localStorage.setItem('dpcalendarComponentEventSources-' + hash, JSON.stringify(dpcalendarOptions['eventSources']));
				} else {
					dpcalendarOptions['eventSources'] = JSON.parse(localStorage.getItem('dpcalendarComponentEventSources-' + hash));
				}
			}

			// Loading the calendar
			jQuery('#dpcalendar_component').fullCalendar(jQuery.extend({}, dpcalendarOptions));

			// Replace class names with the one from Joomla
			jQuery('.fc-icon-icon-print').attr('class', 'icon-print');
			jQuery('.fc-icon-icon-calendar').attr('class', 'icon-calendar');

			if (hasMap && typeof google !== 'undefined') {
				var dpcalendarMap = new google.maps.Map(document.getElementById('dpcalendar_component_map'), {
					zoom : dpcalendarOptions['map_zoom'],
					mapTypeId : google.maps.MapTypeId.ROADMAP,
					center : new google.maps.LatLng(dpcalendarOptions['map_lat'], dpcalendarOptions['map_long']),
					draggable : jQuery(document).width() > 480 ? true : false,
					scrollwheel : jQuery(document).width() > 480 ? true : false
				});
				var dpcalendarMapBounds = new google.maps.LatLngBounds();
				var dpcalendarMapMarkers = [];
			}
			// Adding the custom buttons
			var custom_buttons = '<span class="fc-button fc-button-datepicker fc-state-default fc-corner-left fc-corner-right">'
					+ '<span class="fc-button-inner"><span class="fc-button-content" id="dpcalendar_component_date_picker_button">'
					+ '<input type="hidden" id="dpcalendar_component_date_picker" value="">' + '<i class="icon-calendar" title="'
					+ Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_SHOW_DATEPICKER') + '"></i>' + '</span></span></span>';
			custom_buttons += '<span class="hidden-phone fc-button fc-button-print fc-state-default fc-corner-left fc-corner-right">'
					+ '<span class="fc-button-inner"><span class="fc-button-content" id="dpcalendar_component_print_button">'
					+ '<i class="icon-print" title="' + Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_PRINT') + '"></i>'
					+ '</span></span></span>';
			jQuery('span.fc-header-space').after(custom_buttons);

			// Initializing the datepicker
			jQuery("#dpcalendar_component_date_picker").datepicker({
				dateFormat : 'dd-mm-yy',
				changeYear : true,
				dayNames : dpcalendarOptions['dayNames'],
				dayNamesShort : dpcalendarOptions['dayNamesShort'],
				dayNamesMin : dpcalendarOptions['dayNamesMin'],
				monthNames : dpcalendarOptions['monthNames'],
				monthNamesShort : dpcalendarOptions['monthNamesShort'],
				firstDay : dpcalendarOptions['firstDay'],
				showButtonPanel : true,
				closeText : Joomla.JText._('JCANCEL', true),
				currentText : Joomla.JText._('COM_DPCALENDAR_VIEW_CALENDAR_TOOLBAR_TODAY', true),
				onSelect : function(dateText, inst) {
					var d = jQuery('#dpcalendar_component_date_picker').datepicker('getDate');
					var view = jQuery('#dpcalendar_component').fullCalendar('getView').name;
					jQuery('#dpcalendar_component').fullCalendar('gotoDate', d);
				}
			});
			jQuery('.ui-widget-overlay').on('click', function() {
				jQuery('#dpcalendar-dialog').dialog('close');
			});

			// Listening for hash/url changes
			jQuery(window).bind('hashchange', function() {
				var today = new Date();
				var tmpYear = today.getFullYear();
				var tmpMonth = today.getMonth();
				var tmpDay = today.getDate();
				var tmpView = dpcalendarOptions['defaultView'];
				var vars = window.location.hash.replace(/&amp;/gi, "&").split("&");
				for (var i = 0; i < vars.length; i++) {
					if (vars[i].match("^#year"))
						tmpYear = vars[i].substring(6);
					if (vars[i].match("^month"))
						tmpMonth = vars[i].substring(6) - 1;
					if (vars[i].match("^day"))
						tmpDay = vars[i].substring(4);
					if (vars[i].match("^view"))
						tmpView = vars[i].substring(5);
				}
				var date = new Date(tmpYear, tmpMonth, tmpDay, 0, 0, 0);
				var d = jQuery('#dpcalendar_component').fullCalendar('getDate');
				var view = jQuery('#dpcalendar_component').fullCalendar('getView');
				if (date.getFullYear() != d.getFullYear() || date.getMonth() != d.getMonth() || date.getDate() != d.getDate())
					jQuery('#dpcalendar_component').fullCalendar('gotoDate', date);
				if (view.name != tmpView)
					jQuery('#dpcalendar_component').fullCalendar('changeView', tmpView);
			});

			if (jQuery('table').disableSelection)
				jQuery('div.fc-button-today').closest('table.fc-header').disableSelection();

			// Showing the date picker
			jQuery(document).on('click', '#dpcalendar_component_date_picker_button', function(e) {
				jQuery('#dpcalendar_component_date_picker').datepicker('show');
			});

			// Go to the print page
			jQuery(document).on('click', '#dpcalendar_component_print_button', function(e) {
				var loc = document.location.href.replace(/\?/, "\?layout=print&format=raw\&");
				if (loc == document.location.href)
					loc = document.location.href.replace(/#/, "\?layout=print&format=raw#");
				var printWindow = window.open(loc);
				printWindow.focus();
			});

			// Toggle the list of calendars
			jQuery('#dpcalendar_view_toggle_status').bind('click', function(e) {
				jQuery('#dpcalendar_view_list').slideToggle('slow', function() {
					var iconClass = 'icon-arrow-up';
					if (!jQuery('#dpcalendar_view_list').is(":visible"))
						iconClass = 'icon-arrow-down';

					jQuery('#dpcalendar_view_toggle_status').attr('class', iconClass);
				});
			});

			jQuery.each(dpcalendarOptions['eventSources'], function(index, value) {
				jQuery('#dpcalendar_view_list input').each(function(indexInput) {
					var input = jQuery(this);
					if (value == input.val()) {
						input.attr('checked', true);
					}
				});
			});
		});

function updateDPCalendarFrame(calendar) {
	var hash = md5(JSON.stringify(jQuery('#dpcalendar_component').data('eventSources')));
	var eventSources = isLocalStorageNameSupported() ? JSON.parse(localStorage.getItem('dpcalendarComponentEventSources-' + hash)) : [];

	if (calendar.checked) {
		jQuery('#dpcalendar_component').fullCalendar('addEventSource', calendar.value);
		eventSources.push(calendar.value);
	} else {
		jQuery('#dpcalendar_component').fullCalendar('removeEventSource', calendar.value);
		jQuery.each(eventSources, function(index, value) {
			if (value == calendar.value) {
				eventSources.splice(index, 1);
			}
		});
	}

	if (isLocalStorageNameSupported()) {
		localStorage.setItem('dpcalendarComponentEventSources-' + hash, JSON.stringify(eventSources));
	}
}

function isLocalStorageNameSupported() {
	var testKey = 'test';
	try {
		localStorage.setItem(testKey, '1');
		localStorage.removeItem(testKey);
		return true;
	} catch (error) {
		return false;
	}
}

!function(){md5=function(n){function r(n,r,t,u,o,c){return r=h(h(r,n),h(u,c)),h(r<<o|r>>>32-o,t)}function t(n,t,u,o,c,e,f){return r(t&u|~t&o,n,t,c,e,f)}function u(n,t,u,o,c,e,f){return r(t&o|u&~o,n,t,c,e,f)}function o(n,t,u,o,c,e,f){return r(t^u^o,n,t,c,e,f)}function c(n,t,u,o,c,e,f){return r(u^(t|~o),n,t,c,e,f)}function e(n,r){var e=n[0],f=n[1],i=n[2],a=n[3];e=t(e,f,i,a,r[0],7,-680876936),a=t(a,e,f,i,r[1],12,-389564586),i=t(i,a,e,f,r[2],17,606105819),f=t(f,i,a,e,r[3],22,-1044525330),e=t(e,f,i,a,r[4],7,-176418897),a=t(a,e,f,i,r[5],12,1200080426),i=t(i,a,e,f,r[6],17,-1473231341),f=t(f,i,a,e,r[7],22,-45705983),e=t(e,f,i,a,r[8],7,1770035416),a=t(a,e,f,i,r[9],12,-1958414417),i=t(i,a,e,f,r[10],17,-42063),f=t(f,i,a,e,r[11],22,-1990404162),e=t(e,f,i,a,r[12],7,1804603682),a=t(a,e,f,i,r[13],12,-40341101),i=t(i,a,e,f,r[14],17,-1502002290),f=t(f,i,a,e,r[15],22,1236535329),e=u(e,f,i,a,r[1],5,-165796510),a=u(a,e,f,i,r[6],9,-1069501632),i=u(i,a,e,f,r[11],14,643717713),f=u(f,i,a,e,r[0],20,-373897302),e=u(e,f,i,a,r[5],5,-701558691),a=u(a,e,f,i,r[10],9,38016083),i=u(i,a,e,f,r[15],14,-660478335),f=u(f,i,a,e,r[4],20,-405537848),e=u(e,f,i,a,r[9],5,568446438),a=u(a,e,f,i,r[14],9,-1019803690),i=u(i,a,e,f,r[3],14,-187363961),f=u(f,i,a,e,r[8],20,1163531501),e=u(e,f,i,a,r[13],5,-1444681467),a=u(a,e,f,i,r[2],9,-51403784),i=u(i,a,e,f,r[7],14,1735328473),f=u(f,i,a,e,r[12],20,-1926607734),e=o(e,f,i,a,r[5],4,-378558),a=o(a,e,f,i,r[8],11,-2022574463),i=o(i,a,e,f,r[11],16,1839030562),f=o(f,i,a,e,r[14],23,-35309556),e=o(e,f,i,a,r[1],4,-1530992060),a=o(a,e,f,i,r[4],11,1272893353),i=o(i,a,e,f,r[7],16,-155497632),f=o(f,i,a,e,r[10],23,-1094730640),e=o(e,f,i,a,r[13],4,681279174),a=o(a,e,f,i,r[0],11,-358537222),i=o(i,a,e,f,r[3],16,-722521979),f=o(f,i,a,e,r[6],23,76029189),e=o(e,f,i,a,r[9],4,-640364487),a=o(a,e,f,i,r[12],11,-421815835),i=o(i,a,e,f,r[15],16,530742520),f=o(f,i,a,e,r[2],23,-995338651),e=c(e,f,i,a,r[0],6,-198630844),a=c(a,e,f,i,r[7],10,1126891415),i=c(i,a,e,f,r[14],15,-1416354905),f=c(f,i,a,e,r[5],21,-57434055),e=c(e,f,i,a,r[12],6,1700485571),a=c(a,e,f,i,r[3],10,-1894986606),i=c(i,a,e,f,r[10],15,-1051523),f=c(f,i,a,e,r[1],21,-2054922799),e=c(e,f,i,a,r[8],6,1873313359),a=c(a,e,f,i,r[15],10,-30611744),i=c(i,a,e,f,r[6],15,-1560198380),f=c(f,i,a,e,r[13],21,1309151649),e=c(e,f,i,a,r[4],6,-145523070),a=c(a,e,f,i,r[11],10,-1120210379),i=c(i,a,e,f,r[2],15,718787259),f=c(f,i,a,e,r[9],21,-343485551),n[0]=h(e,n[0]),n[1]=h(f,n[1]),n[2]=h(i,n[2]),n[3]=h(a,n[3])}function f(n){txt="";var r,t=n.length,u=[1732584193,-271733879,-1732584194,271733878];for(r=64;t>=r;r+=64)e(u,i(n.substring(r-64,r)));n=n.substring(r-64);var o=[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],c=n.length;for(r=0;c>r;r++)o[r>>2]|=n.charCodeAt(r)<<(r%4<<3);if(o[r>>2]|=128<<(r%4<<3),r>55)for(e(u,o),r=16;r--;)o[r]=0;return o[14]=8*t,e(u,o),u}function i(n){var r,t=[];for(r=0;64>r;r+=4)t[r>>2]=n.charCodeAt(r)+(n.charCodeAt(r+1)<<8)+(n.charCodeAt(r+2)<<16)+(n.charCodeAt(r+3)<<24);return t}function a(n){for(var r="",t=0;4>t;t++)r+=v[n>>8*t+4&15]+v[n>>8*t&15];return r}function d(n){for(var r=n.length,t=0;r>t;t++)n[t]=a(n[t]);return n.join("")}function h(n,r){return n+r&4294967295}function h(n,r){var t=(65535&n)+(65535&r),u=(n>>16)+(r>>16)+(t>>16);return u<<16|65535&t}var v="0123456789abcdef".split("");return"5d41402abc4b2a76b9719d911017c592"!=d(f("hello")),d(f(n))}}();