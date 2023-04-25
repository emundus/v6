<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

$state			= $this->get('state');
$message1		= $state->get('message');
$message2		= $state->get('extension.message');

//use for message
JFactory::getDocument()->addScript('components/com_falang/assets/js/toast.js');

?>
    <script type="text/javascript">
        toastr.options = { "progressBar": true, "positionClass": "toast-top-center","showDuration": "300","hideDuration": "500","timeOut": "3500"};

    </script>
<?php if($message1) { ?>
    <script type="text/javascript">
        toastr.success('<?php echo JText::_($message1) ?>');
    </script>
<?php } ?>
<?php if($message2) { ?>
    <script type="text/javascript">
        toastr.success('<?php echo JText::_($message2) ?>');
    </script>
<?php } ?>