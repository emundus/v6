<?php
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<script language="javascript" type="text/javascript">
    function copyToClipboard(value, action) {
        //codemirror
        try {
            if (document.getElementById) {
                innerHTML="";
                if (action=="copy") {
                    srcEl = document.getElementById("original_value_"+value);
                    innerHTML = srcEl.innerHTML;
                }
                //Joomla.editors.instances["refField_"+value].replaceSelection(innerHTML);
                Joomla.editors.instances["refField_"+value].setValue(innerHTML);

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
        catch(e){
            alert("<?php echo preg_replace( '#<br\s*/>#', '\n', JText::_('CLIPBOARD_NOSUPPORT'));?>");
        }
    }
</script>
