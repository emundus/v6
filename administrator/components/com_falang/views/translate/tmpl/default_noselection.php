<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

?>



<form action="<?php echo JRoute::_('index.php?option=com_falang');?>" method="post" name="adminForm" id="adminForm">
    <?php if(!empty( $this->sidebar)): ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
    <?php else : ?>
        <div id="j-main-container">
    <?php endif;?>

    <?php if (FALANG_J30) { ?>
    <div class="clearfix"> </div>
        <table>
    <?php } else { ?>
        <table>
    <?php } ?>

		<tr>
			<td width="100%">
			</td>
			<td nowrap="nowrap">
				<?php echo JText::_('COM_FALANG_SELECT_LANGUAGE_TITLE'). ':<br/>' .$this->langlist;?>
			</td>
			<td nowrap="nowrap">
				<?php echo JText::_('COM_FALANG_SELECT_CONTENT_ELEMENT_TITLE'). ':<br/>' .$this->clist; ?>
			</td>
		</tr>
	</table>

    <div id="system-message-container">
        <div class="alert alert-info">
            <?php echo JText::_('COM_FALANG_NOELEMENT_SELECTED');?>
        </div>
    </div>

	<input type="hidden" name="option" value="com_falang" />
	<input type="hidden" name="task" value="translate.show" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
