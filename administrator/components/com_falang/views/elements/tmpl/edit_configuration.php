<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stephane
 * Date: 04/12/12
 * Time: 10:54
 * To change this template use File | Settings | File Templates.
 */
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

