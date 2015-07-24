<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stephane
 * Date: 04/12/12
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */

$contentElement = $this->falangManager->getContentElement( $this->id );
$contentTable = $contentElement->getTable();
?>

<table class="adminList" cellspacing="1">
    <tr align="center" valign="middle">
        <td width="15%" align="left" valign="top"><strong><?php echo JText::_('COM_FALANG_ELEMENT_DATABASE_TABLE');?></strong><br /><?php echo JText::_('COM_FALANG_ELEMENT_DATABASE_TABLE_HELP');?></td>
        <td width="60%" align="left" valign="top"><?php echo $contentTable->Name;?></td>
    </tr>
    <tr align="center" valign="middle">
        <td width="15%" align="left" valign="top"><strong><?php echo JText::_('COM_FALANG_ELEMENT_DATABASE_FIELDS');?></strong><br /><?php echo JText::_('COM_FALANG_ELEMENT_DATABASE_FIELDS_HELP');?></td>
        <td width="60%" align="left" valign="top">
            <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
                <tr>
                    <th><?php echo JText::_('COM_FALANG_DBFIELDNAME');?></th>
                    <th><?php echo JText::_('COM_FALANG_DBFIELDTYPE');?></th>
                    <th><?php echo JText::_('COM_FALANG_DBFIELDLABEL');?></th>
                    <th><?php echo JText::_('COM_FALANG_TRANSLATE');?></th>
                </tr>
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
            </table>
            <?php
            ?>
        </td>
    </tr>
</table>
