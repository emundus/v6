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
				if ( typeof(CKEDITOR) == "object") {
					var oEditor = CKEDITOR.instances["refField_"+value] ;
					if ( oEditor.mode == 'wysiwyg')
					{
						// Insert the desired HTML.
						oEditor.insertHtml(innerHTML) ;
					}
					else	alert( 'Please switch to WYSIWYG mode.' ) ;

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
							alert("<?php echo preg_replace( '/<br \/>/', '\n', JText::_('CLIPBOARD_COPY'));?>");
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
