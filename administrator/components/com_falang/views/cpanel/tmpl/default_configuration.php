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

<table class="adminlist table table-striped">
    <tr class="row0">
        <th width="180" align="left"><?php echo JText::_('COM_FALANG_CPANEL_CONFIGURATION_PLG_FALANG'); ?></th>
        <td ><?php
            $falang_driver = JPluginHelper::getPlugin('system', 'falangdriver');
            if (!empty($falang_driver)) {?>
                <i class="fa fa-check fa-success"></i>
            <?php } else { ?>
                <i class="fa fa-times fa-danger"></i>
            <?php } ?>
        </td>
    </tr>
    <tr class="row1">
        <th width="180" align="left"><?php echo JText::_('COM_FALANG_CPANEL_CONFIGURATION_PLG_LANG_FILTER'); ?></th>
        <td><?php
            $language_filter = JPluginHelper::getPlugin('system', 'languagefilter');
            if (!empty($language_filter)) {?>
                <i class="fa fa-check fa-success"></i>
            <?php } else { ?>
                <i class="fa fa-times fa-danger"></i>
            <?php } ?>
        </td>
    </tr>
    <tr class="row0">
        <th width="180" align="left"><?php echo JText::_('COM_FALANG_CPANEL_CONFIGURATION_PLG_QJUMP'); ?></th>
        <td><?php
            $quick_jump = JPluginHelper::getPlugin('system', 'falangquickjump');
            if (!empty($quick_jump)) {?>
                <i class="fa fa-check fa-success"></i>
            <?php } else { ?>
                <i class="fa fa-times fa-danger"></i>
            <?php } ?>
        </td>
    </tr>

</table>