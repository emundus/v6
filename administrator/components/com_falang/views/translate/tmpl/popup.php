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


