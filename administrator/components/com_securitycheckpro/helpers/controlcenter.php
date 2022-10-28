<?php
defined('_JEXEC') or die();

echo '<link href="/media/com_securitycheckpro/new/vendor/chosen/chosen.css" rel="stylesheet" type="text/css">';
?>

<script type="text/javascript" language="javascript">
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

	// Add element to a form
	function add_element_to_form(name,value) {
		var input = document.createElement("input");
		input.setAttribute("type", "hidden");
		input.setAttribute("name", name);
		input.setAttribute("value", value);

		//append to form element that you want .
		document.getElementById("adminForm").appendChild(input);
	}
	
</script>
