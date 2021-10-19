<?php
defined('_JEXEC') or die();

?>

<script type="text/javascript" language="javascript">
    function filter_vulnerable_extension(product) {
        url = 'index.php?option=com_securitycheckpro&controller=securitycheckpro&format=raw&task=filter_vulnerable_extension&product=';
		url = url.concat(product);		
        jQuery.ajax({
            url: url,                            
            method: 'GET',
            error: function(request, status, error) {
                alert(request.responseText);
            },
            success: function(response){                                
                jQuery("#response_result").text("");
                jQuery("#response_result").append(response);                
                jQuery("#modal_vuln_extension").modal('show');                            
            }
        });
    }    
</script>
