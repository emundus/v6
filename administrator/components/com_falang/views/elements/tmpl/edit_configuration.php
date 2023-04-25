<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2021. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

$contentElement = $this->falangManager->getContentElement( $this->element );

?>
    <table class="table">
        <thead>
        <tr>
            <th scope="col"><strong><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_NAME');?></strong></th>
            <th scope="col"><strong><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_AUTHOR');?></strong></th>
            <th scope="col"><strong><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_VERSION');?></strong></th>
            <th scope="col"><strong><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_DESCRIPTION');?></strong></th>
        </tr>
        </thead>
        <tbody>
    <tr>
        <td><?php echo $contentElement->Name;?></td>
        <td><?php echo $contentElement->Author;?></td>
        <td><?php echo $contentElement->Version;?></td>
        <td><?php echo $contentElement->Description;?></td>
    </tr>
        </tbody>
    </table>
