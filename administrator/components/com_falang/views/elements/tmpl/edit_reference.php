<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

$contentElement = $this->falangManager->getContentElement( $this->element );
$contentTable = $contentElement->getTable();
?>

    <p >
        <span class="font-weight-bold"><?php echo JText::_('COM_FALANG_ELEMENT_DATABASE_TABLE');?></span>
        <?php echo $contentTable->Name;?>
    </p>

<table class="table table-striped ">
    <thead>
    <tr>
        <th><?php echo JText::_('COM_FALANG_DBFIELDNAME');?></th>
        <th><?php echo JText::_('COM_FALANG_DBFIELDTYPE');?></th>
        <th><?php echo JText::_('COM_FALANG_DBFIELDLABEL');?></th>
        <th><?php echo JText::_('COM_FALANG_TRANSLATE');?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    $k=0;
    foreach( $contentTable->Fields as $tableField ) {
        ?>
        <tr class="<?php echo "row$k"; ?>">
            <td><?php echo $tableField->Name ? $tableField->Name : "&nbsp;";?></td>
            <td><?php echo $tableField->Type ? $tableField->Type : "&nbsp;";?></td>
            <td><?php echo $tableField->Lable ? $tableField->Lable : "&nbsp;";?></td>
            <td><?php echo $tableField->Translate ? JText::_('YES') : JText::_('NO');?></td>
        </tr>
        <?php
        $k=1-$k;
    }
    ?>
    </tbody>
</table>
