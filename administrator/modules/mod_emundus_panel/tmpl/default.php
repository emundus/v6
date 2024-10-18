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
            <th class="em-text-align-left"><?= JText::_('MOD_EMUNDUS_PANEL_FEATURE') ?></th>
            <th><?= JText::_('MOD_EMUNDUS_PANEL_ENABLED') ?></th>
            <th class="em-text-align-left"><?= JText::_('MOD_EMUNDUS_PANEL_HELP') ?></th>
        </tr>
        <?php foreach ($features as $feature) :?>
        <tr>
            <td>
                <?php if($feature->id != 0) : ?>
                    <a href="index.php?option=com_plugins&view=plugin&layout=edit&extension_id=<?= $feature->id ?>"><?= JText::_($feature->label) ?></a>
                <?php else : ?>
                    <?= JText::_($feature->label) ?>
                <?php endif ?>
            </td>
            <td class="em-text-center">
                <?php if($feature->enabled) : ?>
                <span class="material-icons em-green">done</span>
                <?php else : ?>
                <span class="material-icons em-red">block</span>
                <?php endif ?>
            </td>
            <td><a href="<?= $feature->help ?>" target="_blank"><?= $feature->help ?></a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
