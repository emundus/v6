<?php
defined('_JEXEC') or die();

?>

<script type="text/javascript" language="javascript">
    jQuery(document).ready(function() {    
        
        jQuery( "#filter_rules_search_button" ).click(function() {
            document.getElementById('filter_rules_search').value='';
            this.form.submit();
        });
        
    });    
</script>
