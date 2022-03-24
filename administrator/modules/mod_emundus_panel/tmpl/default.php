<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_emundus_panel
 *
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
?>
<div class="em-p-8-12">
    <h3><?= $sitename ?> - <?= $version ?></h3>
    <table>
        <tr>
            <td><strong><?= JText::_('MOD_EMUNDUS_PANEL_LAST_UPDATED') ?> : </strong></td>
            <td><?= $last_updated ?></td>
        </tr>
        <?php if (!empty($confluence_link)) : ?>
        <tr>
            <td><strong><?= JText::_('MOD_EMUNDUS_PANEL_CONFLUENCE_LINK') ?> : </strong></td>
            <td><a href="<?= $confluence_link ?>" target="_blank"><?= $confluence_link ?></a></td>
        </tr>
        <?php endif;?>
    </table>

    <h4><?= JText::_('MOD_EMUNDUS_PANEL_FEATURES') ?></h4>
    <table border="1" cellpadding="10">
        <tr>
            <th><?= JText::_('MOD_EMUNDUS_PANEL_FEATURE') ?></th>
            <th><?= JText::_('MOD_EMUNDUS_PANEL_ENABLED') ?></th>
            <th><?= JText::_('MOD_EMUNDUS_PANEL_HELP') ?></th>
        </tr>
    </table>
</div>
