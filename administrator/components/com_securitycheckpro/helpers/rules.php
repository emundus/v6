<?php
defined('_JEXEC') or die();

?>

<script type="text/javascript" language="javascript">
    jQuery(document).ready(function() {    
        
        jQuery( "#filter_acl_search_button" ).click(function() {
            document.getElementById('filter_acl_search').value='';
            jQuery("#adminForm").submit();
        });
        
    });    
</script>
