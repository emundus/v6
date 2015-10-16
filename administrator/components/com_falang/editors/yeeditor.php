<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
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
				if ( typeof(yee) == "object") {
					if (yeeditor_status == "0") {
						yee.scHolder.html(trim(innerHTML));
					} else {
						alert( 'Please switch to source editor mode.' )
					}
				}
				else {
					if (window.clipboardData){
						window.clipboardData.setData("Text",innerHTML);
						alert("<?php echo preg_replace( '/<br \/>/', '\n', JText::_('CLIPBOARD_COPIED') );?>");
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
			alert("<?php echo preg_replace( '/<br \/>/', '\n', JText::_('CLIPBOARD_NOSUPPORT'));?>");
		}
	}
</script>
