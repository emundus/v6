<?php
defined('_JEXEC') or die;

JHtml::_('behavior.formvalidation');

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

<header style="height: 27px; margin-bottom: 10px;">
    <div class="btn-group pull-left">
        <button type="button" onclick="Joomla.submitform('translation.apply', document.getElementById('component-form'));" class="btn btn-small btn-success"><?php echo JText::_('JAPPLY');?></button>
    </div>
</header>

<?php //require dirname(__FILE__).'/edit.php'; ?>
