<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2011, Think Network GmbH, Munich
 *
 * All rights reserved.  The Joom!Fish project is a set of extentions for
 * the content management system Joomla!. It enables Joomla!
 * to manage multi lingual sites especially in all dynamic information
 * which are stored in the database.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * $Id: default_noselection.php 1551 2011-03-24 13:03:07Z akede $
 * @package joomfish
 * @subpackage Views
 *
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
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
	<input type="hidden" name="task" value="translate.orphans" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
