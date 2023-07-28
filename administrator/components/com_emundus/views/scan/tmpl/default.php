<?php
/**
* @package Joomla
* @subpackage eMundus
* @copyright Copyright (C) 2019 emundus.fr. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die('RESTRICTED');


?>
<html><body bgcolor="#FFFFFF">
<h4><?php echo JText::_('COM_EMUNDUS_SCAN_PHP8'); ?></h4>
<button id="display-scan">Scanner le code</button>
<div id="result-container">

</div>
</body></html>


<script>
    document.getElementById('display-scan').addEventListener('click', function() {
        fetch('index.php?option=com_emundus&controller=scan&task=scan')
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    document.getElementById('result-container').innerHTML = '<pre>' + JSON.stringify(data.data, null, '\t') + '</pre>';
                }
            });
    });
</script>