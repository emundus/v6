<?php
defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper as JComponentHelper;
?>

<script type="text/javascript" language="javascript">
    jQuery(document).ready(function() {    
        
        jQuery('#redirect_to_non_www').on('change', function(e) {
            // triggers when whole value changed
            var end = jQuery('#redirect_to_non_www option:selected').val();
            if (end == 1)
            {
                jQuery("#redirect_to_www").val("0").trigger('chosen:updated');                        
            }            
        });
        
        jQuery('#redirect_to_www').on('change', function(e) {
            // triggers when whole value changed
            var end = jQuery('#redirect_to_www option:selected').val();
            if (end == 1)
            {
                jQuery("#redirect_to_non_www").val("0").trigger('chosen:updated');                        
            }            
        });
          
        jQuery( "#li_autoprotection_tab" ).click(function() {
            SetActiveTabHtaccess('autoprotection');
        });
        jQuery( "#li_headers_protection_tab" ).click(function() {
            SetActiveTabHtaccess('headers_protection');
        });
        jQuery( "#li_user_agents_protection_tab" ).click(function() {
            SetActiveTabHtaccess('user_agents_protection');
        });
        jQuery( "#li_fingerprinting_tab" ).click(function() {
            SetActiveTabHtaccess('fingerprinting');
        });
        jQuery( "#li_backend_protection_tab" ).click(function() {
            SetActiveTabHtaccess('backend_protection');
        });
        jQuery( "#li_performance_tab_tab" ).click(function() {
            SetActiveTabHtaccess('performance_tab');
        });
        jQuery( "#save_default_user_agent_button" ).click(function() {
            Joomla.submitbutton('save_default_user_agent');
        });
        jQuery( "#boton_default_user_agent" ).click(function() {
            muestra_default_user_agent();
        });
    <?php
    // Obtenemos la longitud de la clave que tenemos que generar
    $params = JComponentHelper::getParams('com_securitycheckpro');
    $size = $params->get('secret_key_length', 20);                
    ?>
        jQuery( "#hide_backend_url_button" ).click(function() {
            document.getElementById("hide_backend_url").value = Password.generate(<?php echo $size; ?>);
        });
        jQuery( "#add_exception_button" ).click(function() {
            add_exception();
        });
        jQuery( "#delete_exception_button" ).click(function() {
            delete_exception();
        });
        jQuery( "#delete_all_button" ).click(function() {
            delete_all();
        });
        
        // Chequeamos cuando se pulsa el botón 'close' del modal 'initialize data' para actualizar la página
        $(function() {
            $("#buttonclose").click(function() {
                setTimeout(function () {window.location.reload()},1000);                
            });
        });            
        
    });        

    var Password = {
     
      _pattern : /[a-zA-Z0-9]/, 
      
      _getRandomByte : function()
      {
        // http://caniuse.com/#feat=getrandomvalues
        if(window.crypto && window.crypto.getRandomValues) 
        {
          var result = new Uint8Array(1);
          window.crypto.getRandomValues(result);
          return result[0];
        }
        else if(window.msCrypto && window.msCrypto.getRandomValues) 
        {
          var result = new Uint8Array(1);
          window.msCrypto.getRandomValues(result);
          return result[0];
        }
        else
        {
          return Math.floor(Math.random() * 256);
        }
      },
      
      generate : function(length)
      {
        return Array.apply(null, {'length': length})
          .map(function()
          {
            var result;
            while(true) 
            {
              result = String.fromCharCode(this._getRandomByte());
              if(this._pattern.test(result))
              {
                return result;
              }
            }        
          }, this)
          .join('');  
      }    
        
    };

    var ActiveTabHtaccess = "autoprotection";

    function SetActiveTabHtaccess($value) {
        ActiveTabHtaccess = $value;
        storeValue('active_htaccess', ActiveTabHtaccess);
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
        hideIt();
        ActiveTabHtaccess = getStoredValue('active_htaccess');
                            
        if (ActiveTabHtaccess) {
            $('.nav-tabs a[href="#' + ActiveTabHtaccess + '"]').parent().addClass('active');
            $('.nav-tabs a[href="#' + ActiveTabHtaccess + '"]').tab('show');
        } else {
            $('.nav-tabs a[href="#autoprotection"]').parent().addClass('active');
        }            
    };

    function add_exception() {
        var exception = document.adminForm.exception.value;
        
        var previous_exceptions = (document.adminForm.backend_exceptions.value).length;
        
        if (previous_exceptions > 0 ) {
            document.adminForm.backend_exceptions.value += ',' + exception;
        } else {
            document.adminForm.backend_exceptions.value += exception;
        }
        document.adminForm.exception.value = "";
    }

    function delete_exception() {
        var exception = document.adminForm.exception.value;
        
        var textarea = document.getElementById("backend_exceptions");
        
        // Borramos todas las opciones posibles, comas delante y detrás y sin comas
        textarea.value = textarea.value.replace(',' + exception, "");    
        textarea.value = textarea.value.replace(exception + ',', "");    
        textarea.value = textarea.value.replace(exception, "");
        
        document.adminForm.exception.value = "";
    }

    function delete_all() {
        var exception = document.adminForm.exception.value;
        
        var textarea = document.getElementById("backend_exceptions");
        
        textarea.value = "";    
    }

    function muestra_default_user_agent(){
            jQuery("#div_default_user_agents").modal('show');            
    }

    function hideIt(){
        var selected = document.getElementById('backend_protection_applied');
        if (selected.checked) {        
            jQuery("#menu_hide_backend_1").hide();
            jQuery("#menu_hide_backend_2").hide();
            jQuery("#menu_hide_backend_3").hide();
            jQuery("#menu_hide_backend_4").hide();
            jQuery("#block").hide();
            jQuery("#block2").hide();
            jQuery("#block3").hide();
            jQuery("#block4").hide();
            document.getElementById("hide_backend_url").value = "";
            document.getElementById("backend_exceptions").value = "";        
            document.getElementById("backend_protection_applied").value = "1";
        } else {
            jQuery("#menu_hide_backend_1").show();
            jQuery("#menu_hide_backend_2").show();
            jQuery("#menu_hide_backend_3").show();
            jQuery("#menu_hide_backend_4").show();
            jQuery("#block").show();
            jQuery("#block2").show();
            jQuery("#block3").show();
            jQuery("#block4").show();
            document.getElementById("backend_protection_applied").value = "0";
        }    
    }
</script>
