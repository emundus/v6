<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

$contentElement = $this->falangManager->getContentElement( $this->id );

?>
    <table class="adminList" cellspacing="1">
    <tr align="center" valign="middle">
        <td width="30%" align="left" valign="top"><strong><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_NAME');?></strong></td>
        <td width="20%" align="left" valign="top"><?php echo $contentElement->Name;?></td>
        <td align="left"></td>
    </tr>
    <tr align="center" valign="middle">
        <td width="30%" align="left" valign="top"><strong><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_AUTHOR');?></strong></td>
        <td width="20%" align="left" valign="top"><?php echo $contentElement->Author;?></td>
        <td align="left"></td>
    </tr>
    <tr align="center" valign="middle">
        <td width="30%" align="left" valign="top"><strong><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_VERSION');?></strong></td>
        <td width="20%" align="left" valign="top"><?php echo $contentElement->Version;?></td>
        <td align="left"></td>
    </tr>
    <tr align="center" valign="middle">
        <td width="30%" align="left" valign="top"><strong><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_DESCRIPTION');?></strong></td>
        <td width="20%" align="left" valign="top"><?php echo $contentElement->Description;?></td>
        <td align="left"></td>
    </tr>
    </table>

