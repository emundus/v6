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
?>
<?php if($message1) { ?>
    <div class="alert alert-success">
        <button class="close" type="button" data-dismiss="alert">×</button>
        <div class="alert-message"><?php echo JText::_($message1) ?></div>
    </div>
<?php } ?>
<?php if($message2) { ?>
    <div class="alert alert-success">
        <button class="close" type="button" data-dismiss="alert">×</button>
        <div class="alert-message"><?php echo JText::_($message2) ?></div>
    </div>
<?php } ?>