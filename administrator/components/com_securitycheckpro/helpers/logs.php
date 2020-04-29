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
</script>
