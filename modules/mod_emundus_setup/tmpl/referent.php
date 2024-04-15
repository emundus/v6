<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<div>
    <div class="mb-4">
        <?php echo $intro; ?>
    </div>
	<?php foreach ($setups as $type => $setup) : ?>
        <div class="mb-6">
            <h3 class="mb-2"><?php echo JText::_('COM_EMUNDUS_SETUP_REFERENT_TYPE_'.$type); ?></h3>
            <p class="mb-3"><?php echo JText::_('COM_EMUNDUS_SETUP_REFERENT_TYPE_DESC_'.$type); ?></p>
            <table style="width: 90%">
                <thead>
                    <tr>
                        <th><?php echo JText::_('COM_EMUNDUS_SETUP_REFERENT_TABLE_CAMPAIGNS'); ?></th>
                        <th><?php echo JText::_('COM_EMUNDUS_SETUP_REFERENT_TABLE_COUNT_REFERENT'); ?></th>
                        <th><?php echo JText::_('COM_EMUNDUS_SETUP_REFERENT_TABLE_EMAIL'); ?></th>
                        <th><?php echo JText::_('COM_EMUNDUS_SETUP_REFERENT_TABLE_ATTACHMENTS'); ?></th>
                    </tr>
                </thead>
                <?php foreach ($setup as $referent_setup) : ?>
                    <tr>
                        <td><?php echo $referent_setup['campaign']; ?></td>
                        <td><?php echo $referent_setup['references_count']; ?></td>
                        <td><?php echo $referent_setup['email_tmpl_id'] ? '<a href="index.php?option=com_emundus&view=emails&layout=add&eid='.$referent_setup['email_tmpl_id'].'" target="_blank">'.$referent_setup['email_tmpl'].'</a>' : JText::_('COM_EMUNDUS_SETUP_REFERENT_NO_EMAIL'); ?></td>
                        <td><?php echo $referent_setup['attachments'] ?: JText::_('COM_EMUNDUS_SETUP_REFERENT_NO_ATTACHMENT'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endforeach; ?>

</div>

<script type="text/javascript">
</script>
