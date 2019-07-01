<?php
defined('_JEXEC') or die();

?>

<script type="text/javascript" language="javascript">

	jQuery(document).ready(function() {
		jQuery( "#li_europe_tab" ).click(function() {
			SetActiveTabContinent('europe');
		});
		jQuery( "#li_northAmerica_tab" ).click(function() {
			SetActiveTabContinent('northAmerica');
		});
		jQuery( "#li_southAmerica_tab" ).click(function() {
			SetActiveTabContinent('southAmerica');
		});
		jQuery( "#africa" ).click(function() {
			SetActiveTabContinent('africa');
		});
		jQuery( "#asia" ).click(function() {
			SetActiveTabContinent('asia');
		});
		jQuery( "#oceania" ).click(function() {
			SetActiveTabContinent('oceania');
		});
		jQuery( "#enable_url_inspector_button" ).click(function() {
			Joomla.submitbutton('enable_url_inspector');
		});
		jQuery( "#li_lists_tab" ).click(function() {
			SetActiveTab('lists');
		});
		jQuery( "#li_methods_tab" ).click(function() {
			SetActiveTab('methods');
		});
		jQuery( "#li_mode_tab" ).click(function() {
			SetActiveTab('mode');
		});
		jQuery( "#li_logs_tab" ).click(function() {
			SetActiveTab('logs');
		});
		jQuery( "#li_redirection_tab" ).click(function() {
			SetActiveTab('redirection');
		});
		jQuery( "#li_second_tab" ).click(function() {
			SetActiveTab('second');
		});
		jQuery( "#li_email_notifications_tab" ).click(function() {
			SetActiveTab('email_notifications');
		});
		jQuery( "#li_exceptions_tab" ).click(function() {
			SetActiveTab('exceptions');
		});
		jQuery( "#li_session_protection_tab" ).click(function() {
			SetActiveTab('session_protection');
		});
		jQuery( "#li_geoblock_tab" ).click(function() {
			SetActiveTab('geoblock');
		});
		jQuery( "#li_upload_scanner_tab" ).click(function() {
			SetActiveTab('upload_scanner');
		});
		jQuery( "#li_spam_protection_tab" ).click(function() {
			SetActiveTab('spam_protection');
		});
		jQuery( "#li_url_inspector_tab" ).click(function() {
			SetActiveTab('url_inspector');
		});
		jQuery( "#li_track_actions_tab" ).click(function() {
			SetActiveTab('track_actions');
		});
		jQuery( "#search_button" ).click(function() {
			document.getElementById('filter_search').value='';
			this.form.submit();
		});
		jQuery( "#li_blacklist_tab" ).click(function() {
			SetActiveTabLists('blacklist');
		});
		jQuery( "#li_dynamic_blacklist_tab" ).click(function() {
			SetActiveTabLists('dynamic_blacklist_tab');
		});
		jQuery( "#li_whitelist_tab" ).click(function() {
			SetActiveTabLists('whitelist');
		});
		jQuery( "#upload_import_button" ).click(function() {
			Joomla.submitbutton('import_blacklist');
		});
		jQuery( "#add_ip_whitelist_button" ).click(function() {
			setOwnIP(); 
			Joomla.submitbutton('addip_whitelist');
		});
		jQuery( "#add_ip_blacklist_button" ).click(function() {
			Joomla.submitbutton('addip_blacklist'); 
		});
		jQuery( "#export_blacklist_button" ).click(function() {
			Joomla.submitbutton('Export_blacklist');
		});
		jQuery( "#delete_ip_blacklist_button" ).click(function() {
			Joomla.submitbutton('deleteip_blacklist');
		});
		jQuery( "#deleteip_dynamic_blacklist_button" ).click(function() {
			Joomla.submitbutton('deleteip_dynamic_blacklist');
		});
		jQuery( "#toggle_dynamic_blacklist" ).click(function() {
			Joomla.checkAll(this);
		});
		jQuery( "#import_whitelist_button" ).click(function() {
			Joomla.submitbutton('import_whitelist');
		});
		jQuery( "#addip_whitelist_button" ).click(function() {
			Joomla.submitbutton('addip_whitelist');
		});
		jQuery( "#export_whitelist_button" ).click(function() {
			Joomla.submitbutton('Export_whitelist');
		});
		jQuery( "#deleteip_whitelist_button" ).click(function() {
			Joomla.submitbutton('deleteip_whitelist');
		});
		jQuery( "#boton_test_email" ).click(function() {
			Joomla.submitbutton('send_email_test');
		});
		jQuery( "#li_header_referer_tab" ).click(function() {
			SetActiveTabExceptions('header_referer');
		});
		jQuery( "#li_base64_tab" ).click(function() {
			SetActiveTabExceptions('base64');
		});
		jQuery( "#li_xss_tab" ).click(function() {
			SetActiveTabExceptions('xss');
		});
		jQuery( "#li_sql_tab" ).click(function() {
			SetActiveTabExceptions('sql');
		});
		jQuery( "#li_lfi_tab" ).click(function() {
			SetActiveTabExceptions('lfi');
		});
		jQuery( "#li_secondlevel_tab" ).click(function() {
			SetActiveTabExceptions('secondlevel');
		});
		jQuery( "#check_all_europe_table_button" ).click(function() {
			CheckAll('europe_table',true,'continentEU');
		});
		jQuery( "#uncheck_all_europe_table_button" ).click(function() {
			CheckAll('europe_table',false,'continentEU');
		});
		jQuery( "#check_all_northamerica_table_button" ).click(function() {
			CheckAll('northamerica_table',true,'continentNA');
		});
		jQuery( "#unccheck_all_northamerica_table_button" ).click(function() {
			CheckAll('northamerica_table',false,'continentNA');
		});
		jQuery( "#check_all_southamerica_table_button" ).click(function() {
			CheckAll('southamerica_table',true,'continentSA');
		});
		jQuery( "#uncheck_all_southamerica_table_button" ).click(function() {
			CheckAll('southamerica_table',false,'continentSA');
		});
		jQuery( "#check_all_africa_table_button" ).click(function() {
			CheckAll('africa_table',true,'continentAF');
		});
		jQuery( "#uncheck_all_africa_table_button" ).click(function() {
			CheckAll('africa_table',false,'continentAF');
		});
		jQuery( "#check_all_asia_table_button" ).click(function() {
			CheckAll('asia_table',true,'continentAF');
		});
		jQuery( "#uncheck_all_asia_table_button" ).click(function() {
			CheckAll('asia_table',false,'continentAF');
		});
		jQuery( "#check_all_oceania_table_button" ).click(function() {
			CheckAll('oceania_table',true,'continentOC');
		});
		jQuery( "#uncheck_all_oceania_table_button" ).click(function() {
			CheckAll('oceania_table',false,'continentOC');
		});
	});
	
	var ActiveTab = "lists"; 
	var ActiveTabLists = "blacklist";
	var ExceptionsActiveTab = "header_referer";
	var ActiveTabContinent = "europe";
	
	function SetActiveTab($value) {
		ActiveTab = $value;
		storeValue('active', ActiveTab);
	}
	
	function SetActiveTabLists($value) {
		ActiveTabLists = $value;
		storeValue('activelists', ActiveTabLists);
	}
	
	function SetActiveTabContinent($value) {
		ActiveTabContinent = $value;
		storeValue('activecontinent', ActiveTabContinent);
	}
	
	function SetActiveTabExceptions($value) {
		ExceptionsActiveTab = $value;
		storeValue('exceptions_active', ExceptionsActiveTab);
	}
	
	function storeValue(key, value) {
		if (localStorage) {
			localStorage.setItem(key, value);
		} else {
			$.cookies.set(key, value);
		}
	}
	
	function getStoredValue(key) {
		if (localStorage) {
			return localStorage.getItem(key);
		} else {
			return $.cookies.get(key);
		}
	}
	
	window.onload = function() {
		ActiveTab = getStoredValue('active');		
		if (ActiveTab) {
			$('.nav-tabs a[href="#' + ActiveTab + '"]').parent().addClass('active');
			$('.nav-tabs a[href="#' + ActiveTab + '"]').tab('show');
		} else {
			$('.nav-tabs a[href="#lists"]').parent().addClass('active');
		}
		
		ActiveTablists = getStoredValue('activelists');
		if (ActiveTablists) {
			$('.nav-tabs a[href="#' + ActiveTablists + '"]').parent().addClass('active');
			$('.nav-tabs a[href="#' + ActiveTablists + '"]').tab('show');
		} else {
			$('.nav-tabs a[href="#blacklist"]').parent().addClass('active');
		}
		
		ExceptionsActiveTab = getStoredValue('exceptions_active');
		if (ExceptionsActiveTab) {
			$('.nav-tabs a[href="#' + ExceptionsActiveTab + '"]').parent().addClass('active');
			$('.nav-tabs a[href="#' + ExceptionsActiveTab + '"]').tab('show');
		} else {
			$('.nav-tabs a[href="#header_referer"]').parent().addClass('active');
		}
		
		ActiveTabContinent = getStoredValue('activecontinent');
		//ActiveTabContinent = "northAmerica";
		if (ActiveTabContinent) {			
			$('.nav-tabs a[href="#' + ActiveTabContinent + '"]').parent().addClass('active');
			$('.nav-tabs a[href="#' + ActiveTabContinent + '"]').tab('show');
		} else {
			$('.nav-tabs a[href="#europe"]').parent().addClass('active');
		}
				
	};
	
		
	function setOwnIP() {
		var ownip = '<?php echo $current_ip; ?>';
		$("#whitelist_add_ip").val(ownip);
		
	}
	
	function muestra_progreso(){
		jQuery("#select_blacklist_file_to_upload").show();
	}
	
	function muestra_progreso_geoblock(){
		jQuery("#div_update_geoblock_database").show();		
		//jQuery("#div_refresh_geoblock").show();
	}
	
	function Disable() {
		
		//Obtenemos el índice las opciones de redirección
		var element = adminForm.elements["redirect_options"].selectedIndex;
		
		// Si está establecida la opción de la propia página, habilitamos el campo redirect_url para escritura. Si no, lo deshabilitamos
		if ( element==0 ) {
			document.getElementById('redirect_url').readOnly = true;
		} else {			
			document.getElementById('redirect_url').readOnly = false;
		}
		
		//Obtenemos el índice de la opción 'strip all tags'
		var element = adminForm.elements["strip_all_tags"].selectedIndex;
				
		// Ocultamos o mostramos la caja de texto según la elección anterior
		if ( element==1 ) {
			$("#tags_to_filter_div").hide();			
		} else {
			$("#tags_to_filter_div").show();			
		}
		
	}
	
	function CheckAll(idname, checktoggle, continentname) {
		var checkboxes = new Array();
		checkboxes = document.getElementById(idname).getElementsByTagName('input');
		document.getElementById(continentname).checked = checktoggle;
		
		for (var i=0; i<checkboxes.length; i++) {
			if (checkboxes[i].type == 'checkbox') {
				checkboxes[i].checked = checktoggle;
			}			
		}
		
    }	 
	
	function disable_continent_checkbox(continentname, name) {
		var checkbox = document.getElementById(name);
		if (checkbox.checked != true) {
			document.getElementById(continentname).checked = false;
		}		
	}

	jQuery(document).ready(function() {
		Disable();				
	});
</script>