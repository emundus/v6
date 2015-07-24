<?php
defined('_JEXEC') or die;

?>
<script>
jQuery(function() {
    setHeight();
    setInterval(setHeight, 5000);
});
function setHeight() {
    var iframe = parent.document.getElementById('falang-frame');
    if (iframe) {
        iframe.style.height = (jQuery(document).height()+30) + 'px';
    }
}
</script>

<p style="text-align: center;font-size: 20px;">
   <?php echo JText::_('COM_FALANG_EDIT_ON_PAID_VERSION_ONLY')?>
</p>
