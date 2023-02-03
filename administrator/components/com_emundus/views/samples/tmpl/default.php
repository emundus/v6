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
<h4><?php echo JText::_('COM_EMUNDUS_SAMPLES_GENERATE_SAMPLES_INTRODUCTION'); ?></h4>
<form method="post" action="index.php?option=com_emundus&controller=samples&task=generate">
    <label for="samples_programs"><?php echo JText::_('COM_EMUNDUS_SAMPLES_GENERATE_PROGRAMS'); ?></label>
    <input type="number" value="0" name="samples_programs"/>

    <label for="samples_campaigns"><?php echo JText::_('COM_EMUNDUS_SAMPLES_GENERATE_CAMPAIGNS'); ?></label>
    <input type="number" value="0" name="samples_campaigns"/>

    <label for="samples_users"><?php echo JText::_('COM_EMUNDUS_SAMPLES_GENERATE_USERS'); ?></label>
    <input type="number" value="0" name="samples_users"/>

    <label for="samples_files"><?php echo JText::_('COM_EMUNDUS_SAMPLES_GENERATE_FILES'); ?></label>
    <input type="number" value="0" name="samples_files"/>

    <br/>
    <button class="em-primary-button em-w-auto">Générer</button>
</form>
</body></html>
