<?php
defined('_JEXEC') or die();

?>

<script type="text/javascript" language="javascript">
    jQuery(document).ready(function() {        
            
        jQuery( "#search_filter_button" ).click(function() {
            document.getElementById('filter_search').value=''; this.form.submit();
        });
    });
    
    jQuery("#whois_button").tooltip();
	
	j_version = '<?php 
        if (version_compare(JVERSION, '3.20', 'lt') ) {
			echo "3";
		} else {
			echo "4";
		}
        ?>'; 
			
	if (j_version == "3") {
		jQuery('#toolbar-plus_blacklist').addClass('btn-danger');
		jQuery('#toolbar-plus_blacklist').find('.button-plus_blacklist').addClass('btn-danger');
		jQuery('#toolbar-plus_blacklist .icon-plus_blacklist').removeClass('icon-plus_blacklist').addClass('icon-plus');
	}
</script>
