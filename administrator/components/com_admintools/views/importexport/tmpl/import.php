<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

defined('_JEXEC') or die;
?>
<form name="adminForm" id="adminForm" action="index.php" method="post" class="form form-horizontal" enctype="multipart/form-data">
    <input type="hidden" name="option" value="com_admintools"/>
    <input type="hidden" name="view" value="importexport"/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>

    <fieldset>
        <legend><?php echo JText::_('ATOOLS_TITLE_IMPORT_SETTINGS')?></legend>

        <div class="control-group">
            <label class="control-label"><?php echo JText::_('COM_ADMINTOOLS_IMPORTEXPORT_FILE')?></label>
            <div class="controls">
                <input type="file" name="importfile" value="" />
            </div>
        </div>
    </fieldset>
</form>