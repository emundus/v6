<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

?>
<script language="javascript" type="text/javascript">
	function copyToClipboard(value, action) {
		try {
			if (document.getElementById) {
				innerHTML="";
				if (action=="copy") {
					srcEl = document.getElementById("original_value_"+value);
					innerHTML = srcEl.innerHTML;
				}
                if (action=="translate") {
                    srcEl = document.getElementById("original_value_"+value);
                    innerHTML = translateService(srcEl.innerHTML);
                }
				refField = document.getElementById("refField_"+value);
				if ( typeof(refField)=="object") {
					refField = document.getElementById("refField_"+value);
					if(refField.html_mode == false) refField.setCode(innerHTML)
				}
				else {
					if (window.clipboardData){
						window.clipboardData.setData("Text",innerHTML);
						alert("<?php echo preg_replace( '#<br\s*/>#', '\n', JText::_('CLIPBOARD_COPIED') );?>");
					}
					else {
						srcEl = document.getElementById("text_origText_"+value);
						if (srcEl != null) {
							srcEl.value = innerHTML;
							srcEl.select();
							alert("<?php echo preg_replace( '#<br\s*/>#', '\n', JText::_('CLIPBOARD_COPY'));?>");
						}
					}
				}
			}
		}
		catch(e){
			alert("<?php echo preg_replace( '#<br\s*/>#', '\n', JText::_('CLIPBOARD_NOSUPPORT'));?>");
		}
	}
</script>
