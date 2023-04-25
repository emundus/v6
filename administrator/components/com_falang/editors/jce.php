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
            if ( typeof(tinyMCE)=="object") {
               tinyMCE.editors["refField_"+value].execCommand("mceSetContent",false,innerHTML );
            }
            else {
               if (window.clipboardData){
                  window.clipboardData.setData("Text",innerHTML);
                  alert("<?php echo preg_replace( '#<br\s*/>#', '\n', JText::_('CLIPBOARD_COPIED',true) );?>");
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
         alert("<?php echo preg_replace( '#<br\s*/>#', '\n', JText::_('CLIPBOARD_NOSUPPORT',true));?>");
      }
   }
   
   function getRefField(value){
      try {
         if (document.getElementById) {
            if ( typeof(tinyMCE)=="object") {
            	editor = tinyMCE.editors["refField_"+value];
            	if (editor){
            		return editor.getContent();
            	}
            	return "";
            }
            else {
                return "";
            }
         }
      }
      catch(e){
         alert("<?php echo preg_replace( '#<br\s*/>#', '\n', JText::_('NO_PREVIEW',true));?>");
         return "";
      }
   }
</script>