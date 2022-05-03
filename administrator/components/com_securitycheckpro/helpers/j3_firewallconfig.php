<?php
defined('_JEXEC') or die();
?>

<script type="text/javascript" language="javascript">
    var textdecoded = "no";

    jQuery(document).ready(function() {        
        Disable();    
        
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
            Joomla.submitbutton('import_list');
        });
        jQuery( "#add_ip_whitelist_button" ).click(function() {			
            setOwnIP();
			Joomla.submitbutton('addip_whitelist');
        });
        jQuery( "#add_ip_whitelist_button2" ).click(function() {
			setOwnIP(); 
			Joomla.submitbutton('addip_whitelist');
        });
        jQuery( "#add_ip_blacklist_button" ).click(function() {				
			var el = '<input type="hidden" name="task" value="addip_blacklist"></input>';
			jQuery('#adminForm').append(el);
			adminForm.submit();
            //Joomla.submitbutton('addip_blacklist'); 
        });
        jQuery( "#export_blacklist_button" ).click(function() {
			var el = '<input type="hidden" name="export" value="blacklist"></input>';
			jQuery('#adminForm').append(el);
			adminForm.submit();
            Joomla.submitbutton('export_list');
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
            Joomla.submitbutton('import_list');
        });
        jQuery( "#addip_whitelist_button" ).click(function() {
            var el = '<input type="hidden" name="task" value="addip_whitelist"></input>';
			jQuery('#adminForm').append(el);
			adminForm.submit();			
        });
        jQuery( "#select_blacklist_file_to_upload" ).click(function() {
            var el = '<input type="hidden" id="import" name="import" value="blacklist"></input>';
			jQuery('#div_boton_subida_blacklist').append(el);
        });	
		jQuery( "#select_whitelist_file_to_upload" ).click(function() {
            var el = '<input type="hidden" id="import" name="import" value="whitelist"></input>';
			jQuery('#div_boton_subida_whitelist').append(el);
        });	
		jQuery( "#export_whitelist_button" ).click(function() {
            var el = '<input type="hidden" name="export" value="whitelist"></input>';
			jQuery('#adminForm').append(el);
			adminForm.submit();
            Joomla.submitbutton('export_list');
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
        
        jQuery( "#second_level_words" ).focusin(function() {
            if (textdecoded == "no") {                
                // Decodificamos el contenido del campo de texto
                var second_text = jQuery('#second_level_words').val();                            
                var decoded = Base64.decode(second_text);                    
                jQuery('#second_level_words').val(decoded);
                textdecoded = "si";
            }
        });
        
        jQuery( "#second_level_words" ).focusout(function() {                
            if (textdecoded == "si") {                
                var second_text = jQuery('#second_level_words').val();
                var encoded = Base64.encode(second_text);            
                jQuery('#second_level_words').val(encoded);
                textdecoded = "no";
            }
        });
        
    });
    
    var Base64 = {


            _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",


            encode: function(input) {
                var output = "";
                var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
                var i = 0;

                input = Base64._utf8_encode(input);

                while (i < input.length) {

                    chr1 = input.charCodeAt(i++);
                    chr2 = input.charCodeAt(i++);
                    chr3 = input.charCodeAt(i++);

                    enc1 = chr1 >> 2;
                    enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                    enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                    enc4 = chr3 & 63;

                    if (isNaN(chr2)) {
                        enc3 = enc4 = 64;
                    } else if (isNaN(chr3)) {
                        enc4 = 64;
                    }

                    output = output + this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) + this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

                }

                return output;
            },


            decode: function(input) {
                var output = "";
                var chr1, chr2, chr3;
                var enc1, enc2, enc3, enc4;
                var i = 0;

                input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

                while (i < input.length) {

                    enc1 = this._keyStr.indexOf(input.charAt(i++));
                    enc2 = this._keyStr.indexOf(input.charAt(i++));
                    enc3 = this._keyStr.indexOf(input.charAt(i++));
                    enc4 = this._keyStr.indexOf(input.charAt(i++));

                    chr1 = (enc1 << 2) | (enc2 >> 4);
                    chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                    chr3 = ((enc3 & 3) << 6) | enc4;

                    output = output + String.fromCharCode(chr1);

                    if (enc3 != 64) {
                        output = output + String.fromCharCode(chr2);
                    }
                    if (enc4 != 64) {
                        output = output + String.fromCharCode(chr3);
                    }

                }

                output = Base64._utf8_decode(output);
                
                return output;

            },

            _utf8_encode: function(string) {
                string = string.replace(/\r\n/g, "\n");
                var utftext = "";

                for (var n = 0; n < string.length; n++) {

                    var c = string.charCodeAt(n);

                    if (c < 128) {
                        utftext += String.fromCharCode(c);
                    }
                    else if ((c > 127) && (c < 2048)) {
                        utftext += String.fromCharCode((c >> 6) | 192);
                        utftext += String.fromCharCode((c & 63) | 128);
                    }
                    else {
                        utftext += String.fromCharCode((c >> 12) | 224);
                        utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                        utftext += String.fromCharCode((c & 63) | 128);
                    }

                }

                return utftext;
            },

            _utf8_decode: function(utftext) {
                var string = "";
                var i = 0;
                var c = c1 = c2 = 0;

                while (i < utftext.length) {

                    c = utftext.charCodeAt(i);

                    if (c < 128) {
                        string += String.fromCharCode(c);
                        i++;
                    }
                    else if ((c > 191) && (c < 224)) {
                        c2 = utftext.charCodeAt(i + 1);
                        string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                        i += 2;
                    }
                    else {
                        c2 = utftext.charCodeAt(i + 1);
                        c3 = utftext.charCodeAt(i + 2);
                        string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                        i += 3;
                    }

                }

                return string;
            }

        }
    
    var ActiveTab = "lists"; 
    var ActiveTabLists = "blacklist";
    var ExceptionsActiveTab = "header_referer";
        
    function SetActiveTab($value) {
        ActiveTab = $value;
        storeValue('active', ActiveTab);

        if ($value == "second") {                
        } else {    
            if (textdecoded == "si") {
                // Codificamos el contenido del campo de texto
                var second_text = jQuery('#second_level_words').val();
                var encoded = Base64.encode(second_text);            
                jQuery('#second_level_words').val(encoded);
                textdecoded = "no";
            }
        }
    }
    
    function SetActiveTabLists($value) {
        ActiveTabLists = $value;
        storeValue('activelists', ActiveTabLists);
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
       
                
    };
    
        
    function setOwnIP() {
        var ownip = '<?php echo $current_ip; ?>';
        $("#whitelist_add_ip").val(ownip);
        
    }
    
    function muestra_progreso(){
        jQuery("#select_blacklist_file_to_upload").show();
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
</script>
